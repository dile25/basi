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
$nuovaCatPadre = trim($_POST['nuova_categoria_padre'] ?? '');
$padreSottocat = trim($_POST['padre_sottocategoria'] ?? '');
$padreSottocat = trim($_POST['padre_sottocategoria'] ?? '');
$tipoProdotto  = $_POST['tipo_prodotto'] ?? 'libro';

// Testata (per periodici/fumetti)
$testata              = trim($_POST['testata'] ?? '');
$nuovaTestata         = trim($_POST['nuova_testata'] ?? '');
$nuovaTestataPerio    = $_POST['nuova_testata_periodicita'] ?? 'mensile';

$abilitaSconto = isset($_POST['abilita_sconto']);

// --- Pacchetto libro (saga / autore / promo) ---
$idPacchettoEsist   = intval($_POST['id_pacchetto_esistente'] ?? 0);
$nomePacchetto      = trim($_POST['nome_pacchetto'] ?? '');
$libriPacchetto     = $_POST['libri_pacchetto'] ?? [];
$sconto2            = intval($_POST['sconto_2'] ?? 10);
$sconto3            = intval($_POST['sconto_3'] ?? 20);
$scontoTutti        = intval($_POST['sconto_tutti'] ?? 30);

// --- Abbonamento periodico ---
$idAbbonamentoEsist = intval($_POST['id_abbonamento_esistente'] ?? 0);
$nomeAbbonamento    = trim($_POST['nome_abbonamento'] ?? '');
$periodicita        = $_POST['periodicita'] ?? 'settimanale';
$numeriAbbonamento  = $_POST['numeri_abbonamento'] ?? [];
$scontoAbbonamento  = intval($_POST['sconto_abbonamento'] ?? 25);

$conn->begin_transaction();

