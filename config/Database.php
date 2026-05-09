<?php
class Database {
    private $hosts = ['127.0.0.1', 'localhost'];
    private $ports = [3306, 3307];
    private $db_name = 'startsmart';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;

        $lastException = null;

        foreach ($this->hosts as $host) {
            foreach ($this->ports as $port) {
                try {
                    $dsn = "mysql:host={$host};port={$port}";
                    $this->conn = new PDO($dsn, $this->username, $this->password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);

                    $this->conn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $this->conn->exec("USE `" . $this->db_name . "`");
                    $this->conn->exec("SET NAMES utf8mb4");
                    $this->syncSchema();
                    return $this->conn;
                } catch(PDOException $exception) {
                    $lastException = $exception;
                    $this->conn = null;
                }
            }
        }

        $message = 'Impossible de se connecter à MySQL sur ' . implode(', ', array_map(function($host) {
            return $host;
        }, $this->hosts)) . ' avec les ports ' . implode(', ', $this->ports) . '.';
        if ($lastException) {
            $message .= ' Détail: ' . $lastException->getMessage();
        }

        throw new RuntimeException($message);
    }

    private function columnExists($tableName, $columnName) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = :column_name"
        );
        $stmt->execute([
            ':table_name' => $tableName,
            ':column_name' => $columnName,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function syncSchema() {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS utilisateur (
            id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            role ENUM('ADMIN', 'CLIENT') NOT NULL,
            bio TEXT NULL,
            avatar_url VARCHAR(255) NULL,
            city VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS categorie (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            num INT(11) NOT NULL,
            typeprojet VARCHAR(100) NOT NULL,
            nom_investisseur VARCHAR(100) NOT NULL,
            created_by_id INT(11) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_categorie_creator (created_by_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS projet (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            num INT(11) NOT NULL,
            nomprojet VARCHAR(100) NOT NULL,
            description LONGTEXT NULL,
            datedebut DATE NOT NULL,
            datefin DATE NOT NULL,
            budget DECIMAL(15,2) NOT NULL,
            gain DECIMAL(15,2) NOT NULL,
            categorie_id INT(11) NULL,
            auteur_id INT(11) NULL,
            city VARCHAR(100) NULL,
            country VARCHAR(100) NULL,
            latitude DECIMAL(10,8) NULL,
            longitude DECIMAL(11,8) NULL,
            statut ENUM('draft', 'actif', 'archived', 'deleted') NOT NULL DEFAULT 'actif',
            etape VARCHAR(50) NOT NULL DEFAULT 'idea',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_projet_categorie (categorie_id),
            INDEX idx_projet_auteur (auteur_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS post (
            id_post INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(200) NOT NULL,
            topic VARCHAR(50) DEFAULT 'General',
            contenu TEXT NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            auteur_id INT NULL,
            statut VARCHAR(20) NOT NULL DEFAULT 'actif',
            likes_count INT DEFAULT 0,
            city VARCHAR(100) NULL,
            country VARCHAR(100) NULL,
            latitude DECIMAL(10,8) NULL,
            longitude DECIMAL(11,8) NULL,
            categorie_id INT NULL,
            projet_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_post_auteur (auteur_id),
            INDEX idx_post_projet (projet_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS commentaire (
            id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
            contenu TEXT NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            auteur_id INT NULL,
            post_id INT NULL,
            parent_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_commentaire_auteur (auteur_id),
            INDEX idx_commentaire_post (post_id),
            INDEX idx_commentaire_parent (parent_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS piece_jointe (
            id_piece_jointe INT AUTO_INCREMENT PRIMARY KEY,
            nom_fichier VARCHAR(255) NOT NULL,
            chemin_fichier VARCHAR(255) NOT NULL,
            type_fichier VARCHAR(100) NOT NULL,
            taille_fichier INT NOT NULL,
            post_id INT NULL,
            commentaire_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_piece_jointe_post (post_id),
            INDEX idx_piece_jointe_commentaire (commentaire_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS reaction (
            id_reaction INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            post_id INT NULL,
            comment_id INT NULL,
            type VARCHAR(20) NOT NULL DEFAULT 'LIKE',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_reaction_user (user_id),
            INDEX idx_reaction_post (post_id),
            INDEX idx_reaction_comment (comment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS competence (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(50) NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$this->columnExists('competence', 'category')) {
            $this->conn->exec("ALTER TABLE competence ADD COLUMN category VARCHAR(50) NULL");
        }
        if (!$this->columnExists('competence', 'description')) {
            $this->conn->exec("ALTER TABLE competence ADD COLUMN description TEXT NULL");
        }

        $this->conn->exec("CREATE TABLE IF NOT EXISTS utilisateur_competence (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            competence_id INT NOT NULL,
            UNIQUE KEY unique_user_competence (utilisateur_id, competence_id),
            INDEX idx_utilisateur_id (utilisateur_id),
            INDEX idx_competence_id (competence_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS projet_competence (
            id INT AUTO_INCREMENT PRIMARY KEY,
            projet_id INT NOT NULL,
            competence_id INT NOT NULL,
            UNIQUE KEY unique_project_competence (projet_id, competence_id),
            INDEX idx_projet_id (projet_id),
            INDEX idx_competence_id (competence_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS utilisateur_interet (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            categorie_id INT NOT NULL,
            interest_score INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_interest (utilisateur_id, categorie_id),
            INDEX idx_utilisateur_id (utilisateur_id),
            INDEX idx_categorie_id (categorie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$this->columnExists('utilisateur_interet', 'interest_score')) {
            $this->conn->exec("ALTER TABLE utilisateur_interet ADD COLUMN interest_score INT DEFAULT 1");
        }

        $this->conn->exec("CREATE TABLE IF NOT EXISTS projet_commentaire (
            id INT AUTO_INCREMENT PRIMARY KEY,
            projet_id INT NOT NULL,
            auteur_id INT NOT NULL,
            contenu LONGTEXT NOT NULL,
            parent_id INT DEFAULT NULL,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut ENUM('actif', 'supprime', 'signale') DEFAULT 'actif',
            INDEX idx_projet_id (projet_id),
            INDEX idx_auteur_id (auteur_id),
            INDEX idx_parent_id (parent_id),
            INDEX idx_date_creation (date_creation)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS projet_reaction (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            projet_id INT NOT NULL,
            type ENUM('LIKE', 'DISLIKE') DEFAULT 'LIKE',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_projet_reaction (user_id, projet_id),
            INDEX idx_projet_id (projet_id),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS skill (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(50) NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS user_skill (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            skill_id INT NOT NULL,
            proficiency_level ENUM('beginner', 'intermediate', 'expert') DEFAULT 'beginner',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_skill (user_id, skill_id),
            INDEX idx_user_id (user_id),
            INDEX idx_skill_id (skill_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS project_skill (
            id INT AUTO_INCREMENT PRIMARY KEY,
            projet_id INT NOT NULL,
            skill_id INT NOT NULL,
            required BOOLEAN DEFAULT TRUE,
            priority INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_project_skill (projet_id, skill_id),
            INDEX idx_projet_id (projet_id),
            INDEX idx_skill_id (skill_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS user_interest (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            categorie_id INT NOT NULL,
            interest_score INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_interest (user_id, categorie_id),
            INDEX idx_user_id (user_id),
            INDEX idx_categorie_id (categorie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$this->columnExists('user_interest', 'interest_score')) {
            $this->conn->exec("ALTER TABLE user_interest ADD COLUMN interest_score INT DEFAULT 1");
        }

        $this->conn->exec("CREATE TABLE IF NOT EXISTS project_match_cache (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            projet_id INT NOT NULL,
            match_score DECIMAL(5,2) NULL,
            skill_score DECIMAL(5,2) NULL,
            interest_score DECIMAL(5,2) NULL,
            activity_score DECIMAL(5,2) NULL,
            calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            UNIQUE KEY unique_user_projet_match (user_id, projet_id),
            INDEX idx_user_id (user_id),
            INDEX idx_match_score (match_score),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS user_match_action (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            projet_id INT NOT NULL,
            action ENUM('interested', 'skipped', 'applied', 'rejected') DEFAULT 'skipped',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_projet_id (projet_id),
            INDEX idx_action (action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("INSERT INTO utilisateur (id_utilisateur, nom, email, mot_de_passe, role)
            VALUES
                (1, 'Administrateur', 'admin@gmail.com', '\$2y\$10\$admin.placeholder.hash', 'ADMIN'),
                (2, 'Client', 'client@gmail.com', '\$2y\$10\$client.placeholder.hash', 'CLIENT')
            ON DUPLICATE KEY UPDATE
                nom = VALUES(nom),
                email = VALUES(email),
                role = VALUES(role)");

        $this->conn->exec("INSERT INTO categorie (id, num, typeprojet, nom_investisseur, created_by_id)
            VALUES
                (1, 1, 'Web Development', 'Tech Investor', 1),
                (2, 2, 'Mobile App', 'Mobile Investor', 1),
                (3, 3, 'Cloud Infrastructure', 'Cloud Investor', 1),
                (4, 4, 'AI/ML Project', 'Innovation Investor', 1),
                (5, 5, 'Consulting', 'Business Investor', 1)
            ON DUPLICATE KEY UPDATE
                typeprojet = VALUES(typeprojet),
                nom_investisseur = VALUES(nom_investisseur),
                created_by_id = VALUES(created_by_id)");

        $defaultCompetences = [
            ['PHP', 'development', 'Server-side PHP programming'],
            ['JavaScript', 'development', 'Frontend JavaScript/Node.js'],
            ['Python', 'development', 'Python backend/data science'],
            ['React', 'development', 'React frontend framework'],
            ['Laravel', 'development', 'Laravel PHP framework'],
            ['SQL', 'development', 'Database design and queries'],
            ['DevOps', 'development', 'Deployment, Docker, CI/CD'],
            ['API Development', 'development', 'REST API design'],
            ['UI Design', 'design', 'User interface design'],
            ['UX Design', 'design', 'User experience research'],
            ['Graphic Design', 'design', 'Visual design and branding'],
            ['Figma', 'design', 'Figma design tool expertise'],
            ['Mobile Design', 'design', 'Mobile app UI/UX design'],
            ['Social Media Marketing', 'marketing', 'Social media strategy and management'],
            ['Content Marketing', 'marketing', 'Blog, video, content creation'],
            ['SEO', 'marketing', 'Search engine optimization'],
            ['SEM', 'marketing', 'Google Ads, paid advertising'],
            ['Analytics', 'marketing', 'Data analytics and reporting'],
            ['Project Management', 'business', 'Project planning and management'],
            ['Business Strategy', 'business', 'Strategic planning'],
            ['Sales', 'business', 'Sales and business development'],
            ['Finance', 'business', 'Financial planning and accounting'],
            ['HR', 'business', 'Human resources management']
        ];

        $values = [];
        foreach ($defaultCompetences as $competence) {
            $name = addslashes($competence[0]);
            $category = addslashes($competence[1]);
            $description = addslashes($competence[2]);
            $values[] = "('{$name}', '{$category}', '{$description}')";
        }
        $this->conn->exec("INSERT IGNORE INTO skill (name, category, description) VALUES " . implode(', ', $values));

        $defaultSkills = [
            ['PHP', 'development', 'Server-side PHP programming'],
            ['JavaScript', 'development', 'Frontend JavaScript/Node.js'],
            ['Python', 'development', 'Python backend/data science'],
            ['React', 'development', 'React frontend framework'],
            ['Laravel', 'development', 'Laravel PHP framework'],
            ['SQL', 'development', 'Database design and queries'],
            ['DevOps', 'development', 'Deployment, Docker, CI/CD'],
            ['API Development', 'development', 'REST API design'],
            ['UI Design', 'design', 'User interface design'],
            ['UX Design', 'design', 'User experience research'],
            ['Graphic Design', 'design', 'Visual design and branding'],
            ['Figma', 'design', 'Figma design tool expertise'],
            ['Mobile Design', 'design', 'Mobile app UI/UX design'],
            ['Social Media Marketing', 'marketing', 'Social media strategy and management'],
            ['Content Marketing', 'marketing', 'Blog, video, content creation'],
            ['SEO', 'marketing', 'Search engine optimization'],
            ['SEM', 'marketing', 'Google Ads, paid advertising'],
            ['Analytics', 'marketing', 'Data analytics and reporting'],
            ['Project Management', 'business', 'Project planning and management'],
            ['Business Strategy', 'business', 'Strategic planning'],
            ['Sales', 'business', 'Sales and business development'],
            ['Finance', 'business', 'Financial planning and accounting'],
            ['HR', 'business', 'Human resources management']
        ];

        $skillValues = [];
        foreach ($defaultSkills as $skill) {
            $name = addslashes($skill[0]);
            $category = addslashes($skill[1]);
            $description = addslashes($skill[2]);
            $skillValues[] = "('{$name}', '{$category}', '{$description}')";
        }
        $this->conn->exec("INSERT IGNORE INTO competence (nom, category, description) VALUES " . implode(', ', $skillValues));

        $this->conn->exec("INSERT IGNORE INTO utilisateur_competence (utilisateur_id, competence_id) VALUES (1, 1), (2, 2)");
        $this->conn->exec("INSERT IGNORE INTO projet_competence (projet_id, competence_id) VALUES (1, 1)");
        $this->conn->exec("INSERT IGNORE INTO utilisateur_interet (utilisateur_id, categorie_id, interest_score) VALUES (1, 1, 3), (2, 2, 2)");
        $this->conn->exec("INSERT IGNORE INTO user_skill (user_id, skill_id, proficiency_level) VALUES (1, 1, 'expert'), (2, 2, 'intermediate')");
        $this->conn->exec("INSERT IGNORE INTO project_skill (projet_id, skill_id, required, priority) VALUES (1, 1, 1, 1)");
    }
}
?>
