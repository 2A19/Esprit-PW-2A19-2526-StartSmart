<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Post.php';

class PostController {
    private $db;
    private $post;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->post = new Post($this->db);
    }

    public function index() {
        $search = trim($_GET['search'] ?? "");
        $topicFilter = trim($_GET['topic'] ?? "");
        $sortBy = $_GET['sort'] ?? "latest";
        $allowedSorts = ['latest', 'most_commented', 'most_liked'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'latest';
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $totalPosts = $this->post->countAll($search, $topicFilter);
        $stmt = $this->post->readAll($search, $topicFilter, $sortBy, currentUserId(), $perPage, $offset);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once 'models/Projet.php';
        $projetModel = new Projet($this->db);
        $topApps = $projetModel->getStats();
        $topics = ['Ideas', 'Feedback', 'Questions', 'Collaborations', 'General'];
        $totalPages = max(1, (int) ceil($totalPosts / $perPage));

        $pageTitle = "Forum StartSmart";
        ob_start();
        require_once 'views/post/index.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function admin() {
        if (!function_exists('isAdmin') || !isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            exit('Accès refusé. Vous devez être administrateur.');
        }

        $search = trim($_GET['search'] ?? "");
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $totalPosts = $this->post->countAll($search);
        // Using "latest" sort without topic filter or user specific reactions for the admin view
        $stmt = $this->post->readAll($search, "", "latest", null, $perPage, $offset);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalPages = max(1, (int) ceil($totalPosts / $perPage));
        $pageTitle = "Administration du Forum";
        
        ob_start();
        require_once 'views/post/admin.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function show() {
        $this->post->id_post = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if (!$this->post->readOne(currentUserId())) {
            http_response_code(404);
            exit('Post introuvable.');
        }

        require_once 'models/Commentaire.php';
        $commentaireModel = new Commentaire($this->db);
        $stmtComments = $commentaireModel->readByPost($this->post->id_post);
        $commentaires = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

        // Group comments by parent_id for threading
        $commentsTree = [];
        foreach ($commentaires as $c) {
            $parentId = $c['parent_id'] ?: 0;
            $commentsTree[$parentId][] = $c;
        }

        $pageTitle = htmlspecialchars($this->post->titre);
        ob_start();
        require_once 'views/post/show.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function create() {
        requireLogin();
        if ($_POST) {
            $this->post->titre = $_POST['titre'];
            $this->post->topic = $_POST['topic'] ?? 'General';
            $this->post->contenu = $_POST['contenu'];
            $this->post->auteur_id = currentUserId();
            $this->post->statut = $_POST['statut'] ?? 'actif';
            
            // Default project from form if provided
            $formProjetId = !empty($_POST['projet_id']) ? (int)$_POST['projet_id'] : null;

            // Run Smart Linker Service
            require_once 'services/SmartLinkerService.php';
            $linker = new \Services\SmartLinkerService($this->db);
            $smartData = $linker->processPostData($this->post->titre, $this->post->contenu);

            $this->post->categorie_id = $smartData['category_id'];
            if ($smartData['location']) {
                $this->post->city = $smartData['location']['city'];
                $this->post->country = $smartData['location']['country'];
                $this->post->latitude = $smartData['location']['lat'];
                $this->post->longitude = $smartData['location']['lng'];
            }
            // Prioritize manually selected project, otherwise use smart detected project
            $this->post->projet_id = $formProjetId ?: $smartData['projet_id'];

            if ($this->post->create()) {
                header("Location: index.php?controller=post&action=index");
                exit;
            } else {
                $error = "Erreur lors de la création.";
            }
        }
        
        $topics = ['Ideas', 'Feedback', 'Questions', 'Collaborations', 'General'];
        
        require_once 'models/Projet.php';
        $projetModel = new Projet($this->db);
        $userProjectsQuery = $projetModel->readAll("", null, "latest", null, 100, 0); // No pagination limit for dropdown
        $allProjects = $userProjectsQuery->fetchAll(PDO::FETCH_ASSOC);
        $userProjects = array_filter($allProjects, function($p) {
            return (int)$p['auteur_id'] === currentUserId();
        });

        $pageTitle = "Créer un Sujet";
        ob_start();
        require_once 'views/post/create.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function edit() {
        $this->post->id_post = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if (!$this->post->readOne()) {
            http_response_code(404);
            exit('Post introuvable.');
        }

        requireOwnershipOrAdmin((int) $this->post->auteur_id);

        if ($_POST) {
            $this->post->titre = $_POST['titre'];
            $this->post->topic = $_POST['topic'] ?? 'General';
            $this->post->contenu = $_POST['contenu'];
            $this->post->statut = $_POST['statut'] ?? $this->post->statut;
            
            $formProjetId = !empty($_POST['projet_id']) ? (int)$_POST['projet_id'] : null;

            // Run Smart Linker Service on Update
            require_once 'services/SmartLinkerService.php';
            $linker = new \Services\SmartLinkerService($this->db);
            $smartData = $linker->processPostData($this->post->titre, $this->post->contenu);

            $this->post->categorie_id = $smartData['category_id'];
            if ($smartData['location']) {
                $this->post->city = $smartData['location']['city'];
                $this->post->country = $smartData['location']['country'];
                $this->post->latitude = $smartData['location']['lat'];
                $this->post->longitude = $smartData['location']['lng'];
            }
            $this->post->projet_id = $formProjetId ?: $smartData['projet_id'];

            if ($this->post->update()) {
                header("Location: index.php?controller=post&action=index");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        }

        $topics = ['Ideas', 'Feedback', 'Questions', 'Collaborations', 'General'];

        require_once 'models/Projet.php';
        $projetModel = new Projet($this->db);
        $userProjectsQuery = $projetModel->readAll("", null, "latest", null, 100, 0); 
        $allProjects = $userProjectsQuery->fetchAll(PDO::FETCH_ASSOC);
        $userProjects = array_filter($allProjects, function($p) {
            return (int)$p['auteur_id'] === currentUserId(); // Allow original author to edit their own projects list
        });
        $pageTitle = "Modifier le Sujet";
        ob_start();
        require_once 'views/post/edit.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function delete() {
        $this->post->id_post = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if (!$this->post->readOne()) {
            http_response_code(404);
            exit('Post introuvable.');
        }

        requireOwnershipOrAdmin((int) $this->post->auteur_id);

        if (isset($_GET['id'])) {
            $this->post->id_post = $_GET['id'];
            $this->post->delete();
        }
        header("Location: index.php?controller=post&action=index");
        exit;
    }

}
?>
