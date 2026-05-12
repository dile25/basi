<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $sql = "SELECT ii.id_ordine AS IdOrdine,
                   ii.quantita_prodotto AS Quantita,
                   o.data AS DataOrdine,
                   o.stato AS Stato,
                   o.username AS Cliente,
                   p.nome AS Titolo,
                   p.prezzo AS Prezzo
            FROM INCLUSO_IN ii
            JOIN ORDINE o ON ii.id_ordine = o.id_ordine
            JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
            WHERE p.username = ?
            ORDER BY o.data DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idVenditore);
    $stmt->execute();
    $res = $stmt->get_result();

    $ordini = [];
    while ($row = $res->fetch_assoc()) {
        $ordini[] = $row;
    }
    echo json_encode(['status' => 'ok', 'ordini' => $ordini]);

} elseif ($action === 'update_status') {
    $idOrdine = $_POST['idOrdine'] ?? 0;
    $nuovoStato = $_POST['stato'] ?? '';

    if (!$idOrdine || !$nuovoStato) {
        echo json_encode(['status' => 'error', 'msg' => 'Dati mancanti']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE ORDINE SET stato = ? WHERE id_ordine = ?");
    $stmt->bind_param("si", $nuovoStato, $idOrdine);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Errore nel database']);
    }
}