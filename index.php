<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookArchive | La tua libreria online</title>

    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Stili specifici per la griglia dei libri */
        .hero {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 0 0 50px 50px;
            margin-bottom: 40px;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .book-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.15);
        }

        .book-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .book-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .book-price {
            font-size: 1.4em;
            color: var(--primary-green);
            font-weight: 800;
            margin: 10px 0;
        }

        .btn-add-cart {
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: background 0.3s;
        }

        .btn-add-cart:hover {
            background: var(--dark-green);
        }

        .star-small {
            color: #2ecc71; /* Stelle verdi */
            font-size: 0.9em;
        }
    </style>
</head>
<body>

    <?php include("header.php"); ?>

    <section class="hero">
        <h1>Benvenuto su BookArchive</h1>
        <p>Scopri migliaia di titoli curati dai nostri venditori indipendenti.</p>
    </section>

    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 20px;">
            <h2 style="color: var(--dark-green);">📚 Ultimi Arrivi</h2>
        </div>

        <div id="lista-libri" class="books-grid">
            <p style="text-align: center; grid-column: 1/-1;">Caricamento libri in corso...</p>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Carica i libri all'avvio
        caricaLibri();

        // Se ci sono parametri di ricerca nell'URL (q o cat), usali
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('q') || urlParams.has('cat')) {
            eseguiRicercaFiltrata(urlParams.get('q'), urlParams.get('cat'));
        }
    });

    function caricaLibri() {
        $.get('api/ba_ricerca.php', function(resp) {
            renderizzaLibri(resp);
        });
    }

    function renderizzaLibri(libri) {
        let html = "";
        if(libri.length === 0) {
            $("#lista-libri").html("<p style='grid-column: 1/-1; text-align:center;'>Nessun libro trovato.</p>");
            return;
        }

        libri.forEach(lib => {
            const stelle = "★".repeat(Math.round(lib.MediaVoto || 0)) + "☆".repeat(5 - Math.round(lib.MediaVoto || 0));

            html += `
            <div class="book-card">
                <img src="${lib.URLfoto}" alt="${lib.Titolo}">
                <div class="book-info">
                    <h3 style="margin:0; font-size: 1.1em;">${lib.Titolo}</h3>
                    <small style="color: #666;">di ${lib.Autore}</small>
                    <div class="star-small">${stelle} <span style="color:#999">(${lib.NumRecensioni || 0})</span></div>
                    <div class="book-price">€${parseFloat(lib.Prezzo).toFixed(2)}</div>

                    <?php if(isset($_SESSION['tipoUtente']) && $_SESSION['tipoUtente'] === 'cliente'): ?>
                        <button class="btn-add-cart" onclick="aggiungiAlCarrello(${lib.IdProdotto})">
                            🛒 Aggiungi al carrello
                        </button>
                    <?php else: ?>
                        <button class="btn-add-cart" style="background: #ccc; cursor:not-allowed;">
                            Solo per clienti
                        </button>
                    <?php endif; ?>
                </div>
            </div>`;
        });
        $("#lista-libri").html(html);
    }

    function aggiungiAlCarrello(id) {
        $.post('api/ba_aggiungi_carrello.php', { idProdotto: id }, function(resp) {
            if(resp.status === 'ok') {
                alert("Libro aggiunto al carrello!");
                // Funzione definita nell'header per aggiornare il badge rosso/verde
                if(typeof updateCartBadge === "function") updateCartBadge();
            } else {
                alert("Errore: " + resp.msg);
            }
        });
    }
    </script>
</body>
</html>