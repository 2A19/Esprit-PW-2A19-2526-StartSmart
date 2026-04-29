<?php
/**
 * api/users.php – Entry point for user management (Form-based)
 * Now handles both regular users and startup accounts (role='startup' in users table)
 * Accepts form submissions and redirects back to dashboard
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Authentication check
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['form_errors'] = ['general' => 'Accès non autorisé.'];
    header('Location: /startsmart/views/back/dashboard.php');
    exit;
}

require_once __DIR__ . '/../controllers/UserController.php';

$controller = new UserController();
$action     = $_GET['action'] ?? '';
$method     = $_SERVER['REQUEST_METHOD'];

// Handle user actions
if ($action === 'list_users' && $method === 'GET') {
    $controller->listUsers();
    header('Location: /startsmart/views/back/dashboard.php?tab=users');
    exit;
}

if ($action === 'create_user' && $method === 'POST') {
    $controller->createUserAction($_POST);
    header('Location: /startsmart/views/back/dashboard.php?tab=users');
    exit;
}

if ($action === 'update_user' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $controller->updateUserAction($id, $_POST);
    header('Location: /startsmart/views/back/dashboard.php?tab=users');
    exit;
}

if ($action === 'delete_user' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $controller->deleteUserAction($id);
    header('Location: /startsmart/views/back/dashboard.php?tab=users');
    exit;
}

// Handle startup actions (now using the same User structure)
if ($action === 'list_startups' && $method === 'GET') {
    $controller->listStartups();
    header('Location: /startsmart/views/back/dashboard.php?tab=startups');
    exit;
}

if ($action === 'update_startup' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $controller->updateStartupAction($id, $_POST);
    header('Location: /startsmart/views/back/dashboard.php?tab=startups');
    exit;
}

if ($action === 'delete_startup' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $controller->deleteUserAction($id); // Use same delete, just check role
    header('Location: /startsmart/views/back/dashboard.php?tab=startups');
    exit;
}

// Default: redirect to dashboard
header('Location: /startsmart/views/back/dashboard.php');
exit;


