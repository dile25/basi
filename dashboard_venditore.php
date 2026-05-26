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
        .btn-modifica-libro { padding:5px 10px; font-size:0.8em; background:var(--primary-green); color:white; border:none; border-radius:6px; cursor:pointer; }
        
        .dashboard-container { display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-top:30px; }
        @media (max-width:768px) { .dashboard-container { grid-template-columns:1fr; } }
        
        .box-insert { background:#f9fbf9; padding:25px; border-radius:12px; border:1px solid var(--border-color); }
        .form-control { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-top:5px; margin-bottom:15px; font-family:inherit; box-sizing:border-box; }
        
        /* Modal stili */
        .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; }
        .modal-body { background:white; padding:30px; border-radius:12px; width:90%; max-width:500px; position:relative; }
    </style>
</head>
<body>

    <?php include("header.php"); ?>

    <div class="container" style="max-width:1200px; margin:0 auto; padding:20px;">
        <h2>👨‍💼 Pannello di Controllo Venditore</h2>
        <p>Gestisci i tuoi libri in vendita e monitora le richieste.</p>

        <div class="dashboard-container">
            <div>
                <h3 style="color:var(--dark-green); margin-bottom:15px;">📚 I tuoi libri in catalogo</h3>
                <div id="miei-libri-lista">Caricamento libri...</div>
            </div>

            <div class="box-insert">
                <h3 style="margin-top:0; color:var(--dark-green);">➕ Aggiungi un nuovo libro</h3>
                <form id="formAggiungiLibro" enctype="multipart/form-data">
                    <label>Titolo del libro *</label>
                    <input type="text" id="add-nome" name="nome" class="form-control" required placeholder="Es. Senilità">

                    <label>Autore *</label>
                    <input type="text" id="add-autore" name="autore" class="form-control" required placeholder="Es. Italo Svevo">

                    <label>Categoria *</label>
                    <select id="add-categoria" name="categoria" class="form-control" required>
                        <option value="">Seleziona una categoria...</option>
                    </select>

                    <label>Descrizione / Trama</label>
                    <textarea id="add-descrizione" name="descrizione" class="form-control" rows="3" placeholder="Breve introduzione al testo..."></textarea>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                        <div>
                            <label>Prezzo (€) *</label>
                            <input type="number" id="add-prezzo" name="prezzo" step="0.01" class="form-control" required placeholder="12.50">
                        </div>
                        <div>
                            <label>Copie disponibili *</label>
                            <input type="number" id="add-quantita" name="quantita" class="form-control" required placeholder="5">
                        </div>
                    </div>

                    <label>Copertina del libro</label>
                    <input type="file" id="add-foto" name="fotoLibro" class="form-control" accept="image/*">

                    <button type="submit" class="btn-primary" style="width:100%; padding:12px; font-weight:bold;">PUBBLICA ANNUNCIO</button>
                    <div id="msg-aggiungi" style="margin-top:10px; text-align:center; display:none; font-weight:bold;"></div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalModifica" class="modal">
        <div class="modal-body">
            <span onclick="$('#modalModifica').fadeOut()" style="position:absolute; top:15px; right:20px; font-size:24px; cursor:pointer;">&times;</span>
            <h3 style="margin-top:0;">✏ Modifica Libro</h3>
            <div id="msg-modifica" style="margin-bottom:10px; display:none; font-weight:bold;"></div>
            
            <input type="hidden" id="modifica-id">
            
            <label>Titolo</label>
            <input type="text" id="modifica-nome" class="form-control">

            <label>Autore</label>
            <input type="text" id="modifica-autore" class="form-control">

            <label>Descrizione</label>
            <textarea id="modifica-descrizione" class="form-control" rows="3"></textarea>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div>
                    <label>Prezzo (€)</label>
                    <input type="number" id="modifica-prezzo" step="0.01" class="form-control">
                </div>
                <div>
                    <label>Quantità</label>
                    <input type="number" id="modifica-quantita" class="form-control">
                </div>
            </div>

            <label>Cambia Copertina</label>
            <input type="file" id="modifica-foto" class="form-control" accept="image/*">

            <button class="btn-primary" onclick="salvaModifica()" style="width:100%; padding:12px; font-weight:bold;">SALVA MODIFICHE</button>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        caricaLibri();
        caricaCategorie();

        // Invio form nuovo libro
        $("#formAggiungiLibro").on("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('nome', $('#add-nome').val());
            formData.append('autore', $('#add-autore').val());
            formData.append('categoria', $('#add-categoria').val());
            formData.append('descrizione', $('#add-descrizione').val());
            formData.append('prezzo', $('#add-prezzo').val());
            formData.append('quantita', $('#add-quantita').val());
            
            const foto = $('#add-foto')[0].files[0];
            if(foto) formData.append('fotoLibro', foto);

            $.ajax({
                url: 'api/ba_aggiungi_libro.php',
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(resp) {
                    if(resp.status === 'ok') {
                        $('#msg-aggiungi').text('✅ Libro pubblicato con successo!').css('color','green').show();
                        $('#formAggiungiLibro')[0].reset();
                        setTimeout(() => { $('#msg-aggiungi').fadeOut(); }, 3000);
                        caricaLibri();
                    } else {
                        $('#msg-aggiungi').text('❌ Errore: ' + resp.msg).css('color','red').show();
                    }
                }
            });
        });
    });

    function caricaCategorie() {
        $.get("api/ba_categorie.php", function(resp) {
            if(resp.categorie) {
                resp.categorie.forEach(c => {
                    $("#add-categoria").append(`<option value="${c.nome_categoria}">${c.nome_categoria}</option>`);
                });
            }
        });
    }

    function caricaLibri() {
        $.get('api/ba_prodotti_venditore.php', function(resp) {
            if(resp.status === 'ok') {
                if(resp.prodotti.length === 0) {
                    $("#miei-libri-lista").html("<p style='color:#666;'>Non hai ancora messo nessun libro in vendita.</p>");
                    return;
                }
                let h = "";
                resp.prodotti.forEach(p => {
                    const foto = p.url_foto ? p.url_foto : 'img/default.jpg';
                    
                    // MODIFICA APPLICATA: Passiamo anche p.autore (gestendo l'escape dei singoli apici) alla funzione apriModifica
                    const titoloScappato = p.nome.replace(/'/g, "\\'");
                    const autoreScappato = p.autore ? p.autore.replace(/'/g, "\\'") : '';
                    const descScappata = p.descrizione ? p.descrizione.replace(/'/g, "\\'").replace(/\n/g, " ") : '';

                    h += `
                    <div class="manage-book-card">
                        <img src="${foto}" class="manage-book-img">
                        <div style="flex-grow:1;">
                            <strong style="font-size:1.05em;">${p.nome}</strong><br>
                            <small style="color:#666;">Autore: ${p.autore || 'N/D'}<br>Prezzo: €${parseFloat(p.prezzo).toFixed(2)} | Copie: ${p.quantita_disponibile}</small>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button class="btn-modifica-libro" onclick="apriModifica(${p.id_prodotto}, '${titoloScappato}', '${autoreScappato}', '${descScappata}', ${p.prezzo}, ${p.quantita_disponibile})">✏ Modifica</button>
                            <button class="btn-elimina" onclick="eliminaLibro(${p.id_prodotto})">🗑 Elimina</button>
                        </div>
                    </div>`;
                });
                $("#miei-libri-lista").html(h);
            }
        });
    }

    // MODIFICA APPLICATA: Aggiunto il parametro 'autore' e valorizzato l'input del modal
    function apriModifica(id, nome, autore, desc, prezzo, qta) {
        $('#modifica-id').val(id);
        $('#modifica-nome').val(nome);
        $('#modifica-autore').val(autore);
        $('#modifica-descrizione').val(desc);
        $('#modifica-prezzo').val(prezzo);
        $('#modifica-quantita').val(qta);
        $('#msg-modifica').hide();
        $('#modalModifica').css('display','flex').hide().fadeIn();
    }

    // MODIFICA APPLICATA: Aggiunto l'append del campo autore nei dati inviati via AJAX
    function salvaModifica() {
        const formData = new FormData();
        formData.append('id_prodotto', $('#modifica-id').val());
        formData.append('nome', $('#modifica-nome').val());
        formData.append('autore', $('#modifica-autore').val());
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
            if(resp.status === 'ok') {
                caricaLibri();
            } else {
                alert("Errore nell'eliminazione");
            }
        });
    }
    </script>
</body>
</html>