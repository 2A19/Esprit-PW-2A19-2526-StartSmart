# Architecture MVC - StartSmart

## Vue d'ensemble

StartSmart suit strictement le pattern **Model-View-Controller (MVC)** avec les principes de **Programmation Orientée Objet (POO)**.

```
USER REQUEST
     ↓
  ROUTER (index.php)
     ↓
  CONTROLLER (RessourceController.php, DemandeAccesController.php)
     ↓
  MODEL (Ressource.php, DemandeAcces.php, etc.)
     ↓
  DATABASE (startsmart.sql avec PDO)
     ↓
  MODEL (Retourne les données)
     ↓
  CONTROLLER (Traite, valide)
     ↓
  VIEW (Template PHP)
     ↓
  USER RESPONSE (HTML)
```

## 1. Model - Couche Données

### Localisation
`model/` - Contient toutes les classes de modèles

### Fichiers
- `Ressource.php` - Gère les ressources
- `Sponsor.php` - Gère les sponsors
- `Utilisateur.php` - Gère les utilisateurs
- `DemandeAcces.php` - Gère les demandes d'accès

### Architecture Classe Modèle

```php
class Ressource {
    private $db;
    private $table = 'ressources';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // CRUD Operations
    public function create($data) { }      // INSERT
    public function getAll($statut = null) { }  // SELECT *
    public function getById($id) { }       // SELECT WHERE ID
    public function update($id, $data) { } // UPDATE
    public function delete($id) { }        // DELETE
    
    // Requêtes spécialisées
    public function getAvailable() { }
    public function getBySponsor($id) { }
}
```

### Principes POO Appliqués

1. **Encapsulation** : Variables privées (`$db`, `$table`)
2. **Abstraction** : Méthodes publiques pour l'interface
3. **Héritage** : Possible (non utilisé actuellement)
4. **Polymorphisme** : Même interface pour différentes entités

### Utilisation en Contrôleur

```php
// Instancier le modèle
$ressource = new Ressource();

// Récupérer des données
$ressources = $ressource->getAll();
$ressource = $ressource->getById(1);

// Créer
$id = $ressource->create([
    'nom_ressource' => 'Audit',
    'type_ressource' => 'Services'
]);

// Mettre à jour
$ressource->update(1, ['nom_ressource' => 'Audit Complet']);

// Supprimer
$ressource->delete(1);
```

## 2. View - Couche Présentation

### Localisation
`view/` - Contient tous les templates HTML/PHP

### Structure

```
view/
├── layout.php                # Template principal (header, footer, CSS)
├── frontoffice/              # Interface utilisateurs
│   ├── ressources-list.php   # Lister ressources
│   ├── demande-create.php    # Créer demande
│   └── demandes-list.php     # Voir mes demandes
└── backoffice/               # Interface admin/sponsors
    ├── ressource-list.php    # Lister ressources (admin)
    ├── ressource-create.php  # Créer ressource
    ├── ressource-edit.php    # Modifier ressource
    ├── demande-list.php      # Gérer demandes
    └── demande-refuser.php   # Refuser demande
```

### Template Principal (`layout.php`)

```php
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header><!-- Navigation --></header>
    <main class="container">
        <!-- Inclure les alertes -->
        <!-- Le contenu de la page spécifique -->
    </main>
    <footer></footer>
</body>
</html>
```

### Variables Disponibles dans les Vues

```php
// De index.php
$title              // Titre de la page
$ressources         // Array de ressources
$demandes           // Array de demandes
$ressource          // Une ressource unique
$demande            // Une demande unique
$errors             // Erreurs de validation
$success            // Messages de succès
```

### Exemple de Vue

```php
<?php
// view/frontoffice/ressources-list.php
?>

<div class="page-header">
    <h1><?php echo $title; ?></h1>
</div>

<?php if (!empty($ressources)): ?>
    <div class="row">
        <?php foreach ($ressources as $ressource): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($ressource['nom_ressource']); ?></h3>
                <!-- Afficher les détails -->
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-data">Aucun résultat</div>
<?php endif; ?>
```

