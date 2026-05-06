<div class="forum-container">
    <div class="forum-header">
        <div class="forum-title">
            <h2>Discussions StartSmart</h2>
            <p>Rejoignez la conversation avec d'autres entrepreneurs et experts.</p>
        </div>
        <div class="forum-actions">
            <a href="index.php?controller=post&action=create" class="btn-primary">+ Nouveau Sujet</a>
        </div>
    </div>


    <form class="forum-searchbar" action="index.php" method="GET">
        <input type="hidden" name="controller" value="post">
        <input type="hidden" name="action" value="index">
        <?php if (!empty($topicFilter)): ?><input type="hidden" name="topic" value="<?php echo htmlspecialchars($topicFilter); ?>"><?php endif; ?>
        <?php if (!empty($sortBy)): ?><input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>"><?php endif; ?>
        <input type="search" name="search" placeholder="Rechercher un sujet..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" class="btn-primary">Rechercher</button>
    </form>

    <div class="forum-layout">
        <aside class="forum-sidebar">
            <div class="sidebar-box filter-box">
                <h3>Sujets</h3>
                <ul class="topic-list">
                    <li><a href="index.php?controller=post&action=index" class="<?php echo empty($_GET['topic']) ? 'active' : ''; ?>">Tous les sujets</a></li>
                    <?php foreach ($topics as $t): ?>
                    <li><a href="index.php?controller=post&action=index&topic=<?php echo urlencode($t); ?>" class="<?php echo (isset($_GET['topic']) && $_GET['topic'] === $t) ? 'active' : ''; ?>"><?php echo htmlspecialchars($t); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="sidebar-box sort-box">
                <h3>Trier par</h3>
                <form action="index.php" method="GET" class="sort-form">
                    <input type="hidden" name="controller" value="post">
                    <input type="hidden" name="action" value="index">
                    <?php if(isset($_GET['topic'])) echo '<input type="hidden" name="topic" value="'.htmlspecialchars($_GET['topic']).'">'; ?>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="latest" <?php echo (isset($_GET['sort']) && $_GET['sort']=='latest') ? 'selected' : ''; ?>>Plus récents</option>
                        <option value="most_commented" <?php echo (isset($_GET['sort']) && $_GET['sort']=='most_commented') ? 'selected' : ''; ?>>Plus commentés</option>
                        <option value="most_liked" <?php echo (isset($_GET['sort']) && $_GET['sort']=='most_liked') ? 'selected' : ''; ?>>Plus aimés</option>
                    </select>
                </form>
            </div>

            <!-- Projets / Apps Tendances -->
            <div class="sidebar-box projects-box" style="margin-top: 20px;">
                <h3>Apps & Projets Tendances</h3>
                <ul class="topic-list" style="list-style:none; padding:0; margin:0;">
                    <?php if(!empty($topApps)): foreach($topApps as $app): ?>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        <strong style="color: #2c3e50; display: block; font-size: 0.95em;"><?php echo htmlspecialchars($app['nomprojet']); ?></strong>
                        <span style="font-size: 0.8em; color: #7f8c8d;">Budget: <?php echo htmlspecialchars($app['budget'] ?? 'N/A'); ?> DT</span>
                    </li>
                    <?php endforeach; else: ?>
                    <li style="color: #7f8c8d; font-size: 0.9em;">Aucun projet en cours.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>

        <main class="forum-feed">
            <?php if (empty($posts)): ?>
                <div class="no-posts empty-state">
                    <h3>Aucun sujet trouvé</h3>
                    <p>Essayez une autre recherche ou lancez la première discussion de la communauté.</p>
                    <a href="index.php?controller=post&action=create" class="btn-primary">Créer un sujet</a>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $row): ?>
                <div class="post-card" onclick="window.location.href='index.php?controller=post&action=show&id=<?php echo $row['id_post']; ?>'">
                    <div class="post-card-left">
                        <button class="upvote-box <?php echo (($row['current_user_reaction'] ?? null) === 'LIKE') ? 'liked' : ''; ?>" id="btnLike_<?php echo $row['id_post']; ?>" onclick="event.stopPropagation(); toggleReaction(<?php echo $row['id_post']; ?>, 'LIKE')">
                            <span class="upvote-icon">▲</span>
                            <span class="upvote-count" id="like-count_<?php echo $row['id_post']; ?>"><?php echo (int) ($row['likes_count'] ?? 0); ?></span>
                        </button>
                        <button class="downvote-box <?php echo (($row['current_user_reaction'] ?? null) === 'DISLIKE') ? 'disliked' : ''; ?>" id="btnDislike_<?php echo $row['id_post']; ?>" onclick="event.stopPropagation(); toggleReaction(<?php echo $row['id_post']; ?>, 'DISLIKE')">
                            <span class="downvote-icon">▼</span>
                            <span class="downvote-count" id="dislike-count_<?php echo $row['id_post']; ?>"><?php echo (int) ($row['dislikes_count'] ?? 0); ?></span>
                        </button>
                    </div>
                    <div class="post-card-main">
                        <div class="post-meta">
                            <span class="post-topic badge-<?php echo strtolower($row['topic'] ?: 'general'); ?>"><?php echo htmlspecialchars($row['topic'] ?: 'General'); ?></span>
                            <?php if (!empty($row['projet_id'])): ?>
                                <a href="index.php?controller=projet&action=show&id=<?php echo $row['projet_id']; ?>" class="post-topic" style="background: #e8f4fd; color: #2980b9; border: 1px solid #d4e6f1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" onclick="event.stopPropagation();"><i data-lucide="rocket" style="width:14px;height:14px;"></i> Projet: <?php echo htmlspecialchars($row['projet_nom']); ?></a>
                            <?php endif; ?>
                            <div class="post-author-info" onclick="event.stopPropagation(); window.location.href='index.php?controller=profile&action=index&id=<?php echo $row['auteur_id']; ?>';" style="cursor: pointer;" title="Voir le profil">
                                <div class="avatar-sm"><?php echo strtoupper(substr($row['auteur_nom'] ?? 'U', 0, 1)); ?></div>
                                <span class="post-author" style="color: #3498db; font-weight: bold;"><?php echo htmlspecialchars($row['auteur_nom'] ?? 'Utilisateur'); ?></span>
                            </div>
                            <span class="post-date">• <?php echo date('d M Y', strtotime($row['date_creation'])); ?></span>
                        </div>
                        <h3 class="post-title"><?php echo htmlspecialchars($row['titre']); ?></h3>
                        <p class="post-excerpt"><?php echo htmlspecialchars(substr($row['contenu'], 0, 150)) . (strlen($row['contenu']) > 150 ? '...' : ''); ?></p>
                        <div class="post-footer">
                            <span class="comment-count" style="display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="message-circle" style="width:14px;height:14px;"></i> <?php echo (int)$row['commentaires_count']; ?> commentaires</span>
                            <?php if ((function_exists('currentUserId') && currentUserId() === (int)$row['auteur_id']) || (function_exists('isAdmin') && isAdmin())): ?>
                                <a href="index.php?controller=post&action=edit&id=<?php echo $row['id_post']; ?>" class="btn-edit-sm" onclick="event.stopPropagation();">Modifier</a>
                                <?php if(function_exists('isAdmin') && isAdmin()): ?>
                                <a href="index.php?controller=post&action=delete&id=<?php echo $row['id_post']; ?>" class="btn-edit-sm" style="color: #e74c3c; border-color: #e74c3c;" onclick="event.stopPropagation(); return confirm('Supprimer ce sujet ?');">Supprimer</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($totalPages) && $totalPages > 1): ?>
                <nav class="pagination-bar" aria-label="Pagination des sujets">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="pagination-link <?php echo $i === (int) ($page ?? 1) ? 'active' : ''; ?>"
                           href="index.php?controller=post&action=index&page=<?php echo $i; ?>&search=<?php echo urlencode($search ?? ''); ?>&topic=<?php echo urlencode($topicFilter ?? ''); ?>&sort=<?php echo urlencode($sortBy ?? 'latest'); ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

