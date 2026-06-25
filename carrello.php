<?php
session_start();
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il Mio Carrello | The (E-)Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- stili in style.css -->
</head>
<body>
<?php include("header.php"); ?>

<div class="container" style="max-width:1200px; margin:0 auto; padding:20px;">
    <h2 style="color:var(--dark-green);">Il tuo Carrello</h2>

    <div id="cart-wrapper" class="cart-container">
        <div>
            <div id="avviso-rimossi" style="display:none; background:#fff3cd; border:1px solid #ffc107; border-radius:10px; padding:12px 16px; margin-bottom:14px; color:#856404; font-size:0.92em; align-items:center; gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;flex-shrink:0;"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                <span id="testo-rimossi"></span>
            </div>
            <div id="cart-items-list"><p>Caricamento...</p></div>
            <!-- BOX ABBONAMENTO: appare se nel carrello c'è almeno una rivista/magazine/fumetto/periodico -->
            <div id="box-abbonamento" style="display:none; margin-top:20px; background:#f5eef8; border:2px solid #9b59b6; border-radius:14px; padding:22px;">
                <h3 style="margin:0 0 6px; color:#6c3483; font-size:1.05em; display:flex; align-items:center; gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8e44ad" style="width:20px;height:20px;"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                    Risparmia con un abbonamento
                </h3>
                <p style="margin:0 0 14px; font-size:0.88em; color:#555;">Hai aggiunto una pubblicazione periodica. Abbonati e ricevi ogni numero con uno sconto esclusivo:</p>
                <div id="lista-abbonamenti" style="display:flex; flex-wrap:wrap; gap:12px;"></div>
            </div>
            <div class="suggestions-box">
                <h3 style="margin:0;">Potrebbero interessarti anche</h3>
                <div id="consigliati-list" class="suggestions-grid"></div>
            </div>
        </div>

        <div class="summary-card">
            <h3 style="margin-top:0;">Riepilogo Ordine</h3>
            <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span>Titoli:</span><strong id="cart-count-label">0</strong>
            </div>
            <div id="sconti-riepilogo" style="font-size:0.82em; color:var(--primary-green); display:none; margin-bottom:10px;"></div>
            <div style="display:flex; justify-content:space-between; font-size:1.4em; font-weight:800; color:var(--dark-green); margin-top:10px;">
                <span>Totale:</span><span id="cart-total-label">€0.00</span>
            </div>
            <button class="btn-checkout" onclick="window.location.href='checkout.php'">Procedi al Pagamento</button>
            <p style="font-size:0.8em; color:#999; text-align:center; margin-top:12px;">Pagamento sicuro e protetto.</p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    caricaCarrello();
    caricaConsigliati();
    caricaSuggerimentoAbbonamento();
});

function caricaCarrello() {
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        // Mostra avviso prodotti rimossi (venditore cancellato)
        if (resp.prodottiRimossi && resp.prodottiRimossi > 0) {
            const n = resp.prodottiRimossi;
            $('#testo-rimossi').text(n + (n === 1 ? ' prodotto è stato rimosso' : ' prodotti sono stati rimossi') + ' dal carrello perché non più disponibili (il venditore ha chiuso il suo account).');
            $('#avviso-rimossi').css('display', 'flex');
        }
        if (!resp.prodotti || resp.prodotti.length === 0) {
            // Se ci sono rimossi mostra avviso sopra al messaggio carrello vuoto
            const avviso = $('#avviso-rimossi').prop('outerHTML');
            $("#cart-wrapper").html(`
                <div style='grid-column:1/-1;'>
                    ${resp.prodottiRimossi > 0 ? avviso : ''}
                    <div style='text-align:center; padding:80px;'>
                        <h3>Il carrello è vuoto.</h3>
                        <a href='index.php' class='btn-primary' style='padding:12px 25px; text-decoration:none; border-radius:8px; display:inline-block; margin-top:10px;'>Esplora i Prodotti</a>
                    </div>
                </div>`);
            return;
        }

        let html = '';
        let scontiHtml = '';
        const totale = parseFloat(resp.totaleCart || 0);

        resp.prodotti.forEach(p => {
            const maxQta  = parseInt(p.quantitaDisponibile) || 99;
            const qtaAtt  = parseInt(p.quantita) || 1;
            const prezzo  = parseFloat(p.prezzoOriginale);
            const prezzoSc = parseFloat(p.prezzoScontato);
            const foto    = p.URLfoto || 'img/default.jpg';

            let badgeHtml = '';
            if (p.scontoAutore) {
                badgeHtml = `<span class="badge-autore">Promo Autore -${p.percentualeSconto}%</span>`;
                if (!scontiHtml.includes('Autore')) scontiHtml += `<div>Sconto autore (x2 libri): -${p.percentualeSconto}%</div>`;
            } else if (p.tipoSconto === 'abbonamento_completo') {
                badgeHtml = `<span class="badge-pacchetto">Abbonamento completo -${p.percentualeSconto}%</span>`;
            } else if (p.tipoPacchetto === 'abbonamento' && p.percentualeSconto == 0) {
                badgeHtml = `<small style="color:var(--text-sec); font-size:0.78em;">${p.libriPackNelCarrello}/${p.libriPackTotale} numeri nel carrello — aggiungi tutti per lo sconto</small>`;
            } else if (p.percentualeSconto > 0) {
                badgeHtml = `<span class="badge-pacchetto">Offerta -${p.percentualeSconto}%</span>`;
            }

            html += `
            <div class="cart-item">
                <img src="${foto}"
                     onclick="location.href='dettaglio_prodotto.php?id=${p.IdProdotto}'"
                     style="width:80px;height:110px;object-fit:cover;border-radius:6px;margin-right:20px;border:1px solid #eee;cursor:pointer;">
                <div style="flex-grow:1;">
                    <h4 style="margin:0 0 4px; cursor:pointer;"
                        onclick="location.href='dettaglio_prodotto.php?id=${p.IdProdotto}'">${p.nome}</h4>
                    ${p.autore ? `<small style="color:#888;">${p.autore}</small><br>` : ''}
                    ${badgeHtml}
                    <div style="margin-top:6px;">
                        <span style="font-weight:bold; color:var(--primary-green); font-size:1.1em;">€${prezzoSc.toFixed(2)}</span>
                        ${prezzoSc < prezzo ? `<small style="text-decoration:line-through; color:#bbb; margin-left:6px;">€${prezzo.toFixed(2)}</small>` : ''}
                    </div>
                    <button class="btn-remove" onclick="rimuoviDalCarrello(${p.IdProdotto})" style="display:inline-flex;align-items:center;gap:4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:13px;height:13px;"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                        Rimuovi
                    </button>
                </div>
                <div style="text-align:right;">
                    <label style="font-size:0.8em; color:#888; display:block; margin-bottom:6px;">Quantità</label>
                    <div class="qty-wrapper">
                        <button class="qty-btn" onclick="cambiaQta(${p.IdProdotto}, -1, ${maxQta})" ${qtaAtt <= 1 ? 'disabled' : ''}>−</button>
                        <span class="qty-display" id="qty-${p.IdProdotto}">${qtaAtt}</span>
                        <button class="qty-btn" onclick="cambiaQta(${p.IdProdotto}, 1, ${maxQta})" ${qtaAtt >= maxQta ? 'disabled' : ''}>+</button>
                    </div>
                    <div style="font-weight:800; font-size:1.1em; margin-top:8px;">€${p.subtotale.toFixed(2)}</div>
                </div>
            </div>`;
        });

        $("#cart-items-list").html(html);
        $("#cart-total-label").text("€" + totale.toFixed(2));
        $("#cart-count-label").text(resp.prodotti.length);
        if (scontiHtml) $("#sconti-riepilogo").html("<strong>Sconti attivi:</strong>" + scontiHtml).show();

    }, "json");
}