### Sécurité dans les Vues

**Toujours** utiliser `htmlspecialchars()` :

```php
<!-- ❌ MAUVAIS - XSS vulnérable -->
<h1><?php echo $data['titre']; ?></h1>

<!-- ✅ BON - Sécurisé -->
<h1><?php echo htmlspecialchars($data['titre']); ?></h1>
```

## 3. Controller - Couche Métier

### Localisation
`controller/` - Contient les contrôleurs

### Fichiers
- `RessourceController.php` - Gère la logique des ressources
- `DemandeAccesController.php` - Gère la logique des demandes

### Architecture Classe Contrôleur

```php
class RessourceController {
    private $ressource;        // Modèle
    private $validator;        // Validateur
    private $errors = [];      // Erreurs
    private $success = [];     // Succès
    
    public function __construct() {
        $this->ressource = new Ressource();
        $this->validator = new Validator();
    }
    
    // Actions CRUD
    public function index() { }           // Lister
    public function create() { }          // Afficher formulaire
    public function store($data) { }      // Sauvegarder
    public function show($id) { }         // Détails
    public function edit($id) { }         // Afficher formulaire d'édition
    public function update($id, $data) {} // Mettre à jour
    public function delete($id) { }       // Supprimer
    
    // Getters
    public function getErrors() { }
    public function getSuccess() { }
}
```

### Flux d'Action (store)

```php
public function store($data) {
    // 1. Valider les données
    $validator = new Validator($data);
    $validator->required('nom')
             ->minLength('nom', 3);
    
    if ($validator->hasErrors()) {
        $this->errors = $validator->getErrors();
        return false;  // Arrêter ici
    }
    
    // 2. Nettoyer les données
    $cleanData = [
        'nom' => Validator::sanitize($data['nom']),
        'email' => Validator::sanitize($data['email'])
    ];
    
    // 3. Appeler le modèle
    if ($this->ressource->create($cleanData)) {
        $this->success[] = "Créé avec succès!";
        return true;
    } else {
        $this->errors[] = "Erreur lors de la création";
        return false;
    }
}
```

### Gestion des Erreurs

```php
public function store($data) {
    // Valider
    if (!$this->validate($data)) {
        return false;
    }
    
    // Vérifications métier
    if (!$this->checkBusinessRules($data)) {
        $this->errors[] = "Erreur métier";
        return false;
    }
    
    // Créer
    $result = $this->model->create($data);
    
    if ($result) {
        $this->success[] = "Succès!";
    } else {
        $this->errors[] = "Erreur DB";
    }
    
    return (bool)$result;
}
```

## 4. Router - Point d'Entrée (`index.php`)

### Architecture

```php
// 1. Démarrer la session
session_start();

// 2. Charger les contrôleurs
require_once 'controller/RessourceController.php';
require_once 'controller/DemandeAccesController.php';

// 3. Instancier les contrôleurs
$ressourceController = new RessourceController();
$demandeController = new DemandeAccesController();

// 4. Récupérer la route
$page = $_GET['page'] ?? 'accueil';

// 5. Router
switch ($page) {
    case 'ressources':
        // Action
        break;
    case 'ressource-create':
        // Action
        break;
    // ...
}
```

### Cycle de Requête

```
HTTP REQUEST
    ↓
switch ($page)
    ↓
case 'resource-store':
    ↓
if ($_SERVER['REQUEST_METHOD'] === 'POST')
    ↓
$controller->store($_POST)
    ↓
if (success)
    ↓
header('Location: ...')
    ↓
else
    ↓
include view.php (avec $errors)
```

## 5. Base de Données (`startsmart.sql`)

### Connexion PDO (`config/Database.php`)

```php
class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        $dsn = "mysql:host=localhost;dbname=startsmart;charset=utf8mb4";
        $this->pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
```

### Utilisation PDO

```php
// Préparer la requête
$sql = "SELECT * FROM ressources WHERE id_ressource = :id";
$stmt = $db->prepare($sql);

// Exécuter avec paramètres
$stmt->execute([':id' => 1]);

// Récupérer les résultats
$ressource = $stmt->fetch();  // Un enregistrement
$ressources = $stmt->fetchAll(); // Tous
```

