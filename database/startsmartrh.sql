-- ============================================================
-- StartSmart + RH - Base de donnees fusionnee
-- Importer ce fichier dans phpMyAdmin ou avec:
-- mysql -u root < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS startsmart_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE startsmart_db;

-- ------------------------------------------------------------
-- Users StartSmart + colonnes de compatibilite RH
-- role:
--   user    -> front office RH employe/candidat
--   startup -> back office RH startup
--   admin   -> back office gestion user
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100) NOT NULL,
    full_name           VARCHAR(201) DEFAULT NULL,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password            VARCHAR(255) NOT NULL,
    telephone           VARCHAR(20) DEFAULT NULL,
    phone               VARCHAR(20) DEFAULT NULL,
    date_naissance      DATE DEFAULT NULL,
    role                ENUM('user','startup','admin') DEFAULT 'user',
    statut              ENUM('actif','inactif','banni','verifie','pending') DEFAULT 'pending',
    date_inscription    DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    derniere_connexion  DATETIME DEFAULT NULL,
    google_id           VARCHAR(255) DEFAULT NULL,
    profile_picture     VARCHAR(255) DEFAULT NULL,
    email_token         VARCHAR(64) DEFAULT NULL,
    email_token_expires DATETIME DEFAULT NULL,
    reset_otp           VARCHAR(6) DEFAULT NULL,
    reset_otp_expires   DATETIME DEFAULT NULL,
    ban_expires         DATETIME DEFAULT NULL,
    ban_reason          VARCHAR(255) DEFAULT NULL,
    remember_selector   VARCHAR(32) DEFAULT NULL,
    remember_token_hash VARCHAR(255) DEFAULT NULL,
    remember_expires    DATETIME DEFAULT NULL,
    nom_startup         VARCHAR(200) DEFAULT NULL,
    company_name        VARCHAR(200) DEFAULT NULL,
    nom_responsable     VARCHAR(100) DEFAULT NULL,
    prenom_responsable  VARCHAR(100) DEFAULT NULL,
    secteur             VARCHAR(100) DEFAULT NULL,
    site_web            VARCHAR(255) DEFAULT NULL,
    stade               ENUM('idee','prototype','mvp','croissance','scale') DEFAULT 'idee',
    profession          VARCHAR(100) DEFAULT NULL,
    experience          VARCHAR(100) DEFAULT NULL,
    resume              VARCHAR(255) DEFAULT NULL,
    face_encoding       LONGBLOB DEFAULT NULL,
    face_recognition_enabled BOOLEAN DEFAULT FALSE,
    face_setup_date     DATETIME DEFAULT NULL,
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_statut (statut),
    INDEX idx_users_email_token (email_token),
    UNIQUE INDEX idx_users_google_id (google_id),
    INDEX idx_users_remember_selector (remember_selector),
    INDEX idx_face_enabled (face_recognition_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrations idempotentes pour installations existantes.
ALTER TABLE users ADD COLUMN IF NOT EXISTS full_name VARCHAR(201) DEFAULT NULL AFTER prenom;
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL AFTER telephone;
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER date_inscription;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) DEFAULT NULL AFTER derniere_connexion;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL AFTER google_id;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_token VARCHAR(64) DEFAULT NULL AFTER profile_picture;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_token_expires DATETIME DEFAULT NULL AFTER email_token;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_otp VARCHAR(6) DEFAULT NULL AFTER email_token_expires;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_otp_expires DATETIME DEFAULT NULL AFTER reset_otp;
ALTER TABLE users ADD COLUMN IF NOT EXISTS ban_expires DATETIME DEFAULT NULL AFTER reset_otp_expires;
ALTER TABLE users ADD COLUMN IF NOT EXISTS ban_reason VARCHAR(255) DEFAULT NULL AFTER ban_expires;
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_selector VARCHAR(32) DEFAULT NULL AFTER ban_reason;
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token_hash VARCHAR(255) DEFAULT NULL AFTER remember_selector;
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_expires DATETIME DEFAULT NULL AFTER remember_token_hash;
ALTER TABLE users ADD COLUMN IF NOT EXISTS company_name VARCHAR(200) DEFAULT NULL AFTER nom_startup;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profession VARCHAR(100) DEFAULT NULL AFTER stade;
ALTER TABLE users ADD COLUMN IF NOT EXISTS experience VARCHAR(100) DEFAULT NULL AFTER profession;
ALTER TABLE users ADD COLUMN IF NOT EXISTS resume VARCHAR(255) DEFAULT NULL AFTER experience;
ALTER TABLE users ADD COLUMN IF NOT EXISTS face_encoding LONGBLOB DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS face_recognition_enabled BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS face_setup_date DATETIME DEFAULT NULL;

