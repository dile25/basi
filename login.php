<?php session_start(); if(isset($_SESSION['IdUtente'])) header("Location: index.php"); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Accedi | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="background: var(--light-green); display: flex; align-items: center; justify-content: center; height: 100vh;">

    <div style="width: 100%; max-width: 350px; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h2 style="color: var(--dark-green); text-align: center; margin-bottom: 30px;">Bentornato!</h2>

        <form id="formLogin" autocomplete="off">
            <!-- Campi trappola per bloccare autofill Chrome -->
            <input type="text" style="display:none;" name="fake_username">
            <input type="password" style="display:none;" name="fake_password">
            <input type="text" name="username" placeholder="Username" style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid var(--border-color);" required>
            <input type="password" name="password" placeholder="Password" style="width:100%; padding:12px; margin-bottom:20px; border-radius:8px; border:1px solid var(--border-color);" required>

            <button type="submit" class="btn-recensisci" style="width: 100%; padding: 12px;">ACCEDI</button>
        </form>

        <p style="text-align:center; margin-top:20px; font-size:0.9em;">Nuovo su BookArchive? <a href="registrazione.php">Registrati</a></p>
        <p style="text-align:center; margin-top:10px; font-size:0.9em;">
    <a href="index.php" style="color: var(--text-sec);">Torna alla home</a>
</p>
    </div>

    <script>
    $("#formLogin").on("submit", function(e) {
        e.preventDefault();
        $.post('api/ba_auth_login.php', $(this).serialize(), function(resp) {
            if(resp.status === 'ok') {
                window.location.href = "index.php";
            } else { alert("Dati non corretti!"); }
        });
    });
    </script>
</body>
</html>