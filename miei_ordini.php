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
    <title>I miei ordini | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page page--medium">
    <h1 class="page-title">I miei ordini</h1>

    <div class="filtri" role="group" aria-label="Filtra gli ordini">
        <button type="button" class="filtro-btn active" data-stato="" aria-pressed="true">Tutti</button>
        <button type="button" class="filtro-btn" data-stato="Pagato" aria-pressed="false">Pagati</button>
        <button type="button" class="filtro-btn" data-stato="Annullato" aria-pressed="false">Annullati</button>
    </div>

    <div id="ordini-lista" aria-live="polite"><p class="loading">Caricamento...</p></div>
</main>

<!-- DIALOG RECENSIONE -->
<div id="modalRecensione" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rev-titolo">
    <div class="modal-box">
        <button type="button" class="modal-close js-chiudi-recensione" aria-label="Chiudi">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
        <h2 class="modal-title" id="rev-titolo">Recensisci il libro</h2>
        <p class="muted">La tua opinione aiuta gli altri lettori.</p>
        <form id="formRecensione" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="idProdotto" id="rev-idProdotto">
            <input type="hidden" name="id_recensione" value="0">

            <fieldset class="star-rating">
                <legend class="form-label">Valutazione</legend>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" name="voto" id="voto-<?php echo $i; ?>" value="<?php echo $i; ?>" class="visually-hidden" <?php echo $i === 5 ? 'checked' : ''; ?>>
                    <label for="voto-<?php echo $i; ?>" class="star on" data-v="<?php echo $i; ?>" title="<?php echo $i; ?> su 5">
                        &#9733;<span class="visually-hidden"><?php echo $i; ?> stelle</span>
                    </label>
                <?php endfor; ?>
            </fieldset>

            <div class="form-field">
                <label for="rev-commento" class="form-label">Commento</label>
                <textarea name="commento" id="rev-commento" rows="4" class="form-control" maxlength="2000" required></textarea>
                <small class="field-error" id="err-commento"></small>
            </div>
            <div class="form-field">
                <label for="rev-foto" class="form-label">Foto (facoltativa)</label>
                <input type="file" name="fotoRecensione" id="rev-foto" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Pubblica recensione</button>
        </form>
    </div>
</div>

<script>
let filtroCorrente = '';

$(function() {
    caricaOrdini();
});

$('.filtro-btn').on('click', function() {
    $('.filtro-btn').removeClass('active').attr('aria-pressed', 'false');
    $(this).addClass('active').attr('aria-pressed', 'true');
    filtroCorrente = $(this).data('stato');
    caricaOrdini();
});

function caricaOrdini() {
    $.get('api/ba_miei_ordini.php', { stato: filtroCorrente }, function(resp) {
        const ordini = resp.ordini || [];
        if (ordini.length === 0) {
            $('#ordini-lista').html(`<div class="empty-state">
                <h2>Nessun ordine trovato</h2>
                <a href="index.php" class="btn btn-primary">Esplora il catalogo</a>
            </div>`);
            return;
        }

        let html = '';
        ordini.forEach(ord => {
            const idOrdine  = parseInt(ord.id_ordine);
            const annullato = ord.stato === 'Annullato';

            // Raggruppamento dei prodotti per venditore (un ordine può contenere più venditori)
            const perVenditore = {};
            ord.libri.forEach(lib => {
                const v = lib.venditore || 'Venditore';
                (perVenditore[v] = perVenditore[v] || []).push(lib);
            });
            const venditori = Object.keys(perVenditore);

            let corpo = '';
            if (annullato) {
                corpo += '<p class="avviso avviso--errore">Ordine annullato: i prodotti sono tornati disponibili e il pagamento è stato stornato.</p>';
            }
            venditori.forEach(venditore => {
                const libri = perVenditore[venditore];
                if (venditori.length > 1) {
                    const totV = libri.reduce((s, l) => s + parseFloat(l.prezzo_acquisto || 0) * parseInt(l.quantita || 1), 0);
                    corpo += `<div class="venditore-group">
                        <span>Venduto da <strong>${escapeHtml(venditore)}</strong></span>
                        <strong>${formatPrezzo(totV)}</strong>
                    </div>`;
                }
                libri.forEach(lib => {
                    const idProd = parseInt(lib.id_prodotto);
                    let azione = '';
                    if (lib.gia_recensito) {
                        azione = `<span class="review-stars" aria-label="Hai dato ${parseInt(lib.voto_utente)} stelle">${'★'.repeat(parseInt(lib.voto_utente) || 0)}</span>`;
                    } else if (!annullato) {
                        azione = `<button type="button" class="btn btn-secondary btn-small js-recensisci" data-id="${idProd}" data-nome="${escapeHtml(lib.nome)}">Recensisci</button>`;
                    }
                    corpo += `<div class="book-item">
                        <a href="dettaglio_prodotto.php?id=${idProd}" tabindex="-1" aria-hidden="true"><img class="book-img" src="${escapeHtml(lib.foto || 'img/default.jpg')}" alt=""></a>
                        <div class="book-item__body">
                            <a href="dettaglio_prodotto.php?id=${idProd}"><strong>${escapeHtml(lib.nome)}</strong></a>
                            <small class="muted">Quantità: ${parseInt(lib.quantita)} · Prezzo: ${formatPrezzo(lib.prezzo_acquisto)}</small>
                        </div>
                        <div>${azione}</div>
                    </div>`;
                });
            });

            if (ord.stato === 'Pagato') {
                corpo += `<div class="order-actions">
                    <button type="button" class="btn btn-danger-outline btn-small js-annulla" data-id="${idOrdine}">Annulla ordine</button>
                </div>`;
            }

            html += `<article class="order-card">
                <h2 class="order-heading">
                    <button type="button" class="order-header" aria-expanded="false" aria-controls="ordine-${idOrdine}">
                        <span><strong>Ordine #${idOrdine}</strong> <span class="muted">${escapeHtml(ord.data)}</span></span>
                        <span class="order-header__right">
                            <span class="badge ${annullato ? 'stato-annullato' : 'stato-pagato'}">${escapeHtml(ord.stato)}</span>
                            <strong>${formatPrezzo(ord.totale)}</strong>
                            <svg class="icon order-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg>
                        </span>
                    </button>
                </h2>
                <div class="order-body is-hidden" id="ordine-${idOrdine}">${corpo}</div>
            </article>`;
        });
        $('#ordini-lista').html(html);
    }, 'json');
}

