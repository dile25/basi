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
    <title>Checkout | The (E-)Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include("header.php"); ?>

<div class="container">

    <!-- STEP 1: INDIRIZZO + METODO -->
    <div id="step-indirizzo" class="checkout-box">
        <h2 class="step-title">Completa il tuo ordine</h2>
        <p class="step-sub">Controlla i dati prima di procedere al pagamento.</p>

        <div class="riepilogo" id="riepilogo-carrello">
            <p style="color:var(--text-sec);">Caricamento...</p>
        </div>

        <label class="checkout-label">Indirizzo di spedizione
            <span id="hint-indirizzo" style="display:none; font-size:0.78em; font-weight:400; color:var(--primary-green); margin-left:8px;">
                ✓ precompilato dal profilo — puoi modificarlo
            </span>
        </label>
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
                <input type="radio" name="metodo" value="Carta">
                <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--dark-green)"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                Carta di Credito / Debito
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('PayPal', this)">
                <input type="radio" name="metodo" value="PayPal">
                <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#003087"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.312 2.48 1.007 4.274-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.118zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.59 3.025-2.566 6.082-8.558 6.082H9.824l-1.226 7.78h3.397c.46 0 .85-.335.92-.788l.04-.196.733-4.649.047-.256a.932.932 0 0 1 .92-.788h.58c3.754 0 6.694-1.524 7.552-5.932.358-1.84.173-3.375-.565-4.455-.21-.298-.45-.558-.72-.778z"/></svg>
                PayPal
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('ApplePay', this)">
                <input type="radio" name="metodo" value="ApplePay">
                <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#000000"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/></svg>
                Apple Pay
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('GooglePay', this)">
                <input type="radio" name="metodo" value="GooglePay">
                <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.99 0C5.364 0 0 5.364 0 11.99 0 18.62 5.364 24 11.99 24 18.62 24 24 18.62 24 11.99 24 5.364 18.62 0 11.99 0zm4.974 12.25c0 2.87-1.925 4.854-4.83 4.854a4.998 4.998 0 0 1-5.006-5.004c0-2.77 2.235-5.004 5.006-5.004 1.351 0 2.483.497 3.352 1.314l-1.362 1.362c-.37-.356-.998-.77-1.99-.77-1.704 0-3.094 1.413-3.094 3.098s1.39 3.098 3.094 3.098c1.978 0 2.72-1.42 2.836-2.155h-2.836v-1.787h4.743c.044.25.087.5.087.994z" fill="#4285F4"/></svg>
                Google Pay
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('Rate', this)">
                <input type="radio" name="metodo" value="Rate">
                <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--dark-green)"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                Paga in 3 rate
            </div>
            <div class="metodo-card" onclick="selezionaMetodo('Contrassegno', this)">
                <input type="radio" name="metodo" value="Contrassegno">
                <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--dark-green)"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2a3 3 0 0 0 3 3 3 3 0 0 0 3-3h6a3 3 0 0 0 3 3 3 3 0 0 0 3-3h2v-5l-3-4zm-.5 1.5 1.96 2.5H17V9.5h2.5zM6 18a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm2.22-3a3.01 3.01 0 0 0-4.44 0H3V6h12v9H8.22zM18 18a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>
                Pagamento alla consegna
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
                    <input type="text" id="cc-scadenza" class="checkout-input" placeholder="MM/AAAA" maxlength="7" style="margin-bottom:0;">
                    <div class="err-msg" id="err-scadenza" style="margin-top:6px; position:static;">Formato non valido o carta scaduta.</div>
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

        <!-- Campi Rate: stessa carta ma con info rata mensile -->
        <div id="campos-rate" class="card-fields">
            <div style="background:#fff3cd; padding:12px; border-radius:8px; margin-bottom:12px; font-size:0.9em;">
                💡 L'importo verrà diviso in 3 rate mensili uguali senza interessi (simulato). Inserisci la carta con cui pagare le rate.
            </div>
            <label class="checkout-label">Numero carta</label>
            <input type="text" id="rate-numero" class="checkout-input" placeholder="1234 5678 9012 3456" maxlength="19">
            <div class="err-msg" id="err-rate-numero">Inserisci un numero carta valido (16 cifre).</div>
            <div class="input-row">
                <div>
                    <label class="checkout-label">Scadenza</label>
                    <input type="text" id="rate-scadenza" class="checkout-input" placeholder="MM/AAAA" maxlength="7" style="margin-bottom:0;">
                    <div class="err-msg" id="err-rate-scadenza" style="margin-top:6px; position:static;">Formato non valido o carta scaduta.</div>
                </div>
                <div>
                    <label class="checkout-label">CVV</label>
                    <input type="text" id="rate-cvv" class="checkout-input" placeholder="123" maxlength="3">
                    <div class="err-msg" id="err-rate-cvv">Il CVV deve essere di 3 cifre.</div>
                </div>
            </div>
            <label style="font-size:0.9em; cursor:pointer;">
                <input type="checkbox" id="salva-rate" style="accent-color:var(--primary-green);"> &nbsp;Salva questa carta per i prossimi acquisti
            </label>
        </div>

        <!-- Apple Pay / Google Pay: account associato -->
        <div id="campos-digitalpay" class="card-fields">
            <label class="checkout-label" id="label-digitalpay">Account associato</label>
            <input type="text" id="digitalpay-account" class="checkout-input" placeholder="es. nome@icloud.com">
            <div class="err-msg" id="err-digitalpay">Inserisci l'account associato.</div>
            <label style="font-size:0.9em; cursor:pointer;">
                <input type="checkbox" id="salva-digitalpay" style="accent-color:var(--primary-green);"> &nbsp;Salva per i prossimi acquisti
            </label>
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
        <h2 class="step-title" style="display:flex;align-items:center;gap:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--dark-green)" style="width:26px;height:26px;flex-shrink:0;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
            Conferma il tuo ordine
        </h2>
        <p class="step-sub">Verifica i dettagli prima di confermare.</p>

        <div class="recap-box" style="border-radius:12px; padding:22px;">
            <div class="recap-row" style="align-items:flex-start; gap:12px;">
                <span style="color:var(--text-sec); white-space:nowrap;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--dark-green)" style="width:15px;height:15px;vertical-align:middle;margin-right:4px;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    Spedizione a:
                </span>
                <strong id="recap-indirizzo" style="text-align:right;"></strong>
            </div>
            <hr style="border:0;border-top:1px solid var(--border-color);margin:12px 0;">
            <div class="recap-row" style="align-items:center; gap:12px;">
                <span style="color:var(--text-sec);">Metodo di pagamento:</span>
                <strong id="recap-metodo" style="display:flex;align-items:center;gap:6px;"></strong>
            </div>
            <hr style="border:0;border-top:1px solid var(--border-color);margin:12px 0;">
            <div class="recap-row totale-finale" style="margin-top:4px;">
                <span>Totale:</span>
                <span id="recap-totale" style="color:var(--dark-green);font-size:1.3em;font-weight:800;"></span>
            </div>
        </div>

        <div id="recap-prodotti" style="margin-bottom:25px; background:white; border:1px solid var(--border-color); border-radius:12px; overflow:hidden;"></div>

        <button class="btn-recensisci" style="width:100%; padding:16px; font-size:1.05em; display:flex; align-items:center; justify-content:center; gap:10px; border-radius:10px;" onclick="confermaPagamento()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:20px;height:20px;flex-shrink:0;"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
            Paga ora — Conferma ordine
        </button>
        <button onclick="$('#step-pagamento').hide(); $('#step-indirizzo').show();"
            style="width:100%; padding:12px; margin-top:10px; background:none; border:1px solid var(--border-color); border-radius:8px; cursor:pointer; color:var(--text-sec); font-family:inherit;">
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
        if(resp.status === 'ok' && resp.dettagli && resp.dettagli.indirizzo_predefinito) {
            const ind = resp.dettagli.indirizzo_predefinito.trim();
            if (!ind) return;
            const parts = ind.split(',').map(s => s.trim());
            if(parts[0]) $('#campo-via').val(parts[0]);
            if(parts[1]) $('#campo-citta').val(parts[1]);
            if(parts[2]) $('#campo-cap').val(parts[2]);
            if(parts[3]) $('#campo-provincia').val(parts[3]);
            $('#hint-indirizzo').show();
        }
    }, 'json');

    // Carica riepilogo carrello
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        if(resp.status === 'ok') {
            if(resp.prodotti.length === 0) { window.location.href = 'carrello.php'; return; }

            // Controlla se arriva da un piano di abbonamento
            const abbRaw = sessionStorage.getItem('abbonamento_selezionato');
            const abb    = abbRaw ? JSON.parse(abbRaw) : null;

            let html = '';
            datiOrdine.prodotti    = resp.prodotti;
            datiOrdine.totaleCart  = resp.totaleCart;
            datiOrdine.abbonamento = abb || null;

            if (abb) {
                const sconto         = parseFloat(abb.sconto) || 0;
                // 12 mesi: mensile=12 uscite, settimanale=52 uscite
                const numUscite      = abb.periodicita === 'settimanale' ? 52 : 12;
                const perioLabel     = abb.periodicita === 'settimanale' ? 'numeri settimanali' : 'numeri mensili';
                const prezzoUnit     = parseFloat(abb.prezzoProdotto) || parseFloat(resp.prodotti[0]?.prezzoOriginale || 0);
                const nomeProdotto   = abb.nomeProdotto  || resp.prodotti[0]?.nome   || 'Abbonamento';
                const fotoProdotto   = abb.fotoProdotto  || resp.prodotti[0]?.URLfoto || 'img/default.jpg';
                const prezzoScontato = prezzoUnit * (1 - sconto / 100);
                const totaleAbb      = prezzoScontato * numUscite;

                html += `<div style="background:#f5eef8; border:1px solid #d2b4de; border-radius:8px; padding:10px 14px; margin-bottom:12px; color:#6c3483; font-size:0.88em; font-weight:600;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8e44ad" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                    Piano: <strong>${abb.nomeAbb}</strong> — ${numUscite} ${perioLabel} con sconto ${sconto}%
                    <button onclick="sessionStorage.removeItem('abbonamento_selezionato'); location.reload();"
                        style="float:right; background:none; border:none; color:#8e44ad; cursor:pointer; font-size:0.85em; text-decoration:underline; font-family:inherit;">
                        Rimuovi
                    </button>
                </div>`;
                html += `<div class="riepilogo-riga">
                    <span>${nomeProdotto} × ${numUscite} ${perioLabel}</span>
                    <span>
                        <small style="text-decoration:line-through; color:#bbb; margin-right:6px;">€${(prezzoUnit * numUscite).toFixed(2)}</small>
                        <strong style="color:#8e44ad;">€${totaleAbb.toFixed(2)}</strong>
                    </span>
                </div>`;
                html += `<div class="riepilogo-riga" style="font-size:0.82em; color:#8e44ad;">
                    <span>Sconto abbonamento ${sconto}%</span>
                    <span>−€${(prezzoUnit * numUscite - totaleAbb).toFixed(2)}</span>
                </div>`;
                html += `<div class="riepilogo-riga totale-finale"><span>Totale</span><span>€${totaleAbb.toFixed(2)}</span></div>`;

                datiOrdine.totale       = totaleAbb.toFixed(2);
                datiOrdine.numUscite    = numUscite;
                datiOrdine.prezzoUnit   = prezzoUnit;
                datiOrdine.nomeProdotto = nomeProdotto;
                datiOrdine.fotoProdotto = fotoProdotto;
                datiOrdine.abbonamento  = abb;

            } else {
                resp.prodotti.forEach(p => {
                    html += `<div class="riepilogo-riga"><span>${p.nome} × ${p.quantita}</span><span>€${parseFloat(p.subtotale).toFixed(2)}</span></div>`;
                });
                html += `<div class="riepilogo-riga totale-finale"><span>Totale</span><span>€${parseFloat(resp.totaleCart).toFixed(2)}</span></div>`;
                datiOrdine.totale = resp.totaleCart;
            }

            $('#riepilogo-carrello').html(html);
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

    // Formattazione automatica scadenza MM/AAAA (inserisce la "/" da sola dopo il mese)
    $('#cc-scadenza').on('input', function() {
        let v = $(this).val().replace(/\D/g, '').substring(0, 6);
        if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2);
        $(this).val(v);
    });

    // Solo cifre CVV
    $('#cc-cvv').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, '').substring(0,3));
    });

    // Stessa formattazione per i campi Rate
    $('#rate-numero').on('input', function() {
        let v = $(this).val().replace(/\D/g, '').substring(0, 16);
        $(this).val(v.replace(/(.{4})/g, '$1 ').trim());
    });
    $('#rate-scadenza').on('input', function() {
        let v = $(this).val().replace(/\D/g, '').substring(0, 6);
        if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2);
        $(this).val(v);
    });
    $('#rate-cvv').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, '').substring(0,3));
    });

    // Solo cifre CAP
    $('#campo-cap').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, '').substring(0,5));
    });
});

