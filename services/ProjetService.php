<?php
namespace Services;

require_once 'dtos/ProjetDTO.php';
require_once 'services/AuthService.php';

use DTOs\ProjetDTO;
use Services\AuthService;
use Exception;

class ProjetService {
    private $projetModel;
    private $categorieModel;

    public function __construct($projetModel, $categorieModel) {
        $this->projetModel = $projetModel;
        $this->categorieModel = $categorieModel;
    }

    public function getAllProjets($search = "", $ownerId = null) {
        // According to new rules, everyone sees all projects, so ownerId filter might be skipped at controller level.
        $stmt = $this->projetModel->readAll($search, $ownerId);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getProjetStats($ownerId = null) {
        return $this->projetModel->getStats($ownerId);
    }

    public function createProjet(ProjetDTO $dto) {
        AuthService::requireLogin();

        if (!$dto->isValid()) {
            throw new Exception("Les données du projet sont invalides.");
        }

        if (!$this->categorieModel->exists($dto->categorie_id)) {
            throw new Exception("La catégorie sélectionnée est introuvable.");
        }

        // Business Rule: Client can only use category created by Admin
        if (AuthService::isClient() && !$this->categorieModel->isCreatedByAdmin($dto->categorie_id)) {
            throw new Exception("Un client ne peut utiliser qu'une catégorie créée par un administrateur.");
        }

        $this->projetModel->num = $dto->num;
        $this->projetModel->nomprojet = $dto->nomprojet;
        $this->projetModel->datedebut = $dto->datedebut;
        $this->projetModel->datefin = $dto->datefin;
        $this->projetModel->budget = $dto->budget;
        $this->projetModel->gain = $dto->gain;
        $this->projetModel->categorie_id = $dto->categorie_id;
        $this->projetModel->auteur_id = AuthService::currentUserId();

        if (!$this->projetModel->create()) {
            throw new Exception("Erreur lors de la création du projet en base de données.");
        }
        
        return true;
    }

    public function updateProjet($id, ProjetDTO $dto) {
        AuthService::requireLogin();
        
        $this->projetModel->id = $id;
        if (!$this->projetModel->readOne()) {
            throw new Exception("Projet introuvable.", 404);
        }

        AuthService::requireOwnershipOrAdmin((int)$this->projetModel->auteur_id);

        if (!$dto->isValid()) {
            throw new Exception("Les données du projet sont invalides.");
        }

        if (!$this->categorieModel->exists($dto->categorie_id)) {
            throw new Exception("La catégorie sélectionnée est introuvable.");
        }

        if (AuthService::isClient() && !$this->categorieModel->isCreatedByAdmin($dto->categorie_id)) {
            throw new Exception("Un client ne peut utiliser qu'une catégorie créée par un administrateur.");
        }

        $this->projetModel->num = $dto->num;
        $this->projetModel->nomprojet = $dto->nomprojet;
        $this->projetModel->datedebut = $dto->datedebut;
        $this->projetModel->datefin = $dto->datefin;
        $this->projetModel->budget = $dto->budget;
        $this->projetModel->gain = $dto->gain;
        $this->projetModel->categorie_id = $dto->categorie_id;

        if (!$this->projetModel->update()) {
            throw new Exception("Erreur lors de la mise à jour du projet.");
        }
        
        return true;
    }

    public function deleteProjet($id) {
        AuthService::requireLogin();
        
        $this->projetModel->id = $id;
        if (!$this->projetModel->readOne()) {
            throw new Exception("Projet introuvable.", 404);
        }

        AuthService::requireOwnershipOrAdmin((int)$this->projetModel->auteur_id);

        if (!$this->projetModel->delete()) {
            throw new Exception("Erreur lors de la suppression du projet.");
        }
        
        return true;
    }

    public function getProjetById($id) {
        $this->projetModel->id = $id;
        if (!$this->projetModel->readOne()) {
            throw new Exception("Projet introuvable.", 404);
        }
        return $this->projetModel;
    }
}
?>
