<?php
include '../db_connect.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$cat = $_GET['cat'] ?? '';

// Query complessa: prende il prodotto e solo la prima immagine associata
$sql = "SELECT p.*,
        (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as url_immagine,
        (SELECT nome_categoria FROM DESCRIVE WHERE id_prodotto = p.id_prodotto LIMIT 1) as categoria_nome
        FROM PRODOTTO p
        WHERE 1=1";

if (!empty($q)) {
    $sql .= " AND (p.nome LIKE '%$q%' OR p.descrizione LIKE '%$q%')";
}

if (!empty($cat)) {
    // Filtro per categoria tramite la tabella di collegamento DESCRIVE
    $sql .= " AND p.id_prodotto IN (SELECT id_prodotto FROM DESCRIVE WHERE nome_categoria = '$cat')";
}

$result = $conn->query($sql);
$prodotti = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $prodotti[] = $row;
    }
}

echo json_encode(["status" => "ok", "prodotti" => $prodotti]);
?>