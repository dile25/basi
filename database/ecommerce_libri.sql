-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Giu 18, 2026 alle 17:09
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
(20, 'profilocliente', 33, '2026-05-27', 1, 0.00),
(53, 'ciao', 100, '2026-06-18', 1, 0.00);

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
(29, 'Fantasy'),
(30, 'Fantascienza'),
(31, 'Fantascienza'),
(32, 'Thriller psicologico'),
(33, 'Crime'),
(34, 'Thriller psicologico'),
(35, 'Crime'),
(36, 'Scienze'),
(36, 'Storia'),
(37, 'Scienze'),
(38, 'Scienze'),
(39, 'Filosofia'),
(40, 'Cucina'),
(41, 'Viaggi'),
(42, '7-12 anni'),
(43, 'Young Adult'),
(46, 'Fantasy'),
(47, 'Fantasy'),
(48, 'Fantasy'),
(49, 'Fantasy'),
(50, 'Fantasy'),
(51, 'Fantasy'),
(52, 'Fantasy'),
(53, 'Noir'),
(54, 'Noir'),
(55, 'Noir'),
(56, 'Romanzi'),
(57, 'Romanzi'),
(58, 'Romanzi'),
(59, 'Romanzi'),
(62, 'Romanzi'),
(64, 'Scienze'),
(65, 'Storia'),
(66, 'Psicologia'),
(67, 'Thriller psicologico'),
(68, 'Thriller psicologico'),
(69, 'Crime'),
(70, 'Arte'),
(71, 'Scienze'),
(72, 'Politica'),
(74, 'Tecnologia'),
(75, 'Storia'),
(76, 'Arte'),
(77, 'Arte'),
(78, 'Arte'),
(79, 'Arte'),
(80, 'Arte'),
(81, 'Fantasy'),
(82, 'Fantasy'),
(83, 'Fantasy'),
(84, 'Fantasy'),
(85, 'Fantasy'),
(87, 'Fantasy'),
(88, 'Fantasy'),
(89, 'Romanzi'),
(90, 'Romanzi'),
(91, 'Romanzi'),
(92, 'Fantasy');

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
  `quantita_prodotto` int(11) NOT NULL DEFAULT 1,
  `prezzo_unitario` decimal(10,2) DEFAULT NULL,
  `trasferito` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `incluso_in`
--

INSERT INTO `incluso_in` (`id_prodotto`, `id_ordine`, `quantita_prodotto`, `prezzo_unitario`, `trasferito`) VALUES
(25, 7, 1, NULL, 1),
(26, 7, 1, NULL, 1),
(43, 8, 1, NULL, 1),
(70, 10, 1, 6.21, 1),
(73, 10, 1, 3.90, 1),
(91, 9, 1, 16.00, 1),
(91, 11, 1, 16.00, 1),
(92, 10, 1, 17.00, 1),
(99, 10, 1, 6.75, 1),
(100, 10, 2, 6.75, 1),
(101, 10, 1, 3.50, 1),
(101, 12, 1, 3.50, 1);

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
(7, 'profilocliente', 8, '2026-05-26', 'Consegnato', 21.00),
(8, 'ciao', 10, '2026-06-13', 'Consegnato', 14.50),
(9, 'ciao', 12, '2026-06-17', 'Consegnato', 16.00),
(10, 'ciao', 14, '2026-06-18', 'Spedito', 50.86),
(11, 'ciao', 15, '2026-06-18', 'Spedito', 16.00),
(12, 'ciao', 16, '2026-06-18', 'Spedito', 3.50);

-- --------------------------------------------------------

--
-- Struttura della tabella `pacchetto`
--

