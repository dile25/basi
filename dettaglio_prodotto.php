<?php
session_start();
$tipoUtente = $_SESSION['tipoUtente'] ?? '';
$loggato    = isset($_SESSION['IdUtente']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Dettaglio prodotto | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page page--medium">
    <p id="loading" class="loading">Caricamento del prodotto...</p>

    <div id="contentWrapper" class="is-hidden">
        <div class="product-layout">
            <!-- GALLERIA FOTO -->
            <div class="gallery">
                <img src="" id="mainImage" class="gallery-main" alt="">
                <div id="thumbsContainer" class="gallery-thumbs"></div>
            </div>

            <!-- INFORMAZIONI -->
            <div class="product-info">
                <h1 id="pTitle" class="product-title"></h1>
                <p class="product-author">di <strong id="pAuthor"></strong></p>
                <p class="product-meta">
                    Venduto da <a href="#" id="pVendorLink"></a>
                    <span aria-hidden="true">|</span> Categoria: <strong id="pCat"></strong>
                </p>
                <p id="pPrice" class="product-price"></p>
                <p id="stockHtml" class="stock"></p>

                <?php if ($tipoUtente !== 'venditore'): ?>
                <div class="product-actions" id="zona-acquisto">
                    <div class="qty-row" id="zona-quantita">
                        <span class="form-label" id="label-quantita">Quantità</span>
                        <div class="qty-wrapper" role="group" aria-labelledby="label-quantita">
                            <button type="button" class="qty-btn" id="qtyMeno" aria-label="Diminuisci quantità">&minus;</button>
                            <span class="qty-display" id="qtyValore" aria-live="polite">1</span>
                            <button type="button" class="qty-btn" id="qtyPiu" aria-label="Aumenta quantità">+</button>
                        </div>
                        <span id="subtotale-dettaglio" class="subtotale"></span>
                    </div>
                    <button type="button" id="btnCarrello" class="btn btn-primary btn-block btn-large">Aggiungi al carrello</button>
                    <div class="button-row">
                        <button type="button" id="btnFav" class="btn btn-fav" aria-pressed="false">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            <span>Aggiungi ai preferiti</span>
                        </button>
                        <button type="button" id="btnRecensisci" class="btn btn-secondary is-hidden">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            Scrivi una recensione
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <section class="product-desc" aria-labelledby="titolo-desc">
                    <h2 id="titolo-desc">Descrizione</h2>
                    <p id="pDesc"></p>
                </section>
            </div>
        </div>

        <!-- PACCHETTO -->
        <section id="riquadro-pacchetto" class="pacchetto-box is-hidden" aria-labelledby="titolo-pacchetto">
            <h2 id="titolo-pacchetto"></h2>
            <p id="desc-pacchetto" class="muted"></p>
            <div id="libri-pacchetto-grid" class="pacchetto-grid"></div>
        </section>

        <!-- RECENSIONI -->
        <section class="reviews" aria-labelledby="titolo-recensioni">
            <h2 id="titolo-recensioni">Recensioni dei lettori</h2>
            <div id="reviewsList"></div>
        </section>
    </div>
</main>

<!-- DIALOG RECENSIONE (crea / modifica) -->
<div id="modalRecensione" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-rec-titolo">
    <div class="modal-box">
        <button type="button" class="modal-close js-chiudi-recensione" aria-label="Chiudi">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
        <h2 class="modal-title" id="modal-rec-titolo">La tua recensione</h2>
        <form id="formRecensione" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="idProdotto" id="rev-idProdotto">
            <input type="hidden" name="id_recensione" id="rev-id-recensione" value="0">

            <fieldset class="star-rating">
                <legend class="form-label">Valutazione</legend>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" name="voto" id="voto-<?php echo $i; ?>" value="<?php echo $i; ?>" class="visually-hidden" <?php echo $i === 5 ? 'checked' : ''; ?>>
                    <label for="voto-<?php echo $i; ?>" class="star" data-v="<?php echo $i; ?>" title="<?php echo $i; ?> su 5">
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
                <img id="rev-foto-preview" class="foto-preview is-hidden" src="" alt="Anteprima della foto scelta">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Pubblica recensione</button>
        </form>
    </div>
</div>

<script>
const TIPO_UTENTE = <?php echo json_encode($tipoUtente); ?>;
const LOGGATO     = <?php echo $loggato ? 'true' : 'false'; ?>;
const ID_PRODOTTO = parseInt(new URLSearchParams(window.location.search).get('id')) || 0;

let prodotto       = null;   // dati del prodotto corrente
let nelCarrello    = false;
let neiPreferiti   = false;
let quantita       = 1;
let idCarrelloSet  = new Set();
let recensioniById = {};     // recensioni caricate, per la modifica senza passare testo negli attributi

$(function() {
    if (!ID_PRODOTTO) {
        $('#loading').html('Prodotto non trovato. <a href="index.php">Torna alla home</a>');
        return;
    }
    caricaProdotto();
    caricaRecensioni();
});

/* ===================== PRODOTTO ===================== */
function caricaProdotto() {
    $.get('api/ba_dettaglio_prodotto.php', { id: ID_PRODOTTO }, function(resp) {
        if (resp.status !== 'ok') {
            $('#loading').text(resp.msg || 'Prodotto non trovato.');
            return;
        }
        prodotto = resp.dettagli;
        document.title = prodotto.NomeProdotto + ' | The (E-)Shop Around the Corner';

        $('#pTitle').text(prodotto.NomeProdotto);
        $('#pAuthor').text(prodotto.autore || 'Autore non specificato');
        $('#pDesc').text(prodotto.descrizione || 'Nessuna descrizione.');
        $('#pCat').text(prodotto.NomeCategoria || 'Generale');
        $('#pVendorLink').text(prodotto.NomeVenditore)
                         .attr('href', 'profilo_venditore.php?u=' + encodeURIComponent(prodotto.IdVenditore));
        $('#pPrice').text(formatPrezzo(prodotto.prezzo));
        $('#rev-idProdotto').val(prodotto.IdProdotto);

        mostraGalleria(prodotto.foto || []);

        const disponibili = parseInt(prodotto.QuantitaDisp) || 0;
        if (disponibili > 0) {
            $('#stockHtml').addClass('stock--ok').text(disponibili + (disponibili === 1 ? ' copia disponibile' : ' copie disponibili'));
        } else {
            $('#stockHtml').addClass('stock--ko').text('Momentaneamente esaurito');
            $('#zona-quantita').addClass('is-hidden');
            $('#btnCarrello').text('Non disponibile').prop('disabled', true);
        }

        if (prodotto.id_pacchetto) mostraPacchetto();

        $('#loading').addClass('is-hidden');
        $('#contentWrapper').removeClass('is-hidden');

        if (TIPO_UTENTE === 'cliente') {
            verificaStatoCarrello();
            verificaStatoPreferiti();
        } else {
            aggiornaQuantita(1);
        }
    }, 'json');
}

function mostraGalleria(foto) {
    const principale = foto.length ? foto[0] : 'img/default.jpg';
    $('#mainImage').attr('src', principale).attr('alt', 'Copertina di ' + prodotto.NomeProdotto);
    if (foto.length < 2) return;
    let html = '';
    foto.forEach((url, i) => {
        html += `<button type="button" class="thumb ${i === 0 ? 'active' : ''}" data-src="${escapeHtml(url)}" aria-label="Mostra foto ${i + 1} di ${foto.length}">
            <img src="${escapeHtml(url)}" alt="">
        </button>`;
    });
    $('#thumbsContainer').html(html);
}

$(document).on('click', '.thumb', function() {
    $('#mainImage').attr('src', $(this).data('src'));
    $('.thumb').removeClass('active');
    $(this).addClass('active');
});

/* ===================== QUANTITÀ E CARRELLO ===================== */
function maxQuantita() {
    return Math.max(1, parseInt(prodotto ? prodotto.QuantitaDisp : 1) || 1);
}

function aggiornaQuantita(q) {
    quantita = Math.max(1, Math.min(q, maxQuantita()));
    $('#qtyValore').text(quantita);
    $('#qtyMeno').prop('disabled', quantita <= 1);
    $('#qtyPiu').prop('disabled', quantita >= maxQuantita());
    $('#subtotale-dettaglio').text('Subtotale: ' + formatPrezzo(parseFloat(prodotto.prezzo) * quantita));
}

function aggiornaBottoneCarrello() {
    if ((parseInt(prodotto.QuantitaDisp) || 0) <= 0 && !nelCarrello) return;   // esaurito: resta "Non disponibile"
    $('#btnCarrello')
        .text(nelCarrello ? 'Rimuovi dal carrello' : 'Aggiungi al carrello')
        .toggleClass('btn-primary', !nelCarrello)
        .toggleClass('btn-danger-outline', nelCarrello);
}

function verificaStatoCarrello() {
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        const prodotti = resp.prodotti || [];
        idCarrelloSet = new Set(prodotti.map(p => parseInt(p.IdProdotto)));
        const inCarrello = prodotti.find(p => parseInt(p.IdProdotto) === ID_PRODOTTO);
        nelCarrello = !!inCarrello;
        aggiornaQuantita(inCarrello ? parseInt(inCarrello.quantita) : 1);
        aggiornaBottoneCarrello();
        if (prodotto.id_pacchetto) mostraPacchetto();   // aggiorna i bottoni del pacchetto
    }, 'json');
}

