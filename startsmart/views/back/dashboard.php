<?php
session_start();
require_once __DIR__ . '/../../controllers/AuthController.php';
$authController = new AuthController();
if (empty($_SESSION['user_id'])) {
    $authController->autoLoginFromRememberMe();
}
if(empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'){
    header('Location: ../../api/auth.php?action=logout');
    exit;
}

$adminName = $_SESSION['user_name'] ?? 'Admin';
$adminPhoto = $_SESSION['user_photo'] ?? null;
$initials  = implode('', array_map(fn($w)=>strtoupper($w[0] ?? ''), array_filter(explode(' ',$adminName))));

$tab = $_GET['tab'] ?? 'users';

require_once __DIR__ . '/../../controllers/UserController.php';
$controller = new UserController();

if (!in_array($tab, ['users', 'startups', 'stats'], true)) {
    $tab = 'users';
}

unset($_GET['u_search'], $_GET['s_search'], $_GET['u_sort'], $_GET['s_sort'], $_GET['u_page'], $_GET['s_page']);
$_GET['u_limit'] = 1000;
$_GET['s_limit'] = 1000;
$controller->listUsers();
$controller->listStartups();

$users_raw     = $_SESSION['users_list']   ?? [];
$startups_raw  = $_SESSION['startups_list'] ?? [];

// Controller stores results as ['data'=>rows, 'total'=>n, ...]
$users_list    = $users_raw['data']    ?? $users_raw;
$startups_list = $startups_raw['data'] ?? $startups_raw;
$users_total   = $users_raw['total']   ?? count($users_list);
$startups_total= $startups_raw['total'] ?? count($startups_list);

unset($_SESSION['users_list'], $_SESSION['startups_list']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StartSmart — Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #07090f;
  --sidebar: #0b0e1a;
  --blue: #3b8cf7;
  --green: #00e5a0;
  --red: #ff5b6b;
  --orange: #ffaa00;
  --text: #e8eaf0;
  --muted: rgba(232,234,240,0.45);
  --border: rgba(255,255,255,0.07);
  --card: rgba(255,255,255,0.03);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  display: flex;
  min-height: 100vh;
}

/* ── Sidebar ── */
.sidebar {
  width: 240px;
  min-height: 100vh;
  background: var(--sidebar);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 50;
  animation: sideIn 0.5s ease both;
}
@keyframes sideIn { from { transform: translateX(-240px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.sidebar-logo {
  padding: 28px 24px 20px;
  font-family: 'Syne', sans-serif;
  font-size: 1.3rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  border-bottom: 1px solid var(--border);
}
.sidebar-logo span { color: var(--blue); }
.sidebar-logo em { color: var(--green); font-style: normal; }

.sidebar-nav {
  padding: 16px 12px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px;
  border-radius: 10px;
  color: var(--muted);
  font-size: 0.88rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.2s;
}
.nav-item:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.04);
}
.nav-item.active {
  color: var(--blue);
  background: rgba(59, 140, 247, 0.1);
  border-color: rgba(59, 140, 247, 0.2);
}
.nav-item .icon {
  font-size: 1rem;
  width: 20px;
  text-align: center;
}

.sidebar-footer {
  padding: 20px 16px;
  border-top: 1px solid var(--border);
}
.admin-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border);
  margin-bottom: 12px;
}
.admin-avatar {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--blue), #5b6ef7);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
  color: white;
  font-weight: 700;
  overflow: hidden;
}
.admin-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.admin-info { flex: 1; min-width: 0; }
.admin-name {
  font-size: 0.85rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.admin-role {
  font-size: 0.72rem;
  color: var(--green);
}
.btn-logout {
  display: block;
  width: 100%;
  padding: 9px;
  border-radius: 9px;
  text-align: center;
  background: rgba(255, 91, 107, 0.08);
  border: 1px solid rgba(255, 91, 107, 0.2);
  color: #ff5b6b;
  font-size: 0.82rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}
.btn-logout:hover {
  background: rgba(255, 91, 107, 0.16);
  border-color: rgba(255, 91, 107, 0.35);
}

/* ── Main ── */
.main {
  margin-left: 240px;
  flex: 1;
  min-height: 100vh;
  padding: 40px;
  animation: mainIn 0.6s 0.1s ease both;
}
@keyframes mainIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

.page-header {
  margin-bottom: 36px;
}
.page-title {
  font-family: 'Syne', sans-serif;
  font-size: 1.9rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  margin-bottom: 6px;
}
.page-subtitle {
  color: var(--muted);
  font-size: 0.88rem;
}

.card {
  border-radius: 18px;
  border: 1px solid var(--border);
  background: var(--card);
  backdrop-filter: blur(10px);
  overflow: hidden;
  animation: fadeUp 0.5s 0.1s ease both;
}
.card-header {
  padding: 22px 28px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.card-title {
  font-family: 'Syne', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
}

.search-bar {
  padding: 18px 28px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.search-input-wrap {
  position: relative;
  flex: 1;
  min-width: 250px;
}
.search-input-wrap::before {
  content: '🔍';
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.85rem;
  pointer-events: none;
}
.search-input {
  width: 100%;
  padding: 10px 14px 10px 38px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input:focus {
  border-color: var(--blue);
  box-shadow: 0 0 0 3px rgba(59, 140, 247, 0.12);
}
.btn-search, .btn-reset, .btn-export {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.04);
  color: var(--text);
  font-size: 0.85rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-search:hover, .btn-reset:hover, .btn-export:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: var(--blue);
}
.btn-reset {
  background: rgba(255, 91, 107, 0.08);
  border-color: rgba(255, 91, 107, 0.2);
  color: #ff5b6b;
}
.btn-export {
  background: rgba(59, 140, 247, 0.1);
  border-color: rgba(59, 140, 247, 0.25);
  color: var(--blue);
}
.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--green), #00c87a);
  border: none;
  cursor: pointer;
  font-family: 'Syne', sans-serif;
  font-size: 0.85rem;
  font-weight: 700;
  color: #06080f;
  transition: transform 0.15s, box-shadow 0.15s;
  box-shadow: 0 4px 14px rgba(0, 229, 160, 0.25);
}
.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 229, 160, 0.35);
}

