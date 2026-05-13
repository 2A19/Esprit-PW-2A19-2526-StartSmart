<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $nom;
    public $prenom;
    public $full_name;
    public $email;
    public $role;
    public $bio;
    public $profile_picture;
    public $city;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->full_name = trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? ''));
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->bio = $row['bio'];
            $this->profile_picture = $row['profile_picture'];
            $this->city = $row['city'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function getStats($userId) {
        $stats = [];
        
        // Forum Posts
        $sql_posts = "SELECT COUNT(*) as cnt FROM post WHERE auteur_id = :user_id";
        $stmt_posts = $this->conn->prepare($sql_posts);
        $stmt_posts->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt_posts->execute();
        $stats['posts'] = (int)($stmt_posts->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Forum Comments
        $sql_comments = "SELECT COUNT(*) as cnt FROM commentaire WHERE auteur_id = :user_id";
        $stmt_comments = $this->conn->prepare($sql_comments);
        $stmt_comments->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt_comments->execute();
        $stats['comments'] = (int)($stmt_comments->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Project Creations
        $sql_projets = "SELECT COUNT(*) as cnt FROM projet WHERE auteur_id = :user_id";
        $stmt_projets = $this->conn->prepare($sql_projets);
        $stmt_projets->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt_projets->execute();
        $stats['projets'] = (int)($stmt_projets->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Project Comments
        $sql_proj_comments = "SELECT COUNT(*) as cnt FROM projet_commentaire WHERE user_id = :user_id";
        $stmt_proj_comments = $this->conn->prepare($sql_proj_comments);
        $stmt_proj_comments->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt_proj_comments->execute();
        $stats['project_comments'] = (int)($stmt_proj_comments->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Reactions Given (Forum + Projects)
        $sql_reactions = "SELECT 
                            (SELECT COUNT(*) FROM reaction WHERE user_id = :user_id) + 
                            (SELECT COUNT(*) FROM projet_reaction WHERE user_id = :user_id) as cnt";
        $stmt_reactions = $this->conn->prepare($sql_reactions);
        $stmt_reactions->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt_reactions->execute();
        $stats['reactions_given'] = (int)($stmt_reactions->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        return $stats;
    }

    public function update() {
        $sql = "UPDATE " . $this->table . " SET bio = :bio, profile_picture = :profile_picture, city = :city, nom = :nom, prenom = :prenom WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        
        $this->bio = isset($this->bio) ? trim($this->bio) : null;
        $this->profile_picture = isset($this->profile_picture) ? trim($this->profile_picture) : null;
        $this->city = isset($this->city) ? trim($this->city) : null;
        $this->nom = isset($this->nom) ? trim($this->nom) : null;
        $this->prenom = isset($this->prenom) ? trim($this->prenom) : null;

        $stmt->bindValue(':bio', $this->bio, PDO::PARAM_STR);
        $stmt->bindValue(':profile_picture', $this->profile_picture, PDO::PARAM_STR);
        $stmt->bindValue(':city', $this->city, PDO::PARAM_STR);
        $stmt->bindValue(':nom', $this->nom, PDO::PARAM_STR);
        $stmt->bindValue(':prenom', $this->prenom, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getRecentPosts($userId, $limit = 10) {
        $sql = "SELECT p.*, COALESCE(u.full_name, CONCAT(u.prenom, ' ', u.nom), u.nom) as auteur_nom FROM post p LEFT JOIN users u ON p.auteur_id = u.id WHERE p.auteur_id = :user_id ORDER BY p.date_creation DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentComments($userId, $limit = 10) {
        $sql = "SELECT c.*, p.titre as post_titre, CONCAT(u.prenom, ' ', u.nom) as auteur_nom FROM commentaire c LEFT JOIN post p ON c.post_id = p.id LEFT JOIN users u ON c.auteur_id = u.id WHERE c.auteur_id = :user_id ORDER BY c.date_creation DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

