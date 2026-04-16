<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Startup.php';
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
        if ($role   !== '') { $conds[] = 'role = :role';     $params[':role']   = $role; }
        if ($statut !== '') { $conds[] = 'statut = :statut'; $params[':statut'] = $statut; }
        if ($search !== '') {
            $conds[] = '(nom LIKE :s OR prenom LIKE :s OR email LIKE :s)';
            $params[':s'] = '%' . $search . '%';
        }
        return [$conds ? 'WHERE ' . implode(' AND ', $conds) : '', $params];
    }

    // ── DATABASE: CREATE USER ──────────────────────────────────
    private function createUser(User $user): bool
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

    // ── DATABASE: READ ALL USERS ───────────────────────────────
    private function readAllUsers(int $limit = 10, int $offset = 0, string $role = '', string $statut = '', string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($role, $statut, $search);
        $sql = "SELECT id, nom, prenom, email, telephone, role, statut, date_inscription
                FROM users {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset";
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
            "SELECT id, nom, prenom, email, telephone, date_naissance, role, statut, date_inscription FROM users WHERE id = :id"
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
                    statut         = :statut
                    {$pwPart}
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $params = [
            ':nom'            => $this->clean($user->getNom()),
            ':prenom'         => $this->clean($user->getPrenom()),
            ':email'          => $this->clean($user->getEmail()),
            ':telephone'      => $user->getTelephone() ? $this->clean($user->getTelephone()) : null,
            ':date_naissance' => $user->getDateNaissance(),
            ':role'           => $user->getRole() ?? 'user',
            ':statut'         => $user->getStatut() ?? 'actif',
            ':id'             => $id,
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
    private function emailExistsUser(string $email, int $excludeId = 0): bool
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
                    SUM(statut='actif')   AS actifs,
                    SUM(statut='inactif') AS inactifs,
                    SUM(statut='banni')   AS bannis
             FROM users"
        )->fetch();
    }

    // ── CONTROLLER: LIST USERS ─────────────────────────────────
    public function listUsers(): void
    {
        $limit  = (int)($_GET['limit']  ?? 8);
        $page   = max(1, (int)($_GET['page']   ?? 1));
        $offset = ($page - 1) * $limit;
        $role   = $_GET['role']   ?? '';
        $statut = $_GET['statut'] ?? '';
        $search = $_GET['search'] ?? '';

        $rows  = $this->readAllUsers($limit, $offset, $role, $statut, $search);
        $total = $this->countUsers($role, $statut, $search);
        $stats = $this->getUserStats();

        $_SESSION['users_list'] = [
            'data'    => $rows,
            'total'   => $total,
            'pages'   => (int)ceil($total / $limit),
            'page'    => $page,
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
            ->inList  ('role',   $d['role']   ?? 'user', ['user','admin'],               'Le rôle')
            ->inList  ('statut', $d['statut'] ?? 'actif', ['actif','inactif','banni'],   'Le statut');

        if ($this->v->fails()) {
            $_SESSION['form_errors'] = $this->v->getErrors();
            $_SESSION['form_data'] = $d;
            return;
        }

        if ($this->emailExistsUser($d['email'])) {
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
            ->inList  ('role',   $d['role']   ?? 'user', ['user','admin'],             'Le rôle')
            ->inList  ('statut', $d['statut'] ?? 'actif', ['actif','inactif','banni'], 'Le statut');

        if (!empty($d['password'])) {
            $this->v->password('password', $d['password'])
                    ->confirm ('password', $d['password'], $d['password_confirm'] ?? '');
        }

        if ($this->v->fails()) {
            $_SESSION['form_errors'] = $this->v->getErrors();
            $_SESSION['form_data'] = $d;
            return;
        }

        if ($this->emailExistsUser($d['email'], $id)) {
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
            $d['statut'] ?? 'actif'
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

    // ── DATABASE: CREATE STARTUP ───────────────────────────────
    private function createStartup(Startup $startup): bool
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

    // ── DATABASE: READ ALL STARTUPS ────────────────────────────
    private function readAllStartups(int $limit = 10, int $offset = 0, string $statut = '', string $search = ''): array
    {
        $sql = "SELECT * FROM startups WHERE 1=1";
        
        if ($statut) {
            $sql .= " AND statut = '" . $this->clean($statut) . "'";
        }
        
        if ($search) {
            $clean = $this->clean($search);
            $sql .= " AND (nom_startup LIKE '%$clean%' OR email LIKE '%$clean%')";
        }
        
        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── DATABASE: COUNT STARTUPS ───────────────────────────────
    private function countStartups(string $statut = '', string $search = ''): int
    {
        $sql = "SELECT COUNT(*) FROM startups WHERE 1=1";
        
        if ($statut) {
            $sql .= " AND statut = '" . $this->clean($statut) . "'";
        }
        
        if ($search) {
            $clean = $this->clean($search);
            $sql .= " AND (nom_startup LIKE '%$clean%' OR email LIKE '%$clean%')";
        }
        
        return (int)$this->db->query($sql)->fetchColumn();
    }

    // ── DATABASE: READ ONE STARTUP ─────────────────────────────
    private function readOneStartup(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM startups WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ── DATABASE: READ BY EMAIL STARTUP ────────────────────────
    private function readByEmailStartup(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM startups WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    // ── DATABASE: CHECK EMAIL EXISTS STARTUP ───────────────────
    private function emailExistsStartup(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM startups WHERE email=:email AND id!=:id");
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── DATABASE: UPDATE STARTUP ───────────────────────────────
    private function updateStartupDb(int $id, Startup $startup): bool
    {
        $pwPart = $startup->getPassword() ? ', password = :password' : '';
        $stmt = $this->db->prepare(
            "UPDATE startups SET
                nom_startup=:nom_startup, nom_responsable=:nom_responsable,
                prenom_responsable=:prenom_responsable, email=:email,
                telephone=:telephone, secteur=:secteur, site_web=:site_web,
                stade=:stade, statut=:statut {$pwPart}
             WHERE id=:id"
        );
        $params = [
            ':nom_startup'        => $this->clean($startup->getNomStartup()),
            ':nom_responsable'    => $this->clean($startup->getNomResponsable()),
            ':prenom_responsable' => $this->clean($startup->getPrenomResponsable()),
            ':email'              => $this->clean($startup->getEmail()),
            ':telephone'          => $startup->getTelephone() ? $this->clean($startup->getTelephone()) : null,
            ':secteur'            => $startup->getSecteur() ? $this->clean($startup->getSecteur()) : null,
            ':site_web'           => $startup->getSiteWeb() ? $this->clean($startup->getSiteWeb()) : null,
            ':stade'              => $startup->getStade(),
            ':statut'             => $startup->getStatut(),
            ':id'                 => $id,
        ];
        if ($startup->getPassword()) $params[':password'] = password_hash($startup->getPassword(), PASSWORD_BCRYPT);
        return $stmt->execute($params);
    }

    // ── DATABASE: DELETE STARTUP ───────────────────────────────
    private function deleteStartupDb(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM startups WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── DATABASE: TOUCH LAST LOGIN STARTUP ─────────────────────
    private function touchLastLoginStartup(int $id): void
    {
        $this->db->prepare("UPDATE startups SET derniere_connexion=NOW() WHERE id=:id")
                 ->execute([':id' => $id]);
    }

    // ── DATABASE: GET STATS STARTUP ────────────────────────────
    private function getStartupStats(): array
    {
        return $this->db->query(
            "SELECT COUNT(*) AS total,
                    SUM(statut='actif')   AS actifs,
                    SUM(statut='verifie') AS verifiees
             FROM startups"
        )->fetch();
    }

    // ── CONTROLLER: LIST STARTUPS ──────────────────────────────
    public function listStartups(): void
    {
        $limit  = (int)($_GET['limit']  ?? 8);
        $page   = max(1, (int)($_GET['page']   ?? 1));
        $offset = ($page - 1) * $limit;
        $statut = $_GET['statut'] ?? '';
        $search = $_GET['search'] ?? '';

        $rows  = $this->readAllStartups($limit, $offset, $statut, $search);
        $total = $this->countStartups($statut, $search);
        $stats = $this->getStartupStats();

        $_SESSION['startups_list'] = [
            'data'    => $rows,
            'total'   => $total,
            'pages'   => (int)ceil($total / $limit),
            'page'    => $page,
            'stats'   => $stats,
        ];
    }

    // ── CONTROLLER: GET STARTUP ────────────────────────────────
    public function getStartup(int $id): void
    {
        if ($id <= 0) {
            $_SESSION['startup_error'] = 'ID invalide.';
            return;
        }

        $startup = $this->readOneStartup($id);
        if (!$startup) {
            $_SESSION['startup_error'] = 'Startup introuvable.';
            return;
        }

        $_SESSION['startup_detail'] = $startup;
    }

    // ── CONTROLLER: UPDATE STARTUP ─────────────────────────────
    public function updateStartupAction(int $id, array $d): void
    {
        if ($id <= 0) {
            $_SESSION['form_errors'] = ['general' => 'ID invalide.'];
            return;
        }
        if (!$this->readOneStartup($id)) {
            $_SESSION['form_errors'] = ['general' => 'Startup introuvable.'];
            return;
        }

        $this->v
            ->required('nom_startup',      $d['nom_startup']      ?? '', 'Le nom')
            ->minLen  ('nom_startup',      $d['nom_startup']      ?? '', 2, 'Le nom')
            ->required('nom_responsable',  $d['nom_responsable']  ?? '', 'Le nom du responsable')
            ->minLen  ('nom_responsable',  $d['nom_responsable']  ?? '', 2, 'Le nom du responsable')
            ->required('prenom_responsable', $d['prenom_responsable'] ?? '', 'Le prénom du responsable')
            ->minLen  ('prenom_responsable', $d['prenom_responsable'] ?? '', 2, 'Le prénom du responsable')
            ->required('email',            $d['email']            ?? '', "L'email")
            ->email   ('email',            $d['email']            ?? '')
            ->phone   ('telephone',        $d['telephone']        ?? null)
            ->inList  ('secteur',          $d['secteur']          ?? '', ['tech','sante','fintech','logistique','retail','autre'], 'Le secteur')
            ->inList  ('stade',            $d['stade']            ?? 'idee', ['idee','MVP','beta','prod'], 'Le stade')
            ->inList  ('statut',           $d['statut']           ?? 'actif', ['actif','inactif','banni'], 'Le statut');

        if (!empty($d['password'])) {
            $this->v->password('password', $d['password'])
                    ->confirm ('password', $d['password'], $d['password_confirm'] ?? '');
        }

        if ($this->v->fails()) {
            $_SESSION['form_errors'] = $this->v->getErrors();
            $_SESSION['form_data'] = $d;
            return;
        }

        if ($this->emailExistsStartup($d['email'], $id)) {
            $_SESSION['form_errors'] = ['email' => 'Cet email est déjà utilisé par une autre startup.'];
            $_SESSION['form_data'] = $d;
            return;
        }

        $startup = new Startup(
            $id,
            $d['nom_startup'],
            $d['nom_responsable'],
            $d['prenom_responsable'],
            $d['email'],
            !empty($d['password']) ? $d['password'] : null,
            $d['telephone'] ?? null,
            $d['secteur'] ?? null,
            $d['site_web'] ?? null,
            $d['stade'] ?? 'idee',
            $d['statut'] ?? 'actif'
        );

        $ok = $this->updateStartupDb($id, $startup);
        if ($ok) {
            $_SESSION['success'] = 'Startup mise à jour.';
            unset($_SESSION['form_data'], $_SESSION['form_errors']);
        } else {
            $_SESSION['form_errors'] = ['general' => 'Erreur lors de la mise à jour.'];
        }
    }

    // ── CONTROLLER: DELETE STARTUP ─────────────────────────────
    public function deleteStartupAction(int $id): void
    {
        if ($id <= 0) {
            $_SESSION['form_errors'] = ['general' => 'ID invalide.'];
            return;
        }
        if (!$this->readOneStartup($id)) {
            $_SESSION['form_errors'] = ['general' => 'Startup introuvable.'];
            return;
        }

        $ok = $this->deleteStartupDb($id);
        if ($ok) {
            $_SESSION['success'] = 'Startup supprimée.';
        } else {
            $_SESSION['form_errors'] = ['general' => 'Erreur lors de la suppression.'];
        }
    }

}


