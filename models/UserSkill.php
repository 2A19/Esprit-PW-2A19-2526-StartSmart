<?php

class UserSkill {
    private $conn;
    private $table = 'user_skill';

    public $id;
    public $user_id;
    public $skill_id;
    public $proficiency_level; // beginner, intermediate, expert
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all skills for a user
    public function readByUser($user_id) {
        $query = "SELECT us.*, s.name, s.category 
                  FROM " . $this->table . " us
                  JOIN skill s ON us.skill_id = s.id
                  WHERE us.user_id = ?
                  ORDER BY s.category, s.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user skills as ID array (for matching)
    public function getUserSkillIds($user_id) {
        $query = "SELECT skill_id FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Add skill to user
    public function addSkill($user_id, $skill_id, $proficiency_level = 'beginner') {
        $query = "INSERT INTO " . $this->table . " (user_id, skill_id, proficiency_level) 
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE proficiency_level = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $skill_id, $proficiency_level, $proficiency_level]);
    }

    // Remove skill from user
    public function removeSkill($user_id, $skill_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = ? AND skill_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $skill_id]);
    }

    // Update proficiency level
    public function updateProficiency($user_id, $skill_id, $level) {
        $query = "UPDATE " . $this->table . " SET proficiency_level = ? WHERE user_id = ? AND skill_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$level, $user_id, $skill_id]);
    }

    // Check if user has skill
    public function hasSkill($user_id, $skill_id) {
        $query = "SELECT id FROM " . $this->table . " WHERE user_id = ? AND skill_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $skill_id]);
        return $stmt->fetch() !== false;
    }

    // Count user skills
    public function countUserSkills($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>
