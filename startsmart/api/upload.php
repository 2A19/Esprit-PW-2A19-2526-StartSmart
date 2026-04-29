<?php
/**
 * api/upload.php – Handles profile picture uploads
 * Accepts multipart/form-data with file upload
 * Saves images to public/img/profiles/ directory
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Authentication check
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'upload_profile_picture' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    uploadProfilePicture();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action invalide']);
}

function uploadProfilePicture() {
    // Validate file upload
    if (empty($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Erreur lors du téléchargement du fichier']);
        return;
    }

    $file = $_FILES['profile_picture'];
    $user_id = (int)$_SESSION['user_id'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_types)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Type de fichier non autorisé. PNG, JPG, GIF ou WebP acceptés.']);
        return;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Le fichier est trop volumineux (max 5MB)']);
        return;
    }

    // Create profiles directory if it doesn't exist
    $upload_dir = __DIR__ . '/../public/img/profiles';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . '/' . $filename;
    $relative_path = '/startsmart/public/img/profiles/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Impossible de sauvegarder le fichier']);
        return;
    }

    // Update database
    require_once __DIR__ . '/../config/Database.php';
    try {
        $db = Database::getInstance()->getConnection();
        
        // Delete old profile picture if exists
        $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $old_pic = $stmt->fetchColumn();
        
        if ($old_pic) {
            $old_path = __DIR__ . '/..' . $old_pic;
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        // Update database with new profile picture path
        $stmt = $db->prepare("UPDATE users SET profile_picture = :pic WHERE id = :id");
        $stmt->execute([':pic' => $relative_path, ':id' => $user_id]);

        // Update session
        $_SESSION['user_photo'] = $relative_path;

        echo json_encode([
            'success' => true,
            'message' => 'Photo de profil mise à jour avec succès',
            'photo_url' => $relative_path
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur base de données: ' . $e->getMessage()]);
    }
}
?>
