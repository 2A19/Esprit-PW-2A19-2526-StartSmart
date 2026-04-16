<?php
/**
 * Database Configuration
 * PDO Connection for StartSmart HR Module
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'startsmart_hr';
    private $user = 'root';
    private $password = '';
    private $pdo;
    private static $instance = null;

    /**
     * Connect to database using PDO
     * @return PDO
     */
    public function connect() {
        $this->pdo = null;

        try {
            $this->pdo = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Erreur de connexion: ' . $e->getMessage());
        }

        return $this->pdo;
    }

    /**
     * Get PDO connection
     * @return PDO
     */
    public function getPDO() {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Static method for backward compatibility
     * @return PDO
     */
    public static function getConnexion() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->getPDO();
    }
}

// Create alias for compatibility
class config extends Database {
}
?>
