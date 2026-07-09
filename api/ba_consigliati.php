<?php
/**
 * ba_consigliati.php
 * Restituisce libri consigliati basati sulle categorie dei prodotti nel carrello dell'utente.
 * Se il carrello è vuoto, ritorna i libri più recenti.
 */
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

$idUtente = $_SESSION['IdUtente'] ?? null;
$consigliati = [];

if ($idUtente) {
    // 1. Troviamo le categorie dei libri nel carrello dell'utente
    $sqlCategorie = "
        SELECT DISTINCT d.nome_categoria
        FROM CARRELLO c
        JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
        JOIN DESCRIVE d ON p.id_prodotto = d.id_prodotto
        WHERE c.username = ?
        LIMIT 5
    ";
    $stmtCat = $conn->prepare($sqlCategorie);
    $stmtCat->bind_param("s", $idUtente);
    $stmtCat->execute();
    $resCat = $stmtCat->get_result();

    $categorie = [];
    while ($row = $resCat->fetch_assoc()) {
        $categorie[] = $row['nome_categoria'];
    }

    if (!empty($categorie)) {
        // 2. Prendiamo libri delle stesse categorie NON già nel carrello, disponibili
        $placeholders = implode(',', array_fill(0, count($categorie), '?'));
        $types = str_repeat('s', count($categorie)) . 's'; // +1 per username

        $sqlConsigli = "
            SELECT DISTINCT p.id_prodotto, p.nome, p.autore, p.prezzo, p.quantita_disponibile,
                   d.nome_categoria,
                   (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as Foto
            FROM PRODOTTO p
            JOIN DESCRIVE d ON p.id_prodotto = d.id_prodotto
            WHERE d.nome_categoria IN ($placeholders)
              AND p.quantita_disponibile > 0
              AND p.attivo = 1
              AND p.id_prodotto NOT IN (
                  SELECT id_prodotto FROM CARRELLO WHERE username = ?
              )
            ORDER BY RAND()
            LIMIT 6
        ";

        $stmtConsigli = $conn->prepare($sqlConsigli);
        // bind_param dinamico: categorie + username
        $params = array_merge($categorie, [$idUtente]);
        $stmtConsigli->bind_param($types, ...$params);
        $stmtConsigli->execute();
        $resConsigli = $stmtConsigli->get_result();

        while ($row = $resConsigli->fetch_assoc()) {
            $consigliati[] = [
                'id_prodotto'  => $row['id_prodotto'],
                'nome'         => $row['nome'],
                'autore'       => $row['autore'] ?? '',
                'prezzo'       => (float)$row['prezzo'],
                'categoria'    => $row['nome_categoria'],
                'Foto'         => $row['Foto'] ?? 'img/default.jpg',
            ];
        }
    }
}

// Fallback: se carrello vuoto o nessun consiglio trovato, mostra ultimi arrivi
if (empty($consigliati)) {
    $sqlFallback = "
        SELECT p.id_prodotto, p.nome, p.autore, p.prezzo,
               (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as Foto
        FROM PRODOTTO p
        WHERE p.quantita_disponibile > 0 AND p.attivo = 1
        ORDER BY p.id_prodotto DESC
        LIMIT 6
    ";
    $resFb = $conn->query($sqlFallback);
    while ($row = $resFb->fetch_assoc()) {
        $consigliati[] = [
            'id_prodotto' => $row['id_prodotto'],
            'nome'        => $row['nome'],
            'autore'      => $row['autore'] ?? '',
            'prezzo'      => (float)$row['prezzo'],
            'categoria'   => '',
            'Foto'        => $row['Foto'] ?? 'img/default.jpg',
        ];
    }
}

echo json_encode(['status' => 'ok', 'consigliati' => $consigliati]);
?>