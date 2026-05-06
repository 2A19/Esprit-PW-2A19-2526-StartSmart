<?php
// Recommend view - uses $projects set by MatchingController
?>

<div class="matching-header">
    <h1>🎯 Projets Recommandés Pour Vous</h1>
    <p>Découvrez les projets qui correspondent le mieux à vos compétences et intérêts</p>
    <div class="header-actions">
        <a href="index.php?controller=matching&action=discover" class="btn-primary">
            ✨ Mode Découverte (Swipe)
        </a>
        <a href="index.php?controller=matching&action=profile" class="btn-secondary">
            ⚙️ Mon Profil
        </a>
    </div>
</div>

<div class="matching-stats">
    <div class="stat-card">
        <span class="stat-label">Projets Recommandés</span>
        <span class="stat-value"><?php echo count($projects ?? []); ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Score Moyen</span>
        <span class="stat-value">
            <?php 
            $projects = $projects ?? [];
            $avg_score = count($projects) > 0 ? round(array_sum(array_column($projects, 'match_score')) / count($projects), 1) : 0;
            echo $avg_score . '%';
            ?>
        </span>
    </div>
</div>

<div class="matching-container">
    <?php if (empty($projects)): ?>
        <div class="empty-state">
            <p>Aucun projet recommandé pour le moment</p>
            <p>Complétez votre profil pour de meilleures recommandations</p>
            <a href="index.php?controller=matching&action=profile" class="btn-primary">
                Compléter Mon Profil
            </a>
        </div>
    <?php else: ?>
        <div class="matching-grid">
            <?php foreach ($projects as $index => $project): ?>
                <div class="match-card">
                    <div class="match-rank"><?php echo $index + 1; ?></div>
                    
                    <div class="match-score-badge">
                        <div class="score-circle" data-score="<?php echo $project['match_score']; ?>">
                            <span class="score-value"><?php echo round($project['match_score']); ?></span>
                            <span class="score-label">%</span>
                        </div>
                    </div>

                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($project['nomprojet']); ?></h3>
                        <span class="ref-number">#<?php echo $project['num']; ?></span>
                    </div>

                    <p class="card-description">
                        <?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 100)); ?>...
                    </p>

                    <div class="card-meta">
                        <span class="meta-item">💰 <?php echo number_format($project['budget'] ?? 0); ?> DT</span>
                        <span class="meta-item">⏱️ Gain: <?php echo number_format($project['gain'] ?? 0); ?> DT</span>
                    </div>

                    <div class="card-actions">
                        <a href="index.php?controller=projet&action=show&id=<?php echo $project['id']; ?>" class="btn-secondary">
                            Voir Détails
                        </a>
                        <button class="btn-primary" onclick="recordAction(<?php echo $project['id']; ?>, 'interested')">
                            ❤️ Intéressé
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="index.php?controller=matching&action=recommend&page=<?php echo $current_page - 1; ?>" class="btn-secondary">← Précédent</a>
                <?php endif; ?>
                
                <span class="page-info">Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?></span>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="index.php?controller=matching&action=recommend&page=<?php echo $current_page + 1; ?>" class="btn-secondary">Suivant →</a>
                <?php endif; ?>
            </div>
</div>

<style>
.matching-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 30px;
    text-align: center;
}

.matching-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.matching-header p {
    margin: 0 0 20px 0;
    font-size: 16px;
    opacity: 0.9;
}

.header-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.matching-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #667eea;
}

.matching-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.match-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    padding: 20px;
}

.match-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.match-score-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.score-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.score-value {
    font-size: 20px;
}

.score-label {
    font-size: 10px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
    padding-right: 70px;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.ref-number {
    font-size: 12px;
    color: #999;
}

.card-description {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
    line-height: 1.4;
}

.skills-section,
.required-skills {
    margin-bottom: 15px;
}

.skills-section h4,
.required-skills h4 {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.skill-tag {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.skill-tag.matched {
    background: #e8f5e9;
    color: #27ae60;
    border: 1px solid #27ae60;
}

.skill-tag.empty {
    background: #f5f5f5;
    color: #999;
}

.skill-tag:not(.matched):not(.empty) {
    background: #f0f0f0;
    color: #999;
}

.card-meta {
    display: flex;
    gap: 15px;
    padding: 12px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 15px;
    font-size: 13px;
    color: #666;
}

.meta-item {
    flex: 1;
    text-align: center;
}

.card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.btn-primary, .btn-secondary {
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background: #e9ecef;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.empty-state p {
    color: #666;
    margin: 10px 0;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 40px;
    padding: 20px 0;
}

.page-link {
    padding: 8px 12px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    color: #667eea;
    font-size: 14px;
    transition: all 0.3s;
}

.page-link:hover {
    background: #667eea;
    color: white;
}

.page-link.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}
</style>

<script src="matching.js"></script>
