<?php
require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../models/rh/RHUser.php');

class AuthController {

    private $userController;

    public function __construct() {
        require_once(__DIR__ . '/UserController.php');
        $this->userController = new UserController();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $data = [
                    'errors' => ['Email and password are required'],
                    'form_data' => $_POST,
                    'currentView' => 'login'
                ];
                $this->renderView('frontend/frontend', $data);
                return;
            }

            // Authenticate user
            $user = $this->userController->authenticate($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = trim((string)($user['full_name'] ?? ''));
                if ($_SESSION['user_name'] === '') {
                    $_SESSION['user_name'] = trim((string)($user['prenom'] ?? '') . ' ' . (string)($user['nom'] ?? ''));
                }
                if (trim($_SESSION['user_name']) === '') {
                    $_SESSION['user_name'] = (string)($user['email'] ?? 'Utilisateur');
                }

                // Bridge redirect only: backend roles stay in RH, normal users enter the frontend UI.
                $role = strtolower(trim((string) $user['role']));
                if (in_array($role, ['admin', 'rh'], true)) {
                    header('Location: rh.php?page=backend/admin');
                } elseif ($role === 'startup') {
                    header('Location: rh.php?page=backend/dashboard');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $data = [
                    'errors' => ['Invalid email or password'],
                    'form_data' => $_POST,
                    'currentView' => 'login'
                ];
                $this->renderView('frontend/frontend', $data);
            }
        } else {
            // Display login form for GET requests
            $this->renderView('frontend/frontend', ['currentView' => 'login']);
        }
    }

    public function showLogin() {
        $this->renderView('frontend/frontend', ['currentView' => 'login']);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['fullName'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $userRole = $_POST['userRole'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $companyName = $_POST['companyName'] ?? null;

            $errors = [];

            // Validate input
            if (empty($fullName)) {
                $errors[] = 'Full Name is required';
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }
            if (empty($password) || strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters';
            }
            if (empty($userRole)) {
                $errors[] = 'User Role is required';
            }
            if (empty($phone)) {
                $errors[] = 'Phone is required';
            }
            if ($userRole === 'startup' && empty($companyName)) {
                $errors[] = 'Company Name is required for startups';
            }

            // Check if email exists
            $existingUser = $this->userController->findByEmail($email);
            if ($existingUser) {
                $errors[] = 'Email already exists';
            }

            if (!empty($errors)) {
                $data = [
                    'errors' => $errors,
                    'form_data' => $_POST,
                    'currentView' => 'register'
                ];
                $this->renderView('frontend/frontend', $data);
                return;
            }

            // Create user object
            $user = new RHUser(
                null,
                $fullName,
                $email,
                $password,
                $userRole,
                $companyName,
                $phone
            );

            // Add user
            $this->userController->addUser($user);

            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: login.php');
            exit;
        } else {
            // Display registration form for GET requests
            $this->renderView('frontend/frontend', ['currentView' => 'register']);
        }
    }

    public function showRegister() {
        $this->renderView('frontend/frontend', ['currentView' => 'register']);
    }

    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    private function renderView($viewPath, $data = []) {
        $viewFile = __DIR__ . '/../../views/rh/' . $viewPath . '.php';
        
        if (!file_exists($viewFile)) {
            die('View file not found: ' . $viewFile);
        }
        
        // Set up view context variables
        $basePath = dirname(__DIR__);
        
        extract($data);
        include $viewFile;
    }
}
?>
