<?php
// Discover view - uses $project set by MatchingController
?>

<div class="discover-header" style="background: linear-gradient(135deg, var(--navy) 0%, var(--blue-dark) 100%); color: white; padding: 60px 40px; border-radius: 16px; text-align: center; margin-bottom: 40px; box-shadow: var(--shadow-md);">
    <h1 style="font-size: 36px; margin: 0 0 10px 0; font-weight: 800;">
        <i class="fa-solid fa-fire" style="color: var(--blue); margin-right: 12px;"></i>Découvrez les Projets
    </h1>
    <p style="font-size: 16px; opacity: 0.9;">Trouvez les projets qui correspondent à vos compétences et intérêts</p>
</div>

<div class="discover-container" style="max-width: 450px; margin: 0 auto; perspective: 1000px; padding: 0 15px;">
    <?php if (isset($project) && !empty($project)): ?>
        <div class="swipe-card card card-elevated" id="swipeCard" data-project-id="<?php echo $project['id']; ?>" style="--score: <?php echo $project['match_score']; ?>; position: relative; padding: 25px; display: flex; flex-direction: column; min-height: 400px; background: white; border-radius: 20px; box-shadow: var(--shadow-lg);">
            
            <div class="swipe-badge" style="position: absolute; top: 20px; right: 20px;">
                <div class="match-percentage" style="background: var(--grad-primary); color: white; padding: 8px 16px; border-radius: 99px; font-weight: 800; font-size: 15px; box-shadow: var(--shadow-blue);">
                    <?php echo round($project['match_score']); ?>% Match
                </div>
            </div>

            <div class="swipe-content" style="flex: 1; margin-top: 10px;">
                <h2 style="font-size: 32px; color: var(--navy); margin: 0 0 5px 0; font-weight: 800;"><?php echo htmlspecialchars($project['nomprojet']); ?></h2>
                <span class="project-ref" style="color: var(--gray-400); font-weight: 600; font-size: 14px; letter-spacing: 1px;">#<?php echo $project['num'] ?? str_pad($project['id'], 4, '0', STR_PAD_LEFT); ?></span>

                <div class="project-details" style="display: flex; justify-content: space-between; margin: 20px 0; padding: 15px; background: var(--gray-50); border-radius: 12px; border: 1px solid var(--gray-200);">
                    <div class="detail-item" style="text-align: center; flex: 1;">
                        <span class="detail-label" style="display: block; font-size: 10px; color: var(--gray-600); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 5px;">Budget</span>
                        <span class="detail-value" style="display: block; font-size: 16px; font-weight: 800; color: var(--navy);">
                            <i class="fa-solid fa-coins" style="color: var(--gray-400); margin-right: 4px;"></i> <?php echo number_format($project['budget'] ?? 0); ?> DT
                        </span>
                    </div>
                    <div class="detail-item" style="text-align: center; flex: 1; border-left: 1px solid var(--gray-200);">
                        <span class="detail-label" style="display: block; font-size: 10px; color: var(--gray-600); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 5px;">Gain</span>
                        <span class="detail-value" style="display: block; font-size: 16px; font-weight: 800; color: var(--green-dark);">
                            <i class="fa-solid fa-arrow-trend-up" style="margin-right: 4px;"></i> +<?php echo number_format($project['gain'] ?? 0); ?> DT
                        </span>
                    </div>
                </div>

                <p class="project-description" style="color: var(--gray-600); line-height: 1.6; font-size: 14px; margin: 15px 0 20px; padding: 15px; background: rgba(100, 197, 235, 0.05); border-left: 3px solid var(--blue); border-radius: 0 8px 8px 0; overflow-wrap: break-word; word-break: break-word;">
                    <?php echo htmlspecialchars(substr($project['description'] ?? 'Pas de description', 0, 150)); ?><?php echo strlen($project['description'] ?? '') > 150 ? '...' : ''; ?>
                </p>

                <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 20px;">
                    <div class="matching-section">
                        <h3 style="font-size: 11px; font-weight: 700; color: var(--gray-600); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-check-circle" style="color: var(--green-dark);"></i> Correspondantes
                        </h3>
                        <div class="matched-skills" style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php if (!empty($project['matched_skills'])): ?>
                                <?php foreach ($project['matched_skills'] as $skill): ?>
                                    <div class="skill-badge matched" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: rgba(46,204,113,0.1); border: 1px solid rgba(46,204,113,0.3); color: var(--green-dark); border-radius: 99px; font-weight: 600; font-size: 12px;">
                                        <span class="skill-name"><?php echo htmlspecialchars($skill['name'] ?? ''); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-skills" style="color: var(--gray-400); font-size: 13px; font-style: italic;">Aucune compétence correspondante</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="required-section">
                        <h3 style="font-size: 11px; font-weight: 700; color: var(--gray-600); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-list-ul"></i> Requises
                        </h3>
                        <div class="required-skills" style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php if (!empty($project['required_skills'])): ?>
                                <?php foreach ($project['required_skills'] as $skill): ?>
                                    <?php 
                                    $is_matched = !empty($project['matched_skills']) && in_array($skill['id'], array_column($project['matched_skills'], 'id'));
                                    ?>
                                    <div class="skill-badge <?php echo $is_matched ? 'matched' : 'missing'; ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 99px; font-weight: 600; font-size: 12px; <?php echo $is_matched ? 'background: rgba(46,204,113,0.1); border: 1px solid rgba(46,204,113,0.3); color: var(--green-dark);' : 'background: #fff0f0; border: 1px solid #ffcccc; color: #c0392b;'; ?>">
                                        <span class="skill-name"><?php echo htmlspecialchars($skill['name'] ?? ''); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-skills" style="color: var(--gray-400); font-size: 13px; font-style: italic;">Aucune compétence requise</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="swipe-info" style="text-align: center; color: var(--gray-400); font-size: 13px; margin: 30px 0 15px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                    Exprimez votre intérêt ou passez
                </div>
            </div>

            <div class="swipe-actions" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; padding-top: 20px; border-top: 1px solid var(--gray-200);">
                <button class="action-btn skip" onclick="skipProject()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 15px; background: var(--gray-100); color: var(--gray-600); border: none; border-radius: 12px; cursor: pointer; transition: all 0.2s; font-weight: 700;">
                    <span class="action-icon" style="font-size: 20px;"><i class="fa-solid fa-xmark"></i></span>
                    <span class="action-text" style="font-size: 14px;">Passer</span>
                </button>
                <a href="index.php?controller=projet&action=show&id=<?php echo $project['id']; ?>" class="action-btn view" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 15px; background: var(--blue-light); color: var(--blue-dark); text-decoration: none; border-radius: 12px; transition: all 0.2s; font-weight: 700;">
                    <span class="action-icon" style="font-size: 20px;"><i class="fa-solid fa-eye"></i></span>
                    <span class="action-text" style="font-size: 14px;">Détails</span>
                </a>
                <button class="action-btn interested" onclick="interestedProject()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 15px; background: var(--grad-primary); color: white; border: none; border-radius: 12px; cursor: pointer; transition: all 0.2s; font-weight: 700; box-shadow: var(--shadow-blue);">
                    <span class="action-icon" style="font-size: 20px;"><i class="fa-solid fa-heart"></i></span>
                    <span class="action-text" style="font-size: 14px;">Intéressé</span>
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="no-projects card card-elevated" style="text-align: center; padding: 80px 40px; background: white; border-radius: 16px;">
            <i class="fa-solid fa-folder-open" style="font-size: 48px; color: var(--gray-400); margin-bottom: 20px;"></i>
            <h2 style="font-size: 28px; color: var(--navy); margin-bottom: 15px; font-weight: 800;">Aucun projet à afficher</h2>
            <p style="color: var(--gray-600); margin-bottom: 30px; font-size: 16px; line-height: 1.6;">Complétez votre profil avec vos compétences et intérêts pour voir les projets recommandés.</p>
            <a href="index.php?controller=matching&action=profile" class="btn btn-primary" style="background: var(--grad-primary); padding: 14px 28px; font-weight: 600; border-radius: 99px; color: white; text-decoration: none; font-size: 15px; box-shadow: var(--shadow-blue); display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-pen"></i> Compléter Mon Profil
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="discover-footer" style="text-align: center; margin-top: 50px; padding-bottom: 50px;">
    <a href="index.php?controller=matching&action=recommend" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 10px; background: white; color: var(--navy); border: 1px solid var(--gray-200); padding: 12px 28px; border-radius: 99px; font-weight: 700; text-decoration: none; transition: all 0.2s; font-size: 15px; box-shadow: var(--shadow-sm);">
        <i class="fa-solid fa-list"></i> Vue Liste (Recommandations)
    </a>
</div>

<style>
.swipe-card {
    animation: cardEntry 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes cardEntry {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(40px) rotate(-2deg);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0) rotate(0deg);
    }
}

.action-btn.skip:hover { background: var(--gray-200) !important; transform: translateY(-3px); }
.action-btn.view:hover { background: rgba(100, 197, 235, 0.3) !important; transform: translateY(-3px); }
.action-btn.interested:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(2, 136, 209, 0.4) !important; }

@media (max-width: 600px) {
    .swipe-card { padding: 20px !important; }
    .project-details { flex-direction: column; gap: 15px; }
    .project-details .detail-item:last-child { border-left: none !important; padding-left: 0 !important; border-top: 1px solid var(--gray-200); padding-top: 15px; }
    .swipe-actions { gap: 10px !important; }
    .action-btn { padding: 10px !important; }
    .swipe-content > div:nth-child(4) { grid-template-columns: 1fr !important; gap: 20px !important; }
}
</style>

<script src="matching.js?v=<?php echo time(); ?>"></script>
