<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    // I metodi salvati hanno stato che inizia con 'salvato:'
    $stmt = $conn->prepare("SELECT id_pagamento, metodo, SUBSTRING(stato, 9) as dati FROM PAGAMENTO WHERE username = ? AND stato LIKE 'salvato:%' ORDER BY id_pagamento DESC");
    $stmt->bind_param("s", $idUtente);
    $stmt->execute();
    $res = $stmt->get_result();
    $metodi = [];
    while($row = $res->fetch_assoc()) $metodi[] = $row;
    echo json_encode(['status' => 'ok', 'metodi' => $metodi]);

} elseif ($action === 'add') {
    $metodo = $_POST['metodo'] ?? '';
    $dati   = 'salvato:' . ($_POST['dati'] ?? '');
    $stmt = $conn->prepare("INSERT INTO PAGAMENTO (username, metodo, stato) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $idUtente, $metodo, $dati);
    echo json_encode(['status' => $stmt->execute() ? 'ok' : 'error']);

} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM PAGAMENTO WHERE id_pagamento = ? AND username = ? AND stato LIKE 'salvato:%'");
    $stmt->bind_param("is", $id, $idUtente);
    echo json_encode(['status' => $stmt->execute() ? 'ok' : 'error']);
}