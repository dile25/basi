<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipoUtente'] ?? 'cliente';

    if (empty($user) || empty($nome) || empty($cogn) || empty($mail) || empty($_POST['password'])) {
        ob_clean();
        echo json_encode(["status" => "error", "msg" => "Tutti i campi obbligatori devono essere compilati."]);
        exit;
    }

    try {
        $conn->begin_transaction();

        // 1. Inserimento nella tabella UTENTE
        $sqlU = "INSERT INTO UTENTE (username, nome, cognome, email, password_hash, data_registrazione) 
                 VALUES (?, ?, ?, ?, ?, CURRENT_DATE)";
        $stmtU = $conn->prepare($sqlU);
        $stmtU->bind_param("sssss", $user, $nome, $cogn, $mail, $pass);
        $stmtU->execute();

        // 2. Inserimento nelle tabelle specifiche (VENDITORE o CLIENTE)
        if ($tipo === 'venditore') {
            $piva    = $_POST['partita_iva'] ?? '';
            $ragSoc  = $_POST['ragione_sociale'] ?? '';
            $sqlV = "INSERT INTO VENDITORE (username, partita_iva, ragione_sociale) VALUES (?, ?, ?)";
            $stmtV = $conn->prepare($sqlV);
            $stmtV->bind_param("sss", $user, $piva, $ragSoc);
            $stmtV->execute();
        } else {
            $tel = $_POST['telefono'] ?? '';
            $ind = $_POST['indirizzo'] ?? '';
            $sqlC = "INSERT INTO CLIENTE (username, telefono, indirizzo_predefinito) VALUES (?, ?, ?)";
            $stmtC = $conn->prepare($sqlC);
            $stmtC->bind_param("sss", $user, $tel, $ind);
            $stmtC->execute();
        }

        $conn->commit();

        // MODIFICA PRINCIPALE: Avviamo la sessione e logghiamo l'utente automaticamente
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Salviamo le variabili di sessione usando lo username appena creato
        $_SESSION['IdUtente'] = $user;
        $_SESSION['tipoUtente'] = $tipo;

        ob_clean();
        // Restituiamo una risposta di successo indicando al frontend che l'autenticazione è automatica
        echo json_encode(["status" => "ok", "msg" => "Registrazione e login effettuati con successo!"]);

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        ob_clean();
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo json_encode(["status" => "error", "msg" => "Username o Email già utilizzati da un altro utente."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Errore database: " . $e->getMessage()]);
        }
    }
}
?>