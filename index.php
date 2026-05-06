<?php
require_once 'config/Auth.php';

require_once 'services/AuthService.php';

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

if ($controller == 'home') {
    ob_start();
    require_once 'views/home.php';
    $viewContent = ob_get_clean();
    $pageTitle = "Accueil";
    require_once 'views/layout.php';
} else {
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
}
?>
