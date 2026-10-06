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
    <title>I miei preferiti | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page">
    <h1 class="page-title">I tuoi preferiti</h1>
    <div id="lista-preferiti" class="books-grid" aria-live="polite"></div>
</main>

<script>
$(function() {
    caricaPreferiti();
});

function caricaPreferiti() {
    $.get('api/ba_get_preferiti.php', function(resp) {
        const preferiti = resp.preferiti || [];
        if (preferiti.length === 0) {
            $('#lista-preferiti').html(`<div class="empty-state">
                <h2>Nessun preferito salvato</h2>
                <p>Sfoglia il catalogo e salva i libri che ti interessano.</p>
                <a href="index.php" class="btn btn-primary">Esplora il catalogo</a>
            </div>`);
            return;
        }
        let html = '';
        preferiti.forEach(p => {
            const id   = parseInt(p.id_prodotto);
            const link = 'dettaglio_prodotto.php?id=' + id;
            html += `<article class="book-card">
                <a href="${link}" class="book-card__img-link" tabindex="-1" aria-hidden="true">
                    <img class="book-card__img" src="${escapeHtml(p.URLfoto || 'img/default.jpg')}" alt="">
                </a>
                <div class="book-info">
                    <h2 class="book-title"><a href="${link}">${escapeHtml(p.nome)}</a></h2>
                    <p class="book-price">${formatPrezzo(p.prezzo)}</p>
                    ${parseInt(p.quantita_disponibile) > 0 ? '' : '<span class="badge badge-esaurito">Esaurito</span>'}
                    <div class="button-row">
                        <a href="${link}" class="btn btn-primary btn-small">Vedi il libro</a>
                        <button type="button" class="btn btn-fav attivo btn-small js-rimuovi-preferito" data-id="${id}" aria-label="Rimuovi ${escapeHtml(p.nome)} dai preferiti">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        </button>
                    </div>
                </div>
            </article>`;
        });
        $('#lista-preferiti').html(html);
    }, 'json');
}

$(document).on('click', '.js-rimuovi-preferito', function() {
    $.post('api/ba_toggle_preferiti.php', { idProdotto: $(this).data('id') }, function(resp) {
        if (resp.status === 'ok') caricaPreferiti();
    }, 'json');
});
</script>
</body>
</html>
