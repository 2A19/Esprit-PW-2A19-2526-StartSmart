<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Startup.php';
require_once __DIR__ . '/../models/Validator.php';

/**
 * AuthController – Login / Logout / Register (Form-based, No JSON)
 */
class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // ── HELPER METHODS ─────────────────────────────────────────
    private function clean(string $v): string
    {
        return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
    }

    // ── DATABASE: READ USER BY EMAIL ───────────────────────────
    private function readUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    // ── DATABASE: READ STARTUP BY EMAIL ────────────────────────
    private function readStartupByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM startups WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    // ── DATABASE: TOUCH LAST LOGIN USER ────────────────────────
    private function touchLastLoginUser(int $id): void
    {
        $this->db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    // ── DATABASE: TOUCH LAST LOGIN STARTUP ─────────────────────
    private function touchLastLoginStartup(int $id): void
    {
        $this->db->prepare("UPDATE startups SET derniere_connexion=NOW() WHERE id=:id")
                 ->execute([':id' => $id]);
    }

    // ── DATABASE: EMAIL EXISTS USER ────────────────────────────
    private function emailExistsUser(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── DATABASE: EMAIL EXISTS STARTUP ─────────────────────────
    private function emailExistsStartup(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM startups WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── DATABASE: CREATE USER ──────────────────────────────────
    private function createUserDb(User $user): bool
    {
        $sql = "INSERT INTO users (nom, prenom, email, password, telephone, date_naissance, role, statut)
                VALUES (:nom, :prenom, :email, :password, :telephone, :date_naissance, :role, :statut)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom'            => $this->clean($user->getNom()),
            ':prenom'         => $this->clean($user->getPrenom()),
            ':email'          => $this->clean($user->getEmail()),
            ':password'       => password_hash($user->getPassword(), PASSWORD_BCRYPT),
            ':telephone'      => $user->getTelephone() ? $this->clean($user->getTelephone()) : null,
            ':date_naissance' => $user->getDateNaissance(),
            ':role'           => $user->getRole() ?? 'user',
            ':statut'         => $user->getStatut() ?? 'actif',
        ]);
    }

    // ── DATABASE: CREATE STARTUP ───────────────────────────────
    private function createStartupDb(Startup $startup): bool
    {
        $sql = "INSERT INTO startups
                    (nom_startup, nom_responsable, prenom_responsable, email, password,
                     telephone, secteur, site_web, stade, statut)
                VALUES
                    (:nom_startup, :nom_responsable, :prenom_responsable, :email, :password,
                     :telephone, :secteur, :site_web, :stade, :statut)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom_startup'        => $this->clean($startup->getNomStartup()),
            ':nom_responsable'    => $this->clean($startup->getNomResponsable()),
            ':prenom_responsable' => $this->clean($startup->getPrenomResponsable()),
            ':email'              => $this->clean($startup->getEmail()),
            ':password'           => password_hash($startup->getPassword(), PASSWORD_BCRYPT),
            ':telephone'          => $startup->getTelephone() ? $this->clean($startup->getTelephone()) : null,
            ':secteur'            => $startup->getSecteur() ? $this->clean($startup->getSecteur()) : null,
            ':site_web'           => $startup->getSiteWeb() ? $this->clean($startup->getSiteWeb()) : null,
            ':stade'              => $startup->getStade() ?? 'idee',
            ':statut'             => $startup->getStatut() ?? 'actif',
        ]);
    }

    // ── LOGIN ─────────────────────────────────────────────────
    public function login(): void
    {
        $d     = $this->getBody();
        $v     = new Validator();
        $email = trim($d['email']    ?? '');
        $pass  = trim($d['password'] ?? '');
        $role  = trim($d['role']     ?? '');

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

        // Chercher le compte selon le rôle
        $account = null;
        $type    = '';

        if ($role === 'user' || $role === 'admin') {
            $account = $this->readUserByEmail($email);
            if ($account && $account['role'] !== $role) $account = null;
            $type = 'user';
        } elseif ($role === 'startup') {
            $account = $this->readStartupByEmail($email);
            $type = 'startup';
        }

        if (!$account || !password_verify($pass, $account['password'])) {
            $_SESSION['login_errors'] = ['general' => 'Email ou mot de passe incorrect.'];
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        if (isset($account['statut']) && $account['statut'] === 'banni') {
            $_SESSION['login_errors'] = ['general' => 'Votre compte a été suspendu. Contactez l\'administrateur.'];
            header('Location: /startsmart/views/auth/login.php');
            exit;
        }

        // Mettre à jour dernière connexion
        if ($type === 'user')    $this->touchLastLoginUser($account['id']);
        else                     $this->touchLastLoginStartup($account['id']);

        // Démarrer la session
        $_SESSION['user_id']   = $account['id'];
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $type === 'startup'
            ? $account['nom_startup']
            : $account['prenom'] . ' ' . $account['nom'];
        $_SESSION['user_type'] = $type;

        $redirect = $role === 'admin' ? '/startsmart/views/back/dashboard.php' : '/startsmart/views/front/dashboard.php';
        header('Location: ' . $redirect);
        exit;
    }

    // ── REGISTER USER ─────────────────────────────────────────
    public function registerUser(): void
    {
        $d = $this->getBody();
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
            $_SESSION['reg_errors'] = $v->getErrors();
            $_SESSION['reg_form_data'] = $d;
            header('Location: /startsmart/views/auth/login.php?tab=register');
            exit;
        }

        if ($this->emailExistsUser($d['email'])) {
            $_SESSION['reg_errors'] = ['email' => 'Cet email est déjà utilisé.'];
            $_SESSION['reg_form_data'] = $d;
            header('Location: /startsmart/views/auth/login.php?tab=register');
            exit;
        }

        $user = new User(
            null,
            $d['nom'],
            $d['prenom'],
            $d['email'],
            $d['password'],
            $d['telephone'] ?? null,
            $d['date_naissance'] ?? null,
            'user',
            'actif'
        );

        $ok = $this->createUserDb($user);
        
        if (!$ok) {
            $_SESSION['reg_errors'] = ['general' => 'Erreur lors de la création du compte.'];
            $_SESSION['reg_form_data'] = $d;
            header('Location: /startsmart/views/auth/login.php?tab=register');
            exit;
        }
        
        $_SESSION['reg_success'] = 'Compte créé avec succès. Vous pouvez vous connecter.';
        header('Location: /startsmart/views/auth/login.php');
        exit;
    }

    // ── REGISTER STARTUP ──────────────────────────────────────
    public function registerStartup(): void
    {
        $d = $this->getBody();
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
            $_SESSION['reg_errors'] = $v->getErrors();
            $_SESSION['reg_form_data'] = $d;
            header('Location: /startsmart/views/auth/login.php?tab=register&type=startup');
            exit;
        }

        if ($this->emailExistsStartup($d['email'])) {
            $_SESSION['reg_errors'] = ['email' => 'Cet email est déjà utilisé.'];
            $_SESSION['reg_form_data'] = $d;
            header('Location: /startsmart/views/auth/login.php?tab=register&type=startup');
            exit;
        }

        $startup = new Startup(
            null,
            $d['nom_startup'],
            $d['nom_responsable'],
            $d['prenom_responsable'],
            $d['email'],
            $d['password'],
            $d['telephone'] ?? null,
            $d['secteur'],
            $d['site_web'] ?? null,
            $d['stade'] ?? 'idee',
            'actif'
        );

        $ok = $this->createStartupDb($startup);
        
        if (!$ok) {
            $_SESSION['reg_errors'] = ['general' => 'Erreur lors de la création.'];
            $_SESSION['reg_form_data'] = $d;
            header('Location: /startsmart/views/auth/login.php?tab=register&type=startup');
            exit;
        }
        
        $_SESSION['reg_success'] = 'Compte startup créé. En attente de vérification.';
        header('Location: /startsmart/views/auth/login.php');
        exit;
    }

    // ── LOGOUT ────────────────────────────────────────────────
    public function logout(): void
    {
        session_destroy();
        header('Location: /startsmart/views/auth/login.php');
        exit;
    }

    // ── Helpers ───────────────────────────────────────────────
    private function getBody(): array
    {
        return $_POST;
    }
}
