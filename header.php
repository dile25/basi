<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$tipoHeader = $_SESSION['tipoUtente'] ?? '';
?>
<a href="#contenuto" class="skip-link">Vai al contenuto</a>

<header class="site-header">
    <a href="index.php" class="logo" aria-label="The (E-)Shop Around the Corner, torna alla home">
        <svg class="logo__mark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" aria-hidden="true">
            <path d="M6 10c0-1.1.9-2 2-2h12c2.2 0 4 1.8 4 4v22c0-1.1-.9-2-2-2H8a2 2 0 0 1-2-2V10z" fill="var(--primary-green)"/>
            <path d="M42 10c0-1.1-.9-2-2-2H28c-2.2 0-4 1.8-4 4v22c0-1.1.9-2 2-2h14a2 2 0 0 0 2-2V10z" fill="var(--dark-green)"/>
            <path d="M34 14l9 4-9 4-2-4z" fill="var(--accent-pink)"/>
        </svg>
        <span class="logo__text">The (E-)Shop<span class="logo__sub">around the corner</span></span>
    </a>

    <div class="header-tools">
        <div class="nav-categorie">
            <button type="button" class="btn-categorie" id="btnCategorie" aria-expanded="false" aria-controls="dropdownCategorie">
                Categorie
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="dropdown-categorie" id="dropdownCategorie"></div>
        </div>

        <form class="search-wrapper" role="search" action="index.php" method="get" autocomplete="off">
            <label for="headerSearchInput" class="visually-hidden">Cerca libri o autori</label>
            <div class="search-form">
                <input type="search" id="headerSearchInput" name="q" class="search-input" placeholder="Cerca libri, autori..." maxlength="100">
                <button type="submit" class="search-btn" aria-label="Cerca">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                </button>
            </div>
            <div id="live-suggestions" class="search-suggestions is-hidden"></div>
        </form>
    </div>

    <nav class="user-nav" aria-label="Area utente">
        <?php if (isset($_SESSION['IdUtente'])): ?>
            <a href="profilo.php" class="user-btn">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                <?php echo htmlspecialchars($_SESSION['IdUtente']); ?>
            </a>
            <?php if ($tipoHeader === 'cliente'): ?>
                <a href="miei_ordini.php" class="user-btn">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 3H3a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm-1 16H4V5h16v14zM6 7h12v2H6zm0 4h12v2H6zm0 4h8v2H6z"/></svg>
                    Ordini
                </a>
                <a href="preferiti.php" class="user-btn">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    Preferiti
                </a>
                <a href="carrello.php" class="user-btn cart-link">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zM7.17 14.75l.03-.12.9-1.63H17c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 21.44 4H5.21L4.54 2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5 16.28 5 17h14v-2H7.42a.25.25 0 0 1-.25-.25z"/></svg>
                    Carrello
                    <span class="cart-badge is-hidden" id="cartCount" aria-label="articoli nel carrello">0</span>
                </a>
            <?php elseif ($tipoHeader === 'venditore'): ?>
                <a href="dashboard_venditore.php" class="btn btn-primary btn-small">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    Dashboard
                </a>
            <?php endif; ?>
            <button type="button" class="user-btn user-btn--esci" id="btnApriLogout">Esci</button>
        <?php else: ?>
            <a href="login.php" class="user-btn">Accedi</a>
            <a href="registrazione.php" class="btn btn-primary btn-small">Registrati</a>
        <?php endif; ?>
    </nav>
</header>

<!-- DIALOG LOGOUT -->
<div class="modal-overlay" id="logout-overlay" role="dialog" aria-modal="true" aria-labelledby="logout-titolo">
    <div class="modal-box modal-box--small text-center">
        <h2 class="modal-title" id="logout-titolo">Uscire dall'account?</h2>
        <p class="muted">Verrai reindirizzato alla pagina di accesso.</p>
        <div class="button-row button-row--center">
            <a href="logout.php" class="btn btn-danger">Esci</a>
            <button type="button" class="btn btn-secondary" id="btnChiudiLogout">Annulla</button>
        </div>
    </div>
</div>

<script>
$(function() {
    /* --- Menu categorie (gerarchia padre/figlie) --- */
    $.get('api/ba_lista_categorie.php', function(resp) {
        const cats   = resp.categorie || [];
        const padri  = cats.filter(c => !c.nome_categoria_padre);
        const figlie = cats.filter(c => c.nome_categoria_padre);
        let html = '<a class="dropdown-tutte" href="index.php">Tutte le categorie</a>';
        padri.forEach(padre => {
            // La categoria padre è sempre cliccabile (mostra anche i prodotti delle sottocategorie)
            html += `<div class="dropdown-gruppo">
                <a class="dropdown-padre" href="index.php?cat=${encodeURIComponent(padre.nome_categoria)}">${escapeHtml(padre.nome_categoria)}</a>`;
            figlie.filter(f => f.nome_categoria_padre === padre.nome_categoria).forEach(f => {
                html += `<a class="dropdown-figlio" href="index.php?cat=${encodeURIComponent(f.nome_categoria)}">${escapeHtml(f.nome_categoria)}</a>`;
            });
            html += '</div>';
        });
        $('#dropdownCategorie').html(html);
    }, 'json');

    $('#btnCategorie').on('click', function() {
        const aperto = $('#dropdownCategorie').toggleClass('open').hasClass('open');
        $(this).attr('aria-expanded', aperto);
    });

    /* --- Suggerimenti di ricerca live (AJAX) --- */
    let timerRicerca = null;
    $('#headerSearchInput').on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(timerRicerca);
        if (query.length < 2) {
            $('#live-suggestions').addClass('is-hidden').empty();
            return;
        }
        // piccolo ritardo per non inviare una richiesta a ogni tasto
        timerRicerca = setTimeout(function() {
            $.get('api/ba_suggerimenti.php', { q: query }, function(resp) {
                const prodotti = resp.prodotti || [];
                if (prodotti.length === 0) {
                    $('#live-suggestions').addClass('is-hidden').empty();
                    return;
                }
                let html = '';
                prodotti.forEach(p => {
                    html += `<a class="suggestion-item" href="dettaglio_prodotto.php?id=${parseInt(p.id_prodotto)}">
                        <strong>${escapeHtml(p.nome)}</strong>
                        <small>${escapeHtml(p.autore || 'Autore non specificato')}</small>
                    </a>`;
                });
                $('#live-suggestions').html(html).removeClass('is-hidden');
            }, 'json');
        }, 250);
    });

    /* Chiusura di menu e suggerimenti cliccando fuori */
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-wrapper').length) $('#live-suggestions').addClass('is-hidden');
        if (!$(e.target).closest('.nav-categorie').length) {
            $('#dropdownCategorie').removeClass('open');
            $('#btnCategorie').attr('aria-expanded', 'false');
        }
    });

    /* --- Logout --- */
    $('#btnApriLogout').on('click', function() { $('#logout-overlay').addClass('open'); });
    $('#btnChiudiLogout').on('click', function() { $('#logout-overlay').removeClass('open'); });

    <?php if ($tipoHeader === 'cliente'): ?>
    updateCartBadge();
    <?php endif; ?>
});

/* Badge del carrello: usa lo stesso endpoint della pagina carrello */
function updateCartBadge() {
    $.get('api/ba_carrello.php', { action: 'list' }, function(resp) {
        const badge = $('#cartCount');
        let totale = 0;
        (resp.prodotti || []).forEach(p => totale += parseInt(p.quantita) || 0);
        badge.text(totale).toggleClass('is-hidden', totale === 0);
    }, 'json');
}
</script>
