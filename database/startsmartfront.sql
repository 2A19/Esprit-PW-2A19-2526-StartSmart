CREATE DATABASE IF NOT EXISTS startsmart;
USE startsmart;

CREATE TABLE IF NOT EXISTS utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'CLIENT') NOT NULL
);

INSERT INTO utilisateur (id_utilisateur, nom, email, mot_de_passe, role)
VALUES
    (1, 'Administrateur', 'admin@gmail.com', '$2y$10$admin.placeholder.hash', 'ADMIN'),
    (2, 'Client', 'client@gmail.com', '$2y$10$client.placeholder.hash', 'CLIENT'),
    (3, 'Administrateur 2', 'admin2@gmail.com', '$2y$10$admin2.placeholder.hash', 'ADMIN'),
    (4, 'Nouveau Client', 'client2@gmail.com', '$2y$10$client2.placeholder.hash', 'CLIENT')
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    email = VALUES(email),
    role = VALUES(role);

CREATE TABLE IF NOT EXISTS categorie (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    num INT(11) NOT NULL,
    typeprojet VARCHAR(100) NOT NULL,
    nom_investisseur VARCHAR(100) NOT NULL,
    created_by_id INT(11) NOT NULL,
    CONSTRAINT fk_categorie_utilisateur FOREIGN KEY (created_by_id) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    INDEX idx_categorie_creator (created_by_id)
);

INSERT INTO categorie (id, num, typeprojet, nom_investisseur, created_by_id) VALUES
    (1, 1, 'Web Development', 'Tech Investor', 1),
    (2, 2, 'Mobile App', 'Mobile Investor', 1),
    (3, 3, 'Cloud Infrastructure', 'Cloud Investor', 1),
    (4, 4, 'AI/ML Project', 'Innovation Investor', 1),
    (5, 5, 'Consulting', 'Business Investor', 1)
ON DUPLICATE KEY UPDATE
    typeprojet = VALUES(typeprojet),
    nom_investisseur = VALUES(nom_investisseur);

CREATE TABLE IF NOT EXISTS projet (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    num INT(11) NOT NULL,
    nomprojet VARCHAR(100) NOT NULL,
    datedebut DATE NOT NULL,
    datefin DATE NOT NULL,
    budget DECIMAL(15,2) NOT NULL,
    gain DECIMAL(15,2) NOT NULL,
    categorie_id INT(11) NOT NULL,
    auteur_id INT(11) NOT NULL,
    CONSTRAINT fk_projet_categorie FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE RESTRICT,
    CONSTRAINT fk_projet_utilisateur FOREIGN KEY (auteur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    INDEX idx_projet_categorie (categorie_id),
    INDEX idx_projet_auteur (auteur_id)
);

CREATE TABLE IF NOT EXISTS post (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    topic VARCHAR(50) DEFAULT 'General',
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    auteur_id INT NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'actif',
    likes_count INT DEFAULT 0,
    CONSTRAINT fk_post_utilisateur FOREIGN KEY (auteur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    INDEX idx_post_auteur (auteur_id)
);

CREATE TABLE IF NOT EXISTS commentaire (
    id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    auteur_id INT NOT NULL,
    post_id INT NOT NULL,
    parent_id INT NULL,
    CONSTRAINT fk_commentaire_utilisateur FOREIGN KEY (auteur_id) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    CONSTRAINT fk_commentaire_post FOREIGN KEY (post_id) REFERENCES post(id_post) ON DELETE CASCADE,
    CONSTRAINT fk_commentaire_parent FOREIGN KEY (parent_id) REFERENCES commentaire(id_commentaire) ON DELETE CASCADE,
    INDEX idx_commentaire_auteur (auteur_id),
    INDEX idx_commentaire_post (post_id),
    INDEX idx_commentaire_parent (parent_id)
);

CREATE TABLE IF NOT EXISTS reaction (
    id_reaction INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NULL,
    comment_id INT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'LIKE',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reaction_user FOREIGN KEY (user_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_post FOREIGN KEY (post_id) REFERENCES post(id_post) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_comment FOREIGN KEY (comment_id) REFERENCES commentaire(id_commentaire) ON DELETE CASCADE,
    CONSTRAINT uq_reaction_post UNIQUE (user_id, post_id),
    CONSTRAINT uq_reaction_comment UNIQUE (user_id, comment_id),
    INDEX idx_reaction_user (user_id),
    INDEX idx_reaction_post (post_id)
);

CREATE TABLE IF NOT EXISTS notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    actor_id INT NULL,
    type VARCHAR(50) NOT NULL,
    reference_id INT NULL,
    message VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_notification_actor FOREIGN KEY (actor_id) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_is_read (is_read)
);

