<?php
require_once __DIR__ . '/comune.php';

$id = intero($_GET['id'] ?? null, 1, PHP_INT_MAX);
if (!$id) errore('Prodotto non valido.');

$stmt = $conn->prepare(
    "SELECT r.id_recensione, r.username, r.valutazione, r.testo, r.data, u.attivo,
            (SELECT ir.url FROM immagine_recensione ir
              WHERE ir.id_recensione = r.id_recensione LIMIT 1) AS foto
     FROM recensione r
     JOIN utente u ON u.username = r.username
     WHERE r.id_prodotto = ?
     ORDER BY r.data DESC, r.id_recensione DESC"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

$recensioni = [];
$giaRecensito = false;
$utente = $_SESSION['IdUtente'] ?? '';
while ($r = $res->fetch_assoc()) {
    if ($r['username'] === $utente) $giaRecensito = true;
    if ((int)$r['attivo'] === 0) $r['username'] = 'utente eliminato';
    unset($r['attivo']);
    $r['data'] = date('d/m/Y', strtotime($r['data']));
    $recensioni[] = $r;
}
$stmt->close();

// Il bottone "Scrivi una recensione" compare solo a chi ha comprato il prodotto
// e non lo ha ancora recensito
$puoRecensire = ($_SESSION['tipoUtente'] ?? '') === 'cliente'
    && !$giaRecensito
    && haAcquistato($conn, $utente, $id);

ok(['recensioni' => $recensioni, 'puoRecensire' => $puoRecensire]);
