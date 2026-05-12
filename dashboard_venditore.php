<?php
session_start();
if(!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Venditore | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .manage-book-card { display:flex; align-items:center; gap:12px; padding:12px; border:1px solid var(--border-color); border-radius:10px; margin-bottom:12px; background:white; }
        .manage-book-img { width:55px; height:75px; object-fit:cover; border-radius:6px; }
        .btn-elimina { padding:5px 10px; font-size:0.8em; background:white; color:#e74c3c; border:1px solid #e74c3c; border-radius:6px; cursor:pointer; }
        .btn-elimina:hover { background:#e74c3c; color:white; }
        .btn-modifica-libro { padding:5px 10px; font-size:0.8em; background:white; color:var(--primary-green); border:1px solid var(--primary-green); border-radius:6px; cursor:pointer; }
        .btn-modifica-libro:hover { background:var(--primary-green); color:white; }
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
        .modal-box { background:white; padding:30px; border-radius:15px; width:90%; overflow-y:auto; max-height:90vh; }
        .ordine-card { background:white; border:1px solid var(--border-color); border-radius:10px; padding:15px; margin-bottom:12px; }
        .ordine-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
        .badge-stato { padding:4px 10px; border-radius:20px; font-size:0.78em; font-weight:bold; }
        .stato-pagato { background:#d4edda; color:#155724; }
        .stato-spedito { background:#cce5ff; color:#004085; }
        .stato-consegnato { background:#d1ecf1; color:#0c5460; }
        .btn-stato { padding:5px 12px; font-size:0.8em; border-radius:6px; cursor:pointer; border:1px solid var(--primary-green); background:white; color:var(--primary-green); }
        .btn-stato:hover { background:var(--primary-green); color:white; }
    </style>
</head>
<body>

    <?php include("header.php"); ?>

    <div class="container">
        <header style="display:flex; justify-content:space-between; align-items:center; margin:30px 0;">
            <h2 style="color:var(--dark-green);">Pagina gestione vendite</h2>
            <button class="btn-primary" onclick="apriModalNuovoLibro()">+ Aggiungi Libro</button>
        </header>

        <div class="dash-grid">
            <div class="stat-box">
                <h3>Libri Online</h3>
                <div class="stat-number" id="count-libri">0</div>
            </div>
            <div class="stat-box">
                <h3>Ordini Ricevuti</h3>
                <div class="stat-number" id="count-ordini">0</div>
            </div>
            <div class="stat-box">
                <h3>Guadagno Totale</h3>
                <div class="stat-number" id="total-guadagno">€ 0.00</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
            <section>
                <h3 style="margin-bottom:20px; color:var(--dark-green);">📚 I tuoi Libri</h3>
                <div id="lista-libri-venditore"></div>
            </section>
            <section>
                <h3 style="margin-bottom:20px; color:var(--dark-green);">📦 Ordini da Spedire</h3>
                <div id="lista-ordini-venditore">
                    <p style="color:var(--text-sec);">Caricamento...</p>
                </div>
            </section>
        </div>
    </div>

    <!-- MODAL AGGIUNGI LIBRO -->
    <div id="modalLibro" class="modal-overlay">
        <div class="modal-box" style="max-width:500px;">
            <span onclick="$('#modalLibro').fadeOut()" style="float:right; cursor:pointer; font-size:1.5em;">&times;</span>
            <h3 style="color:var(--dark-green); margin-bottom:20px;">Nuovo Annuncio</h3>
            <form id="formNuovoLibro" enctype="multipart/form-data">
                <input type="text" name="nome" placeholder="Titolo del libro" class="form-control" required>
                <textarea name="descrizione" placeholder="Descrizione del libro" class="form-control" rows="3"></textarea>
                <label style="display:block; font-size:0.85em; color:var(--dark-green); margin-bottom:5px; font-weight:bold;">Copertina Libro:</label>
                <input type="file" name="fotoLibro" accept="image/*" class="form-control" required>
                <div style="display:flex; gap:10px;">
                    <input type="number" name="prezzo" step="0.01" placeholder="Prezzo (€)" class="form-control" required>
                    <input type="number" name="quantita" placeholder="Quantità" class="form-control" required>
                </div>
                <select name="categoria" class="form-control" required>
                    <option value="">Seleziona Categoria...</option>
                    <option value="Narrativa">Narrativa</option>
                    <option value="Saggistica">Saggistica</option>
                    <option value="Gialli">Gialli & Thriller</option>
                </select>
                <button type="submit" class="btn-primary" style="width:100%; padding:15px; margin-top:10px;">PUBBLICA ANNUNCIO</button>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICA LIBRO -->
    <div id="modalModifica" class="modal-overlay">
        <div class="modal-box" style="max-width:500px;">
            <span onclick="$('#modalModifica').fadeOut()" style="float:right; cursor:pointer; font-size:1.5em;">&times;</span>
            <h3 style="color:var(--dark-green); margin-bottom:20px;">✏️ Modifica Libro</h3>
            <input type="hidden" id="modifica-id">
            <input type="text" id="modifica-nome" placeholder="Titolo" class="form-control" style="margin-bottom:10px;">
            <textarea id="modifica-descrizione" placeholder="Descrizione" class="form-control" rows="3" style="margin-bottom:10px;"></textarea>
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <input type="number" id="modifica-prezzo" step="0.01" placeholder="Prezzo (€)" class="form-control">
                <input type="number" id="modifica-quantita" placeholder="Quantità" class="form-control">
            </div>
            <label style="display:block; font-size:0.85em; color:var(--dark-green); margin-bottom:5px; font-weight:bold;">Nuova Copertina (opzionale):</label>
            <img id="modifica-preview" src="" style="width:60px; height:80px; object-fit:cover; border-radius:6px; margin-bottom:10px; display:none;">
            <input type="file" id="modifica-foto" accept="image/*" class="form-control" style="margin-bottom:10px;">
            <button class="btn-primary" style="width:100%; padding:12px;" onclick="salvaModifica()">💾 Salva Modifiche</button>
            <p id="msg-modifica" style="display:none; color:green; font-weight:600; margin-top:8px;"></p>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        caricaLibri();
        caricaOrdini();

        $("#formNuovoLibro").on("submit", function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: 'api/ba_aggiungi_libro.php',
                type: 'POST',
                data: formData,
                success: function(resp) {
                    if(resp.status === 'ok') {
                        alert("Libro aggiunto correttamente!");
                        $('#modalLibro').fadeOut();
                        $('#formNuovoLibro')[0].reset();
                        caricaLibri();
                    } else { alert("Errore: " + resp.msg); }
                },
                cache: false, contentType: false, processData: false
            });
        });
    });

    function caricaLibri() {
        $.get('api/ba_libri_venditore.php', function(resp) {
            if(resp.status === 'ok') {
                let html = "";
                resp.libri.forEach(lib => {
                    html += `
                    <div class="manage-book-card">
                        <img src="${lib.url_foto || 'img/default.jpg'}" class="manage-book-img">
                        <div style="flex-grow:1;">
                            <div style="font-weight:bold;">${lib.nome}</div>
                            <div style="font-size:0.85em; color:var(--text-sec);">
                                €${parseFloat(lib.prezzo).toFixed(2)} | Disponibili: ${lib.quantita_disponibile}
                            </div>
                        </div>
                        <button class="btn-modifica-libro" onclick="apriModifica(${lib.id_prodotto}, '${lib.nome.replace(/'/g,"\\'")}', '${(lib.descrizione||'').replace(/'/g,"\\'")}', ${lib.prezzo}, ${lib.quantita_disponibile}, '${lib.url_foto||''}')">✏️</button>
                        <button class="btn-elimina" onclick="eliminaLibro(${lib.id_prodotto})">🗑️</button>
                    </div>`;
                });
                $("#lista-libri-venditore").html(html || "<p>Non hai ancora caricato libri.</p>");
                $("#count-libri").text(resp.libri.length);
            }
        });
    }

    function caricaOrdini() {
        $.get('api/ba_ordini_venditore.php', { action: 'list' }, function(resp) {
            if(resp.status === 'ok') {
                $("#count-ordini").text(resp.ordini.length);

                // Calcola guadagno totale
                let guadagno = 0;
                resp.ordini.forEach(o => guadagno += parseFloat(o.Prezzo) * parseInt(o.Quantita));
                $("#total-guadagno").text("€ " + guadagno.toFixed(2));

                if(resp.ordini.length === 0) {
                    $("#lista-ordini-venditore").html("<p style='color:var(--text-sec);'>Nessun ordine ricevuto.</p>");
                    return;
                }

                let html = "";
                resp.ordini.forEach(o => {
                    const badgeClass = o.Stato === 'Spedito' ? 'stato-spedito' : o.Stato === 'Consegnato' ? 'stato-consegnato' : 'stato-pagato';
                    html += `
                    <div class="ordine-card">
                        <div class="ordine-card-header">
                            <strong>Ordine #${o.IdOrdine}</strong>
                            <span class="badge-stato ${badgeClass}">${o.Stato}</span>
                        </div>
                        <div style="font-size:0.9em; color:var(--text-sec); margin-bottom:8px;">
                            📅 ${o.DataOrdine} &nbsp;|&nbsp; 👤 ${o.Cliente}
                        </div>
                        <div style="font-size:0.95em; margin-bottom:10px;">
                            📖 <strong>${o.Titolo}</strong> × ${o.Quantita}
                            &nbsp;— €${(parseFloat(o.Prezzo) * parseInt(o.Quantita)).toFixed(2)}
                        </div>
                        ${o.Stato === 'Pagato' ? `
                        <button class="btn-stato" onclick="aggiornaStato(${o.IdOrdine}, 'Spedito')">
                            🚚 Segna come Spedito
                        </button>` : ''}
                        ${o.Stato === 'Spedito' ? `
                        <button class="btn-stato" onclick="aggiornaStato(${o.IdOrdine}, 'Consegnato')">
                            ✅ Segna come Consegnato
                        </button>` : ''}
                    </div>`;
                });
                $("#lista-ordini-venditore").html(html);
            }
        }, 'json');
    }

    function aggiornaStato(idOrdine, stato) {
        $.post('api/ba_ordini_venditore.php?action=update_status', { idOrdine: idOrdine, stato: stato }, function(resp) {
            if(resp.status === 'ok') caricaOrdini();
            else alert("Errore: " + resp.msg);
        }, 'json');
    }

    function apriModalNuovoLibro() { $("#modalLibro").css('display','flex').hide().fadeIn(); }

    function apriModifica(id, nome, desc, prezzo, qta, urlFoto) {
        $('#modifica-id').val(id);
        $('#modifica-nome').val(nome);
        $('#modifica-descrizione').val(desc);
        $('#modifica-prezzo').val(prezzo);
        $('#modifica-quantita').val(qta);
        $('#modifica-foto').val('');
        urlFoto ? $('#modifica-preview').attr('src', urlFoto).show() : $('#modifica-preview').hide();
        $('#msg-modifica').hide();
        $("#modalModifica").css('display','flex').hide().fadeIn();
    }

    function salvaModifica() {
        const formData = new FormData();
        formData.append('id_prodotto', $('#modifica-id').val());
        formData.append('nome', $('#modifica-nome').val());
        formData.append('descrizione', $('#modifica-descrizione').val());
        formData.append('prezzo', $('#modifica-prezzo').val());
        formData.append('quantita', $('#modifica-quantita').val());
        const foto = $('#modifica-foto')[0].files[0];
        if(foto) formData.append('fotoLibro', foto);
        $.ajax({
            url: 'api/ba_modifica_libro.php', type: 'POST', data: formData,
            cache: false, contentType: false, processData: false,
            success: function(resp) {
                if(resp.status === 'ok') {
                    $('#msg-modifica').text('✅ Modifiche salvate!').css('color','green').show();
                    setTimeout(() => { $('#modalModifica').fadeOut(); caricaLibri(); }, 1500);
                } else {
                    $('#msg-modifica').text('❌ ' + resp.msg).css('color','red').show();
                }
            }
        });
    }

    function eliminaLibro(id) {
        if(!confirm("Sei sicuro di voler eliminare questo libro?")) return;
        $.post('api/ba_elimina_libro.php', { id_prodotto: id }, function(resp) {
            if(resp.status === 'ok') caricaLibri();
            else alert("Errore: " + resp.msg);
        });
    }
    </script>
</body>
</html>