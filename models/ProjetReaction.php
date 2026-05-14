<?php
class ProjetReaction {
    private $conn;
    private $table_name = "projet_reaction";

    public $id;
    public $user_id;
    public $projet_id;
    public $commentaire_id;
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
        if (empty($this->user_id)) return null;

        if (empty($this->projet_id)) return null;

        $target_col = 'projet_id';
        $target_val = $this->projet_id;

        if (empty($target_val)) return null;

        $query = "SELECT id, type FROM " . $this->table_name . " WHERE user_id = :user_id AND {$target_col} = :target_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":target_id", $target_val);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function currentUserReactionType($target, $targetId, $userId) {
        if ($target !== 'projet') {
            return null;
        }

        $target_col = 'projet_id';
        $query = "SELECT type FROM " . $this->table_name . " WHERE {$target_col} = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $targetId);
        $stmt->bindParam(2, $userId);
        $stmt->execute();

        $type = $stmt->fetchColumn();
        return $type ? strtoupper($type) : null;
    }

    public function getCounts($target, $targetId) {
        if ($target !== 'projet') {
            return ['likes' => 0, 'dislikes' => 0];
        }

        $target_col = 'projet_id';
        $query = "SELECT 
                    SUM(CASE WHEN type = 'LIKE' THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN type = 'DISLIKE' THEN 1 ELSE 0 END) as dislikes
                  FROM " . $this->table_name . " 
                  WHERE {$target_col} = :target_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":target_id", $targetId);
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

        $target_col = 'projet_id';
        $target_val = $this->projet_id;

        $insertStmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " (user_id, {$target_col}, type, created_at) VALUES (:user_id, :target_id, :type, NOW())");
        $insertStmt->bindParam(":user_id", $this->user_id);
        $insertStmt->bindParam(":target_id", $target_val);
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
