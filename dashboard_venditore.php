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
    <title>Dashboard Venditore | The (E-)Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
<?php include("header.php"); ?>

<div class="container" style="max-width:1200px; margin:0 auto; padding:20px;">
    <header style="display:flex; justify-content:space-between; align-items:center; margin:20px 0 30px;">
        <h2 style="color:var(--dark-green); margin:0;">Pannello Venditore</h2>
        <button class="btn-primary" onclick="window.location.href='aggiungi_prodotto.php'">+ Aggiungi Prodotto</button>
    </header>

    <!-- STATISTICHE -->
    <div class="dash-grid" style="margin-bottom:30px;">
        <div class="stat-box">
            <h3>Prodotti Online</h3>
            <div class="stat-number" id="count-libri">0</div>
        </div>
        <div class="stat-box" style="cursor:pointer;" onclick="scrollToOrdini()">
            <h3>Ordini Ricevuti</h3>
            <div class="stat-number" id="count-ordini">0</div>
            <small style="color:var(--text-sec);">Clicca per vedere lo storico</small>
        </div>
        <div class="stat-box stat-box-guadagno" id="box-guadagno">
            <h3>Guadagno Disponibile</h3>
            <div class="stat-number" id="total-guadagno">€ 0.00</div>
            <small style="color:var(--text-sec);" id="ultimo-trasf"></small>
            <button class="btn-trasferisci" id="btn-trasferisci" onclick="trasferisciGuadagno()" style="display:none;">
                Trasferisci sul conto
            </button>
        </div>
    </div>

    <!-- MODAL TRASFERIMENTO -->
    <div id="modalTrasferimento" class="modal-overlay">
        <div class="modal-box" style="max-width:420px; text-align:center;">
            <div style="font-size:3em; margin-bottom:10px; color:var(--dark-green);">&#10003;</div>
            <h3 style="color:var(--dark-green);">Trasferimento completato</h3>
            <p id="msg-trasferimento" style="color:#555; font-size:1.05em; margin:15px 0 25px;"></p>
            <button class="btn-primary" onclick="$('#modalTrasferimento').fadeOut()">Chiudi</button>
        </div>
    </div>

    <!-- GRIGLIA PRODOTTI + ORDINI -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
        <section>
            <h3 style="color:var(--dark-green); margin-bottom:15px;">I tuoi Prodotti</h3>
            <div id="lista-libri-venditore"><p style="color:var(--text-sec);">Caricamento...</p></div>
        </section>

        <section id="sezione-ordini">
            <h3 style="color:var(--dark-green); margin-bottom:15px;">Ordini Ricevuti</h3>
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

<!-- SEZIONE PACCHETTI E ABBONAMENTI -->
<div class="container" style="max-width:1200px; margin:0 auto; padding:0 20px 30px;">
    <h3 style="color:var(--dark-green); margin-bottom:15px; display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px;height:22px;"><path d="M20 6h-2.18c.07-.44.18-.87.18-1.33C18 2.54 15.96.5 13.46.5c-1.29 0-2.4.51-3.19 1.33L9 3.1 7.73 1.83C6.94 1.01 5.83.5 4.54.5 2.04.5 0 2.54 0 4.67c0 .46.11.89.18 1.33H0v2h20v-2z"/></svg>
        I miei Pacchetti e Abbonamenti
    </h3>
    <div id="lista-pacchetti-venditore"><p style="color:var(--text-sec);">Caricamento...</p></div>
</div>

