<div class="projet-container">
    <div class="projet-header">
        <div>
            <h2>Découvrez les Startups</h2>
            <p>Explorez les projets innovants et rejoignez la communauté entrepreneuriale.</p>
        </div>
        <div class="forum-actions">
            <?php if (function_exists('currentUserId') && currentUserId()): ?>
                <a href="index.php?controller=projet&action=create" class="btn-primary">+ Nouveau Projet</a>
            <?php else: ?>
                <a href="login.php" class="btn-primary">Connectez-vous</a>
            <?php endif; ?>
        </div>
    </div>

    <form class="projet-searchbar" action="index.php" method="GET">
        <input type="hidden" name="controller" value="projet">
        <input type="hidden" name="action" value="index">
        <?php if (!empty($categoryFilter)): ?><input type="hidden" name="categorie_id" value="<?php echo htmlspecialchars($categoryFilter); ?>"><?php endif; ?>
        <?php if (!empty($sortBy)): ?><input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>"><?php endif; ?>
        <input type="search" name="search" placeholder="Rechercher un projet..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" class="btn-primary">Rechercher</button>
    </form>

    <div class="projet-layout">
        <aside class="projet-sidebar">
            <!-- Categories Filter -->
            <div class="sidebar-box filter-box">
                <h3>Catégories</h3>
                <ul class="category-list">
                    <li><a href="index.php?controller=projet&action=index" class="<?php echo empty($_GET['categorie_id']) ? 'active' : ''; ?>">Toutes les catégories</a></li>
                    <?php foreach ($categoriesList as $cat): ?>
                    <li><a href="index.php?controller=projet&action=index&categorie_id=<?php echo urlencode($cat['id']); ?>" class="<?php echo (isset($_GET['categorie_id']) && $_GET['categorie_id'] == $cat['id']) ? 'active' : ''; ?>"><?php echo htmlspecialchars($cat['typeprojet']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Sort Options -->
            <div class="sidebar-box sort-box">
                <h3>Trier par</h3>
                <form action="index.php" method="GET" class="sort-form">
                    <input type="hidden" name="controller" value="projet">
                    <input type="hidden" name="action" value="index">
                    <?php if(isset($_GET['categorie_id'])) echo '<input type="hidden" name="categorie_id" value="'.htmlspecialchars($_GET['categorie_id']).'">'; ?>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="latest" <?php echo (isset($_GET['sort']) && $_GET['sort']=='latest') ? 'selected' : ''; ?>>Plus récents</option>
                        <option value="trending" <?php echo (isset($_GET['sort']) && $_GET['sort']=='trending') ? 'selected' : ''; ?>>Tendance</option>
                        <option value="most_discussed" <?php echo (isset($_GET['sort']) && $_GET['sort']=='most_discussed') ? 'selected' : ''; ?>>Plus discutés</option>
                        <option value="budget_desc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='budget_desc') ? 'selected' : ''; ?>>Budget élevé</option>
                    </select>
                </form>
            </div>

            <!-- Stats -->
            <div class="sidebar-box stats-box">
                <h3>Statistiques</h3>
                <div style="text-align: center; margin-top: 15px;">
                    <div style="font-size: 28px; font-weight: 700; color: #0B1C48; margin-bottom: 5px;"><?php echo count($projets); ?></div>
                    <div style="font-size: 13px; color: #6c757d;">Projets Actifs</div>
                    <div style="font-size: 28px; font-weight: 700; color: #0B1C48; margin: 15px 0 5px 0;">$<?php echo number_format(array_sum(array_column($projets, 'budget')) / 1000000, 1); ?>M</div>
                    <div style="font-size: 13px; color: #6c757d;">Budget Total</div>
                </div>
            </div>
        </aside>

        <!-- Projects Grid -->
        <main>
            <?php if (empty($projets)): ?>
                <div class="empty-state">
                    <h3>Aucun projet trouvé</h3>
                    <p>Essayez une autre recherche ou lancez votre premier projet.</p>
                    <?php if (function_exists('currentUserId') && currentUserId()): ?>
                        <a href="index.php?controller=projet&action=create" class="btn-primary">Créer un projet</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="projet-grid">
                    <?php foreach ($projets as $row): ?>
                    <div class="projet-card">
                        <span class="projet-category-badge"><?php echo htmlspecialchars($row['categorie_nom'] ?? 'Sans catégorie'); ?></span>
                        
                        <a href="index.php?controller=projet&action=show&id=<?php echo $row['id']; ?>" class="projet-title" onclick="event.stopPropagation();">
                            <?php echo htmlspecialchars($row['nomprojet']); ?>
                        </a>
                        
                        <p class="projet-description">
                            <?php echo htmlspecialchars(substr($row['description'] ?? 'Aucune description', 0, 100)) . (strlen($row['description'] ?? '') > 100 ? '...' : ''); ?>
                        </p>

                        <?php if (!empty($row['competences'])): ?>
                            <div style="margin-top: auto; margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 6px;">
                                <?php foreach (array_slice($row['competences'], 0, 3) as $comp): ?>
                                    <span style="background: #e8f4fd; color: #2980b9; font-size: 11px; padding: 4px 10px; border-radius: 12px; font-weight: bold; border: 1px solid #d4e6f1;">
                                        <?php echo htmlspecialchars($comp); ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($row['competences']) > 3): ?>
                                    <span style="color: #2980b9; font-size: 11px; padding: 4px; font-weight: bold;">+<?php echo count($row['competences']) - 3; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="projet-meta">
                            <div class="projet-budget">
                                <span>💰</span>
                                <strong><?php echo htmlspecialchars($row['budget']); ?> DT</strong>
                            </div>
                            <div class="projet-author" onclick="event.stopPropagation(); window.location.href='index.php?controller=profile&action=index&id=<?php echo $row['auteur_id']; ?>';" style="cursor: pointer;" title="Voir le profil">
                                <span class="avatar-xs"><?php echo strtoupper(substr($row['auteur_nom'] ?? 'U', 0, 1)); ?></span>
                                <span style="color: #3498db; font-weight: bold;"><?php echo htmlspecialchars($row['auteur_nom'] ?? 'Utilisateur'); ?></span>
                            </div>
                        </div>

                        <div class="projet-footer">
                            <div class="projet-reactions">
                                <button class="reaction-btn <?php echo (($row['current_user_reaction'] ?? null) === 'LIKE') ? 'liked' : ''; ?>" id="btnLike_<?php echo $row['id']; ?>" onclick="event.stopPropagation(); toggleProjetReaction(<?php echo $row['id']; ?>, 'LIKE')" title="J'aime">
                                    <i data-lucide="thumbs-up" style="width:14px;height:14px;"></i> <span id="like-count_<?php echo $row['id']; ?>"><?php echo (int)($row['likes_count'] ?? 0); ?></span>
                                </button>
                                <button class="reaction-btn <?php echo (($row['current_user_reaction'] ?? null) === 'DISLIKE') ? 'disliked' : ''; ?>" id="btnDislike_<?php echo $row['id']; ?>" onclick="event.stopPropagation(); toggleProjetReaction(<?php echo $row['id']; ?>, 'DISLIKE')" title="Je n'aime pas">
                                    <i data-lucide="thumbs-down" style="width:14px;height:14px;"></i> <span id="dislike-count_<?php echo $row['id']; ?>"><?php echo (int)($row['dislikes_count'] ?? 0); ?></span>
                                </button>
                                <span style="font-size: 12px; color: #6c757d; padding: 6px 10px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="message-circle" style="width:14px;height:14px;"></i> <?php echo (int)($row['commentaires_count'] ?? 0); ?></span>
                            </div>

                            <div class="projet-actions">
                                <a href="index.php?controller=projet&action=show&id=<?php echo $row['id']; ?>" class="btn-view" onclick="event.stopPropagation();">Voir</a>
                                <?php if ((function_exists('currentUserId') && currentUserId() === (int)$row['auteur_id']) || (function_exists('isAdmin') && isAdmin())): ?>
                                    <a href="index.php?controller=projet&action=edit&id=<?php echo $row['id']; ?>" class="btn-edit-sm" onclick="event.stopPropagation();">Modifier</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="index.php?controller=projet&action=index&page=1<?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&categorie_id='.$categoryFilter : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>">« Première</a>
                        <a href="index.php?controller=projet&action=index&page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&categorie_id='.$categoryFilter : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>">‹ Précédente</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="index.php?controller=projet&action=index&page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&categorie_id='.$categoryFilter : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?controller=projet&action=index&page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&categorie_id='.$categoryFilter : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>">Suivante ›</a>
                        <a href="index.php?controller=projet&action=index&page=<?php echo $totalPages; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&categorie_id='.$categoryFilter : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>">Dernière »</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="projet.js"></script>
<link rel="stylesheet" href="projet.css">

