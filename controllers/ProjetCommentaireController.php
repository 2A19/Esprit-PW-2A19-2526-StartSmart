<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/ProjetCommentaire.php';

class ProjetCommentaireController {
    private $db;
    private $commentaire;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->commentaire = new ProjetCommentaire($this->db);
    }

    public function create() {
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->requestData();
        $projetId = $data['projet_id'] ?? null;
        
        if (!$projetId) {
            if ($this->wantsJson()) {
                $this->jsonResponse(['success' => false, 'message' => 'Projet ID required'], 400);
            }
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit;
        }

        $this->commentaire->projet_id = $projetId;
        $this->commentaire->auteur_id = currentUserId();
        $this->commentaire->contenu = $data['contenu'] ?? '';
        
        $parentId = $data['parent_id'] ?? '';
        $this->commentaire->parent_id = ($parentId !== '') ? (int)$parentId : null;

        if (empty($this->commentaire->contenu)) {
            if ($this->wantsJson()) {
                $this->jsonResponse(['success' => false, 'message' => 'Le commentaire est vide.'], 400);
            }
            header("Location: index.php?controller=projet&action=show&id=" . $projetId);
            exit;
        }

        if ($this->commentaire->create()) {
            $newCommentId = $this->commentaire->id;
            if ($this->wantsJson()) {
                // Fetch the newly created comment to return it
                $this->commentaire->readOne($newCommentId);
                $this->jsonResponse(['success' => true, 'data' => get_object_vars($this->commentaire)]);
            }
            header("Location: index.php?controller=projet&action=show&id=" . $projetId . "#comment-" . $newCommentId);
            exit;
        } else {
            if ($this->wantsJson()) {
                $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la publication'], 500);
            }
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit;
        }
    }

    public function edit() {
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $data = $this->requestData();
        $id = $data['id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID not found'], 404);
        }

        if (!$this->commentaire->readOne($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Commentaire introuvable'], 404);
        }

        requireOwnershipOrAdmin((int)$this->commentaire->auteur_id);

        $this->commentaire->contenu = $data['contenu'] ?? '';
        
        if (empty($this->commentaire->contenu)) {
            $this->jsonResponse(['success' => false, 'message' => 'Le contenu ne peut pas être vide'], 400);
        }

        if ($this->commentaire->update()) {
            $this->commentaire->readOne($id); // Refresh data
            $this->jsonResponse(['success' => true, 'data' => get_object_vars($this->commentaire)]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
        }
    }

    public function delete() {
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->requestData();
        $id = $data['id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID not found'], 404);
        }

        if (!$this->commentaire->readOne($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Commentaire introuvable'], 404);
        }

        requireOwnershipOrAdmin((int)$this->commentaire->auteur_id);
        
        if ($this->commentaire->delete()) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression'], 500);
        }
    }

    private function wantsJson() {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return stripos($accept, 'application/json') !== false
            || stripos($contentType, 'application/json') !== false
            || (isset($_GET['format']) && $_GET['format'] === 'json');
    }

    private function requestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
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
