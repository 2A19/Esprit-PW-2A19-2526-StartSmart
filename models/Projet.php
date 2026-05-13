<?php
class Projet {
    private $conn;
    private $table_name = "projet";

    public $id;
    public $num;
    public $nomprojet;
    public $description;
    public $datedebut;
    public $datefin;
    public $budget;
    public $gain;
    public $categorie_id;
    public $auteur_id;
    public $city;
    public $country;
    public $latitude;
    public $longitude;
    public $categorie_nom;
    public $statut;
    public $created_at;
    public $updated_at;
    public $views_count;
    public $likes_count;
    public $dislikes_count;
    public $commentaires_count;
    public $current_user_reaction;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($search = "", $ownerId = null, $sortBy = "latest", $currentUserId = null, $limit = null, $offset = null) {
        $query = "SELECT p.*, c.titre as categorie_nom,
                        (SELECT COUNT(*) FROM projet_commentaire pc WHERE pc.projet_id = p.id AND pc.statut = 'actif') as commentaires_count,
                        (SELECT COUNT(*) FROM projet_reaction pr_like WHERE pr_like.projet_id = p.id AND pr_like.type = 'LIKE') as likes_count,
                        (SELECT COUNT(*) FROM projet_reaction pr_dislike WHERE pr_dislike.projet_id = p.id AND pr_dislike.type = 'DISLIKE') as dislikes_count";

        if ($currentUserId) {
            $query .= ", (SELECT pr_user.type FROM projet_reaction pr_user WHERE pr_user.projet_id = p.id AND pr_user.user_id = :current_user_id) as current_user_reaction";
        }

        $query .= " FROM " . $this->table_name . " p
                  LEFT JOIN categorie c ON p.categorie_id = c.id";

        $whereClauses = [];
        if (!empty($search)) {
            $whereClauses[] = "(p.nomprojet LIKE :search OR p.num LIKE :search OR p.description LIKE :search)";
        }
        if ($ownerId !== null) {
            $whereClauses[] = "p.auteur_id = :owner_id";
        }
        $whereClauses[] = "p.statut != 'deleted'";

        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $query .= " GROUP BY p.id ";

        if ($sortBy == "trending") {
            $query .= " ORDER BY likes_count DESC, commentaires_count DESC, p.id DESC";
        } elseif ($sortBy == "most_discussed") {
            $query .= " ORDER BY commentaires_count DESC, p.id DESC";
        } elseif ($sortBy == "budget_desc") {
            $query .= " ORDER BY p.budget DESC, p.id DESC";
        } else {
            $query .= " ORDER BY p.id DESC"; // latest
        }

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        if ($ownerId !== null) {
            $stmt->bindParam(":owner_id", $ownerId);
        }
        
        if ($currentUserId) {
            $stmt->bindParam(":current_user_id", $currentUserId);
        }

        $stmt->execute();
        return $stmt;
    }

