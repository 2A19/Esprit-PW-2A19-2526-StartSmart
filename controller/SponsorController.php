<?php
/**
 * Classe SponsorController - Contrôleur pour les sponsors
 * Gère les opérations CRUD et les validations pour les sponsors
 */

require_once __DIR__ . '/../model/Sponsor.php';
require_once __DIR__ . '/../config/Validator.php';

class SponsorController {
    private $sponsor;
    private $validator;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        $this->sponsor = new Sponsor();
        $this->validator = new Validator();
    }
    
    /**
     * Liste tous les sponsors
     * @return array
     */
    public function index() {
        return $this->sponsor->getAll();
    }
    
    /**
     * Récupère un sponsor par son ID
     * @param int $id_sponsor L'ID du sponsor
     * @return array|false
     */
    public function show($id_sponsor) {
        return $this->sponsor->getById($id_sponsor);
    }
    
    /**
     * Crée un nouveau sponsor
     * @param array $data Les données du formulaire
     * @return bool
     */
    public function store($data) {
        // Validation côté serveur
        $validator = new Validator($data);
        
        $validator->required('nom_sponsor', 'Nom du sponsor')
                 ->minLength('nom_sponsor', 3, 'Nom du sponsor')
                 ->maxLength('nom_sponsor', 150, 'Nom du sponsor')
                 ->required('email_sponsor', 'Email du sponsor')
                 ->email('email_sponsor', 'Email du sponsor')
                 ->maxLength('telephone', 20, 'Téléphone');
        
        $this->errors = $validator->getErrors();
        
        if (empty($this->errors)) {
            if ($this->sponsor->create($data)) {
                $this->success[] = 'Sponsor créé avec succès!';
                return true;
            } else {
                $this->errors[] = 'Erreur lors de la création du sponsor';
            }
        }
        
        return false;
    }
    
    /**
     * Met à jour un sponsor
     * @param int $id_sponsor L'ID du sponsor
     * @param array $data Les données du formulaire
     * @return bool
     */
    public function update($id_sponsor, $data) {
        // Validation côté serveur
        $validator = new Validator($data);
        
        $validator->required('nom_sponsor', 'Nom du sponsor')
                 ->minLength('nom_sponsor', 3, 'Nom du sponsor')
                 ->maxLength('nom_sponsor', 150, 'Nom du sponsor')
                 ->required('email_sponsor', 'Email du sponsor')
                 ->email('email_sponsor', 'Email du sponsor')
                 ->maxLength('telephone', 20, 'Téléphone');
        
        $this->errors = $validator->getErrors();
        
        if (empty($this->errors)) {
            $data['statut'] = $data['statut'] ?? 'actif';
            if ($this->sponsor->update($id_sponsor, $data)) {
                $this->success[] = 'Sponsor mis à jour avec succès!';
                return true;
            } else {
                $this->errors[] = 'Erreur lors de la mise à jour du sponsor';
            }
        }
        
        return false;
    }
    
    /**
     * Supprime un sponsor
     * @param int $id_sponsor L'ID du sponsor
     * @return bool
     */
    public function delete($id_sponsor) {
        if ($this->sponsor->delete($id_sponsor)) {
            $this->success[] = 'Sponsor supprimé avec succès!';
            return true;
        } else {
            $this->errors[] = 'Erreur lors de la suppression du sponsor';
            return false;
        }
    }
    
    /**
     * Récupère les erreurs de validation
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
