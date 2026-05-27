<?php
ob_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$sql = "SELECT nome_categoria, nome_categoria_padre FROM CATEGORIA ORDER BY nome_categoria_padre ASC, nome_categoria ASC";
$res = $conn->query($sql);

$categorie = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categorie[] = $row;
    }
}

ob_clean();
echo json_encode(['status' => 'ok', 'categorie' => $categorie]);