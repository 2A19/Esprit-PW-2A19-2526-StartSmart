<?php
/**
 * Auth API Handler - Local authentication endpoint
 * Routes /api/auth/* to auth methods
 */

require_once __DIR__ . '/../config/Auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';
$parts = explode('/', trim($path, '/'));
$action = isset($parts[1]) ? $parts[1] : 'index';
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$expectsJson = stripos($contentType, 'application/json') !== false
    || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

if ($method === 'POST' && $action === 'login') {
    // Temporary debug logging
    file_put_contents(__DIR__ . '/../login_attempts.log', date('Y-m-d H:i:s') . " - Attempting login with email: " . ($_POST['email'] ?? 'N/A') . "\n", FILE_APPEND);

    $input = $expectsJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;
    $role = $input['role'] ?? null;
    
    if (!$email || !$password) {
        if (!$expectsJson) {
            http_response_code(302);
            $_SESSION['login_errors'] = ['Email and password required'];
            header('Location: ../login.php');
            exit;
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email and password required']);
        exit;
    }
    
    if (login($email, $password, $role)) {
        $role = normalizedRole($_SESSION['user_role'] ?? null);
        if (in_array($role, ['admin', 'rh'], true)) {
            $redirect = 'rh.php?page=backend/admin';
        } elseif ($role === 'startup') {
            $redirect = 'rh.php?page=backend/dashboard';
        } else {
            $redirect = 'index.php';
        }

        if (!$expectsJson) {
            header('Location: ../' . $redirect);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Logged in successfully',
            'redirect' => $redirect,
            'role' => $_SESSION['user_role'] ?? null
        ]);
    } else {
        if (!$expectsJson) {
            http_response_code(302);
            if (empty($_SESSION['login_errors'])) {
                $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            }
            header('Location: ../login.php');
            exit;
        }
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
    exit;
}

if ($method === 'POST' && $action === 'register_user') {
    require_once __DIR__ . '/../controllers/rh/UserController.php';
    require_once __DIR__ . '/../models/rh/RHUser.php';

    $nom    = trim((string) ($_POST['nom']    ?? ''));
    $prenom = trim((string) ($_POST['prenom'] ?? ''));
    $email  = trim((string) ($_POST['email']  ?? ''));
    $pass   = (string) ($_POST['password']         ?? '');
    $pass2  = (string) ($_POST['password_confirm'] ?? '');
    $tel    = trim((string) ($_POST['telephone']    ?? ''));

    if ($nom === '' || $prenom === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nom et prénom obligatoires.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Adresse email invalide.']);
        exit;
    }
    if (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Mot de passe : min. 8 caractères, 1 majuscule, 1 chiffre.']);
        exit;
    }
    if ($pass !== $pass2) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Les mots de passe ne correspondent pas.']);
        exit;
    }

    $uc = new UserController();
    if ($uc->emailExists($email)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Cette adresse email est déjà utilisée.']);
        exit;
    }

    try {
        $user = new RHUser(
            null,
            $prenom . ' ' . $nom,
            $email,
            $pass,
            'user',
            null,
            $tel !== '' ? $tel : null
        );
        $uc->addUser($user);
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès. Vous pouvez maintenant vous connecter.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la création du compte : ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST' && $action === 'register_startup') {
    require_once __DIR__ . '/../controllers/rh/UserController.php';
    require_once __DIR__ . '/../models/rh/RHUser.php';

    $nom_startup   = trim((string) ($_POST['nom_startup']       ?? ''));
    $nom_resp      = trim((string) ($_POST['nom_responsable']   ?? ''));
    $prenom_resp   = trim((string) ($_POST['prenom_responsable']?? ''));
    $email         = trim((string) ($_POST['email']             ?? ''));
    $pass          = (string) ($_POST['password']               ?? '');
    $pass2         = (string) ($_POST['password_confirm']       ?? '');
    $secteur       = trim((string) ($_POST['secteur']           ?? ''));
    $tel           = trim((string) ($_POST['telephone']         ?? ''));

    if ($nom_startup === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nom de la startup obligatoire.']);
        exit;
    }
    if ($nom_resp === '' || $prenom_resp === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nom et prénom du responsable obligatoires.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Adresse email invalide.']);
        exit;
    }
    if (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Mot de passe : min. 8 caractères, 1 majuscule, 1 chiffre.']);
        exit;
    }
    if ($pass !== $pass2) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Les mots de passe ne correspondent pas.']);
        exit;
    }

    $uc = new UserController();
    if ($uc->emailExists($email)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Cette adresse email est déjà utilisée.']);
        exit;
    }

    try {
        $user = new RHUser(
            null,
            $prenom_resp . ' ' . $nom_resp,
            $email,
            $pass,
            'startup',
            $nom_startup,
            $tel !== '' ? $tel : null,
            $secteur !== '' ? $secteur : null
        );
        $uc->addUser($user);
        echo json_encode(['success' => true, 'message' => 'Compte startup créé avec succès. Vous pouvez maintenant vous connecter.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la création du compte : ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST' && in_array($action, ['google_login', 'face_login', 'forgot_password', 'verify_otp', 'reset_password'], true)) {
    http_response_code(501);
    echo json_encode([
        'success' => false,
        'error' => 'Fonctionnalité non disponible dans cette version.'
    ]);
    exit;
}

if ($method === 'GET' && $action === 'logout') {
    logout();
    exit;
}

if ($method === 'GET' && $action === 'me') {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    $user = currentUser();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'user' => $user]);
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'error' => 'Auth endpoint not found']);


