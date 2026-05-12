<?php
session_start();
if(!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Checkout | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .checkout-box { max-width:650px; margin:40px auto; background:white; padding:35px; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.08); }
        .checkout-label { display:block; font-weight:600; margin-bottom:8px; color:var(--dark-green); }
        .checkout-input { width:100%; padding:12px; border:1px solid var(--border-color); border-radius:8px; font-size:0.95em; box-sizing:border-box; margin-bottom:18px; font-family:inherit; }
        .checkout-input:focus { outline:none; border-color:var(--primary-green); box-shadow:0 0 0 3px rgba(39,174,96,0.1); }
        .checkout-input.error { border-color:#e74c3c; }
        .riepilogo { background:var(--light-green); padding:20px; border-radius:10px; margin-bottom:25px; border:1px solid var(--border-color); }
        .riepilogo-riga { display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.95em; }
        .totale-finale { font-size:1.2em; font-weight:800; color:var(--primary-green); border-top:1px solid var(--border-color); padding-top:12px; margin-top:8px; }
        .metodo-card { border:2px solid var(--border-color); border-radius:10px; padding:15px 20px; margin-bottom:12px; cursor:pointer; transition:0.2s; display:flex; align-items:center; gap:12px; }
        .metodo-card:hover { border-color:var(--primary-green); background:var(--light-green); }
        .metodo-card.selected { border-color:var(--primary-green); background:var(--light-green); }
        .metodo-card input[type=radio] { accent-color:var(--primary-green); width:18px; height:18px; }
        .metodo-saved { font-size:0.85em; color:var(--text-sec); margin-top:3px; }
        .card-fields { background:#f8f9fa; padding:20px; border-radius:10px; margin-top:5px; margin-bottom:18px; display:none; }
        .input-row { display:flex; gap:15px; }
        .input-row > div { flex:1; }
        .err-msg { color:#e74c3c; font-size:0.82em; margin-top:-14px; margin-bottom:10px; display:none; }
        .step-title { color:var(--dark-green); margin-bottom:5px; }
        .step-sub { color:var(--text-sec); font-size:0.9em; margin-bottom:25px; }
        #step-pagamento { display:none; }
        .recap-box { background:var(--light-green); border:1px solid var(--border-color); border-radius:10px; padding:20px; margin-bottom:25px; }
        .recap-row { display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.95em; }
    </style>
</head>
<body>
<?php include("header.php"); ?>

<div class="container">

    <!-- STEP 1: INDIRIZZO + METODO -->
    <div id="step-indirizzo" class="checkout-box">
        <h2 class="step-title">📦 Completa il tuo ordine</h2>
        <p class="step-sub">Controlla i dati prima di procedere al pagamento.</p>

        <div class="riepilogo" id="riepilogo-carrello">
            <p style="color:var(--text-sec);">Caricamento...</p>
        </div>

        <label class="checkout-label">Indirizzo di spedizione</label>
        <input type="text" id="campo-via" class="checkout-input" placeholder="Via e numero civico" required>
        <div class="input-row">
            <div>
                <input type="text" id="campo-citta" class="checkout-input" placeholder="Città" required>
            </div>
            <div>
                <input type="text" id="campo-cap" class="checkout-input" placeholder="CAP" maxlength="5" required>
            </div>
        </div>
        <input type="text" id="campo-provincia" class="checkout-input" placeholder="Provincia (es. MI)">

        <label class="checkout-label" style="margin-bottom:12px;">Metodo di pagamento</label>

        <div id="lista-metodi-salvati"></div>

        <div id="metodo-nuovo">
            <div class="metodo-card" onclick="selezionaMetodo('Carta', this)">
                <input type="radio" name="metodo" value="Carta"> 💳 Carta di Credito / Debito
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('PayPal', this)">
                <input type="radio" name="metodo" value="PayPal"> 🅿️ PayPal (simulato)
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('ApplePay', this)">
                <input type="radio" name="metodo" value="ApplePay">  Apple Pay (simulato)
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('Rate', this)">
                <input type="radio" name="metodo" value="Rate"> 📅 Paga in 3 rate (simulato)
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('Contrassegno', this)">
                <input type="radio" name="metodo" value="Contrassegno"> 🚚 Pagamento alla consegna
            </div>
        </div>

        <!-- Campi carta -->
        <div id="campos-carta" class="card-fields">
            <label class="checkout-label">Numero carta</label>
            <input type="text" id="cc-numero" class="checkout-input" placeholder="1234 5678 9012 3456" maxlength="19">
            <div class="err-msg" id="err-numero">Inserisci un numero carta valido (16 cifre).</div>
            <div class="input-row">
                <div>
                    <label class="checkout-label">Scadenza</label>
                    <input type="text" id="cc-scadenza" class="checkout-input" placeholder="MM/AA" maxlength="5">
                    <div class="err-msg" id="err-scadenza">Formato non valido o carta scaduta.</div>
                </div>
                <div>
                    <label class="checkout-label">CVV</label>
                    <input type="text" id="cc-cvv" class="checkout-input" placeholder="123" maxlength="3">
                    <div class="err-msg" id="err-cvv">Il CVV deve essere di 3 cifre.</div>
                </div>
            </div>
            <label style="font-size:0.9em; cursor:pointer;">
                <input type="checkbox" id="salva-carta" style="accent-color:var(--primary-green);"> &nbsp;Salva questa carta per i prossimi acquisti
            </label>
        </div>

        <!-- Campi PayPal -->
        <div id="campos-paypal" class="card-fields">
            <label class="checkout-label">Email PayPal</label>
            <input type="email" id="pp-email" class="checkout-input" placeholder="tuoemail@paypal.com">
            <label style="font-size:0.9em; cursor:pointer;">
                <input type="checkbox" id="salva-paypal" style="accent-color:var(--primary-green);"> &nbsp;Salva per i prossimi acquisti
            </label>
        </div>

        <!-- Rate info -->
        <div id="campos-rate" class="card-fields">
            <div style="background:#fff3cd; padding:15px; border-radius:8px;">
                💡 L'importo verrà diviso in 3 rate mensili uguali senza interessi (simulato).
            </div>
        </div>

        <!-- Contrassegno info -->
        <div id="campos-contrassegno" class="card-fields">
            <div style="background:#fff3cd; padding:15px; border-radius:8px;">
                🚚 Pagherai in contanti al corriere alla consegna. Potrebbe essere applicato un sovrapprezzo.
            </div>
        </div>

        <button class="btn-recensisci" style="width:100%; padding:15px; font-size:1.05em; margin-top:10px;" onclick="procediAlPagamento()">
            Procedi al pagamento →
        </button>
    </div>

    <!-- STEP 2: RIEPILOGO + CONFERMA -->
    <div id="step-pagamento" class="checkout-box">
        <h2 class="step-title">✅ Conferma il tuo ordine</h2>
        <p class="step-sub">Verifica i dettagli prima di confermare.</p>

        <div class="recap-box">
            <div class="recap-row"><span>Spedizione a:</span><strong id="recap-indirizzo"></strong></div>
            <div class="recap-row"><span>Metodo:</span><strong id="recap-metodo"></strong></div>
            <div class="recap-row totale-finale"><span>Totale:</span><span id="recap-totale"></span></div>
        </div>

        <div id="recap-prodotti" style="margin-bottom:25px;"></div>

        <button class="btn-recensisci" style="width:100%; padding:15px; font-size:1.05em;" onclick="confermaPagamento()">
            💳 PAGA ORA — Conferma Ordine
        </button>
        <button onclick="$('#step-pagamento').hide(); $('#step-indirizzo').show();"
            style="width:100%; padding:12px; margin-top:10px; background:none; border:1px solid var(--border-color); border-radius:8px; cursor:pointer; color:var(--text-sec);">
            ← Torna indietro
        </button>
    </div>

</div>

<script>
let datiOrdine = { indirizzo: '', metodo: '', totale: 0, salvaDati: false, datiMetodo: {} };
let metodiSalvati = [];

$(document).ready(function() {

    // Carica indirizzo predefinito dal profilo
    $.get('api/ba_get_profilo.php', function(resp) {
        if(resp.status === 'ok' && resp.dettagli.indirizzo_predefinito) {
            const parts = resp.dettagli.indirizzo_predefinito.split(',');
            if(parts[0]) $('#campo-via').val(parts[0].trim());
            if(parts[1]) $('#campo-citta').val(parts[1].trim());
            if(parts[2]) $('#campo-cap').val(parts[2].trim());
            if(parts[3]) $('#campo-provincia').val(parts[3].trim());
        }
    });

    // Carica riepilogo carrello
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        if(resp.status === 'ok') {
            if(resp.prodotti.length === 0) { window.location.href = 'carrello.php'; return; }
            let html = '';
            resp.prodotti.forEach(p => {
                html += `<div class="riepilogo-riga"><span>${p.nome} × ${p.quantita}</span><span>€${parseFloat(p.subtotale).toFixed(2)}</span></div>`;
            });
            html += `<div class="riepilogo-riga totale-finale"><span>Totale</span><span>€${parseFloat(resp.totaleCart).toFixed(2)}</span></div>`;
            $('#riepilogo-carrello').html(html);
            datiOrdine.totale = resp.totaleCart;

            // Salva anche per il recap
            datiOrdine.prodotti = resp.prodotti;
            datiOrdine.totaleCart = resp.totaleCart;
        }
    }, 'json');

    // Carica metodi salvati
    $.get('api/ba_metodi_pagamento_cliente.php', { action: 'list' }, function(resp) {
        if(resp.status === 'ok' && resp.metodi.length > 0) {
            metodiSalvati = resp.metodi;
            let html = '<p style="font-weight:600; margin-bottom:10px; font-size:0.9em; color:var(--text-sec);">Metodi salvati:</p>';
            resp.metodi.forEach(m => {
                html += `
                <div class="metodo-card" onclick="selezionaMetodoSalvato(${m.id_pagamento}, '${m.metodo}', '${m.dati}', this)">
                    <input type="radio" name="metodo"> ${iconaMetodo(m.metodo)} ${m.metodo}
                    <span class="metodo-saved">${m.dati}</span>
                </div>`;
            });
            html += '<p style="font-weight:600; margin:15px 0 10px; font-size:0.9em; color:var(--text-sec);">Oppure aggiungi nuovo:</p>';
            $('#lista-metodi-salvati').html(html);
        }
    }, 'json');

    // Formattazione automatica numero carta
    $('#cc-numero').on('input', function() {
        let v = $(this).val().replace(/\D/g, '').substring(0, 16);
        $(this).val(v.replace(/(.{4})/g, '$1 ').trim());
    });

    // Formattazione scadenza MM/AA
    $('#cc-scadenza').on('input', function() {
        let v = $(this).val().replace(/\D/g, '').substring(0, 4);
        if(v.length > 2) v = v.substring(0,2) + '/' + v.substring(2);
        $(this).val(v);
    });

    // Solo cifre CVV
    $('#cc-cvv').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, '').substring(0,3));
    });

    // Solo cifre CAP
    $('#campo-cap').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, '').substring(0,5));
    });
});

