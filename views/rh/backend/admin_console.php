<?php
$activeSection = $_GET['section'] ?? 'users';
$sectionAliases = [
    'rh' => 'project-projects',
    'projets' => 'project-projects',
    'projects' => 'project-projects',
    'categories' => 'project-categories',
    'forum' => 'forum-posts',
    'posts' => 'forum-posts',
    'comments' => 'forum-comments',
    'commentaires' => 'forum-comments',
];
$activeSection = $sectionAliases[$activeSection] ?? $activeSection;
$sections = [
    'users' => ['label' => 'Utilisateurs', 'icon' => '👥'],
    'startups' => ['label' => 'Startups', 'icon' => '🚀'],
    'evenements' => ['label' => 'Événements', 'icon' => '📅'],
    'forum-posts' => ['label' => 'Posts Forum', 'icon' => '💬'],
    'forum-comments' => ['label' => 'Commentaires', 'icon' => '🗨️'],
    'project-projects' => ['label' => 'Projets', 'icon' => '📂'],
    'project-categories' => ['label' => 'Catégories', 'icon' => '🏷️'],
    'stats' => ['label' => 'Statistiques', 'icon' => '◔'],
];
if (!isset($sections[$activeSection])) {
    $activeSection = 'users';
}

function adminStatusBadge($status) {
    $status = strtolower(trim((string)$status));
    $value = $status !== '' ? $status : 'actif';
    if ($value === 'actif') return '<span class="badge badge-actif">● actif</span>';
    if ($value === 'pending') return '<span class="badge badge-pending">⏳ pending</span>';
    return '<span class="badge badge-' . htmlspecialchars($value) . '">✕ ' . htmlspecialchars($value) . '</span>';
}

function adminName($row) {
    $full = trim((string)($row['full_name'] ?? ''));
    if ($full !== '') return $full;
    return trim((string)($row['prenom'] ?? '') . ' ' . (string)($row['nom'] ?? '')) ?: 'Utilisateur';
}

function adminStartupName($row) {
    $name = trim((string)($row['company_name'] ?? ''));
    if ($name !== '') return $name;
    $name = trim((string)($row['nom_startup'] ?? ''));
    if ($name !== '') return $name;
    return adminName($row);
}

function adminInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $second = strtoupper(substr($parts[1] ?? $first, 0, 1));
    return $first . $second;
}

function adminExcerpt($text, $limit = 80) {
    $text = trim((string)$text);
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...');
    }
    return strlen($text) > $limit ? substr($text, 0, $limit - 3) . '...' : $text;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — StartSmart</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<!-- jsPDF loaded lazily on first PDF export click — never blocks page render -->

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

/* Alerts */
.alert { padding: 15px; margin-bottom: 25px; border-radius: 10px; font-weight: 500; font-size: 0.9rem; }
.alert-success { background: rgba(0, 229, 160, 0.1); color: var(--green); border: 1px solid rgba(0, 229, 160, 0.2); }
.alert-error { background: rgba(255, 91, 107, 0.1); color: var(--red); border: 1px solid rgba(255, 91, 107, 0.2); }

* { box-sizing: border-box; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  margin: 0;
}
a { color: inherit; text-decoration: none; }
.admin-shell {
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
  position: sticky;
  top: 0;
  z-index: 50;
}
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
.nav-item--front {
  color: #00e5a0;
  border-color: rgba(0, 229, 160, 0.25);
  background: rgba(0, 229, 160, 0.06);
  margin-bottom: 8px;
}
.nav-item--front:hover {
  color: #00e5a0;
  background: rgba(0, 229, 160, 0.12);
  border-color: rgba(0, 229, 160, 0.4);
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
  flex: 1;
  min-height: 100vh;
  padding: 40px;
}
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
  margin-bottom: 30px;
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
.btn-search, .btn-reset, .btn-export, button.btn-export {
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
.btn-search:hover, .btn-reset:hover, .btn-export:hover, button.btn-export:hover {
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
.table-wrap {
  overflow-x: auto;
}
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
}
tbody tr {
  border-top: 1px solid var(--border);
  transition: background 0.2s;
}
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
.badge-inactif, .badge-banni { background: rgba(255, 91, 107, 0.1); color: var(--red); border: 1px solid rgba(255, 91, 107, 0.2); }

.actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.btn-edit, .btn-delete, .btn-ban {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 7px 14px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.04);
  font-size: 0.8rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
  color: var(--text);
}
.btn-edit {
  background: rgba(59, 140, 247, 0.1);
  border-color: rgba(59, 140, 247, 0.2);
  color: var(--blue);
}
.btn-edit:hover { background: rgba(59, 140, 247, 0.2); transform: translateY(-1px); }

.btn-delete {
  background: rgba(255, 91, 107, 0.1);
  border-color: rgba(255, 91, 107, 0.2);
  color: var(--red);
}
.btn-delete:hover { background: rgba(255, 91, 107, 0.2); transform: translateY(-1px); }

.btn-ban {
  background: rgba(255, 170, 0, 0.1);
  border-color: rgba(255, 170, 0, 0.2);
  color: var(--orange);
}
.btn-ban:hover { background: rgba(255, 170, 0, 0.2); transform: translateY(-1px); }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(200px, 1fr));
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
  color: var(--muted);
}
.stats-value {
  font-family: 'Syne', sans-serif;
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1;
}

@media (max-width: 768px) {
  .admin-shell { flex-direction: column; }
  .sidebar { width: 100%; min-height: auto; position: static; border-right: none; border-bottom: 1px solid var(--border); }
  .main { padding: 20px; }
  .search-bar { flex-direction: column; }
  .stats-grid { grid-template-columns: 1fr; }
}
</style>

