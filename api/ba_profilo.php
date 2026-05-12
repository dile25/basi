<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione non valida']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$action = $_GET['action'] ?? 'get';

// --- AZIONE 1: RECUPERO DATI PER IL FORM ---
if ($action === 'get') {
    $sql = "SELECT Nome, Cognome, Email, Indirizzo, Telefono, NomeNeg FROM UTENTE WHERE IdUtente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idUtente);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // Restituiamo i dati così come sono (o CASE_LOWER se preferisci nel JS)
        echo json_encode(['status' => 'ok', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Utente non trovato']);
    }

// --- AZIONE 2: AGGIORNAMENTO DATI ---
} elseif ($action === 'update') {
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $email = $_POST['email'] ?? '';
    $indirizzo = $_POST['indirizzo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $newPass = $_POST['password'] ?? ''; // Opzionale

    // Se l'utente vuole cambiare anche la password
    if (!empty($newPass)) {
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $sql = "UPDATE UTENTE SET Nome=?, Cognome=?, Email=?, Indirizzo=?, Telefono=?, Password=? WHERE IdUtente=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nome, $cognome, $email, $indirizzo, $telefono, $hash, $idUtente);
    } else {
        // Aggiornamento standard senza toccare la password
        $sql = "UPDATE UTENTE SET Nome=?, Cognome=?, Email=?, Indirizzo=?, Telefono=? WHERE IdUtente=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nome, $cognome, $email, $indirizzo, $telefono, $idUtente);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'msg' => 'Profilo aggiornato correttamente!']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Errore nell\'aggiornamento dei dati']);
    }
}

