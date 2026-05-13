<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/User.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function profile() {
        // Public profile view
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : currentUserId();
        
        if (!$this->user->readById($userId)) {
            http_response_code(404);
            exit('User not found.');
        }

        $stats = $this->user->getStats($userId);
        $recentPosts = $this->user->getRecentPosts($userId, 5);
        $recentComments = $this->user->getRecentComments($userId, 5);

        $pageTitle = "Profil - " . htmlspecialchars($this->user->nom);
        ob_start();
        require_once 'views/user/profile.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function edit() {
        if (!isLoggedIn()) {
            header('Location: login.php');
            exit;
        }

        $userId = currentUserId();
        if (!$this->user->readById($userId)) {
            http_response_code(404);
            exit('User not found.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->bio = trim($_POST['bio'] ?? '');
            $this->user->avatar_url = trim($_POST['avatar_url'] ?? '');
            $this->user->city = trim($_POST['city'] ?? '');

            if ($this->user->update()) {
                $success = "Profil mis à jour avec succès!";
            } else {
                $error = "Erreur lors de la mise à jour du profil.";
            }
        }

        $pageTitle = "Modifier le profil";
        ob_start();
        require_once 'views/user/edit.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }
}
?>
