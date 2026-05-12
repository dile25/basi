<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "ecommerce_libri"; // Il nuovo nome del DB

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Impostiamo il set di caratteri per evitare problemi con accenti
$conn->set_charset("utf8mb4");
?>
