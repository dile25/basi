<?php
require_once __DIR__ . '/comune.php';
$username = richiediLogin();
$tipo     = $_SESSION['tipoUtente'];

$stmt = $conn->prepare("SELECT username, email, nome, cognome, data_registrazione FROM utente WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$anagrafica = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($tipo === 'venditore') {
    $stmt = $conn->prepare("SELECT partita_iva, ragione_sociale FROM venditore WHERE username = ?");
} else {
    $stmt = $conn->prepare("SELECT telefono, indirizzo_predefinito FROM cliente WHERE username = ?");
}
$stmt->bind_param("s", $username);
$stmt->execute();
$dettagli = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

ok(['tipo' => $tipo, 'anagrafica' => $anagrafica, 'dettagli' => $dettagli]);
