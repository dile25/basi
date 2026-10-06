<?php
/*
 * Catalogo: ricerca per testo, filtro per categoria e sezioni della home.
 * GET q        testo cercato in titolo e autore
 * GET cat      categoria (se è una categoria padre include le sue sottocategorie)
 * GET sezione  'nuovi' (ultimi disponibili) | 'offerte' (un prodotto disponibile per pacchetto)
 * GET limit    numero massimo di risultati (1-100): la paginazione è lato server
 */
require_once __DIR__ . '/comune.php';

$q       = mb_substr(trim($_GET['q'] ?? ''), 0, 100);
$cat     = mb_substr(trim($_GET['cat'] ?? ''), 0, 100);
$sezione = $_GET['sezione'] ?? '';
$limit   = intero($_GET['limit'] ?? 60, 1, 100) ?? 60;

$where  = ['p.attivo = 1'];
$params = [];
$types  = '';

if ($q !== '') {
    // escape dei caratteri jolly di LIKE inseriti dall'utente
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[]  = '(p.nome LIKE ? OR p.autore LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

if ($cat !== '') {
    $where[] = 'EXISTS (SELECT 1 FROM descrive d
                        JOIN categoria c ON c.nome_categoria = d.nome_categoria
                        WHERE d.id_prodotto = p.id_prodotto
                          AND (c.nome_categoria = ? OR c.nome_categoria_padre = ?))';
    $params[] = $cat;
    $params[] = $cat;
    $types   .= 'ss';
}

if ($sezione === 'nuovi') {
    $where[] = 'p.quantita_disponibile > 0';
} elseif ($sezione === 'offerte') {
    // il primo prodotto disponibile di ogni pacchetto
    $where[] = 'p.quantita_disponibile > 0';
    $where[] = 'p.id_prodotto = (SELECT MIN(p2.id_prodotto) FROM prodotto p2
                                 WHERE p2.id_pacchetto = p.id_pacchetto
                                   AND p2.attivo = 1 AND p2.quantita_disponibile > 0)';
}

$sql = "SELECT p.id_prodotto, p.nome, p.autore, p.prezzo, p.quantita_disponibile,
               (SELECT i.url FROM immagine_prodotto i
                 WHERE i.id_prodotto = p.id_prodotto
                 ORDER BY i.id_immagine_prodotto LIMIT 1) AS URLfoto,
               pk.nome   AS nome_pacchetto,
               pk.sconto AS sconto_pacchetto
        FROM prodotto p
        LEFT JOIN pacchetto pk ON pk.id_pacchetto = p.id_pacchetto
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.data_inserimento DESC, p.id_prodotto DESC
        LIMIT ?";
$params[] = $limit;
$types   .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$prodotti = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ok(['prodotti' => $prodotti]);
