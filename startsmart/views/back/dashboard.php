<?php
session_start();
if(empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'){
    header('Location: ../auth/login.php'); exit;
}

$adminName = $_SESSION['user_name'] ?? 'Admin';
$initials  = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',$adminName),0,2)));

// Determine active tab
$tab = $_GET['tab'] ?? 'users';

// Always load both datasets
require_once __DIR__ . '/../../controllers/UserController.php';
$controller = new UserController();
$controller->listUsers();
$controller->listStartups();

$users_list = $_SESSION['users_list'] ?? [];
$startups_list = $_SESSION['startups_list'] ?? [];
$form_errors = $_SESSION['form_errors'] ?? [];
$success = $_SESSION['success'] ?? '';
$form_data = $_SESSION['form_data'] ?? [];

// Load user/startup for editing if requested
$user_detail = null;
$startup_detail = null;
if (isset($_GET['edit_user'])) {
    require_once __DIR__ . '/../../controllers/UserController.php';
    $controller = new UserController();
    $controller->getUser((int)$_GET['edit_user']);
    $user_detail = $_SESSION['user_detail'] ?? null;
}
if (isset($_GET['edit_startup'])) {
    require_once __DIR__ . '/../../controllers/UserController.php';
    $controller = new UserController();
    $controller->getStartup((int)$_GET['edit_startup']);
    $startup_detail = $_SESSION['startup_detail'] ?? null;
}

