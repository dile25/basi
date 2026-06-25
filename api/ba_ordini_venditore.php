<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$idVenditore = $_SESSION['IdUtente'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    $filtroStato = $_GET['stato'] ?? '';

    $sql = "SELECT ii.id_ordine AS IdOrdine,
                   ii.id_prodotto AS IdProdotto,
                   ii.quantita_prodotto AS Quantita,
                   ii.prezzo_unitario AS PrezzoUnitario,
                   ii.stato_venditore AS StatoVenditore,
                   o.data AS DataOrdine,
                   o.stato AS StatoOrdine,
                   o.username AS Cliente,
                   o.totale AS TotaleOrdine,
                   p.nome AS Titolo,
                   p.prezzo AS PrezzoAttuale,
                   (SELECT url FROM IMMAGINE_PRODOTTO WHERE id_prodotto = p.id_prodotto LIMIT 1) AS Foto
            FROM INCLUSO_IN ii
            JOIN ORDINE o ON ii.id_ordine = o.id_ordine
            JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
            WHERE p.username = ?";

    $params = [$idVenditore];
    $types = "s";

    if (!empty($filtroStato)) {
        $sql .= " AND o.stato = ?";
        $params[] = $filtroStato;
        $types .= "s";
    }

    $sql .= " ORDER BY o.data DESC, ii.id_ordine DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $ordini = [];
    while ($row = $res->fetch_assoc()) {
        $id = $row['IdOrdine'];
        // Usa prezzo_unitario salvato (con sconto applicato) se disponibile, altrimenti fallback al prezzo attuale
        $prezzoEffettivo = $row['PrezzoUnitario'] !== null ? (float)$row['PrezzoUnitario'] : (float)$row['PrezzoAttuale'];

        if (!isset($ordini[$id])) {
            $ordini[$id] = [
                'IdOrdine'        => $id,
                'DataOrdine'      => date("d/m/Y", strtotime($row['DataOrdine'])),
                'Stato'           => $row['StatoVenditore'], // stato del venditore (per gestione)
                'StatoOrdine'     => $row['StatoOrdine'],    // stato aggregato (visibile al cliente)
                'Cliente'         => $row['Cliente'],
                'TotaleOrdine'    => $row['TotaleOrdine'],
                'TotaleVenditore' => 0,
                'libri'           => []
            ];
        }
        $ordini[$id]['libri'][] = [
            'IdProdotto' => $row['IdProdotto'],
            'Titolo'     => $row['Titolo'],
            'Quantita'   => $row['Quantita'],
            'Prezzo'     => $prezzoEffettivo,
            'Foto'       => $row['Foto'] ?? 'img/default.jpg'
        ];
        // Accumula guadagno venditore su questo ordine
        $ordini[$id]['TotaleVenditore'] = round(
            ($ordini[$id]['TotaleVenditore'] ?? 0) + $prezzoEffettivo * $row['Quantita'], 2
        );
    }

    // Guadagno DISPONIBILE: somma solo le righe di INCLUSO_IN con stato
    // Spedito/Consegnato che NON sono ancora state trasferite (trasferito = 0).
    // Questo e' indipendente dalla data, quindi funziona correttamente anche
    // con piu' trasferimenti effettuati nello stesso giorno.
    $sqlGuadagno = "SELECT SUM(
                        COALESCE(ii.prezzo_unitario, p.prezzo) * ii.quantita_prodotto
                    ) as guadagno
                    FROM INCLUSO_IN ii
                    JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
                    WHERE p.username = ? AND ii.stato_venditore IN ('Spedito','Consegnato') AND ii.trasferito = 0";

    $stmtG = $conn->prepare($sqlGuadagno);
    $stmtG->bind_param("s", $idVenditore);
    $stmtG->execute();
    $guadagno = $stmtG->get_result()->fetch_assoc()['guadagno'] ?? 0;

    echo json_encode([
        'status'   => 'ok',
        'ordini'   => array_values($ordini),
        'guadagno' => round($guadagno, 2)
    ]);

} elseif ($action === 'update_status') {
    $idOrdine   = intval($_POST['idOrdine'] ?? 0);
    $nuovoStato = $_POST['stato'] ?? '';

    if (!$idOrdine || !$nuovoStato) {
        echo json_encode(['status' => 'error', 'msg' => 'Dati mancanti']);
        exit;
    }

    $scalaStati = ['Pagato' => 0, 'In lavorazione' => 1, 'Spedito' => 2, 'Consegnato' => 3];
    if (!isset($scalaStati[$nuovoStato])) {
        echo json_encode(['status' => 'error', 'msg' => 'Stato non valido']);
        exit;
    }

    // Verifica che questo venditore abbia prodotti nell'ordine
    $check = $conn->prepare(
        "SELECT COUNT(*) as cnt FROM INCLUSO_IN ii
         JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
         WHERE ii.id_ordine = ? AND p.username = ?"
    );
    $check->bind_param("is", $idOrdine, $idVenditore);
    $check->execute();
    $resCheck = $check->get_result()->fetch_assoc();
    $check->close();
    if ((int)$resCheck['cnt'] === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Ordine non autorizzato']);
        exit;
    }

    // Blocca se l'ordine è già annullato
    $stmtAnn = $conn->prepare("SELECT stato FROM ORDINE WHERE id_ordine = ?");
    $stmtAnn->bind_param("i", $idOrdine);
    $stmtAnn->execute();
    $statoOrdine = $stmtAnn->get_result()->fetch_assoc()['stato'] ?? '';
    $stmtAnn->close();
    if ($statoOrdine === 'Annullato') {
        echo json_encode(['status' => 'error', 'msg' => 'Questo ordine è stato annullato e non può essere aggiornato.']);
        exit;
    }

    // Leggi stato_venditore attuale di questo venditore su questo ordine
    $stmtCurr = $conn->prepare(
        "SELECT ii.stato_venditore FROM INCLUSO_IN ii
         JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
         WHERE ii.id_ordine = ? AND p.username = ? LIMIT 1"
    );
    $stmtCurr->bind_param("is", $idOrdine, $idVenditore);
    $stmtCurr->execute();
    $statoCorrente = $stmtCurr->get_result()->fetch_assoc()['stato_venditore'] ?? 'Pagato';
    $stmtCurr->close();

    // Non permettere di retrocedere
    if ($scalaStati[$nuovoStato] < ($scalaStati[$statoCorrente] ?? 0)) {
        echo json_encode(['status' => 'error', 'msg' => 'Non puoi retrocedere lo stato.']);
        exit;
    }

    // 1. Aggiorna stato_venditore sulle righe di questo venditore per questo ordine
    $stmtUpd = $conn->prepare(
        "UPDATE INCLUSO_IN ii
         JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
         SET ii.stato_venditore = ?
         WHERE ii.id_ordine = ? AND p.username = ?"
    );
    $stmtUpd->bind_param("sis", $nuovoStato, $idOrdine, $idVenditore);
    $stmtUpd->execute();
    $stmtUpd->close();

    // 2. Calcola il minimo stato tra tutti i venditori dell'ordine
    //    = lo stato "peggiore" (più basso nella scala)
    $stmtMin = $conn->prepare(
        "SELECT ii.stato_venditore FROM INCLUSO_IN ii
         JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
         WHERE ii.id_ordine = ?
         GROUP BY p.username"
    );
    $stmtMin->bind_param("i", $idOrdine);
    $stmtMin->execute();
    $resMin = $stmtMin->get_result();
    $stmtMin->close();

    $statoMinimo = 'Consegnato'; // parte dal massimo, scende
    while ($r = $resMin->fetch_assoc()) {
        $sv = $r['stato_venditore'] ?? 'Pagato';
        if (($scalaStati[$sv] ?? 0) < ($scalaStati[$statoMinimo] ?? 3)) {
            $statoMinimo = $sv;
        }
    }
    $resMin->free();

    // 3. Aggiorna ORDINE.stato con il minimo calcolato
    $stmtOrd = $conn->prepare("UPDATE ORDINE SET stato = ? WHERE id_ordine = ?");
    $stmtOrd->bind_param("si", $statoMinimo, $idOrdine);
    $stmtOrd->execute();
    $stmtOrd->close();

    echo json_encode(['status' => 'ok', 'statoVenditore' => $nuovoStato, 'statoOrdine' => $statoMinimo]);
}