<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['status' => 'error', 'msg' => 'ID non valido']);
    exit;
}

$sql = "SELECT r.id_recensione, r.username, r.valutazione, r.testo, r.data,
               (SELECT url FROM IMMAGINE_RECENSIONE WHERE id_recensione = r.id_recensione LIMIT 1) AS foto
        FROM RECENSIONE r
        WHERE r.id_prodotto = ?
        ORDER BY r.data DESC, r.id_recensione DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

$recensioni = [];
while ($row = $res->fetch_assoc()) {
    $row['data'] = date("d/m/Y", strtotime($row['data']));
    $recensioni[] = $row;
}

echo json_encode(['status' => 'ok', 'recensioni' => $recensioni]);