function iconaMetodo(m) {
    const svgCarta = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;vertical-align:middle;"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>';
    const svgCamion = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;vertical-align:middle;"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2a3 3 0 0 0 6 0h6a3 3 0 0 0 6 0h2v-5l-3-4zm-2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2zM6 18a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm11.5-9.5 1.96 2.5H17V8.5h.5z"/></svg>';
    const svgCal = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;vertical-align:middle;"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>';
    const icons = {
        'Carta': svgCarta,
        'PayPal': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#003087" style="width:16px;height:16px;vertical-align:middle;"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.312 2.48 1.007 4.274-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.118zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.59 3.025-2.566 6.082-8.558 6.082H9.824l-1.226 7.78h3.397c.46 0 .85-.335.92-.788l.733-4.649a.932.932 0 0 1 .92-.788h.58c3.754 0 6.694-1.524 7.552-5.932.358-1.84.173-3.375-.565-4.455z"/></svg>',
        'ApplePay': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#000" style="width:16px;height:16px;vertical-align:middle;"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-3.182-1.221-3.22-4.857.026-6.374-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/></svg>',
        'GooglePay': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:middle;"><path d="M11.99 0C5.364 0 0 5.364 0 11.99 0 18.62 5.364 24 11.99 24 18.62 24 24 18.62 24 11.99 24 5.364 18.62 0 11.99 0zm4.974 12.25c0 2.87-1.925 4.854-4.83 4.854a4.998 4.998 0 0 1-5.006-5.004c0-2.77 2.235-5.004 5.006-5.004 1.351 0 2.483.497 3.352 1.314l-1.362 1.362c-.37-.356-.998-.77-1.99-.77-1.704 0-3.094 1.413-3.094 3.098s1.39 3.098 3.094 3.098c1.978 0 2.72-1.42 2.836-2.155h-2.836v-1.787h4.743c.044.25.087.5.087.994z" fill="#4285F4"/></svg>',
        'Rate': svgCal,
        'Contrassegno': svgCamion
    };
    return icons[m] || svgCarta;
}

