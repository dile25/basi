# The (E-)Shop Around the Corner — installazione e note

## Struttura
```
floris_valenti/            → copiare in htdocs/
├── db_connect.php         livello dati: connessione MySQLi
├── head.php, header.php   parti comuni incluse da tutte le pagine
├── *.php                  pagine (livello di presentazione), JS inline con jQuery/AJAX
├── style.css              unico foglio di stile
├── img/                   immagini (prodotti/ e recensioni/ create in automatico)
├── api/                   livello di business, risposte JSON
│   ├── comune.php             sessione, ob_start, JSON, controllo accessi, validazione, upload
│   ├── funzioni_carrello.php  calcolo prezzi e sconti (carrello e checkout)
│   ├── funzioni_prodotto.php  validazione prodotto, categorie, foto
│   └── ba_*.php               un file per operazione
└── sql/migrazione.sql
```

## Passi
1. Backup: phpMyAdmin → database → Esporta.
2. Il database deve chiamarsi `floris_valenti` (l'esportazione che mi hai mandato si chiamava `ecommerce_libri`).
3. phpMyAdmin → `floris_valenti` → SQL → incolla ed esegui `sql/migrazione.sql` (una sola volta).
4. Sostituisci i file del progetto con questi. Elimina da `api/`:
   `ba_categorie.php`, `ba_testate.php`, `ba_abbonamenti_disponibili.php`, `ba_abbonamenti_venditore.php`,
   `ba_get_carrello.php`, `ba_consigliati.php`, `ba_trasferisci_guadagno.php`, `ba_metodi_pagamento_cliente.php`.
5. Controlla che in `C:\xampp\php\php.ini` siano attive (senza `;` davanti) `extension=mbstring` e `extension=fileinfo`, poi riavvia Apache.
6. Verifica che esista `img/default.jpg`.
7. Riesporta il database per la consegna.

## Da citare nella relazione
- **Risorse esterne:** jQuery 3.6.0 (CDN code.jquery.com); icone SVG da Material Icons (Google, licenza Apache 2.0).
- **Tre livelli:** pagine PHP (presentazione) → AJAX/JSON → `api/` (business) → MySQL (dati).
- **Sicurezza:** prepared statement ovunque; validazione lato client e ripetuta lato server; escape dell'output con `escapeHtml()` (i dati si salvano come inseriti e si neutralizzano quando si stampano); `password_hash`/`password_verify` al posto di `hash('sha256')` visto a lezione, perché aggiunge il salt e rallenta gli attacchi a forza bruta; `session_regenerate_id` al login; tipo delle immagini controllato sul contenuto (`finfo`), nome file generato dal server.
- **Transazioni:** registrazione, inserimento/modifica prodotto, checkout (con `SELECT ... FOR UPDATE` sui prodotti), annullamento ordine, eliminazione account.
- **Ridondanze:**
  - `incluso_in.prezzo_unitario` è voluta: conserva il prezzo pagato anche se il prezzo del prodotto cambia.
  - `ordine.totale` è voluta: è la somma delle righe, salvata per non ricalcolarla a ogni lettura dello storico.
  - Eliminate: `carrello.prezzo_totale` (mai aggiornato, il totale si calcola) e la categoria padre in `descrive` quando c'è già la figlia.
- **Eliminazioni logiche:** prodotti e utenti hanno `attivo = 0` invece di essere cancellati, perché le righe degli ordini devono restare nello storico (la FK `incluso_in → prodotto` ora è `ON DELETE RESTRICT`).
- **Pacchetti:** sconto fisso applicato a tutti i prodotti del pacchetto solo se sono tutti nel carrello; calcolato solo dal server.
