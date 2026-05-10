<?php
session_start();
$_SESSION['user_id'] = 1;

require_once 'config/Database.php';
require_once 'models/ProjectMatcher.php';
require_once 'models/Skill.php';
require_once 'models/UserSkill.php';
require_once 'models/UserInterest.php';
require_once 'models/ProjectSkill.php';
require_once 'models/Categorie.php';
require_once 'models/Projet.php';
require_once 'controllers/MatchingController.php';

try {
    $matchingController = new MatchingController();
    $matchingController->recommend();
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
