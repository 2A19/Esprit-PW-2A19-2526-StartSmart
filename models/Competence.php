<?php
class Competence {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM competence ORDER BY nom ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserCompetences($userId) {
        $query = "SELECT c.* FROM competence c 
                  JOIN utilisateur_competence uc ON c.id = uc.competence_id 
                  WHERE uc.utilisateur_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectCompetences($projetId) {
        $query = "SELECT c.* FROM competence c 
                  JOIN projet_competence pc ON c.id = pc.competence_id 
                  WHERE pc.projet_id = :projet_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $projetId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserInterests($userId) {
        $query = "SELECT cat.* FROM categorie cat 
                  JOIN utilisateur_interet ui ON cat.id = ui.categorie_id 
                  WHERE ui.utilisateur_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveUserCompetences($userId, $competenceIds) {
        // Delete existing
        $stmt = $this->conn->prepare("DELETE FROM utilisateur_competence WHERE utilisateur_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Insert new
        if (!empty($competenceIds)) {
            $query = "INSERT INTO utilisateur_competence (utilisateur_id, competence_id) VALUES ";
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
        $stmt = $this->conn->prepare("DELETE FROM projet_competence WHERE projet_id = :projet_id");
        $stmt->bindParam(':projet_id', $projetId);
        $stmt->execute();

        // Insert new
        if (!empty($competenceIds)) {
            $query = "INSERT INTO projet_competence (projet_id, competence_id) VALUES ";
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
        $stmt = $this->conn->prepare("DELETE FROM utilisateur_interet WHERE utilisateur_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Insert new
        if (!empty($categoryIds)) {
            $query = "INSERT INTO utilisateur_interet (utilisateur_id, categorie_id) VALUES ";
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
