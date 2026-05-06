<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'StartSmart - Plateforme Startup'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="forum.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jsPDF and AutoTable for PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body, html { font-family: 'Roboto', sans-serif !important; }
        .lucide { vertical-align: middle; }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo-container">
            <a href="index.php"><img src="logo.png" alt="Logo StartSmart" class="logo-img"></a>
        </div>
        
        <nav class="nav-links">
            <a href="index.php?controller=projet&action=index" class="nav-btn">Startups</a>
            <a href="index.php?controller=match&action=index" class="nav-btn" style="color: #e74c3c; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">Découvrir <i data-lucide="flame" style="width:18px;height:18px;"></i></a>
            <a href="index.php?controller=map&action=index" class="nav-btn" style="color: #3498db; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="globe" style="width:18px;height:18px;"></i> Map</a>
            <div class="dropdown">
                <a href="index.php?controller=post&action=index" class="nav-btn dropbtn">Forum ▾</a>
                <div class="dropdown-content">
                    <a href="index.php?controller=post&action=index">Discussions</a>
                    
                    <?php if (function_exists('isAdmin') && isAdmin()): ?>
                    <div style="border-top: 1px solid #eee; margin-top: 5px; padding-top: 5px;">
                        <span style="display:block; padding: 8px 16px; font-size: 0.8em; color: #7f8c8d; font-weight: bold; text-transform: uppercase;">Administration</span>
                        <a href="index.php?controller=post&action=admin" style="padding-top: 5px; padding-bottom: 5px;">Modération Forum</a>
                        <a href="index.php?controller=projet&action=index" style="padding-top: 5px; padding-bottom: 5px;">Gérer Projets</a>
                        <a href="index.php?controller=categorie&action=index" style="padding-top: 5px; padding-bottom: 5px;">Gérer Catégories</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if(function_exists('isLoggedIn') && isLoggedIn()): ?>
                <?php if (file_exists(__DIR__ . '/partials/notifications.php')) include __DIR__ . '/partials/notifications.php'; ?>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="index.php?controller=profile&action=index" class="btn-get-started" style="background-color: #3498db; border-color: #2980b9;">MON PROFIL</a>
                    <a href="logout.php" class="btn-get-started" style="background-color: #e74c3c; border-color: #c0392b;">DÉCONNECTER</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-get-started">SE CONNECTER</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="main-content <?php echo ($controller != 'home') ? 'container-box' : ''; ?>">
        <?php 
        if (isset($viewContent)) {
            echo $viewContent;
        }
        ?>
    </div>
    </div>
    <script src="forum.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
