<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo Venditore | The (E-)Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .venditore-hero {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 25px;
        }
        .venditore-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
            flex-shrink: 0;
        }
        .venditore-info h1 { margin: 0 0 5px; font-size: 1.6em; }
        .venditore-info p { margin: 0; opacity: 0.85; font-size: 0.95em; }
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
        }
        .book-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 15px;
            overflow: hidden;
            transition: 0.3s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }
        .book-card:hover { transform: translateY(-6px); box-shadow: 0 8px 20px rgba(39,174,96,0.15); }
        .book-card img { width: 100%; height: 250px; object-fit: cover; }
        .book-card-info { padding: 14px; flex-grow: 1; display: flex; flex-direction: column; gap: 6px; }
        .book-card-title { font-weight: 700; font-size: 0.95em; color: var(--dark-green); margin: 0; }
        .book-card-author { font-size: 0.82em; color: var(--text-sec); }
        .book-card-price { font-size: 1.15em; font-weight: 800; color: var(--dark-green); }
        .badge-esaurito { display: inline-block; background: #fdecea; color: #c0392b; font-size: 0.75em; padding: 3px 8px; border-radius: 10px; font-weight: 600; }
    </style>
</head>
<body>
<?php include('header.php'); ?>

<main class="container" style="max-width: 1100px; margin: 30px auto; padding: 0 20px;">

    <div id="loading" style="text-align:center; padding:80px; color:var(--text-sec);">
        Caricamento profilo...
    </div>

    <div id="contenuto" style="display:none;">
        <div class="venditore-hero" id="venditore-hero">
            <div class="venditore-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:40px;">
                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                </svg>
            </div>
            <div class="venditore-info">
                <h1 id="v-nome"></h1>
                <p id="v-membro"></p>
            </div>
        </div>

        <h2 style="color:var(--dark-green); margin-bottom:20px;" id="titolo-libri"></h2>
        <div id="libri-grid" class="books-grid"></div>
    </div>

</main>

<script>
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const u = urlParams.get('u');
    if (!u) { $('#loading').text('Venditore non specificato.'); return; }

    $.get('api/ba_profilo_venditore.php', { u: u }, function(resp) {
        if (resp.status !== 'ok') { $('#loading').text(resp.msg); return; }

        const v = resp.venditore;
        const nome = v.nome_visualizzato || v.ragione_sociale || (v.nome + ' ' + v.cognome);
        $('#v-nome').text(nome);
        const dataReg = v.data_registrazione
            ? new Date(v.data_registrazione).toLocaleDateString('it-IT', { day:'2-digit', month:'long', year:'numeric' })
            : '';
        $('#v-membro').text(dataReg ? 'Su The (E-)Shop Around the Corner dal ' + dataReg : 'Venditore');
        $('#titolo-libri').text('Libri in vendita (' + resp.libri.length + ')');

        if (resp.libri.length === 0) {
            $('#libri-grid').html('<p style="color:var(--text-sec);">Nessun libro disponibile al momento.</p>');
        } else {
            let html = '';
            resp.libri.forEach(lib => {
                const foto = lib.foto || 'img/default.jpg';
                const disponibile = lib.quantita_disponibile > 0;
                html += `
                <div class="book-card" onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'">
                    <img src="${foto}" alt="${lib.nome}">
                    <div class="book-card-info">
                        <p class="book-card-title">${lib.nome}</p>
                        ${lib.autore ? `<p class="book-card-author">${lib.autore}</p>` : ''}
                        <p class="book-card-price">€${parseFloat(lib.prezzo).toFixed(2)}</p>
                        ${!disponibile ? '<span class="badge-esaurito">Non disponibile</span>' : ''}
                    </div>
                </div>`;
            });
            $('#libri-grid').html(html);
        }

        $('#loading').hide();
        $('#contenuto').fadeIn();
    }, 'json');
});
</script>
</body>
</html>