try {
    $idPacchetto = null;
    $isPeriodico = in_array($tipoProdotto, ['rivista', 'magazine', 'periodico', 'fumetto']);

    // ===== GESTIONE TESTATA =====
    // Se è un prodotto periodico, risolvi la testata:
    // - testata esistente: usa quella
    // - nuova testata: crea i due pacchetti abbonamento (6 e 12 mesi) e salva la testata
    if ($isPeriodico) {
        if (!empty($nuovaTestata)) {
            // Verifica che non esista già
            $checkT = $conn->prepare("SELECT COUNT(*) as cnt FROM PACCHETTO WHERE testata = ? AND tipo_pacchetto = 'abbonamento'");
            $checkT->bind_param("s", $nuovaTestata);
            $checkT->execute();
            $esisteGia = $checkT->get_result()->fetch_assoc()['cnt'] > 0;
            $checkT->close();

            if (!$esisteGia) {
                // Crea pacchetto 6 mesi
                $nome6 = "Abbonamento {$nuovaTestata} - 6 mesi";
                $stmt6 = $conn->prepare(
                    "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, e_saga, periodicita, testata)
                     VALUES (?, NULL, 0, 0, 15, 1, 'abbonamento', 0, ?, ?)"
                );
                $stmt6->bind_param("sss", $nome6, $nuovaTestataPerio, $nuovaTestata);
                $stmt6->execute();
                $stmt6->close();

                // Crea pacchetto 12 mesi
                $nome12 = "Abbonamento {$nuovaTestata} - 12 mesi";
                $stmt12 = $conn->prepare(
                    "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, e_saga, periodicita, testata)
                     VALUES (?, NULL, 0, 0, 25, 1, 'abbonamento', 0, ?, ?)"
                );
                $stmt12->bind_param("sss", $nome12, $nuovaTestataPerio, $nuovaTestata);
                $stmt12->execute();
                $stmt12->close();
            }
            $testata = $nuovaTestata;
        }
        // $testata è ora valorizzata (esistente o appena creata)
    }

    $isAbbonamento = in_array($tipoProdotto, ['rivista', 'magazine', 'periodico']);

    if ($abilitaSconto) {
        if ($isAbbonamento && ($idAbbonamentoEsist > 0 || !empty($nomeAbbonamento))) {
            // ===== ABBONAMENTO PERIODICO =====
            if ($idAbbonamentoEsist > 0) {
                $checkOwn = $conn->prepare(
                    "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
                );
                $checkOwn->bind_param("is", $idAbbonamentoEsist, $user);
                $checkOwn->execute();
                $owns = $checkOwn->get_result()->fetch_assoc()['cnt'];
                if ($owns > 0) {
                    $idPacchetto = $idAbbonamentoEsist;
                }
            } else {
                // Crea nuovo pacchetto abbonamento: sconto_tutti = sconto_abbonamento,
                // sconto_2/sconto_3 a 0 perche' non si applicano scaglioni intermedi
                $stmtPack = $conn->prepare(
                    "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto, periodicita)
                     VALUES (?, ?, 0, 0, ?, 1, 'abbonamento', ?)"
                );
                $stmtPack->bind_param("siis", $nomeAbbonamento, $scontoAbbonamento, $scontoAbbonamento, $periodicita);
                $stmtPack->execute();
                $idPacchetto = $conn->insert_id;

                foreach ($numeriAbbonamento as $idNumero) {
                    $idNumero = intval($idNumero);
                    $stmtUpd = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
                    $stmtUpd->bind_param("iis", $idPacchetto, $idNumero, $user);
                    $stmtUpd->execute();
                }
            }
        } elseif ($idPacchettoEsist > 0) {
            // ===== PACCHETTO LIBRO ESISTENTE =====
            $checkOwn = $conn->prepare(
                "SELECT COUNT(*) as cnt FROM PRODOTTO WHERE id_pacchetto = ? AND username = ?"
            );
            $checkOwn->bind_param("is", $idPacchettoEsist, $user);
            $checkOwn->execute();
            $owns = $checkOwn->get_result()->fetch_assoc()['cnt'];
            if ($owns > 0) {
                $idPacchetto = $idPacchettoEsist;
            }
        } elseif (!empty($nomePacchetto)) {
            // ===== NUOVO PACCHETTO LIBRO con scaglioni =====
            $stmtPack = $conn->prepare(
                "INSERT INTO PACCHETTO (nome, sconto, sconto_2, sconto_3, sconto_tutti, attivo, tipo_pacchetto)
                 VALUES (?, ?, ?, ?, ?, 1, 'libro')"
            );
            $stmtPack->bind_param("siiii", $nomePacchetto, $sconto2, $sconto2, $sconto3, $scontoTutti);
            $stmtPack->execute();
            $idPacchetto = $conn->insert_id;

            foreach ($libriPacchetto as $idLibroPack) {
                $idLibroPack = intval($idLibroPack);
                $stmtUpd = $conn->prepare("UPDATE PRODOTTO SET id_pacchetto = ? WHERE id_prodotto = ? AND username = ?");
                $stmtUpd->bind_param("iis", $idPacchetto, $idLibroPack, $user);
                $stmtUpd->execute();
            }
        }
    }

    // Inserimento prodotto
    $sqlP = "INSERT INTO PRODOTTO (username, nome, autore, descrizione, prezzo, quantita_disponibile, tipo_prodotto, id_pacchetto, testata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtP = $conn->prepare($sqlP);
    $testataSave = !empty($testata) ? $testata : null;
    $stmtP->bind_param("ssssdisis", $user, $nome, $autore, $desc, $prezzo, $qta, $tipoProdotto, $idPacchetto, $testataSave);
    if (!$stmtP->execute()) throw new Exception("Errore inserimento prodotto");
    $idProdotto = $conn->insert_id;

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
    // ===== GESTIONE CATEGORIE =====
    $categoriaFinale = '';

    // Validazione: sottocategoria nuova deve avere un padre
    if (!empty($nuovaCat) && empty($nuovaCatPadre) && empty($padreSottocat) && empty($cat)) {
        throw new Exception('La nuova sottocategoria deve essere associata a una categoria padre.');
    }

    // 1. Crea nuova categoria padre (se compilata)
    if (!empty($nuovaCatPadre)) {
        $checkP = $conn->prepare("SELECT COUNT(*) as cnt FROM CATEGORIA WHERE nome_categoria = ?");
        $checkP->bind_param("s", $nuovaCatPadre);
        $checkP->execute();
        if ($checkP->get_result()->fetch_assoc()['cnt'] == 0) {
            $stmtP = $conn->prepare("INSERT INTO CATEGORIA (nome_categoria, nome_categoria_padre) VALUES (?, NULL)");
            $stmtP->bind_param("s", $nuovaCatPadre);
            $stmtP->execute();
            $stmtP->close();
        }
        $checkP->close();
    }

    // 2. Crea nuova sottocategoria (se compilata)
    if (!empty($nuovaCat)) {
        $padrePerFiglia = !empty($nuovaCatPadre) ? $nuovaCatPadre
                        : (!empty($padreSottocat) ? $padreSottocat
                        : (!empty($cat) ? $cat : null));

        $checkF = $conn->prepare("SELECT COUNT(*) as cnt FROM CATEGORIA WHERE nome_categoria = ?");
        $checkF->bind_param("s", $nuovaCat);
        $checkF->execute();
        if ($checkF->get_result()->fetch_assoc()['cnt'] == 0) {
            $stmtF = $conn->prepare("INSERT INTO CATEGORIA (nome_categoria, nome_categoria_padre) VALUES (?, ?)");
            $stmtF->bind_param("ss", $nuovaCat, $padrePerFiglia);
            $stmtF->execute();
            $stmtF->close();
        }
        $checkF->close();
        $categoriaFinale = $nuovaCat;
    } elseif (!empty($sottocat)) {
        $categoriaFinale = $sottocat;
    } elseif (!empty($cat)) {
        $categoriaFinale = $cat;
    } elseif (!empty($nuovaCatPadre)) {
        $categoriaFinale = $nuovaCatPadre;
    }

    if (!empty($categoriaFinale)) {
        $stmtD = $conn->prepare("INSERT INTO DESCRIVE (id_prodotto, nome_categoria) VALUES (?, ?)");
        $stmtD->bind_param("is", $idProdotto, $categoriaFinale);
        $stmtD->execute();
        $stmtD->close();
    }

    $conn->commit();
    ob_clean();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    $conn->rollback();
    ob_clean();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}