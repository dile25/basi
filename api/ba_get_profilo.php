<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$idRichiesto = $_GET['u'] ?? null;

if ($idRichiesto) {
    $id = $idRichiesto;
    // Determina il tipo dall'utente richiesto
    $stmtTipo = $conn->prepare("SELECT COUNT(*) as cnt FROM VENDITORE WHERE username = ?");
    $stmtTipo->bind_param("s", $id);
    $stmtTipo->execute();
    $r = $stmtTipo->get_result()->fetch_assoc();
    $tipo = $r['cnt'] > 0 ? 'venditore' : 'cliente';
} elseif (isset($_SESSION['IdUtente'])) {
    $id = $_SESSION['IdUtente'];
    $tipo = $_SESSION['tipoUtente'];
} else {
    echo json_encode(['status' => 'error']);
    exit;
}

// Dati base utente
$stmt = $conn->prepare("SELECT username, email, nome, cognome, data_registrazione FROM UTENTE WHERE username = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();

// Dati specifici per ruolo
$dettagli = [];
if ($tipo === 'venditore') {
    $stmt2 = $conn->prepare("SELECT partita_iva, ragione_sociale FROM VENDITORE WHERE username = ?");
    $stmt2->bind_param("s", $id);
    $stmt2->execute();
    $dettagli = $stmt2->get_result()->fetch_assoc();
} else {
    $stmt2 = $conn->prepare("SELECT telefono, indirizzo_predefinito FROM CLIENTE WHERE username = ?");
    $stmt2->bind_param("s", $id);
    $stmt2->execute();
    $dettagli = $stmt2->get_result()->fetch_assoc();
}

$prodotti = [];
if ($tipo === 'venditore') {
    $stmtP = $conn->prepare("
        SELECT p.id_prodotto, p.nome, p.autore, p.prezzo,
               (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) AS foto
        FROM PRODOTTO p
        WHERE p.username = ?
        ORDER BY p.data_inserimento DESC
    ");
    $stmtP->bind_param("s", $id);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    while ($row = $resP->fetch_assoc()) $prodotti[] = $row;
}

echo json_encode([
    'status'    => 'ok',
    'tipo'      => $tipo,
    'anagrafica'=> $utente,
    'dettagli'  => $dettagli ?? [],
    'prodotti'  => $prodotti
]);