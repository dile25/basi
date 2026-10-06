<?php
/*
 * funzioni_carrello.php — logica del carrello riusata da ba_carrello.php e
 * ba_processa_ordine.php, così prezzi e sconti sono calcolati in un solo punto.
 *
 * Regola dei pacchetti: se nel carrello ci sono TUTTI i prodotti attivi di un
 * pacchetto, a ciascuno di essi si applica lo sconto fisso del pacchetto.
 */

/* Rimuove i prodotti non più in vendita e riporta le quantità entro la disponibilità.
   Restituisce quanti prodotti sono stati rimossi. */
function pulisciCarrello(mysqli $conn, string $username): int {
    $stmt = $conn->prepare(
        "DELETE c FROM carrello c
         JOIN prodotto p ON p.id_prodotto = c.id_prodotto
         WHERE c.username = ? AND (p.attivo = 0 OR p.quantita_disponibile = 0)"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $rimossi = $stmt->affected_rows;
    $stmt->close();

    $stmt = $conn->prepare(
        "UPDATE carrello c
         JOIN prodotto p ON p.id_prodotto = c.id_prodotto
         SET c.quantita_prodotto = p.quantita_disponibile
         WHERE c.username = ? AND c.quantita_prodotto > p.quantita_disponibile"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    return $rimossi;
}

/* Calcola righe, sconti e totale del carrello di un cliente */
function calcolaCarrello(mysqli $conn, string $username): array {
    $stmt = $conn->prepare(
        "SELECT c.id_prodotto, c.quantita_prodotto, p.nome, p.autore, p.prezzo,
                p.quantita_disponibile, p.id_pacchetto,
                pk.nome AS nome_pacchetto, pk.sconto,
                (SELECT COUNT(*) FROM prodotto p2
                  WHERE p2.id_pacchetto = p.id_pacchetto AND p2.attivo = 1) AS tot_pacchetto,
                (SELECT i.url FROM immagine_prodotto i
                  WHERE i.id_prodotto = p.id_prodotto
                  ORDER BY i.id_immagine_prodotto LIMIT 1) AS foto
         FROM carrello c
         JOIN prodotto p ON p.id_prodotto = c.id_prodotto
         LEFT JOIN pacchetto pk ON pk.id_pacchetto = p.id_pacchetto
         WHERE c.username = ? AND p.attivo = 1
         ORDER BY c.id_carrello"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    $righe = [];
    $nelCarrello = [];   // id_pacchetto => quanti suoi prodotti sono nel carrello
    while ($r = $res->fetch_assoc()) {
        $righe[] = $r;
        if ($r['id_pacchetto']) {
            $nelCarrello[$r['id_pacchetto']] = ($nelCarrello[$r['id_pacchetto']] ?? 0) + 1;
        }
    }
    $stmt->close();

    $prodotti = [];
    $totale = 0.0;
    foreach ($righe as $r) {
        $prezzo   = (float)$r['prezzo'];
        $qta      = (int)$r['quantita_prodotto'];
        $ip       = $r['id_pacchetto'];
        $nel      = $ip ? $nelCarrello[$ip] : 0;
        $tot      = (int)$r['tot_pacchetto'];
        $completo = $ip && $tot >= 2 && $nel >= $tot;
        $sconto   = $completo ? (float)$r['sconto'] : 0.0;

        $prezzoScontato = round($prezzo * (1 - $sconto / 100), 2);
        $subtotale      = round($prezzoScontato * $qta, 2);
        $totale        += $subtotale;

        $prodotti[] = [
            'IdProdotto'                   => (int)$r['id_prodotto'],
            'nome'                         => $r['nome'],
            'autore'                       => $r['autore'],
            'URLfoto'                      => $r['foto'] ?? 'img/default.jpg',
            'quantita'                     => $qta,
            'quantitaDisponibile'          => (int)$r['quantita_disponibile'],
            'prezzoOriginale'              => $prezzo,
            'prezzoScontato'               => $prezzoScontato,
            'subtotale'                    => $subtotale,
            'nomePacchetto'                => $r['nome_pacchetto'],
            'percentualeSconto'            => $ip ? (float)$r['sconto'] : 0,
            'pacchettoCompleto'            => $completo,
            'prodottiPacchettoNelCarrello' => $nel,
            'prodottiPacchettoTotale'      => $tot,
        ];
    }

    return ['prodotti' => $prodotti, 'totale' => round($totale, 2)];
}
