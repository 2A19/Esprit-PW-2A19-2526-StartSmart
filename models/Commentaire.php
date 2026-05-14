<?php
class Commentaire {
    private $conn;
    private $table_name = "commentaire";

    public $id;
    public $contenu;
    public $date_creation;
    public $auteur_id;
    public $post_id;
    public $parent_id;
    public $post_titre; // From joined table
    public $auteur_nom; // From joined table

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($search = "") {
        $query = "SELECT c.*, c.id_commentaire AS id, p.titre AS post_titre, u.nom as auteur_nom 
                  FROM " . $this->table_name . " c 
                  INNER JOIN post p ON c.post_id = p.id_post
                  LEFT JOIN users u ON c.auteur_id = u.id";

        if (!empty($search)) {
            $query .= " WHERE c.contenu LIKE :search OR p.titre LIKE :search";
        }
        $query .= " ORDER BY c.id_commentaire DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        $stmt->execute();
        return $stmt;
    }

    public function countByPost($postId) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE post_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $postId);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET contenu=:contenu, auteur_id=:auteur_id, post_id=:post_id, parent_id=:parent_id";

        $stmt = $this->conn->prepare($query);

        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->auteur_id = htmlspecialchars(strip_tags($this->auteur_id));
        $this->post_id = (int) $this->post_id;
        $this->parent_id = $this->parent_id !== null ? (int) $this->parent_id : null;

        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":auteur_id", $this->auteur_id);
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":parent_id", $this->parent_id);

        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT *, id_commentaire AS id FROM " . $this->table_name . " WHERE id_commentaire = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id_commentaire'];
            $this->contenu = $row['contenu'];
            $this->date_creation = $row['date_creation'];
            $this->auteur_id = $row['auteur_id'];
            $this->post_id = $row['post_id'];
            $this->parent_id = $row['parent_id'];
            return true;
        }
        return false;
    }

    public function update() {
        // Assurez-vous que l'utilisateur est le propriétaire ou un admin
        if (!$this->isOwner()) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET contenu = :contenu, updated_at = NOW() 
                  WHERE id_commentaire = :id";

        $stmt = $this->conn->prepare($query);

        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function readByPost($postId, $parentId = null, $currentUserId = null) {
        $query = "SELECT c.*, c.id_commentaire AS id, u.nom as user_nom, u.prenom as user_prenom, u.role as user_role,
                  COALESCE(NULLIF(u.full_name, ''), CONCAT_WS(' ', u.prenom, u.nom), u.nom, 'Utilisateur') AS auteur_nom,
                  (SELECT COUNT(*) FROM reaction r WHERE r.comment_id = c.id_commentaire AND r.type = 'LIKE') AS likes_count,
                  (SELECT COUNT(*) FROM reaction r WHERE r.comment_id = c.id_commentaire AND r.type = 'DISLIKE') AS dislikes_count";
        if ($currentUserId) {
            $query .= ", (SELECT type FROM reaction r WHERE r.comment_id = c.id_commentaire AND r.user_id = :current_user_id LIMIT 1) AS current_user_reaction";
        } else {
            $query .= ", NULL AS current_user_reaction";
        }
        $query .= " FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.auteur_id = u.id 
                  WHERE c.post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $postId);
        if ($currentUserId) {
            $stmt->bindParam(":current_user_id", $currentUserId);
        }
        $stmt->execute();
        return $stmt;
    }

    public function delete() {
        if (!$this->isOwner()) {
            return false;
        }
        $query = "DELETE FROM " . $this->table_name . " WHERE id_commentaire = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function getStats() {
        $query = "SELECT p.titre, COUNT(c.id_commentaire) AS total
                  FROM " . $this->table_name . " c
                  INNER JOIN post p ON c.post_id = p.id_post
                  GROUP BY p.id_post, p.titre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exists($commentId) {
        if (empty($commentId)) {
            return false;
        }

        $query = "SELECT 1 FROM " . $this->table_name . " WHERE id_commentaire = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $commentId);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function getReplies($commentId) {
        $query = "SELECT c.*, c.id_commentaire AS id, u.nom, u.prenom, u.role 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.auteur_id = u.id 
                  WHERE c.parent_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $commentId);
        $stmt->execute();
        return $stmt;
    }

    public function getPostId($commentId) {
        $query = "SELECT post_id FROM " . $this->table_name . " c
                  WHERE c.id_commentaire = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $commentId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function isOwner() {
        if (isAdmin()) return true;
        $query = "SELECT 1 FROM " . $this->table_name . " WHERE id_commentaire = ? AND auteur_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
