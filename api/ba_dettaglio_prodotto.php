<?php
require_once __DIR__ . '/comune.php';

$id = intero($_GET['id'] ?? null, 1, PHP_INT_MAX);
if (!$id) errore('Prodotto non valido.');

$stmt = $conn->prepare(
    "SELECT p.id_prodotto, p.nome, p.autore, p.descrizione, p.prezzo, p.quantita_disponibile,
            p.username, p.id_pacchetto, pk.nome AS nome_pacchetto, pk.sconto,
            (SELECT GROUP_CONCAT(d.nome_categoria ORDER BY d.nome_categoria SEPARATOR ', ')
               FROM descrive d WHERE d.id_prodotto = p.id_prodotto) AS categorie
     FROM prodotto p
     LEFT JOIN pacchetto pk ON pk.id_pacchetto = p.id_pacchetto
     WHERE p.id_prodotto = ? AND p.attivo = 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$p) errore('Prodotto non trovato o non più in vendita.');

// Tutte le foto, la prima è la copertina
$stmt = $conn->prepare("SELECT url FROM immagine_prodotto WHERE id_prodotto = ? ORDER BY id_immagine_prodotto");
$stmt->bind_param("i", $id);
$stmt->execute();
$foto = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'url');
$stmt->close();

// Gli altri prodotti dello stesso pacchetto
$libriPacchetto = [];
if ($p['id_pacchetto']) {
    $stmt = $conn->prepare(
        "SELECT p2.id_prodotto, p2.nome, p2.autore, p2.prezzo, p2.quantita_disponibile,
                (SELECT i.url FROM immagine_prodotto i WHERE i.id_prodotto = p2.id_prodotto
                  ORDER BY i.id_immagine_prodotto LIMIT 1) AS foto
         FROM prodotto p2
         WHERE p2.id_pacchetto = ? AND p2.id_prodotto <> ? AND p2.attivo = 1
         ORDER BY p2.nome"
    );
    $stmt->bind_param("ii", $p['id_pacchetto'], $id);
    $stmt->execute();
    $libriPacchetto = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

ok(['dettagli' => [
    'IdProdotto'       => (int)$p['id_prodotto'],
    'NomeProdotto'     => $p['nome'],
    'autore'           => $p['autore'],
    'descrizione'      => $p['descrizione'],
    'prezzo'           => $p['prezzo'],
    'QuantitaDisp'     => (int)$p['quantita_disponibile'],
    'NomeCategoria'    => $p['categorie'] ?: 'Altro',
    'IdVenditore'      => $p['username'],
    'NomeVenditore'    => $p['username'],
    'foto'             => $foto,
    'id_pacchetto'     => $p['id_pacchetto'] ? (int)$p['id_pacchetto'] : null,
    'NomePacchetto'    => $p['nome_pacchetto'],
    'sconto_pacchetto' => $p['sconto'],
    'libriPacchetto'   => $libriPacchetto,
]]);
