<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$cat = $_GET['cat'] ?? '';
$idUtente = $_SESSION['IdUtente'] ?? null; // Recuperiamo l'utente loggato se esiste

/* MODIFICA: Aggiungiamo IF(pref.id_preferiti IS NULL, 0, 1) as isPreferito
   per sapere se l'utente attuale ha il libro tra i preferiti.
*/
$sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.quantita_disponibile, p.username as venditore,
               img.url as URLfoto,
               AVG(r.valutazione) as MediaVoto,
               COUNT(DISTINCT r.id_recensione) as NumRecensioni,
               pk.sconto as ScontoPacchetto,
               IF(pref.id_preferiti IS NULL, 0, 1) as isPreferito
        FROM PRODOTTO p
        LEFT JOIN IMMAGINE_PRODOTTO img ON p.id_prodotto = img.id_prodotto
        LEFT JOIN RECENSIONE r ON p.id_prodotto = r.id_prodotto
        LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
        LEFT JOIN PREFERITI pref ON p.id_prodotto = pref.id_prodotto AND pref.username = ?
        WHERE (p.nome LIKE ? OR p.descrizione LIKE ?)";

// Prepariamo i parametri
$searchTerm = "%$query%";
// Il primo parametro è lo username per la JOIN dei preferiti, gli altri due per il LIKE
$params = [$idUtente, $searchTerm, $searchTerm];
$types = "sss";

// Filtro Categoria
if (!empty($cat)) {
    $sql .= " AND p.id_prodotto IN (SELECT id_prodotto FROM DESCRIVE WHERE nome_categoria = ?)";
    $types .= "s";
    $params[] = $cat;
}

$sql .= " GROUP BY p.id_prodotto ORDER BY p.id_prodotto DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$prodotti = [];
while ($row = $res->fetch_assoc()) {
    $row['MediaVoto'] = $row['MediaVoto'] ? round((float)$row['MediaVoto'], 1) : 0;
    
    $prezzoOriginale = (float)$row['prezzo'];
    $sconto = (float)($row['ScontoPacchetto'] ?? 0);
    
    if ($sconto > 0) {
        $row['PrezzoScontato'] = round($prezzoOriginale - ($prezzoOriginale * ($sconto / 100)), 2);
    } else {
        $row['PrezzoScontato'] = $prezzoOriginale;
    }

    $prodotti[] = $row;
}

echo json_encode(['status' => 'ok', 'prodotti' => $prodotti]);