<?php
/**
 * Classe Database - Connexion PDO à la base de données
 * Utilise le pattern Singleton pour une seule instance de connexion
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    // Paramètres de connexion
    private $host = 'localhost';
    private $db_name = 'startsmart';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    
    /**
     * Constructeur privé pour empêcher l'instantiation directe
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Méthode pour obtenir l'instance unique (Singleton)
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Connexion à la base de données avec PDO
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    
    /**
     * Récupère l'objet PDO pour les requêtes
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Prépare une requête SQL
     * @param string $sql La requête SQL
     * @return PDOStatement
     */
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    /**
     * Exécute une requête préparée
     * @param PDOStatement $stmt La requête préparée
     * @param array $params Les paramètres
     * @return bool
     */
    public function execute($stmt, $params = []) {
        return $stmt->execute($params);
    }
    
    /**
     * Empêche le clonage
     */
    private function __clone() {}
    
    /**
     * Empêche la désérialisation
     */
    public function __wakeup() {
        throw new Exception("Impossible de désérialiser une instance de Database");
    }
}
?>
