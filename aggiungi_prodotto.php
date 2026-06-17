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
    <title>Aggiungi Prodotto | The Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .page-wrapper { max-width: 720px; margin: 30px auto 60px; padding: 0 20px; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--text-sec); text-decoration:none; font-size:0.9em; margin-bottom:18px; }
        .back-link:hover { color:var(--dark-green); }
        .form-card { background:white; border:1px solid var(--border-color); border-radius:14px; padding:30px; box-shadow:0 4px 14px rgba(0,0,0,0.04); }
        .form-control { margin-bottom:14px; width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); font-family:inherit; background:#fff; box-sizing:border-box; }
        label.field-label { font-weight:600; font-size:0.92em; display:block; margin-bottom:6px; }
        .section-box { border:1px solid var(--border-color); border-radius:10px; padding:18px; margin-bottom:18px; background:#f9fbf9; }
        .section-title { font-weight:700; color:var(--dark-green); margin:0 0 12px; font-size:1.02em; }
        .row-2 { display:flex; gap:12px; }
        .row-2 > div { flex:1; }
        .tab-switch { display:flex; gap:8px; margin-bottom:16px; }
        .tab-btn { flex:1; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:white; cursor:pointer; font-weight:600; font-size:0.9em; color:var(--text-sec); transition:0.2s; text-align:center; }
        .tab-btn.active { background:var(--dark-green); color:white; border-color:var(--dark-green); }
        .help-text { color:var(--text-sec); font-size:0.82em; margin-top:4px; display:block; }
        .btn-submit { width:100%; padding:14px; margin-top:8px; font-size:1.02em; }
        #msg-nuovo-libro { text-align:center; font-weight:600; margin-top:14px; display:none; }
        .checklist-box { max-height:170px; overflow-y:auto; border:1px solid #ddd; border-radius:8px; padding:10px; margin-bottom:10px; font-size:0.88em; background:white; }
        .checklist-box label { display:flex; align-items:center; gap:8px; margin-bottom:8px; cursor:pointer; }
    </style>
</head>
<body>
<?php include("header.php"); ?>

<div class="page-wrapper">
    <a href="dashboard_venditore.php" class="back-link">&#8592; Torna alla dashboard</a>

    <div class="form-card">
        <h2 style="color:var(--dark-green); margin-top:0; margin-bottom:20px;">Nuovo Annuncio</h2>

        <form id="formNuovoLibro" enctype="multipart/form-data">

            <label class="field-label">Tipo di prodotto *</label>
            <select name="tipo_prodotto" id="tipo-prodotto" class="form-control" required onchange="aggiornaCampiTipo(this.value)">
                <option value="libro">Libro</option>
                <option value="rivista">Rivista</option>
                <option value="periodico">Periodico</option>
                <option value="magazine">Magazine</option>
                <option value="fumetto">Fumetto</option>
            </select>

            <label class="field-label">Titolo *</label>
            <input type="text" name="nome" placeholder="Titolo" class="form-control" required>

            <label class="field-label" id="label-autore">Autore *</label>
            <input type="text" name="autore" id="campo-autore" placeholder="Es. Elena Ferrante" class="form-control">

            <div class="row-2">
                <div>
                    <label class="field-label">Prezzo (€) *</label>
                    <input type="number" name="prezzo" step="0.01" min="0.01" placeholder="0.00" class="form-control" required>
                </div>
                <div>
                    <label class="field-label">Quantità *</label>
                    <input type="number" name="quantita" placeholder="0 = non disponibile" class="form-control" min="0" required>
                </div>
            </div>

            <label class="field-label">Categoria *</label>
            <select name="categoria" id="modal-categoria" class="form-control" required onchange="caricaSottocategorie(this.value)">
                <option value="">Seleziona categoria...</option>
            </select>

            <label class="field-label">Sottocategoria</label>
            <select name="sottocategoria" id="modal-sottocategoria" class="form-control">
                <option value="">Nessuna</option>
            </select>

            <div style="margin-bottom:14px;">
                <a href="#" onclick="toggleNuovaCat(); return false;" style="font-size:0.85em; color:var(--dark-green);">
                    + Aggiungi nuova categoria
                </a>
                <div id="nuova-cat-box" style="display:none; margin-top:8px;">
                    <input type="text" name="nuova_categoria" id="campo-nuova-cat" placeholder="Nome nuova categoria" class="form-control" style="margin-bottom:0;">
                </div>
            </div>

            <label class="field-label">Descrizione</label>
            <textarea name="descrizione" placeholder="Descrizione o trama..." class="form-control" rows="3"></textarea>

            <label class="field-label">Immagine copertina</label>
            <input type="file" name="fotoLibro" accept="image/*" class="form-control">

            <!-- SEZIONE PROMOZIONI -->
            <div class="section-box">
                <label style="font-weight:600; display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:0;">
                    <input type="checkbox" name="abilita_sconto" id="abilita-sconto" onchange="toggleScontoBox()">
                    Aggiungi a una promozione
                </label>

                <div id="sconto-box" style="display:none; margin-top:14px;">

                    <!-- Tab: pacchetto libro vs abbonamento periodico -->
                    <div class="tab-switch" id="tab-tipo-promo">
                        <div class="tab-btn active" data-tipo="libro" onclick="selezionaTabPromo('libro')">Pacchetto sconto</div>
                        <div class="tab-btn" data-tipo="abbonamento" onclick="selezionaTabPromo('abbonamento')" id="tab-abbonamento" style="display:none;">Abbonamento periodico</div>
                    </div>

                    <!-- ===== PACCHETTO LIBRO (saga / autore / promo) ===== -->
                    <div id="box-tipo-libro">
                        <label class="field-label" style="font-size:0.9em;">Vuoi aggiungerlo a un pacchetto già esistente?</label>
                        <select id="scelta-pacchetto-esistente" class="form-control" onchange="cambiaSceltaPacchetto(this.value)">
                            <option value="">Caricamento pacchetti...</option>
                        </select>

                        <div id="box-pacchetto-esistente" style="display:none; margin-top:10px;">
                            <p class="help-text" style="margin:0 0 10px;">
                                Il prodotto verrà aggiunto a questo pacchetto con gli scaglioni di sconto già impostati.
                            </p>
                            <input type="hidden" name="id_pacchetto_esistente" id="id-pacchetto-esistente">
                        </div>

                        <div id="box-pacchetto-nuovo" style="margin-top:10px;">
                            <label class="field-label" style="font-size:0.9em;">Nome del nuovo pacchetto</label>
                            <input type="text" name="nome_pacchetto" id="campo-nome-pacchetto" placeholder="Es. Saga del Signore degli Anelli, Promo Autore..." class="form-control">

                            <label class="field-label" style="font-size:0.9em;">Seleziona altri prodotti da includere (opzionale ora)</label>
                            <div id="lista-libri-pacchetto" class="checklist-box">
                                <p style="color:var(--text-sec);">Caricamento tuoi prodotti...</p>
                            </div>

                            <p style="font-size:0.85em; font-weight:700; color:var(--dark-green); margin:10px 0 6px;">Sconti a scaglioni</p>
                            <div class="row-2" style="gap:10px;">
                                <div>
                                    <label class="help-text" style="margin-top:0;">2 prodotti (%)</label>
                                    <input type="number" name="sconto_2" min="1" max="90" value="10" class="form-control">
                                </div>
                                <div>
                                    <label class="help-text" style="margin-top:0;">3 prodotti (%)</label>
                                    <input type="number" name="sconto_3" min="1" max="90" value="20" class="form-control">
                                </div>
                                <div>
                                    <label class="help-text" style="margin-top:0;">Tutti (%)</label>
                                    <input type="number" name="sconto_tutti" min="1" max="90" value="30" class="form-control">
                                </div>
                            </div>
                            <span class="help-text">Lo sconto cresce in base a quanti prodotti del pacchetto il cliente ha nel carrello.</span>
                        </div>
                    </div>

                    <!-- ===== ABBONAMENTO PERIODICO (riviste/periodici/magazine) ===== -->
                    <div id="box-tipo-abbonamento" style="display:none;">
                        <label class="field-label" style="font-size:0.9em;">Vuoi aggiungerlo a un abbonamento già esistente?</label>
                        <select id="scelta-abbonamento-esistente" class="form-control" onchange="cambiaSceltaAbbonamento(this.value)">
                            <option value="">Caricamento abbonamenti...</option>
                        </select>

                        <div id="box-abbonamento-esistente" style="display:none; margin-top:10px;">
                            <p class="help-text" style="margin:0 0 10px;">
                                Il numero verrà aggiunto a questo abbonamento. Lo sconto scatta solo se il cliente acquista tutti i numeri del periodo.
                            </p>
                            <input type="hidden" name="id_abbonamento_esistente" id="id-abbonamento-esistente">
                        </div>

                        <div id="box-abbonamento-nuovo" style="margin-top:10px;">
                            <label class="field-label" style="font-size:0.9em;">Nome dell'abbonamento</label>
                            <input type="text" name="nome_abbonamento" id="campo-nome-abbonamento" placeholder="Es. Vogue Italia - Maggio 2026" class="form-control">

                            <label class="field-label" style="font-size:0.9em;">Periodicità della testata</label>
                            <select name="periodicita" id="campo-periodicita" class="form-control">
                                <option value="settimanale">Settimanale (sconto su tutti i numeri del mese)</option>
                                <option value="mensile">Mensile (sconto su tutti i numeri dell'anno)</option>
                            </select>

                            <label class="field-label" style="font-size:0.9em;">Seleziona altri numeri della stessa testata da includere (opzionale ora)</label>
                            <div id="lista-numeri-abbonamento" class="checklist-box">
                                <p style="color:var(--text-sec);">Caricamento tuoi numeri...</p>
                            </div>

                            <label class="field-label" style="font-size:0.9em;">Sconto abbonamento completo (%)</label>
                            <input type="number" name="sconto_abbonamento" min="1" max="90" value="25" class="form-control">
                            <span class="help-text">Lo sconto si applica solo quando il cliente ha nel carrello TUTTI i numeri del periodo (mese o anno). Comprando un numero singolo, o solo alcuni, non c'è alcuno sconto.</span>
                        </div>
                    </div>

                </div>
            </div>

            <button type="submit" class="btn-primary btn-submit">PUBBLICA ANNUNCIO</button>
            <p id="msg-nuovo-libro"></p>
        </form>
    </div>
</div>

<script>
let categorieDB = [];

$(document).ready(function() {
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
                    msg.text('Prodotto pubblicato con successo! Torno alla dashboard...').css('color','green').show();
                    setTimeout(() => { window.location.href = 'dashboard_venditore.php'; }, 1500);
                } else {
                    msg.text('Errore: ' + resp.msg).css('color','red').show();
                }
            }
        });
    });
});

