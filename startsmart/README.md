# StartSmart – Guide d'installation XAMPP

## Architecture MVC
```
startsmart/
├── config/
│   └── Database.php          ← Connexion PDO Singleton
├── models/
│   ├── User.php              ← Modèle User (POO + PDO)
│   ├── Startup.php           ← Modèle Startup (POO + PDO)
│   └── Validator.php         ← Contrôles de saisie PHP (sans HTML5)
├── controllers/
│   ├── UserController.php    ← CRUD Users (retourne JSON)
│   └── AuthController.php    ← Login / Register / Logout
├── api/
│   ├── users.php             ← Endpoint REST appelé par fetch() JS
│   └── auth.php              ← Endpoint REST auth
├── views/
│   ├── auth/
│   │   └── login.php         ← FrontOffice : Login + Inscription
│   ├── front/
│   │   └── dashboard.php     ← Espace utilisateur connecté
│   └── back/
│       └── dashboard.php     ← BackOffice Admin CRUD Users
├── public/
│   └── css/style.css
├── startsmart_db.sql
└── generate_hashes.php       ← À supprimer après usage
```

## Principes respectés
- **MVC** : Models (accès BDD), Controllers (logique métier), Views (HTML/JS)
- **POO** : Classes User, Startup, Validator, Database (Singleton)
- **PDO uniquement** : toutes les requêtes utilisent PDO avec requêtes préparées
- **Pas de HTML5** : validation uniquement par JS (côté client) + PHP/Validator (côté serveur)
- **CRUD JS** : toutes les opérations CRUD passent par `fetch()` vers l'API JSON

## Installation XAMPP

### 1. Copier le projet
```
C:/xampp/htdocs/startsmart/
```

### 2. Importer la base de données
1. Démarrer Apache + MySQL dans XAMPP Control Panel
2. Ouvrir http://localhost/phpmyadmin
3. Onglet **Importer** → sélectionner `startsmart_db.sql`
4. Cliquer **Exécuter**

### 3. Générer les hash bcrypt
Ouvrir : http://localhost/startsmart/generate_hashes.php
→ Puis supprimer ce fichier !

### 4. Accéder au projet
- **Page de connexion** : http://localhost/startsmart/views/auth/login.php
- **BackOffice Admin** : http://localhost/startsmart/views/back/dashboard.php

## Comptes de test (après generate_hashes.php)
| Type     | Email                      | Mot de passe |
|----------|----------------------------|--------------|
| Admin    | admin@startsmart.com       | Admin1234!   |
| User     | ahmed@email.com            | Test1234!    |
| Startup  | contact@techtunisia.tn     | Test1234!    |

## Flux CRUD (BackOffice)
```
JS fetch()  →  api/users.php  →  UserController  →  User Model  →  PDO  →  MySQL
```
Chaque action (list, get, create, update, delete) :
1. fetch() envoie la requête JSON depuis le navigateur
2. api/users.php route vers la méthode du controller
3. UserController valide avec Validator.php (PHP pur)
4. User model exécute la requête PDO préparée
5. Réponse JSON retournée au JS pour mise à jour de l'interface
