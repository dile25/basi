<?php
/* Modifica di un prodotto del venditore; le nuove foto si aggiungono a quelle esistenti. */
require_once __DIR__ . '/comune.php';
require_once __DIR__ . '/funzioni_prodotto.php';
richiediMetodo('POST');
$username = richiediLogin('venditore');

$idProdotto = intero($_POST['id_prodotto'] ?? null, 1, PHP_INT_MAX);
if (!$idProdotto) errore('Prodotto non valido.');

$stmt = $conn->prepare(
    "SELECT (SELECT COUNT(*) FROM immagine_prodotto i WHERE i.id_prodotto = p.id_prodotto) AS n_foto
     FROM prodotto p WHERE p.id_prodotto = ? AND p.username = ? AND p.attivo = 1"
);
$stmt->bind_param("is", $idProdotto, $username);
$stmt->execute();
$prodotto = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$prodotto) errore('Prodotto non trovato.');

$dati = validaProdotto($conn, $_POST);

$idPacchetto = intero($_POST['id_pacchetto'] ?? '', 1, PHP_INT_MAX);   // null = nessun pacchetto
if ($idPacchetto && !pacchettoDelVenditore($conn, $idPacchetto, $username)) {
    errore('Pacchetto non valido.');
}

$files = elencoFile('foto');
if ((int)$prodotto['n_foto'] + count($files) > MAX_FOTO_PRODOTTO) {
    errore('Un prodotto può avere al massimo ' . MAX_FOTO_PRODOTTO . ' foto: eliminane qualcuna prima di aggiungerne altre.');
}

$fotoSalvate = [];
$conn->begin_transaction();
try {
    $stmt = $conn->prepare(
        "UPDATE prodotto
         SET nome = ?, autore = ?, descrizione = ?, prezzo = ?, quantita_disponibile = ?, id_pacchetto = ?
         WHERE id_prodotto = ? AND username = ?"
    );
    $stmt->bind_param("sssdiiis", $dati['nome'], $dati['autore'], $dati['descrizione'], $dati['prezzo'],
                      $dati['quantita'], $idPacchetto, $idProdotto, $username);
    $stmt->execute();
    $stmt->close();

    salvaCategoria($conn, $idProdotto, $dati['categoria']);
    salvaFotoProdotto($conn, $idProdotto, $dati['nome'], $files, $fotoSalvate);

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    foreach ($fotoSalvate as $url) eliminaImmagine($url);
    throw $e;
}

ok();
