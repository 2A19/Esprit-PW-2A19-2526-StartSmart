# Guide Complet des Validations - StartSmart

## Overview

StartSmart utilise une validation **côté serveur obligatoire** implémentée via la classe `Validator` dans `config/Validator.php`.

**Important** : Aucune validation HTML5 n'est utilisée. Toutes les validations sont effectuées en PHP.

## Classes de Validation

### 1. Classe Validator

Fichier : `config/Validator.php`

```php
$validator = new Validator($data);
$validator->required('champ')
          ->minLength('champ', 3)
          ->validate();

if ($validator->hasErrors()) {
    $errors = $validator->getErrors();
}
```

## Méthodes de Validation

### Email

```php
$validator->validateEmail('email', $required = true);
```

**Exemple** :
```php
$validator = new Validator(['email' => 'user@example.com']);
$validator->validateEmail('email');

if ($validator->hasErrors()) {
    // Affiche : "Format d'email invalide"
}
```

### Champ Obligatoire

```php
$validator->required('nom', 'Nom complet');
```

**Exemple** :
```php
$validator = new Validator(['nom' => '']);
$validator->required('nom', 'Nom complet');

if ($validator->hasErrors()) {
    // Affiche : "Nom complet est obligatoire"
}
```

### Longueur Minimale

```php
$validator->minLength('password', 8, 'Mot de passe');
```

**Exemple** :
```php
$validator = new Validator(['password' => 'abc']);
$validator->minLength('password', 8, 'Mot de passe');

if ($validator->hasErrors()) {
    // Affiche : "Mot de passe doit avoir au minimum 8 caractères"
}
```

### Longueur Maximale

```php
$validator->maxLength('nom', 150, 'Nom');
```

### Nombre

```php
$validator->isNumeric('quantite', 'Quantité');
```

**Accepte** : 123, 456.78, -10, etc.

### Nombre Positif

```php
$validator->isPositive('quantite', 'Quantité');
```

**Refuse** : les nombres négatifs

### Valeur Minimale

```php
$validator->min('age', 18, 'Âge');
```

### Téléphone

```php
$validator->validatePhone('telephone', $required = false);
```

**Format accepté** :
- `+33612345678`
- `0612345678`
- `+33 6 12 34 56 78`
- Variantes avec espaces

### Correspondance

```php
$validator->match('password', 'password_confirm', 'Mot de passe');
```

**Exemple** :
```php
$validator = new Validator([
    'password' => 'secure123',
    'password_confirm' => 'secure123'
]);
$validator->match('password', 'password_confirm', 'Mot de passe');
```

### Alphanumérique

```php
$validator->alphaNumeric('code', 'Code');
```

## Validations par Formulaire

### Formulaire de Création de Ressource

```php
$validator = new Validator($_POST);

$validator->required('id_sponsor', 'Sponsor')
         ->required('nom_ressource', 'Nom de la ressource')
         ->minLength('nom_ressource', 3, 'Nom de la ressource')
         ->maxLength('nom_ressource', 150, 'Nom de la ressource')
         ->required('type_ressource', 'Type de ressource')
         ->required('quantite_disponible', 'Quantité disponible')
         ->isNumeric('quantite_disponible', 'Quantité')
         ->isPositive('quantite_disponible', 'Quantité');

if ($validator->hasErrors()) {
    $errors = $validator->getErrors();
    // Afficher les erreurs
} else {
    // Créer la ressource
}
```

### Formulaire de Création de Demande

```php
$validator = new Validator($_POST);

$validator->required('id_utilisateur', 'Utilisateur')
         ->required('id_ressource', 'Ressource')
         ->required('quantite_demandee', 'Quantité demandée')
         ->isNumeric('quantite_demandee', 'Quantité')
         ->min('quantite_demandee', 1, 'Quantité');

if ($validator->hasErrors()) {
    $errors = $validator->getErrors();
}
```

## Nettoyage des Données

### Sanitization

```php
$safe_data = Validator::sanitize($user_input);
```

**Fonction** : Échappe les caractères spéciaux HTML
**Utilisation** : Avant de stocker en base de données
**Exemple** :
```php
$nom = Validator::sanitize("<script>alert('xss')</script>");
// Résultat : "&lt;script&gt;alert('xss')&lt;/script&gt;"
```

### Escape

```php
$safe_display = Validator::escape($data);
```

**Fonction** : Échappe pour affichage HTML sécurisé
**Utilisation** : Avant d'afficher en HTML
**Exemple** :
```php
echo Validator::escape($user_input);
```

## Flow de Validation Complet

### 1. Recevoir les Données

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
}
```

### 2. Valider

```php
$validator = new Validator($data);
$validator->required('nom')
         ->required('email')
         ->validateEmail('email');
