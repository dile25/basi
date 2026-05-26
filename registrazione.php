<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Unisciti a BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .auth-container { max-width: 400px; margin: 80px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid var(--primary-green); }
        .auth-input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid var(--border-color); border-radius: 8px; box-sizing: border-box; }
        .role-selector { display: flex; gap: 10px; margin-bottom: 20px; }
        .role-option { flex: 1; text-align: center; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .role-option.active { background: var(--primary-green); color: white; border-color: var(--primary-green); }
    </style>
</head>
<body style="background: var(--light-green);">

    <div class="auth-container">
        <h2 style="color: var(--dark-green); text-align: center; margin-bottom: 25px;">Crea il tuo Account</h2>

        <form id="formRegistrazione">
            <input type="text" name="username" placeholder="Username" class="auth-input" required>
            <input type="text" name="nome" placeholder="Nome" class="auth-input" required>
            <input type="text" name="cognome" placeholder="Cognome" class="auth-input" required>
            <input type="email" name="email" placeholder="Email" class="auth-input" required>
            <input type="password" name="password" placeholder="Password" class="auth-input" required>

            <!-- Campi solo per CLIENTE -->
            <div id="cliente-fields">
                <input type="tel" name="telefono" placeholder="Telefono (opzionale)" class="auth-input">
                <input type="text" name="indirizzo" placeholder="Indirizzo (opzionale)" class="auth-input">
            </div>

            <!-- Campi solo per VENDITORE -->
            <div id="vendor-fields" style="display:none;">
                <input type="text" name="ragione_sociale" placeholder="Ragione Sociale" class="auth-input">
                <input type="text" name="partita_iva" placeholder="Partita IVA" class="auth-input">
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

$("#formRegistrazione").on("submit", function(e) {
    e.preventDefault();
    $.post('api/ba_registrazione.php', $(this).serialize())
        .done(function(resp) {
            if (resp.status === 'ok') {
                // MODIFICA: Niente più reindirizzamento al login. Andiamo direttamente alla home!
                // Se preferisci che il venditore vada direttamente alla sua dashboard, puoi decommentare le righe sotto:
                // if ($('#tipoUtente').val() === 'venditore') {
                //     window.location.href = "dashboard_venditore.php";
                // } else {
                //     window.location.href = "index.php";
                // }
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