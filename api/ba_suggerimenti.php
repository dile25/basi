<?php
require_once('../db_connect.php');
header('Content-Type: application/json');
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if(strlen($q) < 2) { echo json_encode(['prodotti' => []]); exit; }

$search = "%".$q."%";
$stmt = $conn->prepare("SELECT id_prodotto, nome, autore FROM PRODOTTO WHERE nome LIKE ? OR autore LIKE ? LIMIT 6");
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$res = $stmt->get_result();
$prodotti = [];
while($r = $res->fetch_assoc()) { $prodotti[] = $r; }
echo json_encode(['prodotti' => $prodotti]);
?>