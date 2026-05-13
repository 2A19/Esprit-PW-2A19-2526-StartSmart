<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config/Database.php';
require 'models/ProjectMatcher.php';
$db = (new Database())->getConnection();
$matcher = new ProjectMatcher($db);
try {
    $res = $matcher->getInterestedUsers(1);
    print_r($res);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage();
}