UPDATE users
SET full_name = TRIM(CONCAT(COALESCE(prenom, ''), ' ', COALESCE(nom, '')))
WHERE full_name IS NULL OR full_name = '';

UPDATE users
SET phone = telephone
WHERE (phone IS NULL OR phone = '') AND telephone IS NOT NULL;

UPDATE users
SET company_name = nom_startup
WHERE role = 'startup' AND (company_name IS NULL OR company_name = '') AND nom_startup IS NOT NULL;

-- ------------------------------------------------------------
-- Tables RH
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS job_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description LONGTEXT NOT NULL,
    requirements LONGTEXT,
    salary_min DECIMAL(10, 2) NOT NULL,
    salary_max DECIMAL(10, 2) NOT NULL,
    location VARCHAR(100) NOT NULL,
    type ENUM('Full-time', 'Part-time', 'Contract', 'Freelance') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_offers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_job_offers_user_id (user_id),
    INDEX idx_job_offers_status (status),
    INDEX idx_job_offers_created_at (created_at),
    INDEX idx_job_offers_salary (salary_min, salary_max),
    INDEX idx_job_offers_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_offer_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    experience VARCHAR(100),
    cover_letter LONGTEXT NOT NULL,
    resume VARCHAR(255),
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_job_offer FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_applications_job_offer_id (job_offer_id),
    INDEX idx_applications_user_id (user_id),
    INDEX idx_applications_status (status),
    INDEX idx_applications_created_at (created_at),
    INDEX idx_applications_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_offer_id INT DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_employees_job_offer FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE SET NULL,
    INDEX idx_employees_user_id (user_id),
    INDEX idx_employees_department (department),
    INDEX idx_employees_status (status),
    UNIQUE KEY unique_employee_email_per_startup (user_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Comptes de test
-- Comptes de test:
--   ahmed@email.com / user123
--   admin@startsmart.com / admin123
--   contact@techtunisia.tn / startup123
-- ------------------------------------------------------------
INSERT IGNORE INTO users (nom, prenom, full_name, email, password, telephone, phone, role, statut) VALUES
('Ben Ali', 'Ahmed', 'Ahmed Ben Ali', 'ahmed@email.com', '$2y$10$rTEyIxTgyqHlu66exgsNm.iOJ6C7T.pBJs2lie6yPei1ua71MP9qu', '55123456', '55123456', 'user', 'actif'),
('Admin', 'Super', 'Super Admin', 'admin@startsmart.com', '$2y$10$fYtQXDj8fKlTSjmy1Rl7dOtN.Gc6RJ8PrKA7Xp1MzJBTNHXex9vom', NULL, NULL, 'admin', 'actif');

INSERT IGNORE INTO users (nom, prenom, full_name, email, password, telephone, phone, role, statut, nom_startup, company_name, nom_responsable, prenom_responsable, secteur, stade) VALUES
('Chaabane', 'Mehdi', 'Mehdi Chaabane', 'contact@techtunisia.tn', '$2y$10$easGrqBneI/vciSwnWB/0.cSBqYGR.IT2kIgSVbhCajRQHB7qyhoa', '55001122', '55001122', 'startup', 'actif', 'TechTunisia', 'TechTunisia', 'Chaabane', 'Mehdi', 'Technologie', 'mvp');
