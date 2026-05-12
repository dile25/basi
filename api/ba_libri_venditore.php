<?php
ob_start();
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Accesso negato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];

// Recupera tutti i libri del venditore con immagine
$sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.quantita_disponibile,
               i.url AS url_foto
        FROM PRODOTTO p
        LEFT JOIN IMMAGINE_PRODOTTO i ON p.id_prodotto = i.id_prodotto
        WHERE p.username = ?
        ORDER BY p.id_prodotto DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idVenditore);
$stmt->execute();
$res = $stmt->get_result();

$libri = [];
while ($row = $res->fetch_assoc()) {
    $libri[] = $row;
}

ob_clean();
echo json_encode(['status' => 'ok', 'libri' => $libri]);