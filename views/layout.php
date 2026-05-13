<?php
// RH and other entry points may start the session without loading Auth; navbar needs these helpers.
if (!function_exists('isLoggedIn')) {
    $authBootstrap = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Auth.php';
    if (is_file($authBootstrap)) {
        require_once $authBootstrap;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — StartSmart' : 'StartSmart — Plateforme Startup'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="forum.css">
    <link rel="stylesheet" href="evenement.css">
    <link rel="stylesheet" href="public/css/rh-integrated.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
    /* ================================================================
       LAYOUT & NAVBAR — StartSmart Design System v3
    ================================================================ */
    :root {
        --blue:       #4FC3F7;
        --blue-dark:  #0288D1;
        --blue-light: #B3E5FC;
        --blue-bg:    rgba(79,195,247,0.09);
        --green:      #2ECC71;
        --green-dark: #27AE60;
        --navy:       #0B1C48;
        --white:      #ffffff;
        --gray-50:    #F8FAFC;
        --gray-100:   #F0F4F8;
        --gray-200:   #E2E8F0;
        --gray-400:   #94A3B8;
        --gray-600:   #475569;
        --gray-800:   #1E293B;
        --radius-sm:  8px;
        --radius-md:  12px;
        --radius-lg:  18px;
        --radius-xl:  24px;
        --radius-full:999px;
        --shadow-sm:  0 2px 8px rgba(11,28,72,0.07);
        --shadow-md:  0 8px 24px rgba(11,28,72,0.10);
        --shadow-lg:  0 20px 60px rgba(11,28,72,0.14);
        --shadow-blue:0 8px 28px rgba(2,136,209,0.22);
        --font-display:'Syne', sans-serif;
        --font-body:  'DM Sans', sans-serif;
        --transition: 0.22s cubic-bezier(0.4,0,0.2,1);
        --spring:     0.35s cubic-bezier(0.34,1.56,0.64,1);
        --grad-primary: linear-gradient(135deg, var(--blue-dark) 0%, var(--blue) 100%);
        --grad-text:  linear-gradient(90deg, var(--blue-dark) 0%, var(--blue) 50%, var(--green) 100%);
        --topbar-h:   70px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
        font-family: var(--font-body);
        background: var(--gray-50);
        color: var(--gray-800);
        overflow-x: hidden;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
    }
    a { text-decoration: none; color: inherit; }

    /* ── Scroll progress ── */
    #scroll-progress {
        position: fixed;
        top: 0; left: 0;
        height: 3px;
        background: var(--grad-text);
        width: 0%;
        z-index: 9999;
        transition: width 0.1s linear;
        border-radius: 0 99px 99px 0;
        box-shadow: 0 0 10px rgba(79,195,247,0.5);
    }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: var(--gray-100); }
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, var(--blue) 0%, var(--green) 100%);
        border-radius: var(--radius-full);
    }

    /* ── Navbar ── */
    .navbar {
        position: sticky;
        top: 0;
        z-index: 200;
        height: var(--topbar-h);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 5%;
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        border-bottom: 1px solid rgba(79,195,247,0.18);
        transition: box-shadow var(--transition), background var(--transition);
    }
    .navbar.scrolled {
        box-shadow: 0 4px 30px rgba(11,28,72,0.10);
        background: rgba(255,255,255,0.96);
    }

    /* ── Logo ── */
    .logo-container { display: flex; align-items: center; }
    .logo-container a { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .logo-mark {
        width: 38px; height: 38px;
        background: var(--grad-primary);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--font-display);
        font-weight: 800; font-size: 15px; color: white;
        letter-spacing: -0.5px;
        position: relative; overflow: hidden;
        flex-shrink: 0;
    }
    .logo-mark::after {
        content: '';
        position: absolute;
        top: -50%; left: -75%;
        width: 50%; height: 200%;
        background: rgba(255,255,255,0.35);
        transform: skewX(-20deg);
        animation: logoShimmer 4s ease-in-out infinite;
    }
    @keyframes logoShimmer {
        0%,70%,100% { left:-75%; opacity:0; }
        40% { left:125%; opacity:1; }
    }
    .logo-img { height: 36px; width: auto; }
    .logo-text-name {
        font-family: var(--font-display);
        font-weight: 700; font-size: 20px;
        color: var(--navy); letter-spacing: -0.5px;
    }
    .logo-text-name span { color: var(--blue-dark); }

    /* ── Nav links ── */
    .nav-links {
        display: flex;
        align-items: center;
        gap: 4px;
        list-style: none;
    }
    .nav-btn {
        font-family: var(--font-body);
        font-size: 14px; font-weight: 500;
        color: var(--gray-600);
        padding: 7px 14px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        transition: all var(--transition);
        white-space: nowrap;
        position: relative;
    }
    .nav-btn::after {
        content: '';
        position: absolute;
        bottom: 2px; left: 14px; right: 14px;
        height: 2px;
        background: var(--grad-primary);
        border-radius: 99px;
        transform: scaleX(0);
        transition: transform var(--spring);
    }
    .nav-btn:hover { color: var(--navy); background: var(--gray-100); }
    .nav-btn:hover::after { transform: scaleX(1); }
    .nav-btn.active { color: var(--blue-dark); background: var(--blue-bg); }
    .nav-btn.active::after { transform: scaleX(1); }

    /* ── Discover / Map special links ── */
    .nav-btn.nav-discover {
        color: #e74c3c !important; font-weight: 600;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .nav-btn.nav-map {
        color: var(--blue-dark) !important; font-weight: 600;
        display: inline-flex; align-items: center; gap: 4px;
    }

    /* ── Dropdown ── */
    .dropdown { position: relative; }
    .dropdown::after { content:''; position:absolute; bottom:-10px; left:0; width:100%; height:10px; }
    .dropbtn { cursor: pointer; }
    .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        margin-top: 5px;
        left: 50%;
        transform: translateX(-50%);
        min-width: 200px;
        background: white;
        border-radius: var(--radius-md);
        border: 1px solid var(--gray-200);
        box-shadow: var(--shadow-lg);
        padding: 8px;
        z-index: 300;
        animation: dropIn 0.2s ease both;
    }
    @keyframes dropIn {
        from { opacity:0; transform: translateX(-50%) translateY(-6px); }
        to   { opacity:1; transform: translateX(-50%) translateY(0); }
    }
    .dropdown:hover .dropdown-content { display: block; }
    .dropdown-content a {
        display: block;
        padding: 9px 14px;
        font-size: 13px; font-weight: 500;
        color: var(--gray-600);
        border-radius: var(--radius-sm);
        transition: all var(--transition);
    }
    .dropdown-content a:hover { background: var(--gray-100); color: var(--navy); }

    /* ── Buttons ── */
    .btn-get-started {
        font-family: var(--font-body);
        font-size: 13px; font-weight: 600;
        color: white;
        background: var(--grad-primary);
        border: none; cursor: pointer;
        padding: 8px 18px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        box-shadow: var(--shadow-blue);
        transition: all var(--transition);
        white-space: nowrap;
        position: relative; overflow: hidden;
    }
    .btn-get-started::before {
        content: '';
        position: absolute; inset: 0;
        background: rgba(255,255,255,0.12);
        opacity: 0;
        transition: opacity var(--transition);
    }
    .btn-get-started:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(2,136,209,0.38);
    }
    .btn-get-started:hover::before { opacity: 1; }
    .btn-get-started.btn-danger-nav {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        box-shadow: 0 4px 14px rgba(231,76,60,0.28);
    }
    .btn-get-started.btn-danger-nav:hover { box-shadow: 0 8px 24px rgba(231,76,60,0.38); }

    /* ── Hamburger ── */
    .nav-hamburger {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        width: 36px; height: 36px;
        background: none;
        border: 1.5px solid var(--gray-200);
        border-radius: var(--radius-sm);
        cursor: pointer;
        padding: 7px;
        transition: all var(--transition);
    }
    .nav-hamburger span {
        display: block; height: 2px;
        background: var(--navy); border-radius: 99px;
        transition: all 0.3s ease;
    }
    .nav-hamburger:hover { border-color: var(--blue); }
    .nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .nav-hamburger.open span:nth-child(2) { opacity: 0; }
    .nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    /* ── Main content ── */
    .main-content { min-height: calc(100vh - var(--topbar-h)); }
    .container-box { max-width: 1200px; margin: 0 auto; padding: 32px 5%; }

    /* ── Footer ── */
    .site-footer {
        background: var(--gray-800);
        color: rgba(255,255,255,0.55);
        padding: 52px 5% 28px;
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 40px;
        margin-bottom: 36px;
    }
    .footer-brand .logo-text-name { color: white; }
    .footer-desc { font-size: 13px; line-height: 1.7; margin-top: 12px; }
    .footer-col h4 {
        font-size: 11px; font-weight: 700;
        color: rgba(255,255,255,0.85);
        text-transform: uppercase; letter-spacing: 1.2px;
        margin-bottom: 14px;
    }
    .footer-col ul { list-style: none; }
    .footer-col ul li { margin-bottom: 8px; }
    .footer-col ul a { font-size: 13px; color: rgba(255,255,255,0.45); transition: color var(--transition); }
    .footer-col ul a:hover { color: var(--blue-light); }
    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.08);
        padding-top: 22px;
        display: flex; justify-content: space-between; align-items: center;
        font-size: 12px;
    }
    .social-links { display: flex; gap: 10px; }
    .social-link {
        width: 32px; height: 32px;
        border-radius: var(--radius-sm);
        background: rgba(255,255,255,0.06);
        display: flex; align-items: center; justify-content: center;
        color: rgba(255,255,255,0.45);
        font-size: 14px;
        transition: all var(--transition);
    }
    .social-link:hover { background: rgba(79,195,247,0.2); color: var(--blue-light); }

    /* ── Mobile ── */
    @media (max-width: 768px) {
        .nav-links { display: none; }
        .nav-links.nav-open {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: absolute;
            top: var(--topbar-h); left: 0; right: 0;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            padding: 16px 5% 20px;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: 0 12px 40px rgba(11,28,72,0.12);
            z-index: 199;
            animation: slideDown 0.25s ease both;
        }
        @keyframes slideDown {
            from { opacity:0; transform: translateY(-8px); }
            to   { opacity:1; transform: translateY(0); }
        }
        .nav-hamburger { display: flex; }
        .footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; }
        .footer-bottom { flex-direction: column; gap: 14px; }
    }
    @media (max-width: 480px) {
        .footer-grid { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body class="<?php echo !empty($hideNavbar) ? 'rh-smart-standalone' : ''; ?>">
<?php if (empty($hideNavbar)): ?>
<div id="scroll-progress"></div>

<!-- ========== NAVBAR ========== -->
<header class="navbar" id="navbar">
    <div class="logo-container">
        <a href="index.php" style="display:flex; align-items:center;">
            <img src="logo.svg?v=<?php echo time(); ?>" alt="StartSmart" class="logo-img" style="height: 42px;">
        </a>
    </div>

    <nav class="nav-links" id="nav-links">
        <a href="index.php" class="nav-btn <?php echo (!isset($_GET['controller']) || $_GET['controller']==='home') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house" style="font-size:13px;"></i> Accueil
        </a>
        <a href="index.php?controller=projet&action=index" class="nav-btn <?php echo (isset($_GET['controller']) && $_GET['controller']==='projet') ? 'active' : ''; ?>">
            Startups
        </a>
        <div class="dropdown">
            <a href="#" class="nav-btn dropbtn nav-discover <?php echo (isset($_GET['controller']) && $_GET['controller']==='matching') ? 'active' : ''; ?>">
                Match <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
            </a>
            <div class="dropdown-content">
                <a href="index.php?controller=matching&action=discover">
                    <i class="fa-solid fa-fire" style="margin-right:8px;color:#e74c3c;"></i>Découvrir (Swipe)
                </a>
                <a href="index.php?controller=matching&action=recommend">
                    <i class="fa-solid fa-list" style="margin-right:8px;color:var(--blue-dark);"></i>Vue Liste
                </a>
                <a href="index.php?controller=matching&action=candidatures">
                    <i class="fa-solid fa-users" style="margin-right:8px;color:var(--green-dark);"></i>Candidatures
                </a>
            </div>
        </div>
        <a href="index.php?controller=map&action=index" class="nav-btn nav-map">
            <i class="fa-solid fa-globe" style="font-size:13px;"></i> Map
        </a>
        <a href="index.php?url=evenement/index" class="nav-btn <?php echo (isset($_GET['url']) && strpos($_GET['url'], 'evenement') === 0) || (isset($_GET['controller']) && $_GET['controller'] === 'evenement') ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-days" style="font-size:13px;"></i> Evenements
        </a>
        <div class="dropdown">
            <a href="index.php?controller=post&action=index" class="nav-btn dropbtn <?php echo (isset($_GET['controller']) && $_GET['controller']==='post') ? 'active' : ''; ?>">
                Forum <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
            </a>
            <div class="dropdown-content">
                <a href="index.php?controller=post&action=index">
                    <i class="fa-regular fa-comments" style="margin-right:8px;color:var(--blue-dark);"></i>Discussions
                </a>
            </div>
        </div>
        <?php
            // "RH" = module recrutement (offres / candidatures), pas la console admin (défaut section=users).
            $rhHref = 'login.php';
            if (function_exists('isLoggedIn') && isLoggedIn()) {
                $role = function_exists('normalizedRole') ? normalizedRole($_SESSION['user_role'] ?? null) : strtolower(trim((string)($_SESSION['user_role'] ?? '')));
                if (in_array($role, ['admin', 'rh'], true)) {
                    $rhHref = 'rh.php?page=job-offer/index&action=index';
                } elseif ($role === 'startup') {
                    $rhHref = 'rh.php?page=backend/dashboard';
                } else {
                    $rhHref = 'rh.php?page=job-offer/index&action=index';
                }
            }
        ?>
        <a href="<?php echo htmlspecialchars($rhHref); ?>" class="nav-btn"><i class="fa-solid fa-briefcase"></i> RH</a>
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <a href="rh.php?page=backend/admin" class="nav-btn" style="color: var(--blue-dark); font-weight: 700;">
                <i class="fa-solid fa-gauge-high"></i> Admin Backend
            </a>
        <?php endif; ?>

        <?php if(function_exists('isLoggedIn') && isLoggedIn()): ?>
            <a href="index.php?controller=message&action=index" class="nav-btn" style="color: var(--navy); font-weight: 600;">
                <i class="fa-regular fa-message"></i> Messagerie
            </a>
            <?php if (file_exists(__DIR__ . '/partials/notifications.php')) include __DIR__ . '/partials/notifications.php'; ?>
            <a href="index.php?controller=profile&action=index" class="btn-get-started" style="margin-left:6px;">
                <i class="fa-regular fa-user"></i> Mon Profil
            </a>
            <a href="logout.php" class="btn-get-started btn-danger-nav">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnecter
            </a>
        <?php else: ?>
            <a href="login.php" class="btn-get-started" style="margin-left:6px;">
                <i class="fa-solid fa-right-to-bracket"></i> Se Connecter
            </a>
        <?php endif; ?>
    </nav>

    <button class="nav-hamburger" id="nav-hamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</header>
<?php endif; ?>

<!-- ========== MAIN CONTENT ========== -->
<main class="main-content <?php echo (isset($controller) && $controller !== 'home') ? 'container-box' : ''; ?>">
    <?php if (isset($viewContent)) echo $viewContent; ?>
</main>

<!-- ========== FOOTER ========== -->
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="logo-mark" style="width:34px;height:34px;font-size:13px;">SS</div>
                <span class="logo-text-name" style="color: white !important;">Start<span>Smart</span></span>
            </div>
            <p class="footer-desc">La plateforme web dédiée à l'entrepreneuriat digital. Proposez des idées, collaborez et trouvez des financements.</p>
        </div>
        <div class="footer-col">
            <h4>Plateforme</h4>
            <ul>
                <li><a href="index.php?controller=projet&action=index">Projets</a></li>
                <li><a href="index.php?controller=post&action=index">Forum</a></li>
                <li><a href="index.php?controller=matching&action=discover">Découvrir</a></li>
                <li><a href="index.php?controller=map&action=index">Carte Startups</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Ressources</h4>
            <ul>
                <li><a href="#">Documentation</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Tutoriels</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Entreprise</h4>
            <ul>
                <li><a href="#">À propos</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Confidentialité</a></li>
                <li><a href="login.php">Connexion</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2026 StartSmart. Tous droits réservés.</span>
        <div class="social-links">
            <a href="#" class="social-link"><i class="fa-brands fa-linkedin-in"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-github"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-twitter"></i></a>
        </div>
    </div>
</footer>

<script src="forum.js?v=<?php echo time(); ?>"></script>
<script src="speech.js?v=<?php echo time(); ?>"></script>
<?php if (empty($hideNavbar)): ?>
<script>
// Scroll progress
const progressBar = document.getElementById('scroll-progress');
window.addEventListener('scroll', () => {
    const pct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
    if (progressBar) progressBar.style.width = pct + '%';
    const navBarEl = document.getElementById('navbar');
    if (navBarEl) navBarEl.classList.toggle('scrolled', window.scrollY > 20);
}, { passive: true });

// Hamburger
const hamburger = document.getElementById('nav-hamburger');
const navLinks  = document.getElementById('nav-links');
if (hamburger) {
    hamburger.addEventListener('click', () => {
        const open = hamburger.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', open);
        if (navLinks) navLinks.classList.toggle('nav-open', open);
    });
    if (navLinks) {
        navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
            hamburger.classList.remove('open');
            navLinks.classList.remove('nav-open');
        }));
    }
}
</script>
<?php endif; ?>

<!-- Lucide Icons initialization -->
<script src="lucide.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
</body>
</html>
