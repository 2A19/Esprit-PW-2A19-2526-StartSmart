-- Frontend tables for StartSmart
CREATE TABLE IF NOT EXISTS categorie (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    num INT(11),
    typeprojet VARCHAR(100) NOT NULL,
    nom_investisseur VARCHAR(100),
    created_by_id INT(11),
    CONSTRAINT fk_categorie_users FOREIGN KEY (created_by_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_categorie_creator (created_by_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projet (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    num INT(11),
    nomprojet VARCHAR(100) NOT NULL,
    datedebut DATE,
    datefin DATE,
    budget DECIMAL(15,2),
    gain DECIMAL(15,2),
    categorie_id INT(11),
    auteur_id INT(11),
    CONSTRAINT fk_projet_categorie FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE SET NULL,
    CONSTRAINT fk_projet_users FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_projet_categorie (categorie_id),
    INDEX idx_projet_auteur (auteur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    topic VARCHAR(50) DEFAULT 'General',
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    auteur_id INT NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'actif',
    likes_count INT DEFAULT 0,
    CONSTRAINT fk_post_users FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_post_auteur (auteur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commentaire (
    id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    auteur_id INT NOT NULL,
    post_id INT NOT NULL,
    parent_id INT NULL,
    CONSTRAINT fk_commentaire_users FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_commentaire_post FOREIGN KEY (post_id) REFERENCES post(id_post) ON DELETE CASCADE,
    CONSTRAINT fk_commentaire_parent FOREIGN KEY (parent_id) REFERENCES commentaire(id_commentaire) ON DELETE CASCADE,
    INDEX idx_commentaire_auteur (auteur_id),
    INDEX idx_commentaire_post (post_id),
    INDEX idx_commentaire_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reaction (
    id_reaction INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NULL,
    comment_id INT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'LIKE',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_post FOREIGN KEY (post_id) REFERENCES post(id_post) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_comment FOREIGN KEY (comment_id) REFERENCES commentaire(id_commentaire) ON DELETE CASCADE,
    INDEX idx_reaction_user (user_id),
    INDEX idx_reaction_post (post_id),
    INDEX idx_reaction_comment (comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    actor_id INT NULL,
    type VARCHAR(50) NOT NULL,
    reference_id INT NULL,
    message VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
