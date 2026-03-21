<?php
session_start();
header('Content-Type: application/json');
require_once('../db_connect.php');

// Usiamo IdUtente o username a seconda di come hai settato la sessione nel login
if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Devi effettuare il login per ordinare.']);
    exit;
}

$idUtente = $_SESSION['IdUtente'];

// 1. Iniziamo la transazione: tutto o niente!
$conn->begin_transaction();

try {
    // 2. Recuperiamo gli articoli nel carrello e controlliamo sconti e stock
    // JOIN con PRODOTTO per i dati e PACCHETTO per lo sconto attivo
    $sqlCheck = "SELECT c.id_prodotto, c.quantita as QtaRichiesta,
                        p.quantita_disponibile as QtaDisp, p.nome as Titolo, p.prezzo as PrezzoBase,
                        pk.sconto as ScontoPacchetto
                 FROM CARRELLO c
                 JOIN PRODOTTO p ON c.id_prodotto = p.id_prodotto
                 LEFT JOIN PACCHETTO pk ON p.id_pacchetto = pk.id_pacchetto AND pk.attivo = 1
                 WHERE c.username = ?";

    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $idUtente);
    $stmtCheck->execute();
    $resCarrello = $stmtCheck->get_result();

    if ($resCarrello->num_rows === 0) {
        throw new Exception("Il tuo carrello è vuoto.");
    }

    $prodottiDaOrdinare = [];
    $totaleOrdine = 0;

    while ($item = $resCarrello->fetch_assoc()) {
        // CONTROLLO DISPONIBILITÀ
        if ($item['QtaDisp'] < $item['QtaRichiesta']) {
            throw new Exception("Spiacenti, il libro '".$item['Titolo']."' ha solo ".$item['QtaDisp']." copie disponibili.");
        }

        // CALCOLO PREZZO FINALE (Scontato se presente)
        $prezzoBase = (float)$item['PrezzoBase'];
        $sconto = (int)($item['ScontoPacchetto'] ?? 0);
        $prezzoScontato = $prezzoBase - ($prezzoBase * ($sconto / 100));

        $totaleOrdine += ($prezzoScontato * $item['QtaRichiesta']);

        // Prepariamo i dati per lo step successivo
        $item['PrezzoPagato'] = round($prezzoScontato, 2);
        $prodottiDaOrdinare[] = $item;
    }

    // 3. Creiamo l'ORDINE (tabella ORDINE)
    // Assicurati che le colonne siano corrette (username, data, totale, stato)
    $stmtOrd = $conn->prepare("INSERT INTO ORDINE (username, data, totale, stato) VALUES (?, NOW(), ?, 'In elaborazione')");
    $stmtOrd->bind_param("sd", $idUtente, $totaleOrdine);
    $stmtOrd->execute();
    $idNuovoOrdine = $conn->insert_id;

    // 4. Inseriamo i dettagli e scaliamo il magazzino
    $stmtDettaglio = $conn->prepare("INSERT INTO COMPOSIZIONE (id_ordine, id_prodotto, quantita, prezzo_acquisto) VALUES (?, ?, ?, ?)");
    $stmtUpdateStock = $conn->prepare("UPDATE PRODOTTO SET quantita_disponibile = quantita_disponibile - ? WHERE id_prodotto = ?");

    foreach ($prodottiDaOrdinare as $p) {
        $idP = $p['id_prodotto'];
        $qty = $p['QtaRichiesta'];
        $prezzoUn = $p['PrezzoPagato'];

        // Inserimento dettaglio ordine (tabella COMPOSIZIONE o DETTAGLIO)
        $stmtDettaglio->bind_param("iiid", $idNuovoOrdine, $idP, $qty, $prezzoUn);
        $stmtDettaglio->execute();

        // Scalo magazzino
        $stmtUpdateStock->bind_param("ii", $qty, $idP);
        $stmtUpdateStock->execute();
    }

    // 5. Svuotiamo il carrello dell'utente
    $stmtSvuota = $conn->prepare("DELETE FROM CARRELLO WHERE username = ?");
    $stmtSvuota->bind_param("s", $idUtente);
    $stmtSvuota->execute();

    // Se siamo arrivati qui senza errori, confermiamo tutto sul DB
    $conn->commit();
    echo json_encode(['status' => 'ok', 'idOrdine' => $idNuovoOrdine]);

} catch (Exception $e) {
    // Se c'è un errore, annulliamo tutto quello fatto sopra
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}