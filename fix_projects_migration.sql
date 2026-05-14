-- Fixed project interactions migration (using users table instead of utilisateur)
ALTER TABLE projet ADD COLUMN IF NOT EXISTS `description` LONGTEXT NULL DEFAULT NULL AFTER `nomprojet`;
ALTER TABLE projet ADD COLUMN IF NOT EXISTS `statut` ENUM('draft', 'actif', 'archived', 'deleted') DEFAULT 'actif' AFTER `auteur_id`;
ALTER TABLE projet ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `statut`;
ALTER TABLE projet ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

CREATE TABLE IF NOT EXISTS `projet_reaction` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `projet_id` INT NOT NULL,
  `type` ENUM('LIKE', 'DISLIKE') DEFAULT 'LIKE',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_projet_reaction` (`user_id`, `projet_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`projet_id`) REFERENCES `projet`(`id`) ON DELETE CASCADE,
  INDEX `idx_projet_id` (`projet_id`),
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `projet_commentaire` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `projet_id` INT NOT NULL,
  `auteur_id` INT NOT NULL,
  `contenu` LONGTEXT NOT NULL,
  `parent_id` INT DEFAULT NULL,
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `statut` ENUM('actif', 'supprime', 'signale') DEFAULT 'actif',
  FOREIGN KEY (`projet_id`) REFERENCES `projet`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`auteur_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `projet_commentaire`(`id`) ON DELETE CASCADE,
  INDEX `idx_projet_id` (`projet_id`),
  INDEX `idx_auteur_id` (`auteur_id`),
  INDEX `idx_parent_id` (`parent_id`),
  INDEX `idx_date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
