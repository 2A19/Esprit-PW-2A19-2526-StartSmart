<!-- Admin Menu/Navigation -->
<div style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap;">
    <a href="index.php?page=ressource-list" class="btn <?php echo (isset($_GET['page']) && strpos($_GET['page'], 'ressource') !== false) ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 10px 20px;">
        📋 Ressources
    </a>
    <a href="index.php?page=sponsor-list" class="btn <?php echo (isset($_GET['page']) && strpos($_GET['page'], 'sponsor') !== false) ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 10px 20px;">
        🤝 Sponsors
    </a>
    <a href="index.php?page=demande-list" class="btn <?php echo (isset($_GET['page']) && strpos($_GET['page'], 'demande') !== false) ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 10px 20px;">
        ✋ Demandes
    </a>
</div>
