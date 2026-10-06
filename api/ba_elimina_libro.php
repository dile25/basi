<?php
/*
 * "Eliminazione" di un prodotto: viene tolto dalla vendita (attivo = 0) invece di
 * essere cancellato, perché le righe degli ordini già fatti devono restare nello storico.
 */
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');
$username = richiediLogin('venditore');

$id = intero($_POST['id_prodotto'] ?? null, 1, PHP_INT_MAX);
if (!$id) errore('Prodotto non valido.');

$conn->begin_transaction();
try {
    $stmt = $conn->prepare(
        "UPDATE prodotto SET attivo = 0, id_pacchetto = NULL WHERE id_prodotto = ? AND username = ? AND attivo = 1"
    );
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    if ($stmt->affected_rows === 0) throw new ErroreUtente('Prodotto non trovato.');
    $stmt->close();

    foreach (['carrello', 'preferiti'] as $tabella) {
        $stmt = $conn->prepare("DELETE FROM $tabella WHERE id_prodotto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    throw $e;
}

ok();
