function caricaLibri(filtri = {}) {
    $.get('api/ba_ricerca.php', filtri, function(resp) {
        if(resp.status === 'ok') {
            let html = "";
            resp.prodotti.forEach(p => {
                // Calcoliamo le stelle (es. 4.2 diventa 4 stelle piene)
                let stellePiene = Math.floor(p.MediaVoto);
                let ratingHtml = "⭐".repeat(stellePiene) + (p.NumRecensioni > 0 ? ` (${p.MediaVoto})` : " (Nuovo)");

                html += `
                <div class="book-card">
                    <img src="${p.URLfoto}" alt="${p.Titolo}">
                    <h4>${p.Titolo}</h4>
                    <p class="author">${p.NomeNeg}</p>
                    <p class="rating">${ratingHtml}</p>
                    <p class="price">€ ${parseFloat(p.Prezzo).toFixed(2)}</p>
                    <button onclick="aggiungiAlCarrello(${p.IdProdotto})">Aggiungi al Carrello</button>
                </div>`;
            });
            $("#container-libri").html(html);
        }
    });
}