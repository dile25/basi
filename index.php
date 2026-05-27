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
        /* ============================================================
           HERO
        ============================================================ */
        .hero {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 0 0 50px 50px;
            margin-bottom: 40px;
        }

        /* ============================================================
           BANNER CATEGORIE STILE AMAZON
        ============================================================ */
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
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-green) #f0f0f0;
        }

        .categories-scroll::-webkit-scrollbar { height: 5px; }
        .categories-scroll::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 4px; }
        .categories-scroll::-webkit-scrollbar-thumb { background: var(--primary-green); border-radius: 4px; }

        .cat-chip {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 130px;
            padding: 18px 14px;
            background: white;
            border: 2px solid #e8f5e9;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .cat-chip:hover, .cat-chip.active {
            border-color: var(--primary-green);
            background: #f0faf2;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(39, 174, 96, 0.15);
        }

        .cat-chip .cat-icon { font-size: 2em; margin-bottom: 6px; }
        .cat-chip .cat-name {
            font-size: 0.8em;
            font-weight: 700;
            color: var(--dark-green);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 110px;
        }
        .cat-chip .cat-count {
            font-size: 0.7em;
            color: #888;
            margin-top: 3px;
        }

        /* ============================================================
           GRIGLIE LIBRI
        ============================================================ */
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

        /* ============================================================
           BADGE ESAURITO + AVVISAMI
        ============================================================ */
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

        /* Modal Avvisami */
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

        /* Pacchetto badge */
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
    </style>
