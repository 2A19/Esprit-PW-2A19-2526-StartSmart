<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function currentUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function isLoggedIn(): bool {
    return currentUserRole() !== null && currentUserId() !== null;
}

function isAdmin(): bool {
    return currentUserRole() === 'ADMIN';
}

function isClient(): bool {
    return currentUserRole() === 'CLIENT';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(array $roles): void {
    requireLogin();

    if (!in_array(currentUserRole(), $roles, true)) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

function requireOwnershipOrAdmin(?int $ownerId): void {
    requireLogin();

    if (!isAdmin() && currentUserId() !== $ownerId) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}