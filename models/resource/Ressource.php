<?php
/**
 * Classe Ressource - Modèle pour les ressources
 * Gère toutes les opérations CRUD des ressources
 */

require_once __DIR__ . '/../../config/Database.php';

class Ressource {
    private $db;
    private $table = 'ressources';
    
    private $id_ressource;
    private $id_sponsor;
    private $nom_ressource;
    private $description;
    private $type_ressource;
    private $quantite_disponible;
    private $quantite_utilisee;
    private $statut;
    private $date_ajout;
    private $date_modification;
    
    /**
     * Constructeur - Initialise la connexion à la base de données
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crée une nouvelle ressource
     * @param array $data Les données de la ressource
     * @return bool|int ID de la ressource créée ou false
     */
    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " 
                (id_sponsor, nom_ressource, description, type_ressource, quantite_disponible, statut)
                VALUES (:id_sponsor, :nom_ressource, :description, :type_ressource, :quantite_disponible, :statut)";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([
                ':id_sponsor' => $data['id_sponsor'],
                ':nom_ressource' => $data['nom_ressource'],
                ':description' => $data['description'] ?? null,
                ':type_ressource' => $data['type_ressource'],
                ':quantite_disponible' => $data['quantite_disponible'],
                ':statut' => $data['statut'] ?? 'disponible'
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère toutes les ressources
     * @param string $statut Filtre par statut (optionnel)
     * @return array
     */
    public function getAll($statut = null) {
        $sql = "SELECT r.*, s.nom_sponsor 
                FROM " . $this->table . " r
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor";
        
        if ($statut) {
            $sql .= " WHERE r.statut = :statut";
        }
        
        $sql .= " ORDER BY r.date_ajout DESC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($statut) {
            $stmt->execute([':statut' => $statut]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les ressources d'un sponsor
     * @param int $id_sponsor L'ID du sponsor
     * @return array
     */
    public function getBySponsor($id_sponsor) {
        $sql = "SELECT * FROM " . $this->table . " 
                WHERE id_sponsor = :id_sponsor 
                ORDER BY date_ajout DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sponsor' => $id_sponsor]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère une ressource par son ID
     * @param int $id_ressource L'ID de la ressource
     * @return array|false
     */
    public function getById($id_ressource) {
        $sql = "SELECT r.*, s.nom_sponsor 
                FROM " . $this->table . " r
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor
                WHERE r.id_ressource = :id_ressource";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_ressource' => $id_ressource]);
        
        return $stmt->fetch();
    }
    
    /**
     * Met à jour une ressource
     * @param int $id_ressource L'ID de la ressource
     * @param array $data Les données à mettre à jour
     * @return bool
     */
    public function update($id_ressource, $data) {
        $sql = "UPDATE " . $this->table . " SET
                nom_ressource = :nom_ressource,
                description = :description,
                type_ressource = :type_ressource,
                quantite_disponible = :quantite_disponible,
                quantite_utilisee = :quantite_utilisee,
                statut = :statut,
                date_modification = NOW()
                WHERE id_ressource = :id_ressource";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                ':id_ressource' => $id_ressource,
                ':nom_ressource' => $data['nom_ressource'],
                ':description' => $data['description'] ?? null,
                ':type_ressource' => $data['type_ressource'],
                ':quantite_disponible' => $data['quantite_disponible'],
                ':quantite_utilisee' => $data['quantite_utilisee'] ?? 0,
                ':statut' => $data['statut']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Supprime une ressource
     * @param int $id_ressource L'ID de la ressource
     * @return bool
     */
    public function delete($id_ressource) {
        $sql = "DELETE FROM " . $this->table . " WHERE id_ressource = :id_ressource";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([':id_ressource' => $id_ressource]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Archive une ressource
     * @param int $id_ressource L'ID de la ressource
     * @return bool
     */
    public function archive($id_ressource) {
        $sql = "UPDATE " . $this->table . " 
                SET statut = 'archive', date_modification = NOW()
                WHERE id_ressource = :id_ressource";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([':id_ressource' => $id_ressource]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère les ressources disponibles uniquement
     * @return array
     */
    public function getAvailable() {
        $sql = "SELECT r.*, s.nom_sponsor 
                FROM " . $this->table . " r
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor
                WHERE r.statut = 'disponible' AND r.quantite_disponible > r.quantite_utilisee
                ORDER BY r.date_ajout DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les statistiques des ressources
     * @return array
     */
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_ressources,
                SUM(quantite_disponible) as quantite_totale,
                SUM(quantite_utilisee) as quantite_utilisee,
                COUNT(CASE WHEN statut = 'disponible' THEN 1 END) as ressources_disponibles,
                COUNT(CASE WHEN statut = 'indisponible' THEN 1 END) as ressources_indisponibles
                FROM " . $this->table;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Recherche des ressources par nom
     * @param string $search Terme de recherche
     * @return array
     */
    public function searchByName($search) {
        $sql = "SELECT r.*, s.nom_sponsor 
                FROM " . $this->table . " r
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor
                WHERE r.nom_ressource LIKE :search
                ORDER BY r.nom_ressource ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les ressources triées par statut
     * @return array
     */
    public function sortByStatus() {
        $sql = "SELECT r.*, s.nom_sponsor 
                FROM " . $this->table . " r
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor
                ORDER BY r.statut ASC, r.nom_ressource ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les ressources triées par première lettre du nom
     * @return array
     */
    public function sortByFirstLetter() {
        $sql = "SELECT r.*, s.nom_sponsor 
                FROM " . $this->table . " r
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor
                ORDER BY UPPER(LEFT(r.nom_ressource, 1)) ASC, r.nom_ressource ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Recherche et trie les ressources
     * @param string $search Terme de recherche (optionnel)
     * @param string $sortBy Type de tri: 'statut', 'lettre', ou 'date'
     * @return array
     */
    public function searchAndSort($search = '', $sortBy = 'date') {
        $sql = "SELECT r.*, s.nom_sponsor 
                FROM " . $this->table . " r
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor";
        
        if (!empty($search)) {
            $sql .= " WHERE r.nom_ressource LIKE :search";
        }
        
        switch ($sortBy) {
            case 'statut':
                $sql .= " ORDER BY r.statut ASC, r.nom_ressource ASC";
                break;
            case 'lettre':
                $sql .= " ORDER BY UPPER(LEFT(r.nom_ressource, 1)) ASC, r.nom_ressource ASC";
                break;
            case 'date':
            default:
                $sql .= " ORDER BY r.date_ajout DESC";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($search)) {
            $stmt->execute([':search' => '%' . $search . '%']);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
}
?>
