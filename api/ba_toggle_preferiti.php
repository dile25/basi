<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Accedi come cliente']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$idProdotto = $_POST['idProdotto'] ?? 0;

// 1. Controlliamo se esiste già
$check = $conn->prepare("SELECT id_preferiti FROM PREFERITI WHERE username = ? AND id_prodotto = ?");
$check->bind_param("si", $idUtente, $idProdotto);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Esiste -> Rimuoviamo
    $stmt = $conn->prepare("DELETE FROM PREFERITI WHERE username = ? AND id_prodotto = ?");
    $stmt->bind_param("si", $idUtente, $idProdotto);
    $stmt->execute();
    echo json_encode(['status' => 'ok', 'action' => 'removed']);
} else {
    // Non esiste -> Aggiungiamo
    $stmt = $conn->prepare("INSERT INTO PREFERITI (username, id_prodotto, data_aggiunta) VALUES (?, ?, CURRENT_DATE)");
    $stmt->bind_param("si", $idUtente, $idProdotto);
    $stmt->execute();
    echo json_encode(['status' => 'ok', 'action' => 'added']);
}