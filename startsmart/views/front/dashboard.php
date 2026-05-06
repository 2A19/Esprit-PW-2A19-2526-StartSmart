<?php
session_start();
require_once __DIR__ . '/../../controllers/AuthController.php';
$authController = new AuthController();
if (empty($_SESSION['user_id'])) {
    $authController->autoLoginFromRememberMe();
}
if(empty($_SESSION['user_id']) || empty($_SESSION['user_role'])){
    header('Location: ../../api/auth.php?action=logout');
    exit;
}

$role = $_SESSION['user_role'] ?? 'user';
$name = $_SESSION['user_name'] ?? 'Utilisateur';
$photo = $_SESSION['user_photo'] ?? null;
$initials = implode('', array_map(fn($w)=>strtoupper($w[0] ?? ''), array_filter(explode(' ',$name))));

// Load user/startup data if admin or self-view
$userData = null;
$isUser = $role === 'user';
$isStartup = $role === 'startup';

if($isUser || $isStartup){
    require_once __DIR__ . '/../../controllers/UserController.php';
    $controller = new UserController();
    
    if($isUser){
        $controller->getUser($_SESSION['user_id']);
        $userData = $_SESSION['user_detail'] ?? null;
    } else {
        $controller->getStartup($_SESSION['user_id']);
        $userData = $_SESSION['startup_detail'] ?? null;
    }
}
unset($_SESSION['user_detail'], $_SESSION['startup_detail']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StartSmart — Espace Personnel</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<script src="../../public/js/face-recognition.js"></script>
<style>
:root {
  --bg: #06080f;
  --sidebar: #0b0e1a;
  --blue: #3b8cf7;
  --green: #00e5a0;
  --red: #ff5b6b;
  --text: #e8eaf0;
  --muted: rgba(232,234,240,0.5);
  --card-bg: rgba(255,255,255,0.03);
  --border: rgba(255,255,255,0.07);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  overflow-x: hidden;
}

/* ── Background ── */
.bg-scene {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background:
    radial-gradient(ellipse 80% 60% at 10% 20%, rgba(59,140,247,0.1) 0%, transparent 60%),
    radial-gradient(ellipse 60% 50% at 90% 80%, rgba(0,229,160,0.07) 0%, transparent 55%);
}

/* ── Nav ── */
nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 48px; height: 64px;
  background: rgba(6,8,15,0.8);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
  animation: navDown 0.6s ease both;
}
@keyframes navDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

.nav-logo {
  font-family: 'Syne', sans-serif;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.5px;
}
.nav-logo span { color: var(--blue); }
.nav-logo em { color: var(--green); font-style: normal; }

.nav-center {
  display: flex;
  align-items: center;
  gap: 24px;
}
.nav-center a {
  color: var(--muted);
  font-size: 0.88rem;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.2s;
}
.nav-center a:hover { color: var(--text); }

.nav-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
.nav-user {
  display: flex;
  align-items: center;
  gap: 12px;
}
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  background: linear-gradient(135deg, var(--blue), #5b6ef7);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 700;
  color: white;
  overflow: hidden;
  flex-shrink: 0;
}
.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.user-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.user-name {
  font-size: 0.9rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
}
.user-role {
  font-size: 0.7rem;
  color: var(--green);
  letter-spacing: 0.1em;
  text-transform: uppercase;
}
.btn-logout {
  padding: 8px 18px;
  border-radius: 8px;
  background: rgba(255, 91, 107, 0.12);
  border: 1px solid rgba(255, 91, 107, 0.25);
  color: #ff5b6b;
  font-size: 0.85rem;
  font-weight: 600;
  font-family: 'Syne', sans-serif;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s;
}
.btn-logout:hover {
  background: rgba(255, 91, 107, 0.2);
  border-color: rgba(255, 91, 107, 0.4);
}

/* ── Main Content ── */
.main {
  position: relative;
  z-index: 1;
  padding-top: 64px;
  min-height: 100vh;
}

.section {
  position: relative;
  z-index: 1;
  padding: 80px 48px;
}

.section-tag {
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--blue);
  margin-bottom: 12px;
}

.section-title {
  font-family: 'Syne', sans-serif;
  font-size: clamp(1.8rem, 3vw, 2.6rem);
  font-weight: 800;
  letter-spacing: -1px;
  margin-bottom: 16px;
}

.section-sub {
  color: var(--muted);
  font-size: 1rem;
  max-width: 500px;
  line-height: 1.7;
  margin-bottom: 60px;
}

.profile-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: center;
  animation: fadeUp 0.8s 0.2s ease both;
}

