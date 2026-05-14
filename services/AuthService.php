<?php
namespace Services;

class AuthService {
    private static function normalizedRole(): ?string {
        $role = $_SESSION['user_role'] ?? null;
        return $role === null ? null : strtolower(trim((string) $role));
    }
    
    public static function isLoggedIn(): bool {
        return self::currentUserRole() !== null && self::currentUserId() !== null;
    }

    public static function currentUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    public static function currentUserId(): ?int {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function isAdmin(): bool {
        return in_array(self::normalizedRole(), ['admin', 'rh'], true);
    }

    public static function isClient(): bool {
        return in_array(self::normalizedRole(), ['client', 'user', 'startup'], true);
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            http_response_code(401);
            header('Location: login.php');
            exit;
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            exit('Accès refusé. Administrateur requis.');
        }
    }

    public static function requireOwnershipOrAdmin(?int $ownerId): void {
        self::requireLogin();
        if (!self::isAdmin() && self::currentUserId() !== $ownerId) {
            http_response_code(403);
            exit('Accès refusé. Vous n\'êtes pas autorisé à modifier cette ressource.');
        }
    }
}
?>