function iconaMetodo(m) {
    const icons = { 'Carta': '💳', 'PayPal': '🅿️', 'ApplePay': '', 'Rate': '📅', 'Contrassegno': '🚚' };
    return icons[m] || '💳';
}

let metodoCorrente = '';

function selezionaMetodo(metodo, el) {
    $('.metodo-card').removeClass('selected');
    $(el).addClass('selected');
    $(el).find('input[type=radio]').prop('checked', true);
    metodoCorrente = metodo;
    $('#campos-carta, #campos-paypal, #campos-rate, #campos-contrassegno').hide();
    if(metodo === 'Carta') $('#campos-carta').show();
    else if(metodo === 'PayPal') $('#campos-paypal').show();
    else if(metodo === 'Rate') $('#campos-rate').show();
    else if(metodo === 'Contrassegno') $('#campos-contrassegno').show();
}

function selezionaMetodoSalvato(id, metodo, dati, el) {
    $('.metodo-card').removeClass('selected');
    $(el).addClass('selected');
    metodoCorrente = metodo;
    datiOrdine.idPagamentoSalvato = id;
    $('#campos-carta, #campos-paypal, #campos-rate, #campos-contrassegno').hide();
}

function validaCarta() {
    let ok = true;
    const numero = $('#cc-numero').val().replace(/\s/g, '');
    if(numero.length !== 16) { $('#err-numero').show(); $('#cc-numero').addClass('error'); ok = false; }
    else { $('#err-numero').hide(); $('#cc-numero').removeClass('error'); }

    const scad = $('#cc-scadenza').val();
    const scadMatch = scad.match(/^(\d{2})\/(\d{2})$/);
    if(!scadMatch) { $('#err-scadenza').show(); $('#cc-scadenza').addClass('error'); ok = false; }
    else {
        const mese = parseInt(scadMatch[1]);
        const anno = 2000 + parseInt(scadMatch[2]);
        const now = new Date();
        if(mese < 1 || mese > 12 || anno < now.getFullYear() || (anno === now.getFullYear() && mese < now.getMonth()+1)) {
            $('#err-scadenza').show(); $('#cc-scadenza').addClass('error'); ok = false;
        } else { $('#err-scadenza').hide(); $('#cc-scadenza').removeClass('error'); }
    }

    const cvv = $('#cc-cvv').val();
    if(cvv.length !== 3) { $('#err-cvv').show(); $('#cc-cvv').addClass('error'); ok = false; }
    else { $('#err-cvv').hide(); $('#cc-cvv').removeClass('error'); }

    return ok;
}

