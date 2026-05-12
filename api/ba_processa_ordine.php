<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione non valida']);
    exit;
}

$idCliente = $_SESSION['IdUtente'];
$indirizzo = $_POST['indirizzo'] ?? '';
$metodo    = $_POST['metodo'] ?? 'Carta';

$conn->begin_transaction();

try {
    // 1. Calcola totale
    $sqlTotale = "SELECT SUM(p.prezzo * c.quantita_prodotto) as totale_calcolato
                  FROM CARRELLO c
                  JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
                  WHERE c.username = ?";
    $stmtTot = $conn->prepare($sqlTotale);
    $stmtTot->bind_param("s", $idCliente);
    $stmtTot->execute();
    $totaleFinale = $stmtTot->get_result()->fetch_assoc()['totale_calcolato'] ?? 0;

    if ($totaleFinale <= 0) throw new Exception("Il carrello è vuoto.");

    // 2. Registra il pagamento simulato
    $stmtPag = $conn->prepare("INSERT INTO PAGAMENTO (username, metodo, stato) VALUES (?, ?, 'Completato')");
    $stmtPag->bind_param("ss", $idCliente, $metodo);
    $stmtPag->execute();
    $idPagamento = $conn->insert_id;

    // 3. Crea ordine con stato Pagato e indirizzo di spedizione
    $stmtOrd = $conn->prepare("INSERT INTO ORDINE (username, data, stato, totale, id_pagamento) VALUES (?, CURRENT_DATE, 'Pagato', ?, ?)");
    $stmtOrd->bind_param("sdi", $idCliente, $totaleFinale, $idPagamento);
    $stmtOrd->execute();
    $idOrdine = $conn->insert_id;

    // 4. Sposta prodotti in INCLUSO_IN
    $sqlSposta = "INSERT INTO INCLUSO_IN (id_ordine, id_prodotto, quantita_prodotto)
                  SELECT ?, id_prodotto, quantita_prodotto
                  FROM CARRELLO WHERE username = ?";
    $stmtSposta = $conn->prepare($sqlSposta);
    $stmtSposta->bind_param("is", $idOrdine, $idCliente);
    $stmtSposta->execute();

    // 5. Aggiorna scorte
    $sqlScorte = "UPDATE PRODOTTO p
                  JOIN CARRELLO c ON p.id_prodotto = c.id_prodotto
                  SET p.quantita_disponibile = p.quantita_disponibile - c.quantita_prodotto
                  WHERE c.username = ?";
    $stmtScorte = $conn->prepare($sqlScorte);
    $stmtScorte->bind_param("s", $idCliente);
    $stmtScorte->execute();

    // 6. Svuota carrello
    $conn->prepare("DELETE FROM CARRELLO WHERE username = ?")->bind_param("s", $idCliente);
    $stmtDel = $conn->prepare("DELETE FROM CARRELLO WHERE username = ?");
    $stmtDel->bind_param("s", $idCliente);
    $stmtDel->execute();

    $conn->commit();
    echo json_encode(['status' => 'ok', 'idOrdine' => $idOrdine]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}