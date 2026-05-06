<?php ?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php if ($this->user->avatar_url): ?>
                <img src="<?php echo htmlspecialchars($this->user->avatar_url); ?>" alt="Avatar" class="avatar-img">
            <?php else: ?>
                <div class="avatar-placeholder"><?php echo strtoupper(substr($this->user->nom, 0, 1)); ?></div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($this->user->nom); ?></h1>
            <p class="role-badge"><?php echo htmlspecialchars($this->user->role); ?></p>
            <?php if ($this->user->city): ?>
                <p class="city">📍 <?php echo htmlspecialchars($this->user->city); ?></p>
            <?php endif; ?>
            <p class="email">✉️ <?php echo htmlspecialchars($this->user->email); ?></p>
            <p class="joined">Membre depuis le <?php echo date('d/m/Y', strtotime($this->user->created_at)); ?></p>
        </div>
        <?php if (currentUserId() === $this->user->id_utilisateur): ?>
            <a href="index.php?controller=user&action=edit" class="btn-edit">Modifier le profil</a>
        <?php endif; ?>
    </div>

    <?php if ($this->user->bio): ?>
    <div class="profile-bio">
        <h3>À propos</h3>
        <p><?php echo nl2br(htmlspecialchars($this->user->bio)); ?></p>
    </div>
    <?php endif; ?>

    <div class="profile-stats">
        <div class="stat">
            <span class="stat-number"><?php echo $stats['posts']; ?></span>
            <span class="stat-label">Posts</span>
        </div>
        <div class="stat">
            <span class="stat-number"><?php echo $stats['comments']; ?></span>
            <span class="stat-label">Commentaires</span>
        </div>
        <div class="stat">
            <span class="stat-number"><?php echo $stats['reactions']; ?></span>
            <span class="stat-label">Réactions</span>
        </div>
    </div>

    <div class="profile-activity">
        <h3>Activité récente</h3>

        <?php if (!empty($recentPosts)): ?>
        <div class="activity-section">
            <h4>Posts récents</h4>
            <ul class="activity-list">
                <?php foreach ($recentPosts as $post): ?>
                <li>
                    <a href="index.php?controller=post&action=show&id=<?php echo $post['id_post']; ?>">
                        <?php echo htmlspecialchars(substr($post['titre'], 0, 50)); ?>...
                    </a>
                    <span class="date"><?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($recentComments)): ?>
        <div class="activity-section">
            <h4>Commentaires récents</h4>
            <ul class="activity-list">
                <?php foreach ($recentComments as $comment): ?>
                <li>
                    <a href="index.php?controller=post&action=show&id=<?php echo $comment['post_id']; ?>">
                        Sur: <?php echo htmlspecialchars(substr($comment['post_titre'] ?? 'Sujet inconnu', 0, 40)); ?>
                    </a>
                    <span class="date"><?php echo date('d/m/Y H:i', strtotime($comment['date_creation'])); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (empty($recentPosts) && empty($recentComments)): ?>
        <p class="empty-message">Aucune activité pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.profile-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.profile-header { display: flex; gap: 2rem; margin-bottom: 2rem; align-items: flex-start; }
.profile-avatar { flex-shrink: 0; }
.avatar-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
.avatar-placeholder { width: 120px; height: 120px; border-radius: 50%; background: #3498db; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: bold; }
.profile-info { flex: 1; }
.profile-info h1 { margin: 0 0 0.5rem 0; }
.role-badge { display: inline-block; background: #2ecc71; color: #fff; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 12px; font-weight: bold; margin: 0 0 0.5rem 0; }
.city, .email, .joined { margin: 0.25rem 0; color: #666; }
.btn-edit { display: inline-block; background: #3498db; color: #fff; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; margin-top: 1rem; }
.btn-edit:hover { background: #2980b9; }
.profile-bio { background: #f9f9f9; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem; }
.profile-bio h3 { margin-top: 0; }
.profile-stats { display: flex; gap: 2rem; margin-bottom: 2rem; padding: 1rem; background: #f0f0f0; border-radius: 4px; }
.stat { text-align: center; }
.stat-number { display: block; font-size: 24px; font-weight: bold; color: #2c3e50; }
.stat-label { display: block; color: #7f8c8d; font-size: 12px; }
.profile-activity h3 { margin-top: 2rem; }
.activity-section { margin-bottom: 1.5rem; }
.activity-list { list-style: none; padding: 0; }
.activity-list li { padding: 0.75rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
.activity-list li a { color: #3498db; text-decoration: none; }
.activity-list li a:hover { text-decoration: underline; }
.date { color: #999; font-size: 12px; }
.empty-message { text-align: center; color: #999; padding: 2rem; }
</style>
