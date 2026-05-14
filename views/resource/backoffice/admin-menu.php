<?php $resourceAction = $_GET['action'] ?? ''; ?>
<div class="admin-menu">
    <a href="index.php?controller=resource&action=resourceList" class="<?php echo strpos($resourceAction, 'resource') === 0 ? 'is-active' : ''; ?>">
        <i class="fa-solid fa-boxes-stacked"></i> Ressources
    </a>
    <a href="index.php?controller=resource&action=sponsorList" class="<?php echo strpos($resourceAction, 'sponsor') === 0 ? 'is-active' : ''; ?>">
        <i class="fa-solid fa-handshake"></i> Sponsors
    </a>
    <a href="index.php?controller=resource&action=demandeList" class="<?php echo strpos($resourceAction, 'demande') === 0 ? 'is-active' : ''; ?>">
        <i class="fa-regular fa-clipboard"></i> Demandes
    </a>
</div>
