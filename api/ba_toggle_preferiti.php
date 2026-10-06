<?php
/* Aggiunge il prodotto ai preferiti se non c'è, altrimenti lo toglie. */
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');
$username = richiediLogin('cliente');

$id = intero($_POST['idProdotto'] ?? null, 1, PHP_INT_MAX);
if (!$id) errore('Prodotto non valido.');

$stmt = $conn->prepare("DELETE FROM preferiti WHERE username = ? AND id_prodotto = ?");
$stmt->bind_param("si", $username, $id);
$stmt->execute();
$rimosso = $stmt->affected_rows > 0;
$stmt->close();

if ($rimosso) {
    ok(['action' => 'removed']);
}

$stmt = $conn->prepare("SELECT 1 FROM prodotto WHERE id_prodotto = ? AND attivo = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) errore('Prodotto non disponibile.');
$stmt->close();

$stmt = $conn->prepare("INSERT INTO preferiti (username, id_prodotto, data_aggiunta) VALUES (?, ?, CURRENT_DATE)");
$stmt->bind_param("si", $username, $id);
$stmt->execute();
$stmt->close();

ok(['action' => 'added']);