let metodoCorrente = '';

function selezionaMetodo(metodo, el) {
    $('.metodo-card').removeClass('selected');
    $(el).addClass('selected');
    $(el).find('input[type=radio]').prop('checked', true);
    metodoCorrente = metodo;
    datiOrdine.idPagamentoSalvato = null;
    $('#campos-carta, #campos-paypal, #campos-rate, #campos-contrassegno, #campos-digitalpay').hide();
    if (metodo === 'Carta') $('#campos-carta').show();
    else if (metodo === 'PayPal') $('#campos-paypal').show();
    else if (metodo === 'Rate') $('#campos-rate').show();
    else if (metodo === 'Contrassegno') $('#campos-contrassegno').show();
    else if (metodo === 'ApplePay' || metodo === 'GooglePay') {
        $('#label-digitalpay').text(metodo === 'ApplePay' ? 'Apple ID (es. nome@icloud.com)' : 'Account Google (es. nome@gmail.com)');
        $('#digitalpay-account').attr('placeholder', metodo === 'ApplePay' ? 'nome@icloud.com' : 'nome@gmail.com');
        $('#campos-digitalpay').show();
    }
}

function selezionaMetodoSalvato(id, metodo, dati, el) {
    $('.metodo-card').removeClass('selected');
    $(el).addClass('selected');
    metodoCorrente = metodo;
    datiOrdine.idPagamentoSalvato = id;
    $('#campos-carta, #campos-paypal, #campos-rate, #campos-contrassegno, #campos-digitalpay').hide();
}

