<?php session_start(); 
if(!isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il Mio Profilo | BookArchive</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-header { background: var(--light-green); padding: 30px; border-radius: 15px; margin-bottom: 30px; border: 1px solid var(--border-color); }
        .info-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .tag-tipo { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .cliente-tag { background: #e3f2fd; color: #1976d2; }
        .venditore-tag { background: #f3e5f5; color: #7b1fa2; }
    </p></style>
</head>
<body>
    <?php include('header.php'); ?>

    <main class="container">
        <div class="profile-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 id="user-fullname" style="margin:0;">Caricamento...</h1>
                    <span id="user-type" class="tag-tipo"></span>
                </div>
                <button onclick="location.href='logout.php'" class="btn-recensisci" style="background:#ff4444;">Esci</button>
            </div>
        </div>

        <div class="dash-grid">
            <section class="info-card">
                <h3><i class="fas fa-user"></i> Informazioni Personali</h3>
                <hr>
                <p><strong>Username:</strong> <span id="p-username"></span></p>
                <p><strong>Email:</strong> <span id="p-email"></span></p>
                <p><strong>Data Nascita:</strong> <span id="p-nascita"></span></p>
                <p><strong>Telefono:</strong> <span id="p-telefono"></span></p>
            </section>

            <section id="extra-info" class="info-card">
                <h3><i class="fas fa-info-circle"></i> Dettagli Account</h3>
                <hr>
                <div id="content-extra"></div>
            </section>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('api/ba_get_profilo_completo.php')
            .then(res => res.json())
            .then(data => {
                if(data.status === 'ok') {
                    const u = data.anagrafica;
                    const d = data.dettagli;

                    // Riempimento base
                    document.getElementById('user-fullname').innerText = `${u.nome} ${u.cognome}`;
                    document.getElementById('p-username').innerText = u.username;
                    document.getElementById('p-email').innerText = u.email;
                    document.getElementById('p-nascita').innerText = u.data_nascita || 'Non specificata';
                    document.getElementById('p-telefono').innerText = d.telefono || d.num_telefono || 'Non inserito';
                    
                    const typeTag = document.getElementById('user-type');
                    typeTag.innerText = data.tipo;
                    typeTag.classList.add(data.tipo === 'venditore' ? 'venditore-tag' : 'cliente-tag');

                    // Riempimento extra
                    let extraHtml = "";
                    if(data.tipo === 'venditore') {
                        extraHtml = `
                            <p><strong>Ragione Sociale:</strong> ${d.ragione_sociale || 'N/D'}</p>
                            <p><strong>Partita IVA:</strong> ${d.partita_iva}</p>
                            <p><strong>Libri nel Catalogo:</strong> ${u.stat_libri}</p>
                        `;
                    } else {
                        extraHtml = `
                            <p><strong>Indirizzo Spedizione:</strong> ${d.indirizzo_predefinito || 'Nessuno'}</p>
                            <p><strong>Ordini Effettuati:</strong> ${u.stat_ordini}</p>
                            <button class="btn-recensisci" onclick="location.href='preferiti.php'">Vedi i tuoi ❤️</button>
                        `;
                    }
                    document.getElementById('content-extra').innerHTML = extraHtml;
                }
            });
    });
    </script>
</body>
</html>