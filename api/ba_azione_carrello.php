<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$idUtente = $_SESSION['IdUtente'];
$idProdotto = $_POST['idProdotto'];
$action = $_POST['action'];

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM CARRELLO WHERE IdCliente = ? AND IdProdotto = ?");
    $stmt->bind_param("si", $idUtente, $idProdotto);
}
elseif ($action === 'update') {
    $qty = $_POST['quantita'];
    $stmt = $conn->prepare("UPDATE CARRELLO SET Quantita = ? WHERE IdCliente = ? AND IdProdotto = ?");
    $stmt->bind_param("isi", $qty, $idUtente, $idProdotto);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error']);
}