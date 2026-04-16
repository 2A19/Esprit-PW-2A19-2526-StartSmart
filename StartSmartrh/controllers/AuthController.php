<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../models/User.php');

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
                    'form_data' => $_POST
                ];
                $this->renderView('frontend/login', $data);
                return;
            }

            // Authenticate user
            $user = $this->userController->authenticate($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];

                // Redirect based on role
                if ($user['role'] === 'startup') {
                    header('Location: index.php?page=backend/dashboard');
                } else {
                    header('Location: index.php?page=frontend/home');
                }
                exit;
            } else {
                $data = [
                    'errors' => ['Invalid email or password'],
                    'form_data' => $_POST
                ];
                $this->renderView('frontend/login', $data);
            }
        } else {
            // Display login form for GET requests
            $this->renderView('frontend/login');
        }
    }

    public function showLogin() {
        $this->renderView('frontend/login');
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
                    'form_data' => $_POST
                ];
                $this->renderView('frontend/register', $data);
                return;
            }

            // Create user object
            $user = new User(
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
            header('Location: index.php?page=auth/login');
            exit;
        } else {
            // Display registration form for GET requests
            $this->renderView('frontend/register');
        }
    }

    public function showRegister() {
        $this->renderView('frontend/register');
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?page=auth/login');
        exit;
    }

    private function renderView($viewPath, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';
        
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
