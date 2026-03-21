<?php
session_start();
// Pagina pubblica: il controllo sessione avviene lato client per le azioni riservate
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/logocar.svg">
    <title>Dettaglio Libro | BookArchive</title>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("header.php"); ?>

    <div id="successModal" class="modal-overlay">
        <div class="modal-box" style="text-align:center;">
            <h2 style="color:var(--primary-green);">Libro Aggiunto!</h2>
            <p style="color:var(--text-sec); margin-bottom:25px;">Il volume è stato inserito nel tuo carrello.</p>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button onclick="window.location.href='carrello.php'" class="btn-primary">Vai alla Cassa ➔</button>
                <button onclick="$('#successModal').fadeOut()" class="btn-secondary" style="padding:12px; border:none; background:none; cursor:pointer;">Continua la ricerca</button>
            </div>
        </div>
    </div>

    <div id="modalRecensione" class="modal-overlay">
        <div class="modal-box">
            <span onclick="$('#modalRecensione').fadeOut()" style="float:right; cursor:pointer; font-size:1.5em;">&times;</span>
            <h3>La tua opinione</h3>
            <p style="font-size:0.9em; color:var(--text-sec);">Raccontaci la tua esperienza con questo libro.</p>

            <form id="formRecensione" style="margin-top:20px;">
                <input type="hidden" name="idProdotto" id="rev-idProdotto">
                <input type="hidden" name="voto" id="rev-voto-val" value="5">

                <div class="star-rating">
                    <span class="star" data-v="1">★</span>
                    <span class="star" data-v="2">★</span>
                    <span class="star" data-v="3">★</span>
                    <span class="star" data-v="4">★</span>
                    <span class="star" data-v="5">★</span>
                </div>

                <textarea name="commento" rows="4" placeholder="Cosa ne pensi del libro? La tua recensione sarà pubblica." required></textarea>

                <button type="submit" class="btn-primary" style="width:100%; margin-top:20px; padding:15px;">PUBBLICA RECENSIONE</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div id="loading" style="text-align:center; padding:100px;">
            <h2 style="color:var(--primary-green);">Caricamento del volume...</h2>
        </div>

        <div id="contentWrapper" style="display:none;">
            <div class="product-wrapper">
                
                <div class="gallery-col">
                    <div class="main-photo-box">
                        <img src="" id="mainImage" class="main-photo" alt="Copertina libro">
                    </div>
                    <div class="thumbs-box" id="thumbsContainer"></div>
                </div>

                <div class="info-col">
                    <h1 class="product-title" id="pTitle"></h1>
                    
                    <div class="meta-info" style="margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid var(--border-color);">
                        Venduto da: <a href="#" id="pVendorLink" style="font-weight:bold;"></a> <br>
                        Genere: <strong id="pCat"></strong>
                    </div>

                    <div id="priceHtml"></div>
                    <div id="stockHtml" style="margin-top:10px; font-weight:bold;"></div>

                    <div style="margin-top:30px; display:flex; flex-direction:column; gap:12px;">
                        <button id="btnAdd" class="btn-primary" style="font-size:1.1em; padding:18px;">
                            🛒 Aggiungi al Carrello
                        </button>
                        
                        <div style="display:flex; gap:10px;">
                            <button id="btnFav" class="btn-fav" style="flex:1;">❤️ Preferiti</button>
                            <button id="btnRecensisci" class="btn-primary" style="flex:1; background:var(--white); color:var(--primary-green); border:1px solid var(--primary-green);">⭐ Recensisci</button>
                        </div>
                    </div>

                    <div class="desc-box" style="margin-top:40px;">
                        <h3 style="color:var(--dark-green); border-bottom:2px solid var(--light-green); padding-bottom:8px; margin-bottom:15px;">Trama</h3>
                        <p id="pDesc" style="white-space: pre-line; color:var(--text-dark);"></p>
                    </div>
                </div>
            </div>

            <div class="order-card" style="margin-top:40px; padding:30px;">
                <h3 style="margin-bottom:20px; color:var(--dark-green);">Opinioni della Community</h3>
                <div id="reviewsList"></div>
            </div>
        </div>
    </div>

    <script>
        // Funzioni di utilità per l'interfaccia
        function changeImage(url, el) {
            $("#mainImage").attr("src", url);
            $(".thumb-item").removeClass("active");
            $(el).addClass("active");
        }

        function aggiornaStelle(v) {
            $(".star").each(function() {
                $(this).css("color", $(this).data("v") <= v ? "var(--accent-orange)" : "#ddd");
            });
        }

        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');

            if (!id) {
                $("#loading").html("<h2>Libro non trovato. Torna alla <a href='index.php'>Home</a>.</h2>");
                return;
            }

            // 1. CARICAMENTO DATI DAL SERVER
            $.ajax({
                url: "api/ba_dettaglio_prodotto.php",
                type: "GET",
                data: { id: id },
                dataType: "json",
                success: function (resp) {
                    if (resp.status !== "ok") {
                        $("#loading").html("<h3 style='color:red;'>" + resp.msg + "</h3>");
                        return;
                    }

                    let p = resp.dettagli;
                    
                    // Titolo e Badge Sconto
                    let badge = (p.ScontoPacchetto > 0) ? `<span class="promo-badge">-${p.ScontoPacchetto}%</span> ` : '';
                    $("#pTitle").html(badge + p.NomeProdotto);
                    $("#pDesc").text(p.descrizione);
                    $("#pCat").text(p.NomeCategoria || 'Generale');
                    $("#pVendorLink").text(p.NomeVenditore).attr("href", "profilo_venditore.php?u=" + p.IdVenditore);

                    // Gestione Immagini
                    let mainImg = (p.foto && p.foto.length > 0) ? p.foto[0] : 'img/placeholder.png';
                    $("#mainImage").attr("src", mainImg);
                    if (p.foto && p.foto.length > 1) {
                        let th = "";
                        p.foto.forEach((url, i) => {
                            th += `<img src="${url}" class="thumb-item ${i===0?'active':''}" onclick="changeImage('${url}', this)">`;
                        });
                        $("#thumbsContainer").html(th);
                    }

                    // Prezzi
                    let p_html = (p.ScontoPacchetto > 0) 
                        ? `<div class="price-card"><span class="old-price">€ ${parseFloat(p.prezzo).toFixed(2)}</span><div class="price-now">€ ${parseFloat(p.PrezzoScontato).toFixed(2)}</div></div>`
                        : `<div class="price-card"><div class="price-now">€ ${parseFloat(p.prezzo).toFixed(2)}</div></div>`;
                    $("#priceHtml").html(p_html);

                    // Disponibilità Stock
                    if (p.QuantitaDisp > 0) {
                        $("#stockHtml").html(`<span style="color:var(--primary-green);">✔ ${p.QuantitaDisp} copie disponibili</span>`);
                        $("#btnAdd").attr("onclick", `aggiungiAlCarrello(${p.IdProdotto})`);
                    } else {
                        $("#stockHtml").html(`<span style="color:var(--danger);">✘ Momentaneamente esaurito</span>`);
                        $("#btnAdd").prop("disabled", true).text("Non disponibile").css("opacity", "0.5");
                    }

                    // Preferiti
                    $("#btnFav").attr("onclick", `togglePreferito(${p.IdProdotto})`);

                    $("#loading").hide();
                    $("#contentWrapper").fadeIn();
                }
            });

            // 2. RECUPERO RECENSIONI
            caricaRecensioni(id);

            // 3. LOGICA MODALE RECENSIONE
            $("#btnRecensisci").on("click", function() {
                $("#rev-idProdotto").val(id);
                aggiornaStelle(5);
                $("#modalRecensione").css("display", "flex").hide().fadeIn();
            });

            $(document).on("click", ".star", function() {
                let v = $(this).data("v");
                $("#rev-voto-val").val(v);
                aggiornaStelle(v);
            });

            $("#formRecensione").on("submit", function(e) {
                e.preventDefault();
                $.post('api/ba_scrivi_recensione.php', $(this).serialize(), function(resp) {
                    if(resp.status === 'ok') {
                        alert("Recensione pubblicata con successo!");
                        $("#modalRecensione").fadeOut();
                        caricaRecensioni(id); 
                    } else {
                        alert(resp.msg);
                        if(resp.msg.includes("Accedi")) window.location.href="login.php";
                    }
                }, "json");
            });
        });

        function caricaRecensioni(id) {
            $.getJSON("api/ba_recensioni_prodotto.php", { id: id }, function(resp) {
                let h = "";
                if (!resp.recensioni || resp.recensioni.length === 0) {
                    h = "<p style='color:var(--text-sec); text-align:center; padding:30px;'>Ancora nessuna recensione. Vuoi essere il primo?</p>";
                } else {
                    resp.recensioni.forEach(r => {
                        let s = "★".repeat(r.valutazione) + "☆".repeat(5-r.valutazione);
                        h += `<div style="border-bottom:1px solid var(--border-color); padding:20px 0;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <strong>@${r.username}</strong>
                                    <span class="stars" style="font-size:1.1em;">${s}</span>
                                </div>
                                <p style="margin:12px 0; color:var(--text-dark);">${r.testo}</p>
                                <small style="color:var(--text-sec);">${r.data}</small>
                              </div>`;
                    });
                }
                $("#reviewsList").html(h);
            });
        }

        function aggiungiAlCarrello(id) {
            $.post("api/ba_carrello.php", { action: "add", idProdotto: id }, function(resp) {
                if(resp.status === "ok") {
                    $("#successModal").css("display", "flex").hide().fadeIn();
                } else {
                    alert(resp.msg);
                    if(resp.msg.includes("Accedi")) window.location.href="login.php";
                }
            }, "json");
        }

        function togglePreferito(id) {
            $.post("api/ba_toggle_preferiti.php", { idProdotto: id }, function(resp) {
                if(resp.status === "ok") {
                    alert(resp.action === 'added' ? "❤️ Aggiunto ai tuoi preferiti!" : "Rimosso dai preferiti.");
                } else {
                    alert("Accedi per salvare i tuoi libri preferiti.");
                }
            }, "json");
        }
    </script>
</body>
</html>