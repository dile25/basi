<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];
$idPacchetto = intval($_POST['id_pacchetto'] ?? 0);

if (!$idPacchetto) {
    echo json_encode(['status' => 'error', 'msg' => 'ID non valido.']);
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

// Dissocia i prodotti dal pacchetto (non elimina i prodotti)
$stmtDiss = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = NULL WHERE id_pacchetto = ? AND username = ?");
$stmtDiss->bind_param("is", $idPacchetto, $idVenditore);
$stmtDiss->execute();
$stmtDiss->close();

// Elimina il pacchetto solo se non ha più prodotti associati (altri venditori potrebbero averli)
$stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ?");
$stmtCheck->bind_param("i", $idPacchetto);
$stmtCheck->execute();
$rimanenti = $stmtCheck->get_result()->fetch_assoc()['cnt'];
$stmtCheck->close();

if ($rimanenti == 0) {
    $stmtDel = $conn->prepare("DELETE FROM PACCHETTO WHERE id_pacchetto = ?");
    $stmtDel->bind_param("i", $idPacchetto);
    $stmtDel->execute();
    $stmtDel->close();
}

echo json_encode(['status' => 'ok']);