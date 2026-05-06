<div class="profile-container" style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    
    <div style="background: linear-gradient(135deg, #0B1C48 0%, #1e3a8a 100%); color: white; border-radius: 16px; padding: 40px; margin-bottom: 30px; display: flex; align-items: center; gap: 30px;">
        <div style="width: 100px; height: 100px; background: rgba(255,255,255,0.2); border: 2px solid white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3em; font-weight: bold;">
            <?php echo strtoupper(substr($userName, 0, 1)); ?>
        </div>
        <div>
            <h1 style="margin: 0 0 10px 0; font-size: 2.5em;"><?php echo htmlspecialchars($userName); ?></h1>
            <p style="margin: 0; opacity: 0.8; font-size: 1.1em;">Gérez vos préférences de matching et vos startups.</p>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c8e6c9;">
            ✅ Préférences mises à jour avec succès !
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Preferences / Skills -->
        <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <?php if (!$isPublicProfile): ?>
                <h2 style="margin-top: 0; color: #0B1C48; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">Préférences de Match</h2>
                <p style="color: #6c757d; font-size: 0.9em; margin-bottom: 20px;">Indiquez vos compétences et intérêts pour recevoir les meilleures recommandations de startups.</p>
                
                <form action="index.php?controller=profile&action=savePreferences" method="POST">
                    
                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 1.1em; color: #2c3e50; margin-bottom: 15px;">Vos Compétences 🛠️</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-height: 200px; overflow-y: auto; padding-right: 10px; border: 1px solid #eee; padding: 15px; border-radius: 8px; background: #fafbfc;">
                            <?php foreach ($allSkills as $skill): ?>
                                <label style="display: flex; align-items: center; cursor: pointer; font-size: 14px; color: #444;">
                                    <input type="checkbox" name="skills[]" value="<?php echo $skill['id']; ?>" <?php echo in_array($skill['id'], $userSkillIds) ? 'checked' : ''; ?> style="margin-right: 8px; width: 16px; height: 16px;">
                                    <?php echo htmlspecialchars($skill['nom']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 1.1em; color: #2c3e50; margin-bottom: 15px;">Vos Intérêts (Secteurs) 🌍</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-height: 200px; overflow-y: auto; padding-right: 10px; border: 1px solid #eee; padding: 15px; border-radius: 8px; background: #fafbfc;">
                            <?php foreach ($allCategories as $cat): ?>
                                <label style="display: flex; align-items: center; cursor: pointer; font-size: 14px; color: #444;">
                                    <input type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $userInterestIds) ? 'checked' : ''; ?> style="margin-right: 8px; width: 16px; height: 16px;">
                                    <?php echo htmlspecialchars($cat['typeprojet']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" style="width: 100%; padding: 12px; background: #0B1C48; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.2s;">Enregistrer mes préférences</button>
                </form>
            <?php else: ?>
                <h2 style="margin-top: 0; color: #0B1C48; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">Compétences</h2>
                <div style="margin-bottom: 25px;">
                    <?php if (empty($userSkills)): ?>
                        <p style="color: #6c757d; font-size: 0.9em;">Cet utilisateur n'a pas encore renseigné de compétences.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px;">
                            <?php foreach ($userSkills as $skill): ?>
                                <span style="background: #e8f4fd; color: #2980b9; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; border: 1px solid #d4e6f1;">
                                    <?php echo htmlspecialchars($skill['nom']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h2 style="margin-top: 30px; color: #0B1C48; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">Intérêts</h2>
                <div>
                    <?php if (empty($userInterests)): ?>
                        <p style="color: #6c757d; font-size: 0.9em;">Cet utilisateur n'a pas encore renseigné d'intérêts.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px;">
                            <?php foreach ($userInterests as $cat): ?>
                                <span style="background: #fdf2e9; color: #d35400; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; border: 1px solid #fae5d3;">
                                    <?php echo htmlspecialchars($cat['typeprojet']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- User's Projects -->
        <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px;">
                <h2 style="margin: 0; color: #0B1C48;"><?php echo $isPublicProfile ? 'Ses Startups' : 'Mes Startups'; ?></h2>
                <?php if (!$isPublicProfile): ?>
                    <a href="index.php?controller=projet&action=create" style="background: #3498db; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;">+ Nouvelle</a>
                <?php endif; ?>
            </div>

            <?php if (empty($userProjects)): ?>
                <div style="text-align: center; padding: 40px 20px; color: #95a5a6; background: #f8f9fa; border-radius: 8px;">
                    <p style="margin-bottom: 15px;"><?php echo $isPublicProfile ? "Cet utilisateur n'a pas encore créé de startup." : "Vous n'avez pas encore créé de startup."; ?></p>
                    <?php if (!$isPublicProfile): ?>
                        <a href="index.php?controller=projet&action=create" style="color: #3498db; font-weight: bold; text-decoration: none;">Lancez-vous maintenant !</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($userProjects as $p): ?>
                        <div style="border: 1px solid #e1e8ed; border-radius: 8px; padding: 15px; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; background: #fff;" onmouseover="this.style.borderColor='#3498db'" onmouseout="this.style.borderColor='#e1e8ed'">
                            <div>
                                <span style="font-size: 11px; background: #eef2f7; color: #0B1C48; padding: 3px 8px; border-radius: 12px; font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars($p['categorie_nom'] ?? 'Général'); ?></span>
                                <h3 style="margin: 8px 0 5px 0; font-size: 1.1em;"><a href="index.php?controller=projet&action=show&id=<?php echo $p['id']; ?>" style="color: #2c3e50; text-decoration: none;"><?php echo htmlspecialchars($p['nomprojet']); ?></a></h3>
                                <div style="font-size: 12px; color: #7f8c8d;">
                                    Statut: <strong><?php echo htmlspecialchars($p['statut']); ?></strong> | 🔥 <?php echo (int)($p['likes_count'] ?? 0); ?> likes
                                </div>
                            </div>
                            <?php if (!$isPublicProfile): ?>
                                <div style="display: flex; gap: 8px;">
                                    <a href="index.php?controller=projet&action=edit&id=<?php echo $p['id']; ?>" style="background: #f1f3f5; color: #495057; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; transition: 0.2s;">Modifier</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
