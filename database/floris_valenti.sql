-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Lug 11, 2026 alle 13:41
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
(166, 'primoutente', 25, '2026-07-10', 1, 0.00),
(167, 'primoutente', 26, '2026-07-10', 1, 0.00),
(168, 'primoutente', 27, '2026-07-10', 1, 0.00);

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
('Altro', NULL),
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
('primoutente', '', 'Via Roma, Milano, 17471, MI'),
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
(1, 'Fantasy'),
(1, 'Middle Grade'),
(2, 'Fantasy'),
(2, 'Middle Grade'),
(3, 'Fantasy'),
(3, 'Middle Grade'),
(4, 'Fantasy'),
(4, 'Middle Grade'),
(5, 'Fantasy'),
(5, 'Middle Grade'),
(6, 'Fantasy'),
(6, 'Middle Grade'),
(7, 'Fantasy'),
(7, 'Middle Grade'),
(8, 'Fantasy'),
(9, 'Fantasy'),
(10, 'Fantasy'),
(11, 'Fantascienza'),
(12, 'Fantascienza'),
(13, 'Fantascienza'),
(17, 'Young Adult'),
(18, 'Young Adult'),
(19, 'Young Adult'),
(20, 'Romanzi'),
(21, 'Romanzi'),
(22, 'Romanzi'),
(23, 'Romanzi'),
(25, 'Romance'),
(26, 'Romance'),
(27, 'Romance'),
(28, 'Romanzi'),
(29, 'Romanzi'),
(30, 'Fantascienza'),
(34, 'Fantascienza'),
(36, 'Thriller psicologico'),
(37, 'Spy story'),
(38, 'Storica'),
(39, 'Romance'),
(40, 'Storia'),
(41, 'Storia'),
(43, 'Filosofia'),
(45, 'Viaggi'),
(46, 'Scienze'),
(48, 'Arte'),
(49, 'Cinema'),
(50, 'Sport'),
(51, '0-6 anni'),
(52, '7-12 anni'),
(53, 'Arte'),
(54, 'Arte'),
(64, 'Architettura'),
(65, 'Altro'),
(67, 'Altro'),
(114, 'Altro');

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
(1, 1, 'img/hp1.jpg', NULL),
(2, 2, 'img/hp2.jpg', NULL),
(3, 3, 'img/hp3.jpg', NULL),
(4, 4, 'img/hp4.jpg', NULL),
(5, 5, 'img/hp5.jpg', NULL),
(6, 6, 'img/hp6.jpg', NULL),
(7, 7, 'img/hp7.jpg', NULL),
(8, 8, 'img/lotr1.jpg', NULL),
(9, 9, 'img/lotr2.jpg', NULL),
(10, 10, 'img/lotr3.jpg', NULL),
(11, 11, 'img/dune1.jpg', NULL),
(12, 12, 'img/dune2.jpg', NULL),
(13, 13, 'img/dune3.jpg', NULL),
(14, 17, 'img/hg1.jpg', NULL),
(15, 18, 'img/hg2.jpg', NULL),
(16, 19, 'img/hg3.jpg', NULL),
(21, 20, 'img/ef1.jpg', NULL),
(22, 21, 'img/ef2.jpg', NULL),
(23, 22, 'img/ef3.jpg', NULL),
(24, 23, 'img/ef4.jpg', NULL),
(28, 25, 'img/personenormali.jpg', NULL),
(29, 26, 'img/dovesei.jpg', NULL),
(30, 27, 'img/intermezzo.jpg', NULL),
(31, 28, 'img/norvegian.jpg', NULL),
(32, 29, 'img/kafkaspiaggia.jpg', NULL),
(33, 30, 'img/1q84.jpg', NULL),
(34, NULL, 'img/nomerosa.jpg', NULL),
(35, NULL, 'img/polvere.jpg', NULL),
(36, NULL, 'img/shining.jpg', NULL),
(42, NULL, 'img/nomerosa.jpg', NULL),
(43, NULL, 'img/shining.jpg', NULL),
(44, NULL, 'img/topolino.jpg', NULL),
(45, NULL, 'img/1984.jpg', NULL),
(46, 56, 'img/wired.jpg', NULL),
(47, 51, 'img/indovinabene.jpg', NULL),
(48, 61, 'img/dylandog.jpg', NULL),
(49, 59, 'img/internazionale.jpg', NULL),
(50, 57, 'img/nationalgeographic.jpg', NULL),
(51, 62, 'img/tex.jpg', NULL),
(52, 52, 'img/matilde.jpg', NULL),
(53, 58, 'img/focusstoria.jpg', NULL),
(54, 43, 'img/zarathustra.jpg', NULL),
(55, 40, 'img/sapiens.jpg', NULL),
(56, 39, 'img/persuasione.jpg', NULL),
(57, 46, 'img/brevestoriatempo.jpg', NULL),
(58, 45, 'img/patagonia.jpg', NULL),
(59, NULL, 'img/grandesonno.jpg', NULL),
(60, 50, 'img/open.jpg', NULL),
(61, 53, 'img/cortomaltese.jpg', NULL),
(62, 48, 'img/arte.jpg', NULL),
(63, 38, 'img/pilastriterra.jpg', NULL),
(64, 41, 'img/mondoieri.jpg', NULL),
(65, 37, 'img/talpa.jpg', NULL),
(66, 54, 'img/maus.jpg', NULL),
(67, 49, 'img/cinemahitchcock.jpg', NULL),
(68, 34, 'img/neuromante.jpg', NULL),
(69, 36, 'img/ragazzatreno.jpg', NULL),
(70, NULL, 'img/1984.jpg', NULL),
(71, 55, 'img/vogue.jpg', NULL),
(72, NULL, 'img/grandesonno.jpg', NULL),
(74, 67, 'img/1783700140_6a511aac67f02.png', 'Copertina Topolino n° 3681'),
(75, 114, 'img/ciak-club_1783701685.jpeg', 'Copertina Ciak Club');

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
  `trasferito` tinyint(1) NOT NULL DEFAULT 0,
  `stato_venditore` varchar(20) NOT NULL DEFAULT 'Pagato'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `incluso_in`
