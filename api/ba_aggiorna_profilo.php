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

$nuovoUsername = trim($input['username'] ?? '');
$email         = $input['email']     ?? '';
$telefono      = $input['telefono']  ?? '';
$indirizzo     = $input['indirizzo'] ?? '';
$password      = $input['password']  ?? '';

$conn->begin_transaction();

try {
    // --- CAMBIO USERNAME (se diverso dall'attuale) ---
    if (!empty($nuovoUsername) && $nuovoUsername !== $id) {
        // Validazione formato
        if (!preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $nuovoUsername)) {
            throw new Exception('Username non valido: usa solo lettere, numeri, _ o - (3-30 caratteri).');
        }
        // Verifica univocita'
        $check = $conn->prepare("SELECT COUNT(*) as cnt FROM UTENTE WHERE username = ?");
        $check->bind_param("s", $nuovoUsername);
        $check->execute();
        if ($check->get_result()->fetch_assoc()['cnt'] > 0) {
            throw new Exception('Username già in uso, scegline un altro.');
        }
        // Aggiorna UTENTE (CASCADE sulle FK propaga il cambio a tutte le tabelle
        // collegate: CLIENTE/VENDITORE, ORDINE, PRODOTTO, CARRELLO, PREFERITI, ecc.)
        $stmt = $conn->prepare("UPDATE UTENTE SET username = ? WHERE username = ?");
        $stmt->bind_param("ss", $nuovoUsername, $id);
        if (!$stmt->execute()) throw new Exception('Errore aggiornamento username.');

        // Aggiorna la sessione con il nuovo username
        $_SESSION['IdUtente'] = $nuovoUsername;
        $id = $nuovoUsername;
    }

    // --- EMAIL e PASSWORD ---
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

    // --- DATI CLIENTE ---
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

    $conn->commit();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}