<div class="admin-shell">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">Start<span>Smart</span><em>:</em></div>
    <nav class="sidebar-nav">
      <a class="nav-item nav-item--front" href="index.php">
        <span class="icon">⬅️</span> Front Office
      </a>
      <?php foreach ($sections as $key => $section): ?>
          <a href="rh.php?page=backend/admin&section=<?php echo $key; ?>" class="nav-item <?php echo $activeSection === $key ? 'active' : ''; ?>">
              <span class="icon"><?php echo htmlspecialchars($section['icon']); ?></span>
              <?php echo htmlspecialchars($section['label']); ?>
          </a>
      <?php endforeach; ?>
      <a class="nav-item" href="rh.php?page=job-offer/index&action=index" title="Offres d'emploi et candidatures (hors console admin)">
        <span class="icon">RH</span> Recrutement RH
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar">
          <?php echo htmlspecialchars($currentAdmin['initials']); ?>
        </div>
        <div class="admin-info">
          <div class="admin-name"><?php echo htmlspecialchars($currentAdmin['name']); ?></div>
          <div class="admin-role">● En ligne</div>
        </div>
      </div>
      <a href="logout.php" class="btn-logout">Se déconnecter</a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main">
    <?php
    $__flashSuccess = $_SESSION['flash']['success'] ?? $_SESSION['form_success'] ?? null;
    $__flashError   = $_SESSION['flash']['error']   ?? $_SESSION['form_error']   ?? null;
    unset($_SESSION['flash']['success'], $_SESSION['flash']['error'], $_SESSION['form_success'], $_SESSION['form_error']);
    ?>
    <?php if ($__flashSuccess): ?>
      <div class="alert alert-success">✅ <?php echo htmlspecialchars($__flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($__flashError): ?>
      <div class="alert alert-error">❌ <?php echo htmlspecialchars($__flashError); ?></div>
    <?php endif; ?>
    <div class="page-header">
      <div class="page-title">Gestion Administrative</div>
      <div class="page-subtitle">Gérez les utilisateurs et startups de la plateforme</div>
    </div>

    <?php if ($activeSection === 'stats'): ?>
        <div class="stats-grid">
            <div class="stats-card"><div class="stats-title">Utilisateurs</div><div class="stats-value"><?php echo (int)$stats['users']; ?></div></div>
            <div class="stats-card"><div class="stats-title">Startups</div><div class="stats-value"><?php echo (int)$stats['startups']; ?></div></div>
            <div class="stats-card"><div class="stats-title">Projets</div><div class="stats-value"><?php echo (int)$stats['projets']; ?></div></div>
            <div class="stats-card"><div class="stats-title">Événements</div><div class="stats-value"><?php echo (int)($stats['evenements'] ?? 0); ?></div></div>
            <div class="stats-card"><div class="stats-title">Sujets forum</div><div class="stats-value"><?php echo (int)$stats['posts']; ?></div></div>
            <div class="stats-card"><div class="stats-title">Commentaires forum</div><div class="stats-value"><?php echo (int)$stats['comments']; ?></div></div>
            <div class="stats-card"><div class="stats-title">Catégories</div><div class="stats-value"><?php echo (int)$stats['categories']; ?></div></div>
            <div class="stats-card"><div class="stats-title">Offres emploi</div><div class="stats-value"><?php echo (int)($stats['job_offers'] ?? 0); ?></div></div>
            <div class="stats-card"><div class="stats-title">Candidatures</div><div class="stats-value"><?php echo (int)($stats['applications'] ?? 0); ?></div></div>
            <div class="stats-card"><div class="stats-title">Employés (RH)</div><div class="stats-value"><?php echo (int)($stats['employees'] ?? 0); ?></div></div>
        </div>
        <div class="search-bar" style="margin-top: 28px;">
            <a class="btn-export" href="rh.php?page=backend/adminExport&amp;format=csv&amp;dataset=stats">Télécharger CSV (résumé)</a>
            <button type="button" class="btn-export" onclick="exportStatsPdf(); return false;">Télécharger PDF (résumé)</button>
        </div>
    <?php elseif ($activeSection === 'users' || $activeSection === 'startups'): ?>
        <?php $rows = $activeSection === 'startups' ? $startupUsers : $standardUsers; ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title"><?php echo $activeSection === 'startups' ? 'Startups' : 'Utilisateurs'; ?></div>
            </div>
            <div class="search-bar">
                <div class="search-input-wrap">
                    <input class="search-input" type="search" placeholder="Rechercher par nom, email..." oninput="filterAdminTable(this.value)">
                </div>
                <a class="btn-reset" href="rh.php?page=backend/admin&section=<?php echo $activeSection; ?>">↻ Réinitialiser</a>
                <a class="btn-export" href="rh.php?page=backend/adminExport&amp;format=csv&amp;dataset=<?php echo $activeSection === 'startups' ? 'startups' : 'users'; ?>">CSV (complet)</a>
                <button type="button" class="btn-export" onclick="exportDataTablePdf('<?php echo $activeSection === 'startups' ? 'Startups' : 'Utilisateurs'; ?>'); return false;">PDF (vue tableau)</button>
                <a class="btn-add" href="#" onclick="openModal('createUserModal'); return false;">+ Nouvel <?php echo $activeSection === 'startups' ? 'Startup' : 'Utilisateur'; ?></a>
            </div>
            <div class="table-wrap">
                <table id="adminDataTable">
                    <thead>
                        <?php if ($activeSection === 'startups'): ?>
                            <tr><th>Startup</th><th>Responsable</th><th>Email</th><th>Statut</th><th>Actions</th></tr>
                        <?php else: ?>
                            <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="5" style="color: var(--muted); text-align: center;">Aucun élément.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row):
                            $name = $activeSection === 'startups' ? adminStartupName($row) : adminName($row);
                            $rowJson = htmlspecialchars(json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                            $statut = strtolower((string)($row['statut'] ?? 'actif'));
                            ?>
                            <tr>
                                <td>
                                    <div class="avatar-cell">
                                        <div class="avatar"><?php echo htmlspecialchars(adminInitials($name)); ?></div>
                                        <div class="name-cell"><strong><?php echo htmlspecialchars($name); ?></strong></div>
                                    </div>
                                </td>
                                <?php if ($activeSection === 'startups'): ?>
                                    <?php
                                    $resp = trim(implode(' ', array_filter([
                                        (string)($row['prenom_responsable'] ?? ''),
                                        (string)($row['nom_responsable'] ?? ''),
                                    ])));
                                    if ($resp === '') {
                                        $resp = adminName($row);
                                    }
                                    ?>
                                    <td style="color: var(--muted);"><?php echo htmlspecialchars($resp); ?></td>
                                    <td><?php echo htmlspecialchars((string)($row['email'] ?? '')); ?></td>
                                    <td><?php echo adminStatusBadge($row['statut'] ?? 'actif'); ?></td>
                                    <td class="actions">
                                        <a class="btn-edit" href="#" onclick="editStartup(<?php echo $rowJson; ?>); return false;">Éditer</a>
                                        <?php if ($statut === 'banni'): ?>
                                            <a class="btn-edit" href="#" onclick="unbanUser(<?php echo (int)($row['id'] ?? 0); ?>); return false;">Débannir</a>
                                        <?php else: ?>
                                            <a class="btn-ban" href="#" onclick="openBanModal(<?php echo (int)($row['id'] ?? 0); ?>, <?php echo json_encode($name, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>); return false;">Bannir</a>
                                        <?php endif; ?>
                                        <a class="btn-delete" href="#" onclick="deleteUser(<?php echo (int)($row['id'] ?? 0); ?>); return false;">Supprimer</a>
                                    </td>
                                <?php else: ?>
                                    <td><?php echo htmlspecialchars((string)($row['email'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(strtoupper((string)($row['role'] ?? 'user'))); ?></td>
                                    <td><?php echo adminStatusBadge($row['statut'] ?? 'actif'); ?></td>
                                    <td class="actions">
                                        <a class="btn-edit" href="#" onclick="editUser(<?php echo $rowJson; ?>); return false;">Éditer</a>
                                        <?php if ($statut === 'banni'): ?>
                                            <a class="btn-edit" href="#" onclick="unbanUser(<?php echo (int)($row['id'] ?? 0); ?>); return false;">Débannir</a>
                                        <?php else: ?>
                                            <a class="btn-ban" href="#" onclick="openBanModal(<?php echo (int)($row['id'] ?? 0); ?>, <?php echo json_encode($name, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>); return false;">Bannir</a>
                                        <?php endif; ?>
                                        <a class="btn-delete" href="#" onclick="deleteUser(<?php echo (int)($row['id'] ?? 0); ?>); return false;">Supprimer</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($activeSection === 'project-projects'): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Gestion Projet</div>
            </div>
            <div class="search-bar">
                <div class="search-input-wrap">
                    <input class="search-input" type="search" placeholder="Rechercher un projet..." oninput="filterAdminTable(this.value)">
                </div>
                <a class="btn-export" href="index.php?controller=projet&action=index">Catalogue public</a>
                <a class="btn-export" href="rh.php?page=backend/projets">Vue avancée</a>
                <a class="btn-export" href="rh.php?page=backend/adminExport&amp;format=csv&amp;dataset=project-projects">CSV (tous les projets)</a>
                <button type="button" class="btn-export" onclick="exportDataTablePdf('Projets'); return false;">PDF (vue tableau)</button>
                <a class="btn-add" href="index.php?controller=projet&action=create">+ Nouveau projet</a>
            </div>
            <div class="table-wrap">
                <table id="adminDataTable">
                    <thead><tr><th>Projet</th><th>Auteur</th><th>Catégorie</th><th>Budget</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if (empty($recentProjets)): ?>
                        <tr><td colspan="5" style="color: var(--muted); text-align: center;">Aucun projet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentProjets as $row): ?>
                            <tr>
                                <td>
                                    <div class="avatar-cell">
                                        <div class="avatar">📂</div>
                                        <div class="name-cell"><strong><?php echo htmlspecialchars($row['nomprojet'] ?? 'Projet'); ?></strong></div>
                                    </div>
                                </td>
                                <td style="color: var(--muted);"><?php echo htmlspecialchars($row['auteur_nom'] ?? 'Utilisateur'); ?></td>
                                <td><?php echo htmlspecialchars($row['categorie_nom'] ?? 'Général'); ?></td>
                                <td><?php echo number_format((float)($row['budget'] ?? 0), 0, ',', ' '); ?> DT</td>
                                <td class="actions">
                                    <a class="btn-edit" href="index.php?controller=projet&action=show&id=<?php echo (int)$row['id']; ?>">Voir</a>
                                    <a class="btn-ban" href="index.php?controller=projet&action=edit&id=<?php echo (int)$row['id']; ?>">Modifier</a>
                                    <a class="btn-delete" href="index.php?controller=projet&action=delete&id=<?php echo (int)$row['id']; ?>">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($activeSection === 'project-categories'): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Catégories Projet</div>
            </div>
            <div class="search-bar">
                <div class="search-input-wrap">
                    <input class="search-input" type="search" placeholder="Rechercher une catégorie..." oninput="filterAdminTable(this.value)">
                </div>
                <a class="btn-export" href="index.php?controller=projet&action=index">Catalogue public</a>
                <a class="btn-export" href="rh.php?page=backend/projets">Vue avancée</a>
                <a class="btn-export" href="rh.php?page=backend/adminExport&amp;format=csv&amp;dataset=project-categories">CSV (catégories)</a>
                <button type="button" class="btn-export" onclick="exportDataTablePdf('Catégories projet'); return false;">PDF (vue tableau)</button>
                <a class="btn-add" href="index.php?controller=categorie&action=create">+ Nouvelle catégorie</a>
            </div>
            <div class="table-wrap">
                <table id="adminDataTable">
                    <thead><tr><th>Numéro</th><th>Catégorie</th><th>Investisseur</th><th>Projets</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="5" style="color: var(--muted); text-align: center;">Aucune catégorie.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['num'] ?? ''); ?></td>
                                <td>
                                    <div class="avatar-cell">
                                        <div class="avatar">🏷️</div>
                                        <div class="name-cell"><strong><?php echo htmlspecialchars($row['typeprojet'] ?? 'Catégorie'); ?></strong></div>
                                    </div>
                                </td>
                                <td style="color: var(--muted);"><?php echo htmlspecialchars($row['nom_investisseur'] ?? ''); ?></td>
                                <td><?php echo (int)($row['projets_count'] ?? 0); ?></td>
                                <td class="actions">
                                    <a class="btn-edit" href="index.php?controller=categorie&action=edit&id=<?php echo (int)$row['id']; ?>">Modifier</a>
                                    <a class="btn-delete" href="index.php?controller=categorie&action=delete&id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($activeSection === 'forum-posts'): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Posts Forum</div>
            </div>
            <div class="search-bar">
                <div class="search-input-wrap">
                    <input class="search-input" type="search" placeholder="Rechercher un post..." oninput="filterAdminTable(this.value)">
                </div>
                <a class="btn-export" href="index.php?controller=post&action=index">Forum public</a>
                <a class="btn-export" href="rh.php?page=backend/forum">Vue avancée</a>
                <a class="btn-export" href="rh.php?page=backend/adminExport&amp;format=csv&amp;dataset=forum-posts">CSV (posts)</a>
                <button type="button" class="btn-export" onclick="exportDataTablePdf('Posts forum'); return false;">PDF (vue tableau)</button>
                <a class="btn-add" href="index.php?controller=post&action=create">+ Nouveau sujet</a>
            </div>
            <div class="table-wrap">
                <table id="adminDataTable">
                    <thead><tr><th>Sujet</th><th>Contenu</th><th>Auteur</th><th>Thème</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if (empty($recentPosts)): ?>
                        <tr><td colspan="6" style="color: var(--muted); text-align: center;">Aucun post.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentPosts as $row): ?>
                            <tr>
                                <td>
                                    <div class="avatar-cell">
                                        <div class="avatar">💬</div>
                                        <div class="name-cell"><strong><?php echo htmlspecialchars($row['titre'] ?? 'Sujet'); ?></strong></div>
                                    </div>
                                </td>
                                <td style="color: var(--muted); font-size: 0.85rem;"><?php echo htmlspecialchars(adminExcerpt($row['contenu'] ?? '', 95)); ?></td>
                                <td style="color: var(--muted);"><?php echo htmlspecialchars($row['auteur_nom'] ?? 'Utilisateur'); ?></td>
                                <td><?php echo htmlspecialchars($row['topic'] ?? 'Général'); ?></td>
                                <td><?php echo adminStatusBadge($row['statut'] ?? 'actif'); ?></td>
                                <td class="actions">
                                    <a class="btn-edit" href="index.php?controller=post&action=show&id=<?php echo (int)$row['id_post']; ?>">Voir</a>
                                    <a class="btn-ban" href="index.php?controller=post&action=edit&id=<?php echo (int)$row['id_post']; ?>">Gérer</a>
                                    <a class="btn-delete" href="index.php?controller=post&action=delete&id=<?php echo (int)$row['id_post']; ?>">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($activeSection === 'evenements'): ?>
        <!-- ── Statistics summary ── -->
        <?php
        $evStatsSorted = $evenements ?? [];
        $pcounts = $participantCounts ?? [];
        usort($evStatsSorted, function($a, $b) use ($pcounts) {
            return ($pcounts[(int)($b['id']??0)] ?? 0) <=> ($pcounts[(int)($a['id']??0)] ?? 0);
        });
        $evTotal = count($evStatsSorted);
        $evTotalPart = array_sum($pcounts);
        $evMax = !empty($pcounts) ? max($pcounts) : 1;
        ?>
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <div class="card-title">📊 Statistiques des Événements</div>
                <span style="color:var(--muted);font-size:0.82rem;">classement par participants ↓</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.06);">
                <div style="text-align:center;padding:12px;background:rgba(59,140,247,0.08);border-radius:10px;">
                    <div style="font-size:1.6rem;font-weight:800;color:var(--blue);"><?php echo $evTotal; ?></div>
                    <div style="font-size:.78rem;color:var(--muted);margin-top:3px;">Événements</div>
                </div>
                <div style="text-align:center;padding:12px;background:rgba(0,229,160,0.08);border-radius:10px;">
                    <div style="font-size:1.6rem;font-weight:800;color:var(--green);"><?php echo $evTotalPart; ?></div>
                    <div style="font-size:.78rem;color:var(--muted);margin-top:3px;">Participants total</div>
                </div>
                <div style="text-align:center;padding:12px;background:rgba(255,200,0,0.08);border-radius:10px;">
                    <div style="font-size:1.6rem;font-weight:800;color:#f59e0b;"><?php echo $evTotal > 0 ? round($evTotalPart/$evTotal,1) : 0; ?></div>
                    <div style="font-size:.78rem;color:var(--muted);margin-top:3px;">Moy. / événement</div>
                </div>
                <div style="text-align:center;padding:12px;background:rgba(180,83,9,0.08);border-radius:10px;">
                    <div style="font-size:1.6rem;font-weight:800;color:#b45309;"><?php echo (int)$evMax; ?></div>
                    <div style="font-size:.78rem;color:var(--muted);margin-top:3px;">Record participants</div>
                </div>
            </div>
            <?php if (!empty($evStatsSorted)): ?>
            <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($evStatsSorted as $rank => $ev):
                    $evId2  = (int)($ev['id'] ?? 0);
                    $cnt    = (int)($pcounts[$evId2] ?? 0);
                    $cap2   = ($ev['capacite'] !== null && $ev['capacite'] !== '') ? (int)$ev['capacite'] : 0;
                    $pct    = $evMax > 0 ? round(($cnt / $evMax) * 100) : 0;
                    $fill   = $cap2 > 0 ? min(100, round($cnt / $cap2 * 100)) : 0;
                    $medal  = $rank === 0 ? '🥇' : ($rank === 1 ? '🥈' : ($rank === 2 ? '🥉' : ($rank+1).'.'));
                    $barClr = $rank === 0 ? '#f59e0b' : ($rank === 1 ? '#94a3b8' : ($rank === 2 ? '#b45309' : 'var(--blue)'));
                ?>
                <div style="display:flex;flex-direction:column;gap:5px;padding:10px 12px;background:rgba(255,255,255,0.03);border-radius:8px;border:1px solid rgba(255,255,255,0.06);">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-size:1.1rem;min-width:28px;"><?php echo $medal; ?></span>
                            <div>
                                <a href="index.php?url=evenement/show&id=<?php echo $evId2; ?>" style="font-weight:700;font-size:.88rem;color:var(--blue);text-decoration:none;">
                                    <?php echo htmlspecialchars($ev['titre'] ?? '—'); ?>
                                </a>
                                <div style="font-size:.75rem;color:var(--muted);">
                                    <?php echo !empty($ev['date_evenement']) ? date('d/m/Y', strtotime($ev['date_evenement'])) : ''; ?>
                                    <?php if (!empty($ev['lieu'])): ?> · <?php echo htmlspecialchars($ev['lieu']); ?><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <span style="background:<?php echo $barClr; ?>;color:#fff;border-radius:20px;padding:3px 12px;font-size:.8rem;font-weight:700;">
                            <?php echo $cnt; ?> participant<?php echo $cnt !== 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-bottom:3px;">
                            <span>Part relative au record</span><span><?php echo $pct; ?>%</span>
                        </div>
                        <div style="background:rgba(255,255,255,0.08);border-radius:99px;height:7px;overflow:hidden;">
                            <div style="width:<?php echo $pct; ?>%;background:<?php echo $barClr; ?>;height:100%;border-radius:99px;"></div>
                        </div>
                    </div>
                    <?php if ($cap2 > 0): ?>
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-bottom:3px;">
                            <span>Remplissage (<?php echo $cnt; ?>/<?php echo $cap2; ?>)</span><span><?php echo $fill; ?>%</span>
                        </div>
                        <div style="background:rgba(255,255,255,0.08);border-radius:99px;height:5px;overflow:hidden;">
                            <div style="width:<?php echo $fill; ?>%;background:<?php echo $fill>=100 ? 'var(--red)' : 'var(--green)'; ?>;height:100%;border-radius:99px;"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="text-align:center;color:var(--muted);padding:24px;">Aucun événement pour le moment.</p>
            <?php endif; ?>
        </div>

        <!-- ── Events table ── -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">📅 Gestion des Événements</div>
                <span style="color:var(--muted);font-size:0.82rem;"><?php echo count($evenements ?? []); ?> événement(s)</span>
            </div>
            <div class="search-bar">
                <div class="search-input-wrap">
                    <input class="search-input" type="search" autocomplete="off" placeholder="Rechercher un événement..." oninput="filterAdminTable(this.value)">
                </div>
                <a class="btn-export" href="index.php?url=evenement/index">Vue publique</a>
                <a class="btn-export" href="rh.php?page=backend/adminExport&format=pdf&dataset=evenements" target="_blank">📄 PDF</a>
                <button class="btn-add" onclick="evCreate()">+ Nouvel événement</button>
            </div>
            <div class="table-wrap">
                <table id="adminDataTable">
                    <thead>
                        <tr>
                            <th>Événement</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Capacité</th>
                            <th>Participants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($evenements)): ?>
                        <tr><td colspan="7" style="color:var(--muted);text-align:center;padding:32px;">Aucun événement pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($evenements as $ev):
                            $evId = (int)($ev['id'] ?? 0);
                            $nbPart = (int)($participantCounts[$evId] ?? 0);
                            $capacite = ($ev['capacite'] !== null && $ev['capacite'] !== '') ? (int)$ev['capacite'] : null;
                            $statut = strtolower(trim((string)($ev['statut'] ?? 'actif')));
                            $statutColor = $statut === 'annule' ? 'var(--red)' : ($statut === 'termine' ? 'var(--muted)' : 'var(--green)');
                            $statutLabel = $statut === 'annule' ? '✕ Annulé' : ($statut === 'termine' ? '■ Terminé' : '● Actif');
                            $evJson = htmlspecialchars(json_encode([
                                'id'             => $evId,
                                'titre'          => (string)($ev['titre'] ?? ''),
                                'date_evenement' => (string)($ev['date_evenement'] ?? ''),
                                'lieu'           => (string)($ev['lieu'] ?? ''),
                                'statut'         => (string)($ev['statut'] ?? 'Ouvert'),
                                'capacite'       => (string)($ev['capacite'] ?? ''),
                                'description'    => (string)($ev['description'] ?? ''),
                                'image_url'      => (string)($ev['image_url'] ?? ''),
                                'participants'   => $nbPart,
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr data-ev="<?php echo $evJson; ?>" data-evid="<?php echo $evId; ?>">
                                <td>
                                    <div class="avatar-cell">
                                        <div class="avatar" style="background:linear-gradient(135deg,rgba(0,229,160,0.25),rgba(0,200,120,0.15));">📅</div>
                                        <div class="name-cell">
                                            <strong><?php echo htmlspecialchars($ev['titre'] ?? 'Sans titre'); ?></strong>
                                            <?php if (!empty($ev['description'])): ?>
                                                <small><?php echo htmlspecialchars(mb_strimwidth(strip_tags($ev['description']), 0, 60, '…')); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:var(--muted);">
                                    <?php echo !empty($ev['date_evenement']) ? date('d/m/Y', strtotime($ev['date_evenement'])) : '—'; ?>
                                </td>
                                <td style="color:var(--muted);"><?php echo htmlspecialchars($ev['lieu'] ?? '—'); ?></td>
                                <td>
                                    <?php if ($capacite !== null): ?>
                                        <span style="color:<?php echo $nbPart >= $capacite ? 'var(--red)' : 'var(--text)'; ?>">
                                            <?php echo $capacite; ?> places
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--muted);">Illimitée</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background:rgba(59,140,247,0.12);color:var(--blue);">
                                        <?php echo $nbPart; ?>
                                        <?php if ($capacite !== null): echo ' / ' . $capacite; endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="color:<?php echo $statutColor; ?>;background:rgba(255,255,255,0.05);border:1px solid currentColor;">
                                        <?php echo $statutLabel; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn-edit"    onclick="evView(this)">Voir</button>
                                    <button class="btn-ban"     onclick="evEdit(this)">Modifier</button>
                                    <button class="btn-edit" style="background:rgba(59,140,247,0.1);border-color:rgba(59,140,247,0.2);color:var(--blue);" onclick="evInscrire(this)">Inscrire</button>
                                    <button class="btn-delete"  onclick="evDelete(this)">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($activeSection === 'forum-comments'): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Commentaires Forum</div>
            </div>
            <div class="search-bar">
                <div class="search-input-wrap">
                    <input class="search-input" type="search" placeholder="Rechercher un commentaire..." oninput="filterAdminTable(this.value)">
                </div>
                <a class="btn-export" href="index.php?controller=post&action=index">Forum public</a>
                <a class="btn-export" href="rh.php?page=backend/forum">Vue avancée</a>
                <a class="btn-export" href="rh.php?page=backend/adminExport&amp;format=csv&amp;dataset=forum-comments">CSV (commentaires)</a>
                <button type="button" class="btn-export" onclick="exportDataTablePdf('Commentaires forum'); return false;">PDF (vue tableau)</button>
            </div>
            <div class="table-wrap">
                <table id="adminDataTable">
                    <thead><tr><th>Commentaire</th><th>Post</th><th>Auteur</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if (empty($recentComments)): ?>
                        <tr><td colspan="5" style="color: var(--muted); text-align: center;">Aucun commentaire.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($recentComments, 0, 12) as $row): ?>
                            <tr>
                                <td>
                                    <div class="avatar-cell">
                                        <div class="avatar">🗨️</div>
                                        <div class="name-cell"><strong><?php echo htmlspecialchars(adminExcerpt($row['contenu'] ?? '', 80)); ?></strong></div>
                                    </div>
                                </td>
                                <td style="color: var(--muted);"><?php echo htmlspecialchars($row['post_titre'] ?? 'Post'); ?></td>
                                <td><?php echo htmlspecialchars($row['auteur_nom'] ?? 'Utilisateur'); ?></td>
                                <td style="color: var(--muted);"><?php echo !empty($row['date_creation']) ? date('d/m/Y H:i', strtotime($row['date_creation'])) : ''; ?></td>
                                <td class="actions">
                                    <a class="btn-edit" href="index.php?controller=commentaire&action=edit&id=<?php echo (int)$row['id_commentaire']; ?>">Éditer</a>
                                    <a class="btn-delete" href="index.php?controller=commentaire&action=delete&id=<?php echo (int)$row['id_commentaire']; ?>" onclick="return confirm('Supprimer ce commentaire ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
  </main>
</div>
<script>
window.ADMIN_STATS = <?php echo json_encode($stats ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.ADMIN_STAT_LABELS = {
  users: 'Utilisateurs (hors startup)',
  startups: 'Startups',
  projets: 'Projets',
  posts: 'Sujets forum',
  comments: 'Commentaires forum',
  categories: 'Catégories projet',
  job_offers: 'Offres emploi (RH)',
  applications: 'Candidatures (RH)',
  employees: 'Employés (RH)'
};

/* ── PDF helpers (lazy-load jsPDF, fall back to print) ── */
var _jspdfLoading = false;
var _jspdfCallbacks = [];

function _loadJsPdf(cb) {
  if (window.jspdf && window.jspdf.jsPDF) { cb(); return; }
  _jspdfCallbacks.push(cb);
  if (_jspdfLoading) return;
  _jspdfLoading = true;
  var s1 = document.createElement('script');
  s1.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
  s1.onload = function() {
    var s2 = document.createElement('script');
    s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js';
    s2.onload = function() { _jspdfCallbacks.forEach(function(fn){ fn(); }); _jspdfCallbacks = []; };
    s2.onerror = function() { _printFallback(); };
    document.head.appendChild(s2);
  };
  s1.onerror = function() { _printFallback(); };
  document.head.appendChild(s1);
}

function _printFallback(title) {
  var win = window.open('', '_blank');
  if (!win) { window.print(); return; }
  var tbl = document.getElementById('adminDataTable');
  var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + (title||'Export') + '</title>'
    + '<style>body{font-family:Arial,sans-serif;font-size:11px;} table{border-collapse:collapse;width:100%;}'
    + 'th,td{border:1px solid #ccc;padding:5px 8px;text-align:left;} th{background:#1e3a8a;color:#fff;}'
    + '.actions,.btn-edit,.btn-delete,.btn-ban,.btn-export{display:none!important;}</style></head><body>'
    + '<h2 style="margin-bottom:10px;">StartSmart — ' + (title||'Export') + '</h2>'
    + '<p style="color:#666;font-size:10px;">Généré le ' + new Date().toLocaleString('fr-FR') + '</p>'
    + (tbl ? tbl.outerHTML : '<p>Aucun tableau trouvé.</p>')
    + '</body></html>';
  win.document.write(html);
  win.document.close();
  win.focus();
  setTimeout(function(){ win.print(); }, 400);
}

function exportStatsPdf() {
  _loadJsPdf(function() {
    var { jsPDF } = window.jspdf;
    var doc = new jsPDF({ unit: 'pt', format: 'a4' });
    var stats = window.ADMIN_STATS || {};
    var labels = window.ADMIN_STAT_LABELS || {};
    doc.setFontSize(16);
    doc.text('StartSmart — Statistiques', 40, 48);
    doc.setFontSize(10);
    var y = 80;
    Object.keys(stats).forEach(function(key) {
      doc.text((labels[key] || key) + ' : ' + String(stats[key]), 40, y);
      y += 20;
    });
    doc.setFontSize(9); doc.setTextColor(120);
    doc.text('Généré le ' + new Date().toLocaleString('fr-FR'), 40, y + 14);
    doc.save('startsmart_stats_' + new Date().toISOString().slice(0,10) + '.pdf');
  });
}

function exportDataTablePdf(title) {
  var tbl = document.getElementById('adminDataTable');
  if (!tbl) { alert('Aucun tableau à exporter sur cette page.'); return; }
  _loadJsPdf(function() {
    if (!window.jspdf || !window.jspdf.jsPDF) { _printFallback(title); return; }
    var { jsPDF } = window.jspdf;
    var doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
    doc.setFontSize(13);
    doc.text('StartSmart — ' + (title || 'Export'), 40, 40);
    doc.autoTable({
      html: tbl,
      startY: 54,
      styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak' },
      headStyles: { fillColor: [15, 20, 40] },
      columnStyles: { 6: { cellWidth: 'wrap' } },
      didParseCell: function(data) {
        if (data.column.index === 6) { data.cell.text = ''; }
      },
      theme: 'striped',
      margin: { left: 36, right: 36 }
    });
    var safe = String(title || 'export').replace(/[^\w\-]+/g, '_');
    doc.save(safe + '_' + new Date().toISOString().slice(0,10) + '.pdf');
  });
}

function filterAdminTable(value) {
    const needle = String(value || '').toLowerCase();
    document.querySelectorAll('#adminDataTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(needle) ? '' : 'none';
    });
}
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function postToApi(action, fields) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'api/users.php?action=' + action;
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
  postToApi('create_user', { nom, prenom, email, password: pass, password_confirm: pass, role: 'user', statut: 'actif', section: 'users' });
}

