<?php
session_start();
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'cliente') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Pagamento | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page">
    <!-- STEP 1: INDIRIZZO + METODO -->
    <section id="step-indirizzo" class="checkout-box" aria-labelledby="titolo-step1">
        <h1 class="step-title" id="titolo-step1">Completa il tuo ordine</h1>
        <p class="step-sub">Controlla i dati prima di procedere al pagamento.</p>

        <div class="riepilogo" id="riepilogo-carrello"><p class="loading">Caricamento...</p></div>

        <fieldset class="checkout-fieldset">
            <legend class="checkout-label">Indirizzo di spedizione
                <span id="hint-indirizzo" class="hint is-hidden">precompilato dal profilo, puoi modificarlo</span>
            </legend>
            <div class="form-field">
                <label for="campo-via" class="form-label">Via e numero civico</label>
                <input type="text" id="campo-via" class="form-control" maxlength="100" autocomplete="address-line1" required>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="campo-citta" class="form-label">Città</label>
                    <input type="text" id="campo-citta" class="form-control" maxlength="60" autocomplete="address-level2" required>
                </div>
                <div class="form-field">
                    <label for="campo-cap" class="form-label">CAP</label>
                    <input type="text" id="campo-cap" class="form-control" maxlength="5" inputmode="numeric" autocomplete="postal-code" required>
                </div>
                <div class="form-field">
                    <label for="campo-provincia" class="form-label">Provincia</label>
                    <input type="text" id="campo-provincia" class="form-control" maxlength="2" placeholder="es. PI">
                </div>
            </div>
            <small class="field-error" id="err-indirizzo"></small>
        </fieldset>

        <fieldset class="checkout-fieldset">
            <legend class="checkout-label">Metodo di pagamento</legend>
            <div id="metodo-nuovo">
                <label class="metodo-card">
                    <input type="radio" name="metodo" value="Carta">
                    <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                    Carta di credito / debito
                </label>
                <label class="metodo-card">
                    <input type="radio" name="metodo" value="PayPal">
                    <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.312 2.48 1.007 4.274-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.118zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.59 3.025-2.566 6.082-8.558 6.082H9.824l-1.226 7.78h3.397c.46 0 .85-.335.92-.788l.04-.196.733-4.649.047-.256a.932.932 0 0 1 .92-.788h.58c3.754 0 6.694-1.524 7.552-5.932.358-1.84.173-3.375-.565-4.455-.21-.298-.45-.558-.72-.778z"/></svg>
                    PayPal
                </label>
            </div>
            <small class="field-error" id="err-metodo"></small>
        </fieldset>

        <!-- Campi carta -->
        <div id="campi-carta" class="card-fields is-hidden">
            <div class="form-field">
                <label for="cc-numero" class="form-label">Numero carta</label>
                <input type="text" id="cc-numero" class="form-control" maxlength="19" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456">
                <small class="field-error" id="err-numero"></small>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="cc-scadenza" class="form-label">Scadenza</label>
                    <input type="text" id="cc-scadenza" class="form-control" maxlength="7" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/AAAA">
                    <small class="field-error" id="err-scadenza"></small>
                </div>
                <div class="form-field">
                    <label for="cc-cvv" class="form-label">CVV</label>
                    <input type="text" id="cc-cvv" class="form-control" maxlength="3" inputmode="numeric" autocomplete="cc-csc" placeholder="123">
                    <small class="field-error" id="err-cvv"></small>
                </div>
            </div>
            <p class="help-text">Pagamento simulato: i dati della carta vengono solo controllati e non sono salvati.</p>
        </div>

        <div id="campi-paypal" class="card-fields is-hidden">
            <div class="form-field">
                <label for="pp-email" class="form-label">Email PayPal</label>
                <input type="email" id="pp-email" class="form-control" maxlength="100" autocomplete="email">
                <small class="field-error" id="err-pp"></small>
            </div>
        </div>

        <button type="button" class="btn btn-primary btn-block btn-large" id="btnProcedi">Continua</button>
    </section>

    <!-- STEP 2: RIEPILOGO + CONFERMA -->
    <section id="step-pagamento" class="checkout-box is-hidden" aria-labelledby="titolo-step2">
        <h1 class="step-title" id="titolo-step2">Conferma il tuo ordine</h1>
        <p class="step-sub">Verifica i dettagli prima di confermare.</p>
        <div class="recap-box">
            <div class="recap-row"><span>Spedizione a</span><strong id="recap-indirizzo"></strong></div>
            <div class="recap-row"><span>Metodo di pagamento</span><strong id="recap-metodo"></strong></div>
            <div class="recap-row totale-finale"><span>Totale</span><span id="recap-totale"></span></div>
        </div>
        <div id="recap-prodotti"></div>
        <button type="button" class="btn btn-primary btn-block btn-large" id="btnConferma">Paga e conferma l'ordine</button>
        <button type="button" class="btn btn-link btn-block" id="btnIndietro">Torna indietro</button>
    </section>
