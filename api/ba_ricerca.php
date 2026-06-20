<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$q    = isset($_GET['q'])    ? trim($_GET['q'])    : '';
$cat  = isset($_GET['cat'])  ? trim($_GET['cat'])  : '';
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';

$where  = [];
$params = [];
$types  = '';

if ($q !== '') {
    $where[]  = "(p.nome LIKE ? OR p.autore LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $types   .= 'ss';
}

if ($cat !== '') {
    $where[]  = "d.nome_categoria = ?";
    $params[] = $cat;
    $types   .= 's';
}

if ($tipo !== '') {
    $where[]  = "p.tipo_prodotto = ?";
    $params[] = $tipo;
    $types   .= 's';
}

$whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// sconto_pacchetto: usa pac.sconto SOLO se valorizzato (> 0).
// Per saghe e promo autore pac.sconto e' NULL, quindi
// sconto_pacchetto = 0 e PrezzoScontato = prezzo pieno:
// nessun badge/prezzo scontato fasullo in home.
$sql = "SELECT p.id_prodotto, p.nome, p.autore, p.descrizione, p.prezzo,
               p.quantita_disponibile, p.data_inserimento, p.tipo_prodotto,
               img.url AS URLfoto,
               d.nome_categoria,
               COALESCE(pac.sconto, 0)                             AS sconto_pacchetto,
               ROUND(p.prezzo * (1 - COALESCE(pac.sconto, 0)/100), 2) AS PrezzoScontato,
               pac.nome     AS nome_pacchetto,
               pac.tipo_pacchetto,
               pac.e_saga
        FROM prodotto p
        LEFT JOIN immagine_prodotto img ON p.id_prodotto = img.id_prodotto
        LEFT JOIN descrive d ON p.id_prodotto = d.id_prodotto
        LEFT JOIN pacchetto pac ON p.id_pacchetto = pac.id_pacchetto AND pac.attivo = 1
        $whereClause
        GROUP BY p.id_prodotto
        ORDER BY p.data_inserimento DESC";

$prodotti = [];
if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}

while ($row = $res->fetch_assoc()) {
    $prodotti[] = $row;
}
echo json_encode(['status' => 'ok', 'prodotti' => $prodotti]);