<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>The (E-)Shop Around the Corner | La tua libreria online</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- stili in style.css -->
</head>
<body>
<?php include("header.php"); ?>

<section class="hero">
    <h1>Benvenuto su The (E-)Shop Around the Corner</h1>
    <p>Scopri il nostro catalogo completo di libri, riviste e altre pubblicazioni.</p>
</section>

<!-- BANNER CATEGORIE -->
<div id="categories-banner-wrapper" style="max-width:1200px; margin:0 auto 40px; padding:0 20px;">
    <div id="categorie-banner" class="categories-scroll"></div>
</div>

<!-- MODAL AVVISAMI -->
<div id="modal-avvisami">
    <div class="modal-box">
        <div id="modal-stato-confirm">
            <div style="font-size:2.5em; margin-bottom:12px; color:#e67e22;">&#9993;</div>
            <h3 id="modal-titolo-libro">Avvisami quando torna disponibile</h3>
            <p style="color:#555; font-size:0.9em; margin-bottom:20px;">Riceverai una notifica quando questo prodotto sarà di nuovo in stock.</p>
            <input type="hidden" id="modal-id-prodotto">
            <input type="hidden" id="modal-nome-prodotto">
            <button class="modal-btn-confirm" onclick="confermaAvvisami()">Si, avvisami!</button>
            <br>
            <button class="modal-btn-cancel" onclick="chiudiModal()">Annulla</button>
        </div>
        <div id="modal-stato-ok" style="display:none;">
            <div style="font-size:2.5em; margin-bottom:12px; color:var(--primary-green);">&#10003;</div>
            <h3 style="color:var(--dark-green);">Sei nella lista!</h3>
            <p id="modal-ok-msg" style="color:#555;"></p>
            <button class="modal-btn-confirm" style="background:var(--primary-green);" onclick="chiudiModal()">Chiudi</button>
        </div>
    </div>
</div>

<div id="main-container" style="max-width:1200px; margin:0 auto;">

    <!-- SEZIONE FILTRO (visibile solo con ricerca/categoria) -->
    <div id="sezione-filtro" style="display:none;">
        <div style="padding:0 20px;">
            <a href="index.php" style="color:var(--primary-green); font-size:0.9em; text-decoration:none;">&#8592; Torna alla home</a>
        </div>
        <h2 class="section-title" id="titolo-filtro"></h2>
        <div id="grid-filtro" class="books-grid"></div>
    </div>

    <!-- SEZIONI HOME NORMALI -->
    <div id="sezione-home">
        <h2 class="section-title" id="titolo-nuovi">Nuovi Arrivi</h2>
        <div id="grid-nuovi" class="books-grid"></div>

        <div id="sezione-sconti-wrapper">
            <h2 class="section-title">In Offerta</h2>
            <div id="grid-sconti" class="books-grid"></div>
        </div>

        <div id="sezione-presto-wrapper">
            <h2 class="section-title">Presto di Nuovo Disponibile</h2>
            <div id="grid-presto" class="books-grid"></div>
        </div>
    </div>

</div>

<script>
var TIPO_UTENTE = "<?php echo isset($_SESSION['tipoUtente']) ? $_SESSION['tipoUtente'] : ''; ?>";
var idCarrelloSet = new Set(); // traccia id prodotti nel carrello per bottone rimuovi

