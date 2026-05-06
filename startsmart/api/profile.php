<?php
/**
 * api/profile.php - Self profile update endpoint for logged-in users/startups
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['user_id']) || empty($_SESSION['user_role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorise.']);
    exit;
}

if (($_GET['action'] ?? '') !== 'update_profile' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action invalide.']);
    exit;
}

require_once __DIR__ . '/../config/Database.php';

$userId = (int)$_SESSION['user_id'];
$role = $_SESSION['user_role'];

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT id, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable.']);
        exit;
    }

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Email invalide.']);
        exit;
    }

    $dup = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id != :id');
    $dup->execute([':email' => $email, ':id' => $userId]);
    if ((int)$dup->fetchColumn() > 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Cet email est deja utilise.']);
        exit;
    }

    $passwordSql = '';
    $params = [':id' => $userId, ':email' => $email];
    if ($password !== '') {
        if (strlen($password) < 8) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caracteres.']);
            exit;
        }
        if ($password !== $passwordConfirm) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'La confirmation du mot de passe ne correspond pas.']);
            exit;
        }
        $passwordSql = ', password = :password';
        $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
    }

    if ($role === 'startup') {
        $nomStartup = trim((string)($_POST['nom_startup'] ?? ''));
        $nomResponsable = trim((string)($_POST['nom_responsable'] ?? ''));
        $prenomResponsable = trim((string)($_POST['prenom_responsable'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));
        $secteur = trim((string)($_POST['secteur'] ?? ''));
        $siteWeb = trim((string)($_POST['site_web'] ?? ''));
        $stade = trim((string)($_POST['stade'] ?? ''));

        if ($nomStartup === '' || $nomResponsable === '' || $prenomResponsable === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Nom startup et responsable sont requis.']);
            exit;
        }

        $allowedSecteurs = ['tech', 'sante', 'fintech', 'logistique', 'retail', 'autre', ''];
        $allowedStades = ['idee', 'prototype', 'mvp', 'croissance', 'scale', ''];
        if (!in_array($secteur, $allowedSecteurs, true) || !in_array($stade, $allowedStades, true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Valeurs de secteur ou stade invalides.']);
            exit;
        }

        if ($siteWeb !== '' && !filter_var($siteWeb, FILTER_VALIDATE_URL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'URL du site web invalide.']);
            exit;
        }

        $sql = "UPDATE users SET
                    email = :email,
                    nom = :nom,
                    prenom = :prenom,
                    nom_startup = :nom_startup,
                    nom_responsable = :nom_responsable,
                    prenom_responsable = :prenom_responsable,
                    telephone = :telephone,
                    secteur = :secteur,
                    site_web = :site_web,
                    stade = :stade
                    {$passwordSql}
                WHERE id = :id";

        $params[':nom'] = $nomResponsable;
        $params[':prenom'] = $prenomResponsable;
        $params[':nom_startup'] = $nomStartup;
        $params[':nom_responsable'] = $nomResponsable;
        $params[':prenom_responsable'] = $prenomResponsable;
        $params[':telephone'] = $telephone !== '' ? $telephone : null;
        $params[':secteur'] = $secteur !== '' ? $secteur : null;
        $params[':site_web'] = $siteWeb !== '' ? $siteWeb : null;
        $params[':stade'] = $stade !== '' ? $stade : null;
        $_SESSION['user_name'] = trim($nomResponsable . ' ' . $prenomResponsable);
    } else {
        $nom = trim((string)($_POST['nom'] ?? ''));
        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));
        $dateNaissance = trim((string)($_POST['date_naissance'] ?? ''));

        if ($nom === '' || $prenom === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Nom et prenom sont requis.']);
            exit;
        }

        if ($dateNaissance !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateNaissance)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Date de naissance invalide.']);
            exit;
        }

        $sql = "UPDATE users SET
                    nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    telephone = :telephone,
                    date_naissance = :date_naissance
                    {$passwordSql}
                WHERE id = :id";

        $params[':nom'] = $nom;
        $params[':prenom'] = $prenom;
        $params[':telephone'] = $telephone !== '' ? $telephone : null;
        $params[':date_naissance'] = $dateNaissance !== '' ? $dateNaissance : null;
        $_SESSION['user_name'] = trim($nom . ' ' . $prenom);
    }

    $update = $db->prepare($sql);
    $update->execute($params);

    echo json_encode(['success' => true, 'message' => 'Profil mis a jour avec succes.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de la mise a jour du profil.']);
}