--

INSERT INTO `incluso_in` (`id_prodotto`, `id_ordine`, `quantita_prodotto`, `prezzo_unitario`, `trasferito`, `stato_venditore`) VALUES
(1, 8, 1, 10.32, 1, 'Consegnato'),
(2, 8, 1, 10.32, 1, 'Consegnato'),
(3, 1, 1, 10.32, 1, 'Pagato'),
(3, 8, 1, 10.32, 1, 'Consegnato'),
(4, 8, 1, 11.12, 1, 'Consegnato'),
(6, 1, 1, 11.92, 1, 'Pagato'),
(7, 1, 1, 13.60, 1, 'Pagato'),
(17, 9, 1, 13.00, 0, 'Pagato'),
(25, 8, 1, 14.40, 1, 'Consegnato'),
(26, 8, 1, 14.85, 1, 'Consegnato'),
(27, 1, 1, 18.90, 1, 'Pagato'),
(27, 9, 8, 18.90, 0, 'Pagato'),
(27, 10, 1, 18.90, 1, 'Consegnato'),
(55, 1, 1, 7.50, 1, 'Pagato'),
(55, 2, 1, 7.50, 0, 'In lavorazione'),
(55, 8, 1, 67.50, 1, 'Consegnato'),
(59, 4, 1, 4.50, 0, 'In lavorazione'),
(59, 5, 1, 4.50, 0, 'In lavorazione'),
(59, 6, 1, 4.50, 0, 'In lavorazione'),
(59, 7, 1, 99.45, 0, 'In lavorazione'),
(59, 11, 1, 187.20, 1, 'Spedito'),
(64, 9, 1, 15.00, 0, 'Consegnato'),
(64, 10, 1, 15.00, 0, 'Consegnato');

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
(1, 'primoutente', 21, '2026-06-20', 'Consegnato', 62.24),
(2, 'primoutente', 22, '2026-06-20', 'In lavorazione', 7.50),
(3, 'primoutente', 23, '2026-06-21', 'In lavorazione', 31.85),
(4, 'primoutente', 24, '2026-06-21', 'In lavorazione', 4.50),
(5, 'primoutente', 25, '2026-06-21', 'In lavorazione', 4.50),
(6, 'primoutente', 26, '2026-06-21', 'In lavorazione', 4.50),
(7, 'primoutente', 27, '2026-06-21', 'In lavorazione', 99.45),
(8, 'primoutente', 28, '2026-06-23', 'Consegnato', 138.83),
(9, 'primoutente', 31, '2026-06-24', 'Annullato', 182.70),
(10, 'primoutente', 32, '2026-06-24', 'Consegnato', 33.90),
(11, 'primoutente', 33, '2026-07-10', 'Spedito', 187.20);

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
  `sconto_tutti` decimal(5,2) DEFAULT NULL,
  `attivo` tinyint(1) DEFAULT 1,
  `tipo_pacchetto` varchar(30) DEFAULT 'libro',
  `e_saga` tinyint(1) DEFAULT 0,
  `periodicita` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `pacchetto`
