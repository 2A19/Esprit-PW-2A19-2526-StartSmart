<?php
// Routeur basique
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if ($controller == 'home') {
    ob_start();
    require_once 'views/home.php';
    $viewContent = ob_get_clean();
    require_once 'views/layout.php';
} else {
    $controllerName = ucfirst($controller) . 'Controller';
    $controllerFile = 'controllers/' . $controllerName . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controllerObj = new $controllerName();
        
        if (method_exists($controllerObj, $action)) {
            $controllerObj->$action();
        } else {
            echo "L'action demandée n'existe pas.";
        }
    } else {
        echo "Le contrôleur demandé n'existe pas.";
    }
}
?>
