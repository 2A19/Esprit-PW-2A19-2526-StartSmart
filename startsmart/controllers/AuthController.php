<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Mailer.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Validator.php';

/**
 * AuthController – Login / Logout / Register
 * Added: hCaptcha verification + email verification token flow
 */
class AuthController
{
    private PDO $db;

    // ── hCaptcha config ────────────────────────────────────────
    // Register free at https://dashboard.hcaptcha.com
    // Replace with your real secret key. The site key goes in login.php.
    private string $hcaptchaSecret = 'YOUR_HCAPTCHA_SECRET_KEY'; // ← replace

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function clean(string $v): string
    {
        return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
    }

    // ── Verify hCaptcha token with hCaptcha API ────────────────
    private function verifyCaptcha(string $token): bool
    {
        // If secret key not yet configured, skip server-side check (dev mode only)
        // Remove this condition once you add your real secret key
        if ($this->hcaptchaSecret === 'YOUR_HCAPTCHA_SECRET_KEY') {
            return !empty($token); // trust client-side widget passed
        }

        if (empty($token)) return false;

        // Use cURL (more reliable than file_get_contents on XAMPP)
        $ch = curl_init('https://hcaptcha.com/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'secret'   => $this->hcaptchaSecret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) return false;
        $json = json_decode($result, true);
        return !empty($json['success']);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function readUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    private function touchLastLogin(int $id): void
    {
        $this->db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function createUserDb(User $user, string $token): bool
    {
        $sql = "INSERT INTO users (nom, prenom, email, password, telephone, date_naissance, role, statut,
                                   nom_startup, nom_responsable, prenom_responsable, secteur, site_web, stade,
                                   email_token, email_token_expires)
                VALUES (:nom, :prenom, :email, :password, :telephone, :date_naissance, :role, 'pending',
                        :nom_startup, :nom_responsable, :prenom_responsable, :secteur, :site_web, :stade,
                        :email_token, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom'                => $this->clean($user->getNom()),
            ':prenom'             => $this->clean($user->getPrenom()),
            ':email'              => $this->clean($user->getEmail()),
            ':password'           => password_hash($user->getPassword(), PASSWORD_BCRYPT),
            ':telephone'          => $user->getTelephone() ? $this->clean($user->getTelephone()) : null,
            ':date_naissance'     => $user->getDateNaissance(),
            ':role'               => $user->getRole() ?? 'user',
            ':nom_startup'        => $user->getNomStartup() ? $this->clean($user->getNomStartup()) : null,
            ':nom_responsable'    => $user->getNomResponsable() ? $this->clean($user->getNomResponsable()) : null,
            ':prenom_responsable' => $user->getPrenomResponsable() ? $this->clean($user->getPrenomResponsable()) : null,
            ':secteur'            => $user->getSecteur() ? $this->clean($user->getSecteur()) : null,
            ':site_web'           => $user->getSiteWeb() ? $this->clean($user->getSiteWeb()) : null,
            ':stade'              => $user->getStade() ?? 'idee',
            ':email_token'        => $token,
        ]);
    }

    // ── VERIFY EMAIL TOKEN (GET ?token=...) ───────────────────
    public function verifyEmailToken(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            $_SESSION['verify_error'] = 'Lien de vérification invalide.';
            header('Location: /startsmart/views/auth/verify-email.php');
            exit;
        }

        $stmt = $this->db->prepare(
            "SELECT id, statut, email_token_expires FROM users WHERE email_token = :token LIMIT 1"
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();

        if (!$row) {
            $_SESSION['verify_error'] = 'Lien invalide ou déjà utilisé.';
            header('Location: /startsmart/views/auth/verify-email.php');
            exit;
        }

        if (strtotime($row['email_token_expires']) < time()) {
            $_SESSION['verify_error'] = 'Ce lien a expiré (valable 24h). Veuillez vous réinscrire.';
            header('Location: /startsmart/views/auth/verify-email.php');
            exit;
        }

        if ($row['statut'] === 'actif') {
            $_SESSION['verify_success'] = 'Votre email est déjà vérifié. Vous pouvez vous connecter.';
            header('Location: /startsmart/views/auth/verify-email.php');
            exit;
        }

        $this->db->prepare(
            "UPDATE users SET statut = 'actif', email_token = NULL, email_token_expires = NULL WHERE id = :id"
        )->execute([':id' => $row['id']]);

        $_SESSION['verify_success'] = 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.';
        header('Location: /startsmart/views/auth/verify-email.php');
        exit;
    }

    // ── LOGIN ─────────────────────────────────────────────────
    public function login(): void
    {
        $d     = $this->getBody();
        $email = trim($d['email']    ?? '');
        $pass  = trim($d['password'] ?? '');
        $role  = trim($d['role']     ?? '');

        // Verify CAPTCHA first
        if (!$this->verifyCaptcha($d['h-captcha-response'] ?? '')) {
            $_SESSION['login_errors'] = ['general' => 'Vérification CAPTCHA échouée. Veuillez réessayer.'];
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        $v = new Validator();
        $v->required('email',    $email, "L'email")
          ->email   ('email',    $email)
          ->required('password', $pass,  'Le mot de passe')
          ->required('role',     $role,  'Le rôle')
          ->inList  ('role',     $role,  ['user','startup','admin'], 'Le rôle');

        if ($v->fails()) {
            $_SESSION['login_errors'] = $v->getErrors();
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        $account = $this->readUserByEmail($email);

        if (!$account || !password_verify($pass, $account['password'])) {
            $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        if ($account['role'] !== $role) {
            $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        if ($account['statut'] === 'pending') {
            $_SESSION['login_errors'] = ['general' => 'Veuillez vérifier votre adresse email avant de vous connecter. Consultez votre boîte de réception.'];
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        if ($account['statut'] === 'banni') {
            $_SESSION['login_errors'] = ['general' => "Votre compte a été suspendu. Contactez l'administrateur."];
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        $this->touchLastLogin($account['id']);

        $_SESSION['user_id']   = $account['id'];
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $role === 'startup'
            ? $account['nom_startup']
            : $account['prenom'] . ' ' . $account['nom'];
        $_SESSION['user_type'] = 'user';
        $_SESSION['user_photo'] = $account['profile_picture'] ?? null;

        $redirect = $role === 'admin'
            ? '/startsmart/views/back/dashboard.php'
            : '/startsmart/views/front/dashboard.php';
        header('Location: ' . $redirect);
        exit;
    }

    // ── REGISTER USER ─────────────────────────────────────────
    public function registerUser(): void
    {
        $d = $this->getBody();

        if (!$this->verifyCaptcha($d['h-captcha-response'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Vérification CAPTCHA échouée. Veuillez réessayer.']);
            exit;
        }

        $v = new Validator();
        $v->required('nom',      $d['nom']      ?? '', 'Le nom')
          ->minLen  ('nom',      $d['nom']       ?? '', 2, 'Le nom')
          ->required('prenom',   $d['prenom']   ?? '', 'Le prénom')
          ->minLen  ('prenom',   $d['prenom']   ?? '', 2, 'Le prénom')
          ->required('email',    $d['email']    ?? '', "L'email")
          ->email   ('email',    $d['email']    ?? '')
          ->required('password', $d['password'] ?? '', 'Le mot de passe')
          ->password('password', $d['password'] ?? '')
          ->confirm ('password', $d['password'] ?? '', $d['password_confirm'] ?? '')
          ->phone   ('telephone', $d['telephone'] ?? null)
          ->date    ('date_naissance', $d['date_naissance'] ?? null, 'Date de naissance');

        if ($v->fails()) {
            echo json_encode(['success' => false, 'error' => array_values($v->getErrors())[0] ?? 'Erreur de validation']);
            exit;
        }

        if ($this->emailExists($d['email'])) {
            echo json_encode(['success' => false, 'error' => 'Cet email est déjà utilisé.']);
            exit;
        }

        $profile_picture = $this->uploadProfilePicture();

        $user = new User(null, $d['nom'], $d['prenom'], $d['email'], $d['password'],
                         $d['telephone'] ?? null, $d['date_naissance'] ?? null, 'user', 'pending',
                         null, $profile_picture);

        $token = $this->generateToken();
        if (!$this->createUserDb($user, $token)) {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la création du compte.']);
            exit;
        }

        (new Mailer())->sendVerification($d['email'], $d['prenom'] . ' ' . $d['nom'], $token);

        echo json_encode(['success' => true, 'message' => 'Compte créé ! Un email de vérification a été envoyé à ' .
            htmlspecialchars($d['email']) . '. Veuillez consulter votre boîte de réception (vérifiez les spams).']);
        exit;
    }

    // ── REGISTER STARTUP ──────────────────────────────────────
    public function registerStartup(): void
    {
        $d = $this->getBody();

        if (!$this->verifyCaptcha($d['h-captcha-response'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Vérification CAPTCHA échouée. Veuillez réessayer.']);
            exit;
        }

        $v = new Validator();
        $v->required('nom_startup',         $d['nom_startup']        ?? '', 'Nom de la startup')
          ->minLen  ('nom_startup',         $d['nom_startup']        ?? '', 2, 'Nom de la startup')
          ->required('nom_responsable',     $d['nom_responsable']    ?? '', 'Nom du responsable')
          ->required('prenom_responsable',  $d['prenom_responsable'] ?? '', 'Prénom du responsable')
          ->required('email',               $d['email']              ?? '', "L'email")
          ->email   ('email',               $d['email']              ?? '')
          ->required('password',            $d['password']           ?? '', 'Le mot de passe')
          ->password('password',            $d['password']           ?? '')
          ->confirm ('password',            $d['password']           ?? '', $d['password_confirm'] ?? '')
          ->required('secteur',             $d['secteur']            ?? '', 'Le secteur')
          ->phone   ('telephone',           $d['telephone']          ?? null)
          ->url     ('site_web',            $d['site_web']           ?? null)
          ->inList  ('stade', $d['stade'] ?? 'idee', ['idee','prototype','mvp','croissance','scale'], 'Stade');

        if ($v->fails()) {
            echo json_encode(['success' => false, 'error' => array_values($v->getErrors())[0] ?? 'Erreur de validation']);
            exit;
        }

        if ($this->emailExists($d['email'])) {
            echo json_encode(['success' => false, 'error' => 'Cet email est déjà utilisé.']);
            exit;
        }

        $profile_picture = $this->uploadProfilePicture();

        $user = new User(null, $d['nom_responsable'], $d['prenom_responsable'], $d['email'],
                         $d['password'], $d['telephone'] ?? null, null, 'startup', 'pending',
                         null, $profile_picture, $d['nom_startup'], $d['nom_responsable'],
                         $d['prenom_responsable'], $d['secteur'], $d['site_web'] ?? null,
                         $d['stade'] ?? 'idee');

        $token = $this->generateToken();
        if (!$this->createUserDb($user, $token)) {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la création.']);
            exit;
        }

        (new Mailer())->sendVerification(
            $d['email'],
            $d['prenom_responsable'] . ' ' . $d['nom_responsable'],
            $token
        );

        echo json_encode(['success' => true, 'message' => 'Compte startup créé ! Un email de vérification a été envoyé à ' .
            htmlspecialchars($d['email']) . '. Veuillez vérifier votre adresse avant de vous connecter.']);
        exit;
    }

    // ── LOGOUT ────────────────────────────────────────────────
    public function logout(): void
    {
        session_destroy();
        header('Location: /startsmart/views/auth/login.php');
        exit;
    }

    private function uploadProfilePicture(): ?string
    {
        if (empty($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            return null;
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        // Create profiles directory if it doesn't exist
        $upload_dir = __DIR__ . '/../public/img/profiles';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $filepath = $upload_dir . '/' . $filename;
        $relative_path = '/startsmart/public/img/profiles/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return null;
        }

        return $relative_path;
    }

    private function getBody(): array { return $_POST; }
}
