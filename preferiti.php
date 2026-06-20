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
    <!-- stili in style.css -->
</head>
<body>
    <?php include('header.php'); ?>

    <main class="container" style="max-width:1100px; margin:30px auto; padding:0 20px;">
        <h2 style="color:var(--dark-green); margin-bottom:25px; display:flex; align-items:center; gap:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:26px;height:26px;color:#e74c3c;"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            I tuoi Preferiti
        </h2>
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
                                <button class="btn-fav" onclick="rimuoviPreferito(${p.id_prodotto}, this)" style="flex:0;padding:8px 10px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                </button>
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