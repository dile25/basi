<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione scaduta']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];

$sql = "SELECT p.id_prodotto, p.nome, p.prezzo, p.quantita_disponibile,
               img.url as URLfoto,
               pk.sconto as ScontoPacchetto
        FROM PREFERITI pref
        JOIN PRODOTTO p ON pref.id_prodotto = p.id_prodotto
        LEFT JOIN IMMAGINE_PRODOTTO img ON p.id_prodotto = img.id_prodotto
        LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
        WHERE pref.username = ?
        ORDER BY pref.data_aggiunta DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idUtente);
$stmt->execute();
$res = $stmt->get_result();

$preferiti = [];
while ($row = $res->fetch_assoc()) {
    $prezzoBase = (float)$row['prezzo'];
    $sconto = (float)($row['ScontoPacchetto'] ?? 0);
    $row['prezzo_scontato'] = round($prezzoBase - ($prezzoBase * ($sconto / 100)), 2);
    
    $preferiti[] = $row;
}

echo json_encode(['status' => 'ok', 'preferiti' => $preferiti]);