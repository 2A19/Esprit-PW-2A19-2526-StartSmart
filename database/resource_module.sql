-- Base de données StartSmart - Gestion des ressources et sponsors
-- ================================================================

CREATE DATABASE IF NOT EXISTS startsmart;
USE startsmart;

-- ================================================================
-- TABLE DES SPONSORS
-- ================================================================
CREATE TABLE IF NOT EXISTS sponsors (
    id_sponsor INT PRIMARY KEY AUTO_INCREMENT,
    nom_sponsor VARCHAR(100) NOT NULL UNIQUE,
    email_sponsor VARCHAR(100) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    description TEXT,
    type_ressources VARCHAR(100),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    INDEX idx_email (email_sponsor),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLE DES RESSOURCES
-- ================================================================
CREATE TABLE IF NOT EXISTS ressources (
    id_ressource INT PRIMARY KEY AUTO_INCREMENT,
    id_sponsor INT NOT NULL,
    nom_ressource VARCHAR(150) NOT NULL,
    description TEXT,
    type_ressource VARCHAR(50),
    quantite_disponible INT NOT NULL DEFAULT 0,
    quantite_utilisee INT DEFAULT 0,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('disponible', 'indisponible', 'archive') DEFAULT 'disponible',
    FOREIGN KEY (id_sponsor) REFERENCES sponsors(id_sponsor) ON DELETE CASCADE,
    INDEX idx_sponsor (id_sponsor),
    INDEX idx_statut (statut),
    INDEX idx_type (type_ressource)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLE DES UTILISATEURS (START-UPERS)
-- ================================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    nom_utilisateur VARCHAR(100) NOT NULL,
    email_utilisateur VARCHAR(100) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    entreprise VARCHAR(100),
    domaine_activite VARCHAR(100),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    INDEX idx_email (email_utilisateur),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLE DES DEMANDES D'ACCÈS
-- ================================================================
CREATE TABLE IF NOT EXISTS demandes_acces (
    id_demande INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    id_ressource INT NOT NULL,
    quantite_demandee INT NOT NULL DEFAULT 1,
    description_demande TEXT,
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_reponse DATETIME,
    statut_demande ENUM('en_attente', 'acceptee', 'refusee', 'archivee') DEFAULT 'en_attente',
    raison_refus TEXT,
    duree_acces_jours INT DEFAULT 30,
    date_fin_acces DATETIME,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_ressource) REFERENCES ressources(id_ressource) ON DELETE CASCADE,
    INDEX idx_utilisateur (id_utilisateur),
    INDEX idx_ressource (id_ressource),
    INDEX idx_statut (statut_demande),
    INDEX idx_date (date_demande)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLE D'AUDIT (traçabilité des actions)
-- ================================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    type_action VARCHAR(50),
    id_ressource INT,
    id_demande INT,
    id_sponsor INT,
    description_action TEXT,
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date_action),
    INDEX idx_type (type_action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- DONNÉES DE TEST
-- ================================================================

-- Sponsors
INSERT INTO sponsors (nom_sponsor, email_sponsor, telephone, description, type_ressources, statut) VALUES
('TechCorp Finance', 'contact@techcorp.com', '+33612345678', 'Fournisseur de solutions financières et de consulting', 'Services de consulting', 'actif'),
('GreenInnovations', 'support@greeninnovations.fr', '+33623456789', 'Solutions durables et technologies vertes', 'Équipements écologiques', 'actif'),
('NetworkHub', 'info@networkhub.fr', '+33634567890', 'Infrastructure réseau et cloud computing', 'Infrastructure IT', 'actif');

-- Ressources
INSERT INTO ressources (id_sponsor, nom_ressource, description, type_ressource, quantite_disponible, statut) VALUES
(1, 'Audit Financier', 'Audit complet des finances de votre startup', 'Services', 5, 'disponible'),
(1, 'Consultation Business Plan', 'Aide à la création et optimisation du business plan', 'Services', 10, 'disponible'),
(2, 'Certification ISO 14001', 'Formation et certification ISO 14001', 'Formation', 3, 'disponible'),
(3, 'Infrastructure Cloud 3 mois', 'Accès à infrastructure cloud complète pendant 3 mois', 'Infrastructure', 8, 'disponible'),
(3, 'Support Technique 24/7', 'Support technique illimité 24h/24 7j/7', 'Services', 2, 'disponible');

-- Utilisateurs
INSERT INTO utilisateurs (nom_utilisateur, email_utilisateur, telephone, entreprise, domaine_activite, statut) VALUES
('Marie Dubois', 'marie.dubois@startup.fr', '+33645678901', 'EcoTech', 'Technologie verte', 'actif'),
('Pierre Martin', 'pierre.martin@startup.fr', '+33656789012', 'StartupAI', 'Intelligence Artificielle', 'actif'),
('Sophie Laurent', 'sophie.laurent@startup.fr', '+33667890123', 'FinTrack', 'FinTech', 'actif');

-- Demandes d'accès
INSERT INTO demandes_acces (id_utilisateur, id_ressource, quantite_demandee, description_demande, statut_demande, date_demande) VALUES
(1, 1, 1, 'Nous avons besoin d\'un audit pour nous préparer aux investisseurs', 'en_attente', NOW()),
(2, 4, 1, 'Infrastructure pour nos tests d\'IA', 'acceptee', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 2, 1, 'Optimisation de notre business plan', 'en_attente', NOW());

-- Mise à jour de la quantité utilisée
UPDATE ressources SET quantite_utilisee = 1 WHERE id_ressource = 4;
