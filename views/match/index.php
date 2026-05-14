<div class="container" style="padding: 40px 5%; max-width: 1000px;">
    <div class="projet-hero" style="text-align: center;">
        <h1 style="font-size: 3em; margin-bottom: 15px;">🎯 Smart Project Match</h1>
        <p style="font-size: 1.2em; opacity: 0.9;">Découvrez les startups qui correspondent le mieux à vos compétences et intérêts.</p>
    </div>

    <?php if (!$isProfileComplete): ?>
        <div class="card card-elevated" style="border: 2px solid #e74c3c; text-align: center; padding: 40px;">
            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
            <h2 style="color: #e74c3c; margin-bottom: 15px;">Profil Incomplet</h2>
            <p style="font-size: 1.1em; color: var(--gray-600); margin-bottom: 25px;">Vous devez obligatoirement renseigner vos compétences et intérêts pour utiliser le système de matching. Complétez votre profil pour obtenir de meilleures recommandations.</p>
            <a href="index.php?controller=profile&action=index" class="btn btn-primary btn-lg" style="background: #e74c3c; border-color: #e74c3c;">
                Aller sur Mon Profil pour configurer
            </a>
        </div>
    <?php else: ?>
        
        <?php if (empty($matches)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>Aucun projet trouvé</h3>
                <p>Nous n'avons pas de nouveaux projets à vous recommander pour le moment. Revenez plus tard !</p>
                <a href="index.php?controller=profile&action=index" class="btn btn-ghost">Revoir mes préférences</a>
            </div>
        <?php else: ?>
            
            <div class="match-cards-container" id="match-deck" style="position: relative; height: 620px; max-width: 500px; margin: 0 auto;">
                <?php foreach (array_reverse($matches) as $index => $projet): ?>
                    <div class="match-card card card-elevated" data-id="<?php echo $projet['id']; ?>" style="position: absolute; width: 100%; top: 0; left: 0; z-index: <?php echo $index; ?>; background: white; padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 100%; box-shadow: 0 15px 35px rgba(0,0,0,0.15);">
                        
                        <div style="background: linear-gradient(135deg, var(--navy), var(--blue-dark)); padding: 30px; color: white; position: relative;">
                            <div class="badge" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); color: white; font-size: 14px; font-weight: bold; border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(4px);">
                                <?php echo $projet['match_score']; ?>% Match
                            </div>
                            <span class="badge" style="margin-bottom: 15px; display: inline-block; background: rgba(255,255,255,0.2); color: white; border: none; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: var(--radius-full);"><?php echo htmlspecialchars($projet['categorie_nom'] ?? 'Général'); ?></span>
                            <h2 style="font-family: var(--font-display); font-size: 28px; line-height: 1.2; margin: 0 0 10px 0; color: white;"><?php echo htmlspecialchars($projet['nomprojet']); ?></h2>
                        </div>
                        
                        <div class="card-content" style="padding: 30px; flex-grow: 1; display: flex; flex-direction: column;">
                            <p class="project-desc" style="font-size: 15px; color: var(--gray-600); line-height: 1.6; margin-bottom: 25px; flex-grow: 1;"><?php echo htmlspecialchars(substr($projet['description'], 0, 180)) . '...'; ?></p>
                            
                            <div class="skills-section" style="margin-bottom: 25px;">
                                <h4 style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--gray-400); margin-bottom: 12px; font-weight: 700;">Compétences Requises</h4>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php if(empty($projet['required_skills'])): ?>
                                        <span class="badge badge-gray">Aucune compétence spécifique</span>
                                    <?php else: ?>
                                        <?php foreach ($projet['required_skills'] as $reqSkill): ?>
                                            <span class="badge <?php echo in_array($reqSkill['id'], $projet['matching_skills']) ? 'badge-green' : 'badge-gray'; ?>" style="font-size: 13px; padding: 6px 12px;">
                                                <?php if(in_array($reqSkill['id'], $projet['matching_skills'])) echo '✓ '; ?><?php echo htmlspecialchars($reqSkill['nom']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="metrics" style="display: flex; gap: 20px; border-top: 1px solid var(--gray-100); padding-top: 20px; margin-bottom: 20px;">
                                <div class="metric" style="flex: 1;">
                                    <span style="display: block; font-size: 12px; color: var(--gray-400); text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Budget</span>
                                    <span style="font-size: 20px; font-weight: 800; color: var(--navy);"><?php echo number_format($projet['budget'], 0, ',', ' '); ?> DT</span>
                                </div>
                                <div class="metric" style="flex: 1;">
                                    <span style="display: block; font-size: 12px; color: var(--gray-400); text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Popularité</span>
                                    <span style="font-size: 20px; font-weight: 800; color: var(--navy);">🔥 <?php echo (int)($projet['likes_count'] ?? 0); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="card-actions" style="display: flex; gap: 15px; padding: 0 30px 30px 30px;">
                            <button class="btn btn-ghost" style="flex: 1; padding: 15px; font-size: 16px; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-weight: bold; color: var(--gray-600); transition: all 0.2s;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'" onclick="swipeCard('left', this.closest('.match-card'), <?php echo $projet['id']; ?>)">Passer 👎</button>
                            <a href="index.php?controller=projet&action=show&id=<?php echo $projet['id']; ?>" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; padding: 15px; border-radius: var(--radius-md); background: var(--blue-light); color: var(--blue-dark);"><i data-lucide="eye" style="width: 20px; height: 20px;"></i></a>
                            <button class="btn btn-primary" style="flex: 1; padding: 15px; font-size: 16px; border-radius: var(--radius-md); font-weight: bold;" onclick="swipeCard('right', this.closest('.match-card'), <?php echo $projet['id']; ?>)">Intéressé ❤️</button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="end-of-deck empty-state" style="display: none; position: absolute; inset: 0; align-items: center; justify-content: center; flex-direction: column; background: var(--gray-50); border-radius: var(--radius-lg); border: 2px dashed var(--gray-200);">
                    <div style="font-size: 64px; margin-bottom: 20px;">🎉</div>
                    <h3 style="font-family: var(--font-display); font-size: 24px; color: var(--navy); margin-bottom: 10px;">Vous avez tout vu !</h3>
                    <p style="color: var(--gray-500); text-align: center; max-width: 80%;">Revenez plus tard pour de nouveaux projets ou modifiez vos préférences pour élargir vos recommandations.</p>
                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function swipeCard(direction, cardElement, projectId = null) {
    if (projectId) {
        const reactionType = direction === 'right' ? 'LIKE' : 'DISLIKE';
        
        fetch('index.php?controller=projet_reaction&action=toggleProjet', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_projet: projectId, reaction_type: reactionType })
        }).catch(err => console.error("Failed to save swipe:", err));
    }

    cardElement.style.transition = 'transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.4s ease-out';
    cardElement.style.transform = `translateX(${direction === 'left' ? '-' : ''}120%) rotate(${direction === 'left' ? '-' : ''}25deg) scale(0.9)`;
    cardElement.style.opacity = '0';
    
    setTimeout(() => {
        cardElement.remove();
        
        // If it was the last card, we might want to refresh or show the end message
        const remainingCards = document.querySelectorAll('.match-card');
        if (remainingCards.length === 0) {
            const endDeck = document.querySelector('.end-of-deck');
            endDeck.style.display = 'flex';
            endDeck.style.animation = 'fadeIn 0.5s ease-out';
        }
    }, 500);
}
</script>
