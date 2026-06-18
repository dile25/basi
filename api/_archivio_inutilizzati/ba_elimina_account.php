<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];

// Iniziamo la transazione per eliminare tutto ciò che riguarda l'utente
$conn->begin_transaction();

try {
    // 1. Elimina recensioni
    $conn->query("DELETE FROM RECENSIONE WHERE IdCliente = '$idUtente'");
    // 2. Elimina carrello
    $conn->query("DELETE FROM CARRELLO WHERE IdCliente = '$idUtente'");
    // 3. Elimina metodi di pagamento
    $conn->query("DELETE FROM METODO_PAGAMENTO WHERE IdUtente = '$idUtente'");
    // 4. Elimina l'utente stesso
    $conn->query("DELETE FROM UTENTE WHERE IdUtente = '$idUtente'");

    $conn->commit();
    session_destroy(); // Distruggi la sessione dopo l'eliminazione
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => 'Errore durante l\'eliminazione.']);
}