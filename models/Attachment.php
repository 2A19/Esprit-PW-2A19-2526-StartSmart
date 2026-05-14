<?php
class Attachment {
    private $conn;
    private $table_name = "piece_jointe";

    public $id_piece_jointe;
    public $nom_fichier;
    public $chemin_fichier;
    public $type_fichier;
    public $taille_fichier;
    public $post_id;
    public $commentaire_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nom_fichier=:nom_fichier, chemin_fichier=:chemin_fichier, type_fichier=:type_fichier, 
                     taille_fichier=:taille_fichier, post_id=:post_id, commentaire_id=:commentaire_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nom_fichier", $this->nom_fichier);
        $stmt->bindParam(":chemin_fichier", $this->chemin_fichier);
        $stmt->bindParam(":type_fichier", $this->type_fichier);
        $stmt->bindParam(":taille_fichier", $this->taille_fichier);
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":commentaire_id", $this->commentaire_id);
        
        return $stmt->execute();
    }

    public function getByPostId($post_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCommentId($commentaire_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE commentaire_id = :commentaire_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentaire_id", $commentaire_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByPostId($post_id) {
        $attachments = $this->getByPostId($post_id);
        foreach ($attachments as $att) {
            $this->physicallyDeleteFile($att['chemin_fichier']);
        }
        $query = "DELETE FROM " . $this->table_name . " WHERE post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $post_id);
        return $stmt->execute();
    }

    public function deleteByCommentId($commentaire_id) {
        $attachments = $this->getByCommentId($commentaire_id);
        foreach ($attachments as $att) {
            $this->physicallyDeleteFile($att['chemin_fichier']);
        }
        $query = "DELETE FROM " . $this->table_name . " WHERE commentaire_id = :commentaire_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentaire_id", $commentaire_id);
        return $stmt->execute();
    }

    private function physicallyDeleteFile($filepath) {
        $absolutePath = __DIR__ . '/../' . ltrim($filepath, '/');
        if (file_exists($absolutePath) && is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
?>
