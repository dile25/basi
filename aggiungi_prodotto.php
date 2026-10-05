<?php
session_start();
if (!isset($_SESSION['IdUtente']) || $_SESSION['tipoUtente'] !== 'venditore') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Nuovo prodotto | The (E-)Shop Around the Corner</title>
    <?php include 'head.php'; ?>
</head>
<body>
<?php include 'header.php'; ?>

<main id="contenuto" class="page page--narrow">
    <a href="dashboard_venditore.php" class="back-link">&#8592; Torna alla dashboard</a>

    <div class="form-card">
        <h1 class="page-title">Nuovo prodotto</h1>
        <p class="muted">I campi contrassegnati con * sono obbligatori.</p>

        <form id="formNuovoLibro" enctype="multipart/form-data" novalidate>
            <div class="form-field">
                <label for="campo-nome" class="form-label">Titolo *</label>
                <input type="text" name="nome" id="campo-nome" class="form-control" maxlength="150" required>
                <small class="field-error" id="err-nome"></small>
            </div>

            <div class="form-field">
                <label for="campo-autore" class="form-label">Autore *</label>
                <input type="text" name="autore" id="campo-autore" class="form-control" maxlength="100" placeholder="es. Elena Ferrante" required>
                <small class="field-error" id="err-autore"></small>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="campo-prezzo" class="form-label">Prezzo (€) *</label>
                    <input type="number" name="prezzo" id="campo-prezzo" class="form-control" step="0.01" min="0.01" max="9999.99" required>
                    <small class="field-error" id="err-prezzo"></small>
                </div>
                <div class="form-field">
                    <label for="campo-quantita" class="form-label">Copie disponibili *</label>
                    <input type="number" name="quantita" id="campo-quantita" class="form-control" min="0" max="9999" step="1" aria-describedby="help-quantita" required>
                    <small class="help-text" id="help-quantita">0 = non ancora disponibile.</small>
                    <small class="field-error" id="err-quantita"></small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="campo-categoria" class="form-label">Categoria *</label>
                    <select name="categoria" id="campo-categoria" class="form-control" required>
                        <option value="">Seleziona una categoria</option>
                    </select>
                    <small class="field-error" id="err-categoria"></small>
                </div>
                <div class="form-field">
                    <label for="campo-sottocategoria" class="form-label">Sottocategoria</label>
                    <select name="sottocategoria" id="campo-sottocategoria" class="form-control">
                        <option value="">Nessuna</option>
                    </select>
                </div>
            </div>

            <div class="form-field">
                <label for="campo-descrizione" class="form-label">Descrizione *</label>
                <textarea name="descrizione" id="campo-descrizione" class="form-control" rows="5" maxlength="2000" required></textarea>
                <small class="field-error" id="err-descrizione"></small>
            </div>

            <div class="form-field">
                <label for="campo-foto" class="form-label">Foto * (da 1 a 5)</label>
                <input type="file" name="foto[]" id="campo-foto" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required aria-describedby="help-foto">
                <small class="help-text" id="help-foto">JPG, PNG o WEBP, massimo 2 MB ciascuna. La prima foto sarà la copertina.</small>
                <small class="field-error" id="err-foto"></small>
                <div id="anteprima-foto" class="foto-gestione"></div>
            </div>

            <!-- PACCHETTO -->
            <fieldset class="section-box">
                <legend class="visually-hidden">Pacchetto</legend>
                <label class="checkbox-label">
                    <input type="checkbox" name="abilita_pacchetto" id="abilita-pacchetto" value="1">
                    Inserisci il prodotto in un pacchetto con sconto
                </label>

                <div id="box-pacchetto" class="is-hidden">
                    <p class="help-text">Il cliente ottiene lo sconto quando mette nel carrello tutti i prodotti del pacchetto.</p>
                    <div class="form-field">
                        <label for="scelta-pacchetto" class="form-label">Pacchetto</label>
                        <select name="id_pacchetto_esistente" id="scelta-pacchetto" class="form-control">
                            <option value="">Crea un nuovo pacchetto</option>
                        </select>
                    </div>

                    <div id="box-pacchetto-nuovo">
                        <div class="form-row">
                            <div class="form-field">
                                <label for="campo-nome-pacchetto" class="form-label">Nome del pacchetto</label>
                                <input type="text" name="nome_pacchetto" id="campo-nome-pacchetto" class="form-control" maxlength="100" placeholder="es. Trilogia della città di K.">
                            </div>
                            <div class="form-field">
                                <label for="campo-sconto-pacchetto" class="form-label">Sconto (%)</label>
                                <input type="number" name="sconto_pacchetto" id="campo-sconto-pacchetto" class="form-control" min="1" max="90" value="15">
                            </div>
                        </div>
                        <small class="field-error" id="err-pacchetto"></small>
                        <fieldset class="form-field">
                            <legend class="form-label">Altri tuoi prodotti da includere</legend>
                            <div id="lista-libri-pacchetto" class="checklist-box"><p class="muted">Caricamento...</p></div>
                        </fieldset>
                    </div>
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary btn-block btn-large" id="btnPubblica">Pubblica prodotto</button>
            <p id="msg-nuovo-libro" class="form-msg is-hidden" role="status"></p>
        </form>
    </div>
