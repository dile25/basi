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
    // 1. Carica carrello con info pacchetto per calcolare sconti reali
    $sql = "SELECT c.id_prodotto, c.quantita_prodotto, p.prezzo, p.autore, p.id_pacchetto,
                   pk.sconto_2, pk.sconto_3, pk.sconto_tutti
            FROM CARRELLO c
            JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
            LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
            WHERE c.username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idCliente);
    $stmt->execute();
    $result = $stmt->get_result();

    $righe = [];
    $conteggioAutori = [];
    $conteggioPacket = [];

    while ($row = $result->fetch_assoc()) {
        $righe[] = $row;
        $autore = trim($row['autore'] ?? '');
        if (!empty($autore)) {
            $conteggioAutori[$autore] = ($conteggioAutori[$autore] ?? 0) + 1;
        }
        if ($row['id_pacchetto']) {
            $ip = $row['id_pacchetto'];
            if (!isset($conteggioPacket[$ip])) {
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

    // 2. Calcola prezzo effettivo per ogni riga (stessa logica di ba_carrello.php)
    $righeFinali = [];
    $totaleFinale = 0;

    foreach ($righe as $row) {
        $prezzoUnitario = (float)$row['prezzo'];
        $autore = trim($row['autore'] ?? '');
        $percSconto = 0;
        $ip = $row['id_pacchetto'];

        if ($ip && isset($conteggioPacket[$ip])) {
            $pack  = $conteggioPacket[$ip];
            $nCart = $pack['nel_carrello'];
            $totDB = $pack['totale_db'];

            if ($nCart >= $totDB && $totDB >= 2) {
                $percSconto = $pack['sconto_tutti'];
            } elseif ($nCart >= 3) {
                $percSconto = $pack['sconto_3'];
            } elseif ($nCart >= 2) {
                $percSconto = $pack['sconto_2'];
            }
        }

        if ($percSconto == 0 && !empty($autore) && ($conteggioAutori[$autore] ?? 0) >= 2) {
            $percSconto = 10;
        }

        if ($percSconto > 0) {
            $prezzoUnitario = $prezzoUnitario * (1 - $percSconto / 100);
        }
        $prezzoUnitario = round($prezzoUnitario, 2);

        $righeFinali[] = [
            'id_prodotto'      => $row['id_prodotto'],
            'quantita'         => $row['quantita_prodotto'],
            'prezzo_unitario'  => $prezzoUnitario
        ];

        $totaleFinale += $prezzoUnitario * $row['quantita_prodotto'];
    }

    $totaleFinale = round($totaleFinale, 2);

    // 3. Registra il pagamento simulato
    $stmtPag = $conn->prepare("INSERT INTO PAGAMENTO (username, metodo, stato) VALUES (?, ?, 'Completato')");
    $stmtPag->bind_param("ss", $idCliente, $metodo);
    $stmtPag->execute();
    $idPagamento = $conn->insert_id;

    // 4. Crea ordine
    $stmtOrd = $conn->prepare("INSERT INTO ORDINE (username, data, stato, totale, id_pagamento) VALUES (?, CURRENT_DATE, 'Pagato', ?, ?)");
    $stmtOrd->bind_param("sdi", $idCliente, $totaleFinale, $idPagamento);
    $stmtOrd->execute();
    $idOrdine = $conn->insert_id;

    // 5. Inserisci in INCLUSO_IN con prezzo_unitario reale (post-sconto)
    foreach ($righeFinali as $r) {
        $stmtIncl = $conn->prepare(
            "INSERT INTO INCLUSO_IN (id_ordine, id_prodotto, quantita_prodotto, prezzo_unitario) VALUES (?, ?, ?, ?)"
        );
        $stmtIncl->bind_param("iiid", $idOrdine, $r['id_prodotto'], $r['quantita'], $r['prezzo_unitario']);
        $stmtIncl->execute();
    }

    // 6. Aggiorna scorte
    $sqlScorte = "UPDATE PRODOTTO p
                  JOIN CARRELLO c ON p.id_prodotto = c.id_prodotto
                  SET p.quantita_disponibile = p.quantita_disponibile - c.quantita_prodotto
                  WHERE c.username = ?";
    $stmtScorte = $conn->prepare($sqlScorte);
    $stmtScorte->bind_param("s", $idCliente);
    $stmtScorte->execute();

    // 7. Svuota carrello
    $stmtDel = $conn->prepare("DELETE FROM CARRELLO WHERE username = ?");
    $stmtDel->bind_param("s", $idCliente);
    $stmtDel->execute();

    $conn->commit();
    echo json_encode(['status' => 'ok', 'idOrdine' => $idOrdine]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}