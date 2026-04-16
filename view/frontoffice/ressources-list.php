<?php
// view/frontoffice/ressources-list.php - Liste des ressources disponibles (FrontOffice)
?>

<div class="page-header">
    <h1>Ressources Disponibles</h1>
    <p>Consultez les ressources offertes par nos sponsors et déposez une demande d'accès</p>
</div>

<?php if (!empty($ressources)): ?>
    <div class="row">
        <?php foreach ($ressources as $ressource): ?>
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($ressource['nom_ressource']); ?></h3>
                    </div>
                    
                    <div class="mb-20">
                        <p><strong>Sponsor:</strong> <?php echo htmlspecialchars($ressource['nom_sponsor']); ?></p>
                        <p><strong>Type:</strong> 
                            <span class="badge badge-info"><?php echo htmlspecialchars($ressource['type_ressource']); ?></span>
                        </p>
                        <p><strong>Disponibilité:</strong> 
                            <?php 
                            $reste = $ressource['quantite_disponible'] - $ressource['quantite_utilisee'];
                            echo $reste . ' / ' . $ressource['quantite_disponible'];
                            ?>
                        </p>
                        <p><strong>Description:</strong></p>
                        <p><?php echo htmlspecialchars($ressource['description']); ?></p>
                    </div>
                    
                    <div class="btn-group">
                        <a href="index.php?page=demande-detail&id=<?php echo $ressource['id_ressource']; ?>" class="btn btn-info btn-small">
                            Voir détails
                        </a>
                        <a href="index.php?page=demande-create&ressource=<?php echo $ressource['id_ressource']; ?>" class="btn btn-primary btn-small">
                            Demander l'accès
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="no-data">
            <p>Aucune ressource disponible pour le moment.</p>
        </div>
    </div>
<?php endif; ?>
