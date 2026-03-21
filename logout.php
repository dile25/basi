<?php
session_start();
session_destroy(); // Distrugge la sessione attuale
header("Location: login.php"); // Torna al login
exit;
?>