</main>

<script>
let categorieDB = [];
let pacchettiCaricati = false;
const MAX_FOTO = 5;
const MAX_PESO = 2 * 1024 * 1024;

$(function() {
    $.get('api/ba_categorie.php', function(resp) {
        categorieDB = resp.categorie || [];
        categorieDB.filter(c => !c.nome_categoria_padre).forEach(c => {
            $('#campo-categoria').append($('<option>').val(c.nome_categoria).text(c.nome_categoria));
        });
    }, 'json');
});

/* Sottocategorie della categoria scelta (gerarchia) */
$('#campo-categoria').on('change', function() {
    const padre = $(this).val();
    const select = $('#campo-sottocategoria').html('<option value="">Nessuna</option>');
    categorieDB.filter(c => c.nome_categoria_padre === padre).forEach(c => {
        select.append($('<option>').val(c.nome_categoria).text(c.nome_categoria));
    });
});

/* Anteprima e controllo delle foto scelte */
$('#campo-foto').on('change', function() {
    const files = Array.from(this.files);
    const anteprima = $('#anteprima-foto').empty();
    $('#err-foto').text(controllaFoto(files));
    files.slice(0, MAX_FOTO).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => anteprima.append(
            $('<figure class="foto-item">').append(
                $('<img>').attr({ src: e.target.result, alt: 'Anteprima foto ' + (i + 1) }),
                i === 0 ? $('<figcaption>').text('Copertina') : null
            )
        );
        reader.readAsDataURL(file);
    });
});

function controllaFoto(files) {
    if (files.length === 0) return 'Carica almeno una foto.';
    if (files.length > MAX_FOTO) return 'Puoi caricare al massimo ' + MAX_FOTO + ' foto.';
    for (const f of files) {
        if (!/^image\/(jpeg|png|webp)$/.test(f.type)) return '"' + f.name + '" non è un\'immagine JPG, PNG o WEBP.';
        if (f.size > MAX_PESO) return '"' + f.name + '" supera i 2 MB.';
    }
    return '';
}