function cambiaQuantita(delta) {
    const nuova = Math.max(1, Math.min(quantita + delta, maxQuantita()));
    if (nuova === quantita) return;
    if (!nelCarrello) { aggiornaQuantita(nuova); return; }
    // Se il prodotto è già nel carrello la quantità si aggiorna subito sul server
    $.post('api/ba_carrello.php', { action: 'update', idProdotto: ID_PRODOTTO, qty: nuova }, function(resp) {
        if (resp.status === 'ok') {
            aggiornaQuantita(nuova);
            updateCartBadge();
        } else {
            mostraNotifica(resp.msg || 'Quantità non disponibile.', true);
        }
    }, 'json');
}
$('#qtyMeno').on('click', () => cambiaQuantita(-1));
$('#qtyPiu').on('click',  () => cambiaQuantita(1));

$('#btnCarrello').on('click', function() {
    if (!LOGGATO) { window.location.href = 'login.php'; return; }
    if (nelCarrello) {
        $.post('api/ba_carrello.php', { action: 'remove', idProdotto: ID_PRODOTTO }, function(resp) {
            if (resp.status === 'ok') {
                nelCarrello = false;
                idCarrelloSet.delete(ID_PRODOTTO);
                aggiornaQuantita(1);
                aggiornaBottoneCarrello();
                updateCartBadge();
            }
        }, 'json');
    } else {
        $.post('api/ba_carrello.php', { action: 'add', idProdotto: ID_PRODOTTO, quantita: quantita }, function(resp) {
            if (resp.status === 'ok') {
                nelCarrello = true;
                idCarrelloSet.add(ID_PRODOTTO);
                aggiornaBottoneCarrello();
                updateCartBadge();
            } else {
                mostraNotifica(resp.msg || 'Impossibile aggiungere il prodotto.', true);
            }
        }, 'json');
    }
});

