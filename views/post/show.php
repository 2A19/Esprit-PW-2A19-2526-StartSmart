<?php $postId = isset($_GET['id']) ? (int)$_GET['id'] : 0; ?>

<div class="discussion-container">
    <div class="bo-header">
        <a href="index.php?controller=post&action=index" class="btn-secondary">← Retour au forum</a>
    </div>

    <div class="discussion-main">
        <!-- THE POST -->
        <div class="post-full-card" id="postCard" style="display:none;">
            <div class="post-full-header">
                <span id="postTopic" class="post-topic">—</span>
                <div id="postProjectLink"></div>
                <h1 id="postTitle">Loading…</h1>
                <div class="post-meta">
                    <div class="post-author-info">
                        <div class="avatar-sm" id="postAuthorAvatar">U</div>
                        <a id="postAuthorLink" href="#" class="post-author-link">Utilisateur</a>
                    </div>
                    <span class="post-date" id="postDate">—</span>
                </div>
            </div>
            
            <div class="post-full-content" style="position: relative;">
                <div id="postContent" style="margin-top: 20px;">Loading…</div>
                <div id="postAttachments" style="margin-top: 30px; border-top: 1px solid #f0f2f5; padding-top: 20px; display:none;"></div>
            </div>

            <div class="post-full-actions" id="postActions"></div>
        </div>

        <!-- THE COMMENTS -->
        <div class="comments-section">
            <div class="comments-header">
                <h3><span id="commentCountHeader">0</span> Commentaires</h3>
                <span class="comments-helper">Les réponses sont affichées en fil de discussion.</span>
            </div>
            
            <div id="commentFormContainer"></div>

            <div class="comments-list" id="comments-list"></div>
        </div>
    </div>
</div>
<script>
const POST_ID = <?php echo $postId; ?>;

