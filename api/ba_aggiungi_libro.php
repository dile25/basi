<?php
ob_start();
session_start();
require_once('../db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Accesso non autorizzato']);
    exit;
}

$user          = $_SESSION['IdUtente'];
$nome          = $_POST['nome'] ?? '';
$autore        = $_POST['autore'] ?? '';
$desc          = $_POST['descrizione'] ?? '';
$prezzo        = floatval($_POST['prezzo'] ?? 0);
$qta           = intval($_POST['quantita'] ?? 0);
$cat           = $_POST['categoria'] ?? '';
$sottocat      = $_POST['sottocategoria'] ?? '';
$nuovaCat      = trim($_POST['nuova_categoria'] ?? '');
$tipoProdotto  = $_POST['tipo_prodotto'] ?? 'libro';

$abilitaSconto   = isset($_POST['abilita_sconto']);
$tipoSconto      = $_POST['tipo_sconto'] ?? '';
$percSconto      = intval($_POST['percentuale_sconto'] ?? 0);
$nomePacchetto   = trim($_POST['nome_pacchetto'] ?? '');
$libriPacchetto  = $_POST['libri_pacchetto'] ?? [];
$scontoPacchetto = intval($_POST['sconto_pacchetto'] ?? 0);

$conn->begin_transaction();

try {
    $idPacchetto = null;

    if ($abilitaSconto) {
        if ($tipoSconto === 'sconto_semplice' && $percSconto > 0) {
            $nPack = 'Sconto ' . $percSconto . '% su ' . $nome;
            $stmtPack = $conn->prepare("INSERT INTO PACCHETTO (nome, sconto, attivo) VALUES (?, ?, 1)");
            $stmtPack->bind_param("si", $nPack, $percSconto);
            $stmtPack->execute();
            $idPacchetto = $conn->insert_id;

        } elseif (in_array($tipoSconto, ['pacchetto_autore','pacchetto_saga','pacchetto_custom']) && $scontoPacchetto > 0) {
            $nPack = !empty($nomePacchetto) ? $nomePacchetto : 'Pacchetto ' . $nome;
            $stmtPack = $conn->prepare("INSERT INTO PACCHETTO (nome, sconto, attivo) VALUES (?, ?, 1)");
            $stmtPack->bind_param("si", $nPack, $scontoPacchetto);
            $stmtPack->execute();
            $idPacchetto = $conn->insert_id;

            // Aggiorna gli altri prodotti del pacchetto
            foreach ($libriPacchetto as $idLibroPack) {
                $idLibroPack = intval($idLibroPack);
                $stmtUpd = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
                $stmtUpd->bind_param("iis", $idPacchetto, $idLibroPack, $user);
                $stmtUpd->execute();
            }
        }
    }

    // Inserimento prodotto
    $sqlP = "INSERT INTO PRODOTTO (username, nome, autore, descrizione, prezzo, quantita_disponibile, tipo_prodotto, id_pacchetto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtP = $conn->prepare($sqlP);
    $stmtP->bind_param("ssssdisi", $user, $nome, $autore, $desc, $prezzo, $qta, $tipoProdotto, $idPacchetto);
    if (!$stmtP->execute()) throw new Exception("Errore inserimento prodotto");
    $idProdotto = $conn->insert_id;

    // Se pacchetto, aggiorna anche questo nuovo prodotto
    if ($idPacchetto && $tipoSconto !== 'sconto_semplice') {
        $stmtSelf = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ?");
        $stmtSelf->bind_param("ii", $idPacchetto, $idProdotto);
        $stmtSelf->execute();
    }

    // Foto
    if (isset($_FILES['fotoLibro']) && $_FILES['fotoLibro']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/basi/img/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($_FILES['fotoLibro']['name'], PATHINFO_EXTENSION);
        $fileName = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['fotoLibro']['tmp_name'], $uploadDir . $fileName)) {
            $urlDb = 'img/' . $fileName;
            $stmtI = $conn->prepare("INSERT INTO IMMAGINE_PRODOTTO (id_prodotto, url, alt_text) VALUES (?, ?, ?)");
            $alt = "Copertina " . $nome;
            $stmtI->bind_param("iss", $idProdotto, $urlDb, $alt);
            $stmtI->execute();
        } else {
            throw new Exception("Impossibile salvare l'immagine");
        }
    }

    // Categoria
    $categoriaFinale = '';
    if (!empty($nuovaCat)) {
        $checkCat = $conn->prepare("SELECT nome_categoria FROM CATEGORIA WHERE nome_categoria = ?");
        $checkCat->bind_param("s", $nuovaCat);
        $checkCat->execute();
        if ($checkCat->get_result()->num_rows === 0) {
            $stmtCat = $conn->prepare("INSERT INTO CATEGORIA (nome_categoria, nome_categoria_padre) VALUES (?, ?)");
            $padre = !empty($cat) ? $cat : null;
            $stmtCat->bind_param("ss", $nuovaCat, $padre);
            $stmtCat->execute();
        }
        $categoriaFinale = $nuovaCat;
    } elseif (!empty($sottocat)) {
        $categoriaFinale = $sottocat;
    } elseif (!empty($cat)) {
        $categoriaFinale = $cat;
    }

    if (!empty($categoriaFinale)) {
        $stmtD = $conn->prepare("INSERT INTO DESCRIVE (id_prodotto, nome_categoria) VALUES (?, ?)");
        $stmtD->bind_param("is", $idProdotto, $categoriaFinale);
        $stmtD->execute();
    }

    $conn->commit();
    ob_clean();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}