<?php
/*
 * Carrello del cliente.
 * GET  action=list                              righe con sconti e totale
 * POST action=add    idProdotto, quantita       aggiunge (o incrementa)
 * POST action=update idProdotto, qty            imposta la quantità
 * POST action=remove idProdotto                 rimuove
 */
require_once __DIR__ . '/comune.php';
require_once __DIR__ . '/funzioni_carrello.php';
$username = richiediLogin('cliente');
$action   = $_REQUEST['action'] ?? '';

/* Disponibilità di un prodotto in vendita, null se non esiste o non è attivo */
function disponibilita(mysqli $conn, int $idProdotto): ?int {
    $stmt = $conn->prepare("SELECT quantita_disponibile FROM prodotto WHERE id_prodotto = ? AND attivo = 1");
    $stmt->bind_param("i", $idProdotto);
    $stmt->execute();
    $riga = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $riga ? (int)$riga['quantita_disponibile'] : null;
}

/* Quantità già nel carrello (0 se il prodotto non c'è) */
function quantitaNelCarrello(mysqli $conn, string $username, int $idProdotto): int {
    $stmt = $conn->prepare("SELECT quantita_prodotto FROM carrello WHERE username = ? AND id_prodotto = ?");
    $stmt->bind_param("si", $username, $idProdotto);
    $stmt->execute();
    $riga = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $riga ? (int)$riga['quantita_prodotto'] : 0;
}

switch ($action) {

    case 'list':
        $rimossi = pulisciCarrello($conn, $username);
        $carrello = calcolaCarrello($conn, $username);
        ok([
            'prodotti'        => $carrello['prodotti'],
            'totaleCart'      => $carrello['totale'],
            'prodottiRimossi' => $rimossi,
        ]);
        break;

    case 'add':
        richiediMetodo('POST');
        $id  = intero($_POST['idProdotto'] ?? null, 1, PHP_INT_MAX);
        $qta = intero($_POST['quantita'] ?? 1, 1, 99);
        if (!$id || !$qta) errore('Dati non validi.');

        $disp = disponibilita($conn, $id);
        if ($disp === null) errore('Prodotto non disponibile.');
        $giaPresenti = quantitaNelCarrello($conn, $username, $id);
        if ($giaPresenti + $qta > $disp) {
            errore($disp === 0 ? 'Prodotto esaurito.' : "Sono disponibili solo $disp copie.");
        }

        if ($giaPresenti > 0) {
            $nuova = $giaPresenti + $qta;
            $stmt = $conn->prepare("UPDATE carrello SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
            $stmt->bind_param("isi", $nuova, $username, $id);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO carrello (username, id_prodotto, quantita_prodotto, data_creazione) VALUES (?, ?, ?, CURRENT_DATE)"
            );
            $stmt->bind_param("sii", $username, $id, $qta);
        }
        $stmt->execute();
        $stmt->close();
        ok();
        break;

    case 'update':
        richiediMetodo('POST');
        $id  = intero($_POST['idProdotto'] ?? null, 1, PHP_INT_MAX);
        $qta = intero($_POST['qty'] ?? null, 1, 99);
        if (!$id || !$qta) errore('Quantità non valida.');
        if (quantitaNelCarrello($conn, $username, $id) === 0) errore('Il prodotto non è nel carrello.');

        $disp = disponibilita($conn, $id) ?? 0;
        if ($qta > $disp) errore("Sono disponibili solo $disp copie.");

        $stmt = $conn->prepare("UPDATE carrello SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("isi", $qta, $username, $id);
        $stmt->execute();
        $stmt->close();
        ok();
        break;

    case 'remove':
        richiediMetodo('POST');
        $id = intero($_POST['idProdotto'] ?? null, 1, PHP_INT_MAX);
        if (!$id) errore('Prodotto non valido.');
        $stmt = $conn->prepare("DELETE FROM carrello WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
        $stmt->close();
        ok();
        break;

    default:
        errore('Azione non valida.');
}
