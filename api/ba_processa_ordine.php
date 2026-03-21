<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

// Sicurezza: Solo clienti loggati
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione non valida']);
    exit;
}

$idCliente = $_SESSION['IdUtente'];

// 1. Iniziamo la transazione SQL
$conn->begin_transaction();

try {
    // 2. Calcoliamo il totale considerando gli sconti dei pacchetti attivi
    // Usiamo COALESCE per gestire i casi in cui non c'è uno sconto (sconto = 0)
    $sqlTotale = "SELECT SUM(
                    (p.prezzo - (p.prezzo * COALESCE(pk.sconto, 0) / 100)) * c.quantita_prodotto
                  ) as Totale Calcolato
                  FROM CARRELLO c 
                  JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
                  LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
                  WHERE c.username = ?";
    
    $stmtTot = $conn->prepare($sqlTotale);
    $stmtTot->bind_param("s", $idCliente);
    $stmtTot->execute();
    $resTot = $stmtTot->get_result();
    $rowTot = $resTot->fetch_assoc();
    $totaleFinale = $rowTot['Totale Calcolato'] ?? 0;

    if ($totaleFinale <= 0) {
        throw new Exception("Il carrello è vuoto o errore nel calcolo.");
    }

    // 3. Creiamo l'ordine nella tabella ORDINE
    // Nota: id_pagamento può essere NULL inizialmente o gestito con una query precedente
    $stmtOrd = $conn->prepare("INSERT INTO ORDINE (username, data, stato, totale) VALUES (?, CURRENT_DATE, 'In elaborazione', ?)");
    $stmtOrd->bind_param("sd", $idCliente, $totaleFinale);
    $stmtOrd->execute();
    $idOrdine = $conn->insert_id;

    // 4. Spostiamo i prodotti dal CARRELLO alla tabella ponte INCLUSO_IN
    $sqlSposta = "INSERT INTO INCLUSO_IN (id_ordine, id_prodotto, quantita)
                  SELECT ?, id_prodotto, quantita_prodotto
                  FROM CARRELLO 
                  WHERE username = ?";
    $stmtSposta = $conn->prepare($sqlSposta);
    $stmtSposta->bind_param("is", $idOrdine, $idCliente);
    $stmtSposta->execute();

    // 5. Aggiorniamo le scorte in PRODOTTO (quantita_disponibile)
    $sqlScorte = "UPDATE PRODOTTO p
                  JOIN CARRELLO c ON p.id_prodotto = c.id_prodotto
                  SET p.quantita_disponibile = p.quantita_disponibile - c.quantita_prodotto
                  WHERE c.username = ?";
    $stmtScorte = $conn->prepare($sqlScorte);
    $stmtScorte->bind_param("s", $idCliente);
    $stmtScorte->execute();

    // 6. Svuotiamo il carrello dell'utente
    $stmtDel = $conn->prepare("DELETE FROM CARRELLO WHERE username = ?");
    $stmtDel->bind_param("s", $idCliente);
    $stmtDel->execute();

    // Se arriviamo qui senza errori, salviamo tutto nel DB
    $conn->commit();
    echo json_encode(['status' => 'ok', 'idOrdine' => $idOrdine]);

} catch (Exception $e) {
    // In caso di errore (es. magazzino insufficiente o DB offline), annulliamo tutto
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}