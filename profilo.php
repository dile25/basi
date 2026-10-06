<?php
session_start();
if (!isset($_SESSION['IdUtente'])) {
    header("Location: login.php");
    exit;
}
$tipoUtente = $_SESSION['tipoUtente'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Il mio profilo | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page page--medium">
    <header class="profile-header">
        <h1 id="user-fullname">Caricamento...</h1>
        <span id="user-type" class="tag-tipo"></span>
    </header>

    <div class="dash-grid">
        <!-- INFORMAZIONI PERSONALI -->
        <section class="info-card" aria-labelledby="titolo-info">
            <div class="card-header">
                <h2 id="titolo-info">Informazioni personali</h2>
                <button type="button" class="btn-modifica js-toggle" data-target="#form-modifica" aria-expanded="false">Modifica</button>
            </div>
            <dl class="dati-lista">
                <dt>Username</dt><dd id="p-username"></dd>
                <dt>Email</dt><dd id="p-email"></dd>
                <?php if ($tipoUtente === 'cliente'): ?><dt>Telefono</dt><dd id="p-telefono"></dd><?php endif; ?>
            </dl>

            <form id="form-modifica" class="form-modifica-section" novalidate>
                <div class="form-field">
                    <label for="edit-username" class="form-label">Username</label>
                    <input type="text" id="edit-username" class="form-control" maxlength="30" autocomplete="username">
                    <small class="field-error" id="err-username"></small>
                </div>
                <div class="form-field">
                    <label for="edit-email" class="form-label">Email</label>
                    <input type="email" id="edit-email" class="form-control" maxlength="100" autocomplete="email">
                    <small class="field-error" id="err-email"></small>
                </div>
                <?php if ($tipoUtente === 'cliente'): ?>
                <div class="form-field">
                    <label for="edit-telefono" class="form-label">Telefono</label>
                    <input type="tel" id="edit-telefono" class="form-control" maxlength="16" autocomplete="tel">
                    <small class="field-error" id="err-telefono"></small>
                </div>
                <?php endif; ?>
                <div class="form-field">
                    <label for="edit-password" class="form-label">Nuova password</label>
                    <input type="password" id="edit-password" class="form-control" autocomplete="new-password" aria-describedby="help-pwd">
                    <small class="help-text" id="help-pwd">Lascia vuoto per non cambiarla.</small>
                    <small class="field-error" id="err-password"></small>
                </div>
                <button type="submit" class="btn btn-primary">Salva modifiche</button>
                <p class="form-msg is-hidden" id="msg-dati" role="status"></p>
            </form>
        </section>

        <!-- DETTAGLI ACCOUNT -->
        <section class="info-card" aria-labelledby="titolo-account">
            <h2 id="titolo-account">Dettagli account</h2>
            <p><strong>Membro dal:</strong> <span id="p-data-reg"></span></p>
            <?php if ($tipoUtente === 'venditore'): ?>
                <a class="btn-account-link" href="dashboard_venditore.php">Vai alla dashboard</a>
            <?php elseif ($tipoUtente === 'cliente'): ?>
                <a class="btn-account-link" href="preferiti.php">I miei preferiti</a>
                <a class="btn-account-link" href="miei_ordini.php">I miei ordini</a>
                <a class="btn-account-link" href="carrello.php">Il mio carrello</a>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($tipoUtente === 'venditore'): ?>
    <!-- DATI AZIENDALI -->
    <section class="info-card" aria-labelledby="titolo-azienda">
        <div class="card-header">
            <h2 id="titolo-azienda">Dati aziendali</h2>
            <button type="button" class="btn-modifica js-toggle" data-target="#form-modifica-venditore" aria-expanded="false">Modifica</button>
        </div>
        <dl class="dati-lista">
            <dt>Ragione sociale</dt><dd id="p-ragione-sociale">—</dd>
            <dt>Partita IVA</dt><dd id="p-partita-iva">—</dd>
        </dl>
        <form id="form-modifica-venditore" class="form-modifica-section" novalidate>
            <div class="form-field">
                <label for="edit-ragione-sociale" class="form-label">Ragione sociale</label>
                <input type="text" id="edit-ragione-sociale" class="form-control" maxlength="100">
            </div>
            <div class="form-field">
                <label for="edit-partita-iva" class="form-label">Partita IVA</label>
                <input type="text" id="edit-partita-iva" class="form-control" maxlength="11" inputmode="numeric">
            </div>
            <button type="submit" class="btn btn-primary">Salva</button>
            <p class="form-msg is-hidden" id="msg-venditore" role="status"></p>
        </form>
    </section>
    <?php endif; ?>

    <?php if ($tipoUtente === 'cliente'): ?>
    <!-- INDIRIZZO -->
    <section class="info-card" aria-labelledby="titolo-indirizzo">
        <div class="card-header">
            <h2 id="titolo-indirizzo">Indirizzo di spedizione</h2>
            <button type="button" class="btn-modifica js-toggle" data-target="#form-modifica-indirizzo" aria-expanded="false">Modifica</button>
        </div>
        <p id="p-indirizzo-full">Nessun indirizzo salvato</p>
        <form id="form-modifica-indirizzo" class="form-modifica-section" novalidate>
            <div class="form-field">
                <label for="edit-via" class="form-label">Via e numero civico</label>
                <input type="text" id="edit-via" class="form-control" maxlength="100">
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="edit-citta" class="form-label">Città</label>
                    <input type="text" id="edit-citta" class="form-control" maxlength="60">
                </div>
                <div class="form-field">
                    <label for="edit-cap" class="form-label">CAP</label>
                    <input type="text" id="edit-cap" class="form-control" maxlength="5" inputmode="numeric">
                </div>
                <div class="form-field">
                    <label for="edit-provincia" class="form-label">Provincia</label>
                    <input type="text" id="edit-provincia" class="form-control" maxlength="2">
                </div>
            </div>
            <small class="field-error" id="err-indirizzo"></small>
            <button type="submit" class="btn btn-primary">Salva indirizzo</button>
            <p class="form-msg is-hidden" id="msg-indirizzo" role="status"></p>
        </form>
    </section>

    <?php endif; ?>

    <!-- ELIMINA ACCOUNT -->
    <section class="info-card danger-zone" aria-labelledby="titolo-elimina">
        <h2 id="titolo-elimina">Elimina account</h2>
        <p class="muted">L'operazione è definitiva: il tuo account e i tuoi dati non potranno essere recuperati.</p>
        <button type="button" class="btn btn-danger-outline" id="btnApriElimina">Elimina il mio account</button>
    </section>

    <div id="modalElimina" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="titolo-modal-elimina">
        <div class="modal-box modal-box--small">
            <h2 class="modal-title" id="titolo-modal-elimina">Eliminare l'account?</h2>
            <p class="muted">Per confermare, scrivi il tuo username.</p>
            <div class="form-field">
                <label for="conferma-username" class="form-label">Username</label>
                <input type="text" id="conferma-username" class="form-control" autocomplete="off">
                <small class="field-error" id="msg-elimina-err"></small>
            </div>
            <div class="button-row">
                <button type="button" class="btn btn-danger" id="btnConfermaElimina">Elimina definitivamente</button>
                <button type="button" class="btn btn-secondary" id="btnChiudiElimina">Annulla</button>
            </div>
        </div>
    </div>
</main>

<script>

/* Le API del profilo leggono un corpo JSON (php://input): con jQuery si usa
   $.ajax indicando contentType e serializzando i dati con JSON.stringify */
function postJson(url, dati, callback) {
    return $.ajax({
        url: url, method: 'POST', dataType: 'json',
        contentType: 'application/json', data: JSON.stringify(dati),
        success: callback
    });
}

function mostraMsg(sel, testo, ok) {
    $(sel).text(testo).toggleClass('form-msg--ok', ok).toggleClass('form-msg--err', !ok).removeClass('is-hidden');
}

$(function() {
    $.get('api/ba_get_profilo.php', function(data) {
        if (data.status !== 'ok') return;
        const a = data.anagrafica || {};
        const d = data.dettagli   || {};

        $('#user-fullname').text(((a.nome || '') + ' ' + (a.cognome || '')).trim() || a.username);
        $('#user-type').text(data.tipo).addClass(data.tipo === 'venditore' ? 'venditore-tag' : 'cliente-tag');
        $('#p-username').text(a.username);
        $('#p-email').text(a.email);
        $('#edit-username').val(a.username);
        $('#edit-email').val(a.email);
        $('#p-data-reg').text(a.data_registrazione
            ? new Date(a.data_registrazione).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' })
            : 'N/D');

        if (data.tipo === 'cliente') {
            $('#p-telefono').text(d.telefono || 'Non inserito');
            $('#edit-telefono').val(d.telefono || '');
            if (d.indirizzo_predefinito) {
                $('#p-indirizzo-full').text(d.indirizzo_predefinito);
                const parti = d.indirizzo_predefinito.split(',').map(s => s.trim());
                $('#edit-via').val(parti[0] || '');
                $('#edit-citta').val(parti[1] || '');
                $('#edit-cap').val(parti[2] || '');
                $('#edit-provincia').val(parti[3] || '');
            }
        } else if (data.tipo === 'venditore') {
            $('#p-ragione-sociale').text(d.ragione_sociale || '—');
            $('#p-partita-iva').text(d.partita_iva || '—');
            $('#edit-ragione-sociale').val(d.ragione_sociale || '');
            $('#edit-partita-iva').val(d.partita_iva || '');
        }
    }, 'json');
});

/* Apertura/chiusura delle sezioni di modifica */
$('.js-toggle').on('click', function() {
    const aperto = $($(this).data('target')).toggleClass('open').hasClass('open');
    $(this).attr('aria-expanded', aperto);
});

/* ===== Dati personali ===== */
$('#form-modifica').on('submit', function(e) {
    e.preventDefault();
    const username = $('#edit-username').val().trim();
    const email    = $('#edit-email').val().trim();
    const telefono = ($('#edit-telefono').val() || '').trim();
    const password = $('#edit-password').val();
    let ok = true;

    $('#err-username').text(/^[a-zA-Z0-9_\-]{3,30}$/.test(username) ? '' : 'Da 3 a 30 caratteri: lettere, numeri, _ o -.');
    $('#err-email').text(/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email) ? '' : 'Email non valida.');
    $('#err-telefono').text(!$('#edit-telefono').length || /^(\+39)?\d{6,11}$/.test(telefono.replace(/[\s\-]/g, '')) ? '' : 'Numero di telefono non valido.');
    $('#err-password').text(!password || /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password) ? '' : 'Almeno 8 caratteri, una maiuscola, un numero e un simbolo.');
    $(this).find('.field-error').each(function() { if ($(this).text()) ok = false; });
    if (!ok) return;

    const dati = { username: username, email: email, password: password };
    if ($('#edit-telefono').length) dati.telefono = telefono;   // solo i clienti hanno il telefono
    postJson('api/ba_aggiorna_profilo.php', dati, function(resp) {
        if (resp.status === 'ok') {
            $('#p-username').text(username);
            $('#p-email').text(email);
            $('#p-telefono').text(telefono || 'Non inserito');
            $('#edit-password').val('');
            mostraMsg('#msg-dati', 'Dati aggiornati.', true);
        } else {
            mostraMsg('#msg-dati', resp.msg || 'Salvataggio non riuscito.', false);
        }
    });
});

