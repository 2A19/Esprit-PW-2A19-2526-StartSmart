<?php
class Post {
    private $conn;
    private $table_name = "post";

    public $id_post;
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
        $query = "SELECT p.*, u.nom as auteur_nom, prj.nomprojet as projet_nom, cat.typeprojet as categorie_nom, COUNT(DISTINCT c.id_commentaire) AS commentaires_count ";
        
        if ($currentUserId) {
            $query .= ", (SELECT type FROM reaction r WHERE r.post_id = p.id_post AND r.user_id = :current_user_id LIMIT 1) as current_user_reaction ";
        } else {
            $query .= ", NULL as current_user_reaction ";
        }

        $query .= ", (SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'LIKE') AS likes_count ";
        $query .= ", (SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'DISLIKE') AS dislikes_count ";

        $query .= "FROM " . $this->table_name . " p 
                  LEFT JOIN commentaire c ON c.post_id = p.id_post
                  LEFT JOIN utilisateur u ON p.auteur_id = u.id_utilisateur
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
                  SET titre=:titre, topic=:topic, contenu=:contenu, auteur_id=:auteur_id, statut=:statut, projet_id=:projet_id,
                  city=:city, country=:country, latitude=:latitude, longitude=:longitude, categorie_id=:categorie_id";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->topic = htmlspecialchars(strip_tags($this->topic ?: 'General'));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->auteur_id = htmlspecialchars(strip_tags($this->auteur_id));
        $this->statut = htmlspecialchars(strip_tags($this->statut));
        $this->projet_id = !empty($this->projet_id) ? (int)$this->projet_id : null;
        $this->categorie_id = !empty($this->categorie_id) ? (int)$this->categorie_id : null;
        $this->city = htmlspecialchars(strip_tags($this->city ?? ''));
        $this->country = htmlspecialchars(strip_tags($this->country ?? ''));
        $this->latitude = !empty($this->latitude) ? (float)$this->latitude : null;
        $this->longitude = !empty($this->longitude) ? (float)$this->longitude : null;

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

        return $stmt->execute();
    }

    public $current_user_liked = false;

    public function readOne($currentUserId = null) {
        $query = "SELECT p.*, u.nom as auteur_nom, prj.nomprojet as projet_nom, ";
        $query .= "(SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'LIKE') AS likes_count, ";
        $query .= "(SELECT COUNT(*) FROM reaction r WHERE r.post_id = p.id_post AND r.type = 'DISLIKE') AS dislikes_count ";
        
        if ($currentUserId) {
            $query .= ", (SELECT type FROM reaction r WHERE r.post_id = p.id_post AND r.user_id = :current_user_id LIMIT 1) as current_user_reaction ";
        } else {
            $query .= ", NULL as current_user_reaction ";
        }

        $query .= "FROM " . $this->table_name . " p 
                  LEFT JOIN utilisateur u ON p.auteur_id = u.id_utilisateur
                  LEFT JOIN projet prj ON p.projet_id = prj.id
                  WHERE p.id_post = :id_post LIMIT 0,1";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_post", $this->id_post);
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
            $this->id_post = $row['id_post'];
            $this->likes_count = (int) $row['likes_count'];
            $this->dislikes_count = (int) $row['dislikes_count'];
            $this->current_user_reaction = $row['current_user_reaction'] ? strtoupper($row['current_user_reaction']) : null;
            $this->auteur_nom = $row['auteur_nom'] ?? null;
            $this->projet_nom = $row['projet_nom'] ?? null;
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET titre=:titre, topic=:topic, contenu=:contenu, statut=:statut, projet_id=:projet_id,
                  city=:city, country=:country, latitude=:latitude, longitude=:longitude, categorie_id=:categorie_id
                  WHERE id_post = :id_post";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->topic = htmlspecialchars(strip_tags($this->topic ?: 'General'));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->statut = htmlspecialchars(strip_tags($this->statut));
        $this->projet_id = !empty($this->projet_id) ? (int)$this->projet_id : null;
        $this->categorie_id = !empty($this->categorie_id) ? (int)$this->categorie_id : null;
        $this->city = htmlspecialchars(strip_tags($this->city ?? ''));
        $this->country = htmlspecialchars(strip_tags($this->country ?? ''));
        $this->latitude = !empty($this->latitude) ? (float)$this->latitude : null;
        $this->longitude = !empty($this->longitude) ? (float)$this->longitude : null;
        $this->id_post = htmlspecialchars(strip_tags($this->id_post));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":topic", $this->topic);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":projet_id", $this->projet_id);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":id_post", $this->id_post);

        return $stmt->execute();
    }

    public function incrementLike() {
        $query = "UPDATE " . $this->table_name . " 
                  SET likes_count = likes_count + 1 
                  WHERE id_post = :id_post";
        $stmt = $this->conn->prepare($query);
        $this->id_post = htmlspecialchars(strip_tags($this->id_post));
        $stmt->bindParam(":id_post", $this->id_post);
        return $stmt->execute();
    }

    public function delete() {
        require_once __DIR__ . '/Attachment.php';
        $attachment = new Attachment($this->conn);
        $this->id_post = htmlspecialchars(strip_tags($this->id_post));
        $attachment->deleteByPostId($this->id_post);

        $query = "DELETE FROM " . $this->table_name . " WHERE id_post = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_post);
        return $stmt->execute();
    }

    public function exists($postId) {
        if (empty($postId)) {
            return false;
        }

        $query = "SELECT 1 FROM " . $this->table_name . " WHERE id_post = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $postId);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function getStats() {
        // Count posts by status
        $query = "SELECT statut, COUNT(*) as total FROM " . $this->table_name . " GROUP BY statut";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
