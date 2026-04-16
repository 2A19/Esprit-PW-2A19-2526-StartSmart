-- ============================================================
--  StartSmart – Base de données XAMPP
--  Importer via phpMyAdmin > Importer > startsmart_db.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS startsmart_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE startsmart_db;

-- ------------------------------------------------------------
-- TABLE users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    nom                VARCHAR(100)  NOT NULL,
    prenom             VARCHAR(100)  NOT NULL,
    email              VARCHAR(150)  NOT NULL UNIQUE,
    password           VARCHAR(255)  NOT NULL,
    telephone          VARCHAR(20)   DEFAULT NULL,
    date_naissance     DATE          DEFAULT NULL,
    role               ENUM('user','admin') DEFAULT 'user',
    statut             ENUM('actif','inactif','banni') DEFAULT 'actif',
    date_inscription   DATETIME      DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME      DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE startups
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS startups (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom_startup         VARCHAR(200) NOT NULL,
    nom_responsable     VARCHAR(100) NOT NULL,
    prenom_responsable  VARCHAR(100) NOT NULL,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password            VARCHAR(255) NOT NULL,
    telephone           VARCHAR(20)  DEFAULT NULL,
    secteur             VARCHAR(100) DEFAULT NULL,
    site_web            VARCHAR(255) DEFAULT NULL,
    stade               ENUM('idee','prototype','mvp','croissance','scale') DEFAULT 'idee',
    statut              ENUM('actif','inactif','verifie') DEFAULT 'actif',
    date_inscription    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion  DATETIME     DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Données de test  (mot de passe en clair : Test1234!)
-- Le hash bcrypt ci-dessous correspond à "Test1234!"
-- ------------------------------------------------------------
INSERT INTO users (nom, prenom, email, password, telephone, role, statut) VALUES
('Ben Ali',  'Ahmed', 'ahmed@email.com',       '$2y$10$YourHashHere.replaceMe', '55123456', 'user',  'actif'),
('Trabelsi', 'Sonia', 'sonia@email.com',       '$2y$10$YourHashHere.replaceMe', '22987654', 'user',  'actif'),
('Mansouri', 'Karim', 'karim@email.com',       '$2y$10$YourHashHere.replaceMe', '99456123', 'user',  'inactif'),
('Admin',    'Super', 'admin@startsmart.com',  '$2y$10$YourHashHere.replaceMe', NULL,       'admin', 'actif');

INSERT INTO startups (nom_startup, nom_responsable, prenom_responsable, email, password, telephone, secteur, stade, statut) VALUES
('TechTunisia', 'Chaabane', 'Mehdi', 'contact@techtunisia.tn', '$2y$10$YourHashHere.replaceMe', '55001122', 'Technologie', 'mvp',       'verifie'),
('GreenAgri',   'Hamdi',    'Leila', 'info@greenagri.tn',      '$2y$10$YourHashHere.replaceMe', '22334455', 'Agriculture', 'prototype', 'actif'),
('EduBridge',   'Sassi',    'Omar',  'hello@edubridge.tn',     '$2y$10$YourHashHere.replaceMe', NULL,       'Education',   'idee',      'actif');

-- NOTE : après import, exécutez generate_hashes.php une fois
-- pour générer les vrais hash bcrypt dans la table.

CREATE INDEX idx_users_email   ON users(email);
CREATE INDEX idx_startup_email ON startups(email);
