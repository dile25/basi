<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Profilo venditore | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page">
    <p id="loading" class="loading">Caricamento profilo...</p>

    <div id="contenuto-venditore" class="is-hidden">
        <section class="venditore-hero">
            <div class="venditore-avatar" aria-hidden="true">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </div>
            <div class="venditore-info">
                <h1 id="v-nome"></h1>
                <p id="v-membro"></p>
            </div>
        </section>

        <h2 class="section-title" id="titolo-libri"></h2>
        <div id="libri-grid" class="books-grid"></div>
    </div>
</main>

<script>
$(function() {
    const u = new URLSearchParams(window.location.search).get('u');
    if (!u) { $('#loading').text('Venditore non specificato.'); return; }

    $.get('api/ba_profilo_venditore.php', { u: u }, function(resp) {
        if (resp.status !== 'ok') { $('#loading').text(resp.msg || 'Venditore non trovato.'); return; }

        const v    = resp.venditore;
        const nome = v.nome_visualizzato || v.ragione_sociale || ((v.nome || '') + ' ' + (v.cognome || ''));
        $('#v-nome').text(nome);
        document.title = nome + ' | The (E-)Shop Around the Corner';

        const dataReg = v.data_registrazione
            ? new Date(v.data_registrazione).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' })
            : '';
        $('#v-membro').text(dataReg ? 'Su The (E-)Shop Around the Corner dal ' + dataReg : 'Venditore');

        const libri = resp.libri || [];
        $('#titolo-libri').text('Libri in vendita (' + libri.length + ')');

        if (libri.length === 0) {
            $('#libri-grid').html('<p class="muted">Nessun libro disponibile al momento.</p>');
        } else {
            let html = '';
            libri.forEach(lib => {
                const link = 'dettaglio_prodotto.php?id=' + parseInt(lib.id_prodotto);
                html += `<article class="book-card">
                    <a href="${link}" class="book-card__img-link" tabindex="-1" aria-hidden="true">
                        <img class="book-card__img" src="${escapeHtml(lib.foto || 'img/default.jpg')}" alt="">
                    </a>
                    <div class="book-info">
                        <h3 class="book-title"><a href="${link}">${escapeHtml(lib.nome)}</a></h3>
                        ${lib.autore ? `<p class="book-author">${escapeHtml(lib.autore)}</p>` : ''}
                        <p class="book-price">${formatPrezzo(lib.prezzo)}</p>
                        ${parseInt(lib.quantita_disponibile) > 0 ? '' : '<span class="badge badge-esaurito">Non disponibile</span>'}
                    </div>
                </article>`;
            });
            $('#libri-grid').html(html);
        }

        $('#loading').addClass('is-hidden');
        $('#contenuto-venditore').removeClass('is-hidden');
    }, 'json');
});
</script>
</body>
</html>