.profile-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.profile-avatar {
  width: 140px;
  height: 140px;
  border-radius: 18px;
  background: linear-gradient(135deg, var(--blue), #5b6ef7);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  overflow: hidden;
  box-shadow: 0 20px 48px rgba(59, 140, 247, 0.3);
}
.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-data {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.profile-field {
  padding: 16px;
  border-radius: 12px;
  background: var(--card-bg);
  border: 1px solid var(--border);
}

.profile-field-label {
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 6px;
}

.profile-field-value {
  font-size: 0.95rem;
  font-weight: 500;
}

.profile-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}
.profile-status {
  margin-top: 14px;
  font-size: 0.9rem;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--blue) 0%, #5b6ef7 100%);
  border: none;
  cursor: pointer;
  font-family: 'Syne', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  color: white;
  text-decoration: none;
  transition: transform 0.15s, box-shadow 0.15s;
  box-shadow: 0 8px 24px rgba(59, 140, 247, 0.35);
}
.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 32px rgba(59, 140, 247, 0.45);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  font-family: 'Syne', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--text);
  text-decoration: none;
  transition: border-color 0.2s, background 0.2s;
}
.btn-secondary:hover {
  border-color: rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.06);
}

/* ── Modal ── */
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
}
.modal.active {
  display: flex;
  animation: fadeIn 0.2s;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

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

.field input[type="file"] {
  padding: 20px;
  text-align: center;
  cursor: pointer;
  background: rgba(59, 140, 247, 0.05);
}

.field input[type="file"]::file-selector-button {
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  background: var(--blue);
  color: white;
  font-weight: 600;
  cursor: pointer;
}

.btn-submit {
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
.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 140, 247, 0.3);
}

/* ── Reveal Animation ── */
.reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}

@keyframes fadeUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
  nav { padding: 0 24px; }
  .section { padding: 60px 24px; }
  .profile-section { grid-template-columns: 1fr; }
  .nav-center { display: none; }
}
</style>
</head>
<body>

<div class="bg-scene"></div>

<!-- Navigation -->
<nav>
  <div class="nav-logo">Start<span>Smart</span><em>:</em></div>
  <div class="nav-center">
    <a href="#profile">Mon Profil</a>
    <a href="#fonctionnalites">Accueil</a>
  </div>
  <div class="nav-right">
    <div class="nav-user">
      <div class="user-avatar">
        <?php if($photo): ?>
        <img src="<?= htmlspecialchars($photo) ?>" alt="Photo">
        <?php else: ?>
        <?= htmlspecialchars($initials ?: 'U') ?>
        <?php endif; ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($name) ?></div>
        <div class="user-role"><?= htmlspecialchars($role) ?></div>
      </div>
    </div>
    <a href="../../api/auth.php?action=logout" class="btn-logout">Quitter</a>
  </div>
</nav>

