<?php
session_start();
if(!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Venditore | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .manage-book-card { display:flex; align-items:center; gap:12px; padding:12px; border:1px solid var(--border-color); border-radius:10px; margin-bottom:12px; background:white; }
        .manage-book-img { width:55px; height:75px; object-fit:cover; border-radius:6px; }
        .btn-elimina { padding:5px 10px; font-size:0.8em; background:white; color:#e74c3c; border:1px solid #e74c3c; border-radius:6px; cursor:pointer; }
        .btn-elimina:hover { background:#e74c3c; color:white; }
        .btn-modifica-libro { padding:5px 10px; font-size:0.8em; background:white; color:var(--primary-green); border:1px solid var(--primary-green); border-radius:6px; cursor:pointer; }
        .btn-modifica-libro:hover { background:var(--primary-green); color:white; }
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
        .modal-box { background:white; padding:30px; border-radius:15px; width:90%; overflow-y:auto; max-height:90vh; }
        .ordine-card { background:white; border:1px solid var(--border-color); border-radius:12px; margin-bottom:15px; overflow:hidden; }
        .ordine-header { background:#f8f9fa; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; cursor:pointer; }
        .ordine-body { padding:15px 18px; display:none; }
        .ordine-libro { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid #f0f0f0; }
        .ordine-libro:last-child { border-bottom:none; }
        .badge-stato { padding:4px 12px; border-radius:20px; font-size:0.78em; font-weight:bold; }
        .stato-pagato { background:#d4edda; color:#155724; }
        .stato-lavorazione { background:#fff3cd; color:#856404; }
        .stato-spedito { background:#cce5ff; color:#004085; }
        .stato-consegnato { background:#d1ecf1; color:#0c5460; }
        .filtro-btn { padding:7px 16px; border-radius:20px; border:1px solid var(--border-color); background:white; cursor:pointer; font-size:0.85em; transition:0.2s; }
        .filtro-btn.active { background:var(--primary-green); color:white; border-color:var(--primary-green); }
        .stat-box-click { cursor:pointer; }
        .stat-box-click:hover { transform:translateY(-5px); box-shadow:0 6px 15px rgba(0,0,0,0.1); }
        .form-control { margin-bottom:12px; width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); font-family:inherit; background:#fff; box-sizing:border-box; }
    </style>
</head>
<body>
<?php include("header.php"); ?>

<div class="container" style="max-width:1200px; margin:0 auto; padding:20px;">
    <header style="display:flex; justify-content:space-between; align-items:center; margin:20px 0 30px;">
        <h2 style="color:var(--dark-green); margin:0;">👨‍💼 Pannello Venditore</h2>
        <button class="btn-primary" onclick="apriModalNuovoLibro()">+ Aggiungi Libro</button>
    </header>

    <!-- STATISTICHE -->
    <div class="dash-grid" style="margin-bottom:30px;">
        <div class="stat-box">
            <h3>Libri Online</h3>
            <div class="stat-number" id="count-libri">0</div>
        </div>
        <div class="stat-box stat-box-click" onclick="scrollToOrdini()">
            <h3>Ordini Ricevuti</h3>
            <div class="stat-number" id="count-ordini">0</div>
            <small style="color:var(--text-sec);">Clicca per vedere lo storico</small>
        </div>
        <div class="stat-box">
            <h3>Guadagno Totale</h3>
            <div class="stat-number" id="total-guadagno">€ 0.00</div>
            <small style="color:var(--text-sec);">Da ordini spediti/consegnati</small>
        </div>
    </div>

    <!-- GRIGLIA LIBRI + ORDINI -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
        <section>
            <h3 style="color:var(--dark-green); margin-bottom:15px;">📚 I tuoi Libri</h3>
            <div id="lista-libri-venditore"><p style="color:var(--text-sec);">Caricamento...</p></div>
        </section>

        <section id="sezione-ordini">
            <h3 style="color:var(--dark-green); margin-bottom:15px;">📦 Ordini Ricevuti</h3>

            <!-- Filtri stato -->
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:15px;">
                <button class="filtro-btn active" onclick="filtraOrdini('', this)">Tutti</button>
                <button class="filtro-btn" onclick="filtraOrdini('Pagato', this)">Pagati</button>
                <button class="filtro-btn" onclick="filtraOrdini('In lavorazione', this)">In lavorazione</button>
                <button class="filtro-btn" onclick="filtraOrdini('Spedito', this)">Spediti</button>
                <button class="filtro-btn" onclick="filtraOrdini('Consegnato', this)">Consegnati</button>
            </div>

            <div id="lista-ordini-venditore"><p style="color:var(--text-sec);">Caricamento...</p></div>
        </section>
    </div>
</div>

<!-- MODAL AGGIUNGI LIBRO -->
<div id="modalLibro" class="modal-overlay">
    <div class="modal-box" style="max-width:520px;">
        <span onclick="$('#modalLibro').fadeOut()" style="float:right; cursor:pointer; font-size:1.5em;">&times;</span>
        <h3 style="color:var(--dark-green); margin-bottom:20px;">📖 Nuovo Annuncio</h3>
        <form id="formNuovoLibro" enctype="multipart/form-data">
            <label style="font-weight:600;">Titolo *</label>
            <input type="text" name="nome" placeholder="Titolo del libro" class="form-control" required>

            <label style="font-weight:600;">Autore *</label>
            <input type="text" name="autore" placeholder="Nome Cognome autore" class="form-control" required>

            <label style="font-weight:600;">Categoria *</label>
            <select name="categoria" id="modal-categoria" class="form-control" required onchange="caricaSottocategorie(this.value)">
                <option value="">Seleziona categoria...</option>
            </select>

            <label style="font-weight:600;">Sottocategoria</label>
            <select name="sottocategoria" id="modal-sottocategoria" class="form-control">
                <option value="">Seleziona sottocategoria...</option>
            </select>

            <label style="font-weight:600; font-size:0.85em; color:var(--text-sec);">
                Non trovi la categoria? <a href="#" onclick="toggleNuovaCat()" style="color:var(--primary-green);">Aggiungi nuova</a>
            </label>
            <div id="nuova-cat-box" style="display:none; margin-bottom:12px;">
                <input type="text" name="nuova_categoria" id="campo-nuova-cat" placeholder="Nome nuova categoria/sottocategoria" class="form-control" style="margin-bottom:0;">
            </div>

            <label style="font-weight:600;">Descrizione</label>
            <textarea name="descrizione" placeholder="Trama o descrizione del libro" class="form-control" rows="3"></textarea>

            <div style="display:flex; gap:10px;">
                <div style="flex:1;">
                    <label style="font-weight:600;">Prezzo (€) *</label>
                    <input type="number" name="prezzo" step="0.01" placeholder="12.50" class="form-control" required>
                </div>
                <div style="flex:1;">
                    <label style="font-weight:600;">Quantità disponibile *</label>
                    <input type="number" name="quantita" placeholder="0 = non disponibile" class="form-control" min="0" required>
                </div>
            </div>

            <label style="font-weight:600;">Copertina</label>
            <input type="file" name="fotoLibro" accept="image/*" class="form-control">

            <button type="submit" class="btn-primary" style="width:100%; padding:14px; margin-top:5px;">PUBBLICA ANNUNCIO</button>
            <p id="msg-nuovo-libro" style="display:none; text-align:center; font-weight:600; margin-top:10px;"></p>
        </form>
    </div>
</div>

<!-- MODAL MODIFICA LIBRO -->
<div id="modalModifica" class="modal-overlay">
    <div class="modal-box" style="max-width:500px;">
        <span onclick="$('#modalModifica').fadeOut()" style="float:right; cursor:pointer; font-size:1.5em;">&times;</span>
        <h3 style="color:var(--dark-green); margin-bottom:20px;">✏️ Modifica Libro</h3>
        <input type="hidden" id="modifica-id">
        <label style="font-weight:600;">Titolo</label>
        <input type="text" id="modifica-nome" class="form-control">
        <label style="font-weight:600;">Autore</label>
        <input type="text" id="modifica-autore" class="form-control">
        <label style="font-weight:600;">Descrizione</label>
        <textarea id="modifica-descrizione" class="form-control" rows="3"></textarea>
        <div style="display:flex; gap:10px;">
            <div style="flex:1;">
                <label style="font-weight:600;">Prezzo (€)</label>
                <input type="number" id="modifica-prezzo" step="0.01" class="form-control">
            </div>
            <div style="flex:1;">
                <label style="font-weight:600;">Quantità disponibile</label>
                <input type="number" id="modifica-quantita" min="0" class="form-control">
            </div>
        </div>
        <label style="font-weight:600;">Nuova Copertina (opzionale)</label>
        <img id="modifica-preview" src="" style="width:55px;height:75px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:none;">
        <input type="file" id="modifica-foto" accept="image/*" class="form-control">
        <button class="btn-primary" style="width:100%; padding:12px;" onclick="salvaModifica()">💾 Salva Modifiche</button>
        <p id="msg-modifica" style="display:none; font-weight:600; margin-top:8px; text-align:center;"></p>
    </div>
</div>

<script>
let categorieDB = [];
let filtroCorrente = '';

$(document).ready(function() {
    caricaLibri();
    caricaOrdini('');
    caricaCategorieModal();

    $("#formNuovoLibro").on("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: 'api/ba_aggiungi_libro.php', type: 'POST', data: formData,
            cache: false, contentType: false, processData: false,
            success: function(resp) {
                const msg = $('#msg-nuovo-libro');
                if(resp.status === 'ok') {
                    msg.text('✅ Libro pubblicato!').css('color','green').show();
                    $('#formNuovoLibro')[0].reset();
                    $('#modal-sottocategoria').html('<option value="">Seleziona sottocategoria...</option>');
                    setTimeout(() => { msg.hide(); $('#modalLibro').fadeOut(); caricaLibri(); }, 2000);
                } else {
                    msg.text('❌ ' + resp.msg).css('color','red').show();
                }
            }
        });
    });
});

function caricaCategorieModal() {
    $.get('api/ba_categorie.php', function(resp) {
        if(!resp.categorie) return;
        categorieDB = resp.categorie;
        // Solo categorie principali (senza padre)
        resp.categorie.forEach(c => {
            if (!c.nome_categoria_padre) {
                $('#modal-categoria').append(`<option value="${c.nome_categoria}">${c.nome_categoria}</option>`);
            }
        });
    });
}

function caricaSottocategorie(catPadre) {
    const select = $('#modal-sottocategoria');
    select.html('<option value="">Nessuna sottocategoria</option>');
    if (!catPadre) return;
    categorieDB.forEach(c => {
        if (c.nome_categoria_padre === catPadre) {
            select.append(`<option value="${c.nome_categoria}">${c.nome_categoria}</option>`);
        }
    });
}

function toggleNuovaCat() {
    const box = $('#nuova-cat-box');
    box.toggle();
}

function caricaLibri() {
    $.get('api/ba_libri_venditore.php', function(resp) {
        if(resp.status === 'ok') {
            $('#count-libri').text(resp.libri.length);
            if(resp.libri.length === 0) {
                $('#lista-libri-venditore').html('<p style="color:var(--text-sec);">Nessun libro in vendita.</p>');
                return;
            }
            let html = '';
            resp.libri.forEach(lib => {
                const qta = lib.quantita_disponibile;
                const qtaHtml = qta > 0
                    ? `<span style="color:var(--primary-green);">${qta} disponibili</span>`
                    : `<span style="color:#e74c3c;">Non disponibile</span>`;
                html += `
                <div class="manage-book-card">
                    <img src="${lib.url_foto || 'img/default.jpg'}" class="manage-book-img">
                    <div style="flex-grow:1;">
                        <div style="font-weight:bold;">${lib.nome}</div>
                        <div style="font-size:0.82em; color:var(--text-sec);">
                            ${lib.autore ? lib.autore + ' &nbsp;|&nbsp; ' : ''}
                            €${parseFloat(lib.prezzo).toFixed(2)} &nbsp;|&nbsp; ${qtaHtml}
                        </div>
                    </div>
                    <button class="btn-modifica-libro" onclick="apriModifica(${lib.id_prodotto},'${lib.nome.replace(/'/g,"\\'")}','${(lib.autore||'').replace(/'/g,"\\'")}','${(lib.descrizione||'').replace(/'/g,"\\'").replace(/\n/g,' ')}',${lib.prezzo},${lib.quantita_disponibile},'${lib.url_foto||''}')">✏️</button>
                    <button class="btn-elimina" onclick="eliminaLibro(${lib.id_prodotto})">🗑️</button>
                </div>`;
            });
            $('#lista-libri-venditore').html(html);
        }
    });
}

function caricaOrdini(stato) {
    filtroCorrente = stato;
    $.get('api/ba_ordini_venditore.php', { action: 'list', stato: stato }, function(resp) {
        if(resp.status === 'ok') {
            $('#count-ordini').text(resp.ordini.length);
            $('#total-guadagno').text('€ ' + parseFloat(resp.guadagno).toFixed(2));

            if(resp.ordini.length === 0) {
                $('#lista-ordini-venditore').html('<p style="color:var(--text-sec);">Nessun ordine trovato.</p>');
                return;
            }

            let html = '';
            resp.ordini.forEach(o => {
                const badgeClass = {
                    'Pagato':'stato-pagato','In lavorazione':'stato-lavorazione',
                    'Spedito':'stato-spedito','Consegnato':'stato-consegnato'
                }[o.Stato] || 'stato-pagato';

                let libriHtml = '';
                o.libri.forEach(l => {
                    libriHtml += `
                    <div class="ordine-libro">
                        <img src="${l.Foto}" style="width:40px;height:55px;object-fit:cover;border-radius:4px;">
                        <div>
                            <strong style="font-size:0.9em;">${l.Titolo}</strong><br>
                            <small style="color:var(--text-sec);">× ${l.Quantita} — €${(parseFloat(l.Prezzo)*parseInt(l.Quantita)).toFixed(2)}</small>
                        </div>
                    </div>`;
                });

                let azioniHtml = '';
                if(o.Stato === 'Pagato') {
                    azioniHtml = `<button class="btn-primary" style="padding:7px 14px;font-size:0.85em;margin-top:10px;" onclick="aggiornaStato(${o.IdOrdine},'In lavorazione')">📦 Conferma ordine</button>`;
                } else if(o.Stato === 'In lavorazione') {
                    azioniHtml = `<button class="btn-primary" style="padding:7px 14px;font-size:0.85em;margin-top:10px;" onclick="aggiornaStato(${o.IdOrdine},'Spedito')">🚚 Segna come Spedito</button>`;
                } else if(o.Stato === 'Spedito') {
                    azioniHtml = `<button class="btn-primary" style="padding:7px 14px;font-size:0.85em;margin-top:10px;" onclick="aggiornaStato(${o.IdOrdine},'Consegnato')">✅ Segna come Consegnato</button>`;
                }

                html += `
                <div class="ordine-card">
                    <div class="ordine-header" onclick="toggleOrdine(this)">
                        <div>
                            <strong>Ordine #${o.IdOrdine}</strong>
                            <span style="color:var(--text-sec);margin-left:10px;font-size:0.85em;">${o.DataOrdine}</span>
                            <span style="color:var(--text-sec);margin-left:10px;font-size:0.85em;">👤 ${o.Cliente}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="badge-stato ${badgeClass}">${o.Stato}</span>
                            <strong style="color:var(--primary-green);">€${parseFloat(o.Totale).toFixed(2)}</strong>
                            <span style="color:var(--text-sec);">▼</span>
                        </div>
                    </div>
                    <div class="ordine-body">
                        ${libriHtml}
                        ${azioniHtml}
                    </div>
                </div>`;
            });
            $('#lista-ordini-venditore').html(html);
        }
    }, 'json');
}

function toggleOrdine(header) {
    $(header).next('.ordine-body').slideToggle(200);
}

function filtraOrdini(stato, btn) {
    $('.filtro-btn').removeClass('active');
    $(btn).addClass('active');
    caricaOrdini(stato);
}

function scrollToOrdini() {
    $('html,body').animate({ scrollTop: $('#sezione-ordini').offset().top - 20 }, 400);
}

function aggiornaStato(idOrdine, stato) {
    $.post('api/ba_ordini_venditore.php?action=update_status', { idOrdine: idOrdine, stato: stato }, function(resp) {
        if(resp.status === 'ok') caricaOrdini(filtroCorrente);
        else alert('Errore: ' + resp.msg);
    }, 'json');
}

function apriModalNuovoLibro() {
    $('#formNuovoLibro')[0].reset();
    $('#modal-sottocategoria').html('<option value="">Seleziona sottocategoria...</option>');
    $('#nuova-cat-box').hide();
    $('#msg-nuovo-libro').hide();
    $('#modalLibro').css('display','flex').hide().fadeIn();
}

function apriModifica(id, nome, autore, desc, prezzo, qta, urlFoto) {
    $('#modifica-id').val(id);
    $('#modifica-nome').val(nome);
    $('#modifica-autore').val(autore);
    $('#modifica-descrizione').val(desc);
    $('#modifica-prezzo').val(prezzo);
    $('#modifica-quantita').val(qta);
    $('#modifica-foto').val('');
    urlFoto ? $('#modifica-preview').attr('src',urlFoto).show() : $('#modifica-preview').hide();
    $('#msg-modifica').hide();
    $('#modalModifica').css('display','flex').hide().fadeIn();
}

function salvaModifica() {
    const formData = new FormData();
    formData.append('id_prodotto', $('#modifica-id').val());
    formData.append('nome', $('#modifica-nome').val());
    formData.append('autore', $('#modifica-autore').val());
    formData.append('descrizione', $('#modifica-descrizione').val());
    formData.append('prezzo', $('#modifica-prezzo').val());
    formData.append('quantita', $('#modifica-quantita').val());
    const foto = $('#modifica-foto')[0].files[0];
    if(foto) formData.append('fotoLibro', foto);

    $.ajax({
        url: 'api/ba_modifica_libro.php', type: 'POST', data: formData,
        cache: false, contentType: false, processData: false,
        success: function(resp) {
            if(resp.status === 'ok') {
                $('#msg-modifica').text('✅ Salvato!').css('color','green').show();
                setTimeout(() => { $('#modalModifica').fadeOut(); caricaLibri(); }, 1500);
            } else {
                $('#msg-modifica').text('❌ '+resp.msg).css('color','red').show();
            }
        }
    });
}

function eliminaLibro(id) {
    if(!confirm('Eliminare questo libro?')) return;
    $.post('api/ba_elimina_libro.php', { id_prodotto: id }, function(resp) {
        if(resp.status === 'ok') caricaLibri();
        else alert('Errore: ' + resp.msg);
    });
}
</script>
</body>
</html>