<?php
session_start();
// Se l'utente non è loggato, non può stare qui
if(!isset($_SESSION['IdUtente'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Conferma Ordine | BookArchive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="container" style="max-width: 600px; margin: 50px auto;">
        <div class="dash-card" style="padding: 30px; border: 1px solid #ddd; border-radius: 12px; background: #fff;">
            <h2 style="margin-bottom: 20px; color: #333;">📦 Ultimo passaggio</h2>

            <div id="recap-ordine" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                <p>Stai per ordinare i libri nel tuo carrello.</p>
                <h4 id="totale-conferma">Totale: € --.--</h4>
            </div>

            <form id="formFinalizzaOrdine">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom: 8px; font-weight: bold;">Indirizzo di Spedizione</label>
                    <input type="text" name="indirizzo" id="campo-indirizzo" placeholder="Via, Città, CAP..." required
                           style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom: 8px; font-weight: bold;">Metodo di Pagamento</label>
                    <select name="metodo" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px;">
                        <option value="Carta">Carta di Credito</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Contrassegno">Pagamento alla consegna</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em; cursor: pointer;">
                    CONFERMA ACQUISTO
                </button>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // 1. Chiediamo all'API del profilo l'indirizzo salvato per non farlo riscrivere
        $.get('api/ba_profilo.php?action=get', function(resp) {
            if(resp.status === 'ok') {
                $("#campo-indirizzo").val(resp.data.Indirizzo);
            }
        });

        // 2. Recuperiamo il totale dal carrello per mostrarlo un'ultima volta
        $.get('api/ba_get_carrello.php', function(resp) {
            if(resp.status === 'ok') {
                let totale = 0;
                resp.prodotti.forEach(p => totale += (p.Prezzo * p.Quantita));
                $("#totale-conferma").text("Totale da pagare: € " + totale.toFixed(2));
            }
        });

        // 3. INVIO DELL'ORDINE
        $("#formFinalizzaOrdine").on("submit", function(e) {
            e.preventDefault();

            if(!confirm("Confermi di voler inviare l'ordine?")) return;

            $.post('api/ba_conferma_ordine.php', $(this).serialize(), function(resp) {
                if(resp.status === 'ok') {
                    alert("Ordine #"+resp.idOrdine+" inviato con successo!");
                    window.location.href = 'miei_ordini.php'; // Lo mandiamo a vedere i suoi ordini
                } else {
                    alert("Errore: " + resp.msg);
                }
            });
        });
    });
    </script>
</body>
</html>