<?php
/*
 * POST action=update          id_pacchetto, nome, sconto
 * POST action=add_product     id_pacchetto, id_prodotto
 * POST action=remove_product  id_pacchetto, id_prodotto
 */
require_once __DIR__ . '/comune.php';
require_once __DIR__ . '/funzioni_prodotto.php';
richiediMetodo('POST');
$username = richiediLogin('venditore');

$idPacchetto = intero($_POST['id_pacchetto'] ?? null, 1, PHP_INT_MAX);
if (!$idPacchetto || !pacchettoDelVenditore($conn, $idPacchetto, $username)) {
    errore('Pacchetto non trovato.');
}
$action = $_POST['action'] ?? '';

if ($action === 'update') {
    $nome   = testo($_POST, 'nome', 2, 100, 'Nome del pacchetto');
    $sconto = intero($_POST['sconto'] ?? null, 1, 90);
    if ($sconto === null) errore('Lo sconto deve essere tra 1 e 90%.');
    $stmt = $conn->prepare("UPDATE pacchetto SET nome = ?, sconto = ? WHERE id_pacchetto = ?");
    $stmt->bind_param("sii", $nome, $sconto, $idPacchetto);
    $stmt->execute();
    $stmt->close();
    ok();
}

if ($action === 'add_product' || $action === 'remove_product') {
    $idProdotto = intero($_POST['id_prodotto'] ?? null, 1, PHP_INT_MAX);
    if (!$idProdotto) errore('Prodotto non valido.');

    if ($action === 'add_product') {
        // Un prodotto appartiene al massimo a un pacchetto: si aggiungono solo quelli liberi
        $stmt = $conn->prepare(
            "UPDATE prodotto SET id_pacchetto = ?
             WHERE id_prodotto = ? AND username = ? AND attivo = 1 AND id_pacchetto IS NULL"
        );
        $stmt->bind_param("iis", $idPacchetto, $idProdotto, $username);
    } else {
        $stmt = $conn->prepare(
            "UPDATE prodotto SET id_pacchetto = NULL WHERE id_prodotto = ? AND username = ? AND id_pacchetto = ?"
        );
        $stmt->bind_param("isi", $idProdotto, $username, $idPacchetto);
    }
    $stmt->execute();
    $modificati = $stmt->affected_rows;
    $stmt->close();
    if ($modificati === 0) errore('Operazione non possibile su questo prodotto.');
    ok();
}

errore('Azione non valida.');
