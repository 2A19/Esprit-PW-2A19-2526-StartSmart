<?php

class Skill {
    private $conn;
    private $table = 'skill';

    public $id;
    public $name;
    public $category;
    public $description;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all skills
    public function readAll() {
        $query = "SELECT id, name AS nom, name, category, description, created_at FROM " . $this->table . " ORDER BY category, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get skills by category
    public function readByCategory($category) {
        $query = "SELECT id, name AS nom, name, category, description, created_at FROM " . $this->table . " WHERE category = ? ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get categories (distinct)
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table . " ORDER BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Create skill
    public function create() {
        $query = "INSERT INTO " . $this->table . " (name, category, description) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->name, $this->category, $this->description]);
    }

    // Get single skill
    public function readOne() {
        $query = "SELECT id, name AS nom, name, category, description, created_at FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->name = $row['name'];
            $this->category = $row['category'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
}
?>
