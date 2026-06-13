<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];

// Calcola guadagno da ordini spediti/consegnati
$sql = "SELECT COALESCE(SUM(p.prezzo * ii.quantita_prodotto), 0) as guadagno
        FROM INCLUSO_IN ii
        JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
        JOIN ORDINE o ON ii.id_ordine = o.id_ordine
        WHERE p.username = ? AND o.stato IN ('Spedito','Consegnato')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idVenditore);
$stmt->execute();
$guadagno = (float)$stmt->get_result()->fetch_assoc()['guadagno'];

if ($guadagno <= 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Nessun guadagno disponibile da trasferire.']);
    exit;
}

// Registra data trasferimento nella tabella VENDITORE
$stmtUpd = $conn->prepare("UPDATE VENDITORE SET ultimo_trasferimento = NOW() WHERE username = ?");
$stmtUpd->bind_param("s", $idVenditore);

if ($stmtUpd->execute()) {
    echo json_encode([
        'status'  => 'ok',
        'importo' => round($guadagno, 2),
        'msg'     => 'Trasferimento di €' . number_format($guadagno, 2, ',', '.') . ' simulato con successo!'
    ]);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore durante il trasferimento.']);
}