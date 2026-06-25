<?php
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Non autorizzato']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$id       = $_SESSION['IdUtente'];
$tipo     = $_SESSION['tipoUtente'];
$conferma = trim($input['conferma'] ?? '');

// L'utente deve digitare il proprio username per confermare
if ($conferma !== $id) {
    echo json_encode(['status' => 'error', 'msg' => 'Username di conferma errato.']);
    exit;
}

$conn->begin_transaction();

try {
    if ($tipo === 'venditore') {
        // Nascondi tutti i prodotti del venditore
        $stmtProd = $conn->prepare("UPDATE PRODOTTO SET attivo = 0 WHERE username = ?");
        $stmtProd->bind_param("s", $id);
        $stmtProd->execute();
        $stmtProd->close();

        // Rimuovi i prodotti del venditore dai carrelli di tutti i clienti
        $stmtCarrelli = $conn->prepare(
            "DELETE FROM CARRELLO WHERE id_prodotto IN (SELECT id_prodotto FROM PRODOTTO WHERE username = ?)"
        );
        $stmtCarrelli->bind_param("s", $id);
        $stmtCarrelli->execute();
        $stmtCarrelli->close();

        // Annulla ordini ancora pendenti che contengono suoi prodotti
        // (stato "Pagato" o "In lavorazione" — non toccare Spedito/Consegnato)
        $stmtAnn = $conn->prepare("
            UPDATE ORDINE o
            SET o.stato = 'Annullato'
            WHERE o.stato IN ('Pagato', 'In lavorazione')
              AND EXISTS (
                SELECT 1 FROM INCLUSO_IN ii
                JOIN PRODOTTO p ON ii.id_prodotto = p.id_prodotto
                WHERE ii.id_ordine = o.id_ordine AND p.username = ?
              )
        ");
        $stmtAnn->bind_param("s", $id);
        $stmtAnn->execute();
        $stmtAnn->close();

    } elseif ($tipo === 'cliente') {
        // Svuota carrello
        $stmtCarr = $conn->prepare("DELETE FROM CARRELLO WHERE username = ?");
        $stmtCarr->bind_param("s", $id);
        $stmtCarr->execute();
        $stmtCarr->close();

        // Svuota preferiti
        $stmtPref = $conn->prepare("DELETE FROM PREFERITI WHERE username = ?");
        $stmtPref->bind_param("s", $id);
        $stmtPref->execute();
        $stmtPref->close();

        // Gli ordini restano intatti (storia acquisti per i venditori)
        // Le recensioni restano (contenuto pubblico utile)
    }

    // Disattiva account (soft delete)
    $stmtDis = $conn->prepare("UPDATE UTENTE SET attivo = 0 WHERE username = ?");
    $stmtDis->bind_param("s", $id);
    $stmtDis->execute();
    $stmtDis->close();

    $conn->commit();

    // Distruggi sessione
    session_destroy();

    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}