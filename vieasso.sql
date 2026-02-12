-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : co1610-001.eu.clouddb.ovh.net:35228
-- Généré le : sam. 27 déc. 2025 à 00:46
-- Version du serveur : 8.0.44-35
-- Version de PHP : 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `vieasso`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnements`
--

CREATE TABLE `abonnements` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `date_abonnement` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `abonnements`
--

INSERT INTO `abonnements` (`id`, `event_id`, `date_abonnement`) VALUES
(25, 13, '2025-11-08 14:16:20'),
(41, 12, '2025-11-07 14:07:59'),
(88, 12, '2025-11-10 21:46:08'),
(97, 66, '2025-11-25 21:05:21');

-- --------------------------------------------------------

--
-- Structure de la table `config`
--

CREATE TABLE `config` (
  `id` int NOT NULL,
  `creation_club_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `config`
--

INSERT INTO `config` (`id`, `creation_club_active`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `fiche_club`
--

CREATE TABLE `fiche_club` (
  `club_id` int NOT NULL,
  `nom_club` varchar(128) NOT NULL,
  `type_club` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `logo_club` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tuteur` varchar(128) NOT NULL,
  `campus` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `validation_admin` tinyint(1) DEFAULT NULL,
  `validation_tuteur` tinyint(1) DEFAULT NULL,
  `motif_refus` varchar(255) DEFAULT NULL,
  `validation_finale` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fiche_club`
--

INSERT INTO `fiche_club` (`club_id`, `nom_club`, `type_club`, `description`, `logo_club`, `tuteur`, `campus`, `validation_admin`, `validation_tuteur`, `motif_refus`, `validation_finale`) VALUES
(1, 'BDE - Campus de Calais', 'BDE', 'Le BDE s&#039;occupe principalement de la supervision de la vie associative du campus de Calais, de la création de clubs à l&#039;organisation des événements et activités de chaque d&#039;entre eux. Il est également en charge des relations entre les étudiants du campus et l&#039;administration de l&#039;école.', NULL, '17', 'Calais', 1, 1, 'Veuillez svp remplacer Mme Delvart par un-e autre tuteur-trice. Merci.', 0),
(2, 'Bureau des Sport Calais (BDS)', 'Sportif', 'Organiser des événements sportifs, faire participer des étudiants à des initiations aux sports, participer à des manifestations sportives.', '../uploads/logos/BureaudesSportCalaisBDS_logo_1759751046.png', '52', 'Calais', 1, 1, NULL, 1),
(3, 'BDE EILCO Dunkerque', 'BDE', 'Organisation d&#039;événements sur le campus de Dunkerque pour unifier l&#039;école.', '../uploads/logos/BDEEILCODunkerque_logo_1759850986.jpg', '48', 'Dunkerque', 1, 1, NULL, 1),
(4, 'BDE Calais', 'Bureau des Étudiants', 'Le BDE de Calais est en charge de la supervision de la vie associative et la vie étudiante du campus de Calais. Dans ce cadre, il supervise l&#039;ensemble des clubs (création, activités/événements, trésorerie, évaluation ...) et met tout en oeuvre pour que les clubs puissent organiser leurs activités/événements dans les meilleures conditions. Il assure également le lien entre l&#039;administration de l&#039;école et les étudiants du campus. Il représente notamment les étudiants et défend leurs intérêts.', '../uploads/logos/BDECalais_logo_1759851027.png', '48', 'Calais', 1, 1, NULL, 1),
(5, 'Bureau des Sport Calais (BDS)', 'Sportif', 'Organiser des événements sportifs, faire participer des étudiants à des initiations aux sports et également participer à des manifestations sportives', '../uploads/logos/BureaudesSportCalaisBDS_logo_1759855109.png', '52', 'Calais', 1, 1, NULL, 1),
(6, 'D10 Cassé', 'Jeux de sociétés/jeux de rôles/échecs', 'D10Cassé est un club dont l&#039;objectif est de rassembler les passionnés de jeux de sociétés, de jeux de rôles et/ou de jeux de stratégies sur plateaux, tel que les échecs, pour partager des moments de convivialité. Nous possédons ainsi un catalogue de jeux à dispositions des élèves que nous faisons découvrir lors de nos nombreux évènements toute l&#039;année.', '../uploads/logos/D10Cass_logo_1759870269.png', '52', 'Calais', 1, 1, 'Modification de la liste des membres du club', 1),
(7, 'Ch&#039;tis Gamers', 'Jeux Vidéo', 'Ch&#039;tis Gamers est un club créé pour rassembler les étudiants autour d&#039;une passion commune : les jeux vidéo.\r\nTout au long de l&#039;année, nous proposons différents événements en rapport avec les jeux vidéo comme des tournois, des découvertes de jeux, des soirées sur le jeu vidéo du mois, un voyage à la Paris Games Week ...\r\nDe plus, pour participer au financement de notre club, nous effectuons des ventes de boissons (Coca Cola, Cacolac, ...), de snacks (snickers, twix, ...) ou des ventes de pizzas.', '../uploads/logos/Ch039tisGamers_logo_1759954097.png', '51', 'Calais', 1, 1, NULL, 1),
(8, 'EILTech', 'Nouvelles technologies', 'Le club EILTECH propose des événements dédiés aux nouvelles technologies, à la programmation et à la \r\ncybersécurité. Nous organisons régulièrement des activités variées telles que des ateliers pratiques de \r\nprogrammation, des initiations à la modélisation 3D et à la conception numérique.\r\n\r\nAu-delà de ces événements, le club a pour ambition de donner une continuité aux projets initiés les années précédentes, afin de les enrichir et de les mener à maturité. Nous encourageons également la créativité et l’esprit d’innovation de nos membres en leur offrant un cadre propice à l’expérimentation et à l’apprentissage collaboratif.', '../uploads/logos/EILTech_logo_1759955916.jpg', '89', 'Calais', 1, 1, NULL, 1),
(9, 'BDE de Boulogne-sur-Mer', 'Bureau des étudiants ', 'Ce club est le Bureau Des Étudiants du campus de Boulogne-sur-Mer. Il a pour objectif d&#039;apporter de la bonne humeur, du fun et des activités sur le campus', '../uploads/logos/BDEdeBoulogne-sur-Mer_logo_1759995238.png', '48', 'Boulogne', 1, 1, NULL, 1),
(10, 'D10Cassé', 'Jeux de société/jeux de rôles/échecs', 'D10Cassé est un club dont l&#039;objectif est de rassembler les passionnés de jeux de sociétés, de jeux de rôles et/ou de jeux de stratégies sur plateaux, tel que les échecs, pour partager des moments de convivialité. Nous possédons ainsi un catalogue de jeux à dispositions des élèves que nous faisons découvrir lors de nos nombreux évènements toute l&#039;année.', '../uploads/logos/D10Cass_logo_1760010596.png', '52', 'Calais', 1, 1, NULL, 1),
(11, 'Bureau des arts', 'ART', 'Le Bureau des Arts (BDA) de l’EILCO est un club étudiant visant à promouvoir et organiser des activités culturelles et artistiques. À travers des événements comme des ateliers de peinture, de poterie, et des projections de films, ce club encourage l’expression créative et offre aux étudiants des espaces de détente et d’échange autour de l’art. Il cherche à développer la vie culturelle sur le campus en impliquant les étudiants dans diverses activités artistiques et ludiques.', '../uploads/logos/Bureaudesarts_logo_1760091718.png', '45', 'Calais', 1, 1, NULL, 1),
(12, 'EILCO&#039;TAKU', 'Culture', 'EILCO&#039;TAKU est un club qui permet de faire découvrir le monde asiatique et sa culture !', '../uploads/logos/EILCO039TAKU_logo_1760196017.svg', '52', 'Calais', 1, 1, NULL, 1),
(13, 'BDH', 'Humanitaire ', 'Le Bureau d&#039;Aides Humanitaires : \r\n1. Sensibiliser les étudiants aux enjeux humanitaires et solidaires.\r\n 2. Organiser des actions de soutien (collectes de fonds, dons, bénévolat) pour des causes\r\n humanitaires.\r\n 3. Encourager l&#039;engagement des élèves dans des initiatives sociales et communautaires.\r\n 4. Faciliter la participation à des projets humanitaires, locaux ou internationaux.\r\n Ce bureau favorise le développement de valeurs de solidarité, de citoyenneté et d&#039;entraide chez les \r\nétudiants. ', '../uploads/logos/BDH_logo_1760377250.jpg', '48', 'Calais', 1, 1, NULL, 1),
(14, 'Eilcoriental', 'Culturel', 'Le club a pour objectif de favoriser l’échange et le partage de richesses à la culture orientale au sein de la communauté universitaire. À travers diverses activités culturelles, interactives et gastronomiques, le club souhaite être un espace d’échange et d’ouverture favorisant la rencontre entre étudiants de différentes nationalités', NULL, '48', 'Calais', 1, 1, NULL, 1),
(15, 'Eilcoriental', 'Culturel', 'Le club a pour objectif de favoriser l’échange et le partage de richesses à la culture orientale au sein de la communauté universitaire. À travers diverses activités culturelles, interactives et gastronomiques, le club souhaite être un espace d’échange et d’ouverture favorisant la rencontre entre étudiants de différentes nationalités', NULL, '48', 'Calais', 1, 1, 'Liste des membres non complétée - doublon : le club a déposé 2 fois la fiche', 0),
(16, 'BDE Saint Omer ', 'Événementiel', 'Le BDE a pour mission d&#039;organiser des événements pour les classes de Saint-Omer, en proposant notamment des ventes de croissants et de pain au chocolat.\r\n\r\nIl vise également à apporter du soutien actif aux autres clubs de l&#039;école et à contribuer au projet du BDE Général.', NULL, '138', 'Longuenesse', 1, 1, NULL, 1),
(17, 'EILColors', 'Culturel', 'Notre club a pour ambition de promouvoir l’ouverture d’esprit, l’inclusion et la solidarité au sein de la communauté universitaire, en organisant des événements variés qui mettent en lumière les arts, les traditions et les langues du monde entier. Nous croyons que la diversité culturelle est un atout majeur pour enrichir les échanges entre étudiants et permettre à chacun de mieux comprendre les différentes réalités qui composent notre société.\r\n\r\nÀ travers nos activités, nous souhaitons créer un espace d’échange convivial où les étudiants, quel que soit leur parcours ou leur origine, peuvent partager et découvrir des cultures riches et variées. Ce lieu d’interaction favorise l’apprentissage mutuel, la curiosité intellectuelle et le respect des différences, tout en contribuant à renforcer les liens au sein de la communauté universitaire.\r\n\r\nLe club œuvre également à mettre en valeur le patrimoine culturel, qu’il soit local, français ou international, afin d’élargir les horizons de chacun et de stimuler une réflexion collective sur l’importance de la diversité. Nous pensons que ce projet peut non seulement enrichir l’expérience universitaire des étudiants, mais aussi jouer un rôle clé dans la promotion de la tolérance, de l’ouverture et de la cohésion sociale.', '../uploads/logos/EILColors_logo_1760527083.png', '46', 'Longuenesse', 1, 1, NULL, 1),
(18, 'EilChess', 'Club d&#039;Echec', 'Les principaux objectifs de EilChess consistent à développer les échecs et à faire progresser tout joueur, quel que soit son niveau. Il s’adresse aussi bien aux joueurs loisirs qui cherchent à s’amuser sans pression, qu’aux joueurs désirant pratiquer la compétition. Ayant aussi pour but de rentrer dans le milieu compétitif.', NULL, '52', 'Calais', 1, 0, 'Club déjà existant', 0),
(19, 'AURA’EILCO', 'solidaire, culturel et humaniste', 'Club solidaire, culturel et humaniste, visant à promouvoir la diversité, l’entraide et la sensibilisation à travers diverses actions', NULL, '48', 'Calais', 1, 1, 'La définition de l&#039;objet du club est trop succinct. Il faut développer, en particulier avec le détail des activités qui pourraient faire l&#039;objet d&#039;une soutenance.', 1),
(20, 'Innov&#039;EILCO', 'Club scientifique et technique', 'Le Club Innov a pour objectif de stimuler la créativité et l’innovation technologique chez les étudiants.\r\nIl réunit des passionnés d’informatique et d’ingénierie autour d’activités techniques et collaboratives.\r\nLe club prévoit l’organisation d’événements tels que des hackathons, ateliers pratiques, défis techniques ou challenges de débogage et de logique.', NULL, '48', 'Calais', 1, 1, NULL, 1),
(21, 'EnjoyUp', 'divertissement', 'Le club a pour objectif de proposer des activités conviviales pour les étudiants, telles que des jeux de société, des sorties locales à Calais (patinage, plage, foire ,etc.) et d’autres activités de détente.\r\n\r\nToutes nos activités se déroulent aux alentours de l’école, donc nous n’avons pas besoin de transport ou de bus pour nos déplacements.', '../uploads/logos/EnjoyUp_logo_1761310705.jpg', '48', 'Calais', 1, 1, NULL, 1),
(22, 'EILCO Games', 'Club de jeux de société ', 'EILCO Games est un club de jeux de société. Il rassemble les étudiants autour de moments conviviaux, que ce soit dans la salle du BDE ou lors de soirées/après-midi organisées en partenariat avec un bar à jeux.', '../uploads/logos/EILCOGames_logo_1761313085.png', '168', 'Boulogne', 1, 1, NULL, 1),
(23, 'EilZik', 'Musique &amp; Danse ', 'EilZik est un espace ouvert à tous les passionnés de musique, débutants ou confirmés. Il permet aux étudiants de découvrir, pratiquer et partager leur passion à travers des répétitions, des ateliers et des représentations. Le club favorise l’expression artistique, la créativité et le travail en équipe. Les membres peuvent chanter, jouer d’un instrument, composer ou simplement apprendre des autres. L’objectif est de créer un lieu convivial où la musique rassemble, tout en développant la confiance en soi et le sens du rythme.\r\n\r\nInstagram : @eilzik_officiel\r\nDiscord : https://discord.gg/cexEbGhAjh', '../uploads/logos/EilZik_logo_1761316301.png', '52', 'Calais', 1, 1, NULL, 1),
(24, 'AURA’EILCO', 'solidaire, culturel et humaniste', 'AURA’EILCO est un club solidaire, culturel et humaniste ayant pour mission de promouvoir la diversité, la solidarité et la sensibilisation à travers des actions concrètes sur le campus et au-delà.\r\nNotre objectif est de rassembler des étudiants engagés souhaitant avoir un impact positif, que ce soit sur le plan social, environnemental, éducatif ou culturel.\r\nLe club se veut un espace d’initiatives, d’échanges et de collaboration, favorisant à la fois l’ouverture d’esprit et la créativité au service du bien commun.\r\n\r\nActivités envisagées :\r\nLes activités du club s’articuleront autour de plusieurs axes :\r\n\r\nSensibilisation &amp; solidarité : organisation de journées thématiques autour de causes sociales (ex. inclusion, précarité, santé mentale, environnement), avec stands, conférences et ateliers interactifs.\r\n\r\nÉvénements solidaires : création d’événements visant à collecter des fonds ou du matériel pour des associations partenaires locales ou internationales (ex. marché solidaire, actions de dons, partenariats humanitaires).\r\n\r\nProjets collaboratifs : participation à des actions conjointes avec d’autres clubs EILCO (ex. hackathon solidaire, ateliers culturels, projets éco-responsables).\r\n\r\nActions locales : initiatives directes en faveur des publics fragiles (distribution alimentaire, ateliers éducatifs, sensibilisation dans les écoles, etc.).\r\n\r\nRayonnement culturel : mise en avant de la diversité culturelle à travers des expositions, soirées thématiques, ou échanges interculturels entre étudiants.\r\n\r\nPerspectives et soutenances :\r\nLes projets menés par AURA’EILCO pourront donner lieu à des soutenances sous forme de bilans d’événements, d’études d’impact, de rapports d’organisation ou de présentations de projets solidaires et culturels. Chaque action sera documentée pour valoriser l’engagement des membres et le travail collectif accompli.', NULL, '48', 'Calais', 1, 1, NULL, 1),
(25, 'EILCO Games', 'Club de jeux de société ', 'EILCO Games est un club de jeux de société. Il rassemble les étudiants autour de moments conviviaux, que ce soit dans la salle du BDE ou lors de soirées/après-midi organisées en partenariat avec un bar à jeux.', '../uploads/logos/EILCOGames_logo_1761376874.png', '168', 'Boulogne', NULL, 0, 'Liste des membres incomplète', 0),
(26, 'CinéCo', 'Artistique', 'Le club CinéCo réunit les étudiants passionnés par le 7ᵉ art à travers des projections, des ateliers, des jeux de rôle, ainsi que des débats culturels.\r\nIl met en avant le processus de création cinématographique et le rôle de l’ingénierie dans le cinéma.\r\nLe club prévoit un partenariat avec le cinéma Pathé et l’organisation de projections choisies par le public.\r\nIl favorise des collaborations avec d’autres clubs pour renforcer la communauté étudiante et encourager les échanges entre disciplines.', '../uploads/logos/CinCo_logo_1761381143.png', '49', 'Calais', 1, 1, NULL, 1),
(27, 'Mixgamemates', 'Gaming', 'Le club a pour objectif de mettre en place des activités collectives et ludiques telles \r\nque des tournois, des quiz et bien d’autres compétitions amicales, pour faire découvrir \r\naux étudiants un espace de détente et de loisir. À travers ces activités, le club espère \r\nfavoriser la cohésion entre les étudiants, aider à leur intégration et développer la \r\nconvivialité, l’esprit d’équipe et le fair-play tout en contribuant à l’animation de la vie \r\nétudiante et au développement du sentiment d’appartenance à l’école.', '../uploads/logos/Mixgamemates_logo_1761472532.png', '46', 'Longuenesse', 1, 1, NULL, 1),
(28, 'Horizon Bien-être', 'sportif', 'Le club Horizon Bien-Être a pour vocation d’accompagner les étudiants dans leur épanouissement personnel. Il propose des activités et des moments de partage favorisant la détente, la motivation et l’équilibre entre les études et la vie quotidienne. À travers des ateliers, du yoga et des échanges bienveillants, le club aide chacun à retrouver énergie, sérénité et confiance en soi.', '../uploads/logos/HorizonBien-tre_logo_1761637921.png', '49', 'Longuenesse', 1, 1, NULL, 1),
(29, 'Eil&#039;cinéaste', 'Club Culturel et Artistique', 'Le Club Cinéma est un espace de créativité, de culture et de partage, destiné aux étudiants du campus.\r\nSon objectif principal est de promouvoir la culture cinématographique sous toutes ses formes : projection de films, analyse de scènes, création audiovisuelle, et organisation d’événements liés au monde du cinéma.', '../uploads/logos/Eil039cinaste_logo_1761773574.PNG', '49', 'Longuenesse', 1, 1, NULL, 1),
(30, 'Les Joueurs de Dés/Cartes', 'Loisir', 'Les Joueurs de Dés/Cartes a pour ambition de rassembler les étudiants de l&#039;ULCO à travers les jeux de société.\r\nPour cela, nous organisons régulièrement des événements en mettant à disposition les jeux de société de notre inventaire. Ces jeux sont de tout genre (rôle, stratégie, hasard, ambiance, ...) pouvant plaire au plus grand nombre. Nous proposons également des moments dédiés à certains jeux (loup-garou, échec…) en les présentant et en organisant des sessions d&#039;initiation.', '../uploads/logos/LesJoueursdeDsCartes_logo_1761813204.jpg', '52', 'Longuenesse', 1, 1, NULL, 1),
(31, 'PolyCulture', 'Culture générale', 'Il s&#039;agit qui permettra au élèves de mieux se cultiver tout en jouant, resserrer les liens via les diverses activités qui seront organisées et apprendre aussi des cultures différentes des nôtres.', NULL, '48', 'Longuenesse', NULL, 1, 'La présentation du club n&#039;est pas suffisamment détaillée. Quelles seront les activités ? Quels sont les objectifs pratiques du club ? ', 0),
(32, 'Baladopale', 'Découverte ', 'Le club Baladopale est né d’une idée simple mais pleine de sens : découvrir ensemble la ville de Boulogne-sur-Mer, tout en cassant la routine des études. Nous voulons créer un espace où les étudiants peuvent s’évader, explorer, rire et partager de bons moments en dehors des salles de cours.\r\n\r\nNotre objectif est de permettre à chacun de mieux connaître la richesse historique, culturelle et naturelle de Boulogne-sur-Mer à travers des sorties organisées dans ses lieux les plus emblématiques : ses monuments, ses ruelles pleines de charme, son port, sa mer et ses musées. Ces balades seront aussi l’occasion de renforcer les liens entre étudiants, d’échanger, d’apprendre les uns des autres et de créer des souvenirs uniques, immortalisés par des photos de groupe qui resteront avec nous pour toujours.  \r\n Nous avons déjà visité l’Office de Tourisme de Boulogne-sur-Mer, qui nous a présenté les lieux les plus visités et les plus intéressants de la ville. Grâce à cela, nous avons pu réfléchir et imaginer plusieurs idées d’événements et de sorties que nous pourrions organiser dans le cadre du club.\r\n\r\nEn résumé, Baladopale, c’est plus qu’un simple club étudiant : c’est une aventure collective, une ouverture sur la ville, un moyen de changer d’air, de se cultiver et de renforcer la convivialité entre étudiants dans une ambiance de bonne humeur et de découverte.', '../uploads/logos/Baladopale_logo_1761839399.png', '48', 'Boulogne', 1, 1, NULL, 1),
(33, 'EILMOTION', 'Divertissement', 'Eilmotion est un club étudiant consacré à la création de moments d&#039;échange, de bien-être et de l&#039;innovation sur le campus.\r\nEn organisant diverses activités, des pique-niques de soutien, des journées de dessin, des ateliers interactifs, des sorties récréatives, des événements communautaires et des campagnes de sensibilisation, nous encourageons le dialogue, l’ouverture d’esprit et l’assistance mutuelle parmi les étudiants.\r\nNotre objectif est de favoriser l&#039;unité, d&#039;encourager l&#039;initiative individuelle et collective, et de proposer un lieu où chaque personne peut s&#039;exprimer, se revitaliser et apporter une contribution positive à la vie étudiante, le tout dans une atmosphère amicale et ouverte à tous.\r\nBougeons autrement.', '../uploads/logos/EILMOTION_logo_1761853964.png', '47', 'Dunkerque', 1, 1, NULL, 1),
(34, 'TEDxEILCO', 'Club de développement personnel, leadership et culture scientifique — inspiré du concept TEDx.', 'Le club TEDxEILCO a pour vocation de créer un espace d’échange et d’inspiration au sein de notre université. Inspiré du format TEDx, il vise à organiser des conférences et des rencontres avec des intervenants aux parcours inspirants comme des ingénieurs, chercheurs, entrepreneurs ou acteurs du changement. Son objectif est de stimuler la curiosité, d’encourager la prise de parole et d’élargir les horizons des étudiants ingénieurs au-delà du cadre académique. À travers des discussions autour de l’innovation, du développement personnel, de la technologie ou de la responsabilité sociale, le club aspire à promouvoir le partage d’idées nouvelles, à renforcer les compétences en communication et en leadership, et à créer un lien concret entre le monde universitaire et le monde professionnel, pour former des ingénieurs ouverts, inspirés et inspirants.', NULL, '22', 'Calais', 1, 1, NULL, 1),
(35, 'Cultura Connect', 'Club Culturel', 'Cultura Connect est un club étudiant qui fait découvrir les cultures du monde à travers des événements uniques et inspirants. Notre objectif est de créer un espace où chacun peut voyager, apprendre et partager sans quitter le campus.\r\nNous organisons des activités culturelles qui mettent en lumière les traditions, la gastronomie, la musique et les langues de différents pays.\r\nChaque événement est une expérience humaine riche en découvertes et en échanges.\r\nAvec Cultura Connect, tu explores la diversité du monde, tu rencontres des personnes passionnées et tu élargis ton horizon culturel.', '../uploads/logos/CulturaConnect_logo_1761863804.png', '47', 'Dunkerque', 1, 1, NULL, 1),
(36, 'LevelUp', 'Professionnel / développement personnel ', 'Le club LevelUp a pour mission d’accompagner les étudiants de l’EILCO dans leur insertion professionnelle. Il propose des ateliers de correction de CV, des simulations d’entretiens, ainsi que des séminaires et workshops dédiés à la recherche de stages et d’alternances. Son objectif est d’aider chaque étudiant à développer ses compétences professionnelles et à se préparer efficacement au monde du travail.', '../uploads/logos/LevelUp_logo_1761865598.png', '48', 'Dunkerque', 1, 1, NULL, 1),
(37, 'Bureau des sports Longuenesse', 'Sportif', 'Création d’un club sportif accessible à tous dans le but de promouvoir la cohésion entre étudiants et la santé grâce au sport sur le campus de Longuenesse.', '../uploads/logos/BureaudessportsLonguenesse_logo_1761899730.jpeg', '22', 'Longuenesse', 1, 1, NULL, 1),
(38, 'Bureau des sports Longuenesse ', 'Sportif ', 'Création d’un club sportif accessible à tous dans le but de promouvoir la cohésion entre étudiants et la santé grâce au sport sur le campus de Longuenesse.', '../uploads/logos/BureaudessportsLonguenesse_logo_1761899765.jpeg', '22', 'Longuenesse', NULL, 0, 'Liste des membres non complétée', 0),
(39, 'LevelUp', 'Professionnel / développement personnel ', ' Le club LevelUp a pour mission d’accompagner les étudiants de l’EILCO dans leur insertion professionnelle. Il propose des ateliers de correction de CV, des simulations d’entretiens, ainsi que des séminaires et workshops dédiés à la recherche de stages et d’alternances. Son objectif est d’aider chaque étudiant à développer ses compétences professionnelles et à se préparer efficacement au monde du travail.', '../uploads/logos/LevelUp_logo_1761917314.png', '48', 'Dunkerque', NULL, 1, 'Liste des membres non complétée', 0),
(40, 'Bureau des Loisirs Dunkerque (BDL)', 'Divertissement', 'Le Bureau des Loisirs est un club dédié à la conception et l&#039;organisation d&#039;activités de divertissement, visant à offrir aux étudiants des moments de détente. Nous nous engageons à proposer des événements variés centrés sur les loisirs, comme des soirées jeux, des sorties et des ateliers, afin de favoriser l&#039;épanouissement personnel et le renforcement des liens sociaux au sein de notre école.', '../uploads/logos/BureaudesLoisirsDunkerqueBDL_logo_1761932563.png', '48', 'Dunkerque', 1, 1, NULL, 1),
(41, 'Car community', 'Passion automobile et sports mécaniques', 'Le club réunirait les amateurs de voitures autour d’activités conviviales comme une sortie karting pour vivre l’adrénaline de la course, ou encore une watch party de Formule 1 afin de partager ensemble les moments forts des grands prix', '../uploads/logos/Carcommunity_logo_1761934398.jpeg', '48', 'Calais', 1, NULL, 'Bonjour,\r\n\r\nSuite au retour de la présidente, qui a signalé l’absence de membres, la fiche ne peut pas être validée pour le moment. Merci de bien vouloir compléter les informations nécessaires afin que nous puissions procéder à la validation.\r\n', 0),
(42, 'SHA-BIO TECH', 'Junior Entreprise', 'Junior Entreprise vu avec Mme POREBSKI', '../uploads/logos/SHA-BIOTECH_logo_1761942683.jpg', '22', 'Boulogne', 1, 1, NULL, 1),
(43, 'Move&#039;up', 'sportif', 'Move’Up est le club sportif étudiant qui encourage le mouvement, la motivation et l’esprit d’équipe au sein du campus.\r\nNotre mission : aider chaque étudiant à se dépasser, à rester actif et à trouver l’équilibre entre études et bien-être physique', '../uploads/logos/Move039up_logo_1761950809.jpg', '22', 'Boulogne', 1, 1, NULL, 1),
(44, 'MusicPulse', 'Club de musique', 'Club rassemblant les étudiants autour de la musique, de la pratique instrumentale, du chant et de la création.\r\nMusicpulse favorise l’expression artistique, la collaboration et la découverte de différents styles musicaux.', '../uploads/logos/MusicPulse_logo_1762154690.jpg', '221', 'Boulogne', 1, 1, NULL, 1),
(45, 'MoZaiKSpot', 'Club de culture et de loisirs', 'MoZaïkSpot s’inscrit dans la catégorie Culture et Loisirs. Le nom du club reflète notre vision : tout comme les pièces d’une mosaïque s’assemblent pour former une image harmonieuse, nos activités visent à réunir les étudiants de l’EILCO — chacun avec sa culture et sa personnalité — autour d’échanges, de partages culturels et de moments conviviaux. Véritable spot de rencontres, de créativité et de découverte, le club offre un espace de détente et d’évasion face au rythme des études, tout en favorisant la cohésion et l’ouverture au sein de la communauté étudiante.', '../uploads/logos/MoZaiKSpot_logo_1762182636.png', '47', 'Dunkerque', 1, NULL, 'Vous devez faire 2 trinômes en incluant Rihem MOKHTARI', 0),
(46, 'Eilco Car Community', 'Passion automobile et sports mécaniques', 'EILCO Car Community est le club automobile de notre école d’ingénieurs, rassemblant les passionnés de voitures et de sport mécanique. Notre objectif est de partager notre intérêt commun pour l’automobile à travers diverses activités : watch parties de courses de F1 et d’autres compétitions, sorties karting, visites d’expositions de voitures de collection, et bien d’autres événements conviviaux.\r\nPlus qu’un simple club, EILCO Car Community est un espace d’échange, de découverte et de passion autour du monde de l’automobile, ouvert à tous les curieux et amateurs de vitesse !', '../uploads/logos/EilcoCarCommunity_logo_1762196143.jpg', '48', 'Calais', 1, 1, NULL, 1),
(47, 'EILCook', 'Cuisine', 'Le club EILCook a pour objectif de promouvoir la cuisine et la découverte culinaire au sein du campus. Il offre aux étudiants un espace d’échange et de convivialité autour de la gastronomie, permettant à chacun de développer ses compétences culinaires tout en partageant sa culture et ses traditions culinaires.\r\nÀ travers des ateliers pratiques, des dégustations et des événements thématiques, le club vise à encourager la créativité, le travail d’équipe et la curiosité gustative des membres. EILCook souhaite ainsi créer un véritable moment de partage et de détente dans la vie étudiante.', '../uploads/logos/EILCook_logo_1762541460.jpg', '48', 'Calais', 1, 1, NULL, 1),
(48, 'Poly&#039;Culture', 'Culture générale', 'Notre club de culture générale effectuera plusieurs activités à caractères ludique et éducatif.Ainsi, nous organiserons des quizz par équipe qui permettront aux personnes de mieux se connaître, communiquer et aussi s&#039;amuser tout en apprenant.Nous aurons aussi des moments d&#039;édification après lesquelles plusieurs quitteront en ayant appris de nouvelles choses qui pourraient leur aider dans leurs futures carrières et même pour leur culture propre.', '../uploads/logos/Poly039Culture_logo_1762785195.jpg', '48', 'Longuenesse', NULL, 1, 'La présentation du club n&#039;est pas suffisamment détaillée. Quelles seront les activités ? (détailler) Quels sont les objectifs pratiques du club ? (affinez, ne soyez pas aussi &quot;généraliste&quot;)', 0),
(49, 'Poly&#039;Culture', 'Culture générale', '📝 Proposition de Création du Club Étudiant : Club Poly&#039;Culture\r\nCe document présente un plan d&#039;action détaillé pour la création du Club Poly&#039;Culture, axé sur le ludisme, l&#039;interculturalité et l&#039;engagement sociétal.\r\nI. Mission et Objectifs Fondamentaux\r\nLe Club Poly&#039;Culture a pour objectif de :\r\nPromouvoir la Culture Générale et le Ludisme : Utiliser des jeux populaires comme le Baccalauréat et les quiz pour développer la curiosité et la mémoire rapide.\r\nResserrer les Liens et l&#039;Interculturalité : Créer un environnement où les étudiants de tous niveaux et de toutes nationalités interagissent activement, notamment lors des événements annuels dédiés.\r\nSensibilisation et Action Éthique : Développer la conscience éthique en abordant des sujets cruciaux (ex : drépanocytose, pauvreté), avec une ambition de proposer des solutions,et peut être entreprendre des actions.\r\nAssurer une Présence Numérique Pédagogique : Publier  du contenu culturel en ligne durant cette année académique.\r\n Plan d&#039;Action : Événements Annuels et Activités Régulières\r\nA. Les Grands Événements Annuels (Ouverture à Toute l&#039;EILCO)\r\nCes événements sont conçus pour maximiser la visibilité, l&#039;intégration et la compétition.\r\n1. Le &quot;Poly&#039;Club Challenge&quot; (Une fois l&#039;an) 🏆\r\nFormat : Un grand quiz de culture générale ouvert à l&#039;ensemble des étudiants de l&#039;école et éventuellement au personnel.\r\nObjectif : Créer une compétition amicale et de grande ampleur, mettant en avant le club et la culture générale.\r\n2. Le &quot;Poly&#039;Culture Inter-Monde&quot; (Une fois l&#039;an) 🌍\r\nFormat : Un événement entièrement dédié à la culture interculturelle, pouvant prendre la forme d&#039;un quiz lié à des questions sur les diverses nationalités au sein de l&#039;école. On aimerait poser des questions toutes les nationalités présentes à l&#039;école.\r\nObjectif : Valoriser la diversité des nationalités de l&#039;école et renforcer la communication interculturelle.\r\n3. Le Grand Baccalauréat\r\nFormat : Organisation d&#039;un tournoi du jeu du Petit Bac (catégories classiques : Pays, Villes, Animaux, etc.) de façon individuelle, mettant l&#039;accent sur la réflexion rapide et l&#039;amusement.\r\n\r\nB. Les Moments de Sensibilisation et d&#039;Action  🤝\r\nFormat : Ateliers &quot;Éthique et Poly&#039;Action&quot; sur des thèmes précis (ex : éthique, pauvreté en Afrique, drépanocytose).\r\nOù après plusieurs proposerons leurs idées pour améliorer l&#039;état de la situation.\r\nC. La Communication Numérique \r\nContenu Instagram : le club s&#039;engage à publier des contenus courts et visuels sur la culture générale (faits historiques, citations scientifiques, anecdotes géographiques) sur Instagram de façon régulière.\r\nObjectif : Maintenir une présence active et un engagement régulier des étudiants, transformant la culture générale en un flux d&#039;information régulière et accessible', '../uploads/logos/Poly039Culture_logo_1763094606.jpg', '48', 'Longuenesse', 1, 1, 'Liste des membres non complétée.', 0),
(50, 'MoZaikSpot', 'Culture et Loisirs', 'MoZaikSpot s’inscrit dans la catégorie Culture et Loisirs. Le nom du club reflète notre vision : tout comme les pièces d’une mosaïque s’assemblent pour former une image harmonieuse, nos activités visent à réunir les étudiants de l’EILCO — chacun avec sa culture et sa personnalité — autour d’échanges, de partages culturels et de moments conviviaux. Véritable spot de rencontres, de créativité et de découverte, le club offre un espace de détente et d’évasion face au rythme des études, tout en favorisant la cohésion et l’ouverture au sein de la communauté étudiante.', '../uploads/logos/MoZaikSpot_logo_1763458107.png', '47', 'Dunkerque', NULL, NULL, 'Pas de liste des membres', 0),
(51, 'Poly&#039;Culture', 'Culture générale', 'Club Poly&#039;Culture\r\nCe document présente un plan d&#039;action détaillé pour la création du Club Poly&#039;Culture, axé sur le ludisme, l&#039;interculturalité et l&#039;engagement sociétal.\r\nI. Mission et Objectifs Fondamentaux\r\nLe Club Poly&#039;Culture a pour objectif de :\r\nPromouvoir la Culture Générale et le Ludisme : Utiliser des jeux populaires comme le Baccalauréat et les quiz pour développer la curiosité et la mémoire rapide.\r\nResserrer les Liens et l&#039;Interculturalité : Créer un environnement où les étudiants de tous niveaux et de toutes nationalités interagissent activement, notamment lors des événements annuels dédiés.\r\nSensibilisation et Action Éthique : Développer la conscience éthique en abordant des sujets cruciaux (ex : drépanocytose, pauvreté), avec une ambition de proposer des solutions,et peut être entreprendre des actions.\r\nAssurer une Présence Numérique Pédagogique : Publier  du contenu culturel en ligne durant cette année académique.\r\n Plan d&#039;Action : Événements Annuels et Activités Régulières\r\nA. Les Grands Événements Annuels (Ouverture à Toute l&#039;EILCO)\r\nCes événements sont conçus pour maximiser la visibilité, l&#039;intégration et la compétition.\r\n1. Le &quot;Poly&#039;Club Challenge&quot; (Une fois l&#039;an) 🏆\r\nFormat : Un grand quiz de culture générale ouvert à l&#039;ensemble des étudiants de l&#039;école et éventuellement au personnel.\r\nObjectif : Créer une compétition amicale et de grande ampleur, mettant en avant le club et la culture générale.\r\n2. Le &quot;Poly&#039;Culture Inter-Monde&quot; (Une fois l&#039;an) 🌍\r\nFormat : Un événement entièrement dédié à la culture interculturelle, pouvant prendre la forme d&#039;un quiz lié à des questions sur les diverses nationalités au sein de l&#039;école. On aimerait poser des questions toutes les nationalités présentes à l&#039;école.\r\nObjectif : Valoriser la diversité des nationalités de l&#039;école et renforcer la communication interculturelle.\r\n3. Le Grand Baccalauréat\r\nFormat : Organisation d&#039;un tournoi du jeu du Petit Bac (catégories classiques : Pays, Villes, Animaux, etc.) de façon individuelle, mettant l&#039;accent sur la réflexion rapide et l&#039;amusement.\r\n\r\nB. Les Moments de Sensibilisation et d&#039;Action  🤝\r\nFormat : Ateliers &quot;Éthique et Poly&#039;Action&quot; sur des thèmes précis (ex : éthique, pauvreté en Afrique, drépanocytose).\r\nOù après plusieurs proposerons leurs idées pour améliorer l&#039;état de la situation.\r\nC. La Communication Numérique \r\nContenu Instagram : le club s&#039;engage à publier des contenus courts et visuels sur la culture générale (faits historiques, citations scientifiques, anecdotes géographiques) sur Instagram de façon régulière.\r\nObjectif : Maintenir une présence active et un engagement régulier des étudiants, transformant la culture générale en un flux d&#039;information régulière et accessible', '../uploads/logos/Poly039Culture_logo_1763480542.jpg', '48', 'Longuenesse', NULL, NULL, 'Pas de liste des membres', 0),
(52, 'PULSAR – Préserver, Unir, Lutter, Solidariser, Agir, Responsabiliser', 'Club environnemental et humanitaire', 'Slogan : « Vert main-tenant, c’est pour ça qu’on va agir maintenant ! »\r\nDevise : « La solidarité, ça se vit, pas seulement ça se dit. »\r\n\r\nDescription :\r\nPULSAR est bien plus qu’un simple club associatif : c’est une énergie collective qui rayonne autour de valeurs humaines, écologiques et solidaires. À l’image de l’étoile dont elle porte le nom, PULSAR symbolise la lumière, le mouvement et la constance. Elle éclaire nos villes par des initiatives concrètes et durables, en rappelant qu’un petit geste peut devenir une grande lueur d’espoir.\r\n\r\nNotre mission est de : préserver la planète, unir les citoyens, lutter contre les injustices, solidariser les cœurs, agir concrètement et responsabiliser chacun pour un avenir plus vert et plus juste.\r\n\r\nNous croyons qu’un véritable changement commence là où nous vivons. À travers des actions locales et durables, nous voulons redonner de la vie à nos quartiers, reverdir nos espaces urbains et créer des liens entre les habitants.\r\n\r\nPULSAR rassemble des jeunes, des familles et tous ceux qui veulent passer de la parole à l’action. Nos projets vont du reboisement urbain à la collecte solidaire, en passant par des campagnes de sensibilisation à la protection de l’environnement et au vivre-ensemble.\r\n\r\nNous sommes convaincus que la solidarité et l’écologie ne sont pas des mots à la mode, mais des engagements concrets. Ensemble, nous pouvons bâtir des villes plus vertes, plus humaines et plus responsables.', '../uploads/logos/PULSARPrserverUnirLutterSolidariserAgirResponsabiliser_logo_1763481435.jpg', '231', 'Dunkerque', 1, 1, NULL, 1),
(53, 'Poly&#039;Culture', 'Culture générale', 'Club Poly&#039;Culture\r\nCe document présente un plan d&#039;action détaillé pour la création du Club Poly&#039;Culture, axé sur le ludisme, l&#039;interculturalité et l&#039;engagement sociétal.\r\nI. Mission et Objectifs Fondamentaux\r\nLe Club Poly&#039;Culture a pour objectif de :\r\nPromouvoir la Culture Générale et le Ludisme : Utiliser des jeux populaires comme le Baccalauréat et les quiz pour développer la curiosité et la mémoire rapide.\r\nResserrer les Liens et l&#039;Interculturalité : Créer un environnement où les étudiants de tous niveaux et de toutes nationalités interagissent activement, notamment lors des événements annuels dédiés.\r\nSensibilisation et Action Éthique : Développer la conscience éthique en abordant des sujets cruciaux (ex : drépanocytose, pauvreté), avec une ambition de proposer des solutions,et peut être entreprendre des actions.\r\nAssurer une Présence Numérique Pédagogique : Publier  du contenu culturel en ligne durant cette année académique.\r\n Plan d&#039;Action : Événements Annuels et Activités Régulières\r\nA. Les Grands Événements Annuels (Ouverture à Toute l&#039;EILCO)\r\nCes événements sont conçus pour maximiser la visibilité, l&#039;intégration et la compétition.\r\n1. Le &quot;Poly&#039;Club Challenge&quot; (Une fois l&#039;an) 🏆\r\nFormat : Un grand quiz de culture générale ouvert à l&#039;ensemble des étudiants de l&#039;école et éventuellement au personnel.\r\nObjectif : Créer une compétition amicale et de grande ampleur, mettant en avant le club et la culture générale.\r\n2. Le &quot;Poly&#039;Culture Inter-Monde&quot; (Une fois l&#039;an) 🌍\r\nFormat : Un événement entièrement dédié à la culture interculturelle, pouvant prendre la forme d&#039;un quiz lié à des questions sur les diverses nationalités au sein de l&#039;école. On aimerait poser des questions toutes les nationalités présentes à l&#039;école.\r\nObjectif : Valoriser la diversité des nationalités de l&#039;école et renforcer la communication interculturelle.\r\n3. Le Grand Baccalauréat\r\nFormat : Organisation d&#039;un tournoi du jeu du Petit Bac (catégories classiques : Pays, Villes, Animaux, etc.) de façon individuelle, mettant l&#039;accent sur la réflexion rapide et l&#039;amusement.\r\n\r\nB. Les Moments de Sensibilisation et d&#039;Action  🤝\r\nFormat : Ateliers &quot;Éthique et Poly&#039;Action&quot; sur des thèmes précis (ex : éthique, pauvreté en Afrique, drépanocytose).\r\nOù après plusieurs proposerons leurs idées pour améliorer l&#039;état de la situation.\r\nC. La Communication Numérique \r\nContenu Instagram : le club s&#039;engage à publier des contenus courts et visuels sur la culture générale (faits historiques, citations scientifiques, anecdotes géographiques) sur Instagram de façon régulière.\r\nObjectif : Maintenir une présence active et un engagement régulier des étudiants, transformant la culture générale en un flux d&#039;information régulière et accessible', '../uploads/logos/Poly039Culture_logo_1763554051.jpeg', '48', 'Longuenesse', NULL, NULL, 'Pas de liste des membres', 0),
(54, 'MoZaiKSpot', 'Culture et Loisirs', 'MoZaïkSpot s’inscrit dans la catégorie Culture et Loisirs. Le nom du club reflète notre vision : tout comme les pièces d’une mosaïque s’assemblent pour former une image harmonieuse, nos activités visent à réunir les étudiants de l’EILCO — chacun avec sa culture et sa personnalité — autour d’échanges, de partages culturels et de moments conviviaux. Véritable spot de rencontres, de créativité et de découverte, le club offre un espace de détente et d’évasion face au rythme des études, tout en favorisant la cohésion et l’ouverture au sein de la communauté étudiante.', NULL, '47', 'Dunkerque', NULL, NULL, 'Pas de liste des membres', 0),
(57, 'MoZaiKSpot', 'Culture et Loisirs', 'MoZaïkSpot s’inscrit dans la catégorie Culture et Loisirs. Le nom du club reflète notre vision : tout comme les pièces d’une mosaïque s’assemblent pour former une image harmonieuse, nos activités visent à réunir les étudiants de l’EILCO — chacun avec sa culture et sa personnalité — autour d’échanges, de partages culturels et de moments conviviaux. Véritable spot de rencontres, de créativité et de découverte, le club offre un espace de détente et d’évasion face au rythme des études, tout en favorisant la cohésion et l’ouverture au sein de la communauté étudiante.', '../uploads/logos/TestInfo_logo_1763629921.png', '47', 'Dunkerque', 1, 1, NULL, 1),
(58, 'Poly\Culture', 'Culture générale', 'Club Poly&#039;Culture Ce document présente un plan d&#039;action détaillé pour la création du Club Poly&#039;Culture, axé sur le ludisme, l&#039;interculturalité et l&#039;engagement sociétal. I. Mission et Objectifs Fondamentaux Le Club Poly&#039;Culture a pour objectif de : Promouvoir la Culture Générale et le Ludisme : Utiliser des jeux populaires comme le Baccalauréat et les quiz pour développer la curiosité et la mémoire rapide. Resserrer les Liens et l&#039;Interculturalité : Créer un environnement où les étudiants de tous niveaux et de toutes nationalités interagissent activement, notamment lors des événements annuels dédiés. Sensibilisation et Action Éthique : Développer la conscience éthique en abordant des sujets cruciaux (ex : drépanocytose, pauvreté), avec une ambition de proposer des solutions,et peut être entreprendre des actions. Assurer une Présence Numérique Pédagogique : Publier du contenu culturel en ligne durant cette année académique. Plan d&#039;Action : Événements Annuels et Activités Régulières A. Les Grands Événements Annuels (Ouverture à Toute l&#039;EILCO) Ces événements sont conçus pour maximiser la visibilité, l&#039;intégration et la compétition. 1. Le &quot;Poly&#039;Club Challenge&quot; (Une fois l&#039;an) 🏆 Format : Un grand quiz de culture générale ouvert à l&#039;ensemble des étudiants de l&#039;école et éventuellement au personnel. Objectif : Créer une compétition amicale et de grande ampleur, mettant en avant le club et la culture générale. 2. Le &quot;Poly&#039;Culture Inter-Monde&quot; (Une fois l&#039;an) 🌍 Format : Un événement entièrement dédié à la culture interculturelle, pouvant prendre la forme d&#039;un quiz lié à des questions sur les diverses nationalités au sein de l&#039;école. On aimerait poser des questions toutes les nationalités présentes à l&#039;école. Objectif : Valoriser la diversité des nationalités de l&#039;école et renforcer la communication interculturelle. 3. Le Grand Baccalauréat Format : Organisation d&#039;un tournoi du jeu du Petit Bac (catégories classiques : Pays, Villes, Animaux, etc.) de façon individuelle, mettant l&#039;accent sur la réflexion rapide et l&#039;amusement. B. Les Moments de Sensibilisation et d&#039;Action 🤝 Format : Ateliers &quot;Éthique et Poly&#039;Action&quot; sur des thèmes précis (ex : éthique, pauvreté en Afrique, drépanocytose). Où après plusieurs proposerons leurs idées pour améliorer l&#039;état de la situation. C. La Communication Numérique Contenu Instagram : le club s&#039;engage à publier des contenus courts et visuels sur la culture générale (faits historiques, citations scientifiques, anecdotes géographiques) sur Instagram de façon régulière. Objectif : Maintenir une présence active et un engagement régulier des étudiants, transformant la culture générale en un flux d&#039;information régulière et accessible.', '../uploads/logos/Polyculture_logo_1763731723.jpeg', '48', 'Longuenesse', 1, 1, NULL, 1),
(60, 'Poly&#039;Culture', 'Culture générale', 'Club Poly&#039;Culture\r\nCe document présente un plan d&#039;action détaillé pour la création du Club Poly&#039;Culture, axé sur le ludisme, l&#039;interculturalité et l&#039;engagement sociétal.\r\nI. Mission et Objectifs Fondamentaux\r\nLe Club Poly&#039;Culture a pour objectif de :\r\nPromouvoir la Culture Générale et le Ludisme : Utiliser des jeux populaires comme le Baccalauréat et les quiz pour développer la curiosité et la mémoire rapide.\r\nResserrer les Liens et l&#039;Interculturalité : Créer un environnement où les étudiants de tous niveaux et de toutes nationalités interagissent activement, notamment lors des événements annuels dédiés.\r\nSensibilisation et Action Éthique : Développer la conscience éthique en abordant des sujets cruciaux (ex : drépanocytose, pauvreté), avec une ambition de proposer des solutions,et peut être entreprendre des actions.\r\nAssurer une Présence Numérique Pédagogique : Publier du contenu culturel en ligne durant cette année académique.\r\n Plan d&#039;Action : Événements Annuels et Activités Régulières\r\nA. Les Grands Événements Annuels (Ouverture à Toute l&#039;EILCO)\r\nCes événements sont conçus pour maximiser la visibilité, l&#039;intégration et la compétition.\r\n1. Le &quot;Poly&#039;Club Challenge&quot; (Une fois l&#039;an) 🏆\r\nFormat : Un grand quiz de culture générale ouvert à l&#039;ensemble des étudiants de l&#039;école et éventuellement au personnel.\r\nObjectif : Créer une compétition amicale et de grande ampleur, mettant en avant le club et la culture générale.\r\n2. Le &quot;Poly&#039;Culture Inter-Monde&quot; (Une fois l&#039;an) 🌍\r\nFormat : Un événement entièrement dédié à la culture interculturelle, pouvant prendre la forme d&#039;un quiz lié à des questions sur les diverses nationalités au sein de l&#039;école. On aimerait poser des questions toutes les nationalités présentes à l&#039;école.\r\nObjectif : Valoriser la diversité des nationalités de l&#039;école et renforcer la communication interculturelle.\r\n3. Le Grand Baccalauréat\r\nFormat : Organisation d&#039;un tournoi du jeu du Petit Bac (catégories classiques : Pays, Villes, Animaux, etc.) de façon individuelle, mettant l&#039;accent sur la réflexion rapide et l&#039;amusement.\r\n\r\nB. Les Moments de Sensibilisation et d&#039;Action 🤝\r\nFormat : Ateliers &quot;Éthique et Poly&#039;Action&quot; sur des thèmes précis (ex : éthique, pauvreté en Afrique, drépanocytose).\r\nOù après plusieurs proposerons leurs idées pour améliorer l&#039;état de la situation.\r\nC. La Communication Numérique \r\nContenu Instagram : le club s&#039;engage à publier des contenus courts et visuels sur la culture générale (faits historiques, citations scientifiques, anecdotes géographiques) sur Instagram de façon régulière.\r\nObjectif : Maintenir une présence active et un engagement régulier des étudiants, transformant la culture générale en un flux d&#039;information régulière et accessible', '../uploads/logos/Poly039Culture_logo_1763985011.jpg', '48', 'Longuenesse', 1, 1, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `fiche_event`
--

CREATE TABLE `fiche_event` (
  `event_id` int NOT NULL,
  `date_depot` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `validation_admin` tinyint(1) DEFAULT NULL,
  `validation_bde` tinyint(1) DEFAULT NULL,
  `validation_tuteur` tinyint(1) DEFAULT NULL,
  `validation_soutenance` tinyint(1) NOT NULL DEFAULT '0',
  `titre` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `club_orga` varchar(128) NOT NULL,
  `campus` varchar(128) NOT NULL,
  `date_ev` date NOT NULL,
  `horaire_debut` time NOT NULL,
  `horaire_fin` time NOT NULL,
  `lieu` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_responsable` smallint NOT NULL,
  `description` text NOT NULL,
  `financement_bde` tinyint NOT NULL,
  `montant` int NOT NULL,
  `fiche_sanitaire` varchar(255) DEFAULT NULL,
  `affiche` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rapport_event` varchar(255) DEFAULT NULL,
  `images_event` text DEFAULT NULL,
  `motif_refus` varchar(255) DEFAULT NULL,
  `validation_finale` tinyint DEFAULT NULL,
  `commentaire_validation` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fiche_event`
--

INSERT INTO `fiche_event` (`event_id`, `date_depot`, `validation_admin`, `validation_bde`, `validation_tuteur`, `validation_soutenance`, `titre`, `club_orga`, `campus`, `date_ev`, `horaire_debut`, `horaire_fin`, `lieu`, `id_responsable`, `description`, `financement_bde`, `montant`, `fiche_sanitaire`, `affiche`, `rapport_event`, `motif_refus`, `validation_finale`, `commentaire_validation`) VALUES
(1, '2025-09-22 13:41:55', NULL, NULL, NULL, 0, 'Vente de viennoiseries', '1', 'Calais', '2025-10-15', '10:00:00', '15:30:00', 'Hall du bâtiment A', 25, 'Ceci est un événement test.', 1, 100, NULL, NULL, NULL, 'Événement test', 0, NULL),
(2, '2025-09-22 14:12:45', NULL, NULL, NULL, 0, 'Vente', '1', 'Calais', '2025-10-16', '10:00:00', '15:30:00', 'Bat A', 25, 'Deuxième événement test', 1, 100, NULL, NULL, NULL, 'Événement test', 0, NULL),
(3, '2025-10-14 19:06:02', 1, 1, 1, 1, 'Événement Loup-Garou', '10', 'Calais', '2025-06-11', '13:30:00', '17:30:00', 'A118', 64, 'La nuit tombe sur le village de Thiercelieux. Dans l’ombre qui s’étend, la peur grandit. Comme chaque nuit, les loups-garous se réveillent pour commettre un meurtre.\r\n\r\nVenez nous rejoindre, comme chaque année, pour participer à des parties de Loup-Garou, dans une ambiance spécialement liée à Halloween ! Vous aurez ainsi l’occasion de jouer, ou de rejouer, à ce célèbre jeu de coopération.\r\n\r\nAmusement garanti !', 0, 0, NULL, '../uploads/affiches_event/D10Cass_affiche_Événement Loup-Garou_1760461562.webp', '../uploads/rapports/D10Cassé_Événement Loup-Garou_1763293233.pdf', NULL, 1, NULL),
(4, '2025-10-14 19:07:14', NULL, NULL, 1, 0, 'Événement Loup-Garou', '10', 'Calais', '2025-06-11', '13:30:00', '17:30:00', 'A118', 64, 'La nuit tombe sur le village de Thiercelieux. Dans l’ombre qui s’étend, la peur grandit. Comme chaque nuit, les loups-garous se réveillent pour commettre un meurtre.\r\n\r\nVenez nous rejoindre, comme chaque année, pour participer à des parties de Loup-Garou, dans une ambiance spécialement liée à Halloween ! Vous aurez ainsi l’occasion de jouer, ou de rejouer, à ce célèbre jeu de coopération.\r\n\r\nAmusement garanti !', 0, 0, NULL, '../uploads/affiches_event/D10Cass_affiche_Événement Loup-Garou_1760461634.webp', '../uploads/rapports/D10Cassé_Événement Loup-Garou_1763293267.pdf', 'Doublon', 0, NULL),
(5, '2025-10-14 19:09:03', NULL, NULL, NULL, 0, 'Événement Loup-Garou', '10', 'Calais', '2025-06-11', '13:30:00', '17:30:00', 'A118', 64, 'La nuit tombe sur le village de Thiercelieux. Dans l’ombre qui s’étend, la peur grandit. Comme chaque nuit, les loups-garous se réveillent pour commettre un meurtre.\r\n\r\nVenez nous rejoindre, comme chaque année, pour participer à des parties de Loup-Garou, dans une ambiance spécialement liée à Halloween ! Vous aurez ainsi l’occasion de jouer, ou de rejouer, à ce célèbre jeu de coopération.\r\n\r\nAmusement garanti !', 0, 0, NULL, '../uploads/affiches_event/D10Cass_affiche_Événement Loup-Garou_1760461743.webp', '../uploads/rapports/D10Cassé_Événement Loup-Garou_1763293277.pdf', 'Doublon', 0, NULL),
(6, '2025-10-14 19:10:16', NULL, NULL, NULL, 0, 'Événement Loup-Garou', '10', 'Calais', '2025-06-11', '13:30:00', '17:30:00', 'A118', 64, 'La nuit tombe sur le village de Thiercelieux. Dans l’ombre qui s’étend, la peur grandit. Comme chaque nuit, les loups-garous se réveillent pour commettre un meurtre.\r\n\r\nVenez nous rejoindre, comme chaque année, pour participer à des parties de Loup-Garou, dans une ambiance spécialement liée à Halloween ! Vous aurez ainsi l’occasion de jouer, ou de rejouer, à ce célèbre jeu de coopération.\r\n\r\nAmusement garanti !', 0, 0, NULL, '../uploads/affiches_event/D10Cass_affiche_Événement Loup-Garou_1760461816.webp', '../uploads/rapports/D10Cassé_Événement Loup-Garou_1763293286.pdf', 'Doublon', 0, NULL),
(7, '2025-10-14 19:11:06', NULL, NULL, NULL, 0, 'Événement Loup-Garou', '6', 'Calais', '2025-06-11', '13:30:00', '17:30:00', 'A118', 64, 'La nuit tombe sur le village de Thiercelieux. Dans l’ombre qui s’étend, la peur grandit. Comme chaque nuit, les loups-garous se réveillent pour commettre un meurtre.\r\n\r\nVenez nous rejoindre, comme chaque année, pour participer à des parties de Loup-Garou, dans une ambiance spécialement liée à Halloween ! Vous aurez ainsi l’occasion de jouer, ou de rejouer, à ce célèbre jeu de coopération.\r\n\r\nAmusement garanti !', 0, 0, NULL, '../uploads/affiches_event/D10Cass_affiche_Événement Loup-Garou_1760461865.webp', '../uploads/rapports/D10 Cassé_Événement Loup-Garou_1763293295.pdf', 'Doublon', 0, NULL),
(8, '2025-10-15 18:11:17', NULL, NULL, NULL, 0, 'Voyage à la Paris Games Week', '7', 'Calais', '2025-10-31', '05:00:00', '00:30:00', 'Paris, Porte de Versailles', 26, 'Cet évènement a pour objectif de faire voyager les étudiants dans un univers qui pour beaucoup, leur est méconnu à la fois sur le plan de l’innovation et du divertissement. Le projet vise à offrir une nouvelle vision du jeu vidéo, loin des clichés associés à une simple activité derrière un écran. Il s’agit également de leur faire découvrir les dernières nouveautés en avant-première, ainsi que les avancées technologiques destinées à rendre le jeu plus accessible aux personnes en situation de handicap. Le départ est prévu à 5h30 depuis le restaurant universitaire de Calais, pour une arrivée estimée aux alentours de 10h au salon. Le retour s&#039;effectuera à 20h30 depuis Paris, avec une arrivée à Calais vers 00h30. Ce voyage est proposé à 10€ par étudiant. Les repas resteront à la charge des étudiants.', 1, 150, NULL, '../uploads/affiches_event/Ch039tisGamers_affiche_Voyage à la Paris Games Week_1760544677.jpg', '../uploads/rapports/Ch&#039;tis Gamers_Voyage à la Paris Games Week_1762548713.pdf', 'Fiche reçue en double', 0, NULL),
(9, '2025-10-15 18:12:28', 1, 1, 1, 1, 'Voyage à la Paris Games Week', '7', 'Calais', '2025-10-31', '05:00:00', '00:30:00', 'Paris, Porte de Versailles', 26, 'Cet évènement a pour objectif de faire voyager les étudiants dans un univers qui pour beaucoup, leur est méconnu à la fois sur le plan de l’innovation et du divertissement. Le projet vise à offrir une nouvelle vision du jeu vidéo, loin des clichés associés à une simple activité derrière un écran. Il s’agit également de leur faire découvrir les dernières nouveautés en avant-première, ainsi que les avancées technologiques destinées à rendre le jeu plus accessible aux personnes en situation de handicap. Le départ est prévu à 5h30 depuis le restaurant universitaire de Calais, pour une arrivée estimée aux alentours de 10h au salon. Le retour s&#039;effectuera à 20h30 depuis Paris, avec une arrivée à Calais vers 00h30. Ce voyage est proposé à 10€ par étudiant. Les repas resteront à la charge des étudiants.', 1, 150, NULL, '../uploads/affiches_event/Ch039tisGamers_affiche_Voyage à la Paris Games Week_1760544748.jpg', NULL, NULL, 1, NULL),
(11, '2025-10-28 16:59:45', NULL, NULL, 1, 0, 'Atelier DIY', '12', 'Calais', '2025-11-13', '13:30:00', '18:00:00', 'A118', 42, 'Atelier de créations artistiques tournées vers la culture asiatique : peinture de lanternes, création d&#039;origamis et d&#039;omamoris et pleins d&#039;autres !', 0, 0, '../uploads/fiches_sanitaires/EILCO039TAKU_fiche_sanitaire_Atelier DIY_1761667185.pdf', '../uploads/affiches_event/EILCO039TAKU_affiche_Atelier DIY_1761667185.jpg', '../uploads/rapports/EILCO&#039;TAKU_Atelier DIY_1764065031.pdf', 'Fiche en double. ', 0, NULL),
(12, '2025-10-28 16:59:53', 1, 1, 1, 1, 'Atelier DIY', '12', 'Calais', '2025-11-13', '13:30:00', '18:00:00', 'A118', 42, 'Atelier de créations artistiques tournées vers la culture asiatique : peinture de lanternes, création d&#039;origamis et d&#039;omamoris et pleins d&#039;autres !', 0, 0, '../uploads/fiches_sanitaires/EILCO039TAKU_fiche_sanitaire_Atelier DIY_1761667193.pdf', '../uploads/affiches_event/EILCO039TAKU_affiche_Atelier DIY_1761667193.jpg', NULL, NULL, 1, NULL),
(13, '2025-10-28 21:11:07', 1, 1, 1, 1, 'Atelier de Programmation et Soutien Projets', '8', 'Calais', '2025-11-13', '13:30:00', '17:30:00', 'A113/A114', 81, 'L&#039;objectif de cet atelier est de proposer un accompagnement pour travailler sur des projets ou se perfectionner dans différents langages.\r\n- avec leur propre projet (ex : Arduino pour les CP2, projet C pour les ING1, etc.) afin de bénéficier de conseils,\r\n- ou simplement pour pratiquer la programmation à travers des exercices en ligne adaptés à leur niveau.\r\n\r\nCet atelier favorise l&#039;entraide entre étudiants et permet d&#039;échanger sur les bonnes pratiques de code, tout en consolidant les bases techniques de chacun.', 0, 0, NULL, '../uploads/affiches_event/EILTech_affiche_Atelier de Programmation et Soutien Projets_1761682267.png', '../uploads/rapports/EILTech_Atelier de Programmation et Soutien Projets_1763671396.pdf', NULL, 1, NULL),
(14, '2025-10-28 23:24:27', 1, 1, 1, 1, 'Journée orientale', '14', 'Calais', '2025-11-13', '13:30:00', '17:30:00', 'A117', 84, '-Présentation des traditions et coûtumes de la culture orientale(mariage, fêtes).\r\n-Organiser un jeu linguistique. \r\n-Organiser un coin photo bien décoré avec le thème traditionnel.\r\n-danses traditionnelles', 0, 0, NULL, NULL, '../uploads/rapports/Eilcoriental_Journée orientale_1764191506.pdf', NULL, 1, 'En cas de diffusion de musique, le son de celle-ci devra être modéré.'),
(15, '2025-10-28 23:25:15', NULL, NULL, 1, 0, 'Journée orientale', '14', 'Calais', '2025-11-13', '13:30:00', '17:00:00', 'A117', 84, '-Présentation des traditions et coûtumes de la culture orientale(mariage, fêtes).\r\n-Organiser un jeu linguistique. \r\n-Organiser un coin photo bien décoré avec le thème traditionnel.\r\n-danses traditionnelles', 0, 0, NULL, NULL, NULL, 'Fiche envoyée en doublon.', 0, NULL),
(16, '2025-11-04 12:38:01', 1, 1, 1, 1, 'Tournoi fléchettes et ringfit', '2', 'Calais', '2025-11-20', '13:15:00', '16:15:00', 'A111', 58, '🎉 Tournoi Fun &amp; Sport : Fléchettes &amp; Ring Fit !\r\n\r\nPréparez-vous pour une journée placée sous le signe de la convivialité, du fun et du défi sportif !\r\nQue vous soyez un tireur d’élite des fléchettes ou un adepte du fitness virtuel, cet événement est fait pour vous.\r\n\r\n🏆 Au programme :\r\n\r\n🎯 Tournoi de fléchettes\r\n\r\n- Matches en un contre un, format à élimination directe.\r\n\r\n- Précision, stratégie et sang-froid seront vos meilleurs atouts !\r\n\r\n- Des prix pour les meilleurs tireurs et les plus originaux.\r\n\r\n💪 Ring Fit Adventure Challenge\r\n\r\n- Défiez vous dans une série d’épreuves sportives sur Nintendo Switch.\r\n\r\n- Tests d’endurance, de vitesse et de coordination : qui sera le champion du Ring ?\r\n\r\n- Classement en temps réel et encouragements garantis !\r\n\r\n🍻 Ambiance &amp; animations :\r\n\r\n- Musique, rires et encouragements toute la journée.\r\n\r\n- Espace détente et boissons disponibles.\r\n\r\n- Petites récompenses et surprises à gagner !\r\n\r\n📅 Infos pratiques :\r\n\r\n📍 Lieu : A111\r\n🕒 Date et heure : de 13h15 à 16h15\r\n🎟️ Entrée libre ', 0, 0, NULL, '../uploads/affiches_event/BureaudesSportCalaisBDS_affiche_Tournoi fléchettes et ringfit_1762256281.jpg', '../uploads/rapports/Bureau des Sport Calais (BDS)_Tournoi fléchettes et ringfit_1764321439.pdf', NULL, 1, 'Attention aux mesures de sécurité. Merci de désigner un référent sécurité. Me le confirmer par mail jean-francois.bernard@univ-littoral.fr'),
(17, '2025-11-04 21:29:49', 1, 1, 1, 1, 'C&#039;est Noël chez D10Cassé', '10', 'Calais', '2025-12-11', '15:00:00', '17:30:00', 'A118 et A117', 64, 'C&#039;est Noël chez D10Cassé, la neige tombe dehors, mais les cartes s&#039;enflamment et les dés roulent.\r\nD10 t&#039;invite à une après midi ludique enchantée !\r\n\r\nViens affronter les esprits festifs avec l&#039;âme des cartes ou incarne un héros téméraire lors d&#039;un possible one-shot de jeu de rôle spécialement imaginé pour l&#039;occasion.\r\n\r\nAu programme :\r\n- Jeux de carte (Uno, Skyjo, Poker...)\r\n- Jeux de Rôle (Dungeons &amp; Dragons)\r\n\r\nQue tu sois mage, stratège ou simple curieux, une seule règle : amuse-toi à fond !', 0, 0, NULL, '../uploads/affiches_event/D10Cass_affiche_C&#039;est Noël chez D10Cassé_1762288189.webp', NULL, NULL, 1, NULL),
(18, '2025-11-05 14:57:53', NULL, NULL, 1, 0, 'Gala soirée orientale ', '14', 'Calais', '2025-11-20', '13:30:00', '17:30:00', 'A110', 82, 'Repas tunisien et marocain.\r\n\r\nKaraoké, musique et danse pour une ambiance festive et conviviale.\r\n\r\nJeux et activités pour partager un bon moment ensemble.\r\n\r\nHabits traditionnels et défis (optionnel, si on arrive à en trouver', 0, 0, '../uploads/fiches_sanitaires/Eilcoriental_fiche_sanitaire_Gala soirée orientale _1762351073.pdf', NULL, NULL, 'Par respect des normes sanitaires, il ne peut pas y avoir de préparration culinaire à risques ou complexe (viande, cuisson) dans l&#039;enceinte du bâtiment. En l&#039;espèce la fiche sanitaire ne précise pas quelles seraient les aliments servis.', 0, NULL),
(19, '2025-11-05 15:49:14', NULL, NULL, 1, 0, 'EILCO PROBLEM SOLVING CHALLENGE', '20', 'Calais', '2025-11-20', '13:30:00', '17:30:00', 'EILCO , SALLE TP', 132, 'Le “EILCO Problem Solving Challenge” est une compétition de programmation algorithmique en équipes, prévu le jeudi 20 novembre 2025 de 13h30 à 17h30 au campus de l’EILCO. L’événement, ouvert à tous les cycles ainsi qu’aux participants extérieurs, vise à développer les compétences en logique, algorithmique et travail collaboratif à travers des défis inspirés des concours internationaux. Selon le nombre de participants, la compétition pourra se dérouler en une ou deux phases, nécessitant éventuellement deux salles. L’activité sera encadrée par des étudiants organisateurs sous supervision pédagogique, et pourra se conclure par la remise de récompenses symboliques aux meilleures équipes.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(20, '2025-11-05 15:50:09', NULL, NULL, 1, 0, 'PROBLEM SOLVING CHALLENGE', '20', 'Calais', '2025-11-20', '13:30:00', '17:30:00', 'EILCO , SALLE TP', 132, 'Le “EILCO Problem Solving Challenge” est une compétition de programmation algorithmique en équipes, prévu le jeudi 20 novembre 2025 de 13h30 à 17h30 au campus de l’EILCO. L’événement, ouvert à tous les cycles ainsi qu’aux participants extérieurs, vise à développer les compétences en logique, algorithmique et travail collaboratif à travers des défis inspirés des concours internationaux. Selon le nombre de participants, la compétition pourra se dérouler en une ou deux phases, nécessitant éventuellement deux salles. L’activité sera encadrée par des étudiants organisateurs sous supervision pédagogique, et pourra se conclure par la remise de récompenses symboliques aux meilleures équipes.', 1, 100, NULL, NULL, '../uploads/rapports/Innov&#039;EILCO_PROBLEM SOLVING CHALLENGE_1764451649.pdf', 'Fiche envoyée en plusieurs fois.', 0, NULL),
(21, '2025-11-05 15:51:06', NULL, NULL, 1, 0, 'PROBLEM SOLVING CHALLENGE', '20', 'Calais', '2025-11-20', '13:30:00', '17:30:00', 'EILCO , SALLE TP', 132, 'Le “EILCO Problem Solving Challenge” est une compétition de programmation algorithmique en équipes, prévu le jeudi 20 novembre 2025 de 13h30 à 17h30 au campus de l’EILCO. L’événement, ouvert à tous les cycles ainsi qu’aux participants extérieurs, vise à développer les compétences en logique, algorithmique et travail collaboratif à travers des défis inspirés des concours internationaux. Selon le nombre de participants, la compétition pourra se dérouler en une ou deux phases, nécessitant éventuellement deux salles. L’activité sera encadrée par des étudiants organisateurs sous supervision pédagogique, et pourra se conclure par la remise de récompenses symboliques aux meilleures équipes.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(22, '2025-11-05 15:53:26', 1, 1, 1, 1, 'PROBLEM SOLVING CHALLENGE', '20', 'Calais', '2025-11-20', '13:30:00', '17:30:00', 'EILCO , SALLE TP', 132, 'Le “EILCO Problem Solving Challenge” est une compétition de programmation algorithmique en équipes, prévue le jeudi 20 novembre 2025 de 13h30 à 17h30 sur le campus de l’EILCO. L’événement, ouvert à tous les cycles ainsi qu’aux participants extérieurs, vise à développer les compétences en logique, en algorithmique et en travail collaboratif à travers des défis inspirés des concours internationaux. Selon le nombre de participants, la compétition pourra se dérouler en une ou deux phases, nécessitant éventuellement deux salles. L’activité sera encadrée par des étudiants organisateurs, sous supervision pédagogique, et pourra se conclure par la remise de récompenses symboliques aux meilleures équipes.', 1, 100, NULL, '../uploads/affiches_event/Innov039EILCO_affiche_PROBLEM SOLVING CHALLENGE_1762354406.png', NULL, NULL, 1, NULL),
(23, '2025-11-06 21:54:17', NULL, NULL, 1, 0, 'Forum Solidarité', '13', 'Calais', '2025-12-04', '12:15:00', '18:00:00', 'Amphi A002', 106, 'un forum de la solidarité pour aider les étudiants de ING1 à choisir leur association pour leur projet solidaire.', 1, 150, NULL, '../uploads/affiches_event/BDH_affiche_Forum Solidarité_1762462457.jpg', NULL, 'Fiche envoyée en plusieurs fois.', 0, NULL),
(24, '2025-11-06 21:55:09', NULL, NULL, 1, 0, 'Forum Solidarité', '13', 'Calais', '2025-12-04', '12:15:00', '18:00:00', 'Amphi A002', 106, 'un forum de la solidarité pour aider les étudiants de ING1 à choisir leur association pour leur projet solidaire.', 1, 150, NULL, '../uploads/affiches_event/BDH_affiche_Forum Solidarité_1762462509.jpg', NULL, 'Fiche envoyée en plusieurs fois.', 0, NULL),
(25, '2025-11-06 21:55:34', 1, 1, 1, 1, 'Forum Solidarité', '13', 'Calais', '2025-12-04', '12:15:00', '18:00:00', 'Amphi A002', 106, 'un forum de la solidarité pour aider les étudiants de ING1 à choisir leur association pour leur projet solidaire.', 1, 150, NULL, '../uploads/affiches_event/BDH_affiche_Forum Solidarité_1762462534.jpg', NULL, NULL, 1, NULL),
(26, '2025-11-11 22:55:50', NULL, NULL, NULL, 0, 'Vente de pizzas + tournoi Mario Kart', '7', 'Calais', '2025-11-27', '12:15:00', '16:30:00', 'Hall bâtiment A (pour la vente) et salle A117 (pour le tournoi)', 26, 'C&#039;est un événement traditionnel du club Ch&#039;tis Gamers qui a pour but de rassembler les étudiants autour d&#039;une vente de pizza et d&#039;un tournoi Mario Kart. La vente se fera dans le hall du bâtiment A.', 0, 0, '../uploads/fiches_sanitaires/Ch039tisGamers_fiche_sanitaire_Vente de pizzas + tournoi Mario Kart_1762898150.pdf', NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(27, '2025-11-11 22:55:55', NULL, NULL, NULL, 0, 'Vente de pizzas + tournoi Mario Kart', '7', 'Calais', '2025-11-27', '12:15:00', '16:30:00', 'Hall bâtiment A (pour la vente) et salle A117 (pour le tournoi)', 26, 'C&#039;est un événement traditionnel du club Ch&#039;tis Gamers qui a pour but de rassembler les étudiants autour d&#039;une vente de pizza et d&#039;un tournoi Mario Kart. La vente se fera dans le hall du bâtiment A.', 0, 0, '../uploads/fiches_sanitaires/Ch039tisGamers_fiche_sanitaire_Vente de pizzas + tournoi Mario Kart_1762898155.pdf', NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(28, '2025-11-11 22:56:03', NULL, NULL, NULL, 0, 'Vente de pizzas + tournoi Mario Kart', '7', 'Calais', '2025-11-27', '12:15:00', '16:30:00', 'Hall bâtiment A (pour la vente) et salle A117 (pour le tournoi)', 26, 'C&#039;est un événement traditionnel du club Ch&#039;tis Gamers qui a pour but de rassembler les étudiants autour d&#039;une vente de pizza et d&#039;un tournoi Mario Kart. La vente se fera dans le hall du bâtiment A.', 0, 0, '../uploads/fiches_sanitaires/Ch039tisGamers_fiche_sanitaire_Vente de pizzas + tournoi Mario Kart_1762898163.pdf', NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(29, '2025-11-11 22:56:18', NULL, NULL, NULL, 0, 'Vente de pizzas + tournoi Mario Kart', '7', 'Calais', '2025-11-27', '12:15:00', '16:30:00', 'Hall bâtiment A (pour la vente) et salle A117 (pour le tournoi)', 26, 'C&#039;est un événement traditionnel du club Ch&#039;tis Gamers qui a pour but de rassembler les étudiants autour d&#039;une vente de pizza et d&#039;un tournoi Mario Kart. La vente se fera dans le hall du bâtiment A.', 0, 0, '../uploads/fiches_sanitaires/Ch039tisGamers_fiche_sanitaire_Vente de pizzas + tournoi Mario Kart_1762898178.pdf', NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(30, '2025-11-11 22:56:19', NULL, NULL, NULL, 0, 'Vente de pizzas + tournoi Mario Kart', '7', 'Calais', '2025-11-27', '12:15:00', '16:30:00', 'Hall bâtiment A (pour la vente) et salle A117 (pour le tournoi)', 26, 'C&#039;est un événement traditionnel du club Ch&#039;tis Gamers qui a pour but de rassembler les étudiants autour d&#039;une vente de pizza et d&#039;un tournoi Mario Kart. La vente se fera dans le hall du bâtiment A.', 0, 0, '../uploads/fiches_sanitaires/Ch039tisGamers_fiche_sanitaire_Vente de pizzas + tournoi Mario Kart_1762898179.pdf', NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(31, '2025-11-11 22:56:19', 1, 1, 1, 1, 'Vente de pizzas + tournoi Mario Kart', '7', 'Calais', '2025-11-27', '12:15:00', '16:30:00', 'Hall bâtiment A (pour la vente) et salle A117 (pour le tournoi)', 26, 'C&#039;est un événement traditionnel du club Ch&#039;tis Gamers qui a pour but de rassembler les étudiants autour d&#039;une vente de pizza et d&#039;un tournoi Mario Kart. La vente se fera dans le hall du bâtiment A.', 0, 0, '../uploads/fiches_sanitaires/Ch039tisGamers_fiche_sanitaire_Vente de pizzas + tournoi Mario Kart_1762898179.pdf', NULL, '../uploads/rapports/Ch&#039;tis Gamers_Vente de pizzas + tournoi Mario Kart_1765142506.pdf', NULL, 1, NULL),
(32, '2025-11-12 19:20:51', 1, 1, 1, 1, 'Atelier Scénario &amp; Acting', '26', 'Calais', '2025-11-27', '15:30:00', '17:30:00', 'A119', 121, 'Avec CinéCo, revis les scènes cultes de tes films préférés, puis imagine et écris ta propre version de la fin avec ton groupe ! Entre jeu d’acteur, créativité et fous rires pour tous ceux qui aiment vivre le cinéma plutôt que de le regarder.', 0, 0, NULL, '../uploads/affiches_event/CinCo_affiche_Atelier Scénario &amp; Acting_1762971651.jpg', '../uploads/rapports/CinéCo_Atelier Scénario &amp; Acting_1765459791.pdf', NULL, 1, NULL),
(33, '2025-11-12 21:09:16', 1, 1, 1, 1, 'Soirée orientale', '14', 'Calais', '2025-11-27', '13:30:00', '17:30:00', 'A110', 82, 'Repas tunisien et marocain. Karaoké, musique et danse pour une ambiance festive et conviviale. Jeux et activités pour partager un bon moment ensemble. Habits traditionnels et défis (optionnel, si on arrive à en trouver)', 0, 0, '../uploads/fiches_sanitaires/Eilcoriental_fiche_sanitaire_Soirée orientale_1762978156.pdf', NULL, '../uploads/rapports/Eilcoriental_Soirée orientale_1765396763.pdf', NULL, 1, NULL),
(34, '2025-11-13 17:37:23', NULL, NULL, 1, 0, 'Initiation au JDR', '30', 'Longuenesse', '2025-12-04', '13:00:00', '18:00:00', 'MDE Saint-Omer', 135, 'Nous souhaitons proposer aux participants de découvrir l&#039;univers des jeux de rôles. Selon le thème du JDR qui sera choisit les joueurs incarneront des personnages dans un monde où ils devront remplir une quête principale et des objectifs secondaires en s&#039;appuyant sur les capacités de leur personnage et en travaillant en équipe. Ils seront encadré par un maître du jeu qui fera progresser l&#039;équipe et les mettra face à des défis. ', 0, 0, NULL, '../uploads/affiches_event/LesJoueursdeDsCartes_affiche_Initiation au JDR_1763051843.png', NULL, NULL, NULL, NULL),
(35, '2025-11-13 19:49:06', NULL, NULL, 1, 0, 'Escape Game', '27', 'Longuenesse', '2025-11-29', '13:15:00', '18:15:00', 'Les énigmes de Vissery - Escape Game - 9 Rue Henri Dupuis, 62500 Saint-Omer', 93, 'Un escape game est un jeu d&#039;évasion grandeur nature où une équipe est enfermée dans une pièce thématique, comme un laboratoire ou un donjon. Le but est de résoudre une série d&#039;énigmes et de casse-têtes complexes en moins de 60 minutes. C&#039;est une activité de team building et de loisir immersive, exigeant observation, logique et collaboration intense.', 1, 100, NULL, '../uploads/affiches_event/Mixgamemates_affiche_Escape Game_1763059746.jpeg', NULL, NULL, NULL, NULL),
(36, '2025-11-13 19:50:22', NULL, NULL, 1, 0, 'Escape Game', '27', 'Longuenesse', '2025-11-29', '13:15:00', '18:15:00', 'Les énigmes de Vissery - Escape Game - 9 Rue Henri Dupuis, 62500 Saint-Omer', 93, 'Un escape game est un jeu d&#039;évasion grandeur nature où une équipe est enfermée dans une pièce thématique, comme un laboratoire ou un donjon. Le but est de résoudre une série d&#039;énigmes et de casse-têtes complexes en moins de 60 minutes. C&#039;est une activité de team building et de loisir immersive, exigeant observation, logique et collaboration intense.', 1, 100, NULL, '../uploads/affiches_event/Mixgamemates_affiche_Escape Game_1763059822.jpeg', NULL, NULL, NULL, NULL),
(37, '2025-11-13 19:51:57', NULL, NULL, 1, 0, 'Escape Game', '27', 'Longuenesse', '2025-11-29', '13:15:00', '18:15:00', 'Les énigmes de Vissery - Escape Game - 9 Rue Henri Dupuis, 62500 Saint-Omer', 93, 'Un escape game est un jeu d&amp;#039;évasion grandeur nature où une équipe est enfermée dans une pièce thématique, comme un laboratoire ou un donjon. Le but est de résoudre une série d&amp;#039;énigmes et de casse-têtes complexes en moins de 60 minutes. C&amp;#039;est une activité de team building et de loisir immersive, exigeant observation, logique et collaboration intense.', 1, 100, NULL, '../uploads/affiches_event/Mixgamemates_affiche_Escape Game_1763059917.jpeg', NULL, NULL, NULL, NULL),
(38, '2025-11-14 13:00:58', 1, 1, 1, 1, 'Atelier peinture, karaoke et  Modeling Clay', '11', 'Calais', '2025-12-04', '15:30:00', '17:00:00', 'A 117', 72, 'Atelier Peinture :\r\nMontrer votre créativité en réalisant votre propre toile sur un thème révélé lors de l’événement.\r\n\r\nKaraoké :\r\nMonter sur scène et chanter vos chansons préférées dans une ambiance chaleureuse et pleine d’encouragements.\r\n\r\nModeling Clay :\r\nModeler, façonner et créer des objets uniques que vous pourrez emporter avec vous', 1, 150, NULL, '../uploads/affiches_event/Bureaudesarts_affiche_Atelier peinture, karaoke et  Modeling Clay_1763121658.jpg', '../uploads/rapports/Bureau des arts_Atelier peinture, karaoke et  Modeling Clay_1766128179.pdf', NULL, 1, NULL),
(39, '2025-11-15 17:02:07', NULL, 1, NULL, 0, 'Spread The Light', '19', 'Calais', '2025-11-30', '15:00:00', '17:00:00', 'L’événement ne nécessite pas de salle. Les actions se déroulent exclusivement en extérieur', 128, '« Spread The Light » est un événement pensé pour créer un impact immédiat et tangible sur l’environnement humain du campus et au-delà. Cet atelier-défi transforme chaque participant en créateur d’émotions positives : les étudiants rédigent des citations inspirantes, préparent des fleurs, puis partent les offrir à des personnes extérieures: passants, clients de supermarché, personnels rencontrés afin de déclencher un moment de lumière dans une journée ordinaire.\r\n\r\nPlus qu’un geste symbolique, l’action vise à démontrer la puissance concrète des micro-interventions positives : un message bien choisi peut réorienter l’humeur d’une personne, un geste inattendu peut créer un souvenir durable, et une énergie collective peut influencer la perception d’un lieu tout entier.\r\n\r\nCet événement met en avant la mission centrale d’AURA’EILCO : générer de l’impact humain, développer des initiatives qui transforment les interactions du quotidien, et faire de chaque étudiant un acteur de changement par sa créativité, sa présence et sa capacité à inspirer.\r\nEn participant, les étudiants vivent une expérience immersive où leurs mots deviennent des leviers de transformation instantanée, et où de simples actions déclenchent des réactions authentiques et puissantes.', 1, 100, NULL, '../uploads/affiches_event/AURAEILCO_affiche_Spread The Light_1763222527.png', NULL, 'La date renseignée sur la fiche événement ne correspond pas à la date réelle de l&#039;événement.', 0, NULL),
(40, '2025-11-15 18:12:20', NULL, NULL, 1, 0, 'Quiz Culturel ', '17', 'Longuenesse', '2025-12-04', '13:30:00', '16:00:00', 'Médiathèque pour le quiz et foyer pour le buffet', 137, 'L’événement consiste en un quiz culturel interactif organisé par le club EL Colors. Les participants seront répartis en équipes et répondront à des questions autour de différentes thématiques culturelles (musique, cinéma, pays, art, culture générale…). L’objectif est de promouvoir l’échange, la diversité culturelle et la cohésion entre les étudiants.\r\n\r\nLe quiz sera projeté sur un écran interactif, qui permettra d’afficher les questions, les scores et l’avancement du jeu de manière dynamique.\r\n\r\nÀ la fin du quiz, une équipe gagnante sera annoncée et recevra un prix symbolique en lien avec les cultures du monde.\r\n\r\nNous prévoyons également une petite distribution gratuite de nourriture sous forme d’un buffet simple (une variété de plats multiculturels). Aucun produit sensible n’est prévu. Vous trouverez ci-joint une fiche sanitaire.\r\nAucune diffusion de film n’est prévue dans le cadre de cet événement.', 1, 50, NULL, NULL, NULL, NULL, NULL, NULL),
(41, '2025-11-15 18:12:58', NULL, NULL, NULL, 0, 'Quiz Culturel ', '17', 'Longuenesse', '2025-12-04', '13:30:00', '16:00:00', 'Médiathèque pour le quiz et foyer pour le buffet', 137, 'L’événement consiste en un quiz culturel interactif organisé par le club EL Colors. Les participants seront répartis en équipes et répondront à des questions autour de différentes thématiques culturelles (musique, cinéma, pays, art, culture générale…). L’objectif est de promouvoir l’échange, la diversité culturelle et la cohésion entre les étudiants.\r\n\r\nLe quiz sera projeté sur un écran interactif, qui permettra d’afficher les questions, les scores et l’avancement du jeu de manière dynamique.\r\n\r\nÀ la fin du quiz, une équipe gagnante sera annoncée et recevra un prix symbolique en lien avec les cultures du monde.\r\n\r\nNous prévoyons également une petite distribution gratuite de nourriture sous forme d’un buffet simple (une variété de plats multiculturels). Aucun produit sensible n’est prévu. Vous trouverez ci-joint une fiche sanitaire.\r\nAucune diffusion de film n’est prévue dans le cadre de cet événement.', 1, 50, '../uploads/fiches_sanitaires/EILColors_fiche_sanitaire_Quiz Culturel _1763226778.pdf', NULL, NULL, NULL, NULL, NULL),
(42, '2025-11-15 18:14:54', NULL, NULL, NULL, 0, 'Quiz Culturel ', '17', 'Longuenesse', '2025-12-04', '13:30:00', '16:00:00', 'Médiathèque pour le quiz et foyer pour le buffet', 137, 'L’événement consiste en un quiz culturel interactif organisé par le club EL Colors. Les participants seront répartis en équipes et répondront à des questions autour de différentes thématiques culturelles (musique, cinéma, pays, art, culture générale…). L’objectif est de promouvoir l’échange, la diversité culturelle et la cohésion entre les étudiants.\r\n\r\nLe quiz sera projeté sur un écran interactif, qui permettra d’afficher les questions, les scores et l’avancement du jeu de manière dynamique.\r\n\r\nÀ la fin du quiz, une équipe gagnante sera annoncée et recevra un prix symbolique en lien avec les cultures du monde.\r\n\r\nNous prévoyons également une petite distribution gratuite de nourriture sous forme d’un buffet simple (une variété de plats multiculturels). Aucun produit sensible n’est prévu. Vous trouverez ci-joint une fiche sanitaire.\r\n\r\nAucune diffusion de film n’est prévue dans le cadre de cet événement.', 1, 50, '../uploads/fiches_sanitaires/EILColors_fiche_sanitaire_Quiz Culturel _1763226894.pdf', NULL, NULL, NULL, NULL, NULL),
(43, '2025-11-15 18:15:50', NULL, NULL, NULL, 0, 'Quiz Culturel ', '17', 'Longuenesse', '2025-12-04', '13:30:00', '16:00:00', 'Médiathèque pour le quiz et foyer pour le buffet', 137, 'L’événement consiste en un quiz culturel interactif organisé par le club EL Colors. Les participants seront répartis en équipes et répondront à des questions autour de différentes thématiques culturelles (musique, cinéma, pays, art, culture générale…). L’objectif est de promouvoir l’échange, la diversité culturelle et la cohésion entre les étudiants.\r\n\r\nLe quiz sera projeté sur un écran interactif, qui permettra d’afficher les questions, les scores et l’avancement du jeu de manière dynamique.\r\n\r\nÀ la fin du quiz, une équipe gagnante sera annoncée et recevra un prix symbolique en lien avec les cultures du monde.\r\n\r\nNous prévoyons également une petite distribution gratuite de nourriture sous forme d’un buffet simple (une variété de plats multiculturels). Aucun produit sensible n’est prévu. Vous trouverez ci-joint une fiche sanitaire.\r\n\r\nAucune diffusion de film n’est prévue dans le cadre de cet événement.', 0, 50, '../uploads/fiches_sanitaires/EILColors_fiche_sanitaire_Quiz Culturel _1763226950.pdf', NULL, NULL, NULL, NULL, NULL),
(44, '2025-11-15 18:22:17', NULL, NULL, NULL, 0, 'Quizz Culturel', '17', 'Longuenesse', '2025-12-04', '13:30:00', '16:00:00', 'Médiathèque pour quizz/Foyer pour buffet', 137, 'L’événement consiste en un quiz culturel interactif organisé par le club EL Colors. Les participants seront répartis en équipes et répondront à des questions autour de différentes thématiques culturelles (musique, cinéma, pays, art, culture générale…). L’objectif est de promouvoir l’échange, la diversité culturelle et la cohésion entre les étudiants.\r\n\r\nLe quiz sera projeté sur un écran interactif, qui permettra d’afficher les questions, les scores et l’avancement du jeu de manière dynamique.\r\n\r\nÀ la fin du quiz, une équipe gagnante sera annoncée et recevra un prix symbolique en lien avec les cultures du monde.\r\n\r\nNous prévoyons également une petite distribution gratuite de nourriture sous forme d’un buffet simple (une variété de plats multiculturels). Aucun produit sensible n’est prévu. Vous trouverez ci-joint une fiche sanitaire.\r\n\r\nAucune diffusion de film n’est prévue dans le cadre de cet événement.', 1, 50, NULL, NULL, NULL, NULL, NULL, NULL),
(45, '2025-11-15 19:41:09', NULL, NULL, NULL, 0, 'Course d’orientation ', '37', 'Longuenesse', '2025-11-30', '14:00:00', '16:30:00', 'Jardin public de Saint-Omer', 29, 'Mise en place d’un jeu de piste pour les étudiants afin de découvrir le jardin public de Saint-Omer et de créer de la cohésion sociale au sein de l’école ', 1, 80, NULL, '../uploads/affiches_event/BureaudessportsLonguenesse_affiche_Course d’orientation _1763232069.jpeg', NULL, NULL, NULL, NULL),
(46, '2025-11-17 12:22:26', NULL, NULL, 1, 0, 'Séance de yoga &amp; moment bien-être ', '28', 'Longuenesse', '2025-12-05', '10:00:00', '12:00:00', 'Maison Wellness- 32 rue François chifflart, Saint-Omer ', 161, 'Séance de yoga\r\n\r\nLa rencontre débutera par une séance de yoga guidée, destinée à permettre aux participants de se recentrer à travers des exercices de respiration, des postures accessibles et une phase de relaxation.\r\n\r\nMini-pause gourmande\r\n\r\nUne courte pause gourmande sera ensuite proposée, comprenant :\r\nfruits, biscuits, jus..\r\nCe temps de partage favorisera la convivialité et les échanges.\r\n\r\nActivité anti-stress\r\n\r\nUne activité légère, sous forme de jeu de compliments, sera animée afin de renforcer l’estime de soi, la bienveillance et la dynamique positive au sein du groupe.\r\n\r\nClôture\r\n\r\nL’événement se clôturera par un bref échange collectif permettant aux participants de partager leurs ressentis et d’exprimer leurs attentes pour les prochaines activités.', 1, 40, NULL, NULL, NULL, NULL, NULL, NULL),
(47, '2025-11-17 12:22:55', NULL, NULL, 1, 0, 'Séance de yoga &amp; moment bien-être ', '28', 'Longuenesse', '2025-12-06', '10:00:00', '12:00:00', 'Maison Wellness- 32 rue François chifflart, Saint-Omer ', 161, 'Séance de yoga\r\n\r\nLa rencontre débutera par une séance de yoga guidée, destinée à permettre aux participants de se recentrer à travers des exercices de respiration, des postures accessibles et une phase de relaxation.\r\n\r\nMini-pause gourmande\r\n\r\nUne courte pause gourmande sera ensuite proposée, comprenant :\r\nfruits, biscuits, jus..\r\nCe temps de partage favorisera la convivialité et les échanges.\r\n\r\nActivité anti-stress\r\n\r\nUne activité légère, sous forme de jeu de compliments, sera animée afin de renforcer l’estime de soi, la bienveillance et la dynamique positive au sein du groupe.\r\n\r\nClôture\r\n\r\nL’événement se clôturera par un bref échange collectif permettant aux participants de partager leurs ressentis et d’exprimer leurs attentes pour les prochaines activités.', 1, 40, NULL, NULL, NULL, NULL, NULL, NULL),
(48, '2025-11-17 12:23:23', NULL, NULL, 1, 0, 'Séance de yoga &amp; moment bien-être ', '28', 'Longuenesse', '2025-12-05', '10:00:00', '12:00:00', 'Maison Wellness- 32 rue François chifflart, Saint-Omer ', 161, 'Séance de yoga\r\n\r\nLa rencontre débutera par une séance de yoga guidée, destinée à permettre aux participants de se recentrer à travers des exercices de respiration, des postures accessibles et une phase de relaxation.\r\n\r\nMini-pause gourmande\r\n\r\nUne courte pause gourmande sera ensuite proposée, comprenant :\r\nfruits, biscuits, jus..\r\nCe temps de partage favorisera la convivialité et les échanges.\r\n\r\nActivité anti-stress\r\n\r\nUne activité légère, sous forme de jeu de compliments, sera animée afin de renforcer l’estime de soi, la bienveillance et la dynamique positive au sein du groupe.\r\n\r\nClôture\r\n\r\nL’événement se clôturera par un bref échange collectif permettant aux participants de partager leurs ressentis et d’exprimer leurs attentes pour les prochaines activités.', 1, 40, NULL, NULL, NULL, NULL, NULL, NULL),
(49, '2025-11-17 12:24:55', NULL, NULL, 1, 0, 'Séance de yoga &amp;amp; moment bien-être ', '28', 'Longuenesse', '2025-12-05', '10:00:00', '12:00:00', 'Maison Wellness- 32 rue François chifflart, Saint-Omer ', 161, 'Séance de yoga\r\n\r\nLa rencontre débutera par une séance de yoga guidée, destinée à permettre aux participants de se recentrer à travers des exercices de respiration, des postures accessibles et une phase de relaxation.\r\n\r\nMini-pause gourmande\r\n\r\nUne courte pause gourmande sera ensuite proposée, comprenant :\r\nfruits, biscuits, jus..\r\nCe temps de partage favorisera la convivialité et les échanges.\r\n\r\nActivité anti-stress\r\n\r\nUne activité légère, sous forme de jeu de compliments, sera animée afin de renforcer l’estime de soi, la bienveillance et la dynamique positive au sein du groupe.\r\n\r\nClôture\r\n\r\nL’événement se clôturera par un bref échange collectif permettant aux participants de partager leurs ressentis et d’exprimer leurs attentes pour les prochaines activités.', 1, 40, NULL, NULL, NULL, NULL, NULL, NULL),
(50, '2025-11-17 12:27:00', NULL, NULL, 1, 0, 'Séance de yoga &amp;amp; moment bien-être ', '28', 'Longuenesse', '2025-12-05', '10:00:00', '12:00:00', 'Maison Wellness- 32 rue François chifflart, Saint-Omer ', 161, 'Séance de yoga\r\n\r\nLa rencontre débutera par une séance de yoga guidée, destinée à permettre aux participants de se recentrer à travers des exercices de respiration, des postures accessibles et une phase de relaxation.\r\n\r\nMini-pause gourmande\r\n\r\nUne courte pause gourmande sera ensuite proposée, comprenant :\r\nfruits, biscuits, jus..\r\nCe temps de partage favorisera la convivialité et les échanges.\r\n\r\nActivité anti-stress\r\n\r\nUne activité légère, sous forme de jeu de compliments, sera animée afin de renforcer l’estime de soi, la bienveillance et la dynamique positive au sein du groupe.\r\n\r\nClôture\r\n\r\nL’événement se clôturera par un bref échange collectif permettant aux participants de partager leurs ressentis et d’exprimer leurs attentes pour les prochaines activités.', 1, 40, NULL, NULL, NULL, NULL, NULL, NULL),
(51, '2025-11-17 22:27:59', 1, 1, 1, 1, 'Noël Créatif : Eiltech x Eilco&#039;taku', '8', 'Calais', '2025-12-04', '13:30:00', '17:00:00', 'Salle EilTech', 81, 'L&#039;objectif de cet atelier est de proposer un moment créatif pour produire des décorations de Noël inspirées de la culture japonaise, en combinant modélisation 3D et travaux manuels.\r\n\r\n- avec la modélisation d&#039;objets japonais réalisée avec l&#039;accompagnement du club Eiltech, et la possibilité de les imprimer en 3D moyennant une petite participation,\r\n- ou avec la création de décorations DIY proposées par le club Eilco&#039;taku, permettant de découvrir différentes techniques artisanales inspirées du Japon.', 0, 0, NULL, '../uploads/affiches_event/EILTech_affiche_Noël Créatif : Eiltech x Eilco&#039;taku_1763414879.png', NULL, NULL, 1, NULL),
(52, '2025-11-17 22:28:03', 1, 1, 1, 1, 'Noël Créatif : Eiltech x Eilco&#039;taku', '8', 'Calais', '2025-12-04', '13:30:00', '17:00:00', 'Salle EilTech', 81, 'L&#039;objectif de cet atelier est de proposer un moment créatif pour produire des décorations de Noël inspirées de la culture japonaise, en combinant modélisation 3D et travaux manuels.\r\n\r\n- avec la modélisation d&#039;objets japonais réalisée avec l&#039;accompagnement du club Eiltech, et la possibilité de les imprimer en 3D moyennant une petite participation,\r\n- ou avec la création de décorations DIY proposées par le club Eilco&#039;taku, permettant de découvrir différentes techniques artisanales inspirées du Japon.', 0, 0, NULL, '../uploads/affiches_event/EILTech_affiche_Noël Créatif : Eiltech x Eilco&#039;taku_1763414883.png', NULL, NULL, 1, NULL),
(53, '2025-11-18 18:29:51', 1, 1, 1, 1, 'Noël Créatif', '12', 'Calais', '2025-12-04', '14:00:00', '18:00:00', 'Salle Eiltech', 42, 'Ceci est un événement en collaboration avec Eiltech !\r\nL&#039;objectif de cet atelier est de proposer un moment créatif pour produire des décorations de Noël inspirées de la culture japonaise, en combinant modélisation 3D et travaux manuels.\r\n\r\n• avec la modélisation d&#039;objets japonais réalisée avec l&#039;accompagnement du club Eiltech, et la possibilité de les imprimer en 3D moyennant une petite participation,\r\n• ou avec la création de décorations DIY proposées par le club Eilco&#039;taku, permettant de découvrir différentes techniques artisanales inspirées du Japon.', 0, 0, NULL, '../uploads/affiches_event/EILCO039TAKU_affiche_Noël Créatif_1763486991.png', NULL, NULL, 1, NULL),
(54, '2025-11-19 20:03:25', NULL, NULL, NULL, 0, 'test', '16', 'Longuenesse', '2025-12-19', '10:00:00', '11:00:00', 'BDE', 131, 'test', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(55, '2025-11-19 20:03:31', NULL, NULL, NULL, 0, 'test', '16', 'Longuenesse', '2025-12-19', '10:00:00', '11:00:00', 'BDE', 131, 'test', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(56, '2025-11-19 20:04:00', NULL, NULL, NULL, 0, 'test', '16', 'Longuenesse', '2025-12-19', '10:00:00', '11:00:00', 'BDE', 131, 'test', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(57, '2025-11-19 20:04:51', NULL, NULL, NULL, 0, 'test', '16', 'Longuenesse', '2025-12-19', '10:00:00', '11:00:00', 'BDE', 131, 'test', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(58, '2025-11-19 20:06:46', NULL, NULL, NULL, 0, 'test', '16', 'Longuenesse', '2025-12-18', '14:00:00', '17:00:00', 'BDE', 131, 'test', 0, 0, NULL, '../uploads/affiches_event/BDESaintOmer_affiche_test_1763579206.jpg', NULL, NULL, NULL, NULL),
(59, '2025-11-19 22:32:36', NULL, NULL, NULL, 0, 'Course d’orientation EILCO', '37', 'Longuenesse', '2025-12-04', '14:00:00', '16:30:00', 'Jardin public de Saint-Omer', 29, 'Découverte ludique du parc de Saint Omer via du jeu d’orientation en équipe ', 1, 70, NULL, NULL, NULL, NULL, NULL, NULL),
(60, '2025-11-19 22:32:41', NULL, NULL, 1, 0, 'Course d’orientation EILCO', '37', 'Longuenesse', '2025-12-04', '14:00:00', '16:30:00', 'Jardin public de Saint-Omer', 29, 'Découverte ludique du parc de Saint Omer via du jeu d’orientation en équipe ', 1, 70, NULL, NULL, NULL, NULL, NULL, NULL),
(61, '2025-11-20 12:40:23', 1, 1, 1, 1, 'Play &amp; Connect', '57', 'Dunkerque', '2025-12-11', '13:30:00', '16:30:00', '3.04', 219, 'Cet événement a pour objectif de rassembler les étudiants autour d’activités ludiques favorisant la cohésion, la communication et l’esprit d’équipe. Au programme : une série de jeux collectifs, défis coopératifs et mini-compétitions amicales permettant aux participants de se connaître, de collaborer et de partager un moment convivial.\r\nL’événement est ouvert à tous, sans prérequis, et se déroule dans une ambiance détendue et dynamique. L’accent est mis sur la participation, l’entraide et le plaisir de jouer ensemble.', 0, 0, NULL, NULL, '../uploads/rapports/MoZaiKSpot_Play &amp; Connect_1766423214.pdf', NULL, 1, NULL),
(62, '2025-11-20 21:49:42', 1, 1, 1, 1, 'EnjoyUp On Ice', '21', 'Calais', '2025-12-19', '18:30:00', '20:30:00', 'patinoire Calais place d&#039;arme ', 162, 'L’événement consiste en une sortie patinage organisée par le club EnjoyUp. Les participants profiteront d’une séance sur glace suivie d’un moment convivial autour de jeux de société. Une petite animation sera proposée à l’aide d’un micro, et du chocolat chaud ainsi qu’un cake seront distribués. Des bougies décoratives seront également installées pour créer une ambiance chaleureuse.\r\nAchat de nourriture : chocolat chaud et cake (fiche sanitaire disponible si nécessaire).', 1, 100, '../uploads/fiches_sanitaires/EnjoyUp_fiche_sanitaire_EnjoyUp On Ice_1763671782.pdf', '../uploads/affiches_event/EnjoyUp_affiche_EnjoyUp On Ice_1763671782.jpg', NULL, NULL, 1, NULL),
(63, '2025-11-20 21:52:04', NULL, NULL, 1, 0, 'EnjoyUp On Ice', '21', 'Calais', '2025-12-19', '18:30:00', '20:30:00', 'patinoire Calais place d&#039;arme ', 162, 'L’événement consiste en une sortie patinage organisée par le club EnjoyUp. Les participants profiteront d’une séance sur glace suivie d’un moment convivial autour de jeux de société. Une petite animation sera proposée à l’aide d’un micro, et du chocolat chaud ainsi qu’un cake seront distribués. Des bougies décoratives seront également installées pour créer une ambiance chaleureuse.\r\nAchat de nourriture : chocolat chaud et cake (fiche sanitaire disponible si nécessaire).\r\n', 1, 100, '../uploads/fiches_sanitaires/EnjoyUp_fiche_sanitaire_EnjoyUp On Ice_1763671924.pdf', '../uploads/affiches_event/EnjoyUp_affiche_EnjoyUp On Ice_1763671924.jpg', NULL, 'Fiche envoyée en double.', 0, NULL),
(64, '2025-11-20 21:53:09', NULL, NULL, 1, 0, 'EnjoyUp On Ice', '21', 'Calais', '2025-12-19', '18:30:00', '20:30:00', 'patinoire Calais place d&#039;arme ', 162, 'L’événement consiste en une sortie patinage organisée par le club EnjoyUp. Les participants profiteront d’une séance sur glace suivie d’un moment convivial autour de jeux de société. Une petite animation sera proposée à l’aide d’un micro, et du chocolat chaud ainsi qu’un cake seront distribués. Des bougies décoratives seront également installées pour créer une ambiance chaleureuse.\r\nAchat de nourriture : chocolat chaud et cake (fiche sanitaire disponible si nécessaire).\r\n', 1, 100, '../uploads/fiches_sanitaires/EnjoyUp_fiche_sanitaire_EnjoyUp On Ice_1763671989.pdf', '../uploads/affiches_event/EnjoyUp_affiche_EnjoyUp On Ice_1763671989.jpg', NULL, 'Fiche envoyée en double.', 0, NULL),
(65, '2025-11-21 11:49:15', NULL, NULL, 1, 0, 'EnjoyUp On Ice', '21', 'Calais', '2025-12-19', '18:30:00', '20:30:00', 'patinoire Calais place d&#039;arme ', 162, 'L’événement consiste en une sortie patinage organisée par le club EnjoyUp. Les participants profiteront d’une séance sur glace suivie d’un moment convivial autour de jeux de société. Une petite animation sera proposée à l’aide d’un micro, et du chocolat chaud ainsi qu’un cake seront distribués. Des bougies décoratives seront également installées pour créer une ambiance chaleureuse.\r\nAchat de nourriture : chocolat chaud et cake (fiche sanitaire disponible si nécessaire).', 1, 100, '../uploads/fiches_sanitaires/EnjoyUp_fiche_sanitaire_EnjoyUp On Ice_1763722155.pdf', '../uploads/affiches_event/EnjoyUp_affiche_EnjoyUp On Ice_1763722155.jpg', NULL, 'Fiche envoyée en double.', 0, NULL),
(66, '2025-11-21 12:59:32', 1, 1, 1, 1, 'Concert Carte blanche au conservatoire ', '23', 'Calais', '2025-12-13', '14:00:00', '16:30:00', 'Cité de la Dentelle, 135 Quai du Commerce, 62100 Calais', 126, '🎶 INSCRIPTIONS OUVERTES — Voyage Sonore avec EILZIK à la Cité de la Dentelle !\r\n\r\nPour le dernier mois de l’exposition “Yiqing Yin. D’air et de songes”, les élèves et professeurs du Conservatoire du Calaisis vous invitent à vivre un moment musical unique, entre tradition et modernité.\r\nL’événement est gratuit, mais l’inscription est obligatoire.\r\n\r\n📅 Samedi 14 décembre\r\n📍 Cité de la Dentelle et de la Mode – Calais\r\n\r\n🎹 14h00 — Pianorama\r\n🎼 15h30 — Duo clarinettes et piano + projection vidéo\r\n\r\n👉 Inscription ici :\r\nhttps://docs.google.com/forms/d/e/1FAIpQLScyClllnaiX0AaLgN1loMZPsHV1AlWnkNKaHeCdIleOh7JoBg/viewform?usp=header\r\n\r\nRejoignez-nous pour un voyage sonore inspiré de l’univers poétique de Yiqing Yin.', 0, 0, NULL, '../uploads/affiches_event/EilZik_affiche_Concert Carte blanche au conservatoire _1763726372.png', '../uploads/rapports/EilZik_Concert Carte blanche au conservatoire _1766534495.pdf', NULL, 1, NULL),
(67, '2025-11-22 16:56:57', NULL, NULL, 1, 0, 'Ciné-Noël', '29', 'Longuenesse', '2025-12-18', '14:00:00', '17:00:00', 'Foyer', 92, 'Projection cinématographique « Ciné-Noël »\r\nLe club CinéEILCO organise une projection de film sur le thème de Noël au Foyer afin de renforcer la cohésion étudiante avant les fêtes. L&#039;événement vise à créer une ambiance conviviale et chaleureuse, accompagnée d&#039;une distribution de popcorn. Le club s&#039;engage à assurer le nettoyage complet des lieux après la séance.', 1, 30, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `fiche_event` (`event_id`, `date_depot`, `validation_admin`, `validation_bde`, `validation_tuteur`, `validation_soutenance`, `titre`, `club_orga`, `campus`, `date_ev`, `horaire_debut`, `horaire_fin`, `lieu`, `id_responsable`, `description`, `financement_bde`, `montant`, `fiche_sanitaire`, `affiche`, `rapport_event`, `motif_refus`, `validation_finale`, `commentaire_validation`) VALUES
(68, '2025-11-22 16:57:09', NULL, NULL, 1, 0, 'Ciné-Noël', '29', 'Longuenesse', '2025-12-18', '14:00:00', '17:00:00', 'Foyer', 92, 'Projection cinématographique « Ciné-Noël »\r\nLe club CinéEILCO organise une projection de film sur le thème de Noël au Foyer afin de renforcer la cohésion étudiante avant les fêtes. L&#039;événement vise à créer une ambiance conviviale et chaleureuse, accompagnée d&#039;une distribution de popcorn. Le club s&#039;engage à assurer le nettoyage complet des lieux après la séance.', 1, 30, NULL, NULL, NULL, NULL, NULL, NULL),
(69, '2025-11-23 11:24:47', NULL, NULL, NULL, 0, 'Spread The Light', '19', 'Calais', '2025-12-08', '15:00:00', '17:00:00', 'salle équipée de tables déplaçables ', 128, '***Un atelier créatif et chaleureux où chaque participant donne vie à un petit objet en argile autodurcissante, puis le personnalise avec des peintures acryliques selon son style et son imagination.\r\nL’événement commence par un geste positif : chacun écrit une citation inspirante dans une mini-lettre et l’accroche au “Mur des Citations”.\r\nÀ la fin, un tirage au hasard permet d’échanger les créations : chaque personne repart avec un objet fait main et un message motivant préparés spécialement pour elle.\r\nUn moment apaisant, artistique et rempli de belles énergies, pensé pour créer du lien, partager de la joie et offrir une expérience qui reste en mémoire.*** \r\n\r\nNous souhaitons maintenir la date du 04 décembre 2025 pour cet événement.\r\nUne première demande avait déjà été déposée il y a plus de deux semaines pour notre événement “Spread the Light”. Cependant, en raison des contraintes liées à la distinction entre activité et événement, nous avons dû changer la nature de la fiche et reformuler une nouvelle demande.\r\n\r\nCette reclassification a réinitialisé automatiquement le délai du site, ce qui génère un écart inférieur aux 14 jours exigés. De plus, l’événement initialement prévu en novembre ne pouvait plus être validé à temps ; j’ai donc essayé de le décaler en décembre pour respecter les délais, mais cela reste compliqué avec la contrainte technique du site.\r\n\r\nLe système ne me permettant pas de sélectionner la date du 04 décembre en raison de cette contrainte, j’ai dû saisir une date ultérieure (le 08 décembre) uniquement pour que la demande puisse être envoyée.\r\nLa date réellement prévue et souhaitée pour l’événement reste le 04 décembre 2025.\r\n\r\nNous sollicitons donc exceptionnellement l’autorisation de conserver cette date, qui correspond à l’organisation déjà prévue pour nos membres et nos participants.', 1, 96, NULL, '../uploads/affiches_event/AURAEILCO_affiche_Spread The Light_1763893487.png', '../uploads/rapports/AURA’EILCO_Spread The Light_1765445691.pdf', NULL, 0, NULL),
(70, '2025-11-23 18:32:36', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, NULL, 0, NULL),
(71, '2025-11-23 18:33:59', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(72, '2025-11-23 18:34:03', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(73, '2025-11-23 18:34:11', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(74, '2025-11-23 18:35:21', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(75, '2025-11-23 18:36:58', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(76, '2025-11-23 23:58:08', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(77, '2025-11-24 09:09:55', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'Eilco calais', 116, 'L’EILCook organise un événement culinaire consacré à la découverte de la gastronomie internationale.\r\nÀ cette occasion, plusieurs plats représentatifs de différentes cultures seront préparés et présentés aux participants.\r\nCet événement a pour objectif de promouvoir l’ouverture culturelle, de favoriser les échanges entre étudiants et de mettre en valeur la diversité des traditions culinaires.', 1, 100, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(78, '2025-11-25 12:05:45', 1, 1, 1, 0, 'Restaurant Chinois', '12', 'Calais', '2025-12-11', '12:15:00', '14:00:00', 'Restaurant Monsieur Wok (Coquelles)', 42, 'Nous vous proposons une sortie au restaurant chinois pour vous faire découvrir des spécialités culinaires asiatiques ! Nouilles, beignets de crevettes, sushis... un bon repas s&#039;annonce !\r\nLe transport sera assuré par l&#039;équipe d&#039;EILCOTAKU donc pas de soucis !', 0, 0, NULL, NULL, NULL, NULL, 1, NULL),
(79, '2025-11-25 16:15:49', NULL, NULL, 1, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 187, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764083749.png', NULL, NULL, NULL, NULL),
(80, '2025-11-25 16:17:14', NULL, NULL, NULL, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 187, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764083834.png', NULL, NULL, NULL, NULL),
(81, '2025-11-25 16:18:47', NULL, NULL, 1, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 187, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764083927.png', NULL, NULL, NULL, NULL),
(82, '2025-11-25 16:20:05', NULL, NULL, NULL, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 187, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764084005.png', NULL, NULL, NULL, NULL),
(83, '2025-11-25 16:27:08', NULL, NULL, 1, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 185, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764084428.png', NULL, NULL, NULL, NULL),
(84, '2025-11-25 16:34:44', NULL, NULL, 1, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 187, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764084883.png', NULL, NULL, NULL, NULL),
(85, '2025-11-25 16:35:55', NULL, NULL, NULL, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 182, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764084954.png', NULL, NULL, NULL, NULL),
(86, '2025-11-25 16:42:47', NULL, NULL, 1, 0, 'Plongée fantastique à Nausicaá', '32', 'Boulogne', '2025-12-14', '11:00:00', '14:00:00', 'Nausicaa Centre National De La Mer, bd Sainte Beuve, 62200 Boulogne sur Mer', 185, 'Le club Baladopale vous propose une sortie à Nausicaá pour découvrir ensemble le monde marin. Au programme : accueil du groupe, visite libre des différents espaces de l’aquarium, moments photo et temps convivial en fin de parcours. Une belle occasion d’explorer, d’apprendre et de partager une sortie agréable entre les étudiants.', 0, 0, NULL, '../uploads/affiches_event/Baladopale_affiche_Plongée fantastique à Nausicaá_1764085367.png', NULL, NULL, NULL, NULL),
(87, '2025-11-25 16:57:19', NULL, 1, NULL, 0, 'Vente de viennoiseries ', '4', 'Calais', '2025-12-16', '10:00:00', '15:30:00', 'Hall ', 25, 'Le but de cet événement est de permettre aux étudiants et au personnel de l&#039;école d&#039;acheter des viennoiseries. Cette vente générera des bénéfices qui permettront au BDE de pouvoir envisager des projets futurs.Cet événement aura lieu une fois par semaine (et ce à partir de la semaine du 24 novembre), en conservant le mercredi comme toutes les fois d&#039;avant.', 0, 0, '../uploads/fiches_sanitaires/BDECalais_fiche_sanitaire_Vente de viennoiseries _1764086239.pdf', '../uploads/affiches_event/BDECalais_affiche_Vente de viennoiseries _1764086239.png', NULL, 'Fiche envoyée de double. ', 0, NULL),
(88, '2025-11-25 16:57:26', NULL, 1, NULL, 0, 'Vente de viennoiseries ', '4', 'Calais', '2025-12-16', '10:00:00', '15:30:00', 'Hall ', 25, 'Le but de cet événement est de permettre aux étudiants et au personnel de l&#039;école d&#039;acheter des viennoiseries. Cette vente générera des bénéfices qui permettront au BDE de pouvoir envisager des projets futurs.Cet événement aura lieu une fois par semaine (et ce à partir de la semaine du 24 novembre), en conservant le mercredi comme toutes les fois d&#039;avant.', 0, 0, '../uploads/fiches_sanitaires/BDECalais_fiche_sanitaire_Vente de viennoiseries _1764086246.pdf', '../uploads/affiches_event/BDECalais_affiche_Vente de viennoiseries _1764086246.png', NULL, NULL, NULL, NULL),
(89, '2025-11-25 18:30:05', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'salle 117', 116, 'Dans le cadre de la valorisation de la diversité culturelle au sein de notre établissement, le club EILCook organise un événement culinaire intitulé « Découverte culinaire Internationale », qui se tiendra le 19 décembre 2025 dans la salle 117.\r\n\r\nCet événement vise à faire découvrir aux étudiants différentes traditions gastronomiques à travers la préparation et la dégustation de divers plats internationaux. Chaque préparation sera accompagnée d’une ambiance musicale propre à son pays d’origine, afin de proposer une expérience immersive à la fois culinaire et culturelle.\r\n\r\nPlusieurs stands seront organisés, chacun consacré à un plat spécifique :\r\n\r\n\r\n 1. Briwats salés (Maroc)\r\n\r\nDescription :\r\nPetites pâtisseries salées en forme de triangles, farcies traditionnellement d’un mélange de viande, d’oignons et d’épices marocaines, puis frites ou cuites au four. Ce mets est emblématique des tables marocaines lors des fêtes et grandes occasions.\r\n\r\nIngrédients :\r\n\r\nFeuilles de brick\r\n\r\nViande hachée (ou poulet haché selon la variante)\r\n\r\nOignons\r\n\r\nPersil et coriandre frais\r\n\r\nAil\r\n\r\nÉpices marocaines (ras el hanout, paprika, cumin)\r\n\r\nSel, poivre\r\n\r\nFromage type kiri (optionnel, selon les recettes modernes)\r\n\r\nŒuf pour lier\r\n\r\nHuile pour friture ou cuisson\r\n\r\n\r\nAmbiance musicale :\r\nSélection de musiques marocaines instrumentales et andalouses pour accompagner l’expérience.\r\n\r\n\r\n 2. Taboulé (Liban)\r\n\r\nDescription : Salade fraîche libanaise à base de persil, boulgour et herbes aromatiques.\r\nIngrédients : Persil, boulgour fin, tomates, menthe, citron, huile d’olive, oignons, sel, poivre.\r\nMusique : Ambiance libanaise instrumentale.\r\n\r\n\r\n\r\n 3. Pancakes (États-Unis)\r\n\r\nDescription : Pâtisseries moelleuses servies avec sirop, fruits ou chocolat.\r\nIngrédients : Farine, lait, œufs, sucre, levure chimique, beurre fondu, vanille.\r\nMusique : Jazz et soul américains.\r\n\r\n\r\n\r\n 4. Muffins (France)\r\n\r\nDescription : Gâteaux individuels déclinés en chocolat, fruits rouges ou vanille.\r\nIngrédients : Farine, sucre, œufs, beurre ou huile, levure chimique, lait, chocolat/fruits.\r\nMusique : Musique française douce.\r\n\r\n\r\n\r\n\r\n5. Jus variés\r\n\r\nDescription : Jus de fruits frais pressés ou mixés.\r\nIngrédients : Oranges, pommes, fraises, bananes, citron, eau.\r\nMusique : Playlist internationale.\r\n\r\n\r\n\r\nObjectifs de l’événement :\r\n\r\nMettre en valeur la diversité culturelle du campus\r\nFavoriser les échanges entre étudiants\r\nDécouvrir des traditions culinaires variées\r\nRenforcer la cohésion au sein de l’école', 0, 100, NULL, NULL, NULL, 'Absence du protocole sanitaire.', 0, NULL),
(90, '2025-11-26 22:39:38', NULL, NULL, NULL, 0, 'Retour à baby Aura', '19', 'Calais', '2025-12-11', '15:00:00', '17:00:00', 'Eilco ', 128, '1. Le Concept \r\n&quot;Retour à Baby Aura&quot; est une bulle temporelle. Le temps d&#039;une demi-journée, les étudiants de l&#039;EILCO laissent leurs costumes de futurs ingénieurs au vestiaire pour retrouver l&#039;insouciance de leurs 5 ans. C&#039;est un événement &quot;Feel Good&quot; axé sur le partage d&#039;anecdotes, la découverte des cultures de chacun à travers leurs souvenirs d&#039;enfance, et la déconnexion totale.\r\n2. Les Objectifs du Club (Impact &amp; Cohésion) \r\nSolidarité (Lien Social) : Briser la glace et créer des liens profonds en partageant des souvenirs intimes et drôles.\r\n\r\nCulture &amp; International : Découvrir que les dessins animés ou les comptines sont différents (ou pareils !) au Maroc, en France, au Sénégal, en Chine, etc.\r\n\r\nBien-être : Offrir une pause mentale amusante face au stress des études.\r\nLe Cercle des Souvenirs (Storytelling) \r\n\r\nOn s&#039;assoit en cercle sur des coussins.\r\n\r\nChacun tire un papier avec un thème : &quot;Ma plus grosse bêtise&quot;, &quot;Mon jouet préféré&quot;, &quot;Ce que je voulais faire comme métier&quot;.\r\n\r\nOn partage les histoires. C&#039;est le moment &quot;émotion et rire&quot;.\r\nQuiz &amp; Karaoké &quot;Génération Minikeums/Disney&quot; \r\n\r\nBlind Test : Deviner les génériques de dessins animés (Pokemon, Code Lyoko, Bob l&#039;éponge...).\r\n\r\nInternational : Les étudiants étrangers font écouter un générique culte de leur pays.\r\n\r\nChant : On chante tous en chœur les classiques Disney ou les tubes des années 2000.\r\nProjection &quot;Ciné-Câlin&quot; \r\n\r\nOn projette 2 ou 3 épisodes de dessins animés cultes choisis par vote, juste pour le plaisir de regarder ça ensemble.\r\nLe &quot;Buffet des Babies&quot; (Menu Régressif) \r\nL&#039;idée est de proposer de la nourriture &quot;doudou&quot; qui rappelle l&#039;enfance.\r\n\r\nLe Bar à Céréales : Bols de lait (vache/végétal) avec Chocapic, Miel Pops, Froot Loops.\r\n\r\nLa &quot;Cerelac&quot; Station : Pour la touche internationale (très populaire au Maghreb/Afrique), proposer de la bouillie lactée ou des biscuits trempés.\r\n\r\nSnacks : Tartines de Nutella, Madeleines, Kinder Surprise, Compotes à boire (Pom&#039;Potes), Biscuits BN ou Prince.\r\n\r\nBoissons : Chocolat chaud avec guimauves, Sirop de grenadine, Jus de pomme.\r\n\r\n', 1, 40, NULL, NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(91, '2025-11-26 22:44:07', NULL, NULL, 1, 0, 'Padel 12/12/2025', '37', 'Longuenesse', '2025-12-12', '17:30:00', '19:00:00', '54 Allée des Sports, 62500, Saint Omer', 29, 'Soirée d’initiation au Padel en collaboration avec le club de squash &amp; tennis de Saint Omer ', 1, 80, NULL, NULL, NULL, NULL, NULL, NULL),
(92, '2025-11-26 22:44:11', NULL, NULL, NULL, 0, 'Padel 12/12/2025', '37', 'Longuenesse', '2025-12-12', '17:30:00', '19:00:00', '54 Allée des Sports, 62500, Saint Omer', 29, 'Soirée d’initiation au Padel en collaboration avec le club de squash &amp; tennis de Saint Omer ', 1, 80, NULL, NULL, NULL, NULL, NULL, NULL),
(93, '2025-11-26 22:52:57', NULL, NULL, 1, 0, 'Le mouvement du bien-être', '33', 'Dunkerque', '2025-12-21', '09:30:00', '11:45:00', 'Plage Malo les Bains', 202, 'Une petite course à pied :\r\nPoint de départ : Mémorial des Alliés\r\nPoint d&#039;arrivée : Poste de Secours Malo Terminus', 1, 30, NULL, '../uploads/affiches_event/EILMOTION_affiche_Le mouvement du bien-être_1764193977.png', NULL, 'Mauvaise date sur l&#039;affiche', 0, NULL),
(94, '2025-11-26 22:53:12', NULL, NULL, 1, 0, 'Le mouvement du bien-être', '33', 'Dunkerque', '2025-12-21', '09:30:00', '11:45:00', 'Plage Malo les Bains', 202, 'Une petite course à pied :\r\nPoint de départ : Mémorial des Alliés\r\nPoint d&#039;arrivée : Poste de Secours Malo Terminus', 1, 30, NULL, '../uploads/affiches_event/EILMOTION_affiche_Le mouvement du bien-être_1764193992.png', NULL, 'Mauvaise date sur l&#039;affiche', 0, NULL),
(95, '2025-11-26 22:53:16', NULL, NULL, 1, 0, 'Le mouvement du bien-être', '33', 'Dunkerque', '2025-12-21', '09:30:00', '11:45:00', 'Plage Malo les Bains', 202, 'Une petite course à pied :\r\nPoint de départ : Mémorial des Alliés\r\nPoint d&#039;arrivée : Poste de Secours Malo Terminus', 1, 30, NULL, '../uploads/affiches_event/EILMOTION_affiche_Le mouvement du bien-être_1764193996.png', NULL, 'Mauvaise date sur l&#039;affiche', 0, NULL),
(96, '2025-11-26 22:55:50', NULL, NULL, 1, 0, 'Le mouvement du bien-être', '33', 'Dunkerque', '2025-12-21', '09:30:00', '11:45:00', 'Plage Malo les Bains', 202, 'Une petite course à pied :\r\nPoint de départ : Mémorial des Alliés\r\nPoint d&#039;arrivée : Poste de Secours Malo Terminus', 1, 30, NULL, '../uploads/affiches_event/EILMOTION_affiche_Le mouvement du bien-être_1764194150.png', NULL, 'Mauvaise date sur l&#039;affiche', 0, NULL),
(97, '2025-11-27 10:11:15', 1, 1, 1, 1, 'Le mouvement du bien être', '33', 'Dunkerque', '2025-12-21', '09:30:00', '11:45:00', 'Plage Malo Les Bains', 202, 'Une petite course à pied :\r\nPoint de départ : Mémorial des Alliés\r\nPoint d&#039;arrivée : Point de Secours Malo Terminus', 1, 30, NULL, '../uploads/affiches_event/EILMOTION_affiche_Le mouvement du bien être_1764234675.jpg', NULL, NULL, 1, NULL),
(98, '2025-11-27 11:38:22', NULL, 1, NULL, 0, 'Sortie Marché de Noël', '40', 'Dunkerque', '2025-12-12', '19:00:00', '21:00:00', 'Place JEAN BART', 194, 'Le 12 décembre 2025, de 19h à 21h, nous organisons une sortie au Marché de Noël de Dunkerque, sur la place Jean Bart. Nous profiterons de l’ambiance féérique avec la grande roue illuminée, la patinoire ouverte à tous et les nombreux stands proposant gourmandises et artisanat. Une belle occasion de se retrouver et de partager la magie de Noël.', 0, 0, NULL, '../uploads/affiches_event/BureaudesLoisirsDunkerqueBDL_affiche_Sortie Marché de Noël_1764239902.jpg', NULL, NULL, NULL, NULL),
(99, '2025-11-27 11:38:44', NULL, NULL, NULL, 0, 'Sortie Marché de Noël', '40', 'Dunkerque', '2025-12-12', '19:00:00', '21:00:00', 'Place JEAN BART', 194, 'Le 12 décembre 2025, de 19h à 21h, nous organisons une sortie au Marché de Noël de Dunkerque, sur la place Jean Bart. Nous profiterons de l’ambiance féérique avec la grande roue illuminée, la patinoire ouverte à tous et les nombreux stands proposant gourmandises et artisanat. Une belle occasion de se retrouver et de partager la magie de Noël.', 0, 0, NULL, '../uploads/affiches_event/BureaudesLoisirsDunkerqueBDL_affiche_Sortie Marché de Noël_1764239924.jpg', NULL, 'Doublon', 0, NULL),
(100, '2025-11-27 11:38:53', NULL, NULL, NULL, 0, 'Sortie Marché de Noël', '40', 'Dunkerque', '2025-12-12', '19:00:00', '21:00:00', 'Place JEAN BART', 194, 'Le 12 décembre 2025, de 19h à 21h, nous organisons une sortie au Marché de Noël de Dunkerque, sur la place Jean Bart. Nous profiterons de l’ambiance féérique avec la grande roue illuminée, la patinoire ouverte à tous et les nombreux stands proposant gourmandises et artisanat. Une belle occasion de se retrouver et de partager la magie de Noël.', 0, 0, NULL, '../uploads/affiches_event/BureaudesLoisirsDunkerqueBDL_affiche_Sortie Marché de Noël_1764239933.jpg', NULL, 'Doublon', 0, NULL),
(101, '2025-11-27 11:39:40', NULL, NULL, NULL, 0, 'Sortie Marché de Noël', '40', 'Dunkerque', '2025-12-12', '19:00:00', '21:00:00', 'Place JEAN BART', 200, 'Le 12 décembre 2025, de 19h à 21h, nous organisons une sortie au Marché de Noël de Dunkerque, sur la place Jean Bart. Nous profiterons de l’ambiance féérique avec la grande roue illuminée, la patinoire ouverte à tous et les nombreux stands proposant gourmandises et artisanat. Une belle occasion de se retrouver et de partager la magie de Noël.', 0, 0, NULL, '../uploads/affiches_event/BureaudesLoisirsDunkerqueBDL_affiche_Sortie Marché de Noël_1764239980.jpg', NULL, 'Doublon', 0, NULL),
(102, '2025-11-28 10:54:50', NULL, 1, NULL, 0, 'Boost ton CV !', '36', 'Dunkerque', '2025-12-19', '17:30:00', '19:30:00', '3.06 salle de cours', 212, 'L’événement proposé est un atelier de correction et d’optimisation de CV destiné aux participants souhaitant améliorer la qualité de leurs candidatures professionnelles. L’objectif est d’offrir un accompagnement personnalisé afin d’aider chacun à structurer son CV, valoriser ses compétences et le rendre conforme aux attentes actuelles du marché du travail.\r\n\r\nL’atelier sera encadré par des intervenants compétents et se déroulera dans un format individuel et/ou groupé, selon l’affluence. Il s’agit d’une activité à visée pédagogique visant à renforcer l’employabilité des participants', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(103, '2025-11-28 13:09:13', NULL, 1, NULL, 0, 'Retour à baby Aura', '19', 'Calais', '2026-02-19', '13:30:00', '15:30:00', 'Eilco ', 128, '1. Le Concept \r\n&amp;quot;Retour à Baby Aura&amp;quot; est une bulle temporelle. Le temps d&amp;#039;une demi-journée, les étudiants de l&amp;#039;EILCO laissent leurs costumes de futurs ingénieurs au vestiaire pour retrouver l&amp;#039;insouciance de leurs 5 ans. C&amp;#039;est un événement &amp;quot;Feel Good&amp;quot; axé sur le partage d&amp;#039;anecdotes, la découverte des cultures de chacun à travers leurs souvenirs d&amp;#039;enfance, et la déconnexion totale.\r\n2. Les Objectifs du Club (Impact &amp;amp; Cohésion) \r\nSolidarité (Lien Social) : Briser la glace et créer des liens profonds en partageant des souvenirs intimes et drôles.\r\n\r\nCulture &amp;amp; International : Découvrir que les dessins animés ou les comptines sont différents (ou pareils !) au Maroc, en France, au Sénégal, en Chine, etc.\r\n\r\nBien-être : Offrir une pause mentale amusante face au stress des études.\r\nLe Cercle des Souvenirs (Storytelling) \r\n\r\nOn s&amp;#039;assoit en cercle sur des coussins.\r\n\r\nChacun tire un papier avec un thème : &amp;quot;Ma plus grosse bêtise&amp;quot;, &amp;quot;Mon jouet préféré&amp;quot;, &amp;quot;Ce que je voulais faire comme métier&amp;quot;.\r\n\r\nOn partage les histoires. C&amp;#039;est le moment &amp;quot;émotion et rire&amp;quot;.\r\nQuiz &amp;amp; Karaoké &amp;quot;Génération Minikeums/Disney&amp;quot; \r\n\r\nBlind Test : Deviner les génériques de dessins animés (Pokemon, Code Lyoko, Bob l&amp;#039;éponge...).\r\n\r\nInternational : Les étudiants étrangers font écouter un générique culte de leur pays.\r\n\r\nChant : On chante tous en chœur les classiques Disney ou les tubes des années 2000.\r\nProjection &amp;quot;Ciné-Câlin&amp;quot; \r\n\r\nOn projette 2 ou 3 épisodes de dessins animés cultes choisis par vote, juste pour le plaisir de regarder ça ensemble.\r\nLe &amp;quot;Buffet des Babies&amp;quot; (Menu Régressif) \r\nL&amp;#039;idée est de proposer de la nourriture &amp;quot;doudou&amp;quot; qui rappelle l&amp;#039;enfance.\r\n\r\nLe Bar à Céréales : Bols de lait (vache/végétal) avec Chocapic, Miel Pops, Froot Loops.\r\n\r\nLa &amp;quot;Cerelac&amp;quot; Station : Pour la touche internationale (très populaire au Maghreb/Afrique), proposer de la bouillie lactée ou des biscuits trempés.\r\n\r\nSnacks : Tartines de Nutella, Madeleines, Kinder Surprise, Compotes à boire (Pom&amp;#039;Potes), Biscuits BN ou Prince.\r\n\r\nBoissons : Chocolat chaud avec guimauves, Sirop de grenadine, Jus de pomme.\r\n\r\n', 0, 0, '../uploads/fiches_sanitaires/AURAEILCO_fiche_sanitaire_Retour à baby Aura_1764331753.pdf', '../uploads/affiches_event/AURAEILCO_affiche_Retour à baby Aura_1764331753.jpg', NULL, NULL, NULL, NULL),
(104, '2025-11-28 13:09:28', 1, 1, 1, 1, 'QUIZ CULTUREL ', '35', 'Dunkerque', '2025-12-20', '14:00:00', '16:00:00', 'PARC PUBLIC: Rue Célestin Malo, 59210 Coudekerque-Branche', 207, 'Participez à notre quiz culturel en plein air, une parenthèse conviviale où amateurs de culture générale et esprits curieux se retrouvent dans une atmosphère détendue. Organisé dans un espace ouvert, cet événement vous propose de mesurer vos connaissances à travers une série de questions variées : histoire, arts, cinéma, musique, traditions du monde…', 1, 40, NULL, '../uploads/affiches_event/CulturaConnect_affiche_QUIZ CULTUREL _1764331768.png', NULL, NULL, 1, NULL),
(105, '2025-12-03 21:40:58', NULL, NULL, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'salle 117', 116, 'Dans le cadre de la valorisation de la diversité culturelle au sein de notre établissement, le club EILCook organise un événement culinaire intitulé « Découverte culinaire Internationale », qui se tiendra le 19 décembre 2025 dans la salle 117.\r\n\r\nCet événement vise à faire découvrir aux étudiants différentes traditions gastronomiques à travers la préparation et la dégustation de divers plats internationaux. Chaque préparation sera accompagnée d’une ambiance musicale propre à son pays d’origine, afin de proposer une expérience immersive à la fois culinaire et culturelle.\r\n\r\nPlusieurs stands seront organisés, chacun consacré à un plat spécifique :\r\n\r\n\r\n 1. Briwats salés (Maroc)\r\n\r\nDescription :\r\nPetites pâtisseries salées en forme de triangles, farcies traditionnellement d’un mélange de viande, d’oignons et d’épices marocaines, puis frites ou cuites au four. Ce mets est emblématique des tables marocaines lors des fêtes et grandes occasions.\r\n\r\nIngrédients :\r\n\r\nFeuilles de brick\r\n\r\nViande hachée (ou poulet haché selon la variante)\r\n\r\nOignons\r\n\r\nPersil et coriandre frais\r\n\r\nAil\r\n\r\nÉpices marocaines (ras el hanout, paprika, cumin)\r\n\r\nSel, poivre\r\n\r\nFromage type kiri (optionnel, selon les recettes modernes)\r\n\r\nŒuf pour lier\r\n\r\nHuile pour friture ou cuisson\r\n\r\n\r\nAmbiance musicale :\r\nSélection de musiques marocaines instrumentales et andalouses pour accompagner l’expérience.\r\n\r\n\r\n 2. Taboulé (Liban)\r\n\r\nDescription : Salade fraîche libanaise à base de persil, boulgour et herbes aromatiques.\r\nIngrédients : Persil, boulgour fin, tomates, menthe, citron, huile d’olive, oignons, sel, poivre.\r\nMusique : Ambiance libanaise instrumentale.\r\n\r\n\r\n\r\n 3. Pancakes (États-Unis)\r\n\r\nDescription : Pâtisseries moelleuses servies avec sirop, fruits ou chocolat.\r\nIngrédients : Farine, lait, œufs, sucre, levure chimique, beurre fondu, vanille.\r\nMusique : Jazz et soul américains.\r\n\r\n\r\n\r\n 4. Muffins (France)\r\n\r\nDescription : Gâteaux individuels déclinés en chocolat, fruits rouges ou vanille.\r\nIngrédients : Farine, sucre, œufs, beurre ou huile, levure chimique, lait, chocolat/fruits.\r\nMusique : Musique française douce.\r\n\r\n\r\n\r\n\r\n5. Jus variés\r\n\r\nDescription : Jus de fruits frais pressés ou mixés.\r\nIngrédients : Oranges, pommes, fraises, bananes, citron, eau.\r\nMusique : Playlist internationale.\r\n\r\n\r\n\r\nObjectifs de l’événement :\r\n\r\nMettre en valeur la diversité culturelle du campus\r\nFavoriser les échanges entre étudiants\r\nDécouvrir des traditions culinaires variées\r\nRenforcer la cohésion au sein de l’école', 0, 0, '../uploads/fiches_sanitaires/EILCook_fiche_sanitaire_Découverte Culinaire Internationale_1764794458.pdf', NULL, NULL, 'Fiche envoyée en double.', 0, NULL),
(106, '2025-12-03 21:41:12', NULL, 1, NULL, 0, 'Découverte Culinaire Internationale', '47', 'Calais', '2025-12-19', '14:00:00', '16:00:00', 'salle 117', 116, 'Dans le cadre de la valorisation de la diversité culturelle au sein de notre établissement, le club EILCook organise un événement culinaire intitulé « Découverte culinaire Internationale », qui se tiendra le 19 décembre 2025 dans la salle 117.\r\n\r\nCet événement vise à faire découvrir aux étudiants différentes traditions gastronomiques à travers la préparation et la dégustation de divers plats internationaux. Chaque préparation sera accompagnée d’une ambiance musicale propre à son pays d’origine, afin de proposer une expérience immersive à la fois culinaire et culturelle.\r\n\r\nPlusieurs stands seront organisés, chacun consacré à un plat spécifique :\r\n\r\n\r\n 1. Briwats salés (Maroc)\r\n\r\nDescription :\r\nPetites pâtisseries salées en forme de triangles, farcies traditionnellement d’un mélange de viande, d’oignons et d’épices marocaines, puis frites ou cuites au four. Ce mets est emblématique des tables marocaines lors des fêtes et grandes occasions.\r\n\r\nIngrédients :\r\n\r\nFeuilles de brick\r\n\r\nViande hachée (ou poulet haché selon la variante)\r\n\r\nOignons\r\n\r\nPersil et coriandre frais\r\n\r\nAil\r\n\r\nÉpices marocaines (ras el hanout, paprika, cumin)\r\n\r\nSel, poivre\r\n\r\nFromage type kiri (optionnel, selon les recettes modernes)\r\n\r\nŒuf pour lier\r\n\r\nHuile pour friture ou cuisson\r\n\r\n\r\nAmbiance musicale :\r\nSélection de musiques marocaines instrumentales et andalouses pour accompagner l’expérience.\r\n\r\n\r\n 2. Taboulé (Liban)\r\n\r\nDescription : Salade fraîche libanaise à base de persil, boulgour et herbes aromatiques.\r\nIngrédients : Persil, boulgour fin, tomates, menthe, citron, huile d’olive, oignons, sel, poivre.\r\nMusique : Ambiance libanaise instrumentale.\r\n\r\n\r\n\r\n 3. Pancakes (États-Unis)\r\n\r\nDescription : Pâtisseries moelleuses servies avec sirop, fruits ou chocolat.\r\nIngrédients : Farine, lait, œufs, sucre, levure chimique, beurre fondu, vanille.\r\nMusique : Jazz et soul américains.\r\n\r\n\r\n\r\n 4. Muffins (France)\r\n\r\nDescription : Gâteaux individuels déclinés en chocolat, fruits rouges ou vanille.\r\nIngrédients : Farine, sucre, œufs, beurre ou huile, levure chimique, lait, chocolat/fruits.\r\nMusique : Musique française douce.\r\n\r\n\r\n\r\n\r\n5. Jus variés\r\n\r\nDescription : Jus de fruits frais pressés ou mixés.\r\nIngrédients : Oranges, pommes, fraises, bananes, citron, eau.\r\nMusique : Playlist internationale.\r\n\r\n\r\n\r\nObjectifs de l’événement :\r\n\r\nMettre en valeur la diversité culturelle du campus\r\nFavoriser les échanges entre étudiants\r\nDécouvrir des traditions culinaires variées\r\nRenforcer la cohésion au sein de l’école', 0, 0, '../uploads/fiches_sanitaires/EILCook_fiche_sanitaire_Découverte Culinaire Internationale_1764794472.pdf', NULL, NULL, NULL, NULL, NULL),
(107, '2025-12-04 17:51:24', 1, 1, 1, 1, 'PULSAR Clay Art – Décoration de pots écologiques', '52', 'Dunkerque', '2025-12-19', '17:30:00', '19:00:00', 'MDE', 224, 'Objectif\r\nAcheter des pots et permettre à chaque participant de personnaliser son propre pot à la peinture, pour une décoration durable et réutilisable.\r\n\r\nDéroulé\r\n\r\nAccueil et présentation rapide des consignes de sécurité et du matériel.\r\n\r\nDistribution des pots et choix des couleurs/motifs.\r\n\r\nPersonnalisation à la peinture acrylique .\r\n\r\nSéchage rapide et vernis de protection si nécessaire.\r\n\r\nAnnonce de la suite du projet: lors d’une prochaine séance, mise en pot de petites plantes dans les créations réalisées. \r\n\r\nRemarque \r\n\r\nPrévoir nappes de protection pour tables, zone de séchage, et gobelets d’eau changeables pour limiter le gaspillage.\r\n\r\n(Ces pots serviront ensuite à décorer un espace dans l’université )', 1, 120, NULL, '../uploads/affiches_event/PULSARPrserverUnirLutterSolidariserAgirResponsabiliser_affiche_PULSAR Clay Art – Décoration de pots écologiques_1764867084.png', NULL, NULL, 1, NULL),
(108, '2025-12-05 23:07:27', NULL, 1, NULL, 0, 'Sortie Karting', '46', 'Calais', '2025-12-20', '13:00:00', '17:00:00', 'extérieure - Planet-karting', 85, 'Sortie karting au &quot;Planet-Karting&quot; à st Omer le 19/12/2025.\r\nDépart groupé de la gare calais ville à 13h.\r\nprix: - 23 € prix normale \r\n      - 20 € pour les cotisants du BDE', 1, 100, NULL, '../uploads/affiches_event/EilcoCarCommunity_affiche_Sortie Karting_1764972447.png', NULL, NULL, NULL, NULL),
(109, '2025-12-05 23:07:45', NULL, NULL, NULL, 0, 'Sortie Karting', '46', 'Calais', '2025-12-20', '13:00:00', '17:00:00', 'extérieure - Planet-karting', 85, 'Sortie karting au &quot;Planet-Karting&quot; à st Omer le 19/12/2025.\r\nDépart groupé de la gare calais ville à 13h.\r\nprix: - 23 € prix normale \r\n      - 20 € pour les cotisants du BDE', 1, 100, NULL, '../uploads/affiches_event/EilcoCarCommunity_affiche_Sortie Karting_1764972465.png', NULL, 'Fiche envoyée en double.', 0, NULL),
(110, '2025-12-05 23:09:21', NULL, NULL, NULL, 0, 'Sortie Karting', '46', 'Calais', '2025-12-20', '13:00:00', '17:00:00', 'extérieure - Planet-karting', 85, 'Sortie karting au &quot;Planet-Karting&quot; à st Omer le 19/12/2025.\r\nDépart groupé de la gare calais ville à 13h.\r\nprix: - 23 € prix normale \r\n      - 20 € pour les cotisants du BDE', 1, 100, NULL, '../uploads/affiches_event/EilcoCarCommunity_affiche_Sortie Karting_1764972561.png', NULL, 'Fiche envoyée en double.', 0, NULL),
(111, '2025-12-05 23:14:56', NULL, NULL, NULL, 0, 'Sortie Karting', '46', 'Calais', '2025-12-20', '13:00:00', '17:00:00', 'extérieure - Planet-karting', 85, 'Sortie karting au &quot;Planet-Karting&quot; à st Omer le 19/12/2025.\r\nDépart groupé de la gare calais ville à 13h.\r\nprix: - 23 € prix normale \r\n      - 20 € pour les cotisants du BDE', 1, 100, NULL, '../uploads/affiches_event/EilcoCarCommunity_affiche_Sortie Karting_1764972896.png', NULL, 'Fiche envoyée en double.', 0, NULL),
(112, '2025-12-05 23:21:04', NULL, NULL, NULL, 0, 'Karting', '46', 'Calais', '2025-12-20', '13:00:00', '17:00:00', 'Exterieure planet karting ', 85, 'Sortie karting au &quot;Planet-Karting&quot; à st Omer le 19/12/2025.\r\nDépart groupé de la gare calais ville à 13h.\r\nprix: - 23 € prix normale \r\n      - 20 € pour les cotisants du BDE', 1, 100, NULL, '../uploads/affiches_event/EilcoCarCommunity_affiche_Karting_1764973264.jpg', NULL, 'Fiche envoyée en double.', 0, NULL),
(113, '2025-12-05 23:51:54', NULL, NULL, NULL, 0, 'Sortie Karting', '46', 'Calais', '2025-12-20', '13:00:00', '17:00:00', 'extérieure - Planet-karting', 98, 'Sortie karting au &quot;Planet-Karting&quot; à st Omer le 19/12/2025.\r\nDépart groupé de la gare calais ville à 13h.\r\nprix: - 23 € prix normale \r\n      - 20 € pour les cotisants du BDE', 1, 100, NULL, '../uploads/affiches_event/EilcoCarCommunity_affiche_Sortie Karting_1764975114.png', NULL, 'Fiche envoyée en double.', 0, NULL),
(114, '2025-12-22 20:02:59', NULL, 1, NULL, 0, 'MoZaïk’Art – Atelier Créatif &amp; Expression Artistique', '57', 'Dunkerque', '2026-01-08', '13:30:00', '15:30:00', '3.04', 219, 'Cet événement artistique propose aux étudiants de participer à des ateliers de fresque collective, de poterie et de peinture. Il a pour objectif de favoriser la créativité, les échanges culturels et la cohésion entre les participants dans un cadre convivial. Les activités permettent à chacun de s’exprimer librement tout en contribuant à des réalisations artistiques individuelles et collectives, en accord avec les valeurs de partage et de diversité portées par le club MoZaïkSpot.', 1, 100, NULL, NULL, NULL, NULL, NULL, NULL),
(115, '2025-12-23 23:35:02', NULL, NULL, NULL, 0, 'Conférence sur l&#039;Esport', '7', 'Calais', '2026-01-08', '15:30:00', '17:30:00', 'Salle A022', 26, 'L’évènement se déroulera le jeudi 8 janvier 2026 à partir de 15h30 en salle A022.\r\nLe président d&#039;une structure Esport, Genesium, viendra faire une conférence concernant son parcours, la structure qu&#039;il a créée ainsi que le monde de l&#039;Esport. Il répondra aux différentes attentes des personnes sondées. Il y a également une possibilité qu&#039;un joueur de la structure soit présent. De plus, cette conférence sera diffusée sur la chaîne Twitch de Genesium pour qu&#039;un maximum de monde puisse y assister même sans être présent.', 0, 0, NULL, '../uploads/affiches_event/Ch039tisGamers_affiche_Conférence sur l&#039;Esport_1766529302.png', NULL, NULL, NULL, NULL),
(116, '2025-12-24 13:16:49', NULL, 1, 1, 0, 'Bienvenue au D10 Cassé Casino !', '10', 'Calais', '2026-01-08', '13:15:00', '17:30:00', 'A118 et A117', 64, 'D10cassé vous invite à une après-midi exceptionnel placé sous le signe du hasard, de la stratégie et de l’imaginaire.\r\n\r\nLe club se transforme en un casino éphémère, vous pourrez découvrir ou redécouvrir de nombreux jeux de société aux mécaniques inspirées du casino : bluff, prise de risque, gestion de ressources et coups de maître seront au rendez-vous. Que vous soyez joueur débutant ou stratège aguerri, chaque table proposera une expérience accessible, conviviale et rythmée où vous devrez grossir vos bourses par la réussite aux jeux.\r\n\r\nEn parallèle, les amateurs de narration et d’aventure pourront participer à une partie de jeu de rôle sous la forme de défis et de combats pour eux aussi gagner des jetons d&#039;argent.\r\n\r\nAurez-vous l&#039;âme des cartes pour gagner ou laisserez vous le hasard des dés décider.', 0, 0, NULL, '../uploads/affiches_event/D10Cass_affiche_Bienvenue au D10 Cassé Casino !_1766578609.png', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `mails`
--

CREATE TABLE `mails` (
  `id` int NOT NULL,
  `u_id` int NOT NULL,
  `campus` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `role` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `mails`
--

INSERT INTO `mails` (`id`, `u_id`, `campus`, `role`) VALUES
(1, 5, 'Calais', 1),
(3, 4, 'Calais', 1),
(4, 26, 'Calais', 1),
(5, 28, 'Longuenesse', 1),
(6, 29, 'Calais', 1),
(7, 27, 'Dunkerque', 1),
(8, 25, 'Calais', 1);

-- --------------------------------------------------------

--
-- Structure de la table `membres_club`
--

CREATE TABLE `membres_club` (
  `id` int NOT NULL,
  `membre_id` int NOT NULL,
  `club_id` int NOT NULL,
  `fonction` varchar(128) NOT NULL,
  `soutenance` tinyint(1) NOT NULL DEFAULT '0',
  `valide` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `membres_club`
--

INSERT INTO `membres_club` (`id`, `membre_id`, `club_id`, `fonction`, `soutenance`, `valide`) VALUES
(1, 25, 1, 'Président', 0, 0),
(2, 26, 1, 'Trésorier', 0, 0),
(3, 27, 1, 'Secrétaire', 0, 0),
(4, 28, 1, 'Chargé événements', 0, 0),
(8, 58, 2, 'Président', 0, 1),
(9, 56, 2, 'Trésorier', 1, 1),
(10, 55, 2, 'Secrétaire', 1, 1),
(11, 54, 2, 'Chargé événements', 1, 1),
(12, 41, 2, 'Vice-Président', 1, 1),
(13, 43, 2, 'Chargé Partenariat', 0, 1),
(14, 27, 3, 'Président', 0, 1),
(15, 38, 3, 'Trésorier', 0, 1),
(16, 36, 3, 'Secrétaire', 0, 1),
(17, 37, 3, 'Chargé événements', 0, 1),
(18, 34, 3, 'Chargée communication', 0, 1),
(19, 35, 3, 'Vice Trésorier', 0, 1),
(20, 31, 3, 'Chargée évent', 0, 1),
(21, 33, 3, 'Chargé évent', 0, 1),
(22, 25, 4, 'Président', 0, 1),
(23, 41, 4, 'Trésorier', 0, 1),
(24, 54, 4, 'Secrétaire', 0, 1),
(25, 62, 4, 'Chargé événements', 0, 1),
(26, 40, 4, 'Chargé Événements', 0, 1),
(27, 58, 4, 'Chargé communication', 0, 1),
(28, 55, 4, 'Chargé communication', 0, 1),
(29, 43, 4, 'Vice-Président', 0, 1),
(30, 26, 4, 'Chargée partenariats ', 0, 1),
(31, 39, 4, 'Chargé associations/clubs', 0, 1),
(32, 42, 4, 'Chargée Cycles Préparatoires', 0, 1),
(33, 58, 2, 'Président', 0, 1),
(34, 56, 2, 'Trésorier', 0, 1),
(35, 55, 2, 'Secrétaire', 0, 1),
(36, 54, 2, 'Chargé événements', 0, 1),
(37, 41, 2, 'Vice-Président', 0, 1),
(38, 43, 2, 'Chargé Partenariat', 0, 1),
(39, 64, 6, 'Président', 1, 1),
(40, 42, 6, 'Trésorier', 0, 1),
(41, 66, 6, 'Secrétaire', 0, 1),
(42, 71, 6, 'Chargé événements', 1, 1),
(43, 70, 6, 'Responsable communication', 1, 1),
(44, 69, 6, 'Vice-président', 1, 1),
(45, 26, 7, 'Président', 1, 1),
(46, 40, 7, 'Trésorier', 0, 1),
(47, 62, 7, 'Secrétaire', 1, 1),
(48, 60, 7, 'Chargé événements', 1, 1),
(49, 25, 7, 'Vice-Président', 0, 1),
(50, 83, 7, 'Chargé Communication', 1, 1),
(51, 67, 7, 'Vice-Chargé événement', 0, 1),
(52, 79, 7, 'Membre actif', 0, 1),
(53, 81, 8, 'Président', 1, 1),
(54, 61, 8, 'Trésorier', 1, 1),
(55, 96, 8, 'Secrétaire', 1, 1),
(56, 102, 8, 'Chargé événements', 1, 1),
(57, 105, 8, ' Chargé Communication', 1, 1),
(58, 100, 8, 'Vice-Président', 0, 1),
(59, 59, 9, 'Président', 0, 1),
(60, 99, 9, 'Trésorier', 0, 1),
(61, 91, 9, 'Secrétaire', 0, 1),
(62, 68, 9, 'Chargé événements', 0, 1),
(63, 64, 10, 'Président', 1, 1),
(64, 39, 10, 'Trésorier', 1, 1),
(65, 66, 10, 'Secrétaire', 0, 1),
(66, 71, 10, 'Chargé événements', 1, 1),
(67, 70, 10, 'Chargé communication', 1, 1),
(68, 42, 10, 'Chargé cartes', 0, 1),
(69, 69, 10, 'Vice-président', 1, 1),
(70, 107, 10, 'Maître du jeux', 0, 1),
(71, 72, 11, 'Président', 1, 1),
(72, 75, 11, 'Trésorier', 1, 1),
(73, 80, 11, 'Secrétaire', 1, 1),
(74, 73, 11, 'Chargé événements', 1, 1),
(75, 76, 11, 'Vice-Prédisent', 1, 1),
(76, 39, 11, 'Responsable de relations externes', 0, 1),
(77, 42, 12, 'Président', 0, 1),
(78, 77, 12, 'Trésorier', 0, 1),
(79, 74, 12, 'Secrétaire', 0, 1),
(80, 66, 12, 'Chargé événements', 0, 1),
(81, 111, 12, 'Chargé évenements', 0, 1),
(82, 79, 12, 'Vice-président', 0, 1),
(83, 78, 12, 'Vice-secrétaire', 0, 1),
(84, 103, 13, 'Président', 1, 1),
(85, 125, 13, 'Trésorier', 1, 1),
(86, 119, 13, 'Secrétaire', 1, 1),
(87, 106, 13, 'Chargé événements', 1, 1),
(88, 118, 13, 'vise présidente ', 1, 1),
(89, 84, 14, 'Président', 1, 1),
(90, 86, 14, 'Trésorier', 1, 1),
(91, 120, 14, 'Secrétaire', 1, 1),
(92, 87, 14, 'Chargé événements', 1, 1),
(93, 82, 14, 'Vice présidente ', 1, 1),
(94, 84, 14, 'Président', 1, 1),
(95, 86, 14, 'Trésorier', 1, 1),
(96, 120, 14, 'Secrétaire', 1, 1),
(97, 87, 14, 'Chargé événements', 1, 1),
(98, 82, 14, 'Vice présidente ', 1, 1),
(99, 131, 16, 'Président', 0, 1),
(100, 135, 16, 'Trésorier', 0, 1),
(101, 112, 16, 'Secrétaire', 0, 1),
(102, 133, 16, 'Chargé événements', 0, 1),
(103, 28, 16, 'Vice président', 0, 1),
(104, 109, 17, 'Président', 0, 1),
(105, 143, 17, 'Trésorier', 0, 1),
(106, 136, 17, 'Secrétaire', 0, 1),
(107, 137, 17, 'Chargé événements', 0, 1),
(108, 108, 17, 'Responsable média', 0, 1),
(109, 126, 18, 'Président', 1, 0),
(110, 139, 18, 'Trésorier', 1, 0),
(111, 127, 18, 'Secrétaire', 1, 0),
(112, 141, 18, 'Chargé événements', 1, 0),
(113, 140, 18, 'Vice président ', 1, 0),
(114, 128, 19, 'Président', 1, 1),
(115, 123, 19, 'Trésorier', 0, 1),
(116, 129, 19, 'Secrétaire', 1, 1),
(117, 132, 19, 'Chargé événements', 0, 1),
(118, 124, 19, 'Vice-président', 1, 1),
(119, 134, 19, 'Chargé partenariat', 0, 1),
(120, 132, 20, 'Président', 1, 1),
(121, 123, 20, 'Trésorier', 1, 1),
(122, 128, 20, 'Secrétaire', 0, 1),
(123, 129, 20, 'Chargé événements', 0, 1),
(124, 134, 20, 'Vice président', 1, 1),
(125, 124, 20, 'chargé partenariat', 0, 1),
(127, 130, 21, 'Président', 0, 1),
(128, 166, 21, 'Trésorier', 0, 1),
(129, 162, 21, 'Secrétaire', 0, 1),
(130, 165, 21, 'Chargé événements', 0, 1),
(131, 167, 21, 'chargé design', 0, 1),
(132, 152, 22, 'Président', 0, 1),
(133, 154, 22, 'Trésorier', 0, 1),
(134, 153, 22, 'Secrétaire', 0, 1),
(135, 59, 22, 'Chargé événements', 0, 1),
(136, 126, 23, 'Président', 1, 1),
(137, 139, 23, 'Trésorier', 1, 1),
(138, 127, 23, 'Secrétaire', 1, 1),
(139, 141, 23, 'Chargé événements', 1, 1),
(140, 140, 23, 'Vice président ', 1, 1),
(141, 128, 19, 'Président', 1, 1),
(142, 123, 19, 'Trésorier', 0, 1),
(143, 129, 19, 'Secrétaire', 1, 1),
(144, 132, 19, 'Chargé événements', 0, 1),
(145, 124, 19, 'Vice-président', 1, 1),
(146, 134, 19, 'Chargé partenariat', 0, 1),
(147, 152, 22, 'Président', 1, 1),
(148, 154, 22, 'Trésorier', 0, 1),
(149, 153, 22, 'Secrétaire', 1, 1),
(150, 59, 22, 'Chargé événements', 1, 1),
(151, 121, 26, 'Président', 1, 1),
(152, 174, 26, 'Trésorier', 1, 1),
(153, 122, 26, 'Secrétaire', 1, 1),
(154, 159, 26, 'Chargé événements', 1, 1),
(155, 151, 26, 'Responsable Média', 0, 1),
(156, 93, 27, 'Président', 1, 1),
(157, 95, 27, 'Trésorier', 1, 1),
(158, 101, 27, 'Secrétaire', 1, 1),
(159, 110, 27, 'Chargé événements', 1, 1),
(160, 94, 27, 'Responsable média et infographiste', 1, 1),
(161, 92, 27, 'Membre actif', 0, 1),
(162, 161, 28, 'Président', 1, 1),
(163, 170, 28, 'Trésorier', 1, 1),
(164, 164, 28, 'Secrétaire', 1, 1),
(165, 173, 28, 'Chargé événements', 1, 1),
(166, 171, 28, 'Vice-président', 1, 1),
(167, 92, 29, 'Président', 1, 1),
(168, 163, 29, 'Trésorier', 1, 1),
(169, 145, 29, 'Secrétaire', 1, 1),
(170, 157, 29, 'Chargé événements', 1, 1),
(171, 146, 29, 'Responsable média et infographie', 1, 1),
(172, 135, 30, 'Président', 1, 1),
(173, 184, 30, 'Trésorier', 0, 1),
(174, 112, 30, 'Secrétaire', 1, 1),
(175, 113, 30, 'Chargé événements', 1, 1),
(176, 114, 30, 'Vice-président', 0, 1),
(177, 190, 30, 'Vice-trésorier', 0, 1),
(178, 158, 30, 'Chargée évènements', 0, 1),
(179, 172, 31, 'Président', 0, 0),
(180, 178, 31, 'Trésorier', 0, 0),
(181, 177, 31, 'Secrétaire', 0, 0),
(182, 115, 31, 'Chargé événements', 0, 0),
(183, 179, 31, 'Vice chargé événement', 0, 0),
(184, 187, 32, 'Président', 1, 1),
(185, 182, 32, 'Trésorier', 1, 1),
(186, 188, 32, 'Secrétaire', 1, 1),
(187, 186, 32, 'Chargé événements', 1, 1),
(188, 185, 32, 'vice-président ', 1, 1),
(189, 195, 33, 'Président', 1, 1),
(190, 196, 33, 'Trésorier', 1, 1),
(191, 197, 33, 'Secrétaire', 1, 1),
(192, 202, 33, 'Chargé événements', 1, 1),
(193, 199, 33, 'Responsable Média', 1, 1),
(194, 117, 34, 'Président', 0, 1),
(195, 189, 34, 'Trésorier', 0, 1),
(196, 204, 34, 'Secrétaire', 0, 1),
(197, 192, 34, 'Chargé événements', 0, 1),
(198, 191, 34, 'Responsable pole speakers ', 0, 1),
(199, 193, 34, 'Responsable de la stratégie digitale ', 0, 1),
(200, 207, 35, 'Président', 1, 1),
(201, 206, 35, 'Trésorier', 1, 1),
(202, 209, 35, 'Secrétaire', 1, 1),
(203, 210, 35, 'Chargé événements', 1, 1),
(204, 208, 35, 'Vice-présidente', 1, 1),
(205, 212, 36, 'Président', 0, 1),
(206, 175, 36, 'Trésorier', 0, 1),
(207, 203, 36, 'Secrétaire', 0, 1),
(208, 205, 36, 'Chargé événements', 0, 1),
(217, 212, 36, 'Président', 1, 1),
(218, 175, 36, 'Trésorier', 1, 1),
(219, 203, 36, 'Secrétaire', 1, 1),
(220, 213, 36, 'Chargé événements', 1, 1),
(221, 205, 36, 'Vice président ', 1, 1),
(222, 200, 40, 'Président', 1, 1),
(223, 201, 40, 'Trésorier', 1, 1),
(224, 144, 40, 'Secrétaire', 1, 1),
(225, 198, 40, 'Chargé événements', 1, 1),
(226, 194, 40, 'Vice président', 1, 1),
(227, 104, 41, 'Président', 1, 0),
(228, 98, 41, 'Trésorier', 1, 0),
(229, 142, 41, 'Secrétaire', 1, 0),
(230, 85, 41, 'Chargé événements', 1, 0),
(231, 160, 42, 'Président', 1, 1),
(232, 169, 42, 'Trésorier', 1, 1),
(233, 57, 42, 'Secrétaire', 1, 1),
(234, 176, 42, 'Chargé événements', 0, 1),
(235, 216, 43, 'Président', 0, 1),
(236, 215, 43, 'Trésorier', 0, 1),
(237, 217, 43, 'Secrétaire', 0, 1),
(238, 57, 43, 'Chargé événements', 0, 1),
(239, 160, 43, 'MEMBRE', 0, 1),
(240, 180, 44, 'Président', 1, 1),
(241, 181, 44, 'Trésorier', 1, 1),
(242, 183, 44, 'Secrétaire', 1, 1),
(243, 99, 44, 'Chargé événements', 1, 1),
(244, 176, 44, 'Respinsable de médiatisation', 1, 1),
(245, 219, 45, 'Président', 1, 0),
(246, 218, 45, 'Trésorier', 1, 0),
(247, 220, 45, 'Secrétaire', 1, 0),
(248, 222, 45, 'Chargé événements', 1, 0),
(249, 224, 45, 'vice président ', 1, 0),
(250, 104, 46, 'Président', 1, 1),
(251, 214, 46, 'Trésorier', 1, 1),
(252, 85, 46, 'Secrétaire', 1, 1),
(253, 98, 46, 'Chargé événements', 1, 1),
(254, 142, 46, 'Chargé communication ', 0, 1),
(255, 29, 37, 'Président', 1, 1),
(256, 223, 37, 'Chargé de communication', 1, 1),
(257, 116, 47, 'Président', 1, 1),
(258, 226, 47, 'Trésorier', 1, 1),
(259, 227, 47, 'Secrétaire', 1, 1),
(260, 228, 47, 'Chargé événements', 1, 1),
(261, 97, 47, 'Vice-président', 1, 1),
(262, 172, 48, 'Président', 0, 0),
(263, 178, 48, 'Trésorier', 0, 0),
(264, 177, 48, 'Secrétaire', 0, 0),
(265, 115, 48, 'Chargé événements', 0, 0),
(266, 179, 48, 'Vice chargé événement/communication', 0, 0),
(267, 229, 48, 'Vice secrétaire ', 0, 0),
(268, 172, 48, 'Président', 0, NULL),
(269, 178, 48, 'Trésorier', 0, NULL),
(270, 177, 48, 'Secrétaire', 0, NULL),
(271, 115, 48, 'Chargé événements', 0, NULL),
(272, 230, 48, 'Vice président ', 0, NULL),
(273, 229, 48, 'Vice secrétaire', 0, NULL),
(274, 179, 48, 'Vice chargé événements/communication', 0, NULL),
(275, 149, 37, 'Vice-Président', 1, 1),
(276, 147, 37, 'Trésorier', 1, 1),
(277, 148, 37, 'Secrétaire', 1, 1),
(278, 219, 45, 'Président', 1, NULL),
(279, 218, 45, 'Trésorier', 1, NULL),
(280, 222, 45, 'Secrétaire', 1, NULL),
(281, 172, 48, 'Président', 0, NULL),
(282, 178, 48, 'Trésorier', 0, NULL),
(283, 177, 48, 'Secrétaire', 0, NULL),
(284, 115, 48, 'Chargé événements', 0, NULL),
(285, 179, 48, 'Vice chargé événement/communication', 0, NULL),
(286, 230, 48, 'Vice président ', 0, NULL),
(287, 229, 48, 'Vive secrétaire ', 0, NULL),
(288, 224, 52, 'Président', 1, 1),
(289, 220, 52, 'Trésorier', 1, 1),
(290, 232, 52, 'Secrétaire', 1, 1),
(291, 172, 48, 'Président', 1, NULL),
(292, 178, 48, 'Trésorier', 0, NULL),
(293, 177, 48, 'Secrétaire', 1, NULL),
(294, 115, 48, 'Chargé événements', 1, NULL),
(295, 230, 48, 'Vice-président', 0, NULL),
(296, 229, 48, 'Vice secrétaire', 0, NULL),
(297, 179, 48, 'vice chargé évènement/communication', 0, NULL),
(298, 219, 45, 'Président', 0, NULL),
(299, 218, 45, 'Trésorier', 0, NULL),
(300, 222, 45, 'Secrétaire', 0, NULL),
(304, 219, 45, 'Président', 0, NULL),
(305, 218, 45, 'Trésorier', 0, NULL),
(306, 222, 45, 'Secrétaire', 0, NULL),
(307, 219, 57, 'Président', 0, 1),
(308, 218, 57, 'Trésorier', 0, 1),
(309, 222, 57, 'Secrétaire', 0, 1),
(310, 172, 58, 'Président', 0, 1),
(311, 178, 58, 'Trésorier', 0, 1),
(312, 177, 58, 'Secrétaire', 0, 1),
(313, 115, 58, 'Chargé événements', 0, 1),
(314, 179, 58, 'Vice chargé événement/communication', 0, 1),
(315, 229, 58, 'Vice secrétaire', 0, 1),
(319, 172, 60, 'Président', 0, 1),
(320, 178, 60, 'Trésorier', 0, 1),
(321, 177, 60, 'Secrétaire', 0, 1),
(322, 115, 60, 'Chargé événements', 0, 1),
(323, 179, 60, 'Vice chargé évènement/communication', 0, 1),
(324, 229, 60, 'Vice sécretaire', 0, 1),
(325, 230, 60, 'Vice Président', 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nom` varchar(128) NOT NULL,
  `prenom` varchar(128) NOT NULL,
  `promo` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mail` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `permission` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `promo`, `mail`, `password`, `permission`) VALUES
(16, 'BERNARD', 'Jean-François', 'admin', 'jean-francois.bernard@eilco.univ-littoral.fr', '$2y$12$9FHmO093W0AUVVA/fK720.dbPaESU1FBryF6zgobKgGFk0FYTJAxC', 5),
(17, 'DELVART', 'Sabine', 'admin', 'sabine.delvart@eilco.univ-littoral.fr', '$2y$12$BJ5i2lBgqXbu9qbX//rUwOZAUCsox9mzygSkiw.MxCxKUBX/c6mXe', 5),
(22, 'POREBSKI', 'Alice', 'Tuteur', 'alice.porebski@eilco.univ-littoral.fr', '$2y$12$AKYGXf4QKsoFraR43H8vXOj0G7//SXW5fDkH.TdG1OQLB9XStf/P6', 2),
(25, 'Cuvillier', 'Antoine', 'ING2FISEA', 'antoine.cuvillier@etu.eilco.univ-littoral.fr', '$2y$12$1wfR2gfZMZNsdec1Sa5XseUMjfrUhgHckb5mhqaR13nagodVaxE4K', 3),
(26, 'Lapôtre', 'Marie-Steffie', 'ING2FISE', 'Marie-Steffie.Lapotre@etu.eilco.univ-littoral.fr', '$2y$12$L5LQMpvKJ6wMDD6dmhBfsuLDTpOyBpySW0C2VL0VOpWHlLck43dOi', 3),
(27, 'Eckstein', 'Anna', 'ING2FISE', 'anna.eckstein@etu.eilco.univ-littoral.fr', '$2y$12$BdLoYcrR0GhLTI/UgMeal.0KNMJrbrl0/hT.cxpVmxzp3SHLYY8Iq', 3),
(28, 'Brebion', 'Thomas', 'ING2FISEA', 'thomas.brebion@etu.eilco.univ-littoral.fr', '$2y$12$oGXZgXyG1oPFP3FLINJ.d.2PupTd/AWdDgwA2XwmGe7XSROPQKmgu', 3),
(29, 'Chelhi', 'Yassine', 'ING2FISE', 'yassine.chelhi@etu.eilco.univ-littoral.fr', '$2y$12$2G3QCpiaXSwDbJtxsiiiWOXzgZPAS24GkzUTyyvvJQctiPPzgdbja', 3),
(31, 'Revault-Mazenq', 'Valentine', 'ING2FISEA', 'valentine.revault-mazenq@etu.eilco.univ-littoral.fr', '$2y$12$r5O6iNAl7AL3Tswdc.1QmO3ChZ7QnVT4x8vsjrwHmsktiSJUMo4wW', 1),
(32, 'NAELS', 'Adrien', 'admin', 'adrien.naels@eilco.univ-littoral.fr', '$2y$12$ppmT8PfHZsZqBlsFmHCSnevJw31z4cbDWAlEjm/8i7RR60EegoX4.', 5),
(33, 'Moglia', 'Paul', 'ING2FISEA', 'paul.moglia@etu.eilco.univ-littoral.fr', '$2y$12$WnIiGjMHuI8CUy1I0qkoluVZNGUlVSUtjObplIQEpopXVoYVRfYby', 1),
(34, 'Lécrivent', 'Cléa', 'ING2FISEA', 'clea.lecrivent@etu.eilco.univ-littoral.fr', '$2y$12$q1ePaCx/l8WypiQZCk1npekBipfiuRsJj3x1T4mp6DS9SGv/VN4RO', 1),
(35, 'Popelier ', 'Jules', 'ING2FISEA', 'jules.popelier@etu.eilco.univ-littoral.fr', '$2y$12$Ft0HSHJwSVwaTqVYt5BLFu645OpaJdNm4w1Jtxn13RgzvnMxq8kIu', 1),
(36, 'Parcollet', 'Emilie', 'ING2FISEA', 'emilie.parcollet@etu.eilco.univ-littoral.fr', '$2y$12$Adz29kTGkcffyBtZLxU7YuvmwDo5mam0ZGMzc28HHw6lEMZcQxQe6', 1),
(37, 'Catala ', 'Mathilde ', 'ING2FISEA', 'Mathilde.catala@etu.eilco.univ-littoral.fr', '$2y$12$Gf4g88AL0faCGYp9bJTTx.iobk9dcrXVXc7jmbY0oZ/prVY/huYRa', 1),
(38, 'BOS', 'Louis-Alexandre', 'ING2FISEA', 'Louis-Alexandre.Bos@etu.eilco.univ-littoral.fr', '$2y$12$UxPgQ8NWqUArEaWEhJViHeKiT6hlh.fcAGFVVZvG0RCuGD258sgY.', 1),
(39, 'Desmis', 'Loan', 'ING2FISE', 'loan.desmis@etu.eilco.univ-littoral.fr', '$2y$12$TXGWm5GNBj21kcSTq9QaW.2dKqD7TmHOJPFFERyNlAxYYQdm/Eqti', 3),
(40, 'biesse', 'gaetan', 'etu', 'Gaetan.Biesse@etu.eilco.univ-littoral.fr', '$2y$12$FWSH2lrT2YDctjlNcCazZOYdOuPeIK3nc3axRvoe7pR5tHInIK9Ey', 1),
(41, 'Letendart', 'Hugo', 'ING2FISE', 'hugo.letendart@etu.eilco.univ-littoral.fr', '$2y$12$LAdXxKMMcCX0L9gED/TtXO.qhg4FZsniULPaa5PQK9Q.m2KHVzlYy', 1),
(42, 'Lapôtre', 'Marylou', 'etu', 'marylou.lapotre@etu.eilco.univ-littoral.fr', '$2y$12$ryZpel7p0UIGb7jfUOE8TOOIOtzly61tLUVZpX0IzpYPb9TbViPiq', 1),
(43, 'Ampen', 'Noah ', 'ING2FISEA', 'noah.ampen@etu.eilco.univ-littoral.fr', '$2y$12$Ai9vGsrLN.sCXOYOObD4peL1cNP6CUGOzUXlc5t.8z0Dm.LsatAO2', 1),
(44, 'CIOTONEA', 'Carmen', 'Tuteur', 'carmen.ciotonea@eilco.univ-littoral.fr', '$2y$12$J53Z75Cd8EEtjftO6VLc/eQ8ZGTgWJWWc.aqC0Bu/s6SycU4PSHhC', 2),
(45, 'COSSE', 'Augustin', 'Tuteur', 'augustin.cosse@univ-littoral.fr', '$2y$12$MyqThJ09iL1QZJcUM2poxuKYzIBpAQt2H2uotFAyOG6TUgB4nUi4a', 2),
(46, 'DOUCHAIN', 'Vincent', 'Tuteur', 'vincent.douchain@univ-littoral.fr', '$2y$12$r.mZvtIgkajLIpQJGI35detEhHp9ZzNQrWMOWe/ZfLPVWD6Qi87Tu', 2),
(47, 'FERCHICHI', 'Khaoula', 'Tuteur', 'khaoula.ferchichi@eilco.univ-littoral.fr', '$2y$12$cC9HdJXswlqGOn7xAAGmgeKF0DmeSUSjjNmOAtzsc0TtDMHqJcFmm', 2),
(48, 'FORTUNI', 'Gino', 'Tuteur', 'gino.fortuni@univ-littoral.fr', '$2y$12$TBf96Fk/f6RhsU7Yq2IEJemx/dL6mURFmnIa7BBADFbfyY2pviUju', 2),
(49, 'GIROIRE', 'Vincent', 'Tuteur', 'vincent.giroire@wanadoo.fr', '$2y$12$YVuGJGC4W3vGL4e5GeoZFeHvqmpRYUkTL00Tn/5KdnIjmSegqVLme', 2),
(50, 'HEBERT', 'Pierre-Alexandre', 'Tuteur', 'pierre-alexandre.hebert@univ-littoral.fr', '$2y$12$2o/Z8fbsByEenERS5QvLKeFNFVxY6Dr11ynQhcgGU9LdHSDSuZou6', 2),
(51, 'MASCOT', 'Manuel', 'Tuteur', 'manuel.mascot@eilco.univ-littoral.fr', '$2y$12$Yig7ePPaY1PkvPcrQ9DR2u7szuHB6BnTH/D/53cbT5e7UCz6nFLzu', 2),
(52, 'PODVIN', 'Aurélien', 'Tuteur', 'aurelien.podvin@eilco.univ-littoral.fr', '$2y$12$uAcJ7EQ1IxWkvAV3Y8PBvuTT5Zl5100aJZBD1UqbQBJL5p/QC6MQ6', 2),
(53, 'WALDHOFF', 'Nicolas', 'Tuteur', 'nicolas.waldhoff@eilco.univ-littoral.fr', '$2y$12$tw.9NM6wxjoNGJYq768qhO6W/4azgqNT/qRDQ7RsuWMTfgrKhqAAa', 2),
(54, 'Elattari', 'Kaouthar', 'ING2FISE', 'kaouthar.elattari@etu.eilco.univ-littoral.fr', '$2y$12$OO9esam2f.BnhdxmgtcGk.jQ0ms..YZhX1mIhOT6DW4P3GlBKny4S', 1),
(55, 'Landry', 'Rayann', 'ING2FISE', 'rayann.landry@etu.eilco.univ-littoral.fr', '$2y$12$bdQ0AJbjF2WdrsUdBdOcN.zkvwd.thbVhsAVhHPrGp625UrMQABFO', 1),
(56, 'Calmels', 'Nathan', 'ING2FISE', 'nathan.calmels@etu.eilco.univ-littoral.fr', '$2y$12$rGhgJBWK7LPXBsm4JGrB6e7YFr64VXtdyrqO9XBUevyoxEMPQ7NOy', 1),
(57, 'Roukbane', 'Akram Souhail', 'ING2FISE', 'akram-souhail.roukbane@etu.eilco.univ-littoral.fr', '$2y$12$zq/4qVZRVovvQ2HOLpgzBOp6sSS.APFJXo/B7KMCSOWhIu3ucblpi', 1),
(58, 'ALLAIRE', 'THOMAS', 'ING2FISEA', 'thomas.allaire@etu.eilco.univ-littoral.fr', '$2y$12$x5VfH6JyvikHmEEVK6LX4OjStOiIr.09XFMmw6G/NPhsjU3fy8W8y', 1),
(59, 'DURIEZ', 'Eva', 'ING2FISE', 'eva.duriez@etu.eilco.univ-littoral.fr', '$2y$12$/6xMjBOM8/mPV8eeHq6ER.NPx0cJvtrubXto.0RYtVnY.wDjIt9w2', 3),
(60, 'Fontaine', 'Clément', 'ING2FISE', 'clement.fontaine001@etu.eilco.univ-littoral.fr', '$2y$12$6InFjb8kBIrUgJrjMLZlZuMjsewMZkXxmhIwcqSJR/eyS4ceK2hRW', 1),
(61, 'Banco', 'Kelian', 'ING2FISE', 'kelian.banco@etu.eilco.univ-littoral.fr', '$2y$12$wMH2F83.SXJvdZjZESTAu.p3P4EqA1GqxLpqg130og7kXBdwlNmwW', 1),
(62, 'Lehir', 'Cylien', 'ING2FISE', 'cylien.lehir@etu.eilco.univ-littoral.fr', '$2y$12$J.20nQNmK/slQz2A8GM2nOziz8x3O2xTcRjFZq8tXxianD1I2kiRy', 1),
(63, 'Lahouir', 'Mehdi', 'etu', 'mehdi.lahouir@etu.eilco.univ-littoral.fr', '$2y$12$jwG95EatW3m/ajNFhPIBeujhXjYJfdxnCOs4W3O9mDmgxALnTM3P.', 1),
(64, 'Brun', 'Guillaume', 'ING2FISE', 'guillaume.brun@etu.eilco.univ-littoral.fr', '$2y$12$zDwH4naFDkqM8EJwFkybSO0.jgCfzo06qSrs3uKoAEo2rpnBFnXpu', 1),
(65, 'Benmiloud', 'Ismail', 'etu', 'ismail.benmiloud@etu.eilco.univ-littoral.fr', '$2y$12$7KaarjHv.ou92bOuzyfp.OAEoY425J5T9HfaTPd4ZolOxlgU4k2Ei', 1),
(66, 'Lefetz', 'Chloé', 'etu', 'chloe.lefetz@etu.eilco.univ-littoral.fr', '$2y$12$ej9HJIZcU0V3VZm1F.IMtOwchwPxEXuRiN6yVj7xuMCevranNfFjW', 1),
(67, 'Delaux', 'Clarence', 'etu', 'clarence.delaux@etu.eilco.univ-littoral.fr', '$2y$12$W.zvMSiWBbO7tCg9wzPkr.zkMNDukTfhr7O4MXvsTt3pJUxLuewmG', 1),
(68, 'TASSA', 'Tchandikou puduo', 'ING2FISEA', 'Tchandikou-Pudu.Tassa@etu.eilco.univ-littoral.fr', '$2y$12$1GhzJavrUXF7msKB9lVRpOi94NJJCpquzK.gA6eTwvzJ44W6pivgq', 1),
(69, 'Daubercourt', 'Mattéo', 'ING2FISE', 'matteo.daubercourt@etu.eilco.univ-littoral.fr', '$2y$12$03e4yYfYi9OY48zP/Vapse.bhz8.Bgo.I9H4M8S/7vxqZHLtJZU1e', 1),
(70, 'PLAETEVOET', 'Sébastien', 'ING2FISE', 'sebastien.plaetevoet@etu.eilco.univ-littoral.fr', '$2y$12$/N8t9/Ek5cjGGZ1iB/MCfu7vSeCI8iHbP73lkRZb93wv04.8j/dDK', 1),
(71, 'CHAPPELON', 'Clément', 'ING2FISE', 'Clement.Chappelon@etu.eilco.univ-littoral.fr', '$2y$12$tr8LxTOg8tKJpWbOLd7HIub3.q/hReWOj5eHeGXsG2.WDAPiPK23K', 1),
(72, 'Ismaili', 'Oussama', 'ING2FISE', 'oussama.ismaili@etu.eilco.univ-littoral.fr', '$2y$12$mEyxOy6UcScZmUaE6sbLUe2CaUA1Z8EyNryuC2xcO3T0mLAbbbyem', 1),
(73, 'Mhanni', 'Wassima', 'ING2FISE', 'wassima.mhanni@etu.eilco.univ-littoral.fr', '$2y$12$S4PpDjYRV4oBdLHzcLsRQeWFocUnZHS7U3q7xybZmcx/MpiVFoCEW', 1),
(74, 'MIEZE', 'Pierre', 'etu', 'p.mieze@etu.eilco.univ-littoral.fr', '$2y$12$vqbwR5N8FAIDAkA768yK0.zkW4H7NMaLZSWdJsDb0pRwYqgA4Edqu', 1),
(75, 'KELLA BENNANI', 'Othmane', 'ING2FISE', 'Othmane.Kella-Bennani@etu.eilco.univ-littoral.fr', '$2y$12$lqMRtQJN12UNo0vM2lP8uuMV06UDOY8cXtI/5omaGSBHe2MQeYCtW', 1),
(76, 'EL KHANTACHE', 'Saif-Eddine', 'ING2FISE', 'saif-eddine.el-khantache@etu.eilco.univ-littoral.fr', '$2y$12$vPn6s1zyDPk3k9e7ykXljeVbtTCB6cs936J5omGxkJwSq2rLeStEi', 1),
(77, 'Nowakowski', 'Basile', 'etu', 'basile.nowakowski@etu.eilco.univ-littoral.fr', '$2y$12$A7a3lKxUdtzeK7cAjRbQNuaE3GzpBkY.IUJEOhKC8duMTyxabyr3O', 1),
(78, 'Louys', 'Noé', 'etu', 'Noe.Louys@etu.eilco.univ-littoral.fr', '$2y$12$Pe1oSij6OWS55ronZ5qmS.lQYhnpagMDoAqGMeM8yzPuaDfgovX0y', 1),
(79, 'Maillet', 'Paul', 'etu', 'Paul.Maillet@etu.eilco.univ-littoral.fr', '$2y$12$XsRoZk4Tx9gYu/xpVCkUU.xsIDTuTvtd/Z8j71x1ZWNsJAMLgp4a6', 1),
(80, 'Sahnoun', 'Hanae', 'ING2FISE', 'hanae.sahnoun@etu.eilco.univ-littoral.fr', '$2y$12$7gm2mryDmMpJTfF97f4IdO6/TSqwVH3Av6cSyNMnE5A1PK74CrQNW', 1),
(81, 'Deffontaine', 'Nathan', 'ING2FISE', 'nathan.deffontaine@etu.eilco.univ-littoral.fr', '$2y$12$oGWKFDGhKibi7B6mzEJu.ucKNUbJFfZAQ8oFaevqITWkT62E1Uc7i', 1),
(82, 'Chikh', 'Yasmine', 'ING2FISE', 'yasmine.chikh@etu.eilco.univ-littoral.fr', '$2y$12$JNkWU197guDYRb8RX0bxLO54jH/DJOXH8XBG4njQRL5uZgYL1N3/O', 1),
(83, 'Guillin', 'Stylian', 'ING2FISE', 'stylian.guillin@etu.eilco.univ-littoral.fr', '$2y$12$eNjSD7QppSSavanVjXZMouOH/vrdNlCgTuJuVeD084ro2tlXVdm9G', 1),
(84, 'Ben chaabene', 'Nourhene', 'ING2FISE', 'nourhene.ben-chaabene@etu.eilco.univ-littoral.fr', '$2y$12$e0jF3XMVFC40Y7r6GgVAqu5SPKhpDqUF2Uh07LP.GrS72OJ4QrLWu', 1),
(85, 'ZAOUI', 'Anass', 'ING2FISE', 'anass.zaoui@etu.eilco.univ-littoral.fr', '$2y$12$RcrjHyw/y2YmIN3o4j6m.exV4HDATtTIGf4ELoZDn.cpXuOMQTfSG', 1),
(86, 'KHAIDER', 'Brahim', 'ING2FISE', 'brahim.khaider@etu.eilco.univ-littoral.fr', '$2y$12$a9fZvdvDJJMvoFtrUuAgCOofDGx8E5qVVTYElXkaWb2A4QOQfFatq', 1),
(87, 'Talhaoui', 'Inass', 'ING2FISE', 'Inass.Talhaoui@etu.eilco.univ-littoral.fr', '$2y$12$6sag.mxsLy2g2eXyR5e5NeI50qDjIGwhACL304sD0SCMYFQAO5m7u', 1),
(88, 'Leturque', 'Lucas', 'etu', 'lucas.leturque@etu.eilco.univ-littoral.fr', '$2y$12$j5zbEXJCKf8/K6fmJx027.4rW/lhWy9dV.FUnhFJnEcengZcC2wve', 1),
(89, 'PONCIN', 'GHISLAIN', 'Tuteur', 'ghislain.poncin@eilco.univ-littoral.fr', '$2y$12$zbUliluXgD0v.pYYULOWo.NsQ0aof3WK6AbQoA9IN10hy3e0Ormhu', 2),
(90, 'EL ARGOUBI', 'EL MEHDI', 'etu', 'el-mehdi.el-argoubi@etu.eilco.univ-littoral.fr', '$2y$12$HRyX/.yvHepiz5bQH9/K5OVsCNNLKfq7WP.wty.Rubtt/etoEKTmi', 1),
(91, 'Krasnowolski', 'Damien', 'ING2FISEA', 'Damien.Krasnowolski@etu.eilco.univ-littoral.fr', '$2y$12$DMINpJwe1T1TFhjaSFgmKOIQF5uFDJDUZ3VdSbhdDcSKcFxU7psDa', 1),
(92, 'RAKNI', 'Ayoub', 'ING2FISE', 'ayoub.rakni@etu.eilco.univ-littoral.fr', '$2y$12$0ivPjgD.Q9cvNyYfLFFFDOkSBQBiPqeKk8VfyCELqJ7v6BIwGBDGq', 1),
(93, 'Lamouadene', 'Samiha', 'ING2FISE', 'samiha.lamouadene@etu.eilco.univ-littoral.fr', '$2y$12$52/QRq8qEyKfMMx9zp2/YOrxOJ84MWRPEblp8UntLFtUPjfh/dC0u', 1),
(94, 'Rahhali', 'Dounia', 'ING2FISE', 'dounia.rahhali@etu.eilco.univ-littoral.fr', '$2y$12$AyyePA2cxccGcjyT5FFqsetQ8ttDshlZtW5n0l8qX1dKuZCPQ6nYi', 1),
(95, 'Lahbal', 'Hiba ', 'ING2FISE', 'hiba.lahbal@etu.eilco.univ-littoral.fr', '$2y$12$17l6GLT8rIshElQbz6OC4.U1Hh6veHOhlEiXMqNPS3QQCaH.KZ3CC', 1),
(96, 'MBUYI KAMANOU ', 'Glorieux Michel ', 'ING2FISE', 'glorieux-michel.mbuyi-kamanou@etu.eilco.univ-littoral.fr', '$2y$12$tV5zdNo7suVWRJ0hYGRwoOTgoKnZaKrPjrvrUQ/H.9tUgntUJlKPu', 1),
(97, 'Selk', 'Youssef', 'ING2FISE', 'youssef.selk@etu.eilco.univ-littoral.fr', '$2y$12$jNnajdOC3QBxQhcAkOE9cO4ufr1/V5cPt6mfLWLxPc6Hg0.ISWKPC', 1),
(98, 'Erahouten ', 'Iliass', 'ING2FISE', 'iliass.erahouten@etu.eilco.univ-littoral.fr', '$2y$12$4K0cmAn.xQDTH6CSND5gve3WkRCIxX9TcdjhXq4YRmF9crqIINVk.', 1),
(99, 'SRAIDI', 'AYMANE', 'ING2FISE', 'aymane.sraidi@etu.eilco.univ-littoral.fr', '$2y$12$vLbSwur6HkG/Hid/s8L2tumuQt1AusOlK7tiXlTU8gO4iGc/01ulq', 1),
(100, 'Dubois', 'Jules', 'ING2FISEA', 'jules.dubois@etu.eilco.univ-littoral.fr', '$2y$12$nvwf0WyclI1sULASE7CoeegEJZfWC0bCjs/tcJsK/sWTzTgcDT/6G', 1),
(101, 'Belkhadir', 'Yassine', 'ING2FISE', 'yassine.belkhadir@etu.eilco.univ-littoral.fr', '$2y$12$.1HV3sJZ1IxrBo35rLo13u7vFA3cZJnZ/.TLEpwMTRZER6ndwsL.m', 1),
(102, 'BELAHCEN ', 'Siham', 'ING2FISE', 'Siham.Belahcen@etu.eilco.univ-littoral.fr', '$2y$12$RjkQ/8FgEoI3vP4330I70OK9R7Fc2bVHLTAXUKbBg8brLc..U46qy', 1),
(103, 'EDDIB', 'Aya', 'ING2FISE', 'aya.eddib@etu.eilco.univ-littoral.fr', '$2y$12$Atk5jEP/h0znEy.WVmQof.YvtjoIt3rnHWi9Mpi5m5C.w6yjxeO1G', 1),
(104, 'Adil', 'Nouhaila', 'ING2FISE', 'nouhaila.adil@etu.eilco.univ-littoral.fr', '$2y$12$v8DVqfNWquaIh/NWoMq17ey6A3pKXmpEIiKNg2dIP6KlshoYj.25i', 1),
(105, 'Belattar', 'Rachid', 'ING2FISE', 'rachid.belattar@etu.eilco.univ-littoral.fr', '$2y$12$0b2METjTm4LcLGD3tjfZCecOLiQj4jmGyeB0QifSTuQuUBy6lAfvK', 1),
(106, 'HADDAD', 'Ilyas', 'ING2FISE', 'Ilyas.Haddad@etu.eilco.univ-littoral.fr', '$2y$12$VpP2rfG02Ox45KaaKSL3zeyXd../UC8kBvU3/31pG5H.GDoK3eMGK', 1),
(107, 'Cardaropoli', 'Kenzo', 'etu', 'Kenzo.Cardaropoli@etu.eilco.univ-littoral.fr', '$2y$12$oFDzEMaROo563m4xeplFu.q9JTuLowvMck7idZnl3hpxjdbMLskB2', 1),
(108, 'EL BOURACHDI', 'Aya', 'ING2FISE', 'aya.el-bourachdi@etu.eilco.univ-littoral.fr', '$2y$12$Tyq2iQj322/OTMzuRFH5R./G.DwA4/qtchdGLQogHd1L1Ipxh1/qO', 1),
(109, 'SIF ADDINE', 'Douaa', 'ING2FISE', 'Douaa.Sif-Addine@etu.eilco.univ-littoral.fr', '$2y$12$EC8xgb1ZYdzb2kS4nJsgHuaP8JHZ2XhYH./qMOaXu/z0gE1Op04t2', 1),
(110, 'zdazi', 'aymane', 'ING2FISE', 'aymane.zdazi@etu.eilco.univ-littoral.fr', '$2y$12$poH0C9Cl.VLeq94sj5jGveyjQbLRbLjesiOBb//BbKNmU1WSx/3qK', 1),
(111, 'TCHUEMBUE', 'Mylene', 'etu', 'mylene.tchuembue@etu.eilco.univ-littoral.fr', '$2y$12$VtHrS/Mqb1.wl.F/Cm.KVuh7I.UrymGV4RcECtmAZosj/n7o4lv/m', 1),
(112, 'GUY-DE-FONTGALLAND', 'Angelo', 'ING2FISE', 'angelo.guy-de-fontgall@etu.eilco.univ-littoral.fr', '$2y$12$9fjG/yRD9o4urwDEYmHiBOAmhIf6zFP4oneKJcDt.8hCyHLUACnJq', 1),
(113, 'ATIVON', 'Kokou Espoir', 'ING2FISE', 'kokou-espoir.ativon@etu.eilco.univ-littoral.fr', '$2y$12$YuKua2o/Ruw9adx.RsZ4ReBvdhwFbjb2i8Qv05yhy6nGGnTNqm28i', 1),
(114, 'Soares', 'Nolan', 'etu', 'nolan.soares@etu.eilco.univ-littoral.fr', '$2y$12$8igZKIBhb51KMl7hLPklEum3ZurQ1fwkJx7TansJzN4ZtvwC/MmW6', 1),
(115, 'MBOUNDOU-ESSONGUE', 'IRMAND GRÂCE', 'ING2FISE', 'irmand-grace.mboundou-essong@etu.eilco.univ-littoral.fr', '$2y$12$ns.TL3DMn22fTta4KPssRuXG90wTw/.sfB1GHvts9dc8VUvPuCvq6', 1),
(116, 'lakhsim', 'ikram', 'ING2FISE', 'Ikram.Lakhsim@etu.eilco.univ-littoral.fr', '$2y$12$rGeKmoM7TIixbga603GfxOjQPMbJO9CNmbOaISnFJ.ITL858bgjhy', 1),
(117, 'Rouine', 'Chaima', 'etu', 'chaima.rouine@etu.eilco.univ-littoral.fr', '$2y$12$/Ou8uNJ3hp8ZKqYoOIuTgeWmlCtTMefn00rslHjwQJ8GSAvRpcLEq', 1),
(118, 'EL-HLAISSI', 'Nada', 'ING2FISE', 'nada.el-hlaissi@etu.eilco.univ-littoral.fr', '$2y$12$8rSYuuZ8/n2WLbrD47uu/O8ihPlW1AsYlY8K29IoEIeG9svuYR1gu', 1),
(119, 'ES-SAHLY', 'Hamza', 'ING2FISE', 'hamza.es-sahly@etu.eilco.univ-littoral.fr', '$2y$12$U9SPYHUFAQnZg92q6oVTJuTJieRSWAgulT4hnnvTWqWC.2/oXVTZ.', 1),
(120, 'Mazri', 'Dina', 'ING2FISE', 'dina.Mazri@etu.eilco.univ-littoral.fr', '$2y$12$4IPUgf1jOaNHyGNQBdNv6OYLL7.goGFnfAR11LuI38Kq454T//QQa', 1),
(121, 'Chakir', 'Achraf', 'ING2FISE', 'achraf.chakir@etu.eilco.univ-littoral.fr', '$2y$12$BOkwr1UZ4/NVdXaqmtWELOjNQXG6XjrJ6eoKaBbTb9BRdMmi1a862', 1),
(122, 'Ressaki', 'Douha', 'ING2FISE', 'douha.ressaki@etu.eilco.univ-littoral.fr', '$2y$12$8YWre3jJaakGW0DM.rGY8.SxSboYE7H.3jQvcTbJ6x0nSq4.ab4QG', 1),
(123, 'BOUDA', 'HAMZA', 'ING2FISE', 'hamza.bouda@etu.eilco.univ-littoral.fr', '$2y$12$pA7xNDKe6airr2RUMgRjPuFwUaiVx0vdKFc96oypr1HPOQFfjtnVK', 1),
(124, 'Afkir', 'Iliass', 'ING2FISE', 'iliass.afkir@etu.eilco.univ-littoral.fr', '$2y$12$b13AezvS/pLJ6hebLOBGiOEckrwR5JjUgHYOhpVv67JM.ooJOKanu', 1),
(125, 'Senhaji haj', 'Idriss', 'ING2FISE', 'Idriss.Senhaji-Haj@etu.eilco.univ-littoral.fr', '$2y$12$pZ5r0ME4ixuEUOF9D2En2e5X/YJlp9i6jKfgvDtX1sNcJnrUuMWK2', 1),
(126, 'Bacar', 'Badroudine', 'ING2FISE', 'badroudine.bacar@etu.eilco.univ-littoral.fr', '$2y$12$2OATZdqVlLBQzmc9xMbfVuwj/vE6yeY4ucoUmwTp32zeLgH4R8pDq', 1),
(127, 'GANGBE', 'Mariella Celsia', 'ING2FISE', 'Bignon-Mariella.Gangbe@etu.eilco.univ-littoral.fr', '$2y$12$bUkw.jEzJIMgNVmjyRoH9OilIaSRuQwe7n9lgTlUvi7TGKSpQujBK', 1),
(128, 'Seyyar', 'Chaimae', 'ING2FISE', 'chaimae.seyyar@etu.eilco.univ-littoral.fr', '$2y$12$QvIo8X/ZC0v2JLBQrXUeBORMvxIZC4CE1f1QjPXVFmgieqBydUzwy', 1),
(129, 'BOUNOUA', 'Aya', 'ING2FISE', 'Aya.Bounoua@etu.eilco.univ-littoral.fr', '$2y$12$0JjfRPFhNznnzXD7ME4s8egWwYzd7PPgoYkJod4BRYNkas7sKa/xe', 1),
(130, 'MESSAOUDI', 'Fatma', 'ING2FISE', 'fatma.messaoudi@etu.eilco.univ-littoral.fr', '$2y$12$zu8EOSbasrda5cS/f29IhuvmiXiKNUXQB/vvbx1l4fXk4dViUAeDe', 1),
(131, 'MARTIN', 'Loic', 'ING2FISEA', 'loic.martin@etu.eilco.univ-littoral.fr', '$2y$12$Uculmm9HKN8KhIJ6.wA/Eeji4L6SwUbUbmiOqnfTlsCoUGTf6giRm', 3),
(132, 'charaf', 'yahya', 'ING2FISE', 'yahya.charaf@etu.eilco.univ-littoral.fr', '$2y$12$k6NuPy1I.TuvEeroDE2iTetThNoeBVoi.avs0DX9vJAu0RLZ7GoRK', 1),
(133, 'SALERA', 'Léo', 'ING2FISEA', 'leo.salera@etu.eilco.univ-littoral.fr', '$2y$12$GlvMu3VGvee9z9ftHhngFe5K7AhYxPcD6dCzHEuZh/wj8LPkfHxAC', 1),
(134, 'OUAHAB', 'ABDERRAHIM', 'ING2FISE', 'abderrahim.ouahab@etu.eilco.univ-littoral.fr', '$2y$12$WN5vp5CXl7GHNplz.FmxY.UUDtAfHU0QplriFUtj/j5QcF6eoUK1.', 1),
(135, 'POOLS', 'Florentin', 'ING2FISE', 'florentin.pools@etu.eilco.univ-littoral.fr', '$2y$12$aucDuJQuo/0EAhr.0AbHDO2TbJpOJndmGkGcGkn2KC9dVavH0TCJS', 3),
(136, 'Sebbane', 'Salma', 'ING2FISE', 'Salma.Sebbane@etu.eilco.univ-littoral.fr', '$2y$12$0e5VBVPeqAGMZhTfqaKXrOuSFZFvyMWhkJRZWRbsGBrVdvq0kNXvS', 1),
(137, 'Tsoul ', 'Hiba', 'ING2FISE', 'hiba.tsoul@etu.eilco.univ-littoral.fr', '$2y$12$kI3bynaPrwllHAUxHlx8DeRC2sojHdoqiUtp71IIOcir2jn7m/Inm', 1),
(138, 'CHATELAIN', 'PIERRE', 'Tuteur', 'pierre.chatelain@univ-littoral.fr', '$2y$12$30jlrcX58cZ0p/TKED28XuE8EDKOPaXIzFbDyFXmkVPhoqQst0Y9W', 2),
(139, 'morelle', 'NGOUEMETA', 'ING2FISE', 'Morelle.Ngouemeta-Tchin@etu.eilco.univ-littoral.fr', '$2y$12$AnDCy.xJydf/veDEPJuYBeNH0YJetY3VmiVtfvMN0WAmfj1RzQhIC', 1),
(140, 'ASSANI-BENTHO', 'Mohamed', 'ING2FISE', 'Mohamed-Youssou.Assani-Bentho@etu.eilco.univ-littoral.fr', '$2y$12$ED3gt6eJRjkycs1ztTeZwOepyN8uOZvLwXpHCq002b5Hx33Ci823S', 1),
(141, 'GODONOU', 'Prince Arsène ', 'ING2FISE', 'prince-arsene.godonou@etu.eilco.univ-littoral.fr', '$2y$12$Nvvb2EKQch42QMIGNJ9ADORvs3eiHezJgsIUbhueXNyWjdiuQL7IO', 1),
(142, 'Wankida', 'Walid', 'ING2FISE', 'Walid.Wankida@etu.eilco.univ-littoral.fr', '$2y$12$gc9YGcNbYeZOEt3SMBx2SuheYiFWKj/Cv19SBxBY8zRfRKYEov1.O', 1),
(143, 'RAKOTOMAHEFA', 'Haja Fitiavana', 'ING2FISE', 'Haja-Fitiavana.Rakotomahefa@etu.eilco.univ-littoral.fr', '$2y$12$rHNk5.RHBwkrT3gFm0BLse19HKRwtxGD57IY.0V7phzl3FlFrhEby', 1),
(144, 'AZZOUZI', 'MOHAMED', 'ING2FISE', 'mohamed.azzouzi@etu.eilco.univ-littoral.fr', '$2y$12$OkxcsTxZ6uEuzF7NNalL4ujnIX8pbmVAwtU7QnzvXNM6L8TVewo7W', 1),
(145, 'rhazzar', 'charaf eddine', 'ING2FISE', 'charaf-eddine.rhazzar@etu.eilco.univ-littoral.fr', '$2y$12$9Ah8US65nQ2hsNPf99z8YePMU6l9fnZFoWydF89n09amWBJVcvObO', 1),
(146, 'EL FADIL', 'Sami', 'ING2FISE', 'sami.el-fadil@etu.eilco.univ-littoral.fr', '$2y$12$qQ7YbfWsWgjANro.NOelSudDN0M6.59R4PSiUb1zfdMV2o5RKLJP6', 1),
(147, 'FERJANE', 'MOHAMMED AMINE', 'ING2FISE', 'mohammed-amine.ferjane@etu.eilco.univ-littoral.fr', '$2y$12$tJlhdx7BQlgfWTGTNTR8uOS09uxGULWS9yDkWvatdtNjXI4hX2Z6.', 1),
(148, 'Bariz', 'Ayman', 'ING2FISE', 'ayman.bariz@etu.eilco.univ-littoral.fr', '$2y$12$/ZndNkK6Ihutqm45BF6hOewZhyiR12zNy6xLVb.RlfXAabBNPXCUG', 1),
(149, 'El Gaamouch', 'Amin', 'ING2FISE', 'amin.el-gaamouch@etu.eilco.univ-littoral.fr', '$2y$12$t8U5BL7pXYyhc1tzZgC7RugehdNj1DzS.v5kTor5wG3wKLRlsa6Vm', 1),
(150, 'Stevenart', 'Julien', 'etu', 'julien.stevenart@etu.eilco.univ-littoral.fr', '$2y$12$6efp0fLvLbfjdRWtUyHliupHd4bssVH8n0IEr3/jnrH4aP6Eq7Qpy', 1),
(151, 'Karam', 'Imane', 'etu', 'imane.karam@etu.eilco.univ-littoral.fr', '$2y$12$9q28kvgVZl/QMv0/jF4Bf.EDsDt9UF0Bqu8eI2uOySdjrRFTkn2wu', 1),
(152, 'Duquenoy', 'Maëlle', 'ING2FISE', 'maelle.duquenoy@etu.eilco.univ-littoral.fr', '$2y$12$E2vYdXG6DNFiGA.u/MyPTO2xPoqc9pf8KcOUPLPoekR4/JQr61X8S', 1),
(153, 'Delohen', 'Marie', 'ING2FISE', 'marie.delohen@etu.eilco.univ-littoral.fr', '$2y$12$73IDIHC2goUDhY68xh6Zeu9Ug3jY.W.aeP4OYTXCOX4BFAMv/NMI2', 1),
(154, 'ECH-CHAFIY', 'Fadwa', 'ING2FISEA', 'fadwa.ech-chafiy@etu.eilco.univ-littoral.fr', '$2y$12$n1L9B9mYEgJDdOQ1XQPy4.7lbhUUT7iZu1yfOrwfnomY2Cblb8eK6', 1),
(157, 'Elhassani', 'Mohamed', 'ING2FISE', 'mohamed.elhassani@etu.eilco.univ-littoral.fr', '$2y$12$3ZyYa4EbTjQKtjHkPuOT/edSOJVfvoII6DJWRyLmHuMdGUuLTXWIG', 1),
(158, 'Duret', 'Eryne', 'etu', 'eryne.duret@etu.eilco.univ-littoral.fr', '$2y$12$261JgAdFpDVLbpBL7eiUsOh/uarx8JYBwpFVzsyQ63I8cIbuKM3ze', 1),
(159, 'Hajji', 'Lamia', 'ING2FISE', 'Lamia.Hajji@etu.eilco.univ-littoral.fr', '$2y$12$34lmprJIttfUAxSy6qFwaOOzipvesnrhE7SDq4MO8IvYmocozP8Yu', 1),
(160, 'MOUNKORO', 'Hamadoun', 'ING2FISE', 'hamadoun.mounkoro@etu.eilco.univ-littoral.fr', '$2y$12$abMJ/BYZL8D27OqRxmJZsO5uHQ/gHcV899iYr0YQ6PcYR5tlyjKvy', 1),
(161, 'LAMRAOUI', 'Wissal', 'ING2FISE', 'wissal.lamraoui@etu.eilco.univ-littoral.fr', '$2y$12$snB5qDHXeuOrqAKfrLm67uALlab3ZA3SXd9mXjnl7oRzdfCo7zEwa', 1),
(162, 'Aida', 'Nisrine', 'ING2FISE', 'nisrine.aida@etu.eilco.univ-littoral.fr', '$2y$12$nLdxAQexmT2kt3kWpibOiuiP.qy/QMFhMnFTj8BMzHFKc.N57Hqwq', 1),
(163, 'Ben-Dahmane', 'Mohammed', 'ING2FISE', 'mohammed.ben-dahmane@etu.eilco.univ-littoral.fr', '$2y$12$XL/9Owqlgvd.lYPpxxWZJerMtn3.2J/gVIAQxDQryDhVPEFhcEEAq', 1),
(164, 'El-Abzizi', 'Imane', 'ING2FISE', 'imane.el-abzizi@etu.eilco.univ-littoral.fr', '$2y$12$wcKS8t/0aw3rFjWZoaCb2OpUqc0SbwaQN1OzGZ.TfkLc4TXgAPFJe', 1),
(165, 'NOURI', 'Ahlam', 'ING2FISE', 'Ahlam.Nouri@etu.eilco.univ-littoral.fr', '$2y$12$w0XHz30kA0BpA1NTOcYlreflZoXyaDR9F2pV7SJlhssZiT.L4BJMW', 1),
(166, 'BENAOUI', 'CHAIMAE', 'ING2FISE', 'chaimae.benaoui@etu.eilco.univ-littoral.fr', '$2y$12$AQWB9OoceMFktI22KObqIuYjQLbWqn8LwK10tLLQd/EaSczjsKvEu', 1),
(167, 'ECHALI', 'HIBA', 'ING2FISE', 'hiba.echali@etu.eilco.univ-littoral.fr', '$2y$12$tYC0YMAYzPc2RjsfqbMWpeJPi4.C.ObdkmDfO5iKQQlp28XbkipuS', 1),
(168, 'Roquigny', 'Roxane', 'CP1', 'roxane.roquigny@univ-littoral.fr', '$2y$12$x6oYwMUgW1q5dgMLh8jXg.N254s09eoFcLwjWHEW76muCgM9.tK9S', 2),
(169, 'ASSOGBA', 'Kossi Epiphane Steven', 'ING2FISE', 'k-e.assogba@etu.eilco.univ-littoral.fr', '$2y$12$I69jE0WefVXbOF61AAxPauEo2cpN1hA/PUqzkL/kcYqgXbDSKrBvm', 1),
(170, 'BELAHCEN', 'Amine', 'ING2FISE', 'amine.belahcen@etu.eilco.univ-littoral.fr', '$2y$12$5mgHpnqR.Okrhr27f1wiA.o8wPUK6.UhXQoVLA43AF.z/ym7Jqsii', 1),
(171, 'EDDAOUDI', 'MOHAMED', 'ING2FISE', 'm.eddaoudi@etu.eilco.univ-littoral.fr', '$2y$12$u/FcqMuEJWciSjhavrmk7OH8Sdv1PRhthQ5.YoLwXw2JDLQCLPfhm', 1),
(172, 'Sokoudjou Fotsing ', 'Patrick', 'ING2FISE', 'Patrick.Sokoudjou-Fotsi@etu.eilco.univ-littoral.fr', '$2y$12$Z2mgbN0ugJOswFR4ZY6DV.cpiRkS/NN3YTOvmeabTm3McQzbTCfmu', 1),
(173, 'TOUJANE', 'MARIAM', 'ING2FISE', 'mariam.toujane@etu.eilco.univ-littoral.fr', '$2y$12$hstlQZv9lSXtKq2Rk5H2eOwX1K0kBDkY8DfBtTkyT9PFtUgtqhQtS', 1),
(174, 'Christian', 'Lonla', 'ING2FISE', 'christian.lonla-tchinda@etu.eilco.univ-littoral.fr', '$2y$12$TxWAQ1cPeNX7LpG0OT2RqOsDitHbFNN/Wndr0SqA5K/3D2JC38v1W', 1),
(175, 'AIT SY', 'Hasna', 'ING2FISE', 'hasna.ait-sy@etu.eilco.univ-littoral.fr', '$2y$12$UWzVimeYmwtauashyhn/QeNrc6kpQMkyQxeLNqPb8pKhQrAam.Me.', 1),
(176, 'EL HAROUNI ', 'Imad', 'ING2FISE', 'imad.el-harouni@etu.eilco.univ-littoral.fr', '$2y$12$hnHDCWVGA2Oiznk7pYvSJu5MMbfIXFCp/E.LZXZcmrZ4keC0J.dU6', 1),
(177, 'HOUNGBEDJI ', 'Lauriana ', 'ING2FISE', 'lauriana.houngbedji@etu.eilco.univ-littoral.fr', '$2y$12$B.82Z8CBOyoP8deMWJKMm.0AyvrNIGPOpvxNAOHqYAT/GofzARAfm', 1),
(178, 'NDJENGUE NDJODO ', 'Dan Mathurin ', 'etu', 'dan-mathurin.ndjengue-ndjodo@etu.eilco.univ-littoral.fr', '$2y$12$ZEL/TvFGR9UDBnX3myoXY.atEoy3QMYvJgmnFyt4MHNd7d2ZHt5/m', 1),
(179, 'Nimoho', 'Lenzo', 'etu', 'lenzo.nimoho@etu.eilco.univ-littoral.fr', '$2y$12$jNiZcNVEack4Bcrd1UqWFu4EWhF5j42yHzaYBnKY2j0MCS7yBS91m', 1),
(180, 'CHAKIR', 'Assia', 'ING2FISE', 'assia.chakir@etu.eilco.univ-littoral.fr', '$2y$12$rGKy131KosD1R9VaTKxYJuiUTY2fgVimG3JmHiDlsDkKuuvDAPhc6', 1),
(181, 'Errahimi', 'Adam', 'ING2FISE', 'adam.errahimi@etu.eilco.univ-littoral.fr', '$2y$12$Pq.EqpfupCjyIKR5C6ri9OO2kWTPj0xuwZgc8HZQhkhLTOkmmXGka', 1),
(182, 'TAHA', 'Fatima', 'ING2FISE', 'fatima.taha@etu.eilco.univ-littoral.fr', '$2y$12$eCm8o5c1NbT/5vbYhP/IWOtgXSKJtcl0gYgQzi/FxrMk8IygFJCLC', 1),
(183, 'DAKHCHICH', 'Hanae', 'ING2FISE', 'hanae.dakhchich@etu.eilco.univ-littoral.fr', '$2y$12$lUNorqedzEuf2730OREnLe0CozxnNtVHE/ka1tNAd7Ir5NT9Bniee', 1),
(184, 'LEMAITRE', 'Tafita', 'etu', 'tafita.steeven@etu.eilco.univ-littoral.fr', '$2y$12$W/AOCBSDR5E9bbWQfOdYouL40y.dGGyVkW62uhiE.6u74O84FAFNi', 1),
(185, 'Kabil', 'Oumaima', 'ING2FISE', 'oumaima.kabil@etu.eilco.univ-littoral.fr', '$2y$12$VH26IGa1TXOKjfwsSIGXXOFf0lrssQIQPTSPOgvlM0fWnXdQUNNVq', 1),
(186, 'EL AMRY', 'Nissrine', 'ING2FISE', 'nissrine.sraghna@etu.eilco.univ-littoral.fr', '$2y$12$3F2YIGKOE7UAKqy4ZqgVL.l2rEX6uYN2DFBRFHt6fBB.jrjA1herq', 1),
(187, 'Elamrani', 'aya', 'ING2FISE', 'Aya.El-Amrani@etu.eilco.univ-littoral.fr', '$2y$12$WK647jdiPNqrH23joL9Nlu1huZnz00kYY4331uLzecT.HHDPFDykO', 1),
(188, 'EL HATIMY', 'Ikrame', 'ING2FISE', 'Ikrame.El-Hatimy@etu.eilco.univ-littoral.fr', '$2y$12$kAEq7bGeOgz9zjucWu20ZurMvxtPNGoJYPTZV7BS0r3k2mUbpTyHS', 1),
(189, 'Aouf', 'Mohamed Ameur', 'etu', 'mohamed-ameur.aouf@etu.eilco.univ-littoral.fr', '$2y$12$UK6tIGjR2nJMGD.ELmaJJei6FvCfMjnxnjVR.sHW3ks6KlvhfPlAy', 1),
(190, 'Delaunay ', 'Arthur', 'etu', 'arthur.delaunay@etu.eilco.univ-littoral.fr', '$2y$12$b0jS.K1X5mb0GFlvDBVpXep8bqLra4xsiFbgnMiYtE33FGFkX4OwS', 1),
(191, 'Nefzaoui', 'Abdelmajid', 'etu', 'abdelmajid.nefzaoui@etu.eilco.univ-littoral.fr', '$2y$12$AHr3LUAjJTt2z7kT8Sf7uusIwOfUPaShN6RlAJLUw1666D5s3qKzu', 1),
(192, 'Amrouni', 'Meissam', 'etu', 'meissam.amrouni@etu.eilco.univ-littoral.fr', '$2y$12$5bMjKR5WMaVjUyW5lj4xrOnb0w0qnP2yhDCAnOQ2zuz1P9brnYPVi', 1),
(193, 'NANCHI NDASSOHO', 'Ivan Junior', 'etu', 'ivan-junior.nanchi-ndassoho@etu.eilco.univ-littoral.fr', '$2y$12$kZd31fXib1E0DsmNeTrXGOWkssyfuQcZRPvJyIV5sQMjHZRXCYdKu', 1),
(194, 'el mostaqim', 'ilyas', 'ING2FISE', 'ilyas.el-mostaqim@etu.eilco.univ-littoral.fr', '$2y$12$BKmDPHEvvG34luwNJ.XH4ebVZZfx1kua/Fiuwi5ATOac1EsnvoCZW', 1),
(195, 'KASMI', 'ABDERRAZAK', 'ING2FISE', 'abderrazak.kasmi@etu.eilco.univ-littoral.fr', '$2y$12$HShkmtVcZZUipCeJRa.Dw.Z/WLdoXTIRworT2U8zImeHxz6zyu0IG', 1),
(196, 'Es-saghiri', 'Abdessamad', 'ING2FISE', 'abdessamad.es-saghiri@etu.eilco.univ-littoral.fr', '$2y$12$P0JNAXhGsJl88CrHVvEAr.UhPQ30rDyVgF786ZmNJHcn6vxT.fxQa', 1),
(197, 'EL-HJOUJY', 'Badia', 'ING2FISE', 'badia.el-hjoujy@etu.eilco.univ-littoral.fr', '$2y$12$KA77q9MPLWSAeOB5dI5YOOsgZZ8LfBsfSH7LkU.KtZn.jdI3pHF0S', 1),
(198, 'Chouf', 'Haytam', 'ING2FISE', 'haytam.chouf@etu.eilco.univ-littoral.fr', '$2y$12$8qghd2Qajw5K0pjeMwqiVejWOJ8aO/fvbrIF4AZGOhvQJ0yF8iSVu', 1),
(199, 'EL ATBAOUI', 'AYMAN', 'ING2FISE', 'ayman.el-atbaoui@etu.eilco.univ-littoral.fr', '$2y$12$hMOzv/lbBnsPJPiA8WZPkemWfUxEyW24ak12Z2e7WUNUeD6km7DaS', 1),
(200, 'Louizat', 'Bader', 'ING2FISE', 'bader.louizat@etu.eilco.univ-littoral.fr', '$2y$12$1pgkmOkWSPEbVfhEh.pSQutP4RB7Mvsy1A/vZPSHMOl/fLzqfEdKu', 1),
(201, 'El makni', 'Mohamed ghali', 'ING2FISE', 'Mohamed-Ghali.El-Makni@etu.eilco.univ-littoral.fr', '$2y$12$rpzqfSl7VNcNP4w4pyDl1u58dPD4tgh/vB88vbgPY6fm1dgxshSta', 1),
(202, 'EL QARAOUI', 'Salma', 'ING2FISE', 'salma.el-qaraoui@etu.eilco.univ-littoral.fr', '$2y$12$v4jCDPADAliU6IIeLDBrGuduU8QXqLOGVdTvU./KOtyKjMXKC00AS', 1),
(203, 'lahmam', 'nouhaila', 'ING2FISE', 'nouhaila.lahmam@etu.eilco.univ-littoral.fr', '$2y$12$NpTjTm7GDatFpgexYVPrh.FXjphpJPuZROK/FmtsM1ju.KoYIYu5G', 1),
(204, 'Bakrim', 'Hiba', 'etu', 'hiba.bakrim@etu.eilco.univ-littoral.fr', '$2y$12$m3ZF4i4dsxjWmr/e2wgmbuz2bfIKEtYmUVipiZo3Y9VLTXp4Rqtya', 1),
(205, 'chraiti', 'maryam', 'ING2FISE', 'maryam.chraiti@etu.eilco.univ-littoral.fr', '$2y$12$I1xiy6eECdQsWbvkFmv78.fp6TMt.Yf89y3Fl.5Cr54elKplVOwv2', 1),
(206, 'soufiani', 'zaynab', 'ING2FISE', 'zaynab.soufiani@etu.eilco.univ-littoral.fr', '$2y$12$eL7bv03jzEYOZ2ZG.HP.WODZ08KExogtaMnI3VQjHvd8cFg0WKYSK', 1),
(207, 'OUALLAM', 'ISMAIL', 'ING2FISE', 'ismail.ouallam@etu.eilco.univ-littoral.fr', '$2y$12$p6jFIqzL3/NKb19MxKOpKOZSky6RD7Fiwtx0bvej7ucvUA9.wEi72', 1),
(208, 'CHABANI', 'Ghofrane', 'ING2FISE', 'ghofrane.chabani@etu.eilco.univ-littoral.fr', '$2y$12$eii/pPh0PsoW8e34hJbOjOPqnRjTvopq5wS.Urtn89HitjsD7.Ud2', 1),
(209, 'Mouzoun', 'Ahlam', 'ING2FISE', 'Ahlam.Mouzoun@etu.eilco.univ-littoral.fr', '$2y$12$6k6BHwXQFbuYz6Xfu57i0OUMSu5IgIMiJyhg6qEJOOjoe8P7o98Ti', 1),
(210, 'El wahabi', 'Zineb', 'ING2FISE', 'Zineb.El-Wahabi@etu.eilco.univ-littoral.fr', '$2y$12$PHMtvpCWcJwmbUhtPkk9JeOZRvppj/zZ.uYsgZELBBmgSZYfBgohm', 1),
(211, 'RANDRIAMBAZAHA', 'Tafitasoa', 'etu', 'tafitasoa-ianja.randriambazaha@etu.eilco.univ-littoral.fr', '$2y$12$8F6qDkGT//QCKPM2qZ8XD.PQxjRg/u6mLrZUIUxByjoWx2HyKLesG', 1),
(212, 'Halloul', 'Meriem', 'ING2FISE', 'meriem.halloul@etu.eilco.univ-littoral.fr', '$2y$12$c9BjZl.lXOZh5U3OtdndIe6DqTmx4Khy5iqZK2F08ItictwVoCYd.', 1),
(213, 'Bakhouch ', 'Assya', 'ING2FISE', 'assya.bakhouch@etu.eilco.univ-littoral.fr', '$2y$12$F62giv.vLEY6HFmNayApeu3pagZ1Mz4NTvXscBt7yLTjslFZlNKUK', 1),
(214, 'Diakhate', 'Serigne Saliou Mbacke', 'ING2FISE', 'serigne-saliou-.diakhate@etu.eilco.univ-littoral.fr', '$2y$12$wzn0RNg0lc1vQDA8Mqbg8OZJws6ySgld8Qnat6RHd0jtYnNMGe/2C', 1),
(215, 'EL-HANNADI', 'HOSSAM', 'ING2FISE', 'Hossam.El-Hannadi@etu.eilco.univ-littoral.fr', '$2y$12$b6rEXuantu2xii8yWj22keQ5gD66xI7y5A7QAszCF8MuPPnMvBEyi', 1),
(216, 'Younes', 'mohamed abdelhadi', 'ING2FISE', 'mohamed-abdelha.younes@etu.eilco.univ-littoral.fr', '$2y$12$ptDqcLAx7el/c0fuosmUbeA4XSYn1QK9XM900Au/MVqO7EhHfJWzu', 1),
(217, 'amayas', 'khacer', 'ING2FISE', 'amayas.khacer@etu.eilco.univ-littoral.fr', '$2y$12$nCnr2LPoVZkEmEsgWN8t7.7DUx2tnlaEq/LQToBCmri2ysUBpzlSq', 1),
(218, 'Foukhar', 'Hiba', 'ING2FISE', 'hiba.foukhar@etu.eilco.univ-littoral.fr', '$2y$12$PcCsJj5YiFIiSipb8TK5g.5waX5ChKaathU4kjrX5Tm6b046SsDea', 1),
(219, 'SEBBAR', 'MOAD', 'ING2FISE', 'moad.sebbar@etu.eilco.univ-littoral.fr', '$2y$12$D4xOldW4w91S0kFTlPyqW.AxLxoPN01Hxh0urL3ZfhGdchh9bDkc2', 1),
(220, 'Khattabi ', 'Chaymae ', 'ING2FISE', 'chaymae.khattabi@etu.eilco.univ-littoral.fr', '$2y$12$hT9njGQRbyoGLyOv9PE40O0qymWeRiIhbi14WKClNbQDgA5Xhl7K6', 1),
(222, 'AROUI', 'Hiba Allah', 'ING2FISE', 'hiba-allah.aroui@etu.eilco.univ-littoral.fr', '$2y$12$Rc3aFAKFS/LT6Kbaz73WjezUyJ0ODsAOpr2fDlgeKY1Z6TncJnYKu', 1),
(223, 'Tall', 'Issouf', 'ING2FISE', 'issouf.tall@etu.eilco.univ-littoral.fr', '$2y$12$n0bNgZ9i4Us2rW.FMKsjTuCfIvB.KL9CxnuWqH8EUAqaEwHeZTMde', 1),
(224, 'Azzou', 'Aya', 'ING2FISE', 'aya.azzou@etu.eilco.univ-littoral.fr', '$2y$12$4JmLDl8WheZWMvrN287x8e6cV5YyWWZqrcLlPvVp8QSYzooGs/TAe', 1),
(225, 'Direction', 'QITT', 'admin', 'directionqitt@eilco.univ-littoral.fr', '$2y$12$le1y5dm0uyvceSpAq1CA5ujTwhTGQHOHl1KI2a3cwEQfptmM4JpCu', 5),
(226, 'El ahmadi', 'Bourhan eddine', 'ING2FISE', 'bourhan-eddine.el-ahmadi@etu.eilco.univ-littoral.fr', '$2y$12$yu1XJAK1WTaNo0A6gF1Bge3jyH60yCEm.4kB2tAde0n9tMume2z.m', 1),
(227, 'Najem', 'Aline', 'ING2FISE', 'aline.najem@etu.eilco.univ-littoral.fr', '$2y$12$cfsr0xYjijW0F65p13D5eeSWXymGEgdtl7NF7DuHCz3Gt3XkVZZ0S', 1),
(228, 'Abderrazik', 'Othmane', 'ING2FISE', 'othmane.abderrazik@etu.eilco.univ-littoral.fr', '$2y$12$skx4bZmR3e18YXfFPHRpaOh3iiSTFyJ.B/8rdGXfKAzDvd3li6lOy', 1),
(229, 'Camara', 'Mamadou Lamine', 'etu', 'mamadou-lamine.camara@etu.eilco.univ-littoral.fr', '$2y$12$VUlybS4pXuA3sSPQhWsWluKd4tB3/0P74/0Apth2ddcDoQSr/NUpu', 1),
(230, 'DJOKO MBOUE', 'STEVE ROSTAND', 'etu', 'steve-rostand.djoko-mboue@etu.eilco.univ-littoral.fr', '$2y$12$8qEqYDx1I2Dkt71LApnRLeVkvGWl3sqaWSzkuRJNHhWqnZ3BPusNm', 1),
(231, 'NUTTEN', 'Sandrine', 'Tuteur', 'sandrine.nutten@univ-littoral.fr', '$2y$12$BssY0UF0KvHiaH1V9VIdVeV6mtPN6AkNh/Vg784d9dgm6iOcjxn.O', 2),
(232, 'Mokhtari ', 'Rihem', 'ING2FISE', 'rihem.mokhtari@etu.eilco.univ-littoral.fr', '$2y$12$9Ffa4jDrbF/J5c7MxCn7BeY8dI2CKoxGhtxcKfR/LckqVZ11kKDWG', 1),
(233, 'LE-BRIS', 'Cédric', 'Tuteur', 'cedric.le-bris@eilco.univ-littoral.fr', '$2y$12$VQMH2WTDiDbBOpiBwZ18GOEoHO5GB6cg0EgXD17wL/n0XBkbQHtKa', 2),
(234, 'Kenmegne kamdem', 'Yvan junior', 'etu', 'yvan-junior.kenmegne-kamdem@etu.eilco.univ-littoral.fr', '$2y$12$7Af6WkKUDN9YicUSItegRul4TPZoxX06qxjYg.lHhOSlLPX./gdtK', 1);

-- --------------------------------------------------------

--
-- Structure de la table `ville`
--

CREATE TABLE `ville` (
  `id` int NOT NULL,
  `ville` varchar(128) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ville`
--

INSERT INTO `ville` (`id`, `ville`) VALUES
(0, 'Calais'),
(1, 'Longuenesse'),
(2, 'Dunkerque'),
(3, 'Boulogne');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abonnements`
--
ALTER TABLE `abonnements`
  ADD PRIMARY KEY (`id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Index pour la table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fiche_club`
--
ALTER TABLE `fiche_club`
  ADD PRIMARY KEY (`club_id`);

--
-- Index pour la table `fiche_event`
--
ALTER TABLE `fiche_event`
  ADD PRIMARY KEY (`event_id`);

--
-- Index pour la table `mails`
--
ALTER TABLE `mails`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `membres_club`
--
ALTER TABLE `membres_club`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `membre_id` (`membre_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- Index pour la table `ville`
--
ALTER TABLE `ville`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `config`
--
ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `fiche_club`
--
ALTER TABLE `fiche_club`
  MODIFY `club_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT pour la table `fiche_event`
--
ALTER TABLE `fiche_event`
  MODIFY `event_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT pour la table `mails`
--
ALTER TABLE `mails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `membres_club`
--
ALTER TABLE `membres_club`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=326;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

--
-- AUTO_INCREMENT pour la table `ville`
--
ALTER TABLE `ville`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
