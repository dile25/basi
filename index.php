<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>The (E-)Shop Around the Corner | La tua libreria online</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .hero { background: linear-gradient(135deg, var(--primary-green), var(--dark-green)); color: white; padding: 60px 20px; text-align: center; border-radius: 0 0 50px 50px; margin-bottom: 40px; }
        .hero h1 { font-size: 2.2em; margin-bottom: 10px; }
        .hero p { opacity: 0.9; font-size: 1.1em; }

        .categories-scroll { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 10px; scrollbar-width: thin; scrollbar-color: var(--primary-green) #f0f0f0; }
        .categories-scroll::-webkit-scrollbar { height: 5px; }
        .categories-scroll::-webkit-scrollbar-thumb { background: var(--primary-green); border-radius: 4px; }
        .cat-group { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
        .cat-group-label { font-size: 0.7em; font-weight: 800; color: var(--dark-green); text-transform: uppercase; letter-spacing: 0.06em; padding: 0 4px; }
        .cat-group-chips { display: flex; gap: 8px; }
        .cat-chip { display: flex; align-items: center; padding: 8px 14px; background: white; border: 2px solid var(--light-green); border-radius: 20px; cursor: pointer; transition: all 0.2s; font-size: 0.85em; font-weight: 600; color: var(--dark-green); white-space: nowrap; text-decoration: none; box-shadow: 0 2px 8px rgba(95,122,92,0.08); }
        .cat-chip:hover { border-color: var(--primary-green); background: var(--light-green); transform: translateY(-2px); }

        .books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 30px; padding: 20px; }
        .book-card { background: white; border: 1px solid var(--border-color); border-radius: 15px; overflow: hidden; transition: 0.3s; display: flex; flex-direction: column; cursor: pointer; }
        .book-card:hover { transform: translateY(-8px); box-shadow: 0 10px 24px rgba(95,122,92,0.18); }
        .book-card img { width: 100%; height: 300px; object-fit: cover; }
        .book-info { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
        .book-price { font-size: 1.3em; color: var(--dark-green); font-weight: 800; margin: 8px 0; }
        .btn-add-cart { background: var(--dark-green); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-top: auto; transition: background 0.2s; font-family: inherit; }
        .btn-add-cart:hover { background: #4d6649; }
        .btn-avvisami { background: white; color: #e67e22; border: 2px solid #e67e22; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-top: auto; font-size: 0.9em; transition: all 0.2s; font-family: inherit; }
        .btn-avvisami:hover { background: #e67e22; color: white; }
        .badge-esaurito { display: inline-block; background: #f8d7da; color: #842029; font-size: 0.72em; font-weight: 700; padding: 2px 8px; border-radius: 4px; margin-bottom: 6px; }
        .badge-sconto { display: inline-block; background: linear-gradient(135deg, var(--accent-pink-dark), var(--dark-green)); color: white; font-size: 0.72em; font-weight: 700; padding: 2px 8px; border-radius: 4px; margin-bottom: 4px; }
        .section-title { padding: 0 20px; color: var(--dark-green); margin-top: 40px; margin-bottom: 5px; border-left: 5px solid var(--primary-green); }

        /* MODAL AVVISAMI */
        #modal-avvisami { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; }
        #modal-avvisami.open { display: flex; }
        .modal-box { background: white; border-radius: 16px; padding: 35px; max-width: 440px; width: 90%; text-align: center; }
        .modal-box h3 { margin: 0 0 8px; color: var(--dark-green); }
        .modal-btn-confirm { background: #e67e22; color: white; border: none; padding: 14px 30px; border-radius: 8px; font-weight: 800; width: 100%; margin-bottom: 10px; cursor: pointer; font-family: inherit; font-size: 1em; }
        .modal-btn-cancel { background: none; border: none; color: #999; cursor: pointer; font-size: 0.9em; text-decoration: underline; font-family: inherit; }

        /* POPUP NOTIFICA */
        #popup-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 99999; display: flex; align-items: center; justify-content: center; }
        #popup-box { background: white; border-radius: 14px; padding: 30px 35px; text-align: center; max-width: 320px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        #popup-icon { font-size: 2.5em; margin-bottom: 10px; }
        #popup-msg { font-size: 1.05em; color: #333; margin-bottom: 20px; }
        #popup-ok { background: var(--primary-green); color: white; border: none; padding: 10px 30px; border-radius: 8px; cursor: pointer; font-weight: bold; font-family: inherit; }
    </style>
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
$(document).ready(function() {

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
            const disponibili = libri.filter(l => parseInt(l.quantita_disponibile) > 0);
            const esauriti    = libri.filter(l => parseInt(l.quantita_disponibile) === 0);
            const conSconto   = libri.filter(l => parseFloat(l.sconto_pacchetto) > 0);

            renderizza(disponibili.slice(0, 8), '#grid-nuovi');

            if (conSconto.length > 0) {
                renderizza(conSconto, '#grid-sconti');
            } else {
                $('#sezione-sconti-wrapper').hide();
            }

            if (esauriti.length > 0) {
                renderizza(esauriti, '#grid-presto');
            } else {
                $('#sezione-presto-wrapper').hide();
            }
        }
    }, 'json');
});

function renderizza(libri, selector) {
    let html = '';
    libri.forEach(lib => {
        const esaurito   = parseInt(lib.quantita_disponibile) === 0;
        const haPacchetto = parseFloat(lib.sconto_pacchetto) > 0;
        const prezzo      = parseFloat(lib.prezzo);
        const prezzoSc    = parseFloat(lib.PrezzoScontato || lib.prezzo);

        let badgeSconto = '';
        if (haPacchetto) {
            const nomePack = lib.nome_pacchetto || '';
            let labelSconto = '';
            if (nomePack.toLowerCase().includes('autore')) {
                labelSconto = 'Sconto Autore';
            } else if (nomePack.toLowerCase().includes('saga') || nomePack.toLowerCase().includes('serie') || nomePack.toLowerCase().includes('trilogia')) {
                labelSconto = 'Sconto Saga';
            } else if (nomePack.toLowerCase().includes('arrivi') || nomePack.toLowerCase().includes('nuovo')) {
                labelSconto = 'Novita in Offerta';
            } else if (nomePack) {
                labelSconto = nomePack;
            } else {
                labelSconto = 'In Offerta';
            }
            badgeSconto = `<span class="badge-sconto">${labelSconto}</span><br>`;
        }

        let prezzoHtml = `<div class="book-price">€${prezzoSc.toFixed(2)}</div>`;
        if (haPacchetto && prezzoSc < prezzo) {
            prezzoHtml = `<div class="book-price">
                €${prezzoSc.toFixed(2)}
                <small style="text-decoration:line-through; color:#bbb; font-size:0.6em; font-weight:400; margin-left:6px;">€${prezzo.toFixed(2)}</small>
            </div>`;
        }

        let btnHtml;
        if (esaurito) {
            btnHtml = `
                <span class="badge-esaurito">Esaurito</span>
                <button class="btn-avvisami" onclick="event.stopPropagation(); apriModal(${lib.id_prodotto}, '${lib.nome.replace(/'/g,"\\'")}')">
                    Avvisami quando torna
                </button>`;
        } else if (TIPO_UTENTE === 'venditore') {
            btnHtml = '';
        } else {
            btnHtml = `<button class="btn-add-cart" onclick="event.stopPropagation(); aggiungiAlCarrello(${lib.id_prodotto})">Aggiungi al carrello</button>`;
        }

        html += `
        <div class="book-card">
            <img src="${lib.URLfoto || 'img/default.jpg'}" alt="${lib.nome}"
                 onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'"
                 style="cursor:pointer;">
            <div class="book-info">
                ${badgeSconto}
                <h3 style="margin:0; font-size:1em; line-height:1.3; cursor:pointer;"
                    onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'">${lib.nome}</h3>
                <small style="color:#888; margin-top:4px;">${lib.autore || ''}</small>
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
    $.post('api/ba_avvisami.php', {
        idProdotto: $('#modal-id-prodotto').val(),
        nomeProdotto: $('#modal-nome-prodotto').val()
    }, function(resp) {
        if (resp.status === 'ok') {
            $('#modal-stato-confirm').hide();
            $('#modal-ok-msg').text(resp.msg);
            $('#modal-stato-ok').show();
        } else {
            chiudiModal();
            mostraNotifica(resp.msg, true);
        }
    }, 'json');
}

function aggiungiAlCarrello(id) {
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
        } else {
            mostraNotifica(resp.msg || 'Errore!', true);
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