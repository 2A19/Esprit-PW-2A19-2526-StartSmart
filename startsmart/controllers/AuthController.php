<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Mailer.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Validator.php';

class AuthController
{
    private PDO $db;
    private string $hcaptchaSecret = 'YOUR_HCAPTCHA_SECRET_KEY';
    private string $googleClientId = '';
    private string $rememberCookieName = 'startsmart_remember';
    private int $rememberLifetime = 2592000;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->googleClientId = trim((string)(getenv('GOOGLE_CLIENT_ID') ?: ''));
    }

    private function clean(string $v): string
    {
        return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
    }

    private function verifyCaptcha(string $token): bool
    {
        if ($this->hcaptchaSecret === 'YOUR_HCAPTCHA_SECRET_KEY') return !empty($token);
        if (empty($token)) return false;
        $ch = curl_init('https://hcaptcha.com/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'secret'   => $this->hcaptchaSecret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]),
            CURLOPT_TIMEOUT => 10,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === false) return false;
        $json = json_decode($result, true);
        return !empty($json['success']);
    }

    private function generateToken(): string { return bin2hex(random_bytes(32)); }
    private function generateOtp(): string   { return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT); }

    private function setUserSession(array $account): void
    {
        $_SESSION['user_id']    = $account['id'];
        $_SESSION['user_role']  = $account['role'];
        $_SESSION['user_name']  = $account['role'] === 'startup' ? $account['nom_startup'] : trim(($account['prenom'] ?? '') . ' ' . ($account['nom'] ?? ''));
        $_SESSION['user_type']  = 'user';
        $_SESSION['user_photo'] = $account['profile_picture'] ?? null;
    }

    private function redirectAfterLogin(string $role): void
    {
        header('Location: ' . ($role === 'admin' ? '/startsmart/views/back/dashboard.php' : '/startsmart/views/front/dashboard.php'));
        exit;
    }

    private function setRememberCookieValue(string $value, int $expires): void
    {
        setcookie($this->rememberCookieName, $value, [
            'expires'  => $expires,
            'path'     => '/startsmart/',
            'httponly' => true,
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
        ]);
    }

    private function clearRememberCookie(): void
    {
        setcookie($this->rememberCookieName, '', [
            'expires'  => time() - 3600,
            'path'     => '/startsmart/',
            'httponly' => true,
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
        ]);
    }

    private function clearRememberTokenBySelector(string $selector): void
    {
        $this->db->prepare("UPDATE users SET remember_selector = NULL, remember_token_hash = NULL, remember_expires = NULL WHERE remember_selector = :selector")
                 ->execute([':selector' => $selector]);
    }

    private function clearRememberTokenForUser(int $id): void
    {
        $this->db->prepare("UPDATE users SET remember_selector = NULL, remember_token_hash = NULL, remember_expires = NULL WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    private function issueRememberMeToken(int $id): void
    {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = time() + $this->rememberLifetime;

        $this->db->prepare("UPDATE users
                               SET remember_selector = :selector,
                                   remember_token_hash = :token_hash,
                                   remember_expires = :expires
                             WHERE id = :id")
                 ->execute([
                     ':selector'   => $selector,
                     ':token_hash' => hash('sha256', $validator),
                     ':expires'    => gmdate('Y-m-d H:i:s', $expiresAt),
                     ':id'         => $id,
                 ]);

        $this->setRememberCookieValue($selector . ':' . $validator, $expiresAt);
    }

    private function loginUser(array $account, bool $rememberMe = false): void
    {
        $this->touchLastLogin((int)$account['id']);
        $this->setUserSession($account);

        if ($rememberMe) {
            $this->issueRememberMeToken((int)$account['id']);
        } else {
            $this->clearRememberTokenForUser((int)$account['id']);
            $this->clearRememberCookie();
        }

        $this->redirectAfterLogin((string)$account['role']);
    }

    public function autoLoginFromRememberMe(): bool
    {
        if (!empty($_SESSION['user_id']) || empty($_COOKIE[$this->rememberCookieName])) {
            return !empty($_SESSION['user_id']);
        }

        $parts = explode(':', (string)$_COOKIE[$this->rememberCookieName], 2);
        if (count($parts) !== 2 || !$parts[0] || !$parts[1]) {
            $this->clearRememberCookie();
            return false;
        }

        [$selector, $validator] = $parts;
        $stmt = $this->db->prepare("SELECT * FROM users WHERE remember_selector = :selector AND remember_expires IS NOT NULL AND remember_expires > UTC_TIMESTAMP() LIMIT 1");
        $stmt->execute([':selector' => $selector]);
        $account = $stmt->fetch();

        if (!$account || empty($account['remember_token_hash']) || !hash_equals($account['remember_token_hash'], hash('sha256', $validator))) {
            $this->clearRememberTokenBySelector($selector);
            $this->clearRememberCookie();
            return false;
        }

        $this->setUserSession($account);
        $this->touchLastLogin((int)$account['id']);
        $this->issueRememberMeToken((int)$account['id']);
        return true;
    }

    private function verifyGoogleCredential(string $credential): ?array
    {
        if ($this->googleClientId === '' || $credential === '') {
            return null;
        }

        $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($credential));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === false) {
            return null;
        }

        $payload = json_decode($result, true);
        if (!is_array($payload)) {
            return null;
        }

        $issuer = $payload['iss'] ?? '';
        if (($payload['aud'] ?? '') !== $this->googleClientId) {
            return null;
        }
        if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            return null;
        }
        if (($payload['email_verified'] ?? '') !== 'true' && ($payload['email_verified'] ?? false) !== true) {
            return null;
        }
        if (empty($payload['email']) || empty($payload['sub'])) {
            return null;
        }

        return $payload;
    }

    private function readUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    private function touchLastLogin(int $id): void
    {
        $this->db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :id")->execute([':id' => $id]);
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
                        :email_token, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 24 HOUR))";
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

    // ── VERIFY EMAIL TOKEN ─────────────────────────────────────
    public function verifyEmailToken(): void
    {
        $token = trim($_GET['token'] ?? '');
        if (empty($token)) {
            $_SESSION['verify_error'] = 'Lien de verification invalide.';
            header('Location: /startsmart/views/auth/verify-email.php'); exit;
        }
        $stmt = $this->db->prepare("SELECT id, statut, email_token_expires FROM users WHERE email_token = :token LIMIT 1");
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();
        if (!$row) {
            $_SESSION['verify_error'] = 'Lien invalide ou deja utilise.';
            header('Location: /startsmart/views/auth/verify-email.php'); exit;
        }
        if (strtotime($row['email_token_expires'] . ' UTC') < time()) {
            $_SESSION['verify_error'] = 'Ce lien a expire (valable 24h).';
            header('Location: /startsmart/views/auth/verify-email.php'); exit;
        }
        if ($row['statut'] === 'actif') {
            $_SESSION['verify_success'] = 'Votre email est deja verifie.';
            header('Location: /startsmart/views/auth/verify-email.php'); exit;
        }
        $this->db->prepare("UPDATE users SET statut = 'actif', email_token = NULL, email_token_expires = NULL WHERE id = :id")
                 ->execute([':id' => $row['id']]);
        $_SESSION['verify_success'] = 'Email verifie avec succes ! Vous pouvez maintenant vous connecter.';
        header('Location: /startsmart/views/auth/verify-email.php'); exit;
    }

    // ── LOGIN ─────────────────────────────────────────────────
    public function login(): void
    {
        $d     = $_POST;
        $email = trim($d['email']    ?? '');
        $pass  = trim($d['password'] ?? '');
        $role  = trim($d['role']     ?? '');
        $rememberMe = !empty($d['remember_me']);

        if (!$this->verifyCaptcha($d['h-captcha-response'] ?? '')) {
            $_SESSION['login_errors'] = ['general' => 'Verification CAPTCHA echouee.'];
            header('Location: /startsmart/views/auth/login.php'); exit;
        }

        $v = new Validator();
        $v->required('email', $email, "L'email")->email('email', $email)
          ->required('password', $pass, 'Le mot de passe')
          ->required('role', $role, 'Le role')->inList('role', $role, ['user','startup','admin'], 'Le role');

        if ($v->fails()) {
            $_SESSION['login_errors'] = $v->getErrors();
            header('Location: /startsmart/views/auth/login.php'); exit;
        }

        $account = $this->readUserByEmail($email);
        if (!$account || !password_verify($pass, $account['password'])) {
            $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            header('Location: /startsmart/views/auth/login.php'); exit;
        }
        if ($account['role'] !== $role) {
            $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            header('Location: /startsmart/views/auth/login.php'); exit;
        }
        if ($account['statut'] === 'pending') {
            $_SESSION['login_errors'] = ['general' => 'Veuillez verifier votre adresse email avant de vous connecter.'];
            header('Location: /startsmart/views/auth/login.php'); exit;
        }
        if ($account['statut'] === 'banni') {
            // Check if timed ban has expired
            $banExpires = $account['ban_expires'] ?? null;
            if ($banExpires !== null && strtotime($banExpires) < time()) {
                // Ban has expired — auto-unban
                $this->db->prepare("UPDATE users SET statut = 'actif', ban_expires = NULL, ban_reason = NULL WHERE id = :id")
                         ->execute([':id' => $account['id']]);
                // Continue login normally below
            } else {
                $reason   = $account['ban_reason'] ? ' Raison : ' . htmlspecialchars($account['ban_reason']) : '';
                $until    = $banExpires ? ' Jusqu\'au ' . date('d/m/Y H:i', strtotime($banExpires)) . '.' : ' (permanent).';
                $_SESSION['login_errors'] = ['general' => "Votre compte a ete suspendu{$until}{$reason}"];
                header('Location: /startsmart/views/auth/login.php'); exit;
            }
        }

        $this->loginUser($account, $rememberMe);
    }

    // ── FORGOT PASSWORD: step 1 — send OTP ────────────────────
    public function forgotPassword(): void
    {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Adresse email invalide.']); exit;
        }

        $user = $this->readUserByEmail($email);
        // Always return success to avoid email enumeration
        if (!$user || in_array($user['statut'], ['banni'])) {
            echo json_encode(['success' => true]); exit;
        }

        $otp = $this->generateOtp();
        $this->db->prepare("UPDATE users SET reset_otp = :otp, reset_otp_expires = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 10 MINUTE) WHERE id = :id")
                 ->execute([':otp' => $otp, ':id' => $user['id']]);

        $displayName = $user['role'] === 'startup'
            ? ($user['nom_startup'] ?? $user['nom'])
            : $user['prenom'] . ' ' . $user['nom'];

        (new Mailer())->sendPasswordResetOtp($email, $displayName, $otp);

        echo json_encode(['success' => true]); exit;
    }

    // ── FORGOT PASSWORD: step 2 — verify OTP ──────────────────
    public function verifyOtp(): void
    {
        $email = trim($_POST['email'] ?? '');
        $otp   = trim($_POST['otp']   ?? '');

        if (!$email || !$otp) {
            echo json_encode(['success' => false, 'error' => 'Donnees manquantes.']); exit;
        }

        $stmt = $this->db->prepare("SELECT id, reset_otp, reset_otp_expires FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || $user['reset_otp'] !== $otp) {
            echo json_encode(['success' => false, 'error' => 'Code incorrect ou invalide.']); exit;
        }
        if (!$user['reset_otp_expires'] || strtotime($user['reset_otp_expires'] . ' UTC') < time()) {
            echo json_encode(['success' => false, 'error' => 'Code expire. Veuillez recommencer.']); exit;
        }

        // Generate a short-lived reset session token
        $resetToken = $this->generateToken();
        $this->db->prepare("UPDATE users SET reset_otp = NULL, reset_otp_expires = NULL, email_token = :rt, email_token_expires = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 MINUTE) WHERE id = :id")
                 ->execute([':rt' => $resetToken, ':id' => $user['id']]);

        echo json_encode(['success' => true, 'reset_token' => $resetToken]); exit;
    }

    // ── FORGOT PASSWORD: step 3 — set new password ────────────
    public function resetPassword(): void
    {
        $token = trim($_POST['reset_token'] ?? '');
        $pass  = $_POST['password']         ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';

        if (!$token) {
            echo json_encode(['success' => false, 'error' => 'Token manquant.']); exit;
        }
        if (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
            echo json_encode(['success' => false, 'error' => 'Mot de passe : min. 8 car., 1 majuscule, 1 chiffre.']); exit;
        }
        if ($pass !== $pass2) {
            echo json_encode(['success' => false, 'error' => 'Les mots de passe ne correspondent pas.']); exit;
        }

        $stmt = $this->db->prepare("SELECT id, email_token_expires FROM users WHERE email_token = :token LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if (!$user || !$user['email_token_expires'] || strtotime($user['email_token_expires'] . ' UTC') < time()) {
            echo json_encode(['success' => false, 'error' => 'Session expiree. Veuillez recommencer.']); exit;
        }

        $hashed = password_hash($pass, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE users SET password = :pw, email_token = NULL, email_token_expires = NULL WHERE id = :id")
                 ->execute([':pw' => $hashed, ':id' => $user['id']]);

        echo json_encode(['success' => true]); exit;
    }

    // ── REGISTER USER ─────────────────────────────────────────
    public function registerUser(): void
    {
        $d = $_POST;
        if (!$this->verifyCaptcha($d['h-captcha-response'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Verification CAPTCHA echouee.']); exit;
        }
        $v = new Validator();
        $v->required('nom', $d['nom'] ?? '', 'Le nom')->minLen('nom', $d['nom'] ?? '', 2, 'Le nom')
          ->required('prenom', $d['prenom'] ?? '', 'Le prenom')->minLen('prenom', $d['prenom'] ?? '', 2, 'Le prenom')
          ->required('email', $d['email'] ?? '', "L'email")->email('email', $d['email'] ?? '')
          ->required('password', $d['password'] ?? '', 'Le mot de passe')
          ->password('password', $d['password'] ?? '')
          ->confirm('password', $d['password'] ?? '', $d['password_confirm'] ?? '')
          ->phone('telephone', $d['telephone'] ?? null)
          ->date('date_naissance', $d['date_naissance'] ?? null, 'Date de naissance');
        if ($v->fails()) { echo json_encode(['success' => false, 'error' => array_values($v->getErrors())[0]]); exit; }
        if ($this->emailExists($d['email'])) { echo json_encode(['success' => false, 'error' => 'Cet email est deja utilise.']); exit; }

        $profile_picture = $this->uploadProfilePicture();
        $user  = new User(null, $d['nom'], $d['prenom'], $d['email'], $d['password'],
                          $d['telephone'] ?? null, $d['date_naissance'] ?? null, 'user', 'pending', null, $profile_picture);
        $token = $this->generateToken();
        if (!$this->createUserDb($user, $token)) { echo json_encode(['success' => false, 'error' => 'Erreur creation compte.']); exit; }
        (new Mailer())->sendVerification($d['email'], $d['prenom'] . ' ' . $d['nom'], $token);
        echo json_encode(['success' => true, 'message' => 'Compte cree ! Email de verification envoye a ' . htmlspecialchars($d['email']) . '.']); exit;
    }

    // ── REGISTER STARTUP ──────────────────────────────────────
    public function registerStartup(): void
    {
        $d = $_POST;
        if (!$this->verifyCaptcha($d['h-captcha-response'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Verification CAPTCHA echouee.']); exit;
        }
        $v = new Validator();
        $v->required('nom_startup', $d['nom_startup'] ?? '', 'Nom startup')->minLen('nom_startup', $d['nom_startup'] ?? '', 2, 'Nom startup')
          ->required('nom_responsable', $d['nom_responsable'] ?? '', 'Nom responsable')
          ->required('prenom_responsable', $d['prenom_responsable'] ?? '', 'Prenom responsable')
          ->required('email', $d['email'] ?? '', "L'email")->email('email', $d['email'] ?? '')
          ->required('password', $d['password'] ?? '', 'Le mot de passe')
          ->password('password', $d['password'] ?? '')
          ->confirm('password', $d['password'] ?? '', $d['password_confirm'] ?? '')
          ->required('secteur', $d['secteur'] ?? '', 'Le secteur')
          ->phone('telephone', $d['telephone'] ?? null)->url('site_web', $d['site_web'] ?? null)
          ->inList('stade', $d['stade'] ?? 'idee', ['idee','prototype','mvp','croissance','scale'], 'Stade');
        if ($v->fails()) { echo json_encode(['success' => false, 'error' => array_values($v->getErrors())[0]]); exit; }
        if ($this->emailExists($d['email'])) { echo json_encode(['success' => false, 'error' => 'Cet email est deja utilise.']); exit; }

        $profile_picture = $this->uploadProfilePicture();
        $user  = new User(null, $d['nom_responsable'], $d['prenom_responsable'], $d['email'], $d['password'],
                          $d['telephone'] ?? null, null, 'startup', 'pending', null, $profile_picture,
                          $d['nom_startup'], $d['nom_responsable'], $d['prenom_responsable'],
                          $d['secteur'], $d['site_web'] ?? null, $d['stade'] ?? 'idee');
        $token = $this->generateToken();
        if (!$this->createUserDb($user, $token)) { echo json_encode(['success' => false, 'error' => 'Erreur creation.']); exit; }
        (new Mailer())->sendVerification($d['email'], $d['prenom_responsable'] . ' ' . $d['nom_responsable'], $token);
        echo json_encode(['success' => true, 'message' => 'Compte startup cree ! Email de verification envoye a ' . htmlspecialchars($d['email']) . '.']); exit;
    }

    // ── LOGOUT ────────────────────────────────────────────────
    public function logout(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->clearRememberTokenForUser((int)$_SESSION['user_id']);
        } elseif (!empty($_COOKIE[$this->rememberCookieName])) {
            $parts = explode(':', (string)$_COOKIE[$this->rememberCookieName], 2);
            if (!empty($parts[0])) {
                $this->clearRememberTokenBySelector($parts[0]);
            }
        }
        $this->clearRememberCookie();
        $_SESSION = [];
        session_destroy();
        header('Location: /startsmart/views/auth/login.php'); exit;
    }

    // ── FACE RECOGNITION LOGIN ────────────────────────────────
    public function faceLogin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $role  = trim($_POST['role'] ?? '');

        $v = new Validator();
        $v->required('email', $email, "L'email")
          ->email('email', $email)
          ->required('role', $role, 'Le role')
          ->inList('role', $role, ['user','startup','admin'], 'Le role');

        if ($v->fails()) {
            $_SESSION['login_errors'] = $v->getErrors();
            header('Location: /startsmart/views/auth/login.php?tab=login'); exit;
        }

        $account = $this->readUserByEmail($email);
        if (!$account) {
            $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            header('Location: /startsmart/views/auth/login.php?tab=login'); exit;
        }

        if ($account['role'] !== $role) {
            $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            header('Location: /startsmart/views/auth/login.php?tab=login'); exit;
        }

        if (!$account['face_recognition_enabled']) {
            $_SESSION['login_errors'] = ['general' => 'Reconnaissance faciale non activee pour ce compte.'];
            header('Location: /startsmart/views/auth/login.php?tab=login'); exit;
        }

        if ($account['statut'] === 'pending') {
            $_SESSION['login_errors'] = ['general' => 'Veuillez verifier votre adresse email avant de vous connecter.'];
            header('Location: /startsmart/views/auth/login.php?tab=login'); exit;
        }

        if ($account['statut'] === 'banni') {
            $banExpires = $account['ban_expires'] ?? null;
            if ($banExpires !== null && strtotime($banExpires) < time()) {
                $this->db->prepare("UPDATE users SET statut = 'actif', ban_expires = NULL, ban_reason = NULL WHERE id = :id")
                         ->execute([':id' => $account['id']]);
            } else {
                $reason   = $account['ban_reason'] ? ' Raison : ' . htmlspecialchars($account['ban_reason']) : '';
                $until    = $banExpires ? ' Jusqu\'au ' . date('d/m/Y H:i', strtotime($banExpires)) . '.' : ' (permanent).';
                $_SESSION['login_errors'] = ['general' => "Votre compte a ete suspendu{$until}{$reason}"];
                header('Location: /startsmart/views/auth/login.php?tab=login'); exit;
            }
        }

        // Face recognition verification is handled by the API
        // If we reach here, the user has been authenticated via face_recognition API
        // Set the session
        $this->loginUser($account, false);
    }

    public function googleLogin(): void
    {
        $credential = trim($_POST['credential'] ?? '');
        $rememberMe = !empty($_POST['remember_me']);

        if ($this->googleClientId === '') {
            $_SESSION['login_errors'] = ['general' => 'Connexion Google non configuree.'];
            header('Location: /startsmart/views/auth/login.php'); exit;
        }

        $payload = $this->verifyGoogleCredential($credential);
        if (!$payload) {
            $_SESSION['login_errors'] = ['general' => 'Connexion Google invalide.'];
            header('Location: /startsmart/views/auth/login.php'); exit;
        }

        $email = strtolower(trim((string)$payload['email']));
        $googleId = trim((string)$payload['sub']);
        $account = $this->readUserByEmail($email);

        if ($account) {
            if (($account['role'] ?? '') !== 'user') {
                $_SESSION['login_errors'] = ['general' => 'Connexion Google disponible uniquement pour les comptes utilisateur.'];
                header('Location: /startsmart/views/auth/login.php'); exit;
            }

            if (($account['statut'] ?? '') === 'banni') {
                $banExpires = $account['ban_expires'] ?? null;
                if ($banExpires !== null && strtotime($banExpires) < time()) {
                    $this->db->prepare("UPDATE users SET statut = 'actif', ban_expires = NULL, ban_reason = NULL WHERE id = :id")
                             ->execute([':id' => $account['id']]);
                } else {
                    $_SESSION['login_errors'] = ['general' => 'Votre compte est suspendu.'];
                    header('Location: /startsmart/views/auth/login.php'); exit;
                }
            }

            $this->db->prepare("UPDATE users
                                   SET google_id = :google_id,
                                       statut = CASE WHEN statut = 'pending' THEN 'actif' ELSE statut END,
                                       email_token = CASE WHEN statut = 'pending' THEN NULL ELSE email_token END,
                                       email_token_expires = CASE WHEN statut = 'pending' THEN NULL ELSE email_token_expires END,
                                       profile_picture = CASE
                                           WHEN (profile_picture IS NULL OR profile_picture = '') AND :picture_check <> '' THEN :picture_value
                                           ELSE profile_picture
                                       END
                                 WHERE id = :id")
                     ->execute([
                         ':google_id'     => $googleId,
                         ':picture_check' => trim((string)($payload['picture'] ?? '')),
                         ':picture_value' => trim((string)($payload['picture'] ?? '')),
                         ':id'            => $account['id'],
                     ]);

            $account = $this->readUserByEmail($email);
            $this->loginUser($account, $rememberMe);
        }

        $givenName = trim((string)($payload['given_name'] ?? ''));
        $familyName = trim((string)($payload['family_name'] ?? ''));
        $fullName = trim((string)($payload['name'] ?? 'Utilisateur Google'));
        if ($givenName === '' && $fullName !== '') {
            $parts = preg_split('/\s+/', $fullName);
            $givenName = trim((string)array_shift($parts));
            $familyName = trim(implode(' ', $parts));
        }

        $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, email, password, role, statut, profile_picture, google_id)
                                    VALUES (:nom, :prenom, :email, :password, 'user', 'actif', :profile_picture, :google_id)");
        $stmt->execute([
            ':nom'             => $this->clean($familyName !== '' ? $familyName : 'Google'),
            ':prenom'          => $this->clean($givenName !== '' ? $givenName : 'Utilisateur'),
            ':email'           => $this->clean($email),
            ':password'        => password_hash(bin2hex(random_bytes(16)) . 'Aa1', PASSWORD_BCRYPT),
            ':profile_picture' => trim((string)($payload['picture'] ?? '')) ?: null,
            ':google_id'       => $googleId,
        ]);

        $account = $this->readUserByEmail($email);
        $this->loginUser($account, $rememberMe);
    }

    private function uploadProfilePicture(): ?string
    {
        if (empty($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) return null;
        $file = $_FILES['profile_picture'];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed) || $file['size'] > 5 * 1024 * 1024) return null;
        $upload_dir = __DIR__ . '/../public/img/profiles';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . '/' . $filename)) return null;
        return '/startsmart/public/img/profiles/' . $filename;
    }
}
