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
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        @media (max-width: 768px) { .cart-container { grid-template-columns: 1fr; } }

        .summary-card {
            background: #f9fbf9;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border);
            position: sticky;
            top: 20px;
            height: fit-content;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 15px;
            transition: 0.3s;
        }
        .cart-item:hover { border-color: var(--accent); }

        /* ============================================================
           CONTROLLO QUANTITÀ con pulsanti +/-
        ============================================================ */
        .qty-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-end;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            border: 2px solid var(--primary-green, #27ae60);
            background: white;
            color: var(--primary-green, #27ae60);
            border-radius: 50%;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            padding: 0;
        }
        .qty-btn:hover { background: var(--primary-green, #27ae60); color: white; }
        .qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .qty-display {
            min-width: 40px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
        }

        .qty-alert {
            font-size: 0.75em;
            color: #e67e22;
            margin-top: 5px;
            text-align: center;
            display: none;
        }

        .btn-checkout {
            background: var(--accent);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 8px;
            font-weight: 800;
            width: 100%;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 20px;
            transition: filter 0.2s;
        }
        .btn-checkout:hover { filter: brightness(1.1); }

        .btn-remove {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.9em;
            margin-top: 10px;
            padding: 0;
            text-decoration: underline;
        }

        /* ============================================================
           BADGE SCONTO
        ============================================================ */
        .badge-sconto-autore {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: bold;
            margin-top: 5px;
        }

        .badge-sconto-pacchetto {
            display: inline-block;
            background: linear-gradient(135deg, #f39c12, #e74c3c);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: bold;
            margin-top: 5px;
        }

        /* ============================================================
           CONSIGLIATI
        ============================================================ */
        .suggestions-box {
            margin-top: 40px;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .suggestions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(165px, 1fr));
            gap: 20px;
            margin-top: 18px;
        }

        .suggestion-card {
            border: 1px solid #f0f0f0;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-size: 0.9em;
            transition: box-shadow 0.2s;
        }
        .suggestion-card:hover { box-shadow: 0 4px 12px rgba(39,174,96,0.12); border-color: #c8e6c9; }
        .suggestion-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }
        .suggestion-card h5 {
            margin: 8px 0 2px;
            font-size: 0.9em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .suggestion-card .cat-label {
            font-size: 0.7em;
            color: #aaa;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <h2 style="color: var(--text-main); margin-bottom: 20px;">🛒 Il tuo Carrello</h2>

        <div id="cart-wrapper" class="cart-container">
            <div>
                <div id="cart-items-list">
                    <div style="text-align:center; padding:50px;">
                        <h3>Caricamento in corso...</h3>
                    </div>
                </div>

                <!-- CONSIGLIATI -->
                <div class="suggestions-box">
                    <h3 style="margin:0; color:var(--dark-green);">💡 Ti potrebbero interessare anche...</h3>
                    <p style="color:#888; font-size:0.85em; margin:4px 0 0;">Selezionati in base alle categorie nel tuo carrello.</p>
                    <div id="consigliati-list" class="suggestions-grid">
                        <p style="color:#ccc; font-size:0.9em;">Caricamento...</p>
                    </div>
                </div>
            </div>

            <!-- RIEPILOGO -->
            <div class="summary-card">
                <h3 style="margin-top:0;">Riepilogo Ordine</h3>
                <hr style="border:0; border-top:1px solid var(--border); margin:15px 0;">

                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span>Titoli in carrello:</span>
                    <strong id="cart-count-label">0</strong>
                </div>

                <!-- Dettaglio sconti attivi -->
                <div id="sconti-attivi" style="margin-bottom:12px; font-size:0.85em; color:#27ae60; display:none;">
                    <hr style="border:0; border-top:1px dashed #c8e6c9; margin:10px 0;">
                    <strong>🏷️ Sconti attivi:</strong>
                    <div id="sconti-lista"></div>
                </div>

                <div style="display:flex; justify-content:space-between; font-size:1.4em; font-weight:800; color:var(--accent); margin-top:20px;">
                    <span>Totale:</span>
                    <span id="cart-total-label">€0.00</span>
                </div>

                <button class="btn-checkout" onclick="procediAlCheckout()">
                    🔒 PROCEDI AL PAGAMENTO ➔
                </button>

                <p style="font-size:0.85em; color:#666; margin-top:15px; text-align:center; line-height:1.4;">
                    Cliccando su "Procedi al pagamento", verrai indirizzato alla pagina di pagamento sicuro.
                </p>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        caricaCarrello();
        caricaConsigliati();
    });

    // ============================================================
    // CARICA CARRELLO
    // ============================================================
    function caricaCarrello() {
        $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
            if (resp.status === 'ok') {
                if (resp.prodotti.length === 0) {
                    $("#cart-wrapper").html(`
                        <div style='text-align:center; padding:80px; width:100%; grid-column:1/-1;'>
                            <div style='font-size:4em;'>🛒</div>
                            <h3>Il tuo carrello è vuoto.</h3>
                            <p>Non hai ancora aggiunto libri.</p><br>
                            <a href="index.php" class="btn-primary" style="padding:12px 25px; text-decoration:none; border-radius:8px;">Esplora i Libri</a>
                        </div>
                    `);
                    return;
                }

                let html = "";
                let scontiHtml = "";
                let haSconti = false;

                resp.prodotti.forEach(p => {
                    // Badge sconto
                    let badgeHtml = '';
                    if (p.scontoAutore) {
                        badgeHtml = `<span class="badge-sconto-autore">🔥 Promo Autore -${p.percentualeSconto}%</span>`;
                        if (!scontiHtml.includes('Autore')) {
                            scontiHtml += `<div>• Sconto autore (×2 stesso autore): -${p.percentualeSconto}%</div>`;
                            haSconti = true;
                        }
                    } else if (p.nomePacchetto) {
                        badgeHtml = `<span class="badge-sconto-pacchetto">🏷️ ${p.nomePacchetto} -${p.percentualeSconto}%</span>`;
                        if (!scontiHtml.includes(p.nomePacchetto)) {
                            scontiHtml += `<div>• Pacchetto "${p.nomePacchetto}": -${p.percentualeSconto}%</div>`;
                            haSconti = true;
                        }
                    }

                    const maxQta = p.quantitaDisponibile;

                    html += `
                    <div class="cart-item" id="item-${p.IdProdotto}">
                        <img src="${p.URLfoto}" style="width:80px; height:110px; object-fit:cover; border-radius:6px; margin-right:20px; border:1px solid #eee;">
                        <div style="flex-grow:1;">
                            <h4 style="margin:0; font-size:1.2em;">${p.nome}</h4>
                            <small style="color:#666;">Autore: ${p.autore}</small><br>
                            ${badgeHtml}
                            <div style="margin-top:8px;">
                                <span style="font-weight:bold; color:var(--primary-green); font-size:1.1em;">€${parseFloat(p.prezzoScontato).toFixed(2)}</span>
                                ${p.prezzoOriginale > p.prezzoScontato
                                    ? `<small style="text-decoration:line-through; color:#bbb; margin-left:8px;">€${parseFloat(p.prezzoOriginale).toFixed(2)}</small>`
                                    : ''}
                            </div>
                            <button class="btn-remove" onclick="rimuoviDalCarrello(${p.IdProdotto})">🗑️ Rimuovi</button>
                        </div>
                        <div style="text-align:right;">
                            <label style="font-size:0.8em; color:#666; display:block; margin-bottom:8px;">Quantità</label>
                            <div class="qty-wrapper">
                                <button class="qty-btn" id="btn-minus-${p.IdProdotto}"
                                    onclick="cambiaQuantita(${p.IdProdotto}, -1, ${maxQta})"
                                    ${p.quantita <= 1 ? 'disabled' : ''}>−</button>
                                <span class="qty-display" id="qty-display-${p.IdProdotto}">${p.quantita}</span>
                                <button class="qty-btn" id="btn-plus-${p.IdProdotto}"
                                    onclick="cambiaQuantita(${p.IdProdotto}, 1, ${maxQta})"
                                    ${p.quantita >= maxQta ? 'disabled' : ''}>+</button>
                            </div>
                            <div class="qty-alert" id="qty-alert-${p.IdProdotto}">
                                Max ${maxQta} cop${maxQta === 1 ? 'ia' : 'ie'} disponibili
                            </div>
                            <div style="font-weight:800; margin-top:10px; font-size:1.15em;" id="subtotale-${p.IdProdotto}">
                                €${parseFloat(p.subtotale).toFixed(2)}
                            </div>
                        </div>
                    </div>`;
                });

                $("#cart-items-list").html(html);
                $("#cart-total-label").text("€" + parseFloat(resp.totaleCart).toFixed(2));
                $("#cart-count-label").text(resp.prodotti.length);

                if (haSconti) {
                    $("#sconti-lista").html(scontiHtml);
                    $("#sconti-attivi").show();
                }

            } else {
                alert(resp.msg);
            }
        }, "json");
    }

    // ============================================================
    // CAMBIO QUANTITÀ con pulsanti +/-
    // ============================================================
    function cambiaQuantita(idProdotto, delta, maxQta) {
        const display = $(`#qty-display-${idProdotto}`);
        const alert   = $(`#qty-alert-${idProdotto}`);
        let qtaAttuale = parseInt(display.text());
        let nuovaQta   = qtaAttuale + delta;

        if (nuovaQta < 1) nuovaQta = 1;

        // Blocco client-side
        if (nuovaQta > maxQta) {
            alert.show();
            $(`#btn-plus-${idProdotto}`).prop('disabled', true);
            return;
        }

        alert.hide();

        // Aggiorna UI ottimisticamente
        display.text(nuovaQta);
        $(`#btn-minus-${idProdotto}`).prop('disabled', nuovaQta <= 1);
        $(`#btn-plus-${idProdotto}`).prop('disabled', nuovaQta >= maxQta);

        // Invia al server
        $.post('api/ba_carrello.php', { action: 'update', idProdotto: idProdotto, qty: nuovaQta }, function(resp) {
            if (resp.status === 'ok') {
                caricaCarrello(); // ricarica per aggiornare subtotali e totale
                if (typeof updateCartBadge === 'function') updateCartBadge();
            } else {
                // Il server ha rifiutato (es. stock cambiato nel frattempo)
                alert.text(resp.msg || 'Quantità non disponibile').show();
                caricaCarrello();
            }
        }, "json");
    }

    // ============================================================
    // RIMUOVI DAL CARRELLO
    // ============================================================
    function rimuoviDalCarrello(id) {
        if (confirm("Vuoi davvero rimuovere questo libro dal carrello?")) {
            $.post('api/ba_carrello.php', { action: 'remove', idProdotto: id }, function(resp) {
                if (resp.status === 'ok') {
                    caricaCarrello();
                    if (typeof updateCartBadge === 'function') updateCartBadge();
                }
            }, "json");
        }
    }

    // ============================================================
    // CONSIGLIATI (basati sulle categorie del carrello)
    // ============================================================
    function caricaConsigliati() {
        $.get('api/ba_consigliati.php', function(resp) {
            if (resp.status === 'ok' && resp.consigliati.length > 0) {
                let sHtml = "";
                resp.consigliati.forEach(c => {
                    sHtml += `
                    <div class="suggestion-card">
                        <img src="${c.Foto || 'img/default.jpg'}" alt="${c.nome}">
                        <h5>${c.nome}</h5>
                        <div class="cat-label">${c.categoria || ''}</div>
                        <div style="color:var(--primary-green); font-weight:bold; margin-bottom:8px;">€${parseFloat(c.prezzo).toFixed(2)}</div>
                        <a href="dettaglio_prodotto.php?id=${c.id_prodotto}"
                           class="btn-primary"
                           style="font-size:0.8em; padding:6px 12px; text-decoration:none; border-radius:6px; display:inline-block;">
                            Vedi libro
                        </a>
                    </div>`;
                });
                $("#consigliati-list").html(sHtml);
            } else {
                $("#consigliati-list").html("<p style='font-size:0.85em; color:#bbb;'>Nessun consiglio disponibile al momento.</p>");
            }
        }, "json");
    }

    // ============================================================
    // CHECKOUT
    // ============================================================
    function procediAlCheckout() {
        window.location.href = 'checkout.php';
    }
    </script>
</body>
</html>