/* ── CREATE STARTUP ── */
function createStartup() {
  const nom_startup  = document.getElementById('new-startup-nom').value.trim();
  const nom_resp     = document.getElementById('new-startup-resp').value.trim();
  const email        = document.getElementById('new-startup-email').value.trim();
  const secteur      = document.getElementById('new-startup-secteur').value;
  const pass         = document.getElementById('new-startup-pass').value;
  if (!nom_startup || !nom_resp || !email || !pass) { alert('Veuillez remplir tous les champs.'); return; }
  // Map 'nom_responsable' to 'nom' for unified backend, 'nom_startup' maps to 'company_name'
  postToApi('create_user', { nom: nom_resp, prenom: '', email, password: pass, password_confirm: pass, role: 'startup', statut: 'actif', company_name: nom_startup, profession: secteur, section: 'startups' });
}

/* ── EDIT USER ── */
function editUser(user) {
  document.getElementById('edit-user-id').value = user.id;
  document.getElementById('edit-user-nom').value = user.nom;
  document.getElementById('edit-user-prenom').value = user.prenom;
  document.getElementById('edit-user-email').value = user.email;
  document.getElementById('edit-user-role').value = user.role || 'user';
  document.getElementById('edit-user-statut').value = user.statut || 'actif';
  openModal('editUserModal');
}

