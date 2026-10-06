<?php
require_once __DIR__ . '/comune.php';
$username = richiediLogin('venditore');

// Una sola query con LEFT JOIN (anche i pacchetti senza prodotti), raggruppata in PHP
$stmt = $conn->prepare(
    "SELECT pk.id_pacchetto, pk.nome, pk.sconto, p.id_prodotto, p.nome AS nome_prodotto
     FROM pacchetto pk
     LEFT JOIN prodotto p ON p.id_pacchetto = pk.id_pacchetto AND p.attivo = 1
     WHERE pk.username = ?
     ORDER BY pk.nome, p.nome"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

$pacchetti = [];
while ($r = $res->fetch_assoc()) {
    $id = $r['id_pacchetto'];
    if (!isset($pacchetti[$id])) {
        $pacchetti[$id] = [
            'id_pacchetto' => (int)$id,
            'nome'         => $r['nome'],
            'sconto'       => $r['sconto'],
            'tot_prodotti' => 0,
            'prodotti'     => [],
        ];
    }
    if ($r['id_prodotto'] !== null) {
        $pacchetti[$id]['prodotti'][] = ['id_prodotto' => (int)$r['id_prodotto'], 'nome' => $r['nome_prodotto']];
        $pacchetti[$id]['tot_prodotti']++;
    }
}
$stmt->close();

ok(['pacchetti' => array_values($pacchetti)]);
