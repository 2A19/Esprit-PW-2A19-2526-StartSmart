<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once __DIR__ . '/../config/session.php';

$role = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if (empty($_SESSION['user_id']) || !in_array($role, ['admin', 'rh'], true)) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/rh/UserController.php';
require_once __DIR__ . '/../models/rh/RHUser.php';

$controller = new UserController();
$action     = $_GET['action'] ?? '';
$method     = $_SERVER['REQUEST_METHOD'];

if ($action === 'create_user' && $method === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = $nom . ' ' . $prenom;
    $role = $_POST['role'] ?? 'user';
    $section = $_POST['section'] ?? ($role === 'startup' ? 'startups' : 'users');
    
    if ($controller->emailExists($email)) {
        $_SESSION['form_error'] = "Cet email est déjà utilisé.";
    } else {
        $company = trim((string)($_POST['company_name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? $_POST['telephone'] ?? ''));
        $profession = trim((string)($_POST['profession'] ?? ''));
        $user = new RHUser(
            null,
            $fullName,
            $email,
            $_POST['password'] ?? '',
            $role,
            $company,
            $phone,
            $profession,
            '',
            ''
        );
        try {
            $controller->addUser($user);
            $_SESSION['form_success'] = "L'élément a été créé avec succès.";
        } catch (Throwable $e) {
            $_SESSION['form_error'] = 'Erreur à la création : ' . $e->getMessage();
        }
    }
    
    header('Location: ../rh.php?page=backend/admin&section=' . $section);
    exit;
}

if ($action === 'update_user' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = $nom . ' ' . $prenom;
    $role = $_POST['role'] ?? 'user';
    $statut = $_POST['statut'] ?? 'actif';
    $section = $_POST['section'] ?? ($role === 'startup' ? 'startups' : 'users');
    
    $existing = $controller->showUser($id);
    if ($existing) {
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $existing['password'];
        $db = (new Database())->getPDO();
        $roleIn = strtolower(trim((string) $role));
        if ($roleIn === 'rh') {
            $roleIn = 'admin';
        }
        if (!in_array($roleIn, ['user', 'startup', 'admin'], true)) {
            $roleIn = 'user';
        }
        $statutDb = strtolower(trim((string) $statut));
        if (!in_array($statutDb, ['actif', 'inactif', 'banni', 'verifie', 'pending'], true)) {
            $statutDb = 'actif';
        }
        $telephone = trim((string)($_POST['telephone'] ?? $_POST['phone'] ?? $existing['telephone'] ?? ''));

        try {
            if ($roleIn === 'startup') {
                $nomStartup = trim((string)($_POST['company_name'] ?? $existing['nom_startup'] ?? ''));
                $secteur = trim((string)($_POST['profession'] ?? $existing['secteur'] ?? ''));
                $stmt = $db->prepare(
                    "UPDATE users SET nom = :nom, prenom = :prenom, email = :email, password = :password,
                    role = 'startup', statut = :statut, nom_startup = :nom_startup,
                    nom_responsable = :nom, prenom_responsable = :prenom, secteur = :secteur,
                    telephone = :telephone WHERE id = :id"
                );
                $stmt->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'password' => $password,
                    'statut' => $statutDb,
                    'nom_startup' => $nomStartup !== '' ? $nomStartup : ($existing['nom_startup'] ?? ''),
                    'secteur' => $secteur !== '' ? $secteur : null,
                    'telephone' => $telephone !== '' ? $telephone : null,
                    'id' => $id,
                ]);
            } else {
                $stmt = $db->prepare(
                    "UPDATE users SET nom = :nom, prenom = :prenom, email = :email, password = :password,
                    role = :role, statut = :statut, telephone = :telephone WHERE id = :id"
                );
                $stmt->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'password' => $password,
                    'role' => $roleIn,
                    'statut' => $statutDb,
                    'telephone' => $telephone !== '' ? $telephone : null,
                    'id' => $id,
                ]);
            }
            $_SESSION['form_success'] = "L'élément a été mis à jour avec succès.";
        } catch (Throwable $e) {
            $_SESSION['form_error'] = 'Erreur à la mise à jour : ' . $e->getMessage();
        }
    } else {
        $_SESSION['form_error'] = "L'élément est introuvable.";
    }
    
    header('Location: ../rh.php?page=backend/admin&section=' . $section);
    exit;
}

if ($action === 'ban_user' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $section = $_POST['section'] ?? 'users';
    $db = (new Database())->getPDO();
    $stmt = $db->prepare("UPDATE users SET statut = 'banni' WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $_SESSION['form_success'] = "L'élément a été banni avec succès.";
    header('Location: ../rh.php?page=backend/admin&section=' . $section);
    exit;
}

if ($action === 'unban_user' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $section = $_POST['section'] ?? 'users';
    $db = (new Database())->getPDO();
    $stmt = $db->prepare("UPDATE users SET statut = 'actif' WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $_SESSION['form_success'] = "L'élément a été débanni avec succès.";
    header('Location: ../rh.php?page=backend/admin&section=' . $section);
    exit;
}

if ($action === 'delete_user' && $method === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $section = $_POST['section'] ?? 'users';
    $controller->deleteUser($id);
    $_SESSION['form_success'] = "L'élément a été supprimé avec succès.";
    header('Location: ../rh.php?page=backend/admin&section=' . $section);
    exit;
}

header('Location: ../rh.php?page=backend/admin');
exit;
