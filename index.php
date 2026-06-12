<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>BookArchive | La tua libreria online</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #grid-filtro:empty, #grid-filtro > div {
    grid-column: 1 / -1;
}
        .hero {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 0 0 50px 50px;
            margin-bottom: 40px;
        }

        .categories-banner {
            margin: 0 auto 40px;
            max-width: 1200px;
            padding: 0 20px;
        }

        .categories-banner h2 {
            font-size: 1.3em;
            color: var(--dark-green);
            margin-bottom: 16px;
            font-weight: 800;
        }

        .categories-scroll {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-green) #f0f0f0;
        }

        .categories-scroll::-webkit-scrollbar { height: 5px; }
        .categories-scroll::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 4px; }
        .categories-scroll::-webkit-scrollbar-thumb { background: var(--primary-green); border-radius: 4px; }

        .cat-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-shrink: 0;
        }

        .cat-group-label {
            font-size: 0.7em;
            font-weight: 800;
            color: var(--primary-green);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 0 4px;
        }

        .cat-group-chips {
            display: flex;
            gap: 8px;
        }

        .cat-chip {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            background: white;
            border: 2px solid #e8f5e9;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            flex-shrink: 0;
            font-size: 0.85em;
            font-weight: 600;
            color: var(--dark-green);
            white-space: nowrap;
        }

        .cat-chip:hover {
            border-color: var(--primary-green);
            background: #f0faf2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.15);
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .book-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 15px;
            overflow: hidden;
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.15);
        }

        .book-card img { width: 100%; height: 300px; object-fit: cover; }
        .book-info { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
        .book-price { font-size: 1.4em; color: var(--primary-green); font-weight: 800; margin: 10px 0; }

        .btn-add-cart {
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: auto;
        }

        .badge-esaurito {
            display: inline-block;
            background: #f8d7da;
            color: #842029;
            font-size: 0.7em;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .btn-avvisami {
            background: #fff;
            color: #e67e22;
            border: 2px solid #e67e22;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: auto;
            font-size: 0.9em;
            transition: all 0.2s;
        }

        .btn-avvisami:hover { background: #e67e22; color: white; }

        #modal-avvisami {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        #modal-avvisami.open { display: flex; }

        .modal-box {
            background: white;
            border-radius: 16px;
            padding: 35px;
            max-width: 440px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            text-align: center;
        }

        .modal-box h3 { margin: 0 0 8px; color: var(--dark-green); font-size: 1.3em; }
        .modal-box p { color: #555; font-size: 0.9em; margin-bottom: 20px; }

        .modal-btn-confirm {
            background: #e67e22;
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 1em;
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
        }

        .modal-btn-cancel {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: underline;
        }

        .badge-pacchetto {
            display: inline-block;
            background: linear-gradient(135deg, #f39c12, #e74c3c);
            color: white;
            font-size: 0.7em;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
        }

        .section-title {
            padding: 0 20px;
            color: var(--dark-green);
            margin-top: 40px;
            border-left: 5px solid var(--primary-green);
        }

        #main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div id="toast-container"></div>
    <?php include("header.php"); ?>

    <section class="hero">
        <h1>BookArchive</h1>
        <p style="max-width: 600px; margin: 12px auto 0; font-size: 1.1em; opacity: 0.92; line-height: 1.6;">
            La libreria online che riunisce venditori indipendenti e lettori appassionati.<br>
            Libri, riviste, periodici e saghe complete — tutto in un unico posto.
        </p>
    </section>

    <!-- BANNER CATEGORIE -->
    <div class="categories-banner" id="categories-banner-wrapper">
        <h2>Sfoglia per categoria</h2>
        <div class="categories-scroll" id="categorie-banner"></div>
    </div>

    <div id="main-container">
        <h2 class="section-title" id="titolo-nuovi">Nuovi Arrivi</h2>
        <div id="grid-nuovi" class="books-grid"></div>

        <h2 class="section-title" id="titolo-presto">Presto di nuovo disponibile</h2>
        <div id="grid-presto" class="books-grid"></div>

        <h2 class="section-title" id="titolo-sconti">Pacchetti &amp; Sconti</h2>
        <p id="desc-sconti" style="padding: 0 20px; color: var(--text-sec); margin-bottom: 0;">Acquista insieme e risparmia.</p>
        <div id="grid-sconti" class="books-grid"></div>
    </div>

    <!-- MODAL AVVISAMI -->
    <div id="modal-avvisami">
        <div class="modal-box">
            <div id="modal-stato-confirm">
                <div style="font-size:2.5em; margin-bottom:10px;">&#128276;</div>
                <h3>Avvisami quando torna disponibile</h3>
                <p id="modal-titolo-libro" style="color:#555; font-size:0.95em;">Riceverai una notifica non appena il libro tornerà disponibile.</p>
                <input type="hidden" id="modal-id-prodotto" value="">
                <input type="hidden" id="modal-nome-prodotto" value="">
                <button class="modal-btn-confirm" onclick="confermaAvvisami()">Sì, avvisami!</button>
                <br>
                <button class="modal-btn-cancel" onclick="chiudiModal()">Annulla</button>
            </div>
            <div id="modal-stato-ok" style="display:none;">
                <div style="font-size:3em; margin-bottom:12px;">&#10003;</div>
                <h3 style="color:var(--dark-green);">Sei nella lista!</h3>
                <p id="modal-ok-msg" style="color:#555; font-size:0.95em;"></p>
                <button class="modal-btn-confirm" style="background:var(--primary-green);" onclick="chiudiModal()">Chiudi</button>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {

        // === BANNER CATEGORIE ===
        $.get('api/ba_lista_categorie.php', function(resp) {
            const cats = resp.categorie || [];
            const padri = cats.filter(c => !c.nome_categoria_padre);
            const figlie = cats.filter(c => c.nome_categoria_padre);

            padri.forEach(padre => {
                const sottocats = figlie.filter(f => f.nome_categoria_padre === padre.nome_categoria);
                if (sottocats.length === 0) return;

                let chipsHtml = sottocats.map(f =>
                    `<a href="index.php?cat=${encodeURIComponent(f.nome_categoria)}" class="cat-chip">${f.nome_categoria}</a>`
                ).join('');

                $('#categorie-banner').append(`
                    <div class="cat-group">
                        <div class="cat-group-label">${padre.nome_categoria}</div>
                        <div class="cat-group-chips">${chipsHtml}</div>
                    </div>
                `);
            });
        }, 'json');

        // === PRODOTTI ===
        const urlParams = new URLSearchParams(window.location.search);
        const catFiltro = urlParams.get('cat') || '';
        const qFiltro   = urlParams.get('q')   || '';

        $.get('api/ba_ricerca.php', { cat: catFiltro, q: qFiltro }, function(resp) {
            const libri = resp.prodotti || [];

            if (catFiltro || qFiltro) {
                // Modalità filtro
                $('#categories-banner-wrapper').hide();
                const titolo = catFiltro
                    ? `Risultati per: <em>${catFiltro}</em>`
                    : `Risultati per: <em>"${qFiltro}"</em>`;

                $('#titolo-nuovi').hide();
                $('#grid-nuovi').hide();
                $('#titolo-presto').hide();
                $('#grid-presto').hide();
                $('#titolo-sconti').hide();
                $('#desc-sconti').hide();
                $('#grid-sconti').hide();

                $('#main-container').prepend(`
                    <div style="padding: 20px 20px 0;">
                        <a href="index.php" style="color: var(--primary-green); font-size:0.9em; text-decoration:none;">&#8592; Torna alla home</a>
                    </div>
                    <h2 class="section-title" style="margin-top:20px;">${titolo}</h2>
                    <div id="grid-filtro" class="books-grid"></div>
                `);
                if (libri.length === 0) {
    $('#grid-filtro').html(`
        <div style="padding: 60px 20px; text-align: center; color: var(--text-sec); width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 3em; margin-bottom: 16px;">📚</div>
            <h3 style="color: var(--dark-green); margin-bottom: 8px;">Nessun titolo disponibile al momento</h3>
            <p style="max-width: 400px; margin: 0 auto 20px;">Stiamo lavorando per aggiungere nuovi titoli in questa categoria. Torna presto!</p>
            <a href="index.php" style="color: var(--primary-green); font-weight: 600; text-decoration: none;">← Torna alla home</a>
        </div>
    `);
} else {
    renderizza(libri, '#grid-filtro');
}

            } else {
                // Modalità home normale
                renderizza(libri.slice(0, 4), '#grid-nuovi');

                const esauriti = libri.filter(l => parseInt(l.quantita_disponibile) === 0);
                if (esauriti.length > 0) {
                    renderizza(esauriti, '#grid-presto');
                } else {
                    $('#grid-presto').html("<p style='padding:20px; color:#999;'>Nessun libro esaurito al momento.</p>");
                }

                const conSconto = libri.filter(l => l.sconto_pacchetto > 0);
                if (conSconto.length > 0) {
                    renderizza(conSconto, '#grid-sconti');
                } else {
                    $('#grid-sconti').html("<p style='padding:20px; color:#999;'>Nessun pacchetto disponibile al momento.</p>");
                }
            }

        }, 'json');
    });

    function renderizza(libri, selector) {
        let html = "";
        libri.forEach(lib => {
            const esaurito = parseInt(lib.quantita_disponibile) === 0;
            const haPacchetto = lib.sconto_pacchetto > 0;

            const badgePacchetto = haPacchetto
                ? `<span class="badge-pacchetto">-${lib.sconto_pacchetto}%</span><br>`
                : '';

            let prezzoHtml = `<div class="book-price">€${parseFloat(lib.PrezzoScontato || lib.prezzo).toFixed(2)}</div>`;
            if (haPacchetto && lib.PrezzoScontato < lib.prezzo) {
                prezzoHtml = `
                    <div class="book-price">
                        €${parseFloat(lib.PrezzoScontato).toFixed(2)}
                        <small style="text-decoration:line-through; color:#999; font-size:0.6em; font-weight:400; margin-left:6px;">€${parseFloat(lib.prezzo).toFixed(2)}</small>
                    </div>`;
            }

            let btnHtml;
            if (esaurito) {
                btnHtml = `
                    <span class="badge-esaurito">Esaurito</span>
                    <button class="btn-avvisami" onclick="event.stopPropagation(); apriModal(${lib.id_prodotto}, '${lib.nome.replace(/'/g, "\\'")}')">
                        Avvisami quando torna
                    </button>`;
            } else {
                btnHtml = `<button class="btn-add-cart" onclick="event.stopPropagation(); aggiungiAlCarrello(${lib.id_prodotto})">Aggiungi al carrello</button>`;
            }

            html += `
            <div class="book-card" onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'">
                <img src="${lib.URLfoto || 'img/default.jpg'}" alt="${lib.nome}">
                <div class="book-info">
                    ${badgePacchetto}
                    <h3 style="margin:0; font-size:1.1em;">${lib.nome}</h3>
                    <small style="color:#888; margin-top:4px;">${lib.autore || ''}</small>
                    ${prezzoHtml}
                    ${btnHtml}
                </div>
            </div>`;
        });
        $(selector).html(html);
    }

    // MODAL AVVISAMI
    function apriModal(idProdotto, nomeProdotto) {
        <?php if (!isset($_SESSION['IdUtente'])): ?>
            if (confirm('Devi essere loggato per usare "Avvisami". Vuoi accedere?')) {
                window.location.href = 'login.php';
            }
            return;
        <?php endif; ?>
        $('#modal-stato-confirm').show();
        $('#modal-stato-ok').hide();
        $('#modal-id-prodotto').val(idProdotto);
        $('#modal-nome-prodotto').val(nomeProdotto);
        $('#modal-titolo-libro').text(`Vuoi essere avvisato quando "${nomeProdotto}" tornerà disponibile?`);
        $('#modal-avvisami').addClass('open');
    }

    function chiudiModal() {
        $('#modal-avvisami').removeClass('open');
    }

    $('#modal-avvisami').on('click', function(e) {
        if ($(e.target).is('#modal-avvisami')) chiudiModal();
    });

    function confermaAvvisami() {
        const idProdotto   = $('#modal-id-prodotto').val();
        const nomeProdotto = $('#modal-nome-prodotto').val();
        $.post('api/ba_avvisami.php', { idProdotto: idProdotto, nomeProdotto: nomeProdotto }, function(resp) {
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

    // CARRELLO
    function aggiungiAlCarrello(id) {
        $.post('api/ba_aggiungi_carrello.php', { idProdotto: id }, function(resp) {
            if (resp.status === 'ok') {
                mostraNotifica('Aggiunto al carrello!');
                if (typeof updateCartBadge === 'function') updateCartBadge();
            } else {
                mostraNotifica(resp.msg || 'Errore!', true);
            }
        }, 'json');
    }

    // NOTIFICA POPUP
    function mostraNotifica(messaggio, isError = false) {
        const vecchio = document.getElementById('popup-overlay');
        if (vecchio) vecchio.remove();

        const overlay = document.createElement('div');
        overlay.id = 'popup-overlay';
        overlay.innerHTML = `
            <div id="popup-box">
                <div id="popup-icon">${isError ? '&#10007;' : '&#10003;'}</div>
                <div id="popup-msg">${messaggio}</div>
                <button id="popup-ok">OK</button>
            </div>
        `;
        document.body.appendChild(overlay);
        document.getElementById('popup-ok').onclick = () => overlay.remove();
        overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
    }
    </script>
</body>
</html>