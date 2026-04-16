<?php
session_start();
$isLoggedIn = !empty($_SESSION['user_id']);
if($isLoggedIn){
  $name = $_SESSION['user_name'] ?? 'Utilisateur';
  $type = $_SESSION['user_type'] ?? 'user';
  
  require_once __DIR__ . '/../../controllers/UserController.php';
  $userController = new UserController();
  
  // Get user detail
  $userController->getUser($_SESSION['user_id']);
  $user = $_SESSION['user_detail'] ?? null;
  
  // Handle profile update
  if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['save_profile'])){
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $dob = trim($_POST['date_naissance'] ?? '');
    
    if($nom && $prenom && $email){
      $userController->updateUserAction($_SESSION['user_id'], [
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'telephone' => $telephone,
        'date_naissance' => $dob,
        'role' => $user['role'],
        'statut' => $user['statut']
      ]);
      
      if(!empty($_SESSION['success'])){
        $_SESSION['user_name'] = "$prenom $nom";
        $userController->getUser($_SESSION['user_id']);
        $user = $_SESSION['user_detail'];
        $name = $_SESSION['user_name'];
        echo '<script>showToast("✅ Profil mis à jour avec succès!")</script>';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StartSmart – Créez votre startup en ligne</title>
<link rel="stylesheet" href="../../public/css/style.css">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#fff; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#333; }
  #toast { position:fixed; bottom:1.8rem; right:1.8rem; z-index:9999; background:#0d1b3e; color:#fff; padding:13px 20px; border-radius:8px; font-size:.875rem; font-weight:500; display:none; }
  #toast.show { display:block; animation:slideIn .3s ease-out; }
  @keyframes slideIn { from { transform:translateX(400px); opacity:0; } to { transform:translateX(0); opacity:1; } }
  
  /* Navigation */
  .navbar {
    background:#2d3e50; padding:1.2rem 3rem; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:100; box-shadow:0 2px 8px rgba(0,0,0,.1);
  }
  .navbar .logo { font-size:1.5rem; font-weight:800; color:#fff; letter-spacing:-.5px; }
  .navbar .logo span { margin:0 2px; }
  .navbar .logo .w { color:#fff; }
  .navbar .logo .b { color:#3b8cf7; }
  .navbar .logo .g { color:#2ddc78; }
  .nav-links { display:flex; gap:2rem; list-style:none; }
  .nav-links a { color:#fff; text-decoration:none; font-size:.95rem; font-weight:500; transition:color .3s; cursor:pointer; }
  .nav-links a:hover { color:#3b8cf7; }
  .navbar .btn-container { display:flex; gap:1rem; align-items:center; }
  .navbar .btn-login { background:transparent; color:#fff; border:none; cursor:pointer; font-size:.95rem; font-weight:500; }
  .navbar .btn-login:hover { color:#3b8cf7; }
  .navbar .btn-started { background:#2ddc78; color:#fff; border:none; padding:.6rem 1.5rem; border-radius:6px; cursor:pointer; font-weight:600; font-size:.9rem; transition:all .3s; }
  .navbar .btn-started:hover { background:#24c16b; transform:scale(1.05); }
  
  /* Hero Section */
  .hero {
    background:linear-gradient(135deg, #1e3a5f 0%, #2d5a7b 100%); color:#fff; padding:4rem 3rem; display:flex; justify-content:space-between; align-items:center; position:relative; overflow:hidden; min-height:500px;
  }
  .hero::before {
    content:''; position:absolute; top:0; left:0; right:0; bottom:0;
    background:radial-gradient(ellipse 80% 60% at 15% 30%, rgba(59,140,247,.15) 0%, transparent 60%),
               radial-gradient(ellipse 70% 65% at 85% 70%, rgba(45,220,120,.1) 0%, transparent 60%);
    pointer-events:none;
  }
  .hero-content { flex:1; z-index:1; }
  .hero h1 { font-size:3.5rem; font-weight:700; line-height:1.2; margin-bottom:1rem; }
  .hero .subtitle { font-size:1.4rem; font-weight:700; color:#2ddc78; margin-bottom:1.5rem; }
  .hero p { font-size:1rem; color:rgba(255,255,255,.85); line-height:1.6; margin-bottom:2rem; max-width:600px; }
  .hero-shape { flex:1; position:relative; height:400px; display:flex; align-items:center; justify-content:center; }
  .hex-shape { width:250px; height:250px; position:relative; }
  .hex { position:absolute; border:2px solid rgba(59,140,247,.3); }
  .hex1 { width:100px; height:115px; clip-path:polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%); background:rgba(59,140,247,.1); }
  .hex2 { width:150px; height:173px; clip-path:polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%); background:rgba(45,220,120,.08); top:40px; left:50px; }
  .hex3 { width:120px; height:138px; clip-path:polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%); background:rgba(59,140,247,.05); top:100px; left:65px; }
  
  /* Features Section */
  .features { padding:4rem 3rem; background:#f5f7fa; }
  .features-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:2rem; max-width:1200px; margin:0 auto; }
  .feature-card {
    background:#fff; border-radius:12px; padding:2.5rem; text-align:center; box-shadow:0 4px 12px rgba(0,0,0,.08); transition:all .3s;
  }
  .feature-card:hover { transform:translateY(-8px); box-shadow:0 12px 24px rgba(0,0,0,.15); }
  .feature-icon { width:70px; height:70px; margin:0 auto 1.5rem; background:#f0f4f8; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; }
  .feature-card:nth-child(1) .feature-icon { background:rgba(59,140,247,.1); }
  .feature-card:nth-child(2) .feature-icon { background:rgba(45,220,120,.1); }
  .feature-card:nth-child(3) .feature-icon { background:rgba(255,193,7,.1); }
  .feature-card h3 { font-size:1.2rem; font-weight:700; color:#1e3a5f; margin-bottom:.8rem; }
  .feature-card p { color:#666; font-size:.95rem; line-height:1.6; }
  
  /* User Dashboard Section */
  .dashboard-section { padding:3rem; background:#fff; display:none; }
  .dashboard-section.active { display:block; }
  .dashboard-header { margin-bottom:2rem; }
  .dashboard-header h2 { font-size:1.8rem; font-weight:700; color:#1e3a5f; }
  .dashboard-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; }
  .dashboard-card { background:linear-gradient(135deg, #f5f7fa 0%, #fff 100%); border-radius:12px; padding:2rem; box-shadow:0 4px 12px rgba(0,0,0,.08); cursor:pointer; transition:all .3s; }
  .dashboard-card:hover { transform:translateY(-4px); box-shadow:0 8px 20px rgba(0,0,0,.12); }
  .dashboard-card .icon { font-size:2.5rem; margin-bottom:1rem; }
  .dashboard-card h3 { font-size:1.1rem; font-weight:700; color:#1e3a5f; margin-bottom:.5rem; }
  .dashboard-card p { color:#666; font-size:.9rem; }
  
  .logout-btn { background:#e74c3c; color:#fff; border:none; padding:.7rem 1.5rem; border-radius:6px; cursor:pointer; font-weight:600; transition:background .3s; }
  .logout-btn:hover { background:#c0392b; }
  
  @media(max-width:768px) {
    .navbar { flex-direction:column; gap:1rem; }
    .nav-links { gap:1rem; font-size:.85rem; }
    .hero { flex-direction:column; padding:2rem; }
    .hero h1 { font-size:2.2rem; }
    .hero-shape { height:300px; }
    .hex-shape { width:200px; height:200px; }
  }
</style>
<body>

<div id="toast"></div>

<!-- Navigation -->
<nav class="navbar">
  <div class="logo">
    <span class="w">Start</span><span class="b">Smart</span><span class="g">:</span>
  </div>
  <ul class="nav-links">
    <li><a onclick="scrollTo('projects')">📁 Projets</a></li>
    <li><a onclick="scrollTo('sponsors')">🏆 Ressources des sponsors</a></li>
    <li><a onclick="scrollTo('events')">🎯 Événement</a></li>
    <li><a onclick="scrollTo('offers')">🚀 Offres des startups</a></li>
    <li><a onclick="scrollTo('forum')">💬 Notre forum</a></li>
  </ul>
  <div class="btn-container">
    <?php if($isLoggedIn): ?>
      <span style="color:#fff;font-size:.9rem;cursor:pointer;" onclick="openProfileModal()">👤 Bienvenue, <?= htmlspecialchars($name) ?></span>
      <button class="logout-btn" onclick="doLogout()">Log out</button>
    <?php endif; ?>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-content">
    <h1>CRÉEZ VOTRE<br>STARTUP EN LIGNE</h1>
    <div class="subtitle">COLLABOREZ, TROUVEZ DES<br>FINANCEMENTS, & RÉUSSISSEZ.</div>
    <p>Notre projet StartSmart utilise les technologies numériques pour faciliter en ligne de consommatrices, et encourageage. StartSmart représente une réponse moderne au défis du futur du business.</p>
    <button class="btn-started" onclick="<?= $isLoggedIn ? "location.href='#dashboard'" : "location.href='../auth/login.php'" ?>">GET STARTED</button>
  </div>
  <div class="hero-shape">
    <div class="hex-shape">
      <div class="hex hex1"></div>
      <div class="hex hex2"></div>
      <div class="hex hex3"></div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="features" id="projects">
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">📁</div>
      <h3>Projets</h3>
      <p>Découvrez et participez à des projets innovants, partager votre expertise et développer votre réseau professionnel.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🏆</div>
      <h3>Ressources des sponsors</h3>
      <p>Accédez aux ressources et opportunités proposées par nos partenaires sponsors pour accélérer votre croissance.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🎯</div>
      <h3>Événement</h3>
      <p>Participez à nos événements exclusifs, conférences et ateliers pour réseauter et apprendre des meilleurs.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🚀</div>
      <h3>Offres des startups</h3>
      <p>Explorez les offres spéciales et services proposés par la communauté StartSmart pour booster votre activité.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">💬</div>
      <h3>Notre forum</h3>
      <p>Connectez-vous avec d'autres entrepreneurs, posez des questions et partagez vos expériences en toute bienveillance.</p>
    </div>
  </div>
</section>

<!-- User Dashboard Section -->
<?php if($isLoggedIn): ?>
<section class="dashboard-section active" id="dashboard">
  <div class="dashboard-header">
    <h2 style="cursor:pointer;" onclick="openProfileModal()">👤 Bonjour, <?= htmlspecialchars($name) ?> 👋</h2>
  </div>
  <div class="dashboard-grid">
    <div class="dashboard-card"><div class="icon">💡</div><h3>Mes idées</h3><p>Créez et gérez vos idées de projets</p></div>
    <div class="dashboard-card"><div class="icon">🤝</div><h3>Collaboration</h3><p>Trouvez des co-fondateurs et partenaires</p></div>
    <div class="dashboard-card"><div class="icon">💰</div><h3>Financement</h3><p>Découvrez les opportunités de financement</p></div>
    <div class="dashboard-card"><div class="icon">📊</div><h3>Métriques</h3><p>Suivez la progression de vos projets</p></div>
    <div class="dashboard-card" onclick="openProfileModal()" style="cursor:pointer;"><div class="icon">👤</div><h3>Mon profil</h3><p>Gérez vos informations personnelles</p></div>
    <div class="dashboard-card"><div class="icon">🔔</div><h3>Notifications</h3><p>Restez informé des mises à jour</p></div>
  </div>
</section>
<?php endif; ?>

<script>
function doLogout() {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/auth.php?action=logout';
  form.style.display = 'none';
  document.body.appendChild(form);
  form.submit();
}

function scrollTo(section) {
  const el = document.getElementById(section);
  if(el) el.scrollIntoView({ behavior:'smooth' });
}

let toastTimeout;
function showToast(msg) {
  clearTimeout(toastTimeout);
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.classList.add('show');
  toastTimeout = setTimeout(() => el.classList.remove('show'), 3000);
}

function openProfileModal() {
  document.getElementById('profile-modal').style.display = 'flex';
}

function closeProfileModal() {
  document.getElementById('profile-modal').style.display = 'none';
}

window.onclick = function(event) {
  const modal = document.getElementById('profile-modal');
  if(event.target === modal) {
    closeProfileModal();
  }
}
</script>

<!-- Profile Modal -->
<div id="profile-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,.5); align-items:center; justify-content:center; font-family:inherit;">
  <div style="background:#fff; border-radius:12px; width:90%; max-width:600px; max-height:80vh; overflow-y:auto; box-shadow:0 8px 32px rgba(0,0,0,.15); padding:0;">
    <!-- Modal Header -->
    <div style="background:linear-gradient(135deg, #1e3a5f 0%, #2d5a7b 100%); color:#fff; padding:1.5rem; display:flex; justify-content:space-between; align-items:center; border-radius:12px 12px 0 0;">
      <h2 style="margin:0; font-size:1.5rem;">Mon Profil</h2>
      <button onclick="closeProfileModal()" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">✕</button>
    </div>
    
    <!-- Modal Body -->
    <div style="padding:2rem;">
      <form method="POST" style="display:grid; gap:1.5rem;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div>
            <label style="display:block; margin-bottom:.5rem; font-weight:600; color:#1e3a5f; font-size:.9rem;">Prénom *</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required style="width:100%; padding:.75rem; border:1px solid #ddd; border-radius:6px; font-size:.9rem;">
          </div>
          <div>
            <label style="display:block; margin-bottom:.5rem; font-weight:600; color:#1e3a5f; font-size:.9rem;">Nom *</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required style="width:100%; padding:.75rem; border:1px solid #ddd; border-radius:6px; font-size:.9rem;">
          </div>
        </div>

        <div>
          <label style="display:block; margin-bottom:.5rem; font-weight:600; color:#1e3a5f; font-size:.9rem;">Email *</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required style="width:100%; padding:.75rem; border:1px solid #ddd; border-radius:6px; font-size:.9rem;">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div>
            <label style="display:block; margin-bottom:.5rem; font-weight:600; color:#1e3a5f; font-size:.9rem;">Téléphone</label>
            <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" style="width:100%; padding:.75rem; border:1px solid #ddd; border-radius:6px; font-size:.9rem;">
          </div>
          <div>
            <label style="display:block; margin-bottom:.5rem; font-weight:600; color:#1e3a5f; font-size:.9rem;">Date de naissance</label>
            <input type="date" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>" style="width:100%; padding:.75rem; border:1px solid #ddd; border-radius:6px; font-size:.9rem;">
          </div>
        </div>

        <div>
          <label style="display:block; margin-bottom:.5rem; font-weight:600; color:#1e3a5f; font-size:.9rem;">Rôle</label>
          <input type="text" value="<?= htmlspecialchars($user['role'] ?? '') ?>" disabled style="width:100%; padding:.75rem; border:1px solid #ddd; border-radius:6px; font-size:.9rem; background:#f0f0f0; cursor:not-allowed; color:#999;">
        </div>

        <div style="display:flex; gap:1rem; margin-top:1.5rem;">
          <button type="submit" name="save_profile" value="1" style="flex:1; padding:.75rem; background:#3b8cf7; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; font-size:.9rem;">💾 Enregistrer</button>
          <button type="button" onclick="closeProfileModal()" style="flex:1; padding:.75rem; background:#e0e0e0; color:#333; border:none; border-radius:6px; font-weight:600; cursor:pointer; font-size:.9rem;">Fermer</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
