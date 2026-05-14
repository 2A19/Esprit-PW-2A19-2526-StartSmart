-- Forum integration fixes: attachment table used by models/Attachment.php.

CREATE TABLE IF NOT EXISTS piece_jointe (
    id_piece_jointe INT AUTO_INCREMENT PRIMARY KEY,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    type_fichier VARCHAR(100) DEFAULT NULL,
    taille_fichier INT DEFAULT 0,
    post_id INT NULL,
    commentaire_id INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_piece_jointe_post (post_id),
    INDEX idx_piece_jointe_commentaire (commentaire_id),
    CONSTRAINT fk_piece_jointe_post
        FOREIGN KEY (post_id) REFERENCES post(id_post)
        ON DELETE CASCADE,
    CONSTRAINT fk_piece_jointe_commentaire
        FOREIGN KEY (commentaire_id) REFERENCES commentaire(id_commentaire)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
