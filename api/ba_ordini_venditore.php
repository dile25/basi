<?php
/* Vendite del venditore, in sola lettura: per ogni ordine solo le righe dei suoi prodotti. */
require_once __DIR__ . '/comune.php';
$username = richiediLogin('venditore');

$stato = $_GET['stato'] ?? '';
if (!in_array($stato, ['', 'Pagato', 'Annullato'], true)) errore('Filtro non valido.');

$sql = "SELECT o.id_ordine, o.data, o.stato, o.username AS cliente,
               p.nome, ii.quantita_prodotto, ii.prezzo_unitario,
               (SELECT i.url FROM immagine_prodotto i WHERE i.id_prodotto = p.id_prodotto
                 ORDER BY i.id_immagine_prodotto LIMIT 1) AS foto
        FROM incluso_in ii
        JOIN ordine o   ON o.id_ordine = ii.id_ordine
        JOIN prodotto p ON p.id_prodotto = ii.id_prodotto
        WHERE p.username = ?" . ($stato !== '' ? " AND o.stato = ?" : "") . "
        ORDER BY o.data DESC, o.id_ordine DESC";

$stmt = $conn->prepare($sql);
if ($stato !== '') {
    $stmt->bind_param("ss", $username, $stato);
} else {
    $stmt->bind_param("s", $username);
}
$stmt->execute();
$res = $stmt->get_result();

$ordini = [];
while ($r = $res->fetch_assoc()) {
    $id = $r['id_ordine'];
    if (!isset($ordini[$id])) {
        $ordini[$id] = [
            'IdOrdine'        => (int)$id,
            'DataOrdine'      => date('d/m/Y', strtotime($r['data'])),
            'StatoOrdine'     => $r['stato'],
            'Cliente'         => $r['cliente'],
            'TotaleVenditore' => 0,
            'libri'           => [],
        ];
    }
    $ordini[$id]['libri'][] = [
        'Titolo'   => $r['nome'],
        'Quantita' => (int)$r['quantita_prodotto'],
        'Prezzo'   => $r['prezzo_unitario'],
        'Foto'     => $r['foto'] ?? 'img/default.jpg',
    ];
    $ordini[$id]['TotaleVenditore'] = round($ordini[$id]['TotaleVenditore'] + $r['prezzo_unitario'] * $r['quantita_prodotto'], 2);
}
$stmt->close();

ok(['ordini' => array_values($ordini)]);
