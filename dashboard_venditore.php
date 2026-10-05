<?php
session_start();
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Dashboard venditore | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page">
    <div class="dash-header">
        <h1 class="page-title">Pannello venditore</h1>
        <a href="aggiungi_prodotto.php" class="btn btn-primary">Aggiungi prodotto</a>
    </div>

    <!-- STATISTICHE -->
    <div class="stat-grid">
        <div class="stat-box">
            <h2>Prodotti in vendita</h2>
            <p class="stat-number" id="count-libri">0</p>
        </div>
        <div class="stat-box">
            <h2>Vendite</h2>
            <p class="stat-number" id="count-ordini">0</p>
            <a href="#sezione-ordini" class="stat-link">Vedi lo storico</a>
        </div>
    </div>

    <div class="dash-columns">
        <!-- PRODOTTI -->
        <section aria-labelledby="titolo-prodotti">
            <h2 class="section-title" id="titolo-prodotti">I tuoi prodotti</h2>
            <div id="lista-libri-venditore"><p class="loading">Caricamento...</p></div>
        </section>

        <!-- VENDITE (sola lettura) -->
        <section id="sezione-ordini" aria-labelledby="titolo-ordini">
            <h2 class="section-title" id="titolo-ordini">Vendite</h2>
            <div class="filtri" role="group" aria-label="Filtra le vendite">
                <button type="button" class="filtro-btn active" data-stato="" aria-pressed="true">Tutte</button>
                <button type="button" class="filtro-btn" data-stato="Pagato" aria-pressed="false">Pagate</button>
                <button type="button" class="filtro-btn" data-stato="Annullato" aria-pressed="false">Annullate</button>
            </div>
            <div id="lista-ordini-venditore"><p class="loading">Caricamento...</p></div>
        </section>
    </div>

    <!-- PACCHETTI -->
    <section aria-labelledby="titolo-pacchetti">
        <h2 class="section-title" id="titolo-pacchetti">I tuoi pacchetti</h2>
        <div id="lista-pacchetti-venditore" class="pacchetti-grid"><p class="loading">Caricamento...</p></div>
    </section>
</main>

<!-- DIALOG MODIFICA PRODOTTO -->
<div id="modalModifica" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="titolo-modifica">
    <div class="modal-box modal-box--large">
        <button type="button" class="modal-close js-chiudi-modal" aria-label="Chiudi">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
        <h2 class="modal-title" id="titolo-modifica">Modifica prodotto</h2>
        <form id="formModifica" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id_prodotto" id="modifica-id">
            <div class="form-field">
                <label for="modifica-nome" class="form-label">Titolo</label>
                <input type="text" name="nome" id="modifica-nome" class="form-control" maxlength="150">
            </div>
            <div class="form-field">
                <label for="modifica-autore" class="form-label">Autore</label>
                <input type="text" name="autore" id="modifica-autore" class="form-control" maxlength="100">
            </div>
            <div class="form-field">
                <label for="modifica-descrizione" class="form-label">Descrizione</label>
                <textarea name="descrizione" id="modifica-descrizione" class="form-control" rows="4" maxlength="2000"></textarea>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="modifica-prezzo" class="form-label">Prezzo (€)</label>
                    <input type="number" name="prezzo" id="modifica-prezzo" class="form-control" step="0.01" min="0.01" max="9999.99">
                </div>
                <div class="form-field">
                    <label for="modifica-quantita" class="form-label">Copie disponibili</label>
                    <input type="number" name="quantita" id="modifica-quantita" class="form-control" min="0" max="9999" step="1">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="modifica-categoria" class="form-label">Categoria</label>
                    <select name="categoria" id="modifica-categoria" class="form-control"></select>
                </div>
                <div class="form-field">
                    <label for="modifica-sottocategoria" class="form-label">Sottocategoria</label>
                    <select name="sottocategoria" id="modifica-sottocategoria" class="form-control"></select>
                </div>
            </div>
            <div class="form-field">
                <label for="modifica-pacchetto" class="form-label">Pacchetto</label>
                <select name="id_pacchetto" id="modifica-pacchetto" class="form-control"></select>
            </div>

            <fieldset class="form-field">
                <legend class="form-label">Foto attuali</legend>
                <div id="modifica-foto-esistenti" class="foto-gestione"></div>
            </fieldset>
            <div class="form-field">
                <label for="modifica-foto" class="form-label">Aggiungi foto</label>
                <input type="file" name="foto[]" id="modifica-foto" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                <small class="help-text">JPG, PNG o WEBP, massimo 2 MB ciascuna, 5 foto in totale.</small>
            </div>

            <small class="field-error" id="err-modifica"></small>
            <button type="submit" class="btn btn-primary btn-block">Salva modifiche</button>
            <p id="msg-modifica" class="form-msg is-hidden" role="status"></p>
        </form>
    </div>
