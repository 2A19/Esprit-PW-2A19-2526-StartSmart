<?php

class UserInterest {
    private $conn;
    private $table = 'user_interests';

    public $id;
    public $user_id;
    public $categorie_id;
    public $interest_score; // 1-5
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all interests for a user
    public function readByUser($user_id) {
        $query = "SELECT ui.*, c.titre as categorie_titre
                  FROM " . $this->table . " ui
                  JOIN categorie c ON ui.categorie_id = c.id
                  WHERE ui.user_id = ?
                  ORDER BY ui.interest_score DESC, c.titre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user interested category IDs
    public function getUserInterestCategoryIds($user_id) {
        $query = "SELECT categorie_id FROM " . $this->table . " WHERE user_id = ? ORDER BY interest_score DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Add interest
    public function addInterest($user_id, $categorie_id, $score = 3) {
        $score = max(1, min(5, $score)); // Ensure 1-5 range
        $query = "INSERT INTO " . $this->table . " (user_id, categorie_id, interest_score)
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE interest_score = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $categorie_id, $score, $score]);
    }

    // Remove interest
    public function removeInterest($user_id, $categorie_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = ? AND categorie_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $categorie_id]);
    }

    // Check if user interested in category
    public function hasInterest($user_id, $categorie_id) {
        $query = "SELECT id FROM " . $this->table . " WHERE user_id = ? AND categorie_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $categorie_id]);
        return $stmt->fetch() !== false;
    }

    // Count user interests
    public function countUserInterests($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>
