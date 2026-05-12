<?php session_start(); 
if(!isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il Mio Profilo | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-header { background: var(--light-green); padding: 30px; border-radius: 15px; margin-bottom: 30px; border: 1px solid var(--border-color); }
        .info-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .tag-tipo { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .cliente-tag { background: #e3f2fd; color: #1976d2; }
        .venditore-tag { background: #f3e5f5; color: #7b1fa2; }
        .edit-input { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95em; box-sizing: border-box; }
        .btn-modifica { background: none; border: 1px solid var(--primary-green); color: var(--primary-green); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .btn-modifica:hover { background: var(--primary-green); color: white; }
        .dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width: 768px) { .dash-grid { grid-template-columns: 1fr; } }
        .form-modifica-section { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color); }
        .msg-success { color: green; font-weight: 600; margin-top: 10px; display: none; }
        .msg-error { color: red; font-weight: 600; margin-top: 10px; display: none; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .card-header h3 { margin: 0; }
        .btn-dashboard {
    display: inline-block;
    margin-top: 24px;
    padding: 12px 24px;
    background: white;
    color: #1e8449;
    border: 2px solid #27ae60;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95em;
    transition: background 0.2s;
}
.btn-dashboard:hover { background: #eafaf1; }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <main class="container" style="max-width: 900px; margin: 30px auto; padding: 0 20px;">

        <div class="profile-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 id="user-fullname" style="margin:0;">Caricamento...</h1>
                    <span id="user-type" class="tag-tipo"></span>
                </div>
            </div>
        </div>

        <div class="dash-grid">

            <!-- CARD 1: INFORMAZIONI PERSONALI -->
            <section class="info-card">
                <div class="card-header">
                    <h3>Informazioni Personali</h3>
                    <button class="btn-modifica" onclick="toggleModifica()">✏️ Modifica</button>
                </div>
                <p><strong>Username:</strong> <span id="p-username"></span></p>
                <p><strong>Email:</strong> <span id="p-email"></span></p>
                <p><strong>Telefono:</strong> <span id="p-telefono"></span></p>

                <div id="form-modifica" class="form-modifica-section">
                    <p style="font-weight:600; margin-bottom:12px;">Modifica i tuoi dati:</p>
                    <input type="email" id="edit-email" placeholder="Nuova Email" class="edit-input">
                    <input type="tel" id="edit-telefono" placeholder="Telefono" class="edit-input">
                    <input type="text" id="edit-indirizzo" placeholder="Indirizzo di spedizione" class="edit-input" <?php echo ($_SESSION['tipoUtente'] === 'venditore') ? 'style="display:none"' : ''; ?>>
                    <input type="password" id="edit-password" placeholder="Nuova Password (lascia vuoto per non cambiarla)" class="edit-input">
                    <button class="btn-recensisci" style="width:100%;" onclick="salvaDati()">💾 Salva Modifiche</button>
                    <p class="msg-success" id="msg-ok">✅ Dati aggiornati con successo!</p>
                    <p class="msg-error" id="msg-err">❌ Errore durante il salvataggio.</p>
                </div>
            </section>

            <!-- CARD 2: DETTAGLI ACCOUNT (contenuto iniettato da JS) -->
            <section id="extra-info" class="info-card">
                <div id="content-extra">
                    <p style="color: var(--text-sec);">Caricamento...</p>
                </div>
            </section>

        </div>

        <!-- SOLO PER VENDITORE: bottone dashboard -->
        <?php if($_SESSION['tipoUtente'] === 'venditore'): ?>
<div style="margin-top: 24px;">
    <a href="dashboard_venditore.php" class="btn-dashboard">
        Vai alla pagina per la gestione delle vendite
    </a>
</div>
<?php endif; ?>

    </main>

    <script>
    let datiProfilo = {};

    document.addEventListener('DOMContentLoaded', function() {
        fetch('api/ba_get_profilo.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    const u = data.anagrafica;
                    const d = data.dettagli;
                    datiProfilo = data;

                    document.getElementById('user-fullname').innerText = u.nome + ' ' + u.cognome;
                    document.getElementById('p-username').innerText = u.username;
                    document.getElementById('p-email').innerText = u.email;
                    document.getElementById('p-telefono').innerText = d.telefono || 'Non inserito';

                    document.getElementById('edit-email').value = u.email || '';
                    document.getElementById('edit-telefono').value = d.telefono || '';
                    document.getElementById('edit-indirizzo').value = d.indirizzo_predefinito || '';

                    const typeTag = document.getElementById('user-type');
                    typeTag.innerText = data.tipo;
                    typeTag.classList.add(data.tipo === 'venditore' ? 'venditore-tag' : 'cliente-tag');

                    let extraHtml = '';

                    if (data.tipo === 'venditore') {
                        extraHtml = `
                            <div class="card-header">
                                <h3>Dettagli Account</h3>
                            </div>
                            <p><strong>Ragione Sociale:</strong> ${d.ragione_sociale || 'N/D'}</p>
                            <p><strong>Partita IVA:</strong> ${d.partita_iva || 'N/D'}</p>
                        `;
                    } else {
                        extraHtml = `
                            <div class="card-header">
                                <h3>Dettagli Account</h3>
                                <button class="btn-modifica" onclick="toggleModificaExtra()">✏️ Modifica</button>
                            </div>
                            <p><strong>Indirizzo Spedizione:</strong> <span id="p-indirizzo">${d.indirizzo_predefinito || 'Nessuno'}</span></p>

                            <div id="form-modifica-extra" class="form-modifica-section">
                                <p style="font-weight:600; margin-bottom:12px;">Modifica i tuoi dettagli:</p>
                                <input type="text" id="edit-indirizzo-extra" placeholder="Indirizzo di spedizione"
                                       value="${d.indirizzo_predefinito || ''}" class="edit-input">
                                <button class="btn-recensisci" style="width:100%;" onclick="salvaIndirizzo()">💾 Salva Modifiche</button>
                                <p id="msg-indirizzo" style="display:none; color:green; font-weight:600; margin-top:8px;">✅ Indirizzo aggiornato!</p>
                            </div>
                            <br>
                            <button class="btn-recensisci" onclick="location.href='preferiti.php'">Vedi la tua lista preferiti</button>
                        `;
                    }

                    document.getElementById('content-extra').innerHTML = extraHtml;
                }
            });
    });

    function toggleModifica() {
        const form = document.getElementById('form-modifica');
        form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
    }

    function toggleModificaExtra() {
        const form = document.getElementById('form-modifica-extra');
        form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
    }

    function salvaIndirizzo() {
        const indirizzo = document.getElementById('edit-indirizzo-extra').value;
        fetch('api/ba_aggiorna_profilo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ indirizzo: indirizzo })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                document.getElementById('p-indirizzo').innerText = indirizzo;
                const msg = document.getElementById('msg-indirizzo');
                msg.style.display = 'block';
                setTimeout(() => msg.style.display = 'none', 2000);
            }
        });
    }

    function salvaDati() {
        const payload = {
            email:    document.getElementById('edit-email').value,
            telefono: document.getElementById('edit-telefono').value,
            indirizzo: document.getElementById('edit-indirizzo').value,
            password: document.getElementById('edit-password').value
        };

        fetch('api/ba_aggiorna_profilo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                document.getElementById('msg-ok').style.display = 'block';
                document.getElementById('msg-err').style.display = 'none';
                document.getElementById('p-email').innerText = payload.email;
                document.getElementById('p-telefono').innerText = payload.telefono || 'Non inserito';
                if (document.getElementById('p-indirizzo'))
                    document.getElementById('p-indirizzo').innerText = payload.indirizzo || 'Nessuno';
                setTimeout(() => toggleModifica(), 1500);
            } else {
                document.getElementById('msg-err').innerText = '❌ ' + (data.msg || 'Errore durante il salvataggio.');
                document.getElementById('msg-err').style.display = 'block';
            }
        });
    }
    </script>
</body>
</html>