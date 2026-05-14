<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once __DIR__ . '/controllers/rh/UserController.php';
require_once __DIR__ . '/models/rh/RHUser.php';

$controller = new UserController();
$user = new RHUser(
    null,
    'Test User',
    'test3@test.com',
    'password',
    'user',
    '', '', '', '', ''
);
$controller->addUser($user);
echo "User added successfully";
?>
