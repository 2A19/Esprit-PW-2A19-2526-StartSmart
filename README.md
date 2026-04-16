# StartSmart - Gestion des Ressources et Sponsors

## Description

Application web de gestion des ressources et sponsors pour startups. Permet aux sponsors de proposer des ressources (services, formations, infrastructure, etc.) et aux start-upers de demander l'accès à ces ressources.

## Architecture

- **Pattern MVC** : Model-View-Controller
- **POO** : Programmation Orientée Objet
- **PDO** : Connexion à la base de données
- **Validation côté serveur** : Pas de validation HTML5

```
startsmart/
├── config/
│   ├── Database.php         # Classe PDO Singleton
│   └── Validator.php        # Classe de validation des données
├── model/
│   ├── Ressource.php        # Modèle des ressources
│   ├── Sponsor.php          # Modèle des sponsors
│   ├── Utilisateur.php      # Modèle des utilisateurs
│   └── DemandeAcces.php     # Modèle des demandes d'accès
├── controller/
│   ├── RessourceController.php      # Contrôleur des ressources
│   └── DemandeAccesController.php   # Contrôleur des demandes
├── view/
│   ├── layout.php           # Template principal
│   ├── frontoffice/
│   │   ├── ressources-list.php      # Liste des ressources
│   │   ├── demande-create.php       # Formulaire de demande
│   │   └── demandes-list.php        # Liste des demandes utilisateur
│   └── backoffice/
│       ├── ressource-list.php       # Gestion ressources
│       ├── ressource-create.php     # Créer ressource
│       ├── ressource-edit.php       # Modifier ressource
│       ├── demande-list.php         # Gestion demandes
│       └── demande-refuser.php      # Refuser une demande
├── index.php                # Point d'entrée (routeur)
└── startsmart.sql          # Script base de données
```

## Installation

### 1. Créer la base de données

Importez le fichier `startsmart.sql` dans phpMyAdmin ou MySQL :

```bash
mysql -u root < startsmart.sql
```

Ou via phpMyAdmin :
- Aller à "Importer"
- Sélectionner le fichier `startsmart.sql`
- Cliquer sur "Exécuter"

### 2. Configuration

Modifier les paramètres de connexion dans `config/Database.php` si nécessaire :

```php
private $host = 'localhost';
private $db_name = 'startsmart';
private $user = 'root';
private $pass = '';
```

### 3. Accéder à l'application

```
http://localhost/startsmart
```

## Fonctionnalités

### FrontOffice (Pour les Start-Upers)

#### 1. **Voir les Ressources Disponibles**
- Liste toutes les ressources proposées par les sponsors
- Affiche : nom, sponsor, type, description, disponibilité
- Statut de disponibilité en temps réel

#### 2. **Déposer une Demande d'Accès**
- Formulaire avec validation côté serveur
- Champs : nom, ressource, quantité, durée, description
- Vérification de la disponibilité
- Notification de création

#### 3. **Suivi des Demandes**
- Vue d'ensemble des demandes soumises
- Affichage du statut (en attente, acceptée, refusée)
- Accès aux détails

### BackOffice (Pour les Sponsors)

#### 1. **Gestion des Ressources (CRUD)**
- **Créer** : Ajouter une nouvelle ressource
- **Lire** : Consulter la liste avec filtres
- **Modifier** : Éditer nom, type, quantité, statut
- **Supprimer** : Archiver ou supprimer

Validation des champs :
- Nom : obligatoire, 3-150 caractères
- Type : obligatoire
- Quantité : obligatoire, nombre positif

