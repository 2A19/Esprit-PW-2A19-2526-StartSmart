<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Validator.php';

class UserController
{
    private PDO $db;
    private Validator $v;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->v  = new Validator();
    }

    // ── HELPER METHODS ─────────────────────────────────────────
    private function clean(string $v): string
    {
        return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
    }

    private function buildWhere(string $role, string $statut, string $search): array
    {
        $conds = []; $params = [];
        
        // Handle comma-separated roles
        if ($role !== '') { 
            $roles = array_map('trim', explode(',', $role));
            if (count($roles) > 1) {
                $placeholders = [];
                foreach ($roles as $i => $r) {
                    $key = ":role{$i}";
                    $placeholders[] = $key;
                    $params[$key] = $r;
                }
                $conds[] = 'role IN (' . implode(',', $placeholders) . ')';
            } else {
                $conds[] = 'role = :role';
                $params[':role'] = $role;
            }
        }
        
        if ($statut !== '') { $conds[] = 'statut = :statut'; $params[':statut'] = $statut; }
        if ($search !== '') {
            $searchTerm = '%' . $search . '%';
            $conds[] = '(nom LIKE :s_nom OR prenom LIKE :s_prenom OR email LIKE :s_email OR nom_startup LIKE :s_startup)';
            $params[':s_nom'] = $searchTerm;
            $params[':s_prenom'] = $searchTerm;
            $params[':s_email'] = $searchTerm;
            $params[':s_startup'] = $searchTerm;
        }
        return [$conds ? 'WHERE ' . implode(' AND ', $conds) : '', $params];
    }

    // ── DATABASE: CREATE USER ──────────────────────────────────
    private function createUser(User $user): bool
    {
        $sql = "INSERT INTO users (nom, prenom, email, password, telephone, date_naissance, role, statut,
                                   nom_startup, nom_responsable, prenom_responsable, secteur, site_web, stade)
                VALUES (:nom, :prenom, :email, :password, :telephone, :date_naissance, :role, :statut,
                        :nom_startup, :nom_responsable, :prenom_responsable, :secteur, :site_web, :stade)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom'                => $this->clean($user->getNom()),
            ':prenom'             => $this->clean($user->getPrenom()),
            ':email'              => $this->clean($user->getEmail()),
            ':password'           => password_hash($user->getPassword(), PASSWORD_BCRYPT),
            ':telephone'          => $user->getTelephone() ? $this->clean($user->getTelephone()) : null,
            ':date_naissance'     => $user->getDateNaissance(),
            ':role'               => $user->getRole() ?? 'user',
            ':statut'             => $user->getStatut() ?? 'actif',
            ':nom_startup'        => $user->getNomStartup() ? $this->clean($user->getNomStartup()) : null,
            ':nom_responsable'    => $user->getNomResponsable() ? $this->clean($user->getNomResponsable()) : null,
            ':prenom_responsable' => $user->getPrenomResponsable() ? $this->clean($user->getPrenomResponsable()) : null,
            ':secteur'            => $user->getSecteur() ? $this->clean($user->getSecteur()) : null,
            ':site_web'           => $user->getSiteWeb() ? $this->clean($user->getSiteWeb()) : null,
            ':stade'              => $user->getStade() ?? 'idee',
        ]);
    }

    // ── DATABASE: READ ALL USERS ───────────────────────────────
    private function readAllUsers(int $limit = 10, int $offset = 0, string $role = '', string $statut = '', string $search = '', string $sort = 'id DESC'): array
    {
        [$where, $params] = $this->buildWhere($role, $statut, $search);
        $sql = "SELECT id, nom, prenom, email, telephone, role, statut, date_inscription, nom_startup, profile_picture, ban_expires, ban_reason
                FROM users {$where} ORDER BY {$sort} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── DATABASE: COUNT USERS ──────────────────────────────────
    private function countUsers(string $role = '', string $statut = '', string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($role, $statut, $search);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // ── DATABASE: READ ONE USER ────────────────────────────────
    private function readOneUser(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ── DATABASE: READ BY EMAIL ────────────────────────────────
    private function readByEmailUser(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    // ── DATABASE: UPDATE USER ──────────────────────────────────
    private function updateUserDb(int $id, User $user): bool
    {
        $pwPart = $user->getPassword() ? ', password = :password' : '';
        $sql = "UPDATE users SET
                    nom            = :nom,
                    prenom         = :prenom,
                    email          = :email,
                    telephone      = :telephone,
                    date_naissance = :date_naissance,
                    role           = :role,
                    statut         = :statut,
                    nom_startup    = :nom_startup,
                    nom_responsable = :nom_responsable,
                    prenom_responsable = :prenom_responsable,
                    secteur        = :secteur,
                    site_web       = :site_web,
                    stade          = :stade
                    {$pwPart}
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $params = [
            ':nom'                => $this->clean($user->getNom()),
            ':prenom'             => $this->clean($user->getPrenom()),
            ':email'              => $this->clean($user->getEmail()),
            ':telephone'          => $user->getTelephone() ? $this->clean($user->getTelephone()) : null,
            ':date_naissance'     => $user->getDateNaissance(),
            ':role'               => $user->getRole() ?? 'user',
            ':statut'             => $user->getStatut() ?? 'actif',
            ':nom_startup'        => $user->getNomStartup() ? $this->clean($user->getNomStartup()) : null,
            ':nom_responsable'    => $user->getNomResponsable() ? $this->clean($user->getNomResponsable()) : null,
            ':prenom_responsable' => $user->getPrenomResponsable() ? $this->clean($user->getPrenomResponsable()) : null,
            ':secteur'            => $user->getSecteur() ? $this->clean($user->getSecteur()) : null,
            ':site_web'           => $user->getSiteWeb() ? $this->clean($user->getSiteWeb()) : null,
            ':stade'              => $user->getStade() ?? 'idee',
            ':id'                 => $id,
        ];
        if ($user->getPassword()) {
            $params[':password'] = password_hash($user->getPassword(), PASSWORD_BCRYPT);
        }
        return $stmt->execute($params);
    }

    // ── DATABASE: DELETE USER ──────────────────────────────────
    private function deleteUserDb(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── DATABASE: CHECK EMAIL EXISTS ──────────────────────────
    private function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id"
        );
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── DATABASE: UPDATE LAST LOGIN ────────────────────────────
    private function touchLastLogin(int $id): void
    {
        $this->db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    // ── DATABASE: GET STATS ────────────────────────────────────
    private function getUserStats(): array
    {
        return $this->db->query(
            "SELECT COUNT(*) AS total,
                    SUM(role='user')     AS users,
                    SUM(role='startup')  AS startups,
                    SUM(role='admin')    AS admins,
                    SUM(statut='actif')   AS actifs,
                    SUM(statut='inactif') AS inactifs,
                    SUM(statut='banni')   AS bannis,
                    SUM(statut='verifie') AS verifiees
             FROM users"
        )->fetch();
    }

    // ── CONTROLLER: LIST USERS (excluding startups) ────────────
    public function listUsers(): void
    {
        $limit  = (int)($_GET['u_limit']  ?? 8);
        $page   = max(1, (int)($_GET['u_page']   ?? 1));
        $offset = ($page - 1) * $limit;
        $role   = $_GET['u_role']   ?? '';
        $statut = $_GET['u_statut'] ?? '';
        $search = $_GET['u_search'] ?? '';
        $sort   = $_GET['u_sort']   ?? 'id DESC';
        
        // Validate sort parameter to prevent SQL injection
        $allowed_sorts = ['id ASC', 'id DESC', 'nom ASC', 'nom DESC', 'prenom ASC', 'prenom DESC', 'email ASC', 'email DESC', 'role ASC', 'role DESC', 'statut ASC', 'statut DESC'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'id DESC';

        // Filter out startups when listing regular users
        if ($role === '') $role = 'user,admin';
        
        $rows  = $this->readAllUsers($limit, $offset, $role, $statut, $search, $sort);
        $total = $this->countUsers($role, $statut, $search);
        $stats = $this->getUserStats();

        $_SESSION['users_list'] = [
            'data'    => $rows,
            'total'   => $total,
            'pages'   => (int)ceil($total / $limit),
            'page'    => $page,
            'sort'    => $sort,
            'search'  => $search,
            'stats'   => $stats,
        ];
    }

    // ── CONTROLLER: LIST STARTUPS (role='startup') ─────────────
    public function listStartups(): void
    {
        $limit  = (int)($_GET['s_limit']  ?? 8);
        $page   = max(1, (int)($_GET['s_page']   ?? 1));
        $offset = ($page - 1) * $limit;
        $statut = $_GET['s_statut'] ?? '';
        $search = $_GET['s_search'] ?? '';
        $sort   = $_GET['s_sort']   ?? 'id DESC';
        
        // Validate sort parameter to prevent SQL injection
        $allowed_sorts = ['id ASC', 'id DESC', 'nom_startup ASC', 'nom_startup DESC', 'nom_responsable ASC', 'nom_responsable DESC', 'email ASC', 'email DESC', 'secteur ASC', 'secteur DESC', 'statut ASC', 'statut DESC'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'id DESC';

        // Filter only startups
        $rows  = $this->readAllUsers($limit, $offset, 'startup', $statut, $search, $sort);
        $total = $this->countUsers('startup', $statut, $search);
        $stats = $this->getUserStats();

        $_SESSION['startups_list'] = [
            'data'    => $rows,
            'total'   => $total,
            'pages'   => (int)ceil($total / $limit),
            'page'    => $page,
            'sort'    => $sort,
            'search'  => $search,
            'stats'   => $stats,
        ];
    }

    // ── CONTROLLER: GET USER ───────────────────────────────────
    public function getUser(int $id): void
    {
        if ($id <= 0) {
            $_SESSION['user_error'] = 'ID invalide.';
            return;
        }

        $user = $this->readOneUser($id);
        if (!$user) {
            $_SESSION['user_error'] = 'Utilisateur introuvable.';
            return;
        }

        $_SESSION['user_detail'] = $user;
    }

    // ── CONTROLLER: GET STARTUP ────────────────────────────────
    public function getStartup(int $id): void
    {
        if ($id <= 0) {
            $_SESSION['startup_error'] = 'ID invalide.';
            return;
        }

        $startup = $this->readOneUser($id);
        if (!$startup || $startup['role'] !== 'startup') {
            $_SESSION['startup_error'] = 'Startup introuvable.';
            return;
        }

        $_SESSION['startup_detail'] = $startup;
    }

    // ── CONTROLLER: CREATE USER ────────────────────────────────
    public function createUserAction(array $d): void
    {
        $this->v
            ->required('nom',      $d['nom']      ?? '', 'Le nom')
            ->minLen  ('nom',      $d['nom']       ?? '', 2, 'Le nom')
            ->required('prenom',   $d['prenom']   ?? '', 'Le prénom')
            ->minLen  ('prenom',   $d['prenom']   ?? '', 2, 'Le prénom')
            ->required('email',    $d['email']    ?? '', "L'email")
            ->email   ('email',    $d['email']    ?? '')
            ->required('password', $d['password'] ?? '', 'Le mot de passe')
            ->password('password', $d['password'] ?? '')
            ->confirm ('password', $d['password'] ?? '', $d['password_confirm'] ?? '')
            ->phone   ('telephone', $d['telephone'] ?? null)
            ->date    ('date_naissance', $d['date_naissance'] ?? null, 'Date de naissance')
            ->inList  ('role',   $d['role']   ?? 'user', ['user','startup','admin'],               'Le rôle')
            ->inList  ('statut', $d['statut'] ?? 'actif', ['actif','inactif','banni','verifie'],   'Le statut');

        if ($this->v->fails()) {
            $_SESSION['form_errors'] = $this->v->getErrors();
            $_SESSION['form_data'] = $d;
            return;
        }

        if ($this->emailExists($d['email'])) {
            $_SESSION['form_errors'] = ['email' => 'Cet email est déjà utilisé.'];
            $_SESSION['form_data'] = $d;
            return;
        }

        $user = new User(
            null,
            $d['nom'],
            $d['prenom'],
            $d['email'],
            $d['password'],
            $d['telephone'] ?? null,
            $d['date_naissance'] ?? null,
            $d['role'] ?? 'user',
            $d['statut'] ?? 'actif'
        );

        $ok = $this->createUser($user);
        if ($ok) {
            $_SESSION['success'] = 'Utilisateur créé avec succès.';
            unset($_SESSION['form_data'], $_SESSION['form_errors']);
        } else {
            $_SESSION['form_errors'] = ['general' => 'Erreur lors de la création.'];
        }
    }

    // ── CONTROLLER: UPDATE USER ────────────────────────────────
    public function updateUserAction(int $id, array $d): void
    {
        if ($id <= 0) {
            $_SESSION['form_errors'] = ['general' => 'ID invalide.'];
            return;
        }
        if (!$this->readOneUser($id)) {
            $_SESSION['form_errors'] = ['general' => 'Utilisateur introuvable.'];
            return;
        }

        $this->v
            ->required('nom',    $d['nom']    ?? '', 'Le nom')
            ->minLen  ('nom',    $d['nom']    ?? '', 2, 'Le nom')
            ->required('prenom', $d['prenom'] ?? '', 'Le prénom')
            ->minLen  ('prenom', $d['prenom'] ?? '', 2, 'Le prénom')
            ->required('email',  $d['email']  ?? '', "L'email")
            ->email   ('email',  $d['email']  ?? '')
            ->phone   ('telephone', $d['telephone'] ?? null)
            ->date    ('date_naissance', $d['date_naissance'] ?? null, 'Date de naissance')
            ->inList  ('role',   $d['role']   ?? 'user', ['user','startup','admin'],             'Le rôle')
            ->inList  ('statut', $d['statut'] ?? 'actif', ['actif','inactif','banni','verifie'], 'Le statut');

        if (!empty($d['password'])) {
            $this->v->password('password', $d['password'])
                    ->confirm ('password', $d['password'], $d['password_confirm'] ?? '');
        }

        if ($this->v->fails()) {
            $_SESSION['form_errors'] = $this->v->getErrors();
            $_SESSION['form_data'] = $d;
            return;
        }

        if ($this->emailExists($d['email'], $id)) {
            $_SESSION['form_errors'] = ['email' => 'Cet email est déjà utilisé par un autre compte.'];
            $_SESSION['form_data'] = $d;
            return;
        }

        $user = new User(
            $id,
            $d['nom'],
            $d['prenom'],
            $d['email'],
            !empty($d['password']) ? $d['password'] : null,
            $d['telephone'] ?? null,
            $d['date_naissance'] ?? null,
            $d['role'] ?? 'user',
            $d['statut'] ?? 'actif',
            null,
            null,
            $d['nom_startup'] ?? null,
            $d['nom_responsable'] ?? null,
            $d['prenom_responsable'] ?? null,
            $d['secteur'] ?? null,
            $d['site_web'] ?? null,
            $d['stade'] ?? 'idee'
        );

        $ok = $this->updateUserDb($id, $user);
        if ($ok) {
            $_SESSION['success'] = 'Utilisateur mis à jour.';
            unset($_SESSION['form_data'], $_SESSION['form_errors']);
        } else {
            $_SESSION['form_errors'] = ['general' => 'Erreur lors de la mise à jour.'];
        }
    }

    // ── CONTROLLER: DELETE USER ────────────────────────────────
    public function deleteUserAction(int $id): void
    {
        if ($id <= 0) {
            $_SESSION['form_errors'] = ['general' => 'ID invalide.'];
            return;
        }
        if (!$this->readOneUser($id)) {
            $_SESSION['form_errors'] = ['general' => 'Utilisateur introuvable.'];
            return;
        }

        $ok = $this->deleteUserDb($id);
        if ($ok) {
            $_SESSION['success'] = 'Utilisateur supprimé.';
        } else {
            $_SESSION['form_errors'] = ['general' => 'Erreur lors de la suppression.'];
        }
    }

    // ── CONTROLLER: UPDATE STARTUP ─────────────────────────────
    public function updateStartupAction(int $id, array $d): void
    {
        if ($id <= 0) {
            $_SESSION['form_errors'] = ['general' => 'ID invalide.'];
            return;
        }
        $startup = $this->readOneUser($id);
        if (!$startup || $startup['role'] !== 'startup') {
            $_SESSION['form_errors'] = ['general' => 'Startup introuvable.'];
            return;
        }

        $this->v
            ->required('nom_startup',       $d['nom_startup']       ?? '', 'Le nom')
            ->minLen  ('nom_startup',       $d['nom_startup']       ?? '', 2, 'Le nom')
            ->required('nom_responsable',   $d['nom_responsable']   ?? '', 'Le nom du responsable')
            ->minLen  ('nom_responsable',   $d['nom_responsable']   ?? '', 2, 'Le nom du responsable')
            ->required('prenom_responsable', $d['prenom_responsable'] ?? '', 'Le prénom du responsable')
            ->minLen  ('prenom_responsable', $d['prenom_responsable'] ?? '', 2, 'Le prénom du responsable')
            ->required('email',             $d['email']             ?? '', "L'email")
            ->email   ('email',             $d['email']             ?? '')
            ->phone   ('telephone',         $d['telephone']         ?? null)
            ->inList  ('secteur',           $d['secteur']           ?? '', ['tech','sante','fintech','logistique','retail','autre'], 'Le secteur')
            ->inList  ('stade',             $d['stade']             ?? 'idee', ['idee','prototype','mvp','croissance','scale'], 'Le stade')
            ->inList  ('statut',            $d['statut']            ?? 'actif', ['actif','inactif','banni','verifie'], 'Le statut');

        if (!empty($d['password'])) {
            $this->v->password('password', $d['password'])
                    ->confirm ('password', $d['password'], $d['password_confirm'] ?? '');
        }

        if ($this->v->fails()) {
            $_SESSION['form_errors'] = $this->v->getErrors();
            $_SESSION['form_data'] = $d;
            return;
        }

        if ($this->emailExists($d['email'], $id)) {
            $_SESSION['form_errors'] = ['email' => 'Cet email est déjà utilisé par une autre startup.'];
            $_SESSION['form_data'] = $d;
            return;
        }

        $user = new User(
            $id,
            $d['nom_responsable'],
            $d['prenom_responsable'],
            $d['email'],
            !empty($d['password']) ? $d['password'] : null,
            $d['telephone'] ?? null,
            null, // date_naissance not used for startups
            'startup',
            $d['statut'] ?? 'actif',
            null,
            null,
            $d['nom_startup'],
            $d['nom_responsable'],
            $d['prenom_responsable'],
            $d['secteur'] ?? null,
            $d['site_web'] ?? null,
            $d['stade'] ?? 'idee'
        );

        $ok = $this->updateUserDb($id, $user);
        if ($ok) {
            $_SESSION['success'] = 'Startup mise à jour.';
            unset($_SESSION['form_data'], $_SESSION['form_errors']);
        } else {
            $_SESSION['form_errors'] = ['general' => 'Erreur lors de la mise à jour.'];
        }
    }

    // ── CONTROLLER: BAN USER ────────────────────────────────────
    public function banUserAction(int $id, string $type, int $durationHours, string $reason): void
    {
        if ($id <= 0 || !$this->readOneUser($id)) {
            $_SESSION['form_errors'] = ['general' => 'Utilisateur introuvable.']; return;
        }
        if ($type === 'timed' && $durationHours > 0) {
            $expires = date('Y-m-d H:i:s', strtotime("+{$durationHours} hours"));
            $this->db->prepare("UPDATE users SET statut = 'banni', ban_expires = :exp, ban_reason = :reason WHERE id = :id")
                     ->execute([':exp' => $expires, ':reason' => $reason ?: null, ':id' => $id]);
        } else {
            $this->db->prepare("UPDATE users SET statut = 'banni', ban_expires = NULL, ban_reason = :reason WHERE id = :id")
                     ->execute([':reason' => $reason ?: null, ':id' => $id]);
        }
        $_SESSION['success'] = 'Utilisateur banni.';
    }

    // ── CONTROLLER: UNBAN USER ──────────────────────────────────
    public function unbanUserAction(int $id): void
    {
        if ($id <= 0) { $_SESSION['form_errors'] = ['general' => 'ID invalide.']; return; }
        $this->db->prepare("UPDATE users SET statut = 'actif', ban_expires = NULL, ban_reason = NULL WHERE id = :id")
                 ->execute([':id' => $id]);
        $_SESSION['success'] = 'Utilisateur debanni.';
    }
}
