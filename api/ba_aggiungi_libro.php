<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

// Sicurezza: solo venditori loggati
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Accesso non autorizzato']);
    exit;
}

$user = $_SESSION['IdUtente'];
$nome = $_POST['nome'] ?? '';
$desc = $_POST['descrizione'] ?? '';
$prezzo = floatval($_POST['prezzo'] ?? 0);
$qta = intval($_POST['quantita'] ?? 0);
$cat = $_POST['categoria'] ?? '';

// Iniziamo la transazione
$conn->begin_transaction();

try {
    // 1. Inserimento in PRODOTTO (Assicurati che il campo sia 'username' o 'id_venditore' nel DB)
    $sqlP = "INSERT INTO PRODOTTO (username, nome, descrizione, prezzo, quantita_disponibile) VALUES (?, ?, ?, ?, ?)";
    $stmtP = $conn->prepare($sqlP);
    $stmtP->bind_param("sssdi", $user, $nome, $desc, $prezzo, $qta);
    if (!$stmtP->execute()) throw new Exception("Errore inserimento prodotto");
    
    $idProdotto = $conn->insert_id;

    // 2. Gestione Immagine (Caricamento fisico e DB)
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = '../uploads/';
        
        // Se la cartella non esiste, la creiamo
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        // Generiamo un nome unico per evitare sovrascritture
        $ext = pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION);
        $fileName = time() . "_" . uniqid() . "." . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $targetPath)) {
            $urlDb = 'uploads/' . $fileName; // Percorso relativo per il frontend
            $sqlI = "INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)";
            $stmtI = $conn->prepare($sqlI);
            $alt = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute();
        } else {
            throw new Exception("Impossibile salvare l'immagine sul server");
        }
    }

    // 3. Associazione Categoria (Tabella DESCRIVE)
    if (!empty($cat)) {
        $sqlD = "INSERT INTO DESCRIVE (id_prodotto, nome_categoria) VALUES (?, ?)";
        $stmtD = $conn->prepare($sqlD);
        $stmtD->bind_param("is", $idProdotto, $cat);
        $stmtD->execute();
    }

    // Se tutto è andato bene, confermiamo
    $conn->commit();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    // Se qualcosa fallisce, annulliamo tutto (anche l'inserimento del prodotto)
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}