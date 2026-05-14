<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Categorie.php';
require_once 'models/Projet.php';
require_once 'models/ProjetCommentaire.php';
require_once 'models/Competence.php';
require_once 'services/ProjetService.php';
require_once 'services/AuthService.php';
require_once 'dtos/ProjetDTO.php';

use Services\ProjetService;
use Services\AuthService;
use DTOs\ProjetDTO;

class ProjetController {
    private $db;
    private $projetModel;
    private $categorieModel;
    private $projetService;
    private $competenceModel;
    private $projet;
    private $projectData = [];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        $this->projetModel = new Projet($this->db);
        $this->categorieModel = new Categorie($this->db);
        $this->competenceModel = new Competence($this->db);
        
        $this->projetService = new ProjetService($this->projetModel, $this->categorieModel);
    }

    public function index() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : "";
        $categoryFilter = isset($_GET['categorie_id']) ? (int)$_GET['categorie_id'] : null;
        $sortBy = $_GET['sort'] ?? 'latest';
        $allowedSorts = ['latest', 'trending', 'most_discussed', 'budget_desc'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'latest';
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        // Get counts
        $totalProjets = $this->projetModel->countAll($search, null);
        $stmt = $this->projetModel->readAll($search, null, $sortBy, currentUserId(), $perPage, $offset);
        $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply category filter to results
        if ($categoryFilter) {
            $projets = array_filter($projets, function($p) use ($categoryFilter) {
                return (int)$p['categorie_id'] === $categoryFilter;
            });
        }

        // Fetch competences for displayed projects
        $projetIds = array_column($projets, 'id');
        $projetCompetencesMap = [];
        if (!empty($projetIds)) {
            $placeholders = implode(',', array_fill(0, count($projetIds), '?'));
            $compQuery = "SELECT pc.projet_id, c.name AS nom FROM project_skill pc JOIN skill c ON pc.skill_id = c.id WHERE pc.projet_id IN ($placeholders)";
            $compStmt = $this->db->prepare($compQuery);
            $compStmt->execute($projetIds);
            $allProjCompetences = $compStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allProjCompetences as $row) {
                $projetCompetencesMap[$row['projet_id']][] = $row['nom'];
            }
        }

        foreach ($projets as &$p) {
            $p['competences'] = $projetCompetencesMap[$p['id']] ?? [];
        }

        $categoriesList = $this->categorieModel->readAllForSelect()->fetchAll(PDO::FETCH_ASSOC);
        $totalPages = max(1, (int) ceil($totalProjets / $perPage));

        if ($this->wantsJson()) {
            $this->jsonResponse([
                'success' => true,
                'data' => array_values($projets),
                'categories' => $categoriesList,
                'total' => $totalProjets,
                'totalPages' => $totalPages,
                'page' => $page
            ]);
        }

        $pageTitle = "Découvrez les Startups";
        ob_start();
        require_once 'views/projet/catalog.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function show() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(404);
            exit('Projet introuvable.');
        }

        $this->projetModel->id = $id;
        if (!$this->projetModel->readOne()) {
            http_response_code(404);
            exit('Projet introuvable.');
        }

        // Get comments
        $commentaireModel = new ProjetCommentaire($this->db);
        $stmtComments = $commentaireModel->readByProjet($id);
        $commentaires = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

        // Group comments by parent_id for threading
        $commentsTree = [];
        foreach ($commentaires as $c) {
            $parentId = $c['parent_id'] ?: 0;
            $commentsTree[$parentId][] = $c;
        }

        // Get competences
        $projectCompetences = $this->competenceModel->getProjectCompetences($id);

        $projectData = get_object_vars($this->projetModel);
        $projectData['competences'] = $projectCompetences;
        $projectData['commentsFlat'] = $commentaires;
        $projectData['commentsTree'] = $commentsTree;
        $projectData['commentaires_count'] = count($commentaires);
        $projectData['current_user_logged_in'] = isLoggedIn();
        $projectData['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) $this->projetModel->auteur_id);
        $projectData['can_delete'] = isLoggedIn() && isAdmin();

        if ($this->wantsJson()) {
            require_once 'models/ProjetReaction.php';
            $reactionModel = new ProjetReaction($this->db);

            $projectData = get_object_vars($this->projetModel);
            $projectData['current_user_reaction'] = isLoggedIn()
                ? $reactionModel->currentUserReactionType('projet', $id, currentUserId())
                : null;
            $projectData['competences'] = $projectCompetences;
            $projectData['commentsFlat'] = $commentaires;
            $projectData['commentsTree'] = $commentsTree;
            $projectData['current_user_logged_in'] = isLoggedIn();
            $projectData['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) $this->projetModel->auteur_id);
            $projectData['can_delete'] = isLoggedIn() && isAdmin();

            foreach ($projectData['commentsFlat'] as &$comment) {
                $comment['can_reply'] = isLoggedIn();
                $comment['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) ($comment['auteur_id'] ?? 0));
                $comment['can_delete'] = $comment['can_edit'];
            }
            unset($comment);

            foreach ($projectData['commentsTree'] as &$commentsAtLevel) {
                foreach ($commentsAtLevel as &$comment) {
                    $comment['can_reply'] = isLoggedIn();
                    $comment['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) ($comment['auteur_id'] ?? 0));
                    $comment['can_delete'] = $comment['can_edit'];
                }
                unset($comment);
            }
            unset($commentsAtLevel);

            $this->jsonResponse([
                'success' => true,
                'data' => $projectData
            ]);
        }

        $pageTitle = "StartSmart - " . htmlspecialchars($this->projetModel->nomprojet);
        ob_start();
        $this->projet = $this->projetModel;
        $this->projet->competences = $projectCompetences;
        $projectViewData = get_object_vars($this->projetModel);
        $projectViewData['competences'] = $projectCompetences;
        $projectViewData['commentsFlat'] = $commentaires;
        $projectViewData['commentsTree'] = $commentsTree;
        $projectViewData['commentaires_count'] = count($commentaires);
        $projectViewData['current_user_logged_in'] = isLoggedIn();
        $projectViewData['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) $this->projetModel->auteur_id);
        $projectViewData['can_delete'] = isLoggedIn() && isAdmin();
        $this->projectData = $projectViewData;
        require_once 'views/projet/show.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function admin() {
        requireRole(['ADMIN']);
        header('Location: rh.php?page=backend/projets');
        exit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : "";
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $totalProjets = $this->projetModel->countAll($search, null);
        $stmt = $this->projetModel->readAll($search, null, "latest", null, $perPage, $offset);
        $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPages = max(1, (int) ceil($totalProjets / $perPage));
        $pageTitle = "Administration des Projets";

        ob_start();
        require_once 'views/projet/admin.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function create() {
        requireLogin();
        
        $requestData = $this->requestData();
        if (!empty($requestData)) {
            $year = date('Y');
            $random = strtoupper(substr(uniqid(), -5));
            $this->projetModel->num = "PRJ-{$year}-{$random}";
            $this->projetModel->nomprojet = $requestData['nomprojet'] ?? '';
            $this->projetModel->description = $requestData['description'] ?? '';
            $this->projetModel->datedebut = $requestData['datedebut'] ?? '';
            $this->projetModel->datefin = $requestData['datefin'] ?? '';
            $this->projetModel->budget = $requestData['budget'] ?? 0;
            $this->projetModel->gain = $requestData['gain'] ?? 0;
            $this->projetModel->categorie_id = $requestData['categorie_id'] ?? '';
            $this->projetModel->auteur_id = currentUserId();
            $this->projetModel->city = $requestData['city'] ?? '';
            $this->projetModel->country = $requestData['country'] ?? '';
            
            // Fetch real coordinates using Nominatim API instead of randomizing
            if (empty($requestData['latitude']) || empty($requestData['longitude'])) {
                $coords = $this->geocodeLocation($this->projetModel->city, $this->projetModel->country);
                if ($coords) {
                    $this->projetModel->latitude = $coords['lat'];
                    $this->projetModel->longitude = $coords['lng'];
                } else {
                    $this->projetModel->latitude = 0;
                    $this->projetModel->longitude = 0;
                }
            } else {
                $this->projetModel->latitude = $requestData['latitude'];
                $this->projetModel->longitude = $requestData['longitude'];
            }

            $this->projetModel->statut = $requestData['statut'] ?? 'actif';
            $this->projetModel->etape = $requestData['etape'] ?? 'idea';

            if ($this->projetModel->create()) {
                $projetId = $this->projetModel->id;
                $skills = $requestData['skills'] ?? [];
                if (!empty($skills) && $projetId) {
                    $this->competenceModel->saveProjectCompetences($projetId, $skills);
                }
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => true, 'id' => $projetId]);
                }
                header("Location: index.php?controller=match&action=index"); // Send to match discovery after creation!
                exit;
            } else {
                $error = "Erreur lors de la création.";
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => false, 'message' => $error], 500);
                }
            }
        }

        $categoriesList = $this->categorieModel->readAllForSelect()->fetchAll(PDO::FETCH_ASSOC);
        $allSkills = $this->competenceModel->getAll();
        
        $pageTitle = "Lancer un Projet - Wizard";
        ob_start();
        require_once 'views/projet/create.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(404);
            exit('Projet introuvable.');
        }

        $this->projetModel->id = $id;
        if (!$this->projetModel->readOne()) {
            http_response_code(404);
            exit('Projet introuvable.');
        }

        requireOwnershipOrAdmin((int)$this->projetModel->auteur_id);

        $requestData = $this->requestData();
        if (!empty($requestData)) {
            $this->projetModel->num = $requestData['num'] ?? $this->projetModel->num;
            $this->projetModel->nomprojet = $requestData['nomprojet'] ?? '';
            $this->projetModel->description = $requestData['description'] ?? '';
            $this->projetModel->datedebut = $requestData['datedebut'] ?? '';
            $this->projetModel->datefin = $requestData['datefin'] ?? '';
            $this->projetModel->budget = $requestData['budget'] ?? 0;
            $this->projetModel->gain = $requestData['gain'] ?? 0;
            $this->projetModel->categorie_id = $requestData['categorie_id'] ?? '';
            $this->projetModel->city = $requestData['city'] ?? '';
            $this->projetModel->country = $requestData['country'] ?? '';
            $this->projetModel->statut = $requestData['statut'] ?? $this->projetModel->statut;

            if ($this->projetModel->update()) {
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => true, 'id' => $id]);
                }
                header("Location: index.php?controller=projet&action=show&id=" . $id);
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => false, 'message' => $error], 500);
                }
            }
        }

        $categoriesList = $this->categorieModel->readAllForSelect()->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Modifier le Projet";
        ob_start();
        $this->projet = $this->projetModel;
        require_once 'views/projet/edit.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(404);
            exit('Projet introuvable.');
        }

        $this->projetModel->id = $id;
        if (!$this->projetModel->readOne()) {
            http_response_code(404);
            exit('Projet introuvable.');
        }

        requireAdmin();

        // Soft delete - mark as deleted instead of removing
        $this->projetModel->statut = 'deleted';
        
        if ($this->projetModel->update()) {
            header("Location: index.php?controller=projet&action=index");
            exit;
        } else {
            http_response_code(500);
            exit('Erreur lors de la suppression.');
        }
    }

    public function reaction() {
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        require_once 'models/ProjetReaction.php';
        $data = $this->requestData();
        $projetId = $data['id'] ?? $data['id_projet'] ?? null;
        $reactionType = $data['reaction'] ?? $data['reaction_type'] ?? 'LIKE';

        if (!$projetId) {
            $this->jsonResponse(['success' => false, 'message' => 'Projet ID required'], 400);
        }

        $reaction = new ProjetReaction($this->db);
        $reaction->user_id = currentUserId();
        $reaction->projet_id = $projetId;
        $result = $reaction->toggleReaction($reactionType);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la réaction'], 500);
        }

        $counts = $reaction->getCounts('projet', $projetId);
        $this->jsonResponse([
            'success' => true,
            'status' => $result['status'],
            'current_type' => $result['current_type'],
            'likes_count' => $counts['likes'],
            'dislikes_count' => $counts['dislikes']
        ]);
    }

    private function wantsJson() {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return (isset($_GET['format']) && $_GET['format'] === 'json')
            || stripos($accept, 'application/json') !== false
            || $this->isJsonRequest();
    }

    private function isJsonRequest() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return stripos($contentType, 'application/json') !== false;
    }

    private function requestData() {
        if ($this->isJsonRequest()) {
            $data = json_decode(file_get_contents('php://input'), true);
            return is_array($data) ? $data : [];
        }

        return $_POST;
    }

    private function jsonResponse(array $payload, int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    private function geocodeLocation($city, $country) {
        $query = urlencode($city . ', ' . $country);
        $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1";
        
        $options = [
            "http" => [
                "header" => "User-Agent: StartSmartApp/1.0\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result) {
            $data = json_decode($result, true);
            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return ['lat' => $data[0]['lat'], 'lng' => $data[0]['lon']];
            }
        }
        
        // Fallback to just city
        $query = urlencode($city);
        $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1";
        $result = @file_get_contents($url, false, $context);
        if ($result) {
            $data = json_decode($result, true);
            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return ['lat' => $data[0]['lat'], 'lng' => $data[0]['lon']];
            }
        }
        return false;
    }
}
?>
