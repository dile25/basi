<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];

// Pacchetti libro del venditore con e_saga
$sql = "SELECT pk.id_pacchetto, pk.nome, pk.sconto_2, pk.sconto_3, pk.sconto_tutti,
               pk.e_saga, COUNT(p.id_prodotto) as tot_prodotti
        FROM PACCHETTO pk
        JOIN PRODOTTO p ON p.id_pacchetto = pk.id_pacchetto
        WHERE p.username = ? AND pk.attivo = 1 AND (pk.tipo_pacchetto = 'libro' OR pk.tipo_pacchetto IS NULL)
        GROUP BY pk.id_pacchetto
        ORDER BY pk.nome ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idVenditore);
$stmt->execute();
$res = $stmt->get_result();

$pacchetti = [];
while ($row = $res->fetch_assoc()) {
    // Carica i prodotti di questo pacchetto
    $stmtProd = $conn->prepare(
        "SELECT id_prodotto, nome FROM PRODOTTO WHERE id_pacchetto = ? AND username = ? AND attivo = 1 ORDER BY nome"
    );
    $stmtProd->bind_param("is", $row['id_pacchetto'], $idVenditore);
    $stmtProd->execute();
    $resProd = $stmtProd->get_result();
    $prodotti = [];
    while ($p = $resProd->fetch_assoc()) $prodotti[] = $p;
    $resProd->free();
    $stmtProd->close();

    $row['prodotti'] = $prodotti;
    $pacchetti[] = $row;
}
$res->free();
$stmt->close();

echo json_encode(['status' => 'ok', 'pacchetti' => $pacchetti]);