<!-- Main Content -->
<main class="main">
  <!-- Hero Section -->
  <section class="section" style="padding-top: 80px; padding-bottom: 80px; text-align: center;">
    <div class="reveal">
      <div class="section-tag">Bienvenue</div>
      <div style="font-family: 'Syne', sans-serif; font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 800; letter-spacing: -1px; margin-bottom: 20px;">
        Explorez StartSmart
      </div>
      <p class="section-sub" style="margin-left: auto; margin-right: auto;">
        <?php if($isUser): ?>
        Vous êtes connecté en tant qu'utilisateur. Découvrez les startups, les sponsorships et participez à l'écosystème entrepreneurial.
        <?php elseif($isStartup): ?>
        Vous êtes connecté en tant que startup. Présentez votre projet, trouvez des sponsors et collaborez avec d'autres entrepreneurs.
        <?php endif; ?>
      </p>
      <div class="hero-actions" style="display: flex; gap: 12px; justify-content: center; margin-top: 32px;">
        <a href="#profile" class="btn-primary">Mon Profil →</a>
        <a href="javascript:void(0)" class="btn-secondary">En savoir plus</a>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="section" id="fonctionnalites">
    <div class="reveal">
      <div class="section-tag">Fonctionnalités</div>
      <div class="section-title">Ce que vous pouvez faire</div>
      <p class="section-sub">Exploitez tous les outils et ressources de StartSmart</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; animation: fadeUp 0.6s ease both;">
      <div style="padding: 28px; border-radius: 18px; border: 1px solid var(--border); background: var(--card-bg); backdrop-filter: blur(10px); transition: transform 0.25s, border-color 0.25s; cursor: default;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(59,140,247,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,0.07)'">
        <div style="width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; background: rgba(59,140,247,0.1); border: 1px solid rgba(59,140,247,0.2);">🎯</div>
        <div style="font-family: 'Syne', sans-serif; font-size: 1.05rem; font-weight: 700; margin-bottom: 10px;">Projets</div>
        <div style="font-size: 0.875rem; color: var(--muted); line-height: 1.65;">Créez et gérez vos projets entrepreneuriaux</div>
      </div>

      <div style="padding: 28px; border-radius: 18px; border: 1px solid var(--border); background: var(--card-bg); backdrop-filter: blur(10px); transition: transform 0.25s, border-color 0.25s; cursor: default;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(0,229,160,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,0.07)'">
        <div style="width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; background: rgba(0,229,160,0.1); border: 1px solid rgba(0,229,160,0.2);">💰</div>
        <div style="font-family: 'Syne', sans-serif; font-size: 1.05rem; font-weight: 700; margin-bottom: 10px;">Financement</div>
        <div style="font-size: 0.875rem; color: var(--muted); line-height: 1.65;">Accédez aux opportunités de financement et sponsorships</div>
      </div>

      <div style="padding: 28px; border-radius: 18px; border: 1px solid var(--border); background: var(--card-bg); backdrop-filter: blur(10px); transition: transform 0.25s, border-color 0.25s; cursor: default;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(255,170,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,0.07)'">
        <div style="width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; background: rgba(255,170,0,0.1); border: 1px solid rgba(255,170,0,0.2);">🤝</div>
        <div style="font-family: 'Syne', sans-serif; font-size: 1.05rem; font-weight: 700; margin-bottom: 10px;">Collaboration</div>
        <div style="font-size: 0.875rem; color: var(--muted); line-height: 1.65;">Connectez-vous avec d'autres entrepreneurs et mentors</div>
      </div>

      <div style="padding: 28px; border-radius: 18px; border: 1px solid var(--border); background: var(--card-bg); backdrop-filter: blur(10px); transition: transform 0.25s, border-color 0.25s; cursor: default;" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='rgba(180,90,255,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,0.07)'">
        <div style="width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; background: rgba(180,90,255,0.1); border: 1px solid rgba(180,90,255,0.2);">📊</div>
        <div style="font-family: 'Syne', sans-serif; font-size: 1.05rem; font-weight: 700; margin-bottom: 10px;">Analytics</div>
        <div style="font-size: 0.875rem; color: var(--muted); line-height: 1.65;">Suivez les métriques et la croissance de votre projet</div>
      </div>
    </div>
  </section>

  <!-- Profile Section -->
  <section class="section" id="profile">
    <div class="reveal">
      <div class="section-tag">Espace Personnel</div>
      <div class="section-title">Mon Profil</div>
      <p class="section-sub">Gérez vos informations personnelles et votre photo de profil</p>
    </div>

    <div class="profile-section reveal">
      <div>
        <div class="profile-avatar" id="profile-avatar-display">
          <?php if($photo): ?>
          <img src="<?= htmlspecialchars($photo) ?>" alt="Photo">
          <?php else: ?>
          <?= htmlspecialchars($initials ?: 'U') ?>
          <?php endif; ?>
        </div>
        <div class="profile-actions">
          <button class="btn-primary" onclick="openModal('photoModal')">📷 Changer la photo</button>
          <button class="btn-secondary" onclick="openProfileEditModal()">✏️ Modifier mes infos</button>
        </div>
        <div id="profile-update-status" class="profile-status"></div>
      </div>

      <div class="profile-info">
        <?php if($isUser && $userData): ?>
        <div class="profile-data">
          <div class="profile-field">
            <div class="profile-field-label">Nom</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['nom'] ?? '') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Prénom</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['prenom'] ?? '') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Email</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['email'] ?? '') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Téléphone</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['telephone'] ?? 'Non renseigné') ?></div>
          </div>
          <div class="profile-field" style="grid-column: 1/-1;">
            <div class="profile-field-label">Date de naissance</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['date_naissance'] ?? 'Non renseignée') ?></div>
          </div>
        </div>

        <?php elseif($isStartup && $userData): ?>
        <div class="profile-data">
          <div class="profile-field">
            <div class="profile-field-label">Nom Startup</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['nom_startup'] ?? '') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Secteur</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['secteur'] ?? '') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Responsable</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['nom_responsable'] ?? '') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Email</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['email'] ?? '') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Téléphone</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['telephone'] ?? 'Non renseigné') ?></div>
          </div>
          <div class="profile-field">
            <div class="profile-field-label">Stade</div>
            <div class="profile-field-value"><?= htmlspecialchars($userData['stade'] ?? 'Non défini') ?></div>
          </div>
          <div class="profile-field" style="grid-column: 1/-1;">
            <div class="profile-field-label">Site Web</div>
            <div class="profile-field-value">
              <?php if(!empty($userData['site_web'])): ?>
              <a href="<?= htmlspecialchars($userData['site_web']) ?>" target="_blank" style="color: var(--blue);">
                <?= htmlspecialchars($userData['site_web']) ?>
              </a>
              <?php else: ?>
              Non renseigné
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php else: ?>
        <div class="profile-field" style="grid-column: 1/-1; padding: 40px; text-align: center; color: var(--muted);">
          Chargement des données...
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Face Recognition Section -->
  <section class="section" id="face-recognition">
    <div class="reveal">
      <div class="section-tag">Sécurité</div>
      <div class="section-title">Reconnaissance Faciale</div>
      <p class="section-sub">Configurez la reconnaissance faciale pour une connexion plus rapide et sécurisée</p>
    </div>

    <div style="max-width: 600px; margin: 40px auto;">
      <div style="padding: 32px; border-radius: 18px; border: 1px solid var(--border); background: var(--card-bg);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
          <div>
            <div style="font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; margin-bottom: 6px;">Reconnaissance Faciale</div>
            <div style="font-size: 0.9rem; color: var(--muted);">Activez la connexion par reconnaissance faciale</div>
          </div>
          <div style="text-align: right;">
            <div id="face-status-badge" style="font-size: 0.8rem; font-weight: 600; padding: 8px 16px; border-radius: 8px; background: rgba(255,91,107,0.15); color: var(--red); display: inline-block;">
              ⚙️ À configurer
            </div>
          </div>
        </div>

        <div id="face-setup-area" style="margin-bottom: 20px;">
          <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px;">Méthode de configuration</label>
            <div style="display: flex; gap: 10px;">
              <button class="btn-secondary" id="btn-upload-face" onclick="showUploadFaceForm()" style="flex: 1;">📁 Importer une photo</button>
              <button class="btn-secondary" id="btn-webcam-face" onclick="showWebcamFaceForm()" style="flex: 1;">📷 Utiliser la caméra</button>
            </div>
          </div>

          <!-- Upload Form -->
          <div id="upload-face-form" style="display: none;">
            <div class="field" style="margin-bottom: 16px;">
              <label>Sélectionner une photo de votre visage</label>
              <input type="file" id="face-image-upload" accept="image/jpeg,image/png" onchange="previewFaceImage()">
              <div id="face-preview" style="margin-top: 16px; display: none;">
                <img id="face-preview-img" src="" alt="Aperçu" style="width: 100%; border-radius: 12px; max-height: 300px; object-fit: cover;">
              </div>
            </div>
            <div id="face-upload-status" class="profile-status"></div>
            <button class="btn-submit" id="btn-submit-face" onclick="uploadFaceImage()" style="width: 100%;">Enregistrer mon visage</button>
            <button class="btn-secondary" onclick="hideFaceForm()" style="width: 100%; margin-top: 10px;">Annuler</button>
          </div>

          <!-- Webcam Form -->
          <div id="webcam-face-form" style="display: none;">
            <div style="margin-bottom: 16px;">
              <div style="aspect-ratio: 1; background: #000; border-radius: 12px; overflow: hidden; margin-bottom: 12px; display: none;" id="face-camera-preview">
                <video id="face-webcam-video" style="width: 100%; height: 100%; object-fit: cover;" playsinline></video>
              </div>
              <div id="face-webcam-controls" style="display: none; margin-bottom: 12px;">
                <div style="display: flex; gap: 8px;">
                  <button class="btn-secondary" id="btn-capture-webcam-face" onclick="captureWebcamFace()" style="flex: 1;">📸 Capturer</button>
                  <button class="btn-secondary" onclick="stopWebcamFace()" style="flex: 1;">⏹ Arrêter</button>
                </div>
              </div>
              <button class="btn-primary" id="btn-start-webcam-face" onclick="startWebcamFace()" style="width: 100%; display: none;">🎥 Démarrer la caméra</button>
            </div>
            <div id="face-webcam-status" class="profile-status"></div>
            <button class="btn-secondary" onclick="hideFaceForm()" style="width: 100%;">Annuler</button>
          </div>
        </div>

        <!-- Face Recognition Settings (shown when configured) -->
        <div id="face-settings-area" style="display: none;">
          <div style="padding: 16px; border-radius: 10px; background: rgba(0,229,160,0.1); border: 1px solid rgba(0,229,160,0.3); margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--green);">
              ✅ Reconnaissance faciale configurée le <span id="face-setup-date"></span>
            </div>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px;">Activer la connexion par visage</label>
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 4px;">Connexion par reconnaissance faciale</div>
                <div style="font-size: 0.85rem; color: var(--muted);">Connectez-vous rapidement en utilisant votre visage</div>
              </div>
              <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" id="face-enabled-toggle" onchange="toggleFaceRecognition()" style="width: 18px; height: 18px; accent-color: var(--green);">
              </label>
            </div>
          </div>

          <div style="display: flex; gap: 10px;">
            <button class="btn-secondary" id="btn-reregister-face" onclick="showUploadFaceForm()" style="flex: 1;">🔄 Réenregistrer mon visage</button>
            <button class="btn-secondary" id="btn-remove-face" onclick="removeFaceRecognition()" style="flex: 1; border-color: rgba(255,91,107,0.3); color: var(--red);">🗑️ Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </section>

