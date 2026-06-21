<?php
session_start();
if(!isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Ordini | The (E-)Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- stili in style.css -->
</head>
<body>
<?php include("header.php"); ?>

<div class="container" style="max-width:900px; margin:40px auto; padding:0 20px;">
    <h2 style="color:var(--dark-green); margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;"><path d="M21 3H3a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm-1 16H4V5h16v14zM6 7h12v2H6zm0 4h12v2H6zm0 4h8v2H6z"/></svg>
        I Miei Ordini
    </h2>

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
    <div style="background:#fff; border-radius:16px; padding:32px; width:100%; max-width:440px; position:relative; box-shadow:0 8px 30px rgba(0,0,0,0.15);">
        <button onclick="$('#modalRecensione').fadeOut()" style="position:absolute;top:14px;right:16px;background:none;border:none;cursor:pointer;padding:4px;line-height:1;color:#999;" title="Chiudi">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px;height:22px;display:block;"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
        <h3 id="rev-titolo" style="margin:0 0 4px; color:var(--dark-green); font-size:1.1em; padding-right:30px;">Recensisci il libro</h3>
        <p style="color:#999; font-size:0.88em; margin:0 0 20px;">La tua opinione aiuterà altri lettori!</p>
        <form id="formRecensione">
            <input type="hidden" name="idProdotto" id="rev-idProdotto">
            <input type="hidden" name="voto" id="rev-voto-val" value="5">
            <div class="star-rating" style="margin-bottom:16px;">
                <span class="star" data-v="1">★</span><span class="star" data-v="2">★</span>
                <span class="star" data-v="3">★</span><span class="star" data-v="4">★</span>
                <span class="star" data-v="5">★</span>
            </div>
            <textarea name="commento" rows="4" placeholder="Scrivi la tua recensione..." required
                style="width:100%;border:1px solid #ddd;border-radius:10px;padding:12px;font-family:inherit;box-sizing:border-box;font-size:0.95em;resize:vertical;"></textarea>
            <button type="submit" class="btn-primary" style="width:100%;margin-top:14px;padding:14px;font-weight:700;border-radius:10px;font-size:1em;">PUBBLICA</button>
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

            const icone = [
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>',
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;"><path d="M20 6h-2.18c.07-.44.18-.87.18-1.33C18 2.54 15.96.5 13.46.5c-1.29 0-2.4.51-3.19 1.33L9 3.1 7.73 1.83C6.94 1.01 5.83.5 4.54.5 2.04.5 0 2.54 0 4.67c0 .46.11.89.18 1.33H0v2h20v-2z M0 20c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9H0v11z"/></svg>',
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;"><path d="M20 8H4V6H2v14c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6h-2v2zm0 12H4v-6h16v6zm0-8H4v-2h16v2zM4 2h2v2H4zm14 0h2v2h-2z"/></svg>',
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>'
            ];
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
                        <small style="color:#666;">Quantità: ${lib.quantita} | Prezzo: €${parseFloat(lib.prezzo_acquisto).toFixed(2)}</small>
                    </div>
                    <div>
                        ${lib.gia_recensito
                            ? `<span style="color:#f1c40f;font-size:1.1em;">${'★'.repeat(lib.voto_utente)}</span>`
                            : `<button class="btn-recensisci-colorato" onclick="apriModal(${lib.id_prodotto},'${lib.nome.replace(/'/g,"\\'")}')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:14px;height:14px;"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg> Recensisci</button>`
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