<?php
require 'config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query('SELECT id_post FROM post LIMIT 1');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
