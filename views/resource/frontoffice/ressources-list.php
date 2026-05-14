<?php
// view/frontoffice/ressources-list.php - Liste des ressources disponibles (FrontOffice)
?>

<div class="page-header">
    <h1>Ressources Disponibles</h1>
    <p>Consultez les ressources offertes par nos sponsors et déposez une demande d'accès</p>
</div>

<?php if (!empty($ressources)): ?>
    <!-- Statistics Section -->
    <div class="stats-container">
        <div class="stats-card">
            <h2>Aperçu des Ressources par Type</h2>
            <div class="stats-content">
                <div class="stats-chart">
                    <canvas id="ressourcesChart"></canvas>
                </div>
                <div class="stats-info">
                    <?php
                    // Count resources by type
                    $types_count = [];
                    $colors = [
                        '#7dd442', '#2c4a8d', '#1d2f5a', '#17a2b8', '#ffc107', 
                        '#28a745', '#dc3545', '#6f42c1', '#e83e8c', '#fd7e14'
                    ];
                    
                    foreach ($ressources as $r) {
                        $type = $r['type_ressource'];
                        if (!isset($types_count[$type])) {
                            $types_count[$type] = 0;
                        }
                        $types_count[$type]++;
                    }
                    
                    $total_ressources = count($ressources);
                    ?>
                    <div class="stat-item">
                        <span class="stat-label">Total de Ressources</span>
                        <span class="stat-value"><?php echo $total_ressources; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Types Différents</span>
                        <span class="stat-value"><?php echo count($types_count); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('ressourcesChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [<?php 
                        foreach ($types_count as $type => $count) {
                            echo "'" . htmlspecialchars($type) . "', ";
                        }
                    ?>],
                    datasets: [{
                        data: [<?php 
                            foreach ($types_count as $type => $count) {
                                echo $count . ", ";
                            }
                        ?>],
                        backgroundColor: [
                            '<?php echo implode("', '", array_slice($colors, 0, count($types_count))); ?>'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 13
                                },
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        });
    </script>
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
                        <a href="index.php?controller=resource&action=resourceShow&id=<?php echo $ressource['id_ressource']; ?>" class="btn btn-info btn-small">
                            Voir détails
                        </a>
                        <a href="index.php?controller=resource&action=demandeCreate&ressource=<?php echo $ressource['id_ressource']; ?>" class="btn btn-primary btn-small">
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
