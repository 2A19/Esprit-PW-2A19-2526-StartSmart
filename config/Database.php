<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'startsmart';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Connect to server first without dbname to ensure we can create the DB if it doesn't exist
            $this->conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Auto-create database if it doesn't exist
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->conn->exec("USE `" . $this->db_name . "`");
            
            $this->conn->exec("set names utf8mb4");
            $this->syncSchema();
        } catch(PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }

        return $this->conn;
    }

    private function columnExists($tableName, $columnName) {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE ?");
        $stmt->execute([$columnName]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function syncSchema() {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS utilisateur (
            id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            role ENUM('ADMIN', 'CLIENT') NOT NULL
        )");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS categorie (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            num INT(11) NOT NULL,
            typeprojet VARCHAR(100) NOT NULL,
            nom_investisseur VARCHAR(100) NOT NULL,
            created_by_id INT(11) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_categorie_creator (created_by_id)
        )");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS projet (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            num INT(11) NOT NULL,
            nomprojet VARCHAR(100) NOT NULL,
            datedebut DATE NOT NULL,
            datefin DATE NOT NULL,
            budget DECIMAL(15,2) NOT NULL,
            gain DECIMAL(15,2) NOT NULL,
            categorie_id INT(11) NULL,
            auteur_id INT(11) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_projet_categorie (categorie_id),
            INDEX idx_projet_auteur (auteur_id)
        )");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS post (
            id_post INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(200) NOT NULL,
            contenu TEXT NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            auteur_id INT NULL,
            statut VARCHAR(20) NOT NULL DEFAULT 'actif',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_post_auteur (auteur_id)
        )");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS commentaire (
            id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
            contenu TEXT NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            auteur_id INT NULL,
            post_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_commentaire_auteur (auteur_id),
            INDEX idx_commentaire_post (post_id)
        )");

        // Reaction table for likes/dislikes
        $this->conn->exec("CREATE TABLE IF NOT EXISTS reaction (
            id_reaction INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            post_id INT NULL,
            comment_id INT NULL,
            type VARCHAR(20) NOT NULL DEFAULT 'LIKE',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_reaction_user (user_id),
            INDEX idx_reaction_post (post_id)
        )");

        // Notification table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS notification (
            id_notification INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            actor_id INT NULL,
            type VARCHAR(50) NOT NULL,
            reference_id INT NULL,
            message VARCHAR(255) NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notification_user (user_id),
            INDEX idx_notification_is_read (is_read)
        )");

        if (!$this->columnExists('categorie', 'created_by_id')) {
            $this->conn->exec("ALTER TABLE categorie ADD COLUMN created_by_id INT(11) NULL");
        }

        if (!$this->columnExists('projet', 'categorie_id')) {
            $this->conn->exec("ALTER TABLE projet ADD COLUMN categorie_id INT(11) NULL");
        }

        if (!$this->columnExists('projet', 'auteur_id')) {
            $this->conn->exec("ALTER TABLE projet ADD COLUMN auteur_id INT(11) NULL");
        }

        if (!$this->columnExists('projet', 'city')) {
            $this->conn->exec("ALTER TABLE projet ADD COLUMN city VARCHAR(100) NULL");
        }
        if (!$this->columnExists('projet', 'country')) {
            $this->conn->exec("ALTER TABLE projet ADD COLUMN country VARCHAR(100) NULL");
        }
        if (!$this->columnExists('projet', 'latitude')) {
            $this->conn->exec("ALTER TABLE projet ADD COLUMN latitude DECIMAL(10,8) NULL");
        }
        if (!$this->columnExists('projet', 'longitude')) {
            $this->conn->exec("ALTER TABLE projet ADD COLUMN longitude DECIMAL(11,8) NULL");
        }

        if (!$this->columnExists('post', 'auteur_id')) {
            $this->conn->exec("ALTER TABLE post ADD COLUMN auteur_id INT NULL");
        }
        
        if (!$this->columnExists('post', 'city')) {
            $this->conn->exec("ALTER TABLE post ADD COLUMN city VARCHAR(100) NULL");
        }
        if (!$this->columnExists('post', 'country')) {
            $this->conn->exec("ALTER TABLE post ADD COLUMN country VARCHAR(100) NULL");
        }
        if (!$this->columnExists('post', 'latitude')) {
            $this->conn->exec("ALTER TABLE post ADD COLUMN latitude DECIMAL(10,8) NULL");
        }
        if (!$this->columnExists('post', 'longitude')) {
            $this->conn->exec("ALTER TABLE post ADD COLUMN longitude DECIMAL(11,8) NULL");
        }
        if (!$this->columnExists('post', 'categorie_id')) {
            $this->conn->exec("ALTER TABLE post ADD COLUMN categorie_id INT NULL");
        }

        if (!$this->columnExists('commentaire', 'auteur_id')) {
            $this->conn->exec("ALTER TABLE commentaire ADD COLUMN auteur_id INT NULL");
        }

        if (!$this->columnExists('commentaire', 'post_id')) {
            $this->conn->exec("ALTER TABLE commentaire ADD COLUMN post_id INT NULL");
        }

        if (!$this->columnExists('post', 'projet_id')) {
            $this->conn->exec("ALTER TABLE post ADD COLUMN projet_id INT NULL");
            $this->conn->exec("ALTER TABLE post ADD INDEX idx_post_projet (projet_id)");
        }

        // User profile fields
        if (!$this->columnExists('utilisateur', 'bio')) {
            $this->conn->exec("ALTER TABLE utilisateur ADD COLUMN bio TEXT NULL");
        }
        if (!$this->columnExists('utilisateur', 'avatar_url')) {
            $this->conn->exec("ALTER TABLE utilisateur ADD COLUMN avatar_url VARCHAR(255) NULL");
        }
        if (!$this->columnExists('utilisateur', 'city')) {
            $this->conn->exec("ALTER TABLE utilisateur ADD COLUMN city VARCHAR(100) NULL");
        }
        if (!$this->columnExists('utilisateur', 'created_at')) {
            $this->conn->exec("ALTER TABLE utilisateur ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        }

        $tables = ['categorie', 'projet', 'post', 'commentaire'];
        foreach ($tables as $table) {
            if (!$this->columnExists($table, 'created_at')) {
                $this->conn->exec("ALTER TABLE {$table} ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            }
            if (!$this->columnExists($table, 'updated_at')) {
                $this->conn->exec("ALTER TABLE {$table} ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            }
        }

        $this->conn->exec("INSERT INTO utilisateur (id_utilisateur, nom, email, mot_de_passe, role)
            VALUES
                (1, 'Administrateur', 'admin@gmail.com', '\$2y\$10\$admin.placeholder.hash', 'ADMIN'),
                (2, 'Client', 'client@gmail.com', '\$2y\$10\$client.placeholder.hash', 'CLIENT')
            ON DUPLICATE KEY UPDATE
                nom = VALUES(nom),
                email = VALUES(email),
                role = VALUES(role)");
    }
}
?>
