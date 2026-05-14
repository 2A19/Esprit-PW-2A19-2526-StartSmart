<?php
class Competence {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT id, name AS nom, name, category, description, created_at FROM skill ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserCompetences($userId) {
        $query = "SELECT c.id, c.name AS nom, c.name, c.category, c.description, c.created_at FROM skill c 
                  JOIN user_skill uc ON c.id = uc.skill_id 
                  WHERE uc.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectCompetences($projetId) {
        $query = "SELECT c.id, c.name AS nom, c.name, c.category, c.description, c.created_at FROM skill c 
                  JOIN project_skill pc ON c.id = pc.skill_id 
                  WHERE pc.projet_id = :projet_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $projetId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserInterests($userId) {
        $query = "SELECT cat.* FROM categorie cat 
                  JOIN user_interests ui ON cat.id = ui.categorie_id 
                  WHERE ui.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveUserCompetences($userId, $competenceIds) {
        // Delete existing
        $stmt = $this->conn->prepare("DELETE FROM user_skill WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Insert new
        if (!empty($competenceIds)) {
            $query = "INSERT INTO user_skill (user_id, skill_id) VALUES ";
            $values = [];
            foreach ($competenceIds as $compId) {
                $values[] = "(" . (int)$userId . ", " . (int)$compId . ")";
            }
            $query .= implode(", ", $values);
            $this->conn->query($query);
        }
    }

    public function saveProjectCompetences($projetId, $competenceIds) {
        // Delete existing
        $stmt = $this->conn->prepare("DELETE FROM project_skill WHERE projet_id = :projet_id");
        $stmt->bindParam(':projet_id', $projetId);
        $stmt->execute();

        // Insert new
        if (!empty($competenceIds)) {
            $query = "INSERT INTO project_skill (projet_id, skill_id) VALUES ";
            $values = [];
            foreach ($competenceIds as $compId) {
                $values[] = "(" . (int)$projetId . ", " . (int)$compId . ")";
            }
            $query .= implode(", ", $values);
            $this->conn->query($query);
        }
    }

    public function saveUserInterests($userId, $categoryIds) {
        // Delete existing
        $stmt = $this->conn->prepare("DELETE FROM user_interest WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Insert new
        if (!empty($categoryIds)) {
            $query = "INSERT INTO user_interest (user_id, categorie_id) VALUES ";
            $values = [];
            foreach ($categoryIds as $catId) {
                $values[] = "(" . (int)$userId . ", " . (int)$catId . ")";
            }
            $query .= implode(", ", $values);
            $this->conn->query($query);
        }
    }
}
?>
