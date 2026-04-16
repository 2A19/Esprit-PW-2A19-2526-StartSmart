# Installation StartSmart

## Prérequis

- **Serveur Web** : Apache (avec XAMPP)
- **PHP** : Version 7.2 ou supérieure
- **MySQL** : Version 5.7 ou supérieure
- **Navigateur** : Chrome, Firefox, Edge (récent)

## Étapes d'Installation

### 1. Placer les fichiers

Les fichiers doivent être dans :
```
C:\xampp\htdocs\startsmart\
```

### 2. Créer la base de données

#### Option A : Avec phpMyAdmin (Interface graphique)

1. Ouvrir **phpMyAdmin** : `http://localhost/phpmyadmin`
2. Cliquer sur l'onglet **"Importer"**
3. Sélectionner le fichier **`startsmart.sql`**
4. Cliquer sur **"Exécuter"**

#### Option B : Avec MySQL en ligne de commande

1. Ouvrir un terminal
2. Naviguer vers le dossier StartSmart
3. Exécuter :
```bash
mysql -u root -p < startsmart.sql
```
4. Appuyer sur Entrée (pas de mot de passe par défaut dans XAMPP)

### 3. Vérifier la connexion

Accédez à :
```
http://localhost/startsmart/test-connexion.php
```

Vous devriez voir un message **"Connexion réussie"** avec les statistiques de la base de données.

### 4. Lancer l'application

Accédez à :
```
http://localhost/startsmart/
```

## Accès à l'Application

### Page d'Accueil
```
http://localhost/startsmart/
```

### FrontOffice (Start-Upers)
- **Ressources** : `http://localhost/startsmart/index.php?page=ressources`
- **Créer Demande** : `http://localhost/startsmart/index.php?page=demande-create`
- **Mes Demandes** : `http://localhost/startsmart/index.php?page=demandes`

### BackOffice (Sponsors/Admin)
- **Gestion Ressources** : `http://localhost/startsmart/index.php?page=ressource-list`
- **Créer Ressource** : `http://localhost/startsmart/index.php?page=ressource-create`
- **Gestion Demandes** : `http://localhost/startsmart/index.php?page=demande-list`

## Données de Test

### Sponsors (déjà créés)
1. **TechCorp Finance** - Services de consulting
2. **GreenInnovations** - Solutions durables
3. **NetworkHub** - Infrastructure IT

### Ressources (déjà créées)
- Audit Financier
- Consultation Business Plan
- Certification ISO 14001
- Infrastructure Cloud 3 mois
- Support Technique 24/7

### Utilisateurs (déjà créés)
- Marie Dubois (EcoTech)
- Pierre Martin (StartupAI)
- Sophie Laurent (FinTrack)

## Configuration Optionnelle

### Modifier les paramètres de connexion

Si votre MySQL a un mot de passe, éditer `config/Database.php` :

```php
private $host = 'localhost';      // Adresse du serveur
private $db_name = 'startsmart';  // Nom de la BD
private $user = 'root';            // Utilisateur MySQL
private $pass = '';                // Mot de passe MySQL
private $charset = 'utf8mb4';     // Encodage
```

## Dépannage

### ❌ Erreur : "Impossible de se connecter à la base de données"

**Solution** :
1. Vérifier que MySQL est lancé (XAMPP Control Panel)
2. Vérifier que la base de données est créée
3. Tester via `test-connexion.php`

### ❌ Erreur : "Base de données non trouvée"

**Solution** :
1. Importer le fichier `startsmart.sql` dans phpMyAdmin
2. Vérifier le nom de la base : `startsmart` (minuscules)

### ❌ Erreur : "Erreur 404 - Page non trouvée"

**Solution** :
1. Vérifier que les fichiers sont dans `C:\xampp\htdocs\startsmart\`
2. Vérifier que le serveur Apache est lancé
3. Accéder via `http://localhost/startsmart/` et non `C:\xampp\...`

### ❌ Erreur lors de la création d'une ressource

**Solution** :
1. Remplir tous les champs obligatoires
2. Vérifier les messages d'erreur affichés
3. Consulter les logs du serveur

## Structure des Fichiers

```
startsmart/
├── config/
│   ├── Database.php           # Connexion PDO
│   ├── Validator.php          # Validation des données
│   └── (config.php optionnel)
├── model/
│   ├── Ressource.php          # Modèle ressources
│   ├── Sponsor.php            # Modèle sponsors
│   ├── Utilisateur.php        # Modèle utilisateurs
│   └── DemandeAcces.php       # Modèle demandes
├── controller/
│   ├── RessourceController.php
│   └── DemandeAccesController.php
├── view/
│   ├── layout.php             # Template principal
│   ├── frontoffice/           # Pages utilisateurs
│   │   ├── ressources-list.php
│   │   ├── demande-create.php
│   │   └── demandes-list.php
│   └── backoffice/            # Pages admin
│       ├── ressource-list.php
│       ├── ressource-create.php
│       ├── ressource-edit.php
│       ├── demande-list.php
│       └── demande-refuser.php
├── index.php                  # Point d'entrée (routeur)
├── test-connexion.php         # Script de test
├── startsmart.sql             # Base de données
├── README.md                  # Documentation
└── INSTALLATION.md            # Ce fichier
```

## Fonctionnement du MVC

### Model (Modèles)
- Classes dans `model/`
- Gèrent la base de données avec PDO
- Exemple : `$ressource = new Ressource();`

### View (Vues)
- Templates HTML dans `view/`
- Affichent les données
- Contiennent les formulaires

### Controller (Contrôleurs)
- Classes dans `controller/`
- Traitent les requêtes
- Appliquent les validations
- Communiquent avec Model et View

## Routes Disponibles

| Route | Méthode | Description |
|-------|---------|-------------|
| `index.php` | GET | Accueil |
| `?page=ressources` | GET | Liste ressources |
| `?page=demande-create` | GET | Formulaire demande |
| `?page=demande-store` | POST | Créer demande |
| `?page=demandes` | GET | Mes demandes |
| `?page=ressource-list` | GET | Gérer ressources |
| `?page=ressource-create` | GET | Créer ressource |
| `?page=ressource-store` | POST | Sauvegarder ressource |
| `?page=ressource-edit` | GET | Éditer ressource |
| `?page=ressource-update` | POST | Mettre à jour |
| `?page=ressource-delete` | GET | Supprimer ressource |
| `?page=demande-list` | GET | Gérer demandes |
| `?page=demande-accepter` | GET | Accepter demande |
| `?page=demande-refuser` | GET | Formulaire refus |
| `?page=demande-refuser-store` | POST | Refuser demande |

## Support

En cas de problème :
1. Consulter le fichier `README.md`
2. Vérifier les logs du serveur
3. Tester la connexion BD : `test-connexion.php`
4. Vérifier que tous les fichiers sont en place

---

**Version** : 1.0.0  
**Dernière mise à jour** : 2026  
**Auteur** : StartSmart
