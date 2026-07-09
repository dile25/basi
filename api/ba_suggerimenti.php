<?php
require_once('../db_connect.php');
header('Content-Type: application/json');
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if(strlen($q) < 2) { echo json_encode(['prodotti' => []]); exit; }

$search = "%".$q."%";
// Confronto case-insensitive esplicito per evitare mismatch di collation
// tra nome/autore inseriti manualmente e quelli caricati da sample data.
// Ordinamento alfabetico (non per ID) cosi' se ci sono piu' risultati del
// limite, quelli mostrati sono sempre gli stessi e in un ordine prevedibile
// invece di tagliare a caso in base a quando sono stati inseriti.
$stmt = $conn->prepare(
    "SELECT id_prodotto, nome, autore FROM PRODOTTO
     WHERE attivo = 1 AND LOWER(CONVERT(nome USING utf8mb4)) LIKE LOWER(CONVERT(? USING utf8mb4))
        OR LOWER(CONVERT(autore USING utf8mb4)) LIKE LOWER(CONVERT(? USING utf8mb4))
     ORDER BY nome ASC
     LIMIT 15"
);
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$res = $stmt->get_result();
$prodotti = [];
while($r = $res->fetch_assoc()) { $prodotti[] = $r; }
echo json_encode(['prodotti' => $prodotti]);