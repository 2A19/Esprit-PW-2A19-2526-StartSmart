<?php
require 'config/session.php';
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'user';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['controller'] = 'post';
$_GET['action'] = 'show';
$_GET['id'] = 7;
$_GET['format'] = 'json';

require_once 'config/Database.php';
$db = (new Database())->getConnection();
$db->query("INSERT INTO commentaire (contenu, auteur_id, post_id) VALUES ('Test comment by admin', 2, 7)");

ob_start();
require 'index.php';
$output = ob_get_clean();
echo "Output: \n" . $output;
