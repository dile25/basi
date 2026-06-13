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

    /* DROPDOWN CATEGORIE */
    .nav-categorie {
        position: relative;
        display: inline-block;
    }
    .btn-categorie {
        background: none;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 16px;
        font-family: inherit;
        font-size: 0.95em;
        font-weight: 600;
        color: var(--text-dark);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s;
    }
    .btn-categorie:hover {
        border-color: var(--primary-green);
        color: var(--primary-green);
    }
    .btn-categorie svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s;
    }
    .btn-categorie.open svg {
        transform: rotate(180deg);
    }
    .dropdown-categorie {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        z-index: 99999;
        min-width: 260px;
        padding: 8px 0;
        max-height: 480px;
        overflow-y: auto;
    }
    .dropdown-categorie.open { display: block; }
    .dropdown-padre {
        padding: 10px 18px 6px;
        font-size: 0.75em;
        font-weight: 800;
        color: var(--primary-green);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-top: 1px solid #f0f0f0;
        margin-top: 4px;
    }
    .dropdown-padre:first-child { border-top: none; margin-top: 0; }
    .dropdown-figlio {
        display: block;
        padding: 7px 18px 7px 28px;
        font-size: 0.88em;
        color: var(--text-dark);
        text-decoration: none;
        transition: background 0.15s;
        cursor: pointer;
    }
    .dropdown-figlio:hover {
        background: var(--light-green);
        color: var(--dark-green);
        font-weight: 600;
    }
</style>

<div class="header-container">
    <a href="index.php" class="logo" style="text-decoration:none; font-weight:800; font-size:1.4em; color:var(--dark-green); display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:28px; fill:currentColor;">
            <path d="M21 4H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1zm-1 15H7a1 1 0 0 1 0-2h13v1a1 1 0 0 1-1 1zm0-4H7V6h13v9zM3 6a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h1V6H3z"/>
        </svg>
        Book<span style="color:var(--primary-green)">Archive</span>
    </a>

    <div style="display:flex; align-items:center; gap:12px;">
        <div class="nav-categorie">
            <button class="btn-categorie" id="btnCategorie" onclick="toggleDropdown()">
                Categorie
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 10l5 5 5-5z"/>
                </svg>
            </button>
            <div class="dropdown-categorie" id="dropdownCategorie"></div>
        </div>

        <div class="search-wrapper">
            <div class="search-bar" style="display:flex; border:1px solid var(--border-color); border-radius:50px; overflow:hidden; background:#f9f9f9;">
                <input type="search" id="headerSearchInput" autocomplete="off" placeholder="Cerca libri, autori..."
                       onkeypress="handleSearchKeyPress(event)"
                       style="border:none; background:none; padding:8px 15px; width:250px; outline:none;">
                <button class="search-btn" onclick="eseguiRicerca()" style="border:none; background:var(--primary-green); color:white; padding:8px 15px; cursor:pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:16px; height:16px; display:block;">
                        <path d="M21 19.9l-4.69-4.69A7.5 7.5 0 1 0 4.5 15a7.5 7.5 0 0 0 4.81-1.75L14 17.94 19.9 21 21 19.9zM4.5 15a5.5 5.5 0 1 1 5.5 5.5A5.51 5.51 0 0 1 4.5 15z"/>
                    </svg>
                </button>
            </div>
            <div id="live-suggestions" class="search-suggestions"></div>
        </div>
    </div>

    <div class="user-nav">
        <?php if (isset($_SESSION['IdUtente'])): ?>
            <a href="profilo.php" class="user-btn" style="display:flex; align-items:center; gap:6px;">
                👤 <?php echo htmlspecialchars($_SESSION['IdUtente']); ?>
            </a>
            <?php if ($_SESSION['tipoUtente'] === 'cliente'): ?>
                <a href="miei_ordini.php" class="user-btn">I miei ordini</a>
                <a href="preferiti.php" class="user-btn">Preferiti</a>
                <a href="carrello.php" class="user-btn cart-link">
                    Carrello <span class="cart-badge" id="cartCount">0</span>
                </a>
            <?php endif; ?>
            <?php if ($_SESSION['tipoUtente'] === 'venditore'): ?>
                <a href="dashboard_venditore.php" class="btn-dashboard-venditore">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    Dashboard
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
let categoriaSelezionata = '';

$(document).ready(function() {

    $.get("api/ba_lista_categorie.php", function(resp) {
        const cats = resp.categorie || [];
        const padri = cats.filter(c => !c.nome_categoria_padre);
        const figlie = cats.filter(c => c.nome_categoria_padre);

        $("#dropdownCategorie").append(`
            <div class="dropdown-figlio" onclick="selezionaCategoria('')">Tutte le categorie</div>
        `);

padri.forEach(padre => {
    const sotto = figlie.filter(f => f.nome_categoria_padre === padre.nome_categoria);
    if (sotto.length === 0) return;

    $("#dropdownCategorie").append(`
        <div class="dropdown-padre">${padre.nome_categoria}</div>
    `);
    sotto.forEach(figlia => {
        $("#dropdownCategorie").append(`
            <div class="dropdown-figlio" onclick="selezionaCategoria('${figlia.nome_categoria}')">
                ${figlia.nome_categoria}
            </div>
        `);
    });
});
    }, "json");

    <?php if(isset($_SESSION['tipoUtente']) && $_SESSION['tipoUtente'] === 'cliente'): ?>
        updateCartBadge();
    <?php endif; ?>

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
                        <small>Autore: ${p.autore || 'Non specificato'}</small>
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
        if (!$(e.target).closest('.nav-categorie').length) {
            $('#btnCategorie').removeClass('open');
            $('#dropdownCategorie').removeClass('open');
        }
    });
});

function toggleDropdown() {
    $('#btnCategorie').toggleClass('open');
    $('#dropdownCategorie').toggleClass('open');
}

function selezionaCategoria(cat) {
    if (cat === '') {
        window.location.href = 'index.php';
    } else {
        window.location.href = `index.php?cat=${encodeURIComponent(cat)}`;
    }
}

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
    window.location.href = `index.php?q=${encodeURIComponent(q)}&cat=${encodeURIComponent(categoriaSelezionata)}`;
}

function handleSearchKeyPress(e) {
    if (e.key === 'Enter') eseguiRicerca();
}

</script>