```

### 3. Vérifier les Erreurs

```php
if ($validator->hasErrors()) {
    $errors = $validator->getErrors();
    // Afficher les erreurs
    $_SESSION['error'] = $errors;
    return false;
}
```

### 4. Nettoyer les Données

```php
$clean_data = [
    'nom' => Validator::sanitize($data['nom']),
    'email' => Validator::sanitize($data['email'])
];
```

### 5. Traiter les Données

```php
$result = $model->create($clean_data);
if ($result) {
    $_SESSION['success'][] = 'Créé avec succès!';
}
```

## Affichage des Erreurs dans les Vues

### Erreur Globale

```php
<?php if (isset($errors) && is_string($errors)): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($errors); ?>
    </div>
<?php endif; ?>
```

### Erreur par Champ

```php
<div class="form-group">
    <input type="text" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
    <?php if (isset($errors['nom'])): ?>
        <div class="form-error">
            <?php echo htmlspecialchars($errors['nom']); ?>
        </div>
    <?php endif; ?>
</div>
```

### Erreurs Multiples

```php
<?php if (!empty($errors) && is_array($errors)): ?>
    <div class="alert alert-error">
        <strong>Erreurs :</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
```

## Validation Côté Client (JavaScript)

Pour améliorer l'UX, une validation JavaScript est fournie :

```javascript
document.getElementById('form').addEventListener('submit', function(e) {
    const errors = [];
    
    const nom = document.getElementById('nom').value.trim();
    if (!nom) {
        errors.push('Le nom est obligatoire');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Erreurs:\n\n' + errors.join('\n'));
    }
});
```

**Important** : Cette validation client ne suffit pas ! La validation serveur est obligatoire.

## Cas d'Usage Réels

### Créer une Ressource

```php
// Dans le contrôleur
public function store($data) {
    $validator = new Validator($data);
    
    $validator->required('id_sponsor', 'Sponsor')
             ->required('nom_ressource', 'Nom')
             ->minLength('nom_ressource', 3)
             ->maxLength('nom_ressource', 150)
             ->required('quantite_disponible', 'Quantité')
             ->isNumeric('quantite_disponible')
             ->isPositive('quantite_disponible');
    
    if ($validator->hasErrors()) {
        $this->errors = $validator->getErrors();
        return false;
    }
    
    // Nettoyer
    $cleanData = [
        'id_sponsor' => intval($data['id_sponsor']),
        'nom_ressource' => Validator::sanitize($data['nom_ressource']),
        'description' => Validator::sanitize($data['description'] ?? ''),
        'type_ressource' => Validator::sanitize($data['type_ressource']),
        'quantite_disponible' => intval($data['quantite_disponible'])
    ];
    
    // Créer
    return $this->ressource->create($cleanData);
}
```

### Créer une Demande

```php
public function store($data) {
    $validator = new Validator($data);
    
    $validator->required('id_utilisateur', 'Utilisateur')
             ->required('id_ressource', 'Ressource')
             ->required('quantite_demandee', 'Quantité')
             ->isNumeric('quantite_demandee')
             ->min('quantite_demandee', 1);
    
    if ($validator->hasErrors()) {
        $this->errors = $validator->getErrors();
        return false;
    }
    
    // Vérifications métier
    $ressource = $this->ressource->getById($data['id_ressource']);
    if (!$ressource) {
        $this->errors[] = "Ressource non trouvée";
        return false;
    }
    
    // Nettoyer et créer
    $cleanData = [
        'id_utilisateur' => intval($data['id_utilisateur']),
        'id_ressource' => intval($data['id_ressource']),
        'quantite_demandee' => intval($data['quantite_demandee']),
        'description_demande' => Validator::sanitize($data['description_demande'] ?? '')
    ];
    
    return $this->demande->create($cleanData);
}
```

## Erreurs Courantes à Éviter

### ❌ Ne pas valider les données

```php
// MAUVAIS
$model->create($_POST); // Dangereux!
```

### ❌ Oublier de nettoyer

```php
// MAUVAIS
$data = ['nom' => $_POST['nom']];
echo $data['nom']; // Peut afficher du HTML injecté
```

### ✅ Bonne pratique

```php
// BON
$validator = new Validator($_POST);
$validator->required('nom')->minLength('nom', 3);

if (!$validator->hasErrors()) {
    $clean = ['nom' => Validator::sanitize($_POST['nom'])];
    $model->create($clean);
}
```

## Règles de Validation Métier

En plus des validations standards, certaines validations métier sont impliquées :

### Demande d'Accès

1. **Ressource existe** : Vérifier que l'ID ressource existe
2. **Ressource disponible** : Vérifier que le statut est 'disponible'
3. **Quantité suffisante** : Quantité demandée ≤ disponible - utilisée
4. **Utilisateur existe** : Vérifier que l'ID utilisateur existe

### Ressource

1. **Sponsor existe** : Vérifier que l'ID sponsor existe
2. **Quantité logique** : Quantité disponible ≥ quantité utilisée

## Ressources

- [OWASP Input Validation](https://owasp.org/www-community/attacks/injection-attacks)
- [OWASP Output Encoding](https://owasp.org/www-community/attacks/xss/)
- [PHP Security](https://www.php.net/manual/en/security.php)

---

**Version** : 1.0.0
**Dernière mise à jour** : 2026