--

INSERT INTO `pacchetto` (`id_pacchetto`, `nome`, `descrizione`, `sconto`, `sconto_2`, `sconto_3`, `sconto_tutti`, `attivo`, `tipo_pacchetto`, `e_saga`, `periodicita`) VALUES
(1, 'Saga Harry Potter', 'Serie completa in 7 volumi di J.K. Rowling', NULL, 10.00, 20.00, 35.00, 1, 'libro', 1, NULL),
(2, 'Trilogia Il Signore degli Anelli', 'Trilogia completa di J.R.R. Tolkien', NULL, 10.00, 20.00, 30.00, 1, 'libro', 1, NULL),
(3, 'Saga Dune', 'Ciclo originale in 3 volumi di Frank Herbert', NULL, 10.00, 20.00, 30.00, 1, 'libro', 1, NULL),
(4, 'Saga Millennium', 'Trilogia di Stieg Larsson', NULL, 10.00, 25.00, 30.00, 1, 'libro', 1, NULL),
(5, 'Saga Hunger Games', 'Trilogia di Suzanne Collins', NULL, 10.00, 20.00, 25.00, 1, 'libro', 1, NULL),
(6, 'Promo Autrice: Elena Ferrante', '2+ libri di Elena Ferrante', NULL, 10.00, 20.00, 25.00, 1, 'libro', 0, NULL),
(7, 'Promo Autrice: Sally Rooney', '2+ libri di Sally Rooney', NULL, 10.00, 20.00, 25.00, 1, 'libro', 0, NULL),
(8, 'Promo Autore: Haruki Murakami', '2+ libri di Haruki Murakami', NULL, 10.00, 20.00, 25.00, 1, 'libro', 0, NULL),
(29, 'Abbonamento Vogue Italia', NULL, NULL, 0.00, 0.00, 20.00, 1, 'abbonamento', 0, 'mensile'),
(30, 'Abbonamento Wired Italia', NULL, NULL, 0.00, 0.00, 20.00, 1, 'abbonamento', 0, 'mensile'),
(31, 'Abbonamento National Geographic', NULL, NULL, 0.00, 0.00, 20.00, 1, 'abbonamento', 0, 'mensile'),
(32, 'Abbonamento Focus Storia', NULL, NULL, 0.00, 0.00, 20.00, 1, 'abbonamento', 0, 'mensile'),
(33, 'Abbonamento Internazionale', NULL, NULL, 0.00, 0.00, 20.00, 1, 'abbonamento', 0, 'settimanale'),
(35, 'Abbonamento Dylan Dog', NULL, NULL, 0.00, 0.00, 15.00, 1, 'abbonamento', 0, 'mensile'),
(36, 'Abbonamento Tex', NULL, NULL, 0.00, 0.00, 15.00, 1, 'abbonamento', 0, 'mensile'),
(79, 'Ciak Club Abbonamento Annuale', NULL, NULL, 0.00, 0.00, 25.00, 1, 'abbonamento', 0, 'mensile');

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
(1, 'primoutente', 'Carta', 'Completato'),
(2, 'primoutente', 'Carta', 'Carta che termina con 7590'),
(3, 'primoutente', 'Carta', 'Completato'),
(5, 'primoutente', 'Carta', 'Completato'),
(7, 'profilocliente', 'PayPal', 'salvato:email@paypal.com'),
(8, 'profilocliente', 'PayPal', 'Completato'),
(10, 'primoutente', 'PayPal', 'Completato'),
(12, 'primoutente', 'Carta', 'Completato'),
(13, 'primoutente', 'Carta', 'salvato:Carta che termina con 3935'),
(14, 'primoutente', 'Carta', 'Completato'),
(15, 'primoutente', 'Carta', 'Completato'),
(16, 'primoutente', 'Carta', 'Completato'),
(19, 'primoutente', 'Contrassegno', 'Completato'),
(20, 'primoutente', 'Carta', 'Completato'),
(21, 'primoutente', 'Contrassegno', 'Completato'),
(22, 'primoutente', 'Carta', 'Completato'),
(23, 'primoutente', 'Carta', 'Completato'),
(24, 'primoutente', 'Carta', 'Completato'),
(25, 'primoutente', 'Carta', 'Completato'),
(26, 'primoutente', 'Carta', 'Completato'),
(27, 'primoutente', 'Carta', 'Completato'),
(28, 'primoutente', 'Carta', 'Completato'),
(29, 'primoutente', 'PayPal', 'salvato:primoutente@gmail.com'),
(30, 'primoutente', 'Carta', 'salvato:Carta che termina con 2855'),
(31, 'primoutente', 'Carta', 'Completato'),
(32, 'primoutente', 'Carta', 'Completato'),
(33, 'primoutente', 'Carta', 'Completato');

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
  `data_inserimento` date DEFAULT curdate(),
  `testata` varchar(100) DEFAULT NULL,
  `attivo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prodotto`
