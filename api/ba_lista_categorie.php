<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT nome_categoria, nome_categoria_padre FROM CATEGORIA ORDER BY nome_categoria_padre ASC, nome_categoria ASC");
$stmt->execute();
$res = $stmt->get_result();

$categorie = [];
while ($row = $res->fetch_assoc()) {
    $categorie[] = $row;
}
$stmt->close();

echo json_encode(['status' => 'ok', 'categorie' => $categorie]);