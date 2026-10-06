<?php
require_once __DIR__ . '/comune.php';
$username = richiediLogin('venditore');

// Una sola riga per prodotto: foto e categoria con sottoquery (prima un JOIN sulle
// immagini duplicava i prodotti con più foto)
$stmt = $conn->prepare(
    "SELECT p.id_prodotto, p.nome, p.autore, p.descrizione, p.prezzo, p.quantita_disponibile, p.id_pacchetto,
            (SELECT i.url FROM immagine_prodotto i WHERE i.id_prodotto = p.id_prodotto
              ORDER BY i.id_immagine_prodotto LIMIT 1) AS url_foto,
            c.nome_categoria, c.nome_categoria_padre
     FROM prodotto p
     LEFT JOIN categoria c ON c.nome_categoria =
          (SELECT d.nome_categoria FROM descrive d WHERE d.id_prodotto = p.id_prodotto
            ORDER BY d.nome_categoria LIMIT 1)
     WHERE p.username = ? AND p.attivo = 1
     ORDER BY p.data_inserimento DESC, p.id_prodotto DESC"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$libri = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ok(['libri' => $libri]);
