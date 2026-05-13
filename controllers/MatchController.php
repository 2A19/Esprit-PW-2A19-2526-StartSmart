<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'services/MatchService.php';
require_once 'models/Competence.php';
require_once 'models/Categorie.php';

use Services\MatchService;

class MatchController {
    private $db;
    private $matchService;
    private $competenceModel;
    private $categorieModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->matchService = new MatchService($this->db);
        $this->competenceModel = new Competence($this->db);
        $this->categorieModel = new Categorie($this->db);
    }

    public function index() {
        requireLogin();
        $userId = currentUserId();

        // 1. Check if user has setup their profile (skills & interests)
        $userSkills = $this->competenceModel->getUserCompetences($userId);
        $userInterests = $this->competenceModel->getUserInterests($userId);

        $isProfileComplete = count($userSkills) > 0 || count($userInterests) > 0;

        // 2. Fetch matches
        $matches = [];
        if ($isProfileComplete) {
            $matches = $this->matchService->calculateMatches($userId);
        }

        // We need all skills and categories for the setup modal
        $allSkills = $this->competenceModel->getAll();
        $allCategories = $this->categorieModel->readAllForSelect()->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Découvrir des Startups (Matching)";
        ob_start();
        require_once 'views/match/index.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function savePreferences() {
        requireLogin();
        $userId = currentUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $skills = $_POST['skills'] ?? [];
            $categories = $_POST['categories'] ?? [];

            $this->competenceModel->saveUserCompetences($userId, $skills);
            $this->competenceModel->saveUserInterests($userId, $categories);

            header('Location: index.php?controller=match&action=index');
            exit;
        }
    }
}
?>
