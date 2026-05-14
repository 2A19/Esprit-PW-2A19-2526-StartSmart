-- StartSmart resource module integration.
-- Additive only: no existing StartSmart table is dropped or altered.

CREATE TABLE IF NOT EXISTS sponsors (
    id_sponsor INT PRIMARY KEY AUTO_INCREMENT,
    nom_sponsor VARCHAR(100) NOT NULL UNIQUE,
    email_sponsor VARCHAR(100) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    description TEXT,
    type_ressources VARCHAR(100),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    INDEX idx_sponsors_email (email_sponsor),
    INDEX idx_sponsors_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_ressources_sponsor (id_sponsor),
    INDEX idx_ressources_statut (statut),
    INDEX idx_ressources_type (type_ressource),
    CONSTRAINT fk_ressources_sponsor
        FOREIGN KEY (id_sponsor) REFERENCES sponsors(id_sponsor)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    nom_utilisateur VARCHAR(100) NOT NULL,
    email_utilisateur VARCHAR(100) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    entreprise VARCHAR(100),
    domaine_activite VARCHAR(100),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    INDEX idx_utilisateurs_email (email_utilisateur),
    INDEX idx_utilisateurs_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_demandes_utilisateur (id_utilisateur),
    INDEX idx_demandes_ressource (id_ressource),
    INDEX idx_demandes_statut (statut_demande),
    INDEX idx_demandes_date (date_demande),
    CONSTRAINT fk_demandes_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur)
        ON DELETE CASCADE,
    CONSTRAINT fk_demandes_ressource
        FOREIGN KEY (id_ressource) REFERENCES ressources(id_ressource)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    type_action VARCHAR(50),
    id_ressource INT,
    id_demande INT,
    id_sponsor INT,
    description_action TEXT,
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_date (date_action),
    INDEX idx_audit_type (type_action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL DEFAULT 'sms',
    title VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_resource_notifications_read (is_read),
    INDEX idx_resource_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO sponsors (id_sponsor, nom_sponsor, email_sponsor, telephone, description, type_ressources, statut) VALUES
(1, 'TechCorp Finance', 'contact@techcorp.com', '+33612345678', 'Fournisseur de solutions financières et de consulting', 'Services de consulting', 'actif'),
(2, 'GreenInnovations', 'support@greeninnovations.fr', '+33623456789', 'Solutions durables et technologies vertes', 'Equipements écologiques', 'actif'),
(3, 'NetworkHub', 'info@networkhub.fr', '+33634567890', 'Infrastructure réseau et cloud computing', 'Infrastructure IT', 'actif');

INSERT IGNORE INTO ressources (id_ressource, id_sponsor, nom_ressource, description, type_ressource, quantite_disponible, quantite_utilisee, statut) VALUES
(1, 1, 'Audit Financier', 'Audit complet des finances de votre startup', 'Services', 5, 0, 'disponible'),
(2, 1, 'Consultation Business Plan', 'Aide à la création et optimisation du business plan', 'Services', 10, 0, 'disponible'),
(3, 2, 'Certification ISO 14001', 'Formation et certification ISO 14001', 'Formation', 3, 0, 'disponible'),
(4, 3, 'Infrastructure Cloud 3 mois', 'Accès à infrastructure cloud complète pendant 3 mois', 'Infrastructure', 8, 1, 'disponible'),
(5, 3, 'Support Technique 24/7', 'Support technique illimité 24h/24 7j/7', 'Services', 2, 0, 'disponible');

INSERT IGNORE INTO utilisateurs (id_utilisateur, nom_utilisateur, email_utilisateur, telephone, entreprise, domaine_activite, statut) VALUES
(1, 'Marie Dubois', 'marie.dubois@startup.fr', '+33645678901', 'EcoTech', 'Technologie verte', 'actif'),
(2, 'Pierre Martin', 'pierre.martin@startup.fr', '+33656789012', 'StartupAI', 'Intelligence Artificielle', 'actif'),
(3, 'Sophie Laurent', 'sophie.laurent@startup.fr', '+33667890123', 'FinTrack', 'FinTech', 'actif');

INSERT IGNORE INTO demandes_acces (id_demande, id_utilisateur, id_ressource, quantite_demandee, description_demande, statut_demande, date_demande) VALUES
(1, 1, 1, 1, 'Nous avons besoin d''un audit pour nous préparer aux investisseurs', 'en_attente', NOW()),
(2, 2, 4, 1, 'Infrastructure pour nos tests d''IA', 'acceptee', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 3, 2, 1, 'Optimisation de notre business plan', 'en_attente', NOW());
