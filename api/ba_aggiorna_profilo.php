<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$id   = $_SESSION['IdUtente'];
$tipo = $_SESSION['tipoUtente'];

// Supporta sia JSON (da profilo.php) che POST normale (da checkout.php)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$email     = $input['email']     ?? '';
$telefono  = $input['telefono']  ?? '';
$indirizzo = $input['indirizzo'] ?? '';
$password  = $input['password']  ?? '';

try {
    // Aggiorna UTENTE
    if (!empty($email)) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE UTENTE SET email=?, password_hash=? WHERE username=?");
            $stmt->bind_param("sss", $email, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE UTENTE SET email=? WHERE username=?");
            $stmt->bind_param("ss", $email, $id);
        }
        $stmt->execute();
    }

    // Aggiorna CLIENTE
    if ($tipo === 'cliente') {
        if (!empty($telefono) && !empty($indirizzo)) {
            $stmt2 = $conn->prepare("UPDATE CLIENTE SET telefono=?, indirizzo_predefinito=? WHERE username=?");
            $stmt2->bind_param("sss", $telefono, $indirizzo, $id);
        } elseif (!empty($indirizzo)) {
            $stmt2 = $conn->prepare("UPDATE CLIENTE SET indirizzo_predefinito=? WHERE username=?");
            $stmt2->bind_param("ss", $indirizzo, $id);
        } elseif (!empty($telefono)) {
            $stmt2 = $conn->prepare("UPDATE CLIENTE SET telefono=? WHERE username=?");
            $stmt2->bind_param("ss", $telefono, $id);
        }
        if (isset($stmt2)) $stmt2->execute();
    }

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}