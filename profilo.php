<?php session_start(); 
$utentePublico = $_GET['u'] ?? null;
$modalitaPubblica = $utentePublico && (!isset($_SESSION['IdUtente']) || $_SESSION['IdUtente'] !== $utentePublico);
if (!$utentePublico && !isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il Mio Profilo | The Shop Around the Corner</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-header { background: var(--light-green); padding: 30px; border-radius: 15px; margin-bottom: 30px; border: 1px solid var(--border-color); }
        .info-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .tag-tipo { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .cliente-tag { background: #e3f2fd; color: #1976d2; }
        .venditore-tag { background: #f3e5f5; color: #7b1fa2; }
        .edit-input { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95em; box-sizing: border-box; }
        .btn-modifica { background: none; border: 1px solid var(--primary-green); color: var(--primary-green); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .btn-modifica:hover { background: var(--dark-green); color: white; }
        .dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width: 768px) { .dash-grid { grid-template-columns: 1fr; } }
        .form-modifica-section { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color); }
        .msg-success { color: green; font-weight: 600; margin-top: 10px; display: none; }
        .msg-error { color: red; font-weight: 600; margin-top: 10px; display: none; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .card-header h3 { margin: 0; }
        .btn-dashboard { display: inline-block; margin-top: 24px; padding: 12px 24px; background: white; color: #1e8449; border: 2px solid #27ae60; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 0.95em; transition: background 0.2s; }
        .btn-dashboard:hover { background: #eafaf1; }
        .input-row { display: flex; gap: 10px; }
        .input-row .edit-input { flex: 1; }
        .metodo-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 8px; font-size: 0.9em; }
        .btn-elimina-metodo { background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 0.85em; font-weight: 600; }
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
                    <input type="email" id="edit-email" placeholder="Email" class="edit-input">
                    <input type="tel" id="edit-telefono" placeholder="Telefono" class="edit-input" <?php echo ($_SESSION['tipoUtente'] === 'venditore') ? 'style="display:none"' : ''; ?>>
                    <input type="password" id="edit-password" placeholder="Nuova Password (lascia vuoto per non cambiarla)" class="edit-input">
                    <button class="btn-recensisci" style="width:100%;" onclick="salvaDati()">💾 Salva Modifiche</button>
                    <p class="msg-success" id="msg-ok">✅ Dati aggiornati con successo!</p>
                    <p class="msg-error" id="msg-err">❌ Errore durante il salvataggio.</p>
                </div>
            </section>

            <!-- CARD 2: DETTAGLI ACCOUNT -->
            <section id="extra-info" class="info-card">
                <div id="content-extra">
                    <p style="color: var(--text-sec);">Caricamento...</p>
                </div>
            </section>

        </div>

        <!-- CARD INDIRIZZO (solo cliente) -->
        <?php if($_SESSION['tipoUtente'] === 'cliente'): ?>
        <section class="info-card" style="margin-top: 0;">
            <div class="card-header">
                <h3>📍 Indirizzo di Spedizione</h3>
                <button class="btn-modifica" onclick="toggleModificaIndirizzo()">✏️ Modifica</button>
            </div>
            <p><strong>Indirizzo:</strong> <span id="p-indirizzo-full">Nessuno</span></p>

            <div id="form-modifica-indirizzo" class="form-modifica-section">
                <p style="font-weight:600; margin-bottom:12px;">Modifica indirizzo:</p>
                <input type="text" id="edit-via" placeholder="Via e numero civico" class="edit-input">
                <div class="input-row">
                    <input type="text" id="edit-citta" placeholder="Città" class="edit-input">
                    <input type="text" id="edit-cap" placeholder="CAP" class="edit-input" maxlength="5">
                </div>
                <input type="text" id="edit-provincia" placeholder="Provincia (es. MI)" class="edit-input">
                <button class="btn-recensisci" style="width:100%;" onclick="salvaIndirizzo()">💾 Salva Indirizzo</button>
                <p id="msg-indirizzo" style="display:none; color:green; font-weight:600; margin-top:8px;">✅ Indirizzo aggiornato!</p>
            </div>
        </section>

        <!-- CARD METODI DI PAGAMENTO (solo cliente) -->
        <section class="info-card">
            <div class="card-header">
                <h3>💳 Metodi di Pagamento</h3>
                <button class="btn-modifica" onclick="toggleAggiungiMetodo()">+ Aggiungi</button>
            </div>
            <div id="lista-metodi-pagamento">
                <p style="color:var(--text-sec); font-size:0.9em;">Caricamento...</p>
            </div>

            <div id="form-aggiungi-metodo" class="form-modifica-section">
                <p style="font-weight:600; margin-bottom:12px;">Aggiungi metodo:</p>
                <select id="nuovo-metodo-tipo" class="edit-input" onchange="mostraCampiMetodo()">
                    <option value="">Seleziona tipo...</option>
                    <option value="Carta">💳 Carta di Credito/Debito</option>
                    <option value="PayPal">🅿️ PayPal</option>
                    <option value="ApplePay"> Apple Pay</option>
                    <option value="Rate">📅 Paga in 3 rate</option>
                </select>
                <div id="campo-carta-profilo" style="display:none;">
                    <input type="text" id="nuovo-cc" placeholder="Numero carta" class="edit-input" maxlength="19">
                </div>
                <div id="campo-paypal-profilo" style="display:none;">
                    <input type="email" id="nuovo-pp" placeholder="Email PayPal" class="edit-input">
                </div>
                <button class="btn-recensisci" style="width:100%;" onclick="salvaMetodo()">💾 Salva Metodo</button>
                <p id="msg-metodo" style="display:none; color:green; font-weight:600; margin-top:8px;">✅ Metodo aggiunto!</p>
            </div>
        </section>
        <?php endif; ?>

        <!-- BOTTONE DASHBOARD (solo venditore) -->
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
        const urlP = new URLSearchParams(window.location.search);
const uParam = urlP.get('u') ? '?u=' + encodeURIComponent(urlP.get('u')) : '';
fetch('api/ba_get_profilo.php' + uParam)
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
                    document.getElementById('edit-telefono') && (document.getElementById('edit-telefono').value = d.telefono || '');

                    const typeTag = document.getElementById('user-type');
                    typeTag.innerText = data.tipo;
                    typeTag.classList.add(data.tipo === 'venditore' ? 'venditore-tag' : 'cliente-tag');

                    // Indirizzo (solo cliente)
                    if (data.tipo === 'cliente' && d.indirizzo_predefinito) {
                        document.getElementById('p-indirizzo-full').innerText = d.indirizzo_predefinito;
                        const parts = d.indirizzo_predefinito.split(',');
                        if(parts[0]) document.getElementById('edit-via').value = parts[0].trim();
                        if(parts[1]) document.getElementById('edit-citta').value = parts[1].trim();
                        if(parts[2]) document.getElementById('edit-cap').value = parts[2].trim();
                        if(parts[3]) document.getElementById('edit-provincia').value = parts[3].trim();
                    }

                    let extraHtml = '';
                    if (data.tipo === 'venditore') {
                        extraHtml = `
                            <div class="card-header"><h3>Dettagli Account</h3></div>
                            <p><strong>Ragione Sociale:</strong> ${d.ragione_sociale || 'N/D'}</p>
                            <p><strong>Partita IVA:</strong> ${d.partita_iva || 'N/D'}</p>
                        `;
                    } else {
    const dataReg = u.data_registrazione 
        ? new Date(u.data_registrazione).toLocaleDateString('it-IT', { day:'2-digit', month:'long', year:'numeric' })
        : 'N/D';
    extraHtml = `
        <div class="card-header">
            <h3>Dettagli Account</h3>
        </div>
        <p><strong>Membro dal:</strong> ${dataReg}</p>
        <br>
        <button class="btn-recensisci" onclick="location.href='preferiti.php'">❤️ Vedi i tuoi preferiti</button>
    `;
}
document.getElementById('content-extra').innerHTML = extraHtml;
// Mostra libri se profilo pubblico venditore
if (data.prodotti && data.prodotti.length > 0) {
    let libriHtml = `
        <div style="margin-top:30px;">
            <h3 style="color:var(--dark-green); border-left:4px solid var(--primary-green); padding-left:10px;">
                Libri in vendita
            </h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:16px; margin-top:16px;">
    `;
    data.prodotti.forEach(p => {
        libriHtml += `
            <a href="dettaglio_prodotto.php?id=${p.id_prodotto}" style="text-decoration:none; color:inherit;">
                <div style="border:1px solid #eee; border-radius:10px; overflow:hidden; transition:0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(39,174,96,0.15)'" onmouseout="this.style.boxShadow=''">
                    <img src="${p.foto || 'img/default.jpg'}" style="width:100%; height:180px; object-fit:cover;">
                    <div style="padding:10px;">
                        <div style="font-weight:700; font-size:0.9em; margin-bottom:4px;">${p.nome}</div>
                        <div style="font-size:0.8em; color:#888;">${p.autore || ''}</div>
                        <div style="color:var(--dark-green); font-weight:800; margin-top:6px;">€${parseFloat(p.prezzo).toFixed(2)}</div>
                    </div>
                </div>
            </a>
        `;
    });
    libriHtml += `</div></div>`;
    document.querySelector('main.container').insertAdjacentHTML('beforeend', libriHtml);
}
                }
            });

        // Carica metodi pagamento (solo cliente)
        <?php if($_SESSION['tipoUtente'] === 'cliente'): ?>
        caricaMetodi();
        <?php endif; ?>
    });

    function caricaMetodi() {
        fetch('api/ba_metodi_pagamento_cliente.php?action=list')
            .then(r => r.json())
            .then(data => {
                const cont = document.getElementById('lista-metodi-pagamento');
                if (!data.metodi || data.metodi.length === 0) {
                    cont.innerHTML = '<p style="color:var(--text-sec); font-size:0.9em;">Nessun metodo salvato.</p>';
                    return;
                }
                let html = '';
                data.metodi.forEach(m => {
                    html += `<div class="metodo-item">
                        <span>${iconaMetodo(m.metodo)} <strong>${m.metodo}</strong> — ${m.dati}</span>
                        <button class="btn-elimina-metodo" onclick="eliminaMetodo(${m.id_pagamento})">🗑️ Elimina</button>
                    </div>`;
                });
                cont.innerHTML = html;
            });
    }

    function iconaMetodo(m) {
        const i = { 'Carta':'💳','PayPal':'🅿️','ApplePay':'','Rate':'📅','Contrassegno':'🚚' };
        return i[m] || '💳';
    }

    function eliminaMetodo(id) {
        if(!confirm('Eliminare questo metodo?')) return;
        fetch('api/ba_metodi_pagamento_cliente.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=delete&id=${id}`
        }).then(r => r.json()).then(() => caricaMetodi());
    }

    function toggleAggiungiMetodo() {
        const f = document.getElementById('form-aggiungi-metodo');
        f.style.display = f.style.display === 'none' || f.style.display === '' ? 'block' : 'none';
    }

    function mostraCampiMetodo() {
        const tipo = document.getElementById('nuovo-metodo-tipo').value;
        document.getElementById('campo-carta-profilo').style.display = tipo === 'Carta' ? 'block' : 'none';
        document.getElementById('campo-paypal-profilo').style.display = tipo === 'PayPal' ? 'block' : 'none';
    }

    function salvaMetodo() {
        const tipo = document.getElementById('nuovo-metodo-tipo').value;
        if (!tipo) { alert('Seleziona un tipo di metodo.'); return; }
        let dati = tipo;
        if (tipo === 'Carta') {
            const num = document.getElementById('nuovo-cc').value.replace(/\s/g,'');
            if (num.length < 4) { alert('Inserisci il numero carta.'); return; }
            dati = 'Carta che termina con ' + num.slice(-4);
        } else if (tipo === 'PayPal') {
            const email = document.getElementById('nuovo-pp').value;
            if (!email) { alert('Inserisci email PayPal.'); return; }
            dati = email;
        }

        fetch('api/ba_metodi_pagamento_cliente.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=add&metodo=${encodeURIComponent(tipo)}&dati=${encodeURIComponent(dati)}`
        }).then(r => r.json()).then(data => {
            if (data.status === 'ok') {
                document.getElementById('msg-metodo').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('msg-metodo').style.display = 'none';
                    document.getElementById('form-aggiungi-metodo').style.display = 'none';
                    caricaMetodi();
                }, 1500);
            }
        });
    }

    function toggleModifica() {
        const f = document.getElementById('form-modifica');
        f.style.display = f.style.display === 'none' || f.style.display === '' ? 'block' : 'none';
    }

    function toggleModificaIndirizzo() {
        const f = document.getElementById('form-modifica-indirizzo');
        f.style.display = f.style.display === 'none' || f.style.display === '' ? 'block' : 'none';
    }

    function salvaIndirizzo() {
        const via = document.getElementById('edit-via').value.trim();
        const citta = document.getElementById('edit-citta').value.trim();
        const cap = document.getElementById('edit-cap').value.trim();
        const prov = document.getElementById('edit-provincia').value.trim();
        if (!via || !citta || !cap) { alert('Inserisci via, città e CAP.'); return; }
        const indirizzo = `${via}, ${citta}, ${cap}${prov ? ', ' + prov : ''}`;

        fetch('api/ba_aggiorna_profilo.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ indirizzo: indirizzo })
        }).then(r => r.json()).then(data => {
            if (data.status === 'ok') {
                document.getElementById('p-indirizzo-full').innerText = indirizzo;
                const msg = document.getElementById('msg-indirizzo');
                msg.style.display = 'block';
                setTimeout(() => { msg.style.display = 'none'; toggleModificaIndirizzo(); }, 1500);
            }
        });
    }

    function salvaDati() {
        const payload = {
            email:    document.getElementById('edit-email').value,
            telefono: document.getElementById('edit-telefono') ? document.getElementById('edit-telefono').value : '',
            password: document.getElementById('edit-password').value
        };
        fetch('api/ba_aggiorna_profilo.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(data => {
            if (data.status === 'ok') {
                document.getElementById('msg-ok').style.display = 'block';
                document.getElementById('msg-err').style.display = 'none';
                document.getElementById('p-email').innerText = payload.email;
                document.getElementById('p-telefono').innerText = payload.telefono || 'Non inserito';
                setTimeout(() => toggleModifica(), 1500);
            } else {
                document.getElementById('msg-err').innerText = '❌ ' + (data.msg || 'Errore.');
                document.getElementById('msg-err').style.display = 'block';
            }
        });
    }
    </script>
</body>
</html>