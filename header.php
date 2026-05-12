<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    /* Badge del carrello migliorato e coordinato */
    .cart-link { position: relative; display: inline-block; }
    .cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #e74c3c; /* Rosso per attirare l'attenzione */
        color: white;
        font-size: 0.7em;
        padding: 2px 6px;
        border-radius: 50%;
        font-weight: 800;
        display: none;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        min-width: 18px;
        text-align: center;
        animation: pop-in 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes pop-in {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }

    /* Coerenza con il tema Green */
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 5%;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .user-nav { display: flex; align-items: center; gap: 20px; }
    .user-btn {
        text-decoration: none;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.95em;
        transition: 0.3s;
    }
    .user-btn:hover { color: var(--primary-green); }

    /* Bottone registrazione coordinato al resto del sito */
    .btn-reg-header {
        background: var(--primary-green) !important;
        color: white !important;
        padding: 8px 18px !important;
        border-radius: 6px !important;
    }
    .btn-reg-header:hover {
        background: var(--dark-green) !important;
    }
</style>

<div class="header-container">
    <a href="index.php" class="logo" style="text-decoration:none; font-weight:800; font-size:1.4em; color:var(--dark-green); display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:28px; fill:currentColor;">
    <path d="M21 4H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1zm-1 15H7a1 1 0 0 1 0-2h13v1a1 1 0 0 1-1 1zm0-4H7V6h13v9zM3 6a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h1V6H3z"/>
</svg>
        Book<span style="color:var(--primary-green)">Archive</span>
    </a>

    <div class="search-wrapper">
        <div class="search-bar" style="display:flex; border:1px solid var(--border-color); border-radius:50px; overflow:hidden; background:#f9f9f9;">
            <select id="headerCatSelect" class="search-select" style="border:none; background:none; padding:8px 15px; cursor:pointer; font-family:inherit; outline:none; border-right:1px solid #ddd;">
                <option value="">Tutte le categorie</option>
            </select>
            <input type="text" id="headerSearchInput" placeholder="Cerca libri, autori..." onkeypress="handleSearchKeyPress(event)" style="border:none; background:none; padding:8px 15px; width:250px; outline:none;">
            <button class="search-btn" onclick="eseguiRicerca()" style="border:none; background:var(--primary-green); color:white; padding:8px 15px; cursor:pointer;">🔍</button>
        </div>
    </div>

    <div class="user-nav">
    <?php if (isset($_SESSION['IdUtente'])): ?>
        <a href="profilo.php" class="user-btn" title="<?php echo htmlspecialchars($_SESSION['IdUtente']); ?>" style="display:flex; align-items:center; gap:6px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:22px; height:22px; fill:currentColor;">
        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
    </svg>
    <?php echo htmlspecialchars($_SESSION['IdUtente']); ?>
</a>

        <?php if ($_SESSION['tipoUtente'] === 'cliente'): ?>
            <a href="miei_ordini.php" class="user-btn">I miei ordini</a>
            <a href="carrello.php" class="user-btn cart-link">
                🛒 <span class="cart-badge" id="cartCount">0</span>
            </a>
        <?php endif; ?>

        <a href="logout.php" class="user-btn" style="color:#e74c3c;">Esci</a>

    <?php else: ?>
        <a href="login.php" class="user-btn">Accedi</a>
        <a href="registrazione.php" class="user-btn btn-reg-header">Registrati</a>
    <?php endif; ?>
</div>
</div>

<script>
$(document).ready(function() {
    // 1. Carica Categorie nell'header
    $.get("api/ba_categorie.php", function(resp) {
    const cats = resp.categorie || [];
    cats.forEach(cat => {
        $("#headerCatSelect").append(`<option value="${cat.nome_categoria}">${cat.nome_categoria}</option>`);
    });
});

    // 2. Aggiorna badge carrello solo se è un cliente loggato
    <?php if(isset($_SESSION['tipoUtente']) && $_SESSION['tipoUtente'] === 'cliente'): ?>
        updateCartBadge();
    <?php endif; ?>
});

function updateCartBadge() {
    $.get("api/ba_get_carrello.php", function(resp) {
        const badge = $("#cartCount");
        // Calcoliamo la quantità totale sommando le quantità dei prodotti
        if(resp.status === "ok" && resp.prodotti && resp.prodotti.length > 0) {
            let qtaTotale = 0;
            resp.prodotti.forEach(p => qtaTotale += parseInt(p.QuantitaNelCarrello));
            badge.text(qtaTotale).fadeIn();
        } else {
            badge.fadeOut();
        }
    });
}

function eseguiRicerca() {
    const q = $("#headerSearchInput").val().trim();
    const cat = $("#headerCatSelect").val();
    window.location.href = `index.php?q=${encodeURIComponent(q)}&cat=${cat}`;
}

function handleSearchKeyPress(e) {
    if (e.key === 'Enter') eseguiRicerca();
}
</script>