<?php
session_start();
if(!isset($_SESSION['IdUtente'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Ordini | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .order-card { background: white; border: 1px solid var(--border); border-radius: 12px; margin-bottom: 25px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .order-header { background: #f8f9fa; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); }
        .order-body { padding: 20px; }
        .book-item { display: flex; align-items: center; gap: 15px; padding: 10px 0; border-bottom: 1px solid #eee; }
        .book-item:last-child { border-bottom: none; }
        .book-img { width: 50px; height: 75px; object-fit: cover; border-radius: 4px; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; }
        .status-lavorazione { background: #fff3cd; color: #856404; }
        .status-spedito { background: #d4edda; color: #155724; }
        
        /* Modal recensioni */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 3000; align-items: center; justify-content: center; backdrop-filter: blur(3px); }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 450px; position: relative; }
        .star-rating { font-size: 30px; cursor: pointer; color: #ddd; margin: 15px 0; }
        .star { transition: 0.2s; }
        textarea { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-top: 10px; font-family: inherit; }
    </style>
</head>
<body>

    <?php include("header.php"); ?>

    <div class="container" style="max-width: 900px; margin: 40px auto; padding: 0 20px;">
        <h2 style="color: var(--text-main); border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-bottom: 30px;">📦 I Miei Ordini</h2>

        <div id="ordini-lista">
            <p style="text-align:center; padding: 50px;">Caricamento ordini...</p>
        </div>
    </div>

    <div id="modalRecensione" class="modal-overlay">
        <div class="modal-content">
            <span onclick="$('#modalRecensione').fadeOut()" style="position:absolute; top:15px; right:20px; font-size:24px; cursor:pointer;">&times;</span>
            <h3 id="rev-titolo" style="color: var(--text-main); margin-bottom:5px;">Recensisci il libro</h3>
            <p style="color: #666; font-size: 0.9em;">La tua opinione aiuterà altri lettori!</p>

            <form id="formRecensione">
                <input type="hidden" name="idProdotto" id="rev-idProdotto">
                <input type="hidden" name="voto" id="rev-voto-val" value="5">

                <div class="star-rating">
                    <span class="star" data-v="1">★</span>
                    <span class="star" data-v="2">★</span>
                    <span class="star" data-v="3">★</span>
                    <span class="star" data-v="4">★</span>
                    <span class="star" data-v="5">★</span>
                </div>

                <textarea name="commento" rows="4" placeholder="Scrivi qui la tua recensione..." required></textarea>

                <button type="submit" class="btn-primary" style="width:100%; margin-top:20px; padding: 15px; font-weight: bold;">PUBBLICA RECENSIONE</button>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        caricaMieiOrdini();

        // Gestione click stelle
        $(document).on("click", ".star", function() {
            let v = $(this).data("v");
            $("#rev-voto-val").val(v);
            aggiornaStelle(v);
        });

        // Invio recensione
        $("#formRecensione").on("submit", function(e) {
            e.preventDefault();
            $.post('api/ba_scrivi_recensione.php', $(this).serialize(), function(resp) {
                if(resp.status === 'ok') {
                    alert("Recensione inviata con successo!");
                    $("#modalRecensione").fadeOut();
                    caricaMieiOrdini(); // Ricarica per nascondere il tasto recensisci
                } else {
                    alert(resp.msg);
                }
            }, "json");
        });
    });

    function caricaMieiOrdini() {
        $.get('api/ba_miei_ordini.php', function(resp) {
            if(resp.status === 'ok') {
                if(resp.ordini.length === 0) {
                    $("#ordini-lista").html("<div style='text-align:center; padding:50px;'><h3>Non hai ancora effettuato ordini.</h3><a href='index.php'>Inizia a leggere!</a></div>");
                    return;
                }
                
                let h = "";
                resp.ordini.forEach(ord => {
                    let bCls = (ord.stato === 'Spedito') ? 'status-spedito' : 'status-lavorazione';
                    h += `
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <strong>ORDINE #${ord.id_ordine}</strong> 
                                <span style="margin-left:15px; color:#666;">${ord.data}</span>
                            </div>
                            <div>
                                <span class="badge ${bCls}">${ord.stato}</span> 
                                <strong style="margin-left:10px; color:var(--accent);">€${parseFloat(ord.totale).toFixed(2)}</strong>
                            </div>
                        </div>
                        <div class="order-body">`;
                    
                    ord.libri.forEach(lib => {
                        h += `
                        <div class="book-item">
                            <img src="${lib.foto}" class="book-img">
                            <div style="flex-grow:1;">
                                <strong style="font-size:1.05em;">${lib.nome}</strong><br>
                                <small style="color:#666;">Quantità: ${lib.quantita} | Prezzo: €${parseFloat(lib.prezzo_acquisto).toFixed(2)}</small>
                            </div>
                            <div>
                                ${lib.gia_recensito ? 
                                    `<span style="color:var(--accent); font-weight:bold;">${"★".repeat(lib.voto_utente)}</span>` : 
                                    `<button class="btn-secondary" style="padding:8px 12px; font-size:0.85em;" onclick="apriModal(${lib.id_prodotto}, '${lib.nome.replace(/'/g, "\\'")}')">⭐ Recensisci</button>`
                                }
                            </div>
                        </div>`;
                    });
                    h += `</div></div>`;
                });
                $("#ordini-lista").html(h);
            }
        }, "json");
    }

    function apriModal(id, titolo) {
        $("#rev-idProdotto").val(id);
        $("#rev-titolo").text(titolo);
        $("#formRecensione")[0].reset();
        aggiornaStelle(5);
        $("#modalRecensione").css('display','flex').hide().fadeIn();
    }

    function aggiornaStelle(v) {
        $(".star").each(function() {
            $(this).css("color", $(this).data("v") <= v ? "#f1c40f" : "#ddd");
        });
    }
    </script>
</body>
</html>