-- ============================================================
--  StartSmart – Base de données XAMPP
--  Importer via phpMyAdmin > Importer > startsmart_db.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS startsmart_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE startsmart_db;

-- ------------------------------------------------------------
-- TABLE users (merged with startups)
-- Added: email_token, email_token_expires for email verification
--        statut 'pending' = awaiting email verification
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    nom                VARCHAR(100)  NOT NULL,
    prenom             VARCHAR(100)  NOT NULL,
    email              VARCHAR(150)  NOT NULL UNIQUE,
    password           VARCHAR(255)  NOT NULL,
    telephone          VARCHAR(20)   DEFAULT NULL,
    date_naissance     DATE          DEFAULT NULL,
    role               ENUM('user','startup','admin') DEFAULT 'user',
    statut             ENUM('actif','inactif','banni','verifie','pending') DEFAULT 'pending',
    date_inscription   DATETIME      DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME      DEFAULT NULL,
    -- Email verification
    email_token        VARCHAR(64)   DEFAULT NULL,
    email_token_expires DATETIME     DEFAULT NULL,
    -- Startup-specific fields (nullable for regular users)
    nom_startup         VARCHAR(200) DEFAULT NULL,
    nom_responsable     VARCHAR(100) DEFAULT NULL,
    prenom_responsable  VARCHAR(100) DEFAULT NULL,
    secteur             VARCHAR(100) DEFAULT NULL,
    site_web            VARCHAR(255) DEFAULT NULL,
    stade               ENUM('idee','prototype','mvp','croissance','scale') DEFAULT 'idee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migration for existing installations: add new columns if not present
-- Run this if upgrading from a previous version:
--
-- ALTER TABLE users
--   ADD COLUMN email_token VARCHAR(64) DEFAULT NULL AFTER derniere_connexion,
--   ADD COLUMN email_token_expires DATETIME DEFAULT NULL AFTER email_token,
--   MODIFY COLUMN statut ENUM('actif','inactif','banni','verifie','pending') DEFAULT 'pending';
--
-- UPDATE users SET statut = 'actif' WHERE statut NOT IN ('banni','inactif');

-- ------------------------------------------------------------
-- Données de test  (mot de passe en clair : Test1234!)
-- Le hash bcrypt ci-dessous correspond à "Test1234!"
-- statut = 'actif' pour les comptes de test (déjà vérifiés)
-- ------------------------------------------------------------
INSERT IGNORE INTO users (nom, prenom, email, password, telephone, role, statut) VALUES
('Ben Ali',  'Ahmed', 'ahmed@email.com',       '$2y$10$YourHashHere.replaceMe', '55123456', 'user',  'actif'),
('Trabelsi', 'Sonia', 'sonia@email.com',       '$2y$10$YourHashHere.replaceMe', '22987654', 'user',  'actif'),
('Mansouri', 'Karim', 'karim@email.com',       '$2y$10$YourHashHere.replaceMe', '99456123', 'user',  'inactif'),
('Admin',    'Super', 'admin@startsmart.com',  '$2y$10$YourHashHere.replaceMe', NULL,       'admin', 'actif');

INSERT IGNORE INTO users (nom, prenom, email, password, telephone, role, statut, nom_startup, nom_responsable, prenom_responsable, secteur, stade) VALUES
('Chaabane', 'Mehdi', 'contact@techtunisia.tn', '$2y$10$YourHashHere.replaceMe', '55001122', 'startup', 'actif', 'TechTunisia', 'Chaabane', 'Mehdi', 'Technologie', 'mvp'),
('Hamdi',    'Leila', 'info@greenagri.tn',      '$2y$10$YourHashHere.replaceMe', '22334455', 'startup', 'actif', 'GreenAgri', 'Hamdi', 'Leila', 'Agriculture', 'prototype'),
('Sassi',    'Omar',  'hello@edubridge.tn',     '$2y$10$YourHashHere.replaceMe', NULL,       'startup', 'actif', 'EduBridge', 'Sassi', 'Omar', 'Education', 'idee');

-- NOTE : après import, exécutez generate_hashes.php une fois
-- pour générer les vrais hash bcrypt dans la table.

CREATE INDEX IF NOT EXISTS idx_users_email        ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role         ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_statut       ON users(statut);
CREATE INDEX IF NOT EXISTS idx_users_email_token  ON users(email_token);
