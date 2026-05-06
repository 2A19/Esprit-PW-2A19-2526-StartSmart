# Configuration Twilio SMS pour StartSmart

## Étapes de configuration

### 1. Installer Composer (si pas déjà installé)
Téléchargez Composer depuis https://getcomposer.org/download/

### 2. Installer les dépendances Twilio
Naviguez jusqu'au répertoire du projet et exécutez :
```bash
composer install
```

Cela créera un dossier `vendor/` contenant le SDK Twilio.

### 3. Configurer vos credentials Twilio
Ouvrez `config.php` et remplacez les valeurs par défaut :

```php
define('TWILIO_SID', 'YOUR_TWILIO_SID');
define('TWILIO_AUTH_TOKEN', 'YOUR_TWILIO_AUTH_TOKEN');
define('TWILIO_PHONE_NUMBER', 'YOUR_TWILIO_PHONE_NUMBER');
```

Exemple avec données réelles :
```php
define('TWILIO_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_AUTH_TOKEN', 'auth_token_xxxxxxxxxxxxxxxx');
define('TWILIO_PHONE_NUMBER', '+33123456789');
```

### 4. Assurer que les sponsors ont des numéros de téléphone
- Les numéros doivent être au format international (ex: +33612345678)
- Les numéros sont stockés dans la table `sponsors` colonne `telephone`

## Fonctionnement

Quand un utilisateur soumet une demande d'accès :
1. La demande est créée dans la base de données
2. Un SMS est automatiquement envoyé au sponsor de la ressource
3. Le message contient :
   - Le nom de l'utilisateur qui demande
   - Le nom de la ressource demandée
   - Un rappel de se connecter au backoffice

## Format du message SMS
```
Un utilisateur a soumis une demande d'accès.

Utilisateur: [Nom utilisateur]
Ressource: [Nom ressource]
Connectez-vous au backoffice pour examiner la demande.
```

## Dépannage

### Erreur: "Composer dependencies non installées"
- Exécutez `composer install` dans le répertoire du projet

### SMS non reçus
1. Vérifiez que les credentials Twilio sont corrects
2. Vérifiez que le numéro du sponsor est au format international (+33...)
3. Vérifiez que le numéro Twilio dans `config.php` est correct
4. Vérifiez les logs d'erreur (fichier `error_log` du serveur)

### Tester l'intégration
Vous pouvez tester en soumettant une demande d'accès via le formulaire frontoffice.

## Structure du code

- **Service SMS**: `service/TwilioSMSService.php`
- **Controller mis à jour**: `controller/DemandeAccesController.php`
- **Configuration**: `config.php`
- **Composer config**: `composer.json`
