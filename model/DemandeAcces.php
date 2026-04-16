<?php
/**
 * Classe DemandeAcces - Modèle pour les demandes d'accès aux ressources
 * Gère toutes les opérations CRUD des demandes
 */

require_once __DIR__ . '/../config/Database.php';

class DemandeAcces {
    private $db;
    private $table = 'demandes_acces';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crée une nouvelle demande d'accès
     * @param array $data Les données de la demande
     * @return bool|int ID de la demande créée ou false
     */
    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " 
                (id_utilisateur, id_ressource, quantite_demandee, description_demande, duree_acces_jours, statut_demande)
                VALUES (:id_utilisateur, :id_ressource, :quantite_demandee, :description_demande, :duree_acces_jours, :statut_demande)";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([
                ':id_utilisateur' => $data['id_utilisateur'],
                ':id_ressource' => $data['id_ressource'],
                ':quantite_demandee' => $data['quantite_demandee'],
                ':description_demande' => $data['description_demande'] ?? null,
                ':duree_acces_jours' => $data['duree_acces_jours'] ?? 30,
                ':statut_demande' => 'en_attente'
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère toutes les demandes
     * @param string $statut Filtre par statut (optionnel)
     * @return array
     */
    public function getAll($statut = null) {
        $sql = "SELECT d.*, u.nom_utilisateur, u.email_utilisateur, u.entreprise,
                       r.nom_ressource, r.type_ressource, s.nom_sponsor
                FROM " . $this->table . " d
                LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
                LEFT JOIN ressources r ON d.id_ressource = r.id_ressource
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor";
        
        if ($statut) {
            $sql .= " WHERE d.statut_demande = :statut";
        }
        
        $sql .= " ORDER BY d.date_demande DESC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($statut) {
            $stmt->execute([':statut' => $statut]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère une demande par son ID
     * @param int $id_demande L'ID de la demande
     * @return array|false
     */
    public function getById($id_demande) {
        $sql = "SELECT d.*, u.nom_utilisateur, u.email_utilisateur, u.entreprise,
                       r.nom_ressource, r.type_ressource, s.nom_sponsor
                FROM " . $this->table . " d
                LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
                LEFT JOIN ressources r ON d.id_ressource = r.id_ressource
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor
                WHERE d.id_demande = :id_demande";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_demande' => $id_demande]);
        
        return $stmt->fetch();
    }
    
    /**
     * Récupère les demandes d'un utilisateur
     * @param int $id_utilisateur L'ID de l'utilisateur
     * @return array
     */
    public function getByUtilisateur($id_utilisateur) {
        $sql = "SELECT d.*, r.nom_ressource, r.type_ressource, s.nom_sponsor
                FROM " . $this->table . " d
                LEFT JOIN ressources r ON d.id_ressource = r.id_ressource
                LEFT JOIN sponsors s ON r.id_sponsor = s.id_sponsor
                WHERE d.id_utilisateur = :id_utilisateur
                ORDER BY d.date_demande DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_utilisateur' => $id_utilisateur]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les demandes pour une ressource
     * @param int $id_ressource L'ID de la ressource
     * @return array
     */
    public function getByRessource($id_ressource) {
        $sql = "SELECT d.*, u.nom_utilisateur, u.email_utilisateur, u.entreprise
                FROM " . $this->table . " d
                LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
                WHERE d.id_ressource = :id_ressource
                ORDER BY d.date_demande DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_ressource' => $id_ressource]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Accepte une demande
     * @param int $id_demande L'ID de la demande
     * @return bool
     */
    public function accepter($id_demande) {
        $demande = $this->getById($id_demande);
        if (!$demande) return false;
        
        // Calculer la date de fin d'accès
        $date_fin = date('Y-m-d H:i:s', strtotime("+{$demande['duree_acces_jours']} days"));
        
        $sql = "UPDATE " . $this->table . " SET
                statut_demande = 'acceptee',
                date_reponse = NOW(),
                date_fin_acces = :date_fin
                WHERE id_demande = :id_demande";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                ':id_demande' => $id_demande,
                ':date_fin' => $date_fin
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Refuse une demande
     * @param int $id_demande L'ID de la demande
     * @param string $raison La raison du refus
     * @return bool
     */
    public function refuser($id_demande, $raison = '') {
        $sql = "UPDATE " . $this->table . " SET
                statut_demande = 'refusee',
                date_reponse = NOW(),
                raison_refus = :raison
                WHERE id_demande = :id_demande";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                ':id_demande' => $id_demande,
                ':raison' => $raison
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Archive une demande
     * @param int $id_demande L'ID de la demande
     * @return bool
     */
    public function archiver($id_demande) {
        $sql = "UPDATE " . $this->table . " SET
                statut_demande = 'archivee'
                WHERE id_demande = :id_demande";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([':id_demande' => $id_demande]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère les statistiques des demandes
     * @return array
     */
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_demandes,
                COUNT(CASE WHEN statut_demande = 'en_attente' THEN 1 END) as en_attente,
                COUNT(CASE WHEN statut_demande = 'acceptee' THEN 1 END) as acceptees,
                COUNT(CASE WHEN statut_demande = 'refusee' THEN 1 END) as refusees
                FROM " . $this->table;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>
