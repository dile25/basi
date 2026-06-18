<?php session_start();
if(!isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I Miei Preferiti | The (E-)Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px; padding: 10px 0; }
        .book-card { background: white; border: 1px solid var(--border-color); border-radius: 15px; overflow: hidden; transition: all 0.3s; display: flex; flex-direction: column; }
        .book-card:hover { transform: translateY(-6px); box-shadow: 0 8px 20px rgba(39,174,96,0.15); }
        .book-card img { width: 100%; height: 260px; object-fit: cover; cursor: pointer; }
        .book-info { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; gap: 8px; }
        .book-title { font-weight: 700; font-size: 1em; color: var(--dark-green); margin: 0; }
        .book-price { font-size: 1.2em; font-weight: 800; color: var(--dark-green); }
        .book-price-old { text-decoration: line-through; color: #999; font-size: 0.85em; }
        .btn-rimuovi { background: none; border: 1px solid #e74c3c; color: #e74c3c; padding: 8px; border-radius: 7px; cursor: pointer; font-size: 0.85em; font-weight: 600; transition: 0.2s; }
        .btn-rimuovi:hover { background: #e74c3c; color: white; }
        .btn-compra { background: var(--dark-green); color: white; border: none; padding: 10px; border-radius: 7px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-compra:hover { background: #4d6649; }
        .empty-state { text-align: center; padding: 80px 20px; }
        .empty-state h3 { color: var(--dark-green); margin-bottom: 10px; }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <main class="container" style="max-width:1100px; margin:30px auto; padding:0 20px;">
        <h2 style="color:var(--dark-green); margin-bottom:25px;">❤️ I tuoi Preferiti</h2>
        <div id="lista-preferiti" class="books-grid"></div>
    </main>

    <script>
    $(document).ready(function() {
        caricaPreferiti();
    });

    function caricaPreferiti() {
        $.get('api/ba_get_preferiti.php', function(data) {
            const cont = $("#lista-preferiti");
            if (data.status === 'ok' && data.preferiti.length > 0) {
                let html = '';
                data.preferiti.forEach(p => {
                    const foto = p.URLfoto || 'img/default.jpg';
                    const prezzoHtml = p.prezzo_scontato < p.prezzo
                        ? `<span class="book-price-old">€${parseFloat(p.prezzo).toFixed(2)}</span>
                           <span class="book-price">€${parseFloat(p.prezzo_scontato).toFixed(2)}</span>`
                        : `<span class="book-price">€${parseFloat(p.prezzo).toFixed(2)}</span>`;

                    html += `
                    <div class="book-card">
                        <img src="${foto}" alt="${p.nome}" onclick="location.href='dettaglio_prodotto.php?id=${p.id_prodotto}'">
                        <div class="book-info">
                            <p class="book-title">${p.nome}</p>
                            <div>${prezzoHtml}</div>
                            <div style="display:flex; gap:8px; margin-top:auto;">
                                <button class="btn-compra" style="flex:1;" onclick="location.href='dettaglio_prodotto.php?id=${p.id_prodotto}'">Vedi dettaglio libro</button>
                                <button class="btn-rimuovi" onclick="rimuoviPreferito(${p.id_prodotto}, this)">💔 Rimuovi</button>
                            </div>
                        </div>
                    </div>`;
                });
                cont.html(html);
            } else {
                cont.html(`
                    <div class="empty-state" style="grid-column:1/-1;">
                        <h3>Nessun preferito salvato</h3>
                        <p style="color:var(--text-sec);">Sfoglia i libri e aggiungi quelli che ti interessano!</p>
                        <br>
                        <a href="index.php" class="btn-recensisci" style="padding:12px 24px; text-decoration:none;">Esplora i libri</a>
                    </div>`);
            }
        }, 'json');
    }

    function rimuoviPreferito(id, btn) {
        $.post('api/ba_toggle_preferiti.php', { idProdotto: id }, function(resp) {
            if(resp.status === 'ok') caricaPreferiti();
        }, 'json');
    }
    </script>
</body>
</html>