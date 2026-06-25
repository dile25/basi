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
                    <button class="btn-modifica" onclick="toggleModifica()"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg> Modifica</button>
                </div>
                <p><strong>Username:</strong> <span id="p-username"></span></p>
                <p><strong>Email:</strong> <span id="p-email"></span></p>
                <p><strong>Telefono:</strong> <span id="p-telefono"></span></p>

                <div id="form-modifica" class="form-modifica-section">
                    <p style="font-weight:600; margin-bottom:12px;">Modifica i tuoi dati:</p>
                    <input type="text" id="edit-username" placeholder="Username" class="edit-input">
                    <p id="err-username" style="display:none; color:#e74c3c; font-size:0.85em; margin:-8px 0 10px;">Username non disponibile o non valido.</p>
                    <input type="email" id="edit-email" placeholder="Email" class="edit-input">
                    <input type="tel" id="edit-telefono" placeholder="Telefono" class="edit-input">
                    <input type="password" id="edit-password" placeholder="Nuova Password (lascia vuoto per non cambiarla)" class="edit-input">
                    <button class="btn-recensisci" style="width:100%;" onclick="salvaDati()">Salva Modifiche</button>
                    <p class="msg-success" id="msg-ok">Dati aggiornati con successo!</p>
                    <p class="msg-error" id="msg-err">Errore durante il salvataggio.</p>
                </div>
            </section>

            <!-- CARD 2: DETTAGLI ACCOUNT -->
            <section id="extra-info" class="info-card">
                <div id="content-extra">
                    <p style="color: var(--text-sec);">Caricamento...</p>
                </div>
            </section>

        </div>

        <!-- CARD DATI VENDITORE (solo venditore) -->
        <?php if($_SESSION['tipoUtente'] === 'venditore'): ?>
        <section class="info-card" style="margin-top:0;" id="card-venditore">
            <div class="card-header">
                <h3>Dati Aziendali</h3>
                <button class="btn-modifica" onclick="toggleModificaVenditore()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    Modifica
                </button>
            </div>
            <p><strong>Ragione Sociale:</strong> <span id="p-ragione-sociale">—</span></p>
            <p><strong>Partita IVA:</strong> <span id="p-partita-iva">—</span></p>

            <div id="form-modifica-venditore" class="form-modifica-section">
                <p style="font-weight:600; margin-bottom:12px;">Modifica dati aziendali:</p>
                <input type="text" id="edit-ragione-sociale" placeholder="Ragione Sociale" class="edit-input">
                <input type="text" id="edit-partita-iva" placeholder="Partita IVA (11 cifre)" class="edit-input" maxlength="11">
                <input type="tel" id="edit-telefono-venditore" placeholder="Telefono" class="edit-input">
                <button class="btn-recensisci" style="width:100%;" onclick="salvaVenditore()">Salva</button>
                <p class="msg-success" id="msg-ok-venditore" style="display:none;">Dati aggiornati!</p>
                <p class="msg-error" id="msg-err-venditore" style="display:none;">Errore durante il salvataggio.</p>
            </div>
        </section>
        <?php endif; ?>

        <?php if($_SESSION['tipoUtente'] === 'cliente'): ?>
        <section class="info-card" style="margin-top: 0;">
            <div class="card-header">
                <h3>Indirizzo di Spedizione</h3>
                <button class="btn-modifica" onclick="toggleModificaIndirizzo()"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg> Modifica</button>
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
                <button class="btn-recensisci" style="width:100%;" onclick="salvaIndirizzo()">Salva Indirizzo</button>
                <p id="msg-indirizzo" style="display:none; color:green; font-weight:600; margin-top:8px;">Indirizzo aggiornato!</p>
            </div>
        </section>

        <!-- CARD METODI DI PAGAMENTO (solo cliente) -->
        <section class="info-card">
            <div class="card-header">
                <h3>Metodi di Pagamento</h3>
                <button class="btn-modifica" onclick="toggleAggiungiMetodo()">+ Aggiungi</button>
            </div>
            <div id="lista-metodi-pagamento">
                <p style="color:var(--text-sec); font-size:0.9em;">Caricamento...</p>
            </div>

            <div id="form-aggiungi-metodo" class="form-modifica-section">
                <p style="font-weight:600; margin-bottom:12px;">Scegli il metodo da aggiungere:</p>

                <!-- Card visive stile checkout -->
                <div id="metodo-cards-profilo" style="margin-bottom:14px;">
                    <div class="metodo-card" onclick="selezionaMetodoProfilo('Carta', this)" style="padding:12px 16px; margin-bottom:8px;">
                        <input type="radio" name="metodo-profilo" value="Carta" style="accent-color:var(--primary-green); width:16px; height:16px; flex-shrink:0;">
                        <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--dark-green)"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                        Carta di Credito/Debito
                    </div>
                    <div class="metodo-card" onclick="selezionaMetodoProfilo('PayPal', this)" style="padding:12px 16px; margin-bottom:8px;">
                        <input type="radio" name="metodo-profilo" value="PayPal" style="accent-color:var(--primary-green); width:16px; height:16px; flex-shrink:0;">
                        <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#003087"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.312 2.48 1.007 4.274-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.118zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.59 3.025-2.566 6.082-8.558 6.082H9.824l-1.226 7.78h3.397c.46 0 .85-.335.92-.788l.04-.196.733-4.649.047-.256a.932.932 0 0 1 .92-.788h.58c3.754 0 6.694-1.524 7.552-5.932.358-1.84.173-3.375-.565-4.455-.21-.298-.45-.558-.72-.778z"/></svg>
                        PayPal
                    </div>
                    <div class="metodo-card" onclick="selezionaMetodoProfilo('ApplePay', this)" style="padding:12px 16px; margin-bottom:8px;">
                        <input type="radio" name="metodo-profilo" value="ApplePay" style="accent-color:var(--primary-green); width:16px; height:16px; flex-shrink:0;">
                        <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#000000"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/></svg>
                        Apple Pay
                    </div>
                    <div class="metodo-card" onclick="selezionaMetodoProfilo('GooglePay', this)" style="padding:12px 16px; margin-bottom:8px;">
                        <input type="radio" name="metodo-profilo" value="GooglePay" style="accent-color:var(--primary-green); width:16px; height:16px; flex-shrink:0;">
                        <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.99 0C5.364 0 0 5.364 0 11.99 0 18.62 5.364 24 11.99 24 18.62 24 24 18.62 24 11.99 24 5.364 18.62 0 11.99 0zm4.974 12.25c0 2.87-1.925 4.854-4.83 4.854a4.998 4.998 0 0 1-5.006-5.004c0-2.77 2.235-5.004 5.006-5.004 1.351 0 2.483.497 3.352 1.314l-1.362 1.362c-.37-.356-.998-.77-1.99-.77-1.704 0-3.094 1.413-3.094 3.098s1.39 3.098 3.094 3.098c1.978 0 2.72-1.42 2.836-2.155h-2.836v-1.787h4.743c.044.25.087.5.087.994z" fill="#4285F4"/></svg>
                        Google Pay
                    </div>
                    <div class="metodo-card" onclick="selezionaMetodoProfilo('Rate', this)" style="padding:12px 16px; margin-bottom:8px;">
                        <input type="radio" name="metodo-profilo" value="Rate" style="accent-color:var(--primary-green); width:16px; height:16px; flex-shrink:0;">
                        <svg class="metodo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--dark-green)"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                        Paga in 3 rate
                    </div>
                </div>

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
                <button class="btn-recensisci" style="width:100%;" onclick="salvaMetodo()">Salva Metodo</button>
                <p id="msg-metodo" style="display:none; color:green; font-weight:600; margin-top:8px;">Metodo aggiunto!</p>
            </div>
        </section>
        <?php endif; ?>

    <!-- ELIMINA ACCOUNT -->
    <section class="info-card" style="border:1.5px solid #e74c3c; margin-top:10px;">
        <div class="card-header" style="background:#fff5f5;">
            <h3 style="color:#e74c3c; display:flex; align-items:center; gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                Elimina il tuo account
            </h3>
        </div>
        <div style="padding:16px 20px;">
            <?php if($_SESSION['tipoUtente'] === 'cliente'): ?>
            <p style="color:#666; font-size:0.92em; margin:0 0 14px;">
                Eliminando l'account il tuo carrello e i tuoi preferiti verranno svuotati. I tuoi ordini resteranno visibili ai venditori e le tue recensioni resteranno pubbliche.
            </p>
            <?php elseif($_SESSION['tipoUtente'] === 'venditore'): ?>
            <p style="color:#666; font-size:0.92em; margin:0 0 14px;">
                Eliminando l'account tutti i tuoi prodotti verranno rimossi dal catalogo. Gli ordini ancora in lavorazione verranno annullati; quelli già spediti o consegnati restano invariati.
            </p>
            <?php endif; ?>
            <button onclick="apriModalElimina()" style="background:#e74c3c; color:#fff; border:none; border-radius:8px; padding:10px 20px; font-weight:600; cursor:pointer; font-size:0.95em;">
                Elimina il mio account
            </button>
        </div>
    </section>

    </main>

    <!-- MODAL ELIMINA ACCOUNT -->
    <div id="modalElimina" class="modal-overlay" style="display:none;">
        <div style="background:#fff; border-radius:16px; padding:32px; width:100%; max-width:420px; position:relative; box-shadow:0 8px 30px rgba(0,0,0,0.15);">
            <button onclick="chiudiModalElimina()" style="position:absolute;top:14px;right:16px;background:none;border:none;cursor:pointer;color:#999;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px;height:22px;display:block;"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
            <h3 style="color:#e74c3c; margin:0 0 8px;">Sei sicuro di voler eliminare l'account?</h3>
            <?php if($_SESSION['tipoUtente'] === 'cliente'): ?>
            <p style="color:#666; font-size:0.9em; margin:0 0 6px;">Questa operazione è <strong>irreversibile</strong>: carrello e preferiti verranno eliminati.</p>
            <?php elseif($_SESSION['tipoUtente'] === 'venditore'): ?>
            <p style="color:#666; font-size:0.9em; margin:0 0 6px;">Questa operazione è <strong>irreversibile</strong>: i tuoi prodotti verranno rimossi e gli ordini pendenti annullati.</p>
            <?php endif; ?>
            <p style="color:#666; font-size:0.9em; margin:0 0 20px;">Per confermare, digita il tuo username:</p>
            <input type="text" id="conferma-username" placeholder="Il tuo username" autocomplete="off"
                style="width:100%; border:1.5px solid #ddd; border-radius:8px; padding:10px 12px; font-size:0.95em; box-sizing:border-box; margin-bottom:16px;">
            <p id="msg-elimina-err" style="display:none; color:#e74c3c; font-size:0.88em; margin:0 0 12px;"></p>
            <button onclick="confermaElimina()" style="width:100%; background:#e74c3c; color:#fff; border:none; border-radius:8px; padding:13px; font-weight:700; font-size:1em; cursor:pointer;">
                Sì, elimina definitivamente
            </button>
        </div>
    </div>

    <script>
    let datiProfilo = {};

    document.addEventListener('DOMContentLoaded', function() {
        const urlP = new URLSearchParams(window.location.search);
        const uParam = urlP.get('u') ? '?u=' + encodeURIComponent(urlP.get('u')) : '';
        fetch('api/ba_get_profilo.php' + uParam)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'ok') return;
                const u = data.anagrafica || {};
                const d = data.dettagli  || {};
                datiProfilo = data;

                // Dati comuni
                const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val || ''; };
                const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value   = val || ''; };

                setTxt('user-fullname', (u.nome || '') + ' ' + (u.cognome || ''));
                setTxt('p-username', u.username);
                setTxt('p-email', u.email);
                setVal('edit-email', u.email);
                setVal('edit-username', u.username);

                const typeTag = document.getElementById('user-type');
                if (typeTag) {
                    typeTag.innerText = data.tipo;
                    typeTag.classList.add(data.tipo === 'venditore' ? 'venditore-tag' : 'cliente-tag');
                }

                if (data.tipo === 'cliente') {
                    setTxt('p-telefono', d.telefono || 'Non inserito');
                    setVal('edit-telefono', d.telefono);
                    if (d.indirizzo_predefinito) {
                        setTxt('p-indirizzo-full', d.indirizzo_predefinito);
                        const parts = d.indirizzo_predefinito.split(',');
                        setVal('edit-via',      parts[0]);
                        setVal('edit-citta',    parts[1]);
                        setVal('edit-cap',      parts[2]);
                        setVal('edit-provincia', parts[3]);
                    }
                }

                if (data.tipo === 'venditore') {
                    setTxt('p-ragione-sociale',    d.ragione_sociale);
                    setTxt('p-partita-iva',        d.partita_iva);
                    setTxt('p-telefono-venditore', d.telefono || 'Non inserito');
                    setVal('edit-ragione-sociale',    d.ragione_sociale);
                    setVal('edit-partita-iva',        d.partita_iva);
                    setVal('edit-telefono-venditore', d.telefono);
                }

                // Dettagli account (card destra)
                let extraHtml = '';
                const dataReg = u.data_registrazione
                    ? new Date(u.data_registrazione).toLocaleDateString('it-IT', { day:'2-digit', month:'long', year:'numeric' })
                    : 'N/D';

                if (data.tipo === 'venditore') {
                    extraHtml = `
                        <div class="card-header"><h3>Dettagli Account</h3></div>
                        <p style="margin:4px 0;"><strong>Membro dal:</strong> ${dataReg}</p>
                        <br>
                        <a class="btn-account-link" href="dashboard_venditore.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                            Vai alla Dashboard
                        </a>`;
                } else {
                    extraHtml = `
                        <div class="card-header"><h3>Dettagli Account</h3></div>
                        <p><strong>Membro dal:</strong> ${dataReg}</p>
                        <br>
                        <a class="btn-account-link" href="preferiti.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            I miei preferiti
                        </a>
                        <a class="btn-account-link" href="miei_ordini.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 3H3a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm-1 16H4V5h16v14zM6 7h12v2H6zm0 4h12v2H6zm0 4h8v2H6z"/></svg>
                            I miei ordini
                        </a>
                        <a class="btn-account-link" href="carrello.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zM7.17 14.75l.03-.12.9-1.63H17c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 21.44 4H5.21L4.54 2H1v2h2l3.6 7.59-1.35 2.44A2 2 0 0 0 7 18h14v-2H7.42a.25.25 0 0 1-.25-.25z"/></svg>
                            Il mio carrello
                        </a>`;
                }
                const ce = document.getElementById('content-extra');
                if (ce) ce.innerHTML = extraHtml;
            })
            .catch(err => console.error('Errore caricamento profilo:', err));

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
                        <button class="btn-elimina-metodo" onclick="eliminaMetodo(${m.id_pagamento})">Elimina</button>
                    </div>`;
                });
                cont.innerHTML = html;
            });
    }

    function iconaMetodo(m) {
        const i = {
            'Carta':'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:15px;height:15px;vertical-align:middle;"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>',
            'PayPal':'PayPal',
            'ApplePay':'Apple Pay',
            'GooglePay':'Google Pay',
            'Rate':'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:15px;height:15px;vertical-align:middle;"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>',
            'Contrassegno':'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:15px;height:15px;vertical-align:middle;"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2a3 3 0 0 0 6 0h6a3 3 0 0 0 6 0h2v-5l-3-4zM6 18a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm11.5-9.5 1.96 2.5H17V8.5h.5zm.5 9.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>'
        };
        return i[m] || '';
    }

    function eliminaMetodo(id) {
        if(!confirm('Eliminare questo metodo?')) return;
        fetch('api/ba_metodi_pagamento_cliente.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=delete&id=${id}`
        }).then(r => r.json()).then(() => caricaMetodi());
    }

    function selezionaMetodoProfilo(tipo, el) {
        // Deseleziona tutte le card
        document.querySelectorAll('#metodo-cards-profilo .metodo-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        el.querySelector('input[type=radio]').checked = true;
        mostraCampiMetodo();
    }

    function toggleAggiungiMetodo() {
        const f = document.getElementById('form-aggiungi-metodo');
        f.style.display = f.style.display === 'none' || f.style.display === '' ? 'block' : 'none';
    }

    function mostraCampiMetodo() {
        const checked = document.querySelector('#metodo-cards-profilo input[type=radio]:checked');
        const tipo = checked ? checked.value : '';
        const eCarta = (tipo === 'Carta' || tipo === 'Rate');
        const eDigital = (tipo === 'ApplePay' || tipo === 'GooglePay');
        document.getElementById('campo-carta-profilo').style.display = eCarta ? 'block' : 'none';
        document.getElementById('campo-paypal-profilo').style.display = tipo === 'PayPal' ? 'block' : 'none';
        document.getElementById('campo-digitalpay-profilo').style.display = eDigital ? 'block' : 'none';
        document.getElementById('err-carta-profilo').style.display = 'none';
        document.getElementById('err-digitalpay-profilo').style.display = 'none';
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
        const checked = document.querySelector('#metodo-cards-profilo input[type=radio]:checked');
        const tipo = checked ? checked.value : '';
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

    function toggleModificaVenditore() {
        const f = document.getElementById('form-modifica-venditore');
        f.style.display = f.style.display === 'none' || f.style.display === '' ? 'block' : 'none';
    }

    function salvaVenditore() {
        const rs   = document.getElementById('edit-ragione-sociale').value.trim();
        const piva = document.getElementById('edit-partita-iva').value.trim();
        const tel  = document.getElementById('edit-telefono-venditore').value.trim();
        if (!rs) { alert('Inserisci la ragione sociale.'); return; }
        if (piva && !/^\d{11}$/.test(piva)) { alert('La Partita IVA deve essere di 11 cifre numeriche.'); return; }
        fetch('api/ba_aggiorna_profilo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ragione_sociale: rs, partita_iva: piva, telefono: tel })
        }).then(r => r.json()).then(data => {
            if (data.status === 'ok') {
                document.getElementById('p-ragione-sociale').innerText = rs;
                document.getElementById('p-partita-iva').innerText = piva || '—';
                document.getElementById('p-telefono-venditore').innerText = tel || 'Non inserito';
                document.getElementById('msg-ok-venditore').style.display = 'block';
                document.getElementById('msg-err-venditore').style.display = 'none';
                setTimeout(() => { document.getElementById('msg-ok-venditore').style.display = 'none'; toggleModificaVenditore(); }, 1500);
            } else {
                document.getElementById('msg-err-venditore').innerText = data.msg || 'Errore.';
                document.getElementById('msg-err-venditore').style.display = 'block';
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
                document.getElementById('msg-err').innerText = (data.msg || 'Errore.');
                document.getElementById('msg-err').style.display = 'block';
            }
        });
    }

    function apriModalElimina() {
        document.getElementById('conferma-username').value = '';
        document.getElementById('msg-elimina-err').style.display = 'none';
        document.getElementById('modalElimina').style.display = 'flex';
    }

    function chiudiModalElimina() {
        document.getElementById('modalElimina').style.display = 'none';
    }

    function confermaElimina() {
        const input = document.getElementById('conferma-username').value.trim();
        const errEl = document.getElementById('msg-elimina-err');
        if (!input) {
            errEl.innerText = 'Inserisci il tuo username.';
            errEl.style.display = 'block';
            return;
        }
        fetch('api/ba_elimina_account.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ conferma: input })
        }).then(r => r.json()).then(data => {
            if (data.status === 'ok') {
                alert('Account eliminato. Verrai reindirizzato alla home.');
                window.location.href = 'index.php';
            } else {
                errEl.innerText = data.msg || 'Errore.';
                errEl.style.display = 'block';
            }
        });
    }
    </script>
</body>
</html>