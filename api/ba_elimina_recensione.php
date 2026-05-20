<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$idRecensione = intval($_POST['id_recensione'] ?? 0);

if (!$idRecensione) {
    echo json_encode(['status' => 'error', 'msg' => 'ID non valido']);
    exit;
}

// Elimina solo se è dell'utente corrente
$stmt = $conn->prepare("DELETE FROM RECENSIONE WHERE id_recensione = ? AND username = ?");
$stmt->bind_param("is", $idRecensione, $idUtente);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Recensione non trovata o non autorizzato']);
}