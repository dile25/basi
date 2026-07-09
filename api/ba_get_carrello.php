<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

// Sicurezza: Solo clienti loggati
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Sessione scaduta o non valida']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];

/* Query aggiornata per ecommerce_libri:
   - Prendiamo i dati dal CARRELLO
   - JOIN con PRODOTTO per i dettagli
   - LEFT JOIN con IMMAGINE_PRODOTTO per la foto
   - LEFT JOIN con PACCHETTO per vedere se c'è uno sconto attivo
*/
$sql = "SELECT c.id_prodotto, 
               c.quantita_prodotto as QuantitaNelCarrello,
               p.nome as Titolo, 
               p.prezzo as PrezzoBase, 
               p.quantita_disponibile as QuantitaDisp,
               img.url as URLfoto,
               pk.sconto as PercentualeSconto
        FROM CARRELLO c
        JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
        LEFT JOIN IMMAGINE_PRODOTTO img ON p.id_prodotto = img.id_prodotto
        LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
        WHERE c.username = ? AND p.attivo = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idUtente);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$totaleGenerale = 0;

while ($row = $res->fetch_assoc()) {
    $prezzoBase = (float)$row['PrezzoBase'];
    $sconto = (float)($row['PercentualeSconto'] ?? 0);

    // Calcolo del prezzo scontato (se presente)
    $prezzoEffettivo = $prezzoBase - ($prezzoBase * ($sconto / 100));
    
    // Calcolo del subtotale per questa riga
    $subtotale = $prezzoEffettivo * (int)$row['QuantitaNelCarrello'];
    $totaleGenerale += $subtotale;

    // Arricchiamo l'array con i dati calcolati per il frontend
    $row['PrezzoEffettivo'] = round($prezzoEffettivo, 2);
    $row['Subtotale'] = round($subtotale, 2);
    $row['ScontoApplicato'] = $sconto;

    $items[] = $row;
}

echo json_encode([
    'status' => 'ok',
    'prodotti' => $items,
    'totale' => round($totaleGenerale, 2)
]);