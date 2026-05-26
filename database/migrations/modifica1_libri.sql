-- 1. CREAZIONE DEL PACCHETTO SCONTI
INSERT INTO `PACCHETTO` (`id_pacchetto`, `descrizione`, `sconto`, `attivo`) VALUES
(1, 'Saga Completa / Promo Autore', 15.00, 1);

-- 2. INSERIMENTO DEI LIBRI REALI
INSERT INTO `PRODOTTO` (`id_prodotto`, `username`, `id_pacchetto`, `nome`, `autore`, `descrizione`, `prezzo`, `quantita_disponibile`) VALUES
-- Saga di Harry Potter (J.K. Rowling)
(19, 'bella', 1, 'Harry Potter e la pietra filosofale', 'J.K. Rowling', 'Il primo capitolo della saga del mago piu famoso del mondo.', 12.00, 20),
(20, 'bella', 1, 'Harry Potter e la camera dei segreti', 'J.K. Rowling', 'Il secondo anno di Harry a Hogwarts tra misteri e creature oscure.', 12.50, 15),
(21, 'bella', 1, 'Harry Potter e il prigioniero di Azkaban', 'J.K. Rowling', 'Il terzo anno vede la fuga del pericoloso Sirius Black.', 13.00, 18),
(22, 'bella', 1, 'Harry Potter e il calice di fuoco', 'J.K. Rowling', 'Il Torneo Tremaghi mette a dura prova la vita di Harry.', 15.00, 12),

-- Cronache del Ghiaccio e del Fuoco (George R.R. Martin)
(23, 'bella', 1, 'Il Trono di Spade', 'George R.R. Martin', 'L inizio dell epica saga fantasy tra intrighi e potere nei Sette Regni.', 19.00, 10),
(24, 'bella', 1, 'Il Grande Inverno', 'George R.R. Martin', 'La stirpe degli Stark si frammenta mentre l inverno sta arrivando.', 19.00, 10),

-- Narrativa Classica / Italiana
(25, 'bella', NULL, 'Senilita', 'Italo Svevo', 'Il capolavoro modernista che esplora l inettitudine e le illusioni di Emilio Brentani.', 10.00, 25),
(26, 'bella', NULL, 'La coscienza di Zeno', 'Italo Svevo', 'Le confessioni psicoanalitiche di Zeno Cosini tra fumo e nevrosi.', 11.00, 30),
(27, 'bella', NULL, 'Il gioco segreto', 'Elsa Morante', 'Una raccolta di racconti intensi e sognanti della celebre autrice.', 9.50, 14),
(28, 'bella', NULL, 'Fosca', 'Igino Ugo Tarchetti', 'Il romanzo simbolo della Scapigliatura, un viaggio tra amore ossessivo e malattia.', 8.50, 15),

-- Fantascienza
(29, 'bella', NULL, 'Dune', 'Frank Herbert', 'Il monumentale romanzo di fantascienza ambientato sul pianeta desertico Arrakis.', 16.50, 22),
(30, 'bella', NULL, 'Neuromante', 'William Gibson', 'Il manifesto del genere cyberpunk tra hacker, matrice e intelligenze artificiali.', 13.00, 8),
(31, 'bella', NULL, 'Guida galattica per gli autostoppisti', 'Douglas Adams', 'Un avventura spaziale esilarante e filosofica sul senso della vita.', 12.00, 19),

