<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Accedi per lasciare una recensione']);
    exit;
}
// I venditori non possono recensire prodotti
if ($_SESSION['tipoUtente'] === 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'I venditori non possono lasciare recensioni.']);
    exit;
}

$idUtente   = $_SESSION['IdUtente'];
$idProdotto = intval($_POST['idProdotto'] ?? 0);
$voto       = intval($_POST['voto'] ?? 0);
$commento   = trim($_POST['commento'] ?? '');
$idRec      = intval($_POST['id_recensione'] ?? 0); // presente solo in modifica

if ($voto < 1 || $voto > 5) {
    echo json_encode(['status' => 'error', 'msg' => 'Il voto deve essere tra 1 e 5']);
    exit;
}
if (empty($commento)) {
    echo json_encode(['status' => 'error', 'msg' => 'Il commento non può essere vuoto']);
    exit;
}

if ($idRec > 0) {
    // MODIFICA: aggiorna la recensione esistente verificando che sia dell'utente
    $stmt = $conn->prepare("UPDATE RECENSIONE SET valutazione=?, testo=?, data=CURRENT_DATE WHERE id_recensione=? AND username=?");
    $stmt->bind_param("isis", $voto, $commento, $idRec, $idUtente);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['status' => 'ok', 'msg' => 'Recensione aggiornata!']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Errore aggiornamento']);
    }
} else {
    // NUOVA: inserisce solo se non ne esiste già una per questo prodotto
    $check = $conn->prepare("SELECT id_recensione FROM RECENSIONE WHERE id_prodotto=? AND username=?");
    $check->bind_param("is", $idProdotto, $idUtente);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Hai già recensito questo libro. Usa il bottone Modifica.']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO RECENSIONE (id_prodotto, username, valutazione, testo, data) VALUES (?, ?, ?, ?, CURRENT_DATE)");
    $stmt->bind_param("isis", $idProdotto, $idUtente, $voto, $commento);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'msg' => 'Recensione pubblicata!']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Errore nel salvataggio']);
    }
}