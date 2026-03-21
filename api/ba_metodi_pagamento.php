<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

$idUtente = $_SESSION['IdUtente'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    $sql = "SELECT IdMetodo, Tipo, DatiMetodo FROM METODO_PAGAMENTO WHERE IdUtente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idUtente);
    $stmt->execute();
    $res = $stmt->get_result();

    $metodi = [];
    while ($row = $res->fetch_assoc()) { $metodi[] = $row; }
    echo json_encode(['status' => 'ok', 'metodi' => $metodi]);

} elseif ($action === 'add') {
    $tipo = $_POST['tipoPagamento'];
    $dati = "";

    // Formattiamo i dati in base al tipo
    if ($tipo === "Carta di Credito") {
        $num = str_replace(' ', '', $_POST['cc_numero']);
        $dati = "Carta che termina con " . substr($num, -4);
    } elseif ($tipo === "PayPal") {
        $dati = $_POST['pp_email'];
    } else {
        $dati = "Bonifico: " . substr($_POST['bon_iban'], 0, 4) . "...";
    }

    $stmt = $conn->prepare("INSERT INTO METODO_PAGAMENTO (Tipo, DatiMetodo, IdUtente) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $tipo, $dati, $idUtente);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $conn->error]);
    }

} elseif ($action === 'delete') {
    $idMetodo = $_POST['idMetodo'];
    $stmt = $conn->prepare("DELETE FROM METODO_PAGAMENTO WHERE IdMetodo = ? AND IdUtente = ?");
    $stmt->bind_param("is", $idMetodo, $idUtente);
    $stmt->execute();
    echo json_encode(['status' => 'ok']);
}