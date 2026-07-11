<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'ok', 'abbonamenti' => []]);
    exit;
}

$idUtente = $_SESSION['IdUtente'];

// Trova i pacchetti abbonamento collegati ai prodotti periodici nel carrello
// Il collegamento è diretto: PRODOTTO.id_pacchetto → PACCHETTO
$sql = "SELECT DISTINCT pk.id_pacchetto, pk.nome, pk.sconto_tutti, pk.periodicita,
               p.id_prodotto, p.nome as nome_prodotto, p.prezzo
        FROM CARRELLO c
        JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto AND p.attivo = 1
        JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto
        WHERE c.username = ?
          AND pk.tipo_pacchetto = 'abbonamento'
          AND pk.attivo = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idUtente);
$stmt->execute();
$res = $stmt->get_result();

$abbonamenti = [];
while ($row = $res->fetch_assoc()) {
    // Calcola uscite: mensile=12, settimanale=52
    $numUscite = $row['periodicita'] === 'settimanale' ? 52 : 12;
    $prezzoTotale = round($row['prezzo'] * $numUscite * (1 - $row['sconto_tutti'] / 100), 2);

    $abbonamenti[] = [
        'idPacchetto'   => $row['id_pacchetto'],
        'nomeAbb'       => $row['nome'],
        'sconto'        => $row['sconto_tutti'],
        'periodicita'   => $row['periodicita'],
        'numUscite'     => $numUscite,
        'idProdotto'    => $row['id_prodotto'],
        'nomeProdotto'  => $row['nome_prodotto'],
        'prezzoProdotto'=> $row['prezzo'],
        'prezzoTotale'  => $prezzoTotale
    ];
}
$res->free();
$stmt->close();

echo json_encode(['status' => 'ok', 'abbonamenti' => $abbonamenti]);