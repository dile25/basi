<?php
require_once('../db_connect.php');
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'recensioni' => []]);
    exit;
}

$sql = "SELECT id_recensione, username, valutazione, testo, data 
        FROM RECENSIONE 
        WHERE id_prodotto = ? 
        ORDER BY data DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

$recensioni = [];
while($row = $res->fetch_assoc()) {
    $recensioni[] = $row;
}

echo json_encode(['status' => 'ok', 'recensioni' => $recensioni]);