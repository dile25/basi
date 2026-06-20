<?php session_start(); 
$utentePublico = $_GET['u'] ?? null;
$modalitaPubblica = $utentePublico && (!isset($_SESSION['IdUtente']) || $_SESSION['IdUtente'] !== $utentePublico);
if (!$utentePublico && !isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il Mio Profilo | The (E-)Shop Around the Corner</title>
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
                    <input type="text" id="edit-username" placeholder="Username" class="edit-input">
                    <p id="err-username" style="display:none; color:#e74c3c; font-size:0.85em; margin:-8px 0 10px;">Username non disponibile o non valido.</p>
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
                    <option value="GooglePay">🔵 Google Pay</option>
                    <option value="Rate">📅 Paga in 3 rate</option>
                </select>
                <div id="campo-carta-profilo" style="display:none;">
                    <input type="text" id="nuovo-cc" placeholder="Numero carta" class="edit-input" maxlength="19" style="margin-bottom:8px;">
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="nuovo-scadenza" placeholder="MM/AAAA" class="edit-input" maxlength="7" style="flex:1; margin-bottom:8px;">
                        <input type="text" id="nuovo-cvv" placeholder="CVV" class="edit-input" maxlength="3" style="flex:1; margin-bottom:8px;">
                    </div>
                    <p id="err-carta-profilo" style="display:none; color:#e74c3c; font-size:0.85em; margin:0 0 8px;"></p>
                </div>
                <div id="campo-digitalpay-profilo" style="display:none;">
                    <input type="text" id="nuovo-account-digitalpay" placeholder="Account associato (es. email)" class="edit-input" style="margin-bottom:8px;">
                    <p id="err-digitalpay-profilo" style="display:none; color:#e74c3c; font-size:0.85em; margin:0 0 8px;"></p>
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
                    document.getElementById('edit-username') && (document.getElementById('edit-username').value = u.username || '');
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
        <button class="btn-recensisci" style="width:100%; margin-bottom:8px;" onclick="location.href='preferiti.php'">❤️ I miei preferiti</button>
        <button class="btn-recensisci" style="width:100%; margin-bottom:8px;" onclick="location.href='miei_ordini.php'">📦 I miei ordini</button>
        <button class="btn-recensisci" style="width:100%;" onclick="location.href='carrello.php'">🛒 Il mio carrello</button>
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
        const i = { 'Carta':'💳','PayPal':'🅿️','ApplePay':'','GooglePay':'🔵','Rate':'📅','Contrassegno':'🚚' };
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
        const eCarta = (tipo === 'Carta' || tipo === 'Rate');
        const eDigital = (tipo === 'ApplePay' || tipo === 'GooglePay');
        document.getElementById('campo-carta-profilo').style.display = eCarta ? 'block' : 'none';
        document.getElementById('campo-paypal-profilo').style.display = tipo === 'PayPal' ? 'block' : 'none';
        document.getElementById('campo-digitalpay-profilo').style.display = eDigital ? 'block' : 'none';
        document.getElementById('err-carta-profilo').style.display = 'none';
        document.getElementById('err-digitalpay-profilo').style.display = 'none';
        // Aggiorna il placeholder account in base al tipo
        if (eDigital) {
            document.getElementById('nuovo-account-digitalpay').placeholder =
                tipo === 'ApplePay' ? 'Apple ID (es. nome@icloud.com)' : 'Account Google (es. nome@gmail.com)';
        }
    }

    // Formattazione automatica numero carta (gruppi di 4)
    document.addEventListener('DOMContentLoaded', function() {
        const ccInput = document.getElementById('nuovo-cc');
        if (ccInput) {
            ccInput.addEventListener('input', function() {
                let v = this.value.replace(/\D/g, '').substring(0, 16);
                this.value = v.replace(/(.{4})/g, '$1 ').trim();
            });
        }
        const scadInput = document.getElementById('nuovo-scadenza');
        if (scadInput) {
            scadInput.addEventListener('input', function() {
                let v = this.value.replace(/\D/g, '').substring(0, 6);
                if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2);
                this.value = v;
            });
        }
        const cvvInput = document.getElementById('nuovo-cvv');
        if (cvvInput) {
            cvvInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 3);
            });
        }
    });

    function salvaMetodo() {
        const tipo = document.getElementById('nuovo-metodo-tipo').value;
        if (!tipo) { alert('Seleziona un tipo di metodo.'); return; }
        let dati = tipo;

        if (tipo === 'Carta' || tipo === 'Rate') {
            const num = document.getElementById('nuovo-cc').value.replace(/\s/g,'');
            const scad = document.getElementById('nuovo-scadenza').value;
            const cvv = document.getElementById('nuovo-cvv').value;
            const errEl = document.getElementById('err-carta-profilo');

            if (num.length < 13) {
                errEl.textContent = 'Inserisci un numero di carta valido.';
                errEl.style.display = 'block'; return;
            }
            const scadMatch = scad.match(/^(\d{2})\/(\d{4})$/);
            if (!scadMatch) {
                errEl.textContent = 'Inserisci la scadenza nel formato MM/AAAA (es. 09/2027).';
                errEl.style.display = 'block'; return;
            }
            const mese = parseInt(scadMatch[1], 10);
            const anno = parseInt(scadMatch[2], 10);
            if (mese < 1 || mese > 12) {
                errEl.textContent = 'Il mese deve essere compreso tra 01 e 12.';
                errEl.style.display = 'block'; return;
            }
            const now = new Date();
            if (anno < now.getFullYear() || (anno === now.getFullYear() && mese < now.getMonth() + 1)) {
                errEl.textContent = 'La carta risulta già scaduta. Inserisci una data futura.';
                errEl.style.display = 'block'; return;
            }
            if (cvv.length !== 3) {
                errEl.textContent = 'Il CVV deve essere di 3 cifre.';
                errEl.style.display = 'block'; return;
            }
            errEl.style.display = 'none';
            const prefisso = tipo === 'Rate' ? 'Rate — Carta che termina con ' : 'Carta che termina con ';
            dati = prefisso + num.slice(-4);

        } else if (tipo === 'PayPal') {
            const email = document.getElementById('nuovo-pp').value;
            if (!email) { alert('Inserisci email PayPal.'); return; }
            dati = email;

        } else if (tipo === 'ApplePay' || tipo === 'GooglePay') {
            const account = document.getElementById('nuovo-account-digitalpay').value.trim();
            const errEl = document.getElementById('err-digitalpay-profilo');
            if (!account) {
                errEl.textContent = 'Inserisci l\'account associato.';
                errEl.style.display = 'block'; return;
            }
            errEl.style.display = 'none';
            dati = account;
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
        const nuovoUsername = document.getElementById('edit-username').value.trim();
        if (!nuovoUsername) { 
            document.getElementById('err-username').textContent = 'L\'username non può essere vuoto.';
            document.getElementById('err-username').style.display = 'block'; 
            return; 
        }
        // Username: solo lettere, numeri, underscore, trattino; 3-30 caratteri
        if (!/^[a-zA-Z0-9_\-]{3,30}$/.test(nuovoUsername)) {
            document.getElementById('err-username').textContent = 'Username non valido: usa solo lettere, numeri, _ o - (3-30 caratteri).';
            document.getElementById('err-username').style.display = 'block';
            return;
        }
        document.getElementById('err-username').style.display = 'none';

        const payload = {
            username: nuovoUsername,
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
                document.getElementById('p-username').innerText = nuovoUsername;
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