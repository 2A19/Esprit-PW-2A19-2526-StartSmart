<?php
require_once 'models/Message.php';

class MessageController {
    private $db;
    private $messageModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'message' => 'Non autorisé']);
                exit;
            }
            header("Location: index.php?controller=utilisateur&action=login");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->messageModel = new Message($this->db);
    }

    /**
     * Display the main messaging interface
     */
    public function index() {
        $user_id = $_SESSION['user_id'];
        
        // Get all active conversations
        $conversations = $this->messageModel->getConversations($user_id);
        
        $pageTitle = "Messagerie";
        ob_start();
        require_once 'views/message/index.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    /**
     * API: Get chat history
     */
    public function getHistory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $other_user_id = isset($_GET['other_user_id']) ? (int)$_GET['other_user_id'] : 0;
        $projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;

        if (!$other_user_id || !$projet_id) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            return;
        }

        // Mark unread messages as read
        $this->messageModel->markAsRead($user_id, $other_user_id, $projet_id);

        $history = $this->messageModel->getChatHistory($user_id, $other_user_id, $projet_id);

        echo json_encode(['success' => true, 'messages' => $history]);
    }

    /**
     * API: Send a message
     */
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        $user_id = $_SESSION['user_id'];
        $receiver_id = isset($data['receiver_id']) ? (int)$data['receiver_id'] : 0;
        $projet_id = isset($data['projet_id']) ? (int)$data['projet_id'] : 0;
        $content = isset($data['content']) ? trim($data['content']) : '';

        if (!$receiver_id || !$projet_id || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            return;
        }

        $this->messageModel->sender_id = $user_id;
        $this->messageModel->receiver_id = $receiver_id;
        $this->messageModel->projet_id = $projet_id;
        $this->messageModel->content = $content;

        if ($this->messageModel->sendMessage()) {
            echo json_encode(['success' => true, 'message' => 'Message envoyé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message']);
        }
    }

    /**
     * API: Get unread count globally
     */
    public function getUnreadCount() {
        $user_id = $_SESSION['user_id'];
        $count = $this->messageModel->getUnreadCount($user_id);
        echo json_encode(['success' => true, 'count' => $count]);
    }
}
?>
