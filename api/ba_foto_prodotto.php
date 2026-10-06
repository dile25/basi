<?php
/*
 * Foto di un prodotto del venditore.
 * GET  action=list&id_prodotto     elenco {id_foto, url}
 * POST action=delete&id_foto       elimina una foto (riga nel DB e file su disco)
 */
require_once __DIR__ . '/comune.php';
$username = richiediLogin('venditore');
$action   = $_REQUEST['action'] ?? '';

if ($action === 'list') {
    $idProdotto = intero($_GET['id_prodotto'] ?? null, 1, PHP_INT_MAX);
    if (!$idProdotto) errore('Prodotto non valido.');
    $stmt = $conn->prepare(
        "SELECT i.id_immagine_prodotto AS id_foto, i.url
         FROM immagine_prodotto i
         JOIN prodotto p ON p.id_prodotto = i.id_prodotto
         WHERE i.id_prodotto = ? AND p.username = ?
         ORDER BY i.id_immagine_prodotto"
    );
    $stmt->bind_param("is", $idProdotto, $username);
    $stmt->execute();
    $foto = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    ok(['foto' => $foto]);
}

if ($action === 'delete') {
    richiediMetodo('POST');
    $idFoto = intero($_POST['id_foto'] ?? null, 1, PHP_INT_MAX);
    if (!$idFoto) errore('Foto non valida.');

    $stmt = $conn->prepare(
        "SELECT i.url FROM immagine_prodotto i
         JOIN prodotto p ON p.id_prodotto = i.id_prodotto
         WHERE i.id_immagine_prodotto = ? AND p.username = ?"
    );
    $stmt->bind_param("is", $idFoto, $username);
    $stmt->execute();
    $foto = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$foto) errore('Foto non trovata.');

    $stmt = $conn->prepare("DELETE FROM immagine_prodotto WHERE id_immagine_prodotto = ?");
    $stmt->bind_param("i", $idFoto);
    $stmt->execute();
    $stmt->close();

    eliminaImmagine($foto['url']);
    ok();
}

errore('Azione non valida.');
