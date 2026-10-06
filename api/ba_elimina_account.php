<?php
/*
 * Eliminazione account (soft delete: utente.attivo = 0).
 * Gli ordini restano nello storico di clienti e venditori; i prodotti di un
 * venditore vengono tolti dalla vendita ma non cancellati, perché compaiono negli ordini.
 */
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');
$username = richiediLogin();
$tipo     = $_SESSION['tipoUtente'];
$input    = leggiJson();

if (trim((string)($input['conferma'] ?? '')) !== $username) {
    errore('Per confermare scrivi esattamente il tuo username.');
}

$conn->begin_transaction();
try {
    if ($tipo === 'venditore') {
        // Prodotti fuori vendita e tolti da carrelli e preferiti dei clienti
        $stmt = $conn->prepare(
            "DELETE c FROM carrello c JOIN prodotto p ON p.id_prodotto = c.id_prodotto WHERE p.username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare(
            "DELETE f FROM preferiti f JOIN prodotto p ON p.id_prodotto = f.id_prodotto WHERE p.username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE prodotto SET attivo = 0, id_pacchetto = NULL WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM pacchetto WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("DELETE FROM carrello WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM preferiti WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("UPDATE utente SET attivo = 0 WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    throw $e;
}

$_SESSION = [];
session_destroy();
ok();
