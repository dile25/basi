<?php
session_start();
if (isset($_SESSION['IdUtente'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Crea un account | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body class="auth-page">
<main id="contenuto" class="auth-container">
    <h1 class="auth-title">Crea il tuo account</h1>

    <form id="formRegistrazione" novalidate>
        <fieldset class="role-selector">
            <legend class="form-label">Voglio registrarmi come</legend>
            <label class="role-option">
                <input type="radio" name="tipoUtente" value="cliente" checked>
                <span>Cliente</span>
            </label>
            <label class="role-option">
                <input type="radio" name="tipoUtente" value="venditore">
                <span>Venditore</span>
            </label>
        </fieldset>

        <div class="form-field">
            <label for="field-username" class="form-label">Username</label>
            <input type="text" name="username" id="field-username" class="form-control" maxlength="30" autocomplete="username" aria-describedby="err-username" required>
            <small class="field-error" id="err-username"></small>
        </div>
        <div class="form-row">
            <div class="form-field">
                <label for="field-nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="field-nome" class="form-control" maxlength="50" autocomplete="given-name" aria-describedby="err-nome" required>
                <small class="field-error" id="err-nome"></small>
            </div>
            <div class="form-field">
                <label for="field-cognome" class="form-label">Cognome</label>
                <input type="text" name="cognome" id="field-cognome" class="form-control" maxlength="50" autocomplete="family-name" aria-describedby="err-cognome" required>
                <small class="field-error" id="err-cognome"></small>
            </div>
        </div>
        <div class="form-field">
            <label for="field-email" class="form-label">Email</label>
            <input type="email" name="email" id="field-email" class="form-control" maxlength="100" autocomplete="email" aria-describedby="err-email" required>
            <small class="field-error" id="err-email"></small>
        </div>
        <div class="form-field">
            <label for="field-password" class="form-label">Password</label>
            <input type="password" name="password" id="field-password" class="form-control" autocomplete="new-password" aria-describedby="help-password err-password" required>
            <small class="help-text" id="help-password">Almeno 8 caratteri, con una maiuscola, un numero e un simbolo.</small>
            <small class="field-error" id="err-password"></small>
        </div>

        <!-- Campi solo per CLIENTE -->
        <div id="cliente-fields">
            <div class="form-field">
                <label for="field-telefono" class="form-label">Telefono</label>
                <input type="tel" name="telefono" id="field-telefono" class="form-control" maxlength="16" autocomplete="tel" placeholder="es. 3201234567" aria-describedby="err-telefono">
                <small class="field-error" id="err-telefono"></small>
            </div>
            <div class="form-field">
                <label for="field-indirizzo" class="form-label">Indirizzo</label>
                <input type="text" name="indirizzo" id="field-indirizzo" class="form-control" maxlength="150" autocomplete="street-address" placeholder="es. Via Roma 1, Pisa, 56126" aria-describedby="err-indirizzo">
                <small class="field-error" id="err-indirizzo"></small>
            </div>
        </div>

        <!-- Campi solo per VENDITORE -->
        <div id="vendor-fields" class="is-hidden">
            <div class="form-field">
                <label for="field-ragione" class="form-label">Ragione sociale</label>
                <input type="text" name="ragione_sociale" id="field-ragione" class="form-control" maxlength="100" aria-describedby="err-ragione">
                <small class="field-error" id="err-ragione"></small>
            </div>
            <div class="form-field">
                <label for="field-piva" class="form-label">Partita IVA</label>
                <input type="text" name="partita_iva" id="field-piva" class="form-control" maxlength="11" inputmode="numeric" placeholder="11 cifre" aria-describedby="err-piva">
                <small class="field-error" id="err-piva"></small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Crea account</button>
    </form>

    <p class="auth-footer">Hai già un account? <a href="login.php">Accedi</a></p>
    <p class="auth-footer"><a href="index.php">Torna alla home</a></p>
</main>

<script>
/* ===== Validazione lato client (la stessa va ripetuta lato server) ===== */
function tipoSelezionato() {
    return $('input[name="tipoUtente"]:checked').val();
}

$('input[name="tipoUtente"]').on('change', function() {
    const venditore = tipoSelezionato() === 'venditore';
    $('#vendor-fields').toggleClass('is-hidden', !venditore);
    $('#cliente-fields').toggleClass('is-hidden', venditore);
});

function setErr(id, msg) {
    const campo = $('#field-' + id);
    $('#err-' + id).text(msg);
    campo.toggleClass('invalid', !!msg)
         .toggleClass('valid', !msg && campo.val().trim() !== '')
         .attr('aria-invalid', msg ? 'true' : 'false');
    return !!msg;
}

function validaUsername(v) {
    if (!v) return 'Inserisci uno username.';
    if (!/^[a-zA-Z0-9_\-]{3,30}$/.test(v)) return 'Da 3 a 30 caratteri: solo lettere, numeri, _ o -.';
    return '';
}
function validaNome(v, label) {
    if (!v) return 'Inserisci il ' + label + '.';
    if (v.length < 2) return label.charAt(0).toUpperCase() + label.slice(1) + ' troppo corto (minimo 2 caratteri).';
    if (!/^[a-zA-ZÀ-ÿ\s'\-]+$/.test(v)) return 'Usa solo lettere, spazi, apostrofi o trattini.';
    return '';
}
function validaEmail(v) {
    if (!v) return "Inserisci l'email.";
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Email non valida (es. nome@dominio.it).';
    return '';
}
function validaPassword(v) {
    if (!v) return 'Inserisci una password.';
    if (!/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(v))
        return 'Almeno 8 caratteri, con una maiuscola, un numero e un simbolo.';
    return '';
}
function validaTelefono(v) {
    if (!v) return 'Inserisci il numero di telefono.';
    const pulito = v.replace(/[\s\-]/g, '');
    if (!/^(\+39)?\d{6,11}$/.test(pulito)) return 'Numero non valido (es. 3201234567 o +39 050 123456).';
    return '';
}
function validaIndirizzo(v) {
    if (!v) return "Inserisci l'indirizzo.";
    if (v.length < 5) return 'Indirizzo troppo corto (minimo 5 caratteri).';
    return '';
}
function validaPiva(v) {
    if (!v) return 'Inserisci la partita IVA.';
    if (!/^\d{11}$/.test(v)) return 'La partita IVA è composta da 11 cifre.';
    return '';
}
function validaRagione(v) {
    if (!v) return 'Inserisci la ragione sociale.';
    if (v.length < 2) return 'Ragione sociale troppo corta (minimo 2 caratteri).';
    return '';
}

const validatori = {
    username:  () => validaUsername($('#field-username').val().trim()),
    nome:      () => validaNome($('#field-nome').val().trim(), 'nome'),
    cognome:   () => validaNome($('#field-cognome').val().trim(), 'cognome'),
    email:     () => validaEmail($('#field-email').val().trim()),
    password:  () => validaPassword($('#field-password').val()),
    telefono:  () => validaTelefono($('#field-telefono').val().trim()),
    indirizzo: () => validaIndirizzo($('#field-indirizzo').val().trim()),
    ragione:   () => validaRagione($('#field-ragione').val().trim()),
    piva:      () => validaPiva($('#field-piva').val().trim())
};

// Validazione campo per campo all'uscita dal campo
Object.keys(validatori).forEach(id => {
    $('#field-' + id).on('blur', function() { setErr(id, validatori[id]()); });
});

$('#formRegistrazione').on('submit', function(e) {
    e.preventDefault();
    const campi = ['username', 'nome', 'cognome', 'email', 'password'].concat(
        tipoSelezionato() === 'cliente' ? ['telefono', 'indirizzo'] : ['ragione', 'piva']
    );
    let errori = false;
    campi.forEach(id => { if (setErr(id, validatori[id]())) errori = true; });
    if (errori) {
        $('.form-control.invalid').first().trigger('focus');
        return;
    }

    $.post('api/ba_registrazione.php', $(this).serialize(), function(resp) {
        if (resp.status === 'ok') {
            window.location.href = 'index.php';
        } else {
            mostraNotifica(resp.msg || 'Registrazione non riuscita.', true);
        }
    }, 'json');
});
</script>
</body>
</html>