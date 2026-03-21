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
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <?php include("header.php"); ?>

    <div class="container">
        <header style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0;">
            <h2 style="color: var(--dark-green);">👨‍💼 Pannello Venditore</h2>
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;" class="responsive-stack">
            <section>
                <h3 style="margin-bottom: 20px; color: var(--dark-green);">📚 I tuoi Libri</h3>
                <div id="lista-libri-venditore"></div>
            </section>

            <section>
                <h3 style="margin-bottom: 20px; color: var(--dark-green);">📦 Ordini da Spedire</h3>
                <div id="lista-ordini-venditore">
                    <p style="color: var(--text-sec);">Nessun ordine in attesa.</p>
                </div>
            </section>
        </div>
    </div>

    <div id="modalLibro" class="modal-overlay">
        <div class="modal-box" style="max-width: 500px;">
            <span onclick="$('#modalLibro').fadeOut()" style="float:right; cursor:pointer; font-size: 1.5em;">&times;</span>
            <h3 style="color: var(--dark-green); margin-bottom: 20px;">Nuovo Annuncio</h3>

            <form id="formNuovoLibro" enctype="multipart/form-data">
                <input type="text" name="nome" placeholder="Titolo del libro" class="form-control" required>
                <textarea name="descrizione" placeholder="Descrizione del libro" class="form-control" rows="3"></textarea>
                
                <label style="display:block; font-size: 0.85em; color: var(--dark-green); margin-bottom: 5px; font-weight: bold;">Copertina Libro:</label>
                <input type="file" name="fotoLibro" accept="image/*" class="form-control" required>

                <div style="display: flex; gap: 10px;">
                    <input type="number" name="prezzo" step="0.01" placeholder="Prezzo (€)" class="form-control" required>
                    <input type="number" name="quantita" placeholder="Quantità" class="form-control" required>
                </div>

                <select name="categoria" class="form-control" required>
                    <option value="">Seleziona Categoria...</option>
                    <option value="Narrativa">Narrativa</option>
                    <option value="Saggistica">Saggistica</option>
                    <option value="Gialli">Gialli & Thriller</option>
                </select>

                <button type="submit" class="btn-primary" style="width:100%; padding: 15px; margin-top: 10px;">PUBBLICA ANNUNCIO</button>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        caricaDatiDashboard();

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
                        caricaDatiDashboard();
                    } else { alert("Errore: " + resp.msg); }
                },
                cache: false, contentType: false, processData: false
            });
        });
    });

    function caricaDatiDashboard() {
        $.get('api/ba_miei_libri_venditore.php', function(resp) {
            if(resp.status === 'ok') {
                let html = "";
                resp.libri.forEach(lib => {
                    html += `
                    <div class="manage-book-card">
                        <img src="${lib.url_foto || 'img/default.jpg'}" class="manage-book-img">
                        <div style="flex-grow:1;">
                            <div style="font-weight: bold;">${lib.nome}</div>
                            <div style="font-size: 0.85em; color: var(--text-sec);">
                                €${parseFloat(lib.prezzo).toFixed(2)} | Disponibili: ${lib.quantita_disponibile}
                            </div>
                        </div>
                        <button class="btn-primary" style="padding: 5px 10px; font-size: 0.8em; background: var(--white); color: var(--primary-green); border: 1px solid var(--primary-green);">Modifica</button>
                    </div>`;
                });
                $("#lista-libri-venditore").html(html || "<p>Non hai ancora caricato libri.</p>");
                $("#count-libri").text(resp.libri.length);
            }
        });
    }

    function apriModalNuovoLibro() { $("#modalLibro").css('display','flex').hide().fadeIn(); }
    </script>
</body>
</html>