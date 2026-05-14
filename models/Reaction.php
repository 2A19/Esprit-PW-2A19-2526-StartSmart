<?php
class Reaction {
    private $conn;
    private $table_name = "reaction";

    public $id;
    public $user_id;
    public $post_id;
    public $comment_id;
    public $type;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function normalizeType($type) {
        $type = strtoupper(trim((string) $type));
        return in_array($type, ['LIKE', 'DISLIKE'], true) ? $type : 'LIKE';
    }

    private function getExistingPostReaction() {
        $query = "SELECT id_reaction AS id, type FROM " . $this->table_name . " WHERE user_id = :user_id AND post_id = :post_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getExistingCommentReaction() {
        $query = "SELECT id_reaction AS id, type FROM " . $this->table_name . " WHERE user_id = :user_id AND comment_id = :comment_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":comment_id", $this->comment_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function currentUserReactionTypeForPost($postId, $userId) {
        $query = "SELECT type FROM " . $this->table_name . " WHERE post_id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $postId);
        $stmt->bindParam(2, $userId);
        $stmt->execute();

        $type = $stmt->fetchColumn();
        return $type ? strtoupper($type) : null;
    }

    public function currentUserReactionTypeForComment($commentId, $userId) {
        $query = "SELECT type FROM " . $this->table_name . " WHERE comment_id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $commentId);
        $stmt->bindParam(2, $userId);
        $stmt->execute();

        $type = $stmt->fetchColumn();
        return $type ? strtoupper($type) : null;
    }

    public function togglePostReaction($reactionType = 'LIKE') {
        if (empty($this->user_id) || empty($this->post_id)) {
            return false;
        }

        $this->type = $this->normalizeType($reactionType);
        $existing = $this->getExistingPostReaction();

        if ($existing) {
            if (strtoupper($existing['type']) === $this->type) {
                $deleteStmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id_reaction = ?");
                $deleteStmt->bindParam(1, $existing['id']);
                if ($deleteStmt->execute()) {
                    return ["status" => "removed", "current_type" => null];
                }
            }

            $updateStmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET type = :type WHERE id_reaction = :id");
            $updateStmt->bindParam(":type", $this->type);
            $updateStmt->bindParam(":id", $existing['id']);
            if ($updateStmt->execute()) {
                return ["status" => "switched", "current_type" => $this->type];
            }

            return false;
        }

        $insertStmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET user_id = :user_id, post_id = :post_id, type = :type");
        $insertStmt->bindParam(":user_id", $this->user_id);
        $insertStmt->bindParam(":post_id", $this->post_id);
        $insertStmt->bindParam(":type", $this->type);
        if ($insertStmt->execute()) {
            return ["status" => "added", "current_type" => $this->type];
        }

        return false;
    }

    public function toggleCommentReaction($reactionType = 'LIKE') {
        if (empty($this->user_id) || empty($this->comment_id)) {
            return false;
        }

        $this->type = $this->normalizeType($reactionType);
        $existing = $this->getExistingCommentReaction();

        if ($existing) {
            if (strtoupper($existing['type']) === $this->type) {
                $deleteStmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id_reaction = ?");
                $deleteStmt->bindParam(1, $existing['id']);
                if ($deleteStmt->execute()) {
                    return ["status" => "removed", "current_type" => null];
                }
            }

            $updateStmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET type = :type WHERE id_reaction = :id");
            $updateStmt->bindParam(":type", $this->type);
            $updateStmt->bindParam(":id", $existing['id']);
            if ($updateStmt->execute()) {
                return ["status" => "switched", "current_type" => $this->type];
            }

            return false;
        }

        $insertStmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " SET user_id = :user_id, comment_id = :comment_id, type = :type");
        $insertStmt->bindParam(":user_id", $this->user_id);
        $insertStmt->bindParam(":comment_id", $this->comment_id);
        $insertStmt->bindParam(":type", $this->type);
        if ($insertStmt->execute()) {
            return ["status" => "added", "current_type" => $this->type];
        }

        return false;
    }

    public function countPostReactionsByType($postId, $type) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE post_id = ? AND type = ?";
        $stmt = $this->conn->prepare($query);
        $normalizedType = $this->normalizeType($type);
        $stmt->bindParam(1, $postId);
        $stmt->bindParam(2, $normalizedType);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countCommentReactionsByType($commentId, $type) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE comment_id = ? AND type = ?";
        $stmt = $this->conn->prepare($query);
        $normalizedType = $this->normalizeType($type);
        $stmt->bindParam(1, $commentId);
        $stmt->bindParam(2, $normalizedType);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countCommentReactions($commentId) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE comment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $commentId);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function currentUserReactedToComment($commentId, $userId) {
        $query = "SELECT 1 FROM " . $this->table_name . " WHERE comment_id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $commentId);
        $stmt->bindParam(2, $userId);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
}
?>
