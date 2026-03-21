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
    // 1. Recuperiamo l'ID dall'indirizzo (es. prodotto.php?id=12)
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    $(document).ready(function() {
        if (!productId) {
            window.location.href = 'index.php'; // Se non c'è ID, torna in home
            return;
        }
        caricaDettagli();
    });

    function caricaDettagli() {
        // Chiamata all'API "buona" che abbiamo confermato
        $.get(`api/ba_dettaglio_prodotto.php?id=${productId}`, function(resp) {
            if (resp.status === 'ok') {
                const p = resp.data;
                const html = `
                    <div class="product-image" style="flex: 1; max-width: 400px;">
                        <img src="${p.URLfoto}" alt="${p.Titolo}" style="width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    </div>
                    <div class="product-details" style="flex: 1.5;">
                        <h1 style="font-size: 2.5em; margin-bottom: 10px;">${p.Titolo}</h1>
                        <p style="color: #666; font-size: 1.1em;">Categoria: <strong>${p.NomeCategoria}</strong></p>
                        <p style="margin: 20px 0; line-height: 1.6; font-size: 1.1em;">${p.Descrizione}</p>

                        <div class="buy-box" style="background: #f9f9f9; padding: 25px; border-radius: 10px; border: 1px solid #eee;">
                            <div style="font-size: 2em; color: var(--accent); font-weight: bold; margin-bottom: 15px;">
                                € ${parseFloat(p.Prezzo).toFixed(2)}
                            </div>
                            <p style="margin-bottom: 20px;">Venduto da: <strong>${p.NomeNeg}</strong></p>

                            <div style="display: flex; gap: 10px;">
                                <input type="number" id="qty" value="1" min="1" max="${p.QuantitaDisp}" style="width: 60px; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                                <button onclick="aggiungiAlCarrello()" class="btn-primary" style="flex: 1; padding: 12px; cursor: pointer;">
                                    🛒 Aggiungi al Carrello
                                </button>
                            </div>
                            <p style="font-size: 0.8em; margin-top: 10px; color: ${p.QuantitaDisp > 0 ? 'green' : 'red'};">
                                ${p.QuantitaDisp > 0 ? 'Disponibile: ' + p.QuantitaDisp + ' copie' : 'Esaurito'}
                            </p>
                        </div>
                    </div>
                `;
                $("#product-content").html(html);
                document.title = p.Titolo + " | BookArchive";
            } else {
                $("#product-content").html(`<h2>Errore: ${resp.msg}</h2>`);
            }
        });
    }

    function aggiungiAlCarrello() {
        const qty = $("#qty").val();
        $.post('api/ba_aggiungi_carrello.php', { idProdotto: productId, quantita: qty }, function(resp) {
            if (resp.status === 'ok') {
                alert("Ottima scelta! Libro aggiunto al carrello.");
                // Se hai la funzione nel header per aggiornare il badge, chiamala qui
                if(typeof updateCartBadge === 'function') updateCartBadge();
            } else {
                alert(resp.msg || "Devi effettuare il login per acquistare.");
            }
        });
    }
</script>

</body>
</html>