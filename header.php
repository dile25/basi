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
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:28px; fill:currentColor;">
            <path d="M416 196.2c-13.8-30.8-49-92.2-100-92.2H196c-51 0-84.8 59.4-100 92.2-24 23-48 45.7-48 84.8v76c0 3.7 2.6 7 6.2 7.8C69.1 368.2 116.4 375 256 375s186.9-6.8 201.8-10.2c3.6-.8 6.2-4.1 6.2-7.8v-76c0-39-22.3-63.1-48-84.8zM190 128h132c40.5 0 62 60 62 70H128c0-10 27-70 62-70zm-78 203.7c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32c0 17.6-14.3 32-32 32zM328 300c0 8.8-7.2 16-16 16H200c-8.8 0-16-7.2-16-16s7.2-16 16-16h112c8.8 0 16 7.2 16 16zm72 32c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32zM160 384c-47.9 0-96-5-96-5 0 17-.3 29 6 29h85c6.3 0 5-13.2 5-24zM352 384c48 0 96-5 96-5 0 16 2 29-5 29h-86c-6.7 0-5-13.5-5-24z" />
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
            <a href="<?php echo ($_SESSION['tipoUtente'] === 'venditore') ? 'dashboard_venditore.php' : 'dashboard_cliente.php'; ?>" class="user-btn">
                👤 <?php echo htmlspecialchars($_SESSION['IdUtente']); ?>
            </a>

            <?php if ($_SESSION['tipoUtente'] === 'cliente'): ?>
                <a href="miei_ordini.php" class="user-btn">📦 Ordini</a>
                <a href="carrello.php" class="user-btn cart-link">
                    🛒 <span class="cart-badge" id="cartCount">0</span>
                </a>
            <?php endif; ?>

            <a href="logout.php" class="user-btn" style="color:#e74c3c;">❌ Esci</a>

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
        // Supponendo che l'API restituisca {status: 'ok', categorie: [...]}
        const cats = resp.categorie || resp;
        cats.forEach(cat => {
            $("#headerCatSelect").append(`<option value="${cat.IdCategoria}">${cat.NomeCategoria}</option>`);
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