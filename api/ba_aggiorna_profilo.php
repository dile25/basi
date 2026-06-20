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

$email          = trim($input['email']          ?? '');
$telefono       = trim($input['telefono']       ?? '');
$indirizzo      = trim($input['indirizzo']      ?? '');
$password       = $input['password']            ?? '';
$nuovoUsername  = trim($input['username']       ?? '');
$ragioneSociale = trim($input['ragione_sociale']?? '');
$partitaIva     = trim($input['partita_iva']    ?? '');

$conn->begin_transaction();

try {
    // Cambio username (se fornito e diverso)
    if (!empty($nuovoUsername) && $nuovoUsername !== $id) {
        if (!preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $nuovoUsername)) {
            throw new Exception('Username non valido (3-30 caratteri: lettere, numeri, _ o -).');
        }
        $check = $conn->prepare("SELECT COUNT(*) as cnt FROM UTENTE WHERE username = ?");
        $check->bind_param("s", $nuovoUsername);
        $check->execute();
        if ($check->get_result()->fetch_assoc()['cnt'] > 0) {
            throw new Exception('Username già in uso.');
        }
        $stmtU = $conn->prepare("UPDATE UTENTE SET username = ? WHERE username = ?");
        $stmtU->bind_param("ss", $nuovoUsername, $id);
        $stmtU->execute();
        $_SESSION['IdUtente'] = $nuovoUsername;
        $id = $nuovoUsername;
    }

    // Aggiorna email e/o password (solo se fornita email)
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
    } elseif (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE UTENTE SET password_hash=? WHERE username=?");
        $stmt->bind_param("ss", $hash, $id);
        $stmt->execute();
    }

    // Aggiorna dati specifici per ruolo
    if ($tipo === 'cliente') {
        $stmt2 = $conn->prepare("UPDATE CLIENTE SET telefono=?, indirizzo_predefinito=? WHERE username=?");
        $stmt2->bind_param("sss", $telefono, $indirizzo, $id);
        $stmt2->execute();
    } elseif ($tipo === 'venditore') {
        if (!empty($ragioneSociale)) {
            $stmt2 = $conn->prepare("UPDATE VENDITORE SET ragione_sociale=?, partita_iva=? WHERE username=?");
            $stmt2->bind_param("sss", $ragioneSociale, $partitaIva, $id);
            $stmt2->execute();
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}