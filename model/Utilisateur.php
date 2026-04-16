<?php
/**
 * Classe Utilisateur - Modèle pour les utilisateurs (Start-upers)
 * Gère toutes les opérations CRUD des utilisateurs
 */

require_once __DIR__ . '/../config/Database.php';

class Utilisateur {
    private $db;
    private $table = 'utilisateurs';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crée un nouvel utilisateur
     * @param array $data Les données de l'utilisateur
     * @return bool|int ID de l'utilisateur créé ou false
     */
    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " 
                (nom_utilisateur, email_utilisateur, telephone, entreprise, domaine_activite, statut)
                VALUES (:nom_utilisateur, :email_utilisateur, :telephone, :entreprise, :domaine_activite, :statut)";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([
                ':nom_utilisateur' => $data['nom_utilisateur'],
                ':email_utilisateur' => $data['email_utilisateur'],
                ':telephone' => $data['telephone'] ?? null,
                ':entreprise' => $data['entreprise'] ?? null,
                ':domaine_activite' => $data['domaine_activite'] ?? null,
                ':statut' => $data['statut'] ?? 'actif'
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère tous les utilisateurs
     * @return array
     */
    public function getAll() {
        $sql = "SELECT * FROM " . $this->table . " ORDER BY nom_utilisateur ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un utilisateur par son ID
     * @param int $id_utilisateur L'ID de l'utilisateur
     * @return array|false
     */
    public function getById($id_utilisateur) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id_utilisateur = :id_utilisateur";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_utilisateur' => $id_utilisateur]);
        
        return $stmt->fetch();
    }
    
    /**
     * Récupère un utilisateur par son email
     * @param string $email L'email de l'utilisateur
     * @return array|false
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM " . $this->table . " WHERE email_utilisateur = :email";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        return $stmt->fetch();
    }
    
    /**
     * Met à jour un utilisateur
     * @param int $id_utilisateur L'ID de l'utilisateur
     * @param array $data Les données à mettre à jour
     * @return bool
     */
    public function update($id_utilisateur, $data) {
        $sql = "UPDATE " . $this->table . " SET
                nom_utilisateur = :nom_utilisateur,
                email_utilisateur = :email_utilisateur,
                telephone = :telephone,
                entreprise = :entreprise,
                domaine_activite = :domaine_activite,
                statut = :statut
                WHERE id_utilisateur = :id_utilisateur";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                ':id_utilisateur' => $id_utilisateur,
                ':nom_utilisateur' => $data['nom_utilisateur'],
                ':email_utilisateur' => $data['email_utilisateur'],
                ':telephone' => $data['telephone'] ?? null,
                ':entreprise' => $data['entreprise'] ?? null,
                ':domaine_activite' => $data['domaine_activite'] ?? null,
                ':statut' => $data['statut']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Supprime un utilisateur
     * @param int $id_utilisateur L'ID de l'utilisateur
     * @return bool
     */
    public function delete($id_utilisateur) {
        $sql = "DELETE FROM " . $this->table . " WHERE id_utilisateur = :id_utilisateur";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([':id_utilisateur' => $id_utilisateur]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