<!-- Modal: Changer Photo -->
<div id="photoModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Changer la photo de profil</div>
      <button class="modal-close" onclick="closeModal('photoModal')">✕</button>
    </div>
    <div class="field">
      <label>Sélectionner une image</label>
      <input type="file" id="photo-input" accept="image/*">
    </div>
    <div id="upload-status"></div>
    <button class="btn-submit" onclick="uploadPhoto()">Télécharger</button>
  </div>
</div>

<!-- Modal: Modifier Profil -->
<div id="profileEditModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Modifier mes informations</div>
      <button class="modal-close" onclick="closeModal('profileEditModal')">✕</button>
    </div>
    <form id="profile-edit-form" onsubmit="submitProfileUpdate(event)">
      <div id="profile-fields-user">
        <div class="field">
          <label>Nom</label>
          <input type="text" id="edit-nom" required>
        </div>
        <div class="field">
          <label>Prénom</label>
          <input type="text" id="edit-prenom" required>
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" id="edit-email" required>
        </div>
        <div class="field">
          <label>Téléphone</label>
          <input type="text" id="edit-telephone">
        </div>
        <div class="field">
          <label>Date de naissance</label>
          <input type="date" id="edit-date-naissance">
        </div>
      </div>
      <div id="profile-fields-startup">
        <div class="field">
          <label>Nom Startup</label>
          <input type="text" id="edit-nom-startup">
        </div>
        <div class="field">
          <label>Nom Responsable</label>
          <input type="text" id="edit-nom-responsable">
        </div>
        <div class="field">
          <label>Prénom Responsable</label>
          <input type="text" id="edit-prenom-responsable">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" id="edit-email-startup">
        </div>
        <div class="field">
          <label>Téléphone</label>
          <input type="text" id="edit-telephone-startup">
        </div>
        <div class="field">
          <label>Secteur</label>
          <select id="edit-secteur">
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
          <label>Stade</label>
          <select id="edit-stade">
            <option value="">Sélectionner…</option>
            <option value="idee">Idée</option>
            <option value="prototype">Prototype</option>
            <option value="mvp">MVP</option>
            <option value="croissance">Croissance</option>
            <option value="scale">Scale</option>
          </select>
        </div>
        <div class="field">
          <label>Site web</label>
          <input type="url" id="edit-site-web" placeholder="https://...">
        </div>
      </div>
      <div class="field">
        <label>Nouveau mot de passe (optionnel)</label>
        <input type="password" id="edit-password" placeholder="Minimum 8 caractères">
      </div>
      <div class="field">
        <label>Confirmer le mot de passe</label>
        <input type="password" id="edit-password-confirm" placeholder="Répéter le mot de passe">
      </div>
      <div id="profile-modal-status" class="profile-status"></div>
      <button class="btn-submit" type="submit">Enregistrer les modifications</button>
    </form>
  </div>