$(document).ready(function() {

    // Carica stato carrello (solo per clienti)
    <?php if(isset($_SESSION['tipoUtente']) && $_SESSION['tipoUtente'] === 'cliente'): ?>
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        if (resp.status === 'ok' && resp.prodotti) {
            resp.prodotti.forEach(p => idCarrelloSet.add(parseInt(p.IdProdotto || p.id_prodotto)));
        }
    }, 'json');
    <?php endif; ?>

    // Banner categorie + tipi prodotto
    $.get('api/ba_lista_categorie.php', function(resp) {
        const cats   = resp.categorie || [];
        const padri  = cats.filter(c => !c.nome_categoria_padre);
        const figlie = cats.filter(c => c.nome_categoria_padre);
        padri.forEach(padre => {
            const sotto = figlie.filter(f => f.nome_categoria_padre === padre.nome_categoria);
            if (sotto.length === 0) return;
            const chips = sotto.map(f =>
                `<a href="index.php?cat=${encodeURIComponent(f.nome_categoria)}" class="cat-chip">${f.nome_categoria}</a>`
            ).join('');
            $('#categorie-banner').append(`
                <div class="cat-group">
                    <div class="cat-group-label">${padre.nome_categoria}</div>
                    <div class="cat-group-chips">${chips}</div>
                </div>`);
        });
    }, 'json');

    // Aggiungi chips per tipo prodotto (riviste, fumetti, etc.)
    const tipiProdotto = [
        { tipo: 'rivista',   label: 'Riviste' },
        { tipo: 'magazine',  label: 'Magazine' },
        { tipo: 'periodico', label: 'Periodici' },
        { tipo: 'fumetto',   label: 'Fumetti' }
    ];
    let tipsHtml = tipiProdotto.map(t =>
        `<a href="index.php?tipo=${encodeURIComponent(t.tipo)}" class="cat-chip">${t.label}</a>`
    ).join('');
    $('#categorie-banner').append(`
        <div class="cat-group">
            <div class="cat-group-label">Altro</div>
            <div class="cat-group-chips">${tipsHtml}</div>
        </div>`);

    // Prodotti
    const urlParams = new URLSearchParams(window.location.search);
    const catFiltro  = urlParams.get('cat')  || '';
    const qFiltro    = urlParams.get('q')    || '';
    const tipoFiltro = urlParams.get('tipo') || '';

    $.get('api/ba_ricerca.php', { cat: catFiltro, q: qFiltro, tipo: tipoFiltro }, function(resp) {
        const libri = resp.prodotti || [];

        if (catFiltro || qFiltro || tipoFiltro) {
            // Modalità filtro — nascondi home, mostra risultati
            $('#sezione-home').hide();
            $('#categories-banner-wrapper').hide();
            $('#sezione-filtro').show();
            const titolo = catFiltro
                ? `Risultati per categoria: <em>${catFiltro}</em>`
                : tipoFiltro
                ? `Risultati per: <em>${tipoFiltro}</em>`
                : `Risultati per: <em>"${qFiltro}"</em>`;
            $('#titolo-filtro').html(titolo);
            if (libri.length === 0) {
                $('#grid-filtro').html(`
                    <div style="padding:60px 20px; text-align:center; color:var(--text-sec); grid-column:1/-1;">
                        <div style="font-size:3em; margin-bottom:16px; color:#ccc;">&#128218;</div>
                        <h3 style="color:var(--dark-green);">Nessun titolo trovato</h3>
                        <p>Prova con un'altra categoria o termine di ricerca.</p>
                        <a href="index.php" style="color:var(--dark-green); font-weight:600;">&#8592; Torna alla home</a>
                    </div>`);
            } else {
                renderizza(libri, '#grid-filtro');
            }
        } else {
            // Home normale
            const disponibili  = libri.filter(l => parseInt(l.quantita_disponibile) > 0);
            const esauriti     = libri.filter(l => parseInt(l.quantita_disponibile) === 0);

            // Nuovi arrivi: 10 più recenti disponibili
            renderizza(disponibili.slice(0, 10), '#grid-nuovi');

            // In Offerta: un solo prodotto per pacchetto, max 10
            const vistoPacchetto = new Set();
            const inOfferta = [];
            libri.filter(l => l.nome_pacchetto && parseInt(l.quantita_disponibile) > 0).forEach(l => {
                if (!vistoPacchetto.has(l.nome_pacchetto) && inOfferta.length < 10) {
                    vistoPacchetto.add(l.nome_pacchetto);
                    inOfferta.push(l);
                }
            });
            if (inOfferta.length > 0) {
                renderizza(inOfferta, '#grid-sconti');
            } else {
                $('#sezione-sconti-wrapper').hide();
            }

            // Presto disponibile: esauriti max 10
            if (esauriti.length > 0) {
                renderizza(esauriti.slice(0, 10), '#grid-presto');
            } else {
                $('#sezione-presto-wrapper').hide();
            }
        }
    }, 'json');
});

