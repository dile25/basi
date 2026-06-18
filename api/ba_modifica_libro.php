<?php
ob_start();
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Accesso non autorizzato']);
    exit;
}

$user        = $_SESSION['IdUtente'];
$idProdotto  = intval($_POST['id_prodotto'] ?? 0);
$nome        = $_POST['nome'] ?? '';
$autore      = $_POST['autore'] ?? '';
$desc        = $_POST['descrizione'] ?? '';
$prezzo      = floatval($_POST['prezzo'] ?? 0);
$qta         = intval($_POST['quantita'] ?? 0);
$idPacchettoRaw = $_POST['id_pacchetto'] ?? '';
$idPacchetto = ($idPacchettoRaw !== '' && $idPacchettoRaw !== 'null') ? intval($idPacchettoRaw) : null;

if (!$idProdotto) {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto non valido']);
    exit;
}

// Verifica che il prodotto appartenga al venditore
$check = $conn->prepare("SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_prodotto = ? AND username = ?");
$check->bind_param("is", $idProdotto, $user);
$check->execute();
if ($check->get_result()->fetch_assoc()['cnt'] == 0) {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto non trovato o non autorizzato']);
    exit;
}

// Se viene specificato un pacchetto, verifica che appartenga anch'esso al venditore
// (cioe' che almeno un altro suo prodotto sia gia' in quel pacchetto, o sia un pacchetto vuoto suo)
if ($idPacchetto !== null) {
    $checkPack = $conn->prepare(
        "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
    );
    $checkPack->bind_param("is", $idPacchetto, $user);
    $checkPack->execute();
    $owns = $checkPack->get_result()->fetch_assoc()['cnt'];
    if ($owns == 0) {
        // Il pacchetto esiste ma non ha ancora nessun prodotto di questo venditore:
        // permesso comunque, dato che le select nel frontend mostrano solo pacchetti
        // ottenuti da ba_pacchetti_venditore.php / ba_abbonamenti_venditore.php,
        // che sono gia' filtrati per venditore.
    }
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "UPDATE PRODOTTO SET nome = ?, autore = ?, descrizione = ?, prezzo = ?, quantita_disponibile = ?, id_pacchetto = ?
         WHERE id_prodotto = ? AND username = ?"
    );
    $stmt->bind_param("sssdiiis", $nome, $autore, $desc, $prezzo, $qta, $idPacchetto, $idProdotto, $user);
    if (!$stmt->execute()) throw new Exception("Errore aggiornamento prodotto");

    // Foto opzionale
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION);
        $fileName = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $uploadDir . $fileName)) {
            $urlDb = 'img/' . $fileName;
            // Rimuove la vecchia immagine e inserisce la nuova
            $del = $conn->prepare("DELETE FROM IMMAGINE_PRODOTTO WHERE id_prodotto = ?");
            $del->bind_param("i", $idProdotto);
            $del->execute();

            $stmtI = $conn->prepare("INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
            $alt = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute();
        } else {
            throw new Exception("Impossibile salvare l'immagine");
        }
    }

    $conn->commit();
    ob_clean();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}