/* ── Table ── */
table {
  width: 100%;
  border-collapse: collapse;
}
thead tr {
  background: rgba(255, 255, 255, 0.02);
}
thead th {
  padding: 14px 24px;
  text-align: left;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--muted);
  cursor: pointer;
  user-select: none;
  transition: all 0.2s;
}
thead th:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.04);
}
thead th.sortable.is-active {
  color: var(--text);
}
thead th.sortable .sort-arrow {
  margin-left: 4px;
  color: var(--muted);
}
thead th.sortable.is-active .sort-arrow {
  color: var(--green);
}
tbody tr {
  border-top: 1px solid var(--border);
  transition: background 0.2s;
  animation: rowIn 0.4s ease both;
}
@keyframes rowIn { from { opacity: 0; transform: translateX(-8px); } to { opacity: 1; transform: translateX(0); } }
tbody tr:hover {
  background: rgba(255, 255, 255, 0.025);
}
tbody td {
  padding: 16px 24px;
  font-size: 0.9rem;
}

.avatar-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}
.avatar {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 700;
  font-family: 'Syne', sans-serif;
  background: linear-gradient(135deg, rgba(59, 140, 247, 0.3), rgba(91, 110, 247, 0.3));
  color: white;
  overflow: hidden;
}
.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.name-cell strong {
  display: block;
  font-size: 0.9rem;
}
.name-cell small {
  color: var(--muted);
  font-size: 0.78rem;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 100px;
  font-size: 0.72rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
}
.badge-user { background: rgba(59, 140, 247, 0.12); color: var(--blue); }
.badge-startup { background: rgba(255, 170, 0, 0.12); color: var(--orange); }
.badge-admin { background: rgba(180, 90, 255, 0.12); color: #b45aff; }
.badge-actif { background: rgba(0, 229, 160, 0.1); color: var(--green); border: 1px solid rgba(0, 229, 160, 0.2); }
.badge-pending { background: rgba(255, 170, 0, 0.1); color: var(--orange); border: 1px solid rgba(255, 170, 0, 0.2); }
.badge-inactif { background: rgba(255, 91, 107, 0.1); color: var(--red); border: 1px solid rgba(255, 91, 107, 0.2); }

.actions {
  display: flex;
  gap: 8px;
}
.btn-edit, .btn-delete {
  padding: 7px 14px;
  border-radius: 8px;
  border: none;
  font-size: 0.8rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-edit {
  background: rgba(59, 140, 247, 0.1);
  border: 1px solid rgba(59, 140, 247, 0.2);
  color: var(--blue);
}
.btn-edit:hover {
  background: rgba(59, 140, 247, 0.2);
  transform: translateY(-1px);
}
.btn-delete {
  background: rgba(255, 91, 107, 0.1);
  border: 1px solid rgba(255, 91, 107, 0.2);
  color: var(--red);
}
.btn-delete:hover {
  background: rgba(255, 91, 107, 0.2);
  transform: translateY(-1px);
}

.table-footer {
  padding: 18px 28px;
  border-top: 1px solid var(--border);
  color: var(--muted);
  font-size: 0.82rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}
.pagination {
  display: flex;
  align-items: center;
  gap: 8px;
}
.pagination button {
  min-width: 34px;
  height: 34px;
  padding: 0 10px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.04);
  color: var(--text);
  font-family: 'Syne', sans-serif;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
}
.pagination button:hover:not(:disabled),
.pagination button.active {
  border-color: var(--blue);
  background: rgba(59, 140, 247, 0.14);
  color: var(--blue);
}
.pagination button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}
.page-size {
  height: 34px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.04);
  color: var(--text);
  padding: 0 8px;
  font-family: 'DM Sans', sans-serif;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(280px, 1fr));
  gap: 22px;
}
.stats-card {
  padding: 28px;
  border-radius: 16px;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02));
  border: 1px solid var(--border);
}
.stats-title {
  margin-bottom: 18px;
  font-family: 'Syne', sans-serif;
  font-size: 1.05rem;
  font-weight: 700;
}
.chart-wrap {
  display: flex;
  align-items: center;
  gap: 22px;
  flex-wrap: wrap;
}
.donut-chart {
  width: 180px;
  aspect-ratio: 1;
  border-radius: 50%;
  position: relative;
  flex-shrink: 0;
}
.donut-chart::after {
  content: '';
  position: absolute;
  inset: 22px;
  border-radius: 50%;
  background: #11141c;
  border: 1px solid rgba(255, 255, 255, 0.05);
}
.donut-center {
  position: absolute;
  inset: 0;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  text-align: center;
}
.donut-total {
  font-family: 'Syne', sans-serif;
  font-size: 1.7rem;
  font-weight: 800;
  line-height: 1;
}
.donut-label {
  margin-top: 6px;
  color: var(--muted);
  font-size: 0.78rem;
}
.stats-legend {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 180px;
}
.legend-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 10px 12px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.04);
}
.legend-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.88rem;
}
.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.legend-value {
  color: var(--muted);
  font-family: 'Syne', sans-serif;
  font-size: 0.9rem;
}

