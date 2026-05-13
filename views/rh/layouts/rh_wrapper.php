<?php
/**
 * RH chrome: vertical sidebar (no Forum / Projets in this nav).
 * @var string $innerContent
 */
$role = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
$isStartup = ($role === 'startup');
$rhPage = (string)($_GET['page'] ?? '');
$isPlatformAdmin = in_array($role, ['admin', 'rh'], true);

$rhHas = static function (string $haystack, string $needle): bool {
    return $needle !== '' && strpos($haystack, $needle) !== false;
};

$items = [];
if ($isStartup) {
    $items[] = [
        'href' => 'rh.php?page=backend/dashboard',
        'label' => 'Dashboard',
        'icon' => 'fa-chart-line',
        'active' => $rhPage === 'backend/dashboard',
    ];
    $items[] = [
        'href' => 'rh.php?page=job-offer/myOffers&action=myOffers',
        'label' => 'Offres',
        'icon' => 'fa-briefcase',
        'active' => $rhHas($rhPage, 'myOffers'),
    ];
    $items[] = [
        'href' => 'rh.php?page=employee/index',
        'label' => 'Employés',
        'icon' => 'fa-users-gear',
        'active' => strncmp($rhPage, 'employee', 8) === 0,
    ];
} else {
    $items[] = [
        'href' => 'rh.php?page=frontend/home',
        'label' => 'Accueil RH',
        'icon' => 'fa-house',
        'active' => strncmp($rhPage, 'frontend', 8) === 0,
    ];
    $items[] = [
        'href' => 'rh.php?page=job-offer/index&action=index',
        'label' => 'Offres',
        'icon' => 'fa-briefcase',
        'active' => strncmp($rhPage, 'job-offer', 9) === 0 && !$rhHas($rhPage, 'myOffers'),
    ];
    $items[] = [
        'href' => 'rh.php?page=application/myApplications&action=myApplications',
        'label' => 'Candidatures',
        'icon' => 'fa-file-lines',
        'active' => strncmp($rhPage, 'application', 11) === 0,
    ];
    $items[] = [
        'href' => 'rh.php?page=user/profile',
        'label' => 'Profil',
        'icon' => 'fa-user',
        'active' => strncmp($rhPage, 'user', 4) === 0,
    ];
}

if ($isPlatformAdmin) {
    $items[] = [
        'href' => 'rh.php?page=backend/admin',
        'label' => 'Console admin',
        'icon' => 'fa-gauge-high',
        'active' => $rhHas($rhPage, 'admin'),
        'admin' => true,
    ];
}
?>
<div class="rh-shell">
    <aside class="rh-side-nav" aria-label="Navigation RH">
        <a class="rh-side-brand" href="<?php echo $isStartup ? 'rh.php?page=backend/dashboard' : 'rh.php?page=frontend/home'; ?>">
            Start<span class="rh-side-brand-accent">Smart</span>
        </a>

        <nav class="rh-module-links">
            <?php foreach ($items as $it): ?>
                <a href="<?php echo htmlspecialchars($it['href']); ?>"
                   class="rh-nav-link<?php echo !empty($it['active']) ? ' is-active' : ''; ?><?php echo !empty($it['admin']) ? ' rh-nav-link-admin' : ''; ?>">
                    <i class="fa-solid <?php echo htmlspecialchars($it['icon']); ?>" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($it['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="rh-side-footer">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <div class="rh-side-user-name" title="<?php echo htmlspecialchars((string)($_SESSION['user_name'] ?? '')); ?>">
                    <?php echo htmlspecialchars((string)($_SESSION['user_name'] ?? 'Utilisateur')); ?>
                </div>
                <a href="index.php" class="rh-nav-link rh-nav-link-compact"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Portail</span></a>
                <a href="logout.php" class="rh-nav-link rh-nav-link-compact rh-nav-link-danger"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Quitter</span></a>
            <?php else: ?>
                <a href="login.php" class="rh-nav-link rh-nav-link-compact"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i><span>Connexion</span></a>
                <a href="login.php?tab=register" class="rh-nav-link rh-nav-link-compact"><i class="fa-solid fa-user-plus" aria-hidden="true"></i><span>Inscription</span></a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="rh-main-col">
        <div class="rh-content">
            <?php echo $innerContent ?? ''; ?>
        </div>
    </div>
</div>
