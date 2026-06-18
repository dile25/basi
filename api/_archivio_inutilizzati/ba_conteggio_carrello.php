<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$idUtente = $_SESSION['IdUtente'] ?? 0;
if ($idUtente == 0) {
    echo json_encode(['status' => 'ok', 'quantita' => 0]);
    exit;
}

// Sommiamo tutte le quantità dei prodotti nel carrello dell'utente
$sql = "SELECT SUM(Quantita) as totale FROM CARRELLO WHERE IdUtente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

$quantita = $row['totale'] ?? 0;

echo json_encode(['status' => 'ok', 'quantita' => (int)$quantita]);