<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../../config/Database.php';

$db    = Database::getInstance()->getConnection();
$email = trim($_POST['email'] ?? '');
$role  = trim($_POST['role']  ?? '');

if (empty($email) || empty($role)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email and role required']);
    exit;
}

define('PYTHON_CMD',     'C:\\Users\\moham\\AppData\\Local\\Programs\\Python\\Python311\\python.exe');
define('PYTHON_SERVICE', __DIR__ . '/../../python/face_recognition_service.py');
define('PYTHON_TIMEOUT', 20);
define('FACE_THRESHOLD', 55.0); // chi-square distance — lower = stricter

function runPython(string $args): array {
    $cmd = '"' . PYTHON_CMD . '" "' . PYTHON_SERVICE . '" ' . $args;
    $descriptors = [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($process)) return ['success' => false, 'error' => 'Failed to start Python'];
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $output = ''; $start = time();
    while (true) {
        $status  = proc_get_status($process);
        $output .= stream_get_contents($pipes[1]);
        if (!$status['running']) break;
        if ((time() - $start) >= PYTHON_TIMEOUT) {
            proc_terminate($process);
            fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
            return ['success' => false, 'error' => 'Face recognition timed out'];
        }
        usleep(100000);
    }
    fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
    $output = trim($output);
    if (empty($output)) return ['success' => false, 'error' => 'No output from Python'];
    $j = strpos($output, '{');
    if ($j === false) return ['success' => false, 'error' => 'Bad Python output: ' . substr($output, 0, 200)];
    $result = json_decode(substr($output, $j), true);
    return is_array($result) ? $result : ['success' => false, 'error' => 'Invalid JSON from Python'];
}

/**
 * Chi-square distance — matches the Python service logic exactly
 * Returns 0 for identical histograms, higher = more different faces
 */
function chiSquareDistance(array $h1, array $h2): float {
    $n = min(count($h1), count($h2));
    $dist = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $sum = $h1[$i] + $h2[$i];
        if ($sum > 0) {
            $dist += (($h1[$i] - $h2[$i]) ** 2) / $sum;
        }
    }
    return $dist;
}

// Validate upload
if (empty($_FILES['face_image']) || $_FILES['face_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No face image provided']);
    exit;
}

$file  = $_FILES['face_image'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, ['image/jpeg','image/png'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Only JPEG and PNG allowed']);
    exit;
}

// Load account with stored face embedding
$stmt = $db->prepare("
    SELECT id, face_encoding, statut, prenom, nom, nom_startup
    FROM users
    WHERE email = :email AND role = :role AND face_recognition_enabled = TRUE
    LIMIT 1
");
$stmt->execute([':email' => $email, ':role' => $role]);
$account = $stmt->fetch();

if (!$account || empty($account['face_encoding'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Reconnaissance faciale non configurée pour ce compte']);
    exit;
}

// Save temp image
$temp_dir = __DIR__ . '/../../public/img/temp/';
if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);
$temp_path = $temp_dir . 'verify_' . $account['id'] . '_' . time() . '.jpg';
if (!move_uploaded_file($file['tmp_name'], $temp_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save image']);
    exit;
}

// Encode captured face
$result = runPython('encode ' . escapeshellarg($temp_path));
@unlink($temp_path);

if (!isset($result['success']) || !$result['success']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Visage non détecté']);
    exit;
}

$captured = $result['embedding'] ?? null;
if (!$captured) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No embedding returned']);
    exit;
}

$stored = json_decode($account['face_encoding'], true);
if (!is_array($stored) || count($stored) !== count($captured)) {
    // Embedding format changed (old pixel-based vs new LBPH) — force re-setup
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Format de visage obsolète. Veuillez reconfigurer votre visage.']);
    exit;
}

// Compare using chi-square distance (same as Python service)
$distance = chiSquareDistance($stored, $captured);
$is_match = $distance < FACE_THRESHOLD;

if ($is_match) {
    $db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :id")
       ->execute([':id' => $account['id']]);
    echo json_encode([
        'success' => true,
        'message' => 'Visage reconnu avec succès',
        'user'    => [
            'id'    => $account['id'],
            'email' => $email,
            'role'  => $role,
            'name'  => $role === 'startup'
                ? $account['nom_startup']
                : trim($account['prenom'] . ' ' . $account['nom'])
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Visage non reconnu. Authentification échouée.']);
}
?>
