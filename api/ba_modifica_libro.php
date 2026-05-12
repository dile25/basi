<?php
ob_start();
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    ob_clean(); echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']); exit;
}

$user   = $_SESSION['IdUtente'];
$id     = intval($_POST['id_prodotto'] ?? 0);
$nome   = $_POST['nome'] ?? '';
$desc   = $_POST['descrizione'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$qta    = intval($_POST['quantita'] ?? 0);

try {
    // Aggiorna dati prodotto
    $stmt = $conn->prepare("UPDATE PRODOTTO SET nome=?, descrizione=?, prezzo=?, quantita_disponibile=? WHERE id_prodotto=? AND username=?");
    $stmt->bind_param("ssdiss", $nome, $desc, $prezzo, $qta, $id, $user);
    $stmt->execute();

    // Se c'è una nuova immagine
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';
        $ext = pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION);
        $fileName = time() . "_" . uniqid() . "." . $ext;

        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $uploadDir . $fileName)) {
            $urlDb = 'img/' . $fileName;
            // Aggiorna o inserisce l'immagine
            $chk = $conn->prepare("SELECT id_immagine_prodotto FROM IMMAGINE_PRODOTTO WHERE id_prodotto=?");
            $chk->bind_param("i", $id);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $stmt2 = $conn->prepare("UPDATE IMMAGINE_PRODOTTO SET url=? WHERE id_prodotto=?");
                $stmt2->bind_param("si", $urlDb, $id);
            } else {
                $stmt2 = $conn->prepare("INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
                $alt = "Copertina " . $nome;
                $stmt2->bind_param("iss", $id, $urlDb, $alt);
            }
            $stmt2->execute();
        }
    }

    ob_clean();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}