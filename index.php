<?php
require_once 'config/Auth.php';

require_once 'services/AuthService.php';
require_once 'config/Database.php';

// Routeur
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// Defaults
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if (!empty($url)) {
    $parts = explode('/', $url);
    $controller = $parts[0] ? $parts[0] : 'home';
    $action = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : 'index';
    
    // Support for IDs in URL e.g. /projet/edit/10
    if (isset($parts[2]) && is_numeric($parts[2])) {
        $_GET['id'] = $parts[2];
    }
}

// Convert "projects" to "projet" to match the controller name
if ($controller === 'projects') $controller = 'projet';
if ($controller === 'categories') $controller = 'categorie';
if ($controller === 'posts') $controller = 'post';
if ($controller === 'comments') $controller = 'commentaire';

// Special-case: render certain frontend-only views directly so they don't execute local DB logic.
// These views fetch data from the backend via the proxy and keep frontend isolated.
$frontendOnlyRoutes = [
    'post' => []
];

$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
$contentTypeHeader = $_SERVER['CONTENT_TYPE'] ?? '';
$isJsonRequest = (isset($_GET['format']) && $_GET['format'] === 'json')
    || stripos($acceptHeader, 'application/json') !== false
    || stripos($contentTypeHeader, 'application/json') !== false;

if (!$isJsonRequest && isset($frontendOnlyRoutes[$controller]) && in_array($action, $frontendOnlyRoutes[$controller], true)) {
    $pageTitle = ucfirst($controller) . ' - ' . ucfirst($action);
    ob_start();
    $viewFile = 'views/' . $controller . '/' . $action . '.php';
    if (file_exists($viewFile)) {
        require_once $viewFile;
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
        exit;
    }
    // fallback to normal flow if view missing
}

if ($controller == 'home') {
    // Frontend-only home: data is fetched client-side from backend via the proxy.
    ob_start();
    require_once 'views/home.php';
    $viewContent = ob_get_clean();
    $pageTitle = "Accueil";
    require_once 'views/layout.php';
} else {
    try {
        $controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $controller))) . 'Controller';
        $controllerFile = 'controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerObj = new $controllerName();
            
            if (method_exists($controllerObj, $action)) {
                $controllerObj->$action();
            } else {
                http_response_code(404);
                echo "L'action demandée n'existe pas.";
            }
        } else {
            http_response_code(404);
            echo "Le contrôleur demandé n'existe pas.";
        }
    } catch (Throwable $exception) {
        $isDbConnectionError = $exception instanceof RuntimeException
            && stripos($exception->getMessage(), 'Database connection failed') !== false;

        if ($isDbConnectionError) {
            http_response_code(503);
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Base de données indisponible</title><style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0b1c48;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;padding:24px}.box{max-width:720px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;box-shadow:0 20px 60px rgba(11,28,72,.12)}h1{margin:0 0 12px;font-size:28px}p{color:#475569;line-height:1.7;margin:0 0 12px}a{color:#0288d1;text-decoration:none;font-weight:700}</style></head><body><div class="box"><h1>Base de données indisponible</h1><p>StartSmart ne peut pas se connecter au serveur MySQL pour le moment. Vérifiez que MySQL est bien démarré dans XAMPP et que le port configuré correspond à votre installation.</p><p><a href="index.php">Retour à l’accueil</a></p></div></body></html>';
            exit;
        }

        http_response_code(500);
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Erreur interne</title><style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0b1c48;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;padding:24px}.box{max-width:720px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;box-shadow:0 20px 60px rgba(11,28,72,.12)}h1{margin:0 0 12px;font-size:28px}p{color:#475569;line-height:1.7;margin:0 0 12px}a{color:#0288d1;text-decoration:none;font-weight:700}</style></head><body><div class="box"><h1>Erreur interne</h1><p>Une erreur applicative est survenue. Réessayez dans un instant.</p><p><a href="index.php">Retour à l’accueil</a></p></div></body></html>';
    }
}
?>
