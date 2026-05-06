<?php
/**
 * Classe DemandeAccesController - Contrôleur pour les demandes d'accès
 * Gère les opérations CRUD et les validations pour les demandes
 */

require_once __DIR__ . '/../model/DemandeAcces.php';
require_once __DIR__ . '/../model/Ressource.php';
require_once __DIR__ . '/../model/Utilisateur.php';
require_once __DIR__ . '/../model/Sponsor.php';
require_once __DIR__ . '/../config/Validator.php';
require_once __DIR__ . '/../service/TwilioSMSService.php';
require_once __DIR__ . '/../config/Database.php';

class DemandeAccesController {
    private $demande;
    private $ressource;
    private $utilisateur;
    private $sponsor;
    private $smsService;
    private $db;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        $this->demande = new DemandeAcces();
        $this->ressource = new Ressource();
        $this->utilisateur = new Utilisateur();
        $this->sponsor = new Sponsor();
        $this->db = Database::getInstance()->getConnection();
        
        // Initialiser le service SMS si les credentials Twilio sont configurés
        try {
            $this->smsService = new TwilioSMSService();
        } catch (Exception $e) {
            $this->smsService = null;
            // SMS optionnel - ne pas bloquer l'application
        }
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
        
        // Récupérer ou créer l'utilisateur
        $userName = trim($data['id_utilisateur']);
        $user = $this->utilisateur->getByNom($userName);
        
        if (!$user) {
            // Créer un nouvel utilisateur
            // Générer un email unique à partir du nom d'utilisateur
            $email = $this->generateUniqueEmail($userName);
            
            $userData = [
                'nom_utilisateur' => $userName,
                'email_utilisateur' => $email,
                'telephone' => null,
                'entreprise' => 'Non spécifiée',
                'domaine_activite' => null,
                'statut' => 'actif'
            ];
            
            $userId = $this->utilisateur->create($userData);
            if (!$userId) {
                $this->errors[] = "Erreur lors de la création de l'utilisateur";
                return false;
            }
        } else {
            $userId = $user['id_utilisateur'];
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
            'id_utilisateur' => $userId,
            'id_ressource' => intval($data['id_ressource']),
            'quantite_demandee' => $quantite,
            'description_demande' => Validator::sanitize($data['description_demande'] ?? ''),
            'duree_acces_jours' => intval($data['duree_acces_jours'] ?? 30)
        ];
        
        // Création de la demande
        if ($this->demande->create($cleanData)) {
            $this->success[] = "Demande d'accès créée avec succès. En attente d'approbation du sponsor.";
            
            // Envoyer une notification SMS au sponsor
            try {
                $this->sendSponsorNotification($ressource, $userName);
            } catch (Exception $e) {
                // Log l'erreur SMS mais ne bloque pas le flux
                error_log("Erreur lors de l'envoi du SMS: " . $e->getMessage());
            }
            
            return true;
        } else {
            $this->errors[] = "Erreur lors de la création de la demande";
            return false;
        }
    }
    
    /**
     * Envoie une notification SMS au sponsor
     * @param array $ressource Données de la ressource
     * @param string $userName Nom de l'utilisateur
     * @return void
     */
    private function sendSponsorNotification($ressource, $userName) {
        $sponsor = $this->sponsor->getById($ressource['id_sponsor']);
        $sponsorName = $sponsor ? $sponsor['nom_sponsor'] : 'Sponsor';
        $resourceName = $ressource['nom_ressource'];

        $smsSent = false;
        if ($this->smsService && $sponsor && !empty($sponsor['telephone'])) {
            $smsSent = $this->smsService->notifyNewDemande(
                $sponsor['telephone'],
                $userName,
                $resourceName
            );
        }

        // Envoyer aussi un SMS à l'administrateur
        if ($this->smsService && defined('ADMIN_PHONE_NUMBER') && !empty(ADMIN_PHONE_NUMBER)) {
            $adminMsg  = "🔔 StartSmart - Nouvelle demande\n";
            $adminMsg .= "Utilisateur: $userName\n";
            $adminMsg .= "Ressource: $resourceName\n";
            $adminMsg .= "Sponsor: $sponsorName\n";
            $adminMsg .= "Connectez-vous au backoffice pour traiter la demande.";
            $this->smsService->sendSMS(ADMIN_PHONE_NUMBER, $adminMsg);
        }

        // Toujours sauvegarder la notification dans la base de données
        $this->saveNotification($userName, $resourceName, $sponsorName, $smsSent);
    }

    /**
     * Enregistre une notification dans la base de données
     */
    private function saveNotification($userName, $resourceName, $sponsorName, $smsSent) {
        try {
            $smsStatus = $smsSent ? '✅ SMS envoyé' : '📋 Enregistrée';
            $title = "Nouvelle demande - $resourceName";
            $message = "$userName a soumis une demande pour \"$resourceName\" (Sponsor: $sponsorName). $smsStatus au sponsor.";

            $stmt = $this->db->prepare(
                "INSERT INTO notifications (type, title, message, is_read, created_at) VALUES ('sms', ?, ?, 0, NOW())"
            );
            $stmt->execute([$title, $message]);
        } catch (Exception $e) {
            error_log("Erreur sauvegarde notification: " . $e->getMessage());
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
    
    /**
     * Génère un email unique à partir du nom d'utilisateur
     * Format: username_timestamp@startsmart.local
     * @param string $userName Le nom d'utilisateur
     * @return string
     */
    private function generateUniqueEmail($userName) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '_', $userName));
        $timestamp = time();
        return $slug . '_' . $timestamp . '@startsmart.local';
    }
}
?>
