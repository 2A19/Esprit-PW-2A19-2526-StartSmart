<?php
// Discover view - uses $project set by MatchingController
?>

<div class="discover-header">
    <h1>🎲 Découvrez les Projets</h1>
    <p>Trouvez les projets qui correspondent à vos compétences et intérêts</p>
</div>

<div class="discover-container">
    <?php if (isset($project) && !empty($project)): ?>
        <div class="swipe-card" id="swipeCard" data-project-id="<?php echo $project['id']; ?>" style="--score: <?php echo $project['match_score']; ?>">
            <div class="swipe-badge">
                <div class="match-percentage"><?php echo round($project['match_score']); ?>% Match</div>
            </div>

            <div class="swipe-content">
                <h2><?php echo htmlspecialchars($project['nomprojet']); ?></h2>
                <span class="project-ref">#<?php echo $project['num']; ?></span>

                <div class="project-details">
                    <div class="detail-item">
                        <span class="detail-label">Budget</span>
                        <span class="detail-value"><?php echo number_format($project['budget'] ?? 0); ?> DT</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Estimé</span>
                        <span class="detail-value"><?php echo number_format($project['gain'] ?? 0); ?> DT</span>
                    </div>
                </div>

                <p class="project-description">
                    <?php echo htmlspecialchars(substr($project['description'] ?? 'Pas de description', 0, 200)); ?>...
                </p>

                <div class="matching-section">
                    <h3>🎯 Compétences Correspondantes</h3>
                    <div class="matched-skills">
                        <?php if (!empty($project['matched_skills'])): ?>
                            <?php foreach ($project['matched_skills'] as $skill): ?>
                                <div class="skill-badge matched">
                                    <span class="skill-check">✓</span>
                                    <span class="skill-name"><?php echo htmlspecialchars($skill['name'] ?? ''); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-skills">Aucune compétence correspondante</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="required-section">
                    <h3>🔧 Compétences Requises</h3>
                    <div class="required-skills">
                        <?php if (!empty($project['required_skills'])): ?>
                            <?php foreach ($project['required_skills'] as $skill): ?>
                                <?php 
                                $is_matched = !empty($project['matched_skills']) && in_array($skill['id'], array_column($project['matched_skills'], 'id'));
                                ?>
                                <div class="skill-badge <?php echo $is_matched ? 'matched' : 'missing'; ?>">
                                    <span class="skill-check"><?php echo $is_matched ? '✓' : '✗'; ?></span>
                                    <span class="skill-name"><?php echo htmlspecialchars($skill['name'] ?? ''); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-skills">Aucune compétence requise</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="swipe-info">
                    <span class="info-text">Exprimez votre intérêt ou passez</span>
                </div>
            </div>

            <div class="swipe-actions">
                <button class="action-btn skip" onclick="skipProject()">
                    <span class="action-icon">←</span>
                    <span class="action-text">Passer</span>
                </button>
                <a href="index.php?controller=projet&action=show&id=<?php echo $project['id']; ?>" class="action-btn view">
                    <span class="action-icon">👁️</span>
                    <span class="action-text">Voir</span>
                </a>
                <button class="action-btn interested" onclick="interestedProject()">
                    <span class="action-icon">❤️</span>
                    <span class="action-text">Intéressé</span>
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="no-projects">
            <h2>✨ Aucun projet à afficher</h2>
            <p>Complétez votre profil avec vos compétences et intérêts pour voir les projets recommandés.</p>
            <a href="index.php?controller=matching&action=profile" class="btn-primary">
                ⚙️ Compléter Mon Profil
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="discover-footer">
    <a href="index.php?controller=matching&action=recommend" class="btn-secondary">
        📊 Vue Liste (Recommandations)
    </a>