function renderizza(libri, selector) {
    let html = '';
    libri.forEach(lib => {
        const esaurito    = parseInt(lib.quantita_disponibile) === 0;
        const eAbbonabile = ['rivista','magazine','periodico','fumetto'].includes(lib.tipo_prodotto);
        const haPacchetto = lib.nome_pacchetto && parseFloat(lib.sconto_pacchetto) > 0;
        const haPacchettoBadge = lib.nome_pacchetto && !eAbbonabile; // saga/promo: badge senza prezzo scontato
        const prezzo      = parseFloat(lib.prezzo);
        const prezzoSc    = parseFloat(lib.PrezzoScontato || lib.prezzo);
        const nelCarr     = idCarrelloSet.has(parseInt(lib.id_prodotto));

        // Badge sconto pacchetto (saga, promo autore, offerte reali)
        let badgeSconto = '';
        if (haPacchettoBadge) {
            const nomePack = lib.nome_pacchetto;
            let label = '';
            if (nomePack.toLowerCase().includes('autore') || nomePack.toLowerCase().includes('autrice')) {
                label = 'Promo autore';
            } else if (nomePack.toLowerCase().includes('saga') || nomePack.toLowerCase().includes('trilogia') || nomePack.toLowerCase().includes('serie')) {
                label = 'Fa parte di una saga';
            } else if (lib.tipo_pacchetto === 'abbonamento') {
                label = 'Abbonamento disponibile';
            } else {
                label = nomePack;
            }
            badgeSconto = `<span class="badge-sconto" style="font-size:0.7em;">${label}</span><br>`;
        }

        // Badge abbonamento per periodici
        let badgeAbb = '';
        if (eAbbonabile) {
            badgeAbb = `<span style="display:inline-block;background:#f5eef8;color:#8e44ad;border:1px solid #d2b4de;border-radius:4px;font-size:0.7em;font-weight:700;padding:2px 7px;margin-bottom:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8e44ad" style="width:10px;height:10px;vertical-align:middle;margin-right:2px;"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                Abbonamento disponibile
            </span><br>`;
        }

        // Prezzo (scontato solo se sconto_pacchetto > 0)
        let prezzoHtml = `<div class="book-price">€${prezzo.toFixed(2)}</div>`;
        if (haPacchetto && prezzoSc < prezzo) {
            prezzoHtml = `<div class="book-price">€${prezzoSc.toFixed(2)} <small style="text-decoration:line-through;color:#bbb;font-size:0.6em;font-weight:400;margin-left:4px;">€${prezzo.toFixed(2)}</small></div>`;
        }

        // Bottone: avvisami / vuoto per venditore / aggiungi o rimuovi
        let btnHtml = '';
        if (esaurito) {
            btnHtml = `
                <span class="badge-esaurito">Esaurito</span>
                <button class="btn-avvisami" onclick="event.stopPropagation(); apriModal(${lib.id_prodotto}, '${lib.nome.replace(/'/g,"\\'")}')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:15px;height:15px;"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                    Avvisami quando torna
                </button>`;
        } else if (TIPO_UTENTE === 'venditore') {
            btnHtml = '';
        } else if (nelCarr) {
            btnHtml = `<button class="btn-add-cart" id="btn-cart-${lib.id_prodotto}"
                style="background:#e74c3c;"
                onclick="event.stopPropagation(); rimuoviDaCarrelloHome(${lib.id_prodotto}, this)">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                Rimuovi dal carrello
            </button>`;
        } else {
            btnHtml = `<button class="btn-add-cart" id="btn-cart-${lib.id_prodotto}"
                onclick="event.stopPropagation(); aggiungiAlCarrello(${lib.id_prodotto}, this)">
                Aggiungi al carrello
            </button>`;
        }

        html += `
        <div class="book-card">
            <img src="${lib.URLfoto || 'img/default.jpg'}" alt="${lib.nome}"
                 onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'"
                 style="cursor:pointer; height:260px; object-fit:cover;">
            <div class="book-info">
                ${badgeSconto}${badgeAbb}
                <h3 style="margin:0; font-size:1em; line-height:1.3; cursor:pointer;"
                    onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'">${lib.nome}</h3>
                <small style="color:#888; margin-top:2px;">${lib.autore || ''}</small>
                ${prezzoHtml}
                ${btnHtml}
            </div>
        </div>`;
    });
    $(selector).html(html || '<p style="padding:20px; color:#999;">Nessun prodotto disponibile.</p>');
}

