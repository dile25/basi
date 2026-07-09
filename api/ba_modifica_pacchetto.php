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
    if ($scontoTutti < 0 || $scontoTutti > 99) {
        echo json_encode(['status' => 'error', 'msg' => 'Sconto non valido (0-99).']);
        exit;
    }
    $stmt = $conn->prepare(
        "UPDATE PACCHETTO SET nome = ?, sconto_tutti = ? WHERE id_pacchetto = ?"
    );
    $stmt->bind_param("sii", $nome, $scontoTutti, $idPacchetto);
} else {
    $s2 = intval($_POST['sconto_2'] ?? 0);
    $s3 = intval($_POST['sconto_3'] ?? 0);
    $st = intval($_POST['sconto_tutti'] ?? 0);
    if ($s2 < 0 || $s3 < 0 || $st < 0 || $s2 > 99 || $s3 > 99 || $st > 99) {
        echo json_encode(['status' => 'error', 'msg' => 'Sconti non validi (0-99).']);
        exit;
    }
    $stmt = $conn->prepare(
        "UPDATE PACCHETTO SET nome = ?, sconto_2 = ?, sconto_3 = ?, sconto_tutti = ? WHERE id_pacchetto = ?"
    );
    $stmt->bind_param("siiii", $nome, $s2, $s3, $st, $idPacchetto);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore aggiornamento.']);
}