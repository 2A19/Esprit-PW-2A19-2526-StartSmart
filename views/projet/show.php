<div class="projet-detail-container" style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="index.php?controller=projet&action=index" style="color: #3498db; text-decoration: none; font-weight: bold;">← Retour aux Startups</a>
    </div>

    <!-- Hero Section -->
    <div class="projet-hero" style="background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; border-radius: 16px; padding: 40px; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
        <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="flex: 1;">
                <span style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); padding: 5px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    <?php echo htmlspecialchars($this->projet->categorie_nom ?? 'Général'); ?>
                </span>
                <h1 style="margin: 20px 0 10px 0; font-size: 3em; font-weight: 800; line-height: 1.1; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    <?php echo htmlspecialchars($this->projet->nomprojet); ?>
                </h1>
                <p style="margin: 0; font-size: 1.1em; opacity: 0.8; font-family: monospace;">Ref: <?php echo htmlspecialchars($this->projet->num); ?></p>
                <div style="margin-top: 15px; display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); padding: 5px 15px 5px 5px; border-radius: 30px; cursor: pointer;" onclick="window.location.href='index.php?controller=profile&action=index&id=<?php echo $this->projet->auteur_id; ?>';" title="Voir le profil">
                    <div style="width: 35px; height: 35px; background: white; color: #1e3c72; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em;">
                        <?php echo strtoupper(substr($this->projet->auteur_nom ?? 'U', 0, 1)); ?>
                    </div>
                    <span style="font-weight: bold; font-size: 1.1em;"><?php echo htmlspecialchars($this->projet->auteur_nom ?? 'Utilisateur'); ?></span>
                </div>
            </div>
            
            <div style="text-align: right; margin-left: 20px;">
                <?php if ((function_exists('currentUserId') && currentUserId() === (int)$this->projet->auteur_id) || (function_exists('isAdmin') && isAdmin())): ?>
                    <div style="background: rgba(0,0,0,0.3); padding: 15px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px; backdrop-filter: blur(5px);">
                        <a href="index.php?controller=projet&action=edit&id=<?php echo $this->projet->id; ?>" style="background: rgba(255,255,255,0.9); color: #1e3c72; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: bold; text-align: center; transition: all 0.2s; display:inline-flex; align-items:center; justify-content:center; gap:5px;"><i data-lucide="edit-3" style="width:16px;height:16px;"></i> Modifier le Pitch</a>
                        <?php if(function_exists('isAdmin') && isAdmin()): ?>
                            <a href="index.php?controller=projet&action=delete&id=<?php echo $this->projet->id; ?>" style="background: rgba(231, 76, 60, 0.9); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: bold; text-align: center; transition: all 0.2s; display:inline-flex; align-items:center; justify-content:center; gap:5px;" onclick="return confirm('Supprimer définitivement ce projet ?');"><i data-lucide="trash-2" style="width:16px;height:16px;"></i> Supprimer</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Decorative background circle -->
        <div style="position: absolute; top: -50px; right: -50px; width: 250px; height: 250px; background: rgba(255,255,255,0.1); border-radius: 50%; z-index: 1;"></div>
    </div>

    <!-- Main Content Layout -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 40px;">
        
        <!-- Left Column: The Pitch -->
        <div>
            <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <h3 style="margin-top: 0; font-size: 1.5em; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; color: #2c3e50;">Le Pitch</h3>
                
                <?php if (!empty($this->projet->competences)): ?>
                    <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed #ecf0f1;">
                        <h4 style="margin: 0 0 10px 0; font-size: 1em; color: #7f8c8d; text-transform: uppercase;">Compétences / Profils Recherchés :</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php foreach ($this->projet->competences as $comp): ?>
                                <span style="background: #e8f4fd; color: #2980b9; font-size: 13px; padding: 6px 12px; border-radius: 6px; font-weight: bold; border: 1px solid #d4e6f1;">
                                    <?php echo htmlspecialchars($comp['nom']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="font-size: 1.15em; line-height: 2; color: #34495e; white-space: pre-wrap; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; letter-spacing: 0.2px;"><?php echo htmlspecialchars($this->projet->description ?? 'Aucune description fournie.'); ?></div>
            </div>

            <!-- Reactions Section -->
            <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; font-size: 1.3em; color: #2c3e50; text-align: center;">Soutenez ce projet</h3>
                <div class="reactions-buttons" style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                    <div class="reaction-large <?php echo (($this->projet->current_user_reaction ?? null) === 'LIKE') ? 'liked' : ''; ?>" id="btnLike_<?php echo $this->projet->id; ?>" onclick="toggleProjetReaction(<?php echo $this->projet->id; ?>, 'LIKE')" style="cursor: pointer; padding: 15px 30px; border-radius: 12px; border: 2px solid #e1e8ed; text-align: center; transition: all 0.2s; background: <?php echo (($this->projet->current_user_reaction ?? null) === 'LIKE') ? '#e8f5e9' : 'white'; ?>; border-color: <?php echo (($this->projet->current_user_reaction ?? null) === 'LIKE') ? '#4caf50' : '#e1e8ed'; ?>;">
                        <div style="margin-bottom: 5px;"><i data-lucide="rocket" style="width:36px;height:36px;color:<?php echo (($this->projet->current_user_reaction ?? null) === 'LIKE') ? '#2e7d32' : '#2c3e50'; ?>"></i></div>
                        <div style="font-size: 1.5em; font-weight: bold; color: <?php echo (($this->projet->current_user_reaction ?? null) === 'LIKE') ? '#2e7d32' : '#2c3e50'; ?>;" id="like-count_<?php echo $this->projet->id; ?>"><?php echo (int)($this->projet->likes_count ?? 0); ?></div>
                        <div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold;">Prometteur</div>
                    </div>
                    <div class="reaction-large <?php echo (($this->projet->current_user_reaction ?? null) === 'DISLIKE') ? 'disliked' : ''; ?>" id="btnDislike_<?php echo $this->projet->id; ?>" onclick="toggleProjetReaction(<?php echo $this->projet->id; ?>, 'DISLIKE')" style="cursor: pointer; padding: 15px 30px; border-radius: 12px; border: 2px solid #e1e8ed; text-align: center; transition: all 0.2s; background: <?php echo (($this->projet->current_user_reaction ?? null) === 'DISLIKE') ? '#ffebee' : 'white'; ?>; border-color: <?php echo (($this->projet->current_user_reaction ?? null) === 'DISLIKE') ? '#f44336' : '#e1e8ed'; ?>;">
                        <div style="margin-bottom: 5px;"><i data-lucide="help-circle" style="width:36px;height:36px;color:<?php echo (($this->projet->current_user_reaction ?? null) === 'DISLIKE') ? '#c62828' : '#2c3e50'; ?>"></i></div>
                        <div style="font-size: 1.5em; font-weight: bold; color: <?php echo (($this->projet->current_user_reaction ?? null) === 'DISLIKE') ? '#c62828' : '#2c3e50'; ?>;" id="dislike-count_<?php echo $this->projet->id; ?>"><?php echo (int)($this->projet->dislikes_count ?? 0); ?></div>
                        <div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold;">À revoir</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Metrics & Team -->
        <div>
            <!-- Financial Metrics -->
            <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <h3 style="margin-top: 0; font-size: 1.3em; color: #2c3e50; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">Indicateurs Clés</h3>
                
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Fonds Requis</div>
                    <div style="font-size: 2em; font-weight: 800; color: #e67e22;"><?php echo number_format($this->projet->budget, 0, ',', ' '); ?> <span style="font-size: 0.5em; color: #7f8c8d;">DT</span></div>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Gains Estimés</div>
                    <div style="font-size: 2em; font-weight: 800; color: #27ae60;"><?php echo number_format($this->projet->gain, 0, ',', ' '); ?> <span style="font-size: 0.5em; color: #7f8c8d;">DT</span></div>
                </div>

                <?php 
                    $roi = $this->projet->budget > 0 ? (($this->projet->gain - $this->projet->budget) / $this->projet->budget) * 100 : 0;
                ?>
                <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; border-left: 4px solid <?php echo $roi >= 0 ? '#27ae60' : '#e74c3c'; ?>;">
                    <div style="font-size: 0.9em; color: #7f8c8d; font-weight: bold;">ROI Potentiel</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: <?php echo $roi >= 0 ? '#27ae60' : '#e74c3c'; ?>;">
                        <?php echo $roi > 0 ? '+' : ''; ?><?php echo number_format($roi, 1); ?>%
                    </div>
                </div>
            </div>

            <!-- Planning -->
            <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; font-size: 1.3em; color: #2c3e50; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">Planning</h3>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <div style="font-size: 0.85em; color: #7f8c8d; font-weight: bold;">LANCEMENT</div>
                        <div style="font-weight: 600; color: #2c3e50;"><?php echo date('d M Y', strtotime($this->projet->datedebut)); ?></div>
                    </div>
                    <div style="color: #bdc3c7;">➔</div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.85em; color: #7f8c8d; font-weight: bold;">CLÔTURE</div>
                        <div style="font-weight: 600; color: #2c3e50;"><?php echo date('d M Y', strtotime($this->projet->datefin)); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="comments-section" id="comments" style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div class="comments-header" style="margin-bottom: 30px; border-bottom: 2px solid #f0f2f5; padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.5em; color: #2c3e50;">💬 Questions & Avis (<?php echo (int)($this->projet->commentaires_count ?? 0); ?>)</h3>
            <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 1em;">Échangez avec les investisseurs et l'équipe</p>
        </div>

        <!-- Comment Form -->
        <?php if (function_exists('currentUserId') && currentUserId()): ?>
        <form class="comment-form" method="POST" action="index.php?controller=projet_commentaire&action=create" style="margin-bottom: 40px; background: #f8f9fa; padding: 20px; border-radius: 12px; border: 1px solid #e1e8ed;">
            <input type="hidden" name="projet_id" value="<?php echo $this->projet->id; ?>">
            <input type="hidden" name="parent_id" value="" id="parent_id">

            <div id="replying-to" style="display: none; background: #3498db; color: white; padding: 8px 15px; border-radius: 6px; margin-bottom: 15px; font-weight: bold; font-size: 0.9em;">
                En réponse à <span id="reply-to-name"></span>
                <span style="cursor: pointer; float: right; opacity: 0.8;" onclick="cancelReply()" title="Annuler">✕</span>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <textarea name="contenu" id="contenu" placeholder="Rédigez votre question ou avis sur ce projet..." required style="width: 100%; padding: 15px; border: 1px solid #dce1e6; border-radius: 8px; font-family: inherit; font-size: 15px; resize: vertical; min-height: 100px;"></textarea>
            </div>

            <div style="text-align: right;">
                <button type="button" onclick="cancelReply()" id="cancelBtn" style="display: none; padding: 10px 20px; background: white; border: 1px solid #dce1e6; border-radius: 6px; cursor: pointer; font-weight: 600; color: #7f8c8d; margin-right: 10px;">Annuler</button>
                <button type="submit" class="btn-primary" style="padding: 10px 25px; background: #2c3e50; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 15px;">Publier</button>
            </div>
        </form>
        <?php else: ?>
        <div style="background: #f8f9fa; padding: 30px; border-radius: 12px; text-align: center; border: 1px dashed #bdc3c7; margin-bottom: 40px;">
            <p style="margin: 0 0 15px 0; color: #2c3e50; font-size: 1.1em; font-weight: bold;">Vous souhaitez poser une question aux fondateurs ?</p>
            <a href="login.php" class="btn-primary" style="background: #3498db; padding: 10px 25px; border-radius: 6px; color: white; text-decoration: none; font-weight: bold; display: inline-block;">Connectez-vous</a>
        </div>
        <?php endif; ?>

        <!-- Comments List -->
        <?php if (empty($commentaires)): ?>
            <div class="no-comments" style="text-align: center; padding: 40px 0; color: #95a5a6; font-style: italic;">
                Aucun échange pour le moment. Soyez le premier à poser une question !
            </div>
        <?php else: ?>
            <div class="comments-list">
                <?php foreach ($commentsTree[0] ?? [] as $comment): ?>
                    <!-- Main Comment -->
                    <div class="comment-item" style="margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #f0f2f5;">
                        <div class="comment-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div class="comment-author-info" style="display: flex; align-items: center; gap: 12px;">
                                <div class="avatar-sm" style="width: 40px; height: 40px; background: #34495e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em;"><?php echo strtoupper(substr($comment['auteur_nom'] ?? 'U', 0, 1)); ?></div>
                                <div class="info">
                                    <div class="author-name" style="font-weight: bold; color: #2c3e50;" onclick="event.stopPropagation(); window.location.href='index.php?controller=profile&action=index&id=<?php echo $comment['auteur_id']; ?>';" style="cursor: pointer;" title="Voir le profil"><?php echo htmlspecialchars($comment['auteur_nom'] ?? 'Utilisateur'); ?></div>
                                    <div class="comment-date" style="font-size: 0.85em; color: #95a5a6;"><?php echo date('d M Y à H:i', strtotime($comment['date_creation'])); ?></div>
                                </div>
                            </div>
                            <div class="comment-actions" style="display: flex; gap: 10px;">
                                <?php if (function_exists('currentUserId') && currentUserId()): ?>
                                    <button onclick="setReply(<?php echo $comment['id']; ?>, '<?php echo htmlspecialchars(addslashes($comment['auteur_nom'] ?? 'Utilisateur')); ?>')" style="background: none; border: none; color: #3498db; font-weight: bold; cursor: pointer; font-size: 0.9em; padding: 5px;">↩️ Répondre</button>
                                <?php endif; ?>
                                <?php if ((function_exists('currentUserId') && currentUserId() === (int)$comment['auteur_id']) || (function_exists('isAdmin') && isAdmin())): ?>
                                    <a href="index.php?controller=projet_commentaire&action=edit&id=<?php echo $comment['id']; ?>" style="text-decoration: none; color: #7f8c8d; font-size: 0.9em; padding: 5px;">✏️</a>
                                    <a href="index.php?controller=projet_commentaire&action=delete&id=<?php echo $comment['id']; ?>" style="text-decoration: none; color: #e74c3c; font-size: 0.9em; padding: 5px;" onclick="return confirm('Supprimer ce commentaire ?');">🗑️</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="comment-content" style="padding-left: 52px; line-height: 1.5; color: #444;">
                            <?php echo nl2br(htmlspecialchars($comment['contenu'])); ?>
                        </div>
                    </div>

                    <!-- Replies -->
                    <?php if (isset($commentsTree[$comment['id']])): ?>
                        <div style="margin-left: 52px; border-left: 2px solid #ecf0f1; padding-left: 20px;">
                            <?php foreach ($commentsTree[$comment['id']] as $reply): ?>
                                <div class="comment-item reply" style="margin-bottom: 20px;">
                                    <div class="comment-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div class="comment-author-info" style="display: flex; align-items: center; gap: 10px;">
                                            <div class="avatar-sm" style="width: 32px; height: 32px; background: #7f8c8d; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9em;"><?php echo strtoupper(substr($reply['auteur_nom'] ?? 'U', 0, 1)); ?></div>
                                            <div class="info">
                                                <div class="author-name" style="font-weight: bold; color: #2c3e50; font-size: 0.95em;" onclick="event.stopPropagation(); window.location.href='index.php?controller=profile&action=index&id=<?php echo $reply['auteur_id']; ?>';" style="cursor: pointer;" title="Voir le profil"><?php echo htmlspecialchars($reply['auteur_nom'] ?? 'Utilisateur'); ?></div>
                                                <div class="comment-date" style="font-size: 0.8em; color: #95a5a6;"><?php echo date('d M Y à H:i', strtotime($reply['date_creation'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="comment-actions">
                                            <?php if ((function_exists('currentUserId') && currentUserId() === (int)$reply['auteur_id']) || (function_exists('isAdmin') && isAdmin())): ?>
                                                <a href="index.php?controller=projet_commentaire&action=edit&id=<?php echo $reply['id']; ?>" style="text-decoration: none; color: #7f8c8d; font-size: 0.8em; padding: 3px;">✏️</a>
                                                <a href="index.php?controller=projet_commentaire&action=delete&id=<?php echo $reply['id']; ?>" style="text-decoration: none; color: #e74c3c; font-size: 0.8em; padding: 3px;" onclick="return confirm('Supprimer cette réponse ?');">🗑️</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="comment-content" style="padding-left: 42px; line-height: 1.5; color: #555; font-size: 0.95em;">
                                        <?php echo nl2br(htmlspecialchars($reply['contenu'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="projet.js"></script>
<link rel="stylesheet" href="projet.css">
