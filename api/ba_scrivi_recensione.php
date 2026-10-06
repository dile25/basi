<?php
/*
 * Crea o modifica (id_recensione > 0) la recensione del cliente su un prodotto.
 * Si può recensire solo un prodotto acquistato in un ordine non annullato.
 * Foto facoltativa: in modifica, una nuova foto sostituisce la precedente.
 */
require_once __DIR__ . '/comune.php';
richiediMetodo('POST');
$username = richiediLogin('cliente');

$idProdotto   = intero($_POST['idProdotto'] ?? null, 1, PHP_INT_MAX);
$idRecensione = intero($_POST['id_recensione'] ?? 0, 0, PHP_INT_MAX) ?? 0;
$voto         = intero($_POST['voto'] ?? null, 1, 5);
if (!$idProdotto)  errore('Prodotto non valido.');
if ($voto === null) errore('Il voto deve essere da 1 a 5 stelle.');
$commento = testo($_POST, 'commento', 3, 2000, 'Commento');

if (!haAcquistato($conn, $username, $idProdotto)) {
    errore('Puoi recensire solo i prodotti che hai acquistato.');
}

$fotoNuova = null;
if (isset($_FILES['fotoRecensione']) && $_FILES['fotoRecensione']['error'] !== UPLOAD_ERR_NO_FILE) {
    $fotoNuova = salvaImmagine($_FILES['fotoRecensione'], 'recensioni');
}

$fotoVecchie = [];
$conn->begin_transaction();
try {
    if ($idRecensione > 0) {
        // MODIFICA: solo la propria recensione su quel prodotto
        $stmt = $conn->prepare(
            "SELECT id_recensione FROM recensione WHERE id_recensione = ? AND username = ? AND id_prodotto = ?"
        );
        $stmt->bind_param("isi", $idRecensione, $username, $idProdotto);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) throw new ErroreUtente('Recensione non trovata.');
        $stmt->close();

        $stmt = $conn->prepare("UPDATE recensione SET valutazione = ?, testo = ?, data = CURRENT_DATE WHERE id_recensione = ?");
        $stmt->bind_param("isi", $voto, $commento, $idRecensione);
        $stmt->execute();
        $stmt->close();

        if ($fotoNuova) {
            $stmt = $conn->prepare("SELECT url FROM immagine_recensione WHERE id_recensione = ?");
            $stmt->bind_param("i", $idRecensione);
            $stmt->execute();
            $fotoVecchie = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'url');
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM immagine_recensione WHERE id_recensione = ?");
            $stmt->bind_param("i", $idRecensione);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // NUOVA: una sola recensione per prodotto (vincolo UNIQUE nel DB)
        $stmt = $conn->prepare("SELECT 1 FROM recensione WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("si", $username, $idProdotto);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) throw new ErroreUtente('Hai già recensito questo prodotto: puoi modificare la tua recensione.');
        $stmt->close();

        $stmt = $conn->prepare(
            "INSERT INTO recensione (username, id_prodotto, valutazione, testo, data) VALUES (?, ?, ?, ?, CURRENT_DATE)"
        );
        $stmt->bind_param("siis", $username, $idProdotto, $voto, $commento);
        $stmt->execute();
        $idRecensione = $conn->insert_id;
        $stmt->close();
    }

    if ($fotoNuova) {
        $alt = 'Foto della recensione di ' . $username;
        $stmt = $conn->prepare("INSERT INTO immagine_recensione (id_recensione, url, alt_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $idRecensione, $fotoNuova, $alt);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    eliminaImmagine($fotoNuova);   // il file salvato non serve più
    throw $e;
}

foreach ($fotoVecchie as $url) eliminaImmagine($url);
ok();
