<?php
require_once('../db_connect.php');
header('Content-Type: application/json');

$username = $_GET['u'] ?? '';
if (empty($username)) {
    echo json_encode(['status' => 'error', 'msg' => 'Venditore non specificato']);
    exit;
}

// Info pubbliche venditore
$stmt = $conn->prepare("SELECT u.nome, u.cognome, u.data_registrazione, v.ragione_sociale
                        FROM UTENTE u JOIN VENDITORE v ON u.username = v.username
                        WHERE u.username = ? AND u.attivo = 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$venditore = $stmt->get_result()->fetch_assoc();

if (!$venditore) {
    echo json_encode(['status' => 'error', 'msg' => 'Venditore non trovato']);
    exit;
}

// Usa lo username come nome visualizzato — sempre aggiornato, non dipende da
// ragione_sociale o nome/cognome che potrebbero non essere stati aggiornati altrove
$venditore['nome_visualizzato'] = $username;

// Libri del venditore
$stmtL = $conn->prepare("SELECT p.id_prodotto, p.nome, p.autore, p.prezzo, p.quantita_disponibile,
                                 (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as foto,
                                 (SELECT nome_categoria FROM DESCRIVE WHERE id_prodotto = p.id_prodotto LIMIT 1) as categoria
                          FROM PRODOTTO p
                          WHERE p.username = ? AND p.attivo = 1
                          ORDER BY p.id_prodotto DESC");
$stmtL->bind_param("s", $username);
$stmtL->execute();
$resL = $stmtL->get_result();

$libri = [];
while ($row = $resL->fetch_assoc()) $libri[] = $row;

echo json_encode([
    'status'    => 'ok',
    'venditore' => $venditore,
    'username'  => $username,
    'libri'     => $libri
]);