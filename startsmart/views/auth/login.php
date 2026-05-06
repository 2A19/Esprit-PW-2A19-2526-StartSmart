<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../controllers/AuthController.php';
$authController = new AuthController();
if (empty($_SESSION['user_id'])) {
  $authController->autoLoginFromRememberMe();
}
if(!empty($_SESSION['user_id']) && !empty($_SESSION['user_role'])){
  header('Location: '.($_SESSION['user_role']==='admin' ? '../back/dashboard.php' : '../front/dashboard.php'));
  exit;
}

$loginErrors = $_SESSION['login_errors'] ?? [];
$regErrors = $_SESSION['reg_errors'] ?? [];
$regSuccess = $_SESSION['reg_success'] ?? '';
$regFormData = $_SESSION['reg_form_data'] ?? [];

unset($_SESSION['login_errors'], $_SESSION['reg_errors'], $_SESSION['reg_success'], $_SESSION['reg_form_data']);

$showTab = $_GET['tab'] ?? 'login';
$googleClientId = trim((string)(getenv('GOOGLE_CLIENT_ID') ?: ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StartSmart — Connexion</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<?php if ($googleClientId !== ''): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<script src="../../public/js/face-recognition.js"></script>
<style>
:root {
  --bg: #07090f;
  --surface: rgba(255,255,255,0.03);
  --border: rgba(255,255,255,0.08);
  --blue: #3b8cf7;
  --green: #00e5a0;
  --text: #e8eaf0;
  --muted: rgba(232,234,240,0.45);
  --error: #ff5b6b;
  --card: rgba(12,16,28,0.85);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  overflow-y: auto;
  overflow-x: hidden;
  position: relative;
  padding: 2rem 0;
}
.bg-grid {
  position: fixed; inset: 0; z-index: 0;
  background-image:
    linear-gradient(rgba(59,140,247,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(59,140,247,0.04) 1px, transparent 1px);
  background-size: 40px 40px;
  animation: gridDrift 20s linear infinite;
}
@keyframes gridDrift { from { transform: translateY(0); } to { transform: translateY(40px); } }
.orb {
  position: fixed; border-radius: 50%; filter: blur(80px); z-index: 0;
  animation: orbFloat 8s ease-in-out infinite;
}
.orb-1 { width: 500px; height: 500px; background: rgba(59,140,247,0.12); top: -100px; left: -100px; animation-delay: 0s; }
.orb-2 { width: 400px; height: 400px; background: rgba(0,229,160,0.08); bottom: -80px; right: -80px; animation-delay: -4s; }
.orb-3 { width: 300px; height: 300px; background: rgba(59,140,247,0.06); top: 50%; left: 60%; animation-delay: -2s; }
@keyframes orbFloat { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(20px, -30px) scale(1.05); } }
.wrap {
  position: relative; z-index: 1;
  width: 100%; max-width: 480px;
  padding: 24px;
  animation: wrapIn 0.7s cubic-bezier(0.16,1,0.3,1) both;
}
@keyframes wrapIn { from { opacity: 0; transform: translateY(32px); } to { opacity: 1; transform: translateY(0); } }
.logo {
  text-align: center; margin-bottom: 36px;
  font-family: 'Syne', sans-serif; font-size: 2.2rem; font-weight: 800;
  letter-spacing: -1px;
}
.logo span { color: var(--blue); }
.logo em { color: var(--green); font-style: normal; }
.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 36px 40px;
  backdrop-filter: blur(20px);
  box-shadow: 0 40px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.04) inset;
}
.tabs {
  display: flex; gap: 4px;
  background: rgba(255,255,255,0.04);
  border-radius: 12px; padding: 4px;
  margin-bottom: 32px;
}
.tab-btn {
  flex: 1; padding: 10px;
  background: none; border: none; cursor: pointer;
  font-family: 'Syne', sans-serif; font-size: .9rem; font-weight: 600;
  color: var(--muted);
  border-radius: 9px;
  transition: all .25s;
}
.tab-btn.active {
  background: var(--blue);
  color: #fff;
  box-shadow: 0 4px 16px rgba(59,140,247,0.35);
}
.role-label { font-size: .7rem; font-weight: 600; letter-spacing: .12em; color: var(--green); text-transform: uppercase; margin-bottom: 12px; }
.roles { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 28px; }
.role-card {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  padding: 14px 8px;
  border: 1px solid var(--border);
  border-radius: 14px;
  cursor: pointer;
  background: rgba(255,255,255,0.02);
  transition: all .2s;
  position: relative; overflow: hidden;
}
.role-card::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(59,140,247,0.1), transparent);
  opacity: 0; transition: opacity .2s;
}
.role-card:hover { border-color: rgba(59,140,247,0.4); transform: translateY(-2px); }
.role-card:hover::after { opacity: 1; }
.role-card.sel {
  border-color: var(--green);
  background: rgba(0,229,160,0.06);
  box-shadow: 0 0 0 1px var(--green), 0 8px 24px rgba(0,229,160,0.1);
}
.role-card .icon { font-size: 1.6rem; }
.role-card .name { font-size: .75rem; font-weight: 600; font-family: 'Syne', sans-serif; }
.field { margin-bottom: 18px; }
.field label { display: block; font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
.field-inner { position: relative; }
.field input, .field select {
  width: 100%; padding: 13px 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border);
  border-radius: 12px;
  color: var(--text); font-family: 'DM Sans', sans-serif; font-size: .95rem;
  outline: none;
  transition: border-color .2s, box-shadow .2s, background .2s;
}
.field input:focus, .field select:focus {
  border-color: var(--blue);
  background: rgba(59,140,247,0.05);
  box-shadow: 0 0 0 3px rgba(59,140,247,0.12);
}
.field input.is-error { border-color: var(--error); box-shadow: 0 0 0 3px rgba(255,91,107,0.12); }
.field-error { font-size: .78rem; color: var(--error); margin-top: 6px; min-height: 1em; display: block; }
.toggle-pass {
  position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: var(--muted); font-size: .9rem; padding: 4px;
  transition: color .2s;
}
.toggle-pass:hover { color: var(--text); }
.forgot { text-align: right; margin-top: -8px; margin-bottom: 20px; }
.forgot a { font-size: .8rem; color: rgba(59,140,247,0.7); text-decoration: none; transition: color .2s; }
.forgot a:hover { color: var(--blue); }
.captcha-wrap { margin-bottom: 20px; }
.btn-main {
  width: 100%; padding: 14px;
  background: linear-gradient(135deg, var(--blue) 0%, #5b6ef7 100%);
  border: none; border-radius: 12px; cursor: pointer;
  font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700;
  color: #fff; letter-spacing: .02em;
  box-shadow: 0 8px 24px rgba(59,140,247,0.35);
  transition: transform .15s, box-shadow .15s, opacity .15s;
  position: relative; overflow: hidden;
}
.btn-main:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(59,140,247,0.45); }
.btn-main:disabled { opacity: .6; cursor: not-allowed; }
.switch-link { text-align: center; margin-top: 20px; font-size: .85rem; color: var(--muted); }
.switch-link a { color: var(--blue); text-decoration: none; font-weight: 500; }
.switch-link a:hover { text-decoration: underline; }
.view { display: none; }
.view.active { display: block; }
.reg-type-toggle { display: flex; gap: 8px; margin-bottom: 24px; }
.reg-type-btn {
  flex: 1; padding: 9px;
  border: 1px solid var(--border); border-radius: 10px; cursor: pointer;
  background: none; color: var(--muted); font-size: .82rem; font-weight: 600;
  font-family: 'Syne', sans-serif; transition: all .2s;
}
.reg-type-btn.active { border-color: var(--blue); color: var(--blue); background: rgba(59,140,247,0.08); }
.alert { display: none; padding: 12px 16px; border-radius: 10px; font-size: .85rem; margin-bottom: 16px; }
.alert.success { background: rgba(0,229,160,0.1); border: 1px solid rgba(0,229,160,0.3); color: var(--green); display: block; }
.alert.error { background: rgba(255,91,107,0.1); border: 1px solid rgba(255,91,107,0.3); color: var(--error); display: block; }
.check-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 18px; }
.remember-row { align-items: center; justify-content: space-between; margin: 12px 0 16px; }
.check-row input[type=checkbox] { width: 16px; height: 16px; margin-top: 2px; accent-color: var(--blue); flex-shrink: 0; cursor: pointer; }
.check-row label { font-size: .82rem; color: var(--muted); cursor: pointer; line-height: 1.4; }
.check-row a { color: var(--blue); text-decoration: none; }
.oauth-divider { display: flex; align-items: center; gap: 12px; margin: 18px 0 14px; color: var(--muted); font-size: .8rem; }
.oauth-divider::before, .oauth-divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.08); }
.oauth-note { min-height: 18px; margin-bottom: 10px; color: var(--muted); font-size: .8rem; }
.google-wrap { transition: opacity .2s ease; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.camera-preview {
  width: 100%; aspect-ratio: 1;
  background: #000; border-radius: 12px; overflow: hidden;
  margin-bottom: 16px; position: relative;
}
.camera-preview video {
  width: 100%; height: 100%; object-fit: cover;
}
.camera-preview.hidden { display: none; }
.camera-controls {
  display: flex; gap: 8px; margin-bottom: 16px;
}
.camera-controls button {
  flex: 1; padding: 10px;
  background: rgba(59,140,247,0.15); border: 1px solid var(--blue);
  border-radius: 8px; color: var(--blue); font-size: .9rem; font-weight: 600;
  cursor: pointer; transition: all .2s;
}
.camera-controls button:hover { background: rgba(59,140,247,0.25); }
.camera-controls button.active { background: var(--blue); color: #fff; }
.camera-controls button:disabled { opacity: .5; cursor: not-allowed; }
.face-status {
  padding: 12px; border-radius: 8px; margin-bottom: 12px; font-size: .85rem;
  display: none;
}
.face-status.success { background: rgba(0,229,160,0.1); border: 1px solid rgba(0,229,160,0.3); color: var(--green); display: block; }
.face-status.error { background: rgba(255,91,107,0.1); border: 1px solid rgba(255,91,107,0.3); color: var(--error); display: block; }
.face-status.loading { background: rgba(59,140,247,0.1); border: 1px solid rgba(59,140,247,0.3); color: var(--blue); display: block; }
.sub-tabs {
  display: flex; gap: 4px; margin-bottom: 16px;
  background: rgba(255,255,255,0.04); border-radius: 10px; padding: 3px;
}
.sub-tab-btn {
  flex: 1; padding: 8px;
  border: none; background: none; cursor: pointer;
  font-size: .8rem; font-weight: 600; color: var(--muted);
  border-radius: 8px; transition: all .2s;
}
.sub-tab-btn.active {
  background: var(--green); color: #000; box-shadow: 0 2px 8px rgba(0,229,160,0.2);
}

</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="wrap">
  <div class="logo">Start<span>Smart</span><em>:</em></div>
  <div class="card">
    <div class="tabs">
      <button class="tab-btn <?= $showTab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Connexion</button>
      <button class="tab-btn <?= $showTab !== 'login' ? 'active' : '' ?>" onclick="switchTab('register')">Inscription</button>
    </div>

    <!-- LOGIN -->
    <div id="v-login" class="view <?= $showTab === 'login' ? 'active' : '' ?>">
      <div class="sub-tabs">
        <button class="sub-tab-btn active" onclick="switchLoginMethod('password')">🔐 Mot de passe</button>
        <button class="sub-tab-btn" onclick="switchLoginMethod('face')">👤 Visage</button>
      </div>
      
      <!-- Password Login -->
      <div id="login-password-form">
        <div class="role-label">Je suis…</div>
        <div class="roles" id="login-roles">
          <div class="role-card sel" data-r="user" onclick="selRole(this,'login-roles')">
            <span class="icon">👤</span><span class="name">Utilisateur</span>
          </div>
          <div class="role-card" data-r="startup" onclick="selRole(this,'login-roles')">
            <span class="icon">🚀</span><span class="name">Startup</span>
          </div>
          <div class="role-card" data-r="admin" onclick="selRole(this,'login-roles')">
            <span class="icon">🛡️</span><span class="name">Admin</span>
          </div>
        </div>
        <?php if(!empty($loginErrors)): ?>
        <div class="alert error">❌ <?= htmlspecialchars($loginErrors['general'] ?? 'Erreur de connexion') ?></div>
        <?php endif; ?>
        <div class="field">
          <label>Email</label>
          <input type="email" id="l-email" placeholder="vous@exemple.com">
          <span class="field-error" id="l-email-e"></span>
        </div>
        <div class="field">
          <label>Mot de passe</label>
          <div class="field-inner">
            <input type="password" id="l-pass" placeholder="••••••••">
            <button class="toggle-pass" type="button" onclick="toggleVis('l-pass',this)">👁</button>
          </div>
          <span class="field-error" id="l-pass-e"></span>
        </div>
        <div class="check-row remember-row">
          <div style="display:flex;align-items:center;gap:10px">
            <input type="checkbox" id="l-remember">
            <label for="l-remember">Se souvenir de moi</label>
          </div>
        </div>
        <div class="forgot"><a href="#" onclick="openForgot(event)">Mot de passe oublié ?</a></div>
        <div class="captcha-wrap">
          <div class="h-captcha" id="login-captcha" data-sitekey="10000000-ffff-ffff-ffff-000000000001" data-theme="dark"></div>
          <span class="field-error" id="login-captcha-e"></span>
        </div>
        <button class="btn-main" id="btn-login" onclick="doLogin()">Se connecter</button>
        <?php if ($googleClientId !== ''): ?>
        <div class="oauth-divider"><span>ou</span></div>
        <div class="oauth-note" id="google-login-note"></div>
        <div class="google-wrap" id="google-login-wrap">
          <div id="google-signin-btn"></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Face Recognition Login -->
      <div id="login-face-form" style="display:none">
        <div class="role-label">Je suis…</div>
        <div class="roles" id="face-login-roles">
          <div class="role-card sel" data-r="user" onclick="selRole(this,'face-login-roles')">
            <span class="icon">👤</span><span class="name">Utilisateur</span>
          </div>
          <div class="role-card" data-r="startup" onclick="selRole(this,'face-login-roles')">
            <span class="icon">🚀</span><span class="name">Startup</span>
          </div>
          <div class="role-card" data-r="admin" onclick="selRole(this,'face-login-roles')">
            <span class="icon">🛡️</span><span class="name">Admin</span>
          </div>
        </div>
        
        <div class="field">
          <label>Email</label>
          <input type="email" id="f-email" placeholder="vous@exemple.com">
          <span class="field-error" id="f-email-e"></span>
        </div>

        <div class="face-status" id="face-status"></div>
        
        <div class="camera-preview" id="face-camera">
          <video id="face-video" playsinline></video>
        </div>
        
        <div class="camera-controls">
          <button id="btn-start-camera" onclick="startFaceCamera()">📷 Activer caméra</button>
          <button id="btn-stop-camera" onclick="stopFaceCamera()" style="display:none">⏹ Arrêter</button>
        </div>

        <button class="btn-main" id="btn-capture-face" onclick="captureFaceForLogin()" style="display:none">📸 Vérifier mon visage</button>
        <button class="btn-main" id="btn-face-try-again" onclick="startFaceCamera()" style="display:none">🔄 Réessayer</button>
        <button class="btn-main" id="btn-face-loading" style="display:none; opacity:.6; cursor:not-allowed">⏳ Vérification en cours…</button>

        <div class="switch-link" style="margin-top:20px">
          Pas de caméra ? <a href="#" onclick="switchLoginMethod('password')">Utiliser mot de passe</a>
        </div>
      </div>

      <div class="switch-link">Pas encore membre ? <a href="#" onclick="switchTab('register')">Créer un compte</a></div>
    </div>

    <!-- REGISTER -->
    <div id="v-register" class="view <?= $showTab !== 'login' ? 'active' : '' ?>">
      <div class="reg-type-toggle">
        <button class="reg-type-btn active" onclick="showRegForm('user',this)">👤 Utilisateur</button>
        <button class="reg-type-btn" onclick="showRegForm('startup',this)">🚀 Startup</button>
      </div>
      
      <!-- USER FORM -->
      <div id="form-user">
        <?php if(!empty($regErrors)): ?>
        <div class="alert error">❌ <?= htmlspecialchars($regErrors['general'] ?? 'Erreur') ?></div>
        <?php elseif(!empty($regSuccess)): ?>
        <div class="alert success">✅ <?= htmlspecialchars($regSuccess) ?></div>
        <?php endif; ?>
        <div class="grid-2">
          <div class="field">
            <label>Nom</label>
            <input type="text" id="u-nom" placeholder="Ben Ali">
            <span class="field-error" id="u-nom-e"></span>
          </div>
          <div class="field">
            <label>Prénom</label>
            <input type="text" id="u-prenom" placeholder="Ahmed">
            <span class="field-error" id="u-prenom-e"></span>
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" id="u-email" placeholder="ahmed@email.com">
          <span class="field-error" id="u-email-e"></span>
        </div>
        <div class="field">
          <label>Téléphone</label>
          <input type="text" id="u-tel" placeholder="55001234">
          <span class="field-error" id="u-tel-e"></span>
        </div>
        <div class="field">
          <label>Date de naissance</label>
          <input type="date" id="u-dob">
          <span class="field-error" id="u-dob-e"></span>
        </div>
        <div class="field">
          <label>Photo de profil</label>
          <input type="file" id="u-photo" accept="image/*">
          <span class="field-error" id="u-photo-e"></span>
        </div>
        <div class="field">
          <label>Mot de passe</label>
          <div class="field-inner">
            <input type="password" id="u-pass" placeholder="Min. 8 car., 1 maj., 1 chiffre">
            <button class="toggle-pass" type="button" onclick="toggleVis('u-pass',this)">👁</button>
          </div>
          <span class="field-error" id="u-pass-e"></span>
        </div>
        <div class="field">
          <label>Confirmer mot de passe</label>
          <div class="field-inner">
            <input type="password" id="u-pass2" placeholder="••••••••">
            <button class="toggle-pass" type="button" onclick="toggleVis('u-pass2',this)">👁</button>
          </div>
          <span class="field-error" id="u-pass2-e"></span>
        </div>
        <div class="check-row">
          <input type="checkbox" id="u-cgu">
          <label for="u-cgu">J'accepte les <a href="#">conditions d'utilisation</a></label>
        </div>
        <span class="field-error" id="u-cgu-e"></span>
        <div class="captcha-wrap">
          <div class="h-captcha" id="user-captcha" data-sitekey="10000000-ffff-ffff-ffff-000000000001" data-theme="dark"></div>
          <span class="field-error" id="user-captcha-e"></span>
        </div>
        <button class="btn-main" id="btn-reg-user" onclick="doRegUser()">Créer mon compte</button>
      </div>

      <!-- STARTUP FORM -->
      <div id="form-startup" style="display:none">
        <div class="grid-2">
          <div class="field">
            <label>Nom de la startup</label>
            <input type="text" id="s-nom" placeholder="Ma Startup">
            <span class="field-error" id="s-nom-e"></span>
          </div>
          <div class="field">
            <label>Secteur</label>
            <select id="s-secteur">
              <option value="">Sélectionner…</option>
              <option value="tech">Tech</option>
              <option value="sante">Santé</option>
              <option value="fintech">Fintech</option>
              <option value="logistique">Logistique</option>
              <option value="retail">Retail</option>
              <option value="autre">Autre</option>
            </select>
            <span class="field-error" id="s-secteur-e"></span>
          </div>
        </div>
        <div class="grid-2">
          <div class="field">
            <label>Nom du responsable</label>
            <input type="text" id="s-rnom" placeholder="Ben Ali">
            <span class="field-error" id="s-rnom-e"></span>
          </div>
          <div class="field">
            <label>Prénom du responsable</label>
            <input type="text" id="s-rprenom" placeholder="Ahmed">
            <span class="field-error" id="s-rprenom-e"></span>
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" id="s-email" placeholder="contact@startup.com">
          <span class="field-error" id="s-email-e"></span>
        </div>
        <div class="grid-2">
          <div class="field">
            <label>Téléphone</label>
            <input type="text" id="s-tel" placeholder="55001234">
            <span class="field-error" id="s-tel-e"></span>
          </div>
          <div class="field">
            <label>Stade</label>
            <select id="s-stade">
              <option value="idee">Idée</option>
              <option value="prototype">Prototype</option>
              <option value="mvp">MVP</option>
              <option value="croissance">Croissance</option>
              <option value="scale">Scale</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label>Site Web</label>
          <input type="url" id="s-site" placeholder="https://startup.tn">
          <span class="field-error" id="s-site-e"></span>
        </div>
        <div class="field">
          <label>Photo de profil</label>
          <input type="file" id="s-photo" accept="image/*">
          <span class="field-error" id="s-photo-e"></span>
        </div>
        <div class="field">
          <label>Mot de passe</label>
          <div class="field-inner">
            <input type="password" id="s-pass" placeholder="Min. 8 car., 1 maj., 1 chiffre">
            <button class="toggle-pass" type="button" onclick="toggleVis('s-pass',this)">👁</button>
          </div>
          <span class="field-error" id="s-pass-e"></span>
        </div>
        <div class="field">
          <label>Confirmer mot de passe</label>
          <div class="field-inner">
            <input type="password" id="s-pass2" placeholder="••••••••">
            <button class="toggle-pass" type="button" onclick="toggleVis('s-pass2',this)">👁</button>
          </div>
          <span class="field-error" id="s-pass2-e"></span>
        </div>
        <div class="check-row">
          <input type="checkbox" id="s-cgu">
          <label for="s-cgu">J'accepte les <a href="#">conditions d'utilisation</a></label>
        </div>
        <span class="field-error" id="s-cgu-e"></span>
        <div class="captcha-wrap">
          <div class="h-captcha" id="startup-captcha" data-sitekey="10000000-ffff-ffff-ffff-000000000001" data-theme="dark"></div>
          <span class="field-error" id="startup-captcha-e"></span>
        </div>
        <button class="btn-main" id="btn-reg-startup" onclick="doRegStartup()">Créer mon compte</button>
      </div>

      <div class="switch-link">Déjà membre ? <a href="#" onclick="switchTab('login')">Se connecter</a></div>
    </div>
  </div>
</div>

<!-- ── Forgot Password Modal ─────────────────────────────── -->
<style>
.fp-overlay {
  display: none; position: fixed; inset: 0; z-index: 500;
  background: rgba(0,0,0,0.75); align-items: center; justify-content: center;
  animation: fadeIn .2s;
}
.fp-overlay.open { display: flex; }
.fp-box {
  background: #0b0e1a; border: 1px solid rgba(255,255,255,.08);
  border-radius: 20px; padding: 36px 32px; width: 100%; max-width: 420px;
  position: relative; animation: slideUp .3s ease both;
}
.fp-close {
  position: absolute; top: 16px; right: 18px; background: none; border: none;
  color: rgba(255,255,255,.4); font-size: 1.4rem; cursor: pointer;
}
.fp-close:hover { color: #fff; }
.fp-title { font-family: 'Syne', sans-serif; font-size: 1.25rem; font-weight: 800; margin-bottom: 6px; }
.fp-sub   { font-size: .85rem; color: rgba(232,234,240,.5); margin-bottom: 22px; }
.fp-step  { display: none; }
.fp-step.active { display: block; }
.otp-row  { display: flex; gap: 8px; justify-content: center; }
.otp-row input {
  width: 42px; max-width: 42px; flex: none; text-align: center; font-size: 1.2rem; font-weight: 700;
  padding: 10px 0; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
  border-radius: 10px; color: #fff; outline: none; transition: border-color .2s;
}
.otp-row input:focus { border-color: var(--blue); }
.fp-timer { font-size: .78rem; color: rgba(255,170,0,.9); margin-top: 8px; }
.fp-err   { font-size: .82rem; color: var(--error); margin-top: 8px; min-height: 1.2em; }
.fp-ok    { font-size: .82rem; color: var(--green); margin-top: 8px; }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
</style>

<div class="fp-overlay" id="fp-overlay">
  <div class="fp-box">
    <button class="fp-close" onclick="closeForgot()">✕</button>

    <!-- Step 1: email -->
    <div class="fp-step active" id="fp-s1">
      <div class="fp-title">Mot de passe oublié ?</div>
      <div class="fp-sub">Entrez votre email et nous vous enverrons un code de vérification.</div>
      <div class="field">
        <label>Adresse email</label>
        <input type="email" id="fp-email" placeholder="vous@exemple.com">
      </div>
      <div class="fp-err" id="fp-err1"></div>
      <button class="btn-main" style="margin-top:10px" onclick="fpSendOtp()">Envoyer le code</button>
    </div>

    <!-- Step 2: OTP -->
    <div class="fp-step" id="fp-s2">
      <div class="fp-title">Vérification</div>
      <div class="fp-sub">Entrez le code à 6 chiffres envoyé à <strong id="fp-email-display"></strong>.</div>
      <div class="otp-row">
        <input type="text" maxlength="1" id="otp0" oninput="otpNext(0)">
        <input type="text" maxlength="1" id="otp1" oninput="otpNext(1)">
        <input type="text" maxlength="1" id="otp2" oninput="otpNext(2)">
        <input type="text" maxlength="1" id="otp3" oninput="otpNext(3)">
        <input type="text" maxlength="1" id="otp4" oninput="otpNext(4)">
        <input type="text" maxlength="1" id="otp5" oninput="otpNext(5)">
      </div>
      <div class="fp-timer" id="fp-timer"></div>
      <div class="fp-err" id="fp-err2"></div>
      <button class="btn-main" style="margin-top:16px" onclick="fpVerifyOtp()">Vérifier le code</button>
      <div style="text-align:center;margin-top:12px;font-size:.82rem;color:var(--muted)">
        <a href="#" style="color:var(--blue)" onclick="fpSendOtp()">Renvoyer un code</a>
      </div>
    </div>

    <!-- Step 3: new password -->
    <div class="fp-step" id="fp-s3">
      <div class="fp-title">Nouveau mot de passe</div>
      <div class="fp-sub">Choisissez un nouveau mot de passe sécurisé.</div>
      <div class="field">
        <label>Nouveau mot de passe</label>
        <div class="field-inner">
          <input type="password" id="fp-pass1" placeholder="Min. 8 car., 1 maj., 1 chiffre">
          <button class="toggle-pass" type="button" onclick="toggleVis('fp-pass1',this)">👁</button>
        </div>
      </div>
      <div class="field">
        <label>Confirmer</label>
        <div class="field-inner">
          <input type="password" id="fp-pass2" placeholder="••••••••">
          <button class="toggle-pass" type="button" onclick="toggleVis('fp-pass2',this)">👁</button>
        </div>
      </div>
      <div class="fp-err" id="fp-err3"></div>
      <button class="btn-main" style="margin-top:10px" onclick="fpResetPass()">Réinitialiser</button>
    </div>

    <!-- Step 4: success -->
    <div class="fp-step" id="fp-s4">
      <div style="text-align:center;padding:20px 0">
        <div style="font-size:3rem;margin-bottom:16px">✅</div>
        <div class="fp-title">Mot de passe réinitialisé !</div>
        <div class="fp-sub" style="margin-top:8px">Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</div>
        <button class="btn-main" style="margin-top:20px" onclick="closeForgot()">Se connecter</button>
      </div>
    </div>
  </div>
</div>

<script>
// ── Existing JS ────────────────────────────────────────────────
const googleClientId = <?= json_encode($googleClientId) ?>;

function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  event.target.classList.add('active');
  document.getElementById('v-' + tab).classList.add('active');
}

function switchLoginMethod(method) {
  // Update sub-tabs
  document.querySelectorAll('.sub-tab-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');

  // Show/hide forms
  document.getElementById('login-password-form').style.display = method === 'password' ? 'block' : 'none';
  document.getElementById('login-face-form').style.display = method === 'face' ? 'block' : 'none';

  // Stop camera if switching away from face
  if (method !== 'face' && faceRecognition.isCameraActive) {
    faceRecognition.stopCamera();
  }
}

function selRole(el, group) {
  document.querySelectorAll('#' + group + ' .role-card').forEach(c => c.classList.remove('sel'));
  el.classList.add('sel');
  if (group === 'login-roles') updateGoogleLoginState();
}
function showRegForm(type, btn) {
  document.querySelectorAll('.reg-type-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('form-user').style.display = type === 'user' ? 'block' : 'none';
  document.getElementById('form-startup').style.display = type === 'startup' ? 'block' : 'none';
}
function toggleVis(id, btn) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
  btn.textContent = el.type === 'password' ? '👁' : '🙈';
}
function clearErrs() {
  document.querySelectorAll('.field-error').forEach(e => { e.textContent = ''; e.style.display = 'none'; });
  document.querySelectorAll('input,select').forEach(e => e.classList.remove('is-error'));
}
function showErr(id, msg) {
  const el = document.getElementById(id);
  if (el) { el.textContent = msg; el.style.display = 'block'; }
}

function updateGoogleLoginState() {
  const wrap = document.getElementById('google-login-wrap');
  const note = document.getElementById('google-login-note');
  if (!wrap || !note) return;
  const role = document.querySelector('#login-roles .sel')?.dataset.r || 'user';
  const enabled = role === 'user';
  wrap.style.opacity = enabled ? '1' : '.45';
  wrap.style.pointerEvents = enabled ? 'auto' : 'none';
  note.textContent = enabled ? '' : 'Connexion Google disponible pour les utilisateurs.';
}

function handleGoogleCredentialResponse(response) {
  const role = document.querySelector('#login-roles .sel')?.dataset.r || 'user';
  if (role !== 'user') {
    updateGoogleLoginState();
    return;
  }

  const rememberMe = document.getElementById('l-remember')?.checked ? '1' : '';
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/auth.php?action=google_login';
  form.innerHTML = `<input type="hidden" name="credential" value="${response.credential}"><input type="hidden" name="remember_me" value="${rememberMe}">`;
  document.body.appendChild(form);
  form.submit();
}

function initGoogleSignIn() {
  if (!googleClientId || !window.google || !document.getElementById('google-signin-btn')) return;
  google.accounts.id.initialize({
    client_id: googleClientId,
    callback: handleGoogleCredentialResponse,
    auto_select: false,
    cancel_on_tap_outside: true
  });
  google.accounts.id.renderButton(document.getElementById('google-signin-btn'), {
    theme: 'outline',
    size: 'large',
    text: 'signin_with',
    shape: 'pill',
    width: 360
  });
  updateGoogleLoginState();
}

// ── Face Recognition Functions ────────────────────────────────
async function startFaceCamera() {
  try {
    const statusEl = document.getElementById('face-status');
    statusEl.className = 'face-status loading';
    statusEl.textContent = '📹 Activation de la caméra...';

    await faceRecognition.initCamera('face-video');
    
    document.getElementById('btn-start-camera').style.display = 'none';
    document.getElementById('btn-stop-camera').style.display = 'block';
    document.getElementById('btn-capture-face').style.display = 'block';
    
    statusEl.className = 'face-status success';
    statusEl.textContent = '✅ Caméra activée. Dirigez votre visage vers la caméra.';
  } catch (error) {
    const statusEl = document.getElementById('face-status');
    statusEl.className = 'face-status error';
    statusEl.textContent = '❌ Erreur: ' + error.message;
  }
}

function stopFaceCamera() {
  faceRecognition.stopCamera();
  document.getElementById('btn-start-camera').style.display = 'block';
  document.getElementById('btn-stop-camera').style.display = 'none';
  document.getElementById('btn-capture-face').style.display = 'none';
  const statusEl = document.getElementById('face-status');
  statusEl.className = 'face-status';
  statusEl.textContent = '';
}

async function captureFaceForLogin() {
  try {
    const email = document.getElementById('f-email').value.trim();
    const role = document.querySelector('#face-login-roles .sel')?.dataset.r || 'user';
    
    if (!email) {
      showErr('f-email-e', 'Email obligatoire');
      return;
    }

    const statusEl = document.getElementById('face-status');
    const btnCapture = document.getElementById('btn-capture-face');
    const btnLoading = document.getElementById('btn-face-loading');
    const btnTryAgain = document.getElementById('btn-face-try-again');

    statusEl.className = 'face-status loading';
    statusEl.textContent = '📸 Capture du visage...';
    
    btnCapture.style.display = 'none';
    btnLoading.style.display = 'block';

    const result = await faceRecognition.captureAndVerifyFace(email, role);

    // Success - create a hidden form and submit for login
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../api/auth.php?action=face_login';
    form.innerHTML = `
      <input type="hidden" name="email" value="${email}">
      <input type="hidden" name="role" value="${role}">
    `;
    document.body.appendChild(form);
    form.submit();

  } catch (error) {
    const statusEl = document.getElementById('face-status');
    const btnCapture = document.getElementById('btn-capture-face');
    const btnLoading = document.getElementById('btn-face-loading');
    const btnTryAgain = document.getElementById('btn-face-try-again');

    statusEl.className = 'face-status error';
    statusEl.textContent = '❌ ' + error.message;

    btnLoading.style.display = 'none';
    btnCapture.style.display = 'none';
    btnTryAgain.style.display = 'block';
  }
}

function doLogin() {
  clearErrs();
  const role = document.querySelector('#login-roles .sel')?.dataset.r || 'user';
  const email = document.getElementById('l-email').value.trim();
  const pass = document.getElementById('l-pass').value;
  const rememberMe = document.getElementById('l-remember').checked ? '1' : '';
  const captchaEl = document.querySelector('#login-captcha [name="h-captcha-response"]');
  const captchaToken = captchaEl ? captchaEl.value : '';
  let ok = true;
  if (!email) { showErr('l-email-e', 'Email obligatoire'); ok = false; }
  if (!pass)  { showErr('l-pass-e', 'Mot de passe obligatoire'); ok = false; }
  if (!captchaToken) { showErr('login-captcha-e', 'Veuillez compléter le CAPTCHA'); ok = false; }
  if (!ok) return;
  const form = document.createElement('form');
  form.method = 'POST'; form.action = '../../api/auth.php?action=login';
  form.innerHTML = `<input type="hidden" name="role" value="${role}"><input type="hidden" name="email" value="${email}"><input type="hidden" name="password" value="${pass}"><input type="hidden" name="remember_me" value="${rememberMe}"><input type="hidden" name="h-captcha-response" value="${captchaToken}">`;
  document.body.appendChild(form); form.submit();
}

function showRegForm(type, btn) {
  document.querySelectorAll('.reg-type-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('form-user').style.display = type === 'user' ? 'block' : 'none';
  document.getElementById('form-startup').style.display = type === 'startup' ? 'block' : 'none';
}
function toggleVis(id, btn) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
  btn.textContent = el.type === 'password' ? '👁' : '🙈';
}
function clearErrs() {
  document.querySelectorAll('.field-error').forEach(e => { e.textContent = ''; e.style.display = 'none'; });
  document.querySelectorAll('input,select').forEach(e => e.classList.remove('is-error'));
}
function showErr(id, msg) {
  const el = document.getElementById(id);
  if (el) { el.textContent = msg; el.style.display = 'block'; }
}
function doLogin() {
  clearErrs();
  const role = document.querySelector('#login-roles .sel')?.dataset.r || 'user';
  const email = document.getElementById('l-email').value.trim();
  const pass = document.getElementById('l-pass').value;
  const rememberMe = document.getElementById('l-remember').checked ? '1' : '';
  const captchaEl = document.querySelector('#login-captcha [name="h-captcha-response"]');
  const captchaToken = captchaEl ? captchaEl.value : '';
  let ok = true;
  if (!email) { showErr('l-email-e', 'Email obligatoire'); ok = false; }
  if (!pass)  { showErr('l-pass-e', 'Mot de passe obligatoire'); ok = false; }
  if (!captchaToken) { showErr('login-captcha-e', 'Veuillez compléter le CAPTCHA'); ok = false; }
  if (!ok) return;
  const form = document.createElement('form');
  form.method = 'POST'; form.action = '../../api/auth.php?action=login';
  form.innerHTML = `<input type="hidden" name="role" value="${role}"><input type="hidden" name="email" value="${email}"><input type="hidden" name="password" value="${pass}"><input type="hidden" name="remember_me" value="${rememberMe}"><input type="hidden" name="h-captcha-response" value="${captchaToken}">`;
  document.body.appendChild(form); form.submit();
}
function doRegUser() {
  clearErrs();
  const nom=document.getElementById('u-nom').value.trim(), prenom=document.getElementById('u-prenom').value.trim();
  const email=document.getElementById('u-email').value.trim(), tel=document.getElementById('u-tel').value.trim();
  const dob=document.getElementById('u-dob').value.trim(), photo=document.getElementById('u-photo').files[0];
  const pass=document.getElementById('u-pass').value, pass2=document.getElementById('u-pass2').value;
  const cgu=document.getElementById('u-cgu').checked;
  const captchaEl=document.querySelector('#user-captcha [name="h-captcha-response"]');
  const captchaToken=captchaEl?captchaEl.value:'';
  let ok=true;
  if(nom.length<2){showErr('u-nom-e','Nom requis');ok=false;}
  if(prenom.length<2){showErr('u-prenom-e','Prénom requis');ok=false;}
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)){showErr('u-email-e','Email invalide');ok=false;}
  if(tel&&!/^[0-9]{8}$/.test(tel)){showErr('u-tel-e','8 chiffres requis');ok=false;}
  if(pass.length<8||!/[A-Z]/.test(pass)||!/[0-9]/.test(pass)){showErr('u-pass-e','Min. 8 car., 1 maj., 1 chiffre');ok=false;}
  else if(pass!==pass2){showErr('u-pass2-e','Ne correspondent pas');ok=false;}
  if(!cgu){showErr('u-cgu-e','Accepter les CGU');ok=false;}
  if(!captchaToken){showErr('user-captcha-e','Compléter le CAPTCHA');ok=false;}
  if(!ok)return;
  const fd=new FormData();
  fd.append('nom',nom);fd.append('prenom',prenom);fd.append('email',email);
  fd.append('password',pass);fd.append('password_confirm',pass2);
  fd.append('telephone',tel);fd.append('date_naissance',dob);
  fd.append('h-captcha-response',captchaToken);
  if(photo)fd.append('profile_picture',photo);
  fetch('../../api/auth.php?action=register_user',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
    if(d.success){document.getElementById('v-register').innerHTML='<div class="alert success" style="display:block">'+d.message+'</div>';}
    else showErr('u-nom-e',d.error||'Erreur');
  });
}
function doRegStartup() {
  clearErrs();
  const nom=document.getElementById('s-nom').value.trim(), rnom=document.getElementById('s-rnom').value.trim();
  const rprenom=document.getElementById('s-rprenom').value.trim(), email=document.getElementById('s-email').value.trim();
  const secteur=document.getElementById('s-secteur').value, stade=document.getElementById('s-stade').value;
  const tel=document.getElementById('s-tel').value.trim(), site=document.getElementById('s-site').value.trim();
  const photo=document.getElementById('s-photo').files[0];
  const pass=document.getElementById('s-pass').value, pass2=document.getElementById('s-pass2').value;
  const cgu=document.getElementById('s-cgu').checked;
  const captchaEl=document.querySelector('#startup-captcha [name="h-captcha-response"]');
  const captchaToken=captchaEl?captchaEl.value:'';
  let ok=true;
  if(nom.length<2){showErr('s-nom-e','Nom requis');ok=false;}
  if(rnom.length<2){showErr('s-rnom-e','Nom responsable requis');ok=false;}
  if(rprenom.length<2){showErr('s-rprenom-e','Prénom requis');ok=false;}
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)){showErr('s-email-e','Email invalide');ok=false;}
  if(!secteur){showErr('s-secteur-e','Secteur requis');ok=false;}
  if(tel&&!/^[0-9]{8}$/.test(tel)){showErr('s-tel-e','8 chiffres requis');ok=false;}
  if(site&&!/^https?:\/\/.+\..+/.test(site)){showErr('s-site-e','URL invalide');ok=false;}
  if(pass.length<8||!/[A-Z]/.test(pass)||!/[0-9]/.test(pass)){showErr('s-pass-e','Min. 8 car., 1 maj., 1 chiffre');ok=false;}
  else if(pass!==pass2){showErr('s-pass2-e','Ne correspondent pas');ok=false;}
  if(!cgu){showErr('s-cgu-e','Accepter les CGU');ok=false;}
  if(!captchaToken){showErr('startup-captcha-e','Compléter le CAPTCHA');ok=false;}
  if(!ok)return;
  const fd=new FormData();
  fd.append('nom_startup',nom);fd.append('nom_responsable',rnom);fd.append('prenom_responsable',rprenom);
  fd.append('email',email);fd.append('password',pass);fd.append('password_confirm',pass2);
  fd.append('secteur',secteur);fd.append('stade',stade);fd.append('telephone',tel);fd.append('site_web',site);
  fd.append('h-captcha-response',captchaToken);
  if(photo)fd.append('profile_picture',photo);
  fetch('../../api/auth.php?action=register_startup',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
    if(d.success){document.getElementById('v-register').innerHTML='<div class="alert success" style="display:block">'+d.message+'</div>';}
    else showErr('s-nom-e',d.error||'Erreur');
  });
}

