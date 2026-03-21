<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$id = $_SESSION['IdUtente'];

// Selezioniamo tutti i campi utili dal nuovo schema
$sql = "SELECT username, email, nome, cognome, data_nascita, num_telefono, partita_iva, nome_negozio, tipo_utente 
        FROM UTENTE WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

echo json_encode(['status' => 'ok', 'user' => $user]);