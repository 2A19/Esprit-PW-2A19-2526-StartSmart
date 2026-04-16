<?php
// view/frontoffice/demandes-list.php - Liste des demandes d'accès de l'utilisateur (FrontOffice)
?>

<div class="page-header">
    <h1>Mes Demandes d'Accès</h1>
    <p>Consultez l'état de vos demandes d'accès aux ressources</p>
</div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Historique des Demandes</h2>
            <a href="index.php?page=demande-create" class="btn btn-primary btn-small">+ Nouvelle Demande</a>
        </div>
    </div>

    <?php if (!empty($demandes)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Ressource</th>
                    <th>Sponsor</th>
                    <th>Quantité</th>
                    <th>Date Demande</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demandes as $demande): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($demande['nom_ressource']); ?></strong></td>
                        <td><?php echo htmlspecialchars($demande['nom_sponsor']); ?></td>
                        <td><?php echo $demande['quantite_demandee']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($demande['date_demande'])); ?></td>
                        <td>
                            <?php
                            $statut = $demande['statut_demande'];
                            $badgeClass = '';
                            $badgeLabel = '';
                            
                            switch ($statut) {
                                case 'en_attente':
                                    $badgeClass = 'badge-warning';
                                    $badgeLabel = 'En Attente';
                                    break;
                                case 'acceptee':
                                    $badgeClass = 'badge-success';
                                    $badgeLabel = 'Acceptée';
                                    break;
                                case 'refusee':
                                    $badgeClass = 'badge-danger';
                                    $badgeLabel = 'Refusée';
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
                            <a href="index.php?page=demande-detail&id=<?php echo $demande['id_demande']; ?>" class="btn btn-info btn-small">
                                Détails
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <p>Vous n'avez pas encore soumis de demande d'accès.</p>
            <a href="index.php?page=demande-create" class="btn btn-primary mt-20">Créer une Demande</a>
        </div>
    <?php endif; ?>
</div>
