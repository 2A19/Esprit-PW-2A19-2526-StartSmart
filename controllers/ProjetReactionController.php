<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/ProjetReaction.php';

class ProjetReactionController {
    private $db;
    private $reaction;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reaction = new ProjetReaction($this->db);
    }

    public function toggleProjet() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $projetId = $data['id_projet'] ?? null;
        $reactionType = $data['reaction_type'] ?? 'LIKE';

        if (!$projetId) {
            echo json_encode(['success' => false, 'message' => 'Projet ID required']);
            exit;
        }

        $this->reaction->user_id = currentUserId();
        $this->reaction->projet_id = $projetId;

        $result = $this->reaction->toggleReaction($reactionType);

        if ($result) {
            $counts = $this->reaction->getCountsByProjet($projetId);
            echo json_encode([
                'success' => true,
                'status' => $result['status'],
                'current_type' => $result['current_type'],
                'likes_count' => $counts['likes'],
                'dislikes_count' => $counts['dislikes']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la réaction']);
        }
        exit;
    }

    public function delete() {
        requireLogin();

        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            exit('ID required');
        }

        if ($this->reaction->delete($id, currentUserId())) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            http_response_code(403);
            exit('Unauthorized or error');
        }
    }
}
?>