function caricaCategorieModal() {
    $.get('api/ba_categorie.php', function(resp) {
        if(!resp.categorie) return;
        categorieDB = resp.categorie;
        const padri = resp.categorie.filter(c => !c.nome_categoria_padre);
        padri.forEach(c => {
            $('#modal-categoria').append(`<option value="${c.nome_categoria}">${c.nome_categoria}</option>`);
        });
    });
}

function caricaSottocategorie(catPadre) {
    const select = $('#modal-sottocategoria');
    select.html('<option value="">Nessuna</option>');
    if (!catPadre) return;
    categorieDB.forEach(c => {
        if (c.nome_categoria_padre === catPadre) {
            select.append(`<option value="${c.nome_categoria}">${c.nome_categoria}</option>`);
        }
    });
}

function aggiornaCampiTipo(tipo) {
    const labelAutore = $('#label-autore');
    const campoAutore = $('#campo-autore');
    const isPeriodico = (tipo === 'rivista' || tipo === 'magazine' || tipo === 'periodico');

    if (isPeriodico) {
        labelAutore.text('Editore');
        campoAutore.attr('placeholder', 'Es. Condé Nast, RCS Media');
        $('#tab-abbonamento').show();
    } else if (tipo === 'fumetto') {
        labelAutore.text('Autore / Casa editrice');
        campoAutore.attr('placeholder', 'Es. Walt Disney, Marvel');
        $('#tab-abbonamento').hide();
        if ($('#tab-abbonamento').hasClass('active')) selezionaTabPromo('libro');
    } else {
        labelAutore.text('Autore *');
        campoAutore.attr('placeholder', 'Es. Elena Ferrante');
        $('#tab-abbonamento').hide();
        if ($('#tab-abbonamento').hasClass('active')) selezionaTabPromo('libro');
    }
}

