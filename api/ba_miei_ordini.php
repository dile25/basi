<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione scaduta']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];

$sql = "SELECT o.id_ordine, o.data, o.totale, o.stato,
               p.id_prodotto, p.nome, ii.quantita_prodotto as quantita,
               (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as foto_url,
               r.valutazione as mioVoto
        FROM ORDINE o
        JOIN INCLUSO_IN ii ON o.id_ordine = ii.id_ordine
        JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
        LEFT JOIN RECENSIONE r ON r.id_prodotto = p.id_prodotto AND r.username = o.username
        WHERE o.username = ?
        ORDER BY o.data DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idUtente);
$stmt->execute();
$res = $stmt->get_result();

$ordini = [];
while ($row = $res->fetch_assoc()) {
    $idO = $row['id_ordine'];
    if (!isset($ordini[$idO])) {
        $ordini[$idO] = [
            'id_ordine' => $idO,
            'data'      => date("d/m/Y", strtotime($row['data'])),
            'totale'    => $row['totale'],
            'stato'     => $row['stato'],
            'libri'     => []
        ];
    }
    $ordini[$idO]['libri'][] = [
        'id_prodotto'    => $row['id_prodotto'],
        'nome'           => $row['nome'],
        'quantita'       => $row['quantita'],
        'prezzo_acquisto'=> $row['totale'],
        'foto'           => $row['foto_url'] ?? 'img/default.jpg',
        'gia_recensito'  => !is_null($row['mioVoto']),
        'voto_utente'    => $row['mioVoto']
    ];
}

echo json_encode(['status' => 'ok', 'ordini' => array_values($ordini)]);