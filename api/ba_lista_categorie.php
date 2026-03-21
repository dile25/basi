<?php
include '../db_connect.php';
header('Content-Type: application/json');

$sql = "SELECT nome_categoria, nome_categoria_padre FROM CATEGORIA";
$result = $conn->query($sql);

$categorie = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categorie[] = $row;
    }
    echo json_encode(["status" => "ok", "categorie" => $categorie]);
} else {
    echo json_encode(["status" => "error", "msg" => "Nessuna categoria trovata"]);
}
?>