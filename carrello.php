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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Mio Carrello | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .cart-container { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px; }
        @media (max-width: 768px) { .cart-container { grid-template-columns: 1fr; } }

        .cart-item { display: flex; align-items: center; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 12px; margin-bottom: 15px; transition: 0.2s; }
        .cart-item:hover { border-color: var(--primary-green); }

        .summary-card { background: #f9fbf9; padding: 25px; border-radius: 12px; position: sticky; top: 20px; height: fit-content; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

        /* Pulsanti +/- quantità */
        .qty-wrapper { display: flex; align-items: center; gap: 6px; }
        .qty-btn {
            width: 28px; height: 28px;
            border: 2px solid var(--primary-green, #27ae60);
            background: white; color: var(--primary-green, #27ae60);
            border-radius: 50%; font-size: 1.1em; font-weight: bold;
            cursor: pointer; line-height: 1;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s; padding: 0;
        }
        .qty-btn:hover { background: var(--primary-green, #27ae60); color: white; }
        .qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .qty-display { min-width: 36px; text-align: center; font-weight: bold; font-size: 1.05em; }
        .qty-max-msg { font-size: 0.75em; color: #e67e22; margin-top: 4px; display: none; }

        .btn-remove { color: #e74c3c; background: none; border: none; cursor: pointer; font-weight: bold; font-size: 0.9em; margin-top: 8px; padding: 0; text-decoration: underline; }

        .btn-checkout { background: var(--primary-green, #27ae60); color: white; border: none; padding: 16px; border-radius: 8px; font-weight: 800; width: 100%; cursor: pointer; font-size: 1.1em; margin-top: 20px; transition: filter 0.2s; }
        .btn-checkout:hover { filter: brightness(1.1); }

        /* Consigliati */
        .suggestions-box { margin-top: 40px; background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #eee; }
        .suggestions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
        .suggestion-card { border: 1px solid #eee; padding: 12px; border-radius: 10px; text-align: center; transition: box-shadow 0.2s; }
        .suggestion-card:hover { box-shadow: 0 4px 12px rgba(39,174,96,0.12); border-color: #c8e6c9; }
        .suggestion-card img { width: 100%; height: 120px; object-fit: cover; border-radius: 6px; }
        .suggestion-card h5 { margin: 8px 0 4px; font-size: 0.85em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Badge sconti */
        .badge-autore { display:inline-block; background:#27ae60; color:white; padding:2px 8px; border-radius:4px; font-size:0.75em; font-weight:bold; margin-top:4px; }
        .badge-pacchetto { display:inline-block; background:linear-gradient(135deg,#f39c12,#e74c3c); color:white; padding:2px 8px; border-radius:4px; font-size:0.75em; font-weight:bold; margin-top:4px; }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="container" style="max-width:1200px; margin:0 auto; padding:20px;">
        <h2 style="color:var(--dark-green);">🛒 Il tuo Carrello</h2>

        <div id="cart-wrapper" class="cart-container">
            <div>
                <div id="cart-items-list">
                    <p>Caricamento in corso...</p>
                </div>

                <div class="suggestions-box">
                    <h3 style="margin:0;">💡 Ti potrebbero interessare anche...</h3>
                    <div id="consigliati-list" class="suggestions-grid">
                        <p style="color:#ccc;">Caricamento consigli...</p>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h3 style="margin-top:0;">Riepilogo Ordine</h3>
                <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span>Titoli:</span>
                    <strong id="cart-count-label">0</strong>
                </div>
                <div id="sconti-riepilogo" style="font-size:0.82em; color:#27ae60; display:none; margin-bottom:10px;"></div>
                <div style="display:flex; justify-content:space-between; font-size:1.4em; font-weight:800; color:var(--primary-green, #27ae60); margin-top:10px;">
                    <span>Totale:</span>
                    <span id="cart-total-label">€0.00</span>
                </div>
                <button class="btn-checkout" onclick="window.location.href='checkout.php'">🔒 PROCEDI AL PAGAMENTO ➔</button>
                <p style="font-size:0.8em; color:#999; text-align:center; margin-top:12px; line-height:1.4;">Pagamento sicuro e protetto.</p>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        caricaCarrello();
        caricaConsigliati();
    });

    // ── usa ba_get_carrello.php (la tua API originale) ──────────────
    function caricaCarrello() {
        $.get('api/ba_get_carrello.php', function(resp) {
            if (!resp.prodotti || resp.prodotti.length === 0) {
                $("#cart-wrapper").html(`
                    <div style='text-align:center; padding:80px; grid-column:1/-1;'>
                        <div style='font-size:4em;'>🛒</div>
                        <h3>Il carrello è vuoto.</h3>
                        <a href='index.php' class='btn-primary' style='padding:12px 25px; text-decoration:none; border-radius:8px; display:inline-block; margin-top:10px;'>Esplora i Libri</a>
                    </div>`);
                return;
            }

            let html = "";
            let scontiHtml = "";
            let totale = parseFloat(resp.totale || resp.totaleGenerale || 0);

            resp.prodotti.forEach(p => {
                const maxQta   = parseInt(p.QuantitaDisp) || 99;
                const qtaAtt   = parseInt(p.QuantitaNelCarrello) || 1;
                const prezzo   = parseFloat(p.PrezzoBase || p.prezzo || 0);
                const prezzoSc = parseFloat(p.PrezzoEffettivo || p.prezzoScontato || prezzo);
                const foto     = p.URLfoto || p.Foto || 'img/default.jpg';
                const titolo   = p.Titolo || p.nome || '';
                const autore   = p.autore || p.Autore || '';

                // Badge sconto
                let badgeHtml = '';
                if (p.scontoAutore || p.ScontoAutore) {
                    const perc = p.percentualeSconto || p.PercentualeSconto || 20;
                    badgeHtml = `<span class="badge-autore">🔥 Promo Autore -${perc}%</span>`;
                    if (!scontiHtml.includes('Autore')) scontiHtml += `<div>• Sconto autore (×2): -${perc}%</div>`;
                } else if (p.nomePacchetto || p.NomePacchetto) {
                    const nome = p.nomePacchetto || p.NomePacchetto;
                    const perc = p.percentualeSconto || p.PercentualeSconto || 0;
                    badgeHtml = `<span class="badge-pacchetto">🏷️ ${nome} -${perc}%</span>`;
                    if (!scontiHtml.includes(nome)) scontiHtml += `<div>• Pacchetto "${nome}": -${perc}%</div>`;
                }

                html += `
                <div class="cart-item" id="item-${p.id_prodotto}">
                    <img src="${foto}" style="width:80px; height:110px; object-fit:cover; border-radius:6px; margin-right:20px; border:1px solid #eee;">
                    <div style="flex-grow:1;">
                        <h4 style="margin:0 0 4px;">${titolo}</h4>
                        ${autore ? `<small style="color:#888;">Autore: ${autore}</small><br>` : ''}
                        ${badgeHtml}
                        <div style="margin-top:6px;">
                            <span style="font-weight:bold; color:var(--primary-green,#27ae60); font-size:1.1em;">€${prezzoSc.toFixed(2)}</span>
                            ${prezzoSc < prezzo ? `<small style="text-decoration:line-through; color:#bbb; margin-left:6px;">€${prezzo.toFixed(2)}</small>` : ''}
                        </div>
                        <button class="btn-remove" onclick="rimuoviDalCarrello(${p.id_prodotto})">🗑️ Rimuovi</button>
                    </div>
                    <div style="text-align:right;">
                        <label style="font-size:0.8em; color:#888; display:block; margin-bottom:6px;">Quantità</label>
                        <div class="qty-wrapper">
                            <button class="qty-btn" id="btn-minus-${p.id_prodotto}"
                                onclick="cambiaQta(${p.id_prodotto}, -1, ${maxQta})"
                                ${qtaAtt <= 1 ? 'disabled' : ''}>−</button>
                            <span class="qty-display" id="qty-${p.id_prodotto}">${qtaAtt}</span>
                            <button class="qty-btn" id="btn-plus-${p.id_prodotto}"
                                onclick="cambiaQta(${p.id_prodotto}, 1, ${maxQta})"
                                ${qtaAtt >= maxQta ? 'disabled' : ''}>+</button>
                        </div>
                        <div class="qty-max-msg" id="msg-${p.id_prodotto}">Max ${maxQta} disponibili</div>
                        <div style="font-weight:800; font-size:1.1em; margin-top:8px;">
                            €${(prezzoSc * qtaAtt).toFixed(2)}
                        </div>
                    </div>
                </div>`;
            });

            $("#cart-items-list").html(html);
            $("#cart-total-label").text("€" + totale.toFixed(2));
            $("#cart-count-label").text(resp.prodotti.length);

            if (scontiHtml) {
                $("#sconti-riepilogo").html("<strong>🏷️ Sconti attivi:</strong>" + scontiHtml).show();
            }

        }, "json");
    }

    // ── usa ba_azione_carrello.php (la tua API originale) ───────────
    function cambiaQta(id, delta, maxQta) {
        const display = $(`#qty-${id}`);
        const msg     = $(`#msg-${id}`);
        let qta = parseInt(display.text()) + delta;

        if (qta < 1) qta = 1;
        if (qta > maxQta) {
            msg.show();
            $(`#btn-plus-${id}`).prop('disabled', true);
            return;
        }
        msg.hide();

        display.text(qta);
        $(`#btn-minus-${id}`).prop('disabled', qta <= 1);
        $(`#btn-plus-${id}`).prop('disabled', qta >= maxQta);

        $.post('api/ba_azione_carrello.php', { action: 'update', idProdotto: id, quantita: qta }, function(resp) {
            caricaCarrello();
            if (typeof updateCartBadge === 'function') updateCartBadge();
        }, "json");
    }

    function rimuoviDalCarrello(id) {
        if (confirm("Vuoi davvero rimuovere questo libro?")) {
            $.post('api/ba_azione_carrello.php', { action: 'delete', idProdotto: id }, function(resp) {
                caricaCarrello();
                if (typeof updateCartBadge === 'function') updateCartBadge();
            }, "json");
        }
    }

    // ── consigliati ─────────────────────────────────────────────────
    function caricaConsigliati() {
        $.get('api/ba_consigliati.php', function(resp) {
            if (resp.status === 'ok' && resp.consigliati && resp.consigliati.length > 0) {
                let sHtml = "";
                resp.consigliati.forEach(c => {
                    sHtml += `
                    <div class="suggestion-card">
                        <img src="${c.Foto || 'img/default.jpg'}" alt="${c.nome}">
                        <h5>${c.nome}</h5>
                        <div style="color:var(--primary-green,#27ae60); font-weight:bold; font-size:0.9em; margin-bottom:8px;">€${parseFloat(c.prezzo).toFixed(2)}</div>
                        <a href="dettaglio_prodotto.php?id=${c.id_prodotto}" class="btn-primary" style="font-size:0.78em; padding:5px 10px; text-decoration:none; border-radius:5px; display:inline-block;">Vedi libro</a>
                    </div>`;
                });
                $("#consigliati-list").html(sHtml);
            } else {
                $("#consigliati-list").html("<p style='color:#bbb; font-size:0.9em;'>Nessun consiglio disponibile.</p>");
            }
        }, "json");
    }
    </script>
</body>
</html>