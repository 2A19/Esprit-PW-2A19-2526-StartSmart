<div class="container" style="padding: 40px 5%;">
    
    <div class="projet-hero" style="display: flex; align-items: center; gap: 30px;">
        <div style="width: 100px; height: 100px; background: rgba(255,255,255,0.2); border: 2px solid white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3em; font-weight: bold; position: relative; z-index: 1;">
            <?php echo strtoupper(substr($userName, 0, 1)); ?>
        </div>
        <div style="position: relative; z-index: 1;">
            <h1><?php echo htmlspecialchars($userName); ?></h1>
            <p style="margin: 0; opacity: 0.8; font-size: 1.1em;">Gérez vos préférences de matching et vos startups.</p>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="background: var(--green-bg); color: var(--green-dark); padding: 15px; border-radius: var(--radius-sm); margin-bottom: 24px; border: 1px solid var(--green-glow); font-weight: 600;">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; vertical-align: middle; margin-right: 8px;"></i> Préférences mises à jour avec succès !
        </div>
    <?php endif; ?>

    <!-- PERFORMANCE STATS & CHARTS -->
    <div class="grid-3" style="margin-bottom: 30px;">
        
        <!-- Performance Score -->
        <div class="card card-elevated" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
            <h3 class="heading-md" style="margin: 0 0 15px 0; color: var(--navy);">Readiness Score</h3>
            <div style="position: relative; width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(var(--green) <?php echo rand(65, 95); ?>%, var(--gray-200) 0); display: flex; align-items: center; justify-content: center;">
                <div style="position: absolute; width: 100px; height: 100px; background: white; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span style="font-size: 24px; font-weight: 800; color: var(--navy);">85%</span>
                    <span style="font-size: 10px; color: var(--gray-400); font-weight: bold;">EXCELLENT</span>
                </div>
            </div>
            <p style="margin: 15px 0 0 0; font-size: 12px; color: var(--gray-500);">Votre profil est très attractif pour les investisseurs.</p>
        </div>

        <!-- Growth Chart -->
        <div class="card card-elevated">
            <h3 class="heading-md" style="margin: 0 0 10px 0; color: var(--navy);">Croissance d'Audience</h3>
            <div style="position: relative; height: 180px; width: 100%;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <!-- Activity Chart -->
        <div class="card card-elevated">
            <h3 class="heading-md" style="margin: 0 0 10px 0; color: var(--navy);">Activité Forum</h3>
            <div style="position: relative; height: 180px; width: 100%;">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Growth Chart (Line)
            new Chart(document.getElementById('growthChart'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                    datasets: [{
                        label: 'Vues Profil',
                        data: [12, 19, 35, 50, 42, 85],
                        borderColor: '#4FC3F7',
                        backgroundColor: 'rgba(79,195,247,0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            // Activity Chart (Bar)
            new Chart(document.getElementById('activityChart'), {
                type: 'bar',
                data: {
                    labels: ['Posts', 'Comments', 'Likes', 'Saves'],
                    datasets: [{
                        label: 'Activité',
                        data: [5, 23, 45, 12],
                        backgroundColor: ['#2ecc71', '#3498db', '#f1c40f', '#e74c3c'],
                        borderRadius: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        });
    </script>

    <div class="grid-2">
        
        <!-- Preferences / Skills -->
        <div class="card card-elevated">
            <?php if (!$isPublicProfile): ?>
                <h2 class="heading-md" style="margin-top: 0; color: var(--navy); border-bottom: 2px solid var(--gray-100); padding-bottom: 10px;">Préférences de Match</h2>
                <p style="color: var(--gray-500); font-size: 0.9em; margin-bottom: 20px;">Indiquez vos compétences et intérêts pour recevoir les meilleures recommandations de startups.</p>
                
                <form action="index.php?controller=profile&action=savePreferences" method="POST">
                    
                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 1.1em; color: var(--gray-800); margin-bottom: 15px;">Vos Compétences 🛠️</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-height: 200px; overflow-y: auto; padding-right: 10px; border: 1px solid var(--gray-200); padding: 15px; border-radius: var(--radius-sm); background: var(--gray-50);">
                            <?php foreach ($allSkills as $skill): ?>
                                <label style="display: flex; align-items: center; cursor: pointer; font-size: 14px; color: var(--gray-700);">
                                    <input type="checkbox" name="skills[]" value="<?php echo $skill['id']; ?>" <?php echo in_array($skill['id'], $userSkillIds) ? 'checked' : ''; ?> style="margin-right: 8px; width: 16px; height: 16px;">
                                    <?php echo htmlspecialchars($skill['nom']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 1.1em; color: var(--gray-800); margin-bottom: 15px;">Vos Intérêts (Secteurs) 🌍</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-height: 200px; overflow-y: auto; padding-right: 10px; border: 1px solid var(--gray-200); padding: 15px; border-radius: var(--radius-sm); background: var(--gray-50);">
                            <?php foreach ($allCategories as $cat): ?>
                                <label style="display: flex; align-items: center; cursor: pointer; font-size: 14px; color: var(--gray-700);">
                                    <input type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $userInterestIds) ? 'checked' : ''; ?> style="margin-right: 8px; width: 16px; height: 16px;">
                                    <?php echo htmlspecialchars($cat['typeprojet']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full btn-lg">Enregistrer mes préférences</button>
                </form>
            <?php else: ?>
                <h2 class="heading-md" style="margin-top: 0; color: var(--navy); border-bottom: 2px solid var(--gray-100); padding-bottom: 10px;">Compétences</h2>
                <div style="margin-bottom: 25px;">
                    <?php if (empty($userSkills)): ?>
                        <p style="color: var(--gray-500); font-size: 0.9em;">Cet utilisateur n'a pas encore renseigné de compétences.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px;">
                            <?php foreach ($userSkills as $skill): ?>
                                <span class="badge badge-blue">
                                    <?php echo htmlspecialchars($skill['nom']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h2 class="heading-md" style="margin-top: 30px; color: var(--navy); border-bottom: 2px solid var(--gray-100); padding-bottom: 10px;">Intérêts</h2>
                <div>
                    <?php if (empty($userInterests)): ?>
                        <p style="color: var(--gray-500); font-size: 0.9em;">Cet utilisateur n'a pas encore renseigné d'intérêts.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px;">
                            <?php foreach ($userInterests as $cat): ?>
                                <span class="badge badge-amber">
                                    <?php echo htmlspecialchars($cat['typeprojet']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- User's Projects -->
        <div class="card card-elevated">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--gray-100); padding-bottom: 10px; margin-bottom: 20px;">
                <h2 class="heading-md" style="margin: 0; color: var(--navy);"><?php echo $isPublicProfile ? 'Ses Startups' : 'Mes Startups'; ?></h2>
                <?php if (!$isPublicProfile): ?>
                    <a href="index.php?controller=projet&action=create" class="btn btn-primary btn-sm">+ Nouvelle</a>
                <?php endif; ?>
            </div>

            <?php if (empty($userProjects)): ?>
                <div class="empty-state">
                    <p style="margin-bottom: 15px;"><?php echo $isPublicProfile ? "Cet utilisateur n'a pas encore créé de startup." : "Vous n'avez pas encore créé de startup."; ?></p>
                    <?php if (!$isPublicProfile): ?>
                        <a href="index.php?controller=projet&action=create" class="btn btn-ghost btn-sm">Lancez-vous maintenant !</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($userProjects as $p): ?>
                        <div class="card card-hover card-flat" style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span class="badge badge-navy" style="margin-bottom: 6px;"><?php echo htmlspecialchars($p['categorie_nom'] ?? 'Général'); ?></span>
                                <h3 style="margin: 0 0 5px 0; font-size: 1.1em;"><a href="index.php?controller=projet&action=show&id=<?php echo $p['id']; ?>" class="link-animated"><?php echo htmlspecialchars($p['nomprojet']); ?></a></h3>
                                <div style="font-size: 12px; color: var(--gray-500);">
                                    Statut: <strong><?php echo htmlspecialchars($p['statut']); ?></strong> | 🔥 <?php echo (int)($p['likes_count'] ?? 0); ?> likes
                                </div>
                            </div>
                            <?php if (!$isPublicProfile): ?>
                                <div style="display: flex; gap: 8px;">
                                    <a href="index.php?controller=projet&action=edit&id=<?php echo $p['id']; ?>" class="btn btn-ghost btn-sm">Modifier</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