function toggleNuovaCat() {
    $('#nuova-cat-box').toggle();
}

function toggleScontoBox() {
    const checked = $('#abilita-sconto').is(':checked');
    $('#sconto-box').toggle(checked);
    if (checked) {
        selezionaTabPromo('libro');
        caricaPacchettiEsistenti();
        caricaLibriPacchetto();
    }
}

function selezionaTabPromo(tipo) {
    $('.tab-btn').removeClass('active');
    $(`.tab-btn[data-tipo="${tipo}"]`).addClass('active');
    if (tipo === 'libro') {
        $('#box-tipo-libro').show();
        $('#box-tipo-abbonamento').hide();
    } else {
        $('#box-tipo-libro').hide();
        $('#box-tipo-abbonamento').show();
        caricaAbbonamentiEsistenti();
        caricaNumeriAbbonamento();
    }
}

function caricaPacchettiEsistenti() {
    $.get('api/ba_pacchetti_venditore.php', function(resp) {
        const select = $('#scelta-pacchetto-esistente');
        select.html('<option value="">-- Crea un nuovo pacchetto --</option>');
        if (resp.status === 'ok' && resp.pacchetti.length > 0) {
            resp.pacchetti.forEach(p => {
                select.append(`<option value="${p.id_pacchetto}">${p.nome} (${p.tot_prodotti} prodotti)</option>`);
            });
        }
    }, 'json');
}