// ── Forgot Password ────────────────────────────────────────────
let fpResetToken = '';
let fpTimerInterval = null;

function openForgot(e) {
  e.preventDefault();
  document.getElementById('fp-overlay').classList.add('open');
  goFpStep(1);
}
function closeForgot() {
  document.getElementById('fp-overlay').classList.remove('open');
  clearInterval(fpTimerInterval);
}
document.getElementById('fp-overlay').addEventListener('click', function(e) {
  if (e.target === this) closeForgot();
});

function goFpStep(n) {
  document.querySelectorAll('.fp-step').forEach(s => s.classList.remove('active'));
  document.getElementById('fp-s' + n).classList.add('active');
}

function fpSendOtp() {
  const email = document.getElementById('fp-email').value.trim();
  const errEl = document.getElementById('fp-err1');
  errEl.textContent = '';
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
    errEl.textContent = 'Adresse email invalide.'; return;
  }
  const btn = event.target;
  btn.disabled = true; btn.textContent = 'Envoi…';

  const fd = new FormData();
  fd.append('email', email);
  fetch('../../api/auth.php?action=forgot_password', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(() => {
      document.getElementById('fp-email-display').textContent = email;
      [0,1,2,3,4,5].forEach(i => document.getElementById('otp'+i).value = '');
      document.getElementById('fp-err2').textContent = '';
      goFpStep(2);
      document.getElementById('otp0').focus();
      startOtpTimer(600); // 10 minutes
    })
    .finally(() => { btn.disabled = false; btn.textContent = 'Envoyer le code'; });
}

