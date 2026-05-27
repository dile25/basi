<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Accedi per gestire il carrello.']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];
$action   = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'add':
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $qty    = 1;

        $checkStock = $conn->prepare("SELECT quantita_disponibile FROM PRODOTTO WHERE id_prodotto = ?");
        $checkStock->bind_param("i", $idProd);
        $checkStock->execute();
        $stock = $checkStock->get_result()->fetch_assoc();

        if (!$stock || $stock['quantita_disponibile'] <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Prodotto esaurito.']);
            exit;
        }

        $check = $conn->prepare("SELECT id_carrello, quantita_prodotto FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $check->bind_param("si", $idUtente, $idProd);
        $check->execute();
        $res = $check->get_result();

        if ($row = $res->fetch_assoc()) {
            $nuovaQta = $row['quantita_prodotto'] + $qty;
            if ($nuovaQta > $stock['quantita_disponibile']) {
                echo json_encode(['status' => 'error', 'msg' => "Limite disponibile raggiunto."]);
                exit;
            }
            $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
            $stmt->bind_param("isi", $nuovaQta, $idUtente, $idProd);
        } else {
            $stmt = $conn->prepare("INSERT INTO CARRELLO (username, id_prodotto, quantita_prodotto, data_creazione) VALUES (?, ?, ?, CURRENT_DATE)");
            $stmt->bind_param("sii", $idUtente, $idProd, $qty);
        }

        // CORREZIONE: Esegui una sola volta e salva il risultato
        $success = $stmt->execute();
        echo json_encode(['status' => $success ? 'ok' : 'error', 'msg' => $success ? 'Aggiunto' : 'Errore database']);
        break;

    case 'list':
        // La tua query SQL è corretta. 
        // Assicurati che i nomi delle colonne siano identici a quelli del DB.
        $sql = "SELECT c.id_prodotto, c.quantita_prodotto, p.nome, p.autore, p.prezzo, p.quantita_disponibile,
                       pk.sconto AS ScontoPacchetto, pk.nome AS NomePacchetto,
                       (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) AS Foto
                FROM CARRELLO c
                JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
                LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
                WHERE c.username = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idUtente);
        $stmt->execute();
        $result = $stmt->get_result();

        $righeCarrello = [];
        $conteggioAutori = [];
        
        while ($row = $result->fetch_assoc()) {
            // Logica di auto-fix quantità
            if ($row['quantita_prodotto'] > $row['quantita_disponibile']) {
                $fix = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
                $fix->bind_param("isi", $row['quantita_disponibile'], $idUtente, $row['id_prodotto']);
                $fix->execute();
                $row['quantita_prodotto'] = $row['quantita_disponibile'];
            }
            
            $righeCarrello[] = $row;
            $autore = trim($row['autore'] ?? '');
            if (!empty($autore)) {
                $conteggioAutori[$autore] = ($conteggioAutori[$autore] ?? 0) + intval($row['quantita_prodotto']);
            }
        }

        $prodotti = [];
        $totaleCart = 0;

        foreach ($righeCarrello as $row) {
            // ... (logica sconti invariata, è corretta) ...
            // Assicurati che nel tuo JS di 'carrello.php' tu stia leggendo 'IdProdotto' 
            // e non 'id_prodotto' se usi le chiavi di questo array:
            $prodotti[] = [
                'IdProdotto'        => $row['id_prodotto'],
                'nome'              => $row['nome'],
                'autore'            => $row['autore'],
                // ... resto invariato
            ];
        }
        // ... (resto invariato)
        break;

    // ... (restanti casi update e remove sono corretti)
}