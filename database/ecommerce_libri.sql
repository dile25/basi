-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 26, 2026 alle 19:37
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mag 27, 2026 alle 11:59
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

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
-- Struttura della tabella `carrello`
--

CREATE TABLE `carrello` (
  `id_carrello` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_prodotto` int(11) NOT NULL,
  `data_creazione` date DEFAULT curdate(),
  `quantita_prodotto` int(11) DEFAULT 1,
  `prezzo_totale` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `carrello`
--

INSERT INTO `carrello` (`id_carrello`, `username`, `id_prodotto`, `data_creazione`, `quantita_prodotto`, `prezzo_totale`) VALUES
(15, 'profilocliente', 25, '2026-05-27', 4, 40.00),
(16, 'profilocliente', 36, '2026-05-27', 13, 234.00),
(18, 'profilocliente', 42, '2026-05-27', 1, 0.00),
(19, 'profilocliente', 33, '2026-05-27', 1, 0.00),
(20, 'profilocliente', 33, '2026-05-27', 1, 0.00);

-- --------------------------------------------------------

--
-- Struttura della tabella `categoria`
--

CREATE TABLE `categoria` (
  `nome_categoria` varchar(100) NOT NULL,
  `nome_categoria_padre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `categoria`
--

INSERT INTO `categoria` (`nome_categoria`, `nome_categoria_padre`) VALUES
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
-- Struttura della tabella `cliente`
--

CREATE TABLE `cliente` (
  `username` varchar(30) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `indirizzo_predefinito` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `cliente`
--

INSERT INTO `cliente` (`username`, `telefono`, `indirizzo_predefinito`) VALUES
('ciao', '3333333333', 'via ciao, ciao, 11111, ci'),
('profilocliente', '', 'via grande 1, città, 50111, provincia');

-- --------------------------------------------------------

--
-- Struttura della tabella `descrive`
--

CREATE TABLE `descrive` (
  `id_prodotto` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `descrive`
--

INSERT INTO `descrive` (`id_prodotto`, `nome_categoria`) VALUES
(17, 'Narrativa'),
(19, 'Fantasy'),
(19, 'Young Adult'),
(20, 'Fantasy'),
(20, 'Young Adult'),
(21, 'Fantasy'),
(21, 'Young Adult'),
(22, 'Fantasy'),
(22, 'Young Adult'),
(23, 'Fantasy'),
(23, 'Romanzi'),
(24, 'Fantasy'),
(24, 'Romanzi'),
(25, 'Narrativa'),
(25, 'Romanzi'),
(26, 'Narrativa'),
(26, 'Romanzi'),
(27, 'Narrativa'),
(27, 'Racconti'),
(28, 'Narrativa'),
(28, 'Romanzi'),
(29, 'Fantascienza'),
(30, 'Fantascienza'),
(31, 'Fantascienza'),
(32, 'Thriller psicologico'),
(33, 'Crime'),
(34, 'Thriller psicologico'),
(35, 'Crime'),
(36, 'Storia'),
(37, 'Scienze'),
(38, 'Scienze'),
(39, 'Filosofia'),
(40, 'Cucina'),
(41, 'Viaggi'),
(42, '7-12 anni'),
(43, 'Young Adult');

-- --------------------------------------------------------

--
-- Struttura della tabella `immagine_prodotto`
--

CREATE TABLE `immagine_prodotto` (
  `id_immagine_prodotto` int(11) NOT NULL,
  `id_prodotto` int(11) DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `alt_text` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `immagine_prodotto`
--

INSERT INTO `immagine_prodotto` (`id_immagine_prodotto`, `id_prodotto`, `url`, `alt_text`) VALUES
(2, NULL, 'img/1777461434_69f1e8bad6d61.png', 'Copertina gsg'),
(3, NULL, 'img/1777459810_69f1e26282985.psd', 'Copertina gsg'),
(4, NULL, 'img/1777460054_69f1e356eeb3b.psd', 'Copertina gsg'),
(5, NULL, 'img/1777460466_69f1e4f261568.psd', 'Copertina cjoaoih'),
(6, NULL, 'img/1777460729_69f1e5f93fae4.jpg', 'Copertina cjoaoih'),
(7, 17, 'img/1779269931_6a0d812b8f1b5.jpg', 'Copertina normal people'),
(8, NULL, 'img/1779270220_6a0d824cc538c.jpeg', 'Copertina Harry Potter');

-- --------------------------------------------------------

--
-- Struttura della tabella `immagine_recensione`
--

CREATE TABLE `immagine_recensione` (
  `id_immagine_recensione` int(11) NOT NULL,
  `id_recensione` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `alt_text` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `incluso_in`
--

CREATE TABLE `incluso_in` (
  `id_prodotto` int(11) NOT NULL,
  `id_ordine` int(11) NOT NULL,
  `quantita_prodotto` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `incluso_in`
--

INSERT INTO `incluso_in` (`id_prodotto`, `id_ordine`, `quantita_prodotto`) VALUES
(25, 7, 1),
(26, 7, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `ordine`
--

CREATE TABLE `ordine` (
  `id_ordine` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_pagamento` int(11) DEFAULT NULL,
  `data` date NOT NULL,
  `stato` varchar(50) DEFAULT 'In elaborazione',
  `totale` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ordine`
--

INSERT INTO `ordine` (`id_ordine`, `username`, `id_pagamento`, `data`, `stato`, `totale`) VALUES
(3, 'ciao', NULL, '2026-05-12', 'In elaborazione', 114.00),
(4, 'ciao', 1, '2026-05-12', 'Spedito', 114.00),
(5, 'ciao', 3, '2026-05-12', 'Pagato', 12.00),
(6, 'ciao', 5, '2026-05-20', 'Pagato', 114.00),
(7, 'profilocliente', 8, '2026-05-26', 'Pagato', 21.00);

-- --------------------------------------------------------

--
-- Struttura della tabella `pacchetto`
--

CREATE TABLE `pacchetto` (
  `id_pacchetto` int(11) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `sconto` decimal(5,2) DEFAULT NULL,
  `attivo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `pacchetto`
--

INSERT INTO `pacchetto` (`id_pacchetto`, `descrizione`, `sconto`, `attivo`) VALUES
(1, 'Saga Completa / Promo Autore', 15.00, 1),
(2, NULL, 20.00, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `pagamento`
--

CREATE TABLE `pagamento` (
  `id_pagamento` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `metodo` varchar(50) DEFAULT NULL,
  `stato` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `pagamento`
--

INSERT INTO `pagamento` (`id_pagamento`, `username`, `metodo`, `stato`) VALUES
(1, 'ciao', 'Carta', 'Completato'),
(2, 'ciao', 'Carta', 'Carta che termina con 7590'),
(3, 'ciao', 'Carta', 'Completato'),
(4, 'ciao', 'Carta', 'salvato:Carta che termina con 1460'),
(5, 'ciao', 'Carta', 'Completato'),
(6, 'ciao', 'PayPal', 'salvato:ciao@gmail.com'),
(7, 'profilocliente', 'PayPal', 'salvato:email@paypal.com'),
(8, 'profilocliente', 'PayPal', 'Completato');

-- --------------------------------------------------------

--
-- Struttura della tabella `preferiti`
--

CREATE TABLE `preferiti` (
  `id_preferiti` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_prodotto` int(11) NOT NULL,
  `data_aggiunta` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `preferiti`
--

INSERT INTO `preferiti` (`id_preferiti`, `username`, `id_prodotto`, `data_aggiunta`) VALUES
(8, 'profilocliente', 43, '2026-05-26');

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotto`
--

CREATE TABLE `prodotto` (
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
-- Dump dei dati per la tabella `prodotto`
--

INSERT INTO `prodotto` (`id_prodotto`, `username`, `id_pacchetto`, `nome`, `autore`, `descrizione`, `prezzo`, `quantita_disponibile`) VALUES
(17, 'utentevenditore', NULL, 'normal people', NULL, 'Connell e Marianne sono due adolescenti irlandesi.', 15.00, 0),
(19, 'utentevenditore', 1, 'Harry Potter e la pietra filosofale', 'J.K. Rowling', 'Il primo capitolo della saga del mago più famoso del mondo.', 12.00, 20),
(20, 'utentevenditore', 1, 'Harry Potter e la camera dei segreti', 'J.K. Rowling', 'Il secondo anno di Harry a Hogwarts tra misteri e creature oscure.', 12.50, 15),
(21, 'utentevenditore', 1, 'Harry Potter e il prigioniero di Azkaban', 'J.K. Rowling', 'Il terzo anno vede la fuga del pericoloso Sirius Black.', 13.00, 18),
(22, 'utentevenditore', 1, 'Harry Potter e il calice di fuoco', 'J.K. Rowling', 'Il Torneo Tremaghi mette a dura prova la vita di Harry.', 15.00, 12),
(23, 'utentevenditore', 1, 'Il Trono di Spade', 'George R.R. Martin', 'L inizio dell epica saga fantasy tra intrighi e potere nei Sette Regni.', 19.00, 10),
(24, 'utentevenditore', 1, 'Il Grande Inverno', 'George R.R. Martin', 'La stirpe degli Stark si frammenta mentre l inverno sta arrivando.', 19.00, 10),
(25, 'utentevenditore', NULL, 'Senilità', 'Italo Svevo', 'Il capolavoro modernista che esplora l inettitudine e le illusioni di Emilio Brentani.', 10.00, 24),
(26, 'utentevenditore', NULL, 'La coscienza di Zeno', 'Italo Svevo', 'Le confessioni psicoanalitiche di Zeno Cosini tra fumo e nevrosi.', 11.00, 29),
(27, 'utentevenditore', NULL, 'Il gioco segreto', 'Elsa Morante', 'Una raccolta di racconti intensi e sognanti della celebre autrice.', 9.50, 14),
(28, 'utentevenditore', NULL, 'Fosca', 'Igino Ugo Tarchetti', 'Il romanzo simbolo della Scapigliatura, un viaggio tra amore ossessivo e malattia.', 8.50, 15),
(29, 'utentevenditore', NULL, 'Dune', 'Frank Herbert', 'Il monumentale romanzo di fantascienza ambientato sul pianeta desertico Arrakis.', 16.50, 22),
(30, 'utentevenditore', NULL, 'Neuromante', 'William Gibson', 'Il manifesto del genere cyberpunk tra hacker, matrice e intelligenze artificiali.', 13.00, 8),
(31, 'utentevenditore', NULL, 'Guida galattica per gli autostoppisti', 'Douglas Adams', 'Un avventura spaziale esilarante e filosofica sul senso della vita.', 12.00, 19),
(32, 'utentevenditore', NULL, 'Il silenzio degli innocenti', 'Thomas Harris', 'Il famigerato thriller psicologico con Clarice Starling e Hannibal Lecter.', 14.00, 11),
(33, 'utentevenditore', NULL, 'L\'alienista', 'Caleb Carr', 'Un thriller storico e psicologico nella New York di fine Ottocento.', 15.00, 7),
(34, 'utentevenditore', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Un intreccio psicologico mozzafiato basato su ricordi distorti e verità nascoste.', 13.50, 20),
(35, 'utentevenditore', NULL, 'Dieci piccoli indiani', 'Agatha Christie', 'Il giallo per eccellenza: dieci persone bloccate su un isola segnata da una filastrocca.', 10.50, 35),
(36, 'utentevenditore', NULL, 'Sapiens. Da animali a dei', 'Yuval Noah Harari', 'Una breve storia dell umanità, dalle scimmie fino all era tecnologica.', 18.00, 15),
(37, 'utentevenditore', NULL, 'Cosmo', 'Carl Sagan', 'Un viaggio magnifico attraverso lo spazio, il tempo e la conoscenza scientifica.', 17.00, 9),
(38, 'utentevenditore', NULL, 'L\'ordine del tempo', 'Carlo Rovelli', 'Un saggio fisico e filosofico sulla natura profonda e misteriosa del tempo.', 12.00, 25),
(39, 'utentevenditore', NULL, 'Così parlò Zarathustra', 'Friedrich Nietzsche', 'L opera filosofica fondamentale sul concetto di oltreuomo.', 9.00, 14),
(40, 'utentevenditore', NULL, 'Il cucchiaio d argento', 'AA.VV.', 'La bibbia della cucina italiana con migliaia di ricette della tradizione.', 45.00, 5),
(41, 'utentevenditore', NULL, 'In Patagonia', 'Bruce Chatwin', 'Il capolavoro della letteratura di viaggio che ha ridefinito il genere.', 11.50, 12),
(42, 'utentevenditore', NULL, 'Percy Jackson e gli dei dell Olimpo: Il ladro di fulmini', 'Rick Riordan', 'Mitologia greca e avventure moderne per ragazzi.', 12.00, 17),
(43, 'utentevenditore', NULL, 'Hunger Games', 'Suzanne Collins', 'Il celebre romanzo distopico young adult sulla sopravvivenza e la ribellione.', 14.50, 21),
(45, 'utentevenditore', NULL, 'L\'altalena', 'Carlo Dossi', 'In quest\'opera l\'autore esprime l\'essenza della Scapigliatura milanese attraverso una prosa arguta, frammentaria ed espressionista, muovendosi costantemente tra satira sociale e introspezione lirica.', 11.80, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `recensione`
--

CREATE TABLE `recensione` (
  `id_recensione` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `id_prodotto` int(11) NOT NULL,
  `testo` text DEFAULT NULL,
  `valutazione` tinyint(4) DEFAULT NULL CHECK (`valutazione` between 1 and 5),
  `data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `recensione`
--

INSERT INTO `recensione` (`id_recensione`, `username`, `id_prodotto`, `testo`, `valutazione`, `data`) VALUES
(12, 'profilocliente', 25, 'bellissimo!!', 5, '2026-05-26'),
(13, 'profilocliente', 26, 'bellissimo!!', 5, '2026-05-26');

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `username` varchar(30) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `data_registrazione` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`username`, `nome`, `cognome`, `email`, `password_hash`, `data_registrazione`) VALUES
('ciao', 'ciao', 'ciao', 'ciao@gmail.com', '$2y$10$aJe2CLwBFK6uBFq4eP.K7OlZDWtYXbz/IztD/zJfBvehpc/EmIYvq', '2026-04-29'),
('profilocliente', 'cliente', 'profilo', 'cliente@profilo.com', '$2y$10$9gJ9cGFprHdCz2nRZjc7qeKXSJUc68rmFqDM4FouKOtFNmuTMzpvi', '2026-05-26'),
('utentevenditore', 'venditore', 'utente', 'venditore@utente.com', '$2y$10$JBzerbT7UAi2fCeDvjfNkOwMZXMspLajvjzc4jIty4baraenGqM8a', '2026-04-29');

-- --------------------------------------------------------

--
-- Struttura della tabella `venditore`
--

CREATE TABLE `venditore` (
  `username` varchar(30) NOT NULL,
  `partita_iva` varchar(20) NOT NULL,
  `ragione_sociale` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `venditore`
--

INSERT INTO `venditore` (`username`, `partita_iva`, `ragione_sociale`) VALUES
('utentevenditore', 'bella', 'bella');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `carrello`
--
ALTER TABLE `carrello`
  ADD PRIMARY KEY (`id_carrello`),
  ADD KEY `username` (`username`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`nome_categoria`),
  ADD KEY `nome_categoria_padre` (`nome_categoria_padre`);

--
-- Indici per le tabelle `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`username`);

--
-- Indici per le tabelle `descrive`
--
ALTER TABLE `descrive`
  ADD PRIMARY KEY (`id_prodotto`,`nome_categoria`),
  ADD KEY `nome_categoria` (`nome_categoria`);

--
-- Indici per le tabelle `immagine_prodotto`
--
ALTER TABLE `immagine_prodotto`
  ADD PRIMARY KEY (`id_immagine_prodotto`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `immagine_recensione`
--
ALTER TABLE `immagine_recensione`
  ADD PRIMARY KEY (`id_immagine_recensione`),
  ADD KEY `id_recensione` (`id_recensione`);

--
-- Indici per le tabelle `incluso_in`
--
ALTER TABLE `incluso_in`
  ADD PRIMARY KEY (`id_prodotto`,`id_ordine`),
  ADD KEY `id_ordine` (`id_ordine`);

--
-- Indici per le tabelle `ordine`
--
ALTER TABLE `ordine`
  ADD PRIMARY KEY (`id_ordine`),
  ADD KEY `username` (`username`),
  ADD KEY `id_pagamento` (`id_pagamento`);

--
-- Indici per le tabelle `pacchetto`
--
ALTER TABLE `pacchetto`
  ADD PRIMARY KEY (`id_pacchetto`);

--
-- Indici per le tabelle `pagamento`
--
ALTER TABLE `pagamento`
  ADD PRIMARY KEY (`id_pagamento`),
  ADD KEY `username` (`username`);

--
-- Indici per le tabelle `preferiti`
--
ALTER TABLE `preferiti`
  ADD PRIMARY KEY (`id_preferiti`),
  ADD KEY `username` (`username`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `prodotto`
--
ALTER TABLE `prodotto`
  ADD PRIMARY KEY (`id_prodotto`),
  ADD KEY `username` (`username`),
  ADD KEY `id_pacchetto` (`id_pacchetto`);

--
-- Indici per le tabelle `recensione`
--
ALTER TABLE `recensione`
  ADD PRIMARY KEY (`id_recensione`),
  ADD KEY `username` (`username`),
  ADD KEY `id_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `venditore`
--
ALTER TABLE `venditore`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `carrello`
--
ALTER TABLE `carrello`
  MODIFY `id_carrello` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `immagine_prodotto`
--
ALTER TABLE `immagine_prodotto`
  MODIFY `id_immagine_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `immagine_recensione`
--
ALTER TABLE `immagine_recensione`
  MODIFY `id_immagine_recensione` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ordine`
--
ALTER TABLE `ordine`
  MODIFY `id_ordine` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `pacchetto`
--
ALTER TABLE `pacchetto`
  MODIFY `id_pacchetto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `pagamento`
--
ALTER TABLE `pagamento`
  MODIFY `id_pagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `preferiti`
--
ALTER TABLE `preferiti`
  MODIFY `id_preferiti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `prodotto`
--
ALTER TABLE `prodotto`
  MODIFY `id_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT per la tabella `recensione`
--
ALTER TABLE `recensione`
  MODIFY `id_recensione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `carrello`
--
ALTER TABLE `carrello`
  ADD CONSTRAINT `carrello_ibfk_1` FOREIGN KEY (`username`) REFERENCES `cliente` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `carrello_ibfk_2` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `categoria_ibfk_1` FOREIGN KEY (`nome_categoria_padre`) REFERENCES `categoria` (`nome_categoria`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`username`) REFERENCES `utente` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `descrive`
--
ALTER TABLE `descrive`
  ADD CONSTRAINT `descrive_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `descrive_ibfk_2` FOREIGN KEY (`nome_categoria`) REFERENCES `categoria` (`nome_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `immagine_prodotto`
--
ALTER TABLE `immagine_prodotto`
  ADD CONSTRAINT `immagine_prodotto_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id_prodotto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `immagine_recensione`
--
ALTER TABLE `immagine_recensione`
  ADD CONSTRAINT `immagine_recensione_ibfk_1` FOREIGN KEY (`id_recensione`) REFERENCES `recensione` (`id_recensione`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `incluso_in`
--
ALTER TABLE `incluso_in`
  ADD CONSTRAINT `incluso_in_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `incluso_in_ibfk_2` FOREIGN KEY (`id_ordine`) REFERENCES `ordine` (`id_ordine`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `ordine`
--
ALTER TABLE `ordine`
  ADD CONSTRAINT `ordine_ibfk_1` FOREIGN KEY (`username`) REFERENCES `cliente` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ordine_ibfk_2` FOREIGN KEY (`id_pagamento`) REFERENCES `pagamento` (`id_pagamento`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `pagamento`
--
ALTER TABLE `pagamento`
  ADD CONSTRAINT `pagamento_ibfk_1` FOREIGN KEY (`username`) REFERENCES `cliente` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `preferiti`
--
ALTER TABLE `preferiti`
  ADD CONSTRAINT `preferiti_ibfk_1` FOREIGN KEY (`username`) REFERENCES `cliente` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `preferiti_ibfk_2` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `prodotto`
--
ALTER TABLE `prodotto`
  ADD CONSTRAINT `prodotto_ibfk_1` FOREIGN KEY (`username`) REFERENCES `venditore` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodotto_ibfk_2` FOREIGN KEY (`id_pacchetto`) REFERENCES `pacchetto` (`id_pacchetto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `recensione`
--
ALTER TABLE `recensione`
  ADD CONSTRAINT `recensione_ibfk_1` FOREIGN KEY (`username`) REFERENCES `cliente` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recensione_ibfk_2` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `venditore`
--
ALTER TABLE `venditore`
  ADD CONSTRAINT `venditore_ibfk_1` FOREIGN KEY (`username`) REFERENCES `utente` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

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
(1, 'utentevenditore', 1, 'Harry Potter e la pietra filosofale', 'J.K. Rowling', 'Il primo capitolo della saga del mago piu famoso del mondo.', 12.00, 20),
(2, 'utentevenditore', 1, 'Harry Potter e la camera dei segreti', 'J.K. Rowling', 'Il secondo anno di Harry a Hogwarts tra misteri e creature oscure.', 12.50, 15),
(3, 'utentevenditore', 1, 'Harry Potter e il prigioniero di Azkaban', 'J.K. Rowling', 'Il terzo anno vede la fuga del pericoloso Sirius Black.', 13.00, 18),
(4, 'utentevenditore', 1, 'Harry Potter e il calice di fuoco', 'J.K. Rowling', 'Il Torneo Tremaghi mette a dura prova la vita di Harry.', 15.00, 12),
(5, 'utentevenditore', 1, 'Il Trono di Spade', 'George R.R. Martin', 'L inizio dell epica saga fantasy tra intrighi e potere nei Sette Regni.', 19.00, 10),
(6, 'utentevenditore', 1, 'Il Grande Inverno', 'George R.R. Martin', 'La stirpe degli Stark si frammenta mentre l inverno sta arrivando.', 19.00, 10),
(7, 'utentevenditore', NULL, 'Senilita', 'Italo Svevo', 'Il capolavoro modernista che esplora l inettitudine e le illusioni di Emilio Brentani.', 10.00, 25),
(8, 'utentevenditore', NULL, 'La coscienza di Zeno', 'Italo Svevo', 'Le confessioni psicoanalitiche di Zeno Cosini tra fumo e nevrosi.', 11.00, 30),
(9, 'utentevenditore', NULL, 'Il gioco segreto', 'Elsa Morante', 'Una raccolta di racconti intensi e sognanti della celebre autrice.', 9.50, 14),
(10, 'utentevenditore', NULL, 'Fosca', 'Igino Ugo Tarchetti', 'Il romanzo simbolo della Scapigliatura.', 8.50, 15),
(11, 'utentevenditore', NULL, 'Dune', 'Frank Herbert', 'Il monumentale romanzo di fantascienza ambientato su Arrakis.', 16.50, 22),
(12, 'utentevenditore', NULL, 'Neuromante', 'William Gibson', 'Il manifesto del cyberpunk.', 13.00, 8),
(13, 'utentevenditore', NULL, 'Guida galattica per gli autostoppisti', 'Douglas Adams', 'Un avventura spaziale esilarante.', 12.00, 19),
(14, 'utentevenditore', NULL, 'Il silenzio degli innocenti', 'Thomas Harris', 'Thriller psicologico con Hannibal Lecter.', 14.00, 11),
(15, 'utentevenditore', NULL, 'L alienista', 'Caleb Carr', 'Thriller storico ambientato nella New York ottocentesca.', 15.00, 7),
(16, 'utentevenditore', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Thriller psicologico basato su ricordi distorti.', 13.50, 20),
(17, 'utentevenditore', NULL, 'Dieci piccoli indiani', 'Agatha Christie', 'Uno dei gialli piu celebri di sempre.', 10.50, 35),
(18, 'utentevenditore', NULL, 'Sapiens. Da animali a dei', 'Yuval Noah Harari', 'Breve storia dell umanita.', 18.00, 15),
(19, 'utentevenditore', NULL, 'Cosmo', 'Carl Sagan', 'Viaggio attraverso spazio e scienza.', 17.00, 9),
(20, 'utentevenditore', NULL, 'L ordine del tempo', 'Carlo Rovelli', 'Saggio sul tempo.', 12.00, 25),
(21, 'utentevenditore', NULL, 'Cosi parlo Zarathustra', 'Friedrich Nietzsche', 'Opera filosofica sull oltreuomo.', 9.00, 14),
(22, 'utentevenditore', NULL, 'Il cucchiaio d argento', 'AA.VV.', 'Grande classico della cucina italiana.', 45.00, 5),
(23, 'utentevenditore', NULL, 'In Patagonia', 'Bruce Chatwin', 'Celebre libro di viaggio.', 11.50, 12),
(24, 'utentevenditore', NULL, 'Percy Jackson e gli dei dell Olimpo: Il ladro di fulmini', 'Rick Riordan', 'Mitologia greca e avventure moderne.', 12.00, 17),
(25, 'utentevenditore', NULL, 'Hunger Games', 'Suzanne Collins', 'Romanzo distopico young adult.', 14.50, 21),
(26, 'utentevenditore', NULL, 'L altalena', 'Carlo Dossi', 'Opera della Scapigliatura milanese.', 11.80, 0),
(27, 'utentevenditore', NULL, 'ciao', 'ciaociao', 'hqghfpiwqpfipwhf', 21.00, 0);

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
