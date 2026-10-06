<?php
/*
 * Aggiorna il profilo. Il corpo è JSON e contiene SOLO i campi da modificare:
 * dal profilo arrivano dati personali, indirizzo o dati aziendali separatamente,
 * quindi si aggiornano solo le chiavi presenti (prima un campo assente veniva svuotato).
 */
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');
$username = richiediLogin();
$tipo     = $_SESSION['tipoUtente'];
$input    = leggiJson();

$campiUtente = [];   // colonna => valore per la tabella UTENTE
$campiRuolo  = [];   // colonna => valore per CLIENTE o VENDITORE

// ===== Validazione dei soli campi presenti =====
if (array_key_exists('username', $input)) {
    $nuovo = trim((string)$input['username']);
    if (!preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $nuovo)) {
        errore('Username non valido: da 3 a 30 caratteri, solo lettere, numeri, _ o -.');
    }
    if ($nuovo !== $username) {
        $check = $conn->prepare("SELECT 1 FROM utente WHERE username = ?");
        $check->bind_param("s", $nuovo);
        $check->execute();
        if ($check->get_result()->num_rows > 0) errore('Username già in uso.');
        $check->close();
        $campiUtente['username'] = $nuovo;
    }
}

if (array_key_exists('email', $input)) {
    $email = trim((string)$input['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        errore('Email non valida.');
    }
    $check = $conn->prepare("SELECT 1 FROM utente WHERE email = ? AND username <> ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) errore('Email già usata da un altro account.');
    $check->close();
    $campiUtente['email'] = $email;
}

if (!empty($input['password'])) {
    $password = (string)$input['password'];
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}$/', $password)) {
        errore('Password non valida: almeno 8 caratteri, con una maiuscola, un numero e un simbolo.');
    }
    $campiUtente['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
}

if ($tipo === 'cliente') {
    if (array_key_exists('telefono', $input)) {
        $telefono = preg_replace('/[\s\-]/', '', (string)$input['telefono']);
        if (!preg_match('/^(\+39)?\d{6,11}$/', $telefono)) errore('Numero di telefono non valido.');
        $campiRuolo['telefono'] = $telefono;
    }
    if (array_key_exists('indirizzo', $input)) {
        $campiRuolo['indirizzo_predefinito'] = testo($input, 'indirizzo', 5, 255, 'Indirizzo');
    }
} else {
    if (array_key_exists('ragione_sociale', $input)) {
        $campiRuolo['ragione_sociale'] = testo($input, 'ragione_sociale', 2, 100, 'Ragione sociale');
    }
    if (array_key_exists('partita_iva', $input)) {
        $piva = trim((string)$input['partita_iva']);
        if (!preg_match('/^\d{11}$/', $piva)) errore('La partita IVA è composta da 11 cifre.');
        $campiRuolo['partita_iva'] = $piva;
    }
}

if (!$campiUtente && !$campiRuolo) {
    ok();   // niente da modificare
}

/* Esegue UPDATE tabella SET col1 = ?, col2 = ? WHERE username = ?
   I nomi di colonna vengono solo dalle chiavi scritte sopra, mai dall'input. */
function aggiorna(mysqli $conn, string $tabella, array $campi, string $username): void {
    $set    = implode(', ', array_map(fn($c) => "$c = ?", array_keys($campi)));
    $valori = array_values($campi);
    $valori[] = $username;
    $stmt = $conn->prepare("UPDATE $tabella SET $set WHERE username = ?");
    $stmt->bind_param(str_repeat('s', count($valori)), ...$valori);
    $stmt->execute();
    $stmt->close();
}

$conn->begin_transaction();
try {
    // Prima i dati di ruolo con lo username attuale; poi UTENTE (il cambio username
    // si propaga alle altre tabelle grazie a ON UPDATE CASCADE).
    if ($campiRuolo) {
        aggiorna($conn, $tipo === 'cliente' ? 'cliente' : 'venditore', $campiRuolo, $username);
    }
    if ($campiUtente) {
        aggiorna($conn, 'utente', $campiUtente, $username);
    }
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    throw $e;
}

if (isset($campiUtente['username'])) {
    $_SESSION['IdUtente'] = $campiUtente['username'];
}
ok();
