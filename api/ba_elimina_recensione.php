<?php
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');
$username = richiediLogin('cliente');

$id = intero($_POST['id_recensione'] ?? null, 1, PHP_INT_MAX);
if (!$id) errore('Recensione non valida.');

// Foto da cancellare dal disco (le righe si cancellano a cascata)
$stmt = $conn->prepare(
    "SELECT ir.url FROM immagine_recensione ir
     JOIN recensione r ON r.id_recensione = ir.id_recensione
     WHERE r.id_recensione = ? AND r.username = ?"
);
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$foto = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'url');
$stmt->close();

$stmt = $conn->prepare("DELETE FROM recensione WHERE id_recensione = ? AND username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$eliminate = $stmt->affected_rows;
$stmt->close();

if ($eliminate === 0) errore('Recensione non trovata.');

foreach ($foto as $url) eliminaImmagine($url);
ok();
