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

        .qty-input {
            width: 60px;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
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

        /* MODIFICA: Stili per il riquadro consigliati affinità */
        .suggestions-box {
            margin-top: 40px;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #eee;
        }
        .suggestions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .suggestion-card {
            border: 1px solid #f0f0f0;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9em;
        }
        .suggestion-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 4px; }
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

                <div class="suggestions-box">
                    <h3 style="margin:0; color:var(--dark-green);">💡 Ti potrebbero interessare anche...</h3>
                    <div id="consigliati-list" class="suggestions-grid">
                        <p>Caricamento consigliati...</p>
                    </div>
                </div>
            </div>

            <div class="summary-card">
    <h3 style="margin-top:0;">Riepilogo Ordine</h3>
    <hr style="border: 0; border-top: 1px solid var(--border); margin: 15px 0;">
    
    <div style="display:flex; justify-content:space-between; margin-bottom: 10px;">
        <span>Titoli in carrello:</span>
        <strong id="cart-count-label">0</strong>
    </div>
    
    <div style="display:flex; justify-content:space-between; font-size: 1.4em; font-weight: 800; color: var(--accent); margin-top: 20px;">
        <span>Totale:</span>
        <span id="cart-total-label">€0.00</span>
    </div>

    <button class="btn-checkout" onclick="procediAlCheckout()">
        🔒 PROCEDI AL PAGAMENTO ➔
    </button>
    
    <p style="font-size: 0.85em; color: #666; margin-top: 15px; text-align: center; line-height: 1.4;">
        Cliccando su "Procedi al pagamento", verrai indirizzato alla pagina di pagamento sicuro per completare il tuo ordine.
    </p>
</div>
    <script>
    $(document).ready(function() {
        caricaCarrello();
        caricaConsigliati(); // MODIFICA: Caricamento asincrono dei libri consigliati
    });

    function caricaCarrello() {
        $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
            if(resp.status === 'ok') {
                if(resp.prodotti.length === 0) {
                    $("#cart-wrapper").html(`
                        <div style='text-align:center; padding:80px; width:100%; grid-column:1/-1;'>
                            <h3>Il tuo carrello è vuoto.</h3>
                            <p>Non hai ancora aggiunto libri.</p><br>
                            <a href="index.php" class="btn-primary" style="padding:12px 25px; text-decoration:none; border-radius:8px;">Esplora i Libri</a>
                        </div>
                    `);
                    return;
                }

                let html = "";
                resp.prodotti.forEach(p => {
                    // MODIFICA: Gestione badge promozionale autore combinato se p.scontoAutore è vero
                    let badgePromo = p.scontoAutore ? `<br><small style="background:#27ae60; color:white; padding:2px 6px; border-radius:4px; font-size:0.75em; font-weight:bold; display:inline-block; margin-top:5px;">🔥 Promo Autore Applicata (-20%)</small>` : '';

                    html += `
                    <div class="cart-item">
                        <img src="${p.URLfoto}" style="width:80px; height:110px; object-fit:cover; border-radius:6px; margin-right:20px; border:1px solid #eee;">
                        <div style="flex-grow:1;">
                            <h4 style="margin:0; font-size:1.2em;">${p.nome}</h4>
                            <small style="color:#666;">Autore: ${p.autore}</small>
                            ${badgePromo}
                            <div style="margin-top:5px;">
                                <span style="font-weight:bold; color:var(--primary-green);">€${parseFloat(p.prezzoScontato).toFixed(2)}</span>
                                ${p.prezzoOriginale > p.prezzoScontato ? `<small style="text-decoration:line-through; color:#999; margin-left:8px;">€${parseFloat(p.prezzoOriginale).toFixed(2)}</small>` : ''}
                            </div>
                            <button class="btn-remove" onclick="rimuoviDalCarrello(${p.IdProdotto})">Elimina</button>
                        </div>
                        <div style="text-align:right;">
                            <label style="font-size:0.8em; color:#666; display:block; margin-bottom:5px;">Quantità</label>
                            
                            <input type="number" class="qty-input" value="${p.quantita}" min="1" max="${p.quantitaDisponibile || 99}"
                                   onchange="aggiornaQuantita(${p.IdProdotto}, this.value, ${p.quantitaDisponibile || 99})">
                            <div style="font-weight:800; margin-top:10px; font-size:1.1em;">€${parseFloat(p.subtotale).toFixed(2)}</div>
                        </div>
                    </div>`;
                });

                $("#cart-items-list").html(html);
                $("#cart-total-label").text("€" + parseFloat(resp.totaleCart).toFixed(2));
                $("#cart-count-label").text(resp.prodotti.length);
            } else {
                alert(resp.msg);
            }
        }, "json");
    }

    // MODIFICA: Controllo anche lato client prima dell'invio post per bloccare quantità superiori
    function aggiornaQuantita(id, qta, maxDispo) {
        if(qta < 1) return;
        if(qta > maxDispo) {
            alert("Spiacenti, sono rimaste solo " + maxDispo + " copie disponibili per questo titolo.");
            caricaCarrello();
            return;
        }
        $.post('api/ba_carrello.php', { action: 'update', idProdotto: id, qty: qta }, function(resp) {
            caricaCarrello();
            if(typeof updateCartBadge === "function") updateCartBadge();
        }, "json");
    }

    function rimuoviDalCarrello(id) {
        if(confirm("Vuoi davvero rimuovere questo libro?")) {
            $.post('api/ba_carrello.php', { action: 'remove', idProdotto: id }, function(resp) {
                if(resp.status === 'ok') {
                    caricaCarrello();
                    if(typeof updateCartBadge === "function") updateCartBadge();
                }
            }, "json");
        }
    }

    // MODIFICA: Funzione JavaScript per popolare i libri consigliati
    function caricaConsigliati() {
        $.get('api/ba_consigliati.php', function(resp) {
            if(resp.status === 'ok' && resp.consigliati.length > 0) {
                let sHtml = "";
                resp.consigliati.forEach(c => {
                    sHtml += `
                    <div class="suggestion-card">
                        <img src="${c.Foto ? c.Foto : 'img/default.jpg'}">
                        <h5 style="margin:8px 0 4px 0; font-size:0.95em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${c.nome}</h5>
                        <div style="color:var(--primary-green); font-weight:bold; margin-bottom:8px;">€${parseFloat(c.prezzo).toFixed(2)}</div>
                        <a href="dettaglio_prodotto.php?id=${c.id_prodotto}" class="btn-primary" style="font-size:0.75em; padding:5px 10px; text-decoration:none; border-radius:4px;">Vedi libro</a>
                    </div>`;
                });
                $("#consigliati-list").html(sHtml);
            } else {
                $("#consigliati-list").html("<p style='font-size:0.85em; color:#999;'>Nessun consiglio mirato al momento.</p>");
            }
        }, "json");
    }

    function procediAlCheckout() {
        window.location.href = 'checkout.php';
    }
    </script>
</body>
</html>