#### 2. **Gestion des Demandes d'Accès**
- Voir toutes les demandes par statut
- **Accepter** une demande (avec date fin d'accès)
- **Refuser** une demande (avec motif)
- Consulter les détails
- Historique complet

#### 3. **Tableau de Bord**
- Statistiques clés
- Demandes en attente
- Ressources actives

## Contrôle de Saisie

Tous les contrôles sont effectués **côté serveur** via la classe `Validator` :

### Validations Disponibles

```php
$validator = new Validator($data);

// Email
$validator->validateEmail('email', $required = true);

// Chaînes de caractères
$validator->required('champ', 'Libellé');
$validator->minLength('champ', 3, 'Libellé');
$validator->maxLength('champ', 150, 'Libellé');

// Nombres
$validator->isNumeric('champ', 'Libellé');
$validator->isPositive('champ', 'Libellé');
$validator->min('champ', 1, 'Libellé');

// Téléphone
$validator->validatePhone('tel', $required = false);

// Correspondance
$validator->match('champ1', 'champ2', 'Libellé');

// Vérifier les erreurs
if ($validator->hasErrors()) {
    $errors = $validator->getErrors();
}
```

### Nettoyage des Données

```php
// Échappe les caractères spéciaux
$safe = Validator::sanitize($data);

// Échappe pour affichage
$safe = Validator::escape($data);
```

## Architecture de la Base de Données

### Tables

#### `sponsors`
- Fournisseurs de ressources
- Champs : id, nom, email, téléphone, description, type_ressources, date_inscription, statut

#### `ressources`
- Ressources disponibles
- Champs : id, id_sponsor, nom, description, type, quantite_disponible, quantite_utilisee, statut, dates

#### `utilisateurs`
- Start-upers
- Champs : id, nom, email, téléphone, entreprise, domaine_activite, date_inscription, statut

#### `demandes_acces`
- Demandes d'accès aux ressources
- Champs : id, id_utilisateur, id_ressource, quantite_demandee, description, statut_demande, raison_refus, dates, duree_acces_jours

#### `audit_log`
- Journal des actions pour traçabilité

## Exemple d'Utilisation

### Créer une Ressource

```php
$controller = new RessourceController();
$data = [
    'id_sponsor' => 1,
    'nom_ressource' => 'Audit Financier',
    'type_ressource' => 'Services',
    'quantite_disponible' => 5,
    'description' => 'Audit complet des finances'
];
$controller->store($data);
```

### Créer une Demande d'Accès

```php
$controller = new DemandeAccesController();
$data = [
    'id_utilisateur' => 1,
    'id_ressource' => 1,
    'quantite_demandee' => 1,
    'duree_acces_jours' => 30,
    'description_demande' => 'Besoin audit pour levée de fonds'
];
$controller->store($data);
```

### Accepter une Demande

```php
$controller = new DemandeAccesController();
$controller->accepter($id_demande);
```

## Palette de Couleurs

```
Bleu Foncé (Primary Dark)    : #1d2f5a
Bleu (Primary Blue)           : #2c4a8d
Vert Clair (Accent Green)    : #7dd442
Vert Clair+ (Light Green)    : #a8e063
Blanc                         : #ffffff
Gris                          : #f5f5f5
```

## Sécurité

- **Préparation des requêtes** : PDO with prepared statements
- **Validation** : Validation côté serveur obligatoire
- **Échappement** : htmlspecialchars() pour affichage
- **Accès direct refusé** : Vérification des données

## Statuts

### Ressources
- `disponible` : Accessible aux demandes
- `indisponible` : Non disponible actuellement
- `archive` : Archivée

### Demandes d'Accès
- `en_attente` : En cours de traitement
- `acceptee` : Approuvée
- `refusee` : Rejetée avec motif
- `archivee` : Archivée

## Erreurs Courantes

### Erreur de connexion à la base de données
- Vérifier les paramètres dans `config/Database.php`
- S'assurer que MySQL est lancé
- Vérifier les droits d'accès

### Erreur de validation
- Les messages d'erreur s'affichent sous les champs
- Les validations sont côté serveur
- Consulter les logs pour plus de détails

## Support

Pour toute question ou problème, consultez les logs ou contactez l'équipe de développement.

## Licence

Propriétaire StartSmart - Tous droits réservés
