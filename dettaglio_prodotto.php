<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio Libro | BookArchive</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        .btn-fav { background: none; border: 1px solid #e74c3c; color: #e74c3c; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; flex: 1; }
        .btn-fav:hover, .btn-fav.attivo { background: #e74c3c; color: white; }
        .btn-carrello { background: var(--primary-green); color: white; border: none; padding: 18px; border-radius: 8px; font-size: 1.1em; font-weight: bold; width: 100%; cursor: pointer; transition: 0.3s; }
        .btn-carrello:hover { background: var(--dark-green); }
        .btn-carrello.nel-carrello { background: white; color: #e74c3c; border: 2px solid #e74c3c; }
        .btn-carrello.nel-carrello:hover { background: #e74c3c; color: white; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-box { background: white; padding: 30px; border-radius: 15px; width: 90%; max-width: 480px; }
        .star-rating { font-size: 30px; cursor: pointer; color: #ddd; margin: 15px 0; }
        .recensione-utente { background: var(--light-green); border: 1px solid var(--border-color); border-radius: 10px; padding: 15px; margin-bottom: 15px; }
        .btn-piccolo { padding: 5px 12px; font-size: 0.82em; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-modifica-rec { background: none; border: 1px solid var(--primary-green); color: var(--primary-green); }
        .btn-elimina-rec { background: none; border: 1px solid #e74c3c; color: #e74c3c; margin-left: 6px; }
    </style>
</head>
<body>
<?php include("header.php"); ?>

<div id="modalRecensione" class="modal-overlay">
    <div class="modal-box">
        <span onclick="$('#modalRecensione').fadeOut()" style="float:right; cursor:pointer; font-size:1.5em;">&times;</span>
        <h3 id="modal-rec-titolo">La tua opinione</h3>
        <p style="font-size:0.9em; color:var(--text-sec);">Raccontaci la tua esperienza con questo libro.</p>
        <form id="formRecensione" style="margin-top:20px;">
            <input type="hidden" name="idProdotto" id="rev-idProdotto">
            <input type="hidden" name="id_recensione" id="rev-id-recensione" value="0">
            <input type="hidden" name="voto" id="rev-voto-val" value="5">
            <div class="star-rating">
                <span class="star" data-v="1">★</span>
                <span class="star" data-v="2">★</span>
                <span class="star" data-v="3">★</span>
                <span class="star" data-v="4">★</span>
                <span class="star" data-v="5">★</span>
            </div>
            <textarea name="commento" id="rev-commento" rows="4" placeholder="Cosa ne pensi del libro?" required
                style="width:100%; border:1px solid #ddd; border-radius:8px; padding:10px; font-family:inherit; box-sizing:border-box;"></textarea>
            <button type="submit" class="btn-recensisci" style="width:100%; margin-top:15px; padding:14px;">PUBBLICA RECENSIONE</button>
        </form>
    </div>
</div>

<div class="container" style="max-width:1100px; margin:30px auto; padding:0 20px;">
    <div id="loading" style="text-align:center; padding:100px;">
        <h2 style="color:var(--primary-green);">Caricamento del volume...</h2>
    </div>

    <div id="contentWrapper" style="display:none;">
        <div style="display:grid; grid-template-columns:300px 1fr; gap:40px; align-items:start;">

            <div>
                <img src="" id="mainImage" style="width:100%; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1);" alt="Copertina">
                <div id="thumbsContainer" style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;"></div>
            </div>

            <div>
                <h1 id="pTitle" style="margin:0 0 4px; color:var(--dark-green);"></h1>
                <p style="font-size: 1.2em; color: var(--text-dark); margin: 0 0 15px 0;">di <strong id="pAuthor"></strong></p>
                
                <p style="color:var(--text-sec); margin-bottom:15px;">
                    Venduto da: <a href="#" id="pVendorLink" style="font-weight:bold; color:var(--primary-green);"></a>
                    &nbsp;|&nbsp; Genere: <strong id="pCat"></strong>
                </p>
                <div id="priceHtml" style="margin-bottom:10px;"></div>
                <div id="stockHtml" style="margin-bottom:25px; font-weight:bold;"></div>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <button id="btnCarrello" class="btn-carrello" onclick="toggleCarrello()">
                        🛒 Aggiungi al Carrello
                    </button>
                    <div style="display:flex; gap:10px;">
                        <button id="btnFav" class="btn-fav" onclick="togglePreferito()">
                            ❤️ Aggiungi ai Preferiti
                        </button>
                        <button id="btnRecensisci" class="btn-recensisci" style="flex:1;" onclick="apriModalRecensione()">
                            ⭐ Recensisci
                        </button>
                    </div>
                </div>

                <div style="margin-top:30px;">
                    <h3 style="color:var(--dark-green); border-bottom:2px solid var(--light-green); padding-bottom:8px;">Trama</h3>
                    <p id="pDesc" style="white-space:pre-line; color:var(--text-dark); line-height:1.7;"></p>
                </div>
            </div>
        </div>

        <div style="margin-top:50px; background:white; border-radius:15px; padding:30px; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
            <h3 style="color:var(--dark-green); margin-bottom:20px;">Opinioni della Community</h3>
            <div id="reviewsList"></div>
        </div>
    </div>
</div>

<script>
let prodottoCorrente = null;
let nelCarrello = false;
let neiPeferiti = false;

$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    if (!id) { $("#loading").html("<h2>Libro non trovato. <a href='index.php'>Torna alla Home</a></h2>"); return; }

    // Carica dettagli prodotto
    $.getJSON("api/ba_dettaglio_prodotto.php", { id: id }, function(resp) {
        if (resp.status !== "ok") { $("#loading").html("<h3 style='color:red;'>" + resp.msg + "</h3>"); return; }

        const p = resp.dettagli;
        prodottoCorrente = p;

        let badge = p.ScontoPacchetto > 0 ? `<span style="background:#e74c3c;color:white;padding:3px 10px;border-radius:20px;font-size:0.8em;font-weight:bold;margin-right:8px;">-${p.ScontoPacchetto}%</span>` : '';
        $("#pTitle").html(badge + p.NomeProdotto);
        
        // POPOLA L'AUTORE ESTRATTO DAL DATABASE
        $("#pAuthor").text(p.autore || 'Autore non specificato');
        
        $("#pDesc").text(p.descrizione);
        $("#pCat").text(p.NomeCategoria || 'Generale');
        $("#pVendorLink").text(p.NomeVenditore).attr("href", "profilo_venditore.php?u=" + p.IdVenditore);
        $("#rev-idProdotto").val(p.IdProdotto);

        // Immagini
        let mainImg = (p.foto && p.foto.length > 0) ? p.foto[0] : 'img/default.jpg';
        $("#mainImage").attr("src", mainImg);
        if (p.foto && p.foto.length > 1) {
            let th = "";
            p.foto.forEach((url, i) => {
                th += `<img src="${url}" style="width:60px;height:80px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid ${i===0?'var(--primary-green)':'#ddd'};" onclick="cambiaImmagine('${url}', this)">`;
            });
            $("#thumbsContainer").html(th);
        }

        // Prezzo
        let prezzoHtml = p.ScontoPacchetto > 0
            ? `<span style="text-decoration:line-through;color:#999;font-size:1em;">€${parseFloat(p.prezzo).toFixed(2)}</span>
               <span style="font-size:1.8em;font-weight:800;color:var(--primary-green);margin-left:10px;">€${parseFloat(p.PrezzoScontato).toFixed(2)}</span>`
            : `<span style="font-size:1.8em;font-weight:800;color:var(--primary-green);">€${parseFloat(p.prezzo).toFixed(2)}</span>`;
        $("#priceHtml").html(prezzoHtml);

        // Stock
        if (p.QuantitaDisp > 0) {
            $("#stockHtml").html(`<span style="color:var(--primary-green);">✔ ${p.QuantitaDisp} copie disponibili</span>`);
        } else {
            $("#stockHtml").html(`<span style="color:#e74c3c;">✘ Momentaneamente esaurito</span>`);
            $("#btnCarrello").prop("disabled", true).text("Non disponibile").css("opacity", "0.5");
        }

        $("#loading").hide();
        $("#contentWrapper").fadeIn();

        // Controlla stato carrello e preferiti
        verificaStatoCarrello(p.IdProdotto);
        verificaStatoPreferiti(p.IdProdotto);
    });

    // Recensioni
    caricaRecensioni(id);

    // Stelle
    $(document).on("click", ".star", function() {
        const v = $(this).data("v");
        $("#rev-voto-val").val(v);
        aggiornaStelle(v);
    });

    // Invio recensione
    $("#formRecensione").on("submit", function(e) {
        e.preventDefault();
        $.post('api/ba_scrivi_recensione.php', $(this).serialize(), function(resp) {
            if(resp.status === 'ok') {
                alert("Recensione pubblicata!");
                $("#modalRecensione").fadeOut();
                caricaRecensioni(new URLSearchParams(window.location.search).get('id'));
            } else {
                alert(resp.msg);
            }
        }, "json");
    });
});

function cambiaImmagine(url, el) {
    $("#mainImage").attr("src", url);
    $("#thumbsContainer img").css("border-color", "#ddd");
    $(el).css("border-color", "var(--primary-green)");
}

function verificaStatoCarrello(idProdotto) {
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        if(resp.status === 'ok') {
            nelCarrello = resp.prodotti.some(p => p.IdProdotto == idProdotto);
            aggiornaBottoneCarrello();
        }
    }, 'json');
}

function verificaStatoPreferiti(idProdotto) {
    <?php if(isset($_SESSION['tipoUtente']) && $_SESSION['tipoUtente'] === 'cliente'): ?>
    $.get('api/ba_get_preferiti.php', function(resp) {
        if(resp.status === 'ok') {
            neiPeferiti = resp.preferiti.some(p => p.id_prodotto == idProdotto);
            aggiornaBottonePreferiti();
        }
    }, 'json');
    <?php endif; ?>
}

function aggiornaBottoneCarrello() {
    const btn = $("#btnCarrello");
    if (nelCarrello) {
        btn.text("🗑️ Rimuovi dal Carrello").addClass("nel-carrello");
    } else {
        btn.text("🛒 Aggiungi al Carrello").removeClass("nel-carrello");
    }
}

function aggiornaBottonePreferiti() {
    const btn = $("#btnFav");
    if (neiPeferiti) {
        btn.text("💔 Rimuovi dai Preferiti").addClass("attivo");
    } else {
        btn.text("❤️ Aggiungi ai Preferiti").removeClass("attivo");
    }
}

function toggleCarrello() {
    if (!prodottoCorrente) return;
    <?php if(!isset($_SESSION['IdUtente'])): ?>
        window.location.href = 'login.php'; return;
    <?php elseif(isset($_SESSION['tipoUtente']) && $_SESSION['tipoUtente'] !== 'cliente'): ?>
        alert('Solo i clienti possono usare il carrello.'); return;
    <?php endif; ?>

    if (nelCarrello) {
        $.post('api/ba_carrello.php', { action: 'remove', idProdotto: prodottoCorrente.IdProdotto }, function(resp) {
            if(resp.status === 'ok') {
                nelCarrello = false;
                aggiornaBottoneCarrello();
                if(typeof updateCartBadge === "function") updateCartBadge();
            }
        }, 'json');
    } else {
        $.post('api/ba_carrello.php', { action: 'add', idProdotto: prodottoCorrente.IdProdotto }, function(resp) {
            if(resp.status === 'ok') {
                nelCarrello = true;
                aggiornaBottoneCarrello();
                if(typeof updateCartBadge === "function") updateCartBadge();
            } else {
                alert(resp.msg);
            }
        }, 'json');
    }
}

function togglePreferito() {
    if (!prodottoCorrente) return;
    <?php if(!isset($_SESSION['IdUtente'])): ?>
        window.location.href = 'login.php'; return;
    <?php endif; ?>

    $.post('api/ba_toggle_preferiti.php', { idProdotto: prodottoCorrente.IdProdotto }, function(resp) {
        if(resp.status === 'ok') {
            neiPeferiti = (resp.action === 'added');
            aggiornaBottonePreferiti();
        } else {
            alert(resp.msg || 'Accedi per usare i preferiti.');
        }
    }, 'json');
}

function aggiornaStelle(v) {
    $(".star").each(function() {
        $(this).css("color", $(this).data("v") <= v ? "#f1c40f" : "#ddd");
    });
}

function caricaRecensioni(id) {
    $.getJSON("api/ba_recensioni_prodotto.php", { id: id }, function(resp) {
        let h = "";
        const utenteCorrente = "<?php echo $_SESSION['IdUtente'] ?? ''; ?>";

        if (!resp.recensioni || resp.recensioni.length === 0) {
            h = "<p style='color:var(--text-sec);text-align:center;padding:30px;'>Ancora nessuna recensione.</p>";
        } else {
            resp.recensioni.forEach(r => {
                const stelle = "★".repeat(r.valutazione) + "☆".repeat(5 - r.valutazione);
                const isMia = r.username === utenteCorrente;
                h += `<div style="border-bottom:1px solid var(--border-color);padding:20px 0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <strong>@${r.username}</strong>
                            ${isMia ? '<span style="font-size:0.78em;background:var(--light-green);color:var(--dark-green);padding:2px 8px;border-radius:10px;margin-left:8px;">Tu</span>' : ''}
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:1.1em;color:#f1c40f;">${stelle}</span>
                            ${isMia ? `
                            <button class="btn-piccolo btn-modifica-rec" onclick="modificaRecensione(${r.id_recensione}, ${r.valutazione}, \`${r.testo.replace(/`/g,"'")}\`)">✏️ Modifica</button>
                            <button class="btn-piccolo btn-elimina-rec" onclick="eliminaRecensione(${r.id_recensione})">🗑️ Elimina</button>
                            ` : ''}
                        </div>
                    </div>
                    <p style="margin:10px 0;color:var(--text-dark);">${r.testo}</p>
                    <small style="color:var(--text-sec);">${r.data}</small>
                </div>`;
            });
        }
        $("#reviewsList").html(h);
    });
}

function modificaRecensione(idRec, voto, testo) {
    $("#rev-id-recensione").val(idRec);
    $("#rev-voto-val").val(voto);
    $("#rev-commento").val(testo);
    $("#modal-rec-titolo").text("Modifica la tua recensione");
    aggiornaStelle(voto);
    $("#modalRecensione").css("display","flex").hide().fadeIn();
}

function apriModalRecensione() {
    <?php if(!isset($_SESSION['IdUtente'])): ?>
        window.location.href = 'login.php'; return;
    <?php endif; ?>
    $("#rev-id-recensione").val(0);
    $("#rev-voto-val").val(5);
    $("#rev-commento").val('');
    $("#modal-rec-titolo").text("La tua opinione");
    aggiornaStelle(5);
    $("#modalRecensione").css("display","flex").hide().fadeIn();
}

function eliminaRecensione(idRec) {
    if(!confirm("Vuoi eliminare la tua recensione?")) return;
    $.post('api/ba_elimina_recensione.php', { id_recensione: idRec }, function(resp) {
        if(resp.status === 'ok') {
            caricaRecensioni(new URLSearchParams(window.location.search).get('id'));
        } else {
            alert(resp.msg);
        }
    }, 'json');
}
</script>
</body>
</html>