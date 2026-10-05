<?php
/*
 * db_connect.php — connessione al database con MySQLi (approccio a oggetti).
 * mysqli_report rende esplicito il comportamento predefinito da PHP 8.1:
 * gli errori MySQL diventano eccezioni, che vengono intercettate.
 * In caso di errore NON si mostra $conn->connect_error all'utente
 * (rivelerebbe dettagli del server) e si risponde in JSON, perché questo
 * file viene incluso dalle API chiamate via AJAX.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host     = "localhost";
$user     = "root";
$password = "";
$dbname   = "floris_valenti";

try {
    $conn = new mysqli($host, $user, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'msg' => 'Servizio momentaneamente non disponibile.']);
    exit;
}
?>