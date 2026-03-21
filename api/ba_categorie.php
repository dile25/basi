<?php
require_once('../db_connect.php');
header('Content-Type: application/json');

// Query semplice: prendiamo tutte le categorie in ordine alfabetico
$sql = "SELECT IdCategoria, NomeCategoria FROM CATEGORIA ORDER BY NomeCategoria ASC";
$res = $conn->query($sql);

$categorie = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categorie[] = $row;
    }
    // Restituiamo direttamente l'array (come si aspetta il tuo header)
    echo json_encode($categorie);
} else {
    // In caso di errore del database
    echo json_encode([]);
}
?>