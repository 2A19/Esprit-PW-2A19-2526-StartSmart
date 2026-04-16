<?php
/**
 * Classe DemandeAccesController - Contrôleur pour les demandes d'accès
 * Gère les opérations CRUD et les validations pour les demandes
 */

require_once __DIR__ . '/../model/DemandeAcces.php';
require_once __DIR__ . '/../model/Ressource.php';
require_once __DIR__ . '/../model/Utilisateur.php';
require_once __DIR__ . '/../config/Validator.php';

class DemandeAccesController {
    private $demande;
    private $ressource;
    private $utilisateur;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        $this->demande = new DemandeAcces();
        $this->ressource = new Ressource();
        $this->utilisateur = new Utilisateur();
    }
    
    /**
     * Liste toutes les demandes
     * @param string $statut Filtre par statut (optionnel)
     * @return array
     */
    public function index($statut = null) {
        return $this->demande->getAll($statut);
    }
    
    /**
     * Affiche le formulaire de création
     * @return void
     */
    public function create() {
        $ressources = $this->ressource->getAvailable();
        include __DIR__ . '/../view/frontoffice/demande-create.php';
    }
    
    /**
     * Crée une nouvelle demande d'accès
     * @param array $data Les données du formulaire
     * @return bool
     */
    public function store($data) {
        // Validation côté serveur
        $validator = new Validator($data);
        
        $validator->required('id_utilisateur', 'Utilisateur')
                 ->required('id_ressource', 'Ressource')
                 ->required('quantite_demandee', 'Quantité demandée')
                 ->isNumeric('quantite_demandee', 'Quantité')
                 ->min('quantite_demandee', 1, 'Quantité');
        
        if ($validator->hasErrors()) {
            $this->errors = $validator->getErrors();
            return false;
        }
        
        // Vérifier que la ressource existe et est disponible
        $ressource = $this->ressource->getById(intval($data['id_ressource']));
        if (!$ressource) {
            $this->errors[] = "Ressource non trouvée";
            return false;
        }
        
        if ($ressource['statut'] !== 'disponible') {
            $this->errors[] = "Cette ressource n'est pas disponible";
            return false;
        }
        
        $quantite = intval($data['quantite_demandee']);
        $disponible = $ressource['quantite_disponible'] - $ressource['quantite_utilisee'];
        
        if ($quantite > $disponible) {
            $this->errors[] = "Quantité insuffisante. Disponible: $disponible";
            return false;
        }
        
        // Nettoyage des données
        $cleanData = [
            'id_utilisateur' => intval($data['id_utilisateur']),
            'id_ressource' => intval($data['id_ressource']),
            'quantite_demandee' => $quantite,
            'description_demande' => Validator::sanitize($data['description_demande'] ?? ''),
            'duree_acces_jours' => intval($data['duree_acces_jours'] ?? 30)
        ];
        
        // Création
        if ($this->demande->create($cleanData)) {
            $this->success[] = "Demande d'accès créée avec succès. En attente d'approbation du sponsor.";
            return true;
        } else {
            $this->errors[] = "Erreur lors de la création de la demande";
            return false;
        }
    }
    
    /**
     * Affiche les détails d'une demande
     * @param int $id L'ID de la demande
     * @return array|false
     */
    public function show($id) {
        return $this->demande->getById($id);
    }
    
    /**
     * Accepte une demande d'accès
     * @param int $id L'ID de la demande
     * @return bool
     */
    public function accepter($id) {
        $demande = $this->demande->getById($id);
        if (!$demande) {
            $this->errors[] = "Demande non trouvée";
            return false;
        }
        
        if ($demande['statut_demande'] !== 'en_attente') {
            $this->errors[] = "Cette demande ne peut pas être acceptée";
            return false;
        }
        
        // Vérifier la quantité disponible
        $ressource = $this->ressource->getById($demande['id_ressource']);
        $disponible = $ressource['quantite_disponible'] - $ressource['quantite_utilisee'];
        
        if ($demande['quantite_demandee'] > $disponible) {
            $this->errors[] = "Quantité insuffisante pour accepter cette demande";
            return false;
        }
        
        // Accepter la demande
        if (!$this->demande->accepter($id)) {
            $this->errors[] = "Erreur lors de l'acceptation";
            return false;
        }
        
        // Mettre à jour la quantité utilisée
        $newQuantiteUtilisee = $ressource['quantite_utilisee'] + $demande['quantite_demandee'];
        $updateData = $ressource;
        $updateData['quantite_utilisee'] = $newQuantiteUtilisee;
        $this->ressource->update($demande['id_ressource'], $updateData);
        
        $this->success[] = "Demande acceptée avec succès";
        return true;
    }
    
    /**
     * Refuse une demande d'accès
     * @param int $id L'ID de la demande
     * @param string $raison La raison du refus
     * @return bool
     */
    public function refuser($id, $raison = '') {
        $demande = $this->demande->getById($id);
        if (!$demande) {
            $this->errors[] = "Demande non trouvée";
            return false;
        }
        
        if ($demande['statut_demande'] !== 'en_attente') {
            $this->errors[] = "Cette demande ne peut pas être refusée";
            return false;
        }
        
        if ($this->demande->refuser($id, $raison)) {
            $this->success[] = "Demande refusée";
            return true;
        } else {
            $this->errors[] = "Erreur lors du refus";
            return false;
        }
    }
    
    /**
     * Récupère les demandes d'un utilisateur
     * @param int $id_utilisateur L'ID de l'utilisateur
     * @return array
     */
    public function getByUtilisateur($id_utilisateur) {
        return $this->demande->getByUtilisateur($id_utilisateur);
    }
    
    /**
     * Récupère les demandes pour une ressource
     * @param int $id_ressource L'ID de la ressource
     * @return array
     */
    public function getByRessource($id_ressource) {
        return $this->demande->getByRessource($id_ressource);
    }
    
    /**
     * Récupère les erreurs
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Récupère les messages de succès
     * @return array
     */
    public function getSuccess() {
        return $this->success;
    }
}
?>