/* ===================== PREFERITI ===================== */
function verificaStatoPreferiti() {
    $.get('api/ba_get_preferiti.php', function(resp) {
        neiPreferiti = (resp.preferiti || []).some(p => parseInt(p.id_prodotto) === ID_PRODOTTO);
        aggiornaBottonePreferiti();
    }, 'json');
}

function aggiornaBottonePreferiti() {
    $('#btnFav').toggleClass('attivo', neiPreferiti)
                .attr('aria-pressed', neiPreferiti)
                .find('span').text(neiPreferiti ? 'Nei preferiti' : 'Aggiungi ai preferiti');
}

$('#btnFav').on('click', function() {
    if (!LOGGATO) { window.location.href = 'login.php'; return; }
    $.post('api/ba_toggle_preferiti.php', { idProdotto: ID_PRODOTTO }, function(resp) {
        if (resp.status === 'ok') {
            neiPreferiti = (resp.action === 'added');
            aggiornaBottonePreferiti();
        } else {
            mostraNotifica(resp.msg || 'Accedi come cliente per usare i preferiti.', true);
        }
    }, 'json');
});

/* ===================== PACCHETTO ===================== */
function mostraPacchetto() {
    const altri  = prodotto.libriPacchetto || [];
    const totale = altri.length + 1;
    const sconto = parseInt(prodotto.sconto_pacchetto) || 0;

    $('#titolo-pacchetto').text('Pacchetto "' + prodotto.NomePacchetto + '"');
    $('#desc-pacchetto').text('Metti nel carrello tutti i ' + totale + ' prodotti del pacchetto: lo sconto del '
        + sconto + '% si applica automaticamente.');

    let html = '';
    altri.forEach(l => {
        const id   = parseInt(l.id_prodotto);
        const link = 'dettaglio_prodotto.php?id=' + id;
        let azione = '';
        if (parseInt(l.quantita_disponibile) <= 0) {
            azione = '<span class="badge badge-esaurito">Esaurito</span>';
        } else if (TIPO_UTENTE === 'cliente') {
            azione = idCarrelloSet.has(id)
                ? `<button type="button" class="btn btn-danger-outline btn-small btn-block js-pack-rimuovi" data-id="${id}">Rimuovi</button>`
                : `<button type="button" class="btn btn-primary btn-small btn-block js-pack-aggiungi" data-id="${id}">Aggiungi</button>`;
        }
        html += `<article class="pacchetto-item">
            <a href="${link}" tabindex="-1" aria-hidden="true"><img src="${escapeHtml(l.foto || 'img/default.jpg')}" alt=""></a>
            <h3><a href="${link}">${escapeHtml(l.nome)}</a></h3>
            ${l.autore ? `<p class="muted">${escapeHtml(l.autore)}</p>` : ''}
            <p class="book-price">${formatPrezzo(l.prezzo)}</p>
            ${azione}
        </article>`;
    });
    $('#libri-pacchetto-grid').html(html);
    $('#riquadro-pacchetto').removeClass('is-hidden');
}

