<?php
require_once __DIR__ . '/comune.php';
$username = richiediLogin('cliente');

$stmt = $conn->prepare(
    "SELECT p.id_prodotto, p.nome, p.prezzo, p.quantita_disponibile,
            (SELECT i.url FROM immagine_prodotto i WHERE i.id_prodotto = p.id_prodotto
              ORDER BY i.id_immagine_prodotto LIMIT 1) AS URLfoto
     FROM preferiti f
     JOIN prodotto p ON p.id_prodotto = f.id_prodotto
     WHERE f.username = ? AND p.attivo = 1
     ORDER BY f.data_aggiunta DESC, f.id_preferiti DESC"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$preferiti = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ok(['preferiti' => $preferiti]);
