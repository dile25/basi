<?php
session_start();
if(!isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Ordini | The Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .order-card { background:white; border:1px solid var(--border-color); border-radius:12px; margin-bottom:20px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
        .order-header { background:#f8f9fa; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; cursor:pointer; }
        .order-body { padding:20px; }
        .book-item { display:flex; align-items:center; gap:15px; padding:10px 0; border-bottom:1px solid #f0f0f0; }
        .book-item:last-child { border-bottom:none; }
        .book-img { width:50px; height:75px; object-fit:cover; border-radius:4px; }
        .badge { padding:5px 12px; border-radius:20px; font-size:0.8em; font-weight:bold; }
        .stato-pagato { background:#d4edda; color:#155724; }
        .stato-lavorazione { background:#fff3cd; color:#856404; }
        .stato-spedito { background:#cce5ff; color:#004085; }
        .stato-consegnato { background:#d1ecf1; color:#0c5460; }

        /* TRACKER */
        .tracker { padding:20px; background:#fafafa; border-bottom:1px solid #eee; }
        .tracker-steps { display:flex; justify-content:space-between; position:relative; }
        .tracker-line-bg { position:absolute; top:14px; left:8%; right:8%; height:4px; background:#e0e0e0; z-index:1; }
        .tracker-line-fill { position:absolute; top:14px; left:8%; height:4px; background:var(--primary-green); z-index:2; transition:width 0.5s; }
        .tracker-step { text-align:center; z-index:3; flex:1; font-size:0.75em; color:#aaa; font-weight:600; }
        .tracker-dot { width:28px; height:28px; background:white; border:3px solid #ddd; border-radius:50%; margin:0 auto 6px; display:flex; align-items:center; justify-content:center; font-size:0.8em; }
        .tracker-step.done .tracker-dot { border-color:var(--dark-green); background:var(--dark-green); color:white; }
        .tracker-step.done { color:var(--dark-green); }
        .tracker-step.current .tracker-dot { border-color:var(--dark-green); background:white; color:var(--dark-green); }
        .tracker-step.current { color:var(--dark-green); }

        /* FILTRI */
        .filtro-btn { padding:7px 16px; border-radius:20px; border:1px solid var(--border-color); background:white; cursor:pointer; font-size:0.85em; margin-right:6px; margin-bottom:8px; transition:0.2s; }
        .filtro-btn.active { background:var(--dark-green); color:white; border-color:var(--dark-green); }

        /* MODAL */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:3000; align-items:center; justify-content:center; }
        .modal-content { background:white; padding:30px; border-radius:12px; width:90%; max-width:450px; position:relative; }
        .star-rating { font-size:28px; cursor:pointer; color:#ddd; margin:12px 0; }
    </style>
</head>
<body>
<?php include("header.php"); ?>

<div class="container" style="max-width:900px; margin:40px auto; padding:0 20px;">
    <h2 style="color:var(--dark-green); margin-bottom:20px;">📦 I Miei Ordini</h2>

    <!-- Filtri -->
    <div style="margin-bottom:20px;">
        <button class="filtro-btn active" onclick="filtra('', this)">Tutti</button>
        <button class="filtro-btn" onclick="filtra('Pagato', this)">Pagati</button>
        <button class="filtro-btn" onclick="filtra('In lavorazione', this)">In lavorazione</button>
        <button class="filtro-btn" onclick="filtra('Spedito', this)">Spediti</button>
        <button class="filtro-btn" onclick="filtra('Consegnato', this)">Consegnati</button>
    </div>

    <div id="ordini-lista">
        <p style="text-align:center; padding:50px;">Caricamento...</p>
    </div>
</div>

<!-- MODAL RECENSIONE -->
<div id="modalRecensione" class="modal-overlay">
    <div class="modal-content">
        <span onclick="$('#modalRecensione').fadeOut()" style="position:absolute;top:15px;right:20px;font-size:22px;cursor:pointer;">&times;</span>
        <h3 id="rev-titolo" style="margin-bottom:5px;">Recensisci il libro</h3>
        <p style="color:#666;font-size:0.9em;">La tua opinione aiuterà altri lettori!</p>
        <form id="formRecensione">
            <input type="hidden" name="idProdotto" id="rev-idProdotto">
            <input type="hidden" name="voto" id="rev-voto-val" value="5">
            <div class="star-rating">
                <span class="star" data-v="1">★</span><span class="star" data-v="2">★</span>
                <span class="star" data-v="3">★</span><span class="star" data-v="4">★</span>
                <span class="star" data-v="5">★</span>
            </div>
            <textarea name="commento" rows="4" placeholder="Scrivi la tua recensione..." required
                style="width:100%;border:1px solid #ddd;border-radius:8px;padding:10px;font-family:inherit;box-sizing:border-box;"></textarea>
            <button type="submit" class="btn-primary" style="width:100%;margin-top:15px;padding:14px;font-weight:bold;">PUBBLICA</button>
        </form>
    </div>
</div>

<script>
let filtroCorrente = '';

$(document).ready(function() {
    caricaOrdini('');

    $(document).on("click", ".star", function() {
        const v = $(this).data("v");
        $("#rev-voto-val").val(v);
        aggiornaStelle(v);
    });

    $("#formRecensione").on("submit", function(e) {
        e.preventDefault();
        $.post('api/ba_scrivi_recensione.php', $(this).serialize(), function(resp) {
            if(resp.status === 'ok') {
                alert("Recensione inviata!");
                $("#modalRecensione").fadeOut();
                caricaOrdini(filtroCorrente);
            } else { alert(resp.msg); }
        }, "json");
    });
});

function filtra(stato, btn) {
    $('.filtro-btn').removeClass('active');
    $(btn).addClass('active');
    filtroCorrente = stato;
    caricaOrdini(stato);
}

function caricaOrdini(stato) {
    $.get('api/ba_miei_ordini.php', { stato: stato }, function(resp) {
        if(resp.status !== 'ok') return;
        if(resp.ordini.length === 0) {
            $("#ordini-lista").html("<div style='text-align:center;padding:60px;'><h3>Nessun ordine trovato.</h3><a href='index.php' style='color:var(--dark-green);'>Esplora i libri</a></div>");
            return;
        }

        let h = '';
        resp.ordini.forEach(ord => {
            const stato = ord.stato;
            const statiOrdine = ['Pagato', 'In lavorazione', 'Spedito', 'Consegnato'];
            const idxCorrente = statiOrdine.indexOf(stato);
            const fillPerc = ['0%','33%','66%','100%'][idxCorrente] || '0%';

            const badgeClass = {
                'Pagato':'stato-pagato','In lavorazione':'stato-lavorazione',
                'Spedito':'stato-spedito','Consegnato':'stato-consegnato'
            }[stato] || 'stato-pagato';

            const icone = ['✓','📦','🚚','🏠'];
            const label = ['Ricevuto','In preparazione','In spedizione','Consegnato'];
            let stepsHtml = '';
            statiOrdine.forEach((s, i) => {
                let cls = '';
                if(i < idxCorrente) cls = 'done';
                else if(i === idxCorrente) cls = 'done current';
                stepsHtml += `<div class="tracker-step ${cls}"><div class="tracker-dot">${icone[i]}</div>${label[i]}</div>`;
            });

            h += `
            <div class="order-card">
                <div class="order-header" onclick="$(this).next('.tracker').toggle(200); $(this).nextAll('.order-body').first().toggle(200);">
                    <div>
                        <strong>ORDINE #${ord.id_ordine}</strong>
                        <span style="margin-left:12px;color:#666;font-size:0.9em;">${ord.data}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span class="badge ${badgeClass}">${stato}</span>
                        <strong style="color:var(--dark-green);">€${parseFloat(ord.totale).toFixed(2)}</strong>
                        <span style="color:#aaa;">▼</span>
                    </div>
                </div>

                <div class="tracker">
                    <div class="tracker-steps">
                        <div class="tracker-line-bg"></div>
                        <div class="tracker-line-fill" style="width:${fillPerc};"></div>
                        ${stepsHtml}
                    </div>
                </div>

                <div class="order-body">`;

            ord.libri.forEach(lib => {
                h += `
                <div class="book-item">
                    <img src="${lib.foto}" class="book-img" onclick="location.href='dettaglio_prodotto.php?id=${lib.id_prodotto}'" style="cursor:pointer;">
                    <div style="flex-grow:1;">
                        <strong>${lib.nome}</strong><br>
                        <small style="color:#666;">Quantità: ${lib.quantita} | Totale ordine: €${parseFloat(lib.prezzo_acquisto).toFixed(2)}</small>
                    </div>
                    <div>
                        ${lib.gia_recensito
                            ? `<span style="color:#f1c40f;font-size:1.1em;">${'★'.repeat(lib.voto_utente)}</span>`
                            : `<button class="btn-recensisci" style="padding:7px 12px;font-size:0.85em;" onclick="apriModal(${lib.id_prodotto},'${lib.nome.replace(/'/g,"\\'")}')">⭐ Recensisci</button>`
                        }
                    </div>
                </div>`;
            });

            h += `</div></div>`;
        });
        $("#ordini-lista").html(h);
    }, "json");
}

function apriModal(id, titolo) {
    $("#rev-idProdotto").val(id);
    $("#rev-titolo").text(titolo);
    $("#formRecensione")[0].reset();
    aggiornaStelle(5);
    $("#modalRecensione").css('display','flex').hide().fadeIn();
}

function aggiornaStelle(v) {
    $(".star").each(function() {
        $(this).css("color", $(this).data("v") <= v ? "#f1c40f" : "#ddd");
    });
}
</script>
</body>
</html>