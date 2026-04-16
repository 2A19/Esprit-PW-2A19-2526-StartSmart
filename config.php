<?php
/**
 * config.php - Fichier de configuration de l'application
 */

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

// Activation des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
