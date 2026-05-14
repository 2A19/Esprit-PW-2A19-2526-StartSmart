<?php
/**
 * Classe TwilioSMSService - Gère l'envoi de SMS via Twilio
 */

$smsConfig = __DIR__ . '/../../config/resource_sms.php';
if (is_readable($smsConfig)) {
    require_once $smsConfig;
}

use Twilio\Rest\Client;

class TwilioSMSService {
    private $client;
    private $twilioPhone;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        // Vérifier que Twilio est configuré
        if (!defined('TWILIO_SID') || !defined('TWILIO_AUTH_TOKEN') || !defined('TWILIO_PHONE_NUMBER')) {
            throw new Exception("Configuration Twilio manquante dans config.php");
        }
        
        // Vérifier que l'autoloader Composer existe
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($autoload)) {
            throw new Exception("Composer dependencies non installées. Veuillez exécuter: composer install");
        }
        
        require_once $autoload;
        
        $this->client = new Client(TWILIO_SID, TWILIO_AUTH_TOKEN);
        $this->twilioPhone = TWILIO_PHONE_NUMBER;
    }
    
    /**
     * Envoie un SMS à un numéro
     * @param string $toPhoneNumber Numéro du destinataire (format international ex: +33612345678)
     * @param string $message Contenu du SMS
     * @return bool
     */
    public function sendSMS($toPhoneNumber, $message) {
        try {
            // Nettoyer le numéro (enlever les espaces)
            $toPhoneNumber = preg_replace('/\s+/', '', $toPhoneNumber);
            
            // Valider le format du numéro de téléphone
            if (empty($toPhoneNumber) || !preg_match('/^\+\d{10,15}$/', $toPhoneNumber)) {
                $this->errors[] = "Numéro de téléphone invalide: $toPhoneNumber";
                return false;
            }
            
            // Envoyer le SMS
            $sms = $this->client->messages->create(
                $toPhoneNumber,
                array(
                    "from" => $this->twilioPhone,
                    "body" => $message
                )
            );
            
            $this->success[] = "SMS envoyé avec succès (SID: {$sms->sid})";
            return true;
            
        } catch (\Twilio\Exceptions\TwilioException $e) {
            $this->errors[] = "Erreur Twilio: " . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->errors[] = "Erreur lors de l'envoi du SMS: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Envoie un SMS à plusieurs numéros
     * @param array $toPhoneNumbers Liste des numéros (format international)
     * @param string $message Contenu du SMS
     * @return array Résultat pour chaque numéro
     */
    public function sendBulkSMS($toPhoneNumbers, $message) {
        $results = [];
        
        foreach ($toPhoneNumbers as $phoneNumber) {
            $results[$phoneNumber] = $this->sendSMS($phoneNumber, $message);
        }
        
        return $results;
    }
    
    /**
     * Envoie une notification au sponsor quand une demande est créée
     * @param string $sponsorPhone Téléphone du sponsor
     * @param string $userName Nom de l'utilisateur qui demande
     * @param string $resourceName Nom de la ressource
     * @return bool
     */
    public function notifyNewDemande($sponsorPhone, $userName, $resourceName) {
        $message = "Un utilisateur a soumis une demande d'accès.\n\n";
        $message .= "Utilisateur: $userName\n";
        $message .= "Ressource: $resourceName\n";
        $message .= "Connectez-vous au backoffice pour examiner la demande.";
        
        return $this->sendSMS($sponsorPhone, $message);
    }
    
    /**
     * Récupère les erreurs
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Récupère les succès
     * @return array
     */
    public function getSuccess() {
        return $this->success;
    }
    
    /**
     * Réinitialise les messages
     */
    public function clearMessages() {
        $this->errors = [];
        $this->success = [];
    }
}
?>
