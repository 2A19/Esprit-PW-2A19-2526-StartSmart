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

    public function toggle() {
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $data = $this->requestData();

        $target = $data['target'] ?? null; // 'projet' or 'commentaire'
        $targetId = $data['id'] ?? null;
        $reactionType = $data['type'] ?? 'LIKE';

        if (!$target || !$targetId || !in_array($target, ['projet', 'commentaire'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Target and ID are required'], 400);
        }

        $this->reaction->user_id = currentUserId();
        if ($target === 'projet') {
            $this->reaction->projet_id = $targetId;
            $this->reaction->commentaire_id = null;
        } else { // commentaire
            $this->reaction->commentaire_id = $targetId;
            $this->reaction->projet_id = null;
        }

        $result = $this->reaction->toggleReaction($reactionType);

        if ($result) {
            $counts = $this->reaction->getCounts($target, $targetId);
            $this->jsonResponse([
                'success' => true,
                'status' => $result['status'],
                'current_type' => $result['current_type'],
                'likes' => $counts['likes'],
                'dislikes' => $counts['dislikes']
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la réaction'], 500);
        }
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
?>
