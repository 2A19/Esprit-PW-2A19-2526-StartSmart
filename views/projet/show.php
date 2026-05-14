<?php $projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0; ?>
<?php $initialProject = (array) (($this->projectData ?? null) ?: ($projectViewData ?? [])); ?>
<?php $initialCompetences = isset($initialProject['competences']) && is_array($initialProject['competences']) ? $initialProject['competences'] : []; ?>
<?php $initialComments = isset($initialProject['commentsFlat']) && is_array($initialProject['commentsFlat']) ? $initialProject['commentsFlat'] : []; ?>

<div class="projet-detail-container" style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="index.php?controller=projet&action=index" style="color: #3498db; text-decoration: none; font-weight: bold;">← Retour aux Startups</a>
    </div>

    <!-- Hero Section (filled by JS) -->
    <div class="projet-hero" style="background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; border-radius: 16px; padding: 40px; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
        <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="flex: 1;">
                <span id="projetCategory" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); padding: 5px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;"><?php echo htmlspecialchars($initialProject['categorie_nom'] ?? '—'); ?></span>
                <h1 id="projetTitle" style="margin: 20px 0 10px 0; font-size: 3em; font-weight: 800; line-height: 1.1; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php echo htmlspecialchars($initialProject['nomprojet'] ?? 'Loading…'); ?></h1>
                <p id="projetRef" style="margin: 0; font-size: 1.1em; opacity: 0.8; font-family: monospace;">Ref: <?php echo htmlspecialchars($initialProject['num'] ?? '—'); ?></p>
                <div id="projetAuthor" style="margin-top: 15px; display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); padding: 5px 15px 5px 5px; border-radius: 30px; cursor: pointer;">
                    <div id="projetAuthorAvatar" style="width: 35px; height: 35px; background: white; color: #1e3c72; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em;"><?php echo htmlspecialchars(strtoupper(substr((string)($initialProject['auteur_nom'] ?? 'U'), 0, 1))); ?></div>
                    <span id="projetAuthorName" style="font-weight: bold; font-size: 1.1em;"><?php echo htmlspecialchars($initialProject['auteur_nom'] ?? 'Utilisateur'); ?></span>
                </div>
            </div>
            <div style="text-align: right; margin-left: 20px;" id="projetAdminActions" style="display:none;"></div>
        </div>
        <div style="position: absolute; top: -50px; right: -50px; width: 250px; height: 250px; background: rgba(255,255,255,0.1); border-radius: 50%; z-index: 1;"></div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 40px;">
        <div>
            <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <h3 style="margin-top: 0; font-size: 1.5em; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; color: #2c3e50;">Le Pitch</h3>
                <div id="competencesList" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed #ecf0f1; display:<?php echo !empty($initialCompetences) ? 'block' : 'none'; ?>;">
                    <?php if (!empty($initialCompetences)): ?>
                        <?php foreach ($initialCompetences as $competence): ?>
                            <span style="background:#e8f4fd;color:#2980b9;font-size:13px;padding:6px 12px;border-radius:6px;font-weight:bold;border:1px solid #d4e6f1;margin-right:6px;display:inline-block;"><?php echo htmlspecialchars($competence['nom'] ?? $competence['name'] ?? $competence); ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="projetDescription" style="font-size: 1.15em; line-height: 2; color: #34495e; white-space: pre-wrap; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; letter-spacing: 0.2px;"><?php echo htmlspecialchars($initialProject['description'] ?? 'Aucune description fournie.'); ?></div>
            </div>

            <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; font-size: 1.3em; color: #2c3e50; text-align: center;">Soutenez ce projet</h3>
                <div class="reactions-buttons" style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                    <div class="reaction-large" id="btnLike_<?php echo $projectId; ?>" style="cursor: pointer; padding: 15px 30px; border-radius: 12px; border: 2px solid #e1e8ed; text-align: center; transition: all 0.2s; background: white; border-color: #e1e8ed;" onclick="toggleProjetReaction(PROJECT_ID, 'LIKE')">
                        <div style="margin-bottom: 5px;"><i data-lucide="rocket" style="width:36px;height:36px;color:#2c3e50;"></i></div>
                        <div style="font-size: 1.5em; font-weight: bold; color: #2c3e50;" id="like-count_<?php echo $projectId; ?>"><?php echo (int)($initialProject['likes_count'] ?? 0); ?></div>
                        <div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold;">Prometteur</div>
                    </div>
                    <div class="reaction-large" id="btnDislike_<?php echo $projectId; ?>" style="cursor: pointer; padding: 15px 30px; border-radius: 12px; border: 2px solid #e1e8ed; text-align: center; transition: all 0.2s; background: white; border-color: #e1e8ed;" onclick="toggleProjetReaction(PROJECT_ID, 'DISLIKE')">
                        <div style="margin-bottom: 5px;"><i data-lucide="help-circle" style="width:36px;height:36px;color:#2c3e50;"></i></div>
                        <div style="font-size: 1.5em; font-weight: bold; color: #2c3e50;" id="dislike-count_<?php echo $projectId; ?>"><?php echo (int)($initialProject['dislikes_count'] ?? 0); ?></div>
                        <div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold;">À revoir</div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <h3 style="margin-top: 0; font-size: 1.3em; color: #2c3e50; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">Indicateurs Clés</h3>
                <div style="margin-bottom: 20px;"><div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Fonds Requis</div><div id="budgetValue" style="font-size: 2em; font-weight: 800; color: #e67e22;"><?php echo htmlspecialchars((string)($initialProject['budget'] ?? '0')); ?> DT</div></div>
                <div style="margin-bottom: 20px;"><div style="font-size: 0.9em; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Gains Estimés</div><div id="gainValue" style="font-size: 2em; font-weight: 800; color: #27ae60;"><?php echo htmlspecialchars((string)($initialProject['gain'] ?? '0')); ?> DT</div></div>
                <div id="roiBox" style="background: #f8f9fa; border-radius: 8px; padding: 15px; border-left: 4px solid #27ae60;"><div style="font-size: 0.9em; color: #7f8c8d; font-weight: bold;">ROI Potentiel</div><div id="roiValue" style="font-size: 1.5em; font-weight: bold; color: #27ae60;"><?php echo !empty($initialProject['budget']) ? htmlspecialchars(number_format((((float)($initialProject['gain'] ?? 0) - (float)$initialProject['budget']) / (float)$initialProject['budget']) * 100, 1)) : '0.0'; ?>%</div></div>
            </div>

            <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; font-size: 1.3em; color: #2c3e50; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">Planning</h3>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;"><div><div style="font-size: 0.85em; color: #7f8c8d; font-weight: bold; margin-bottom: 5px;">LANCEMENT</div><div id="startDate" style="font-weight: 600; color: #2c3e50;"><?php echo !empty($initialProject['datedebut']) ? htmlspecialchars(date('n/j/Y', strtotime($initialProject['datedebut']))) : '—'; ?></div></div><div style="color: #bdc3c7;">➔</div><div style="text-align: right;"><div style="font-size: 0.85em; color: #7f8c8d; font-weight: bold; margin-bottom: 5px;">CLÔTURE</div><div id="endDate" style="font-weight: 600; color: #2c3e50;"><?php echo !empty($initialProject['datefin']) ? htmlspecialchars(date('n/j/Y', strtotime($initialProject['datefin']))) : '—'; ?></div></div></div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="comments-section" id="comments" style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div class="comments-header" style="margin-bottom: 30px; border-bottom: 2px solid #f0f2f5; padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.5em; color: #2c3e50;">💬 Questions & Avis (<span id="commentCount"><?php echo (int)($initialProject['commentaires_count'] ?? 0); ?></span>)</h3>
            <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 1em;">Échangez avec les investisseurs et l'équipe</p>
        </div>

        <div id="commentFormContainer">
            <?php if (empty($initialProject['current_user_logged_in'])): ?>
                <div style="background:#f8f9fa;padding:30px;border-radius:12px;text-align:center;border:1px dashed #bdc3c7;margin-bottom:40px;"><p style="margin:0 0 15px 0;color:#2c3e50;font-size:1.1em;font-weight:bold;">Vous souhaitez poser une question aux fondateurs ?</p><a href="login.php" class="btn-primary" style="background:#3498db;padding:10px 25px;border-radius:6px;color:white;text-decoration:none;font-weight:bold;display:inline-block;">Connectez-vous</a></div>
            <?php endif; ?>
        </div>

        <div id="commentsList" class="comments-list">
            <?php if (!empty($initialComments)): ?>
                <?php foreach ($initialComments as $comment): ?>
                    <?php if (empty($comment['parent_id'])): ?>
                        <div class="comment-item" style="margin-bottom:25px;padding-bottom:25px;border-bottom:1px solid #f0f2f5;">
                            <div class="comment-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div class="avatar-sm" style="width:40px;height:40px;background:#34495e;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:1.2em;"><?php echo htmlspecialchars(strtoupper(substr((string)($comment['auteur_nom'] ?? 'U'), 0, 1))); ?></div>
                                    <div><div class="author-name" style="font-weight:bold;color:#2c3e50;cursor:pointer;"><?php echo htmlspecialchars($comment['auteur_nom'] ?? 'Utilisateur'); ?></div><div class="comment-date" style="font-size:0.85em;color:#95a5a6;"><?php echo htmlspecialchars(date('n/j/Y, g:i:s A', strtotime($comment['date_creation']))); ?></div></div>
                                </div>
                            </div>
                            <div class="comment-content" style="padding-left:52px;line-height:1.5;color:#444;"><?php echo nl2br(htmlspecialchars($comment['contenu'] ?? '')); ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
