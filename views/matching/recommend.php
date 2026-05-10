<?php
// Recommend view - uses $projects set by MatchingController
?>

<div class="matching-header" style="background: linear-gradient(135deg, var(--navy) 0%, var(--blue-dark) 100%); color: white; padding: 60px 40px; border-radius: 16px; margin-bottom: 40px; text-align: center; box-shadow: var(--shadow-md);">
    <h1 style="font-family: var(--font-display); font-size: 36px; font-weight: 800; margin: 0 0 15px 0;">
        <i class="fa-solid fa-bullseye" style="color: var(--blue); margin-right: 12px;"></i> Projets Recommandés
    </h1>
    <p style="font-size: 16px; opacity: 0.9; max-width: 600px; margin: 0 auto 30px;">Découvrez les projets qui correspondent le mieux à vos compétences et intérêts</p>
    <div class="header-actions" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="index.php?controller=matching&action=discover" class="btn btn-primary" style="background: var(--grad-primary); border: none; padding: 12px 24px; border-radius: 99px; font-weight: 600; box-shadow: var(--shadow-blue); display: inline-flex; align-items: center; gap: 8px; color: white; text-decoration: none;">
            <i class="fa-solid fa-fire"></i> Mode Découverte (Swipe)
        </a>
        <a href="index.php?controller=matching&action=profile" class="btn btn-secondary" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 99px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
            <i class="fa-solid fa-gear"></i> Mon Profil
        </a>
    </div>
</div>

<div class="matching-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px;">
    <div class="stat-card card card-elevated" style="padding: 30px; border-radius: 16px; text-align: center; background: white;">
        <span class="stat-label" style="display: block; font-size: 13px; color: var(--gray-600); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 15px;">Projets Recommandés</span>
        <span class="stat-value" style="display: block; font-size: 48px; font-weight: 800; color: var(--navy);"><?php echo count($projects ?? []); ?></span>
    </div>
    <div class="stat-card card card-elevated" style="padding: 30px; border-radius: 16px; text-align: center; background: white;">
        <span class="stat-label" style="display: block; font-size: 13px; color: var(--gray-600); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 15px;">Score Moyen</span>
        <span class="stat-value" style="display: block; font-size: 48px; font-weight: 800; color: var(--green-dark);">
            <?php 
            $projects = $projects ?? [];
            $avg_score = count($projects) > 0 ? round(array_sum(array_column($projects, 'match_score')) / count($projects), 1) : 0;
            echo $avg_score . '%';
            ?>
        </span>
    </div>
</div>

