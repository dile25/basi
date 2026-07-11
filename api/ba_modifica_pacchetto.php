<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore  = $_SESSION['IdUtente'];
$idPacchetto  = intval($_POST['id_pacchetto'] ?? 0);
$action       = trim($_POST['action'] ?? 'update');

// --- Azioni rapide: aggiungi/rimuovi prodotto dal pacchetto ---
if ($action === 'add_product' || $action === 'remove_product') {
    $idProdotto = intval($_POST['id_prodotto'] ?? 0);
    if (!$idPacchetto || !$idProdotto) {
        echo json_encode(['status' => 'error', 'msg' => 'Dati mancanti']);
        exit;
    }
    // Verifica che il venditore abbia il pacchetto
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?");
    $check->bind_param("is", $idPacchetto, $idVenditore);
    $check->execute();
    if ($check->get_result()->fetch_assoc()['cnt'] == 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']); exit;
    }
    $check->close();
    // Verifica che il prodotto appartenga al venditore
    $checkP = $conn->prepare("SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_prodotto = ? AND username = ?");
    $checkP->bind_param("is", $idProdotto, $idVenditore);
    $checkP->execute();
    if ($checkP->get_result()->fetch_assoc()['cnt'] == 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Prodotto non trovato']); exit;
    }
    $checkP->close();
    if ($action === 'add_product') {
        $stmt = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
        $stmt->bind_param("iis", $idPacchetto, $idProdotto, $idVenditore);
    } else {
        $stmt = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = NULL WHERE id_prodotto = ? AND username = ?");
        $stmt->bind_param("is", $idProdotto, $idVenditore);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['status' => 'ok']);
    exit;
}

$nome         = trim($_POST['nome'] ?? '');
$tipoPacchetto = trim($_POST['tipo_pacchetto'] ?? '');

if (!$idPacchetto || empty($nome)) {
    echo json_encode(['status' => 'error', 'msg' => 'Dati mancanti.']);
    exit;
}

// Verifica che il venditore abbia almeno un prodotto in questo pacchetto
$check = $conn->prepare(
    "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
);
$check->bind_param("is", $idPacchetto, $idVenditore);
$check->execute();
if ($check->get_result()->fetch_assoc()['cnt'] == 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Pacchetto non trovato o non autorizzato.']);
    exit;
}
$check->close();

if ($tipoPacchetto === 'abbonamento') {
    $scontoTutti = intval($_POST['sconto_tutti'] ?? 0);
    $periodicita = trim($_POST['periodicita'] ?? 'mensile');
    if ($scontoTutti < 0 || $scontoTutti > 99) {
        echo json_encode(['status' => 'error', 'msg' => 'Sconto non valido (0-99).']);
        exit;
    }
    if (!in_array($periodicita, ['mensile', 'settimanale'])) {
        echo json_encode(['status' => 'error', 'msg' => 'Periodicità non valida.']);
        exit;
    }
    $stmt = $conn->prepare(
        "UPDATE PACCHETTO SET nome = ?, sconto_tutti = ?, periodicita = ? WHERE id_pacchetto = ?"
    );
    $stmt->bind_param("siis", $nome, $scontoTutti, $periodicita, $idPacchetto);
} else {
    $s2 = intval($_POST['sconto_2'] ?? 0);
    $s3 = intval($_POST['sconto_3'] ?? 0);
    $st = intval($_POST['sconto_tutti'] ?? 0);
    if ($s2 < 0 || $s3 < 0 || $st < 0 || $s2 > 99 || $s3 > 99 || $st > 99) {
        echo json_encode(['status' => 'error', 'msg' => 'Sconti non validi (0-99).']);
        exit;
    }
    $eSaga = intval($_POST['e_saga'] ?? 0);
    $stmt = $conn->prepare(
        "UPDATE PACCHETTO SET nome = ?, sconto_2 = ?, sconto_3 = ?, sconto_tutti = ?, e_saga = ? WHERE id_pacchetto = ?"
    );
    $stmt->bind_param("siiiii", $nome, $s2, $s3, $st, $eSaga, $idPacchetto);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore aggiornamento.']);
}