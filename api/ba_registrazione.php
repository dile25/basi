<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once($_SERVER['DOCUMENT_ROOT'] . '/basi/db_connect.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $cogn = $_POST['cognome'] ?? '';
    $mail = $_POST['email'] ?? '';
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipoUtente'] ?? 'cliente';

    try {
        $conn->begin_transaction();

        $sqlU = "INSERT INTO UTENTE (username, nome, cognome, email, password_hash, data_registrazione) 
                 VALUES (?, ?, ?, ?, ?, CURRENT_DATE)";
        $stmtU = $conn->prepare($sqlU);
        $stmtU->bind_param("sssss", $user, $nome, $cogn, $mail, $pass);
        $stmtU->execute();

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
        ob_clean();
        echo json_encode(["status" => "ok", "msg" => "Registrazione completata!"]);

    } catch (Exception $e) {
        $conn->rollback();
        ob_clean();
        echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
    }
}
?>