$(document).on('click', '.js-pack-aggiungi, .js-pack-rimuovi', function() {
    const id     = parseInt($(this).data('id'));
    const azione = $(this).hasClass('js-pack-aggiungi') ? 'add' : 'remove';
    $.post('api/ba_carrello.php', { action: azione, idProdotto: id, quantita: 1 }, function(resp) {
        if (resp.status === 'ok') {
            azione === 'add' ? idCarrelloSet.add(id) : idCarrelloSet.delete(id);
            updateCartBadge();
            mostraPacchetto();
        } else {
            mostraNotifica(resp.msg || 'Operazione non riuscita.', true);
        }
    }, 'json');
});

/* ===================== RECENSIONI ===================== */
function disegnaStelle(n) {
    n = parseInt(n) || 0;
    return '★'.repeat(n) + '☆'.repeat(5 - n);
}

function caricaRecensioni() {
    $.get('api/ba_recensioni_prodotto.php', { id: ID_PRODOTTO }, function(resp) {
        const recensioni = resp.recensioni || [];
        const utente     = <?php echo json_encode($_SESSION['IdUtente'] ?? ''); ?>;
        recensioniById   = {};

        // Il bottone compare solo se il backend conferma che il cliente ha acquistato il prodotto
        $('#btnRecensisci').toggleClass('is-hidden', !resp.puoRecensire);

        if (recensioni.length === 0) {
            $('#reviewsList').html('<p class="muted">Ancora nessuna recensione.</p>');
            return;
        }
        let html = '';
        recensioni.forEach(r => {
            recensioniById[r.id_recensione] = r;
            const mia = r.username === utente;
            html += `<article class="review">
                <header class="review-head">
                    <div>
                        <strong>@${escapeHtml(r.username)}</strong>
                        ${mia ? '<span class="badge badge-tu">Tu</span>' : ''}
                    </div>
                    <span class="review-stars" aria-label="${parseInt(r.valutazione)} stelle su 5">${disegnaStelle(r.valutazione)}</span>
                </header>
                <p class="review-text">${escapeHtml(r.testo)}</p>
                ${r.foto ? `<a href="${escapeHtml(r.foto)}" target="_blank" rel="noopener"><img class="review-photo" src="${escapeHtml(r.foto)}" alt="Foto allegata da ${escapeHtml(r.username)}"></a>` : ''}
                <footer class="review-foot">
                    <small class="muted">${escapeHtml(r.data)}</small>
                    ${mia ? `<span class="button-row">
                        <button type="button" class="btn btn-secondary btn-small js-modifica-rec" data-id="${parseInt(r.id_recensione)}">Modifica</button>
                        <button type="button" class="btn btn-danger-outline btn-small js-elimina-rec" data-id="${parseInt(r.id_recensione)}">Elimina</button>
                    </span>` : ''}
                </footer>
            </article>`;
        });
        $('#reviewsList').html(html);
    }, 'json');
}

