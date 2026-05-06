<div class="match-container">
    <div class="match-header">
        <h1>🎯 Smart Project Match</h1>
        <p>Découvrez les startups qui correspondent le mieux à vos compétences et intérêts.</p>
    </div>

    <?php if (!$isProfileComplete): ?>
        <div class="setup-profile-card" style="border: 2px solid #e74c3c;">
            <h2 style="color: #e74c3c;">⚠️ Profil Incomplet</h2>
            <p style="font-size: 1.1em; font-weight: bold;">Complete your profile to get better matches. Vous devez obligatoirement renseigner vos compétences et intérêts pour utiliser le système de matching.</p>
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php?controller=profile&action=index" class="btn-primary" style="background: #e74c3c; padding: 15px 30px; font-size: 1.1em; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold;">
                    Aller sur Mon Profil pour configurer
                </a>
            </div>
        </div>
    <?php else: ?>
        
        <?php if (empty($matches)): ?>
            <div class="no-matches">
                <h3>Aucun projet trouvé</h3>
                <p>Nous n'avons pas de nouveaux projets à vous recommander pour le moment. Revenez plus tard !</p>
                <a href="index.php?controller=profile&action=index" class="btn-secondary" style="display: inline-block; text-decoration: none; padding: 10px 20px; border-radius: 6px; background: #eef2f7; color: #0B1C48; font-weight: bold;">Configurer mon profil</a>
            </div>
        <?php else: ?>
            
            <div class="match-cards-container" id="match-deck">
                <?php foreach (array_reverse($matches) as $index => $projet): ?>
                    <div class="match-card" data-id="<?php echo $projet['id']; ?>" style="z-index: <?php echo $index; ?>;">
                        <div class="match-score-badge <?php echo $projet['match_score'] >= 80 ? 'high-match' : ($projet['match_score'] >= 50 ? 'med-match' : 'low-match'); ?>">
                            <?php echo $projet['match_score']; ?>% Match
                        </div>
                        
                        <div class="card-content">
                            <span class="category-tag"><?php echo htmlspecialchars($projet['categorie_nom'] ?? 'Général'); ?></span>
                            <h2><?php echo htmlspecialchars($projet['nomprojet']); ?></h2>
                            <p class="project-desc"><?php echo htmlspecialchars(substr($projet['description'], 0, 150)) . '...'; ?></p>
                            
                            <div class="skills-section">
                                <h4>Compétences Requises :</h4>
                                <div class="skills-tags">
                                    <?php if(empty($projet['required_skills'])): ?>
                                        <span class="skill-tag">Aucune compétence spécifique</span>
                                    <?php else: ?>
                                        <?php foreach ($projet['required_skills'] as $reqSkill): ?>
                                            <span class="skill-tag <?php echo in_array($reqSkill['id'], $projet['matching_skills']) ? 'matching-skill' : ''; ?>">
                                                <?php echo htmlspecialchars($reqSkill['nom']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="metrics">
                                <div class="metric">
                                    <span class="label">Budget</span>
                                    <span class="value"><?php echo number_format($projet['budget'], 0, ',', ' '); ?> DT</span>
                                </div>
                                <div class="metric">
                                    <span class="label">Popularité</span>
                                    <span class="value">🔥 <?php echo (int)($projet['likes_count'] ?? 0); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="card-actions">
                            <button class="btn-pass" onclick="swipeCard('left', this.closest('.match-card'), <?php echo $projet['id']; ?>)">Passer</button>
                            <a href="index.php?controller=projet&action=show&id=<?php echo $projet['id']; ?>" class="btn-explore">Explorer</a>
                            <button class="btn-like" onclick="swipeCard('right', this.closest('.match-card'), <?php echo $projet['id']; ?>)">Intéressé</button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="end-of-deck">
                    <h3>Vous avez tout vu !</h3>
                    <p>Revenez plus tard pour de nouveaux projets.</p>
                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="match.css">
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

    cardElement.style.transition = 'transform 0.5s ease-out, opacity 0.5s ease-out';
    cardElement.style.transform = `translateX(${direction === 'left' ? '-' : ''}1000px) rotate(${direction === 'left' ? '-' : ''}20deg)`;
    cardElement.style.opacity = '0';
    
    setTimeout(() => {
        cardElement.remove();
        
        // If it was the last card, we might want to refresh or show the end message
        const remainingCards = document.querySelectorAll('.match-card');
        if (remainingCards.length === 0) {
            document.querySelector('.end-of-deck').style.display = 'block';
        }
    }, 500);
}
</script>