function saveEditUser() {
  const id = document.getElementById('edit-user-id').value;
  const nom = document.getElementById('edit-user-nom').value.trim();
  const prenom = document.getElementById('edit-user-prenom').value.trim();
  const email = document.getElementById('edit-user-email').value.trim();
  const role = document.getElementById('edit-user-role').value;
  const statut = document.getElementById('edit-user-statut').value;
  const pass = document.getElementById('edit-user-pass').value;
  if (!nom || !prenom || !email) { alert('Nom, prénom et email sont requis.'); return; }
  postToApi('update_user', { id, nom, prenom, email, role, statut, password: pass, section: 'users' });
}

/* ── EDIT STARTUP ── */
function editStartup(startup) {
  document.getElementById('edit-startup-id').value = startup.id;
  document.getElementById('edit-startup-nom').value = startup.company_name || startup.nom_startup || '';
  document.getElementById('edit-startup-resp').value = startup.nom || '';
  document.getElementById('edit-startup-email').value = startup.email || '';
  document.getElementById('edit-startup-secteur').value = startup.profession || startup.secteur || '';
  document.getElementById('edit-startup-statut').value = startup.statut || 'actif';
  openModal('editStartupModal');
}

function saveEditStartup() {
  const id = document.getElementById('edit-startup-id').value;
  const nom_startup = document.getElementById('edit-startup-nom').value.trim();
  const nom_responsable = document.getElementById('edit-startup-resp').value.trim();
  const email = document.getElementById('edit-startup-email').value.trim();
  const secteur = document.getElementById('edit-startup-secteur').value;
  const statut = document.getElementById('edit-startup-statut').value;
  const pass = document.getElementById('edit-startup-pass').value;
  if (!nom_startup || !nom_responsable || !email) { alert('Nom, responsable et email sont requis.'); return; }
  postToApi('update_user', { id, nom: nom_responsable, prenom: '', email, role: 'startup', statut, password: pass, company_name: nom_startup, profession: secteur, section: 'startups' });
}