</div>

<script>
const currentRole = <?= json_encode($role) ?>;
const profileData = <?= json_encode($userData ?? []) ?>;

function openModal(id) {
  document.getElementById(id).classList.add('active');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('active');
}

document.querySelectorAll('.modal').forEach(modal => {
  modal.addEventListener('click', (e) => {
    if(e.target === modal) {
      modal.classList.remove('active');
    }
  });
});

function uploadPhoto() {
  const file = document.getElementById('photo-input').files[0];
  if (!file) {
    document.getElementById('upload-status').innerHTML = '<div style="color: var(--red); font-size: 0.9rem; margin-bottom: 12px;">Sélectionnez une image</div>';
    return;
  }

  const fd = new FormData();
  fd.append('profile_picture', file);

  document.getElementById('upload-status').innerHTML = '<div style="color: var(--muted); font-size: 0.9rem; margin-bottom: 12px;">Téléchargement...</div>';

  fetch('../../api/upload.php?action=upload_profile_picture', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if(data.success) {
      document.getElementById('upload-status').innerHTML = '<div style="color: var(--green); font-size: 0.9rem; margin-bottom: 12px;">✅ ' + data.message + '</div>';
      
      // Update avatar display
      const avatar = document.getElementById('profile-avatar-display');
      avatar.innerHTML = '<img src="' + data.photo_url + '?t=' + Date.now() + '" alt="Photo">';
      
      setTimeout(() => {
        closeModal('photoModal');
        location.reload();
      }, 1500);
    } else {
      document.getElementById('upload-status').innerHTML = '<div style="color: var(--red); font-size: 0.9rem; margin-bottom: 12px;">❌ ' + (data.error || 'Erreur') + '</div>';
    }
  })
  .catch(e => {
    document.getElementById('upload-status').innerHTML = '<div style="color: var(--red); font-size: 0.9rem; margin-bottom: 12px;">❌ Erreur: ' + e.message + '</div>';
  });
}