</div>

<!-- DIALOG MODIFICA PACCHETTO -->
<div id="modalPacchetto" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="titolo-mod-pack">
    <div class="modal-box">
        <button type="button" class="modal-close js-chiudi-modal" aria-label="Chiudi">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
        <h2 class="modal-title" id="titolo-mod-pack">Modifica pacchetto</h2>
        <form id="formPacchetto" novalidate>
            <input type="hidden" id="mod-pack-id">
            <div class="form-row">
                <div class="form-field">
                    <label for="mod-pack-nome" class="form-label">Nome</label>
                    <input type="text" id="mod-pack-nome" class="form-control" maxlength="100">
                </div>
                <div class="form-field">
                    <label for="mod-pack-sconto" class="form-label">Sconto (%)</label>
                    <input type="number" id="mod-pack-sconto" class="form-control" min="1" max="90">
                </div>
            </div>
            <small class="field-error" id="mod-pack-err"></small>
            <button type="submit" class="btn btn-primary">Salva</button>
        </form>
        <h3 class="sub-title">Prodotti nel pacchetto</h3>
        <ul id="mod-pack-prodotti" class="lista-gestione"></ul>
        <h3 class="sub-title">Prodotti che puoi aggiungere</h3>
        <ul id="mod-pack-disponibili" class="lista-gestione"></ul>
    </div>
</div>

<script>
let filtroOrdini = '';
let libriById    = {};    // prodotti del venditore, indicizzati per id
let pacchetti    = [];
let categorieDB  = [];

$(function() {
    $.get('api/ba_categorie.php', function(resp) { categorieDB = resp.categorie || []; }, 'json');
    caricaLibri();
    caricaOrdini();
    caricaPacchetti();
});

/* Chiusura generica dei dialog */
$('.js-chiudi-modal').on('click', function() { $(this).closest('.modal-overlay').removeClass('open'); });

/* ===================== PRODOTTI ===================== */
function caricaLibri() {
    return $.get('api/ba_libri_venditore.php', function(resp) {
        const libri = resp.libri || [];
        libriById = {};
        $('#count-libri').text(libri.length);
        if (libri.length === 0) {
            $('#lista-libri-venditore').html('<p class="muted">Non hai ancora prodotti in vendita.</p>');
            return;
        }
        let html = '';
        libri.forEach(lib => {
            libriById[lib.id_prodotto] = lib;
            const qta = parseInt(lib.quantita_disponibile) || 0;
            html += `<article class="manage-book-card">
                <img src="${escapeHtml(lib.url_foto || 'img/default.jpg')}" class="manage-book-img" alt="">
                <div class="manage-book-body">
                    <h3><a href="dettaglio_prodotto.php?id=${parseInt(lib.id_prodotto)}">${escapeHtml(lib.nome)}</a></h3>
                    <p class="muted">
                        ${lib.autore ? escapeHtml(lib.autore) + ' · ' : ''}${formatPrezzo(lib.prezzo)} ·
                        ${qta > 0 ? `<span class="stock--ok">${qta} disponibili</span>` : '<span class="stock--ko">Esaurito</span>'}
                    </p>
                </div>
                <div class="button-row">
                    <button type="button" class="btn btn-secondary btn-small js-modifica" data-id="${parseInt(lib.id_prodotto)}">Modifica</button>
                    <button type="button" class="btn btn-danger-outline btn-small js-elimina" data-id="${parseInt(lib.id_prodotto)}">Elimina</button>
                </div>
            </article>`;
        });
        $('#lista-libri-venditore').html(html);
    }, 'json');
}

$(document).on('click', '.js-elimina', function() {
    const lib = libriById[$(this).data('id')];
    if (!confirm('Eliminare "' + lib.nome + '"?')) return;
    $.post('api/ba_elimina_libro.php', { id_prodotto: lib.id_prodotto }, function(resp) {
        if (resp.status === 'ok') { caricaLibri(); caricaPacchetti(); }
        else mostraNotifica(resp.msg || 'Eliminazione non riuscita.', true);
    }, 'json');
});