<!-- MODAL MODIFICA PACCHETTO -->
<div id="modalModificaPacchetto" class="modal-overlay" style="display:none; justify-content:center; align-items:center;">
    <div class="modal-box" style="max-width:480px; position:relative;">
        <button onclick="$('#modalModificaPacchetto').hide()" style="position:absolute;top:14px;right:16px;background:none;border:none;cursor:pointer;color:#999;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px;height:22px;"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
        <h3 style="color:var(--dark-green); margin:0 0 16px;">Modifica Pacchetto</h3>
        <input type="hidden" id="mod-pack-id">
        <input type="hidden" id="mod-pack-tipo">
        <label style="font-size:0.9em; font-weight:600; display:block; margin-bottom:4px;">Nome</label>
        <input type="text" id="mod-pack-nome" class="form-control" placeholder="Nome pacchetto">
        <div id="mod-pack-sconti-libro">
            <label style="font-size:0.9em; font-weight:600; display:block; margin-bottom:4px;">Sconto 2 prodotti (%)</label>
            <input type="number" id="mod-pack-s2" class="form-control" min="0" max="99">
            <label style="font-size:0.9em; font-weight:600; display:block; margin-bottom:4px;">Sconto 3 prodotti (%)</label>
            <input type="number" id="mod-pack-s3" class="form-control" min="0" max="99">
            <label style="font-size:0.9em; font-weight:600; display:block; margin-bottom:4px;">Sconto collezione completa (%)</label>
            <input type="number" id="mod-pack-st" class="form-control" min="0" max="99">
        </div>
        <div id="mod-pack-sconti-abb" style="display:none;">
            <label style="font-size:0.9em; font-weight:600; display:block; margin-bottom:4px;">Sconto abbonamento (%)</label>
            <input type="number" id="mod-pack-sa" class="form-control" min="0" max="99">
        </div>
        <p id="mod-pack-err" style="color:#e74c3c; font-size:0.88em; display:none; margin-top:8px;"></p>
        <button onclick="salvaPacchetto()" class="btn-primary" style="width:100%; padding:12px; margin-top:12px;">Salva modifiche</button>
    </div>
</div>

<!-- MODAL MODIFICA -->
<div id="modalModifica" class="modal-overlay">
    <div class="modal-box" style="max-width:520px;">
        <span onclick="$('#modalModifica').fadeOut()" style="float:right; cursor:pointer; font-size:1.5em;">&times;</span>
        <h3 style="color:var(--dark-green); margin-bottom:20px;">Modifica Prodotto</h3>
        <input type="hidden" id="modifica-id">
        <input type="hidden" id="modifica-tipo-prodotto">
        <label style="font-weight:600;">Titolo</label>
        <input type="text" id="modifica-nome" class="form-control">
        <label style="font-weight:600;">Autore / Editore</label>
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
        <label style="font-weight:600;">Nuova immagine (opzionale)</label>
        <img id="modifica-preview" src="" style="width:55px;height:75px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:none;">
        <input type="file" id="modifica-foto" accept="image/*" class="form-control">

        <!-- SEZIONE PACCHETTO/ABBONAMENTO -->
        <div style="border:1px solid var(--border-color); border-radius:10px; padding:15px; margin:10px 0; background:#f9fbf9;">
            <p style="font-weight:700; color:var(--dark-green); margin:0 0 4px; font-size:0.95em;">Pacchetto sconto</p>
            <p id="modifica-pacchetto-stato" style="font-size:0.85em; color:var(--text-sec); margin:0 0 10px;">Caricamento stato...</p>

            <div id="modifica-box-libro" style="display:none;">
                <label style="font-size:0.88em; font-weight:600;">Assegna a un pacchetto sconto esistente, o rimuovi</label>
                <select id="modifica-scelta-pacchetto" class="form-control"></select>
            </div>

            <div id="modifica-box-abbonamento" style="display:none;">
                <label style="font-size:0.88em; font-weight:600;">Assegna a un abbonamento esistente, o rimuovi</label>
                <select id="modifica-scelta-abbonamento" class="form-control"></select>
            </div>

            <a href="aggiungi_prodotto.php" style="font-size:0.82em; color:var(--dark-green);">+ Crea un nuovo pacchetto o abbonamento dalla pagina di aggiunta prodotto</a>
        </div>

        <button class="btn-primary" style="width:100%; padding:12px;" onclick="salvaModifica()">Salva Modifiche</button>
        <p id="msg-modifica" style="display:none; font-weight:600; margin-top:8px; text-align:center;"></p>
    </div>
</div>

<script>
let filtroCorrente = '';

$(document).ready(function() {
    caricaLibri();
    caricaOrdini('');
    caricaPacchetti();
});