</main>

<script>
const NOMI_METODO = { Carta: 'Carta di credito/debito', PayPal: 'PayPal' };
let datiOrdine = { indirizzo: '', metodo: '', prodotti: [], totale: 0 };

$(function() {
    // Indirizzo predefinito dal profilo (formato "via, città, CAP, provincia")
    $.get('api/ba_get_profilo.php', function(resp) {
        const ind = (resp.dettagli && resp.dettagli.indirizzo_predefinito || '').trim();
        if (!ind) return;
        const parti = ind.split(',').map(s => s.trim());
        $('#campo-via').val(parti[0] || '');
        $('#campo-citta').val(parti[1] || '');
        $('#campo-cap').val(parti[2] || '');
        $('#campo-provincia').val(parti[3] || '');
        $('#hint-indirizzo').removeClass('is-hidden');
    }, 'json');

    // Riepilogo: il totale è calcolato dal server (sconti pacchetto inclusi)
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        const prodotti = resp.prodotti || [];
        if (prodotti.length === 0) { window.location.href = 'carrello.php'; return; }
        datiOrdine.prodotti = prodotti;
        datiOrdine.totale   = resp.totaleCart;
        let html = '';
        prodotti.forEach(p => {
            html += `<div class="riepilogo-riga"><span>${escapeHtml(p.nome)} &times; ${parseInt(p.quantita)}</span><span>${formatPrezzo(p.subtotale)}</span></div>`;
        });
        html += `<div class="riepilogo-riga totale-finale"><span>Totale</span><span>${formatPrezzo(resp.totaleCart)}</span></div>`;
        $('#riepilogo-carrello').html(html);
    }, 'json');

    // Formattazione automatica dei campi carta
    $('#cc-numero').on('input', function() {
        const v = $(this).val().replace(/\D/g, '').substring(0, 16);
        $(this).val(v.replace(/(.{4})/g, '$1 ').trim());
    });
    $('#cc-scadenza').on('input', function() {
        let v = $(this).val().replace(/\D/g, '').substring(0, 6);
        if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2);
        $(this).val(v);
    });
    $('#cc-cvv, #campo-cap').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, '').substring(0, parseInt($(this).attr('maxlength'))));
    });
});

/* Selezione del metodo (radio dentro label: accessibile anche da tastiera) */
$(document).on('change', 'input[name="metodo"]', function() {
    const metodo = $(this).val();
    $('.metodo-card').removeClass('selected');
    $(this).closest('.metodo-card').addClass('selected');
    $('#err-metodo').text('');
    datiOrdine.metodo = metodo;
    $('#campi-carta').toggleClass('is-hidden', metodo !== 'Carta');
    $('#campi-paypal').toggleClass('is-hidden', metodo !== 'PayPal');
});

function erroreCampo(idCampo, idErrore, msg) {
    $('#' + idErrore).text(msg);
    $('#' + idCampo).toggleClass('invalid', !!msg).attr('aria-invalid', msg ? 'true' : 'false');
    return !msg;
}

