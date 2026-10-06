<?php
/* Elimina un pacchetto del venditore; i prodotti restano in vendita (FK con ON DELETE SET NULL). */
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');
$username = richiediLogin('venditore');

$id = intero($_POST['id_pacchetto'] ?? null, 1, PHP_INT_MAX);
if (!$id) errore('Pacchetto non valido.');

$stmt = $conn->prepare("DELETE FROM pacchetto WHERE id_pacchetto = ? AND username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$eliminati = $stmt->affected_rows;
$stmt->close();

if ($eliminati === 0) errore('Pacchetto non trovato.');
ok();