// Clear one-time messages
unset($_SESSION['form_errors'], $_SESSION['success'], $_SESSION['form_data'], $_SESSION['user_detail'], $_SESSION['startup_detail']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StartSmart – Admin | Gestion</title>
<link rel="stylesheet" href="../../public/css/style.css">
<style>
.admin-section { display: none; }
.admin-section.active { display: block; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group { display: flex; flex-direction: column; }
.form-group label { margin-bottom: 0.5rem; font-weight: 500; }
.form-group input, .form-group select { padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; }
.table-container { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
.table-container table { width: 100%; border-collapse: collapse; }
.table-container th { background: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; border-bottom: 2px solid #eee; }
.table-container td { padding: 1rem; border-bottom: 1px solid #eee; }
.table-container tr:hover { background: #f9f9f9; }
.action-btn { padding: 0.5rem 1rem; margin: 0 0.25rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; }
.btn-edit { background: #2196F3; color: white; }
.btn-delete { background: #f44336; color: white; }
.btn-primary { background: #4CAF50; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
.btn-primary:hover { background: #45a049; }
.success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
.error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.modal.active { display: flex; align-items: center; justify-content: center; }
.modal-content { background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
</style>
</head>
<body>
<?php if($success): ?>
<div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="sidebar" style="width: 250px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); box-shadow: 0 0 10px rgba(0,0,0,0.2); position: fixed; height: 100vh; overflow-y: auto;">
  <div style="padding: 1.5rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
    <img src="../../public/img/logo.png" alt="Logo" style="max-width: 40px; margin-bottom: 0.5rem;">
    <div style="font-weight: 600; color: white;">StartSmart</div>
  </div>
  <nav style="padding: 1rem 0;">
    <button onclick="switchTab('users')" style="width: 100%; padding: 0.75rem 1rem; text-align: left; border: none; background: none; cursor: pointer; font-size: 0.95rem; color: rgba(255,255,255,0.8); transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='none'" id="users-tab">👥 Utilisateurs</button>
    <button onclick="switchTab('startups')" style="width: 100%; padding: 0.75rem 1rem; text-align: left; border: none; background: none; cursor: pointer; font-size: 0.95rem; color: rgba(255,255,255,0.8); transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='none'" id="startups-tab">🚀 Startups</button>
  </nav>
  <div style="padding: 1rem; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto;">
    <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 6px; text-align: center;">
      <div style="font-weight: 600; margin-bottom: 0.5rem; color: white;"><?= htmlspecialchars($adminName) ?></div>
      <form method="POST" action="../../api/auth.php?action=logout" style="margin: 0;">
        <button type="submit" style="background: none; border: none; color: #87CEEB; cursor: pointer; text-decoration: underline; font-size: 0.9rem;">Déconnexion</button>
      </form>
    </div>
  </div>
</div>

<main style="margin-left: 250px; padding: 2rem;">
  <h1>Gestion Administrative</h1>

  <!-- USERS SECTION -->
  <div id="users-section" class="admin-section <?= $tab === 'users' ? 'active' : '' ?>">
    <h2>Utilisateurs</h2>
    
    <button class="btn-primary" onclick="openModal('createUserModal')">+ Nouvel Utilisateur</button>
    
    <?php if(!empty($form_errors)): ?>
    <div class="error">
      <?php foreach($form_errors as $field => $msg): ?>
        <div><?= htmlspecialchars($msg) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="table-container" style="margin-top: 1.5rem;">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($users_list['data'] ?? [] as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['nom'] ?? '') ?></td>
            <td><?= htmlspecialchars($user['prenom'] ?? '') ?></td>
            <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($user['role'] ?? '') ?></td>
            <td><?= htmlspecialchars($user['statut'] ?? '') ?></td>
            <td>
              <button class="action-btn btn-edit" onclick="editUser(<?= $user['id'] ?>)">Éditer</button>
              <form method="POST" action="../../api/users.php?action=delete_user" style="display: inline;">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <button type="submit" class="action-btn btn-delete" onclick="return confirm('Confirmer suppression?')">Supprimer</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div style="margin-top: 1rem; text-align: center;">
      <small>Total: <?= $users_list['total'] ?? 0 ?> utilisateurs</small>
    </div>
  </div>

  <!-- STARTUPS SECTION -->
  <div id="startups-section" class="admin-section <?= $tab === 'startups' ? 'active' : '' ?>">
    <h2>Startups</h2>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Responsable</th>
            <th>Email</th>
            <th>Secteur</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($startups_list['data'] ?? [] as $startup): ?>
          <tr>
            <td><?= htmlspecialchars($startup['nom_startup'] ?? '') ?></td>
            <td><?= htmlspecialchars(($startup['nom_responsable'] ?? '') . ' ' . ($startup['prenom_responsable'] ?? '')) ?></td>
            <td><?= htmlspecialchars($startup['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($startup['secteur'] ?? '') ?></td>
            <td><?= htmlspecialchars($startup['statut'] ?? '') ?></td>
            <td>
              <button class="action-btn btn-edit" onclick="editStartup(<?= $startup['id'] ?>)">Éditer</button>
              <form method="POST" action="../../api/users.php?action=delete_startup" style="display: inline;">
                <input type="hidden" name="id" value="<?= $startup['id'] ?>">
                <button type="submit" class="action-btn btn-delete" onclick="return confirm('Confirmer suppression?')">Supprimer</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div style="margin-top: 1rem; text-align: center;">
      <small>Total: <?= $startups_list['total'] ?? 0 ?> startups</small>
    </div>
  </div>
</main>

<!-- CREATE USER MODAL -->
<div id="createUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Créer Utilisateur</h3>
      <button class="modal-close" onclick="closeModal('createUserModal')">×</button>
    </div>
    <form method="POST" action="../../api/users.php?action=create_user">
      <div class="form-row">
        <div class="form-group">
          <label>Nom *</label>
          <input type="text" name="nom" required>
        </div>
        <div class="form-group">
          <label>Prénom *</label>
          <input type="text" name="prenom" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Mot de passe *</label>
          <input type="password" name="password" required>
        </div>
        <div class="form-group">
          <label>Confirmation *</label>
          <input type="password" name="password_confirm" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Rôle</label>
          <select name="role">
            <option value="user">Utilisateur</option>
            <option value="admin">Administrateur</option>
          </select>
        </div>
        <div class="form-group">
          <label>Statut</label>
          <select name="statut">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
            <option value="banni">Banni</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Créer</button>
    </form>
  </div>
</div>

<!-- EDIT USER MODAL -->
<?php if ($user_detail): ?>
<div id="editUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Éditer Utilisateur</h3>
      <button class="modal-close" onclick="closeModal('editUserModal'); location.href='?tab=users';">×</button>
    </div>
    <?php if(!empty($form_errors)): ?>
    <div class="error">
      <?php foreach($form_errors as $field => $msg): ?>
        <div><?= htmlspecialchars($msg) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <form method="POST" action="../../api/users.php?action=update_user">
      <input type="hidden" name="id" value="<?= $user_detail['id'] ?>">
      <div class="form-row">
        <div class="form-group">
          <label>Nom *</label>
          <input type="text" name="nom" value="<?= htmlspecialchars($form_data['nom'] ?? $user_detail['nom']) ?>" required>
        </div>
        <div class="form-group">
          <label>Prénom *</label>
          <input type="text" name="prenom" value="<?= htmlspecialchars($form_data['prenom'] ?? $user_detail['prenom']) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($form_data['email'] ?? $user_detail['email']) ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Mot de passe (laisser vide pour ne pas changer)</label>
          <input type="password" name="password">
        </div>
        <div class="form-group">
          <label>Confirmation</label>
          <input type="password" name="password_confirm">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Téléphone</label>
          <input type="tel" name="telephone" value="<?= htmlspecialchars($form_data['telephone'] ?? $user_detail['telephone'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Date de naissance</label>
          <input type="date" name="date_naissance" value="<?= htmlspecialchars($form_data['date_naissance'] ?? $user_detail['date_naissance'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Rôle</label>
          <select name="role">
            <option value="user" <?= ($form_data['role'] ?? $user_detail['role']) === 'user' ? 'selected' : '' ?>>Utilisateur</option>
            <option value="admin" <?= ($form_data['role'] ?? $user_detail['role']) === 'admin' ? 'selected' : '' ?>>Administrateur</option>
          </select>
        </div>
        <div class="form-group">
          <label>Statut</label>
          <select name="statut">
            <option value="actif" <?= ($form_data['statut'] ?? $user_detail['statut']) === 'actif' ? 'selected' : '' ?>>Actif</option>
            <option value="inactif" <?= ($form_data['statut'] ?? $user_detail['statut']) === 'inactif' ? 'selected' : '' ?>>Inactif</option>
            <option value="banni" <?= ($form_data['statut'] ?? $user_detail['statut']) === 'banni' ? 'selected' : '' ?>>Banni</option>
          </select>
        </div>
      </div>
      <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <button type="submit" class="btn-primary" style="flex: 1;">Mettre à jour</button>
        <button type="button" class="btn-primary" style="flex: 1; background: #999;" onclick="closeModal('editUserModal'); location.href='?tab=users';">Annuler</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- EDIT STARTUP MODAL -->
<?php if ($startup_detail): ?>
<div id="editStartupModal" class="modal">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
      <h3>Éditer Startup</h3>
      <button class="modal-close" onclick="closeModal('editStartupModal'); location.href='?tab=startups';">×</button>
    </div>
    <?php if(!empty($form_errors)): ?>
    <div class="error">
      <?php foreach($form_errors as $field => $msg): ?>
        <div><?= htmlspecialchars($msg) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <form method="POST" action="../../api/users.php?action=update_startup">
      <input type="hidden" name="id" value="<?= $startup_detail['id'] ?>">
      <div class="form-group">
        <label>Nom de la Startup *</label>
        <input type="text" name="nom_startup" value="<?= htmlspecialchars($form_data['nom_startup'] ?? $startup_detail['nom_startup']) ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Nom du Responsable *</label>
          <input type="text" name="nom_responsable" value="<?= htmlspecialchars($form_data['nom_responsable'] ?? $startup_detail['nom_responsable']) ?>" required>
        </div>
        <div class="form-group">
          <label>Prénom du Responsable *</label>
          <input type="text" name="prenom_responsable" value="<?= htmlspecialchars($form_data['prenom_responsable'] ?? $startup_detail['prenom_responsable']) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($form_data['email'] ?? $startup_detail['email']) ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Téléphone</label>
          <input type="tel" name="telephone" value="<?= htmlspecialchars($form_data['telephone'] ?? $startup_detail['telephone'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Site Web</label>
          <input type="url" name="site_web" value="<?= htmlspecialchars($form_data['site_web'] ?? $startup_detail['site_web'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Secteur</label>
          <select name="secteur">
            <option value="">Sélectionner...</option>
            <option value="tech" <?= ($form_data['secteur'] ?? $startup_detail['secteur']) === 'tech' ? 'selected' : '' ?>>Tech</option>
            <option value="sante" <?= ($form_data['secteur'] ?? $startup_detail['secteur']) === 'sante' ? 'selected' : '' ?>>Santé</option>
            <option value="fintech" <?= ($form_data['secteur'] ?? $startup_detail['secteur']) === 'fintech' ? 'selected' : '' ?>>Fintech</option>
            <option value="logistique" <?= ($form_data['secteur'] ?? $startup_detail['secteur']) === 'logistique' ? 'selected' : '' ?>>Logistique</option>
            <option value="retail" <?= ($form_data['secteur'] ?? $startup_detail['secteur']) === 'retail' ? 'selected' : '' ?>>Retail</option>
            <option value="autre" <?= ($form_data['secteur'] ?? $startup_detail['secteur']) === 'autre' ? 'selected' : '' ?>>Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label>Stade de développement</label>
          <select name="stade">
            <option value="idee" <?= ($form_data['stade'] ?? $startup_detail['stade']) === 'idee' ? 'selected' : '' ?>>Idée</option>
            <option value="MVP" <?= ($form_data['stade'] ?? $startup_detail['stade']) === 'MVP' ? 'selected' : '' ?>>MVP</option>
            <option value="beta" <?= ($form_data['stade'] ?? $startup_detail['stade']) === 'beta' ? 'selected' : '' ?>>Beta</option>
            <option value="prod" <?= ($form_data['stade'] ?? $startup_detail['stade']) === 'prod' ? 'selected' : '' ?>>Production</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Mot de passe (laisser vide pour ne pas changer)</label>
          <input type="password" name="password">
        </div>
        <div class="form-group">
          <label>Confirmation</label>
          <input type="password" name="password_confirm">
        </div>
      </div>
      <div class="form-group">
        <label>Statut</label>
        <select name="statut">
          <option value="actif" <?= ($form_data['statut'] ?? $startup_detail['statut']) === 'actif' ? 'selected' : '' ?>>Actif</option>
          <option value="inactif" <?= ($form_data['statut'] ?? $startup_detail['statut']) === 'inactif' ? 'selected' : '' ?>>Inactif</option>
          <option value="banni" <?= ($form_data['statut'] ?? $startup_detail['statut']) === 'banni' ? 'selected' : '' ?>>Banni</option>
        </select>
      </div>
      <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <button type="submit" class="btn-primary" style="flex: 1;">Mettre à jour</button>
        <button type="button" class="btn-primary" style="flex: 1; background: #999;" onclick="closeModal('editStartupModal'); location.href='?tab=startups';">Annuler</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function switchTab(tab) {
  document.querySelectorAll('.admin-section').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('[id$="-tab"]').forEach(el => el.style.background = 'none');
  
  document.getElementById(tab + '-section').classList.add('active');
  document.getElementById(tab + '-tab').style.background = '#e3f2fd';
  
  // Reload data
  if(tab === 'users') {
    location.href = '?tab=users';
  } else {
    location.href = '?tab=startups';
  }
}

function openModal(modalId) {
  document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('active');
}

function editUser(userId) {
  location.href = '?tab=users&edit_user=' + userId;
}

function editStartup(startupId) {
  location.href = '?tab=startups&edit_startup=' + startupId;
}

// Set active tab on load
document.getElementById('<?= $tab ?>-tab').style.background = 'rgba(255,255,255,0.2)';
document.getElementById('<?= $tab ?>-tab').style.color = 'white';

// Auto-open edit modals if editing
<?php if ($user_detail): ?>
openModal('editUserModal');
<?php endif; ?>
<?php if ($startup_detail): ?>
openModal('editStartupModal');
<?php endif; ?>

</script>

</body>
</html>
