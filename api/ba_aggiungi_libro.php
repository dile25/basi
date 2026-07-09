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

// Testata (per periodici/fumetti)
$testata           = trim($_POST['testata'] ?? '');
$nuovaTestata      = trim($_POST['nuova_testata'] ?? '');
$nuovaTestataPerio = $_POST['nuova_testata_periodicita'] ?? 'mensile';

$abilitaSconto = isset($_POST['abilita_sconto']);

// Pacchetto libro
$idPacchettoEsist = intval($_POST['id_pacchetto_esistente'] ?? 0);
$nomePacchetto    = trim($_POST['nome_pacchetto'] ?? '');
$libriPacchetto   = $_POST['libri_pacchetto'] ?? [];
$sconto2          = intval($_POST['sconto_2'] ?? 10);
$sconto3          = intval($_POST['sconto_3'] ?? 20);
$scontoTutti      = intval($_POST['sconto_tutti'] ?? 30);

// Abbonamento periodico
$idAbbonamentoEsist = intval($_POST['id_abbonamento_esistente'] ?? 0);
$nomeAbbonamento    = trim($_POST['nome_abbonamento'] ?? '');
$periodicita        = $_POST['periodicita'] ?? 'settimanale';
$numeriAbbonamento  = $_POST['numeri_abbonamento'] ?? [];
$scontoAbbonamento  = intval($_POST['sconto_abbonamento'] ?? 25);

// -------------------------------------------------------------------------
// Validazione input
// -------------------------------------------------------------------------
$tipiAmmessi = ['libro', 'fumetto', 'rivista', 'magazine', 'periodico'];

$errori = [];
if (empty($nome))
    $errori[] = 'Il nome del prodotto è obbligatorio.';
if ($prezzo <= 0)
    $errori[] = 'Il prezzo deve essere maggiore di zero.';
if ($qta < 0)
    $errori[] = 'La quantità non può essere negativa.';
if (!in_array($tipoProdotto, $tipiAmmessi))
    $errori[] = 'Tipo prodotto non valido.';
if (!empty($nuovaTestataPerio) && !in_array($nuovaTestataPerio, ['mensile', 'settimanale']))
    $errori[] = 'Periodicità non valida.';
if (!empty($periodicita) && !in_array($periodicita, ['mensile', 'settimanale']))
    $errori[] = 'Periodicità abbonamento non valida.';

if (!empty($errori)) {
    echo json_encode(['status' => 'error', 'msg' => implode(' ', $errori)]);
    exit;
}

$conn->begin_transaction();

