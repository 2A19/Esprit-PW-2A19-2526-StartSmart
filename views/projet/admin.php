<div class="bo-header" style="border-bottom: 2px solid #f0f2f5; padding-bottom: 15px; margin-bottom: 20px;">
    <h2>Modération des Projets</h2>
    <div style="display:flex; gap:10px;">
        <a href="index.php?controller=projet&action=index" class="btn-secondary">Voir le Catalogue Public</a>
        <a href="index.php?controller=projet&action=create" class="btn-primary" style="background-color: #3498db;">+ Nouveau Projet</a>
    </div>
</div>

<div class="search-form" style="background: #f9fbfd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e1e8ed;">
    <form action="index.php" method="GET" style="display:flex; gap:10px; width: 100%;">
        <input type="hidden" name="controller" value="projet">
        <input type="hidden" name="action" value="admin">
        <input type="text" name="search" class="search-input" placeholder="Rechercher un projet par nom ou description..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="flex:1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="submit" class="btn-primary">Filtrer</button>
    </form>
</div>

<div style="overflow-x: auto;">
    <table class="bo-table" id="adminProjetsTable" style="width: 100%; border-collapse: collapse; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Startup / Projet</th>
                <th style="padding: 12px; text-align: left;">Auteur</th>
                <th style="padding: 12px; text-align: center;">Catégorie</th>
                <th style="padding: 12px; text-align: center;">Budget</th>
                <th style="padding: 12px; text-align: right;">Interactions</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projets as $row): ?>
            <tr style="border-bottom: 1px solid #eee; transition: background-color 0.2s;">
                <td style="padding: 12px; color: #7f8c8d;">#<?php echo $row['id']; ?></td>
                <td style="padding: 12px;">
                    <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: bold; color: #2c3e50;" title="<?php echo htmlspecialchars($row['nomprojet']); ?>">
                        <a href="index.php?controller=projet&action=show&id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($row['nomprojet']); ?>
                        </a>
                    </div>
                    <div style="font-size: 0.85em; color: #95a5a6; margin-top: 4px;">
                        Créé le <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                    </div>
                </td>
                <td style="padding: 12px;">
                    <span style="background: #e8f4f8; color: #2980b9; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        <?php echo htmlspecialchars($row['auteur_nom'] ?? 'Utilisateur'); ?>
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span style="background: #f4f6f7; color: #34495e; padding: 3px 8px; border-radius: 4px; border: 1px solid #bdc3c7; font-size: 0.85em;">
                        <?php echo htmlspecialchars($row['categorie_nom'] ?? 'Général'); ?>
                    </span>
                </td>
                <td style="padding: 12px; text-align: center; font-weight: bold; color: #27ae60;">
                    <?php echo number_format($row['budget'], 0, ',', ' '); ?> €
                </td>
                <td style="padding: 12px; text-align: right;">
                    <div style="font-size: 0.9em; color: #7f8c8d;">
                        <span title="Commentaires" style="margin-right:8px;">💬 <?php echo (int)$row['commentaires_count']; ?></span>
                        <span title="Likes" style="margin-right:8px; color: #27ae60;">👍 <?php echo (int)$row['likes_count']; ?></span>
                        <span title="Dislikes" style="color: #e74c3c;">👎 <?php echo (int)$row['dislikes_count']; ?></span>
                    </div>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <a href="index.php?controller=projet&action=edit&id=<?php echo $row['id']; ?>" class="btn-edit" style="margin-right: 5px; padding: 5px 10px; font-size: 0.85em;">Modifier</a>
                    <a href="index.php?controller=projet&action=delete&id=<?php echo $row['id']; ?>" class="btn-danger" style="padding: 5px 10px; font-size: 0.85em;" onclick="return confirm('Attention : La suppression de ce projet est irréversible.\\nÊtes-vous sûr ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($projets)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d; font-style: italic;">Aucun projet trouvé.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
<nav class="pagination-bar" aria-label="Pagination des projets" style="margin-top: 20px; text-align: center;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="pagination-link <?php echo $i === (int) ($page ?? 1) ? 'active' : ''; ?>"
           href="index.php?controller=projet&action=admin&page=<?php echo $i; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>"
           style="display: inline-block; padding: 6px 12px; margin: 0 4px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #2c3e50; <?php echo $i === (int) ($page ?? 1) ? 'background-color: #3498db; color: white; border-color: #3498db;' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
