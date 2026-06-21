<?php
// Restituisce i pacchetti abbonamento filtrati per testata,
// basandosi sui prodotti periodici nel carrello del cliente.
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'abbonamenti' => []]);
    exit;
}

$username = $_SESSION['IdUtente'];

// Recupera testata e tipo dei prodotti periodici nel carrello
$sql = "SELECT DISTINCT p.testata
        FROM carrello c
        JOIN prodotto p ON c.id_prodotto = p.id_prodotto
        WHERE c.username = ?
          AND p.tipo_prodotto IN ('rivista','magazine','periodico','fumetto')
          AND p.testata IS NOT NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$testate = [];
while ($row = $res->fetch_assoc()) {
    $testate[] = $row['testata'];
}
$res->free();
$stmt->close();

if (empty($testate)) {
    echo json_encode(['status' => 'ok', 'abbonamenti' => []]);
    exit;
}

// Recupera i pacchetti abbonamento per le testate trovate
$placeholders = implode(',', array_fill(0, count($testate), '?'));
$stmtAbb = $conn->prepare(
    "SELECT id_pacchetto, nome, descrizione, sconto_tutti, periodicita, testata
     FROM PACCHETTO
     WHERE tipo_pacchetto = 'abbonamento' AND attivo = 1
       AND testata IN ($placeholders)
     ORDER BY testata, id_pacchetto"
);
$types = str_repeat('s', count($testate));
$stmtAbb->bind_param($types, ...$testate);
$stmtAbb->execute();
$resAbb = $stmtAbb->get_result();
$abbonamenti = [];
while ($a = $resAbb->fetch_assoc()) {
    $abbonamenti[] = $a;
}
$resAbb->free();
$stmtAbb->close();

echo json_encode([
    'status'      => 'ok',
    'abbonamenti' => $abbonamenti
]);