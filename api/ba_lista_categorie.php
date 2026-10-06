<?php
require_once __DIR__ . '/comune.php';

// Prima le categorie principali (padre NULL), poi le sottocategorie
$res = $conn->query(
    "SELECT nome_categoria, nome_categoria_padre
     FROM categoria
     ORDER BY nome_categoria_padre IS NOT NULL, nome_categoria_padre, nome_categoria"
);
$categorie = $res->fetch_all(MYSQLI_ASSOC);

ok(['categorie' => $categorie]);
