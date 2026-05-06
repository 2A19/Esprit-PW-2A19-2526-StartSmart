<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Competence.php';
require_once 'models/Categorie.php';

class ProfileController {
    private $db;
    private $competenceModel;
    private $categorieModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->competenceModel = new Competence($this->db);
        $this->categorieModel = new Categorie($this->db);
    }

    public function index() {
        requireLogin();
        $currentUserId = currentUserId();
        
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUserId;
        $isPublicProfile = ($userId !== $currentUserId);

        if ($isPublicProfile) {
            $stmt = $this->db->prepare("SELECT nom FROM utilisateur WHERE id_utilisateur = ? LIMIT 1");
            $stmt->execute([$userId]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$userRow) {
                http_response_code(404);
                exit("Utilisateur introuvable.");
            }
            $userName = $userRow['nom'];
        } else {
            $userName = $_SESSION['user_name'] ?? 'Utilisateur';
        }

        // Get user profile data
        $userSkills = $this->competenceModel->getUserCompetences($userId);
        $userInterests = $this->competenceModel->getUserInterests($userId);
        
        $userSkillIds = array_column($userSkills, 'id');
        $userInterestIds = array_column($userInterests, 'id');

        // Get all available options (only needed if NOT public profile, but we can pass it anyway)
        $allSkills = [];
        $allCategories = [];
        if (!$isPublicProfile) {
            $allSkills = $this->competenceModel->getAll();
            $allCategories = $this->categorieModel->readAllForSelect()->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch User's Projects
        $query = "SELECT p.*, c.typeprojet AS categorie_nom, 
                         (SELECT COUNT(*) FROM projet_reaction pr WHERE pr.projet_id = p.id AND pr.type='LIKE') as likes_count
                  FROM projet p 
                  LEFT JOIN categorie c ON p.categorie_id = c.id 
                  WHERE p.auteur_id = :user_id AND p.statut != 'deleted'
                  ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $userProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Mon Profil - StartSmart";
        ob_start();
        require_once 'views/profile/index.php';
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

            // Redirect back to profile with success message or to Match page
            if (isset($_POST['redirect_to_match'])) {
                header('Location: index.php?controller=match&action=index');
            } else {
                header('Location: index.php?controller=profile&action=index&success=1');
            }
            exit;
        }
    }
}
?>
