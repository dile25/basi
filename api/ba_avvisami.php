<?php
/**
 * ba_avvisami.php - Versione senza DB
 * Restituisce sempre successo: il frontend mostra solo un feedback visivo.
 * Nessuna scrittura su database o file.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Devi essere loggato come cliente.']);
    exit;
}

$nomeProdotto = trim($_POST['nomeProdotto'] ?? 'questo libro');

echo json_encode([
    'status' => 'ok',
    'msg'    => "Perfetto! Ti avviseremo quando \"{$nomeProdotto}\" tornerà disponibile."
]);
?>