</div>

            <div class="swipe-content">
                <h2><?php echo htmlspecialchars($project['nomprojet']); ?></h2>
                <span class="project-ref">#<?php echo $project['num']; ?></span>

                <div class="project-details">
                    <div class="detail-item">
                        <span class="detail-label">Budget</span>
                        <span class="detail-value"><?php echo number_format($project['budget'] ?? 0); ?> DT</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Gain Estimé</span>
                        <span class="detail-value"><?php echo number_format($project['gain'] ?? 0); ?> DT</span>
                    </div>
                </div>

                <p class="project-description">
                    <?php echo htmlspecialchars($project['description'] ?? 'Pas de description'); ?>
                </p>

                <div class="matching-section">
                    <h3>🎯 Vos Compétences Correspondantes</h3>
                    <div class="matched-skills">
                        <?php if (!empty($project['matched_skills'])): ?>
                            <?php foreach ($project['matched_skills'] as $skill): ?>
                                <div class="skill-badge matched">
                                    <span class="skill-check">✓</span>
                                    <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                                    <span class="skill-category"><?php echo htmlspecialchars($skill['category']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-skills">Vous n'avez aucune compétence correspondante pour ce projet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="required-section">
                    <h3>🔧 Compétences Requises</h3>
                    <div class="required-skills">
                        <?php if (!empty($project['required_skills'])): ?>
                            <?php foreach ($project['required_skills'] as $skill): ?>
                                <?php 
                                $is_matched = in_array($skill['id'], array_column($project['matched_skills'] ?? [], 'id'));
                                ?>
                                <div class="skill-badge <?php echo $is_matched ? 'matched' : 'missing'; ?>">
                                    <span class="skill-check"><?php echo $is_matched ? '✓' : '✗'; ?></span>
                                    <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                                    <span class="skill-category"><?php echo htmlspecialchars($skill['category']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="swipe-info">
                    <span class="info-text">Glissez pour réagir</span>
                </div>
            </div>

            <div class="swipe-actions">
                <button class="action-btn skip" onclick="skipProject()">
                    <span class="action-icon">←</span>
                    <span class="action-text">Passer</span>
                </button>
                <a href="index.php?controller=projet&action=show&id=<?php echo $project['id']; ?>" class="action-btn view">
                    <span class="action-icon">👁️</span>
                    <span class="action-text">Voir</span>
                </a>
                <button class="action-btn interested" onclick="interestedProject()">
                    <span class="action-icon">→</span>
                    <span class="action-text">Intéressé</span>
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="no-projects">
            <h2>Aucun projet disponible</h2>
            <p>Vous avez vu tous les projets! Revenez plus tard ou complétez votre profil pour de meilleures recommandations.</p>
            <a href="index.php?controller=matching&action=profile" class="btn-primary">
                ⚙️ Compléter Mon Profil
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="discover-footer">
    <a href="index.php?controller=matching&action=recommend" class="btn-secondary">
        📊 Vue Liste
    </a>
</div>

<style>
.discover-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 40px;
}

.discover-header h1 {
    margin: 0 0 10px 0;
    font-size: 32px;
}

.discover-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}

.discover-container {
    max-width: 600px;
    margin: 0 auto;
    perspective: 1000px;
}

.swipe-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    overflow: hidden;
    padding: 30px;
    min-height: 600px;
    display: flex;
    flex-direction: column;
    position: relative;
    animation: cardEntry 0.5s ease-out;
}

@keyframes cardEntry {
    from {
        opacity: 0;
        transform: scale(0.95) rotateY(20deg);
    }
    to {
        opacity: 1;
        transform: scale(1) rotateY(0deg);
    }
}

.swipe-badge {
    position: absolute;
    top: 20px;
    right: 20px;
}

.match-percentage {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 16px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.swipe-content {
    flex: 1;
}

.swipe-content h2 {
    margin: 0 0 5px 0;
    font-size: 28px;
    color: #333;
}

.project-ref {
    color: #999;
    font-size: 14px;
}

.project-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
}

.detail-item {
    text-align: center;
}

.detail-label {
    display: block;
    font-size: 12px;
    color: #999;
    margin-bottom: 5px;
}

.detail-value {
    display: block;
    font-size: 18px;
    font-weight: bold;
    color: #667eea;
}

.project-description {
    color: #666;
    line-height: 1.6;
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border-left: 4px solid #667eea;
    border-radius: 4px;
}

.matching-section,
.required-section {
    margin-bottom: 20px;
}

.matching-section h3,
.required-section h3 {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
}

.matched-skills,
.required-skills {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.skill-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
}

.skill-badge.matched {
    background: #e8f5e9;
    border: 1px solid #27ae60;
    color: #27ae60;
}

.skill-badge.missing {
    background: #ffebee;
    border: 1px solid #e74c3c;
    color: #c0392b;
}

.skill-check {
    font-weight: bold;
    font-size: 16px;
}

.skill-name {
    font-weight: 600;
    flex: 1;
}

.skill-category {
    font-size: 12px;
    opacity: 0.7;
}

.swipe-info {
    text-align: center;
    color: #999;
    font-size: 12px;
    margin: 20px 0;
}

.no-skills {
    text-align: center;
    color: #999;
    padding: 15px;
}

.swipe-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
}

.action-icon {
    font-size: 24px;
}

.action-text {
    font-size: 12px;
}

.action-btn.skip {
    background: #f0f0f0;
    color: #999;
}

.action-btn.skip:hover {
    background: #e9ecef;
    transform: translateX(-5px);
}

.action-btn.view {
    background: #f0f0f0;
    color: #333;
}

.action-btn.view:hover {
    background: #e9ecef;
}

.action-btn.interested {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-btn.interested:hover {
    transform: translateX(5px) scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.no-projects {
    text-align: center;
    padding: 60px 20px;
    background: #f9f9f9;
    border-radius: 12px;
}

.no-projects h2 {
    color: #333;
    margin-bottom: 10px;
}

.no-projects p {
    color: #666;
    margin-bottom: 20px;
}

.discover-footer {
    text-align: center;
    margin-top: 40px;
}

.btn-primary, .btn-secondary {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-primary:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: white;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background: #f9f9f9;
}

@media (max-width: 600px) {
    .swipe-card {
        padding: 20px;
    }

    .discover-header h1 {
        font-size: 24px;
    }

    .swipe-actions {
        flex-wrap: wrap;
    }

    .action-btn {
        flex: 1;
    }
}
</style>

<script src="matching.js"></script>
