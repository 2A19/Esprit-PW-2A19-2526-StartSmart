<?php
class Categorie {
    private $conn;
    private $table_name = "categorie";

    public $id;
    public $num;
    public $titre;
    public $nom_investisseur;
    public $created_by_id;
    public $nomprojet; // From joined table

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($search = "") {
        $query = "SELECT c.*, u.role AS creator_role, COUNT(p.id) AS projets_count 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN projet p ON c.id = p.categorie_id 
                  LEFT JOIN users u ON c.created_by_id = u.id";
        
        if (!empty($search)) {
            $query .= " WHERE c.titre LIKE :search OR c.nom_investisseur LIKE :search";
        }
        $query .= " GROUP BY c.id ORDER BY c.id DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        $stmt->execute();
        return $stmt;
    }

    public function readAllForSelect() {
        $query = "SELECT id, titre FROM " . $this->table_name . " ORDER BY titre ASC";
        return $this->conn->query($query);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET num=:num, titre=:titre, nom_investisseur=:nom_investisseur, created_by_id=:created_by_id";

        $stmt = $this->conn->prepare($query);

        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->nom_investisseur = htmlspecialchars(strip_tags($this->nom_investisseur));
        $this->created_by_id = htmlspecialchars(strip_tags($this->created_by_id));

        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":nom_investisseur", $this->nom_investisseur);
        $stmt->bindParam(":created_by_id", $this->created_by_id);

        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->num = $row['num'];
            $this->titre = $row['titre'];
            $this->nom_investisseur = $row['nom_investisseur'];
            $this->created_by_id = $row['created_by_id'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET num=:num, titre=:titre, nom_investisseur=:nom_investisseur 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->nom_investisseur = htmlspecialchars(strip_tags($this->nom_investisseur));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":nom_investisseur", $this->nom_investisseur);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function exists($categorieId) {
        if (empty($categorieId)) {
            return false;
        }

        $query = "SELECT 1 FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $categorieId);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function isCreatedByAdmin($categorieId) {
        $query = "SELECT u.role 
                  FROM " . $this->table_name . " c 
                  INNER JOIN users u ON c.created_by_id = u.id 
                  WHERE c.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $categorieId);
        $stmt->execute();

        return $stmt->fetchColumn() === 'ADMIN';
    }

    public function getStats() {
        // Example stat: Number of categories by distinct typeprojet
        $query = "SELECT titre, COUNT(*) as total FROM " . $this->table_name . " GROUP BY titre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

