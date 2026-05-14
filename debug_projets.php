<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Projet.php';

$database = new Database();
$db = $database->getConnection();
$projet = new Projet($db);

echo "User ID: " . currentUserId() . "<br>";
echo "User Role: " . currentUserRole() . "<br>";
echo "Is Admin: " . (isAdmin() ? 'Yes' : 'No') . "<br>";

$ownerId = isAdmin() ? null : currentUserId();
echo "Owner ID passed to readAll: " . var_export($ownerId, true) . "<br><br>";

$stmt = $projet->readAll("", $ownerId);
$projets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Projects visible to this user:<br>";
echo "<pre>";
print_r($projets);
echo "</pre>";
?>