function validaCarta() {
    let ok = true;
    const numero = $('#cc-numero').val().replace(/\s/g, '');
    ok = erroreCampo('cc-numero', 'err-numero', /^\d{16}$/.test(numero) ? '' : 'Inserisci le 16 cifre della carta.') && ok;

    let msgScad = '';
    const m = $('#cc-scadenza').val().match(/^(\d{2})\/(\d{4})$/);
    if (!m) {
        msgScad = 'Usa il formato MM/AAAA (es. 09/2027).';
    } else {
        const mese = parseInt(m[1], 10), anno = parseInt(m[2], 10), oggi = new Date();
        if (mese < 1 || mese > 12) msgScad = 'Il mese deve essere tra 01 e 12.';
        else if (anno < oggi.getFullYear() || (anno === oggi.getFullYear() && mese < oggi.getMonth() + 1)) msgScad = 'La carta risulta scaduta.';
    }
    ok = erroreCampo('cc-scadenza', 'err-scadenza', msgScad) && ok;
    ok = erroreCampo('cc-cvv', 'err-cvv', /^\d{3}$/.test($('#cc-cvv').val()) ? '' : 'Il CVV è di 3 cifre.') && ok;
    return ok;
}

const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

$('#btnProcedi').on('click', function() {
    const via   = $('#campo-via').val().trim();
    const citta = $('#campo-citta').val().trim();
    const cap   = $('#campo-cap').val().trim();
    const prov  = $('#campo-provincia').val().trim().toUpperCase();

    if (!via || !citta || !/^\d{5}$/.test(cap) || (prov && !/^[A-Z]{2}$/.test(prov))) {
        $('#err-indirizzo').text('Inserisci via, città e un CAP di 5 cifre (provincia: 2 lettere).');
        return;
    }
    $('#err-indirizzo').text('');
    if (!datiOrdine.metodo) { $('#err-metodo').text('Scegli un metodo di pagamento.'); return; }

    const metodo = datiOrdine.metodo;
    if (metodo === 'Carta' && !validaCarta()) return;
    if (metodo === 'PayPal' && !erroreCampo('pp-email', 'err-pp', REGEX_EMAIL.test($('#pp-email').val().trim()) ? '' : 'Inserisci un\'email PayPal valida.')) return;

    datiOrdine.indirizzo = `${via}, ${citta}, ${cap}${prov ? ', ' + prov : ''}`;

    // Aggiorna l'indirizzo predefinito del profilo
    $.ajax({
        url: 'api/ba_aggiorna_profilo.php', method: 'POST', dataType: 'json',
        contentType: 'application/json', data: JSON.stringify({ indirizzo: datiOrdine.indirizzo })
    });

    $('#recap-indirizzo').text(datiOrdine.indirizzo);
    $('#recap-metodo').text(NOMI_METODO[metodo] || metodo);
    $('#recap-totale').text(formatPrezzo(datiOrdine.totale));

    let html = '';
    datiOrdine.prodotti.forEach(p => {
        html += `<div class="recap-item">
            <img src="${escapeHtml(p.URLfoto || 'img/default.jpg')}" alt="">
            <div class="recap-item__body"><strong>${escapeHtml(p.nome)}</strong><span class="muted">&times; ${parseInt(p.quantita)}</span></div>
            <strong>${formatPrezzo(p.subtotale)}</strong>
        </div>`;
    });
    $('#recap-prodotti').html(html);

    $('#step-indirizzo').addClass('is-hidden');
    $('#step-pagamento').removeClass('is-hidden');
    $('#titolo-step2').attr('tabindex', '-1').trigger('focus');
});

$('#btnIndietro').on('click', function() {
    $('#step-pagamento').addClass('is-hidden');
    $('#step-indirizzo').removeClass('is-hidden');
});

$('#btnConferma').on('click', function() {
    if (!confirm('Confermi il pagamento?')) return;
    const btn = $(this).prop('disabled', true);
    // Il server ricalcola prezzi, sconti e disponibilità: dal client arrivano solo indirizzo e metodo
    $.post('api/ba_processa_ordine.php', {
        indirizzo: datiOrdine.indirizzo,
        metodo: datiOrdine.metodo
    }, function(resp) {
        if (resp.status === 'ok') {
            alert('Ordine #' + resp.idOrdine + ' confermato.');
            window.location.href = 'miei_ordini.php';
        } else {
            btn.prop('disabled', false);
            mostraNotifica(resp.msg || 'Pagamento non riuscito.', true);
        }
    }, 'json').fail(function() { btn.prop('disabled', false); });
});
</script>
</body>
</html>