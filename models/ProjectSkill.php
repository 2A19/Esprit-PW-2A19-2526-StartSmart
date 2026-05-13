<?php

class ProjectSkill {
    private $conn;
    private $table = 'project_skill';

    public $id;
    public $projet_id;
    public $skill_id;
    public $required;
    public $priority;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all required skills for a project
    public function readByProject($projet_id) {
        $query = "SELECT ps.*, s.name, s.category
                  FROM " . $this->table . " ps
                  JOIN skill s ON ps.skill_id = s.id
                  WHERE ps.projet_id = ?
                  ORDER BY ps.priority, s.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$projet_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get required skill IDs for a project
    public function getProjectRequiredSkillIds($projet_id) {
        $query = "SELECT skill_id FROM " . $this->table . " WHERE projet_id = ? AND required = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$projet_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get all skill IDs for a project (required + nice-to-have)
    public function getProjectSkillIds($projet_id) {
        $query = "SELECT skill_id FROM " . $this->table . " WHERE projet_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$projet_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Add skill to project
    public function addSkill($projet_id, $skill_id, $required = true, $priority = 2) {
        $query = "INSERT INTO " . $this->table . " (projet_id, skill_id, required, priority)
                  VALUES (?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE required = ?, priority = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$projet_id, $skill_id, $required ? 1 : 0, $priority, $required ? 1 : 0, $priority]);
    }

    // Remove skill from project
    public function removeSkill($projet_id, $skill_id) {
        $query = "DELETE FROM " . $this->table . " WHERE projet_id = ? AND skill_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$projet_id, $skill_id]);
    }

    // Count required skills for project
    public function countRequiredSkills($projet_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE projet_id = ? AND required = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$projet_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    // Count all skills for project
    public function countAllSkills($projet_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE projet_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$projet_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>