function setStatus(el, message, colorVar) {
  el.textContent = message;
  el.style.color = colorVar;
}

function openProfileEditModal() {
  const userFields = document.getElementById('profile-fields-user');
  const startupFields = document.getElementById('profile-fields-startup');
  const modalStatus = document.getElementById('profile-modal-status');
  modalStatus.textContent = '';

  if (currentRole === 'startup') {
    userFields.style.display = 'none';
    startupFields.style.display = 'block';
    document.getElementById('edit-nom-startup').value = profileData.nom_startup || '';
    document.getElementById('edit-nom-responsable').value = profileData.nom_responsable || '';
    document.getElementById('edit-prenom-responsable').value = profileData.prenom_responsable || '';
    document.getElementById('edit-email-startup').value = profileData.email || '';
    document.getElementById('edit-telephone-startup').value = profileData.telephone || '';
    document.getElementById('edit-secteur').value = profileData.secteur || '';
    document.getElementById('edit-stade').value = profileData.stade || '';
    document.getElementById('edit-site-web').value = profileData.site_web || '';
  } else {
    startupFields.style.display = 'none';
    userFields.style.display = 'block';
    document.getElementById('edit-nom').value = profileData.nom || '';
    document.getElementById('edit-prenom').value = profileData.prenom || '';
    document.getElementById('edit-email').value = profileData.email || '';
    document.getElementById('edit-telephone').value = profileData.telephone || '';
    const dateValue = (profileData.date_naissance && profileData.date_naissance !== '0000-00-00') ? profileData.date_naissance : '';
    document.getElementById('edit-date-naissance').value = dateValue;
  }

  document.getElementById('edit-password').value = '';
  document.getElementById('edit-password-confirm').value = '';
  openModal('profileEditModal');
}

function submitProfileUpdate(event) {
  event.preventDefault();
  const modalStatus = document.getElementById('profile-modal-status');
  const inlineStatus = document.getElementById('profile-update-status');
  setStatus(modalStatus, 'Mise à jour en cours...', 'var(--muted)');

  const fd = new FormData();
  if (currentRole === 'startup') {
    fd.append('nom_startup', document.getElementById('edit-nom-startup').value.trim());
    fd.append('nom_responsable', document.getElementById('edit-nom-responsable').value.trim());
    fd.append('prenom_responsable', document.getElementById('edit-prenom-responsable').value.trim());
    fd.append('email', document.getElementById('edit-email-startup').value.trim());
    fd.append('telephone', document.getElementById('edit-telephone-startup').value.trim());
    fd.append('secteur', document.getElementById('edit-secteur').value);
    fd.append('stade', document.getElementById('edit-stade').value);
    fd.append('site_web', document.getElementById('edit-site-web').value.trim());
  } else {
    fd.append('nom', document.getElementById('edit-nom').value.trim());
    fd.append('prenom', document.getElementById('edit-prenom').value.trim());
    fd.append('email', document.getElementById('edit-email').value.trim());
    fd.append('telephone', document.getElementById('edit-telephone').value.trim());
    fd.append('date_naissance', document.getElementById('edit-date-naissance').value);
  }

  const password = document.getElementById('edit-password').value;
  const passwordConfirm = document.getElementById('edit-password-confirm').value;
  if (password || passwordConfirm) {
    fd.append('password', password);
    fd.append('password_confirm', passwordConfirm);
  }

  fetch('../../api/profile.php?action=update_profile', {
    method: 'POST',
    body: fd
  })
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        setStatus(modalStatus, '❌ ' + (data.error || 'Erreur lors de la mise à jour.'), 'var(--red)');
        return;
      }
      setStatus(modalStatus, '✅ Profil mis à jour.', 'var(--green)');
      setStatus(inlineStatus, 'Profil mis à jour avec succès.', 'var(--green)');
      setTimeout(() => location.reload(), 700);
    })
    .catch(e => {
      setStatus(modalStatus, '❌ Erreur: ' + e.message, 'var(--red)');
    });
}