/* ── BAN / UNBAN ── */
let currentBanType = 'permanent';

function selectBanType(type) {
  currentBanType = type;
  document.getElementById('btn-ban-perm').classList.toggle('active', type === 'permanent');
  document.getElementById('btn-ban-timed').classList.toggle('active', type === 'timed');
  document.getElementById('ban-duration-field').style.display = type === 'timed' ? 'block' : 'none';
}

function openBanModal(id, name) {
  document.getElementById('ban-user-id').value = id;
  document.getElementById('ban-target-name').textContent = name;
  document.getElementById('ban-reason').value = '';
  document.getElementById('ban-duration').value = '';
  selectBanType('permanent');
  openModal('banModal');
}

function confirmBan() {
  const id = document.getElementById('ban-user-id').value;
  postToApi('ban_user', { id, section: '<?php echo $activeSection; ?>' });
}

function unbanUser(id) {
  if (confirm('Débannir cet utilisateur ?')) {
    postToApi('unban_user', { id, section: '<?php echo $activeSection; ?>' });
  }
}

/* ── DELETE USER ── */
function deleteUser(id) {
  if (confirm('Supprimer cet élément définitivement ?')) {
    postToApi('delete_user', { id, section: '<?php echo $activeSection; ?>' });
  }
}
</script>

