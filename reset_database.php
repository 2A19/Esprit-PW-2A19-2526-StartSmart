<?php
/**
 * Database Reset Script
 * WARNING: This will drop and recreate the entire database!
 * Only run this if you want to completely reset your database to initial state.
 */

$hosts = ['127.0.0.1', 'localhost'];
$ports = [3306, 3307];
$username = 'root';
$password = '';
$db_name = 'startsmart_db';

try {
    $pdo = null;
    $lastException = null;

    foreach ($hosts as $host) {
        foreach ($ports as $port) {
            try {
                $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                echo "Connected to MySQL server at {$host}:{$port}...\n";
                break 2;
            } catch (PDOException $exception) {
                $lastException = $exception;
            }
        }
    }

    if (!$pdo) {
        throw new RuntimeException('Unable to connect to MySQL. Detail: ' . ($lastException ? $lastException->getMessage() : 'unknown error'));
    }
    
    echo "Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `" . $db_name . "`");
    echo "✓ Database dropped\n\n";
    
    echo "Creating new database...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created\n\n";
    
    require_once __DIR__ . '/config/Database.php';
    $database = new Database();
    $database->getConnection();
    echo "✓ Schema synchronized through the app bootstrap\n\n";
    
    echo "========================================\n";
    echo "✓✓✓ DATABASE RESET SUCCESSFUL! ✓✓✓\n";
    echo "========================================\n";
    echo "\nYour database has been completely restored to initial state.\n";
    echo "\nDefault Credentials:\n";
    echo "- Email: admin@startsmart.com | Role: admin | Password: admin123\n";
    echo "- Email: ahmed@email.com | Role: user | Password: user123\n";
    echo "- Email: contact@techtunisia.tn | Role: startup | Password: startup123\n";
    echo "\nThis file should be DELETED after running for security reasons!\n";
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Failed to reset database. Check your MySQL connection.\n";
    exit(1);
}
?>
