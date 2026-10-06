<?php
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') {
    errore('Inserisci username e password.');
}

$stmt = $conn->prepare(
    "SELECT u.username, u.password_hash, u.attivo, v.username AS venditore
     FROM utente u
     LEFT JOIN venditore v ON v.username = u.username
     WHERE u.username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Stesso messaggio per utente inesistente, password errata o account disattivato:
// così non si rivela quali username esistono.
if (!$utente || !password_verify($password, $utente['password_hash']) || (int)$utente['attivo'] === 0) {
    errore('Username o password non corretti.');
}

session_regenerate_id(true);   // nuovo id di sessione dopo il login
$_SESSION['IdUtente']   = $utente['username'];
$_SESSION['tipoUtente'] = $utente['venditore'] ? 'venditore' : 'cliente';

ok(['tipo' => $_SESSION['tipoUtente']]);
