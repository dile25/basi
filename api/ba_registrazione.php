<?php
require_once('../db_connect.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $cogn = $_POST['cognome'] ?? '';
    $mail = $_POST['email'] ?? '';
    // Hash della password per sicurezza
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    $tipo = $_POST['tipoUtente'] ?? 'cliente'; // 'cliente' o 'venditore'

    $conn->begin_transaction();

    try {
        // 1. Inserimento nella tabella comune UTENTE
        $sqlU = "INSERT INTO UTENTE (username, nome, cognome, email, password_hash, data_registrazione) 
                 VALUES (?, ?, ?, ?, ?, CURRENT_DATE)";
        $stmtU = $conn->prepare($sqlU);
        $stmtU->bind_param("sssss", $user, $nome, $cogn, $mail, $pass);
        $stmtU->execute();

        // 2. Inserimento in base al RUOLO
        if ($tipo === 'venditore') {
            $piva = $_POST['partita_iva'] ?? '';
            $ragSoc = $_POST['ragione_sociale'] ?? '';
            
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
        echo json_encode(["status" => "ok", "msg" => "Registrazione completata!"]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "msg" => "Errore: " . $e->getMessage()]);
    }
}