/* ===== Dati aziendali ===== */
$('#form-modifica-venditore').on('submit', function(e) {
    e.preventDefault();
    const rs   = $('#edit-ragione-sociale').val().trim();
    const piva = $('#edit-partita-iva').val().trim();
    if (rs.length < 2)           { mostraMsg('#msg-venditore', 'Inserisci la ragione sociale.', false); return; }
    if (!/^\d{11}$/.test(piva))  { mostraMsg('#msg-venditore', 'La partita IVA è composta da 11 cifre.', false); return; }

    postJson('api/ba_aggiorna_profilo.php', { ragione_sociale: rs, partita_iva: piva }, function(resp) {
        if (resp.status === 'ok') {
            $('#p-ragione-sociale').text(rs);
            $('#p-partita-iva').text(piva);
            mostraMsg('#msg-venditore', 'Dati aggiornati.', true);
        } else {
            mostraMsg('#msg-venditore', resp.msg || 'Salvataggio non riuscito.', false);
        }
    });
});

/* ===== Indirizzo ===== */
$('#form-modifica-indirizzo').on('submit', function(e) {
    e.preventDefault();
    const via   = $('#edit-via').val().trim();
    const citta = $('#edit-citta').val().trim();
    const cap   = $('#edit-cap').val().trim();
    const prov  = $('#edit-provincia').val().trim().toUpperCase();
    if (!via || !citta || !/^\d{5}$/.test(cap) || (prov && !/^[A-Z]{2}$/.test(prov))) {
        $('#err-indirizzo').text('Inserisci via, città e un CAP di 5 cifre (provincia: 2 lettere).');
        return;
    }
    $('#err-indirizzo').text('');
    const indirizzo = `${via}, ${citta}, ${cap}${prov ? ', ' + prov : ''}`;
    postJson('api/ba_aggiorna_profilo.php', { indirizzo: indirizzo }, function(resp) {
        if (resp.status === 'ok') {
            $('#p-indirizzo-full').text(indirizzo);
            mostraMsg('#msg-indirizzo', 'Indirizzo aggiornato.', true);
        } else {
            mostraMsg('#msg-indirizzo', resp.msg || 'Salvataggio non riuscito.', false);
        }
    });
});

$('#edit-cap').on('input', function() {
    $(this).val($(this).val().replace(/\D/g, '').substring(0, 5));
});

/* ===== Eliminazione account ===== */
$('#btnApriElimina').on('click', function() {
    $('#conferma-username').val('');
    $('#msg-elimina-err').text('');
    $('#modalElimina').addClass('open');
    $('#conferma-username').trigger('focus');
});
$('#btnChiudiElimina').on('click', () => $('#modalElimina').removeClass('open'));

$('#btnConfermaElimina').on('click', function() {
    const input = $('#conferma-username').val().trim();
    if (!input) { $('#msg-elimina-err').text('Inserisci il tuo username.'); return; }
    postJson('api/ba_elimina_account.php', { conferma: input }, function(resp) {
        if (resp.status === 'ok') {
            alert('Account eliminato.');
            window.location.href = 'index.php';
        } else {
            $('#msg-elimina-err').text(resp.msg || 'Eliminazione non riuscita.');
        }
    });
});
</script>
</body>
</html>
