<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];

// Pacchetti che contengono almeno un prodotto di questo venditore, tipo 'libro'
$sql = "SELECT pk.id_pacchetto, pk.nome, pk.sconto_2, pk.sconto_3, pk.sconto_tutti,
               COUNT(p.id_prodotto) as tot_prodotti
        FROM PACCHETTO pk
        JOIN PRODOTTO p ON p.id_pacchetto = pk.id_pacchetto
        WHERE p.username = ? AND pk.attivo = 1 AND (pk.tipo_pacchetto = 'libro' OR pk.tipo_pacchetto IS NULL)
        GROUP BY pk.id_pacchetto
        ORDER BY pk.nome ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idVenditore);
$stmt->execute();
$res = $stmt->get_result();

$pacchetti = [];
while ($row = $res->fetch_assoc()) $pacchetti[] = $row;

echo json_encode(['status' => 'ok', 'pacchetti' => $pacchetti]);