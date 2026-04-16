<?php
/**
 * api/auth.php – Authentication endpoints (Form-based, No JSON)
 *
 * POST ?action=login
 * POST ?action=register_user
 * POST ?action=register_startup
 * POST ?action=logout
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController();
$action     = $_GET['action'] ?? '';

switch($action) {
    case 'login':
        $controller->login();
        break;
    case 'register_user':
        $controller->registerUser();
        break;
    case 'register_startup':
        $controller->registerStartup();
        break;
    case 'logout':
        $controller->logout();
        break;
    default:
        header('Location: /startsmart/views/auth/login.php');
        exit;
}

