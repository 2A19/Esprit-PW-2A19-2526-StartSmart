<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/JobOfferController.php');
require_once(__DIR__ . '/ApplicationController.php');

class FrontendController {

    public function home() {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?page=auth/login');
            exit;
        }
        
        $this->renderView('frontend/home');
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $viewPath . '.php';
    }
}

?>