--

INSERT INTO `prodotto` (`id_prodotto`, `username`, `id_pacchetto`, `nome`, `autore`, `descrizione`, `prezzo`, `quantita_disponibile`, `tipo_prodotto`, `data_inserimento`, `testata`, `attivo`) VALUES
(1, 'utentevenditore', 1, 'Harry Potter e la Pietra Filosofale', 'J.K. Rowling', 'Il primo anno di Harry Potter a Hogwarts. L\'inizio di un\'avventura leggendaria.', 12.90, 17, 'libro', '2024-02-01', NULL, 1),
(2, 'utentevenditore', 1, 'Harry Potter e la Camera dei Segreti', 'J.K. Rowling', 'Il secondo anno. Una forza misteriosa sta pietrificando gli studenti.', 12.90, 14, 'libro', '2024-02-01', NULL, 1),
(3, 'utentevenditore', NULL, 'Harry Potter e il Prigioniero di Azkaban', 'J.K. Rowling', 'Il terzo anno. Un pericoloso fuggitivo da Azkaban si avvicina a Hogwarts.', 12.90, 12, 'libro', '2024-02-01', NULL, 1),
(4, 'utentevenditore', NULL, 'Harry Potter e il Calice di Fuoco', 'J.K. Rowling', 'Il quarto anno. Harry è inspiegabilmente iscritto a un pericoloso torneo magico.', 13.90, 11, 'libro', '2024-02-01', NULL, 1),
(5, 'utentevenditore', 1, 'Harry Potter e l\'Ordine della Fenice', 'J.K. Rowling', 'Il quinto anno. Voldemort è tornato e il Ministero della Magia nega la verità.', 14.90, 10, 'libro', '2024-02-01', NULL, 1),
(6, 'utentevenditore', 1, 'Harry Potter e il Principe Mezzosangue', 'J.K. Rowling', 'Il sesto anno. Harry scopre segreti cruciali sul passato di Voldemort.', 14.90, 8, 'libro', '2024-02-01', NULL, 1),
(7, 'utentevenditore', 1, 'Harry Potter e i Doni della Morte', 'J.K. Rowling', 'Il finale. Harry, Ron e Hermione cercano gli Horcrux per sconfiggere Voldemort.', 17.00, 7, 'libro', '2024-02-01', NULL, 1),
(8, 'utentevenditore', 2, 'La Compagnia dell\'Anello', 'J.R.R. Tolkien', 'Frodo e i suoi compagni partono per distruggere l\'Unico Anello.', 14.90, 12, 'libro', '2024-01-10', NULL, 1),
(9, 'utentevenditore', 2, 'Le Due Torri', 'J.R.R. Tolkien', 'La Compagnia si separa mentre la guerra si diffonde nella Terra di Mezzo.', 14.90, 10, 'libro', '2024-01-10', NULL, 1),
(10, 'utentevenditore', 2, 'Il Ritorno del Re', 'J.R.R. Tolkien', 'La battaglia finale per la Terra di Mezzo. Frodo raggiunge il Monte Fato.', 14.90, 9, 'libro', '2024-01-10', NULL, 1),
(11, 'utentevenditore', 3, 'Dune', 'Frank Herbert', 'Paul Atreides eredita il controllo del pianeta deserto Arrakis e della spezia.', 16.50, 10, 'libro', '2024-04-01', NULL, 1),
(12, 'utentevenditore', 3, 'Il Messia di Dune', 'Frank Herbert', 'Dodici anni dopo. Paul è imperatore, ma il potere ha un prezzo altissimo.', 16.50, 7, 'libro', '2024-04-01', NULL, 1),
(13, 'utentevenditore', 3, 'I Figli di Dune', 'Frank Herbert', 'I gemelli di Paul affrontano intrighi e profezie nel deserto di Arrakis.', 16.50, 6, 'libro', '2024-04-01', NULL, 1),
(17, 'utentevenditore', 5, 'Hunger Games', 'Suzanne Collins', 'Katniss Everdeen viene selezionata per i Giochi della Fame al posto della sorella.', 13.00, 13, 'libro', '2024-05-01', NULL, 1),
(18, 'utentevenditore', 5, 'La ragazza di fuoco', 'Suzanne Collins', 'Katniss diventa suo malgrado il simbolo della ribellione contro Capitol City.', 13.00, 11, 'libro', '2024-05-01', NULL, 1),
(19, 'utentevenditore', 5, 'Il canto della rivolta', 'Suzanne Collins', 'La rivoluzione è esplosa. Katniss è il Ghiandaia Imitatrice della resistenza.', 13.00, 9, 'libro', '2024-05-01', NULL, 1),
(20, 'utentevenditore', 6, 'L\'amica geniale', 'Elena Ferrante', 'Elena e Lila crescono nel dopoguerra a Napoli. Un\'amicizia intensa e competitiva.', 15.00, 10, 'libro', '2024-03-15', NULL, 1),
(21, 'utentevenditore', 6, 'Storia del nuovo cognome', 'Elena Ferrante', 'Lila sposa Stefano. Elena parte per Pisa. Le due amiche prendono strade diverse.', 15.00, 8, 'libro', '2024-03-15', NULL, 1),
(22, 'utentevenditore', 6, 'Storia di chi fugge e di chi resta', 'Elena Ferrante', 'Gli anni Settanta. Le vite di Elena e Lila si separano sempre di più.', 15.00, 7, 'libro', '2024-03-15', NULL, 1),
(23, 'utentevenditore', 6, 'Storia della bambina perduta', 'Elena Ferrante', 'Il finale della tetralogia. I segreti di una vita intera vengono a galla.', 15.00, 6, 'libro', '2024-03-15', NULL, 1),
(25, 'utentevenditore', 7, 'Persone Normali', 'Sally Rooney', 'Connell e Marianne: una storia d\'amore complicata attraverso gli anni universitari.', 16.00, 10, 'libro', '2024-02-20', NULL, 1),
(26, 'utentevenditore', 7, 'Dove sei, mondo bello', 'Sally Rooney', 'Quattro amici irlandesi navigano tra amore, lavoro e senso della vita adulta.', 16.50, 8, 'libro', '2024-09-01', NULL, 1),
(27, 'utentevenditore', 7, 'Intermezzo', 'Sally Rooney', 'Due fratelli molto diversi affrontano il lutto e l\'amore nella Dublino contemporanea.', 18.90, 2, 'libro', '2026-06-13', NULL, 1),
(28, 'utentevenditore', 8, 'Norwegian Wood', 'Haruki Murakami', 'Toru ricorda il suo amore giovanile per Naoko in un Giappone in trasformazione.', 12.50, 10, 'libro', '2024-04-10', NULL, 1),
(29, 'utentevenditore', 8, 'Kafka sulla spiaggia', 'Haruki Murakami', 'Un ragazzo in fuga e un vecchio capace di parlare con i gatti. Realismo magico.', 14.00, 8, 'libro', '2024-04-10', NULL, 1),
(30, 'utentevenditore', 8, '1Q84 - Libro 1', 'Haruki Murakami', 'Aomame scopre che il mondo è leggermente diverso da come lo ricordava.', 16.00, 5, 'libro', '2024-04-10', NULL, 1),
(34, 'utentevenditore', NULL, 'Neuromante', 'William Gibson', 'Il manifesto del cyberpunk. Un hacker e una killer cyborg contro una IA.', 13.00, 6, 'libro', '2024-03-10', NULL, 1),
(36, 'utentevenditore', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Rachel osserva ogni giorno la stessa coppia dal treno. Un giorno la donna sparisce.', 13.90, 0, 'libro', '2024-02-15', NULL, 1),
(37, 'utentevenditore', NULL, 'La talpa', 'John le Carré', 'Una spy story della Guerra Fredda. Un agente doppio nel cuore dell\'MI6.', 13.00, 7, 'libro', '2024-04-20', NULL, 1),
(38, 'utentevenditore', NULL, 'I pilastri della terra', 'Ken Follett', 'La costruzione di una cattedrale nell\'Inghilterra medievale tra intrighi e fede.', 17.00, 8, 'libro', '2024-02-01', NULL, 1),
(39, 'utentevenditore', NULL, 'Persuasione', 'Jane Austen', 'Anne Elliot incontra di nuovo l\'ufficiale che aveva rifiutato anni prima.', 9.90, 12, 'libro', '2024-01-20', NULL, 1),
(40, 'utentevenditore', NULL, 'Sapiens', 'Yuval Noah Harari', 'Una breve storia dell\'umanità. Come l\'Homo sapiens ha dominato il pianeta.', 16.00, 14, 'libro', '2024-02-10', NULL, 1),
(41, 'utentevenditore', NULL, 'Il mondo di ieri', 'Stefan Zweig', 'Memorie di un europeo. La Vienna imperiale tra due guerre mondiali.', 13.00, 7, 'libro', '2024-01-20', NULL, 1),
(43, 'utentevenditore', NULL, 'Cosi\' parlò Zarathustra', 'Friedrich Nietzsche', 'L\'opera filosofica sul superuomo e la volontà di potenza.', 9.00, 14, 'libro', '2024-01-01', NULL, 1),
(45, 'utentevenditore', NULL, 'In Patagonia', 'Bruce Chatwin', 'Il capolavoro del genere viaggio. Un\'esplorazione leggendaria.', 11.50, 10, 'libro', '2024-01-01', NULL, 1),
(46, 'utentevenditore', NULL, 'Una breve storia del tempo', 'Stephen Hawking', 'I misteri dell\'universo spiegati in modo accessibile a tutti.', 15.00, 11, 'libro', '2024-01-01', NULL, 1),
(48, 'utentevenditore', NULL, 'Storia dell\'arte', 'Ernst Gombrich', 'Il manuale di storia dell\'arte più letto al mondo. Accessibile e completo.', 32.00, 8, 'libro', '2024-01-01', NULL, 1),
(49, 'utentevenditore', NULL, 'Il cinema secondo Hitchcock', 'François Truffaut', 'Lunga intervista al maestro del thriller cinematografico.', 19.00, 6, 'libro', '2024-01-01', NULL, 1),
(50, 'utentevenditore', NULL, 'Open', 'Andre Agassi', 'L\'autobiografia di uno dei più grandi tennisti di tutti i tempi.', 16.00, 9, 'libro', '2024-01-01', NULL, 1),
(51, 'utentevenditore', NULL, 'Indovina quanto bene ti voglio', 'Sam McBratney', 'Una tenera storia illustrata sull\'amore tra un coniglietto e suo padre.', 9.90, 20, 'libro', '2024-01-01', NULL, 1),
(52, 'utentevenditore', NULL, 'Matilde', 'Roald Dahl', 'Una bambina geniale con poteri straordinari affronta adulti crudeli.', 11.90, 16, 'libro', '2024-01-01', NULL, 1),
(53, 'utentevenditore', NULL, 'Corto Maltese', 'Hugo Pratt', 'Il classico intramontabile del fumetto europeo. Un marinaio avventuriero.', 18.00, 8, 'fumetto', '2024-03-01', NULL, 1),
(54, 'utentevenditore', NULL, 'Maus', 'Art Spiegelman', 'Pulitzer 1992. La Shoah raccontata attraverso topi e gatti. Capolavoro assoluto.', 22.00, 6, 'fumetto', '2024-03-01', NULL, 1),
(55, 'utentevenditore', 29, 'Vogue Italia - Giugno 2026', 'Condé Nast', 'Numero di giugno: speciale moda estate e tendenze beachwear.', 7.50, 22, 'magazine', '2026-06-01', 'Vogue Italia', 1),
(56, 'utentevenditore', 30, 'Wired Italia - Giugno 2026', 'Condé Nast', 'Tecnologia e futuro. Speciale intelligenza artificiale e robotica.', 5.90, 20, 'magazine', '2026-06-01', 'Wired Italia', 1),
(57, 'utentevenditore', 31, 'National Geographic - Giugno 2026', 'National Geographic Society', 'Speciale biodiversità marina e cambiamento climatico.', 6.90, 18, 'rivista', '2026-06-01', 'National Geographic', 1),
(58, 'utentevenditore', 32, 'Focus Storia - Giugno 2026', 'Gruner+Jahr', 'Speciale: la caduta dell\'Impero Romano d\'Occidente.', 4.90, 14, 'rivista', '2026-06-01', 'Focus Storia', 1),
(59, 'utentevenditore', 33, 'Internazionale - N.1550', 'Internazionale', 'Rassegna stampa internazionale. Le notizie più importanti della settimana.', 4.50, 17, 'periodico', '2026-06-16', 'Internazionale', 1),
(61, 'utentevenditore', 35, 'Dylan Dog - Luglio 2026', 'Sergio Bonelli Editore', 'L\'indagatore dell\'incubo affronta una nuova minaccia soprannaturale.', 4.90, 18, 'fumetto', '2026-06-01', 'Dylan Dog', 1),
(62, 'utentevenditore', 36, 'Tex - Luglio 2026', 'Sergio Bonelli Editore', 'Il ranger del Texas in una nuova avventura nel selvaggio West.', 4.50, 16, 'fumetto', '2026-06-01', 'Tex', 1),
(64, 'venditoreprova', NULL, 'Libro', 'Autore', '', 15.00, 10, 'libro', '2026-06-24', NULL, 0),
(65, 'Prova', NULL, 'libro', 'libro', '', 12.00, 12, 'libro', '2026-06-24', NULL, 0),
(67, 'utentevenditore', NULL, 'Topolino n° 3681', 'Walt Disney', 'Topolino e Paperino verso i mondiali', 3.30, 7, 'fumetto', '2026-07-10', NULL, 1),
(114, 'utentevenditore', 79, 'Ciak Club', '', '', 3.00, 32, 'rivista', '2026-07-10', NULL, 1);

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
(1, 'primoutente', 17, 'Bel libro', 5, '2026-06-24');

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
  `data_registrazione` date NOT NULL,
  `attivo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`username`, `nome`, `cognome`, `email`, `password_hash`, `data_registrazione`, `attivo`) VALUES
('eodwpq', 'qwjdpw', 'wuouud', 'wpwqippf@gmail.com', '$2y$10$WkGfH2ZX.lOShrgsn4QUauZkHl6MEPbHfVPhFdfuPJCfDv5QBGJqi', '2026-06-19', 1),
('primoutente', 'Primo', 'Utente', 'primoutente@gmail.com', '$2y$10$aJe2CLwBFK6uBFq4eP.K7OlZDWtYXbz/IztD/zJfBvehpc/EmIYvq', '2026-04-29', 1),
('profilocliente', 'cliente', 'profilo', 'cliente@profilo.com', '$2y$10$9gJ9cGFprHdCz2nRZjc7qeKXSJUc68rmFqDM4FouKOtFNmuTMzpvi', '2026-05-26', 1),
('Prova', 'Prova', 'Uno', 'prova@gmail.com', '$2y$10$EyOe.3nav2gf3ky1VWvne.oMSaCV.UM74drqgO/sjNIusxIMrNyvW', '2026-06-24', 0),
('utentevenditore', 'venditore', 'utente', 'venditore@utente.com', '$2y$10$JBzerbT7UAi2fCeDvjfNkOwMZXMspLajvjzc4jIty4baraenGqM8a', '2026-04-29', 1),
('venditoreprova', 'venditore', 'prova', 'prova@prova.it', '$2y$10$0wEWuaCSU4mB8gUd5jFCgOvLqkCztG3WGLb6IKXhblcYhm92OaHAm', '2026-06-24', 0);

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
('Prova', '11111111111', 'prova', NULL),
('utentevenditore', 'bella', 'bella', '2026-07-11 10:34:03'),
('venditoreprova', '94911123411', 'prova', NULL);

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
  MODIFY `id_carrello` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT per la tabella `immagine_prodotto`
--
ALTER TABLE `immagine_prodotto`
  MODIFY `id_immagine_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT per la tabella `immagine_recensione`
--
ALTER TABLE `immagine_recensione`
  MODIFY `id_immagine_recensione` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ordine`
--
ALTER TABLE `ordine`
  MODIFY `id_ordine` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `pacchetto`
--
ALTER TABLE `pacchetto`
  MODIFY `id_pacchetto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT per la tabella `pagamento`
--
ALTER TABLE `pagamento`
  MODIFY `id_pagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT per la tabella `preferiti`
--
ALTER TABLE `preferiti`
  MODIFY `id_preferiti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `prodotto`
--
ALTER TABLE `prodotto`
  MODIFY `id_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT per la tabella `recensione`
--
ALTER TABLE `recensione`
  MODIFY `id_recensione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