async function loadPost() {
    if (!POST_ID) return;
    try {
        const res = await fetch('public/api.php?controller=post&action=show&id=' + encodeURIComponent(POST_ID) + '&format=json', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
        const json = await res.json();
        const p = json.success ? json.data : json.post || {};
        
        // Display post card
        document.getElementById('postCard').style.display = 'block';
        document.getElementById('postTitle').innerText = p.titre || 'Post';
        document.getElementById('postTopic').className = 'post-topic badge-' + (p.topic||'general').toLowerCase();
        document.getElementById('postTopic').innerText = p.topic || 'General';
        document.getElementById('postAuthorAvatar').innerText = (p.auteur_nom||'U').substring(0,1).toUpperCase();
        document.getElementById('postAuthorLink').href = 'index.php?controller=profile&action=index&id=' + encodeURIComponent(p.auteur_id);
        document.getElementById('postAuthorLink').innerText = p.auteur_nom || 'Utilisateur';
        document.getElementById('postDate').innerText = '• ' + new Date(p.date_creation).toLocaleString();
        
        // Project link
        const projLink = document.getElementById('postProjectLink');
        if (p.projet_id) {
            projLink.innerHTML = '<a href="index.php?controller=projet&action=show&id=' + encodeURIComponent(p.projet_id) + '" class="post-topic" style="background:#e8f4fd;color:#2980b9;border:1px solid #d4e6f1;text-decoration:none;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="rocket" style="width:14px;height:14px;"></i> Projet: ' + escapeHtml(p.projet_nom||'Projet') + '</a>';
        }
        
        // Content
        document.getElementById('postContent').innerText = p.contenu || 'No content';
        
        // Attachments
        const attDiv = document.getElementById('postAttachments');
        if (p.attachments && p.attachments.length) {
            attDiv.style.display = 'block';
            attDiv.innerHTML = '<h4 style="margin:0 0 15px 0;color:#2c3e50;font-size:14px;"><i data-lucide="paperclip" style="width:16px;height:16px;"></i> Pièces jointes</h4><div style="display:flex;gap:15px;flex-wrap:wrap;" id="attachmentsGrid"></div>';
            const grid = document.getElementById('attachmentsGrid');
            p.attachments.forEach(att => {
                const a = document.createElement('a');
                a.href = att.chemin_fichier;
                a.target = '_blank';
                if (att.type_fichier.startsWith('image/')) {
                    a.style = 'display:block;border-radius:8px;overflow:hidden;border:1px solid #dce1e6;';
                    const img = document.createElement('img');
                    img.src = att.chemin_fichier;
                    img.alt = att.nom_fichier;
                    img.style = 'height:100px;width:150px;object-fit:cover;display:block;';
                    a.appendChild(img);
                } else {
                    a.style = 'display:flex;align-items:center;gap:10px;padding:10px 15px;background:#f8f9fa;border:1px solid #dce1e6;border-radius:8px;text-decoration:none;color:#2c3e50;font-size:13px;';
                    a.innerHTML = '<i data-lucide="file" style="color:#3498db;width:20px;height:20px;"></i><span style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escapeHtml(att.nom_fichier) + '</span>';
                }
                grid.appendChild(a);
            });
        } else { attDiv.style.display = 'none'; }
        
        // Actions
        const actionsDiv = document.getElementById('postActions');
        actionsDiv.innerHTML = `<button class="btn-like" id="btnLike" onclick="toggleReaction(POST_ID, 'LIKE')" style="display:inline-flex;align-items:center;gap:5px;"><i data-lucide="thumbs-up" style="width:16px;height:16px;"></i> <span id="likeCount">${p.likes_count||0}</span> J'aime</button>`;
        actionsDiv.innerHTML += `<button class="btn-dislike" id="btnDislike" onclick="toggleReaction(POST_ID, 'DISLIKE')" style="display:inline-flex;align-items:center;gap:5px;margin-left:10px;"><i data-lucide="thumbs-down" style="width:16px;height:16px;"></i> <span id="dislikeCount">${p.dislikes_count||0}</span> Je n'aime pas</button>`;
        if (p.can_edit) {
            actionsDiv.innerHTML += `<a href="index.php?controller=post&action=edit&id=${POST_ID}" class="btn-secondary" style="margin-left:10px;">Modifier</a>`;
        }
        if (p.can_delete) {
            actionsDiv.innerHTML += `<a href="index.php?controller=post&action=delete&id=${POST_ID}" class="btn-danger" style="margin-left:10px;" onclick="return confirm('Supprimer ce sujet ?');">Supprimer</a>`;
        }
        
        // Comments
        document.getElementById('commentCountHeader').innerText = p.commentaires_count || 0;
        renderCommentForm(p.current_user_logged_in, POST_ID);
        renderComments(p.commentsTree || [], p.commentsFlat || []);
        
    } catch (err) { console.error('loadPost error', err); }
}

function renderCommentForm(loggedIn, postId) {
    const container = document.getElementById('commentFormContainer'); container.innerHTML = '';
    if (!loggedIn) {
        container.innerHTML = `<div style="background:#f8f9fa;padding:30px;border-radius:12px;text-align:center;border:1px dashed #bdc3c7;margin-bottom:40px;"><p style="margin:0 0 15px 0;color:#2c3e50;font-size:1.1em;font-weight:bold;">Vous souhaitez partager votre avis ?</p><a href="login.php" class="btn-primary" style="background:#3498db;padding:10px 25px;border-radius:6px;color:white;text-decoration:none;font-weight:bold;display:inline-block;">Connectez-vous</a></div>`; return;
    }
    const form = document.createElement('form'); form.className = 'add-comment-box'; form.style = 'background:white;border-radius:12px;padding:25px;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:30px;border:1px solid #eef2f5;';
    form.innerHTML = `
        <div id="replying-to" style="display:none;background:#e8f4fd;color:#2980b9;padding:10px 15px;border-radius:8px;margin-bottom:15px;font-weight:600;display:flex;justify-content:space-between;align-items:center;">
            <span>Replying to <span id="reply-to-name" style="text-decoration:underline;"></span></span>
            <button type="button" onclick="cancelReply()" style="background:none;border:none;font-size:1.2em;color:#2980b9;cursor:pointer;padding:0;">✕</button>
        </div>
        <textarea id="comment_contenu" placeholder="Partagez votre avis..." required style="width:100%;padding:15px;border:1px solid #dce1e6;border-radius:8px;font-size:15px;font-family:inherit;resize:vertical;min-height:100px;margin-bottom:15px;"></textarea>
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div></div>
            <button type="submit" id="btnSubmitComment" style="padding:12px 25px;background:linear-gradient(135deg,#3498db,#2980b9);color:white;border:none;border-radius:8px;cursor:pointer;font-weight:bold;font-size:15px;">Publier</button>
        </div>
    `;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const contenu = form.querySelector('#comment_contenu').value;
        const parent_id = null;
        try {
            const res = await fetch('public/api.php?controller=commentaire&action=create', { method:'POST', headers:{ 'Content-Type':'application/json' }, credentials:'include', body: JSON.stringify({ post_id: postId, parent_id, contenu }) });
            const j = await res.json();
            if (j.success) { loadPost(); form.querySelector('#comment_contenu').value = ''; }
            else alert(j.message || 'Erreur');
        } catch (err) { console.error(err); alert('Erreur réseau'); }
    });
    container.appendChild(form);
}

