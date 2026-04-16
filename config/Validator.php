<?php
/**
 * Classe Validator - Contrôle de saisie fonctionnel
 * Tous les contrôles de saisie sont effectués côté serveur (PHP)
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    /**
     * Constructeur
     * @param array $data Les données à valider
     */
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Valide un email
     * @param string $field Le champ à valider
     * @param bool $required Si le champ est obligatoire
     * @return $this
     */
    public function validateEmail($field, $required = true) {
        $value = $this->data[$field] ?? '';
        
        if ($required && empty($value)) {
            $this->errors[$field] = "L'email est obligatoire";
            return $this;
        }
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Format d'email invalide";
        }
        
        return $this;
    }
    
    /**
     * Valide que le champ n'est pas vide
     * @param string $field Le champ à valider
     * @param string $label Le libellé du champ
     * @return $this
     */
    public function required($field, $label = null) {
        $value = $this->data[$field] ?? '';
        $label = $label ?? ucfirst($field);
        
        if (empty(trim($value))) {
            $this->errors[$field] = "$label est obligatoire";
        }
        
        return $this;
    }
    
    /**
     * Valide la longueur minimum
     * @param string $field Le champ à valider
     * @param int $min Longueur minimale
     * @param string $label Le libellé du champ
     * @return $this
     */
    public function minLength($field, $min, $label = null) {
        $value = $this->data[$field] ?? '';
        $label = $label ?? ucfirst($field);
        
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = "$label doit avoir au minimum $min caractères";
        }
        
        return $this;
    }
    
    /**
     * Valide la longueur maximum
     * @param string $field Le champ à valider
     * @param int $max Longueur maximale
     * @param string $label Le libellé du champ
     * @return $this
     */
    public function maxLength($field, $max, $label = null) {
        $value = $this->data[$field] ?? '';
        $label = $label ?? ucfirst($field);
        
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = "$label ne doit pas dépasser $max caractères";
        }
        
        return $this;
    }
    
    /**
     * Valide que le champ est un nombre
     * @param string $field Le champ à valider
     * @param string $label Le libellé du champ
     * @return $this
     */
    public function isNumeric($field, $label = null) {
        $value = $this->data[$field] ?? '';
        $label = $label ?? ucfirst($field);
        
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "$label doit être un nombre";
        }
        
        return $this;
    }
    
    /**
     * Valide que le nombre est positif
     * @param string $field Le champ à valider
     * @param string $label Le libellé du champ
     * @return $this
     */
    public function isPositive($field, $label = null) {
        $value = $this->data[$field] ?? '';
        $label = $label ?? ucfirst($field);
        
        if (!empty($value) && is_numeric($value) && $value < 0) {
            $this->errors[$field] = "$label doit être positif";
        }
        
        return $this;
    }
    
    /**
     * Valide que le nombre est supérieur à une valeur
     * @param string $field Le champ à valider
     * @param int $min Valeur minimale
     * @param string $label Le libellé du champ
     * @return $this
     */
    public function min($field, $min, $label = null) {
        $value = $this->data[$field] ?? '';
        $label = $label ?? ucfirst($field);
        
        if (!empty($value) && is_numeric($value) && $value < $min) {
            $this->errors[$field] = "$label doit être au minimum $min";
        }
        
        return $this;
    }
    
    /**
     * Valide un numéro de téléphone
     * @param string $field Le champ à valider
     * @param bool $required Si le champ est obligatoire
     * @return $this
     */
    public function validatePhone($field, $required = false) {
        $value = $this->data[$field] ?? '';
        
        if ($required && empty($value)) {
            $this->errors[$field] = "Le téléphone est obligatoire";
            return $this;
        }
        
        if (!empty($value)) {
            // Accepte formats: +33612345678, 0612345678, +33 6 12 34 56 78, etc.
            $pattern = '/^(\+33|0)[1-9](\s?|\d){8,9}$/';
            if (!preg_match($pattern, str_replace(' ', '', $value))) {
                $this->errors[$field] = "Format de téléphone invalide";
            }
        }
        
        return $this;
    }
    
    /**
     * Vérifie que deux champs sont identiques
     * @param string $field1 Premier champ
     * @param string $field2 Deuxième champ
     * @param string $label Libellé
     * @return $this
     */
    public function match($field1, $field2, $label = null) {
        $value1 = $this->data[$field1] ?? '';
        $value2 = $this->data[$field2] ?? '';
        $label = $label ?? ucfirst($field1);
        
        if ($value1 !== $value2) {
            $this->errors[$field1] = "Les $label ne correspondent pas";
        }
        
        return $this;
    }
    
    /**
     * Valide que le champ contient uniquement des caractères alphanumériques
     * @param string $field Le champ à valider
     * @param string $label Le libellé du champ
     * @return $this
     */
    public function alphaNumeric($field, $label = null) {
        $value = $this->data[$field] ?? '';
        $label = $label ?? ucfirst($field);
        
        if (!empty($value) && !ctype_alnum(str_replace(' ', '', $value))) {
            $this->errors[$field] = "$label doit contenir uniquement des lettres et des chiffres";
        }
        
        return $this;
    }
    
    /**
     * Récupère tous les erreurs
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Récupère une erreur spécifique
     * @param string $field Le champ
     * @return string|null
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Vérifie s'il y a des erreurs
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Valide les données
     * @return bool
     */
    public function validate() {
        return !$this->hasErrors();
    }
    
    /**
     * Nettoie une chaîne de caractères
     * @param string $data La chaîne à nettoyer
     * @return string
     */
    public static function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Échappe une chaîne pour la base de données
     * @param string $data La chaîne à échapper
     * @return string
     */
    public static function escape($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}
?>
