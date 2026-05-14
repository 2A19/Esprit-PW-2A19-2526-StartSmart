<?php
require_once __DIR__ . '/../config/Database.php';

$pdo = Database::getInstance()->getConnection();
$password = password_hash('Password123!', PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    "INSERT INTO users
        (nom, prenom, full_name, email, password, role, statut, nom_startup, company_name, nom_responsable, prenom_responsable, secteur)
     VALUES
        (:nom, :prenom, :full_name, :email, :password, :role, 'actif', :nom_startup, :company_name, :nom_responsable, :prenom_responsable, :secteur)
     ON DUPLICATE KEY UPDATE
        password = VALUES(password),
        role = VALUES(role),
        statut = 'actif',
        full_name = VALUES(full_name),
        nom_startup = VALUES(nom_startup),
        company_name = VALUES(company_name),
        nom_responsable = VALUES(nom_responsable),
        prenom_responsable = VALUES(prenom_responsable),
        secteur = VALUES(secteur)"
);

$accounts = [
    ['nom' => 'Admin', 'prenom' => 'Test', 'full_name' => 'Admin Test', 'email' => 'admin@startsmart.test', 'role' => 'admin', 'startup' => null, 'secteur' => null],
    ['nom' => 'User', 'prenom' => 'Test', 'full_name' => 'User Test', 'email' => 'user@startsmart.test', 'role' => 'user', 'startup' => null, 'secteur' => null],
    ['nom' => 'Startup', 'prenom' => 'Test', 'full_name' => 'Startup Test', 'email' => 'startup@startsmart.test', 'role' => 'startup', 'startup' => 'Test Startup', 'secteur' => 'Innovation'],
];

foreach ($accounts as $account) {
    $stmt->execute([
        ':nom' => $account['nom'],
        ':prenom' => $account['prenom'],
        ':full_name' => $account['full_name'],
        ':email' => $account['email'],
        ':password' => $password,
        ':role' => $account['role'],
        ':nom_startup' => $account['startup'],
        ':company_name' => $account['startup'],
        ':nom_responsable' => $account['nom'],
        ':prenom_responsable' => $account['prenom'],
        ':secteur' => $account['secteur'],
    ]);
}

$pdo->exec(
    "INSERT IGNORE INTO categorie (id, num, typeprojet, titre, nom_investisseur) VALUES
     (1, 1, 'AI', 'AI', 'Seed'),
     (2, 2, 'Fintech', 'Fintech', 'Seed'),
     (3, 3, 'Gaming', 'Gaming', 'Seed'),
     (4, 4, 'HealthTech', 'HealthTech', 'Seed'),
     (5, 5, 'General', 'General', 'Seed')"
);

echo "Test accounts and categories are ready.\n";
