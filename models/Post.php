<?php
class Post {
    private $conn;
    private $table_name = "post";

    public $id;
    public $titre;
    public $topic;
    public $contenu;
    public $date_creation;
    public $auteur_id;
    public $statut;
    public $projet_id;
    public $categorie_id;
    public $city;
    public $country;
    public $latitude;
    public $longitude;
    public $likes_count;
    public $dislikes_count;
    public $current_user_reaction;
    public $auteur_nom;
    public $projet_nom;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($search = "", $topicFilter = "", $sortBy = "latest", $currentUserId = null, $limit = null, $offset = null) {
        $query = "SELECT p.*, p.id_post AS id, u.nom as auteur_nom, prj.nomprojet as projet_nom, cat.titre as categorie_nom, COUNT(DISTINCT c.id_commentaire) AS commentaires_count ";
        
        if ($currentUserId) {
            $query .= ", (SELECT type FROM reaction r WHERE r.post_id = p.id_post AND r.user_id = :current_user_id LIMIT 1) as current_user_reaction ";
        } else {
            $query .= ", NULL as current_user_reaction ";
        }

        $query .= ", (SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'LIKE') AS likes_count ";
        $query .= ", (SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'DISLIKE') AS dislikes_count ";

        $query .= "FROM " . $this->table_name . " p 
                  LEFT JOIN commentaire c ON c.post_id = p.id_post
                  LEFT JOIN users u ON p.auteur_id = u.id
                  LEFT JOIN projet prj ON p.projet_id = prj.id
                  LEFT JOIN categorie cat ON p.categorie_id = cat.id";
        
        $conditions = [];
        
        if (!empty($search)) {
            $conditions[] = "(p.titre LIKE :search OR p.contenu LIKE :search)";
        }
        
        if (!empty($topicFilter)) {
            $conditions[] = "p.topic = :topic";
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " GROUP BY p.id_post ";
        
        if ($sortBy == "most_commented") {
            $query .= " ORDER BY commentaires_count DESC, p.id_post DESC";
        } elseif ($sortBy == "most_liked") {
            $query .= " ORDER BY likes_count DESC, p.id_post DESC";
        } else {
            $query .= " ORDER BY p.id_post DESC";
        }

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchParam = "%{$search}%";
            $stmt->bindParam(":search", $searchParam);
        }
        if (!empty($topicFilter)) {
            $stmt->bindParam(":topic", $topicFilter);
        }
        if ($currentUserId) {
            $stmt->bindParam(":current_user_id", $currentUserId);
        }

        $stmt->execute();
        return $stmt;
    }

    public function countAll($search = "", $topicFilter = "") {
        $query = "SELECT COUNT(*) AS total FROM " . $this->table_name . " p";
        $conditions = [];

        if (!empty($search)) {
            $conditions[] = "(p.titre LIKE :search OR p.contenu LIKE :search)";
        }

        if (!empty($topicFilter)) {
            $conditions[] = "p.topic = :topic";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchParam = "%{$search}%";
            $stmt->bindParam(":search", $searchParam);
        }

        if (!empty($topicFilter)) {
            $stmt->bindParam(":topic", $topicFilter);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET titre=:titre, topic=:topic, contenu=:contenu, auteur_id=:auteur_id, statut=:statut, projet_id=:projet_id, categorie_id=:categorie_id, city=:city, country=:country, latitude=:latitude, longitude=:longitude";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->topic = htmlspecialchars(strip_tags($this->topic));
        $this->contenu = $this->contenu; // Allow HTML
        $this->auteur_id = htmlspecialchars(strip_tags($this->auteur_id));
        $this->statut = htmlspecialchars(strip_tags($this->statut));
        $this->projet_id = empty($this->projet_id) ? null : htmlspecialchars(strip_tags($this->projet_id));
        $this->categorie_id = empty($this->categorie_id) ? null : htmlspecialchars(strip_tags($this->categorie_id));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->latitude = htmlspecialchars(strip_tags($this->latitude));
        $this->longitude = htmlspecialchars(strip_tags($this->longitude));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":topic", $this->topic);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":auteur_id", $this->auteur_id);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":projet_id", $this->projet_id);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public $current_user_liked = false;

    public function readOne($currentUserId = null) {
        $query = "SELECT p.*, p.id_post AS id, u.nom as auteur_nom, prj.nomprojet as projet_nom, cat.titre as categorie_nom ";

        if ($currentUserId) {
            $query .= ", (SELECT type FROM reaction r WHERE r.post_id = p.id_post AND r.user_id = :current_user_id LIMIT 1) as current_user_reaction ";
        } else {
            $query .= ", NULL as current_user_reaction ";
        }
        
        $query .= ", (SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'LIKE') AS likes_count ";
        $query .= ", (SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'DISLIKE') AS dislikes_count ";

        $query .= "FROM " . $this->table_name . " p 
                   LEFT JOIN users u ON p.auteur_id = u.id
                   LEFT JOIN projet prj ON p.projet_id = prj.id
                   LEFT JOIN categorie cat ON p.categorie_id = cat.id
                   WHERE p.id_post = :id_post
                   LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_post", $this->id);

        if ($currentUserId) {
            $stmt->bindParam(":current_user_id", $currentUserId);
        }

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->titre = $row['titre'];
            $this->topic = $row['topic'];
            $this->contenu = $row['contenu'];
            $this->date_creation = $row['date_creation'];
            $this->auteur_id = $row['auteur_id'];
            $this->statut = $row['statut'];
            $this->projet_id = $row['projet_id'];
            $this->categorie_id = $row['categorie_id'];
            $this->city = $row['city'];
            $this->country = $row['country'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
            $this->auteur_nom = $row['auteur_nom'];
            $this->projet_nom = $row['projet_nom'];
            $this->likes_count = $row['likes_count'];
            $this->dislikes_count = $row['dislikes_count'];
            $this->current_user_reaction = $row['current_user_reaction'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET titre=:titre, topic=:topic, contenu=:contenu, statut=:statut, projet_id=:projet_id, categorie_id=:categorie_id 
                  WHERE id_post = :id";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->topic = htmlspecialchars(strip_tags($this->topic));
        $this->contenu = $this->contenu; // Allow HTML
        $this->statut = htmlspecialchars(strip_tags($this->statut));
        $this->projet_id = empty($this->projet_id) ? null : htmlspecialchars(strip_tags($this->projet_id));
        $this->categorie_id = empty($this->categorie_id) ? null : htmlspecialchars(strip_tags($this->categorie_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":topic", $this->topic);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":projet_id", $this->projet_id);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function incrementLike() {
        $query = "UPDATE " . $this->table_name . " 
                  SET likes_count = likes_count + 1 
                  WHERE id_post = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_post = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function isOwner() {
        if (isAdmin()) return true;
        $query = "SELECT 1 FROM " . $this->table_name . " WHERE id_post = ? AND auteur_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function exists($postId): bool {
        if (empty($postId)) {
            return false;
        }

        $query = "SELECT 1 FROM " . $this->table_name . " WHERE id_post = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, (int) $postId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
}
?>