<div class="matching-container" style="max-width: 1000px; margin: 0 auto;">
    <?php if (empty($projects)): ?>
        <div class="empty-state card card-elevated" style="text-align: center; padding: 80px 40px; border-radius: 16px; background: white;">
            <i class="fa-solid fa-folder-open" style="font-size: 48px; color: var(--gray-400); margin-bottom: 20px;"></i>
            <h2 style="font-family: var(--font-display); font-size: 24px; color: var(--navy); margin-bottom: 10px;">Aucun projet recommandé pour le moment</h2>
            <p style="color: var(--gray-600); font-size: 16px; margin-bottom: 30px;">Complétez votre profil pour de meilleures recommandations.</p>
            <a href="index.php?controller=matching&action=profile" class="btn btn-primary" style="background: var(--grad-primary); color: white; padding: 12px 28px; border-radius: 99px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: var(--shadow-blue);">
                <i class="fa-solid fa-pen"></i> Compléter Mon Profil
            </a>
        </div>
    <?php else: ?>
        <div class="matching-list" style="display: flex; flex-direction: column; gap: 24px;">
            <?php foreach ($projects as $index => $project): ?>
                <div class="match-list-item card card-elevated" style="display: flex; background: white; border-radius: 16px; overflow: hidden; position: relative; transition: transform 0.2s, box-shadow 0.2s;">
                    
                    <!-- Rank Indicator -->
                    <div style="background: var(--navy); color: white; width: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 10px;">
                        <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; opacity: 0.8; margin-bottom: 4px;">Top</span>
                        <span style="font-size: 28px; font-weight: 800;"><?php echo $index + 1; ?></span>
                    </div>

                    <!-- Main Content -->
                    <div style="flex: 1; padding: 30px; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <div>
                                <span class="ref-number" style="font-size: 12px; font-weight: 600; color: var(--gray-400); letter-spacing: 1px; margin-bottom: 5px; display: block;">#<?php echo $project['num']; ?></span>
                                <h3 style="font-family: var(--font-display); font-size: 26px; font-weight: 700; color: var(--navy); margin: 0; line-height: 1.3;">
                                    <?php echo htmlspecialchars($project['nomprojet']); ?>
                                </h3>
                            </div>
                            
                            <div style="display: flex; gap: 20px;">
                                <div style="text-align: right;">
                                    <span style="display: block; font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--gray-600); margin-bottom: 4px;">Budget</span>
                                    <span style="font-size: 16px; font-weight: 800; color: var(--navy);">
                                        <?php echo number_format($project['budget'] ?? 0); ?> DT
                                    </span>
                                </div>
                                <div style="text-align: right; border-left: 1px solid var(--gray-200); padding-left: 20px;">
                                    <span style="display: block; font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--gray-600); margin-bottom: 4px;">Gain Estimé</span>
                                    <span style="font-size: 16px; font-weight: 800; color: var(--green-dark);">
                                        +<?php echo number_format($project['gain'] ?? 0); ?> DT
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p style="color: var(--gray-600); font-size: 15px; line-height: 1.7; margin-bottom: 25px; overflow-wrap: break-word; word-break: break-word; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            <?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 300)); ?><?php echo strlen($project['description'] ?? '') > 300 ? '...' : ''; ?>
                        </p>
                    </div>

                    <!-- Right Panel (Score & Actions) -->
                    <div style="width: 250px; background: var(--gray-50); padding: 30px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-left: 1px solid var(--gray-200);">
                        <div style="text-align: center; margin-bottom: 25px;">
                            <span style="display: block; font-size: 12px; text-transform: uppercase; font-weight: 700; color: var(--gray-600); letter-spacing: 1px; margin-bottom: 10px;">Correspondance</span>
                            <div style="background: var(--grad-primary); color: white; padding: 12px 24px; border-radius: 99px; font-weight: 800; font-size: 18px; box-shadow: var(--shadow-blue);">
                                <?php echo round($project['match_score']); ?>% Match
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px; width: 100%;">
                            <button class="btn interested-btn" onclick="recordAction(<?php echo $project['id']; ?>, 'interested')" style="background: var(--grad-primary); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-blue); width: 100%;">
                                <i class="fa-regular fa-heart"></i> Intéressé
                            </button>
                            <a href="index.php?controller=projet&action=show&id=<?php echo $project['id']; ?>" class="btn" style="background: var(--blue-light); color: var(--blue-dark); text-align: center; padding: 12px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;">
                                <i class="fa-solid fa-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 50px;">
                <?php if ($current_page > 1): ?>
                    <a href="index.php?controller=matching&action=recommend&page=<?php echo $current_page - 1; ?>" class="btn" style="background: white; border: 1px solid var(--gray-200); color: var(--navy); padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">
                        <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Précédent
                    </a>
                <?php endif; ?>
                
                <span class="page-info" style="font-size: 14px; font-weight: 600; color: var(--gray-600);">
                    Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?>
                </span>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="index.php?controller=matching&action=recommend&page=<?php echo $current_page + 1; ?>" class="btn" style="background: white; border: 1px solid var(--gray-200); color: var(--navy); padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">
                        Suivant <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.interested-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(2, 136, 209, 0.4) !important;
}
.btn-secondary:hover {
    background: rgba(255,255,255,0.2) !important;
}
.match-list-item .btn[href]:hover {
    background: rgba(100, 197, 235, 0.3) !important;
    transform: translateY(-2px);
}
.match-list-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg) !important;
}

/* Make it responsive */
@media (max-width: 900px) {
    .match-list-item {
        flex-direction: column;
    }
    .match-list-item > div:first-child {
        width: 100% !important;
        flex-direction: row !important;
        padding: 10px 20px !important;
        justify-content: flex-start !important;
        gap: 10px;
    }
    .match-list-item > div:first-child span {
        margin: 0 !important;
    }
    .match-list-item > div:nth-child(2) > div:first-child {
        flex-direction: column;
        gap: 15px;
    }
    .match-list-item > div:nth-child(2) > div:first-child > div:nth-child(2) {
        justify-content: flex-start;
    }
    .match-list-item > div:last-child {
        width: 100% !important;
        border-left: none !important;
        border-top: 1px solid var(--gray-200);
        flex-direction: row;
        justify-content: space-between;
    }
    .match-list-item > div:last-child > div:first-child {
        margin-bottom: 0 !important;
        text-align: left !important;
    }
    .match-list-item > div:last-child > div:last-child {
        width: auto !important;
        flex-direction: row;
    }
}
@media (max-width: 600px) {
    .match-list-item > div:last-child {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .match-list-item > div:last-child > div:first-child {
        text-align: center !important;
        margin-bottom: 20px !important;
    }
    .match-list-item > div:last-child > div:last-child {
        flex-direction: column;
    }
}
</style>

<script src="matching.js"></script>
