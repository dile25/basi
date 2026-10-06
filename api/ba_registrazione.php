<?php
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');

if (isset($_SESSION['IdUtente'])) {
    errore('Hai già effettuato l\'accesso.');
}

$tipo = $_POST['tipoUtente'] ?? '';
if (!in_array($tipo, ['cliente', 'venditore'], true)) {
    errore('Tipo di account non valido.');
}

// ===== Validazione (la stessa del frontend, ripetuta lato server) =====
$username = trim($_POST['username'] ?? '');
if (!preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $username)) {
    errore('Username non valido: da 3 a 30 caratteri, solo lettere, numeri, _ o -.');
}
$nome    = testo($_POST, 'nome', 2, 50, 'Nome');
$cognome = testo($_POST, 'cognome', 2, 50, 'Cognome');
if (!preg_match("/^[\p{L}\s'\-]+$/u", $nome) || !preg_match("/^[\p{L}\s'\-]+$/u", $cognome)) {
    errore('Nome e cognome possono contenere solo lettere, spazi, apostrofi e trattini.');
}
$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
    errore('Email non valida.');
}
$password = $_POST['password'] ?? '';
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}$/', $password)) {
    errore('Password non valida: almeno 8 caratteri, con una maiuscola, un numero e un simbolo.');
}

if ($tipo === 'cliente') {
    $telefono = preg_replace('/[\s\-]/', '', $_POST['telefono'] ?? '');
    if (!preg_match('/^(\+39)?\d{6,11}$/', $telefono)) {
        errore('Numero di telefono non valido.');
    }
    $indirizzo = testo($_POST, 'indirizzo', 5, 255, 'Indirizzo');
} else {
    $ragioneSociale = testo($_POST, 'ragione_sociale', 2, 100, 'Ragione sociale');
    $partitaIva = trim($_POST['partita_iva'] ?? '');
    if (!preg_match('/^\d{11}$/', $partitaIva)) {
        errore('La partita IVA è composta da 11 cifre.');
    }
}

// ===== Username ed email devono essere liberi =====
$check = $conn->prepare("SELECT username, email FROM utente WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();
if ($esistente = $check->get_result()->fetch_assoc()) {
    errore(strcasecmp($esistente['username'], $username) === 0 ? 'Username già in uso.' : 'Email già registrata.');
}
$check->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

// ===== Inserimento in transazione: UTENTE + CLIENTE oppure VENDITORE =====
$conn->begin_transaction();
try {
    $stmt = $conn->prepare(
        "INSERT INTO utente (username, nome, cognome, email, password_hash, data_registrazione)
         VALUES (?, ?, ?, ?, ?, CURRENT_DATE)"
    );
    $stmt->bind_param("sssss", $username, $nome, $cognome, $email, $hash);
    $stmt->execute();
    $stmt->close();

    if ($tipo === 'cliente') {
        $stmt = $conn->prepare("INSERT INTO cliente (username, telefono, indirizzo_predefinito) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $telefono, $indirizzo);
    } else {
        $stmt = $conn->prepare("INSERT INTO venditore (username, partita_iva, ragione_sociale) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $partitaIva, $ragioneSociale);
    }
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    throw $e;
}

session_regenerate_id(true);
$_SESSION['IdUtente']   = $username;
$_SESSION['tipoUtente'] = $tipo;

ok();
