<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione scaduta']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];

// Query ottimizzata: 
// 1. Usiamo una sottoquery per prendere SOLO UNA foto per libro (evita righe duplicate)
// 2. Prendiamo il voto specifico dell'utente per quel libro
$sql = "SELECT o.id_ordine, o.data, o.totale, o.stato,
               p.id_prodotto, p.nome, c.quantita, c.prezzo_acquisto,
               (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as foto_url,
               r.valutazione as mioVoto
        FROM ORDINE o
        JOIN COMPOSIZIONE c ON o.id_ordine = c.id_ordine
        JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
        LEFT JOIN RECENSIONE r ON (r.id_prodotto = p.id_prodotto AND r.username = o.username)
        WHERE o.username = ?
        ORDER BY o.data DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idUtente);
$stmt->execute();
$res = $stmt->get_result();

$ordini = [];

while ($row = $res->fetch_assoc()) {
    $idO = $row['id_ordine'];
    
    // Se è la prima volta che vediamo questo ordine, creiamo l'intestazione
    if (!isset($ordini[$idO])) {
        $ordini[$idO] = [
            'id_ordine' => $idO,
            'data' => date("d/m/Y H:i", strtotime($row['data'])),
            'totale' => $row['totale'],
            'stato' => $row['stato'],
            'libri' => []
        ];
    }
    
    // Aggiungiamo il libro alla lista 'libri' di questo ordine
    $ordini[$idO]['libri'][] = [
        'id_prodotto'   => $row['id_prodotto'],
        'nome'          => $row['nome'],
        'quantita'      => $row['quantita'],
        'prezzo_acquisto' => $row['prezzo_acquisto'],
        'foto'          => $row['foto_url'] ?? 'img/default_book.png',
        'gia_recensito' => !is_null($row['mioVoto']),
        'voto_utente'   => $row['mioVoto']
    ];
}

// Restituiamo l'array pulito (senza chiavi testuali) per il frontend
echo json_encode([
    'status' => 'ok', 
    'ordini' => array_values($ordini)
]);