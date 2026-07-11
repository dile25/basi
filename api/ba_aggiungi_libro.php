<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Accesso non autorizzato']);
    exit;
}

$user         = $_SESSION['IdUtente'];
$nome         = trim($_POST['nome'] ?? '');
$autore       = trim($_POST['autore'] ?? '');
$desc         = trim($_POST['descrizione'] ?? '');
$prezzo       = floatval($_POST['prezzo'] ?? 0);
$qta          = intval($_POST['quantita'] ?? 0);
$cat          = trim($_POST['categoria'] ?? '');
$sottocat     = trim($_POST['sottocategoria'] ?? '');
$tipoProdotto = trim($_POST['tipo_prodotto'] ?? 'libro');
$testata      = trim($_POST['testata'] ?? '') ?: null;

$abilitaSconto = isset($_POST['abilita_sconto']);

// --- Pacchetto libro (saga/promo) ---
$idPacchettoEsist = intval($_POST['id_pacchetto_esistente'] ?? 0);
$nomePacchetto    = trim($_POST['nome_pacchetto'] ?? '');
$libriPacchetto   = $_POST['libri_pacchetto'] ?? [];
$sconto2          = intval($_POST['sconto_2'] ?? 10);
$sconto3          = intval($_POST['sconto_3'] ?? 20);
$scontoTutti      = intval($_POST['sconto_tutti'] ?? 30);

// --- Abbonamento periodico (un solo pacchetto per prodotto) ---
$idAbbonamentoEsist = intval($_POST['id_abbonamento_esistente'] ?? 0);
$nomeAbbonamento    = trim($_POST['nome_abbonamento'] ?? '');
$periodicita        = in_array($_POST['periodicita'] ?? '', ['mensile','settimanale'])
                        ? $_POST['periodicita'] : 'mensile';
$scontoAbbonamento  = intval($_POST['sconto_abbonamento'] ?? 20);

// -------------------------------------------------------------------------
// Validazione input
// -------------------------------------------------------------------------
$tipiAmmessi = ['libro', 'fumetto', 'rivista', 'magazine', 'periodico'];
$errori = [];
if (empty($nome))   $errori[] = 'Il nome del prodotto è obbligatorio.';
if ($prezzo <= 0)   $errori[] = 'Il prezzo deve essere maggiore di zero.';
if ($qta < 0)       $errori[] = 'La quantità non può essere negativa.';
if (!in_array($tipoProdotto, $tipiAmmessi)) $errori[] = 'Tipo prodotto non valido.';
if (!empty($errori)) {
    echo json_encode(['status' => 'error', 'msg' => implode(' ', $errori)]);
    exit;
}

$conn->begin_transaction();

try {
    $idPacchetto = null;
    $isPeriodico = in_array($tipoProdotto, ['rivista', 'magazine', 'periodico', 'fumetto']);

    if ($abilitaSconto) {
        if ($isPeriodico) {
            // ===== ABBONAMENTO ANNUALE (mensile=12 uscite, settimanale=52) =====
            if ($idAbbonamentoEsist > 0) {
                // Usa abbonamento esistente del venditore
                $checkOwn = $conn->prepare(
                    "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
                );
                $checkOwn->bind_param("is", $idAbbonamentoEsist, $user);
                $checkOwn->execute();
                if ($checkOwn->get_result()->fetch_assoc()['cnt'] > 0)
                    $idPacchetto = $idAbbonamentoEsist;
                $checkOwn->close();
            } elseif (!empty($nomeAbbonamento)) {
                // Crea nuovo pacchetto abbonamento annuale
                $stmtPack = $conn->prepare(
                    "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, e_saga, periodicita)
                     VALUES (?, NULL, 0, 0, ?, 1, 'abbonamento', 0, ?)"
                );
                $stmtPack->bind_param("sis", $nomeAbbonamento, $scontoAbbonamento, $periodicita);
                $stmtPack->execute();
                $idPacchetto = $conn->insert_id;
                $stmtPack->close();
            }
        } else {
            // ===== PACCHETTO LIBRO =====
            if ($idPacchettoEsist > 0) {
                $checkOwn = $conn->prepare(
                    "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
                );
                $checkOwn->bind_param("is", $idPacchettoEsist, $user);
                $checkOwn->execute();
                if ($checkOwn->get_result()->fetch_assoc()['cnt'] > 0)
                    $idPacchetto = $idPacchettoEsist;
                $checkOwn->close();
            } elseif (!empty($nomePacchetto)) {
                $stmtPack = $conn->prepare(
                    "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto)
                     VALUES (?, ?, ?, ?, ?, 1, 'libro')"
                );
                $stmtPack->bind_param("siiii", $nomePacchetto, $sconto2, $sconto2, $sconto3, $scontoTutti);
                $stmtPack->execute();
                $idPacchetto = $conn->insert_id;
                $stmtPack->close();
                foreach ($libriPacchetto as $idLibroPack) {
                    $idLibroPack = intval($idLibroPack);
                    $stmtUpd = $conn->prepare(
                        "UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?"
                    );
                    $stmtUpd->bind_param("iis", $idPacchetto, $idLibroPack, $user);
                    $stmtUpd->execute(); $stmtUpd->close();
                }
            }
        }
    }

    // Inserimento prodotto
    $stmtP = $conn->prepare(
        "INSERT INTO PRODOTTO (username, nome, autore, descrizione, prezzo, quantita_disponibile, tipo_prodotto, id_pacchetto, testata)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmtP->bind_param("ssssdiiss", $user, $nome, $autore, $desc, $prezzo, $qta, $tipoProdotto, $idPacchetto, $testata);
    if (!$stmtP->execute()) throw new Exception("Errore inserimento prodotto");
    $idProdotto = $conn->insert_id;
    $stmtP->close();

    // Foto
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif']))
            throw new Exception("Formato immagine non supportato.");
        // Nome file: slug del titolo + timestamp per unicità
        $slug     = preg_replace('/[^a-z0-9]+/', '-', strtolower($nome));
        $slug     = trim($slug, '-');
        $slug     = substr($slug, 0, 40);
        $fileName = $slug . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $uploadDir . $fileName)) {
            $stmtI = $conn->prepare("INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
            $urlDb = 'img/' . $fileName;
            $alt   = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute(); $stmtI->close();
        } else {
            throw new Exception("Impossibile salvare l'immagine.");
        }
    }

    // Categoria — solo categorie esistenti nel DB
    $categoriaFinale = !empty($sottocat) ? $sottocat : (!empty($cat) ? $cat : '');
    if (!empty($categoriaFinale)) {
        $checkCat = $conn->prepare("SELECT COUNT(*) as cnt FROM CATEGORIA WHERE nome_categoria = ?");
        $checkCat->bind_param("s", $categoriaFinale);
        $checkCat->execute();
        if (!$checkCat->get_result()->fetch_assoc()['cnt'])
            throw new Exception("Categoria non valida.");
        $checkCat->close();
        $stmtD = $conn->prepare("INSERT INTO DESCRIVE (id_prodotto, nome_categoria) VALUES (?, ?)");
        $stmtD->bind_param("is", $idProdotto, $categoriaFinale);
        $stmtD->execute(); $stmtD->close();
    }

    $conn->commit();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}