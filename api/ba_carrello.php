<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Accedi per gestire il carrello.']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'add':
        $idProd = $_POST['idProdotto'] ?? 0;
        $qty = 1;

        // Controllo esistenza e disponibilità nel DB
        $checkStock = $conn->prepare("SELECT quantita_disponibile FROM PRODOTTO WHERE id_prodotto = ?");
        $checkStock->bind_param("i", $idProd);
        $checkStock->execute();
        $stock = $checkStock->get_result()->fetch_assoc();

        if (!$stock || $stock['quantita_disponibile'] <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Prodotto esaurito']);
            exit;
        }

        // Upsert (Aggiorna o Inserisce) nel carrello del database
        $check = $conn->prepare("SELECT quantita FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $check->bind_param("si", $idUtente, $idProd);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE CARRELLO SET quantita = quantita + ? WHERE username = ? AND id_prodotto = ?");
            $stmt->bind_param("isi", $qty, $idUtente, $idProd);
        } else {
            $stmt = $conn->prepare("INSERT INTO CARRELLO (username, id_prodotto, quantita) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $idUtente, $idProd, $qty);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'msg' => 'Aggiunto']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Errore database']);
        }
        break;

    case 'list':
        // Query che calcola lo sconto dinamico direttamente nel carrello
        $sql = "SELECT c.id_prodotto, c.quantita, p.nome, p.prezzo, p.quantita_disponibile, 
                       pk.sconto as ScontoPacchetto,
                       (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) as Foto
                FROM CARRELLO c
                JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
                LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
                WHERE c.username = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idUtente);
        $stmt->execute();
        $result = $stmt->get_result();

        $prodotti = [];
        $totaleCart = 0;

        while ($row = $result->fetch_assoc()) {
            // Calcolo prezzo scontato se applicabile
            $prezzoUnitario = (float)$row['prezzo'];
            if ($row['ScontoPacchetto'] > 0) {
                $prezzoUnitario = $prezzoUnitario - ($prezzoUnitario * ($row['ScontoPacchetto'] / 100));
            }
            
            $subtotale = $prezzoUnitario * $row['quantita'];
            $totaleCart += $subtotale;

            $prodotti[] = [
                'IdProdotto' => $row['id_prodotto'],
                'nome' => $row['nome'],
                'prezzoOriginale' => $row['prezzo'],
                'prezzoScontato' => round($prezzoUnitario, 2),
                'quantita' => $row['quantita'],
                'URLfoto' => $row['Foto'] ?? 'img/default.jpg',
                'subtotale' => round($subtotale, 2)
            ];
        }

        echo json_encode([
            'status' => 'ok',
            'prodotti' => $prodotti,
            'totaleCart' => round($totaleCart, 2)
        ]);
        break;

    case 'remove':
        $idProd = $_POST['idProdotto'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("si", $idUtente, $idProd);
        echo json_encode(['status' => ($stmt->execute() ? 'ok' : 'error')]);
        break;
}