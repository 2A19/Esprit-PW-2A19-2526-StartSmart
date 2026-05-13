<?php
/**
 * Integrated Auth helpers.
 * Login delegates to the imported StartSmartrh/RH user controller.
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/session.php';

function normalizedRole(?string $role): string {
    return strtolower(trim((string) $role));
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function isAdmin(): bool {
    if (isLoggedIn()) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => currentUserId()]);
            $role = $stmt->fetchColumn();
            if ($role !== false) {
                $_SESSION['user_role'] = $role;
                return in_array(normalizedRole($role), ['admin', 'rh'], true);
            }
        } catch (Exception $e) {
            // Fall back to the session role if the database is unavailable.
        }
    }

    return in_array(normalizedRole($_SESSION['user_role'] ?? null), ['admin', 'rh'], true);
}

function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, email, prenom, nom, role, NULL AS avatar FROM users WHERE id = :id");
        $stmt->execute([':id' => currentUserId()]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Login form role cards (user / startup / admin) vs account role in `users`.
 * Avoid rejecting valid passwords because the wrong card was selected.
 */
function loginFormRoleMatchesAccount(?string $formRole, ?string $dbRole): bool {
    $form = normalizedRole($formRole);
    $db = normalizedRole($dbRole);
    if ($db === '') {
        return false;
    }
    if ($form === '') {
        return true;
    }
    if ($form === $db) {
        return true;
    }
    // "Administrateur" card: accept platform RH accounts stored as admin or rh
    if ($form === 'admin' && in_array($db, ['admin', 'rh'], true)) {
        return true;
    }
    // "Utilisateur" card: staff can still use the default tab
    if ($form === 'user' && in_array($db, ['user', 'admin', 'rh'], true)) {
        return true;
    }
    return false;
}

function login($email, $password, ?string $role = null) {
    try {
        require_once __DIR__ . '/../controllers/rh/UserController.php';
        $userController = new UserController();
        $user = $userController->authenticate($email, $password);

        if (!$user) {
            return false;
        }

        if ($role !== null && !loginFormRoleMatchesAccount($role, $user['role'] ?? null)) {
            $_SESSION['login_errors'] = ['general' => 'Ce compte ne correspond pas au type de profil choisi (Utilisateur / Startup / Admin).'];
            return false;
        }

        if (isset($user['statut']) && normalizedRole($user['statut']) === 'pending') {
            $_SESSION['login_errors'] = ['general' => 'Veuillez verifier votre adresse email avant de vous connecter.'];
            return false;
        }

        if (isset($user['statut']) && normalizedRole($user['statut']) === 'banni') {
            $_SESSION['login_errors'] = ['general' => 'Votre compte a ete suspendu.'];
            return false;
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'] ?? trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function logout() {
    session_destroy();
    header('Location: /final/login.php');
    exit;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /final/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

function requireRole(array $roles): void {
    requireLogin();
    $userRole = normalizedRole($_SESSION['user_role'] ?? null);
    $allowedRoles = array_map('normalizedRole', $roles);
    if (!in_array($userRole, $allowedRoles, true)) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

function requireOwnershipOrAdmin(?int $ownerId): void {
    requireLogin();
    if (!isAdmin() && (int) currentUserId() !== (int) $ownerId) {
        http_response_code(403);
        exit('Accès refusé. Vous n\'êtes pas autorisé à modifier cette ressource.');
    }
}
