<?php
// view/backoffice/sponsor-list.php - Liste des sponsors (BackOffice)
?>

<div class="page-header">
    <h1>Gestion des Sponsors</h1>
    <p>Administrez tous les sponsors et leurs ressources</p>
</div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Sponsors</h2>
            <a href="index.php?controller=resource&action=sponsorCreate" class="btn btn-primary btn-small">+ Nouveau Sponsor</a>
        </div>
    </div>

    <?php if (!empty($sponsors)): ?>
        <div style="padding: 20px; font-size: 14px; color: #666; border-bottom: 1px solid #e0e0e0;">
            <?php echo count($sponsors); ?> sponsor(s) trouvé(s)
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Type de Ressources</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sponsors as $sponsor): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($sponsor['nom_sponsor']); ?></strong></td>
                        <td><?php echo htmlspecialchars($sponsor['email_sponsor']); ?></td>
                        <td><?php echo htmlspecialchars($sponsor['telephone'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($sponsor['type_ressources'] ?? '-'); ?></td>
                        <td>
                            <?php
                            $statut = $sponsor['statut'];
                            $badgeClass = '';
                            $badgeLabel = '';
                            
                            switch ($statut) {
                                case 'actif':
                                    $badgeClass = 'badge-success';
                                    $badgeLabel = 'Actif';
                                    break;
                                case 'inactif':
                                    $badgeClass = 'badge-warning';
                                    $badgeLabel = 'Inactif';
                                    break;
                                case 'suspendu':
                                    $badgeClass = 'badge-danger';
                                    $badgeLabel = 'Suspendu';
                                    break;
                                default:
                                    $badgeClass = 'badge-pending';
                                    $badgeLabel = htmlspecialchars($statut);
                            }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
                        </td>
                        <td>
                            <div class="btn-group" style="gap: 5px;">
                                <a href="index.php?controller=resource&action=sponsorEdit&id=<?php echo $sponsor['id_sponsor']; ?>" class="btn btn-info btn-small">Modifier</a>
                                <a href="index.php?controller=resource&action=sponsorDelete&id=<?php echo $sponsor['id_sponsor']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Êtes-vous sûr?');">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <h3>Aucun sponsor trouvé</h3>
            <p>Commencez par créer un nouveau sponsor</p>
            <a href="index.php?controller=resource&action=sponsorCreate" class="btn btn-primary">+ Créer un Sponsor</a>
        </div>
    <?php endif; ?>
</div>
