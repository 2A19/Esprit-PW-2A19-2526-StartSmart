<?php
class ProjetReaction {
    private $conn;
    private $table_name = "projet_reaction";

    public $id;
    public $user_id;
    public $projet_id;
    public $type;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function normalizeType($type) {
        $type = strtoupper(trim((string) $type));
        return in_array($type, ['LIKE', 'DISLIKE'], true) ? $type : 'LIKE';
    }

    public function getExistingReaction() {
        if (empty($this->user_id) || empty($this->projet_id)) {
            return null;
        }

        $query = "SELECT id, type FROM " . $this->table_name . " WHERE user_id = :user_id AND projet_id = :projet_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":projet_id", $this->projet_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function currentUserReactionType($projetId, $userId) {
        $query = "SELECT type FROM " . $this->table_name . " WHERE projet_id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $projetId);
        $stmt->bindParam(2, $userId);
        $stmt->execute();

        $type = $stmt->fetchColumn();
        return $type ? strtoupper($type) : null;
    }

    public function getCountsByProjet($projetId) {
        $query = "SELECT 
                    SUM(CASE WHEN type = 'LIKE' THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN type = 'DISLIKE' THEN 1 ELSE 0 END) as dislikes
                  FROM " . $this->table_name . " 
                  WHERE projet_id = :projet_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":projet_id", $projetId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'likes' => (int)($row['likes'] ?? 0),
            'dislikes' => (int)($row['dislikes'] ?? 0)
        ];
    }

    public function toggleReaction($reactionType = 'LIKE') {
        if (empty($this->user_id) || empty($this->projet_id)) {
            return false;
        }

        $this->type = $this->normalizeType($reactionType);
        $existing = $this->getExistingReaction();

        if ($existing) {
            if (strtoupper($existing['type']) === $this->type) {
                $deleteStmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
                $deleteStmt->bindParam(1, $existing['id']);
                if ($deleteStmt->execute()) {
                    return ["status" => "removed", "current_type" => null];
                }
            }

            $updateStmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET type = :type WHERE id = :id");
            $updateStmt->bindParam(":type", $this->type);
            $updateStmt->bindParam(":id", $existing['id']);
            if ($updateStmt->execute()) {
                return ["status" => "switched", "current_type" => $this->type];
            }

            return false;
        }

        $insertStmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " (user_id, projet_id, type, created_at) VALUES (:user_id, :projet_id, :type, NOW())");
        $insertStmt->bindParam(":user_id", $this->user_id);
        $insertStmt->bindParam(":projet_id", $this->projet_id);
        $insertStmt->bindParam(":type", $this->type);
        if ($insertStmt->execute()) {
            return ["status" => "added", "current_type" => $this->type];
        }

        return false;
    }

    public function delete($id, $userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }
}
?>
