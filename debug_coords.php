<?php
require 'config/Database.php';
try {
    $db = (new Database())->getConnection();
    $stmt = $db->query('SELECT id, nomprojet, city, country, latitude, longitude FROM projet LIMIT 10');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
