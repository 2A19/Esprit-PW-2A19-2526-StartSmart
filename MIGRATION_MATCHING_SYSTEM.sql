-- Smart Project Matching System - Database Migration
-- Tables for skills, interests, and matching algorithm

-- ===== TABLE 1: skill =====
-- All available skills in the platform
CREATE TABLE IF NOT EXISTS `skill` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `category` VARCHAR(50) NOT NULL COMMENT 'development, design, marketing, business, etc.',
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE 2: user_skill =====
-- Many-to-many: Users with their skills
CREATE TABLE IF NOT EXISTS `user_skill` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  `proficiency_level` ENUM('beginner', 'intermediate', 'expert') DEFAULT 'beginner' COMMENT 'User skill level',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_skill` (`user_id`, `skill_id`),
  FOREIGN KEY (`user_id`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skill`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_skill_id` (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE 3: project_skill =====
-- Many-to-many: Projects with required skills
CREATE TABLE IF NOT EXISTS `project_skill` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `projet_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  `required` BOOLEAN DEFAULT TRUE COMMENT 'Is this skill required (vs nice-to-have)?',
  `priority` INT DEFAULT 1 COMMENT 'Priority level: 1=critical, 2=high, 3=medium, 4=low',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_project_skill` (`projet_id`, `skill_id`),
  FOREIGN KEY (`projet_id`) REFERENCES `projet`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skill`(`id`) ON DELETE CASCADE,
  INDEX `idx_projet_id` (`projet_id`),
  INDEX `idx_skill_id` (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE 4: user_interest =====
-- Many-to-many: Users interested in categories
CREATE TABLE IF NOT EXISTS `user_interest` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `categorie_id` INT NOT NULL,
  `interest_score` INT DEFAULT 1 COMMENT 'How interested: 1-5',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_interest` (`user_id`, `categorie_id`),
  FOREIGN KEY (`user_id`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`categorie_id`) REFERENCES `categorie`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_categorie_id` (`categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE 5: project_match_cache =====
-- Cache for match scores (optional but recommended for performance)
CREATE TABLE IF NOT EXISTS `project_match_cache` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `projet_id` INT NOT NULL,
  `match_score` DECIMAL(5,2) COMMENT 'Overall match score (0-100)',
  `skill_score` DECIMAL(5,2) COMMENT 'Skill match percentage',
  `interest_score` DECIMAL(5,2) COMMENT 'Interest match percentage',
  `activity_score` DECIMAL(5,2) COMMENT 'Activity level score',
  `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL COMMENT 'Cache expiry for real-time updates',
  UNIQUE KEY `unique_user_projet_match` (`user_id`, `projet_id`),
  FOREIGN KEY (`user_id`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`projet_id`) REFERENCES `projet`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_match_score` (`match_score`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE 6: user_match_action =====
-- Track user swipe actions (like/skip) for future ML improvements
CREATE TABLE IF NOT EXISTS `user_match_action` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `projet_id` INT NOT NULL,
  `action` ENUM('interested', 'skipped', 'applied', 'rejected') DEFAULT 'skipped',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`projet_id`) REFERENCES `projet`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_projet_id` (`projet_id`),
  INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== SEED DATA: Default Skills =====
INSERT IGNORE INTO `skill` (`name`, `category`, `description`) VALUES
-- Development
('PHP', 'development', 'Server-side PHP programming'),
('JavaScript', 'development', 'Frontend JavaScript/Node.js'),
('Python', 'development', 'Python backend/data science'),
('React', 'development', 'React frontend framework'),
('Laravel', 'development', 'Laravel PHP framework'),
('SQL', 'development', 'Database design and queries'),
('DevOps', 'development', 'Deployment, Docker, CI/CD'),
('API Development', 'development', 'REST API design'),
-- Design
('UI Design', 'design', 'User interface design'),
('UX Design', 'design', 'User experience research'),
('Graphic Design', 'design', 'Visual design and branding'),
('Figma', 'design', 'Figma design tool expertise'),
('Mobile Design', 'design', 'Mobile app UI/UX design'),
-- Marketing
('Social Media Marketing', 'marketing', 'Social media strategy and management'),
('Content Marketing', 'marketing', 'Blog, video, content creation'),
('SEO', 'marketing', 'Search engine optimization'),
('SEM', 'marketing', 'Google Ads, paid advertising'),
('Analytics', 'marketing', 'Data analytics and reporting'),
-- Business
('Project Management', 'business', 'Project planning and management'),
('Business Strategy', 'business', 'Strategic planning'),
('Sales', 'business', 'Sales and business development'),
('Finance', 'business', 'Financial planning and accounting'),
('HR', 'business', 'Human resources management');
