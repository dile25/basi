<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');
$idUtente = $_SESSION['IdUtente'] ?? '';

// Estraiamo autori e prodotti correnti nel carrello per escluderli ed effettuare la correlazione
$sub = $conn->prepare("SELECT id_prodotto FROM CARRELLO WHERE username = ?");
$sub->bind_param("s", $idUtente);
$sub->execute();
$resSub = $sub->get_result();
$esclusi = [0];
while($r = $resSub->fetch_assoc()) { $esclusi[] = $r['id_prodotto']; }
$stringEsclusi = implode(",", $esclusi);

// Query consigliati per affinità casuale sul catalogo escludendo i già acquistati
$sql = "SELECT id_prodotto, nome, prezzo, (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as Foto FROM PRODOTTO p WHERE id_prodotto NOT IN ($stringEsclusi) LIMIT 3";
$res = $conn->query($sql);
$consigliati = [];
while($row = $res->fetch_assoc()) { $consigliati[] = $row; }
echo json_encode(['status' => 'ok', 'consigliati' => $consigliati]);
?>