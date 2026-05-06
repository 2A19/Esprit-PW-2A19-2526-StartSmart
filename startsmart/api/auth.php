<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController();
$action     = $_GET['action'] ?? '';

switch ($action) {
    case 'login':           $controller->login();           break;
    case 'google_login':    $controller->googleLogin();     break;
    case 'face_login':      $controller->faceLogin();       break;
    case 'register_user':   $controller->registerUser();    break;
    case 'register_startup':$controller->registerStartup(); break;
    case 'verify_email':    $controller->verifyEmailToken();break;
    case 'forgot_password': $controller->forgotPassword();  break;
    case 'verify_otp':      $controller->verifyOtp();       break;
    case 'reset_password':  $controller->resetPassword();   break;
    case 'logout':          $controller->logout();          break;
    default:
        header('Location: /startsmart/views/auth/login.php'); exit;
}
