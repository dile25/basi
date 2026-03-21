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
<?php
include 'db_connect.php';

// Notare l'uso dei nuovi nomi delle colonne
$sql = "SELECT id_prodotto, nome, prezzo, quantita_disponibile FROM PRODOTTO";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Libro: " . $row["nome"] . " - Prezzo: €" . $row["prezzo"] . "<br>";
    }
} else {
    echo "Nessun prodotto trovato.";
}
?>