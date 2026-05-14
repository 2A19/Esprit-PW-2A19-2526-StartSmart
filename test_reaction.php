<?php
require 'config/session.php';
$_SESSION['user_id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_POST = [];
// Instead of reading php://input, let's override requestData in PostController or just use the controller directly.
require 'controllers/PostController.php';

// Create a child class to override requestData reading from php://input
class TestPostController extends PostController {
    public function requestData() {
        return ['id' => 1, 'reaction' => 'LIKE'];
    }
}

$controller = new TestPostController();
$controller->reaction();
