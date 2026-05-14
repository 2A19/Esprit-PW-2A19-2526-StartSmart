<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/Database.php';

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'menu';

// ── Réinitialiser mot de passe admin ──
if ($action === 'reset_admin') {
    $hash = password_hash('Admin1234', PASSWORD_BCRYPT);
    $db->prepare("UPDATE users SET password = ? WHERE role IN ('admin','rh') LIMIT 1")->execute([$hash]);
    $msg = '<span style="color:green">✅ Mot de passe admin réinitialisé à : <strong>Admin1234</strong></span>';
}

// ── Créer le user test ──
if ($action === 'create') {
    $db->prepare("DELETE FROM users WHERE email = 'testuser@test.com'")->execute();
    $hash = password_hash('Test1234', PASSWORD_BCRYPT);
    try {
        $stmt = $db->prepare("INSERT INTO users (nom, prenom, full_name, email, password, role, statut) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute(['Test', 'User', 'Test User', 'testuser@test.com', $hash, 'user', 'actif']);
        $msg = $ok ? '<span style="color:green">✅ User test créé !</span>' : '<span style="color:red">❌ Erreur</span>';
    } catch (Exception $e) {
        $msg = '<span style="color:red">❌ ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
}

// ── Connexion directe user normal ──
if ($action === 'login_user') {
    $stmt = $db->prepare("SELECT * FROM users WHERE email = 'testuser@test.com' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = 'user';
        $_SESSION['user_name']  = 'Test User';
        header('Location: rh.php');
        exit;
    }
    $msg = '<span style="color:red">❌ Créez d\'abord le user test.</span>';
}

// ── Connexion directe admin ──
if ($action === 'login_admin') {
    $stmt = $db->prepare("SELECT * FROM users WHERE role IN ('admin','rh') LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_name']  = $user['full_name'] ?? 'Admin';
        header('Location: rh.php?page=backend/admin&section=evenements');
        exit;
    }
    $msg = '<span style="color:red">❌ Aucun admin trouvé en base.</span>';
}

// ── Déconnexion ──
if ($action === 'logout') {
    session_destroy();
    header('Location: create_test_user.php');
    exit;
}

$users = $db->query("SELECT id, email, role, statut FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Test Accès</title>
<style>
body{font-family:Arial,sans-serif;max-width:720px;margin:40px auto;padding:20px;background:#f0f2f5;}
.card{background:#fff;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,.1);}
h2{margin-top:0;} h3{margin:0 0 14px;}
.btn{display:inline-block;padding:12px 22px;border-radius:8px;text-decoration:none;color:#fff;font-weight:bold;margin:5px;font-size:.95rem;}
.btn-blue{background:#3b8cf7;} .btn-green{background:#00b894;}
.btn-red{background:#e74c3c;} .btn-gray{background:#636e72;} .btn-orange{background:#e17055;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:9px 12px;border:1px solid #eee;text-align:left;font-size:.9rem;}
th{background:#f8f9fa;}
.badge{padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:bold;}
.user{background:#dfe6e9;color:#2d3436;} .admin{background:#fdcb6e;color:#2d3436;}
.startup{background:#a29bfe;color:#fff;} .rh{background:#fd79a8;color:#fff;}
.msg{padding:12px 16px;border-radius:8px;background:#f8f9fa;border-left:4px solid #3b8cf7;margin-bottom:16px;}
.session-box{background:#f8f9fa;border-radius:8px;padding:12px 16px;font-size:.9rem;}
</style>
</head>
<body>

<?php if (!empty($msg)): ?>
<div class="card"><div class="msg"><?= $msg ?></div></div>
<?php endif; ?>

<div class="card">
  <h2>🔐 Session actuelle</h2>
  <div class="session-box">
    <?php if (!empty($_SESSION['user_id'])): ?>
      Connecté : <strong><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></strong>
      — rôle : <span class="badge <?= $_SESSION['user_role'] ?? '' ?>"><?= $_SESSION['user_role'] ?? '' ?></span>
    <?php else: ?>
      <em>Non connecté</em>
    <?php endif; ?>
  </div>
  <br>
  <a class="btn btn-gray" href="?action=logout">🚪 Se déconnecter</a>
</div>

<div class="card">
  <h3>👤 Tester un utilisateur normal</h3>
  <a class="btn btn-green" href="?action=create">1. Créer user test</a>
  <a class="btn btn-blue" href="?action=login_user">2. Se connecter comme USER → doit aller sur index.php</a>
</div>

<div class="card">
  <h3>🛡️ Tester l'admin</h3>
  <a class="btn btn-orange" href="?action=reset_admin">1. Réinitialiser mot de passe admin → <strong>Admin1234</strong></a>
  <a class="btn btn-red" href="?action=login_admin">2. Se connecter comme ADMIN → doit aller sur back office</a>
  <br><br>
  <small>Ou connectez-vous sur <a href="login.php">login.php</a> avec le mot de passe <strong>Admin1234</strong> (après reset)</small>
</div>

<div class="card">
  <h3>👥 Utilisateurs en base</h3>
  <table>
    <tr><th>ID</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><span class="badge <?= $u['role'] ?>"><?= $u['role'] ?></span></td>
      <td><?= $u['statut'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

</body>
</html>
