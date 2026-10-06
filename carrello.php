<?php
session_start();
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Il mio carrello | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page">
    <h1 class="page-title">Il tuo carrello</h1>

    <div id="avviso-rimossi" class="avviso is-hidden" role="status">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
        <span id="testo-rimossi"></span>
    </div>

    <div id="cart-wrapper" class="cart-container">
        <div id="cart-items-list" aria-live="polite"><p class="loading">Caricamento...</p></div>

        <aside class="summary-card" aria-labelledby="titolo-riepilogo">
            <h2 id="titolo-riepilogo">Riepilogo ordine</h2>
            <div class="summary-row"><span>Articoli</span><strong id="cart-count-label">0</strong></div>
            <div id="sconti-riepilogo" class="summary-sconti is-hidden"></div>
            <div class="summary-row summary-total"><span>Totale</span><span id="cart-total-label">€0.00</span></div>
            <a href="checkout.php" class="btn btn-primary btn-block btn-large">Vai al pagamento</a>
            <p class="summary-note">Il pagamento è simulato: nessun addebito reale.</p>
        </aside>
    </div>

    <div id="cart-vuoto" class="empty-state is-hidden">
        <h2>Il carrello è vuoto</h2>
        <p>Sfoglia il catalogo e aggiungi i libri che ti interessano.</p>
        <a href="index.php" class="btn btn-primary">Esplora il catalogo</a>
    </div>
</main>

<script>
$(function() {
    caricaCarrello();
});

function caricaCarrello() {
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        // Prodotti tolti dal carrello perché il venditore ha chiuso l'account
        const rimossi = parseInt(resp.prodottiRimossi) || 0;
        if (rimossi > 0) {
            $('#testo-rimossi').text(rimossi + (rimossi === 1 ? ' prodotto è stato rimosso' : ' prodotti sono stati rimossi')
                + ' dal carrello perché non più disponibili.');
            $('#avviso-rimossi').removeClass('is-hidden');
        }

        const prodotti = resp.prodotti || [];
        if (prodotti.length === 0) {
            $('#cart-wrapper').addClass('is-hidden');
            $('#cart-vuoto').removeClass('is-hidden');
            return;
        }

        let html = '';
        let articoli = 0;
        const pacchettiScontati = {};

        prodotti.forEach(p => {
            const id       = parseInt(p.IdProdotto);
            const maxQta   = parseInt(p.quantitaDisponibile) || 1;
            const qta      = parseInt(p.quantita) || 1;
            const prezzo   = parseFloat(p.prezzoOriginale);
            const prezzoSc = parseFloat(p.prezzoScontato);
            const link     = 'dettaglio_prodotto.php?id=' + id;
            articoli += qta;

            // Sconto pacchetto: unico sconto fisso se nel carrello ci sono TUTTI i prodotti del pacchetto
            let infoPacchetto = '';
            if (p.nomePacchetto) {
                if (p.pacchettoCompleto) {
                    infoPacchetto = `<span class="badge badge-pacchetto">Pacchetto completo -${parseInt(p.percentualeSconto)}%</span>`;
                    pacchettiScontati[p.nomePacchetto] = parseInt(p.percentualeSconto);
                } else {
                    infoPacchetto = `<p class="pacchetto-hint">${parseInt(p.prodottiPacchettoNelCarrello)} di ${parseInt(p.prodottiPacchettoTotale)}
                        prodotti del pacchetto "${escapeHtml(p.nomePacchetto)}": aggiungili tutti per avere il ${parseInt(p.percentualeSconto)}% di sconto.</p>`;
                }
            }

            html += `
            <article class="cart-item">
                <a href="${link}" class="cart-item__img-link" tabindex="-1" aria-hidden="true">
                    <img class="cart-item__img" src="${escapeHtml(p.URLfoto || 'img/default.jpg')}" alt="">
                </a>
                <div class="cart-item__body">
                    <h3 class="cart-item__title"><a href="${link}">${escapeHtml(p.nome)}</a></h3>
                    ${p.autore ? `<p class="muted">${escapeHtml(p.autore)}</p>` : ''}
                    ${infoPacchetto}
                    <p class="cart-item__price">
                        <strong>${formatPrezzo(prezzoSc)}</strong>
                        ${prezzoSc < prezzo ? `<del class="book-price-old">${formatPrezzo(prezzo)}</del>` : ''}
                    </p>
                    <button type="button" class="btn-remove js-rimuovi" data-id="${id}">Rimuovi</button>
                </div>
                <div class="cart-item__qty">
                    <span class="form-label" id="lbl-qta-${id}">Quantità</span>
                    <div class="qty-wrapper" role="group" aria-labelledby="lbl-qta-${id}">
                        <button type="button" class="qty-btn js-qta" data-id="${id}" data-qta="${qta - 1}" aria-label="Diminuisci quantità" ${qta <= 1 ? 'disabled' : ''}>&minus;</button>
                        <span class="qty-display">${qta}</span>
                        <button type="button" class="qty-btn js-qta" data-id="${id}" data-qta="${qta + 1}" aria-label="Aumenta quantità" ${qta >= maxQta ? 'disabled' : ''}>+</button>
                    </div>
                    <p class="cart-item__subtotal">${formatPrezzo(p.subtotale)}</p>
                </div>
            </article>`;
        });

        $('#cart-items-list').html(html);
        $('#cart-total-label').text(formatPrezzo(resp.totaleCart));
        $('#cart-count-label').text(articoli);

        const nomi = Object.keys(pacchettiScontati);
        if (nomi.length) {
            $('#sconti-riepilogo').html('<strong>Sconti applicati</strong>' +
                nomi.map(n => `<div>Pacchetto "${escapeHtml(n)}": -${pacchettiScontati[n]}%</div>`).join(''))
                .removeClass('is-hidden');
        } else {
            $('#sconti-riepilogo').addClass('is-hidden');
        }
    }, 'json');
}

$(document).on('click', '.js-qta', function() {
    $.post('api/ba_carrello.php', { action: 'update', idProdotto: $(this).data('id'), qty: $(this).data('qta') }, function(resp) {
        if (resp.status !== 'ok') mostraNotifica(resp.msg || 'Quantità non disponibile.', true);
        caricaCarrello();
        updateCartBadge();
    }, 'json');
});

$(document).on('click', '.js-rimuovi', function() {
    if (!confirm('Rimuovere questo prodotto dal carrello?')) return;
    $.post('api/ba_carrello.php', { action: 'remove', idProdotto: $(this).data('id') }, function() {
        caricaCarrello();
        updateCartBadge();
    }, 'json');
});

</script>
</body>
</html>
