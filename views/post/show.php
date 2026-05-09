<div class="discussion-container">
    <div class="bo-header">
        <a href="index.php?controller=post&action=index" class="btn-secondary">← Retour au forum</a>
    </div>

    <div class="discussion-main">
        <!-- THE POST -->
        <div class="post-full-card">
            <div class="post-full-header">
                <span class="post-topic badge-<?php echo strtolower($this->post->topic ?: 'general'); ?>"><?php echo htmlspecialchars($this->post->topic ?: 'General'); ?></span>
                <?php if (!empty($this->post->projet_id)): ?>
                    <a href="index.php?controller=projet&action=show&id=<?php echo $this->post->projet_id; ?>" class="post-topic" style="background: #e8f4fd; color: #2980b9; border: 1px solid #d4e6f1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="rocket" style="width:14px;height:14px;"></i> Projet: <?php echo htmlspecialchars($this->post->projet_nom); ?></a>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars(html_entity_decode($this->post->titre, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="post-meta">
                    <div class="post-author-info">
                        <div class="avatar-sm"><?php echo strtoupper(substr($this->post->auteur_nom ?? 'U', 0, 1)); ?></div>
                        <a href="index.php?controller=profile&action=index&id=<?php echo (int)$this->post->auteur_id; ?>" class="post-author-link"><?php echo htmlspecialchars($this->post->auteur_nom ?? 'Utilisateur'); ?></a>
                    </div>
                    <span class="post-date">• <?php echo date('d M Y, H:i', strtotime($this->post->date_creation)); ?></span>
                </div>
            </div>
            
            <div class="post-full-content" style="position: relative;">
                <div style="position: absolute; right: 0; top: -10px;">
                    <select class="translate-select" onchange="translateText('post-content-<?php echo $this->post->id_post; ?>', this.value)" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #dce1e6; background: #f8f9fa; cursor: pointer; font-size: 13px; color: #34495e; font-weight: 500; outline: none; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3498db'" onmouseout="this.style.borderColor='#dce1e6'">
                        <option value="original">🌐 Original</option>
                        <option value="fr">🇫🇷 Français</option>
                        <option value="en">🇬🇧 English</option>
                        <option value="ar">🇸🇦 العربية</option>
                    </select>
                </div>
                <div id="post-content-<?php echo $this->post->id_post; ?>" style="margin-top: 20px;">
                    <?php echo nl2br(htmlspecialchars(html_entity_decode($this->post->contenu, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8')); ?>
                </div>
                
                <?php if (!empty($postAttachments)): ?>
                <div class="post-attachments" style="margin-top: 30px; border-top: 1px solid #f0f2f5; padding-top: 20px;">
                    <h4 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 14px;"><i data-lucide="paperclip" style="width: 16px; height: 16px;"></i> Pièces jointes</h4>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <?php foreach($postAttachments as $att): ?>
                            <?php if(strpos($att['type_fichier'], 'image/') === 0): ?>
                                <a href="<?php echo htmlspecialchars($att['chemin_fichier']); ?>" target="_blank" style="display: block; border-radius: 8px; overflow: hidden; border: 1px solid #dce1e6;">
                                    <img src="<?php echo htmlspecialchars($att['chemin_fichier']); ?>" alt="<?php echo htmlspecialchars($att['nom_fichier']); ?>" style="height: 100px; width: 150px; object-fit: cover; display: block;">
                                </a>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($att['chemin_fichier']); ?>" target="_blank" style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; background: #f8f9fa; border: 1px solid #dce1e6; border-radius: 8px; text-decoration: none; color: #2c3e50; font-size: 13px;">
                                    <i data-lucide="file" style="color: #3498db; width: 20px; height: 20px;"></i>
                                    <span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($att['nom_fichier']); ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="post-full-actions">
                <button class="btn-like <?php echo $this->post->current_user_reaction === 'LIKE' ? 'liked' : ''; ?>" onclick="toggleReaction(<?php echo $this->post->id_post; ?>, 'LIKE')" id="btnLike_<?php echo $this->post->id_post; ?>">
                    <span class="like-icon"><i data-lucide="thumbs-up" style="width:16px;height:16px;"></i></span> <span id="like-count_<?php echo $this->post->id_post; ?>"><?php echo (int) ($this->post->likes_count ?? 0); ?></span> J'aime
                </button>
                <button class="btn-dislike <?php echo $this->post->current_user_reaction === 'DISLIKE' ? 'disliked' : ''; ?>" onclick="toggleReaction(<?php echo $this->post->id_post; ?>, 'DISLIKE')" id="btnDislike_<?php echo $this->post->id_post; ?>">
                    <span class="dislike-icon"><i data-lucide="thumbs-down" style="width:16px;height:16px;"></i></span> <span id="dislike-count_<?php echo $this->post->id_post; ?>"><?php echo (int) ($this->post->dislikes_count ?? 0); ?></span> Je n'aime pas
                </button>
                <?php if ((function_exists('currentUserId') && currentUserId() === (int) $this->post->auteur_id) || (function_exists('isAdmin') && isAdmin())): ?>
                    <a href="index.php?controller=post&action=edit&id=<?php echo $this->post->id_post; ?>" class="btn-secondary">Modifier</a>
                    <a href="index.php?controller=post&action=delete&id=<?php echo $this->post->id_post; ?>" class="btn-danger" onclick="return confirm('Supprimer ce sujet ?');">Supprimer</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- THE COMMENTS -->
        <div class="comments-section">
            <div class="comments-header">
                <h3><?php echo count($commentaires); ?> Commentaires</h3>
                <span class="comments-helper">Les réponses sont affichées en fil de discussion.</span>
            </div>
            
            <div class="add-comment-box" id="reply-box-container" style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; border: 1px solid #eef2f5;">
                <div id="replying-to" style="display:none; background: #e8f4fd; color: #2980b9; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                    <span>Replying to <span id="reply-to-name" style="text-decoration: underline;"></span></span>
                    <button type="button" onclick="cancelReply()" style="background: none; border: none; font-size: 1.2em; color: #2980b9; cursor: pointer; padding: 0;">✕</button>
                </div>
                <form id="commentForm" onsubmit="submitComment(event)" enctype="multipart/form-data">
                    <input type="hidden" id="post_id" name="post_id" value="<?php echo $this->post->id_post; ?>">
                    <input type="hidden" id="parent_id" name="parent_id" value="">
                <div class="dictation-wrapper">
                    <textarea id="comment_contenu" name="contenu" placeholder="Partagez votre avis ou utilisez le micro pour dicter..." required style="width: 100%; padding: 15px; padding-bottom: 40px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; font-family: inherit; resize: vertical; min-height: 100px; transition: border-color 0.3s; margin-bottom: 15px;" onfocus="this.style.borderColor='#3498db'" onblur="this.style.borderColor='#dce1e6'"></textarea>
                    
                    <div class="dictation-controls">
                        <select class="dictation-lang" title="Choisir la langue de dictée">
                            <option value="en-US">EN</option>
                            <option value="fr-FR" selected>FR</option>
                            <option value="ar-SA">AR</option>
                        </select>
                        <button type="button" class="dictation-btn" onclick="toggleDictation('comment_contenu', this)" title="Cliquer pour parler">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                    </div>
                    <div class="dictation-interim"></div>
                </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <input type="file" id="comment_attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" style="display: none;" onchange="document.getElementById('att-count').innerText = this.files.length + ' fichier(s)'">
                            <button type="button" onclick="document.getElementById('comment_attachments').click()" style="background: none; border: 1px solid #dce1e6; padding: 8px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; color: #7f8c8d; font-size: 13px;">
                                <i data-lucide="paperclip" style="width: 14px; height: 14px;"></i> Joindre
                            </button>
                            <span id="att-count" style="font-size: 12px; color: #7f8c8d; margin-left: 10px;"></span>
                        </div>
                        <div class="comment-form-actions" style="text-align: right;">
                            <button type="submit" id="btnSubmitComment" style="padding: 12px 25px; background: linear-gradient(135deg, #3498db, #2980b9); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 15px; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Publier le commentaire</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="comments-list" id="comments-list">
                <?php 
                if (!function_exists('renderComments')) {
                    function renderComments($parentId, $commentsTree) {
                        if (!isset($commentsTree[$parentId])) return;
                        echo '<div class="comment-thread">';
                        foreach ($commentsTree[$parentId] as $c) {
                            $replyAuthor = htmlspecialchars($c['auteur_nom'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8');
                            $replyOnclick = "setReply(" . (int) $c['id_commentaire'] . ", '" . $replyAuthor . "')";
                            echo '<div class="comment-item" id="comment-'.$c['id_commentaire'].'">';
                            echo '  <div class="comment-meta" style="position:relative;">';
                            echo '      <a href="index.php?controller=profile&action=index&id='.(int)$c['auteur_id'].'" class="comment-author-link">'.$replyAuthor.'</a>';
                            echo '      <span class="comment-date">'.date('d M Y, H:i', strtotime($c['date_creation'])).'</span>';
                            echo '      <select onchange="translateText(\'comment-content-'.(int)$c['id_commentaire'].'\', this.value)" style="position:absolute; right:0; top:0; padding: 4px 8px; font-size:11px; border:1px solid #eef2f5; border-radius:6px; color:#576574; background:#f8f9fa; cursor:pointer; font-weight:500; outline:none; transition:border-color 0.2s;" onmouseover="this.style.borderColor=\'#3498db\'" onmouseout="this.style.borderColor=\'#eef2f5\'">';
                            echo '          <option value="original">🌐 Original</option>';
                            echo '          <option value="fr">🇫🇷 FR</option>';
                            echo '          <option value="en">🇬🇧 EN</option>';
                            echo '          <option value="ar">🇸🇦 AR</option>';
                            echo '      </select>';
                            echo '  </div>';
                            echo '  <div class="comment-body" id="comment-content-'.(int)$c['id_commentaire'].'">'.nl2br(htmlspecialchars(html_entity_decode($c['contenu'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8')).'</div>';
                            
                            // Attachments Display
                            if (!empty($c['attachments'])) {
                                echo '<div style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap;">';
                                foreach($c['attachments'] as $att) {
                                    if(strpos($att['type_fichier'], 'image/') === 0) {
                                        echo '<a href="'.htmlspecialchars($att['chemin_fichier']).'" target="_blank"><img src="'.htmlspecialchars($att['chemin_fichier']).'" style="height:60px; border-radius:4px; border:1px solid #eee;"></a>';
                                    } else {
                                        echo '<a href="'.htmlspecialchars($att['chemin_fichier']).'" target="_blank" style="padding:4px 8px; background:#f8f9fa; border:1px solid #eee; border-radius:4px; font-size:11px; text-decoration:none; color:#2c3e50;"><i class="fa-solid fa-paperclip"></i> '.htmlspecialchars($att['nom_fichier']).'</a>';
                                    }
                                }
                                echo '</div>';
                            }

                            echo '  <div class="comment-actions" style="margin-top: 10px;">';
                            echo '      <button class="btn-reply" onclick="' . htmlspecialchars($replyOnclick, ENT_QUOTES, 'UTF-8') . '">Répondre</button>';
                            echo '      <button class="btn-comment-react ' . (($c['current_user_reaction'] ?? null) === 'LIKE' ? 'liked' : '') . '" id="btnCommentLike_' . (int) $c['id_commentaire'] . '" onclick="toggleCommentReaction(' . (int) $c['id_commentaire'] . ', \'LIKE\')">';
                            echo '          <span><i data-lucide="heart" style="width:14px;height:14px;"></i></span> <span id="comment-like-count_' . (int) $c['id_commentaire'] . '">' . (int) ($c['likes_count'] ?? 0) . '</span>';
                            echo '      </button>';
                            echo '      <button class="btn-comment-react ' . (($c['current_user_reaction'] ?? null) === 'DISLIKE' ? 'disliked' : '') . '" id="btnCommentDislike_' . (int) $c['id_commentaire'] . '" onclick="toggleCommentReaction(' . (int) $c['id_commentaire'] . ', \'DISLIKE\')">';
                            echo '          <span><i data-lucide="thumbs-down" style="width:14px;height:14px;"></i></span> <span id="comment-dislike-count_' . (int) $c['id_commentaire'] . '">' . (int) ($c['dislikes_count'] ?? 0) . '</span>';
                            echo '      </button>';
                            if ((function_exists('currentUserId') && currentUserId() === (int) $c['auteur_id']) || (function_exists('isAdmin') && isAdmin())) {
                                echo '      <a href="index.php?controller=commentaire&action=edit&id='.$c['id_commentaire'].'" class="comment-link">Modifier</a>';
                                echo '      <a href="index.php?controller=commentaire&action=delete&id='.$c['id_commentaire'].'" class="comment-link danger" onclick="return confirm(\'Supprimer ce commentaire ?\');">Supprimer</a>';
                            }
                            echo '  </div>';
                            
                            // Render children
                            renderComments($c['id_commentaire'], $commentsTree);
                            
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                }

                renderComments(0, $commentsTree);
                ?>
            </div>
        </div>
    </div>
</div>
<script src="forum.js?v=<?php echo time(); ?>"></script>
