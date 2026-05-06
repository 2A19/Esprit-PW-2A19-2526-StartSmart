<?php
namespace DTOs;

class ProjetDTO {
    public $num;
    public $nomprojet;
    public $datedebut;
    public $datefin;
    public $budget;
    public $gain;
    public $categorie_id;
    public $auteur_id;

    public function __construct($data = []) {
        $this->num = isset($data['num']) ? htmlspecialchars(strip_tags($data['num'])) : null;
        $this->nomprojet = isset($data['nomprojet']) ? htmlspecialchars(strip_tags($data['nomprojet'])) : null;
        $this->datedebut = isset($data['datedebut']) ? htmlspecialchars(strip_tags($data['datedebut'])) : null;
        $this->datefin = isset($data['datefin']) ? htmlspecialchars(strip_tags($data['datefin'])) : null;
        $this->budget = isset($data['budget']) ? htmlspecialchars(strip_tags($data['budget'])) : null;
        $this->gain = isset($data['gain']) ? htmlspecialchars(strip_tags($data['gain'])) : null;
        $this->categorie_id = isset($data['categorie_id']) ? (int)$data['categorie_id'] : null;
        $this->auteur_id = isset($data['auteur_id']) ? (int)$data['auteur_id'] : null;
    }

    public function isValid() {
        if (empty($this->num) || empty($this->nomprojet) || empty($this->datedebut) || empty($this->datefin)) {
            return false;
        }
        if ($this->budget < 0 || $this->gain < 0) {
            return false;
        }
        if (strtotime($this->datefin) < strtotime($this->datedebut)) {
            return false;
        }
        return true;
    }
}
?>
