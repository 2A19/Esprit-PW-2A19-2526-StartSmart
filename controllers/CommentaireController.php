<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Commentaire.php';
require_once 'models/Post.php';
require_once 'services/NotificationService.php';

class CommentaireController {
    private $db;
    private $commentaire;
    private $post;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->commentaire = new Commentaire($this->db);
        $this->post = new Post($this->db);
        $this->notificationService = new NotificationService($this->db);
    }

    public function index() {
        $search = isset($_GET['search']) ? $_GET['search'] : "";
        $stmt = $this->commentaire->readAll($search);
        $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Gestion des Commentaires";
        ob_start();
        require_once 'views/commentaire/index.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function create() {
        requireLogin();
        if ($_POST) {
            $this->commentaire->contenu = trim($_POST['contenu'] ?? '');
            $this->commentaire->auteur_id = currentUserId();
            $this->commentaire->post_id = !empty($_POST['post_id']) ? (int) $_POST['post_id'] : null;
            $this->commentaire->parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

            if (mb_strlen($this->commentaire->contenu) < 2) {
                $error = "Le commentaire doit contenir au moins 2 caractères.";
            } elseif (!$this->post->exists($this->commentaire->post_id)) {
                $error = "Le post parent est introuvable.";
            } elseif ($this->commentaire->parent_id && !$this->commentaire->exists($this->commentaire->parent_id)) {
                $error = "Le commentaire parent est introuvable.";
                } elseif ($this->commentaire->parent_id) {
                $parent = new Commentaire($this->db);
                $parent->id_commentaire = $this->commentaire->parent_id;
                if (!$parent->readOne() || (int) $parent->post_id !== (int) $this->commentaire->post_id) {
                    $error = "Le commentaire parent ne correspond pas à ce sujet.";
                } elseif ($this->commentaire->create()) {
                        // Notify parent comment author and post author if applicable
                        try {
                            $p = new Post($this->db);
                            $p->id_post = $this->commentaire->post_id;
                            if ($p->readOne()) {
                                $postAuthorId = (int) $p->auteur_id;
                                if ($postAuthorId && $postAuthorId !== currentUserId()) {
                                    $this->notificationService->createNotification($postAuthorId, currentUserId(), 'COMMENT_ON_POST', $this->commentaire->post_id, 'Nouveau commentaire sur votre post');
                                }
                            }
                        
                            if ((int)$parent->auteur_id && (int)$parent->auteur_id !== currentUserId()) {
                                $this->notificationService->createNotification((int)$parent->auteur_id, currentUserId(), 'REPLY_ON_COMMENT', $parent->id_commentaire, 'Quelqu\u2019un a répondu à votre commentaire');
                            }
                        } catch (Exception $e) { }
                    header("Location: index.php?controller=commentaire&action=index");
                    exit;
                } else {
                    $error = "Erreur lors de la création.";
                }
            } elseif ($this->commentaire->create()) {
                    // Notify post author
                    try {
                        $p = new Post($this->db);
                        $p->id_post = $this->commentaire->post_id;
                        if ($p->readOne()) {
                            $postAuthorId = (int) $p->auteur_id;
                            if ($postAuthorId && $postAuthorId !== currentUserId()) {
                                $this->notificationService->createNotification($postAuthorId, currentUserId(), 'COMMENT_ON_POST', $this->commentaire->post_id, 'Nouveau commentaire sur votre post');
                            }
                        }
                    } catch (Exception $e) { }
                header("Location: index.php?controller=commentaire&action=index");
                exit;
            } else {
                $error = "Erreur lors de la création.";
            }
        }
        
        $stmtPosts = $this->post->readAll();
        $postsList = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Ajouter un Commentaire";
        ob_start();
        require_once 'views/commentaire/create.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function edit() {
        $this->commentaire->id_commentaire = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if (!$this->commentaire->readOne()) {
            http_response_code(404);
            exit('Commentaire introuvable.');
        }

        requireOwnershipOrAdmin((int) $this->commentaire->auteur_id);

        if ($_POST) {
            $this->commentaire->contenu = trim($_POST['contenu'] ?? '');
            $this->commentaire->post_id = !empty($_POST['post_id']) ? (int) $_POST['post_id'] : null;
            $this->commentaire->parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

            if (mb_strlen($this->commentaire->contenu) < 2) {
                $error = "Le commentaire doit contenir au moins 2 caractères.";
            } elseif (!$this->post->exists($this->commentaire->post_id)) {
                $error = "Le post parent est introuvable.";
            } elseif ($this->commentaire->parent_id && !$this->commentaire->exists($this->commentaire->parent_id)) {
                $error = "Le commentaire parent est introuvable.";
            } elseif ($this->commentaire->update()) {
                header("Location: index.php?controller=commentaire&action=index");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        }

        $stmtPosts = $this->post->readAll();
        $postsList = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Modifier le Commentaire";
        ob_start();
        require_once 'views/commentaire/edit.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function delete() {
        $this->commentaire->id_commentaire = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if (!$this->commentaire->readOne()) {
            http_response_code(404);
            exit('Commentaire introuvable.');
        }

        requireOwnershipOrAdmin((int) $this->commentaire->auteur_id);

        if (isset($_GET['id'])) {
            $this->commentaire->id_commentaire = $_GET['id'];
            $this->commentaire->delete();
        }
        header("Location: index.php?controller=commentaire&action=index");
        exit;
    }

    public function addAsync() {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode(file_get_contents("php://input"));
                $contenu = $data->contenu ?? '';
                $post_id = $data->post_id ?? null;
                $parent_id = $data->parent_id ?? null;
            } else {
                $contenu = $_POST['contenu'] ?? '';
                $post_id = $_POST['post_id'] ?? null;
                $parent_id = $_POST['parent_id'] ?? null;
            }

            if (!empty($contenu) && !empty($post_id)) {
                $this->commentaire->contenu = trim((string) $contenu);
                $this->commentaire->auteur_id = currentUserId();
                $this->commentaire->post_id = (int) $post_id;
                $this->commentaire->parent_id = !empty($parent_id) ? (int) $parent_id : null;

                if (mb_strlen($this->commentaire->contenu) < 2) {
                    http_response_code(422);
                    header('Content-Type: application/json');
                    echo json_encode(["success" => false, "message" => "Le commentaire est trop court."]);
                    exit;
                }

                $parentIsValid = true;
                if ($this->commentaire->parent_id !== null) {
                    $parent = new Commentaire($this->db);
                    $parent->id_commentaire = $this->commentaire->parent_id;
                    $parentIsValid = $parent->readOne() && (int) $parent->post_id === (int) $this->commentaire->post_id;
                }

                if ($this->post->exists($this->commentaire->post_id) && $parentIsValid && $this->commentaire->create()) {
                    // Notify post author and parent if needed
                    try {
                        $p = new Post($this->db);
                        $p->id_post = $this->commentaire->post_id;
                        if ($p->readOne()) {
                            $postAuthorId = (int) $p->auteur_id;
                            if ($postAuthorId && $postAuthorId !== currentUserId()) {
                                $this->notificationService->createNotification($postAuthorId, currentUserId(), 'COMMENT_ON_POST', $this->commentaire->post_id, 'Nouveau commentaire sur votre post');
                            }
                        }
                        if ($this->commentaire->parent_id) {
                            $parent = new Commentaire($this->db);
                            $parent->id_commentaire = $this->commentaire->parent_id;
                            if ($parent->readOne() && (int)$parent->auteur_id !== currentUserId()) {
                                $this->notificationService->createNotification((int)$parent->auteur_id, currentUserId(), 'REPLY_ON_COMMENT', $parent->id_commentaire, 'Quelqu\u2019un a répondu à votre commentaire');
                            }
                        }
                    } catch (Exception $e) { }

                    $newCommentId = $this->db->lastInsertId();
                    
                    // Handle Attachments
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
                            $attachmentModel->post_id = null;
                            $attachmentModel->commentaire_id = $newCommentId;
                            $attachmentModel->create();
                        }
                    }

                    header('Content-Type: application/json');
                    http_response_code(201);
                    echo json_encode([
                         "success" => true,
                         "message" => "Comment added.",
                         "author" => "You",
                         "date" => date('Y-m-d H:i:s')
                    ]);
                    exit;
                }
            }
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid input data."]);
            exit;
        }
    }
}
?>
