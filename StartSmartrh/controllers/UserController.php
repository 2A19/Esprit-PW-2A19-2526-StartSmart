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
        $sql = "INSERT INTO users (full_name, email, password, role, company_name, phone) 
                VALUES (:fullName, :email, :password, :role, :companyName, :phone)";
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
                'phone' => $user->getPhone()
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
                    phone = :phone
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'companyName' => $user->getCompanyName(),
                'phone' => $user->getPhone()
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
}
?>
