<?php
ob_start();
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Accesso non autorizzato']);
    exit;
}

$user          = $_SESSION['IdUtente'];
$nome          = $_POST['nome'] ?? '';
$autore        = $_POST['autore'] ?? '';
$desc          = $_POST['descrizione'] ?? '';
$prezzo        = floatval($_POST['prezzo'] ?? 0);
$qta           = intval($_POST['quantita'] ?? 0);
$cat           = $_POST['categoria'] ?? '';
$sottocat      = $_POST['sottocategoria'] ?? '';
$nuovaCat      = trim($_POST['nuova_categoria'] ?? '');
$tipoProdotto  = $_POST['tipo_prodotto'] ?? 'libro';

$abilitaSconto = isset($_POST['abilita_sconto']);

// --- Pacchetto libro (saga / autore / promo) ---
$idPacchettoEsist   = intval($_POST['id_pacchetto_esistente'] ?? 0);
$nomePacchetto      = trim($_POST['nome_pacchetto'] ?? '');
$libriPacchetto     = $_POST['libri_pacchetto'] ?? [];
$sconto2            = intval($_POST['sconto_2'] ?? 10);
$sconto3            = intval($_POST['sconto_3'] ?? 20);
$eSaga              = isset($_POST['e_saga']);
$scontoTutti        = $eSaga ? intval($_POST['sconto_tutti'] ?? 30) : 0;

// --- Abbonamento periodico ---
$idAbbonamentoEsist = intval($_POST['id_abbonamento_esistente'] ?? 0);
$nomeAbbonamento    = trim($_POST['nome_abbonamento'] ?? '');
$periodicita        = $_POST['periodicita'] ?? 'settimanale';
$numeriAbbonamento  = $_POST['numeri_abbonamento'] ?? [];
$scontoAbbonamento  = intval($_POST['sconto_abbonamento'] ?? 25);

$conn->begin_transaction();

try {
    $idPacchetto = null;
    $isAbbonamento = in_array($tipoProdotto, ['rivista', 'magazine', 'periodico']);

    if ($abilitaSconto) {
        if ($isAbbonamento && ($idAbbonamentoEsist > 0 || !empty($nomeAbbonamento))) {
            // ===== ABBONAMENTO PERIODICO =====
            if ($idAbbonamentoEsist > 0) {
                $checkOwn = $conn->prepare(
                    "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
                );
                $checkOwn->bind_param("is", $idAbbonamentoEsist, $user);
                $checkOwn->execute();
                $owns = $checkOwn->get_result()->fetch_assoc()['cnt'];
                if ($owns > 0) {
                    $idPacchetto = $idAbbonamentoEsist;
                }
            } else {
                // Crea nuovo pacchetto abbonamento: sconto_tutti = sconto_abbonamento,
                // sconto_2/sconto_3 a 0 perche' non si applicano scaglioni intermedi
                $stmtPack = $conn->prepare(
                    "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, periodicita)
                     VALUES (?, ?, 0, 0, ?, 1, 'abbonamento', ?)"
                );
                $stmtPack->bind_param("siis", $nomeAbbonamento, $scontoAbbonamento, $scontoAbbonamento, $periodicita);
                $stmtPack->execute();
                $idPacchetto = $conn->insert_id;

                foreach ($numeriAbbonamento as $idNumero) {
                    $idNumero = intval($idNumero);
                    $stmtUpd = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
                    $stmtUpd->bind_param("iis", $idPacchetto, $idNumero, $user);
                    $stmtUpd->execute();
                }
            }
        } elseif ($idPacchettoEsist > 0) {
            // ===== PACCHETTO LIBRO ESISTENTE =====
            $checkOwn = $conn->prepare(
                "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
            );
            $checkOwn->bind_param("is", $idPacchettoEsist, $user);
            $checkOwn->execute();
            $owns = $checkOwn->get_result()->fetch_assoc()['cnt'];
            if ($owns > 0) {
                $idPacchetto = $idPacchettoEsist;
            }
        } elseif (!empty($nomePacchetto)) {
            // ===== NUOVO PACCHETTO LIBRO con scaglioni =====
            $stmtPack = $conn->prepare(
                "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, e_saga)
                 VALUES (?, ?, ?, ?, ?, 1, 'libro', ?)"
            );
            $eSagaInt = $eSaga ? 1 : 0;
            $stmtPack->bind_param("siiiii", $nomePacchetto, $sconto2, $sconto2, $sconto3, $scontoTutti, $eSagaInt);
            $stmtPack->execute();
            $idPacchetto = $conn->insert_id;

            foreach ($libriPacchetto as $idLibroPack) {
                $idLibroPack = intval($idLibroPack);
                $stmtUpd = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
                $stmtUpd->bind_param("iis", $idPacchetto, $idLibroPack, $user);
                $stmtUpd->execute();
            }
        }
    }

    // Inserimento prodotto
    $sqlP = "INSERT INTO PRODOTTO (username, nome, autore, descrizione, prezzo, quantita_disponibile, tipo_prodotto, id_pacchetto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtP = $conn->prepare($sqlP);
    $stmtP->bind_param("ssssdisi", $user, $nome, $autore, $desc, $prezzo, $qta, $tipoProdotto, $idPacchetto);
    if (!$stmtP->execute()) throw new Exception("Errore inserimento prodotto");
    $idProdotto = $conn->insert_id;

    // Foto
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION);
        $fileName = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $uploadDir . $fileName)) {
            $urlDb = 'img/' . $fileName;
            $stmtI = $conn->prepare("INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
            $alt = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute();
        } else {
            throw new Exception("Impossibile salvare l'immagine");
        }
    }

    // Categoria
    $categoriaFinale = '';
    if (!empty($nuovaCat)) {
        $checkCat = $conn->prepare("SELECT nome_categoria FROM CATEGORIA WHERE nome_categoria = ?");
        $checkCat->bind_param("s", $nuovaCat);
        $checkCat->execute();
        if ($checkCat->get_result()->num_rows === 0) {
            $stmtCat = $conn->prepare("INSERT INTO CATEGORIA (nome_categoria, nome_categoria_padre) VALUES (?, ?)");
            $padre = !empty($cat) ? $cat : null;
            $stmtCat->bind_param("ss", $nuovaCat, $padre);
            $stmtCat->execute();
        }
        $categoriaFinale = $nuovaCat;
    } elseif (!empty($sottocat)) {
        $categoriaFinale = $sottocat;
    } elseif (!empty($cat)) {
        $categoriaFinale = $cat;
    }

    if (!empty($categoriaFinale)) {
        $stmtD = $conn->prepare("INSERT INTO DESCRIVE (id_prodotto, nome_categoria) VALUES (?, ?)");
        $stmtD->bind_param("is", $idProdotto, $categoriaFinale);
        $stmtD->execute();
    }

    $conn->commit();
    ob_clean();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}