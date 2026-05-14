<?php
require 'config/session.php';
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'user';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['controller'] = 'post';
$_GET['action'] = 'show';
$_GET['id'] = 7;
$_GET['format'] = 'json';
ob_start();
require 'index.php';
$output = ob_get_clean();
echo "Output: \n" . $output;
