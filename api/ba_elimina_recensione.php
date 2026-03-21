<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

$idUtente = $_SESSION['IdUtente'];
$idProdotto = $_POST['idProdotto'];

$stmt = $conn->prepare("DELETE FROM RECENSIONE WHERE IdProdotto = ? AND IdCliente = ?");
$stmt->bind_param("ss", $idProdotto, $idUtente);

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'msg' => $conn->error]);
}