function procediAlPagamento() {
    const via = $('#campo-via').val().trim();
    const citta = $('#campo-citta').val().trim();
    const cap = $('#campo-cap').val().trim();
    const prov = $('#campo-provincia').val().trim();

    if(!via || !citta || !cap) { alert('Inserisci via, città e CAP.'); return; }
    if(cap.length !== 5) { alert('Il CAP deve essere di 5 cifre.'); return; }
    if(!metodoCorrente) { alert('Seleziona un metodo di pagamento.'); return; }

    if(metodoCorrente === 'Carta' && !datiOrdine.idPagamentoSalvato) {
        if(!validaCarta()) return;
    }

    const indirizzo = `${via}, ${citta}, ${cap}${prov ? ', ' + prov : ''}`;
    datiOrdine.indirizzo = indirizzo;
    datiOrdine.metodo = metodoCorrente;

    // Salva metodo se richiesto
    if(metodoCorrente === 'Carta' && $('#salva-carta').is(':checked')) {
        const ultime4 = $('#cc-numero').val().replace(/\s/g,'').slice(-4);
        $.post('api/ba_metodi_pagamento_cliente.php', {
            action: 'add', metodo: 'Carta', dati: 'Carta che termina con ' + ultime4
        });
    }
    if(metodoCorrente === 'PayPal' && $('#salva-paypal').is(':checked') && $('#pp-email').val()) {
        $.post('api/ba_metodi_pagamento_cliente.php', {
            action: 'add', metodo: 'PayPal', dati: $('#pp-email').val()
        });
    }

    // Aggiorna indirizzo predefinito se diverso
    $.post('api/ba_aggiorna_profilo.php', { indirizzo: indirizzo });

    // Mostra recap
    $('#recap-indirizzo').text(indirizzo);
    $('#recap-metodo').text(iconaMetodo(metodoCorrente) + ' ' + metodoCorrente);
    $('#recap-totale').text('€' + parseFloat(datiOrdine.totale).toFixed(2));

    let rpHtml = '';
    if(datiOrdine.prodotti) {
        datiOrdine.prodotti.forEach(p => {
            rpHtml += `<div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #eee;">
                <img src="${p.URLfoto}" style="width:45px; height:60px; object-fit:cover; border-radius:5px;">
                <div><strong>${p.nome}</strong><br><small style="color:var(--text-sec);">× ${p.quantita} — €${parseFloat(p.subtotale).toFixed(2)}</small></div>
            </div>`;
        });
    }
    $('#recap-prodotti').html(rpHtml);

    $('#step-indirizzo').hide();
    $('#step-pagamento').show();
}

function confermaPagamento() {
    if(!confirm('Confermi il pagamento?')) return;
    $.post('api/ba_processa_ordine.php', {
        indirizzo: datiOrdine.indirizzo,
        metodo: datiOrdine.metodo
    }, function(resp) {
        if(resp.status === 'ok') {
            alert('✅ Ordine #' + resp.idOrdine + ' confermato e pagato!');
            window.location.href = 'miei_ordini.php';
        } else {
            alert('Errore: ' + resp.msg);
        }
    }, 'json');
}
</script>
</body>
</html>