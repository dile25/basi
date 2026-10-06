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
    <title>Accedi | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body class="auth-page">
<main id="contenuto" class="auth-container">
    <h1 class="auth-title">Bentornato!</h1>

    <form id="formLogin" novalidate>
        <div class="form-field">
            <label for="login-username" class="form-label">Username</label>
            <input type="text" id="login-username" name="username" class="form-control" autocomplete="username" maxlength="30" required>
        </div>
        <div class="form-field">
            <label for="login-password" class="form-label">Password</label>
            <input type="password" id="login-password" name="password" class="form-control" autocomplete="current-password" required>
        </div>
        <p class="form-msg form-msg--err is-hidden" id="login-errore" role="alert"></p>
        <button type="submit" class="btn btn-primary btn-block">Accedi</button>
    </form>

    <p class="auth-footer">Nuovo su The (E-)Shop Around the Corner? <a href="registrazione.php">Crea un account</a></p>
    <p class="auth-footer"><a href="index.php">Torna alla home</a></p>
</main>

<script>
$('#formLogin').on('submit', function(e) {
    e.preventDefault();
    const username = $('#login-username').val().trim();
    const password = $('#login-password').val();
    const errore   = $('#login-errore');

    if (!username || !password) {
        errore.text('Inserisci username e password.').removeClass('is-hidden');
        return;
    }

    $.post('api/ba_auth_login.php', { username: username, password: password }, function(resp) {
        if (resp.status === 'ok') {
            window.location.href = 'index.php';
        } else {
            errore.text(resp.msg || 'Username o password non corretti.').removeClass('is-hidden');
        }
    }, 'json');
});
</script>
</body>
</html>
