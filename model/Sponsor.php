<?php
/**
 * Classe Sponsor - Modèle pour les sponsors
 * Gère toutes les opérations CRUD des sponsors
 */

require_once __DIR__ . '/../config/Database.php';

class Sponsor {
    private $db;
    private $table = 'sponsors';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crée un nouveau sponsor
     * @param array $data Les données du sponsor
     * @return bool|int ID du sponsor créé ou false
     */
    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " 
                (nom_sponsor, email_sponsor, telephone, description, type_ressources, statut)
                VALUES (:nom_sponsor, :email_sponsor, :telephone, :description, :type_ressources, :statut)";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([
                ':nom_sponsor' => $data['nom_sponsor'],
                ':email_sponsor' => $data['email_sponsor'],
                ':telephone' => $data['telephone'] ?? null,
                ':description' => $data['description'] ?? null,
                ':type_ressources' => $data['type_ressources'] ?? null,
                ':statut' => $data['statut'] ?? 'actif'
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère tous les sponsors
     * @param string $statut Filtre par statut (optionnel)
     * @return array
     */
    public function getAll($statut = null) {
        $sql = "SELECT * FROM " . $this->table;
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
        }
        
        $sql .= " ORDER BY nom_sponsor ASC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($statut) {
            $stmt->execute([':statut' => $statut]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un sponsor par son ID
     * @param int $id_sponsor L'ID du sponsor
     * @return array|false
     */
    public function getById($id_sponsor) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id_sponsor = :id_sponsor";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sponsor' => $id_sponsor]);
        
        return $stmt->fetch();
    }
    
    /**
     * Récupère un sponsor par son email
     * @param string $email L'email du sponsor
     * @return array|false
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM " . $this->table . " WHERE email_sponsor = :email";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        return $stmt->fetch();
    }
    
    /**
     * Met à jour un sponsor
     * @param int $id_sponsor L'ID du sponsor
     * @param array $data Les données à mettre à jour
     * @return bool
     */
    public function update($id_sponsor, $data) {
        $sql = "UPDATE " . $this->table . " SET
                nom_sponsor = :nom_sponsor,
                email_sponsor = :email_sponsor,
                telephone = :telephone,
                description = :description,
                type_ressources = :type_ressources,
                statut = :statut
                WHERE id_sponsor = :id_sponsor";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                ':id_sponsor' => $id_sponsor,
                ':nom_sponsor' => $data['nom_sponsor'],
                ':email_sponsor' => $data['email_sponsor'],
                ':telephone' => $data['telephone'] ?? null,
                ':description' => $data['description'] ?? null,
                ':type_ressources' => $data['type_ressources'] ?? null,
                ':statut' => $data['statut']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Supprime un sponsor
     * @param int $id_sponsor L'ID du sponsor
     * @return bool
     */
    public function delete($id_sponsor) {
        $sql = "DELETE FROM " . $this->table . " WHERE id_sponsor = :id_sponsor";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([':id_sponsor' => $id_sponsor]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère les sponsors actifs uniquement
     * @return array
     */
    public function getActive() {
        $sql = "SELECT * FROM " . $this->table . " WHERE statut = 'actif' ORDER BY nom_sponsor ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>
