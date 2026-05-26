<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Accesso negato']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$idProdotto = $_POST['idProdotto'] ?? 0;
$quantita = intval($_POST['quantita'] ?? 1);

// DEBUG: Vediamo cosa sta succedendo nel DB per questo specifico ID
$checkStmt = $conn->prepare("SELECT quantita_disponibile FROM PRODOTTO WHERE id_prodotto = ?");
$checkStmt->bind_param("i", $idProdotto);
$checkStmt->execute();
$res = $checkStmt->get_result()->fetch_assoc();

if (!$res) {
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto inesistente']);
    exit;
}

$stockReale = (int)$res['quantita_disponibile'];

// Controlliamo quanto ha già nel carrello
$checkCarrello = $conn->prepare("SELECT quantita_prodotto FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
$checkCarrello->bind_param("si", $idUtente, $idProdotto);
$checkCarrello->execute();
$rowCarrello = $checkCarrello->get_result()->fetch_assoc();

$quantitaGiaNelCarrello = $rowCarrello ? (int)$rowCarrello['quantita_prodotto'] : 0;
$totaleRichiesto = $quantitaGiaNelCarrello + $quantita;

// CONTROLLO FINALE RIGIDO
if ($totaleRichiesto > $stockReale) {
    echo json_encode([
        'status' => 'error', 
        'msg' => "ERRORE: Disponibilità nel DB per ID $idProdotto è $stockReale. Tu hai in carrello $quantitaGiaNelCarrello e vuoi aggiungerne $quantita. Totale $totaleRichiesto > $stockReale."
    ]);
    exit;
}

// Se supera questo blocco, procedi con l'aggiornamento
$sqlPrezzo = "SELECT prezzo, COALESCE((SELECT sconto FROM PACCHETTO WHERE id_pacchetto = PRODOTTO.id_pacchetto AND attivo = 1), 0) as sconto FROM PRODOTTO WHERE id_prodotto = ?";
$stmtP = $conn->prepare($sqlPrezzo);
$stmtP->bind_param("i", $idProdotto);
$stmtP->execute();
$dati = $stmtP->get_result()->fetch_assoc();
$prezzoUnitario = $dati['prezzo'] - ($dati['prezzo'] * ($dati['sconto'] / 100));
$nuovoTotale = $totaleRichiesto * $prezzoUnitario;

if ($rowCarrello) {
    $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ?, prezzo_totale = ? WHERE username = ? AND id_prodotto = ?");
    $stmt->bind_param("idsi", $totaleRichiesto, $nuovoTotale, $idUtente, $idProdotto);
} else {
    $stmt = $conn->prepare("INSERT INTO CARRELLO (username, id_prodotto, quantita_prodotto, prezzo_totale, data_creazione) VALUES (?, ?, ?, ?, CURRENT_DATE)");
    $stmt->bind_param("siid", $idUtente, $idProdotto, $totaleRichiesto, $nuovoTotale);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'ok', 'msg' => 'Aggiornato']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Errore database']);
}
?>