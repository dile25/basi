<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$id = $_SESSION['IdUtente'];
$tipo = $_SESSION['tipoUtente'];

// Dati base utente
$stmt = $conn->prepare("SELECT username, email, nome, cognome FROM UTENTE WHERE username = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();

// Dati specifici per ruolo
$dettagli = [];
if ($tipo === 'venditore') {
    $stmt2 = $conn->prepare("SELECT partita_iva, ragione_sociale FROM VENDITORE WHERE username = ?");
    $stmt2->bind_param("s", $id);
    $stmt2->execute();
    $dettagli = $stmt2->get_result()->fetch_assoc();
} else {
    $stmt2 = $conn->prepare("SELECT telefono, indirizzo_predefinito FROM CLIENTE WHERE username = ?");
    $stmt2->bind_param("s", $id);
    $stmt2->execute();
    $dettagli = $stmt2->get_result()->fetch_assoc();
}

echo json_encode([
    'status' => 'ok',
    'tipo' => $tipo,
    'anagrafica' => $utente,
    'dettagli' => $dettagli ?? []
]);