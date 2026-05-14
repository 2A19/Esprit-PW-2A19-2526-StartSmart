<div class="profile-header">
    <h1>⚙️ Mon Profil de Correspondance</h1>
    <p>Gérez vos compétences et vos intérêts pour de meilleures recommandations</p>
</div>

<div class="profile-container">
    <div class="profile-sidebar">
        <div class="stats-box">
            <h3>Statistiques</h3>
            <div class="stat">
                <span class="stat-label">Compétences</span>
                <span class="stat-count" id="skillCount"><?php echo count($user_skills); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Intérêts</span>
                <span class="stat-count" id="interestCount"><?php echo count($user_interests); ?></span>
            </div>
        </div>
    </div>

    <div class="profile-main">
        <!-- Skills Section -->
        <section class="profile-section">
            <h2>🛠️ Mes Compétences</h2>
            <p class="section-description">Ajoutez les compétences que vous maîtrisez pour être mieux matché avec les projets</p>

            <div class="skills-management">
                <div class="current-skills">
                    <h3>Compétences Actuelles</h3>
                    <div class="skills-list" id="userSkillsList">
                        <?php if (!empty($user_skills)): ?>
                            <?php foreach ($user_skills as $skill): ?>
                                <div class="skill-item" data-skill-id="<?php echo $skill['skill_id']; ?>">
                                    <div class="skill-info">
                                        <span class="skill-name"><?php echo htmlspecialchars($skill['name'] ?? ''); ?></span>
                                        <span class="skill-category"><?php echo htmlspecialchars($skill['category'] ?? ''); ?></span>
                                        <span class="skill-proficiency">
                                            <?php 
                                            $levels = ['beginner' => '📘 Débutant', 'intermediate' => '📗 Intermédiaire', 'expert' => '📕 Expert'];
                                            echo $levels[$skill['proficiency'] ?? 'beginner'];
                                            ?>
                                        </span>
                                    </div>
                                    <button class="remove-btn" onclick="removeSkill(<?php echo $skill['skill_id']; ?>)">✕</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-message">Aucune compétence ajoutée pour le moment</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="add-skills">
                    <h3>Ajouter une Compétence</h3>
                    <div class="skills-by-category">
                        <?php 
                        $user_skill_ids = array_column($user_skills, 'skill_id');
                        $grouped = [];
                        foreach ($all_skills as $skill) {
                            if (!isset($grouped[$skill['category']])) {
                                $grouped[$skill['category']] = [];
                            }
                            $grouped[$skill['category']][] = $skill;
                        }
                        ksort($grouped);
                        ?>

                        <?php foreach ($grouped as $category => $category_skills): ?>
                            <div class="category-group">
                                <h4><?php echo ucfirst($category); ?></h4>
                                <div class="skill-buttons">
                                    <?php foreach ($category_skills as $skill): ?>
                                        <?php if (!in_array($skill['id'], $user_skill_ids)): ?>
                                            <button class="skill-btn" onclick="addSkill(<?php echo $skill['id']; ?>, '<?php echo htmlspecialchars($skill['name']); ?>')">
                                                + <?php echo htmlspecialchars($skill['name']); ?>
                                            </button>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interests Section -->
        <section class="profile-section">
            <h2>❤️ Mes Intérêts</h2>
            <p class="section-description">Sélectionnez les catégories de projets qui vous intéressent</p>

            <div class="interests-management">
                <div class="current-interests">
                    <h3>Intérêts Actuels</h3>
                    <div class="interests-list" id="userInterestsList">
                        <?php if (!empty($user_interests)): ?>
                            <?php foreach ($user_interests as $interest): ?>
                                <div class="interest-item" data-category-id="<?php echo $interest['categorie_id']; ?>">
                                    <div class="interest-info">
                                        <span class="interest-name"><?php echo htmlspecialchars($interest['typeprojet'] ?? ''); ?></span>
                                        <div class="interest-score">
                                            <?php 
                                            $score = isset($interest['interest_level']) ? ceil($interest['interest_level'] / 2) : 3;
                                            for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= $score ? 'filled' : ''; ?>" 
                                                      onclick="updateInterestScore(<?php echo $interest['categorie_id']; ?>, <?php echo $i * 2; ?>)">★</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <button class="remove-btn" onclick="removeInterest(<?php echo $interest['categorie_id']; ?>)">✕</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-message">Aucun intérêt sélectionné pour le moment</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="add-interests">
                    <h3>Ajouter un Intérêt</h3>
                    <div class="interest-buttons">
                        <?php 
                        $user_category_ids = array_column($user_interests, 'categorie_id');
                        foreach ($all_categories as $category):
                            if (!in_array($category['id'], $user_category_ids)):
                        ?>
                            <button class="interest-btn" onclick="addInterest(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['typeprojet']); ?>')">
                                + <?php echo htmlspecialchars($category['typeprojet']); ?>
                            </button>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Action Buttons -->
        <section class="profile-actions">
            <a href="index.php?controller=matching&action=recommend" class="btn-primary">
                ✨ Voir Recommandations
            </a>
            <a href="index.php?controller=matching&action=discover" class="btn-secondary">
                🎲 Mode Découverte
            </a>
        </section>
    </div>