/* ===== Pacchetto ===== */
$('#abilita-pacchetto').on('change', function() {
    const attivo = $(this).is(':checked');
    $('#box-pacchetto').toggleClass('is-hidden', !attivo);
    if (attivo && !pacchettiCaricati) {
        pacchettiCaricati = true;
        $.get('api/ba_pacchetti_venditore.php', function(resp) {
            (resp.pacchetti || []).forEach(p => {
                $('#scelta-pacchetto').append($('<option>').val(p.id_pacchetto)
                    .text(p.nome + ' (-' + parseInt(p.sconto) + '%, ' + parseInt(p.tot_prodotti) + ' prodotti)'));
            });
        }, 'json');
        $.get('api/ba_libri_venditore.php', function(resp) {
            const libri = resp.libri || [];
            if (libri.length === 0) {
                $('#lista-libri-pacchetto').html('<p class="muted">Non hai altri prodotti in vendita.</p>');
                return;
            }
            let html = '';
            libri.forEach(l => {
                html += `<label class="checkbox-label">
                    <input type="checkbox" name="libri_pacchetto[]" value="${parseInt(l.id_prodotto)}">
                    ${escapeHtml(l.nome)}${l.autore ? ' (' + escapeHtml(l.autore) + ')' : ''} · ${formatPrezzo(l.prezzo)}
                </label>`;
            });
            $('#lista-libri-pacchetto').html(html);
        }, 'json');
    }
});

$('#scelta-pacchetto').on('change', function() {
    $('#box-pacchetto-nuovo').toggleClass('is-hidden', !!$(this).val());
});

/* ===== Validazione e invio ===== */
function errore(id, msg) {
    $('#err-' + id).text(msg);
    $('#campo-' + id).toggleClass('invalid', !!msg).attr('aria-invalid', msg ? 'true' : 'false');
    return !!msg;
}

function validaForm() {
    const prezzo   = parseFloat($('#campo-prezzo').val());
    const quantita = $('#campo-quantita').val();
    let e = false;
    e = errore('nome',        $('#campo-nome').val().trim().length < 2 ? 'Inserisci il titolo (minimo 2 caratteri).' : '') || e;
    e = errore('autore',      $('#campo-autore').val().trim().length < 2 ? "Inserisci l'autore (minimo 2 caratteri)." : '') || e;
    e = errore('prezzo',      !(prezzo > 0 && prezzo <= 9999.99) ? 'Inserisci un prezzo tra 0,01 e 9999,99 €.' : '') || e;
    e = errore('quantita',    !/^\d{1,4}$/.test(quantita) ? 'Inserisci un numero intero tra 0 e 9999.' : '') || e;
    e = errore('categoria',   !$('#campo-categoria').val() ? 'Scegli una categoria.' : '') || e;
    e = errore('descrizione', $('#campo-descrizione').val().trim().length < 10 ? 'Scrivi una descrizione di almeno 10 caratteri.' : '') || e;
    e = errore('foto',        controllaFoto(Array.from($('#campo-foto')[0].files))) || e;

    if ($('#abilita-pacchetto').is(':checked') && !$('#scelta-pacchetto').val()) {
        const sconto = parseInt($('#campo-sconto-pacchetto').val());
        let msg = '';
        if ($('#campo-nome-pacchetto').val().trim().length < 2) msg = 'Dai un nome al pacchetto.';
        else if (!(sconto >= 1 && sconto <= 90)) msg = 'Lo sconto deve essere tra 1 e 90%.';
        $('#err-pacchetto').text(msg);
        if (msg) e = true;
    } else {
        $('#err-pacchetto').text('');
    }
    return !e;
}

$('#formNuovoLibro').on('submit', function(e) {
    e.preventDefault();
    if (!validaForm()) {
        $('.form-control.invalid').first().trigger('focus');
        return;
    }
    const btn = $('#btnPubblica').prop('disabled', true);
    $.ajax({
        url: 'api/ba_aggiungi_libro.php',
        type: 'POST',
        data: new FormData(this),
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(resp) {
            const msg = $('#msg-nuovo-libro').removeClass('is-hidden');
            if (resp.status === 'ok') {
                msg.text('Prodotto pubblicato. Torno alla dashboard...').addClass('form-msg--ok').removeClass('form-msg--err');
                setTimeout(() => { window.location.href = 'dashboard_venditore.php'; }, 1200);
            } else {
                msg.text(resp.msg || 'Pubblicazione non riuscita.').addClass('form-msg--err').removeClass('form-msg--ok');
                btn.prop('disabled', false);
            }
        },
        error: function() { btn.prop('disabled', false); }
    });
});
</script>
</body>
</html>