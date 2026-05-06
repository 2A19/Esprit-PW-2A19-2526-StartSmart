<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../models/User.php');

class UserController {

    public function listUsers() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $db = (new Database())->getPDO();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addUser(User $user) {
        $sql = "INSERT INTO users (full_name, email, password, role, company_name, phone, profession, experience, resume) 
                VALUES (:fullName, :email, :password, :role, :companyName, :phone, :profession, :experience, :resume)";
        $db = (new Database())->getPDO();
        try {
            $query = $db->prepare($sql);
            $hashedPassword = password_hash($user->getPassword(), PASSWORD_BCRYPT);
            $query->execute([
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'password' => $hashedPassword,
                'role' => $user->getRole(),
                'companyName' => $user->getCompanyName(),
                'phone' => $user->getPhone(),
                'profession' => $user->getProfession(),
                'experience' => $user->getExperience(),
                'resume' => $user->getResume()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateUser(User $user, $id) {
        try {
            $db = (new Database())->getPDO();
            $query = $db->prepare(
                'UPDATE users SET 
                    full_name = :fullName,
                    email = :email,
                    role = :role,
                    company_name = :companyName,
                    phone = :phone,
                    profession = :profession,
                    experience = :experience,
                    resume = :resume
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'companyName' => $user->getCompanyName(),
                'phone' => $user->getPhone(),
                'profession' => $user->getProfession(),
                'experience' => $user->getExperience(),
                'resume' => $user->getResume()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $db = (new Database())->getPDO();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showUser($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $db = (new Database())->getPDO();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id);

        try {
            $query->execute();
            $user = $query->fetch();
            return $user;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $db = (new Database())->getPDO();
        $query = $db->prepare($sql);
        $query->bindValue(':email', $email);

        try {
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $db = (new Database())->getPDO();
        $query = $db->prepare($sql);
        $query->bindValue(':email', $email);
        
        try {
            $query->execute();
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getUsersByRole($role) {
        $sql = "SELECT * FROM users WHERE role = :role ORDER BY created_at DESC";
        $db = (new Database())->getPDO();
        $query = $db->prepare($sql);
        $query->bindValue(':role', $role);
        
        try {
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function updateProfile($userId, $data) {
        $db = (new Database())->getPDO();
        
        // Handle file upload if provided
        $resumePath = '';
        if (!empty($_FILES['resume']['name'])) {
            $uploadsDir = __DIR__ . '/../public/uploads/resumes/';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                return ['error' => 'Resume must be PDF, DOC, or DOCX'];
            }

            if ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
                return ['error' => 'Resume file size must not exceed 5MB'];
            }

            $fileName = uniqid() . '_' . basename($_FILES['resume']['name']);
            $filePath = $uploadsDir . $fileName;

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
                $resumePath = 'public/uploads/resumes/' . $fileName;
            } else {
                return ['error' => 'Failed to upload resume'];
            }
        }

        try {
            $sql = "UPDATE users SET 
                    full_name = :fullName,
                    phone = :phone,
                    profession = :profession,
                    experience = :experience";
            
            $params = [
                'userId' => $userId,
                'fullName' => $data['fullName'] ?? '',
                'phone' => $data['phone'] ?? '',
                'profession' => $data['profession'] ?? '',
                'experience' => $data['experience'] ?? ''
            ];

            if (!empty($resumePath)) {
                $sql .= ", resume = :resume";
                $params['resume'] = $resumePath;
            }

            $sql .= " WHERE id = :userId";

            $query = $db->prepare($sql);
            $query->execute($params);

            return ['success' => true];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function profile() {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->showUser($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->updateProfile($userId, $_POST);
            if (isset($result['error'])) {
                $data = ['user' => $user, 'error' => $result['error'], 'currentView' => 'profile'];
            } else {
                // Refresh user data
                $user = $this->showUser($userId);
                $data = ['user' => $user, 'success' => 'Profile updated successfully', 'currentView' => 'profile'];
            }
        } else {
            $data = ['user' => $user, 'currentView' => 'profile'];
        }

        $this->renderView('frontend/frontend', $data);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $viewPath . '.php';
    }
}
?>
