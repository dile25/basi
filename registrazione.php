<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Unisciti a The (E-)Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .auth-container { max-width: 400px; margin: 80px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid var(--primary-green); }
        .auth-input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid var(--border-color); border-radius: 8px; box-sizing: border-box; }
        .role-selector { display: flex; gap: 10px; margin-bottom: 20px; }
        .role-option { flex: 1; text-align: center; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .role-option.active { background: var(--dark-green); color: white; border-color: var(--dark-green); }
        .field-error { color: #e74c3c; font-size: 0.78em; display: block; margin: -10px 0 10px 2px; min-height: 14px; }
        .auth-input.invalid { border-color: #e74c3c; }
        .auth-input.valid { border-color: var(--primary-green); }
    </style>
</head>
<body style="background: var(--light-green);">

    <div class="auth-container">
        <h2 style="color: var(--dark-green); text-align: center; margin-bottom: 25px;">Crea il tuo Account</h2>

        <form id="formRegistrazione">
            <input type="text" name="username" placeholder="Username (3-30 caratteri)" class="auth-input" id="field-username" required>
            <small class="field-error" id="err-username"></small>
            <input type="text" name="nome" placeholder="Nome" class="auth-input" id="field-nome" required>
            <small class="field-error" id="err-nome"></small>
            <input type="text" name="cognome" placeholder="Cognome" class="auth-input" id="field-cognome" required>
            <small class="field-error" id="err-cognome"></small>
            <input type="email" name="email" placeholder="Email" class="auth-input" id="field-email" required>
            <small class="field-error" id="err-email"></small>
            <input type="password" name="password" placeholder="Password (min 8 car., maiuscola, numero, simbolo)" class="auth-input" id="field-password" required>
            <small class="field-error" id="err-password"></small>

            <!-- Campi solo per CLIENTE -->
            <div id="cliente-fields">
                <input type="tel" name="telefono" placeholder="Telefono (es. 3201234567)" class="auth-input" id="field-telefono">
                <small class="field-error" id="err-telefono"></small>
                <input type="text" name="indirizzo" placeholder="Indirizzo (es. Via Roma 1, Milano)" class="auth-input" id="field-indirizzo">
                <small class="field-error" id="err-indirizzo"></small>
            </div>

            <!-- Campi solo per VENDITORE -->
            <div id="vendor-fields" style="display:none;">
                <input type="text" name="ragione_sociale" placeholder="Ragione Sociale" class="auth-input" id="field-ragione">
                <small class="field-error" id="err-ragione"></small>
                <input type="text" name="partita_iva" placeholder="Partita IVA (11 cifre)" class="auth-input" id="field-piva">
                <small class="field-error" id="err-piva"></small>
            </div>

            <p style="font-size: 0.8em; margin-bottom: 10px; color: #666;">Voglio registrarmi come:</p>
            <div class="role-selector">
                <div class="role-option active" onclick="setRole('cliente', this)">Cliente</div>
                <div class="role-option" onclick="setRole('venditore', this)">Venditore</div>
            </div>
            <input type="hidden" name="tipoUtente" id="tipoUtente" value="cliente">

            <button type="submit" class="btn-recensisci" style="width: 100%; padding: 12px;">REGISTRATI</button>
        </form>

        <p style="text-align:center; margin-top:20px; font-size:0.9em;">Hai già un account? <a href="login.php">Accedi qui</a></p>
        <p style="text-align:center; margin-top:10px; font-size:0.9em;">
    <a href="index.php" style="color: var(--text-sec);">Torna alla home</a>
</p>
    </div>

    <script>
    function setRole(role, el) {
        $('.role-option').removeClass('active');
        $(el).addClass('active');
        $('#tipoUtente').val(role);
        if (role === 'venditore') {
            $('#vendor-fields').slideDown();
            $('#cliente-fields').slideUp();
        } else {
            $('#cliente-fields').slideDown();
            $('#vendor-fields').slideUp();
        }
    }

    function setErr(id, msg) {
        $('#err-' + id).text(msg);
        $('#field-' + id).toggleClass('invalid', !!msg).toggleClass('valid', !msg && $('#field-' + id).val().trim() !== '');
        return !!msg;
    }

    function validaUsername(v) {
        if (!v) return 'Username obbligatorio.';
        if (!/^[a-zA-Z0-9_\-]{3,30}$/.test(v)) return 'Username: 3-30 caratteri, solo lettere, numeri, _ o -.';
        return '';
    }
    function validaNome(v, label) {
        if (!v) return label + ' obbligatorio/a.';
        if (v.length < 2) return label + ' troppo corto (min 2 caratteri).';
        if (!/^[a-zA-ZÀ-ÿ\s'\-]+$/.test(v)) return label + ' non valido (solo lettere e spazi).';
        return '';
    }
    function validaEmail(v) {
        if (!v) return 'Email obbligatoria.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Email non valida (es. nome@dominio.it).';
        return '';
    }
    function validaPassword(v) {
        if (!v) return 'Password obbligatoria.';
        if (!/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\|,.<>\/?]).{8,}$/.test(v))
            return 'Password: min 8 caratteri, 1 maiuscola, 1 numero, 1 simbolo.';
        return '';
    }
    function validaTelefono(v) {
        if (!v) return 'Telefono obbligatorio.';
        if (!/^(\+39\s?)?3\d{2}[\s\-]?\d{6,7}$/.test(v)) return 'Telefono non valido (es. 3201234567 o +39 320 1234567).';
        return '';
    }
    function validaIndirizzo(v) {
        if (!v) return 'Indirizzo obbligatorio.';
        if (v.length < 5) return 'Indirizzo troppo corto (min 5 caratteri).';
        return '';
    }
    function validaPiva(v) {
        if (!v) return 'Partita IVA obbligatoria.';
        if (!/^\d{11}$/.test(v)) return 'Partita IVA: esattamente 11 cifre numeriche.';
        return '';
    }
    function validaRagione(v) {
        if (!v) return 'Ragione Sociale obbligatoria.';
        if (v.length < 2) return 'Ragione Sociale troppo corta (min 2 caratteri).';
        return '';
    }

    // Validazione live campo per campo
    $('#field-username').on('blur', function() { setErr('username', validaUsername($(this).val().trim())); });
    $('#field-nome').on('blur', function() { setErr('nome', validaNome($(this).val().trim(), 'Nome')); });
    $('#field-cognome').on('blur', function() { setErr('cognome', validaNome($(this).val().trim(), 'Cognome')); });
    $('#field-email').on('blur', function() { setErr('email', validaEmail($(this).val().trim())); });
    $('#field-password').on('blur', function() { setErr('password', validaPassword($(this).val())); });
    $('#field-telefono').on('blur', function() { setErr('telefono', validaTelefono($(this).val().trim())); });
    $('#field-indirizzo').on('blur', function() { setErr('indirizzo', validaIndirizzo($(this).val().trim())); });
    $('#field-piva').on('blur', function() { setErr('piva', validaPiva($(this).val().trim())); });
    $('#field-ragione').on('blur', function() { setErr('ragione', validaRagione($(this).val().trim())); });

    $("#formRegistrazione").on("submit", function(e) {
        e.preventDefault();
        const tipo = $('#tipoUtente').val();

        // Valida tutti i campi
        let errori = false;
        errori |= setErr('username', validaUsername($('[name="username"]').val().trim()));
        errori |= setErr('nome',     validaNome($('[name="nome"]').val().trim(), 'Nome'));
        errori |= setErr('cognome',  validaNome($('[name="cognome"]').val().trim(), 'Cognome'));
        errori |= setErr('email',    validaEmail($('[name="email"]').val().trim()));
        errori |= setErr('password', validaPassword($('[name="password"]').val()));

        if (tipo === 'cliente') {
            errori |= setErr('telefono', validaTelefono($('[name="telefono"]').val().trim()));
            errori |= setErr('indirizzo', validaIndirizzo($('[name="indirizzo"]').val().trim()));
        } else {
            errori |= setErr('ragione', validaRagione($('[name="ragione_sociale"]').val().trim()));
            errori |= setErr('piva',    validaPiva($('[name="partita_iva"]').val().trim()));
        }

        if (errori) return;

        $.post('api/ba_registrazione.php', $(this).serialize())
            .done(function(resp) {
                if (resp.status === 'ok') {
                    window.location.href = "index.php";
                } else {
                    alert("Errore: " + resp.msg);
                }
            })
            .fail(function(xhr) {
                alert("Errore di rete (" + xhr.status + "): controlla che il file PHP esista.");
            });
    });
    </script>
</body>
</html>