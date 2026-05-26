<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>BookArchive | La tua libreria online</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* RIPRISTINATO LO STILE ORIGINALE */
        .hero { background: linear-gradient(135deg, var(--primary-green), var(--dark-green)); color: white; padding: 60px 20px; text-align: center; border-radius: 0 0 50px 50px; margin-bottom: 40px; }
        .books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 30px; padding: 20px; }
        .book-card { background: white; border: 1px solid var(--border-color); border-radius: 15px; overflow: hidden; transition: 0.3s; display: flex; flex-direction: column; cursor: pointer; height: 100%; }
        .book-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(39, 174, 96, 0.15); }
        .book-card img { width: 100%; height: 300px; object-fit: cover; }
        .book-info { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
        .book-price { font-size: 1.4em; color: var(--primary-green); font-weight: 800; margin: 10px 0; }
        .btn-add-cart { background: var(--primary-green); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-top: auto; }
        .section-title { padding: 0 20px; color: var(--dark-green); margin-top: 40px; border-left: 5px solid var(--primary-green); }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <section class="hero">
        <h1>Benvenuto su BookArchive</h1>
        <p>Scopri il nostro catalogo completo.</p>
    </section>

    <div id="main-container" style="max-width: 1200px; margin: 0 auto;">
        <h2 class="section-title">✨ Nuovi Arrivi</h2>
        <div id="grid-nuovi" class="books-grid"></div>

        <h2 class="section-title">⏳ Presto di nuovo disponibile</h2>
        <div id="grid-presto" class="books-grid"></div>

        <div id="sezioni-categorie"></div>
    </div>

    <script>
    $(document).ready(function() {
        $.get('api/ba_ricerca.php', function(resp) {
            const libri = resp.prodotti || [];
            
            // 1. Nuovi Arrivi
            renderizza(libri.slice(-4).reverse(), "#grid-nuovi");

            // 2. Esauriti
            renderizza(libri.filter(l => l.quantita_disponibile == 0), "#grid-presto");

            // 3. Categorie dinamiche
            const categorie = [...new Set(libri.map(l => l.nome_categoria || "Senza Categoria"))].sort();
            categorie.forEach(cat => {
                const libriCat = libri.filter(l => (l.nome_categoria || "Senza Categoria") == cat);
                $("#sezioni-categorie").append(`<h2 class="section-title">${cat}</h2><div id="cat-${cat.replace(/\s+/g, '-')}" class="books-grid"></div>`);
                renderizza(libriCat, `#cat-${cat.replace(/\s+/g, '-')}`);
            });
        });
    });

    function renderizza(libri, selector) {
        let html = "";
        libri.forEach(lib => {
            html += `
            <div class="book-card" onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'">
                <img src="${lib.URLfoto || 'img/default.jpg'}" alt="${lib.nome}">
                <div class="book-info">
                    <h3 style="margin:0; font-size:1.1em;">${lib.nome}</h3>
                    <div class="book-price">€${parseFloat(lib.PrezzoScontato || lib.prezzo).toFixed(2)}</div>
                    ${lib.quantita_disponibile > 0 
                        ? `<button class="btn-add-cart" onclick="event.stopPropagation(); aggiungiAlCarrello(${lib.id_prodotto})">🛒 Aggiungi</button>` 
                        : `<button class="btn-add-cart" style="background:#ccc; cursor:not-allowed">Esaurito</button>`}
                </div>
            </div>`;
        });
        $(selector).html(html);
    }

    function aggiungiAlCarrello(id) {
        $.post('api/ba_aggiungi_carrello.php', { idProdotto: id }, function(resp) {
            if(resp.status === 'ok') { alert("Aggiunto!"); updateCartBadge(); }
            else { alert(resp.msg); }
        });
    }
    </script>
</body>
</html>