<!-- Modals -->
<div id="createUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Nouvel Utilisateur</div>
      <button class="modal-close" onclick="closeModal('createUserModal')">✕</button>
    </div>
    <div class="field"><label>Nom</label><input type="text" id="new-user-nom" placeholder="Nom"></div>
    <div class="field"><label>Prénom</label><input type="text" id="new-user-prenom" placeholder="Prénom"></div>
    <div class="field"><label>Email</label><input type="email" id="new-user-email" placeholder="email@example.com"></div>
    <div class="field"><label>Mot de passe</label><input type="password" id="new-user-pass" placeholder="••••••••"></div>
    <button class="btn-primary" onclick="createUser()">Créer</button>
  </div>
</div>

<div id="createStartupModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Nouvelle Startup</div>
      <button class="modal-close" onclick="closeModal('createStartupModal')">✕</button>
    </div>
    <div class="field"><label>Nom de la Startup</label><input type="text" id="new-startup-nom" placeholder="Nom"></div>
    <div class="field"><label>Responsable</label><input type="text" id="new-startup-resp" placeholder="Nom"></div>
    <div class="field"><label>Email</label><input type="email" id="new-startup-email" placeholder="email@example.com"></div>
    <div class="field">
      <label>Secteur</label>
      <select id="new-startup-secteur">
        <option value="">Sélectionner…</option>
        <option value="tech">Tech</option>
        <option value="sante">Santé</option>
        <option value="fintech">Fintech</option>
      </select>
    </div>
    <div class="field"><label>Mot de passe</label><input type="password" id="new-startup-pass" placeholder="••••••••"></div>
    <button class="btn-primary" onclick="createStartup()">Créer</button>
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
        <option value="rh">RH</option>
      </select>
    </div>
    <div class="field">
      <label>Statut</label>
      <select id="edit-user-statut">
        <option value="actif">Actif</option>
        <option value="pending">En attente</option>
        <option value="banni">Banni</option>
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
    <div class="field"><label>Nom de la Startup</label><input type="text" id="edit-startup-nom" placeholder="Nom Startup"></div>
    <div class="field"><label>Responsable</label><input type="text" id="edit-startup-resp" placeholder="Nom"></div>
    <div class="field"><label>Email</label><input type="email" id="edit-startup-email" placeholder="email@example.com"></div>
    <div class="field">
      <label>Secteur</label>
      <select id="edit-startup-secteur">
        <option value="tech">Tech</option>
        <option value="sante">Santé</option>
        <option value="fintech">Fintech</option>
        <option value="">Autre</option>
      </select>
    </div>
    <div class="field">
      <label>Statut</label>
      <select id="edit-startup-statut">
        <option value="actif">Actif</option>
        <option value="pending">En attente</option>
        <option value="banni">Banni</option>
      </select>
    </div>
    <div class="field"><label>Nouveau mot de passe (optionnel)</label><input type="password" id="edit-startup-pass" placeholder="Laisser vide pour ne pas changer"></div>
    <button class="btn-primary" onclick="saveEditStartup()">Enregistrer</button>
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
    <div class="field">
      <label>Type de ban</label>
      <div class="ban-type-row" style="display:flex;gap:10px;margin-bottom:10px;">
        <button class="btn-primary active" id="btn-ban-perm" onclick="selectBanType('permanent')" style="flex:1;background:rgba(255,255,255,0.1);color:#fff;">🔒 Permanent</button>
        <button class="btn-primary" id="btn-ban-timed" onclick="selectBanType('timed')" style="flex:1;background:rgba(255,255,255,0.05);color:#fff;">⏳ Temporaire</button>
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
    <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 20px; margin-top: 20px;">Êtes-vous sûr de vouloir suspendre l'accès à ce compte ?</p>
    <button class="btn-primary" style="background:linear-gradient(135deg,var(--orange),#e07b00);box-shadow:0 4px 14px rgba(255,170,0,0.25)" onclick="confirmBan()">Confirmer le ban</button>
  </div>