.btn-ban {
  padding: 7px 14px;
  border-radius: 8px;
  border: 1px solid rgba(255,170,0,0.25);
  background: rgba(255,170,0,0.08);
  color: var(--orange);
  font-size: 0.8rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-ban:hover { background: rgba(255,170,0,0.18); transform: translateY(-1px); }
.btn-unban {
  padding: 7px 14px;
  border-radius: 8px;
  border: 1px solid rgba(0,229,160,0.25);
  background: rgba(0,229,160,0.08);
  color: var(--green);
  font-size: 0.8rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-unban:hover { background: rgba(0,229,160,0.18); transform: translateY(-1px); }
.ban-type-row { display: flex; gap: 10px; margin-bottom: 16px; }
.ban-type-btn {
  flex: 1; padding: 9px; border-radius: 10px;
  border: 1px solid var(--border); background: none;
  color: var(--muted); font-size: .82rem; font-weight: 600;
  font-family: 'Syne', sans-serif; cursor: pointer; transition: all .2s;
}
.ban-type-btn.active { border-color: var(--orange); color: var(--orange); background: rgba(255,170,0,0.08); }

/* Modal */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.2s;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal.active {
  display: flex;
}
.modal-content {
  background: var(--sidebar);
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 28px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease both;
}
@keyframes slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.modal-title {
  font-family: 'Syne', sans-serif;
  font-size: 1.2rem;
  font-weight: 700;
}
.modal-close {
  background: none;
  border: none;
  color: var(--muted);
  font-size: 1.5rem;
  cursor: pointer;
  transition: color 0.2s;
}
.modal-close:hover {
  color: var(--text);
}
.field {
  margin-bottom: 16px;
}
.field label {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 8px;
}
.field input, .field select {
  width: 100%;
  padding: 11px 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}
.field input:focus, .field select:focus {
  border-color: var(--blue);
  background: rgba(59, 140, 247, 0.05);
}
.btn-primary {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, var(--blue), #5b6ef7);
  border: none;
  border-radius: 10px;
  color: white;
  font-family: 'Syne', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 140, 247, 0.3);
}

.view { display: none; }
.view.active { display: block; }

@media (max-width: 768px) {
  .sidebar { width: 180px; }
  .main { margin-left: 180px; padding: 20px; }
  .search-bar { flex-direction: column; }
  .stats-grid { grid-template-columns: 1fr; }
}
@keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-logo">Start<span>Smart</span><em>:</em></div>
  <nav class="sidebar-nav">
    <div class="nav-item active" onclick="switchTab('users')" id="nav-users">
      <span class="icon">👥</span> Utilisateurs
    </div>
    <div class="nav-item" onclick="switchTab('startups')" id="nav-startups">
      <span class="icon">🚀</span> Startups
    </div>
    <div class="nav-item" onclick="switchTab('stats')" id="nav-stats">
      <span class="icon">◔</span> Statistiques
    </div>
  </nav>
  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar">
        <?php if($adminPhoto): ?>
        <img src="<?= htmlspecialchars($adminPhoto) ?>" alt="Photo">
        <?php else: ?>
        <?= htmlspecialchars($initials ?: 'A') ?>
        <?php endif; ?>
      </div>
      <div class="admin-info">
        <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
        <div class="admin-role">● En ligne</div>
      </div>
    </div>
    <a href="../../api/auth.php?action=logout" class="btn-logout">Se déconnecter</a>
  </div>
</aside>

<!-- Main Content -->
<main class="main">
  <div class="page-header">
    <div class="page-title">Gestion Administrative</div>
    <div class="page-subtitle">Gérez les utilisateurs et startups de la plateforme</div>
  </div>

  <!-- USERS VIEW -->
  <div id="view-users" class="view active">
    <div class="card">
      <div class="card-header">
        <div class="card-title">Utilisateurs</div>
      </div>
      <div class="search-bar">
        <div class="search-input-wrap">
          <input class="search-input" type="text" id="search-users" placeholder="Rechercher par nom, prénom, email…" value="<?= htmlspecialchars($_GET['u_search'] ?? '') ?>">
        </div>
        <button class="btn-reset" onclick="resetSearch()">↻ Réinitialiser</button>
        <button class="btn-export" onclick="exportTable('users', 'csv')">CSV</button>
        <button class="btn-export" onclick="exportTable('users', 'pdf')">PDF</button>
        <button class="btn-add" onclick="openModal('createUserModal')">+ Nouvel Utilisateur</button>
      </div>
      <div class="card-body">
        <table>
          <thead>
            <tr>
              <th class="sortable" data-table="users" data-field="nom" onclick="sortTable('users', 'nom')">Nom <span class="sort-arrow">↕</span></th>
              <th class="sortable" data-table="users" data-field="prenom" onclick="sortTable('users', 'prenom')">Prénom <span class="sort-arrow">↕</span></th>
              <th class="sortable" data-table="users" data-field="email" onclick="sortTable('users', 'email')">Email <span class="sort-arrow">↕</span></th>
              <th class="sortable" data-table="users" data-field="statut" onclick="sortTable('users', 'statut')">Statut <span class="sort-arrow">↕</span></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="users-tbody">
            <?php foreach($users_list as $user): ?>
            <tr>
              <td>
                <div class="avatar-cell">
                  <div class="avatar">
                    <?php if(!empty($user['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Photo">
                    <?php else: ?>
                    <?= htmlspecialchars(substr($user['nom'] ?? '', 0, 1) . substr($user['prenom'] ?? '', 0, 1)) ?>
                    <?php endif; ?>
                  </div>
                  <div class="name-cell">
                    <strong><?= htmlspecialchars($user['nom'] ?? 'N/A') ?></strong>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($user['prenom'] ?? 'N/A') ?></td>
              <td style="color: var(--muted)"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
              <td>
                <span class="badge badge-<?= strtolower($user['statut'] ?? 'actif') ?>">
                  <?php if(($user['statut'] ?? 'actif') === 'actif'): ?>
                  ● actif
                  <?php elseif(($user['statut'] ?? 'actif') === 'pending'): ?>
                  ⏳ pending
                  <?php else: ?>
                  ✕ inactif
                  <?php endif; ?>
                </span>
              </td>
              <td class="actions">
                <button class="btn-edit" onclick="editUser(<?= (int)$user['id'] ?>)">Éditer</button>
                <?php if(($user['statut'] ?? '') === 'banni'): ?>
                <button class="btn-unban" onclick="unbanUser(<?= (int)$user['id'] ?>, 'user')">Débannir</button>
                <?php else: ?>
                <button class="btn-ban" onclick="openBanModal(<?= (int)$user['id'] ?>, 'user', <?= htmlspecialchars(json_encode(trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>)">Bannir</button>
                <?php endif; ?>
                <button class="btn-delete" onclick="deleteUser(<?= (int)$user['id'] ?>)">Supprimer</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <div><strong id="user-count"><?= $users_total ?></strong> utilisateurs <span id="users-page-summary"></span></div>
        <div class="pagination">
          <select class="page-size" id="users-page-size" onchange="changePageSize('users', this.value)">
            <option value="5">5</option>
            <option value="8" selected>8</option>
            <option value="10">10</option>
            <option value="20">20</option>
          </select>
          <div id="users-pagination"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- STARTUPS VIEW -->
  <div id="view-startups" class="view">
    <div class="card">
      <div class="card-header">
        <div class="card-title">Startups</div>
      </div>
      <div class="search-bar">
        <div class="search-input-wrap">
          <input class="search-input" type="text" id="search-startups" placeholder="Rechercher par nom, responsable, email…" value="<?= htmlspecialchars($_GET['s_search'] ?? '') ?>">
        </div>
        <button class="btn-reset" onclick="resetSearch()">↻ Réinitialiser</button>
        <button class="btn-export" onclick="exportTable('startups', 'csv')">CSV</button>
        <button class="btn-export" onclick="exportTable('startups', 'pdf')">PDF</button>
        <button class="btn-add" onclick="openModal('createStartupModal')">+ Nouvelle Startup</button>
      </div>
      <div class="card-body">
        <table>
          <thead>
            <tr>
              <th class="sortable" data-table="startups" data-field="nom_startup" onclick="sortTable('startups', 'nom_startup')">Startup <span class="sort-arrow">↕</span></th>
              <th class="sortable" data-table="startups" data-field="nom_responsable" onclick="sortTable('startups', 'nom_responsable')">Responsable <span class="sort-arrow">↕</span></th>
              <th class="sortable" data-table="startups" data-field="email" onclick="sortTable('startups', 'email')">Email <span class="sort-arrow">↕</span></th>
              <th class="sortable" data-table="startups" data-field="secteur" onclick="sortTable('startups', 'secteur')">Secteur <span class="sort-arrow">↕</span></th>
              <th class="sortable" data-table="startups" data-field="statut" onclick="sortTable('startups', 'statut')">Statut <span class="sort-arrow">↕</span></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="startups-tbody">
            <?php foreach($startups_list as $startup): ?>
            <tr>
              <td>
                <div class="avatar-cell">
                  <div class="avatar">
                    <?php if(!empty($startup['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($startup['profile_picture']) ?>" alt="Photo">
                    <?php else: ?>
                    <?= htmlspecialchars(substr($startup['nom_startup'] ?? '', 0, 2)) ?>
                    <?php endif; ?>
                  </div>
                  <div class="name-cell">
                    <strong><?= htmlspecialchars($startup['nom_startup'] ?? 'N/A') ?></strong>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($startup['nom_responsable'] ?? 'N/A') ?></td>
              <td style="color: var(--muted)"><?= htmlspecialchars($startup['email'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($startup['secteur'] ?? 'N/A') ?></td>
              <td>
                <span class="badge badge-<?= strtolower($startup['statut'] ?? 'actif') ?>">
                  <?php if(($startup['statut'] ?? 'actif') === 'actif'): ?>
                  ● actif
                  <?php elseif(($startup['statut'] ?? 'actif') === 'pending'): ?>
                  ⏳ pending
                  <?php else: ?>
                  ✕ inactif
                  <?php endif; ?>
                </span>
              </td>
              <td class="actions">
                <button class="btn-edit" onclick="editStartup(<?= (int)$startup['id'] ?>)">Éditer</button>
                <?php if(($startup['statut'] ?? '') === 'banni'): ?>
                <button class="btn-unban" onclick="unbanUser(<?= (int)$startup['id'] ?>, 'startup')">Débannir</button>
                <?php else: ?>
                <button class="btn-ban" onclick="openBanModal(<?= (int)$startup['id'] ?>, 'startup', <?= htmlspecialchars(json_encode($startup['nom_startup'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)">Bannir</button>
                <?php endif; ?>
                <button class="btn-delete" onclick="deleteStartup(<?= (int)$startup['id'] ?>)">Supprimer</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <div><strong id="startup-count"><?= $startups_total ?></strong> startups <span id="startups-page-summary"></span></div>
        <div class="pagination">
          <select class="page-size" id="startups-page-size" onchange="changePageSize('startups', this.value)">
            <option value="5">5</option>
            <option value="8" selected>8</option>
            <option value="10">10</option>
            <option value="20">20</option>
          </select>
          <div id="startups-pagination"></div>
        </div>
      </div>
    </div>
  </div>

  <div id="view-stats" class="view">
    <div class="stats-grid">
      <div class="stats-card">
        <div class="stats-title">Statistiques Utilisateurs</div>
        <div class="chart-wrap">
          <div class="donut-chart" id="users-stats-chart">
            <div class="donut-center">
              <div class="donut-total" id="users-stats-total">0</div>
              <div class="donut-label">Utilisateurs</div>
            </div>
          </div>
          <div class="stats-legend" id="users-stats-legend"></div>
        </div>
      </div>
      <div class="stats-card">
        <div class="stats-title">Statistiques Startups</div>
        <div class="chart-wrap">
          <div class="donut-chart" id="startups-stats-chart">
            <div class="donut-center">
              <div class="donut-total" id="startups-stats-total">0</div>
              <div class="donut-label">Startups</div>
            </div>
          </div>
          <div class="stats-legend" id="startups-stats-legend"></div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modals -->
<div id="createUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Nouvel Utilisateur</div>
      <button class="modal-close" onclick="closeModal('createUserModal')">✕</button>
    </div>
    <div class="field">
      <label>Nom</label>
      <input type="text" id="new-user-nom" placeholder="Nom">
    </div>
    <div class="field">
      <label>Prénom</label>
      <input type="text" id="new-user-prenom" placeholder="Prénom">
    </div>
    <div class="field">
      <label>Email</label>
      <input type="email" id="new-user-email" placeholder="email@example.com">
    </div>
    <div class="field">
      <label>Mot de passe</label>
      <input type="password" id="new-user-pass" placeholder="••••••••">
    </div>
    <button class="btn-primary" onclick="createUser()">Créer</button>
  </div>
</div>

<div id="createStartupModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Nouvelle Startup</div>
      <button class="modal-close" onclick="closeModal('createStartupModal')">✕</button>
    </div>
    <div class="field">
      <label>Nom de la Startup</label>
      <input type="text" id="new-startup-nom" placeholder="Nom">
    </div>
    <div class="field">
      <label>Responsable</label>
      <input type="text" id="new-startup-resp" placeholder="Nom">
    </div>
    <div class="field">
      <label>Email</label>
      <input type="email" id="new-startup-email" placeholder="email@example.com">
    </div>
    <div class="field">
      <label>Secteur</label>
      <select id="new-startup-secteur">
        <option value="">Sélectionner…</option>
        <option value="tech">Tech</option>
        <option value="sante">Santé</option>
        <option value="fintech">Fintech</option>
      </select>
    </div>
    <div class="field">
      <label>Mot de passe</label>
      <input type="password" id="new-startup-pass" placeholder="••••••••">
    </div>
    <button class="btn-primary" onclick="createStartup()">Créer</button>
  </div>
</div>

<!-- Ban Modal -->
<div id="banModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">🚫 Bannir <span id="ban-target-name"></span></div>
      <button class="modal-close" onclick="closeModal('banModal')">✕</button>
    </div>
    <input type="hidden" id="ban-user-id">
    <input type="hidden" id="ban-entity-type">
    <div class="field">
      <label>Type de ban</label>
      <div class="ban-type-row">
        <button class="ban-type-btn active" id="btn-ban-perm" onclick="selectBanType('permanent')">🔒 Permanent</button>
        <button class="ban-type-btn" id="btn-ban-timed" onclick="selectBanType('timed')">⏳ Temporaire</button>
      </div>
    </div>
    <div class="field" id="ban-duration-field" style="display:none">
      <label>Durée (heures)</label>
      <input type="number" id="ban-duration" min="1" max="8760" placeholder="ex: 24 = 1 jour, 168 = 1 semaine">
    </div>
    <div class="field">
      <label>Raison (optionnel)</label>
      <input type="text" id="ban-reason" placeholder="ex: Contenu inapproprié, spam…">
    </div>
    <div style="display:flex;gap:10px;margin-top:8px">
      <button class="btn-primary" style="background:linear-gradient(135deg,var(--orange),#e07b00);box-shadow:0 4px 14px rgba(255,170,0,0.25)" onclick="confirmBan()">Confirmer le ban</button>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Éditer Utilisateur</div>
      <button class="modal-close" onclick="closeModal('editUserModal')">✕</button>
    </div>
    <input type="hidden" id="edit-user-id">
    <div class="field"><label>Nom</label><input type="text" id="edit-user-nom" placeholder="Nom"></div>
    <div class="field"><label>Prénom</label><input type="text" id="edit-user-prenom" placeholder="Prénom"></div>
    <div class="field"><label>Email</label><input type="email" id="edit-user-email" placeholder="email@example.com"></div>
    <div class="field">
      <label>Rôle</label>
      <select id="edit-user-role">
        <option value="user">Utilisateur</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <div class="field">
      <label>Statut</label>
      <select id="edit-user-statut">
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
        <option value="banni">Banni</option>
        <option value="verifie">Vérifié</option>
      </select>
    </div>
    <div class="field"><label>Nouveau mot de passe (optionnel)</label><input type="password" id="edit-user-pass" placeholder="Laisser vide pour ne pas changer"></div>
    <button class="btn-primary" onclick="saveEditUser()">Enregistrer</button>
  </div>
</div>

<!-- Edit Startup Modal -->
<div id="editStartupModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Éditer Startup</div>
      <button class="modal-close" onclick="closeModal('editStartupModal')">✕</button>
    </div>
    <input type="hidden" id="edit-startup-id">
    <div class="field"><label>Nom de la Startup</label><input type="text" id="edit-startup-nom" placeholder="Nom startup"></div>
    <div class="field"><label>Nom Responsable</label><input type="text" id="edit-startup-resp" placeholder="Nom"></div>
    <div class="field"><label>Prénom Responsable</label><input type="text" id="edit-startup-prenom-resp" placeholder="Prénom"></div>
    <div class="field"><label>Email</label><input type="email" id="edit-startup-email" placeholder="email@example.com"></div>
    <div class="field">
      <label>Secteur</label>
      <select id="edit-startup-secteur">
        <option value="">Sélectionner…</option>
        <option value="tech">Tech</option>
        <option value="sante">Santé</option>
        <option value="fintech">Fintech</option>
        <option value="logistique">Logistique</option>
        <option value="retail">Retail</option>
        <option value="autre">Autre</option>
      </select>
    </div>
    <div class="field">
      <label>Statut</label>
      <select id="edit-startup-statut">
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
        <option value="banni">Banni</option>
        <option value="verifie">Vérifié</option>
      </select>
    </div>
    <div class="field"><label>Nouveau mot de passe (optionnel)</label><input type="password" id="edit-startup-pass" placeholder="Laisser vide pour ne pas changer"></div>
    <button class="btn-primary" onclick="saveEditStartup()">Enregistrer</button>
  </div>
</div>

<script>
/* ── Tab switching ── */
function setActiveTabVisual(tab) {
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('view-' + tab).classList.add('active');
  document.getElementById('nav-' + tab).classList.add('active');
}

function switchTab(tab) {
  setActiveTabVisual(tab);
  const url = new URL(window.location.href);
  url.searchParams.set('tab', tab);
  window.history.replaceState({}, '', url.toString());
}

const tableState = {
  users: { field: null, direction: 'ASC', search: '', page: 1, perPage: 8 },
  startups: { field: null, direction: 'ASC', search: '', page: 1, perPage: 8 }
};

const exportColumns = {
  users: [
    ['nom', 'Nom'],
    ['prenom', 'Prénom'],
    ['email', 'Email'],
    ['statut', 'Statut']
  ],
  startups: [
    ['nom_startup', 'Startup'],
    ['nom_responsable', 'Responsable'],
    ['email', 'Email'],
    ['secteur', 'Secteur'],
    ['statut', 'Statut']
  ]
};

const statsPalette = {
  actif: '#00e5a0',
  banni: '#ff5b6b',
  inactif: '#ffb020'
};

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[char]));
}

function escapeJsArg(value) {
  return escapeHtml(JSON.stringify(String(value ?? '')));
}

function normalizeText(value) {
  return String(value ?? '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function statusBadge(statut) {
  const value = statut || 'actif';
  if (value === 'actif') return '<span class="badge badge-actif">● actif</span>';
  if (value === 'pending') return '<span class="badge badge-pending">⏳ pending</span>';
  return `<span class="badge badge-${escapeHtml(value)}">✕ ${escapeHtml(value)}</span>`;
}

function avatarHtml(row, type) {
  if (row.profile_picture) {
    return `<img src="${escapeHtml(row.profile_picture)}" alt="Photo">`;
  }
  const text = type === 'users'
    ? `${row.nom?.[0] ?? ''}${row.prenom?.[0] ?? ''}`
    : String(row.nom_startup ?? '').slice(0, 2);
  return escapeHtml(text);
}

function userRow(row) {
  const fullName = `${row.nom ?? ''} ${row.prenom ?? ''}`.trim();
  const banButton = row.statut === 'banni'
    ? `<button class="btn-unban" onclick="unbanUser(${Number(row.id)}, 'user')">Débannir</button>`
    : `<button class="btn-ban" onclick="openBanModal(${Number(row.id)}, 'user', ${escapeJsArg(fullName)})">Bannir</button>`;

  return `<tr>
    <td><div class="avatar-cell"><div class="avatar">${avatarHtml(row, 'users')}</div><div class="name-cell"><strong>${escapeHtml(row.nom || 'N/A')}</strong></div></div></td>
    <td>${escapeHtml(row.prenom || 'N/A')}</td>
    <td style="color: var(--muted)">${escapeHtml(row.email || 'N/A')}</td>
    <td>${statusBadge(row.statut)}</td>
    <td class="actions">
      <button class="btn-edit" onclick="editUser(${Number(row.id)})">Éditer</button>
      ${banButton}
      <button class="btn-delete" onclick="deleteUser(${Number(row.id)})">Supprimer</button>
    </td>
  </tr>`;
}

function startupRow(row) {
  const banButton = row.statut === 'banni'
    ? `<button class="btn-unban" onclick="unbanUser(${Number(row.id)}, 'startup')">Débannir</button>`
    : `<button class="btn-ban" onclick="openBanModal(${Number(row.id)}, 'startup', ${escapeJsArg(row.nom_startup)})">Bannir</button>`;

  return `<tr>
    <td><div class="avatar-cell"><div class="avatar">${avatarHtml(row, 'startups')}</div><div class="name-cell"><strong>${escapeHtml(row.nom_startup || 'N/A')}</strong></div></div></td>
    <td>${escapeHtml(row.nom_responsable || 'N/A')}</td>
    <td style="color: var(--muted)">${escapeHtml(row.email || 'N/A')}</td>
    <td>${escapeHtml(row.secteur || 'N/A')}</td>
    <td>${statusBadge(row.statut)}</td>
    <td class="actions">
      <button class="btn-edit" onclick="editStartup(${Number(row.id)})">Éditer</button>
      ${banButton}
      <button class="btn-delete" onclick="deleteStartup(${Number(row.id)})">Supprimer</button>
    </td>
  </tr>`;
}

function getFilteredRows(type) {
  const state = tableState[type];
  const rows = type === 'users' ? [...usersData] : [...startupsData];
  const query = normalizeText(state.search);
  const searchableFields = type === 'users'
    ? ['nom', 'prenom', 'email', 'statut']
    : ['nom_startup', 'nom_responsable', 'prenom_responsable', 'email', 'secteur', 'statut'];

  const filtered = query
    ? rows.filter(row => searchableFields.some(field => normalizeText(row[field]).includes(query)))
    : rows;

  if (state.field) {
    filtered.sort((a, b) => {
      const left = normalizeText(a[state.field]);
      const right = normalizeText(b[state.field]);
      return state.direction === 'ASC' ? left.localeCompare(right) : right.localeCompare(left);
    });
  }

  return filtered;
}

function renderTable(type) {
  const rows = getFilteredRows(type);
  const state = tableState[type];
  const tbody = document.getElementById(type === 'users' ? 'users-tbody' : 'startups-tbody');
  const count = document.getElementById(type === 'users' ? 'user-count' : 'startup-count');
  const totalPages = Math.max(1, Math.ceil(rows.length / state.perPage));
  state.page = Math.min(Math.max(1, state.page), totalPages);
  const start = (state.page - 1) * state.perPage;
  const visibleRows = rows.slice(start, start + state.perPage);

  tbody.innerHTML = visibleRows.length
    ? visibleRows.map(row => type === 'users' ? userRow(row) : startupRow(row)).join('')
    : `<tr><td colspan="${type === 'users' ? 5 : 6}" style="color: var(--muted)">Aucun résultat</td></tr>`;
  count.textContent = rows.length;
  updateSortArrows(type);
  renderPagination(type, rows.length, totalPages);
}

function sortTable(type, field) {
  const state = tableState[type];
  state.direction = state.field === field && state.direction === 'ASC' ? 'DESC' : 'ASC';
  state.field = field;
  state.page = 1;
  renderTable(type);
}

function renderPagination(type, totalRows, totalPages) {
  const state = tableState[type];
  const container = document.getElementById(type + '-pagination');
  const summary = document.getElementById(type + '-page-summary');
  if (!container || !summary) return;

  const start = totalRows ? (state.page - 1) * state.perPage + 1 : 0;
  const end = Math.min(state.page * state.perPage, totalRows);
  summary.textContent = `(${start}-${end})`;

  const buttons = [];
  buttons.push(`<button type="button" onclick="goToPage('${type}', ${state.page - 1})" ${state.page === 1 ? 'disabled' : ''}>‹</button>`);

  const firstPage = Math.max(1, state.page - 2);
  const lastPage = Math.min(totalPages, firstPage + 4);
  for (let page = firstPage; page <= lastPage; page++) {
    buttons.push(`<button type="button" class="${page === state.page ? 'active' : ''}" onclick="goToPage('${type}', ${page})">${page}</button>`);
  }

  buttons.push(`<button type="button" onclick="goToPage('${type}', ${state.page + 1})" ${state.page === totalPages ? 'disabled' : ''}>›</button>`);
  container.innerHTML = buttons.join('');
}

function goToPage(type, page) {
  tableState[type].page = page;
  renderTable(type);
}

function changePageSize(type, value) {
  tableState[type].perPage = parseInt(value, 10) || 8;
  tableState[type].page = 1;
  renderTable(type);
}

function updateSortArrows(type) {
  const state = tableState[type];
  document.querySelectorAll(`th.sortable[data-table="${type}"]`).forEach(th => {
    const active = th.dataset.field === state.field;
    th.classList.toggle('is-active', active);
    const arrow = th.querySelector('.sort-arrow');
    if (arrow) arrow.textContent = active ? (state.direction === 'ASC' ? '▲' : '▼') : '↕';
  });
}

function bindDynamicSearch(type) {
  const input = document.getElementById('search-' + type);
  if (!input) return;

  input.value = '';
  input.addEventListener('input', () => {
    tableState[type].search = input.value.trim();
    tableState[type].page = 1;
    renderTable(type);
  });
}

function resetSearch() {
  const type = document.querySelector('.view.active').id.split('-')[1];
  const input = document.getElementById('search-' + type);
  if (input) input.value = '';
  tableState[type].search = '';
  tableState[type].field = null;
  tableState[type].direction = 'ASC';
  tableState[type].page = 1;
  renderTable(type);
}

function exportTable(type, format) {
  const rows = getFilteredRows(type);
  if (!rows.length) {
    alert('Aucune donnée à exporter.');
    return;
  }

  if (format === 'csv') {
    exportCsv(type, rows);
    return;
  }

  exportPdf(type, rows);
}

function exportCsv(type, rows) {
  const columns = exportColumns[type];
  const csvRows = [
    columns.map(([, label]) => label),
    ...rows.map(row => columns.map(([field]) => row[field] ?? ''))
  ];
  const csv = csvRows.map(row => row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(';')).join('\n');
  const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `${type}-startsmart.csv`;
  document.body.appendChild(link);
  link.click();
  URL.revokeObjectURL(link.href);
  link.remove();
}

function exportPdf(type, rows) {
  const columns = exportColumns[type];
  const title = type === 'users' ? 'Utilisateurs' : 'Startups';
  const body = rows.map(row => `<tr>${columns.map(([field]) => `<td>${escapeHtml(row[field] || '')}</td>`).join('')}</tr>`).join('');
  const popup = window.open('', '_blank');
  if (!popup) {
    alert('Veuillez autoriser les popups pour exporter en PDF.');
    return;
  }

  popup.document.write(`<!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="UTF-8">
      <title>${escapeHtml(title)} - StartSmart</title>
      <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 32px; }
        h1 { font-size: 22px; margin: 0 0 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f2f4f8; }
      </style>
    </head>
    <body>
      <h1>${escapeHtml(title)} - StartSmart</h1>
      <table>
        <thead><tr>${columns.map(([, label]) => `<th>${escapeHtml(label)}</th>`).join('')}</tr></thead>
        <tbody>${body}</tbody>
      </table>
      <script>window.onload = () => { window.print(); window.close(); }<\/script>
    </body>
    </html>`);
  popup.document.close();
}

function getStatusStats(rows) {
  const stats = { actif: 0, banni: 0, inactif: 0 };
  rows.forEach(row => {
    const status = String(row.statut ?? '').toLowerCase();
    if (status === 'actif') stats.actif += 1;
    else if (status === 'banni') stats.banni += 1;
    else stats.inactif += 1;
  });
  return stats;
}

function donutBackground(stats) {
  const total = stats.actif + stats.banni + stats.inactif;
  if (!total) {
    return 'conic-gradient(rgba(255,255,255,0.08) 0deg 360deg)';
  }

  const actifDeg = (stats.actif / total) * 360;
  const banniDeg = (stats.banni / total) * 360;
  const inactifDeg = 360 - actifDeg - banniDeg;
  return `conic-gradient(
    ${statsPalette.actif} 0deg ${actifDeg}deg,
    ${statsPalette.banni} ${actifDeg}deg ${actifDeg + banniDeg}deg,
    ${statsPalette.inactif} ${actifDeg + banniDeg}deg ${actifDeg + banniDeg + inactifDeg}deg
  )`;
}

function renderStatsCard(type, rows) {
  const stats = getStatusStats(rows);
  const total = rows.length;
  const chart = document.getElementById(`${type}-stats-chart`);
  const totalEl = document.getElementById(`${type}-stats-total`);
  const legend = document.getElementById(`${type}-stats-legend`);
  if (!chart || !totalEl || !legend) return;

  chart.style.background = donutBackground(stats);
  totalEl.textContent = String(total);
  legend.innerHTML = [
    ['actif', 'Actifs'],
    ['banni', 'Bannis'],
    ['inactif', 'Inactifs']
  ].map(([key, label]) => `
    <div class="legend-item">
      <div class="legend-label">
        <span class="legend-dot" style="background:${statsPalette[key]}"></span>
        <span>${label}</span>
      </div>
      <div class="legend-value">${stats[key]}</div>
    </div>
  `).join('');
}

function renderStatistics() {
  renderStatsCard('users', usersData);
  renderStatsCard('startups', startupsData);
}

/* ── BAN / UNBAN ── */
let currentBanType = 'permanent';

function selectBanType(type) {
  currentBanType = type;
  document.getElementById('btn-ban-perm').classList.toggle('active', type === 'permanent');
  document.getElementById('btn-ban-timed').classList.toggle('active', type === 'timed');
  document.getElementById('ban-duration-field').style.display = type === 'timed' ? 'block' : 'none';
}

function openBanModal(id, entity, name) {
  document.getElementById('ban-user-id').value    = id;
  document.getElementById('ban-entity-type').value= entity;
  document.getElementById('ban-target-name').textContent = name;
  document.getElementById('ban-reason').value     = '';
  document.getElementById('ban-duration').value   = '';
  selectBanType('permanent');
  openModal('banModal');
}

function confirmBan() {
  const id       = document.getElementById('ban-user-id').value;
  const entity   = document.getElementById('ban-entity-type').value;
  const reason   = document.getElementById('ban-reason').value.trim();
  const duration = document.getElementById('ban-duration').value;
  if (currentBanType === 'timed' && (!duration || parseInt(duration) < 1)) {
    alert('Veuillez entrer une durée valide (en heures).'); return;
  }
  postToApi('ban_user', { id, entity, ban_type: currentBanType, ban_duration: duration, ban_reason: reason });
}

function unbanUser(id, entity) {
  if (confirm('Débannir cet utilisateur ?')) {
    postToApi('unban_user', { id, entity });
  }
}


function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal').forEach(modal => {
  modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('active'); });
});

/* ── POST helper (form-based) ── */
function postToApi(action, fields) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/users.php?action=' + action;
  Object.entries(fields).forEach(([k, v]) => {
    const i = document.createElement('input');
    i.type = 'hidden'; i.name = k; i.value = v ?? '';
    form.appendChild(i);
  });
  document.body.appendChild(form);
  form.submit();
}

/* ── CREATE USER ── */
function createUser() {
  const nom    = document.getElementById('new-user-nom').value.trim();
  const prenom = document.getElementById('new-user-prenom').value.trim();
  const email  = document.getElementById('new-user-email').value.trim();
  const pass   = document.getElementById('new-user-pass').value;
  if (!nom || !prenom || !email || !pass) { alert('Veuillez remplir tous les champs.'); return; }
  postToApi('create_user', { nom, prenom, email, password: pass, password_confirm: pass, role: 'user', statut: 'actif' });
}

/* ── CREATE STARTUP ── */
function createStartup() {
  const nom_startup  = document.getElementById('new-startup-nom').value.trim();
  const nom_resp     = document.getElementById('new-startup-resp').value.trim();
  const email        = document.getElementById('new-startup-email').value.trim();
  const secteur      = document.getElementById('new-startup-secteur').value;
  const pass         = document.getElementById('new-startup-pass').value;
  if (!nom_startup || !nom_resp || !email || !pass) { alert('Veuillez remplir tous les champs.'); return; }
  postToApi('create_user', { nom: nom_resp, prenom: '', email, password: pass, password_confirm: pass,
    role: 'startup', statut: 'actif', nom_startup, nom_responsable: nom_resp, secteur });
}

/* ── DELETE USER ── */
function deleteUser(id) {
  if (confirm('Supprimer cet utilisateur définitivement ?')) {
    postToApi('delete_user', { id });
  }
}

/* ── DELETE STARTUP ── */
function deleteStartup(id) {
  if (confirm('Supprimer cette startup définitivement ?')) {
    postToApi('delete_startup', { id });
  }
}

/* ── EDIT USER (load into modal) ── */
function editUser(id) {
  // Fetch user data from embedded PHP data
  const row = usersData.find(u => u.id == id);
  if (!row) { alert('Utilisateur introuvable.'); return; }
  document.getElementById('edit-user-id').value    = id;
  document.getElementById('edit-user-nom').value   = row.nom    ?? '';
  document.getElementById('edit-user-prenom').value= row.prenom ?? '';
  document.getElementById('edit-user-email').value = row.email  ?? '';
  document.getElementById('edit-user-role').value  = row.role   ?? 'user';
  document.getElementById('edit-user-statut').value= row.statut ?? 'actif';
  openModal('editUserModal');
}

function saveEditUser() {
  const id     = document.getElementById('edit-user-id').value;
  const nom    = document.getElementById('edit-user-nom').value.trim();
  const prenom = document.getElementById('edit-user-prenom').value.trim();
  const email  = document.getElementById('edit-user-email').value.trim();
  const role   = document.getElementById('edit-user-role').value;
  const statut = document.getElementById('edit-user-statut').value;
  const pass   = document.getElementById('edit-user-pass').value;
  if (!nom || !prenom || !email) { alert('Nom, prénom et email sont requis.'); return; }
  const fields = { id, nom, prenom, email, role, statut };
  if (pass) { fields.password = pass; fields.password_confirm = pass; }
  postToApi('update_user', fields);
}

/* ── EDIT STARTUP (load into modal) ── */
function editStartup(id) {
  const row = startupsData.find(s => s.id == id);
  if (!row) { alert('Startup introuvable.'); return; }
  document.getElementById('edit-startup-id').value           = id;
  document.getElementById('edit-startup-nom').value          = row.nom_startup         ?? '';
  document.getElementById('edit-startup-resp').value         = row.nom_responsable      ?? '';
  document.getElementById('edit-startup-prenom-resp').value  = row.prenom_responsable   ?? '';
  document.getElementById('edit-startup-email').value        = row.email               ?? '';
  document.getElementById('edit-startup-secteur').value      = row.secteur             ?? '';
  document.getElementById('edit-startup-statut').value       = row.statut              ?? 'actif';
  openModal('editStartupModal');
}

function saveEditStartup() {
  const id              = document.getElementById('edit-startup-id').value;
  const nom_startup     = document.getElementById('edit-startup-nom').value.trim();
  const nom_responsable = document.getElementById('edit-startup-resp').value.trim();
  const prenom_resp     = document.getElementById('edit-startup-prenom-resp').value.trim();
  const email           = document.getElementById('edit-startup-email').value.trim();
  const secteur         = document.getElementById('edit-startup-secteur').value;
  const statut          = document.getElementById('edit-startup-statut').value;
  const pass            = document.getElementById('edit-startup-pass').value;
  if (!nom_startup || !nom_responsable || !email) { alert('Nom, responsable et email sont requis.'); return; }
  const fields = { id, nom_startup, nom_responsable, prenom_responsable: prenom_resp, email, secteur, statut, stade: 'idee' };
  if (pass) { fields.password = pass; fields.password_confirm = pass; }
  postToApi('update_startup', fields);
}

/* ── Embed PHP data for JS access ── */
const usersData    = <?= json_encode(array_values($users_list)) ?>;
const startupsData = <?= json_encode(array_values($startups_list)) ?>;

/* ── Set initial tab ── */
const tab = new URLSearchParams(window.location.search).get('tab') || 'users';
setActiveTabVisual(['users', 'startups', 'stats'].includes(tab) ? tab : 'users');
document.getElementById('users-page-size').value = String(tableState.users.perPage);
document.getElementById('startups-page-size').value = String(tableState.startups.perPage);
bindDynamicSearch('users');
bindDynamicSearch('startups');
renderTable('users');
renderTable('startups');
renderStatistics();
</script>
</body>
</html>
