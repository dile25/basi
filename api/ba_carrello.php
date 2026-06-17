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
                echo json_encode(['status' => 'error', 'msg' => 'Limite disponibile raggiunto.']);
                exit;
            }
            $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
            $stmt->bind_param("isi", $nuovaQta, $idUtente, $idProd);
        } else {
            $stmt = $conn->prepare("INSERT INTO CARRELLO (username, id_prodotto, quantita_prodotto, prezzo_totale, data_creazione) VALUES (?, ?, ?, 0, CURRENT_DATE)");
            $stmt->bind_param("sii", $idUtente, $idProd, $qty);
        }

        $success = $stmt->execute();
        echo json_encode(['status' => $success ? 'ok' : 'error', 'msg' => $success ? 'Aggiunto' : 'Errore database']);
        break;

    case 'list':
        // 1. Carica tutti i prodotti nel carrello con info pacchetto
        $sql = "SELECT c.id_prodotto, c.quantita_prodotto, p.nome, p.autore, p.prezzo,
                       p.quantita_disponibile, p.id_pacchetto,
                       pk.sconto AS ScontoBase,
                       pk.sconto_2, pk.sconto_3, pk.sconto_tutti,
                       pk.nome AS NomePacchetto,
                       pk.tipo_pacchetto AS TipoPacchetto,
                       (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) AS Foto
                FROM CARRELLO c
                JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
                LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
                WHERE c.username = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idUtente);
        $stmt->execute();
        $result = $stmt->get_result();

        $righe = [];
        $conteggioAutori  = [];  // username => count
        $conteggioPacket  = [];  // id_pacchetto => [count_nel_carrello, totale_nel_db, info]

        while ($row = $result->fetch_assoc()) {
            // Auto-fix quantità
            if ($row['quantita_prodotto'] > $row['quantita_disponibile']) {
                $fix = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
                $fix->bind_param("isi", $row['quantita_disponibile'], $idUtente, $row['id_prodotto']);
                $fix->execute();
                $row['quantita_prodotto'] = $row['quantita_disponibile'];
            }
            $righe[] = $row;

            // Conteggio autori (per sconto autore) — solo prodotti tipo libro/non-abbonamento
            $autore = trim($row['autore'] ?? '');
            if (!empty($autore) && $row['TipoPacchetto'] !== 'abbonamento') {
                $conteggioAutori[$autore] = ($conteggioAutori[$autore] ?? 0) + 1;
            }

            // Conteggio pacchetti
            if ($row['id_pacchetto']) {
                $ip = $row['id_pacchetto'];
                if (!isset($conteggioPacket[$ip])) {
                    // Quanti prodotti totali esistono in questo pacchetto nel DB
                    $stmtCount = $conn->prepare("SELECT COUNT(*) as tot FROM PRODOTTO WHERE id_pacchetto = ?");
                    $stmtCount->bind_param("i", $ip);
                    $stmtCount->execute();
                    $totDB = $stmtCount->get_result()->fetch_assoc()['tot'];

                    $conteggioPacket[$ip] = [
                        'nel_carrello'   => 0,
                        'totale_db'      => $totDB,
                        'sconto_2'       => (float)($row['sconto_2'] ?? 10),
                        'sconto_3'       => (float)($row['sconto_3'] ?? 20),
                        'sconto_tutti'   => (float)($row['sconto_tutti'] ?? 30),
                        'nome'           => $row['NomePacchetto'] ?? '',
                        'tipo_pacchetto' => $row['TipoPacchetto'] ?? 'libro'
                    ];
                }
                $conteggioPacket[$ip]['nel_carrello']++;
            }
        }

        // 2. Calcola sconto per ogni prodotto
        $prodotti   = [];
        $totaleCart = 0;

        foreach ($righe as $row) {
            $prezzoUnitario = (float)$row['prezzo'];
            $autore         = trim($row['autore'] ?? '');
            $percSconto     = 0;
            $tipoSconto     = '';
            $nomePacchetto  = '';

            $ip = $row['id_pacchetto'];

            if ($ip && isset($conteggioPacket[$ip])) {
                $pack   = $conteggioPacket[$ip];
                $nCart  = $pack['nel_carrello'];
                $totDB  = $pack['totale_db'];
                $nomePacchetto = $pack['nome'];

                if ($pack['tipo_pacchetto'] === 'abbonamento') {
                    // ABBONAMENTO PERIODICO: tutto o niente.
                    // Lo sconto scatta SOLO se tutti i numeri del periodo sono nel carrello.
                    if ($totDB >= 2 && $nCart >= $totDB) {
                        $percSconto = $pack['sconto_tutti'];
                        $tipoSconto = 'abbonamento_completo';
                    }
                    // altrimenti nessuno sconto, anche con più numeri ma non tutti
                } else {
                    // PACCHETTO LIBRO: sconto crescente in base a quanti prodotti sono nel carrello
                    if ($nCart >= $totDB && $totDB >= 2) {
                        $percSconto = $pack['sconto_tutti'];
                        $tipoSconto = 'pacchetto_tutti';
                    } elseif ($nCart >= 3) {
                        $percSconto = $pack['sconto_3'];
                        $tipoSconto = 'pacchetto_3';
                    } elseif ($nCart >= 2) {
                        $percSconto = $pack['sconto_2'];
                        $tipoSconto = 'pacchetto_2';
                    }
                }
            }

            // Sconto autore (solo se non ha già sconto pacchetto/abbonamento)
            $haScontoAutore = false;
            if ($percSconto == 0 && !empty($autore) && ($conteggioAutori[$autore] ?? 0) >= 2) {
                $percSconto     = 10;
                $haScontoAutore = true;
                $tipoSconto     = 'autore';
            }

            if ($percSconto > 0) {
                $prezzoUnitario = $prezzoUnitario * (1 - $percSconto / 100);
            }

            $subtotale   = round($prezzoUnitario * $row['quantita_prodotto'], 2);
            $totaleCart += $subtotale;

            $prodotti[] = [
                'IdProdotto'          => $row['id_prodotto'],
                'nome'                => $row['nome'],
                'autore'              => $autore,
                'prezzoOriginale'     => (float)$row['prezzo'],
                'prezzoScontato'      => round($prezzoUnitario, 2),
                'quantita'            => $row['quantita_prodotto'],
                'quantitaDisponibile' => $row['quantita_disponibile'],
                'URLfoto'             => $row['Foto'] ?? 'img/default.jpg',
                'subtotale'           => $subtotale,
                'percentualeSconto'   => $percSconto,
                'tipoSconto'          => $tipoSconto,
                'scontoAutore'        => $haScontoAutore,
                'nomePacchetto'       => $nomePacchetto,
                'id_pacchetto'        => $ip,
                'tipoPacchetto'       => $ip ? ($conteggioPacket[$ip]['tipo_pacchetto'] ?? 'libro') : null,
                'libriPackNelCarrello'=> $ip ? ($conteggioPacket[$ip]['nel_carrello'] ?? 0) : 0,
                'libriPackTotale'     => $ip ? ($conteggioPacket[$ip]['totale_db'] ?? 0) : 0,
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
        $qty    = intval($_POST['qty'] ?? 1);
        if ($qty < 1) $qty = 1;
        $checkStock = $conn->prepare("SELECT quantita_disponibile FROM PRODOTTO WHERE id_prodotto = ?");
        $checkStock->bind_param("i", $idProd);
        $checkStock->execute();
        $stock = $checkStock->get_result()->fetch_assoc();
        if ($stock && $qty > $stock['quantita_disponibile']) $qty = $stock['quantita_disponibile'];
        $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("isi", $qty, $idUtente, $idProd);
        echo json_encode(['status' => $stmt->execute() ? 'ok' : 'error']);
        break;

    case 'remove':
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $stmt   = $conn->prepare("DELETE FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("si", $idUtente, $idProd);
        echo json_encode(['status' => $stmt->execute() ? 'ok' : 'error']);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Azione non valida']);
        break;
}