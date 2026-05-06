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
                <h1><?php echo htmlspecialchars($this->post->titre); ?></h1>
                <div class="post-meta">
                    <div class="post-author-info">
                        <div class="avatar-sm"><?php echo strtoupper(substr($this->post->auteur_nom ?? 'U', 0, 1)); ?></div>
                        <a href="index.php?controller=profile&action=index&id=<?php echo (int)$this->post->auteur_id; ?>" class="post-author-link"><?php echo htmlspecialchars($this->post->auteur_nom ?? 'Utilisateur'); ?></a>
                    </div>
                    <span class="post-date">• <?php echo date('d M Y, H:i', strtotime($this->post->date_creation)); ?></span>
                </div>
            </div>
            
            <div class="post-full-content">
                <?php echo nl2br(htmlspecialchars($this->post->contenu)); ?>
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
                <form id="formComment" onsubmit="submitComment(event)">
                    <input type="hidden" id="post_id" value="<?php echo $this->post->id_post; ?>">
                    <input type="hidden" id="parent_id" value="">
                    <textarea id="comment_contenu" placeholder="Partagez votre avis ou rejoignez la discussion..." required style="width: 100%; padding: 15px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; font-family: inherit; resize: vertical; min-height: 100px; transition: border-color 0.3s; margin-bottom: 15px;" onfocus="this.style.borderColor='#3498db'" onblur="this.style.borderColor='#dce1e6'"></textarea>
                    <div class="comment-form-actions" style="text-align: right;">
                        <button type="submit" id="btnSubmitComment" style="padding: 12px 25px; background: linear-gradient(135deg, #3498db, #2980b9); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 15px; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Publier le commentaire</button>
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
                            echo '  <div class="comment-meta">';
                            echo '      <a href="index.php?controller=profile&action=index&id='.(int)$c['auteur_id'].'" class="comment-author-link">'.$replyAuthor.'</a>';
                            echo '      <span class="comment-date">'.date('d M Y, H:i', strtotime($c['date_creation'])).'</span>';
                            echo '  </div>';
                            echo '  <div class="comment-body">'.nl2br(htmlspecialchars($c['contenu'])).'</div>';
                            echo '  <div class="comment-actions">';
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
<script src="forum.js"></script>
