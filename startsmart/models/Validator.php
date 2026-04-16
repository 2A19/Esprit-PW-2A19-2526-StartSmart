<?php
class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $val, string $label): self
    {
        if ($val === null || trim((string)$val) === '') {
            $this->errors[$field] = "{$label} est obligatoire.";
        }
        return $this;
    }

    public function minLen(string $field, string $val, int $min, string $label): self
    {
        if (!isset($this->errors[$field]) && mb_strlen(trim($val)) < $min) {
            $this->errors[$field] = "{$label} doit contenir au moins {$min} caractères.";
        }
        return $this;
    }

    public function maxLen(string $field, string $val, int $max, string $label): self
    {
        if (!isset($this->errors[$field]) && mb_strlen(trim($val)) > $max) {
            $this->errors[$field] = "{$label} ne doit pas dépasser {$max} caractères.";
        }
        return $this;
    }

    public function email(string $field, string $val): self
    {
        if (!isset($this->errors[$field]) && !filter_var(trim($val), FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "L'adresse email est invalide.";
        }
        return $this;
    }

    public function password(string $field, string $val): self
    {
        if (isset($this->errors[$field])) return $this;
        if (mb_strlen($val) < 8) {
            $this->errors[$field] = "Mot de passe : minimum 8 caractères.";
        } elseif (!preg_match('/[A-Z]/', $val)) {
            $this->errors[$field] = "Mot de passe : au moins une lettre majuscule requise.";
        } elseif (!preg_match('/[0-9]/', $val)) {
            $this->errors[$field] = "Mot de passe : au moins un chiffre requis.";
        }
        return $this;
    }

    public function confirm(string $field, string $val, string $confirm): self
    {
        if (!isset($this->errors[$field]) && $val !== $confirm) {
            $this->errors[$field] = "Les mots de passe ne correspondent pas.";
        }
        return $this;
    }

    public function phone(string $field, ?string $val): self
    {
        if (!$val || trim($val) === '') return $this;
        if (!isset($this->errors[$field]) && !preg_match('/^[0-9]{8}$/', trim($val))) {
            $this->errors[$field] = "Téléphone : 8 chiffres requis (ex: 55123456).";
        }
        return $this;
    }

    public function url(string $field, ?string $val): self
    {
        if (!$val || trim($val) === '') return $this;
        if (!isset($this->errors[$field]) && !filter_var(trim($val), FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "URL invalide (ex: https://exemple.tn).";
        }
        return $this;
    }

    public function date(string $field, ?string $val, string $label): self
    {
        if (!$val || trim($val) === '') return $this;
        $d = DateTime::createFromFormat('Y-m-d', trim($val));
        if (!isset($this->errors[$field]) && (!$d || $d->format('Y-m-d') !== trim($val))) {
            $this->errors[$field] = "{$label} : date invalide.";
        }
        return $this;
    }

    public function inList(string $field, mixed $val, array $list, string $label): self
    {
        if (!isset($this->errors[$field]) && !in_array($val, $list, true)) {
            $this->errors[$field] = "{$label} : valeur non autorisée.";
        }
        return $this;
    }

    public function fails(): bool  { return !empty($this->errors); }
    public function getErrors(): array { return $this->errors; }
}