</div>

<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 40px;
    text-align: center;
}

.profile-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.profile-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}

.profile-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 30px;
}

.profile-sidebar {
    height: fit-content;
}

.stats-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stats-box h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #333;
}

.stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.stat:last-child {
    border-bottom: none;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.stat-count {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
}

.profile-main {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 30px;
}

.profile-section {
    margin-bottom: 40px;
    padding-bottom: 40px;
    border-bottom: 1px solid #f0f0f0;
}

.profile-section:last-of-type {
    border-bottom: none;
}

.profile-section h2 {
    margin: 0 0 10px 0;
    font-size: 22px;
    color: #333;
}

.section-description {
    color: #999;
    font-size: 14px;
    margin-bottom: 20px;
}

.skills-management,
.interests-management {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.current-skills,
.current-interests,
.add-skills,
.add-interests {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
}

.current-skills h3,
.current-interests h3,
.add-skills h3,
.add-interests h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #333;
}

.skills-list,
.interests-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.skill-item,
.interest-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.skill-info,
.interest-info {
    flex: 1;
}

.skill-name,
.interest-name {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.skill-category {
    display: block;
    font-size: 12px;
    color: #999;
    margin-bottom: 4px;
}

.skill-proficiency {
    display: block;
    font-size: 12px;
    color: #667eea;
    font-weight: 500;
}

.interest-score {
    display: flex;
    gap: 4px;
    margin-top: 4px;
}

.star {
    cursor: pointer;
    font-size: 16px;
    color: #ddd;
    transition: all 0.2s;
}

.star.filled {
    color: #ffc107;
}

.star:hover {
    transform: scale(1.2);
}

.remove-btn {
    background: #ffebee;
    color: #e74c3c;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    flex-shrink: 0;
    margin-left: 10px;
}

.remove-btn:hover {
    background: #e74c3c;
    color: white;
}

.empty-message {
    text-align: center;
    color: #999;
    padding: 20px;
}

.skills-by-category,
.interest-buttons {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.category-group h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.skill-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.interest-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.skill-btn,
.interest-btn {
    background: white;
    border: 1px solid #667eea;
    color: #667eea;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s;
}

.skill-btn:hover,
.interest-btn:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

.profile-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

.btn-primary,
.btn-secondary {
    flex: 1;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
}

.btn-secondary:hover {
    background: #667eea;
    color: white;
}

@media (max-width: 900px) {
    .profile-container {
        grid-template-columns: 1fr;
    }

    .profile-sidebar {
        grid-column: 1;
    }

    .skills-management,
    .interests-management {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .profile-header h1 {
        font-size: 22px;
    }

    .profile-main {
        padding: 20px;
    }

    .profile-actions {
        flex-direction: column;
    }
}
</style>

<script src="matching.js"></script>