// Scroll reveal animation
const reveals = document.querySelectorAll('.reveal');
const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -100px 0px' };
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if(entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, observerOptions);
reveals.forEach(r => observer.observe(r));

// ── Face Recognition Functions ────────────────────────────────
async function initFaceRecognitionUI() {
  try {
    const status = await faceRecognition.getFaceStatus();
    if (status.enabled) {
      showFaceRecognitionSettings(status);
    } else if (status.setup_complete) {
      showFaceRecognitionSettings(status);
    }
  } catch (error) {
    console.error('Error loading face recognition status:', error);
  }
}

function showFaceRecognitionSettings(status) {
  const setupArea = document.getElementById('face-setup-area');
  const settingsArea = document.getElementById('face-settings-area');
  const statusBadge = document.getElementById('face-status-badge');
  const enabledToggle = document.getElementById('face-enabled-toggle');

  setupArea.style.display = 'none';
  settingsArea.style.display = 'block';

  if (status.enabled) {
    statusBadge.innerHTML = '✅ Activée';
    statusBadge.style.background = 'rgba(0,229,160,0.15)';
    statusBadge.style.color = 'var(--green)';
    enabledToggle.checked = true;
  } else {
    statusBadge.innerHTML = '⏸️ Configurée mais désactivée';
    statusBadge.style.background = 'rgba(255,170,0,0.15)';
    statusBadge.style.color = 'rgba(255,170,0,0.9)';
    enabledToggle.checked = false;
  }

  if (status.setup_date) {
    const date = new Date(status.setup_date);
    document.getElementById('face-setup-date').textContent = date.toLocaleDateString('fr-FR');
  }
}

function showUploadFaceForm() {
  document.getElementById('upload-face-form').style.display = 'block';
  document.getElementById('webcam-face-form').style.display = 'none';
  document.getElementById('face-camera-preview').style.display = 'none';
  document.getElementById('btn-upload-face').style.borderColor = 'var(--blue)';
  document.getElementById('btn-upload-face').style.background = 'rgba(59,140,247,0.1)';
  document.getElementById('btn-webcam-face').style.borderColor = 'var(--border)';
  document.getElementById('btn-webcam-face').style.background = 'rgba(255,255,255,0.03)';
}

function showWebcamFaceForm() {
  document.getElementById('upload-face-form').style.display = 'none';
  document.getElementById('webcam-face-form').style.display = 'block';
  document.getElementById('btn-start-webcam-face').style.display = 'block';
  document.getElementById('btn-upload-face').style.borderColor = 'var(--border)';
  document.getElementById('btn-upload-face').style.background = 'rgba(255,255,255,0.03)';
  document.getElementById('btn-webcam-face').style.borderColor = 'var(--blue)';
  document.getElementById('btn-webcam-face').style.background = 'rgba(59,140,247,0.1)';
}

function hideFaceForm() {
  document.getElementById('upload-face-form').style.display = 'none';
  document.getElementById('webcam-face-form').style.display = 'none';
  document.getElementById('face-camera-preview').style.display = 'none';
  stopWebcamFace();
}

function previewFaceImage() {
  const file = document.getElementById('face-image-upload').files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById('face-preview-img').src = e.target.result;
      document.getElementById('face-preview').style.display = 'block';
    };
    reader.readAsDataURL(file);
  }
}

