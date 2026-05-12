<?php
ob_start();
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    ob_clean(); echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']); exit;
}

$user = $_SESSION['IdUtente'];
$id   = intval($_POST['id_prodotto'] ?? 0);

try {
    $stmt = $conn->prepare("DELETE FROM PRODOTTO WHERE id_prodotto=? AND username=?");
    $stmt->bind_param("is", $id, $user);
    $stmt->execute();
    ob_clean();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}