function startOtpTimer(seconds) {
  clearInterval(fpTimerInterval);
  const el = document.getElementById('fp-timer');
  function tick() {
    const m = Math.floor(seconds / 60), s = seconds % 60;
    el.textContent = `Code valide encore ${m}:${String(s).padStart(2,'0')}`;
    if (seconds-- <= 0) { clearInterval(fpTimerInterval); el.textContent = 'Code expiré. Demandez-en un nouveau.'; }
  }
  tick();
  fpTimerInterval = setInterval(tick, 1000);
}

function otpNext(i) {
  const val = document.getElementById('otp' + i).value;
  if (val && i < 5) document.getElementById('otp' + (i + 1)).focus();
}

function fpVerifyOtp() {
  const email = document.getElementById('fp-email').value.trim();
  const otp   = [0,1,2,3,4,5].map(i => document.getElementById('otp'+i).value).join('');
  const errEl = document.getElementById('fp-err2');
  errEl.textContent = '';
  if (otp.length !== 6) { errEl.textContent = 'Entrez les 6 chiffres.'; return; }
  const btn = event.target;
  btn.disabled = true; btn.textContent = 'Vérification…';
  const fd = new FormData();
  fd.append('email', email); fd.append('otp', otp);
  fetch('../../api/auth.php?action=verify_otp', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        fpResetToken = d.reset_token;
        clearInterval(fpTimerInterval);
        goFpStep(3);
      } else {
        errEl.textContent = d.error || 'Code incorrect.';
      }
    })
    .finally(() => { btn.disabled = false; btn.textContent = 'Vérifier le code'; });
}

