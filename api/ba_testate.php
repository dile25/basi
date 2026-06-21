<?php
// Restituisce le testate distinte già presenti nel DB
// (dall'unione di PRODOTTO.testata e PACCHETTO.testata)
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'testate' => []]);
    exit;
}

$testate = [];

// Da PACCHETTO (abbonamenti già configurati)
$res1 = $conn->query(
    "SELECT DISTINCT testata FROM PACCHETTO
     WHERE testata IS NOT NULL AND testata != '' AND tipo_pacchetto = 'abbonamento'
     ORDER BY testata"
);
while ($row = $res1->fetch_assoc()) {
    $testate[] = $row['testata'];
}

// Da PRODOTTO (prodotti periodici già inseriti)
$res2 = $conn->query(
    "SELECT DISTINCT testata FROM PRODOTTO
     WHERE testata IS NOT NULL AND testata != ''
     ORDER BY testata"
);
while ($row = $res2->fetch_assoc()) {
    if (!in_array($row['testata'], $testate)) {
        $testate[] = $row['testata'];
    }
}

sort($testate);
echo json_encode(['status' => 'ok', 'testate' => $testate]);