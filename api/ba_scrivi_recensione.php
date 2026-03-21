<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

// 1. Sicurezza minima: l'utente deve essere loggato
if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Accedi per lasciare una recensione']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$idProdotto = isset($_POST['idProdotto']) ? intval($_POST['idProdotto']) : 0;
$voto = isset($_POST['voto']) ? intval($_POST['voto']) : 0;
$commento = trim($_POST['commento'] ?? '');

// 2. Validazione base dell'input
if ($voto < 1 || $voto > 5) {
    echo json_encode(['status' => 'error', 'msg' => 'Il voto deve essere tra 1 e 5']);
    exit;
}

if (empty($commento)) {
    echo json_encode(['status' => 'error', 'msg' => 'Il commento non può essere vuoto']);
    exit;
}

// 3. Inserimento o Aggiornamento (Senza controllare se ha comprato il libro)
// Usiamo ON DUPLICATE KEY per permettere all'utente di modificare la sua recensione esistente
$sql = "INSERT INTO RECENSIONE (id_prodotto, username, valutazione, testo, data) 
        VALUES (?, ?, ?, ?, CURRENT_DATE) 
        ON DUPLICATE KEY UPDATE valutazione = VALUES(valutazione), testo = VALUES(testo), data = CURRENT_DATE";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isis", $idProdotto, $idUtente, $voto, $commento);

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok', 'msg' => 'Recensione pubblicata!']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore nel salvataggio della recensione']);
}