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
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $qty = 1;

        $checkStock = $conn->prepare("SELECT quantita_disponibile FROM PRODOTTO WHERE id_prodotto = ?");
        $checkStock->bind_param("i", $idProd);
        $checkStock->execute();
        $stock = $checkStock->get_result()->fetch_assoc();

        if (!$stock || $stock['quantita_disponibile'] <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Prodotto esaurito']);
            exit;
        }

        $check = $conn->prepare("SELECT id_carrello, quantita_prodotto FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $check->bind_param("si", $idUtente, $idProd);
        $check->execute();
        $res = $check->get_result();

        if ($row = $res->fetch_assoc()) {
            $nuovaQta = $row['quantita_prodotto'] + $qty;
            $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
            $stmt->bind_param("isi", $nuovaQta, $idUtente, $idProd);
        } else {
            $stmt = $conn->prepare("INSERT INTO CARRELLO (username, id_prodotto, quantita_prodotto, prezzo_totale, data_creazione) VALUES (?, ?, ?, 0, CURRENT_DATE)");
            $stmt->bind_param("sii", $idUtente, $idProd, $qty);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'msg' => 'Aggiunto']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Errore database']);
        }
        break;

    case 'list':
        // MODIFICA: Selezioniamo anche p.autore dalla tabella PRODOTTO
        $sql = "SELECT c.id_prodotto, c.quantita_prodotto, p.nome, p.autore, p.prezzo,
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

        $righeCarrello = [];
        $conteggioAutori = [];

        // Primo passaggio: estraiamo i dati e calcoliamo quante copie ci sono per ogni autore
        while ($row = $result->fetch_assoc()) {
            $righeCarrello[] = $row;
            
            $autore = trim($row['autore'] ?? '');
            if (!empty($autore)) {
                if (!isset($conteggioAutori[$autore])) {
                    $conteggioAutori[$autore] = 0;
                }
                // Sommiamo la quantità di copie di questo libro nel carrello
                $conteggioAutori[$autore] += intval($row['quantita_prodotto']);
            }
        }

        $prodotti = [];
        $totaleCart = 0;

        // Secondo passaggio: calcoliamo i singoli subtotali applicando le regole di sconto
        foreach ($righeCarrello as $row) {
            $prezzoUnitario = (float)$row['prezzo'];
            $autore = trim($row['autore'] ?? '');
            $haScontoAutore = false;
            $percentualeScontoApplicata = 0;

            // 1. Controllo Sconto Autore: se ci sono almeno 2 libri dello stesso autore nel carrello
            if (!empty($autore) && isset($conteggioAutori[$autore]) && $conteggioAutori[$autore] >= 2) {
                $percentualeScontoApplicata = 20; // Sconto del 20% (puoi personalizzare questo valore)
                $haScontoAutore = true;
            } 
            // 2. Altrimenti, se non c'è lo sconto autore, controlliamo lo sconto del pacchetto standard
            else {
                $percentualeScontoApplicata = (float)($row['ScontoPacchetto'] ?? 0);
            }

            // Applichiamo la percentuale di sconto trovata
            if ($percentualeScontoApplicata > 0) {
                $prezzoUnitario = $prezzoUnitario - ($prezzoUnitario * ($percentualeScontoApplicata / 100));
            }

            $subtotale = $prezzoUnitario * $row['quantita_prodotto'];
            $totaleCart += $subtotale;

            $prodotti[] = [
                'IdProdotto'      => $row['id_prodotto'],
                'nome'            => $row['nome'],
                'autore'          => $autore,
                'prezzoOriginale' => (float)$row['prezzo'],
                'prezzoScontato'  => round($prezzoUnitario, 2),
                'quantita'        => $row['quantita_prodotto'],
                'URLfoto'         => $row['Foto'] ?? 'img/default.jpg',
                'subtotale'       => round($subtotale, 2),
                'scontoAutore'    => $haScontoAutore, // Indica al frontend se è attiva la promo autore
                'percentualeSconto' => $percentualeScontoApplicata
            ];
        }

        echo json_encode([
            'status'     => 'ok',
            'prodotti'   => $prodotti,
            'totaleCart' => round($totaleCart, 2)
        ]);
        break;

    case 'update':
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $qty = intval($_POST['qty'] ?? 1);
        if ($qty < 1) $qty = 1;
        $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("isi", $qty, $idUtente, $idProd);
        echo json_encode(['status' => ($stmt->execute() ? 'ok' : 'error')]);
        break;

    case 'remove':
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("si", $idUtente, $idProd);
        echo json_encode(['status' => ($stmt->execute() ? 'ok' : 'error')]);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Azione non valida']);
        break;
}
?>