window.__INITIAL_PROJECT__ = <?php echo json_encode($initialProject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<script>
const PROJECT_ID = <?php echo $projectId; ?>;

function renderProjet(p) {
    const data = p || {};
    document.getElementById('projetCategory').innerText = data.categorie_nom || 'Général';
    document.getElementById('projetTitle').innerText = data.nomprojet || data.name || 'Projet';
    document.getElementById('projetRef').innerText = 'Ref: ' + (data.num || '—');
    document.getElementById('projetAuthorAvatar').innerText = (data.auteur_nom || 'U').substring(0, 1).toUpperCase();
    document.getElementById('projetAuthorName').innerText = data.auteur_nom || 'Utilisateur';
    document.getElementById('projetAuthor').onclick = ()=>{ window.location.href = 'index.php?controller=profile&action=index&id=' + encodeURIComponent(data.auteur_id); };

    const admin = document.getElementById('projetAdminActions');
    admin.innerHTML = '';
    if (data.can_edit) {
        const edit = document.createElement('a'); edit.href = 'index.php?controller=projet&action=edit&id=' + encodeURIComponent(PROJECT_ID); edit.className='btn-edit-sm'; edit.innerText='Modifier'; admin.appendChild(edit);
    }
    if (data.can_delete) {
        const del = document.createElement('a'); del.href = 'index.php?controller=projet&action=delete&id=' + encodeURIComponent(PROJECT_ID); del.style='margin-left:8px;color:#e74c3c;'; del.innerText='Supprimer'; admin.appendChild(del);
    }

    const comps = document.getElementById('competencesList'); comps.innerHTML='';
    if (data.competences && data.competences.length) {
        comps.style.display='block';
        data.competences.forEach(c=>{ const s=document.createElement('span'); s.style='background:#e8f4fd;color:#2980b9;font-size:13px;padding:6px 12px;border-radius:6px;font-weight:bold;border:1px solid #d4e6f1;margin-right:6px;display:inline-block;'; s.innerText = c.nom || c; comps.appendChild(s); });
    } else { comps.style.display='none'; }

    document.getElementById('projetDescription').innerText = data.description || 'Aucune description fournie.';
    document.getElementById('like-count_' + PROJECT_ID).innerText = data.likes_count || 0;
    document.getElementById('dislike-count_' + PROJECT_ID).innerText = data.dislikes_count || 0;
    document.getElementById('btnLike_' + PROJECT_ID).classList.toggle('liked', data.current_user_reaction === 'LIKE');
    document.getElementById('btnDislike_' + PROJECT_ID).classList.toggle('disliked', data.current_user_reaction === 'DISLIKE');
    document.getElementById('budgetValue').innerText = (data.budget || 0) + ' DT';
    document.getElementById('gainValue').innerText = (data.gain || 0) + ' DT';
    const roi = data.budget > 0 ? (((data.gain || 0) - data.budget) / data.budget) * 100 : 0;
    document.getElementById('roiValue').innerText = (roi > 0 ? '+' : '') + roi.toFixed(1) + '%';
    document.getElementById('startDate').innerText = data.datedebut ? new Date(data.datedebut).toLocaleDateString() : '—';
    document.getElementById('endDate').innerText = data.datefin ? new Date(data.datefin).toLocaleDateString() : '—';

    renderComments(data.commentsTree || [], data.commentsFlat || []);
    document.getElementById('commentCount').innerText = (data.commentaires_count || (data.commentsFlat ? data.commentsFlat.length : 0));
    renderCommentForm(data.current_user_logged_in, PROJECT_ID);
}

async function loadProjet() {
    if (!PROJECT_ID) return;
    if (window.__INITIAL_PROJECT__ && Object.keys(window.__INITIAL_PROJECT__).length) {
        renderProjet(window.__INITIAL_PROJECT__);
        return;
    }
    try {
        const res = await fetch('public/api.php?controller=projet&action=show&id=' + encodeURIComponent(PROJECT_ID) + '&format=json', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
        const json = await res.json();
        const p = json.success ? json.data : json.projet || {};
        renderProjet(p);

    } catch (err) { console.error('loadProjet error', err); }
}

function renderComments(tree, flat) {
    const container = document.getElementById('commentsList'); container.innerHTML='';
    if (!flat || flat.length===0) { container.innerHTML = '<div class="no-comments" style="text-align:center;padding:40px 0;color:#95a5a6;font-style:italic;">Aucun échange pour le moment. Soyez le premier à poser une question !</div>'; return; }
    (tree[0]||[]).forEach(comment=>{
        container.appendChild(buildCommentNode(comment, tree));
    });
}

function buildCommentNode(comment, tree) {
    const wrap = document.createElement('div'); wrap.className='comment-item'; wrap.style='margin-bottom:25px;padding-bottom:25px;border-bottom:1px solid #f0f2f5;';
    const header = document.createElement('div'); header.className='comment-header'; header.style='display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;';
    const left = document.createElement('div'); left.style='display:flex;align-items:center;gap:12px;';
    const avatar = document.createElement('div'); avatar.className='avatar-sm'; avatar.style='width:40px;height:40px;background:#34495e;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:1.2em;'; avatar.innerText = (comment.auteur_nom||'U').substring(0,1).toUpperCase();
    const info = document.createElement('div'); info.innerHTML = `<div class="author-name" style="font-weight:bold;color:#2c3e50;cursor:pointer;">${escapeHtml(comment.auteur_nom||'Utilisateur')}</div><div class="comment-date" style="font-size:0.85em;color:#95a5a6;">${new Date(comment.date_creation).toLocaleString()}</div>`;
    left.appendChild(avatar); left.appendChild(info);
    header.appendChild(left);
    const actions = document.createElement('div'); actions.className='comment-actions'; actions.style='display:flex;gap:10px;';
    if (comment.can_reply) { const btn = document.createElement('button'); btn.style='background:none;border:none;color:#3498db;font-weight:bold;cursor:pointer;font-size:0.9em;padding:5px;'; btn.innerText='↩️ Répondre'; btn.onclick=()=> setReply(comment.id, comment.auteur_nom); actions.appendChild(btn); }
    if (comment.can_edit) { const e = document.createElement('a'); e.href='index.php?controller=projet_commentaire&action=edit&id='+comment.id; e.style='text-decoration:none;color:#7f8c8d;font-size:0.9em;padding:5px;'; e.innerText='✏️'; actions.appendChild(e); }
    if (comment.can_delete) { const d = document.createElement('a'); d.href='index.php?controller=projet_commentaire&action=delete&id='+comment.id; d.style='text-decoration:none;color:#e74c3c;font-size:0.9em;padding:5px;'; d.innerText='🗑️'; actions.appendChild(d); }
    header.appendChild(actions);
    wrap.appendChild(header);
    const content = document.createElement('div'); content.className='comment-content'; content.style='padding-left:52px;line-height:1.5;color:#444;'; content.innerHTML = nl2br(escapeHtml(comment.contenu||'')); wrap.appendChild(content);
    if (tree[comment.id]) {
        const repliesWrap = document.createElement('div'); repliesWrap.style='margin-left:52px;border-left:2px solid #ecf0f1;padding-left:20px;';
        tree[comment.id].forEach(r=> repliesWrap.appendChild(buildCommentNode(r, tree)) );
        wrap.appendChild(repliesWrap);
    }
    return wrap;
}

function nl2br(str){ return str.replace(/\n/g, '<br>'); }
function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]); }); }