function cambiaSceltaPacchetto(idPacchetto) {
    if (idPacchetto) {
        $('#box-pacchetto-esistente').show();
        $('#box-pacchetto-nuovo').hide();
        $('#id-pacchetto-esistente').val(idPacchetto);
    } else {
        $('#box-pacchetto-esistente').hide();
        $('#box-pacchetto-nuovo').show();
        $('#id-pacchetto-esistente').val('');
    }
}

function caricaLibriPacchetto() {
    $.get('api/ba_libri_venditore.php', function(resp) {
        if(resp.status === 'ok' && resp.libri.length > 0) {
            let html = '';
            resp.libri.forEach(l => {
                html += `<label>
                    <input type="checkbox" name="libri_pacchetto[]" value="${l.id_prodotto}">
                    <span>${l.nome}${l.autore ? ' — ' + l.autore : ''} (€${parseFloat(l.prezzo).toFixed(2)})</span>
                </label>`;
            });
            $('#lista-libri-pacchetto').html(html);
        } else {
            $('#lista-libri-pacchetto').html('<p style="color:var(--text-sec);font-size:0.85em;">Nessun prodotto disponibile.</p>');
        }
    });
}

function caricaAbbonamentiEsistenti() {
    $.get('api/ba_abbonamenti_venditore.php', function(resp) {
        const select = $('#scelta-abbonamento-esistente');
        select.html('<option value="">-- Crea un nuovo abbonamento --</option>');
        if (resp.status === 'ok' && resp.abbonamenti.length > 0) {
            resp.abbonamenti.forEach(a => {
                select.append(`<option value="${a.id_pacchetto}">${a.nome} (${a.tot_prodotti} numeri, ${a.periodicita_label})</option>`);
            });
        }
    }, 'json');
}

function cambiaSceltaAbbonamento(idAbbonamento) {
    if (idAbbonamento) {
        $('#box-abbonamento-esistente').show();
        $('#box-abbonamento-nuovo').hide();
        $('#id-abbonamento-esistente').val(idAbbonamento);
    } else {
        $('#box-abbonamento-esistente').hide();
        $('#box-abbonamento-nuovo').show();
        $('#id-abbonamento-esistente').val('');
    }
}

function caricaNumeriAbbonamento() {
    const tipoCorrente = $('#tipo-prodotto').val();
    $.get('api/ba_libri_venditore.php', function(resp) {
        if(resp.status === 'ok' && resp.libri.length > 0) {
            const numeri = resp.libri.filter(l => l.tipo_prodotto === tipoCorrente);
            if (numeri.length === 0) {
                $('#lista-numeri-abbonamento').html('<p style="color:var(--text-sec);font-size:0.85em;">Nessun altro numero pubblicato di questo tipo.</p>');
                return;
            }
            let html = '';
            numeri.forEach(l => {
                html += `<label>
                    <input type="checkbox" name="numeri_abbonamento[]" value="${l.id_prodotto}">
                    <span>${l.nome} (€${parseFloat(l.prezzo).toFixed(2)})</span>
                </label>`;
            });
            $('#lista-numeri-abbonamento').html(html);
        } else {
            $('#lista-numeri-abbonamento').html('<p style="color:var(--text-sec);font-size:0.85em;">Nessun altro numero pubblicato.</p>');
        }
    });
}
</script>
</body>
</html>