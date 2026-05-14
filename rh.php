<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config/session.php';

// ── Global access guard ──────────────────────────────────────────
// Modules accessible sans connexion
$_publicModules = ['auth', 'frontend'];

// Détecter le module demandé (avant le routing complet)
$_requestedPage   = $_GET['page'] ?? '';
$_requestedModule = strpos($_requestedPage, '/') !== false
    ? explode('/', $_requestedPage, 2)[0]
    : $_requestedPage;

$_loggedIn = !empty($_SESSION['user_id']);
$_role     = strtolower(trim((string)($_SESSION['user_role'] ?? '')));

if (!in_array($_requestedModule, $_publicModules, true)) {
    // Non connecté → page de login
    if (!$_loggedIn) {
        header('Location: login.php');
        exit;
    }
    // Utilisateur normal → accès limité aux modules candidat uniquement
    $_userAllowedModules = ['job-offer', 'application', 'frontend', 'user'];
    if ($_role === 'user' && !in_array($_requestedModule, $_userAllowedModules, true)) {
        header('Location: rh.php?page=job-offer/index');
        exit;
    }
}
// ────────────────────────────────────────────────────────────────

if (!isset($_GET['page'])) {
    if ($_loggedIn && in_array($_role, ['admin', 'rh'], true)) {
        header('Location: rh.php?page=backend/admin');
        exit;
    } elseif ($_loggedIn && $_role === 'startup') {
        $_GET['page'] = 'backend/dashboard';
    } elseif ($_loggedIn && $_role === 'user') {
        $_GET['page'] = 'job-offer/index';
    } elseif (!$_loggedIn) {
        $_GET['page'] = 'frontend/home';
    }
}

$page = $_GET['page'];
$action = $_GET['action'] ?? null;

if (strpos($page, '/') !== false) {
    [$module, $pageAction] = explode('/', $page, 2);
    $action = $action ?: $pageAction;
} else {
    $module = $page;
}

$routes = [
    'auth' => ['AuthController', 'showLogin'],
    'job-offer' => ['JobOfferController', 'listJobOffers'],
    'application' => ['ApplicationController', 'listApplications'],
    'employee' => ['EmployeeController', 'listEmployees'],
    'user' => ['UserController', 'profile'],
    'frontend' => ['FrontendController', 'home'],
    'backend' => ['BackendController', 'dashboard'],
];

if (!isset($routes[$module])) {
    http_response_code(404);
    exit('RH module not found.');
}

[$controllerName, $defaultAction] = $routes[$module];
$method = $action ?: $defaultAction;
if (strpos($method, '-') !== false) {
    $parts = explode('-', $method);
    $method = array_shift($parts);
    foreach ($parts as $part) {
        $method .= ucfirst($part);
    }
}

$controllerFile = __DIR__ . '/controllers/rh/' . $controllerName . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(500);
    exit('RH controller missing: ' . htmlspecialchars($controllerName));
}

require_once $controllerFile;
$controller = new $controllerName();
if (!method_exists($controller, $method)) {
    $method = $defaultAction;
}
$controller->$method();
