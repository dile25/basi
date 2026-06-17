<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['status' => 'error', 'msg' => 'ID non valido']); exit; }

$sql = "SELECT p.id_prodotto, p.nome, p.autore, p.descrizione, p.prezzo,
               p.quantita_disponibile, p.id_pacchetto, p.username as IdVenditore,
               (SELECT nome_categoria FROM DESCRIVE WHERE id_prodotto = p.id_prodotto LIMIT 1) as NomeCategoria,
               pk.nome as NomePacchetto,
               pk.sconto_2, pk.sconto_3, pk.sconto_tutti,
               pk.tipo_pacchetto as TipoPacchetto,
               pk.periodicita as Periodicita
        FROM PRODOTTO p
        LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
        WHERE p.id_prodotto = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$prodotto = $stmt->get_result()->fetch_assoc();

if (!$prodotto) { echo json_encode(['status' => 'error', 'msg' => 'Prodotto non trovato']); exit; }

// Foto
$stmtFoto = $conn->prepare("SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = ? ORDER BY id_immagine_prodotto ASC");
$stmtFoto->bind_param("i", $id);
$stmtFoto->execute();
$foto = [];
while ($f = $stmtFoto->get_result()->fetch_assoc()) $foto[] = $f['url'];

// Nome venditore
$nomeVenditore = $prodotto['IdVenditore'];

// Altri prodotti dello stesso pacchetto/abbonamento
$libriPacchetto = [];
if ($prodotto['id_pacchetto']) {
    $stmtPack = $conn->prepare(
        "SELECT p2.id_prodotto, p2.nome, p2.autore, p2.prezzo, p2.quantita_disponibile,
                (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p2.id_prodotto LIMIT 1) as foto
         FROM PRODOTTO p2
         WHERE p2.id_pacchetto = ? AND p2.id_prodotto != ?");
    $stmtPack->bind_param("ii", $prodotto['id_pacchetto'], $id);
    $stmtPack->execute();
    $resPack = $stmtPack->get_result();
    while ($r = $resPack->fetch_assoc()) $libriPacchetto[] = $r;
}

// Totale prodotti nel pacchetto (incluso questo)
$totalePacchetto = count($libriPacchetto) + 1;

echo json_encode([
    'status'   => 'ok',
    'dettagli' => [
        'IdProdotto'      => $prodotto['id_prodotto'],
        'NomeProdotto'    => $prodotto['nome'],
        'autore'          => $prodotto['autore'],
        'descrizione'     => $prodotto['descrizione'],
        'prezzo'          => $prodotto['prezzo'],
        'PrezzoScontato'  => $prodotto['prezzo'], // prezzo pieno — sconto solo nel carrello
        'ScontoPacchetto' => 0,                   // non mostrare sconto finché non aggiunto
        'NomePacchetto'   => $prodotto['NomePacchetto'] ?? '',
        'id_pacchetto'    => $prodotto['id_pacchetto'],
        'sconto_2'        => $prodotto['sconto_2'] ?? 10,
        'sconto_3'        => $prodotto['sconto_3'] ?? 20,
        'sconto_tutti'    => $prodotto['sconto_tutti'] ?? 30,
        'tipoPacchetto'   => $prodotto['TipoPacchetto'] ?? 'libro',
        'periodicita'     => $prodotto['Periodicita'] ?? null,
        'totalePacchetto' => $totalePacchetto,
        'QuantitaDisp'    => $prodotto['quantita_disponibile'],
        'IdVenditore'     => $prodotto['IdVenditore'],
        'NomeVenditore'   => $nomeVenditore,
        'NomeCategoria'   => $prodotto['NomeCategoria'] ?? 'Generale',
        'foto'            => $foto,
        'libriPacchetto'  => $libriPacchetto
    ]
]);