-- Gialli & Thriller
(32, 'bella', NULL, 'Il silenzio degli innocenti', 'Thomas Harris', 'Il famigerato thriller psicologico con Clarice Starling e Hannibal Lecter.', 14.00, 11),
(33, 'bella', NULL, 'L alienista', 'Caleb Carr', 'Un thriller storico e psicologico nella New York di fine Ottocento.', 15.00, 7),
(34, 'bella', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Un intreccio psicologico mozzafiato basato su ricordi distorti e verita nascoste.', 13.50, 20),
(35, 'bella', NULL, 'Dieci piccoli indiani', 'Agatha Christie', 'Il giallo per eccellenza: dieci persone bloccate su un isola segnata da una filastrocca.', 10.50, 35),

-- Saggistica
(36, 'bella', NULL, 'Sapiens. Da animali a dei', 'Yuval Noah Harari', 'Una breve storia dell umanita, dalle scimmie fino all era tecnologica.', 18.00, 15),
(37, 'bella', NULL, 'Cosmo', 'Carl Sagan', 'Un viaggio magnifico attraverso lo spazio, il tempo e la conoscenza scientifica.', 17.00, 9),
(38, 'bella', NULL, 'L ordine del tempo', 'Carlo Rovelli', 'Un saggio fisico e filosofico sulla natura profonda e misteriosa del tempo.', 12.00, 25),
(39, 'bella', NULL, 'Cosi parlo Zarathustra', 'Friedrich Nietzsche', 'L opera filosofica fondamentale sul concetto di oltreuomo.', 9.00, 14),

-- Hobby & Viaggi
(40, 'bella', NULL, 'Il cucchiaio d argento', 'AA.VV.', 'La bibbia della cucina italiana con migliaia di ricette della tradizione.', 45.00, 5),
(41, 'bella', NULL, 'In Patagonia', 'Bruce Chatwin', 'Il capolavoro della letteratura di viaggio che ha ridefinito il genere.', 11.50, 12),

-- Ragazzi / Young Adult
(42, 'bella', NULL, 'Percy Jackson e gli dei dell Olimpo: Il ladro di fulmini', 'Rick Riordan', 'Mitologia greca e avventure moderne per ragazzi.', 12.00, 17),
(43, 'bella', NULL, 'Hunger Games', 'Suzanne Collins', 'Il celebre romanzo distopico young adult sulla sopravvivenza e la ribellione.', 14.50, 21);

-- 3. ASSOCIAZIONE ALLE CATEGORIE ESISTENTI
INSERT INTO `DESCRIVE` (`id_prodotto`, `nome_categoria`) VALUES
(19, 'Fantasy'), (20, 'Fantasy'), (21, 'Fantasy'), (22, 'Fantasy'),
(23, 'Fantasy'), (24, 'Fantasy'),
(25, 'Romanzi'), (26, 'Romanzi'), (27, 'Racconti'), (28, 'Romanzi'),
(29, 'Fantascienza'), (30, 'Fantascienza'), (31, 'Fantascienza'),
(32, 'Thriller psicologico'), (33, 'Crime'), (34, 'Thriller psicologico'), (35, 'Crime'),
(36, 'Storia'), (37, 'Scienze'), (38, 'Scienze'), (39, 'Filosofia'),
(40, 'Cucina'), (41, 'Viaggi'),
(42, '7-12 anni'), (43, 'Young Adult');

-- 4. IMMAGINI DI COPERTINA FARE-DA-SEGNAPOSTO
INSERT INTO `IMMAGINE_PRODOTTO` (`id_prodotto`, `url`, `alt_text`) VALUES
(19, 'img/harry_potter_1.jpg', 'Copertina Harry Potter 1'),
(20, 'img/harry_potter_2.jpg', 'Copertina Harry Potter 2'),
(23, 'img/trono_di_spade_1.jpg', 'Copertina Il Trono di Spade'),
(25, 'img/senilita.jpg', 'Copertina Senilita'),
(26, 'img/coscienza_zeno.jpg', 'Copertina La coscienza di Zeno');

INSERT INTO `PRODOTTO` (`id_prodotto`, `username`, `id_pacchetto`, `nome`, `autore`, `descrizione`, `prezzo`, `quantita_disponibile`) VALUES
-- Saga di Harry Potter (J.K. Rowling)
(19, 'bella', 1, 'Harry Potter e la pietra filosofale', 'J.K. Rowling', 'Il primo capitolo della saga del mago piu famoso del mondo.', 12.00, 20),
(20, 'bella', 1, 'Harry Potter e la camera dei segreti', 'J.K. Rowling', 'Il secondo anno di Harry a Hogwarts tra misteri e creature oscure.', 12.50, 15),
(21, 'bella', 1, 'Harry Potter e il prigioniero di Azkaban', 'J.K. Rowling', 'Il terzo anno vede la fuga del pericoloso Sirius Black.', 13.00, 18),
(22, 'bella', 1, 'Harry Potter e il calice di fuoco', 'J.K. Rowling', 'Il Torneo Tremaghi mette a dura prova la vita di Harry.', 15.00, 12),

-- Cronache del Ghiaccio e del Fuoco (George R.R. Martin)
(23, 'bella', 1, 'Il Trono di Spade', 'George R.R. Martin', 'L inizio dell epica saga fantasy tra intrighi e potere nei Sette Regni.', 19.00, 10),
(24, 'bella', 1, 'Il Grande Inverno', 'George R.R. Martin', 'La stirpe degli Stark si frammenta mentre l inverno sta arrivando.', 19.00, 10),

-- Narrativa Classica / Italiana
(25, 'bella', NULL, 'Senilita', 'Italo Svevo', 'Il capolavoro modernista che esplora l inettitudine e le illusioni di Emilio Brentani.', 10.00, 25),
(26, 'bella', NULL, 'La coscienza di Zeno', 'Italo Svevo', 'Le confessioni psicoanalitiche di Zeno Cosini tra fumo e nevrosi.', 11.00, 30),
(27, 'bella', NULL, 'Il gioco segreto', 'Elsa Morante', 'Una raccolta di racconti intensi e sognanti della celebre autrice.', 9.50, 14),
(28, 'bella', NULL, 'Fosca', 'Igino Ugo Tarchetti', 'Il romanzo simbolo della Scapigliatura, un viaggio tra amore ossessivo e malattia.', 8.50, 15),

-- Fantascienza
(29, 'bella', NULL, 'Dune', 'Frank Herbert', 'Il monumentale romanzo di fantascienza ambientato sul pianeta desertico Arrakis.', 16.50, 22),
(30, 'bella', NULL, 'Neuromante', 'William Gibson', 'Il manifesto del genere cyberpunk tra hacker, matrice e intelligenze artificiali.', 13.00, 8),
(31, 'bella', NULL, 'Guida galattica per gli autostoppisti', 'Douglas Adams', 'Un avventura spaziale esilarante e filosofica sul senso della vita.', 12.00, 19),

-- Gialli & Thriller
(32, 'bella', NULL, 'Il silenzio degli innocenti', 'Thomas Harris', 'Il famigerato thriller psicologico con Clarice Starling e Hannibal Lecter.', 14.00, 11),
(33, 'bella', NULL, 'L alienista', 'Caleb Carr', 'Un thriller storico e psicologico nella New York di fine Ottocento.', 15.00, 7),
(34, 'bella', NULL, 'La ragazza del treno', 'Paula Hawkins', 'Un intreccio psicologico mozzafiato basato su ricordi distorti e verita nascoste.', 13.50, 20),
(35, 'bella', NULL, 'Dieci piccoli indiani', 'Agatha Christie', 'Il giallo per eccellenza: dieci persone bloccate su un isola segnata da una filastrocca.', 10.50, 35),

-- Saggistica
(36, 'bella', NULL, 'Sapiens. Da animali a dei', 'Yuval Noah Harari', 'Una breve storia dell umanita, dalle scimmie fino all era tecnologica.', 18.00, 15),
(37, 'bella', NULL, 'Cosmo', 'Carl Sagan', 'Un viaggio magnifico attraverso lo spazio, il tempo e la conoscenza scientifica.', 17.00, 9),
(38, 'bella', NULL, 'L ordine del tempo', 'Carlo Rovelli', 'Un saggio fisico e filosofico sulla natura profonda e misteriosa del tempo.', 12.00, 25),
(39, 'bella', NULL, 'Cosi parlo Zarathustra', 'Friedrich Nietzsche', 'L opera filosofica fondamentale sul concetto di oltreuomo.', 9.00, 14),

-- Hobby & Viaggi
(40, 'bella', NULL, 'Il cucchiaio d argento', 'AA.VV.', 'La bibbia della cucina italiana con migliaia di ricette della tradizione.', 45.00, 5),
(41, 'bella', NULL, 'In Patagonia', 'Bruce Chatwin', 'Il capolavoro della letteratura di viaggio che ha ridefinito il genere.', 11.50, 12),

-- Ragazzi / Young Adult
(42, 'bella', NULL, 'Percy Jackson e gli dei dell Olimpo: Il ladro di fulmini', 'Rick Riordan', 'Mitologia greca e avventure moderne per ragazzi.', 12.00, 17),
(43, 'bella', NULL, 'Hunger Games', 'Suzanne Collins', 'Il celebre romanzo distopico young adult sulla sopravvivenza e la ribellione.', 14.50, 21);


INSERT IGNORE INTO `DESCRIVE` (`id_prodotto`, `nome_categoria`) VALUES
(19, 'Fantasy'), (19, 'Young Adult'),
(20, 'Fantasy'), (20, 'Young Adult'),
(21, 'Fantasy'), (21, 'Young Adult'),
(22, 'Fantasy'), (22, 'Young Adult'),
(23, 'Fantasy'), (23, 'Romanzi'),
(24, 'Fantasy'), (24, 'Romanzi'),
(25, 'Romanzi'), (25, 'Narrativa'),
(26, 'Romanzi'), (26, 'Narrativa'),
(27, 'Racconti'), (27, 'Narrativa'),
(28, 'Romanzi'), (28, 'Narrativa'),
(29, 'Fantascienza'), (30, 'Fantascienza'), (31, 'Fantascienza'),
(32, 'Thriller psicologico'), (33, 'Crime'), (34, 'Thriller psicologico'), (35, 'Crime'),
(36, 'Storia'), (37, 'Scienze'), (38, 'Scienze'), (39, 'Filosofia'),
(40, 'Cucina'), (41, 'Viaggi'),
(42, '7-12 anni'), (43, 'Young Adult');

INSERT INTO `PACCHETTO` (`id_pacchetto`, `sconto`, `attivo`) VALUES (2, 20.00, 1);

INSERT INTO PRODOTTO (username, nome, autore, descrizione, prezzo, quantita_disponibile) 
VALUES (
    'bella', 
    'L\'altalena', 
    'Carlo Dossi', 
    'In quest\'opera l\'autore esprime l\'essenza della Scapigliatura milanese attraverso una prosa arguta, frammentaria ed espressionista, muovendosi costantemente tra satira sociale e introspezione lirica.', 
    11.80, 
    0
);