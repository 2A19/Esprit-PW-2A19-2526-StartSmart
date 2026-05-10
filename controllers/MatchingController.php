<?php

require_once 'config/Database.php';
require_once 'config/Auth.php';
require_once 'models/ProjectMatcher.php';
require_once 'models/Skill.php';
require_once 'models/UserSkill.php';
require_once 'models/UserInterest.php';
require_once 'models/ProjectSkill.php';
require_once 'models/Projet.php';
require_once 'models/Categorie.php';

class MatchingController {
    private $db;
    private $matcher;
    private $skill;
    private $userSkill;
    private $userInterest;
    private $projectSkill;
    private $categorie;
    private $projet;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        $this->matcher = new ProjectMatcher($this->db);
        $this->skill = new Skill($this->db);
        $this->userSkill = new UserSkill($this->db);
        $this->userInterest = new UserInterest($this->db);
        $this->projectSkill = new ProjectSkill($this->db);
        $this->categorie = new Categorie($this->db);
        $this->projet = new Projet($this->db);
    }

    /**
     * GET /index.php?controller=matching&action=recommend
     * Get recommended projects for current user
     */
    public function recommend() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = 12;
        $offset = ($page - 1) * $per_page;

        $user_id = $_SESSION['user_id'];
        
        // Get matched projects
        $projects = $this->matcher->getMatchedProjects($user_id, $per_page, $offset);

        // Get total count for pagination
        $query = "SELECT COUNT(*) as count FROM projet WHERE statut = 'actif'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['count'];
        $total_pages = ceil($total / $per_page);
        $current_page = $page;

        $pageTitle = "Projets Recommandés";
        ob_start();
        require_once 'views/matching/recommend.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    /**
     * GET /index.php?controller=matching&action=discover
     * Tinder-style swipe interface
     */
    public function discover() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            return;
        }

        $user_id = $_SESSION['user_id'];

        // Get projects the user hasn't actioned yet
        $query = "
            SELECT p.*, 
                   COALESCE(uma.action, 'none') as user_action
            FROM projet p
            LEFT JOIN user_match_action uma ON p.id = uma.projet_id AND uma.user_id = ?
            WHERE p.statut = 'actif' AND (uma.action IS NULL OR uma.action = 'skipped')
            ORDER BY RAND()
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            // Calculate match score
            $project['match_score'] = $this->matcher->calculateMatchScore($user_id, $project['id']);
            $project['matched_skills'] = $this->matcher->getMatchedSkills($user_id, $project['id']);
            $project['required_skills'] = $this->matcher->getRequiredSkills($project['id']);
        }

        $pageTitle = "Découvrir les Projets";
        ob_start();
        require_once 'views/matching/discover.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    /**
     * POST /index.php?controller=matching&action=recordAction
     * AJAX: Record user action (interested/skipped/applied)
     */
    public function recordAction() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['project_id']) || !isset($data['action'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing project_id or action']);
            return;
        }

        $valid_actions = ['interested', 'skipped', 'applied', 'rejected'];
        if (!in_array($data['action'], $valid_actions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            return;
        }

        // Record action
        $result = $this->matcher->recordAction($user_id, $data['project_id'], $data['action']);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Action recorded',
                'action' => $data['action']
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to record action']);
        }
    }

    /**
     * GET /index.php?controller=matching&action=profile
     * User skill/interest profile management
     */
    public function profile() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            return;
        }

        $user_id = $_SESSION['user_id'];

        // Get all skills and categories
        $all_skills = $this->skill->readAll();
        $all_categories = $this->categorie->readAll();

        // Get user's skills and interests
        $user_skills = $this->userSkill->readByUser($user_id);
        $user_interests = $this->userInterest->readByUser($user_id);

        $pageTitle = "Mon Profil de Correspondance";
        ob_start();
        require_once 'views/matching/profile.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    /**
     * POST /index.php?controller=matching&action=addSkill
     * AJAX: Add skill to user
     */
    public function addSkill() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        // Check for JSON request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Bad request']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['skill_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing skill_id']);
            return;
        }

        $proficiency = $data['proficiency'] ?? 'beginner';
        $result = $this->userSkill->addSkill($user_id, $data['skill_id'], $proficiency);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Skill added']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add skill']);
        }
    }

    /**
     * POST /index.php?controller=matching&action=removeSkill
     * AJAX: Remove skill from user
     */
    public function removeSkill() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['skill_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing skill_id']);
            return;
        }

        $result = $this->userSkill->removeSkill($user_id, $data['skill_id']);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Skill removed']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove skill']);
        }
    }

    /**
     * POST /index.php?controller=matching&action=addInterest
     * AJAX: Add category interest
     */
    public function addInterest() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing category_id']);
            return;
        }

        $score = isset($data['score']) ? (int)$data['score'] : 3;
        $result = $this->userInterest->addInterest($user_id, $data['category_id'], $score);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Interest added']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add interest']);
        }
    }

    /**
     * POST /index.php?controller=matching&action=removeInterest
     * AJAX: Remove category interest
     */
    public function removeInterest() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing category_id']);
            return;
        }

        $result = $this->userInterest->removeInterest($user_id, $data['category_id']);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Interest removed']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove interest']);
        }
    }

    /**
     * GET /index.php?controller=matching&action=getMatchStats
     * AJAX: Get user match statistics
     */
    public function getMatchStats() {
        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $user_id = $_SESSION['user_id'];

        // Count skills
        $skill_count = $this->userSkill->countUserSkills($user_id);

        // Count interests
        $interest_count = $this->userInterest->countUserInterests($user_id);

        // Count user actions
        $query = "
            SELECT action, COUNT(*) as count 
            FROM user_match_action 
            WHERE user_id = ? 
            GROUP BY action
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $actions_count = array_column($actions, 'count', 'action');

        echo json_encode([
            'user_id' => $user_id,
            'skills_count' => $skill_count,
            'interests_count' => $interest_count,
            'interested_projects' => $actions_count['interested'] ?? 0,
            'skipped_projects' => $actions_count['skipped'] ?? 0,
            'applied_projects' => $actions_count['applied'] ?? 0
        ]);
    }

    /**
     * GET /index.php?controller=matching&action=candidatures
     * Show interested users for the logged in user's projects
     */
    public function candidatures() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            return;
        }

        $user_id = $_SESSION['user_id'];
        
        // Get all projects owned by the user
        $my_projects = $this->projet->readAll("", $user_id);
        
        // For each project, fetch interested users
        $project_candidatures = [];
        foreach ($my_projects as $p) {
            $interested = $this->matcher->getInterestedUsers($p['id']);
            if (!empty($interested)) {
                $project_candidatures[] = [
                    'project' => $p,
                    'candidates' => $interested
                ];
            }
        }

        $pageTitle = "Candidatures Reçues";
        ob_start();
        require_once 'views/projet/candidatures.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    /**
     * POST /index.php?controller=matching&action=acceptCandidate
     * Accept a candidate and initialize chat
     */
    public function acceptCandidate() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $candidate_id = isset($data['candidate_id']) ? (int)$data['candidate_id'] : 0;
        $projet_id = isset($data['projet_id']) ? (int)$data['projet_id'] : 0;

        if (!$candidate_id || !$projet_id) {
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            return;
        }

        // Verify that the logged in user actually owns this project
        $this->projet->id = $projet_id;
        $projectExists = $this->projet->readOne();
        if (!$projectExists || $this->projet->auteur_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé pour ce projet']);
            return;
        }

        // Update status to 'accepted'
        $result = $this->matcher->updateActionStatus($candidate_id, $projet_id, 'accepted');

        if ($result) {
            // Auto-create a welcome message so the conversation appears in the inbox
            require_once 'models/Message.php';
            $msg = new Message($this->db);
            $msg->sender_id = $_SESSION['user_id'];
            $msg->receiver_id = $candidate_id;
            $msg->projet_id = $projet_id;
            $msg->content = "👋 J'ai accepté votre candidature pour mon projet ! Nous pouvons maintenant discuter des détails.";
            $msg->sendMessage();

            echo json_encode(['success' => true, 'message' => 'Candidat accepté!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'acceptation']);
        }
    }
}
?>
