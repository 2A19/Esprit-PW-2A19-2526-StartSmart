<?php
/**
 * Classe RessourceController - Contrôleur pour les ressources
 * Gère les opérations CRUD et les validations pour les ressources
 */

require_once __DIR__ . '/../model/Ressource.php';
require_once __DIR__ . '/../config/Validator.php';

class RessourceController {
    private $ressource;
    private $validator;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        $this->ressource = new Ressource();
        $this->validator = new Validator();
    }
    
    /**
     * Liste toutes les ressources
     * @return array
     */
    public function index() {
        return $this->ressource->getAll();
    }
    
    /**
     * Récupère les ressources disponibles
     * @return array
     */
    public function getAvailable() {
        return $this->ressource->getAvailable();
    }
    
    /**
     * Affiche le formulaire de création
     * @return void
     */
    public function create() {
        // Affiche le formulaire
        include __DIR__ . '/../view/backoffice/ressource-create.php';
    }
    
    /**
     * Crée une nouvelle ressource
     * @param array $data Les données du formulaire
     * @return bool
     */
    public function store($data) {
        // Validation côté serveur
        $validator = new Validator($data);
        
        $validator->required('id_sponsor', 'Sponsor')
                 ->required('nom_ressource', 'Nom de la ressource')
                 ->minLength('nom_ressource', 3, 'Nom de la ressource')
                 ->maxLength('nom_ressource', 150, 'Nom de la ressource')
                 ->required('type_ressource', 'Type de ressource')
                 ->required('quantite_disponible', 'Quantité disponible')
                 ->isNumeric('quantite_disponible', 'Quantité')
                 ->isPositive('quantite_disponible', 'Quantité');
        
        if ($validator->hasErrors()) {
            $this->errors = $validator->getErrors();
            return false;
        }
        
        // Nettoyage des données
        $cleanData = [
            'id_sponsor' => intval($data['id_sponsor']),
            'nom_ressource' => Validator::sanitize($data['nom_ressource']),
            'description' => Validator::sanitize($data['description'] ?? ''),
            'type_ressource' => Validator::sanitize($data['type_ressource']),
            'quantite_disponible' => intval($data['quantite_disponible']),
            'statut' => 'disponible'
        ];
        
        // Création
        if ($this->ressource->create($cleanData)) {
            $this->success[] = "Ressource créée avec succès";
            return true;
        } else {
            $this->errors[] = "Erreur lors de la création de la ressource";
            return false;
        }
    }
    
    /**
     * Affiche les détails d'une ressource
     * @param int $id L'ID de la ressource
     * @return array|false
     */
    public function show($id) {
        return $this->ressource->getById($id);
    }
    
    /**
     * Affiche le formulaire d'édition
     * @param int $id L'ID de la ressource
     * @return void
     */
    public function edit($id) {
        $ressource = $this->ressource->getById($id);
        if (!$ressource) {
            $this->errors[] = "Ressource non trouvée";
            return;
        }
        include __DIR__ . '/../view/backoffice/ressource-edit.php';
    }
    
    /**
     * Met à jour une ressource
     * @param int $id L'ID de la ressource
     * @param array $data Les données du formulaire
     * @return bool
     */
    public function update($id, $data) {
        // Vérifier que la ressource existe
        $ressource = $this->ressource->getById($id);
        if (!$ressource) {
            $this->errors[] = "Ressource non trouvée";
            return false;
        }
        
        // Validation
        $validator = new Validator($data);
        
        $validator->required('nom_ressource', 'Nom de la ressource')
                 ->minLength('nom_ressource', 3, 'Nom de la ressource')
                 ->maxLength('nom_ressource', 150, 'Nom de la ressource')
                 ->required('type_ressource', 'Type de ressource')
                 ->required('quantite_disponible', 'Quantité disponible')
                 ->isNumeric('quantite_disponible', 'Quantité')
                 ->isPositive('quantite_disponible', 'Quantité');
        
        if ($validator->hasErrors()) {
            $this->errors = $validator->getErrors();
            return false;
        }
        
        // Nettoyage
        $cleanData = [
            'nom_ressource' => Validator::sanitize($data['nom_ressource']),
            'description' => Validator::sanitize($data['description'] ?? ''),
            'type_ressource' => Validator::sanitize($data['type_ressource']),
            'quantite_disponible' => intval($data['quantite_disponible']),
            'statut' => $data['statut'] ?? 'disponible'
        ];
        
        if ($this->ressource->update($id, $cleanData)) {
            $this->success[] = "Ressource mise à jour avec succès";
            return true;
        } else {
            $this->errors[] = "Erreur lors de la mise à jour";
            return false;
        }
    }
    
    /**
     * Supprime une ressource
     * @param int $id L'ID de la ressource
     * @return bool
     */
    public function delete($id) {
        if ($this->ressource->delete($id)) {
            $this->success[] = "Ressource supprimée avec succès";
            return true;
        } else {
            $this->errors[] = "Erreur lors de la suppression";
            return false;
        }
    }
    
    /**
     * Archive une ressource
     * @param int $id L'ID de la ressource
     * @return bool
     */
    public function archive($id) {
        if ($this->ressource->archive($id)) {
            $this->success[] = "Ressource archivée avec succès";
            return true;
        } else {
            $this->errors[] = "Erreur lors de l'archivage";
            return false;
        }
    }
    
    /**
     * Récupère les ressources d'un sponsor
     * @param int $id_sponsor L'ID du sponsor
     * @return array
     */
    public function getBySponsor($id_sponsor) {
        return $this->ressource->getBySponsor($id_sponsor);
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
