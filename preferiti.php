<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I Miei Preferiti - E-commerce Libri</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include('header.php'); ?>

    <main class="container">
        <h1>❤️ I tuoi libri preferiti</h1>
        <div id="lista-preferiti" class="grid-prodotti">
            </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('api/ba_get_preferiti.php')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('lista-preferiti');
                if (data.status === 'ok' && data.preferiti.length > 0) {
                    data.preferiti.forEach(p => {
                        container.innerHTML += `
                            <div class="card-prodotto">
                                <img src="${p.URLfoto || 'img/default-book.jpg'}" alt="${p.nome}">
                                <h3>${p.nome}</h3>
                                <p class="prezzo">
                                    <span class="originale">€${p.prezzo}</span> 
                                    <strong>€${p.prezzo_scontato}</strong>
                                </p>
                                <button onclick="togglePreferito(${p.id_prodotto})">Rimuovi</button>
                                <button onclick="aggiungiAlCarrello(${p.id_prodotto})">🛒 Compra</button>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = "<p>Non hai ancora salvato alcun libro tra i preferiti.</p>";
                }
            });
    });

    function togglePreferito(id) {
        const formData = new FormData();
        formData.append('idProdotto', id);
        fetch('api/ba_toggle_preferiti.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(() => location.reload()); // Ricarica per aggiornare la lista
    }
    </script>
</body>
</html>