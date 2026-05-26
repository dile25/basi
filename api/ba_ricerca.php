<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.quantita_disponibile, 
               img.url as URLfoto,
               d.nome_categoria
        FROM PRODOTTO p
        LEFT JOIN IMMAGINE_PRODOTTO img ON p.id_prodotto = img.id_prodotto
        LEFT JOIN DESCRIVE d ON p.id_prodotto = d.id_prodotto
        GROUP BY p.id_prodotto 
        ORDER BY p.nome ASC";

$res = $conn->query($sql);
$prodotti = [];
while ($row = $res->fetch_assoc()) {
    $row['PrezzoScontato'] = (float)$row['prezzo']; 
    $prodotti[] = $row;
}
echo json_encode(['status' => 'ok', 'prodotti' => $prodotti]);
?>