</div>
<style>
/* Modal */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); align-items: center; justify-content: center; animation: fadeIn 0.2s; }
.modal.active { display: flex; }
.modal-content { background: var(--sidebar); border: 1px solid var(--border); border-radius: 18px; padding: 28px; width: 90%; max-width: 500px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.modal-title { font-family: 'Syne', sans-serif; font-size: 1.2rem; font-weight: 700; }
.modal-close { background: none; border: none; color: var(--muted); font-size: 1.5rem; cursor: pointer; }
.field { margin-bottom: 16px; }
.field label { display: block; font-size: 0.72rem; font-weight: 600; color: var(--muted); margin-bottom: 8px; }
.field input, .field select { width: 100%; padding: 11px 14px; background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border); border-radius: 10px; color: var(--text); }
.btn-primary { width: 100%; padding: 12px; background: linear-gradient(135deg, var(--blue), #5b6ef7); border: none; border-radius: 10px; color: white; cursor: pointer; }
</style>
<!-- Hidden form for event deletion -->
<form id="_evDeleteForm" method="post" action="" style="display:none;">
  <input type="hidden" name="from_admin" value="1">
</form>

<!-- ══════════════ EVENT MODALS ══════════════ -->
<style>
.ev-modal{display:none;position:fixed;z-index:2000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;}
.ev-modal.open{display:flex;}
.ev-modal-box{background:#0f1424;border:1px solid rgba(255,255,255,0.1);border-radius:18px;padding:28px 32px;width:95%;max-width:560px;max-height:90vh;overflow-y:auto;position:relative;}
.ev-modal-box h2{font-family:'Syne',sans-serif;font-size:1.15rem;font-weight:700;margin-bottom:20px;color:#e8eaf0;}
.ev-modal-close{position:absolute;top:18px;right:20px;background:none;border:none;color:rgba(232,234,240,0.5);font-size:1.4rem;cursor:pointer;line-height:1;}
.ev-modal-close:hover{color:#e8eaf0;}
.ev-field{margin-bottom:14px;}
.ev-field label{display:block;font-size:0.72rem;font-weight:600;color:rgba(232,234,240,0.5);margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;}
.ev-field input,.ev-field select,.ev-field textarea{width:100%;padding:10px 13px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:9px;color:#e8eaf0;font-size:.88rem;font-family:inherit;}
.ev-field textarea{resize:vertical;min-height:80px;}
.ev-field input:focus,.ev-field select,.ev-field textarea:focus{outline:none;border-color:#3b8cf7;}
.ev-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.ev-actions{display:flex;gap:10px;margin-top:20px;}
.ev-btn-save{flex:1;padding:11px;background:linear-gradient(135deg,#3b8cf7,#5b6ef7);border:none;border-radius:9px;color:#fff;font-weight:700;font-size:.88rem;cursor:pointer;font-family:'Syne',sans-serif;}
.ev-btn-save:hover{opacity:.9;}
.ev-btn-cancel{padding:11px 18px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:9px;color:#e8eaf0;cursor:pointer;font-size:.88rem;}
.ev-info-row{display:flex;flex-direction:column;gap:10px;margin-bottom:16px;}
.ev-info-item{display:flex;gap:10px;align-items:flex-start;font-size:.88rem;}
.ev-info-item strong{color:rgba(232,234,240,0.5);min-width:90px;font-size:.75rem;text-transform:uppercase;}
.ev-part-table{width:100%;border-collapse:collapse;font-size:.8rem;margin-top:12px;}
.ev-part-table th{background:rgba(255,255,255,0.06);padding:7px 10px;text-align:left;color:rgba(232,234,240,0.6);font-weight:600;font-size:.72rem;text-transform:uppercase;}
.ev-part-table td{padding:7px 10px;border-bottom:1px solid rgba(255,255,255,0.05);color:#e8eaf0;}
.ev-empty{text-align:center;padding:24px;color:rgba(232,234,240,0.4);font-size:.85rem;}
.ev-flash-err{background:rgba(255,91,107,0.15);border:1px solid rgba(255,91,107,0.3);color:#ff5b6b;border-radius:8px;padding:10px 14px;font-size:.82rem;margin-bottom:14px;display:none;}
</style>

<!-- Modal: Créer un événement -->
<div class="ev-modal" id="evCreateModal">
  <div class="ev-modal-box">
    <button class="ev-modal-close" onclick="evCloseAll()">✕</button>
    <h2>📅 Nouvel événement</h2>
    <div class="ev-flash-err" id="evCreateErr"></div>
    <form id="evCreateForm" method="post" action="index.php?url=evenement/store">
      <input type="hidden" name="from_admin" value="1">
      <div class="ev-field"><label>Titre *</label><input type="text" name="titre" id="ec-titre" required placeholder="Nom de l'événement"></div>
      <div class="ev-row">
        <div class="ev-field"><label>Date *</label><input type="date" name="date_evenement" id="ec-date" required></div>
        <div class="ev-field"><label>Capacité</label><input type="number" name="capacite" id="ec-cap" min="1" placeholder="Illimitée"></div>
      </div>
      <div class="ev-field"><label>Lieu *</label><input type="text" name="lieu" id="ec-lieu" required placeholder="Ville ou En ligne"></div>
      <div class="ev-field">
        <label>Statut</label>
        <select name="statut" id="ec-statut">
          <option value="Ouvert">Ouvert</option>
          <option value="Complet">Complet</option>
          <option value="Ferme">Fermé</option>
        </select>
      </div>
      <div class="ev-field"><label>URL image</label><input type="url" name="image_url" id="ec-img" placeholder="https://..."></div>
      <div class="ev-field"><label>Description *</label><textarea name="description" id="ec-desc" required placeholder="Décrivez l'événement..."></textarea></div>
      <div class="ev-actions">
        <button type="submit" class="ev-btn-save">✔ Créer l'événement</button>
        <button type="button" class="ev-btn-cancel" onclick="evCloseAll()">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Modifier un événement -->
<div class="ev-modal" id="evEditModal">
  <div class="ev-modal-box">
    <button class="ev-modal-close" onclick="evCloseAll()">✕</button>
    <h2>✏️ Modifier l'événement</h2>
    <div class="ev-flash-err" id="evEditErr"></div>
    <form id="evEditForm" method="post" action="">
      <input type="hidden" name="from_admin" value="1">
      <input type="hidden" id="ee-id" name="_ev_id">
      <div class="ev-field"><label>Titre *</label><input type="text" name="titre" id="ee-titre" required></div>
      <div class="ev-row">
        <div class="ev-field"><label>Date</label><input type="date" name="date_evenement" id="ee-date"></div>
        <div class="ev-field"><label>Capacité</label><input type="number" name="capacite" id="ee-cap" min="1"></div>
      </div>
      <div class="ev-field"><label>Lieu *</label><input type="text" name="lieu" id="ee-lieu" required></div>
      <div class="ev-field">
        <label>Statut</label>
        <select name="statut" id="ee-statut">
          <option value="Ouvert">Ouvert</option>
          <option value="Complet">Complet</option>
          <option value="Ferme">Fermé</option>
        </select>
      </div>
      <div class="ev-field"><label>URL image</label><input type="url" name="image_url" id="ee-img"></div>
      <div class="ev-field"><label>Description *</label><textarea name="description" id="ee-desc" required></textarea></div>
      <div class="ev-actions">
        <button type="submit" class="ev-btn-save">💾 Enregistrer</button>
        <button type="button" class="ev-btn-cancel" onclick="evCloseAll()">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Voir détails + participants -->
<div class="ev-modal" id="evViewModal">
  <div class="ev-modal-box" style="max-width:680px;">
    <button class="ev-modal-close" onclick="evCloseAll()">✕</button>
    <h2 id="vv-titre">📋 Détails de l'événement</h2>
    <div class="ev-info-row" id="vv-info"></div>
    <hr style="border-color:rgba(255,255,255,0.08);margin:16px 0;">
    <div style="font-size:.8rem;font-weight:700;color:rgba(232,234,240,0.5);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Participants (<span id="vv-count">0</span>)</div>
    <div id="vv-participants"><div class="ev-empty">Chargement...</div></div>
  </div>
</div>

<!-- Modal: Inscrire un participant -->
<div class="ev-modal" id="evInscrireModal">
  <div class="ev-modal-box">
    <button class="ev-modal-close" onclick="evCloseAll()">✕</button>
    <h2>👤 Inscrire un participant</h2>
    <div style="font-size:.8rem;color:rgba(232,234,240,0.5);margin-bottom:16px;">Événement : <strong id="ins-titre" style="color:#e8eaf0;"></strong></div>
    <div class="ev-flash-err" id="evInsErr"></div>
    <form id="evInsForm" method="post" action="">
      <input type="hidden" name="from_admin" value="1">
      <div class="ev-row">
        <div class="ev-field"><label>Prénom *</label><input type="text" name="prenom" id="ins-prenom" required></div>
        <div class="ev-field"><label>Nom *</label><input type="text" name="nom" id="ins-nom" required></div>
      </div>
      <div class="ev-row">
        <div class="ev-field"><label>Âge *</label><input type="number" name="age" id="ins-age" min="1" max="120" required></div>
        <div class="ev-field"><label>Téléphone</label><input type="text" name="telephone" id="ins-tel"></div>
      </div>
      <div class="ev-field"><label>Email *</label><input type="email" name="email" id="ins-email" required></div>
      <div class="ev-field"><label>Projet / Startup</label><input type="text" name="projet" id="ins-projet" placeholder="Optionnel"></div>
      <div class="ev-actions">
        <button type="submit" class="ev-btn-save">✔ Inscrire</button>
        <button type="button" class="ev-btn-cancel" onclick="evCloseAll()">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
function evCloseAll() {
  document.querySelectorAll('.ev-modal').forEach(function(m){ m.classList.remove('open'); });
}
document.querySelectorAll('.ev-modal').forEach(function(m){
  m.addEventListener('click', function(e){ if(e.target===m) evCloseAll(); });
});

/* ── Créer ── */
function evCreate() {
  document.getElementById('evCreateForm').reset();
  document.getElementById('evCreateErr').style.display='none';
  document.getElementById('evCreateModal').classList.add('open');
}

/* ── helper: get ev data from button's <tr> ── */
function _evData(btn) {
  var tr = btn.closest('tr');
  if (!tr) { alert('Erreur: ligne introuvable.'); return null; }
  try { return JSON.parse(tr.getAttribute('data-ev')); }
  catch(e) { alert('Erreur de données: ' + e.message); return null; }
}

/* ── Modifier ── */
function evEdit(btn) {
  var ev = _evData(btn); if (!ev) return;
  document.getElementById('ee-id').value     = ev.id;
  document.getElementById('ee-titre').value  = ev.titre;
  document.getElementById('ee-date').value   = ev.date_evenement;
  document.getElementById('ee-lieu').value   = ev.lieu;
  document.getElementById('ee-cap').value    = ev.capacite;
  document.getElementById('ee-img').value    = ev.image_url;
  document.getElementById('ee-desc').value   = ev.description;
  var sel = document.getElementById('ee-statut');
  for(var i=0;i<sel.options.length;i++){
    sel.options[i].selected = (sel.options[i].value === ev.statut);
  }
  document.getElementById('evEditForm').action = 'index.php?url=evenement/update&id='+ev.id;
  document.getElementById('evEditErr').style.display='none';
  document.getElementById('evEditModal').classList.add('open');
}

/* ── Voir ── */
function evView(btn) {
  var ev = _evData(btn); if (!ev) return;
  document.getElementById('vv-titre').textContent = '📋 ' + ev.titre;
  var cap = (ev.capacite !== '' && parseInt(ev.capacite) > 0) ? ev.capacite + ' places' : 'Illimitée';
  var fill = (ev.capacite !== '' && parseInt(ev.capacite) > 0) ? Math.min(100,Math.round(ev.participants/parseInt(ev.capacite)*100))+'%' : '—';
  document.getElementById('vv-info').innerHTML =
    '<div class="ev-info-item"><strong>Date</strong><span>'+(ev.date_evenement||'—')+'</span></div>'+
    '<div class="ev-info-item"><strong>Lieu</strong><span>'+(ev.lieu||'—')+'</span></div>'+
    '<div class="ev-info-item"><strong>Statut</strong><span>'+esc(ev.statut)+'</span></div>'+
    '<div class="ev-info-item"><strong>Capacité</strong><span>'+cap+'</span></div>'+
    '<div class="ev-info-item"><strong>Remplissage</strong><span>'+ev.participants+' inscrits ('+fill+')</span></div>'+
    '<div class="ev-info-item"><strong>Description</strong><span style="white-space:pre-wrap;">'+esc(ev.description)+'</span></div>';
  document.getElementById('vv-count').textContent = ev.participants;
  document.getElementById('vv-participants').innerHTML = '<div class="ev-empty">Chargement...</div>';
  document.getElementById('evViewModal').classList.add('open');
  fetch('api/evenements.php?action=participants&event_id='+ev.id)
    .then(function(r){ return r.json(); })
    .then(function(data){
      if(!data.length){ document.getElementById('vv-participants').innerHTML='<div class="ev-empty">Aucun participant inscrit.</div>'; return; }
      var html='<table class="ev-part-table"><thead><tr><th>#</th><th>Prénom Nom</th><th>Âge</th><th>Email</th><th>Téléphone</th><th>Projet</th></tr></thead><tbody>';
      data.forEach(function(p,i){
        html+='<tr><td>'+(i+1)+'</td><td>'+esc(p.prenom)+' '+esc(p.nom)+'</td><td>'+esc(p.age)+'</td><td>'+esc(p.email)+'</td><td>'+(p.telephone?esc(p.telephone):'—')+'</td><td>'+(p.projet?esc(p.projet):'—')+'</td></tr>';
      });
      html+='</tbody></table>';
      document.getElementById('vv-participants').innerHTML=html;
      document.getElementById('vv-count').textContent=data.length;
    })
    .catch(function(){ document.getElementById('vv-participants').innerHTML='<div class="ev-empty">Impossible de charger les participants.</div>'; });
}

/* ── Inscrire ── */
function evInscrire(btn) {
  var ev = _evData(btn); if (!ev) return;
  document.getElementById('ins-titre').textContent = ev.titre;
  document.getElementById('evInsForm').reset();
  document.getElementById('evInsForm').action = 'index.php?url=participant/store&event_id='+ev.id;
  document.getElementById('evInsErr').style.display='none';
  document.getElementById('evInscrireModal').classList.add('open');
}

/* ── Supprimer ── */
function evDelete(btn) {
  var id = btn.closest('tr').getAttribute('data-evid');
  if(!confirm('Supprimer cet événement et tous ses participants ?')) return;
  var f = document.getElementById('_evDeleteForm');
  f.action = 'index.php?url=evenement/delete&id='+id;
  f.submit();
}

function esc(s){ var d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }
</script>
</body>
</html>
