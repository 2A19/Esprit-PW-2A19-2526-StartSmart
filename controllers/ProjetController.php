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
            $compQuery = "SELECT pc.projet_id, c.nom FROM projet_competence pc JOIN competence c ON pc.competence_id = c.id WHERE pc.projet_id IN ($placeholders)";
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

        $pageTitle = "StartSmart - " . htmlspecialchars($this->projetModel->nomprojet);
        ob_start();
        $this->projet = $this->projetModel;
        $this->projet->competences = $projectCompetences;
        require_once 'views/projet/show.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function admin() {
        requireRole(['ADMIN']);

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
        
        if ($_POST) {
            $year = date('Y');
            $random = strtoupper(substr(uniqid(), -5));
            $this->projetModel->num = "PRJ-{$year}-{$random}";
            $this->projetModel->nomprojet = $_POST['nomprojet'] ?? '';
            $this->projetModel->description = $_POST['description'] ?? '';
            $this->projetModel->datedebut = $_POST['datedebut'] ?? '';
            $this->projetModel->datefin = $_POST['datefin'] ?? '';
            $this->projetModel->budget = $_POST['budget'] ?? 0;
            $this->projetModel->gain = $_POST['gain'] ?? 0;
            $this->projetModel->categorie_id = $_POST['categorie_id'] ?? '';
            $this->projetModel->auteur_id = currentUserId();
            $this->projetModel->city = $_POST['city'] ?? '';
            $this->projetModel->country = $_POST['country'] ?? '';
            
            // Generate some random coordinates if missing for demo purposes, since the form only asks for city/country
            if (empty($_POST['latitude']) || empty($_POST['longitude'])) {
                // Approximate random global coordinates for demo purposes
                $this->projetModel->latitude = (mt_rand(-5000, 5000) / 100);
                $this->projetModel->longitude = (mt_rand(-18000, 18000) / 100);
            } else {
                $this->projetModel->latitude = $_POST['latitude'];
                $this->projetModel->longitude = $_POST['longitude'];
            }

            $this->projetModel->statut = $_POST['statut'] ?? 'actif';
            $this->projetModel->etape = $_POST['etape'] ?? 'idea';

            if ($this->projetModel->create()) {
                $projetId = $this->projetModel->id;
                $skills = $_POST['skills'] ?? [];
                if (!empty($skills) && $projetId) {
                    $this->competenceModel->saveProjectCompetences($projetId, $skills);
                }
                header("Location: index.php?controller=match&action=index"); // Send to match discovery after creation!
                exit;
            } else {
                $error = "Erreur lors de la création.";
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

        if ($_POST) {
            $this->projetModel->num = $_POST['num'] ?? '';
            $this->projetModel->nomprojet = $_POST['nomprojet'] ?? '';
            $this->projetModel->description = $_POST['description'] ?? '';
            $this->projetModel->datedebut = $_POST['datedebut'] ?? '';
            $this->projetModel->datefin = $_POST['datefin'] ?? '';
            $this->projetModel->budget = $_POST['budget'] ?? 0;
            $this->projetModel->gain = $_POST['gain'] ?? 0;
            $this->projetModel->categorie_id = $_POST['categorie_id'] ?? '';
            $this->projetModel->city = $_POST['city'] ?? '';
            $this->projetModel->country = $_POST['country'] ?? '';
            $this->projetModel->statut = $_POST['statut'] ?? $this->projetModel->statut;

            if ($this->projetModel->update()) {
                header("Location: index.php?controller=projet&action=show&id=" . $id);
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
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

        requireOwnershipOrAdmin((int)$this->projetModel->auteur_id);

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
}
?>
