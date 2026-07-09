<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (file_exists(__DIR__ . '/../db_connect.php')) {
    require_once(__DIR__ . '/../db_connect.php');
} else if (file_exists(__DIR__ . '/../basi/db_connect.php')) {
    require_once(__DIR__ . '/../basi/db_connect.php');
} else {
    header('Content-Type: application/json', true, 500);
    echo json_encode(["status" => "error", "msg" => "Impossibile trovare il file db_connect.php."]);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $cogn = $_POST['cognome'] ?? '';
    $mail = $_POST['email'] ?? '';
    $tipo = $_POST['tipoUtente'] ?? 'cliente';

    $errori = [];

    // Username: 3-30 caratteri alfanumerici + _ -
    if (empty($user) || !preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $user))
        $errori[] = "Username non valido (3-30 caratteri: lettere, numeri, _ o -).";

    // Nome: min 2 lettere
    if (empty($nome) || strlen($nome) < 2 || !preg_match('/^[\pL\s\'\-]+$/u', $nome))
        $errori[] = "Nome non valido.";

    // Cognome: min 2 lettere
    if (empty($cogn) || strlen($cogn) < 2 || !preg_match('/^[\pL\s\'\-]+$/u', $cogn))
        $errori[] = "Cognome non valido.";

    // Email
    if (empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL))
        $errori[] = "Email non valida.";

    // Password: min 8 car, 1 maiuscola, 1 numero, 1 simbolo
    $pw = $_POST['password'] ?? '';
    $pwValida = strlen($pw) >= 8
        && preg_match('/[A-Z]/', $pw)
        && preg_match('/[0-9]/', $pw)
        && preg_match('/[^a-zA-Z0-9]/', $pw);
    if (!$pw || !$pwValida)
        $errori[] = "Password non valida (min 8 caratteri, 1 maiuscola, 1 numero, 1 simbolo).";

    // Validazioni per ruolo
    if ($tipo === 'cliente') {
        $tel = trim($_POST['telefono'] ?? '');
        $ind = trim($_POST['indirizzo'] ?? '');
        if (empty($tel) || !preg_match('/^(\+39\s?)?3\d{2}[\s\-]?\d{6,7}$/', $tel))
            $errori[] = "Telefono non valido (es. 3201234567).";
        if (empty($ind) || strlen($ind) < 5)
            $errori[] = "Indirizzo obbligatorio (min 5 caratteri).";
    } else {
        $piva   = trim($_POST['partita_iva'] ?? '');
        $ragSoc = trim($_POST['ragione_sociale'] ?? '');
        if (empty($piva) || !preg_match('/^\d{11}$/', $piva))
            $errori[] = "Partita IVA non valida (11 cifre numeriche).";
        if (empty($ragSoc) || strlen($ragSoc) < 2)
            $errori[] = "Ragione Sociale obbligatoria.";
    }

    if (!empty($errori)) {
        echo json_encode(["status" => "error", "msg" => implode(' ', $errori)]);
        exit;
    }

    $pass = password_hash($pw, PASSWORD_DEFAULT);

    try {
        $conn->begin_transaction();

        $sqlU = "INSERT INTO UTENTE (username, nome, cognome, email, password_hash, data_registrazione)
                 VALUES (?, ?, ?, ?, ?, CURRENT_DATE)";
        $stmtU = $conn->prepare($sqlU);
        $stmtU->bind_param("sssss", $user, $nome, $cogn, $mail, $pass);
        $stmtU->execute();

        if ($tipo === 'venditore') {
            $piva   = $_POST['partita_iva'] ?? '';
            $ragSoc = $_POST['ragione_sociale'] ?? '';
            $stmtV = $conn->prepare("INSERT INTO VENDITORE (username, partita_iva, ragione_sociale) VALUES (?, ?, ?)");
            $stmtV->bind_param("sss", $user, $piva, $ragSoc);
            $stmtV->execute();
        } else {
            $tel = $_POST['telefono'] ?? '';
            $ind = $_POST['indirizzo'] ?? '';
            $stmtC = $conn->prepare("INSERT INTO CLIENTE (username, telefono, indirizzo_predefinito) VALUES (?, ?, ?)");
            $stmtC->bind_param("sss", $user, $tel, $ind);
            $stmtC->execute();
        }

        $conn->commit();

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['IdUtente']  = $user;
        $_SESSION['tipoUtente'] = $tipo;

        echo json_encode(["status" => "ok", "msg" => "Registrazione e login effettuati con successo!"]);

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo json_encode(["status" => "error", "msg" => "Username o Email già utilizzati da un altro utente."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Errore database: " . $e->getMessage()]);
        }
    }
}