// MODAL AVVISAMI
function apriModal(id, nome) {
    <?php if (!isset($_SESSION['IdUtente'])): ?>
        if (confirm('Devi essere loggato. Vuoi accedere?')) window.location.href = 'login.php';
        return;
    <?php endif; ?>
    $('#modal-stato-confirm').show();
    $('#modal-stato-ok').hide();
    $('#modal-id-prodotto').val(id);
    $('#modal-nome-prodotto').val(nome);
    $('#modal-titolo-libro').text('Vuoi essere avvisato quando "' + nome + '" torna disponibile?');
    $('#modal-avvisami').addClass('open');
}

function chiudiModal() { $('#modal-avvisami').removeClass('open'); }

$('#modal-avvisami').on('click', function(e) {
    if ($(e.target).is('#modal-avvisami')) chiudiModal();
});

function confermaAvvisami() {
    $('#modal-stato-confirm').hide();
    $('#modal-ok-msg').text('Sarai avvisato al tuo indirizzo email personale quando il prodotto torna disponibile.');
    $('#modal-stato-ok').show();
}

function aggiungiAlCarrello(id, btn) {
    <?php if(!isset($_SESSION['IdUtente'])): ?>
        if (confirm('Devi essere loggato per aggiungere al carrello. Vuoi accedere?')) {
            window.location.href = 'login.php';
        }
        return;
    <?php endif; ?>
    $.post('api/ba_carrello.php', { action: 'add', idProdotto: id }, function(resp) {
        if (resp.status === 'ok') {
            mostraNotifica('Aggiunto al carrello!');
            if (typeof updateCartBadge === 'function') updateCartBadge();
            idCarrelloSet.add(parseInt(id));
            if (btn) {
                btn.style.background = '#e74c3c';
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg> Rimuovi dal carrello';
                btn.setAttribute('onclick', `event.stopPropagation(); rimuoviDaCarrelloHome(${id}, this)`);
            }
        } else {
            mostraNotifica(resp.msg || 'Errore!', true);
        }
    }, 'json');
}

function rimuoviDaCarrelloHome(id, btn) {
    $.post('api/ba_carrello.php', { action: 'remove', idProdotto: id }, function(resp) {
        if (resp.status === 'ok') {
            if (typeof updateCartBadge === 'function') updateCartBadge();
            idCarrelloSet.delete(parseInt(id));
            if (btn) {
                btn.style.background = '';
                btn.innerHTML = 'Aggiungi al carrello';
                btn.setAttribute('onclick', `event.stopPropagation(); aggiungiAlCarrello(${id}, this)`);
            }
        }
    }, 'json');
}

function mostraNotifica(msg, isError = false) {
    const v = document.getElementById('popup-overlay');
    if (v) v.remove();
    const overlay = document.createElement('div');
    overlay.id = 'popup-overlay';
    overlay.innerHTML = `
        <div id="popup-box">
            <div id="popup-icon" style="color:${isError ? '#e74c3c' : 'var(--primary-green)'};">${isError ? '&#10007;' : '&#10003;'}</div>
            <div id="popup-msg">${msg}</div>
            <button id="popup-ok" onclick="this.closest('#popup-overlay').remove()">OK</button>
        </div>`;
    document.body.appendChild(overlay);
    overlay.onclick = e => { if (e.target === overlay) overlay.remove(); };
}
</script>
</body>
</html>