-- Add evenements table from uploaded file
CREATE TABLE IF NOT EXISTS `evenements` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `titre` varchar(100) DEFAULT NULL,
  `date_evenement` date DEFAULT NULL,
  `lieu` varchar(100) DEFAULT NULL,
  `statut` enum('Ouvert','Complet','Ferme','actif','annulé','terminé') DEFAULT 'Ouvert',
  `capacite` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add participants table from uploaded file
CREATE TABLE IF NOT EXISTS `participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `event_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `telephone` varchar(20) DEFAULT NULL,
  `projet` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('inscrit','confirmé','annulé') DEFAULT 'inscrit',
  FOREIGN KEY (`event_id`) REFERENCES `evenements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert evenements data
INSERT INTO `evenements` (`id`, `titre`, `date_evenement`, `lieu`, `statut`, `capacite`, `description`, `image_url`, `created_at`) VALUES
(2, 'festivallllllll', '2026-05-05', 'hamamet', 'Ouvert', 2, 'zuhgdeazyu', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80', '2026-05-07 10:42:47'),
(3, 'Atelier MVP & Go-To-Market', '2026-07-25', 'En ligne', 'Ouvert', 120, 'Atelier pratique pour structurer votre MVP et accelerer votre mise sur le marche.', 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80', '2026-05-13 17:05:09'),
(12348, 'validationnnnnnnnnnnnnnn', '2026-05-06', 'hamamet', 'Ouvert', 200, 'hgkfckyhcdgtj', 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80', '2026-04-30 10:10:49'),
(12350, 'startup pitch night', '2026-05-25', 'hamamet', 'Ouvert', 23, 'jshazkdgBVSZJEYGD', 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80', '2026-05-13 19:54:54'),
(12351, 'midnight', '2026-05-24', 'hamamet', 'Ouvert', 2, 'sdghhgdhdshjdehuyey', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80', '2026-05-13 20:02:58') ON DUPLICATE KEY UPDATE titre=VALUES(titre);

-- Insert participants data
INSERT INTO `participants` (`id`, `event_id`, `nom`, `prenom`, `age`, `email`, `telephone`, `projet`, `created_at`, `statut`) VALUES
(234544, 12348, 'aloui', 'roua', 20, 'hjsrf@hsqgduyez', '021974238964', 'svdheyzgseq', '2026-04-30 10:11:19', 'inscrit'),
(234545, 12348, 'aloui', 'roua', 20, 'qsjdgyefjshdg', '128371246', 'jhazeydtfeztsy', '2026-04-30 10:44:40', 'inscrit'),
(234546, 12348, 'xx', 'xx', 27, 'VVV', '', '', '2026-04-30 10:49:07', 'inscrit'),
(234554, 2, 'dhia', 'dhia', 28, 'jkgdezyf@zejhdued', '1297836871293', 'hjzegyrfe', '2026-05-07 10:09:53', 'inscrit'),
(234555, 12348, 'aloui', 'dhia', 20, 'hjasgbdyue@iuauzjhdeui', '9820²73782', 'zhdgryegfr', '2026-05-07 10:11:06', 'inscrit') ON DUPLICATE KEY UPDATE nom=VALUES(nom);

-- Insert the test user if not exists
INSERT IGNORE INTO `users` (`id`, `nom`, `prenom`, `full_name`, `email`, `password`, `role`, `statut`, `date_inscription`, `created_at`) 
VALUES (16, 'Test', 'User', 'Test User', 'testuser@test.com', '$2y$10$x.qyV6UznbCza6qXx9WoyesgM2RoM.KLcaejNBaUYVTT.aPPL.bwW', 'user', 'actif', '2026-05-13 20:21:32', '2026-05-13 19:21:32');

-- Update categorie with new fields if needed
ALTER TABLE `categorie` ADD COLUMN IF NOT EXISTS `titre` varchar(100) DEFAULT NULL AFTER `num`;
ALTER TABLE `categorie` ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `created_by_id`;
ALTER TABLE `categorie` ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`;

-- Update post with additional fields from uploaded file
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `city` varchar(100) DEFAULT NULL AFTER `likes_count`;
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `country` varchar(100) DEFAULT NULL AFTER `city`;
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL AFTER `country`;
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`;
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `categorie_id` int(11) DEFAULT NULL AFTER `longitude`;
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `projet_id` int(11) DEFAULT NULL AFTER `categorie_id`;
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `projet_id`;
ALTER TABLE `post` ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`;

-- Update projet with additional fields
ALTER TABLE `projet` ADD COLUMN IF NOT EXISTS `city` varchar(100) DEFAULT NULL AFTER `gain`;
ALTER TABLE `projet` ADD COLUMN IF NOT EXISTS `country` varchar(100) DEFAULT NULL AFTER `city`;
ALTER TABLE `projet` ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL AFTER `country`;
ALTER TABLE `projet` ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`;
ALTER TABLE `projet` ADD COLUMN IF NOT EXISTS `etape` varchar(50) NOT NULL DEFAULT 'idea' AFTER `statut`;

-- Update commentaire with timestamps
ALTER TABLE `commentaire` ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `parent_id`;
ALTER TABLE `commentaire` ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`;
