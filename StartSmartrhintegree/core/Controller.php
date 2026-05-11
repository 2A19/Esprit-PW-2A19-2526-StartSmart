<?php
/**
 * Base Controller Class
 * Parent class for all controllers
 */

class Controller {
    protected $db;

    public function __construct() {
        // Initialize database connection
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getPDO();
    }

    /**
     * Render view with data
     * @param string $view
     * @param array $data
     * @return void
     */
    protected function render($view, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $view . '.php';
    }

    /**
     * Redirect to another page
     * @param string $location
     * @return void
     */
    protected function redirect($location) {
        header("Location: $location");
        exit();
    }

    /**
     * Set session values
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     * @param string $key
     * @return mixed|null
     */
    protected function getSession($key) {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user
     * @return mixed|null
     */
    protected function getCurrentUser() {
        if ($this->isAuthenticated()) {
            return $_SESSION['user_id'];
        }
        return null;
    }

    /**
     * Send JSON response
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}
?>