/* Apertura/chiusura dettagli ordine */
$(document).on('click', '.order-header', function() {
    const corpo  = $('#' + $(this).attr('aria-controls'));
    const aperto = corpo.toggleClass('is-hidden').is(':visible');
    $(this).attr('aria-expanded', aperto);
});

/* Annullamento (UPDATE dello stato): il server ripristina le quantità in una transazione */
$(document).on('click', '.js-annulla', function() {
    const id = $(this).data('id');
    if (!confirm('Vuoi annullare l\'ordine #' + id + '? I prodotti torneranno disponibili.')) return;
    $.post('api/ba_miei_ordini.php', { action: 'annulla', id_ordine: id }, function(resp) {
        if (resp.status === 'ok') {
            mostraNotifica('Ordine #' + id + ' annullato.');
            caricaOrdini();
        } else {
            mostraNotifica(resp.msg || 'Annullamento non riuscito.', true);
        }
    }, 'json');
});

/* ===== Recensioni ===== */
function aggiornaStelle(v) {
    $('.star').each(function() { $(this).toggleClass('on', $(this).data('v') <= v); });
}

$(document).on('click', '.js-recensisci', function() {
    $('#formRecensione')[0].reset();
    $('#rev-idProdotto').val($(this).data('id'));
    $('#rev-titolo').text('Recensisci "' + $(this).data('nome') + '"');
    $('#err-commento').text('');
    aggiornaStelle(5);
    $('#modalRecensione').addClass('open');
    $('#rev-commento').trigger('focus');
});

$('input[name="voto"]').on('change', function() { aggiornaStelle(parseInt($(this).val())); });
$('.js-chiudi-recensione').on('click', function() { $('#modalRecensione').removeClass('open'); });

$('#rev-foto').on('change', function() {
    const file = this.files[0];
    if (file && (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size > 2 * 1024 * 1024)) {
        mostraNotifica('La foto deve essere JPG, PNG o WEBP e pesare al massimo 2 MB.', true);
        $(this).val('');
    }
});

$('#formRecensione').on('submit', function(e) {
    e.preventDefault();
    if ($('#rev-commento').val().trim().length < 3) {
        $('#err-commento').text('Scrivi almeno qualche parola (minimo 3 caratteri).');
        return;
    }
    $.ajax({
        url: 'api/ba_scrivi_recensione.php',
        type: 'POST',
        data: new FormData(this),
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(resp) {
            if (resp.status === 'ok') {
                $('#modalRecensione').removeClass('open');
                mostraNotifica('Recensione pubblicata.');
                caricaOrdini();
            } else {
                mostraNotifica(resp.msg || 'Pubblicazione non riuscita.', true);
            }
        }
    });
});
</script>
</body>
</html>
