<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>Générer les hash</title>
<style>body{font-family:monospace;padding:2rem;background:#0d1b3e;color:#2ddc78;}pre{background:#162654;padding:1.5rem;border-radius:8px;line-height:2;}</style>
</head><body>
<h2>⚙️ Mise à jour des hash bcrypt</h2>
<?php
require_once __DIR__ . '/config/Database.php';

$passwords = [
    'users' => [
        ['email' => 'ahmed@email.com',        'password' => 'Test1234!'],
        ['email' => 'sonia@email.com',         'password' => 'Test1234!'],
        ['email' => 'karim@email.com',         'password' => 'Test1234!'],
        ['email' => 'admin@startsmart.com',    'password' => 'Admin1234!'],
    ],
    'startups' => [
        ['email' => 'contact@techtunisia.tn',  'password' => 'Test1234!'],
        ['email' => 'info@greenagri.tn',        'password' => 'Test1234!'],
        ['email' => 'hello@edubridge.tn',       'password' => 'Test1234!'],
    ],
];

try {
    $pdo = Database::getInstance()->getConnection();
    echo '<pre>';
    foreach ($passwords as $table => $records) {
        foreach ($records as $r) {
            $hash = password_hash($r['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE {$table} SET password = :hash WHERE email = :email");
            $stmt->execute([':hash' => $hash, ':email' => $r['email']]);
            echo "✅ [{$table}] {$r['email']} → hash mis à jour\n";
        }
    }
    echo "\n🎉 Tous les mots de passe ont été hashés avec bcrypt !\n";
    echo "\nComptes de test :\n";
    echo "  User    : ahmed@email.com     / Test1234!\n";
    echo "  Startup : contact@techtunisia.tn / Test1234!\n";
    echo "  Admin   : admin@startsmart.com / Admin1234!\n";
    echo '</pre>';
} catch (Exception $e) {
    echo '<p style="color:#e8445a">Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
<p style="color:rgba(255,255,255,.4);margin-top:1rem">⚠️ Supprimez ce fichier après utilisation.</p>
</body></html>
