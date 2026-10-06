<?php
/*
 * Nuovo prodotto con 1-5 foto, categoria e (facoltativo) pacchetto.
 * Tutto in una transazione: se una foto non è valida non resta nulla a metà.
 */
require_once __DIR__ . '/comune.php';
require_once __DIR__ . '/funzioni_prodotto.php';
richiediMetodo('POST');
$username = richiediLogin('venditore');

$dati = validaProdotto($conn, $_POST);

$files = elencoFile('foto');
if (count($files) < 1 || count($files) > MAX_FOTO_PRODOTTO) {
    errore('Carica da 1 a ' . MAX_FOTO_PRODOTTO . ' foto.');
}

// ===== Pacchetto (facoltativo) =====
$idPacchetto = null;
$nuovoPacchetto = null;
if (!empty($_POST['abilita_pacchetto'])) {
    $esistente = intero($_POST['id_pacchetto_esistente'] ?? '', 1, PHP_INT_MAX);
    if ($esistente) {
        if (!pacchettoDelVenditore($conn, $esistente, $username)) errore('Pacchetto non valido.');
        $idPacchetto = $esistente;
    } else {
        $sconto = intero($_POST['sconto_pacchetto'] ?? null, 1, 90);
        if ($sconto === null) errore('Lo sconto del pacchetto deve essere tra 1 e 90%.');
        $altri = array_filter(array_map(fn($v) => intero($v, 1, PHP_INT_MAX), (array)($_POST['libri_pacchetto'] ?? [])));
        $nuovoPacchetto = [
            'nome'   => testo($_POST, 'nome_pacchetto', 2, 100, 'Nome del pacchetto'),
            'sconto' => $sconto,
            'altri'  => array_values(array_unique($altri)),
        ];
    }
}

$fotoSalvate = [];
$conn->begin_transaction();
try {
    if ($nuovoPacchetto) {
        $stmt = $conn->prepare("INSERT INTO pacchetto (username, nome, sconto) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $username, $nuovoPacchetto['nome'], $nuovoPacchetto['sconto']);
        $stmt->execute();
        $idPacchetto = $conn->insert_id;
        $stmt->close();

        // Altri prodotti del venditore da includere (solo i suoi, solo attivi)
        $stmt = $conn->prepare("UPDATE prodotto SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ? AND attivo = 1");
        foreach ($nuovoPacchetto['altri'] as $idAltro) {
            $stmt->bind_param("iis", $idPacchetto, $idAltro, $username);
            $stmt->execute();
        }
        $stmt->close();
    }

    $stmt = $conn->prepare(
        "INSERT INTO prodotto (username, id_pacchetto, nome, autore, descrizione, prezzo, quantita_disponibile, data_inserimento)
         VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)"
    );
    $stmt->bind_param("sisssdi", $username, $idPacchetto, $dati['nome'], $dati['autore'],
                      $dati['descrizione'], $dati['prezzo'], $dati['quantita']);
    $stmt->execute();
    $idProdotto = $conn->insert_id;
    $stmt->close();

    salvaCategoria($conn, $idProdotto, $dati['categoria']);
    salvaFotoProdotto($conn, $idProdotto, $dati['nome'], $files, $fotoSalvate);

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    foreach ($fotoSalvate as $url) eliminaImmagine($url);
    throw $e;
}

ok(['idProdotto' => $idProdotto]);
