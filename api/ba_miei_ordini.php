<?php
/*
 * Ordini del cliente.
 * GET  stato=''|'Pagato'|'Annullato'      elenco
 * POST action=annulla, id_ordine          annulla un ordine 'Pagato' (UPDATE dello stato)
 */
require_once __DIR__ . '/comune.php';
$username = richiediLogin('cliente');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') !== 'annulla') errore('Azione non valida.');
    $idOrdine = intero($_POST['id_ordine'] ?? null, 1, PHP_INT_MAX);
    if (!$idOrdine) errore('Ordine non valido.');

    $conn->begin_transaction();
    try {
        // Lock dell'ordine: due annullamenti contemporanei non restituiscono due volte la merce
        $stmt = $conn->prepare("SELECT stato, id_pagamento FROM ordine WHERE id_ordine = ? AND username = ? FOR UPDATE");
        $stmt->bind_param("is", $idOrdine, $username);
        $stmt->execute();
        $ordine = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$ordine) throw new ErroreUtente('Ordine non trovato.');
        if ($ordine['stato'] !== 'Pagato') throw new ErroreUtente('Questo ordine è già annullato.');

        // Le copie tornano disponibili
        $stmt = $conn->prepare(
            "UPDATE prodotto p
             JOIN incluso_in ii ON ii.id_prodotto = p.id_prodotto
             SET p.quantita_disponibile = p.quantita_disponibile + ii.quantita_prodotto
             WHERE ii.id_ordine = ?"
        );
        $stmt->bind_param("i", $idOrdine);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE ordine SET stato = 'Annullato' WHERE id_ordine = ?");
        $stmt->bind_param("i", $idOrdine);
        $stmt->execute();
        $stmt->close();

        if ($ordine['id_pagamento']) {
            $stmt = $conn->prepare("UPDATE pagamento SET stato = 'Rimborsato' WHERE id_pagamento = ?");
            $stmt->bind_param("i", $ordine['id_pagamento']);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
    ok();
}

// ===== Elenco =====
$stato = $_GET['stato'] ?? '';
if (!in_array($stato, ['', 'Pagato', 'Annullato'], true)) errore('Filtro non valido.');

$sql = "SELECT o.id_ordine, o.data, o.totale, o.stato,
               p.id_prodotto, p.nome, p.username AS venditore,
               ii.quantita_prodotto, ii.prezzo_unitario,
               (SELECT i.url FROM immagine_prodotto i WHERE i.id_prodotto = p.id_prodotto
                 ORDER BY i.id_immagine_prodotto LIMIT 1) AS foto,
               r.valutazione AS mio_voto
        FROM ordine o
        JOIN incluso_in ii ON ii.id_ordine = o.id_ordine
        JOIN prodotto p    ON p.id_prodotto = ii.id_prodotto
        LEFT JOIN recensione r ON r.id_prodotto = p.id_prodotto AND r.username = o.username
        WHERE o.username = ?" . ($stato !== '' ? " AND o.stato = ?" : "") . "
        ORDER BY o.data DESC, o.id_ordine DESC, p.nome";

$stmt = $conn->prepare($sql);
if ($stato !== '') {
    $stmt->bind_param("ss", $username, $stato);
} else {
    $stmt->bind_param("s", $username);
}
$stmt->execute();
$res = $stmt->get_result();

$ordini = [];
while ($r = $res->fetch_assoc()) {
    $id = $r['id_ordine'];
    if (!isset($ordini[$id])) {
        $ordini[$id] = [
            'id_ordine' => (int)$id,
            'data'      => date('d/m/Y', strtotime($r['data'])),
            'totale'    => $r['totale'],
            'stato'     => $r['stato'],
            'libri'     => [],
        ];
    }
    $ordini[$id]['libri'][] = [
        'id_prodotto'     => (int)$r['id_prodotto'],
        'nome'            => $r['nome'],
        'venditore'       => $r['venditore'],
        'quantita'        => (int)$r['quantita_prodotto'],
        'prezzo_acquisto' => $r['prezzo_unitario'],
        'foto'            => $r['foto'] ?? 'img/default.jpg',
        'gia_recensito'   => $r['mio_voto'] !== null,
        'voto_utente'     => $r['mio_voto'],
    ];
}
$stmt->close();

ok(['ordini' => array_values($ordini)]);
