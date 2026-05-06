<?php
/** @var string $pageTitle */
/** @var string $activeNav */
/** @var string $basePath */
/** @var array<int, string> $extraCssFiles */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'StartSmart', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/css/style.css">
    <?php foreach (($extraCssFiles ?? []) as $extraCssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/<?= ltrim($extraCssFile, '/'); ?>">
    <?php endforeach; ?>
</head>
<body>
<header class="site-header">
    <div class="nav-wrap container">
        <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=home/index" class="brand">
            <!-- Vous pouvez utiliser la balise img avec votre logo ici si vous l'avez uploadé dans le dossier public/img -->
            <!-- <img src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/img/mon-logo.png" alt="SmartSmart" class="brand-logo"> -->
            <div class="brand-icon">
                <i class="fa-solid fa-lightbulb" style="color: #6fb236;"></i>
                <i class="fa-solid fa-rocket" style="color: #006eb5; font-size: 10px; margin-left:-8px; margin-top:-10px"></i>
            </div>
            <div class="brand-text">
                <div class="brand-name"><span class="t-start">Smart</span><span class="t-smart">Smart</span></div>
            </div>
        </a>

        <button class="menu-toggle" type="button" data-menu-toggle aria-label="Ouvrir le menu">
            <span></span><span></span><span></span>
        </button>

        <div class="nav-menu-container" data-main-nav>
            <nav class="main-nav">
                <a class="<?= ($activeNav ?? '') === 'accueil' ? 'active' : ''; ?>" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=home/index">Home</a>
                <a href="#">Ressources humaines</a>
                <a href="#">Projets</a>
                <a class="<?= ($activeNav ?? '') === 'evenements' ? 'active' : ''; ?>" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/index">Événements</a>
                <a href="#">Ressources et formations</a>
            </nav>
            <div class="nav-actions">
                <a href="#" class="btn-login">Sign In</a>
                <a href="#" class="btn-signup">Sign Up</a>
            </div>
        </div>
    </div>
</header>
<main>
