-- ============================================
-- Smart Project Matching System - Database Migration
-- ============================================
-- This script creates all necessary tables for the matching system
-- Run this once before using the matching features
-- ============================================

-- Enable foreign keys
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 1. SKILL TABLE - Skill Catalog
-- ============================================
CREATE TABLE IF NOT EXISTS skill (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. USER_SKILL TABLE - User Skills Proficiency
-- ============================================
CREATE TABLE IF NOT EXISTS user_skill (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'expert') DEFAULT 'intermediate',
    years_experience INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_skill (user_id, skill_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skill(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_skill_id (skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. PROJECT_SKILL TABLE - Project Requirements
-- ============================================
CREATE TABLE IF NOT EXISTS project_skill (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_required ENUM('beginner', 'intermediate', 'expert') DEFAULT 'intermediate',
    is_required BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_project_skill (projet_id, skill_id),
    FOREIGN KEY (projet_id) REFERENCES projet(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skill(id) ON DELETE CASCADE,
    INDEX idx_projet_id (projet_id),
    INDEX idx_skill_id (skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. USER_INTEREST TABLE - User Category Interests
-- ============================================
CREATE TABLE IF NOT EXISTS user_interest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    categorie_id INT NOT NULL,
    interest_level INT DEFAULT 5 CHECK (interest_level >= 1 AND interest_level <= 10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_interest (user_id, categorie_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_categorie_id (categorie_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. MATCHING_CACHE TABLE - Performance Cache
-- ============================================
CREATE TABLE IF NOT EXISTS matching_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    projet_id INT NOT NULL,
    match_score DECIMAL(5, 2),
    skill_match DECIMAL(5, 2),
    interest_match DECIMAL(5, 2),
    activity_match DECIMAL(5, 2),
    match_summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    UNIQUE KEY unique_cache_entry (user_id, projet_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (projet_id) REFERENCES projet(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. MATCHING_INTERACTION TABLE - User Analytics
-- ============================================
CREATE TABLE IF NOT EXISTS matching_interaction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    projet_id INT NOT NULL,
    action ENUM('view', 'like', 'skip', 'save', 'apply') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (projet_id) REFERENCES projet(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_projet_id (projet_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA: 25 Skills in 4 Categories
-- ============================================

-- Development Skills (7)
INSERT INTO skill (name, category, description) VALUES
('PHP', 'dev', 'Backend development with PHP'),
('JavaScript', 'dev', 'Frontend and Node.js JavaScript development'),
('Python', 'dev', 'Python for data science, AI, and web development'),
('React', 'dev', 'React.js frontend framework'),
('Vue.js', 'dev', 'Vue.js progressive frontend framework'),
('Database Design', 'dev', 'SQL database design and optimization'),
('API Development', 'dev', 'RESTful and GraphQL API development');

-- Design Skills (5)
INSERT INTO skill (name, category, description) VALUES
('UI Design', 'design', 'User interface design'),
('UX Design', 'design', 'User experience research and design'),
('Graphic Design', 'design', 'Visual design and branding'),
('Figma', 'design', 'Figma design tool expertise'),
('Prototyping', 'design', 'Interactive prototyping and wireframing');

-- Marketing Skills (5)
INSERT INTO skill (name, category, description) VALUES
('Digital Marketing', 'marketing', 'Digital marketing strategy and execution'),
('Social Media', 'marketing', 'Social media management and strategy'),
('Content Writing', 'marketing', 'Content creation and copywriting'),
('SEO', 'marketing', 'Search engine optimization'),
('Data Analytics', 'marketing', 'Marketing analytics and data analysis');

-- Business Skills (5)
INSERT INTO skill (name, category, description) VALUES
('Project Management', 'business', 'Project planning and execution'),
('Business Strategy', 'business', 'Business strategy and planning'),
('Financial Planning', 'business', 'Financial analysis and planning'),
('Sales', 'business', 'Sales and business development'),
('Negotiation', 'business', 'Negotiation and communication skills');

-- ============================================
-- End of Migration
-- ============================================
-- Success message: Migration complete!
-- Tables created: 6
-- Skills seeded: 25
-- ============================================
