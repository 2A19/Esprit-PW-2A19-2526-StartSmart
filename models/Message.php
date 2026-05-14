<?php
class Message {
    private $conn;
    private $table_name = "message";

    public $id;
    public $sender_id;
    public $receiver_id;
    public $projet_id;
    public $content;
    public $is_read;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Send a new message
     */
    public function sendMessage() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (sender_id, receiver_id, projet_id, content, created_at) 
                  VALUES (:sender_id, :receiver_id, :projet_id, :content, NOW())";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->sender_id = htmlspecialchars(strip_tags($this->sender_id));
        $this->receiver_id = htmlspecialchars(strip_tags($this->receiver_id));
        $this->projet_id = htmlspecialchars(strip_tags($this->projet_id));
        $this->content = htmlspecialchars(strip_tags($this->content));

        // Bind parameters
        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":receiver_id", $this->receiver_id);
        $stmt->bindParam(":projet_id", $this->projet_id);
        $stmt->bindParam(":content", $this->content);

        return $stmt->execute();
    }

    /**
     * Get all active conversations for a user
     * Returns a list of unique project/user combinations they are chatting with
     */
    public function getConversations($user_id) {
        $query = "
            SELECT 
                m.projet_id,
                p.nomprojet,
                u.id as other_user_id,
                u.nom as other_user_name,
                MAX(m.created_at) as last_message_date,
                SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
            FROM " . $this->table_name . " m
            JOIN projet p ON m.projet_id = p.id
            JOIN users u ON (u.id = m.sender_id OR u.id = m.receiver_id) AND u.id != ?
            WHERE (m.sender_id = ? OR m.receiver_id = ?)
            GROUP BY m.projet_id, p.nomprojet, u.id, u.nom
            ORDER BY last_message_date DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $user_id, $user_id, $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get chat history between two users for a specific project
     */
    public function getChatHistory($user_id, $other_user_id, $projet_id) {
        $query = "
            SELECT m.*, u.nom as sender_name 
            FROM " . $this->table_name . " m
            JOIN users u ON m.sender_id = u.id
            WHERE m.projet_id = ? 
              AND ((m.sender_id = ? AND m.receiver_id = ?) 
                OR (m.sender_id = ? AND m.receiver_id = ?))
            ORDER BY m.created_at ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$projet_id, $user_id, $other_user_id, $other_user_id, $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark messages as read in a specific conversation
     */
    public function markAsRead($receiver_id, $sender_id, $projet_id) {
        $query = "
            UPDATE " . $this->table_name . " 
            SET is_read = 1 
            WHERE receiver_id = :receiver_id 
              AND sender_id = :sender_id 
              AND projet_id = :projet_id 
              AND is_read = 0
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":receiver_id", $receiver_id);
        $stmt->bindParam(":sender_id", $sender_id);
        $stmt->bindParam(":projet_id", $projet_id);
        
        return $stmt->execute();
    }

    /**
     * Count total unread messages for a user across all conversations
     */
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE receiver_id = :user_id AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>

