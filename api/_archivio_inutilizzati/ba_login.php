<?php
session_start();
include '../db_connect.php'; // Assicurati che questo file punti a ecommerce_libri

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Prepariamo la query sulla nuova tabella UTENTE
    // Cerchiamo lo username e capiamo se è un CLIENTE o un VENDITORE
    $sql = "SELECT u.username, u.password_hash, u.nome,
            (SELECT 'cliente' FROM CLIENTE c WHERE c.username = u.username) as is_cliente,
            (SELECT 'venditore' FROM VENDITORE v WHERE v.username = u.username) as is_venditore
            FROM UTENTE u
            WHERE u.username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verifica password (assumendo che userai password_hash in futuro,
        // per ora facciamo un controllo semplice se non sono ancora criptate)
        if ($pass === $row['password_hash']) { // Sostituire con password_verify($pass, $row['password_hash']) se criptate

            // Settiamo le sessioni usando lo username come identificativo
            $_SESSION['username'] = $row['username'];
            $_SESSION['nome'] = $row['nome'];
            $_SESSION['tipoUtente'] = $row['is_cliente'] ? 'cliente' : 'venditore';

            echo json_encode(["status" => "ok"]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Password errata"]);
        }
    } else {
        echo json_encode(["status" => "error", "msg" => "Utente non trovato"]);
    }
}
?>