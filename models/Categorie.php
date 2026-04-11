<?php
class Categorie {
    private $conn;
    private $table_name = "categorie";

    public $id;
    public $num;
    public $typeprojet;
    public $nom_investisseur;
    public $projet_id;
    public $nomprojet; // From joined table

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($search = "") {
        $query = "SELECT c.*, p.nomprojet 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN projet p ON c.projet_id = p.id";
        
        if (!empty($search)) {
            $query .= " WHERE c.typeprojet LIKE :search OR c.nom_investisseur LIKE :search OR p.nomprojet LIKE :search";
        }
        $query .= " ORDER BY c.id DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET num=:num, typeprojet=:typeprojet, nom_investisseur=:nom_investisseur, projet_id=:projet_id";

        $stmt = $this->conn->prepare($query);

        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->typeprojet = htmlspecialchars(strip_tags($this->typeprojet));
        $this->nom_investisseur = htmlspecialchars(strip_tags($this->nom_investisseur));
        $this->projet_id = htmlspecialchars(strip_tags($this->projet_id));

        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":typeprojet", $this->typeprojet);
        $stmt->bindParam(":nom_investisseur", $this->nom_investisseur);
        $stmt->bindValue(":projet_id", $this->projet_id ?: null);

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
            $this->typeprojet = $row['typeprojet'];
            $this->nom_investisseur = $row['nom_investisseur'];
            $this->projet_id = $row['projet_id'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET num=:num, typeprojet=:typeprojet, nom_investisseur=:nom_investisseur, projet_id=:projet_id 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->typeprojet = htmlspecialchars(strip_tags($this->typeprojet));
        $this->nom_investisseur = htmlspecialchars(strip_tags($this->nom_investisseur));
        $this->projet_id = htmlspecialchars(strip_tags($this->projet_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":typeprojet", $this->typeprojet);
        $stmt->bindParam(":nom_investisseur", $this->nom_investisseur);
        $stmt->bindValue(":projet_id", $this->projet_id ?: null);
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

    public function getStats() {
        // Example stat: Number of categories by distinct typeprojet
        $query = "SELECT typeprojet, COUNT(*) as total FROM " . $this->table_name . " GROUP BY typeprojet";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