function fpResetPass() {
  const pass  = document.getElementById('fp-pass1').value;
  const pass2 = document.getElementById('fp-pass2').value;
  const errEl = document.getElementById('fp-err3');
  errEl.textContent = '';
  if (pass.length < 8 || !/[A-Z]/.test(pass) || !/[0-9]/.test(pass)) {
    errEl.textContent = 'Min. 8 car., 1 majuscule, 1 chiffre.'; return;
  }
  if (pass !== pass2) { errEl.textContent = 'Les mots de passe ne correspondent pas.'; return; }
  const btn = event.target;
  btn.disabled = true; btn.textContent = 'Réinitialisation…';
  const fd = new FormData();
  fd.append('reset_token', fpResetToken); fd.append('password', pass); fd.append('password_confirm', pass2);
  fetch('../../api/auth.php?action=reset_password', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { goFpStep(4); }
      else { errEl.textContent = d.error || 'Erreur.'; }
    })
    .finally(() => { btn.disabled = false; btn.textContent = 'Réinitialiser'; });
}

// Handle OTP keyboard nav (backspace)
document.querySelectorAll('.otp-row input').forEach((inp, i, arr) => {
  inp.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !inp.value && i > 0) arr[i-1].focus();
  });
  inp.addEventListener('paste', e => {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
    [...text.slice(0,6)].forEach((ch, j) => {
      const el = document.getElementById('otp'+j); if(el) el.value = ch;
    });
    const last = Math.min(text.length, 5);
    document.getElementById('otp'+last)?.focus();
  });
});

updateGoogleLoginState();
if (googleClientId) {
  let googleInitAttempts = 0;
  const googleInitTimer = setInterval(() => {
    if (window.google && window.google.accounts) {
      clearInterval(googleInitTimer);
      initGoogleSignIn();
    } else if (++googleInitAttempts > 20) {
      clearInterval(googleInitTimer);
    }
  }, 250);
}
</script>
</body>
</html>