CREATE TABLE `pacchetto` (
  `id_pacchetto` int(11) NOT NULL,
  `nome` varchar(200) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `sconto` decimal(5,2) DEFAULT NULL,
  `sconto_2` decimal(5,2) DEFAULT 10.00,
  `sconto_3` decimal(5,2) DEFAULT 20.00,
  `sconto_tutti` decimal(5,2) DEFAULT 30.00,
  `attivo` tinyint(1) DEFAULT 1,
  `tipo_pacchetto` varchar(30) DEFAULT 'libro',
  `e_saga` tinyint(1) DEFAULT 0,
  `periodicita` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `pacchetto`
--

INSERT INTO `pacchetto` (`id_pacchetto`, `nome`, `descrizione`, `sconto`, `sconto_2`, `sconto_3`, `sconto_tutti`, `attivo`, `tipo_pacchetto`, `e_saga`, `periodicita`) VALUES
(1, 'Saga Completa', 'Saga Completa / Promo Autore', 15.00, 10.00, 20.00, 30.00, 1, 'libro', 1, NULL),
(2, 'Promo Autore', NULL, 20.00, 10.00, 20.00, 30.00, 1, 'libro', 0, NULL),
(3, 'Saga Il Signore degli Anelli', 'Trilogia completa di Tolkien', 15.00, 10.00, 20.00, 30.00, 1, 'libro', 0, NULL),
(4, 'Saga Harry Potter', 'Serie completa di J.K. Rowling', 20.00, 10.00, 20.00, 35.00, 1, 'libro', 0, NULL),
(5, 'Saga Millennium', 'Trilogia di Stieg Larsson', 15.00, 10.00, 25.00, 30.00, 1, 'libro', 0, NULL),
(6, 'Promo Autore Ferrante', '2+ libri di Elena Ferrante', 10.00, 10.00, 20.00, 25.00, 1, 'libro', 0, NULL),
(7, 'Nuovi Arrivi Narrativa', 'Uscite recenti in sconto', 12.00, 12.00, 15.00, 20.00, 1, 'libro', 0, NULL),
(8, 'Saga Dune', 'Ciclo originale di Frank Herbert', 15.00, 10.00, 20.00, 30.00, 1, 'libro', 0, NULL),
(10, 'Promo Autore Murakami', '2+ libri di Haruki Murakami', 10.00, 10.00, 20.00, 25.00, 1, 'libro', 0, NULL);

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
(8, 'profilocliente', 'PayPal', 'Completato'),
(9, 'ciao', 'PayPal', 'salvato:ciao@gmail.com'),
(10, 'ciao', 'PayPal', 'Completato'),
(11, 'ciao', 'Carta', 'salvato:Carta che termina con 9759'),
(12, 'ciao', 'Carta', 'Completato'),
(13, 'ciao', 'Carta', 'salvato:Carta che termina con 3935'),
(14, 'ciao', 'Carta', 'Completato'),
(15, 'ciao', 'Carta', 'Completato'),
(16, 'ciao', 'Carta', 'Completato');

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
  `quantita_disponibile` int(11) NOT NULL,
  `tipo_prodotto` varchar(50) DEFAULT 'libro',
  `data_inserimento` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prodotto`
--

INSERT INTO `prodotto` (`id_prodotto`, `username`, `id_pacchetto`, `nome`, `autore`, `descrizione`, `prezzo`, `quantita_disponibile`, `tipo_prodotto`, `data_inserimento`) VALUES
(17, 'utentevenditore', NULL, 'normal people', 'Sally Rooney', 'Connell e Marianne sono due adolescenti irlandesi.', 15.00, 18, 'libro', '2026-06-13'),
(23, 'utentevenditore', 1, 'Il Trono di Spade', 'George R.R. Martin', 'L inizio dell epica saga fantasy tra intrighi e potere nei Sette Regni.', 19.00, 10, 'libro', '2026-06-13'),
(24, 'utentevenditore', 1, 'Il Grande Inverno', 'George R.R. Martin', 'La stirpe degli Stark si frammenta mentre l inverno sta arrivando.', 19.00, 10, 'libro', '2026-06-13'),
(25, 'utentevenditore', NULL, 'Senilità', 'Italo Svevo', 'Il capolavoro modernista che esplora l inettitudine e le illusioni di Emilio Brentani.', 10.00, 24, 'libro', '2026-06-13'),
(26, 'utentevenditore', NULL, 'La coscienza di Zeno', 'Italo Svevo', 'Le confessioni psicoanalitiche di Zeno Cosini tra fumo e nevrosi.', 11.00, 29, 'libro', '2026-06-13'),
(27, 'utentevenditore', NULL, 'Il gioco segreto', 'Elsa Morante', 'Una raccolta di racconti intensi e sognanti della celebre autrice.', 9.50, 14, 'libro', '2026-06-13'),
(28, 'utentevenditore', NULL, 'Fosca', 'Igino Ugo Tarchetti', 'Il romanzo simbolo della Scapigliatura, un viaggio tra amore ossessivo e malattia.', 8.50, 15, 'libro', '2026-06-13'),
(29, 'utentevenditore', NULL, 'Dune', 'Frank Herbert', 'Il monumentale romanzo di fantascienza ambientato sul pianeta desertico Arrakis.', 16.50, 22, 'libro', '2026-06-13'),
(30, 'utentevenditore', NULL, 'Neuromante', 'William Gibson', 'Il manifesto del genere cyberpunk tra hacker, matrice e intelligenze artificiali.', 13.00, 8, 'libro', '2026-06-13'),
(31, 'utentevenditore', NULL, 'Guida galattica per gli autostoppisti', 'Douglas Adams', 'Un avventura spaziale esilarante e filosofica sul senso della vita.', 12.00, 19, 'libro', '2026-06-13'),
(32, 'utentevenditore', NULL, 'Il silenzio degli innocenti', 'Thomas Harris', 'Il famigerato thriller psicologico con Clarice Starling e Hannibal Lecter.', 14.00, 11, 'libro', '2026-06-13'),
(33, 'utentevenditore', NULL, 'L\'alienista', 'Caleb Carr', 'Un thriller storico e psicologico nella New York di fine Ottocento.', 15.00, 7, 'libro', '2026-06-13'),
(34, 'utentevenditore', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Un intreccio psicologico mozzafiato basato su ricordi distorti e verità nascoste.', 13.50, 20, 'libro', '2026-06-13'),
(35, 'utentevenditore', NULL, 'Dieci piccoli indiani', 'Agatha Christie', 'Il giallo per eccellenza: dieci persone bloccate su un isola segnata da una filastrocca.', 10.50, 35, 'libro', '2026-06-13'),
(36, 'utentevenditore', NULL, 'Sapiens. Da animali a dei', 'Yuval Noah Harari', 'Una breve storia dell umanità, dalle scimmie fino all era tecnologica.', 18.00, 15, 'libro', '2026-06-13'),
(37, 'utentevenditore', NULL, 'Cosmo', 'Carl Sagan', 'Un viaggio magnifico attraverso lo spazio, il tempo e la conoscenza scientifica.', 17.00, 9, 'libro', '2026-06-13'),
(38, 'utentevenditore', NULL, 'L\'ordine del tempo', 'Carlo Rovelli', 'Un saggio fisico e filosofico sulla natura profonda e misteriosa del tempo.', 12.00, 25, 'libro', '2026-06-13'),
(39, 'utentevenditore', NULL, 'Così parlò Zarathustra', 'Friedrich Nietzsche', 'L opera filosofica fondamentale sul concetto di oltreuomo.', 9.00, 14, 'libro', '2026-06-13'),
(40, 'utentevenditore', NULL, 'Il cucchiaio d argento', 'AA.VV.', 'La bibbia della cucina italiana con migliaia di ricette della tradizione.', 45.00, 5, 'libro', '2026-06-13'),
(41, 'utentevenditore', NULL, 'In Patagonia', 'Bruce Chatwin', 'Il capolavoro della letteratura di viaggio che ha ridefinito il genere.', 11.50, 12, 'libro', '2026-06-13'),
(42, 'utentevenditore', NULL, 'Percy Jackson e gli dei dell Olimpo: Il ladro di fulmini', 'Rick Riordan', 'Mitologia greca e avventure moderne per ragazzi.', 12.00, 17, 'libro', '2026-06-13'),
(43, 'utentevenditore', NULL, 'Hunger Games', 'Suzanne Collins', 'Il celebre romanzo distopico young adult sulla sopravvivenza e la ribellione.', 14.50, 20, 'libro', '2026-06-13'),
(45, 'utentevenditore', NULL, 'L\'altalena', 'Carlo Dossi', 'In quest\'opera l\'autore esprime l\'essenza della Scapigliatura milanese attraverso una prosa arguta, frammentaria ed espressionista, muovendosi costantemente tra satira sociale e introspezione lirica.', 11.80, 0, 'libro', '2026-06-13'),
(46, 'utentevenditore', 3, 'La Compagnia dell\'Anello', 'J.R.R. Tolkien', 'Il primo volume della trilogia. Frodo eredita l\'Unico Anello e intraprende un viaggio pericoloso con la Compagnia.', 14.90, 8, 'libro', '2024-01-15'),
(47, 'utentevenditore', 3, 'Le Due Torri', 'J.R.R. Tolkien', 'Il secondo volume. La Compagnia si divide: Frodo e Sam proseguono verso Mordor, mentre gli altri affrontano nuove battaglie.', 14.90, 6, 'libro', '2024-01-15'),
(48, 'utentevenditore', 3, 'Il Ritorno del Re', 'J.R.R. Tolkien', 'Il capitolo finale della saga. La guerra per la Terra di Mezzo si avvicina alla sua conclusione epica.', 14.90, 5, 'libro', '2024-01-15'),
(49, 'utentevenditore', 4, 'Harry Potter e la Pietra Filosofale', 'J.K. Rowling', 'Il primo anno di Harry Potter alla scuola di magia Hogwarts. L\'inizio di un\'avventura leggendaria.', 12.90, 10, 'libro', '2024-02-01'),
(50, 'utentevenditore', 4, 'Harry Potter e la Camera dei Segreti', 'J.K. Rowling', 'Il secondo anno. Una misteriosa forza sta pietrificando gli studenti di Hogwarts.', 12.90, 8, 'libro', '2024-02-01'),
(51, 'utentevenditore', 4, 'Harry Potter e il Prigioniero di Azkaban', 'J.K. Rowling', 'Il terzo anno. Un pericoloso fuggitivo si avvicina a Hogwarts.', 12.90, 7, 'libro', '2024-02-01'),
(52, 'utentevenditore', 4, 'Harry Potter e il Calice di Fuoco', 'J.K. Rowling', 'Il quarto anno. Harry viene inspiegabilmente iscritto a un pericoloso torneo magico.', 13.90, 6, 'libro', '2024-02-01'),
(53, 'utentevenditore', 5, 'Uomini che odiano le donne', 'Stieg Larsson', 'Lisbeth Salander e Mikael Blomkvist indagano su una sparizione di quarant\'anni prima.', 13.50, 9, 'libro', '2024-03-01'),
(54, 'utentevenditore', 5, 'La ragazza che giocava con il fuoco', 'Stieg Larsson', 'Lisbeth è accusata di triplo omicidio. Blomkvist non ci crede e indaga.', 13.50, 7, 'libro', '2024-03-01'),
(55, 'utentevenditore', 5, 'La regina dei castelli di carta', 'Stieg Larsson', 'Il finale esplosivo della trilogia. Lisbeth affronta i suoi nemici più potenti.', 13.50, 6, 'libro', '2024-03-01'),
(56, 'utentevenditore', 6, 'L\'amica geniale', 'Elena Ferrante', 'Elena e Lila crescono nel dopoguerra a Napoli. Un\'amicizia intensa e competitiva.', 15.00, 10, 'libro', '2024-03-15'),
(57, 'utentevenditore', 6, 'Storia del nuovo cognome', 'Elena Ferrante', 'Lila sposa Stefano. Elena parte per Pisa. Le due amiche prendono strade diverse.', 15.00, 8, 'libro', '2024-03-15'),
(58, 'utentevenditore', 6, 'La frantumaglia', 'Elena Ferrante', 'Raccolta di scritti, lettere e interviste dell\'autrice. Una finestra sul suo mondo.', 13.00, 5, 'libro', '2024-06-01'),
(59, 'utentevenditore', 7, 'Intermezzo', 'Sally Rooney', 'Due fratelli molto diversi affrontano il lutto e l\'amore nella Dublino contemporanea.', 18.90, 12, 'libro', '2026-06-13'),
(60, 'utentevenditore', 7, 'Orbital', 'Samantha Harvey', 'Una giornata sulla Stazione Spaziale Internazionale. Booker Prize 2024.', 17.00, 8, 'libro', '2026-06-13'),
(61, 'utentevenditore', NULL, 'Chiedi alla polvere', 'John Fante', 'Los Angeles degli anni Trenta. Arturo Bandini sogna di diventare uno scrittore famoso.', 12.00, 15, 'libro', '2024-01-10'),
(62, 'utentevenditore', NULL, 'Normal People', 'Sally Rooney', 'Connell e Marianne. Una storia d\'amore complicata che attraversa anni universitari.', 16.00, 11, 'libro', '2024-02-20'),
(63, 'utentevenditore', NULL, 'Il nome della rosa', 'Umberto Eco', 'Un monaco francescano indaga su una serie di morti misteriose in un\'abbazia medievale.', 14.00, 9, 'libro', '2024-01-05'),
(64, 'utentevenditore', NULL, 'Sapiens - Da animali a dei', 'Yuval Noah Harari', 'Una breve storia dell\'umanità. Come Homo sapiens ha dominato il pianeta.', 16.00, 14, 'libro', '2024-02-10'),
(65, 'utentevenditore', NULL, 'Il mondo di ieri', 'Stefan Zweig', 'Memorie di un europeo. La Vienna imperiale tra due guerre mondiali.', 13.00, 7, 'libro', '2024-01-20'),
(66, 'utentevenditore', NULL, 'Psicoanalisi della fiaba', 'Bruno Bettelheim', 'Come le fiabe aiutano i bambini a crescere e affrontare le paure.', 14.50, 6, 'libro', '2024-03-05'),
(67, 'utentevenditore', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Rachel osserva ogni giorno la stessa coppia dal treno. Un giorno la donna sparisce.', 13.90, 10, 'libro', '2024-02-15'),
(68, 'utentevenditore', NULL, 'Gone Girl', 'Gillian Flynn', 'Il mattino del quinto anniversario di matrimonio, Amy Dunne scompare.', 13.90, 8, 'libro', '2024-02-15'),
(69, 'utentevenditore', NULL, 'Il codice Da Vinci', 'Dan Brown', 'Un professore di simbologia viene coinvolto in un mistero secolare.', 12.00, 12, 'libro', '2024-01-12'),
(70, 'utentevenditore', NULL, 'Vogue Italia - Aprile 2025', 'Condé Nast', 'Edizione primaverile. Speciale moda donna, tendenze e beauty.', 6.90, 19, 'magazine', '2026-06-13'),
(71, 'utentevenditore', NULL, 'National Geographic Italia - Maggio 2025', 'National Geographic Partners', 'Reportage: foreste tropicali in pericolo. Foto mozzafiato dalla natura selvaggia.', 5.90, 15, 'rivista', '2026-06-13'),
(72, 'utentevenditore', NULL, 'Internazionale n. 1612', 'Internazionale srl', 'Settimanale di approfondimento. Le notizie più importanti dal mondo.', 4.50, 25, 'periodico', '2026-06-13'),
(73, 'utentevenditore', NULL, 'Il Post - Speciale Scienza', 'Il Post srl', 'Supplemento mensile dedicato alla scienza e alla tecnologia.', 3.90, 17, 'periodico', '2026-06-13'),
(74, 'utentevenditore', NULL, 'Wired Italia - Giugno 2025', 'Condé Nast', 'Tecnologia, futuro e innovazione. Speciale intelligenza artificiale.', 5.90, 22, 'magazine', '2026-06-13'),
(75, 'utentevenditore', NULL, 'Focus Storia n. 214', 'Gruner+Jahr', 'Mensile di storia. Speciale: l\'Impero Romano d\'Oriente.', 4.90, 12, 'rivista', '2026-06-13'),
(76, 'utentevenditore', NULL, 'Topolino n. 3621', 'The Walt Disney Company Italia', 'Il celebre settimanale a fumetti con Mickey Mouse e i suoi amici.', 3.50, 30, 'fumetto', '2026-06-13'),
(77, 'utentevenditore', NULL, 'Dylan Dog n. 450', 'Sergio Bonelli Editore', 'L\'indagatore dell\'incubo affronta una nuova minaccia soprannaturale.', 4.90, 20, 'fumetto', '2026-06-13'),
(78, 'utentevenditore', NULL, 'Tex n. 768', 'Sergio Bonelli Editore', 'Il ranger del Texas in una nuova avventura nel selvaggio West.', 4.50, 18, 'fumetto', '2026-06-13'),
(79, 'utentevenditore', NULL, 'Corto Maltese - La ballata del mare salato', 'Hugo Pratt', 'L\'opera prima di Corto Maltese. Un classico intramontabile del fumetto europeo.', 18.00, 8, 'fumetto', '2024-03-01'),
(80, 'utentevenditore', NULL, 'Maus - La storia di un sopravvissuto', 'Art Spiegelman', 'Pulitzer 1992. La Shoah raccontata attraverso topi e gatti. Un capolavoro assoluto.', 22.00, 6, 'fumetto', '2024-03-01'),
(81, 'utentevenditore', 4, 'Harry Potter e l\'Ordine della Fenice', 'J.K. Rowling', 'Il quinto anno. Voldemort è tornato e il Ministero della Magia nega la verità.', 14.90, 9, 'libro', '2024-02-01'),
(82, 'utentevenditore', 4, 'Harry Potter e il Principe Mezzosangue', 'J.K. Rowling', 'Il sesto anno. Harry scopre segreti cruciali sul passato di Voldemort.', 14.90, 8, 'libro', '2024-02-01'),
(83, 'utentevenditore', 8, 'Dune', 'Frank Herbert', 'Il giovane Paul Atreides eredita il controllo del pianeta deserto Arrakis.', 16.50, 10, 'libro', '2024-04-01'),
(84, 'utentevenditore', 8, 'Il Messia di Dune', 'Frank Herbert', 'Dodici anni dopo. Paul è ora imperatore, ma il potere ha un prezzo.', 16.50, 7, 'libro', '2024-04-01'),
(85, 'utentevenditore', 8, 'I Figli di Dune', 'Frank Herbert', 'I gemelli di Paul affrontano intrighi e profezie nel deserto di Arrakis.', 16.50, 6, 'libro', '2024-04-01'),
(87, 'utentevenditore', 1, 'Il Regno dei Sette Regni', 'George R.R. Martin', 'La guerra dei Cinque Re infuria su Westeros.', 17.90, 7, 'libro', '2024-03-20'),
(88, 'utentevenditore', NULL, 'Tempesta di Spade', 'George R.R. Martin', 'Il conflitto raggiunge il suo apice più sanguinoso.', 17.90, 5, 'libro', '2024-03-20'),
(89, 'utentevenditore', 10, 'Norwegian Wood', 'Haruki Murakami', 'Toru ricorda il suo amore giovanile per Naoko in un Giappone in trasformazione.', 12.50, 11, 'libro', '2024-04-10'),
(90, 'utentevenditore', 10, 'Kafka sulla spiaggia', 'Haruki Murakami', 'Un ragazzo in fuga e un vecchio capace di parlare con i gatti. Realismo magico.', 14.00, 9, 'libro', '2024-04-10'),
(91, 'utentevenditore', 10, '1Q84 - Libro 1', 'Haruki Murakami', 'Aomame scopre che il mondo è leggermente diverso da come lo ricordava.', 16.00, 6, 'libro', '2024-04-10'),
(92, 'utentevenditore', 4, 'Harry Potter e I Doni della Morte', 'J. K. Rowling', 'Harry, Ron e Hermione affrontano la prova finale, raccolgono i doni della morte e affrontano la battaglia finale.', 17.00, 0, 'libro', '2026-06-17'),
(93, 'utentevenditore', NULL, 'National Geographic - Maggio 2026', 'National Geographic Society', 'Numero mensile dedicato agli oceani profondi.', 6.90, 15, 'rivista', '2026-06-18'),
(94, 'utentevenditore', NULL, 'National Geographic - Giugno 2026', 'National Geographic Society', 'Speciale biodiversità e cambiamento climatico.', 6.90, 15, 'rivista', '2026-06-18'),
(95, 'utentevenditore', NULL, 'Internazionale - N.1542', 'Internazionale', 'Rassegna stampa internazionale della settimana.', 4.50, 20, 'periodico', '2026-06-18'),
(96, 'utentevenditore', NULL, 'Internazionale - N.1543', 'Internazionale', 'Rassegna stampa internazionale della settimana.', 4.50, 20, 'periodico', '2026-06-18'),
(97, 'utentevenditore', NULL, 'Internazionale - N.1544', 'Internazionale', 'Rassegna stampa internazionale della settimana.', 4.50, 20, 'periodico', '2026-06-18'),
(98, 'utentevenditore', NULL, 'Internazionale - N.1545', 'Internazionale', 'Rassegna stampa internazionale della settimana.', 4.50, 20, 'periodico', '2026-06-18'),
(99, 'utentevenditore', NULL, 'Vogue Italia - Maggio 2026', 'Condé Nast', 'Numero dedicato alla moda primavera/estate.', 7.50, 9, 'magazine', '2026-06-18'),
(100, 'utentevenditore', NULL, 'Vogue Italia - Giugno 2026', 'Condé Nast', 'Speciale accessori e tendenze estive.', 7.50, 8, 'magazine', '2026-06-18'),
(101, 'utentevenditore', NULL, 'Topolino - N.3580', 'Disney', 'Storie a fumetti settimanali con Topolino e Paperino.', 3.50, 23, 'fumetto', '2026-06-18'),
(102, 'utentevenditore', NULL, 'Topolino - N.3581', 'Disney', 'Storie a fumetti settimanali con Topolino e Paperino.', 3.50, 25, 'fumetto', '2026-06-18');

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
(13, 'profilocliente', 26, 'bellissimo!!', 5, '2026-05-26'),
(14, 'ciao', 43, 'bello', 5, '2026-06-13');

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
  `ragione_sociale` varchar(100) NOT NULL,
  `ultimo_trasferimento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `venditore`
--

INSERT INTO `venditore` (`username`, `partita_iva`, `ragione_sociale`, `ultimo_trasferimento`) VALUES
('utentevenditore', 'bella', 'bella', '2026-06-18 16:45:40');

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
  MODIFY `id_carrello` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

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
  MODIFY `id_ordine` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `pacchetto`
--
ALTER TABLE `pacchetto`
  MODIFY `id_pacchetto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `pagamento`
--
ALTER TABLE `pagamento`
  MODIFY `id_pagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `preferiti`
--
ALTER TABLE `preferiti`
  MODIFY `id_preferiti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `prodotto`
--
ALTER TABLE `prodotto`
  MODIFY `id_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT per la tabella `recensione`
--
ALTER TABLE `recensione`
  MODIFY `id_recensione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
