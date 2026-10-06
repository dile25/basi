<?php
require_once __DIR__ . '/comune.php';

$username = trim($_GET['u'] ?? '');
if (!preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $username)) {
    errore('Venditore non valido.');
}

$stmt = $conn->prepare(
    "SELECT u.username AS nome_visualizzato, u.data_registrazione, v.ragione_sociale
     FROM utente u JOIN venditore v ON v.username = u.username
     WHERE u.username = ? AND u.attivo = 1"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$venditore = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$venditore) errore('Venditore non trovato.');

$stmt = $conn->prepare(
    "SELECT p.id_prodotto, p.nome, p.autore, p.prezzo, p.quantita_disponibile,
            (SELECT i.url FROM immagine_prodotto i WHERE i.id_prodotto = p.id_prodotto
              ORDER BY i.id_immagine_prodotto LIMIT 1) AS foto
     FROM prodotto p
     WHERE p.username = ? AND p.attivo = 1
     ORDER BY p.data_inserimento DESC, p.id_prodotto DESC"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$libri = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ok(['venditore' => $venditore, 'libri' => $libri]);