function renderCommentForm(loggedIn, projectId) {
    const container = document.getElementById('commentFormContainer'); container.innerHTML='';
    if (!loggedIn) {
        container.innerHTML = `<div style="background:#f8f9fa;padding:30px;border-radius:12px;text-align:center;border:1px dashed #bdc3c7;margin-bottom:40px;"><p style="margin:0 0 15px 0;color:#2c3e50;font-size:1.1em;font-weight:bold;">Vous souhaitez poser une question aux fondateurs ?</p><a href="login.php" class="btn-primary" style="background:#3498db;padding:10px 25px;border-radius:6px;color:white;text-decoration:none;font-weight:bold;display:inline-block;">Connectez-vous</a></div>`; return; }
    const form = document.createElement('form'); form.className='comment-form'; form.style='margin-bottom:40px;background:#f8f9fa;padding:20px;border-radius:12px;border:1px solid #e1e8ed;';
    form.innerHTML = `
        <input type="hidden" name="projet_id" value="${projectId}">
        <input type="hidden" name="parent_id" value="" id="parent_id">
        <div id="replying-to" style="display:none;background:#3498db;color:white;padding:8px 15px;border-radius:6px;margin-bottom:15px;font-weight:bold;font-size:0.9em;">En réponse à <span id="reply-to-name"></span><span style="cursor:pointer;float:right;opacity:0.8;" onclick="cancelReply()" title="Annuler">✕</span></div>
        <div class="form-group" style="margin-bottom:15px;"><textarea name="contenu" id="contenu" placeholder="Rédigez votre question ou avis sur ce projet..." required style="width:100%;padding:15px;border:1px solid #dce1e6;border-radius:8px;font-family:inherit;font-size:15px;resize:vertical;min-height:100px;"></textarea></div>
        <div style="text-align:right;"><button type="button" onclick="cancelReply()" id="cancelBtn" style="display:none;padding:10px 20px;background:white;border:1px solid #dce1e6;border-radius:6px;cursor:pointer;font-weight:600;color:#7f8c8d;margin-right:10px;">Annuler</button><button type="submit" class="btn-primary" style="padding:10px 25px;background:#2c3e50;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:bold;font-size:15px;">Publier</button></div>
    `;
    form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const contenu = form.querySelector('#contenu').value;
        const parent_id = form.querySelector('#parent_id').value || null;
        try {
            const res = await fetch('public/api.php?controller=projet_commentaire&action=create', { method:'POST', headers:{ 'Content-Type':'application/json' }, credentials:'include', body: JSON.stringify({ projet_id: projectId, parent_id, contenu }) });
            const j = await res.json();
            if (j.success) { loadProjet(); form.querySelector('#contenu').value=''; cancelReply(); }
            else alert(j.message || 'Erreur lors de la publication');
        } catch (err) { console.error(err); alert('Erreur réseau'); }
    });
    container.appendChild(form);
}

function setReply(commentId, authorName){ document.getElementById('parent_id').value = commentId; document.getElementById('replying-to').style.display='block'; document.getElementById('reply-to-name').innerText = authorName; document.getElementById('cancelBtn').style.display='inline-block'; }
function cancelReply(){ const p = document.getElementById('parent_id'); if (p) p.value=''; const r = document.getElementById('replying-to'); if (r) r.style.display='none'; const cb = document.getElementById('cancelBtn'); if (cb) cb.style.display='none'; }

async function toggleProjetReaction(id, reaction) {
    try {
        const res = await fetch('public/api.php?controller=projet&action=reaction', { method:'POST', headers:{ 'Content-Type':'application/json' }, credentials:'include', body: JSON.stringify({ id, reaction }) });
        const j = await res.json(); if (j.success) { loadProjet(); } else alert(j.message || 'Erreur réaction');
    } catch (err) { console.error(err); }
}

document.addEventListener('DOMContentLoaded', ()=>{ loadProjet(); });
</script>

<script src="projet.js?v=20260513-permissions"></script>
<link rel="stylesheet" href="projet.css">

