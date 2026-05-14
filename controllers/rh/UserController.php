<?php
require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../models/rh/RHUser.php');

class UserController {
    private function splitFullName(?string $fullName): array {
        $parts = preg_split('/\s+/', trim((string)$fullName), 2);
        $prenom = $parts[0] ?? 'Utilisateur';
        $nom = $parts[1] ?? $prenom;
        return [$nom ?: 'Utilisateur', $prenom ?: 'Utilisateur'];
    }

    public function listUsers() {
        // startsmart_db.users uses date_inscription (not created_at)
        $sql = "SELECT * FROM users ORDER BY date_inscription DESC, id DESC";
        $db = (new Database())->getPDO();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addUser(RHUser $user) {
        $db = (new Database())->getPDO();
        $hashedPassword = password_hash($user->getPassword(), PASSWORD_BCRYPT);
        [$nom, $prenom] = $this->splitFullName($user->getFullName());
        $telephone = trim((string) $user->getPhone());
        $telephone = $telephone !== '' ? $telephone : null;
        $roleRaw = strtolower((string) $user->getRole());
        $roleDb = $roleRaw === 'startup' ? 'startup' : ($roleRaw === 'admin' ? 'admin' : 'user');

        $fullName = trim((string) $user->getFullName());

        try {
            if ($roleDb === 'startup') {
                $nomStartup = trim((string) $user->getCompanyName());
                if ($nomStartup === '') {
                    $nomStartup = 'Startup';
                }
                $secteur = trim((string) $user->getProfession());
                $secteur = $secteur !== '' ? $secteur : null;
                $sql = "INSERT INTO users (nom, prenom, full_name, email, password, telephone, role, statut, nom_startup, company_name, nom_responsable, prenom_responsable, secteur)
                        VALUES (:nom, :prenom, :full_name, :email, :password, :telephone, 'startup', 'actif', :nom_startup, :nom_startup2, :nom_resp, :prenom_resp, :secteur)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'full_name' => $fullName !== '' ? $fullName : ($prenom . ' ' . $nom),
                    'email' => $user->getEmail(),
                    'password' => $hashedPassword,
                    'telephone' => $telephone,
                    'nom_startup' => $nomStartup,
                    'nom_startup2' => $nomStartup,
                    'nom_resp' => $nom,
                    'prenom_resp' => $prenom,
                    'secteur' => $secteur,
                ]);
            } else {
                $sql = "INSERT INTO users (nom, prenom, full_name, email, password, telephone, role, statut)
                        VALUES (:nom, :prenom, :full_name, :email, :password, :telephone, :role, 'actif')";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'full_name' => $fullName !== '' ? $fullName : ($prenom . ' ' . $nom),
                    'email' => $user->getEmail(),
                    'password' => $hashedPassword,
                    'telephone' => $telephone,
                    'role' => $roleDb,
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateUser(RHUser $user, $id) {
        try {
            $db = (new Database())->getPDO();
            [$nom, $prenom] = $this->splitFullName($user->getFullName());
            $companyName = $user->getRole() === 'startup' ? trim((string) $user->getCompanyName()) : null;
            $telephone = trim((string) $user->getPhone());
            $telephone = $telephone !== '' ? $telephone : null;
            $secteur = trim((string) $user->getProfession());
            $secteur = $secteur !== '' ? $secteur : null;

            if ($user->getRole() === 'startup') {
                $query = $db->prepare(
                    'UPDATE users SET
                        nom = :nom,
                        prenom = :prenom,
                        email = :email,
                        role = \'startup\',
                        telephone = :telephone,
                        nom_startup = :nom_startup,
                        nom_responsable = :nom_resp,
                        prenom_responsable = :prenom_resp,
                        secteur = :secteur
                    WHERE id = :id'
                );
                $query->execute([
                    'id' => $id,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $user->getEmail(),
                    'telephone' => $telephone,
                    'nom_startup' => $companyName,
                    'nom_resp' => $nom,
                    'prenom_resp' => $prenom,
                    'secteur' => $secteur,
                ]);
            } else {
                $query = $db->prepare(
                    'UPDATE users SET
                        nom = :nom,
                        prenom = :prenom,
                        email = :email,
                        role = :role,
                        telephone = :telephone,
                        secteur = :secteur
                    WHERE id = :id'
                );
                $roleDb = strtolower((string) $user->getRole()) === 'admin' ? 'admin' : 'user';
                $query->execute([
                    'id' => $id,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $user->getEmail(),
                    'role' => $roleDb,
                    'telephone' => $telephone,
                    'secteur' => $secteur,
                ]);
            }
        } catch (PDOException $e) {
            throw $e;
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
        $email = trim((string) $email);
        if ($email === '') {
            return false;
        }
        $sql = "SELECT * FROM users WHERE LOWER(TRIM(email)) = LOWER(:email) LIMIT 1";
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

    /**
     * Supports bcrypt ($2y$...) and legacy 32-char hex MD5 hashes.
     */
    private function verifyPassword(string $plain, ?string $hash): bool {
        $hash = (string) $hash;
        if ($hash === '') {
            return false;
        }
        if (password_verify($plain, $hash)) {
            return true;
        }
        if (strlen($hash) === 32 && ctype_xdigit($hash)) {
            return hash_equals(strtolower($hash), md5($plain));
        }

        return false;
    }

    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && $this->verifyPassword($password, $user['password'] ?? null)) {
            return $user;
        }

        return false;
    }

    public function emailExists($email) {
        $email = trim((string) $email);
        if ($email === '') {
            return false;
        }
        $sql = "SELECT id FROM users WHERE LOWER(TRIM(email)) = LOWER(:email) LIMIT 1";
        $db = (new Database())->getPDO();
        $query = $db->prepare($sql);
        $query->bindValue(':email', $email);
        
        try {
            $query->execute();
            return (bool) $query->fetchColumn();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getUsersByRole($role) {
        $sql = "SELECT * FROM users WHERE role = :role ORDER BY date_inscription DESC, id DESC";
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
        $hasResumeCol = false;
        try {
            $chk = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'resume'");
            $hasResumeCol = ((int) $chk->fetchColumn()) > 0;
        } catch (Exception $e) {
            $hasResumeCol = false;
        }

        if ($hasResumeCol && !empty($_FILES['resume']['name'])) {
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
            [$nom, $prenom] = $this->splitFullName($data['fullName'] ?? '');
            $telephone = trim((string) ($data['phone'] ?? ''));
            $secteur = trim((string) ($data['profession'] ?? ''));

            $sql = "UPDATE users SET
                    nom = :nom,
                    prenom = :prenom,
                    telephone = :telephone,
                    secteur = :secteur";

            $params = [
                'userId' => $userId,
                'nom' => $nom,
                'prenom' => $prenom,
                'telephone' => $telephone !== '' ? $telephone : null,
                'secteur' => $secteur !== '' ? $secteur : null,
            ];

            if ($hasResumeCol && !empty($resumePath)) {
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

    private function normalizeUserRowForViews(?array $user): ?array {
        if ($user === null) {
            return null;
        }
        $user['phone'] = $user['telephone'] ?? ($user['phone'] ?? '');
        $user['full_name'] = trim((string)($user['full_name'] ?? ''));
        if ($user['full_name'] === '') {
            $user['full_name'] = trim((string)($user['prenom'] ?? '') . ' ' . (string)($user['nom'] ?? ''));
        }
        $user['company_name'] = $user['company_name'] ?? ($user['nom_startup'] ?? '');
        $user['profession'] = $user['profession'] ?? ($user['secteur'] ?? '');
        return $user;
    }

    public function profile() {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->normalizeUserRowForViews($this->showUser($userId));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->updateProfile($userId, $_POST);
            if (isset($result['error'])) {
                $data = ['user' => $user, 'error' => $result['error'], 'currentView' => 'profile'];
            } else {
                // Refresh user data
                $user = $this->normalizeUserRowForViews($this->showUser($userId));
                $data = ['user' => $user, 'success' => 'Profile updated successfully', 'currentView' => 'profile'];
            }
        } else {
            $data = ['user' => $user, 'currentView' => 'profile'];
        }

        $this->renderView('frontend/frontend', $data);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../../views/rh/' . $viewPath . '.php';
    }
}
?>
