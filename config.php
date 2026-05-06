<?php
/**
 * config.php - Fichier de configuration de l'application
 */

// Charger les variables d'environnement depuis .env
if (file_exists(__DIR__ . '/.env')) {
    $env_file = __DIR__ . '/.env';
    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($env_lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parser les variables
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'startsmart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuration de l'application
define('APP_NAME', 'StartSmart');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/startsmart');

// Configuration des sessions
define('SESSION_TIMEOUT', 3600); // 1 heure

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Configuration Twilio SMS (depuis .env)
define('TWILIO_SID', $_ENV['TWILIO_SID'] ?? 'NOT_SET');
define('TWILIO_AUTH_TOKEN', $_ENV['TWILIO_AUTH_TOKEN'] ?? 'NOT_SET');
define('TWILIO_PHONE_NUMBER', $_ENV['TWILIO_PHONE_NUMBER'] ?? 'NOT_SET');
define('ADMIN_PHONE_NUMBER', $_ENV['ADMIN_PHONE_NUMBER'] ?? '');

// Activation des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
