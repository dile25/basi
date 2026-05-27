<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .cart-link { position: relative; display: inline-block; }
    .cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #e74c3c;
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

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 5%;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .search-wrapper { position: relative; }
    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 9999;
        display: none;
        margin-top: 5px;
        max-height: 300px;
        overflow-y: auto;
    }
    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        display: flex;
        flex-direction: column;
    }
    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover { background: #f5f9f6; }
    .suggestion-item strong { color: var(--text-dark); font-size: 0.95em; }
    .suggestion-item small { color: #777; font-size: 0.8em; margin-top: 2px; }

    .user-nav { display: flex; align-items: center; gap: 20px; }
    .user-btn {
        text-decoration: none;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.95em;
        transition: 0.3s;
    }
    .user-btn:hover { color: var(--primary-green); }

    .btn-reg-header {
        background: var(--primary-green) !important;
        color: white !important;
        padding: 8px 18px !important;
        border-radius: 6px !important;
    }
    .btn-reg-header:hover { background: var(--dark-green) !important; }

    /* ============================================================
       BOTTONE DASHBOARD VENDITORE
    ============================================================ */
    .btn-dashboard-venditore {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, var(--dark-green, #1a6b3c), var(--primary-green, #27ae60));
        color: white !important;
        padding: 8px 16px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 0.9em !important;
        text-decoration: none !important;
        box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
        transition: all 0.2s ease !important;
        border: none;
    }

    .btn-dashboard-venditore:hover {
        background: linear-gradient(135deg, #145a32, #1e8449) !important;
        box-shadow: 0 4px 14px rgba(39, 174, 96, 0.45);
        transform: translateY(-1px);
        color: white !important;
    }

    .btn-dashboard-venditore svg {
        width: 16px;
        height: 16px;
        fill: white;
        flex-shrink: 0;
    }
</style>

<div class="header-container">
    <a href="index.php" class="logo" style="text-decoration:none; font-weight:800; font-size:1.4em; color:var(--dark-green); display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:28px; fill:currentColor;">
            <path d="M21 4H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1zm-1 15H7a1 1 0 0 1 0-2h13v1a1 1 0 0 1-1 1zm0-4H7V6h13v9zM3 6a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h1V6H3z"/>
        </svg>
        Book<span style="color:var(--primary-green)">Archive</span>
    </a>

    <form role="search" autocomplete="off" onsubmit="return false;">
        <div class="search-wrapper">
            <div class="search-bar" style="display:flex; border:1px solid var(--border-color); border-radius:50px; overflow:hidden; background:#f9f9f9;">
                <select id="headerCatSelect" class="search-select" style="border:none; background:none; padding:8px 15px; cursor:pointer; font-family:inherit; outline:none; border-right:1px solid #ddd;">
                    <option value="">Tutte le categorie</option>
                </select>

                <input type="search" id="headerSearchInput" autocomplete="off" placeholder="Cerca libri, autori..."
                       onkeypress="handleSearchKeyPress(event)"
                       style="border:none; background:none; padding:8px 15px; width:250px; outline:none;">

                <button class="search-btn" onclick="eseguiRicerca()" style="border:none; background:var(--primary-green); color:white; padding:8px 15px; cursor:pointer;">🔍</button>
            </div>
            <div id="live-suggestions" class="search-suggestions"></div>
        </div>
    </form>

    <div class="user-nav">
        <?php if (isset($_SESSION['IdUtente'])): ?>

            <a href="profilo.php" class="user-btn" title="Vai alla pagina personale" style="display:flex; align-items:center; gap:6px;">
                👤 <?php echo htmlspecialchars($_SESSION['IdUtente']); ?>
            </a>

            <?php if ($_SESSION['tipoUtente'] === 'cliente'): ?>
                <a href="miei_ordini.php" class="user-btn">I miei ordini</a>
                <a href="preferiti.php" class="user-btn">❤️ Preferiti</a>
                <a href="carrello.php" class="user-btn cart-link">
                    🛒 Carrello <span class="cart-badge" id="cartCount">0</span>
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['tipoUtente'] === 'venditore'): ?>
                <!-- ============================
                     DASHBOARD VENDITORE
                ============================ -->
                <a href="dashboard_venditore.php" class="btn-dashboard-venditore">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="gestione_prodotti.php" class="user-btn" title="Gestisci il tuo catalogo">📦 Prodotti</a>
                <a href="gestione_ordini.php" class="user-btn" title="Vedi gli ordini ricevuti">📋 Ordini</a>
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
    // Carica categorie nel select
    $.get("api/ba_categorie.php", function(resp) {
        const cats = resp.categorie || [];
        cats.forEach(cat => {
            $("#headerCatSelect").append(`<option value="${cat.nome_categoria}">${cat.nome_categoria}</option>`);
        });
    });

    // Badge carrello (solo clienti loggati)
    <?php if(isset($_SESSION['tipoUtente']) && $_SESSION['tipoUtente'] === 'cliente'): ?>
        updateCartBadge();
    <?php endif; ?>

    // Suggerimenti live
    $("#headerSearchInput").on("input", function() {
        const query = $(this).val().trim();
        if (query.length < 2) {
            $("#live-suggestions").hide().empty();
            return;
        }
        $.get("api/ba_suggerimenti.php", { q: query }, function(resp) {
            if (resp.prodotti && resp.prodotti.length > 0) {
                let htmlSuggestions = "";
                resp.prodotti.forEach(p => {
                    htmlSuggestions += `
                    <div class="suggestion-item" onclick="location.href='dettaglio_prodotto.php?id=${p.id_prodotto}'">
                        <strong>${p.nome}</strong>
                        <small>✍ Autore: ${p.autore || 'Non specificato'}</small>
                    </div>`;
                });
                $("#live-suggestions").html(htmlSuggestions).show();
            } else {
                $("#live-suggestions").hide().empty();
            }
        }, "json");
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            $("#live-suggestions").hide();
        }
    });
});

function updateCartBadge() {
    $.get("api/ba_get_carrello.php", function(resp) {
        const badge = $("#cartCount");
        if (resp.status === "ok" && resp.prodotti && resp.prodotti.length > 0) {
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