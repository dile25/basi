<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

// Sicurezza: solo i clienti loggati possono aggiungere al carrello
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Accesso negato o sessione scaduta']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$idProdotto = $_POST['idProdotto'] ?? 0;
$quantita = $_POST['quantita'] ?? 1;

if ($idProdotto <= 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto non valido']);
    exit;
}

// 1. Recuperiamo il prezzo unitario (considerando eventuali sconti attivi)
$sqlPrezzo = "SELECT p.prezzo, COALESCE(pk.sconto, 0) as sconto
              FROM PRODOTTO p
              LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
              WHERE p.id_prodotto = ?";
$stmtP = $conn->prepare($sqlPrezzo);
$stmtP->bind_param("i", $idProdotto);
$stmtP->execute();
$resP = $stmtP->get_result();

if ($resP->num_rows === 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Libro non trovato']);
    exit;
}

$datiProdotto = $resP->fetch_assoc();
$prezzoUnitario = $datiProdotto['prezzo'] - ($datiProdotto['prezzo'] * ($datiProdotto['sconto'] / 100));

// 2. Controlliamo se il prodotto è già nel carrello per questo utente
$check = $conn->prepare("SELECT id_carrello, quantita_prodotto FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
$check->bind_param("si", $idUtente, $idProdotto);
$check->execute();
$resCheck = $check->get_result();

if ($row = $resCheck->fetch_assoc()) {
    // AGGIORNA: Il prodotto esiste già, sommiamo la quantità e ricalcoliamo il prezzo totale
    $nuovaQta = $row['quantita_prodotto'] + $quantita;
    $nuovoTotale = $nuovaQta * $prezzoUnitario;
    
    $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ?, prezzo_totale = ? WHERE username = ? AND id_prodotto = ?");
    $stmt->bind_param("idsi", $nuovaQta, $nuovoTotale, $idUtente, $idProdotto);
} else {
    // INSERISCI: Nuovo record nel carrello
    $prezzoTotaleIniziale = $quantita * $prezzoUnitario;
    
    $stmt = $conn->prepare("INSERT INTO CARRELLO (username, id_prodotto, quantita_prodotto, prezzo_totale, data_creazione) VALUES (?, ?, ?, ?, CURRENT_DATE)");
    $stmt->bind_param("siid", $idUtente, $idProdotto, $quantita, $prezzoTotaleIniziale);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok', 'msg' => 'Carrello aggiornato']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore durante l\'aggiornamento del carrello']);
}