/* --- Modifica prodotto --- */
function popolaCategorie(categoria, sottocategoria) {
    const selCat = $('#modifica-categoria').empty();
    categorieDB.filter(c => !c.nome_categoria_padre).forEach(c => {
        selCat.append($('<option>').val(c.nome_categoria).text(c.nome_categoria));
    });
    selCat.val(categoria);
    popolaSottocategorie(categoria, sottocategoria);
}

function popolaSottocategorie(padre, selezionata) {
    const sel = $('#modifica-sottocategoria').html('<option value="">Nessuna</option>');
    categorieDB.filter(c => c.nome_categoria_padre === padre).forEach(c => {
        sel.append($('<option>').val(c.nome_categoria).text(c.nome_categoria));
    });
    sel.val(selezionata || '');
}

$('#modifica-categoria').on('change', function() { popolaSottocategorie($(this).val(), ''); });

$(document).on('click', '.js-modifica', function() {
    const lib = libriById[$(this).data('id')];
    $('#formModifica')[0].reset();
    $('#msg-modifica').addClass('is-hidden');
    $('#err-modifica').text('');

    $('#modifica-id').val(lib.id_prodotto);
    $('#modifica-nome').val(lib.nome);
    $('#modifica-autore').val(lib.autore || '');
    $('#modifica-descrizione').val(lib.descrizione || '');
    $('#modifica-prezzo').val(lib.prezzo);
    $('#modifica-quantita').val(lib.quantita_disponibile);

    // Se la categoria del prodotto ha un padre, il padre va nella prima select e lei nella seconda
    if (lib.nome_categoria_padre) popolaCategorie(lib.nome_categoria_padre, lib.nome_categoria);
    else popolaCategorie(lib.nome_categoria, '');

    const selPack = $('#modifica-pacchetto').html('<option value="">Nessun pacchetto</option>');
    pacchetti.forEach(p => selPack.append($('<option>').val(p.id_pacchetto).text(p.nome + ' (-' + parseInt(p.sconto) + '%)')));
    selPack.val(lib.id_pacchetto || '');

    caricaFotoProdotto(lib.id_prodotto);
    $('#modalModifica').addClass('open');
});

/* Foto del prodotto: lettura ed eliminazione singola (CRUD sulle foto) */
function caricaFotoProdotto(idProdotto) {
    $('#modifica-foto-esistenti').html('<p class="muted">Caricamento...</p>');
    $.get('api/ba_foto_prodotto.php', { action: 'list', id_prodotto: idProdotto }, function(resp) {
        const foto = resp.foto || [];
        if (foto.length === 0) {
            $('#modifica-foto-esistenti').html('<p class="muted">Nessuna foto: verrà mostrata l\'immagine predefinita.</p>');
            return;
        }
        let html = '';
        foto.forEach((f, i) => {
            html += `<figure class="foto-item">
                <img src="${escapeHtml(f.url)}" alt="Foto ${i + 1} del prodotto">
                <button type="button" class="btn btn-danger-outline btn-small js-elimina-foto" data-id="${parseInt(f.id_foto)}" data-prodotto="${parseInt(idProdotto)}">Elimina</button>
            </figure>`;
        });
        $('#modifica-foto-esistenti').html(html);
    }, 'json');
}

$(document).on('click', '.js-elimina-foto', function() {
    if (!confirm('Eliminare questa foto?')) return;
    const idProdotto = $(this).data('prodotto');
    $.post('api/ba_foto_prodotto.php', { action: 'delete', id_foto: $(this).data('id') }, function(resp) {
        if (resp.status === 'ok') { caricaFotoProdotto(idProdotto); caricaLibri(); }
        else mostraNotifica(resp.msg || 'Eliminazione non riuscita.', true);
    }, 'json');
});

