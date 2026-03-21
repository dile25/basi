<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';

// 1. Cerchiamo l'utente (colonne aggiornate al tuo nuovo SQL)
$stmt = $conn->prepare("SELECT username, password_hash FROM UTENTE WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$res = $stmt->get_result();

if($row = $res->fetch_assoc()) {
    // 2. Verifica password
    if(password_verify($pass, $row['password_hash'])) {
        
        // 3. Controllo RUOLO (Se è in VENDITORE è un venditore, altrimenti assumiamo cliente)
        $tipoUtente = 'cliente';
        $checkV = $conn->prepare("SELECT username FROM VENDITORE WHERE username = ?");
        $checkV->bind_param("s", $user);
        $checkV->execute();
        if($checkV->get_result()->num_rows > 0) {
            $tipoUtente = 'venditore';
        }

        $_SESSION['IdUtente'] = $row['username'];
        $_SESSION['tipoUtente'] = $tipoUtente;

        echo json_encode(['status' => 'ok', 'tipo' => $tipoUtente]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Password errata']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Utente non trovato']);
}