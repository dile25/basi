<?php
/*
 * head.php — parti comuni dell'<head>, incluse in ogni pagina con include.
 * jQuery viene caricato UNA sola volta qui (prima era caricato sia dalla pagina sia da header.php).
 * Risorsa esterna: jQuery 3.6.0 da CDN ufficiale (da segnalare nella relazione).
 */
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
/* Escape dei caratteri speciali HTML prima di inserire dati degli utenti nel DOM:
   equivalente lato client di htmlspecialchars() (prevenzione XSS). */
function escapeHtml(valore) {
    return String(valore ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatPrezzo(n) {
    return '€' + (parseFloat(n) || 0).toFixed(2);
}

/* Popup di notifica riutilizzato da tutte le pagine */
function mostraNotifica(msg, isErrore) {
    $('.popup-overlay').remove();
    const overlay = $(`
        <div class="popup-overlay" role="alertdialog" aria-modal="true" aria-labelledby="popup-msg">
            <div class="popup-box">
                <div class="popup-icon ${isErrore ? 'popup-icon--err' : 'popup-icon--ok'}" aria-hidden="true">${isErrore ? '&#10007;' : '&#10003;'}</div>
                <p class="popup-msg" id="popup-msg">${escapeHtml(msg)}</p>
                <button type="button" class="btn btn-primary popup-ok">OK</button>
            </div>
        </div>`);
    $('body').append(overlay);
    overlay.find('.popup-ok').trigger('focus');
    overlay.on('click', function(e) {
        if ($(e.target).is('.popup-overlay') || $(e.target).is('.popup-ok')) overlay.remove();
    });
}

/* Gestore globale degli errori AJAX: $.get/$.post eseguono la callback solo in caso
   di successo, quindi gli errori di rete o le risposte non JSON vengono intercettati qui. */
$(document).ajaxError(function(evento, xhr) {
    mostraNotifica('Errore di comunicazione con il server (' + (xhr.status || 'rete') + '). Riprova.', true);
});
</script>
