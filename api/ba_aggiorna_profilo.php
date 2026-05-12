<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id    = $_SESSION['IdUtente'];
$tipo  = $_SESSION['tipoUtente'];

$email    = $input['email']    ?? '';
$telefono = $input['telefono'] ?? '';
$indirizzo= $input['indirizzo']?? '';
$password = $input['password'] ?? '';

try {
    // Aggiorna email in UTENTE
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE UTENTE SET email=?, password_hash=? WHERE username=?");
        $stmt->bind_param("sss", $email, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE UTENTE SET email=? WHERE username=?");
        $stmt->bind_param("ss", $email, $id);
    }
    $stmt->execute();

    // Aggiorna dati specifici per ruolo
    if ($tipo === 'cliente') {
        $stmt2 = $conn->prepare("UPDATE CLIENTE SET telefono=?, indirizzo_predefinito=? WHERE username=?");
        $stmt2->bind_param("sss", $telefono, $indirizzo, $id);
        $stmt2->execute();
    }

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}