    public function countAll($search = "", $ownerId = null) {
        $query = "SELECT COUNT(DISTINCT p.id) as total FROM " . $this->table_name . " p";
        $conditions = [];

        if (!empty($search)) {
            $conditions[] = "(p.nomprojet LIKE :search OR p.num LIKE :search OR p.description LIKE :search)";
        }

        if ($ownerId !== null) {
            $conditions[] = "p.auteur_id = :owner_id";
        }

        $conditions[] = "p.statut != 'deleted'";

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        if ($ownerId !== null) {
            $stmt->bindParam(":owner_id", $ownerId);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public $etape; // new property

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (num, nomprojet, description, datedebut, datefin, budget, gain, categorie_id, auteur_id, city, country, latitude, longitude, statut, etape, created_at, updated_at) 
                  VALUES (:num, :nomprojet, :description, :datedebut, :datefin, :budget, :gain, :categorie_id, :auteur_id, :city, :country, :latitude, :longitude, :statut, :etape, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->nomprojet = htmlspecialchars(strip_tags($this->nomprojet));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->datedebut = htmlspecialchars(strip_tags($this->datedebut));
        $this->datefin = htmlspecialchars(strip_tags($this->datefin));
        $this->budget = htmlspecialchars(strip_tags($this->budget));
        $this->gain = htmlspecialchars(strip_tags($this->gain ?? 0));
        $this->categorie_id = htmlspecialchars(strip_tags($this->categorie_id));
        $this->auteur_id = htmlspecialchars(strip_tags($this->auteur_id));
        $this->city = htmlspecialchars(strip_tags($this->city ?? ''));
        $this->country = htmlspecialchars(strip_tags($this->country ?? ''));
        $this->latitude = !empty($this->latitude) ? (float)$this->latitude : null;
        $this->longitude = !empty($this->longitude) ? (float)$this->longitude : null;
        $this->statut = htmlspecialchars(strip_tags($this->statut ?? 'actif'));
        $this->etape = htmlspecialchars(strip_tags($this->etape ?? 'idea'));

        // Bind
        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":nomprojet", $this->nomprojet);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":datedebut", $this->datedebut);
        $stmt->bindParam(":datefin", $this->datefin);
        $stmt->bindParam(":budget", $this->budget);
        $stmt->bindParam(":gain", $this->gain);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":auteur_id", $this->auteur_id);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":etape", $this->etape);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT p.*, 
                    CONCAT(u.nom, ' ', u.prenom) AS auteur_nom,
                    c.titre as categorie_nom,
                    (SELECT COUNT(*) FROM projet_commentaire pc WHERE pc.projet_id = p.id AND pc.statut = 'actif') AS commentaires_count,
                    (SELECT COUNT(*) FROM projet_reaction pr_like WHERE pr_like.projet_id = p.id AND pr_like.type = 'LIKE') AS likes_count,
                    (SELECT COUNT(*) FROM projet_reaction pr_dislike WHERE pr_dislike.projet_id = p.id AND pr_dislike.type = 'DISLIKE') AS dislikes_count 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN users u ON p.auteur_id = u.id
                  LEFT JOIN categorie c ON p.categorie_id = c.id
                  WHERE p.id = ? AND p.statut != 'deleted' 
                  GROUP BY p.id 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
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
                  SET num=:num, nomprojet=:nomprojet, description=:description, datedebut=:datedebut, datefin=:datefin, budget=:budget, gain=:gain, categorie_id=:categorie_id, city=:city, country=:country, latitude=:latitude, longitude=:longitude, statut=:statut, etape=:etape, updated_at=NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->nomprojet = htmlspecialchars(strip_tags($this->nomprojet));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->datedebut = htmlspecialchars(strip_tags($this->datedebut));
        $this->datefin = htmlspecialchars(strip_tags($this->datefin));
        $this->budget = htmlspecialchars(strip_tags($this->budget));
        $this->gain = htmlspecialchars(strip_tags($this->gain ?? 0));
        $this->categorie_id = htmlspecialchars(strip_tags($this->categorie_id));
        $this->city = htmlspecialchars(strip_tags($this->city ?? ''));
        $this->country = htmlspecialchars(strip_tags($this->country ?? ''));
        $this->latitude = !empty($this->latitude) ? (float)$this->latitude : null;
        $this->longitude = !empty($this->longitude) ? (float)$this->longitude : null;
        $this->statut = htmlspecialchars(strip_tags($this->statut ?? 'actif'));
        $this->etape = htmlspecialchars(strip_tags($this->etape ?? 'idea'));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":nomprojet", $this->nomprojet);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":datedebut", $this->datedebut);
        $stmt->bindParam(":datefin", $this->datefin);
        $stmt->bindParam(":budget", $this->budget);
        $stmt->bindParam(":gain", $this->gain);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":etape", $this->etape);
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

    public function exists($projetId) {
        if (empty($projetId)) {
            return false;
        }

        $query = "SELECT 1 FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $projetId);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function getStats($ownerId = null) {
        $query = "SELECT nomprojet, budget, gain FROM " . $this->table_name;
        if ($ownerId !== null) {
            $query .= " WHERE auteur_id = :owner_id";
        }
        $query .= " LIMIT 10"; // Top 10 for charts

        $stmt = $this->conn->prepare($query);
        
        if ($ownerId !== null) {
            $stmt->bindParam(":owner_id", $ownerId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

