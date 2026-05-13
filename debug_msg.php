<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config/Database.php';
require 'models/Message.php';
$db = (new Database())->getConnection();
$messageModel = new Message($db);
try {
    $res = $messageModel->getConversations(1);
    print_r($res);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage();
}
