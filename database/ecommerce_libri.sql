-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 26, 2026 alle 19:37
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_libri`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `CARRELLO`
--

CREATE TABLE `CARRELLO` (
  `id_carrello` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_prodotto` int(11) NOT NULL,
  `data_creazione` date DEFAULT curdate(),
  `quantita_prodotto` int(11) DEFAULT 1,
  `prezzo_totale` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `CATEGORIA`
--

CREATE TABLE `CATEGORIA` (
  `nome_categoria` varchar(100) NOT NULL,
  `nome_categoria_padre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `CATEGORIA`
--

INSERT INTO `CATEGORIA` (`nome_categoria`, `nome_categoria_padre`) VALUES
('Arte & Cultura', NULL),
('Gialli & Thriller', NULL),
('Hobby', NULL),
('Narrativa', NULL),
('Ragazzi', NULL),
('Saggistica', NULL),
('Architettura', 'Arte & Cultura'),
('Arte', 'Arte & Cultura'),
('Cinema', 'Arte & Cultura'),
('Fotografia', 'Arte & Cultura'),
('Musica', 'Arte & Cultura'),
('Crime', 'Gialli & Thriller'),
('Noir', 'Gialli & Thriller'),
('Spy story', 'Gialli & Thriller'),
('Thriller psicologico', 'Gialli & Thriller'),
('Cucina', 'Hobby'),
('Salute', 'Hobby'),
('Sport', 'Hobby'),
('Tecnologia', 'Hobby'),
('Viaggi', 'Hobby'),
('Fantascienza', 'Narrativa'),
('Fantasy', 'Narrativa'),
('Horror', 'Narrativa'),
('Racconti', 'Narrativa'),
('Romance', 'Narrativa'),
('Romanzi', 'Narrativa'),
('Storica', 'Narrativa'),
('0-6 anni', 'Ragazzi'),
('7-12 anni', 'Ragazzi'),
('Middle Grade', 'Ragazzi'),
('Young Adult', 'Ragazzi'),
('Economia', 'Saggistica'),
('Filosofia', 'Saggistica'),
('Politica', 'Saggistica'),
('Psicologia', 'Saggistica'),
('Scienze', 'Saggistica'),
('Storia', 'Saggistica');

-- --------------------------------------------------------

--
-- Struttura della tabella `CLIENTE`
--

CREATE TABLE `CLIENTE` (
  `username` varchar(30) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `indirizzo_predefinito` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `CLIENTE`
--

INSERT INTO `CLIENTE` (`username`, `telefono`, `indirizzo_predefinito`) VALUES
('ciao', '3333333333', 'via ciao, ciao, 11111, ci');

-- --------------------------------------------------------

--
-- Struttura della tabella `DESCRIVE`
--

CREATE TABLE `DESCRIVE` (
  `id_prodotto` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `DESCRIVE`
--

INSERT INTO `DESCRIVE` (`id_prodotto`, `nome_categoria`) VALUES
(1, 'Fantasy'),
(2, 'Fantasy'),
(3, 'Fantasy'),
(4, 'Fantasy'),
(5, 'Fantasy'),
(6, 'Fantasy'),
(7, 'Romanzi'),
(8, 'Romanzi'),
(9, 'Racconti'),
(10, 'Romanzi'),
(11, 'Fantascienza'),
(12, 'Fantascienza'),
(13, 'Fantascienza'),
(14, 'Thriller psicologico'),
(15, 'Crime'),
(16, 'Thriller psicologico'),
(17, 'Crime'),
(18, 'Storia'),
(19, 'Scienze'),
(20, 'Scienze'),
(21, 'Filosofia'),
(22, 'Cucina'),
(23, 'Viaggi'),
(24, 'Young Adult'),
(25, 'Young Adult'),
(26, 'Narrativa'),
(27, 'Architettura');

-- --------------------------------------------------------

--
-- Struttura della tabella `IMMAGINE_PRODOTTO`
--

CREATE TABLE `IMMAGINE_PRODOTTO` (
  `id_immagine_prodotto` int(11) NOT NULL,
  `id_prodotto` int(11) DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `alt_text` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `IMMAGINE_PRODOTTO`
--

INSERT INTO `IMMAGINE_PRODOTTO` (`id_immagine_prodotto`, `id_prodotto`, `url`, `alt_text`) VALUES
(19, 1, 'img/harry_potter_1.jpg', 'Copertina Harry Potter 1'),
(20, 2, 'img/harry_potter_2.jpg', 'Copertina Harry Potter 2'),
(21, 5, 'img/trono_di_spade_1.jpg', 'Copertina Il Trono di Spade'),
(22, 7, 'img/senilita.jpg', 'Copertina Senilita'),
(23, 8, 'img/coscienza_zeno.jpg', 'Copertina La coscienza di Zeno');

-- --------------------------------------------------------

--
-- Struttura della tabella `IMMAGINE_RECENSIONE`
--

CREATE TABLE `IMMAGINE_RECENSIONE` (
  `id_immagine_recensione` int(11) NOT NULL,
  `id_recensione` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `alt_text` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `INCLUSO_IN`
--

CREATE TABLE `INCLUSO_IN` (
  `id_prodotto` int(11) NOT NULL,
  `id_ordine` int(11) NOT NULL,
  `quantita_prodotto` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ORDINE`
--

CREATE TABLE `ORDINE` (
  `id_ordine` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_pagamento` int(11) DEFAULT NULL,
  `data` date NOT NULL,
  `stato` varchar(50) DEFAULT 'In elaborazione',
  `totale` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ORDINE`
--

INSERT INTO `ORDINE` (`id_ordine`, `username`, `id_pagamento`, `data`, `stato`, `totale`) VALUES
(3, 'ciao', NULL, '2026-05-12', 'In elaborazione', 114.00),
(4, 'ciao', 1, '2026-05-12', 'Spedito', 114.00),
(5, 'ciao', 3, '2026-05-12', 'Pagato', 12.00),
(6, 'ciao', 5, '2026-05-20', 'Pagato', 114.00);

-- --------------------------------------------------------

--
-- Struttura della tabella `PACCHETTO`
--

CREATE TABLE `PACCHETTO` (
  `id_pacchetto` int(11) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `sconto` decimal(5,2) DEFAULT NULL,
  `attivo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `PACCHETTO`
--

INSERT INTO `PACCHETTO` (`id_pacchetto`, `descrizione`, `sconto`, `attivo`) VALUES
(1, 'Saga Completa / Promo Autore', 15.00, 1),
(2, 'Promo Speciale', 20.00, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `PAGAMENTO`
--

CREATE TABLE `PAGAMENTO` (
  `id_pagamento` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `metodo` varchar(50) DEFAULT NULL,
  `stato` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `PAGAMENTO`
--

INSERT INTO `PAGAMENTO` (`id_pagamento`, `username`, `metodo`, `stato`) VALUES
(1, 'ciao', 'Carta', 'Completato'),
(2, 'ciao', 'Carta', 'Carta che termina con 7590'),
(3, 'ciao', 'Carta', 'Completato'),
(4, 'ciao', 'Carta', 'salvato:Carta che termina con 1460'),
(5, 'ciao', 'Carta', 'Completato'),
(6, 'ciao', 'PayPal', 'salvato:ciao@gmail.com');

-- --------------------------------------------------------

--
-- Struttura della tabella `PREFERITI`
--

CREATE TABLE `PREFERITI` (
  `id_preferiti` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_prodotto` int(11) NOT NULL,
  `data_aggiunta` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `PRODOTTO`
--

CREATE TABLE `PRODOTTO` (
  `id_prodotto` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_pacchetto` int(11) DEFAULT NULL,
  `nome` varchar(200) NOT NULL,
  `autore` varchar(150) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `quantita_disponibile` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `PRODOTTO`
--

INSERT INTO `PRODOTTO` (`id_prodotto`, `username`, `id_pacchetto`, `nome`, `autore`, `descrizione`, `prezzo`, `quantita_disponibile`) VALUES
(1, 'bella', 1, 'Harry Potter e la pietra filosofale', 'J.K. Rowling', 'Il primo capitolo della saga del mago piu famoso del mondo.', 12.00, 20),
(2, 'bella', 1, 'Harry Potter e la camera dei segreti', 'J.K. Rowling', 'Il secondo anno di Harry a Hogwarts tra misteri e creature oscure.', 12.50, 15),
(3, 'bella', 1, 'Harry Potter e il prigioniero di Azkaban', 'J.K. Rowling', 'Il terzo anno vede la fuga del pericoloso Sirius Black.', 13.00, 18),
(4, 'bella', 1, 'Harry Potter e il calice di fuoco', 'J.K. Rowling', 'Il Torneo Tremaghi mette a dura prova la vita di Harry.', 15.00, 12),
(5, 'bella', 1, 'Il Trono di Spade', 'George R.R. Martin', 'L inizio dell epica saga fantasy tra intrighi e potere nei Sette Regni.', 19.00, 10),
(6, 'bella', 1, 'Il Grande Inverno', 'George R.R. Martin', 'La stirpe degli Stark si frammenta mentre l inverno sta arrivando.', 19.00, 10),
(7, 'bella', NULL, 'Senilita', 'Italo Svevo', 'Il capolavoro modernista che esplora l inettitudine e le illusioni di Emilio Brentani.', 10.00, 25),
(8, 'bella', NULL, 'La coscienza di Zeno', 'Italo Svevo', 'Le confessioni psicoanalitiche di Zeno Cosini tra fumo e nevrosi.', 11.00, 30),
(9, 'bella', NULL, 'Il gioco segreto', 'Elsa Morante', 'Una raccolta di racconti intensi e sognanti della celebre autrice.', 9.50, 14),
(10, 'bella', NULL, 'Fosca', 'Igino Ugo Tarchetti', 'Il romanzo simbolo della Scapigliatura.', 8.50, 15),
(11, 'bella', NULL, 'Dune', 'Frank Herbert', 'Il monumentale romanzo di fantascienza ambientato su Arrakis.', 16.50, 22),
(12, 'bella', NULL, 'Neuromante', 'William Gibson', 'Il manifesto del cyberpunk.', 13.00, 8),
(13, 'bella', NULL, 'Guida galattica per gli autostoppisti', 'Douglas Adams', 'Un avventura spaziale esilarante.', 12.00, 19),
(14, 'bella', NULL, 'Il silenzio degli innocenti', 'Thomas Harris', 'Thriller psicologico con Hannibal Lecter.', 14.00, 11),
(15, 'bella', NULL, 'L alienista', 'Caleb Carr', 'Thriller storico ambientato nella New York ottocentesca.', 15.00, 7),
(16, 'bella', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Thriller psicologico basato su ricordi distorti.', 13.50, 20),
(17, 'bella', NULL, 'Dieci piccoli indiani', 'Agatha Christie', 'Uno dei gialli piu celebri di sempre.', 10.50, 35),
(18, 'bella', NULL, 'Sapiens. Da animali a dei', 'Yuval Noah Harari', 'Breve storia dell umanita.', 18.00, 15),
(19, 'bella', NULL, 'Cosmo', 'Carl Sagan', 'Viaggio attraverso spazio e scienza.', 17.00, 9),
(20, 'bella', NULL, 'L ordine del tempo', 'Carlo Rovelli', 'Saggio sul tempo.', 12.00, 25),
(21, 'bella', NULL, 'Cosi parlo Zarathustra', 'Friedrich Nietzsche', 'Opera filosofica sull oltreuomo.', 9.00, 14),
(22, 'bella', NULL, 'Il cucchiaio d argento', 'AA.VV.', 'Grande classico della cucina italiana.', 45.00, 5),
(23, 'bella', NULL, 'In Patagonia', 'Bruce Chatwin', 'Celebre libro di viaggio.', 11.50, 12),
(24, 'bella', NULL, 'Percy Jackson e gli dei dell Olimpo: Il ladro di fulmini', 'Rick Riordan', 'Mitologia greca e avventure moderne.', 12.00, 17),
(25, 'bella', NULL, 'Hunger Games', 'Suzanne Collins', 'Romanzo distopico young adult.', 14.50, 21),
(26, 'bella', NULL, 'L altalena', 'Carlo Dossi', 'Opera della Scapigliatura milanese.', 11.80, 0),
(27, 'bella', NULL, 'ciao', 'ciaociao', 'hqghfpiwqpfipwhf', 21.00, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `RECENSIONE`
--

CREATE TABLE `RECENSIONE` (
  `id_recensione` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_prodotto` int(11) NOT NULL,
  `testo` text DEFAULT NULL,
  `valutazione` tinyint(4) DEFAULT NULL CHECK (`valutazione` between 1 and 5),
  `data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `UTENTE`
--

CREATE TABLE `UTENTE` (
  `username` varchar(30) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `data_registrazione` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `UTENTE`
--

INSERT INTO `UTENTE` (`username`, `nome`, `cognome`, `email`, `password_hash`, `data_registrazione`) VALUES
('bella', 'bella', 'bella', 'bella@gmail.com', '$2y$10$JBzerbT7UAi2fCeDvjfNkOwMZXMspLajvjzc4jIty4baraenGqM8a', '2026-04-29'),
('ciao', 'ciao', 'ciao', 'ciao@gmail.com', '$2y$10$aJe2CLwBFK6uBFq4eP.K7OlZDWtYXbz/IztD/zJfBvehpc/EmIYvq', '2026-04-29'),
('ciao1', 'ciao1', 'ciao1', 'ciao1@gmail.com', '$2y$10$zKjT7zwM94OAWt2S5V9s9uUi0BZJv.4/.8J3CYqCl48w0w90slqrW', '2026-05-06');

-- --------------------------------------------------------

--
-- Struttura della tabella `VENDITORE`
--

CREATE TABLE `VENDITORE` (
  `username` varchar(30) NOT NULL,
  `partita_iva` varchar(20) NOT NULL,
  `ragione_sociale` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `VENDITORE`
--

INSERT INTO `VENDITORE` (`username`, `partita_iva`, `ragione_sociale`) VALUES
('bella', 'bella', 'bella'),
('ciao1', 'ciao1', 'ciao1');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `CARRELLO`
--
ALTER TABLE `CARRELLO`
  ADD PRIMARY KEY (`id_carrello`),
  ADD KEY `username` (`username`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `CATEGORIA`
--
ALTER TABLE `CATEGORIA`
  ADD PRIMARY KEY (`nome_categoria`),
  ADD KEY `nome_categoria_padre` (`nome_categoria_padre`);

--
-- Indici per le tabelle `CLIENTE`
--
ALTER TABLE `CLIENTE`
  ADD PRIMARY KEY (`username`);

--
-- Indici per le tabelle `DESCRIVE`
--
ALTER TABLE `DESCRIVE`
  ADD PRIMARY KEY (`id_prodotto`,`nome_categoria`),
  ADD KEY `nome_categoria` (`nome_categoria`);

--
-- Indici per le tabelle `IMMAGINE_PRODOTTO`
--
ALTER TABLE `IMMAGINE_PRODOTTO`
  ADD PRIMARY KEY (`id_immagine_prodotto`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `IMMAGINE_RECENSIONE`
--
ALTER TABLE `IMMAGINE_RECENSIONE`
  ADD PRIMARY KEY (`id_immagine_recensione`),
  ADD KEY `id_recensione` (`id_recensione`);

--
-- Indici per le tabelle `INCLUSO_IN`
--
ALTER TABLE `INCLUSO_IN`
  ADD PRIMARY KEY (`id_prodotto`,`id_ordine`),
  ADD KEY `id_ordine` (`id_ordine`);

--
-- Indici per le tabelle `ORDINE`
--
ALTER TABLE `ORDINE`
  ADD PRIMARY KEY (`id_ordine`),
  ADD KEY `username` (`username`),
  ADD KEY `id_pagamento` (`id_pagamento`);

--
-- Indici per le tabelle `PACCHETTO`
--
ALTER TABLE `PACCHETTO`
  ADD PRIMARY KEY (`id_pacchetto`);

--
-- Indici per le tabelle `PAGAMENTO`
--
ALTER TABLE `PAGAMENTO`
  ADD PRIMARY KEY (`id_pagamento`),
  ADD KEY `username` (`username`);

--
-- Indici per le tabelle `PREFERITI`
--
ALTER TABLE `PREFERITI`
  ADD PRIMARY KEY (`id_preferiti`),
  ADD KEY `username` (`username`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `PRODOTTO`
--
ALTER TABLE `PRODOTTO`
  ADD PRIMARY KEY (`id_prodotto`),
  ADD KEY `username` (`username`),
  ADD KEY `id_pacchetto` (`id_pacchetto`);

--
-- Indici per le tabelle `RECENSIONE`
--
ALTER TABLE `RECENSIONE`
  ADD PRIMARY KEY (`id_recensione`),
  ADD KEY `username` (`username`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `UTENTE`
--
ALTER TABLE `UTENTE`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `VENDITORE`
--
ALTER TABLE `VENDITORE`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `CARRELLO`
--
ALTER TABLE `CARRELLO`
  MODIFY `id_carrello` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `IMMAGINE_PRODOTTO`
--
ALTER TABLE `IMMAGINE_PRODOTTO`
  MODIFY `id_immagine_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT per la tabella `IMMAGINE_RECENSIONE`
--
ALTER TABLE `IMMAGINE_RECENSIONE`
  MODIFY `id_immagine_recensione` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ORDINE`
--
ALTER TABLE `ORDINE`
  MODIFY `id_ordine` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `PACCHETTO`
--
ALTER TABLE `PACCHETTO`
  MODIFY `id_pacchetto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `PAGAMENTO`
--
ALTER TABLE `PAGAMENTO`
  MODIFY `id_pagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `PREFERITI`
--
ALTER TABLE `PREFERITI`
  MODIFY `id_preferiti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `PRODOTTO`
--
ALTER TABLE `PRODOTTO`
  MODIFY `id_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT per la tabella `RECENSIONE`
--
ALTER TABLE `RECENSIONE`
  MODIFY `id_recensione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `CARRELLO`
--
ALTER TABLE `CARRELLO`
  ADD CONSTRAINT `carrello_ibfk_1` FOREIGN KEY (`username`) REFERENCES `CLIENTE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `carrello_ibfk_2` FOREIGN KEY (`id_prodotto`) REFERENCES `PRODOTTO` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `CATEGORIA`
--
ALTER TABLE `CATEGORIA`
  ADD CONSTRAINT `categoria_ibfk_1` FOREIGN KEY (`nome_categoria_padre`) REFERENCES `CATEGORIA` (`nome_categoria`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `CLIENTE`
--
ALTER TABLE `CLIENTE`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`username`) REFERENCES `UTENTE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `DESCRIVE`
--
ALTER TABLE `DESCRIVE`
  ADD CONSTRAINT `descrive_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `PRODOTTO` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `descrive_ibfk_2` FOREIGN KEY (`nome_categoria`) REFERENCES `CATEGORIA` (`nome_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `IMMAGINE_PRODOTTO`
--
ALTER TABLE `IMMAGINE_PRODOTTO`
  ADD CONSTRAINT `immagine_prodotto_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `PRODOTTO` (`id_prodotto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `IMMAGINE_RECENSIONE`
--
ALTER TABLE `IMMAGINE_RECENSIONE`
  ADD CONSTRAINT `immagine_recensione_ibfk_1` FOREIGN KEY (`id_recensione`) REFERENCES `RECENSIONE` (`id_recensione`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `INCLUSO_IN`
--
ALTER TABLE `INCLUSO_IN`
  ADD CONSTRAINT `incluso_in_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `PRODOTTO` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `incluso_in_ibfk_2` FOREIGN KEY (`id_ordine`) REFERENCES `ORDINE` (`id_ordine`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `ORDINE`
--
ALTER TABLE `ORDINE`
  ADD CONSTRAINT `ordine_ibfk_1` FOREIGN KEY (`username`) REFERENCES `CLIENTE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ordine_ibfk_2` FOREIGN KEY (`id_pagamento`) REFERENCES `PAGAMENTO` (`id_pagamento`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `PAGAMENTO`
--
ALTER TABLE `PAGAMENTO`
  ADD CONSTRAINT `pagamento_ibfk_1` FOREIGN KEY (`username`) REFERENCES `CLIENTE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `PREFERITI`
--
ALTER TABLE `PREFERITI`
  ADD CONSTRAINT `preferiti_ibfk_1` FOREIGN KEY (`username`) REFERENCES `CLIENTE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `preferiti_ibfk_2` FOREIGN KEY (`id_prodotto`) REFERENCES `PRODOTTO` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `PRODOTTO`
--
ALTER TABLE `PRODOTTO`
  ADD CONSTRAINT `prodotto_ibfk_1` FOREIGN KEY (`username`) REFERENCES `VENDITORE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodotto_ibfk_2` FOREIGN KEY (`id_pacchetto`) REFERENCES `PACCHETTO` (`id_pacchetto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `RECENSIONE`
--
ALTER TABLE `RECENSIONE`
  ADD CONSTRAINT `recensione_ibfk_1` FOREIGN KEY (`username`) REFERENCES `CLIENTE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recensione_ibfk_2` FOREIGN KEY (`id_prodotto`) REFERENCES `PRODOTTO` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `VENDITORE`
--
ALTER TABLE `VENDITORE`
  ADD CONSTRAINT `venditore_ibfk_1` FOREIGN KEY (`username`) REFERENCES `UTENTE` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