try {
    $idPacchetto = null;
    $isPeriodico = in_array($tipoProdotto, ['rivista', 'magazine', 'periodico', 'fumetto']);

    // Gestione testata per prodotti periodici
    if ($isPeriodico && !empty($nuovaTestata)) {
        $checkT = $conn->prepare("SELECT COUNT(*) as cnt FROM PACCHETTO WHERE testata = ? AND tipo_pacchetto = 'abbonamento'");
        $checkT->bind_param("s", $nuovaTestata);
        $checkT->execute();
        $esisteGia = $checkT->get_result()->fetch_assoc()['cnt'] > 0;
        $checkT->close();

        if (!$esisteGia) {
            $nome6 = "Abbonamento {$nuovaTestata} - 6 mesi";
            $stmt6 = $conn->prepare("INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, e_saga, periodicita, testata) VALUES (?, NULL, 0, 0, 15, 1, 'abbonamento', 0, ?, ?)");
            $stmt6->bind_param("sss", $nome6, $nuovaTestataPerio, $nuovaTestata);
            $stmt6->execute(); $stmt6->close();

            $nome12 = "Abbonamento {$nuovaTestata} - 12 mesi";
            $stmt12 = $conn->prepare("INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, e_saga, periodicita, testata) VALUES (?, NULL, 0, 0, 25, 1, 'abbonamento', 0, ?, ?)");
            $stmt12->bind_param("sss", $nome12, $nuovaTestataPerio, $nuovaTestata);
            $stmt12->execute(); $stmt12->close();
        }
        $testata = $nuovaTestata;
    }

    $isAbbonamento = in_array($tipoProdotto, ['rivista', 'magazine', 'periodico']);

    if ($abilitaSconto) {
        if ($isAbbonamento && ($idAbbonamentoEsist > 0 || !empty($nomeAbbonamento))) {
            if ($idAbbonamentoEsist > 0) {
                $checkOwn = $conn->prepare("SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?");
                $checkOwn->bind_param("is", $idAbbonamentoEsist, $user);
                $checkOwn->execute();
                if ($checkOwn->get_result()->fetch_assoc()['cnt'] > 0)
                    $idPacchetto = $idAbbonamentoEsist;
                $checkOwn->close();
            } else {
                $stmtPack = $conn->prepare("INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, periodicita) VALUES (?, ?, 0, 0, ?, 1, 'abbonamento', ?)");
                $stmtPack->bind_param("siis", $nomeAbbonamento, $scontoAbbonamento, $scontoAbbonamento, $periodicita);
                $stmtPack->execute();
                $idPacchetto = $conn->insert_id;
                $stmtPack->close();
                foreach ($numeriAbbonamento as $idNumero) {
                    $idNumero = intval($idNumero);
                    $stmtUpd = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
                    $stmtUpd->bind_param("iis", $idPacchetto, $idNumero, $user);
                    $stmtUpd->execute(); $stmtUpd->close();
                }
            }
        } elseif ($idPacchettoEsist > 0) {
            $checkOwn = $conn->prepare("SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?");
            $checkOwn->bind_param("is", $idPacchettoEsist, $user);
            $checkOwn->execute();
            if ($checkOwn->get_result()->fetch_assoc()['cnt'] > 0)
                $idPacchetto = $idPacchettoEsist;
            $checkOwn->close();
        } elseif (!empty($nomePacchetto)) {
            $stmtPack = $conn->prepare("INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto) VALUES (?, ?, ?, ?, ?, 1, 'libro')");
            $stmtPack->bind_param("siiii", $nomePacchetto, $sconto2, $sconto2, $sconto3, $scontoTutti);
            $stmtPack->execute();
            $idPacchetto = $conn->insert_id;
            $stmtPack->close();
            foreach ($libriPacchetto as $idLibroPack) {
                $idLibroPack = intval($idLibroPack);
                $stmtUpd = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
                $stmtUpd->bind_param("iis", $idPacchetto, $idLibroPack, $user);
                $stmtUpd->execute(); $stmtUpd->close();
            }
        }
    }

    // Inserimento prodotto
    $stmtP = $conn->prepare("INSERT INTO PRODOTTO (username, nome, autore, descrizione, prezzo, quantita_disponibile, tipo_prodotto, id_pacchetto, testata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $testataSave = !empty($testata) ? $testata : null;
    $stmtP->bind_param("ssssdisis", $user, $nome, $autore, $desc, $prezzo, $qta, $tipoProdotto, $idPacchetto, $testataSave);
    if (!$stmtP->execute()) throw new Exception("Errore inserimento prodotto");
    $idProdotto = $conn->insert_id;
    $stmtP->close();

    // Foto
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION));
        $extAmmesse = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $extAmmesse)) throw new Exception("Formato immagine non supportato.");
        $fileName = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $uploadDir . $fileName)) {
            $urlDb = 'img/' . $fileName;
            $stmtI = $conn->prepare("INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
            $alt = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute(); $stmtI->close();
        } else {
            throw new Exception("Impossibile salvare l'immagine.");
        }
    }

    // Categoria — solo categorie esistenti nel DB
    $categoriaFinale = '';
    if (!empty($sottocat)) {
        $categoriaFinale = $sottocat;
    } elseif (!empty($cat)) {
        $categoriaFinale = $cat;
    }

    if (!empty($categoriaFinale)) {
        // Verifica che la categoria esista davvero nel DB
        $checkCat = $conn->prepare("SELECT COUNT(*) as cnt FROM CATEGORIA WHERE nome_categoria = ?");
        $checkCat->bind_param("s", $categoriaFinale);
        $checkCat->execute();
        $esiste = $checkCat->get_result()->fetch_assoc()['cnt'] > 0;
        $checkCat->close();
        if (!$esiste) throw new Exception("Categoria non valida.");

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