function cambiaQta(id, delta, maxQta) {
    const display = $(`#qty-${id}`);
    let qta = parseInt(display.text()) + delta;
    if (qta < 1) qta = 1;
    if (qta > maxQta) { alert('Limite disponibile raggiunto.'); return; }
    $.post('api/ba_carrello.php', { action: 'update', idProdotto: id, qty: qta }, function() {
        caricaCarrello();
        if (typeof updateCartBadge === 'function') updateCartBadge();
    }, "json");
}

function rimuoviDalCarrello(id) {
    if (confirm("Rimuovere questo prodotto?")) {
        $.post('api/ba_carrello.php', { action: 'remove', idProdotto: id }, function() {
            caricaCarrello();
            if (typeof updateCartBadge === 'function') updateCartBadge();
        }, "json");
    }
}

function caricaSuggerimentoAbbonamento() {
    $.get('api/ba_abbonamenti_disponibili.php', function(resp) {
        if (resp.status !== 'ok' || !resp.abbonamenti || resp.abbonamenti.length === 0) return;

        let html = '';
        resp.abbonamenti.forEach(a => {
            html += `
            <div style="flex:1; min-width:200px; background:white; border:1px solid #d2b4de; border-radius:10px; padding:14px;">
                <div style="font-weight:700; color:#6c3483; margin-bottom:4px; font-size:0.92em;">${a.nome}</div>
                <div style="font-size:0.82em; color:#555; margin-bottom:10px;">${a.descrizione || ''}</div>
                <div style="font-size:0.9em; font-weight:700; color:#8e44ad; margin-bottom:10px;">
                    Sconto ${a.sconto_tutti}% su tutti i numeri del periodo
                </div>
                <button onclick="aggiungiAbbonamento(${a.id_pacchetto})"
                    style="background:#8e44ad; color:white; border:none; padding:9px 16px; border-radius:8px; font-weight:700; cursor:pointer; font-size:0.85em; width:100%; font-family:inherit;">
                    Abbonati ora
                </button>
            </div>`;
        });

        $('#lista-abbonamenti').html(html);
        $('#box-abbonamento').show();
    }, 'json');
}

function aggiungiAbbonamento(idPacchetto) {
    // Simula l'aggiunta all'abbonamento: mostra conferma
    // (implementazione reale richiederebbe logica dedicata)
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Richiesta inviata!';
    btn.style.background = 'var(--dark-green)';
}

function caricaConsigliati() {
    $.get('api/ba_consigliati.php', function(resp) {
        if (resp.status === 'ok' && resp.consigliati && resp.consigliati.length > 0) {
            let html = '';
            resp.consigliati.slice(0, 4).forEach(c => {
                html += `
                <div class="suggestion-card">
                    <img src="${c.Foto || 'img/default.jpg'}" alt="${c.nome}">
                    <h5 style="margin:8px 0 4px; font-size:0.85em; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${c.nome}</h5>
                    <div style="color:var(--dark-green); font-weight:bold; font-size:0.9em; margin-bottom:8px;">€${parseFloat(c.prezzo).toFixed(2)}</div>
                    <a href="dettaglio_prodotto.php?id=${c.id_prodotto}" class="btn-primary" style="font-size:0.78em; padding:5px 10px; text-decoration:none; border-radius:5px; display:inline-block;">Vedi</a>
                </div>`;
            });
            $("#consigliati-list").html(html);
        } else {
            $("#consigliati-list").html("<p style='color:#bbb; font-size:0.9em;'>Nessun consiglio disponibile.</p>");
        }
    }, "json");
}
</script>
</body>
</html>