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
                   o.data AS DataOrdine,
                   o.stato AS Stato,
                   o.username AS Cliente,
                   o.totale AS Totale,
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
                'IdOrdine'   => $id,
                'DataOrdine' => date("d/m/Y", strtotime($row['DataOrdine'])),
                'Stato'      => $row['Stato'],
                'Cliente'    => $row['Cliente'],
                'Totale'     => $row['Totale'],
                'libri'      => []
            ];
        }
        $ordini[$id]['libri'][] = [
            'IdProdotto' => $row['IdProdotto'],
            'Titolo'     => $row['Titolo'],
            'Quantita'   => $row['Quantita'],
            'Prezzo'     => $prezzoEffettivo,
            'Foto'       => $row['Foto'] ?? 'img/default.jpg'
        ];
    }

    // Guadagno: usa prezzo_unitario (con sconto reale applicato) se disponibile
    $sqlGuadagno = "SELECT SUM(
                        COALESCE(ii.prezzo_unitario, p.prezzo) * ii.quantita_prodotto
                    ) as guadagno
                    FROM INCLUSO_IN ii
                    JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
                    JOIN ORDINE o ON ii.id_ordine = o.id_ordine
                    WHERE p.username = ? AND o.stato IN ('Spedito','Consegnato')";
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
    $idOrdine    = intval($_POST['idOrdine'] ?? 0);
    $nuovoStato  = $_POST['stato'] ?? '';

    if (!$idOrdine || !$nuovoStato) {
        echo json_encode(['status' => 'error', 'msg' => 'Dati mancanti']);
        exit;
    }

    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM INCLUSO_IN ii
                             JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
                             WHERE ii.id_ordine = ? AND p.username = ?");
    $check->bind_param("is", $idOrdine, $idVenditore);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];

    if ($cnt === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Ordine non autorizzato']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE ORDINE SET stato = ? WHERE id_ordine = ?");
    $stmt->bind_param("si", $nuovoStato, $idOrdine);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Errore database']);
    }
}