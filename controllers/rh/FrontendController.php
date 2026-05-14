<?php
require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/JobOfferController.php');
require_once(__DIR__ . '/ApplicationController.php');

class FrontendController {

    public function home() {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $role = strtolower(trim((string) ($_SESSION['user_role'] ?? '')));
        if (!in_array($role, ['admin', 'rh', 'startup'], true)) {
            header('Location: index.php');
            exit;
        }
        
        $this->renderView('frontend/frontend', ['currentView' => 'home']);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../../views/rh/' . $viewPath . '.php';
    }
}

?>
