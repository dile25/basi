<?php
session_start();
$tipoUtente = $_SESSION['tipoUtente'] ?? '';
$loggato    = isset($_SESSION['IdUtente']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>The (E-)Shop Around the Corner | La tua libreria online</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto">
    <section class="hero">
        <h1>Benvenuto su The (E-)Shop Around the Corner</h1>
        <p>Libri nuovi e usati dalle librerie indipendenti, a portata di click.</p>
    </section>

    <div class="page">
        <!-- CATEGORIE -->
        <nav id="categories-banner-wrapper" aria-label="Sfoglia per categoria">
            <div id="categorie-banner" class="categories-scroll"></div>
        </nav>

        <!-- RISULTATI DI RICERCA / FILTRO -->
        <section id="sezione-filtro" class="is-hidden" aria-live="polite">
            <a href="index.php" class="back-link">&#8592; Torna alla home</a>
            <h2 class="section-title" id="titolo-filtro"></h2>
            <div id="grid-filtro" class="books-grid"></div>
        </section>

        <!-- SEZIONI DELLA HOME -->
        <div id="sezione-home">
            <section aria-labelledby="titolo-nuovi">
                <h2 class="section-title" id="titolo-nuovi">Nuovi arrivi</h2>
                <div id="grid-nuovi" class="books-grid"></div>
            </section>
            <section id="sezione-offerte" aria-labelledby="titolo-offerte">
                <h2 class="section-title" id="titolo-offerte">In offerta nei pacchetti</h2>
                <div id="grid-offerte" class="books-grid"></div>
            </section>
        </div>
    </div>
</main>

<script>
const TIPO_UTENTE = <?php echo json_encode($tipoUtente); ?>;
const LOGGATO     = <?php echo $loggato ? 'true' : 'false'; ?>;
let idCarrelloSet = new Set();   // id dei prodotti già nel carrello

$(function() {
    caricaBannerCategorie();

    // Prima si legge il carrello (solo per i clienti), POI si disegnano i prodotti:
    // così i bottoni "Aggiungi/Rimuovi" sono sempre corretti.
    const carrelloPronto = (TIPO_UTENTE === 'cliente')
        ? $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
              (resp.prodotti || []).forEach(p => idCarrelloSet.add(parseInt(p.IdProdotto)));
          }, 'json')
        : $.Deferred().resolve();

    carrelloPronto.always(caricaProdotti);
});

function caricaBannerCategorie() {
    $.get('api/ba_lista_categorie.php', function(resp) {
        const cats   = resp.categorie || [];
        const padri  = cats.filter(c => !c.nome_categoria_padre);
        const figlie = cats.filter(c => c.nome_categoria_padre);
        let html = '';
        padri.forEach(padre => {
            const chips = figlie
                .filter(f => f.nome_categoria_padre === padre.nome_categoria)
                .map(f => `<a href="index.php?cat=${encodeURIComponent(f.nome_categoria)}" class="cat-chip">${escapeHtml(f.nome_categoria)}</a>`)
                .join('');
            html += `<div class="cat-group">
                <a class="cat-group-label" href="index.php?cat=${encodeURIComponent(padre.nome_categoria)}">${escapeHtml(padre.nome_categoria)}</a>
                ${chips ? `<div class="cat-group-chips">${chips}</div>` : ''}
            </div>`;
        });
        $('#categorie-banner').html(html);
    }, 'json');
}

