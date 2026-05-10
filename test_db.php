<?php
require 'config/Database.php';
require 'models/ProjectMatcher.php';
try {
    $db = (new Database())->getConnection();
    $matcher = new ProjectMatcher($db);
    $projects = $matcher->getMatchedProjects(1, 10, 0);
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
