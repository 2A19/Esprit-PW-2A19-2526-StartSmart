<?php
/**
 * StartSmart HR - Main Router
 * Routes all requests to appropriate controllers
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_debug.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path
define('BASE_PATH', __DIR__);
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');

// Get page parameter
$page = $_GET['page'] ?? 'auth';
$action = $_GET['action'] ?? null;

// Parse page into module and action if not explicitly provided
if (!$action && strpos($page, '/') !== false) {
    list($module, $action) = explode('/', $page, 2);
} else {
    $module = explode('/', $page)[0];
    if (!$action) {
        $action = null;
    }
}

// Map pages to controllers and their default actions
$routes = [
    'auth' => ['controller' => 'AuthController', 'default' => 'showLogin'],
    'job-offer' => ['controller' => 'JobOfferController', 'default' => 'listJobOffers'],
    'application' => ['controller' => 'ApplicationController', 'default' => 'listApplications'],
    'employee' => ['controller' => 'EmployeeController', 'default' => 'listEmployees'],
    'frontend' => ['controller' => 'FrontendController', 'default' => 'home'],
    'backend' => ['controller' => 'BackendController', 'default' => 'dashboard']
];

if (isset($routes[$module])) {
    $controllerName = $routes[$module]['controller'];
    $controllerFile = CONTROLLERS_PATH . '/' . $controllerName . '.php';
    $defaultAction = $routes[$module]['default'];

    if (file_exists($controllerFile)) {
        require_once $controllerFile;

        try {
            // Instantiate and call controller
            $controller = new $controllerName();
            
            // Determine which method to call
            $methodToCall = $action ?? $defaultAction;
            
            // Convert action name to camelCase method name
            if (strpos($methodToCall, '-') !== false) {
                $parts = explode('-', $methodToCall);
                $methodToCall = $parts[0];
                for ($i = 1; $i < count($parts); $i++) {
                    $methodToCall .= ucfirst($parts[$i]);
                }
            }

            // Call the method if it exists
            if (method_exists($controller, $methodToCall)) {
                $controller->$methodToCall();
            } else {
                // Call default action
                $controller->$defaultAction();
            }
        } catch (Exception $e) {
            echo "ERROR: " . htmlspecialchars($e->getMessage());
            error_log("Controller Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    } else {
        // Controller not found
        include BASE_PATH . '/views/layouts/header.php';
        ?>
        <div class="container" style="padding: 4rem 0;">
            <div style="text-align: center;">
                <h1>Error</h1>
                <p>Controller not found: <?php echo htmlspecialchars($controllerName); ?></p>
                <a href="index.php?page=auth/login" class="btn btn-primary">Go to Login</a>
            </div>
        </div>
        <?php
        include BASE_PATH . '/views/layouts/footer.php';
    }
} else {
    // Route not found - show login page
    include BASE_PATH . '/views/layouts/header.php';
    ?>
    <div class="container" style="padding: 4rem 0;">
        <div style="text-align: center;">
            <h1>Page Not Found</h1>
            <p>The page you're looking for doesn't exist.</p>
            <a href="index.php?page=auth/login" class="btn btn-primary">Go to Login</a>
        </div>
    </div>
    <?php
    include BASE_PATH . '/views/layouts/footer.php';
}
?>
