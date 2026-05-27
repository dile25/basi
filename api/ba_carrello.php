<?php
/**
 * ba_carrello.php
 * Gestione carrello con:
 * - Limite quantità basato su quantita_disponibile in PRODOTTO
 * - Sconto autore (≥2 libri stesso autore → -20%)
 * - Sconto pacchetto (da tabella PACCHETTO)
 * - quantitaDisponibile esposta al frontend per validazione lato client
 */
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

    // ============================================================
    case 'add':
    // ============================================================
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $qty    = 1;

        // Controlla disponibilità
        $checkStock = $conn->prepare("SELECT quantita_disponibile FROM PRODOTTO WHERE id_prodotto = ?");
        $checkStock->bind_param("i", $idProd);
        $checkStock->execute();
        $stock = $checkStock->get_result()->fetch_assoc();

        if (!$stock || $stock['quantita_disponibile'] <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Prodotto esaurito.']);
            exit;
        }

        // Controlla se già in carrello
        $check = $conn->prepare("SELECT id_carrello, quantita_prodotto FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $check->bind_param("si", $idUtente, $idProd);
        $check->execute();
        $res = $check->get_result();

        if ($row = $res->fetch_assoc()) {
            $nuovaQta = $row['quantita_prodotto'] + $qty;

            // === LIMITE QUANTITÀ: non superare lo stock disponibile ===
            if ($nuovaQta > $stock['quantita_disponibile']) {
                echo json_encode([
                    'status' => 'error',
                    'msg'    => "Hai già raggiunto il limite disponibile ({$stock['quantita_disponibile']} copie) per questo titolo."
                ]);
                exit;
            }

            $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
            $stmt->bind_param("isi", $nuovaQta, $idUtente, $idProd);
        } else {
            $stmt = $conn->prepare("INSERT INTO CARRELLO (username, id_prodotto, quantita_prodotto, prezzo_totale, data_creazione) VALUES (?, ?, ?, 0, CURRENT_DATE)");
            $stmt->bind_param("sii", $idUtente, $idProd, $qty);
        }

        echo json_encode([
            'status' => $stmt->execute() ? 'ok' : 'error',
            'msg'    => $stmt->execute() ? 'Aggiunto' : 'Errore database'
        ]);
        break;

    // ============================================================
    case 'list':
    // ============================================================
        $sql = "
            SELECT c.id_prodotto, c.quantita_prodotto,
                   p.nome, p.autore, p.prezzo, p.quantita_disponibile,
                   pk.sconto AS ScontoPacchetto, pk.nome AS NomePacchetto,
                   (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) AS Foto
            FROM CARRELLO c
            JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
            LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
            WHERE c.username = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idUtente);
        $stmt->execute();
        $result = $stmt->get_result();

        $righeCarrello  = [];
        $conteggioAutori = [];

        while ($row = $result->fetch_assoc()) {
            // === LIMITE QUANTITÀ: auto-correggi se la quantità in carrello supera lo stock ===
            $qtaCarrello   = intval($row['quantita_prodotto']);
            $qtaDisponibile = intval($row['quantita_disponibile']);

            if ($qtaCarrello > $qtaDisponibile && $qtaDisponibile > 0) {
                // Aggiorna silenziosamente nel DB
                $fix = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
                $fix->bind_param("isi", $qtaDisponibile, $idUtente, $row['id_prodotto']);
                $fix->execute();
                $row['quantita_prodotto'] = $qtaDisponibile;
            }

            $righeCarrello[] = $row;
            $autore = trim($row['autore'] ?? '');
            if (!empty($autore)) {
                $conteggioAutori[$autore] = ($conteggioAutori[$autore] ?? 0) + intval($row['quantita_prodotto']);
            }
        }

        $prodotti   = [];
        $totaleCart = 0;

        foreach ($righeCarrello as $row) {
            $prezzoUnitario = (float)$row['prezzo'];
            $autore         = trim($row['autore'] ?? '');
            $haScontoAutore = false;
            $percentualeSconto = 0;
            $nomePacchetto  = '';

            // Sconto autore: ≥2 libri dello stesso autore → 20%
            if (!empty($autore) && isset($conteggioAutori[$autore]) && $conteggioAutori[$autore] >= 2) {
                $percentualeSconto = 20;
                $haScontoAutore    = true;
            }
            // Altrimenti sconto da pacchetto
            elseif (!empty($row['ScontoPacchetto'])) {
                $percentualeSconto = (float)$row['ScontoPacchetto'];
                $nomePacchetto     = $row['NomePacchetto'] ?? '';
            }

            if ($percentualeSconto > 0) {
                $prezzoUnitario = $prezzoUnitario * (1 - $percentualeSconto / 100);
            }

            $qtaAttuale = intval($row['quantita_prodotto']);
            $subtotale  = $prezzoUnitario * $qtaAttuale;
            $totaleCart += $subtotale;

            $prodotti[] = [
                'IdProdotto'          => $row['id_prodotto'],
                'nome'                => $row['nome'],
                'autore'              => $autore,
                'prezzoOriginale'     => (float)$row['prezzo'],
                'prezzoScontato'      => round($prezzoUnitario, 2),
                'quantita'            => $qtaAttuale,
                'quantitaDisponibile' => intval($row['quantita_disponibile']), // esposto al frontend!
                'URLfoto'             => $row['Foto'] ?? 'img/default.jpg',
                'subtotale'           => round($subtotale, 2),
                'scontoAutore'        => $haScontoAutore,
                'nomePacchetto'       => $nomePacchetto,
                'percentualeSconto'   => $percentualeSconto,
            ];
        }

        echo json_encode([
            'status'     => 'ok',
            'prodotti'   => $prodotti,
            'totaleCart' => round($totaleCart, 2),
        ]);
        break;

    // ============================================================
    case 'update':
    // ============================================================
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $qty    = intval($_POST['qty'] ?? 1);
        if ($qty < 1) $qty = 1;

        // === LIMITE QUANTITÀ: verifica stock prima di aggiornare ===
        $checkStock = $conn->prepare("SELECT quantita_disponibile FROM PRODOTTO WHERE id_prodotto = ?");
        $checkStock->bind_param("i", $idProd);
        $checkStock->execute();
        $stock = $checkStock->get_result()->fetch_assoc();

        if ($stock && $qty > intval($stock['quantita_disponibile'])) {
            $max = intval($stock['quantita_disponibile']);
            echo json_encode([
                'status' => 'error',
                'msg'    => "Disponibili solo {$max} cop" . ($max === 1 ? 'ia' : 'ie') . " per questo titolo.",
                'maxQty' => $max
            ]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE CARRELLO SET quantita_prodotto = ? WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("isi", $qty, $idUtente, $idProd);
        echo json_encode(['status' => $stmt->execute() ? 'ok' : 'error']);
        break;

    // ============================================================
    case 'remove':
    // ============================================================
        $idProd = intval($_POST['idProdotto'] ?? 0);
        $stmt   = $conn->prepare("DELETE FROM CARRELLO WHERE username = ? AND id_prodotto = ?");
        $stmt->bind_param("si", $idUtente, $idProd);
        echo json_encode(['status' => $stmt->execute() ? 'ok' : 'error']);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Azione non valida.']);
        break;
}
?>