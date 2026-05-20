<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['status' => 'error', 'msg' => 'ID non valido']);
    exit;
}

// Dati prodotto con categoria, venditore, sconto pacchetto
$sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.quantita_disponibile, p.username as IdVenditore,
               (SELECT nome_categoria FROM DESCRIVE WHERE id_prodotto = p.id_prodotto LIMIT 1) as NomeCategoria,
               pk.sconto as ScontoPacchetto,
               ROUND(p.prezzo - (p.prezzo * COALESCE(pk.sconto,0) / 100), 2) as PrezzoScontato
        FROM PRODOTTO p
        LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
        WHERE p.id_prodotto = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$prodotto = $stmt->get_result()->fetch_assoc();

if (!$prodotto) {
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto non trovato']);
    exit;
}

// Foto
$stmtFoto = $conn->prepare("SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = ? ORDER BY id_immagine_prodotto ASC");
$stmtFoto->bind_param("i", $id);
$stmtFoto->execute();
$resFoto = $stmtFoto->get_result();
$foto = [];
while ($f = $resFoto->fetch_assoc()) $foto[] = $f['url'];

// Nome venditore (ragione sociale o username)
$stmtV = $conn->prepare("SELECT ragione_sociale FROM VENDITORE WHERE username = ?");
$stmtV->bind_param("s", $prodotto['IdVenditore']);
$stmtV->execute();
$venditore = $stmtV->get_result()->fetch_assoc();
$nomeVenditore = $venditore['ragione_sociale'] ?? $prodotto['IdVenditore'];

echo json_encode([
    'status' => 'ok',
    'dettagli' => [
        'IdProdotto'     => $prodotto['id_prodotto'],
        'NomeProdotto'   => $prodotto['nome'],
        'descrizione'    => $prodotto['descrizione'],
        'prezzo'         => $prodotto['prezzo'],
        'PrezzoScontato' => $prodotto['PrezzoScontato'],
        'ScontoPacchetto'=> $prodotto['ScontoPacchetto'] ?? 0,
        'QuantitaDisp'   => $prodotto['quantita_disponibile'],
        'IdVenditore'    => $prodotto['IdVenditore'],
        'NomeVenditore'  => $nomeVenditore,
        'NomeCategoria'  => $prodotto['NomeCategoria'] ?? 'Generale',
        'foto'           => $foto
    ]
]);