function validaCarta() {
    let ok = true;
    const numero = $('#cc-numero').val().replace(/\s/g, '');
    if(numero.length !== 16) { $('#err-numero').show(); $('#cc-numero').addClass('error'); ok = false; }
    else { $('#err-numero').hide(); $('#cc-numero').removeClass('error'); }

    const scad = $('#cc-scadenza').val();
    const scadMatch = scad.match(/^(\d{2})\/(\d{4})$/);
    if (!scadMatch) {
        $('#err-scadenza').text('Inserisci la scadenza nel formato MM/AAAA (es. 09/2027).').show();
        $('#cc-scadenza').addClass('error'); ok = false;
    } else {
        const mese = parseInt(scadMatch[1], 10);
        const anno = parseInt(scadMatch[2], 10);

        if (mese < 1 || mese > 12) {
            $('#err-scadenza').text('Il mese deve essere compreso tra 01 e 12.').show();
            $('#cc-scadenza').addClass('error'); ok = false;
        } else {
            const now = new Date();
            const annoCorrente = now.getFullYear();
            const meseCorrente = now.getMonth() + 1; // getMonth() è 0-indexed

            const ePassata = (anno < annoCorrente) || (anno === annoCorrente && mese < meseCorrente);
            if (ePassata) {
                $('#err-scadenza').text('La carta risulta già scaduta. Inserisci una data futura rispetto a oggi.').show();
                $('#cc-scadenza').addClass('error'); ok = false;
            } else {
                $('#err-scadenza').hide(); $('#cc-scadenza').removeClass('error');
            }
        }
    }

    const cvv = $('#cc-cvv').val();
    if(cvv.length !== 3) { $('#err-cvv').show(); $('#cc-cvv').addClass('error'); ok = false; }
    else { $('#err-cvv').hide(); $('#cc-cvv').removeClass('error'); }

    return ok;
}