$('#formModifica').on('submit', function(e) {
    e.preventDefault();
    const prezzo = parseFloat($('#modifica-prezzo').val());
    const qta    = $('#modifica-quantita').val();
    const files  = Array.from($('#modifica-foto')[0].files);
    let msg = '';
    if ($('#modifica-nome').val().trim().length < 2)        msg = 'Il titolo deve avere almeno 2 caratteri.';
    else if ($('#modifica-autore').val().trim().length < 2) msg = "L'autore deve avere almeno 2 caratteri.";
    else if (!(prezzo > 0 && prezzo <= 9999.99))           msg = 'Il prezzo deve essere tra 0,01 e 9999,99 €.';
    else if (!/^\d{1,4}$/.test(qta))                       msg = 'Le copie devono essere un numero intero tra 0 e 9999.';
    else if (!$('#modifica-categoria').val())              msg = 'Scegli una categoria.';
    else if (files.some(f => !/^image\/(jpeg|png|webp)$/.test(f.type) || f.size > 2 * 1024 * 1024))
        msg = 'Le foto devono essere JPG, PNG o WEBP e pesare al massimo 2 MB.';
    $('#err-modifica').text(msg);
    if (msg) return;

    $.ajax({
        url: 'api/ba_modifica_libro.php',
        type: 'POST',
        data: new FormData(this),
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(resp) {
            if (resp.status === 'ok') {
                $('#msg-modifica').text('Modifiche salvate.').addClass('form-msg--ok').removeClass('form-msg--err is-hidden');
                caricaLibri();
                caricaPacchetti();
                setTimeout(() => $('#modalModifica').removeClass('open'), 900);
            } else {
                $('#msg-modifica').text(resp.msg || 'Salvataggio non riuscito.').addClass('form-msg--err').removeClass('form-msg--ok is-hidden');
            }
        }
    });
});

/* ===================== VENDITE (sola lettura) ===================== */
$('.filtro-btn').on('click', function() {
    $('.filtro-btn').removeClass('active').attr('aria-pressed', 'false');
    $(this).addClass('active').attr('aria-pressed', 'true');
    filtroOrdini = $(this).data('stato');
    caricaOrdini();
});

function caricaOrdini() {
    $.get('api/ba_ordini_venditore.php', { action: 'list', stato: filtroOrdini }, function(resp) {
        const ordini = resp.ordini || [];
        $('#count-ordini').text(ordini.length);

        if (ordini.length === 0) {
            $('#lista-ordini-venditore').html('<p class="muted">Nessuna vendita trovata.</p>');
            return;
        }
        let html = '';
        ordini.forEach(o => {
            const id = parseInt(o.IdOrdine);
            const annullato = o.StatoOrdine === 'Annullato';
            let righe = '';
            (o.libri || []).forEach(l => {
                righe += `<div class="ordine-libro">
                    <img src="${escapeHtml(l.Foto || 'img/default.jpg')}" alt="">
                    <div>
                        <strong>${escapeHtml(l.Titolo)}</strong><br>
                        <small class="muted">&times; ${parseInt(l.Quantita)} · ${formatPrezzo(parseFloat(l.Prezzo) * parseInt(l.Quantita))}</small>
                    </div>
                </div>`;
            });
            html += `<article class="ordine-card">
                <h3 class="order-heading">
                    <button type="button" class="ordine-header" aria-expanded="false" aria-controls="vendita-${id}">
                        <span><strong>Ordine #${id}</strong> <span class="muted">${escapeHtml(o.DataOrdine)} · ${escapeHtml(o.Cliente)}</span></span>
                        <span class="order-header__right">
                            <span class="badge ${annullato ? 'stato-annullato' : 'stato-pagato'}">${escapeHtml(o.StatoOrdine)}</span>
                            <strong>${formatPrezzo(o.TotaleVenditore)}</strong>
                        </span>
                    </button>
                </h3>
                <div class="ordine-body is-hidden" id="vendita-${id}">${righe}</div>
            </article>`;
        });
        $('#lista-ordini-venditore').html(html);
    }, 'json');
}

$(document).on('click', '.ordine-header', function() {
    const corpo  = $('#' + $(this).attr('aria-controls'));
    const aperto = corpo.toggleClass('is-hidden').is(':visible');
    $(this).attr('aria-expanded', aperto);
});

/* ===================== PACCHETTI ===================== */
function caricaPacchetti() {
    $.get('api/ba_pacchetti_venditore.php', function(resp) {
        pacchetti = resp.pacchetti || [];
        if (pacchetti.length === 0) {
            $('#lista-pacchetti-venditore').html('<p class="muted">Nessun pacchetto. Puoi crearne uno quando aggiungi un prodotto.</p>');
            return;
        }
        let html = '';
        pacchetti.forEach(p => {
            html += `<article class="pacchetto-card">
                <h3>${escapeHtml(p.nome)}</h3>
                <p class="muted">Sconto ${parseInt(p.sconto)}% acquistando tutti i ${parseInt(p.tot_prodotti)} prodotti</p>
                <div class="button-row">
                    <button type="button" class="btn btn-secondary btn-small js-modifica-pack" data-id="${parseInt(p.id_pacchetto)}">Modifica</button>
                    <button type="button" class="btn btn-danger-outline btn-small js-elimina-pack" data-id="${parseInt(p.id_pacchetto)}">Elimina</button>
                </div>
            </article>`;
        });
        $('#lista-pacchetti-venditore').html(html);

        // Se il dialog è aperto, ne aggiorna le liste
        const aperto = $('#mod-pack-id').val();
        if ($('#modalPacchetto').hasClass('open') && aperto) disegnaListePacchetto(parseInt(aperto));
    }, 'json');
}

