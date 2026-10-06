<?php
/*
 * Conferma dell'ordine (pagamento simulato), tutto in una TRANSAZIONE:
 *   1. blocca le righe dei prodotti nel carrello (SELECT ... FOR UPDATE)
 *   2. ricalcola prezzi e sconti lato server (il client invia solo indirizzo e metodo)
 *   3. controlla la disponibilità
 *   4. registra PAGAMENTO, ORDINE e righe INCLUSO_IN
 *   5. scala le quantità e svuota il carrello
 * Se un passo fallisce, rollback: nessuna modifica parziale.
 */
require_once __DIR__ . '/comune.php';
require_once __DIR__ . '/funzioni_carrello.php';
richiediMetodo('POST');
$username = richiediLogin('cliente');

$metodo = $_POST['metodo'] ?? '';
if (!in_array($metodo, ['Carta', 'PayPal'], true)) {
    errore('Metodo di pagamento non valido.');
}
$indirizzo = testo($_POST, 'indirizzo', 5, 255, 'Indirizzo di spedizione');

$conn->begin_transaction();
try {
    // 1. Lock dei prodotti: un altro ordine contemporaneo aspetta la fine di questo
    $stmt = $conn->prepare(
        "SELECT p.id_prodotto FROM prodotto p
         JOIN carrello c ON c.id_prodotto = p.id_prodotto
         WHERE c.username = ? FOR UPDATE"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    // 2. Prezzi e sconti calcolati dal server
    $carrello = calcolaCarrello($conn, $username);
    if (empty($carrello['prodotti'])) {
        throw new ErroreUtente('Il carrello è vuoto.');
    }

    // 3. Disponibilità
    foreach ($carrello['prodotti'] as $r) {
        if ($r['quantita'] > $r['quantitaDisponibile']) {
            throw new ErroreUtente('"' . $r['nome'] . '": sono disponibili solo ' . $r['quantitaDisponibile'] . ' copie. Aggiorna il carrello.');
        }
    }

    // 4. Pagamento (simulato) e ordine
    $stmt = $conn->prepare("INSERT INTO pagamento (username, metodo, stato) VALUES (?, ?, 'Completato')");
    $stmt->bind_param("ss", $username, $metodo);
    $stmt->execute();
    $idPagamento = $conn->insert_id;
    $stmt->close();

    $totale = $carrello['totale'];
    $stmt = $conn->prepare(
        "INSERT INTO ordine (username, id_pagamento, data, stato, totale) VALUES (?, ?, CURRENT_DATE, 'Pagato', ?)"
    );
    $stmt->bind_param("sid", $username, $idPagamento, $totale);
    $stmt->execute();
    $idOrdine = $conn->insert_id;
    $stmt->close();

    // Righe d'ordine con il prezzo effettivamente pagato (storico indipendente dai prezzi futuri)
    $stmtRiga  = $conn->prepare(
        "INSERT INTO incluso_in (id_ordine, id_prodotto, quantita_prodotto, prezzo_unitario) VALUES (?, ?, ?, ?)"
    );
    $stmtScala = $conn->prepare(
        "UPDATE prodotto SET quantita_disponibile = quantita_disponibile - ? WHERE id_prodotto = ?"
    );
    foreach ($carrello['prodotti'] as $r) {
        $idProd = $r['IdProdotto'];
        $qta    = $r['quantita'];
        $prezzo = $r['prezzoScontato'];
        $stmtRiga->bind_param("iiid", $idOrdine, $idProd, $qta, $prezzo);
        $stmtRiga->execute();

        // 5. Scala la disponibilità
        $stmtScala->bind_param("ii", $qta, $idProd);
        $stmtScala->execute();
    }
    $stmtRiga->close();
    $stmtScala->close();

    // Indirizzo salvato come predefinito per il prossimo ordine
    $stmt = $conn->prepare("UPDATE cliente SET indirizzo_predefinito = ? WHERE username = ?");
    $stmt->bind_param("ss", $indirizzo, $username);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM carrello WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    throw $e;
}

ok(['idOrdine' => $idOrdine]);
