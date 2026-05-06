<?php
class Notification {
    private $conn;
    public $id_notification;
    public $user_id;
    public $actor_id;
    public $type;
    public $reference_id;
    public $message;
    public $is_read;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $sql = "INSERT INTO notification (user_id, actor_id, type, reference_id, message, is_read) VALUES (:user_id, :actor_id, :type, :reference_id, :message, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(':actor_id', $this->actor_id ?: null, PDO::PARAM_INT);
        $stmt->bindValue(':type', $this->type, PDO::PARAM_STR);
        $stmt->bindValue(':reference_id', $this->reference_id ?: null, PDO::PARAM_INT);
        $stmt->bindValue(':message', $this->message ?: null, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getUnreadByUser($userId, $limit = 10) {
        $sql = "SELECT n.*, u.nom as actor_name FROM notification n LEFT JOIN utilisateur u ON n.actor_id = u.id_utilisateur WHERE n.user_id = :user_id ORDER BY n.created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSinceByUser($userId, $sinceId = 0, $limit = 50) {
        $sql = "SELECT n.*, u.nom as actor_name FROM notification n LEFT JOIN utilisateur u ON n.actor_id = u.id_utilisateur WHERE n.user_id = :user_id AND n.id_notification > :since_id ORDER BY n.id_notification ASC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':since_id', (int)$sinceId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnreadByUser($userId) {
        $sql = "SELECT COUNT(*) as cnt FROM notification WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['cnt'] ?? 0);
    }

    public function markAsRead($id, $userId) {
        $sql = "UPDATE notification SET is_read = 1 WHERE id_notification = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function markAllRead($userId) {
        $sql = "UPDATE notification SET is_read = 1 WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
