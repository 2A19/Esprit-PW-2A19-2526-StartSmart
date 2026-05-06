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
        requireLogin();

        $projetId = $_POST['projet_id'] ?? null;
        
        if (!$projetId) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $this->commentaire->projet_id = $projetId;
        $this->commentaire->auteur_id = currentUserId();
        $this->commentaire->contenu = $_POST['contenu'] ?? '';
        
        $parentId = $_POST['parent_id'] ?? '';
        $this->commentaire->parent_id = ($parentId !== '') ? (int)$parentId : null;

        if (empty($this->commentaire->contenu)) {
            header("Location: index.php?controller=projet&action=show&id=" . $projetId);
            exit;
        }

        if ($this->commentaire->create()) {
            header("Location: index.php?controller=projet&action=show&id=" . $projetId . "#comments");
            exit;
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(404);
            exit('ID not found');
        }

        if (!$this->commentaire->readOne($id)) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        requireOwnershipOrAdmin((int)$this->commentaire->auteur_id);

        if ($_POST) {
            $this->commentaire->contenu = $_POST['contenu'] ?? '';
            
            if (empty($this->commentaire->contenu)) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if ($this->commentaire->update()) {
                header("Location: index.php?controller=projet&action=show&id=" . $this->commentaire->projet_id . "#comments");
                exit;
            }
        }

        $pageTitle = "Modifier le Commentaire";
        ob_start();
        require_once 'views/projet/commentaire_edit.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(404);
            exit('ID not found');
        }

        if (!$this->commentaire->readOne($id)) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        requireOwnershipOrAdmin((int)$this->commentaire->auteur_id);
        
        $projetId = $this->commentaire->projet_id;

        if ($this->commentaire->delete()) {
            header("Location: index.php?controller=projet&action=show&id=" . $projetId . "#comments");
            exit;
        } else {
            http_response_code(500);
            exit('Erreur lors de la suppression');
        }
    }
}
?>
