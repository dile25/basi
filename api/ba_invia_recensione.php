<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

$idUtente = $_SESSION['IdUtente'];
$idProdotto = $_POST['idProdotto'];
$voto = $_POST['voto'];
$testo = $_POST['testo'];

// Gestione opzionale del caricamento foto
$urlFoto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $nomeFile = time() . "_" . $_FILES['foto']['name'];
    move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/reviews/" . $nomeFile);
    $urlFoto = "uploads/reviews/" . $nomeFile;
}

// Controllo se l'utente ha davvero acquistato il libro (sicurezza)
$checkAcquisto = $conn->prepare("SELECT o.IdOrdine FROM ORDINE o
                                JOIN DETTAGLIO_ORDINE do ON o.IdOrdine = do.IdOrdine
                                WHERE o.IdCliente = ? AND do.IdProdotto = ?");
$checkAcquisto->bind_param("ss", $idUtente, $idProdotto);
$checkAcquisto->execute();

if ($checkAcquisto->get_result()->num_rows > 0) {
    // Se ha comprato, usiamo REPLACE INTO (aggiorna se esiste la chiave primaria composta)
    // Nota: Assicurati che la tabella RECENSIONE abbia una chiave primaria su (IdProdotto, IdCliente)
    $sql = "REPLACE INTO RECENSIONE (IdProdotto, IdCliente, Voto, Testo, Data) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $idProdotto, $idUtente, $voto, $testo);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'msg' => 'Recensione salvata!']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Puoi recensire solo libri che hai acquistato.']);
}