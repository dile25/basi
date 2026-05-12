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

$user = $_SESSION['IdUtente'];
$nome = $_POST['nome'] ?? '';
$desc = $_POST['descrizione'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$qta = intval($_POST['quantita'] ?? 0);
$cat = $_POST['categoria'] ?? '';

$conn->begin_transaction();

try {
    $sqlP = "INSERT INTO PRODOTTO (username, nome, descrizione, prezzo, quantita_disponibile) VALUES (?, ?, ?, ?, ?)";
    $stmtP = $conn->prepare($sqlP);
    $stmtP->bind_param("sssdi", $user, $nome, $desc, $prezzo, $qta);
    if (!$stmtP->execute()) throw new Exception("Errore inserimento prodotto");
    
    $idProdotto = $conn->insert_id;

    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';

        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION);
        $fileName = time() . "_" . uniqid() . "." . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $targetPath)) {
            $urlDb = 'img/' . $fileName;
            $sqlI = "INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)";
            $stmtI = $conn->prepare($sqlI);
            $alt = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute();
        } else {
            throw new Exception("Impossibile salvare l'immagine sul server");
        }
    }

    if (!empty($cat)) {
        $sqlD = "INSERT INTO DESCRIVE (id_prodotto, nome_categoria) VALUES (?, ?)";
        $stmtD = $conn->prepare($sqlD);
        $stmtD->bind_param("is", $idProdotto, $cat);
        $stmtD->execute();
    }

    $conn->commit();
    ob_clean();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}