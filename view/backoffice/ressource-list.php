<?php
// view/backoffice/ressource-list.php - Liste des ressources (BackOffice)

// Récupérer les paramètres de recherche et de tri
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date';
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

    <!-- Barre de recherche et tri -->
    <div style="padding: 20px; border-bottom: 1px solid #e0e0e0;">
        <form method="GET" action="index.php" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="page" value="ressource-list">
            
            <div style="flex: 1; min-width: 250px;">
                <label for="search" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    Rechercher par nom:
                </label>
                <input 
                    type="text" 
                    id="search" 
                    name="search" 
                    placeholder="Entrez le nom de la ressource..." 
                    value="<?php echo $search; ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div>
                <label for="sort" style="display: block; margin-bottom: 5px; font-weight: 500;">
                    Trier par:
                </label>
                <select 
                    id="sort" 
                    name="sort" 
                    style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 150px;"
                    onchange="this.form.submit();">
                    <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Date (Récent)</option>
                    <option value="statut" <?php echo $sortBy === 'statut' ? 'selected' : ''; ?>>Statut</option>
                    <option value="lettre" <?php echo $sortBy === 'lettre' ? 'selected' : ''; ?>>Première lettre (A-Z)</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-info" style="padding: 8px 15px;">Rechercher</button>
            
            <?php if (!empty($search) || $sortBy !== 'date'): ?>
                <a href="index.php?page=ressource-list" class="btn btn-secondary" style="padding: 8px 15px;">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!empty($ressources)): ?>
        <div style="padding: 20px; font-size: 14px; color: #666; border-bottom: 1px solid #e0e0e0;">
            <?php echo count($ressources); ?> ressource(s) trouvée(s)
        </div>
        
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
            <p><?php echo !empty($search) ? 'Aucune ressource trouvée avec ce terme de recherche.' : 'Aucune ressource enregistrée.'; ?></p>
            <?php if (empty($search) && $sortBy === 'date'): ?>
                <a href="index.php?page=ressource-create" class="btn btn-primary mt-20">Créer une Ressource</a>
            <?php else: ?>
                <a href="index.php?page=ressource-list" class="btn btn-secondary mt-20">Afficher toutes les ressources</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
