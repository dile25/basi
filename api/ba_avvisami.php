<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Devi essere loggato come cliente per usare questa funzione.']);
    exit;
}

$username   = $_SESSION['IdUtente'];
$idProdotto = intval($_POST['id_prodotto'] ?? 0);

if (!$idProdotto) {
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto non valido.']);
    exit;
}

// Controlla se e' gia' iscritto alla notifica per questo prodotto
$check = $conn->prepare("SELECT COUNT(*) as cnt FROM AVVISAMI WHERE username = ? AND id_prodotto = ?");
$check->bind_param("si", $username, $idProdotto);
$check->execute();
if ($check->get_result()->fetch_assoc()['cnt'] > 0) {
    echo json_encode(['status' => 'already']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO AVVISAMI (username, id_prodotto) VALUES (?, ?)");
$stmt->bind_param("si", $username, $idProdotto);

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore durante la registrazione.']);
}