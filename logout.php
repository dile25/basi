<?php
session_start();
$_SESSION = [];          // svuota le variabili di sessione
session_destroy();       // distrugge la sessione sul server
header("Location: login.php");
exit;
?>