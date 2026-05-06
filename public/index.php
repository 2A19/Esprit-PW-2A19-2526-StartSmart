<?php

declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

spl_autoload_register(static function (string $className): void {
    $prefixes = [
        'Config\\' => BASE_PATH . '/config/',
        'Controllers\\' => BASE_PATH . '/controllers/',
        'Models\\' => BASE_PATH . '/models/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (str_starts_with($className, $prefix)) {
            $relativeClass = substr($className, strlen($prefix));
            $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';

            if (is_file($file)) {
                require_once $file;
            }

            return;
        }
    }
});

$route = $_GET['route'] ?? '';
$controllerParam = $_GET['controller'] ?? '';
$actionParam = $_GET['action'] ?? '';

if ($route !== '') {
    $parts = explode('/', trim($route, '/'));
    $controllerParam = $parts[0] ?? 'home';
    $actionParam = $parts[1] ?? 'index';
}

$controllerParam = $controllerParam !== '' ? $controllerParam : 'home';
$actionParam = $actionParam !== '' ? $actionParam : 'index';
$actionParam = str_replace('-', '', strtolower($actionParam));

if (strtolower($controllerParam) === 'home') {
    $pageTitle = 'StartSmart - Accueil';
    $activeNav = 'accueil';
    $basePath = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');

    require BASE_PATH . '/views/layouts/header.php';
    require BASE_PATH . '/views/home/index.php';
    require BASE_PATH . '/views/layouts/footer.php';
    exit;
}

$controllerClass = 'Controllers\\' . ucfirst(strtolower($controllerParam)) . 'Controller';
$actionMethod = strtolower($actionParam);

if (!class_exists($controllerClass)) {
    http_response_code(404);
    echo 'Controller not found.';
    exit;
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionMethod)) {
    http_response_code(404);
    echo 'Action not found.';
    exit;
}

$controller->{$actionMethod}();
