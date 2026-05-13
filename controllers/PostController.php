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

        if ($this->wantsJson()) {
            $this->jsonResponse([
                'success' => true,
                'data' => $posts,
                'topics' => $topics,
                'topApps' => $topApps,
                'total' => $totalPosts,
                'totalPages' => $totalPages,
                'page' => $page
            ]);
        }

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
        header('Location: rh.php?page=backend/forum');
        exit;

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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(404);
            exit('Post introuvable.');
        }
        $this->post->id = $id;

        if (!$this->post->readOne(currentUserId())) {
            http_response_code(404);
            exit('Post introuvable.');
        }

        require_once 'models/Commentaire.php';
        $commentaireModel = new Commentaire($this->db);
        $stmtComments = $commentaireModel->readByPost($this->post->id);
        $commentaires = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

        // Group comments by parent_id for threading
        $commentsTree = [];
        require_once 'models/Attachment.php';
        $attachmentModel = new Attachment($this->db);
        
        $postAttachments = $attachmentModel->getByPostId($this->post->id);

        foreach ($commentaires as &$c) {
            $parentId = $c['parent_id'] ?: 0;
            // Fetch attachments for this comment
            $c['attachments'] = $attachmentModel->getByCommentId($c['id']);
            $commentsTree[$parentId][] = $c;
        }

        if ($this->wantsJson()) {
            $postData = get_object_vars($this->post);
            $postData['attachments'] = $postAttachments;
            $postData['commentsFlat'] = $commentaires;
            $postData['commentsTree'] = $commentsTree;
            $postData['commentaires_count'] = count($commentaires);
            $postData['current_user_logged_in'] = isLoggedIn();
            $postData['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) $this->post->auteur_id);
            $postData['can_delete'] = isLoggedIn() && isAdmin();

            foreach ($postData['commentsFlat'] as &$comment) {
                $comment['can_reply'] = isLoggedIn();
                $comment['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) ($comment['auteur_id'] ?? 0));
                $comment['can_delete'] = $comment['can_edit'];
            }
            unset($comment);

            foreach ($postData['commentsTree'] as &$commentsAtLevel) {
                foreach ($commentsAtLevel as &$comment) {
                    $comment['can_reply'] = isLoggedIn();
                    $comment['can_edit'] = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) ($comment['auteur_id'] ?? 0));
                    $comment['can_delete'] = $comment['can_edit'];
                }
                unset($comment);
            }
            unset($commentsAtLevel);

            $this->jsonResponse(['success' => true, 'data' => $postData]);
        }

        $pageTitle = htmlspecialchars($this->post->titre);
        ob_start();
        require_once 'views/post/show.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function create() {
        requireLogin();
        $requestData = $this->requestData();
        if (!empty($requestData)) {
            $this->post->titre = $requestData['titre'] ?? '';
            $this->post->topic = $requestData['topic'] ?? 'General';
            $this->post->contenu = $requestData['contenu'] ?? '';
            $this->post->auteur_id = currentUserId();
            $this->post->statut = $requestData['statut'] ?? 'actif';
            
            // Default project from form if provided
            $formProjetId = !empty($requestData['projet_id']) ? (int)$requestData['projet_id'] : null;

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
                $newPostId = $this->post->id;
                if (!empty($_FILES['attachments'])) {
                    require_once 'services/FileService.php';
                    require_once 'models/Attachment.php';
                    $uploads = FileService::processUploads($_FILES['attachments']);
                    $attachmentModel = new Attachment($this->db);
                    foreach ($uploads as $fileData) {
                        $attachmentModel->nom_fichier = $fileData['nom_fichier'];
                        $attachmentModel->chemin_fichier = $fileData['chemin_fichier'];
                        $attachmentModel->type_fichier = $fileData['type_fichier'];
                        $attachmentModel->taille_fichier = $fileData['taille_fichier'];
                        $attachmentModel->post_id = $newPostId;
                        $attachmentModel->commentaire_id = null;
                        $attachmentModel->create();
                    }
                }
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => true, 'id' => $newPostId]);
                }
                header("Location: index.php?controller=post&action=index");
                exit;
            } else {
                $error = "Erreur lors de la création.";
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => false, 'message' => $error], 500);
                }
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(404);
            exit('Post introuvable.');
        }
        $this->post->id = $id;

        if (!$this->post->readOne()) {
            http_response_code(404);
            exit('Post introuvable.');
        }

        requireOwnershipOrAdmin((int) $this->post->auteur_id);

        $requestData = $this->requestData();
        if (!empty($requestData)) {
            $this->post->titre = $requestData['titre'] ?? '';
            $this->post->topic = $requestData['topic'] ?? 'General';
            $this->post->contenu = $requestData['contenu'] ?? '';
            $this->post->statut = $requestData['statut'] ?? $this->post->statut;
            
            $formProjetId = !empty($requestData['projet_id']) ? (int)$requestData['projet_id'] : null;

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
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => true, 'id' => $this->post->id]);
                }
                header("Location: index.php?controller=post&action=index");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
                if ($this->wantsJson()) {
                    $this->jsonResponse(['success' => false, 'message' => $error], 500);
                }
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(404);
            exit('Post introuvable.');
        }
        $this->post->id = $id;

        if (!$this->post->readOne()) {
            http_response_code(404);
            exit('Post introuvable.');
        }

        requireAdmin();

        if ($this->post->delete()) {
            if ($this->wantsJson()) {
                $this->jsonResponse(['success' => true]);
            }
            header("Location: index.php?controller=post&action=index");
            exit;
        } else {
            $error = "Erreur lors de la suppression.";
            if ($this->wantsJson()) {
                $this->jsonResponse(['success' => false, 'message' => $error], 500);
            }
            // Fallback for non-json
            die($error);
        }
    }

    public function reaction() {
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        require_once 'models/Reaction.php';
        $data = $this->requestData();
        $postId = $data['id'] ?? $data['id_post'] ?? null;
        $reactionType = $data['reaction'] ?? $data['reaction_type'] ?? 'LIKE';

        if (!$postId) {
            $this->jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
        }

        $reaction = new Reaction($this->db);
        $reaction->post_id = $postId;
        $reaction->user_id = currentUserId();
        $result = $reaction->togglePostReaction($reactionType);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la réaction'], 500);
        }

        $counts = [
            'likes' => $reaction->countPostReactionsByType((int) $postId, 'LIKE'),
            'dislikes' => $reaction->countPostReactionsByType((int) $postId, 'DISLIKE')
        ];

        $this->jsonResponse([
            'success' => true,
            'status' => $result['status'],
            'current_type' => $result['current_type'] ?? null,
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

}
?>
