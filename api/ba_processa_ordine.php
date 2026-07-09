<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

// Solo i clienti autenticati possono processare un ordine
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione non valida']);
    exit;
}

$idUtente  = $_SESSION['IdUtente'];
$indirizzo = $_POST['indirizzo'] ?? '';
$metodo    = $_POST['metodo'] ?? 'Carta';

$conn->begin_transaction();

try {
    // -------------------------------------------------------------------------
    // 1. Carica il carrello con le informazioni sui pacchetti sconto
    //    Serve per ricalcolare i prezzi lato server (non ci fidiamo del frontend)
    // -------------------------------------------------------------------------
    $sql = "SELECT c.id_prodotto, c.quantita_prodotto, p.prezzo, p.autore, p.id_pacchetto,
                   pk.sconto_2, pk.sconto_3, pk.sconto_tutti
            FROM CARRELLO c
            JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
            LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
            WHERE c.username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idUtente);
    $stmt->execute();
    $result = $stmt->get_result();

    $righe           = [];
    $conteggioAutori = []; // autore => quante volte appare nel carrello (per sconto autore)
    $conteggioPacket = []; // id_pacchetto => info e contatori

    while ($row = $result->fetch_assoc()) {
        $righe[] = $row;

        // Conta quanti libri dello stesso autore sono nel carrello
        $autore = trim($row['autore'] ?? '');
        if (!empty($autore)) {
            $conteggioAutori[$autore] = ($conteggioAutori[$autore] ?? 0) + 1;
        }

        // Conta quanti prodotti dello stesso pacchetto sono nel carrello
        if ($row['id_pacchetto']) {
            $ip = $row['id_pacchetto'];
            if (!isset($conteggioPacket[$ip])) {
                // Quanti prodotti esistono in totale in questo pacchetto nel DB
                $stmtCount = $conn->prepare("SELECT COUNT(*) as tot FROM PRODOTTO WHERE id_pacchetto = ?");
                $stmtCount->bind_param("i", $ip);
                $stmtCount->execute();
                $totDB = $stmtCount->get_result()->fetch_assoc()['tot'];
                $conteggioPacket[$ip] = [
                    'nel_carrello' => 0,
                    'totale_db'    => $totDB,
                    'sconto_2'     => (float)($row['sconto_2'] ?? 10),
                    'sconto_3'     => (float)($row['sconto_3'] ?? 20),
                    'sconto_tutti' => (float)($row['sconto_tutti'] ?? 30),
                ];
            }
            $conteggioPacket[$ip]['nel_carrello']++;
        }
    }

    if (empty($righe)) throw new Exception("Il carrello è vuoto.");

    // -------------------------------------------------------------------------
    // 2. Calcola il prezzo effettivo per ogni riga applicando gli sconti
    //    Logica identica a ba_carrello.php (sconto pacchetto > sconto autore)
    // -------------------------------------------------------------------------
    $righeFinali  = [];
    $totaleFinale = 0;

    foreach ($righe as $row) {
        $prezzoUnitario = (float)$row['prezzo'];
        $autore         = trim($row['autore'] ?? '');
        $percSconto     = 0;
        $ip             = $row['id_pacchetto'];

        // Sconto pacchetto: crescente in base a quanti volumi sono nel carrello
        if ($ip && isset($conteggioPacket[$ip])) {
            $pack  = $conteggioPacket[$ip];
            $nCart = $pack['nel_carrello'];
            $totDB = $pack['totale_db'];

            if ($nCart >= $totDB && $totDB >= 2) {
                $percSconto = $pack['sconto_tutti']; // tutti i volumi della saga
            } elseif ($nCart >= 3) {
                $percSconto = $pack['sconto_3'];
            } elseif ($nCart >= 2) {
                $percSconto = $pack['sconto_2'];
            }
        }

        // Sconto autore: 10% se almeno 2 libri dello stesso autore nel carrello
        // Si applica solo se non c'è già uno sconto pacchetto
        if ($percSconto == 0 && !empty($autore) && ($conteggioAutori[$autore] ?? 0) >= 2) {
            $percSconto = 10;
        }

        if ($percSconto > 0) {
            $prezzoUnitario = $prezzoUnitario * (1 - $percSconto / 100);
        }
        $prezzoUnitario = round($prezzoUnitario, 2);

        $righeFinali[] = [
            'id_prodotto'     => $row['id_prodotto'],
            'quantita'        => $row['quantita_prodotto'],
            'prezzo_unitario' => $prezzoUnitario
        ];

        $totaleFinale += $prezzoUnitario * $row['quantita_prodotto'];
    }

    $totaleFinale = round($totaleFinale, 2);

    // -------------------------------------------------------------------------
    // 2b. Gestione abbonamento periodico
    //     Se il cliente ha scelto un piano abbonamento dal dettaglio prodotto,
    //     il checkout invia i parametri abb_* via POST.
    //     Il totale abbonamento = prezzo_numero × uscite × (1 - sconto%)
    //     e sostituisce il prezzo del singolo numero nel totale finale.
    // -------------------------------------------------------------------------
    $abbDati = null;
    if (!empty($_POST['abb_idPacchetto'])) {
        $abbIdPacchetto    = (int)$_POST['abb_idPacchetto'];
        $abbSconto         = (float)($_POST['abb_sconto']         ?? 0);
        $abbNumUscite      = (int)($_POST['abb_numUscite']        ?? 1);
        $abbPrezzoProdotto = (float)($_POST['abb_prezzoProdotto'] ?? 0);
        $abbNomeAbb        = $_POST['abb_nomeAbb']      ?? '';
        $abbPeriodicita    = $_POST['abb_periodicita']  ?? '';

        // Trova il prodotto nel carrello che appartiene alla testata dell'abbonamento
        $stmtAbbProd = $conn->prepare(
            "SELECT p.id_prodotto FROM PRODOTTO p
             JOIN PACCHETTO pk ON p.testata = pk.testata
             JOIN CARRELLO c ON c.id_prodotto = p.id_prodotto
             WHERE pk.id_pacchetto = ? AND c.username = ?
             LIMIT 1"
        );
        $stmtAbbProd->bind_param("is", $abbIdPacchetto, $idUtente);
        $stmtAbbProd->execute();
        $rowAbbProd    = $stmtAbbProd->get_result()->fetch_assoc();
        $abbIdProdotto = $rowAbbProd ? (int)$rowAbbProd['id_prodotto'] : null;

        // Totale abbonamento = prezzo scontato × numero di uscite
        $prezzoScontato    = $abbPrezzoProdotto * (1 - $abbSconto / 100);
        $totaleAbbonamento = round($prezzoScontato * $abbNumUscite, 2);

        // Totale finale = abbonamento + altri prodotti nel carrello (escluso il periodico)
        $totaleAltri = 0;
        foreach ($righeFinali as $r) {
            if ($abbIdProdotto && $r['id_prodotto'] == $abbIdProdotto) continue;
            $totaleAltri += $r['prezzo_unitario'] * $r['quantita'];
        }
        $totaleFinale = round($totaleAbbonamento + $totaleAltri, 2);

        $perioLabel = $abbPeriodicita === 'settimanale' ? 'numeri settimanali' : 'numeri mensili';
        $abbDati = [
            'id_prodotto'     => $abbIdProdotto,
            'prezzo_unitario' => $totaleAbbonamento, // salvato come riga singola in INCLUSO_IN
            'label'           => "Abbonamento \"{$abbNomeAbb}\" — {$abbNumUscite} {$perioLabel}",
        ];
    }

    // -------------------------------------------------------------------------
    // 3. Registra il pagamento (simulato)
    // -------------------------------------------------------------------------
    $stmtPag = $conn->prepare("INSERT INTO PAGAMENTO (username, metodo, stato) VALUES (?, ?, 'Completato')");
    $stmtPag->bind_param("ss", $idUtente, $metodo);
    $stmtPag->execute();
    $idPagamento = $conn->insert_id;

    // -------------------------------------------------------------------------
    // 4. Crea l'ordine
    // -------------------------------------------------------------------------
    $stmtOrd = $conn->prepare("INSERT INTO ORDINE (username, data, stato, totale, id_pagamento) VALUES (?, CURRENT_DATE, 'Pagato', ?, ?)");
    $stmtOrd->bind_param("sdi", $idUtente, $totaleFinale, $idPagamento);
    $stmtOrd->execute();
    $idOrdine = $conn->insert_id;

    // -------------------------------------------------------------------------
    // 5. Inserisci le righe in INCLUSO_IN con il prezzo post-sconto
    //    Se c'è un abbonamento, il periodico viene inserito con prezzo totale
    //    abbonamento (qty=1); gli altri prodotti vengono inseriti normalmente.
    // -------------------------------------------------------------------------
    if ($abbDati && $abbDati['id_prodotto']) {
        $stmtIncl = $conn->prepare(
            "INSERT INTO INCLUSO_IN (id_ordine, id_prodotto, quantita_prodotto, prezzo_unitario) VALUES (?, ?, 1, ?)"
        );
        $stmtIncl->bind_param("iid", $idOrdine, $abbDati['id_prodotto'], $abbDati['prezzo_unitario']);
        $stmtIncl->execute();
    }
    foreach ($righeFinali as $r) {
        if ($abbDati && $abbDati['id_prodotto'] && $r['id_prodotto'] == $abbDati['id_prodotto']) continue;
        $stmtIncl = $conn->prepare(
            "INSERT INTO INCLUSO_IN (id_ordine, id_prodotto, quantita_prodotto, prezzo_unitario) VALUES (?, ?, ?, ?)"
        );
        $stmtIncl->bind_param("iiid", $idOrdine, $r['id_prodotto'], $r['quantita'], $r['prezzo_unitario']);
        $stmtIncl->execute();
    }

    // -------------------------------------------------------------------------
    // 6. Decrementa le scorte in base alle quantità acquistate
    // -------------------------------------------------------------------------
    $stmtScorte = $conn->prepare(
        "UPDATE PRODOTTO p
         JOIN CARRELLO c ON p.id_prodotto = c.id_prodotto
         SET p.quantita_disponibile = p.quantita_disponibile - c.quantita_prodotto
         WHERE c.username = ?"
    );
    $stmtScorte->bind_param("s", $idUtente);
    $stmtScorte->execute();

    // -------------------------------------------------------------------------
    // 7. Svuota il carrello
    // -------------------------------------------------------------------------
    $stmtDel = $conn->prepare("DELETE FROM CARRELLO WHERE username = ?");
    $stmtDel->bind_param("s", $idUtente);
    $stmtDel->execute();

    $conn->commit();

    $risposta = ['status' => 'ok', 'idOrdine' => $idOrdine];
    if ($abbDati) $risposta['labelAbbonamento'] = $abbDati['label'];
    echo json_encode($risposta);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}