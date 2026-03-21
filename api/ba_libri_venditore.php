<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

// Protezione: solo i venditori loggati possono interagire
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Accesso negato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Recupera tutti i libri caricati da questo specifico venditore
        $sql = "SELECT p.*, c.NomeCategoria
                FROM PRODOTTO p
                LEFT JOIN CATEGORIA c ON p.IdCategoria = c.IdCategoria
                WHERE p.IdVenditore = ?
                ORDER BY p.IdProdotto DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idVenditore);
        $stmt->execute();
        $res = $stmt->get_result();

        $libri = [];
        while ($row = $res->fetch_assoc()) { $libri[] = $row; }
        echo json_encode(['status' => 'ok', 'libri' => $libri]);
        break;

    case 'save':
        // Recupero dati dal form
        $idProdotto = $_POST['idProdotto'] ?? null;
        $titolo = $_POST['titolo'];
        $prezzo = $_POST['prezzo'];
        $quantita = $_POST['quantita'];
        $idCat = $_POST['idCategoria'];
        $desc = $_POST['descrizione'];

        $urlFoto = $_POST['current_foto'] ?? 'img/book-placeholder.png';

        // Gestione Upload Immagine (se presente)
        if (isset($_FILES['copertina']) && $_FILES['copertina']['error'] === UPLOAD_ERR_OK) {
            $estensione = pathinfo($_FILES['copertina']['name'], PATHINFO_EXTENSION);
            $nomeFile = "libro_" . time() . "." . $estensione;
            $pathDestinazione = "../uploads/libri/" . $nomeFile;

            if (move_uploaded_file($_FILES['copertina']['tmp_name'], $pathDestinazione)) {
                $urlFoto = "uploads/libri/" . $nomeFile;
            }
        }

        if ($idProdotto) {
            // UPDATE: Modifica libro esistente
            $sql = "UPDATE PRODOTTO SET Titolo=?, Prezzo=?, QuantitaDisp=?, IdCategoria=?, Descrizione=?, URLfoto=?
                    WHERE IdProdotto=? AND IdVenditore=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdisssis", $titolo, $prezzo, $quantita, $idCat, $desc, $urlFoto, $idProdotto, $idVenditore);
        } else {
            // INSERT: Nuovo libro
            $sql = "INSERT INTO PRODOTTO (Titolo, Prezzo, QuantitaDisp, IdCategoria, Descrizione, URLfoto, IdVenditore)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdissss", $titolo, $prezzo, $quantita, $idCat, $desc, $urlFoto, $idVenditore);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => $conn->error]);
        }
        break;

    case 'delete':
        $idProdotto = $_POST['idProdotto'];
        // Sicurezza: cancella solo se il prodotto appartiene al venditore loggato
        $stmt = $conn->prepare("DELETE FROM PRODOTTO WHERE IdProdotto = ? AND IdVenditore = ?");
        $stmt->bind_param("is", $idProdotto, $idVenditore);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Impossibile eliminare: il libro potrebbe essere presente in ordini passati.']);
        }
        break;
}