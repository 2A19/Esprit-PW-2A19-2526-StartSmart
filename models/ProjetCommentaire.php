<?php
class ProjetCommentaire {
    private $conn;
    private $table_name = "projet_commentaire";

    public $id;
    public $projet_id;
    public $auteur_id;
    public $contenu;
    public $parent_id;
    public $date_creation;
    public $statut;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (projet_id, auteur_id, contenu, parent_id, date_creation, statut) 
                  VALUES (:projet_id, :auteur_id, :contenu, :parent_id, NOW(), 'actif')";

        $stmt = $this->conn->prepare($query);

        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->parent_id = $this->parent_id ?? null;

        $stmt->bindParam(":projet_id", $this->projet_id);
        $stmt->bindParam(":auteur_id", $this->auteur_id);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":parent_id", $this->parent_id);

        return $stmt->execute();
    }

    public function readByProjet($projetId) {
        $query = "SELECT c.*, u.nom as auteur_nom, u.prenom as auteur_prenom,
                         0 as likes_count,
                         0 as dislikes_count
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.auteur_id = u.id 
                  WHERE c.projet_id = :projet_id AND c.statut = 'actif'
                  ORDER BY c.parent_id, c.date_creation ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":projet_id", $projetId);
        $stmt->execute();

        return $stmt;
    }

    public function readOne($id) {
        $query = "SELECT c.*, u.nom as auteur_nom, u.prenom as auteur_prenom FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.auteur_id = u.id 
                  WHERE c.id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET contenu = :contenu 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function countByProjet($projetId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE projet_id = :projet_id AND statut = 'actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":projet_id", $projetId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['count'] ?? 0);
    }
}
?>

