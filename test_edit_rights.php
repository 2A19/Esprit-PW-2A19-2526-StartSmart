<?php
require_once 'config/Auth.php';
require_once 'models/Post.php';

$_SESSION['user_id'] = 1; // User Ahmed
$_SESSION['user_role'] = 'user';

$db = Database::getInstance()->getConnection();
$postModel = new Post($db);
$postModel->id_post = 7; // Authored by 2

if ($postModel->readOne()) {
    $can_edit = isLoggedIn() && (isAdmin() || (int) currentUserId() === (int) $postModel->auteur_id);
    echo "Can Edit Post 7? " . ($can_edit ? 'Yes' : 'No') . "\n";
    echo "Current user id: " . currentUserId() . "\n";
    echo "Post author id: " . $postModel->auteur_id . "\n";
} else {
    echo "Post not found\n";
}
