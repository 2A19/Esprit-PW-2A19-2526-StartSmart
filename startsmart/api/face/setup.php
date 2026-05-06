<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/Database.php';

// Define constants BEFORE the switch that calls functions using them
define('PYTHON_CMD',     'C:\\Users\\moham\\AppData\\Local\\Programs\\Python\\Python311\\python.exe');
define('PYTHON_SERVICE', __DIR__ . '/../../python/face_recognition_service.py');
define('PYTHON_TIMEOUT', 20);

$action  = $_GET['action'] ?? '';
$db      = Database::getInstance()->getConnection();
$user_id = (int)$_SESSION['user_id'];

switch ($action) {
    case 'upload_face':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
        }
        uploadAndEncodeFace($db, $user_id);
        break;
    case 'enable':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
        }
        enableFaceRecognition($db, $user_id);
        break;
    case 'disable':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
        }
        disableFaceRecognition($db, $user_id);
        break;
    case 'remove':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
        }
        removeFaceData($db, $user_id);
        break;
    case 'status':
        getFaceRecognitionStatus($db, $user_id);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
}

function runPython(string $args): array {
    $cmd = '"' . PYTHON_CMD . '" "' . PYTHON_SERVICE . '" ' . $args;
    $descriptors = [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($process)) return ['success' => false, 'error' => 'Failed to start Python process'];

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $output = ''; $stderr = ''; $start = time();
    while (true) {
        $status  = proc_get_status($process);
        $output .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);
        if (!$status['running']) break;
        if ((time() - $start) >= PYTHON_TIMEOUT) {
            proc_terminate($process);
            fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
            return ['success' => false, 'error' => 'Face recognition timed out after ' . PYTHON_TIMEOUT . 's'];
        }
        usleep(100000);
    }
    fclose($pipes[1]); fclose($pipes[2]); proc_close($process);

    $output = trim($output);
    if (empty($output)) return ['success' => false, 'error' => 'No output from Python: ' . trim($stderr)];
    $j = strpos($output, '{');
    if ($j === false) return ['success' => false, 'error' => 'Bad Python output: ' . substr($output, 0, 200)];
    $result = json_decode(substr($output, $j), true);
    return is_array($result) ? $result : ['success' => false, 'error' => 'Invalid JSON from Python'];
}

function uploadAndEncodeFace($db, $user_id) {
    if (empty($_FILES['face_image']) || $_FILES['face_image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Error uploading file']);
        return;
    }
    $file = $_FILES['face_image'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg','image/png'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Only JPEG and PNG images allowed']);
        return;
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File too large (max 10MB)']);
        return;
    }
    $temp_dir = __DIR__ . '/../../public/img/temp/';
    if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);
    $temp_path = $temp_dir . 'face_' . $user_id . '_' . time() . '.jpg';
    if (!move_uploaded_file($file['tmp_name'], $temp_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
        return;
    }

    $result = runPython('encode ' . escapeshellarg($temp_path));
    @unlink($temp_path);

    if (!isset($result['success']) || !$result['success']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Face processing failed']);
        return;
    }
    $embedding = $result['embedding'] ?? null;
    if (!$embedding) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No face embedding returned']);
        return;
    }
    try {
        $stmt = $db->prepare("UPDATE users SET face_encoding = :enc, face_setup_date = NOW(), face_recognition_enabled = FALSE WHERE id = :uid");
        $stmt->execute([':enc' => json_encode($embedding), ':uid' => $user_id]);
        echo json_encode(['success' => true, 'message' => 'Visage enregistré avec succès.', 'setup_complete' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function enableFaceRecognition($db, $user_id) {
    try {
        $stmt = $db->prepare("SELECT face_encoding FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $user = $stmt->fetch();
        if (!$user || empty($user['face_encoding'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Configurez votre visage d\'abord']);
            return;
        }
        $db->prepare("UPDATE users SET face_recognition_enabled = TRUE WHERE id = :uid")->execute([':uid' => $user_id]);
        echo json_encode(['success' => true, 'message' => 'Reconnaissance faciale activée', 'enabled' => true]);
    } catch (PDOException $e) {
        http_response_code(500); echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

function disableFaceRecognition($db, $user_id) {
    try {
        $db->prepare("UPDATE users SET face_recognition_enabled = FALSE WHERE id = :uid")->execute([':uid' => $user_id]);
        echo json_encode(['success' => true, 'message' => 'Reconnaissance faciale désactivée', 'enabled' => false]);
    } catch (PDOException $e) {
        http_response_code(500); echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

// Bug fix: actually wipe face_encoding from DB, not just disable
function removeFaceData($db, $user_id) {
    try {
        $db->prepare("UPDATE users SET face_encoding = NULL, face_setup_date = NULL, face_recognition_enabled = FALSE WHERE id = :uid")
           ->execute([':uid' => $user_id]);
        echo json_encode(['success' => true, 'message' => 'Données faciales supprimées']);
    } catch (PDOException $e) {
        http_response_code(500); echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}

function getFaceRecognitionStatus($db, $user_id) {
    try {
        $stmt = $db->prepare("SELECT face_encoding, face_recognition_enabled, face_setup_date FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $user = $stmt->fetch();
        echo json_encode([
            'success'        => true,
            'enabled'        => (bool)$user['face_recognition_enabled'],
            'setup_complete' => !empty($user['face_encoding']),
            'setup_date'     => $user['face_setup_date']
        ]);
    } catch (PDOException $e) {
        http_response_code(500); echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}
?>