function renderComments(tree, flat) {
    const list = document.getElementById('comments-list'); list.innerHTML = '';
    if (!flat || flat.length===0) { list.innerHTML = '<div style="text-align:center;padding:40px 0;color:#95a5a6;font-style:italic;">Aucun commentaire. Soyez le premier à répondre !</div>'; return; }
    (tree[0]||[]).forEach(c => list.appendChild(buildCommentNode(c, tree)));
}

function buildCommentNode(c, tree) {
    const wrap = document.createElement('div'); wrap.className = 'comment-item'; wrap.id = 'comment-' + c.id_commentaire;
    const meta = document.createElement('div'); meta.className = 'comment-meta'; meta.style = 'position:relative;margin-bottom:10px;';
    const author = document.createElement('a'); author.href = 'index.php?controller=profile&action=index&id=' + encodeURIComponent(c.auteur_id); author.className = 'comment-author-link'; author.innerText = c.auteur_nom || 'Utilisateur';
    const date = document.createElement('span'); date.className = 'comment-date'; date.innerText = ' • ' + new Date(c.date_creation).toLocaleString();
    meta.appendChild(author); meta.appendChild(date);
    wrap.appendChild(meta);
    const body = document.createElement('div'); body.className = 'comment-body'; body.id = 'comment-content-' + c.id_commentaire; body.innerText = c.contenu || '';
    wrap.appendChild(body);
    const actions = document.createElement('div'); actions.className = 'comment-actions'; actions.style = 'margin-top:10px;';
    const replyBtn = document.createElement('button'); replyBtn.className = 'btn-reply'; replyBtn.innerText = 'Répondre'; replyBtn.onclick = () => setReply(c.id_commentaire, c.auteur_nom); actions.appendChild(replyBtn);
    if (c.can_edit) { const e = document.createElement('a'); e.href = 'index.php?controller=commentaire&action=edit&id=' + c.id_commentaire; e.className = 'comment-link'; e.innerText = 'Modifier'; e.style = 'margin-left:10px;'; actions.appendChild(e); }
    if (c.can_delete) { const d = document.createElement('a'); d.href = 'index.php?controller=commentaire&action=delete&id=' + c.id_commentaire; d.className = 'comment-link danger'; d.innerText = 'Supprimer'; d.style = 'margin-left:10px;'; d.onclick = () => confirm('Supprimer ce commentaire ?'); actions.appendChild(d); }
    wrap.appendChild(actions);
    if (tree[c.id_commentaire]) {
        const replies = document.createElement('div'); replies.style = 'margin-left:20px;border-left:2px solid #ecf0f1;padding-left:20px;margin-top:15px;';
        tree[c.id_commentaire].forEach(r => replies.appendChild(buildCommentNode(r, tree)));
        wrap.appendChild(replies);
    }
    return wrap;
}

function setReply(cId, cName) { document.getElementById('replying-to').style.display = 'flex'; document.getElementById('reply-to-name').innerText = cName; }
function cancelReply() { document.getElementById('replying-to').style.display = 'none'; }
function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

async function toggleReaction(id, reaction) {
    try {
        const res = await fetch('public/api.php?controller=post&action=reaction', { method:'POST', headers:{ 'Content-Type':'application/json' }, credentials:'include', body: JSON.stringify({ id, reaction }) });
        const j = await res.json(); if (j.success) { loadPost(); } else alert(j.message || 'Erreur');
    } catch (err) { console.error(err); }
}

document.addEventListener('DOMContentLoaded', () => loadPost());
</script>
<script src="forum.js?v=<?php echo time(); ?>"></script>

