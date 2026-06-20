<?php
// Restituisce i pacchetti abbonamento disponibili (6 e 12 mesi)
// in base ai tipi di prodotto presenti nel carrello del cliente.
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'abbonamenti' => []]);
    exit;
}

$username = $_SESSION['IdUtente'];

// Trova i tipi di prodotto periodici nel carrello corrente
$sql = "SELECT DISTINCT p.tipo_prodotto
        FROM carrello c
        JOIN prodotto p ON c.id_prodotto = p.id_prodotto
        WHERE c.username = ?
          AND p.tipo_prodotto IN ('rivista','magazine','periodico','fumetto')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$tipi = [];
while ($row = $res->fetch_assoc()) {
    $tipi[] = $row['tipo_prodotto'];
}
$res->free();
$stmt->close();

if (empty($tipi)) {
    echo json_encode(['status' => 'ok', 'abbonamenti' => []]);
    exit;
}

// Restituisce i pacchetti abbonamento disponibili
$abbonamenti = [];
$stmtPac = $conn->prepare(
    "SELECT id_pacchetto, nome, descrizione, sconto_tutti, periodicita
     FROM pacchetto
     WHERE tipo_pacchetto = 'abbonamento' AND attivo = 1
     ORDER BY id_pacchetto"
);
$stmtPac->execute();
$resPac = $stmtPac->get_result();
while ($row = $resPac->fetch_assoc()) {
    $abbonamenti[] = $row;
}
$resPac->free();
$stmtPac->close();

echo json_encode([
    'status'      => 'ok',
    'tipi'        => $tipi,
    'abbonamenti' => $abbonamenti
]);