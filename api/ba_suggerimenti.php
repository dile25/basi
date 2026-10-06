<?php
require_once __DIR__ . '/comune.php';

$q = mb_substr(trim($_GET['q'] ?? ''), 0, 100);
if (mb_strlen($q) < 2) {
    ok(['prodotti' => []]);
}

$like = '%' . addcslashes($q, '%_\\') . '%';
// Le parentesi sono necessarie: senza, "attivo = 1 AND nome LIKE ? OR autore LIKE ?"
// restituiva anche prodotti non più in vendita.
$stmt = $conn->prepare(
    "SELECT id_prodotto, nome, autore FROM prodotto
     WHERE attivo = 1 AND (nome LIKE ? OR autore LIKE ?)
     ORDER BY nome
     LIMIT 8"
);
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$prodotti = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ok(['prodotti' => $prodotti]);
