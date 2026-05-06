<div class="bo-header" style="border-bottom: 2px solid #f0f2f5; padding-bottom: 15px; margin-bottom: 20px;">
    <h2>Modération du Forum</h2>
    <div style="display:flex; gap:10px;">
        <a href="index.php?controller=post&action=index" class="btn-secondary">Voir le Forum Public</a>
        <a href="index.php?controller=post&action=create" class="btn-primary" style="background-color: #3498db;">+ Nouveau Sujet</a>
    </div>
</div>

<div class="search-form" style="background: #f9fbfd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e1e8ed;">
    <form action="index.php" method="GET" style="display:flex; gap:10px; width: 100%;">
        <input type="hidden" name="controller" value="post">
        <input type="hidden" name="action" value="admin">
        <input type="text" name="search" class="search-input" placeholder="Rechercher un sujet par titre ou auteur..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="flex:1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="submit" class="btn-primary">Filtrer</button>
    </form>
</div>

<div style="overflow-x: auto;">
    <table class="bo-table" id="adminPostsTable" style="width: 100%; border-collapse: collapse; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Sujet</th>
                <th style="padding: 12px; text-align: left;">Auteur</th>
                <th style="padding: 12px; text-align: center;">Thème</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
                <th style="padding: 12px; text-align: right;">Interactions</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $row): ?>
            <tr style="border-bottom: 1px solid #eee; transition: background-color 0.2s;">
                <td style="padding: 12px; color: #7f8c8d;">#<?php echo $row['id_post']; ?></td>
                <td style="padding: 12px;">
                    <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: bold; color: #2c3e50;" title="<?php echo htmlspecialchars($row['titre']); ?>">
                        <a href="index.php?controller=post&action=show&id=<?php echo $row['id_post']; ?>" style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($row['titre']); ?>
                        </a>
                    </div>
                    <div style="font-size: 0.85em; color: #95a5a6; margin-top: 4px;">
                        Publié le <?php echo date('d/m/Y H:i', strtotime($row['date_creation'])); ?>
                    </div>
                </td>
                <td style="padding: 12px;">
                    <span style="background: #e8f4f8; color: #2980b9; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        <?php echo htmlspecialchars($row['auteur_nom'] ?? 'Utilisateur'); ?>
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span class="badge-<?php echo strtolower($row['topic'] ?: 'general'); ?>" style="font-size: 0.8em; padding: 3px 6px; border-radius: 4px;"><?php echo htmlspecialchars($row['topic'] ?: 'General'); ?></span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <?php if(($row['statut'] ?? 'actif') === 'actif'): ?>
                        <span style="background: #eafaf1; color: #27ae60; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">Actif</span>
                    <?php else: ?>
                        <span style="background: #fdf2e9; color: #d35400; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;"><?php echo ucfirst(htmlspecialchars($row['statut'])); ?></span>
                    <?php endif; ?>
                </td>
                <td style="padding: 12px; text-align: right;">
                    <div style="font-size: 0.9em; color: #7f8c8d;">
                        <span title="Commentaires" style="margin-right:8px;">💬 <?php echo (int)$row['commentaires_count']; ?></span>
                        <span title="Likes" style="margin-right:8px; color: #27ae60;">👍 <?php echo (int)$row['likes_count']; ?></span>
                        <span title="Dislikes" style="color: #e74c3c;">👎 <?php echo (int)$row['dislikes_count']; ?></span>
                    </div>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <a href="index.php?controller=post&action=edit&id=<?php echo $row['id_post']; ?>" class="btn-edit" style="margin-right: 5px; padding: 5px 10px; font-size: 0.85em;">Gérer</a>
                    <a href="index.php?controller=post&action=delete&id=<?php echo $row['id_post']; ?>" class="btn-danger" style="padding: 5px 10px; font-size: 0.85em;" onclick="return confirm('Attention : La suppression de ce sujet entraînera la perte de tous ses commentaires.\\nÊtes-vous sûr ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d; font-style: italic;">Aucun sujet trouvé dans le forum.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
<nav class="pagination-bar" aria-label="Pagination des sujets" style="margin-top: 20px; text-align: center;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="pagination-link <?php echo $i === (int) ($page ?? 1) ? 'active' : ''; ?>"
           href="index.php?controller=post&action=admin&page=<?php echo $i; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>"
           style="display: inline-block; padding: 6px 12px; margin: 0 4px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #2c3e50; <?php echo $i === (int) ($page ?? 1) ? 'background-color: #3498db; color: white; border-color: #3498db;' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
