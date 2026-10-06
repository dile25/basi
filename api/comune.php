<?php
/*
 * comune.php — incluso con require_once da tutte le API (livello di business).
 * - avvia buffer di output e sessione
 * - include la connessione al database (livello dati)
 * - imposta la risposta in JSON
 * - fornisce funzioni riutilizzabili: risposte, controllo accessi, validazione, upload immagini
 */

ob_start();   // nessun output accidentale (warning, spazi) può rompere il JSON
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

/* Errore causato da un input dell'utente: il messaggio può essere mostrato. */
class ErroreUtente extends Exception {}

/* Qualsiasi eccezione non gestita diventa una risposta JSON:
   - ErroreUtente: messaggio mostrato all'utente
   - altre (es. errori MySQL): messaggio generico, dettagli solo nel log del server */
set_exception_handler(function (Throwable $e) {
    if ($e instanceof ErroreUtente) {
        rispondi(['status' => 'error', 'msg' => $e->getMessage()]);
    }
    error_log('[floris_valenti] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    rispondi(['status' => 'error', 'msg' => 'Si è verificato un errore imprevisto. Riprova più tardi.'], 500);
});

/* ===================== RISPOSTE ===================== */

function rispondi(array $dati, int $codiceHttp = 200): void {
    ob_clean();                 // scarta qualsiasi output prodotto prima
    http_response_code($codiceHttp);
    echo json_encode($dati);
    exit;
}

function ok(array $extra = []): void {
    rispondi(['status' => 'ok'] + $extra);
}

/* Errori applicativi: HTTP 200 con status "error", così il frontend legge resp.msg */
function errore(string $msg): void {
    rispondi(['status' => 'error', 'msg' => $msg]);
}

/* ===================== ACCESSI ===================== */

function richiediMetodo(string $metodo): void {
    if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
        rispondi(['status' => 'error', 'msg' => 'Metodo non consentito.'], 405);
    }
}

/* Restituisce lo username dell'utente loggato; se serve, controlla anche il tipo */
function richiediLogin(?string $tipo = null): string {
    if (!isset($_SESSION['IdUtente'])) {
        errore('Accedi per continuare.');
    }
    if ($tipo !== null && ($_SESSION['tipoUtente'] ?? '') !== $tipo) {
        errore('Operazione non consentita per il tuo tipo di account.');
    }
    return $_SESSION['IdUtente'];
}

/* ===================== INPUT ===================== */

/* Corpo della richiesta in formato JSON (usato dalle API del profilo) */
function leggiJson(): array {
    $dati = json_decode(file_get_contents('php://input'), true);
    return is_array($dati) ? $dati : [];
}

/* Testo obbligatorio o facoltativo con lunghezza minima e massima */
function testo(array $fonte, string $chiave, int $min, int $max, string $etichetta, bool $obbligatorio = true): string {
    $valore = trim((string)($fonte[$chiave] ?? ''));
    if ($valore === '' && !$obbligatorio) return '';
    $lung = mb_strlen($valore);
    if ($lung < $min || $lung > $max) {
        throw new ErroreUtente("$etichetta: da $min a $max caratteri.");
    }
    return $valore;
}

/* Numero intero in un intervallo (typecasting + controllo), null se non valido */
function intero($valore, int $min, int $max): ?int {
    $n = filter_var($valore, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
    return $n === false ? null : $n;
}

/* Prezzo con al massimo 2 decimali, null se non valido */
function prezzo($valore): ?float {
    $valore = str_replace(',', '.', trim((string)$valore));
    if (!preg_match('/^\d{1,4}(\.\d{1,2})?$/', $valore)) return null;
    $n = (float)$valore;
    return ($n > 0 && $n <= 9999.99) ? $n : null;
}

/* ===================== IMMAGINI ===================== */

/* Trasforma $_FILES['campo'] con "multiple" in un elenco di file */
function elencoFile(string $campo): array {
    if (!isset($_FILES[$campo]) || !is_array($_FILES[$campo]['name'])) return [];
    $files = [];
    foreach ($_FILES[$campo]['name'] as $i => $nome) {
        if ($_FILES[$campo]['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        $files[] = [
            'name'     => $nome,
            'tmp_name' => $_FILES[$campo]['tmp_name'][$i],
            'error'    => $_FILES[$campo]['error'][$i],
            'size'     => $_FILES[$campo]['size'][$i],
        ];
    }
    return $files;
}

/* Controlla un file caricato (errore, peso, tipo reale) e lo salva in img/<cartella>/
   con un nome generato dal server. Restituisce l'URL relativo da salvare nel DB. */
function salvaImmagine(array $file, string $cartella): string {
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new ErroreUtente('Caricamento della foto non riuscito.');
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new ErroreUtente('Ogni foto può pesare al massimo 2 MB.');
    }
    // Il tipo si controlla sul contenuto del file, non sull'estensione dichiarata dal client
    $estensioni = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($estensioni[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new ErroreUtente('Le foto devono essere immagini JPG, PNG o WEBP.');
    }

    $dir = __DIR__ . '/../img/' . $cartella . '/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $nomeFile = bin2hex(random_bytes(8)) . '.' . $estensioni[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $nomeFile)) {
        throw new Exception('move_uploaded_file non riuscito');
    }
    return 'img/' . $cartella . '/' . $nomeFile;
}

/* Elimina dal disco un'immagine caricata (solo dentro img/, mai l'immagine predefinita) */
function eliminaImmagine(?string $url): void {
    if (!$url || strpos($url, 'img/') !== 0 || strpos($url, '..') !== false || $url === 'img/default.jpg') return;
    $percorso = __DIR__ . '/../' . $url;
    if (is_file($percorso)) {
        unlink($percorso);
    }
}

/* ===================== REGOLE DI DOMINIO ===================== */

/* Il cliente ha comprato il prodotto in un ordine non annullato? (serve per le recensioni) */
function haAcquistato(mysqli $conn, string $username, int $idProdotto): bool {
    $stmt = $conn->prepare(
        "SELECT 1 FROM ordine o
         JOIN incluso_in ii ON ii.id_ordine = o.id_ordine
         WHERE o.username = ? AND ii.id_prodotto = ? AND o.stato = 'Pagato'
         LIMIT 1"
    );
    $stmt->bind_param("si", $username, $idProdotto);
    $stmt->execute();
    $trovato = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $trovato;
}
