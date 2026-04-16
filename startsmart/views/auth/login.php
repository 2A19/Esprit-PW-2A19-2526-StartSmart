<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StartSmart – Connexion</title>
<link rel="stylesheet" href="../../public/css/style.css">
<style>
  body{background:var(--navy);display:flex;align-items:center;justify-content:center;min-height:100vh;}
  body::before{content:'';position:fixed;inset:0;
    background:radial-gradient(ellipse 60% 50% at 15% 20%,rgba(59,140,247,.12) 0%,transparent 60%),
               radial-gradient(ellipse 50% 55% at 85% 80%,rgba(45,220,120,.07) 0%,transparent 60%);
    pointer-events:none;}
  .auth-wrap{width:min(480px,96vw);position:relative;z-index:1;padding:2rem 0;}
  .auth-logo{text-align:center;margin-bottom:2rem;}
  .auth-logo .logo{font-size:3.4rem;}
  .auth-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2.5rem 2rem;backdrop-filter:blur(18px);}
  .tab-row{display:flex;gap:5px;background:rgba(0,0,0,.25);border-radius:8px;padding:4px;margin-bottom:1.8rem;}
  .tab-btn{flex:1;border:none;background:none;color:rgba(255,255,255,.45);font-family:var(--font);font-size:.875rem;font-weight:600;padding:10px;border-radius:5px;cursor:pointer;transition:all .18s;}
  .tab-btn.active{background:var(--blue);color:#fff;}
  .view{display:none;} .view.active{display:block;}
  .sec-title{font-size:.65rem;font-weight:700;letter-spacing:.12em;color:var(--green);text-transform:uppercase;margin-bottom:10px;}
  .role-row{display:flex;gap:10px;margin-bottom:1.5rem;}
  .role-card{flex:1;border:1.5px solid rgba(255,255,255,.1);border-radius:8px;padding:13px 8px;cursor:pointer;text-align:center;transition:all .18s;background:rgba(0,0,0,.2);color:rgba(255,255,255,.6);}
  .role-card:hover{border-color:var(--blue);}
  .role-card.sel{border-color:var(--green);background:rgba(45,220,120,.08);color:#fff;}
  .role-card .ri{font-size:1.5rem;margin-bottom:5px;}
  .role-card .rl{font-size:.75rem;font-weight:700;}
  /* Override field styles for dark bg */
  .field label{color:rgba(255,255,255,.5);}
  .field input,.field select{background:rgba(0,0,0,.28);border-color:rgba(255,255,255,.1);color:#fff;}
  .field input::placeholder{color:rgba(255,255,255,.3);}
  .field input:focus,.field select:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(59,140,247,.15);}
  .field select option{background:var(--navy-mid);color:#fff;}
  .field input.is-error{border-color:var(--danger);}
  .field-error{color:#f87089;}
  .pw-eye{color:rgba(255,255,255,.4);}
  .divider{height:1px;background:rgba(255,255,255,.07);margin:1.2rem 0;}
  .switch-link{text-align:center;margin-top:1.2rem;font-size:.85rem;color:rgba(255,255,255,.4);}
  .switch-link a{color:var(--blue);font-weight:600;}
  .switch-link a:hover{color:var(--green);}
  .check-row{display:flex;align-items:flex-start;gap:9px;font-size:.82rem;color:rgba(255,255,255,.5);}
  .check-row input[type=checkbox]{accent-color:var(--green);width:16px;height:16px;flex-shrink:0;margin-top:3px;}
  .check-row a{color:var(--blue);}
  /* Alert overrides */
  .alert-error{background:rgba(232,68,90,.12);border-color:rgba(232,68,90,.35);color:#f87089;}
  .alert-success{background:rgba(45,220,120,.1);border-color:rgba(45,220,120,.3);color:#2ddc78;}
  .pw-bars .pw-bar{background:rgba(255,255,255,.12);}
  .pw-label{color:rgba(255,255,255,.35);}
</style>
</head>
<body>
<?php 
if(session_status()===PHP_SESSION_NONE) session_start();
if(!empty($_SESSION['user_id']) && !empty($_SESSION['user_role'])){
  header('Location: '.($_SESSION['user_role']==='admin' ? '../back/dashboard.php' : '../front/dashboard.php'));
  exit;
}

// Get errors and success messages from session
$loginErrors = $_SESSION['login_errors'] ?? [];
$regErrors = $_SESSION['reg_errors'] ?? [];
$regSuccess = $_SESSION['reg_success'] ?? '';
$regFormData = $_SESSION['reg_form_data'] ?? [];

// Clear session messages after displaying
unset($_SESSION['login_errors'], $_SESSION['reg_errors'], $_SESSION['reg_success'], $_SESSION['reg_form_data']);

// Determine which tab to show
$showTab = $_GET['tab'] ?? 'login';
?>

<div id="toast"></div>

<div class="auth-wrap">
  <div class="auth-logo">
    <img src="../../public/img/logo.png" alt="StartSmart Logo" style="max-width:180px;height:auto;margin-bottom:1rem;">
    <div class="logo"><span class="w">Start</span><span class="b">Smart</span><span class="g">:</span></div>
  </div>

  <div class="auth-card">

    <!-- Tabs -->
    <div class="tab-row">
      <button class="tab-btn <?= $showTab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Connexion</button>
      <button class="tab-btn <?= $showTab !== 'login' ? 'active' : '' ?>" onclick="switchTab('register')">Inscription</button>
    </div>

    <!-- ══════════ VUE LOGIN ══════════ -->
    <div id="v-login" class="view <?= $showTab === 'login' ? 'active' : '' ?>">
      <?php if (!empty($loginErrors)): ?>
        <div class="alert alert-error" style="margin-bottom:1.5rem;">
          ❌ <?= htmlspecialchars($loginErrors['general'] ?? 'Erreur de connexion.') ?>
        </div>
      <?php endif; ?>
      <div class="alert alert-error" id="login-err" style="display:none;"></div>

      <div class="sec-title">Je suis…</div>
      <div class="role-row" id="login-roles">
        <div class="role-card sel" data-r="user"    onclick="selRole(this,'login-roles')"><div class="ri">👤</div><div class="rl">Utilisateur</div></div>
        <div class="role-card"     data-r="startup" onclick="selRole(this,'login-roles')"><div class="ri">🚀</div><div class="rl">Startup</div></div>
        <div class="role-card"     data-r="admin"   onclick="selRole(this,'login-roles')"><div class="ri">🛡️</div><div class="rl">Admin</div></div>
      </div>

      <div class="field">
        <label>Email</label>
        <input type="text" id="l-email" placeholder="votre@email.com" autocomplete="email">
        <span class="field-error" id="l-email-e"></span>
      </div>
      <div class="field">
        <label>Mot de passe</label>
        <div class="pw-wrap">
          <input type="password" id="l-pass" placeholder="••••••••" autocomplete="current-password">
          <button class="pw-eye" onclick="togglePw('l-pass',this)" tabindex="-1">👁</button>
        </div>
        <span class="field-error" id="l-pass-e"></span>
      </div>
      <div style="text-align:right;margin:-4px 0 14px"><a href="#" style="font-size:.8rem;color:rgba(59,140,247,.8)">Mot de passe oublié ?</a></div>

      <button class="btn btn-primary" style="width:100%" id="btn-login" onclick="doLogin()">Se connecter</button>

      <div class="switch-link">Pas encore membre ? <a href="#" onclick="switchTab('register')">Créer un compte</a></div>
    </div>

    <!-- ══════════ VUE REGISTER ══════════ -->
    <div id="v-register" class="view <?= $showTab !== 'login' ? 'active' : '' ?>">
      <?php if (!empty($regErrors)): ?>
        <div class="alert alert-error" style="margin-bottom:1.5rem;">
          ❌ <?= htmlspecialchars($regErrors['general'] ?? 'Erreur lors de l\'inscription.') ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($regSuccess)): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem;">
          ✅ <?= htmlspecialchars($regSuccess) ?>
        </div>
      <?php endif; ?>
      <div class="alert alert-error" id="reg-err" style="display:none;"></div>
      <div class="alert alert-success" id="reg-ok" style="display:none;"></div>

      <div class="sec-title">Type de compte</div>
      <div class="role-row" id="reg-roles">
        <div class="role-card sel" data-r="user"    onclick="selRole(this,'reg-roles');showRegForm('user')"><div class="ri">👤</div><div class="rl">Utilisateur</div></div>
        <div class="role-card"     data-r="startup" onclick="selRole(this,'reg-roles');showRegForm('startup')"><div class="ri">🚀</div><div class="rl">Startup</div></div>
      </div>

      <!-- Formulaire USER -->
      <div id="form-user">
        <div class="row-2">
          <div class="field"><label>Nom *</label><input type="text" id="u-nom" placeholder="Ben Ali"><span class="field-error" id="u-nom-e"></span></div>
          <div class="field"><label>Prénom *</label><input type="text" id="u-prenom" placeholder="Ahmed"><span class="field-error" id="u-prenom-e"></span></div>
        </div>
        <div class="field"><label>Email *</label><input type="text" id="u-email" placeholder="ahmed@email.com"><span class="field-error" id="u-email-e"></span></div>
        <div class="field"><label>Téléphone</label><input type="text" id="u-tel" placeholder="55123456"><span class="field-error" id="u-tel-e"></span></div>
        <div class="field"><label>Date de naissance</label><input type="text" id="u-dob" placeholder="AAAA-MM-JJ"><span class="field-error" id="u-dob-e"></span></div>
        <div class="field"><label>Mot de passe *</label>
          <div class="pw-wrap"><input type="password" id="u-pass" placeholder="Min. 8 car., 1 maj., 1 chiffre" oninput="strengthBar(this,'ub')"><button class="pw-eye" onclick="togglePw('u-pass',this)" tabindex="-1">👁</button></div>
          <div class="pw-bars"><div class="pw-bar" id="ub1"></div><div class="pw-bar" id="ub2"></div><div class="pw-bar" id="ub3"></div><div class="pw-bar" id="ub4"></div></div>
          <div class="pw-label" id="ub-lbl">Entrez un mot de passe</div>
          <span class="field-error" id="u-pass-e"></span>
        </div>
        <div class="field"><label>Confirmer mot de passe *</label>
          <div class="pw-wrap"><input type="password" id="u-pass2" placeholder="Répétez le mot de passe"><button class="pw-eye" onclick="togglePw('u-pass2',this)" tabindex="-1">👁</button></div>
          <span class="field-error" id="u-pass2-e"></span>
        </div>
        <div class="check-row" style="margin-bottom:14px">
          <input type="checkbox" id="u-cgu">
          <label for="u-cgu">J'accepte les <a href="#">conditions d'utilisation</a></label>
        </div>
        <span class="field-error" id="u-cgu-e" style="display:none;margin-top:-10px;margin-bottom:10px"></span>
        <button class="btn btn-primary" style="width:100%" id="btn-reg-user" onclick="doRegUser()">Créer mon compte</button>
      </div>

      <!-- Formulaire STARTUP -->
      <div id="form-startup" style="display:none">
        <div class="field"><label>Nom de la startup *</label><input type="text" id="s-nom" placeholder="TechTunisia"><span class="field-error" id="s-nom-e"></span></div>
        <div class="row-2">
          <div class="field"><label>Nom responsable *</label><input type="text" id="s-rnom" placeholder="Chaabane"><span class="field-error" id="s-rnom-e"></span></div>
          <div class="field"><label>Prénom responsable *</label><input type="text" id="s-rprenom" placeholder="Mehdi"><span class="field-error" id="s-rprenom-e"></span></div>
        </div>
        <div class="field"><label>Email professionnel *</label><input type="text" id="s-email" placeholder="contact@startup.tn"><span class="field-error" id="s-email-e"></span></div>
        <div class="field"><label>Secteur d'activité *</label>
          <select id="s-secteur"><option value="">-- Choisir --</option><option>Technologie</option><option>Agriculture</option><option>Education</option><option>Santé</option><option>Finance</option><option>Commerce</option><option>Energie</option><option>Transport</option><option>Autre</option></select>
          <span class="field-error" id="s-secteur-e"></span>
        </div>
        <div class="row-2">
          <div class="field"><label>Stade</label>
            <select id="s-stade"><option value="idee">💡 Idée</option><option value="prototype">🔧 Prototype</option><option value="mvp">🚀 MVP</option><option value="croissance">📈 Croissance</option><option value="scale">🌍 Scale</option></select>
          </div>
          <div class="field"><label>Téléphone</label><input type="text" id="s-tel" placeholder="55001122"><span class="field-error" id="s-tel-e"></span></div>
        </div>
        <div class="field"><label>Site web</label><input type="text" id="s-site" placeholder="https://startup.tn"><span class="field-error" id="s-site-e"></span></div>
        <div class="field"><label>Mot de passe *</label>
          <div class="pw-wrap"><input type="password" id="s-pass" placeholder="Min. 8 car., 1 maj., 1 chiffre" oninput="strengthBar(this,'sb')"><button class="pw-eye" onclick="togglePw('s-pass',this)" tabindex="-1">👁</button></div>
          <div class="pw-bars"><div class="pw-bar" id="sb1"></div><div class="pw-bar" id="sb2"></div><div class="pw-bar" id="sb3"></div><div class="pw-bar" id="sb4"></div></div>
          <div class="pw-label" id="sb-lbl">Entrez un mot de passe</div>
          <span class="field-error" id="s-pass-e"></span>
        </div>
        <div class="field"><label>Confirmer mot de passe *</label>
          <div class="pw-wrap"><input type="password" id="s-pass2" placeholder="Répétez"><button class="pw-eye" onclick="togglePw('s-pass2',this)" tabindex="-1">👁</button></div>
          <span class="field-error" id="s-pass2-e"></span>
        </div>
        <div class="check-row" style="margin-bottom:14px">
          <input type="checkbox" id="s-cgu">
          <label for="s-cgu">J'accepte les <a href="#">conditions d'utilisation</a></label>
        </div>
        <span class="field-error" id="s-cgu-e" style="display:none;margin-top:-10px;margin-bottom:10px"></span>
        <button class="btn btn-primary" style="width:100%" id="btn-reg-startup" onclick="doRegStartup()">Créer mon compte startup</button>
      </div>

      <div class="switch-link">Déjà membre ? <a href="#" onclick="switchTab('login')">Se connecter</a></div>
    </div>

  </div><!-- /auth-card -->
</div><!-- /auth-wrap -->

<script>
/* ── Helpers ─────────────────────────────────────── */

function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach((b,i)=> b.classList.toggle('active',(i===0)===(tab==='login')));
  document.getElementById('v-login').classList.toggle('active', tab==='login');
  document.getElementById('v-register').classList.toggle('active', tab==='register');
  clearAll();
}

function selRole(el, group) {
  document.querySelectorAll('#'+group+' .role-card').forEach(c=>c.classList.remove('sel'));
  el.classList.add('sel');
}

function showRegForm(type) {
  document.getElementById('form-user').style.display    = type==='user'    ? '' : 'none';
  document.getElementById('form-startup').style.display = type==='startup' ? '' : 'none';
}

function togglePw(id, btn) {
  const i = document.getElementById(id);
  i.type = i.type==='password' ? 'text' : 'password';
  btn.textContent = i.type==='password' ? '👁' : '🙈';
}

const PW_COLORS = ['#e8445a','#f5a623','#f5a623','#2ddc78'];
const PW_LABELS = ['Très faible','Faible','Moyen','Fort'];

function strengthBar(input, prefix) {
  const pw = input.value;
  let score = 0;
  if(pw.length>=8) score++;
  if(/[A-Z]/.test(pw)) score++;
  if(/[0-9]/.test(pw)) score++;
  if(/[^A-Za-z0-9]/.test(pw)) score++;
  [1,2,3,4].forEach(n=>{
    const b = document.getElementById(prefix+n);
    if(b) b.style.background = n<=score ? PW_COLORS[score-1] : 'rgba(255,255,255,.12)';
  });
  const lbl = document.getElementById(prefix+'-lbl');
  if(lbl){ lbl.textContent = pw ? PW_LABELS[Math.min(score-1,3)] : 'Entrez un mot de passe';
           lbl.style.color  = pw ? PW_COLORS[Math.min(score-1,3)] : 'rgba(255,255,255,.35)'; }
}

function showErr(id, msg) {
  const el = document.getElementById(id);
  if(el){ el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
}

function clearAll() {
  document.querySelectorAll('.field-error').forEach(e=>{ e.textContent=''; e.style.display='none'; });
  document.querySelectorAll('.field input,.field select').forEach(e=>e.classList.remove('is-error'));
  ['login-err','reg-err','reg-ok'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.className='alert';
  });
}

function showAlert(id, type, msg) {
  const el = document.getElementById(id);
  el.textContent = msg; el.className = 'alert alert-'+type+' show';
}

function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if(!btn) return;
  btn.disabled = loading;
  btn.innerHTML = loading ? '<span class="spinner"></span>Chargement…' : btn.dataset.orig || btn.textContent;
  if(!loading && btn.dataset.orig) btn.innerHTML = btn.dataset.orig;
}

function showErrors(errors, prefix) {
  if(typeof errors === 'object') {
    Object.entries(errors).forEach(([k,v]) => {
      showErr((prefix||'')+k+'-e', v);
      const inp = document.getElementById((prefix||'')+k);
      if(inp) inp.classList.add('is-error');
    });
  }
}

/* ── LOGIN ──────────────────────────────────────── */
function doLogin() {
  clearAll();
  const role  = document.querySelector('#login-roles .sel')?.dataset.r || 'user';
  const email = document.getElementById('l-email').value.trim();
  const pass  = document.getElementById('l-pass').value;

  // Validation JS côté client
  let ok = true;
  if(!email)                    { showErr('l-email-e','Email obligatoire.'); document.getElementById('l-email').classList.add('is-error'); ok=false; }
  else if(!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { showErr('l-email-e',"Format d'email invalide."); document.getElementById('l-email').classList.add('is-error'); ok=false; }
  if(!pass)                     { showErr('l-pass-e','Mot de passe obligatoire.'); document.getElementById('l-pass').classList.add('is-error'); ok=false; }
  if(!ok) return;

  document.getElementById('btn-login').dataset.orig = 'Se connecter';
  setLoading('btn-login', true);

  // Créer un formulaire caché et le soumettre
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/auth.php?action=login';
  form.style.display = 'none';
  
  const inputRole = document.createElement('input');
  inputRole.type = 'hidden';
  inputRole.name = 'role';
  inputRole.value = role;
  
  const inputEmail = document.createElement('input');
  inputEmail.type = 'hidden';
  inputEmail.name = 'email';
  inputEmail.value = email;
  
  const inputPass = document.createElement('input');
  inputPass.type = 'hidden';
  inputPass.name = 'password';
  inputPass.value = pass;
  
  form.appendChild(inputRole);
  form.appendChild(inputEmail);
  form.appendChild(inputPass);
  document.body.appendChild(form);
  form.submit();
}

/* ── REGISTER USER ──────────────────────────────── */
function doRegUser() {
  clearAll();
  const nom    = document.getElementById('u-nom').value.trim();
  const prenom = document.getElementById('u-prenom').value.trim();
  const email  = document.getElementById('u-email').value.trim();
  const tel    = document.getElementById('u-tel').value.trim();
  const dob    = document.getElementById('u-dob').value.trim();
  const pass   = document.getElementById('u-pass').value;
  const pass2  = document.getElementById('u-pass2').value;
  const cgu    = document.getElementById('u-cgu').checked;

  // Validation JS
  let ok = true;
  if(nom.length<2)               { showErr('u-nom-e','Nom requis (min. 2 car.).');    document.getElementById('u-nom').classList.add('is-error');   ok=false; }
  if(prenom.length<2)            { showErr('u-prenom-e','Prénom requis (min. 2 car.).'); document.getElementById('u-prenom').classList.add('is-error');ok=false; }
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { showErr('u-email-e',"Email invalide."); document.getElementById('u-email').classList.add('is-error'); ok=false; }
  if(tel && !/^[0-9]{8}$/.test(tel)) { showErr('u-tel-e','8 chiffres requis.'); document.getElementById('u-tel').classList.add('is-error'); ok=false; }
  if(dob && !/^\d{4}-\d{2}-\d{2}$/.test(dob)) { showErr('u-dob-e','Format: AAAA-MM-JJ'); document.getElementById('u-dob').classList.add('is-error'); ok=false; }
  if(pass.length<8 || !/[A-Z]/.test(pass) || !/[0-9]/.test(pass)) { showErr('u-pass-e','Min. 8 car., 1 majuscule, 1 chiffre.'); document.getElementById('u-pass').classList.add('is-error'); ok=false; }
  else if(pass!==pass2)          { showErr('u-pass2-e','Les mots de passe ne correspondent pas.'); document.getElementById('u-pass2').classList.add('is-error'); ok=false; }
  if(!cgu)                       { showErr('u-cgu-e','Vous devez accepter les CGU.'); document.getElementById('u-cgu-e').style.display='block'; ok=false; }
  if(!ok) return;

  document.getElementById('btn-reg-user').dataset.orig = 'Créer mon compte';
  setLoading('btn-reg-user', true);

  // Créer un formulaire caché et le soumettre
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/auth.php?action=register_user';
  form.style.display = 'none';
  
  const fields = [
    { name: 'nom', value: nom },
    { name: 'prenom', value: prenom },
    { name: 'email', value: email },
    { name: 'password', value: pass },
    { name: 'password_confirm', value: pass2 },
    { name: 'telephone', value: tel },
    { name: 'date_naissance', value: dob }
  ];
  
  fields.forEach(field => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = field.name;
    input.value = field.value;
    form.appendChild(input);
  });
  
  document.body.appendChild(form);
  form.submit();
}

/* ── REGISTER STARTUP ───────────────────────────── */
function doRegStartup() {
  clearAll();
  const nom    = document.getElementById('s-nom').value.trim();
  const rnom   = document.getElementById('s-rnom').value.trim();
  const rprenom= document.getElementById('s-rprenom').value.trim();
  const email  = document.getElementById('s-email').value.trim();
  const secteur= document.getElementById('s-secteur').value;
  const stade  = document.getElementById('s-stade').value;
  const tel    = document.getElementById('s-tel').value.trim();
  const site   = document.getElementById('s-site').value.trim();
  const pass   = document.getElementById('s-pass').value;
  const pass2  = document.getElementById('s-pass2').value;
  const cgu    = document.getElementById('s-cgu').checked;

  let ok = true;
  if(nom.length<2)    { showErr('s-nom-e','Nom requis.'); document.getElementById('s-nom').classList.add('is-error'); ok=false; }
  if(rnom.length<2)   { showErr('s-rnom-e','Requis.'); document.getElementById('s-rnom').classList.add('is-error'); ok=false; }
  if(rprenom.length<2){ showErr('s-rprenom-e','Requis.'); document.getElementById('s-rprenom').classList.add('is-error'); ok=false; }
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { showErr('s-email-e','Email invalide.'); document.getElementById('s-email').classList.add('is-error'); ok=false; }
  if(!secteur)        { showErr('s-secteur-e','Secteur requis.'); document.getElementById('s-secteur').classList.add('is-error'); ok=false; }
  if(tel && !/^[0-9]{8}$/.test(tel)) { showErr('s-tel-e','8 chiffres requis.'); document.getElementById('s-tel').classList.add('is-error'); ok=false; }
  if(site && !/^https?:\/\/.+\..+/.test(site)) { showErr('s-site-e','URL invalide (ex: https://...).'); document.getElementById('s-site').classList.add('is-error'); ok=false; }
  if(pass.length<8 || !/[A-Z]/.test(pass) || !/[0-9]/.test(pass)) { showErr('s-pass-e','Min. 8 car., 1 majuscule, 1 chiffre.'); document.getElementById('s-pass').classList.add('is-error'); ok=false; }
  else if(pass!==pass2) { showErr('s-pass2-e','Ne correspondent pas.'); document.getElementById('s-pass2').classList.add('is-error'); ok=false; }
  if(!cgu) { showErr('s-cgu-e','Vous devez accepter les CGU.'); document.getElementById('s-cgu-e').style.display='block'; ok=false; }
  if(!ok) return;

  document.getElementById('btn-reg-startup').dataset.orig = 'Créer mon compte startup';
  setLoading('btn-reg-startup', true);

  // Créer un formulaire caché et le soumettre
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../../api/auth.php?action=register_startup';
  form.style.display = 'none';
  
  const fields = [
    { name: 'nom_startup', value: nom },
    { name: 'nom_responsable', value: rnom },
    { name: 'prenom_responsable', value: rprenom },
    { name: 'email', value: email },
    { name: 'password', value: pass },
    { name: 'password_confirm', value: pass2 },
    { name: 'secteur', value: secteur },
    { name: 'stade', value: stade },
    { name: 'telephone', value: tel },
    { name: 'site_web', value: site }
  ];
  
  fields.forEach(field => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = field.name;
    input.value = field.value;
    form.appendChild(input);
  });
  
  document.body.appendChild(form);
  form.submit();
}

/* ── Toast ───────────────────────────────────────── */
let toastTimer;
function showToast(msg) {
  clearTimeout(toastTimer);
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  toastTimer = setTimeout(()=>t.classList.remove('show'), 3200);
}
</script>
</body>
</html>