function caricaProdotti() {
    const params = new URLSearchParams(window.location.search);
    const cat = params.get('cat') || '';
    const q   = params.get('q')   || '';

    if (cat || q) {
        // Modalità ricerca/filtro
        $('#sezione-home, #categories-banner-wrapper').addClass('is-hidden');
        $('#sezione-filtro').removeClass('is-hidden');
        $('#titolo-filtro').text(cat ? 'Categoria: ' + cat : 'Risultati per "' + q + '"');

        $.get('api/ba_ricerca.php', { cat: cat, q: q }, function(resp) {
            const prodotti = resp.prodotti || [];
            if (prodotti.length === 0) {
                $('#grid-filtro').html(`
                    <div class="empty-state">
                        <h3>Nessun titolo trovato</h3>
                        <p>Prova con un'altra categoria o con un termine di ricerca diverso.</p>
                        <a href="index.php" class="btn btn-secondary">Torna alla home</a>
                    </div>`);
            } else {
                renderizza(prodotti, '#grid-filtro');
            }
        }, 'json');
        return;
    }

    // Home: ogni sezione chiede al server solo i prodotti che mostra (LIMIT lato server)
    $.get('api/ba_ricerca.php', { sezione: 'nuovi', limit: 10 }, function(resp) {
        renderizza(resp.prodotti || [], '#grid-nuovi');
    }, 'json');

    $.get('api/ba_ricerca.php', { sezione: 'offerte', limit: 10 }, function(resp) {
        const prodotti = resp.prodotti || [];
        if (prodotti.length) renderizza(prodotti, '#grid-offerte');
        else $('#sezione-offerte').addClass('is-hidden');
    }, 'json');
}

function bottoneCarrello(id) {
    return idCarrelloSet.has(id)
        ? `<button type="button" class="btn btn-danger-outline btn-block js-rimuovi-carrello" data-id="${id}">Rimuovi dal carrello</button>`
        : `<button type="button" class="btn btn-primary btn-block js-aggiungi-carrello" data-id="${id}">Aggiungi al carrello</button>`;
}

function renderizza(prodotti, selettore) {
    let html = '';
    prodotti.forEach(p => {
        const id       = parseInt(p.id_prodotto);
        const esaurito = parseInt(p.quantita_disponibile) <= 0;
        const nome     = escapeHtml(p.nome);
        const link     = 'dettaglio_prodotto.php?id=' + id;

        const badge = p.nome_pacchetto
            ? `<span class="badge badge-pacchetto">Pacchetto -${parseInt(p.sconto_pacchetto)}%</span>`
            : '';

        let azione = '';
        if (esaurito) {
            azione = '<span class="badge badge-esaurito">Esaurito</span>';
        } else if (TIPO_UTENTE !== 'venditore') {
            azione = bottoneCarrello(id);
        }

        html += `
        <article class="book-card">
            <a href="${link}" class="book-card__img-link" tabindex="-1" aria-hidden="true">
                <img class="book-card__img" src="${escapeHtml(p.URLfoto || 'img/default.jpg')}" alt="">
            </a>
            <div class="book-info">
                ${badge}
                <h3 class="book-title"><a href="${link}">${nome}</a></h3>
                <p class="book-author">${escapeHtml(p.autore || '')}</p>
                <p class="book-price">${formatPrezzo(p.prezzo)}</p>
                ${azione}
            </div>
        </article>`;
    });
    $(selettore).html(html || '<p class="muted">Nessun prodotto disponibile.</p>');
}

/* --- Carrello (event delegation: i bottoni sono creati dinamicamente) --- */
$(document).on('click', '.js-aggiungi-carrello', function() {
    if (!LOGGATO) {
        if (confirm('Devi accedere per usare il carrello. Vuoi accedere ora?')) window.location.href = 'login.php';
        return;
    }
    const btn = $(this);
    const id  = parseInt(btn.data('id'));
    $.post('api/ba_carrello.php', { action: 'add', idProdotto: id, quantita: 1 }, function(resp) {
        if (resp.status === 'ok') {
            idCarrelloSet.add(id);
            btn.replaceWith(bottoneCarrello(id));
            updateCartBadge();
        } else {
            mostraNotifica(resp.msg || 'Impossibile aggiungere il prodotto.', true);
        }
    }, 'json');
});

$(document).on('click', '.js-rimuovi-carrello', function() {
    const btn = $(this);
    const id  = parseInt(btn.data('id'));
    $.post('api/ba_carrello.php', { action: 'remove', idProdotto: id }, function(resp) {
        if (resp.status === 'ok') {
            idCarrelloSet.delete(id);
            btn.replaceWith(bottoneCarrello(id));
            updateCartBadge();
        }
    }, 'json');
});

</script>
</body>
</html>
