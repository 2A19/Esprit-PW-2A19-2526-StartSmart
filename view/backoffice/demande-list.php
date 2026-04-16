<?php
// view/backoffice/demande-list.php - Liste des demandes d'accès (BackOffice)
?>

<div class="page-header">
    <h1>Gestion des Demandes d'Accès</h1>
    <p>Acceptez ou refusez les demandes d'accès des utilisateurs aux ressources</p>
</div>

<div class="card">
    <div class="card-header">
        <h2>Demandes en Attente</h2>
    </div>

    <?php
    $demandesEnAttente = array_filter($demandes, function($d) {
        return $d['statut_demande'] === 'en_attente';
    });
    ?>

    <?php if (!empty($demandesEnAttente)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Entreprise</th>
                    <th>Ressource</th>
                    <th>Quantité</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demandesEnAttente as $demande): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($demande['nom_utilisateur']); ?></strong><br>
                            <small><?php echo htmlspecialchars($demande['email_utilisateur']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($demande['entreprise']); ?></td>
                        <td><?php echo htmlspecialchars($demande['nom_ressource']); ?></td>
                        <td><?php echo $demande['quantite_demandee']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($demande['date_demande'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="index.php?page=demande-detail&id=<?php echo $demande['id_demande']; ?>" class="btn btn-info btn-small">
                                    Détails
                                </a>
                                <a href="index.php?page=demande-accepter&id=<?php echo $demande['id_demande']; ?>" class="btn btn-success btn-small" onclick="return confirm('Accepter cette demande?')">
                                    Accepter
                                </a>
                                <a href="index.php?page=demande-refuser&id=<?php echo $demande['id_demande']; ?>" class="btn btn-danger btn-small">
                                    Refuser
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <p>Aucune demande en attente.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Demandes Acceptées -->
<div class="card">
    <div class="card-header">
        <h2>Demandes Acceptées</h2>
    </div>

    <?php
    $demandesAcceptees = array_filter($demandes, function($d) {
        return $d['statut_demande'] === 'acceptee';
    });
    ?>

    <?php if (!empty($demandesAcceptees)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Ressource</th>
                    <th>Quantité</th>
                    <th>Fin d'Accès</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demandesAcceptees as $demande): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($demande['nom_utilisateur']); ?></td>
                        <td><?php echo htmlspecialchars($demande['nom_ressource']); ?></td>
                        <td><?php echo $demande['quantite_demandee']; ?></td>
                        <td><?php echo $demande['date_fin_acces'] ? date('d/m/Y', strtotime($demande['date_fin_acces'])) : '-'; ?></td>
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
            <p>Aucune demande acceptée.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Demandes Refusées -->
<div class="card">
    <div class="card-header">
        <h2>Demandes Refusées</h2>
    </div>

    <?php
    $demandesRefusees = array_filter($demandes, function($d) {
        return $d['statut_demande'] === 'refusee';
    });
    ?>

    <?php if (!empty($demandesRefusees)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Ressource</th>
                    <th>Raison</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demandesRefusees as $demande): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($demande['nom_utilisateur']); ?></td>
                        <td><?php echo htmlspecialchars($demande['nom_ressource']); ?></td>
                        <td><?php echo htmlspecialchars($demande['raison_refus']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($demande['date_reponse'])); ?></td>
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
            <p>Aucune demande refusée.</p>
        </div>
    <?php endif; ?>
</div>
