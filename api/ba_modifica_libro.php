<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Accesso non autorizzato']);
    exit;
}

$user       = $_SESSION['IdUtente'];
$idProdotto = intval($_POST['id_prodotto'] ?? 0);
$nome       = trim($_POST['nome'] ?? '');
$autore     = trim($_POST['autore'] ?? '');
$desc       = trim($_POST['descrizione'] ?? '');
$prezzo     = floatval($_POST['prezzo'] ?? 0);
$qta        = intval($_POST['quantita'] ?? 0);
$idPacchettoRaw = $_POST['id_pacchetto'] ?? '';
$idPacchetto = ($idPacchettoRaw !== '' && $idPacchettoRaw !== 'null') ? intval($idPacchettoRaw) : null;

// -------------------------------------------------------------------------
// Validazione input
// -------------------------------------------------------------------------
$errori = [];
if (!$idProdotto)
    $errori[] = 'ID prodotto non valido.';
if (empty($nome))
    $errori[] = 'Il nome del prodotto è obbligatorio.';
if ($prezzo <= 0)
    $errori[] = 'Il prezzo deve essere maggiore di zero.';
if ($qta < 0)
    $errori[] = 'La quantità non può essere negativa.';

if (!empty($errori)) {
    echo json_encode(['status' => 'error', 'msg' => implode(' ', $errori)]);
    exit;
}

// Verifica che il prodotto appartenga al venditore
$check = $conn->prepare("SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_prodotto = ? AND username = ?");
$check->bind_param("is", $idProdotto, $user);
$check->execute();
if ($check->get_result()->fetch_assoc()['cnt'] == 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Prodotto non trovato o non autorizzato']);
    exit;
}
$check->close();

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "UPDATE PRODOTTO SET nome = ?, autore = ?, descrizione = ?, prezzo = ?, quantita_disponibile = ?, id_pacchetto = ?
         WHERE id_prodotto = ? AND username = ?"
    );
    $stmt->bind_param("sssdiiis", $nome, $autore, $desc, $prezzo, $qta, $idPacchetto, $idProdotto, $user);
    if (!$stmt->execute()) throw new Exception("Errore aggiornamento prodotto");
    $stmt->close();

    // Foto opzionale
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION));
        $extAmmesse = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $extAmmesse)) throw new Exception("Formato immagine non supportato.");
        $fileName = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $uploadDir . $fileName)) {
            $urlDb = 'img/' . $fileName;
            $del = $conn->prepare("DELETE FROM IMMAGINE_PRODOTTO WHERE id_prodotto = ?");
            $del->bind_param("i", $idProdotto);
            $del->execute(); $del->close();
            $stmtI = $conn->prepare("INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
            $alt = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute(); $stmtI->close();
        } else {
            throw new Exception("Impossibile salvare l'immagine.");
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}