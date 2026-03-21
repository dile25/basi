<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Non loggato']);
    exit;
}

$user = $_SESSION['IdUtente'];
$tipo = $_SESSION['tipoUtente'];

// 1. Dati base dall'anagrafica UTENTE
$sql = "SELECT username, nome, cognome, email, data_registrazione FROM UTENTE WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$dati = $stmt->get_result()->fetch_assoc();

// 2. Dati specifici e statistiche
if ($tipo === 'venditore') {
    $extra = $conn->prepare("SELECT partita_iva, ragione_sociale FROM VENDITORE WHERE username = ?");
    $extra->bind_param("s", $user);
    $extra->execute();
    $datiExtra = $extra->get_result()->fetch_assoc();
    
    // Conta quanti libri ha caricato il venditore nel DB
    $count = $conn->prepare("SELECT COUNT(*) as totale FROM PRODOTTO WHERE username = ?");
    $count->bind_param("s", $user);
    $count->execute();
    $dati['stat_libri'] = $count->get_result()->fetch_assoc()['totale'];
} else {
    $extra = $conn->prepare("SELECT telefono, indirizzo_predefinito FROM CLIENTE WHERE username = ?");
    $extra->bind_param("s", $user);
    $extra->execute();
    $datiExtra = $extra->get_result()->fetch_assoc();
    
    // Conta quanti ordini ha fatto il cliente
    $count = $conn->prepare("SELECT COUNT(*) as totale FROM ORDINE WHERE username = ?");
    $count->bind_param("s", $user);
    $count->execute();
    $dati['stat_ordini'] = $count->get_result()->fetch_assoc()['totale'];
}

echo json_encode([
    'status' => 'ok', 
    'tipo' => $tipo,
    'anagrafica' => $dati, 
    'dettagli' => $datiExtra
]);