<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettaglio Libro | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include("header.php"); ?>

<div class="container" style="margin-top: 50px;">
    <div id="product-content" style="display: flex; gap: 40px; align-items: start;">
        <p>Caricamento dettagli libro...</p>
    </div>
</div>

<script>
    // Recupero ID dal URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    $(document).ready(function() {
        if (!productId) {
            window.location.href = 'index.php';
            return;
        }
        caricaDettagli();
    });

    function caricaDettagli() {
        // Aggiungiamo un timestamp casuale alla richiesta per evitare la cache del server/browser
        const cacheBuster = new Date().getTime();
        $.get(`api/ba_dettaglio_prodotto.php?id=${productId}&_=${cacheBuster}`, function(resp) {
            
            if (resp && resp.status === 'ok') {
                const p = resp.dettagli; 
                const foto = p.foto && p.foto.length > 0 ? p.foto[0] : 'img/default.jpg';
                const stock = parseInt(p.QuantitaDisp || 0);

                // Struttura HTML pulita
                const html = `
                    <div class="product-image" style="flex: 1; max-width: 400px;">
                        <img src="${foto}" alt="${p.NomeProdotto}" style="width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    </div>
                    <div class="product-details" style="flex: 1.5;">
                        <h1>${p.NomeProdotto}</h1>
                        <p>Autore: <strong>${p.autore || 'N/D'}</strong></p>
                        <p>Categoria: <strong>${p.NomeCategoria}</strong></p>
                        <p>${p.descrizione}</p>
                        <div class="buy-box" style="background: #f9f9f9; padding: 25px; border-radius: 10px; border: 1px solid #eee;">
                            <div style="font-size: 2em; color: var(--accent); font-weight: bold; margin-bottom: 15px;">
                                € ${parseFloat(p.PrezzoScontato || 0).toFixed(2)}
                            </div>
                            <p>Venduto da: <strong>${p.NomeVenditore}</strong></p>
                            
                            <div id="action-container"></div>
                        </div>
                    </div>
                `;

                $("#product-content").html(html);

                // Logica separata per decidere cosa iniettare nel contenitore delle azioni
                if (stock > 0) {
                    $("#action-container").html(`
                        <div style="display: flex; gap: 10px;">
                            <input type="number" id="qty" value="1" min="1" max="${stock}" style="width: 60px; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <button onclick="aggiungiAlCarrello()" class="btn-primary" style="flex: 1; padding: 12px; cursor: pointer;">
                                🛒 Aggiungi al Carrello
                            </button>
                        </div>
                        <p style="color: green; margin-top: 10px;">Disponibilità: ${stock} copie</p>
                    `);
                } else {
                    const oggetto = encodeURIComponent(`Richiesta disponibilità: ${p.NomeProdotto}`);
                    const corpo = encodeURIComponent(`Salve, vorrei essere avvisato quando il libro "${p.NomeProdotto}" (ID: ${p.IdProdotto}) tornerà disponibile.`);
                    $("#action-container").html(`
                        <p style="color: #e74c3c; font-weight: bold; margin-bottom: 10px;">❌ Prodotto momentaneamente esaurito</p>
                        <a href="mailto:supporto@bookarchive.it?subject=${oggetto}&body=${corpo}" 
                           style="display:block; padding:12px; background:#e67e22; color:white; text-align:center; text-decoration:none; border-radius:5px; font-weight:bold;">
                           🔔 Avvisami quando disponibile
                        </a>
                    `);
                }

            } else {
                $("#product-content").html(`<h2>Errore: ${resp.msg || 'Dati non trovati'}</h2>`);
            }
        });
    }

function aggiungiAlCarrello() {
    const qty = parseInt($("#qty").val());
    const maxQty = parseInt($("#qty").attr("max"));

    // Controllo preventivo lato client (UX)
    if (qty > maxQty) {
        alert("Spiacenti, sono disponibili solo " + maxQty + " copie.");
        return;
    }

    $.post('api/ba_aggiungi_carrello.php', { idProdotto: productId, quantita: qty }, function(resp) {
        if (resp.status === 'ok') {
            alert("Ottima scelta! Libro aggiunto al carrello.");
            if(typeof updateCartBadge === 'function') updateCartBadge();
        } else {
            // Feedback specifico basato sulla risposta del server
            alert(resp.msg); 
        }
    });
}
</script>
</body>
</html>