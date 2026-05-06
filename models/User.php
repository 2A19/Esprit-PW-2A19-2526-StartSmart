<?php
class User {
    private $conn;
    private $table = 'utilisateur';

    public $id_utilisateur;
    public $nom;
    public $email;
    public $role;
    public $bio;
    public $avatar_url;
    public $city;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id_utilisateur = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id_utilisateur = $row['id_utilisateur'];
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->bio = $row['bio'] ?? null;
            $this->avatar_url = $row['avatar_url'] ?? null;
            $this->city = $row['city'] ?? null;
            $this->created_at = $row['created_at'] ?? null;
            return true;
        }
        return false;
    }

    public function getStats($userId) {
        $stats = [];
        
        // Count posts
        $sql = "SELECT COUNT(*) as cnt FROM post WHERE auteur_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['posts'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Count comments
        $sql = "SELECT COUNT(*) as cnt FROM commentaire WHERE auteur_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['comments'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Count reactions given
        $sql = "SELECT COUNT(*) as cnt FROM reaction WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['reactions'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        return $stats;
    }

    public function update() {
        $sql = "UPDATE " . $this->table . " SET bio = :bio, avatar_url = :avatar_url, city = :city WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($sql);
        
        $this->bio = isset($this->bio) ? trim($this->bio) : null;
        $this->avatar_url = isset($this->avatar_url) ? trim($this->avatar_url) : null;
        $this->city = isset($this->city) ? trim($this->city) : null;

        $stmt->bindValue(':bio', $this->bio, PDO::PARAM_STR);
        $stmt->bindValue(':avatar_url', $this->avatar_url, PDO::PARAM_STR);
        $stmt->bindValue(':city', $this->city, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id_utilisateur, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getRecentPosts($userId, $limit = 10) {
        $sql = "SELECT p.*, u.nom as auteur_nom FROM post p LEFT JOIN utilisateur u ON p.auteur_id = u.id_utilisateur WHERE p.auteur_id = :user_id ORDER BY p.date_creation DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentComments($userId, $limit = 10) {
        $sql = "SELECT c.*, p.titre as post_titre, u.nom as auteur_nom FROM commentaire c LEFT JOIN post p ON c.post_id = p.id_post LEFT JOIN utilisateur u ON c.auteur_id = u.id_utilisateur WHERE c.auteur_id = :user_id ORDER BY c.date_creation DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