## 6. Validation (`config/Validator.php`)

### Classe Validator

```php
class Validator {
    private $errors = [];
    private $data = [];
    
    public function required($field) { }
    public function minLength($field, $min) { }
    public function isNumeric($field) { }
    
    public function getErrors() { }
    public function hasErrors() { }
    
    public static function sanitize($data) { }
    public static function escape($data) { }
}
```

### Utilisation

```php
$validator = new Validator($_POST);
$validator->required('nom')
         ->minLength('nom', 3);

if ($validator->hasErrors()) {
    $errors = $validator->getErrors();
}
```

## Flux Complet d'une Créatio de Ressource

```
1. USER (Frontend)
   └─ Remplit le formulaire
   └─ Clique sur "Créer"
   └─ POST vers index.php?page=ressource-store

2. ROUTER (index.php)
   └─ Récupère $page = 'ressource-store'
   └─ Crée $ressourceController
   └─ Appelle $resourceController->store($_POST)

3. CONTROLLER (RessourceController.php)
   └─ Reçoit les données $_POST
   └─ Valide avec Validator
   └─ Nettoie les données avec sanitize()
   └─ Appelle $ressource->create($cleanData)

4. MODEL (Ressource.php)
   └─ Prépare la requête SQL
   └─ Exécute via PDO
   └─ Retourne l'ID ou false

5. CONTROLLER
   └─ Récupère le résultat
   └─ Si succès : $_SESSION['success'][] = "..."
   └─ Si erreur : $this->errors[] = "..."

6. ROUTER
   └─ Si succès : header('Location: ...')
   └─ Si erreur : include view with $errors

7. VIEW (ressource-create.php)
   └─ Affiche les erreurs
   └─ Affiche le formulaire
   └─ Remplit les champs avec $_POST

8. USER
   └─ Voit les erreurs OU est redirigé
   └─ Corrige et réessaie
```

## Diagramme UML Simplifié

```
┌─────────────┐
│   Ressource │ (Model)
└──────┬──────┘
       │ create()
       │ update()
       │ getAll()
       │ delete()
       │
┌──────▼──────────────────┐
│ RessourceController     │ (Controller)
└──────┬──────────────────┘
       │ store()
       │ update()
       │ delete()
       │
┌──────▼──────────────────┐
│ index.php (Router)      │ (Router)
└──────┬──────────────────┘
       │ route les requêtes
       │
┌──────▼──────────────────┐
│ ressource-list.php      │ (View)
└──────────────────────────┘
       │ Affiche les données
       │
┌──────▼──────────────────┐
│ HTML Response           │
└──────────────────────────┘
```

## Principes POO Appliqués

### 1. Encapsulation

```php
class Ressource {
    private $db;      // Private - accessible seulement dans la classe
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
}
```

### 2. Abstraction

```php
class RessourceController {
    // Interface publique - les détails sont cachés
    public function store($data) { }
    
    // Détails internes
    private function validate($data) { }
    private function sanitize($data) { }
}
```

### 3. Héritage

Possible mais non utilisé actuellement. Exemple :

```php
class BaseModel {
    protected $db;
    
    public function getAll() { }
    public function getById($id) { }
}

class Ressource extends BaseModel {
    // Hérite getAll() et getById()
}
```

### 4. Polymorphisme

```php
class RessourceController {
    public function show($id) { }  // Affiche détails
}

class DemandeAccesController {
    public function show($id) { }  // Affiche autres détails
}
```

## Avantages de cette Architecture

✅ **Séparation des préoccupations** - Chaque couche a une responsabilité
✅ **Maintenabilité** - Facile à modifier, tester, déboguer
✅ **Réutilisabilité** - Les modèles peuvent être utilisés par plusieurs contrôleurs
✅ **Testabilité** - Chaque couche peut être testée indépendamment
✅ **Scalabilité** - Facile d'ajouter de nouvelles entités

---

**Version** : 1.0.0
**Dernière mise à jour** : 2026
