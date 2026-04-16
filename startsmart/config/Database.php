<?php
/**
 * Database – Connexion PDO (Singleton)
 * Seule méthode de connexion autorisée : PDO
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private string $host   = 'localhost';
    private string $dbname = 'startsmart_db';
    private string $user   = 'root';
    private string $pass   = '';           // XAMPP : vide par défaut
    private string $charset = 'utf8mb4';

    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die('<h1>Database Connection Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>');
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private function __clone() {}
}
