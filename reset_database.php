<?php
/**
 * Database Reset Script
 * WARNING: This will drop and recreate the entire database!
 * Only run this if you want to completely reset your database to initial state.
 */

$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'startsmart';

try {
    // Connect to MySQL server without selecting a database
    $pdo = new PDO("mysql:host=" . $host, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server...\n";
    
    // Step 1: Drop the corrupted database
    echo "Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `" . $db_name . "`");
    echo "✓ Database dropped\n\n";
    
    // Step 2: Create fresh database
    echo "Creating new database...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . $db_name . "`");
    echo "✓ Database created\n\n";
    
    // Step 3: Create tables
    echo "Creating tables...\n";
    
    // utilisateur table
    $pdo->exec("CREATE TABLE utilisateur (
        id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        role ENUM('ADMIN', 'CLIENT') NOT NULL
    )");
    echo "✓ Table 'utilisateur' created\n";
    
    // categorie table
    $pdo->exec("CREATE TABLE categorie (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        num INT(11) NOT NULL,
        typeprojet VARCHAR(100) NOT NULL,
        nom_investisseur VARCHAR(100) NOT NULL,
        created_by_id INT(11) NOT NULL,
        CONSTRAINT fk_categorie_utilisateur FOREIGN KEY (created_by_id) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
        INDEX idx_categorie_creator (created_by_id)
    )");
    echo "✓ Table 'categorie' created\n";
    
    // projet table
    $pdo->exec("CREATE TABLE projet (
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
    )");
    echo "✓ Table 'projet' created\n";
    
    // post table
    $pdo->exec("CREATE TABLE post (
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
    )");
    echo "✓ Table 'post' created\n";
    
    // commentaire table
    $pdo->exec("CREATE TABLE commentaire (
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
    )");
    echo "✓ Table 'commentaire' created\n";

    // reaction table
    $pdo->exec("CREATE TABLE reaction (
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
    )");
    echo "✓ Table 'reaction' created\n\n";

    
    // Step 4: Insert initial data
    echo "Inserting initial data...\n";
    $pdo->exec("INSERT INTO utilisateur (id_utilisateur, nom, email, mot_de_passe, role) VALUES
        (1, 'Administrateur', 'admin@gmail.com', '\$2y\$10\$admin.placeholder.hash', 'ADMIN'),
        (2, 'Client', 'client@gmail.com', '\$2y\$10\$client.placeholder.hash', 'CLIENT'),
        (3, 'Administrateur 2', 'admin2@gmail.com', '\$2y\$10\$admin2.placeholder.hash', 'ADMIN'),
        (4, 'Nouveau Client', 'client2@gmail.com', '\$2y\$10\$client2.placeholder.hash', 'CLIENT')");
    echo "✓ Initial users inserted\n";
    
    // Insert default categories
    $pdo->exec("INSERT INTO categorie (id, num, typeprojet, nom_investisseur, created_by_id) VALUES
        (1, 1, 'Web Development', 'Tech Investor', 1),
        (2, 2, 'Mobile App', 'Mobile Investor', 1),
        (3, 3, 'Cloud Infrastructure', 'Cloud Investor', 1),
        (4, 4, 'AI/ML Project', 'Innovation Investor', 1),
        (5, 5, 'Consulting', 'Business Investor', 1)");
    echo "✓ Default categories inserted\n\n";
    
    echo "========================================\n";
    echo "✓✓✓ DATABASE RESET SUCCESSFUL! ✓✓✓\n";
    echo "========================================\n";
    echo "\nYour database has been completely restored to initial state.\n";
    echo "\nDefault Credentials:\n";
    echo "- Email: admin@gmail.com | ID: 1 (ADMIN) | Password: admin123\n";
    echo "- Email: admin2@gmail.com | ID: 3 (ADMIN) | Password: admin123\n";
    echo "- Email: client@gmail.com | ID: 2 (CLIENT) | Password: client123\n";
    echo "- Email: client2@gmail.com | ID: 4 (CLIENT) | Password: client123\n";
    echo "\nNote: Password hashes are placeholders. Update them with bcrypt hashes if needed.\n";
    echo "\nThis file should be DELETED after running for security reasons!\n";
    
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Failed to reset database. Check your MySQL connection.\n";
    exit(1);
}
?>
