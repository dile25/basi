<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];

$conn->begin_transaction();

try {
    // Calcola il guadagno disponibile: solo righe non ancora trasferite
    $sql = "SELECT COALESCE(SUM(
                COALESCE(ii.prezzo_unitario, p.prezzo) * ii.quantita_prodotto
            ), 0) as guadagno
            FROM INCLUSO_IN ii
            JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
            JOIN ORDINE o ON ii.id_ordine = o.id_ordine
            WHERE p.username = ? AND o.stato IN ('Spedito','Consegnato') AND ii.trasferito = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idVenditore);
    $stmt->execute();
    $guadagno = (float)$stmt->get_result()->fetch_assoc()['guadagno'];

    if ($guadagno <= 0) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'msg' => 'Nessun guadagno disponibile da trasferire.']);
        exit;
    }

    // Marca come trasferite tutte le righe appena conteggiate, cosi' non
    // verranno mai piu' incluse in calcoli futuri del guadagno disponibile.
    $sqlUpdate = "UPDATE INCLUSO_IN ii
                  JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
                  JOIN ORDINE o ON ii.id_ordine = o.id_ordine
                  SET ii.trasferito = 1
                  WHERE p.username = ? AND o.stato IN ('Spedito','Consegnato') AND ii.trasferito = 0";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("s", $idVenditore);
    if (!$stmtUpdate->execute()) {
        throw new Exception("Errore nel marcare le righe come trasferite");
    }

    // Aggiorna anche la data dell'ultimo trasferimento, utile per lo storico
    $stmtUpd = $conn->prepare("UPDATE VENDITORE SET ultimo_trasferimento = NOW() WHERE username = ?");
    $stmtUpd->bind_param("s", $idVenditore);
    $stmtUpd->execute();

    $conn->commit();

    echo json_encode([
        'status'  => 'ok',
        'importo' => round($guadagno, 2),
        'msg'     => 'Trasferimento di €' . number_format($guadagno, 2, ',', '.') . ' simulato con successo!'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => 'Errore durante il trasferimento: ' . $e->getMessage()]);
}