function caricaLibri() {
    $.get('api/ba_libri_venditore.php', function(resp) {
        if(resp.status === 'ok') {
            $('#count-libri').text(resp.libri.length);
            if(resp.libri.length === 0) {
                $('#lista-libri-venditore').html('<p style="color:var(--text-sec);">Nessun prodotto in vendita.</p>');
                return;
            }
            let html = '';
            resp.libri.forEach(lib => {
                const qta = lib.quantita_disponibile;
                const qtaHtml = qta > 0
                    ? `<span style="color:var(--dark-green);">${qta} disp.</span>`
                    : `<span style="color:#e74c3c;">Esaurito</span>`;
                const tipo = lib.tipo_prodotto || 'libro';
                const badgeTipo = `<span class="badge-tipo tipo-${tipo}">${tipo}</span>`;
                html += `
                <div class="manage-book-card">
                    <img src="${lib.url_foto || 'img/default.jpg'}" class="manage-book-img">
                    <div style="flex-grow:1;">
                        <div style="font-weight:bold;">${lib.nome} ${badgeTipo}</div>
                        <div style="font-size:0.82em; color:var(--text-sec);">
                            ${lib.autore ? lib.autore + ' &nbsp;|&nbsp; ' : ''}
                            €${parseFloat(lib.prezzo).toFixed(2)} &nbsp;|&nbsp; ${qtaHtml}
                        </div>
                    </div>
                    <button class="btn-modifica-libro" onclick="apriModifica(${lib.id_prodotto},'${lib.nome.replace(/'/g,"\\'")}','${(lib.autore||'').replace(/'/g,"\\'")}','${(lib.descrizione||'').replace(/'/g,"\\'").replace(/\n/g,' ')}',${lib.prezzo},${lib.quantita_disponibile},'${lib.url_foto||''}','${lib.tipo_prodotto||'libro'}',${lib.id_pacchetto || 'null'})">Modifica</button>
                    <button class="btn-elimina" onclick="eliminaLibro(${lib.id_prodotto})">Elimina</button>
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
            const guadagno = parseFloat(resp.guadagno || 0);
            $('#total-guadagno').text('€ ' + guadagno.toFixed(2));
            if (guadagno > 0) {
                $('#btn-trasferisci').show();
            } else {
                $('#btn-trasferisci').hide();
            }

            if(resp.ordini.length === 0) {
                $('#lista-ordini-venditore').html('<p style="color:var(--text-sec);">Nessun ordine trovato.</p>');
                return;
            }

            let html = '';
            resp.ordini.forEach(o => {
                // o.Stato = stato del venditore (per azioni), o.StatoOrdine = stato aggregato (visibile al cliente)
                const statoV = o.Stato || 'Pagato';
                const statoO = o.StatoOrdine || statoV;
                const badgeClass = {
                    'Pagato':'stato-pagato','In lavorazione':'stato-lavorazione',
                    'Spedito':'stato-spedito','Consegnato':'stato-consegnato',
                    'Annullato':'stato-annullato'
                }[statoV] || 'stato-pagato';
                const badgeClassOrdine = {
                    'Pagato':'stato-pagato','In lavorazione':'stato-lavorazione',
                    'Spedito':'stato-spedito','Consegnato':'stato-consegnato'
                }[statoO] || 'stato-pagato';
                // Mostra badge ordine cliente solo se diverso dallo stato venditore
                const badgeOrdineHtml = statoV !== statoO
                    ? `<span class="badge-stato ${badgeClassOrdine}" style="opacity:0.7;" title="Stato visibile al cliente">${statoO} (cliente)</span>`
                    : '';

                let libriHtml = '';
                o.libri.forEach(l => {
                    libriHtml += `
                    <div class="ordine-libro">
                        <img src="${l.Foto}" style="width:40px;height:55px;object-fit:cover;border-radius:4px;">
                        <div>
                            <strong style="font-size:0.9em;">${l.Titolo}</strong><br>
                            <small style="color:var(--text-sec);">x ${l.Quantita} — €${(parseFloat(l.Prezzo)*parseInt(l.Quantita)).toFixed(2)}</small>
                        </div>
                    </div>`;
                });

                let azioniHtml = '';
                if (statoO === 'Annullato') {
                    azioniHtml = `<div style="margin-top:10px; padding:10px 14px; background:#fff3f3; border:1px solid #e74c3c; border-radius:8px; color:#c0392b; font-size:0.88em; display:flex; align-items:center; gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;flex-shrink:0;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                        Ordine annullato — rimborso da effettuare al cliente <strong>${o.Cliente}</strong>.
                    </div>`;
                } else if(statoV === 'Pagato') {
                    azioniHtml = `<button class="btn-primary" style="padding:7px 14px;font-size:0.85em;margin-top:10px;" onclick="aggiornaStato(${o.IdOrdine},'In lavorazione')">Conferma ordine</button>`;
                } else if(statoV === 'In lavorazione') {
                    azioniHtml = `<button class="btn-primary" style="padding:7px 14px;font-size:0.85em;margin-top:10px;" onclick="aggiornaStato(${o.IdOrdine},'Spedito')">Segna come Spedito</button>`;
                } else if(statoV === 'Spedito') {
                    azioniHtml = `<button class="btn-primary" style="padding:7px 14px;font-size:0.85em;margin-top:10px;" onclick="aggiornaStato(${o.IdOrdine},'Consegnato')">Segna come Consegnato</button>`;
                }

                html += `
                <div class="ordine-card">
                    <div class="ordine-header" onclick="toggleOrdine(this)">
                        <div>
                            <strong>Ordine #${o.IdOrdine}</strong>
                            <span style="color:var(--text-sec);margin-left:10px;font-size:0.85em;">${o.DataOrdine}</span>
                            <span style="color:var(--text-sec);margin-left:10px;font-size:0.85em;">${o.Cliente}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="badge-stato ${badgeClass}">${statoV}</span>
                            ${badgeOrdineHtml}
                            <strong style="color:var(--dark-green);" title="Il tuo guadagno su questo ordine">€${parseFloat(o.TotaleVenditore || 0).toFixed(2)}</strong>
                            <span style="color:var(--text-sec);">&#9660;</span>
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

function trasferisciGuadagno() {
    const guadagno = $('#total-guadagno').text();
    if (!confirm('Confermi il trasferimento di ' + guadagno + ' sul tuo conto?')) return;
    $.post('api/ba_trasferisci_guadagno.php', function(resp) {
        if (resp.status === 'ok') {
            $('#msg-trasferimento').text(resp.msg);
            $('#modalTrasferimento').css('display','flex').hide().fadeIn();
            $('#total-guadagno').text('€ 0.00');
            $('#btn-trasferisci').hide();
        } else {
            alert(resp.msg);
        }
    }, 'json');
}

function toggleOrdine(header) { $(header).next('.ordine-body').slideToggle(200); }
function filtraOrdini(stato, btn) { $('.filtro-btn').removeClass('active'); $(btn).addClass('active'); caricaOrdini(stato); }
function scrollToOrdini() { $('html,body').animate({ scrollTop: $('#sezione-ordini').offset().top - 20 }, 400); }

// ===== GESTIONE PACCHETTI E ABBONAMENTI =====

function caricaPacchetti() {
    // Carica sia pacchetti libro che abbonamenti
    $.when(
        $.get('api/ba_pacchetti_venditore.php'),
        $.get('api/ba_abbonamenti_venditore.php')
    ).done(function(rPack, rAbb) {
        const pacchetti = rPack[0].status === 'ok' ? rPack[0].pacchetti : [];
        const abbonamenti = rAbb[0].status === 'ok' ? rAbb[0].abbonamenti : [];

        if (pacchetti.length === 0 && abbonamenti.length === 0) {
            $('#lista-pacchetti-venditore').html("<p style='color:var(--text-sec);'>Nessun pacchetto o abbonamento creato. Aggiungili durante l'inserimento di un prodotto.</p>");
            return;
        }

        let html = '<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px;">';
        pacchetti.forEach(p => {
            html += `<div style="background:white; border:1px solid var(--border-color); border-radius:10px; padding:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong style="font-size:0.95em; color:var(--dark-green);">${p.nome}</strong>
                    <span style="font-size:0.75em; background:#e3f2fd; color:#1565c0; padding:2px 8px; border-radius:10px; font-weight:700;">SAGA/PROMO</span>
                </div>
                <small style="color:var(--text-sec);">
                    Sconto: ${p.sconto_2}% (2), ${p.sconto_3}% (3), ${p.sconto_tutti}% (tutti) — ${p.tot_prodotti} prodotti
                </small>
                <div style="display:flex; gap:8px; margin-top:10px;">
                    <button onclick="apriModificaPacchetto(${p.id_pacchetto},encodeURIComponent(p.nome),'libro',${p.sconto_2},${p.sconto_3},${p.sconto_tutti},0)"
                        style="flex:1; padding:5px; font-size:0.82em; background:white; color:var(--dark-green); border:1px solid var(--dark-green); border-radius:6px; cursor:pointer;">Modifica</button>
                    <button onclick="eliminaPacchetto(${p.id_pacchetto},encodeURIComponent(p.nome))"
                        style="flex:1; padding:5px; font-size:0.82em; background:white; color:#e74c3c; border:1px solid #e74c3c; border-radius:6px; cursor:pointer;">Elimina</button>
                </div>
            </div>`;
        });
        abbonamenti.forEach(a => {
            html += `<div style="background:white; border:1px solid #d2b4de; border-radius:10px; padding:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong style="font-size:0.95em; color:#6c3483;">${a.nome}</strong>
                    <span style="font-size:0.75em; background:#f3e5f5; color:#6a1b9a; padding:2px 8px; border-radius:10px; font-weight:700;">ABBONAMENTO</span>
                </div>
                <small style="color:var(--text-sec);">
                    Sconto: ${a.sconto_tutti}% — ${a.periodicita || ''} — ${a.tot_prodotti} numeri
                </small>
                <div style="display:flex; gap:8px; margin-top:10px;">
                    <button onclick="apriModificaPacchetto(${a.id_pacchetto},encodeURIComponent(a.nome),'abbonamento',0,0,${a.sconto_tutti},${a.sconto_tutti})"
                        style="flex:1; padding:5px; font-size:0.82em; background:white; color:#6c3483; border:1px solid #6c3483; border-radius:6px; cursor:pointer;">Modifica</button>
                    <button onclick="eliminaPacchetto(${a.id_pacchetto},encodeURIComponent(a.nome))"
                        style="flex:1; padding:5px; font-size:0.82em; background:white; color:#e74c3c; border:1px solid #e74c3c; border-radius:6px; cursor:pointer;">Elimina</button>
                </div>
            </div>`;
        });
        html += '</div>';
        $('#lista-pacchetti-venditore').html(html);
    });
}

function apriModificaPacchetto(id, nomeEnc, tipo, s2, s3, st, sa) {
    const nome = decodeURIComponent(nomeEnc);
    $('#mod-pack-id').val(id);
    $('#mod-pack-tipo').val(tipo);
    $('#mod-pack-nome').val(nome);
    $('#mod-pack-err').hide();
    if (tipo === 'abbonamento') {
        $('#mod-pack-sconti-libro').hide();
        $('#mod-pack-sconti-abb').show();
        $('#mod-pack-sa').val(sa);
    } else {
        $('#mod-pack-sconti-abb').hide();
        $('#mod-pack-sconti-libro').show();
        $('#mod-pack-s2').val(s2);
        $('#mod-pack-s3').val(s3);
        $('#mod-pack-st').val(st);
    }
    $('#modalModificaPacchetto').css('display','flex');
}

function salvaPacchetto() {
    const id   = $('#mod-pack-id').val();
    const tipo = $('#mod-pack-tipo').val();
    const nome = $('#mod-pack-nome').val().trim();
    if (!nome) { $('#mod-pack-err').text('Il nome è obbligatorio.').show(); return; }

    const data = { id_pacchetto: id, nome: nome, tipo_pacchetto: tipo };
    if (tipo === 'abbonamento') {
        data.sconto_tutti = $('#mod-pack-sa').val();
    } else {
        data.sconto_2    = $('#mod-pack-s2').val();
        data.sconto_3    = $('#mod-pack-s3').val();
        data.sconto_tutti = $('#mod-pack-st').val();
    }

    $.post('api/ba_modifica_pacchetto.php', data, function(resp) {
        if (resp.status === 'ok') {
            $('#modalModificaPacchetto').hide();
            caricaPacchetti();
        } else {
            $('#mod-pack-err').text(resp.msg || 'Errore.').show();
        }
    }, 'json');
}

function eliminaPacchetto(id, nomeEnc) {
    const nome = decodeURIComponent(nomeEnc);
    if (!confirm('Eliminare il pacchetto "' + nome + '"?\nI prodotti collegati perderanno l\'associazione ma non verranno eliminati.')) return;
    $.post('api/ba_elimina_pacchetto.php', { id_pacchetto: id }, function(resp) {
        if (resp.status === 'ok') {
            caricaPacchetti();
        } else {
            alert(resp.msg || 'Errore eliminazione.');
        }
    }, 'json');
}
function aggiornaStato(idOrdine, stato) {
    $.post('api/ba_ordini_venditore.php?action=update_status', { idOrdine: idOrdine, stato: stato }, function(resp) {
        if(resp.status === 'ok') caricaOrdini(filtroCorrente);
        else alert('Errore: ' + resp.msg);
    }, 'json');
}
function apriModifica(id, nome, autore, desc, prezzo, qta, urlFoto, tipoProdotto, idPacchetto) {
    $('#modifica-id').val(id);
    $('#modifica-nome').val(nome);
    $('#modifica-autore').val(autore);
    $('#modifica-descrizione').val(desc);
    $('#modifica-prezzo').val(prezzo);
    $('#modifica-quantita').val(qta);
    $('#modifica-foto').val('');
    $('#modifica-tipo-prodotto').val(tipoProdotto || 'libro');
    urlFoto ? $('#modifica-preview').attr('src',urlFoto).show() : $('#modifica-preview').hide();
    $('#msg-modifica').hide();
    caricaSezionePacchettoModifica(tipoProdotto, idPacchetto);
    $('#modalModifica').css('display','flex').hide().fadeIn();
}

function caricaSezionePacchettoModifica(tipoProdotto, idPacchettoAttuale) {
    const eAbbonabile = (tipoProdotto === 'rivista' || tipoProdotto === 'magazine' || tipoProdotto === 'periodico');
    $('#modifica-box-libro, #modifica-box-abbonamento').hide();

    if (eAbbonabile) {
        $('#modifica-pacchetto-stato').text('Caricamento abbonamenti...');
        $.get('api/ba_abbonamenti_venditore.php', function(resp) {
            const select = $('#modifica-scelta-abbonamento');
            select.html('<option value="">-- Nessun abbonamento --</option>');
            if (resp.status === 'ok' && resp.abbonamenti.length > 0) {
                resp.abbonamenti.forEach(a => {
                    const sel = (idPacchettoAttuale && a.id_pacchetto == idPacchettoAttuale) ? 'selected' : '';
                    select.append(`<option value="${a.id_pacchetto}" ${sel}>${a.nome} (${a.tot_prodotti} numeri, ${a.periodicita_label})</option>`);
                });
            }
            $('#modifica-box-abbonamento').show();
            $('#modifica-pacchetto-stato').text(idPacchettoAttuale ? 'Questo numero è già assegnato a un abbonamento.' : 'Questo numero non è ancora in nessun abbonamento.');
        }, 'json');
    } else {
        $('#modifica-pacchetto-stato').text('Caricamento pacchetti...');
        $.get('api/ba_pacchetti_venditore.php', function(resp) {
            const select = $('#modifica-scelta-pacchetto');
            select.html('<option value="">-- Nessun pacchetto --</option>');
            if (resp.status === 'ok' && resp.pacchetti.length > 0) {
                resp.pacchetti.forEach(p => {
                    const sel = (idPacchettoAttuale && p.id_pacchetto == idPacchettoAttuale) ? 'selected' : '';
                    select.append(`<option value="${p.id_pacchetto}" ${sel}>${p.nome} (${p.tot_prodotti} prodotti)</option>`);
                });
            }
            $('#modifica-box-libro').show();
            $('#modifica-pacchetto-stato').text(idPacchettoAttuale ? 'Questo prodotto è già assegnato a un pacchetto sconto.' : 'Questo prodotto non è ancora in nessun pacchetto sconto.');
        }, 'json');
    }
}

function salvaModifica() {
    const formData = new FormData();
    formData.append('id_prodotto', $('#modifica-id').val());
    formData.append('nome', $('#modifica-nome').val());
    formData.append('autore', $('#modifica-autore').val());
    formData.append('descrizione', $('#modifica-descrizione').val());
    formData.append('prezzo', $('#modifica-prezzo').val());
    formData.append('quantita', $('#modifica-quantita').val());

    const tipoProdotto = $('#modifica-tipo-prodotto').val();
    const eAbbonabile = (tipoProdotto === 'rivista' || tipoProdotto === 'magazine' || tipoProdotto === 'periodico');
    if (eAbbonabile) {
        formData.append('id_pacchetto', $('#modifica-scelta-abbonamento').val());
    } else {
        formData.append('id_pacchetto', $('#modifica-scelta-pacchetto').val());
    }

    const foto = $('#modifica-foto')[0].files[0];
    if(foto) formData.append('fotoLibro', foto);
    $.ajax({
        url: 'api/ba_modifica_libro.php', type: 'POST', data: formData,
        cache: false, contentType: false, processData: false,
        success: function(resp) {
            if(resp.status === 'ok') {
                $('#msg-modifica').text('Salvato!').css('color','green').show();
                setTimeout(() => { $('#modalModifica').fadeOut(); caricaLibri(); }, 1500);
            } else {
                $('#msg-modifica').text('Errore: '+resp.msg).css('color','red').show();
            }
        }
    });
}
function eliminaLibro(id) {
    if(!confirm('Eliminare questo prodotto?')) return;
    $.post('api/ba_elimina_libro.php', { id_prodotto: id }, function(resp) {
        if(resp.status === 'ok') caricaLibri();
        else alert('Errore: ' + resp.msg);
    });
}
</script>
</body>
</html>