async function uploadFaceImage() {
  const file = document.getElementById('face-image-upload').files[0];
  if (!file) {
    showFaceStatus('face-upload-status', 'Sélectionnez une image', 'error');
    return;
  }

  const btn = document.getElementById('btn-submit-face');
  btn.disabled = true;
  btn.textContent = '⏳ Upload en cours...';

  try {
    showFaceStatus('face-upload-status', '⏳ Upload en cours...', 'loading');
    const result = await faceRecognition.uploadFaceImage(file);
    showFaceStatus('face-upload-status', '✅ Visage enregistré avec succès!', 'success');
    setTimeout(() => { initFaceRecognitionUI(); hideFaceForm(); }, 1500);
  } catch (error) {
    console.error('uploadFaceImage error:', error);
    showFaceStatus('face-upload-status', '❌ ' + (error.message || 'Erreur inconnue'), 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Enregistrer mon visage';
  }
}

async function startWebcamFace() {
  try {
    showFaceStatus('face-webcam-status', 'Activation de la caméra...', 'loading');
    await faceRecognition.initCamera('face-webcam-video');
    
    document.getElementById('btn-start-webcam-face').style.display = 'none';
    document.getElementById('face-camera-preview').style.display = 'block';
    document.getElementById('face-webcam-controls').style.display = 'block';
    showFaceStatus('face-webcam-status', '✅ Caméra activée. Dirigez votre visage vers la caméra.', 'success');
  } catch (error) {
    showFaceStatus('face-webcam-status', '❌ ' + error.message, 'error');
  }
}

function stopWebcamFace() {
  faceRecognition.stopCamera();
  document.getElementById('btn-start-webcam-face').style.display = 'block';
  document.getElementById('face-camera-preview').style.display = 'none';
  document.getElementById('face-webcam-controls').style.display = 'none';
  document.getElementById('face-webcam-status').textContent = '';
}

async function captureWebcamFace() {
  const btn = document.getElementById('btn-capture-webcam-face');
  btn.disabled = true;
  btn.textContent = '⏳ Capture en cours...';

  try {
    showFaceStatus('face-webcam-status', '⏳ Capture et traitement en cours...', 'loading');
    const result = await faceRecognition.captureAndSetupFace();
    showFaceStatus('face-webcam-status', '✅ Visage capturé et enregistré!', 'success');
    stopWebcamFace();
    setTimeout(() => { initFaceRecognitionUI(); hideFaceForm(); }, 1500);
  } catch (error) {
    console.error('captureWebcamFace error:', error);
    showFaceStatus('face-webcam-status', '❌ ' + (error.message || 'Erreur inconnue'), 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = '📸 Capturer';
  }
}

async function toggleFaceRecognition() {
  const toggle = document.getElementById('face-enabled-toggle');
  try {
    if (toggle.checked) {
      await faceRecognition.enableFaceRecognition();
      showFaceStatus('face-webcam-status', '✅ Reconnaissance faciale activée', 'success');
      const badge = document.getElementById('face-status-badge');
      badge.innerHTML = '✅ Activée';
      badge.style.background = 'rgba(0,229,160,0.15)';
      badge.style.color = 'var(--green)';
    } else {
      await faceRecognition.disableFaceRecognition();
      showFaceStatus('face-webcam-status', '✅ Reconnaissance faciale désactivée', 'success');
      const badge = document.getElementById('face-status-badge');
      badge.innerHTML = '⏸️ Configurée mais désactivée';
      badge.style.background = 'rgba(255,170,0,0.15)';
      badge.style.color = 'rgba(255,170,0,0.9)';
    }
  } catch (error) {
    toggle.checked = !toggle.checked;
    showFaceStatus('face-webcam-status', '❌ ' + error.message, 'error');
  }
}

async function removeFaceRecognition() {
  if (confirm('Êtes-vous sûr? Vous devrez réenregistrer votre visage pour utiliser la reconnaissance faciale.')) {
    try {
      const response = await fetch('/startsmart/api/face/setup.php?action=remove', { method: 'POST' });
      const text = await response.text();
      const j = text.indexOf('{');
      const data = j !== -1 ? JSON.parse(text.slice(j)) : null;
      if (!data || !data.success) throw new Error(data?.error || 'Erreur serveur');
      document.getElementById('face-setup-area').style.display = 'block';
      document.getElementById('face-settings-area').style.display = 'none';
      document.getElementById('face-status-badge').innerHTML = '⚙️ À configurer';
      document.getElementById('face-status-badge').style.background = 'rgba(255,91,107,0.15)';
      document.getElementById('face-status-badge').style.color = 'var(--red)';
    } catch (error) {
      alert('❌ Erreur: ' + error.message);
    }
  }
}

function showFaceStatus(elementId, message, type) {
  const el = document.getElementById(elementId);
  el.textContent = message;
  el.className = 'profile-status ' + type;
}

// Initialize face recognition UI on page load
document.addEventListener('DOMContentLoaded', initFaceRecognitionUI);
</script>
</body>
</html>
