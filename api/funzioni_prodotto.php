<?php
/*
 * funzioni_prodotto.php — validazione e foto dei prodotti,
 * riusate da ba_aggiungi_libro.php e ba_modifica_libro.php.
 */

const MAX_FOTO_PRODOTTO = 5;

/* Valida i campi del prodotto inviati dal form e restituisce i valori puliti */
function validaProdotto(mysqli $conn, array $post): array {
    $dati = [
        'nome'        => testo($post, 'nome', 2, 150, 'Titolo'),
        'autore'      => testo($post, 'autore', 2, 100, 'Autore'),
        'descrizione' => testo($post, 'descrizione', 10, 2000, 'Descrizione'),
        'prezzo'      => prezzo($post['prezzo'] ?? ''),
        'quantita'    => intero($post['quantita'] ?? null, 0, 9999),
    ];
    if ($dati['prezzo'] === null)   throw new ErroreUtente('Il prezzo deve essere tra 0,01 e 9999,99 €.');
    if ($dati['quantita'] === null) throw new ErroreUtente('Le copie devono essere un numero intero tra 0 e 9999.');
    $dati['categoria'] = categoriaScelta($conn, trim($post['categoria'] ?? ''), trim($post['sottocategoria'] ?? ''));
    return $dati;
}

/* La categoria deve essere principale; la sottocategoria, se c'è, deve essere sua figlia.
   Nel prodotto si salva la più specifica (il padre si ricava dalla gerarchia). */
function categoriaScelta(mysqli $conn, string $categoria, string $sottocategoria): string {
    $stmt = $conn->prepare("SELECT 1 FROM categoria WHERE nome_categoria = ? AND nome_categoria_padre IS NULL");
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) throw new ErroreUtente('Scegli una categoria valida.');
    $stmt->close();

    if ($sottocategoria === '') return $categoria;

    $stmt = $conn->prepare("SELECT 1 FROM categoria WHERE nome_categoria = ? AND nome_categoria_padre = ?");
    $stmt->bind_param("ss", $sottocategoria, $categoria);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) throw new ErroreUtente('La sottocategoria non appartiene alla categoria scelta.');
    $stmt->close();
    return $sottocategoria;
}

/* Sostituisce le categorie del prodotto con quella scelta */
function salvaCategoria(mysqli $conn, int $idProdotto, string $categoria): void {
    $stmt = $conn->prepare("DELETE FROM descrive WHERE id_prodotto = ?");
    $stmt->bind_param("i", $idProdotto);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO descrive (id_prodotto, nome_categoria) VALUES (?, ?)");
    $stmt->bind_param("is", $idProdotto, $categoria);
    $stmt->execute();
    $stmt->close();
}

/* Il pacchetto esiste ed è del venditore? */
function pacchettoDelVenditore(mysqli $conn, int $idPacchetto, string $username): bool {
    $stmt = $conn->prepare("SELECT 1 FROM pacchetto WHERE id_pacchetto = ? AND username = ?");
    $stmt->bind_param("is", $idPacchetto, $username);
    $stmt->execute();
    $esiste = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $esiste;
}

/* Salva su disco e nel DB le foto caricate. Gli URL salvati vengono aggiunti a
   $salvate, così in caso di errore il chiamante può cancellarli. */
function salvaFotoProdotto(mysqli $conn, int $idProdotto, string $nome, array $files, array &$salvate): void {
    $stmt = $conn->prepare("INSERT INTO immagine_prodotto (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
    $alt = mb_substr('Foto di ' . $nome, 0, 150);
    foreach ($files as $file) {
        $url = salvaImmagine($file, 'prodotti');
        $salvate[] = $url;
        $stmt->bind_param("iss", $idProdotto, $url, $alt);
        $stmt->execute();
    }
    $stmt->close();
}
