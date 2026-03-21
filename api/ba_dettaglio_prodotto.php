<?php
require_once('../db_connect.php');
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

$sql = "SELECT p.*, c.NomeCategoria, v.NomeNeg, v.IdUtente as IdVenditore
        FROM PRODOTTO p
        JOIN CATEGORIA c ON p.IdCategoria = c.IdCategoria
        JOIN UTENTE v ON p.IdVenditore = v.IdUtente
        WHERE p.IdProdotto = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$prodotto = $stmt->get_result()->fetch_assoc();

if ($prodotto) {
    echo json_encode(['status' => 'ok', 'data' => $prodotto]);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto non trovato']);
}