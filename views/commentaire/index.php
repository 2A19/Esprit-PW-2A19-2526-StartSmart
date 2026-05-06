<div class="bo-header" style="border-bottom: 2px solid #f0f2f5; padding-bottom: 15px; margin-bottom: 20px;">
    <h2>Modération des Commentaires</h2>
    <div style="display:flex; gap:10px;">
        <a href="index.php?controller=commentaire&action=create" class="btn-primary" style="background-color: #3498db;">+ Nouveau Commentaire</a>
    </div>
</div>

<div class="search-form" style="background: #f9fbfd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e1e8ed;">
    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher par mot-clé, auteur ou titre du post..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
</div>

<div style="overflow-x: auto;">
    <table class="bo-table" id="commentsTable" style="width: 100%; border-collapse: collapse; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Commentaire</th>
                <th style="padding: 12px; text-align: left;">Auteur</th>
                <th style="padding: 12px; text-align: left;">Sujet (Post)</th>
                <th style="padding: 12px; text-align: center;">Type</th>
                <th style="padding: 12px; text-align: right;">Date</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commentaires as $row): ?>
            <tr style="border-bottom: 1px solid #eee; transition: background-color 0.2s;">
                <td style="padding: 12px; color: #7f8c8d;">#<?php echo $row['id_commentaire']; ?></td>
                <td style="padding: 12px;">
                    <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500; color: #2c3e50;" title="<?php echo htmlspecialchars($row['contenu']); ?>">
                        <?php echo htmlspecialchars($row['contenu']); ?>
                    </div>
                </td>
                <td style="padding: 12px;">
                    <span style="background: #e8f4f8; color: #2980b9; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        <?php echo htmlspecialchars($row['auteur_nom'] ?? 'Auteur #' . $row['auteur_id']); ?>
                    </span>
                </td>
                <td style="padding: 12px; color: #34495e;">
                    <em><?php echo $row['post_titre'] ? htmlspecialchars($row['post_titre']) : 'Post introuvable'; ?></em>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <?php if(!empty($row['parent_id'])): ?>
                        <span style="background: #fdf2e9; color: #d35400; padding: 3px 6px; border-radius: 4px; font-size: 0.8em;">Réponse</span>
                    <?php else: ?>
                        <span style="background: #eafaf1; color: #27ae60; padding: 3px 6px; border-radius: 4px; font-size: 0.8em;">Principal</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 12px; text-align: right; color: #95a5a6; font-size: 0.9em;">
                    <?php echo date('d M Y, H:i', strtotime($row['date_creation'])); ?>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <?php if (function_exists('isAdmin') && function_exists('currentUserId') && (isAdmin() || currentUserId() === (int)$row['auteur_id'])): ?>
                        <a href="index.php?controller=commentaire&action=edit&id=<?php echo $row['id_commentaire']; ?>" class="btn-edit" style="margin-right: 5px; padding: 5px 10px; font-size: 0.85em;">Éditer</a>
                        <a href="index.php?controller=commentaire&action=delete&id=<?php echo $row['id_commentaire']; ?>" class="btn-danger" style="padding: 5px 10px; font-size: 0.85em;" onclick="return confirm('Supprimer ce commentaire définitivement ?');">Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($commentaires)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d; font-style: italic;">Aucun commentaire trouvé.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche dynamique (Filtrage de la table)
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#commentsTable tbody tr');
            
            rows.forEach(row => {
                // Ignore empty state row
                if (row.children.length === 1 && row.children[0].colSpan > 1) return;
                
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>
