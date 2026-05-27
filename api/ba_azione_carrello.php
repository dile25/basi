<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

// Assicurati che l'ID utente sia presente nella sessione
if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione non valida']);
    exit;
}

$idUtente = $_SESSION['IdUtente']; // Esempio: 'mario_rossi'
$idProdotto = intval($_POST['idProdotto'] ?? 0);
$action = $_POST['action'] ?? '';

// CORREZIONE: Usa i nomi reali delle tue colonne database
// Controlla se la tua tabella si chiama CARRELLO e usa 'username' e 'quantita_prodotto'
if ($action === 'remove') { // Ho cambiato 'delete' in 'remove' per coerenza col frontend
    $stmt = $conn->prepare("DELETE FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
    $stmt->bind_param("si", $idUtente, $idProdotto);
}
elseif ($action === 'update') {
    $qty = intval($_POST['quantita'] ?? 1);
    $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
    $stmt->bind_param("isi", $qty, $idUtente, $idProdotto);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Azione non riconosciuta']);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore nel database']);
}
?>