</head>
<body>
    <div id="toast-container"></div>
    <?php include("header.php"); ?>

    <section class="hero">
        <h1>Benvenuto su BookArchive</h1>
        <p>Scopri il nostro catalogo completo.</p>
    </section>

    <!-- ============================================================
         BANNER CATEGORIE STILE AMAZON
    ============================================================ -->
    <div class="categories-banner">
        <h2>📚 Sfoglia per categoria</h2>
        <div class="categories-scroll" id="categorie-banner">
            <!-- popolato via JS -->
        </div>
    </div>

    <div id="main-container" style="max-width: 1200px; margin: 0 auto;">
        <h2 class="section-title">✨ Nuovi Arrivi</h2>
        <div id="grid-nuovi" class="books-grid"></div>

        <h2 class="section-title">⏳ Presto di nuovo disponibile</h2>
        <div id="grid-presto" class="books-grid"></div>

        <div id="sezioni-categorie"></div>
    </div>

    <!-- ============================================================
         MODAL "AVVISAMI" — solo feedback visivo, nessun DB
    ============================================================ -->
    <div id="modal-avvisami">
        <div class="modal-box">
            <!-- Stato: conferma -->
            <div id="modal-stato-confirm">
                <div style="font-size:2.5em; margin-bottom:10px;">🔔</div>
                <h3>Avvisami quando torna disponibile</h3>
                <p id="modal-titolo-libro" style="color:#555; font-size:0.95em;">Riceverai una notifica non appena il libro tornerà disponibile.</p>
                <input type="hidden" id="modal-id-prodotto" value="">
                <input type="hidden" id="modal-nome-prodotto" value="">
                <button class="modal-btn-confirm" onclick="confermaAvvisami()">🔔 Sì, avvisami!</button>
                <br>
                <button class="modal-btn-cancel" onclick="chiudiModal()">Annulla</button>
            </div>
            <!-- Stato: successo -->
            <div id="modal-stato-ok" style="display:none;">
                <div style="font-size:3em; margin-bottom:12px;">✅</div>
                <h3 style="color:var(--dark-green);">Sei nella lista!</h3>
                <p id="modal-ok-msg" style="color:#555; font-size:0.95em;"></p>
                <button class="modal-btn-confirm" style="background:var(--primary-green, #27ae60);" onclick="chiudiModal()">Chiudi</button>
            </div>
        </div>
    </div>

    <script>
    // Icone categoria (mappa nome → emoji)
    const iconeCategorie = {
        'romanzo': '📖', 'fantasy': '🧙', 'fantascienza': '🚀', 'thriller': '🔪',
        'horror': '👻', 'storico': '🏛️', 'biografia': '👤', 'saggistica': '🎓',
        'bambini': '🧒', 'ragazzi': '📚', 'manga': '⛩️', 'fumetti': '💬',
        'poesia': '🌸', 'classici': '🏺', 'cucina': '🍝', 'sport': '⚽',
        'arte': '🎨', 'musica': '🎵', 'scienza': '🔬', 'filosofia': '🤔',
        'default': '📕'
    };

    function getIconaCategoria(nome) {
        const chiave = nome.toLowerCase();
        for (const [k, v] of Object.entries(iconeCategorie)) {
            if (chiave.includes(k)) return v;
        }
        return iconeCategorie['default'];
    }

    $(document).ready(function() {
        $.get('api/ba_ricerca.php', function(resp) {
            const libri = resp.prodotti || [];

            // === BANNER CATEGORIE ===
            const conteggioCat = {};
            libri.forEach(l => {
                const cat = l.nome_categoria || 'Altro';
                conteggioCat[cat] = (conteggioCat[cat] || 0) + 1;
            });

            // Chip "Tutti"
            $('#categorie-banner').append(`
                <a href="#main-container" class="cat-chip active" id="chip-tutti">
                    <div class="cat-icon">🏠</div>
                    <div class="cat-name">Tutti</div>
                    <div class="cat-count">${libri.length} libri</div>
                </a>
            `);

            Object.keys(conteggioCat).sort().forEach(cat => {
                const anchor = `#sezione-${cat.replace(/\s+/g, '-')}`;
                $('#categorie-banner').append(`
                    <a href="${anchor}" class="cat-chip" onclick="highlightChip(this)">
                        <div class="cat-icon">${getIconaCategoria(cat)}</div>
                        <div class="cat-name">${cat}</div>
                        <div class="cat-count">${conteggioCat[cat]} libri</div>
                    </a>
                `);
            });

            // === NUOVI ARRIVI (ultimi 4) ===
            renderizza(libri.slice(-4).reverse(), "#grid-nuovi");

            // === ESAURITI ===
            const esauriti = libri.filter(l => parseInt(l.quantita_disponibile) === 0);
            if (esauriti.length > 0) {
                renderizza(esauriti, "#grid-presto");
            } else {
                $("#grid-presto").html("<p style='padding:20px; color:#999;'>Nessun libro esaurito al momento.</p>");
            }

            // === SEZIONI CATEGORIE DINAMICHE ===
            const categorie = [...new Set(libri.map(l => l.nome_categoria || 'Senza Categoria'))].sort();
            categorie.forEach(cat => {
                const libriCat = libri.filter(l => (l.nome_categoria || 'Senza Categoria') === cat);
                const safeId = `sezione-${cat.replace(/\s+/g, '-')}`;
                $('#sezioni-categorie').append(`
                    <h2 class="section-title" id="${safeId}">${getIconaCategoria(cat)} ${cat}</h2>
                    <div id="cat-${cat.replace(/\s+/g, '-')}" class="books-grid"></div>
                `);
                renderizza(libriCat, `#cat-${cat.replace(/\s+/g, '-')}`);
            });

        }, 'json');
    });

    function highlightChip(el) {
        $('.cat-chip').removeClass('active');
        $(el).addClass('active');
    }

    function renderizza(libri, selector) {
        let html = "";
        libri.forEach(lib => {
            const esaurito = parseInt(lib.quantita_disponibile) === 0;
            const haPacchetto = lib.sconto_pacchetto > 0;

            // Badge pacchetto sconto
            const badgePacchetto = haPacchetto
                ? `<span class="badge-pacchetto">🏷️ -${lib.sconto_pacchetto}%</span><br>`
                : '';

            // Prezzo mostrato
            let prezzoHtml = `<div class="book-price">€${parseFloat(lib.PrezzoScontato || lib.prezzo).toFixed(2)}</div>`;
            if (haPacchetto && lib.PrezzoScontato < lib.prezzo) {
                prezzoHtml = `
                    <div class="book-price">
                        €${parseFloat(lib.PrezzoScontato).toFixed(2)}
                        <small style="text-decoration:line-through; color:#999; font-size:0.6em; font-weight:400; margin-left:6px;">€${parseFloat(lib.prezzo).toFixed(2)}</small>
                    </div>`;
            }

            // Pulsante azione
            let btnHtml;
            if (esaurito) {
                btnHtml = `
                    <span class="badge-esaurito">📦 Esaurito</span>
                    <button class="btn-avvisami" onclick="event.stopPropagation(); apriModal(${lib.id_prodotto}, '${lib.nome.replace(/'/g, "\\'")}')">
                        🔔 Avvisami quando torna
                    </button>`;
            } else {
                btnHtml = `<button class="btn-add-cart" onclick="event.stopPropagation(); aggiungiAlCarrello(${lib.id_prodotto})">🛒 Aggiungi</button>`;
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

    // ============================================================
    // MODAL AVVISAMI
    // ============================================================
    function apriModal(idProdotto, nomeProdotto) {
        <?php if (!isset($_SESSION['IdUtente'])): ?>
            if (confirm('Devi essere loggato per usare "Avvisami". Vuoi accedere?')) {
                window.location.href = 'login.php';
            }
            return;
        <?php endif; ?>
        // Reset al stato iniziale
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
        const idProdotto  = $('#modal-id-prodotto').val();
        const nomeProdotto = $('#modal-nome-prodotto').val();

        $.post('api/ba_avvisami.php', { idProdotto: idProdotto, nomeProdotto: nomeProdotto }, function(resp) {
            if (resp.status === 'ok') {
                // Mostra stato successo dentro il modal
                $('#modal-stato-confirm').hide();
                $('#modal-ok-msg').text(resp.msg);
                $('#modal-stato-ok').show();
            } else {
                chiudiModal();
                mostraNotifica(resp.msg, true);
            }
        }, 'json');
    }

    // ============================================================
    // CARRELLO
    // ============================================================
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

    // ============================================================
    // NOTIFICA POPUP
    // ============================================================
    function mostraNotifica(messaggio, isError = false) {
        const vecchio = document.getElementById('popup-overlay');
        if (vecchio) vecchio.remove();

        const overlay = document.createElement('div');
        overlay.id = 'popup-overlay';
        overlay.innerHTML = `
            <div id="popup-box">
                <div id="popup-icon">${isError ? '❌' : '✅'}</div>
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