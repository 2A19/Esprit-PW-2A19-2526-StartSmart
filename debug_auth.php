<?php
require_once 'config/Database.php';
require_once 'config/Auth.php';

$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT id, prenom, nom, role FROM users LIMIT 5");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Users:\n";
print_r($users);

echo "Admin roles array: \n";
var_dump(['admin', 'rh']);
echo "Normalized role for null: " . normalizedRole(null) . "\n";
echo "isAdmin() for null: " . (isAdmin() ? 'true' : 'false') . "\n";

$_SESSION['user_role'] = 'user';
echo "isAdmin() for 'user': " . (isAdmin() ? 'true' : 'false') . "\n";

$_SESSION['user_role'] = 'startup';
echo "isAdmin() for 'startup': " . (isAdmin() ? 'true' : 'false') . "\n";

$_SESSION['user_role'] = 'admin';
echo "isAdmin() for 'admin': " . (isAdmin() ? 'true' : 'false') . "\n";