function aggiornaStelle(v) {
    $('.star').each(function() { $(this).toggleClass('on', $(this).data('v') <= v); });
}

function apriModalRecensione(idRec, voto, testo, titolo) {
    $('#formRecensione')[0].reset();
    $('#rev-idProdotto').val(ID_PRODOTTO);
    $('#rev-id-recensione').val(idRec);
    $('#voto-' + voto).prop('checked', true);
    aggiornaStelle(voto);
    $('#rev-commento').val(testo);
    $('#err-commento').text('');
    $('#rev-foto-preview').addClass('is-hidden');
    $('#modal-rec-titolo').text(titolo);
    $('#modalRecensione').addClass('open');
    $('#rev-commento').trigger('focus');
}

$('#btnRecensisci').on('click', function() {
    apriModalRecensione(0, 5, '', 'La tua recensione');
});

$(document).on('click', '.js-modifica-rec', function() {
    const r = recensioniById[$(this).data('id')];
    if (r) apriModalRecensione(r.id_recensione, parseInt(r.valutazione), r.testo, 'Modifica la tua recensione');
});

$(document).on('click', '.js-elimina-rec', function() {
    if (!confirm('Vuoi eliminare la tua recensione?')) return;
    $.post('api/ba_elimina_recensione.php', { id_recensione: $(this).data('id') }, function(resp) {
        if (resp.status === 'ok') caricaRecensioni();
        else mostraNotifica(resp.msg || 'Eliminazione non riuscita.', true);
    }, 'json');
});

$('input[name="voto"]').on('change', function() { aggiornaStelle(parseInt($(this).val())); });
$('.js-chiudi-recensione').on('click', function() { $('#modalRecensione').removeClass('open'); });

$('#rev-foto').on('change', function() {
    const file = this.files[0];
    if (!file) { $('#rev-foto-preview').addClass('is-hidden'); return; }
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size > 2 * 1024 * 1024) {
        mostraNotifica('La foto deve essere JPG, PNG o WEBP e pesare al massimo 2 MB.', true);
        $(this).val('');
        return;
    }
    const reader = new FileReader();
    reader.onload = e => $('#rev-foto-preview').attr('src', e.target.result).removeClass('is-hidden');
    reader.readAsDataURL(file);
});

$('#formRecensione').on('submit', function(e) {
    e.preventDefault();
    const testo = $('#rev-commento').val().trim();
    if (testo.length < 3) {
        $('#err-commento').text('Scrivi almeno qualche parola (minimo 3 caratteri).');
        return;
    }
    // FormData + contentType/processData false: necessario per inviare il file via AJAX
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
                caricaRecensioni();
            } else {
                mostraNotifica(resp.msg || 'Pubblicazione non riuscita.', true);
            }
        }
    });
});
</script>
</body>
</html>