function trovaPacchetto(id) {
    return pacchetti.find(p => parseInt(p.id_pacchetto) === parseInt(id));
}

function disegnaListePacchetto(idPacchetto) {
    const pack = trovaPacchetto(idPacchetto);
    if (!pack) return;
    const dentro = pack.prodotti || [];
    $('#mod-pack-prodotti').html(dentro.length
        ? dentro.map(p => `<li><span>${escapeHtml(p.nome)}</span>
            <button type="button" class="btn btn-danger-outline btn-small js-pack-prod" data-azione="remove_product" data-prodotto="${parseInt(p.id_prodotto)}">Togli</button></li>`).join('')
        : '<li class="muted">Nessun prodotto nel pacchetto.</li>');

    // Un prodotto può appartenere a un solo pacchetto
    const liberi = Object.values(libriById).filter(l => !l.id_pacchetto);
    $('#mod-pack-disponibili').html(liberi.length
        ? liberi.map(l => `<li><span>${escapeHtml(l.nome)}</span>
            <button type="button" class="btn btn-secondary btn-small js-pack-prod" data-azione="add_product" data-prodotto="${parseInt(l.id_prodotto)}">Aggiungi</button></li>`).join('')
        : '<li class="muted">Tutti i tuoi prodotti sono già in un pacchetto.</li>');
}

$(document).on('click', '.js-modifica-pack', function() {
    const pack = trovaPacchetto($(this).data('id'));
    $('#mod-pack-id').val(pack.id_pacchetto);
    $('#mod-pack-nome').val(pack.nome);
    $('#mod-pack-sconto').val(pack.sconto);
    $('#mod-pack-err').text('');
    disegnaListePacchetto(pack.id_pacchetto);
    $('#modalPacchetto').addClass('open');
});

$('#formPacchetto').on('submit', function(e) {
    e.preventDefault();
    const nome   = $('#mod-pack-nome').val().trim();
    const sconto = parseInt($('#mod-pack-sconto').val());
    if (nome.length < 2)              { $('#mod-pack-err').text('Il nome deve avere almeno 2 caratteri.'); return; }
    if (!(sconto >= 1 && sconto <= 90)) { $('#mod-pack-err').text('Lo sconto deve essere tra 1 e 90%.'); return; }
    $.post('api/ba_modifica_pacchetto.php', { action: 'update', id_pacchetto: $('#mod-pack-id').val(), nome: nome, sconto: sconto }, function(resp) {
        if (resp.status === 'ok') {
            $('#modalPacchetto').removeClass('open');
            caricaPacchetti();
        } else {
            $('#mod-pack-err').text(resp.msg || 'Salvataggio non riuscito.');
        }
    }, 'json');
});

$(document).on('click', '.js-pack-prod', function() {
    const idPacchetto = $('#mod-pack-id').val();
    $.post('api/ba_modifica_pacchetto.php', {
        action: $(this).data('azione'), id_pacchetto: idPacchetto, id_prodotto: $(this).data('prodotto')
    }, function(resp) {
        if (resp.status === 'ok') {
            // prima i prodotti (id_pacchetto aggiornato), poi i pacchetti
            caricaLibri().done(caricaPacchetti);
        } else {
            mostraNotifica(resp.msg || 'Operazione non riuscita.', true);
        }
    }, 'json');
});

$(document).on('click', '.js-elimina-pack', function() {
    const pack = trovaPacchetto($(this).data('id'));
    if (!confirm('Eliminare il pacchetto "' + pack.nome + '"? I prodotti restano in vendita senza sconto.')) return;
    $.post('api/ba_elimina_pacchetto.php', { id_pacchetto: pack.id_pacchetto }, function(resp) {
        if (resp.status === 'ok') { caricaPacchetti(); caricaLibri(); }
        else mostraNotifica(resp.msg || 'Eliminazione non riuscita.', true);
    }, 'json');
});
</script>
</body>
</html>