<?php
// view/backoffice/ressource-list.php - Liste des ressources (BackOffice)
?>

<div class="page-header">
    <h1>Gestion des Ressources</h1>
    <p>Administrez toutes les ressources offertes par les sponsors</p>
</div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Ressources</h2>
            <a href="index.php?page=ressource-create" class="btn btn-primary btn-small">+ Nouvelle Ressource</a>
        </div>
    </div>

    <?php if (!empty($ressources)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Sponsor</th>
                    <th>Type</th>
                    <th>Disponible</th>
                    <th>Utilisée</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ressources as $ressource): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ressource['nom_ressource']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ressource['nom_sponsor']); ?></td>
                        <td><?php echo htmlspecialchars($ressource['type_ressource']); ?></td>
                        <td><?php echo $ressource['quantite_disponible']; ?></td>
                        <td><?php echo $ressource['quantite_utilisee']; ?></td>
                        <td>
                            <?php
                            $statut = $ressource['statut'];
                            $badgeClass = '';
                            $badgeLabel = '';
                            
                            switch ($statut) {
                                case 'disponible':
                                    $badgeClass = 'badge-success';
                                    $badgeLabel = 'Disponible';
                                    break;
                                case 'indisponible':
                                    $badgeClass = 'badge-warning';
                                    $badgeLabel = 'Indisponible';
                                    break;
                                case 'archive':
                                    $badgeClass = 'badge-danger';
                                    $badgeLabel = 'Archivée';
                                    break;
                                default:
                                    $badgeClass = 'badge-pending';
                                    $badgeLabel = ucfirst($statut);
                            }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo $badgeLabel; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="index.php?page=ressource-edit&id=<?php echo $ressource['id_ressource']; ?>" class="btn btn-info btn-small">
                                    Modifier
                                </a>
                                <a href="index.php?page=ressource-delete&id=<?php echo $ressource['id_ressource']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Êtes-vous sûr?')">
                                    Supprimer
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <p>Aucune ressource enregistrée.</p>
            <a href="index.php?page=ressource-create" class="btn btn-primary mt-20">Créer une Ressource</a>
        </div>
    <?php endif; ?>
</div>