function validaRate() {
    let ok = true;
    const numero = $('#rate-numero').val().replace(/\s/g, '');
    if (numero.length !== 16) { $('#err-rate-numero').show(); $('#rate-numero').addClass('error'); ok = false; }
    else { $('#err-rate-numero').hide(); $('#rate-numero').removeClass('error'); }

    const scad = $('#rate-scadenza').val();
    const scadMatch = scad.match(/^(\d{2})\/(\d{4})$/);
    if (!scadMatch) {
        $('#err-rate-scadenza').text('Inserisci la scadenza nel formato MM/AAAA.').show();
        $('#rate-scadenza').addClass('error'); ok = false;
    } else {
        const mese = parseInt(scadMatch[1], 10);
        const anno = parseInt(scadMatch[2], 10);
        if (mese < 1 || mese > 12) {
            $('#err-rate-scadenza').text('Il mese deve essere tra 01 e 12.').show();
            $('#rate-scadenza').addClass('error'); ok = false;
        } else {
            const now = new Date();
            if (anno < now.getFullYear() || (anno === now.getFullYear() && mese < now.getMonth() + 1)) {
                $('#err-rate-scadenza').text('La carta risulta già scaduta.').show();
                $('#rate-scadenza').addClass('error'); ok = false;
            } else {
                $('#err-rate-scadenza').hide(); $('#rate-scadenza').removeClass('error');
            }
        }
    }

    const cvv = $('#rate-cvv').val();
    if (cvv.length !== 3) { $('#err-rate-cvv').show(); $('#rate-cvv').addClass('error'); ok = false; }
    else { $('#err-rate-cvv').hide(); $('#rate-cvv').removeClass('error'); }

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

    // Validazione campi per metodo selezionato (solo se non si usa un metodo salvato)
    if (!datiOrdine.idPagamentoSalvato) {
        if (metodoCorrente === 'Carta') {
            if (!validaCarta()) return;
        } else if (metodoCorrente === 'Rate') {
            if (!validaRate()) return;
        } else if (metodoCorrente === 'ApplePay' || metodoCorrente === 'GooglePay') {
            const account = $('#digitalpay-account').val().trim();
            if (!account) { $('#err-digitalpay').show(); return; }
            $('#err-digitalpay').hide();
        }
    }

    const indirizzo = `${via}, ${citta}, ${cap}${prov ? ', ' + prov : ''}`;
    datiOrdine.indirizzo = indirizzo;
    datiOrdine.metodo = metodoCorrente;

    // Salva metodo se richiesto
    if (metodoCorrente === 'Carta' && $('#salva-carta').is(':checked')) {
        const ultime4 = $('#cc-numero').val().replace(/\s/g,'').slice(-4);
        $.post('api/ba_metodi_pagamento_cliente.php', {
            action: 'add', metodo: 'Carta', dati: 'Carta che termina con ' + ultime4
        });
    }
    if (metodoCorrente === 'Rate' && $('#salva-rate').is(':checked')) {
        const ultime4 = $('#rate-numero').val().replace(/\s/g,'').slice(-4);
        $.post('api/ba_metodi_pagamento_cliente.php', {
            action: 'add', metodo: 'Rate', dati: 'Rate — Carta che termina con ' + ultime4
        });
    }
    if (metodoCorrente === 'PayPal' && $('#salva-paypal').is(':checked') && $('#pp-email').val()) {
        $.post('api/ba_metodi_pagamento_cliente.php', {
            action: 'add', metodo: 'PayPal', dati: $('#pp-email').val()
        });
    }
    if ((metodoCorrente === 'ApplePay' || metodoCorrente === 'GooglePay') && $('#salva-digitalpay').is(':checked')) {
        const account = $('#digitalpay-account').val().trim();
        if (account) {
            $.post('api/ba_metodi_pagamento_cliente.php', {
                action: 'add', metodo: metodoCorrente, dati: account
            });
        }
    }

    // Aggiorna indirizzo predefinito se diverso
    $.ajax({ url: 'api/ba_aggiorna_profilo.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ indirizzo: indirizzo }) });

    // Mostra recap
    $('#recap-indirizzo').text(indirizzo);
    // Mostra solo il nome del metodo (no SVG HTML che verrebbe stampato come testo)
    const nomiMetodo = { 'Carta':'Carta di Credito/Debito', 'PayPal':'PayPal', 'ApplePay':'Apple Pay', 'GooglePay':'Google Pay', 'Rate':'Paga in 3 rate', 'Contrassegno':'Pagamento alla consegna' };
    $('#recap-metodo').text(nomiMetodo[metodoCorrente] || metodoCorrente);
    $('#recap-totale').text('€' + parseFloat(datiOrdine.totale).toFixed(2));

    let rpHtml = '';
    if(datiOrdine.abbonamento) {
        const a            = datiOrdine.abbonamento;
        // 12 mesi: mensile=12 uscite, settimanale=52 uscite
        const numUscite    = (a.periodicita || datiOrdine.abbonamento?.periodicita) === 'settimanale' ? 52 : 12;
        const prezzoUnit   = datiOrdine.prezzoUnit || parseFloat(a.prezzoProdotto) || 0;
        const sconto       = parseFloat(a.sconto) || 0;
        const prezzoSc     = prezzoUnit * (1 - sconto / 100);
        const totaleAbb    = prezzoSc * numUscite;
        const perioLabel   = a.periodicita === 'settimanale' ? 'numeri settimanali' : 'numeri mensili';

        rpHtml += `<div style="background:#f5eef8; border-bottom:1px solid #e8d5f0; padding:12px 18px; color:#6c3483; font-size:0.88em; font-weight:600;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8e44ad" style="width:13px;height:13px;vertical-align:middle;margin-right:4px;"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
            ${a.nomeAbb} — ${numUscite} ${perioLabel}, sconto ${sconto}%
        </div>`;

        if(datiOrdine.prodotti && datiOrdine.prodotti[0]) {
            const foto = datiOrdine.fotoProdotto || datiOrdine.prodotti[0].URLfoto || 'img/default.jpg';
            const nome = datiOrdine.nomeProdotto || datiOrdine.prodotti[0].nome || 'Abbonamento';
            rpHtml += `<div style="display:flex; align-items:center; gap:14px; padding:14px 18px; border-bottom:1px solid #f0f0f0;">
                <img src="${foto}" style="width:48px; height:64px; object-fit:cover; border-radius:6px; border:1px solid #eee; flex-shrink:0;">
                <div style="flex-grow:1;">
                    <div style="font-weight:700; font-size:0.95em;">${nome}</div>
                    <div style="font-size:0.82em; color:var(--text-sec);">× ${numUscite} ${perioLabel}</div>
                    <div style="font-size:0.8em; color:#bbb; text-decoration:line-through;">€${(prezzoUnit * numUscite).toFixed(2)} senza sconto</div>
                </div>
                <div style="font-weight:700; color:#8e44ad;">€${totaleAbb.toFixed(2)}</div>
            </div>`;
        }
    } else if(datiOrdine.prodotti) {
        datiOrdine.prodotti.forEach(p => {
            rpHtml += `<div style="display:flex; align-items:center; gap:14px; padding:14px 18px; border-bottom:1px solid #f0f0f0;">
                <img src="${p.URLfoto || 'img/default.jpg'}" style="width:48px; height:64px; object-fit:cover; border-radius:6px; border:1px solid #eee; flex-shrink:0;">
                <div style="flex-grow:1;">
                    <div style="font-weight:700; font-size:0.95em;">${p.nome}</div>
                    <div style="font-size:0.82em; color:var(--text-sec);">× ${p.quantita}</div>
                </div>
                <div style="font-weight:700; color:var(--dark-green);">€${parseFloat(p.subtotale).toFixed(2)}</div>
            </div>`;
        });
    }
    $('#recap-prodotti').html(rpHtml);

    $('#step-indirizzo').hide();
    $('#step-pagamento').show();
}

function confermaPagamento() {
    if(!confirm('Confermi il pagamento?')) return;

    const postData = {
        indirizzo: datiOrdine.indirizzo,
        metodo:    datiOrdine.metodo
    };

    if (datiOrdine.abbonamento) {
        const a = datiOrdine.abbonamento;
        postData.abb_idPacchetto    = a.idPacchetto   || '';
        postData.abb_nomeAbb        = a.nomeAbb        || '';
        postData.abb_sconto         = parseFloat(a.sconto)       || 0;
        postData.abb_numUscite      = (a.periodicita === 'settimanale') ? 52 : 12;
        postData.abb_periodicita    = a.periodicita    || '';
        postData.abb_prezzoProdotto = parseFloat(datiOrdine.prezzoUnit || a.prezzoProdotto) || 0;
        postData.abb_nomeProdotto   = datiOrdine.nomeProdotto || a.nomeProdotto || '';
    }


    $.post('api/ba_processa_ordine.php', postData, function(resp) {
        if(resp.status === 'ok') {
            sessionStorage.removeItem('abbonamento_selezionato');
            alert('Ordine #' + resp.idOrdine + ' confermato e pagato!');
            window.location.href = 'miei_ordini.php';
        } else {
            alert('Errore: ' + resp.msg);
        }
    }, 'json');
}
</script>
</body>
</html>