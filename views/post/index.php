<div class="forum-container">
    <div class="forum-header">
        <div class="forum-title">
            <h2>Discussions StartSmart</h2>
            <p>Rejoignez la conversation avec d'autres entrepreneurs et experts.</p>
        </div>
        <div class="forum-actions">
            <a href="index.php?controller=post&action=create" class="btn-primary">+ Nouveau Sujet</a>
        </div>
    </div>


    <form id="forumSearchForm" class="forum-searchbar" action="javascript:void(0);">
        <input type="hidden" name="controller" value="post">
        <input type="hidden" name="action" value="index">
        <input type="search" id="forumSearchInput" name="search" placeholder="Rechercher un sujet...">
        <button type="submit" class="btn-primary">Rechercher</button>
    </form>

    <div class="forum-layout">
        <aside class="forum-sidebar">
            <div class="sidebar-box filter-box">
                <h3>Sujets</h3>
                <ul id="topicList" class="topic-list">
                    <!-- topics populated by JS -->
                </ul>
            </div>
            
            <div class="sidebar-box sort-box">
                <h3>Trier par</h3>
                <form action="index.php" method="GET" class="sort-form">
                    <input type="hidden" name="controller" value="post">
                    <input type="hidden" name="action" value="index">
                    <?php if(isset($_GET['topic'])) echo '<input type="hidden" name="topic" value="'.htmlspecialchars($_GET['topic']).'">'; ?>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="latest" <?php echo (isset($_GET['sort']) && $_GET['sort']=='latest') ? 'selected' : ''; ?>>Plus récents</option>
                        <option value="most_commented" <?php echo (isset($_GET['sort']) && $_GET['sort']=='most_commented') ? 'selected' : ''; ?>>Plus commentés</option>
                        <option value="most_liked" <?php echo (isset($_GET['sort']) && $_GET['sort']=='most_liked') ? 'selected' : ''; ?>>Plus aimés</option>
                    </select>
                </form>
            </div>

            <!-- Projets / Apps Tendances -->
            <div class="sidebar-box projects-box" style="margin-top: 20px;">
                <h3>Apps & Projets Tendances</h3>
                <ul id="topAppsList" class="topic-list" style="list-style:none; padding:0; margin:0;">
                    <!-- top apps populated by JS -->
                </ul>
            </div>
        </aside>

        <main class="forum-feed">
            <div id="forum-skeleton">
                <!-- Skeleton Loading Cards -->
                <div class="skeleton-card">
                    <div style="display:flex; gap:15px;">
                        <div class="skeleton skeleton-avatar"></div>
                        <div style="flex:1;">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
                <div class="skeleton-card">
                    <div style="display:flex; gap:15px;">
                        <div class="skeleton skeleton-avatar"></div>
                        <div style="flex:1;">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
                <div class="skeleton-card">
                    <div style="display:flex; gap:15px;">
                        <div class="skeleton skeleton-avatar"></div>
                        <div style="flex:1;">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="forum-real-feed" style="display: none; animation: fadeIn 0.4s ease-in-out;">
                <div id="postsContainer"><!-- posts rendered here --></div>
            </div>
            
            <style>
                .post-footer button.saved { color: #3498db !important; }
            </style>

            <nav id="paginationBar" class="pagination-bar" aria-label="Pagination des sujets" style="display:none;"></nav>
            </div>
            
            <script>
            // Client-side forum renderer: fetch posts from backend via proxy and render
            const topics = ['Ideas','Feedback','Questions','Collaborations','General'];
            function renderTopics() {
                const ul = document.getElementById('topicList');
                ul.innerHTML = '';
                const allLi = document.createElement('li');
                allLi.innerHTML = `<a href="index.php?controller=post&action=index" class="active">Tous les sujets</a>`;
                ul.appendChild(allLi);
                topics.forEach(t => {
                    const li = document.createElement('li');
                    li.innerHTML = `<a href="javascript:void(0);" data-topic="${t}">${t}</a>`;
                    ul.appendChild(li);
                });
                ul.addEventListener('click', (e) => {
                    const a = e.target.closest('a');
                    if (!a) return;
                    const topic = a.getAttribute('data-topic') || '';
                    document.querySelectorAll('#topicList a').forEach(x=>x.classList.remove('active'));
                    a.classList.add('active');
                    loadPosts({ topic });
                });
            }

            async function loadTopApps() {
                try {
                    const res = await fetch('public/api.php?controller=map&action=apiData', { credentials: 'include' });
                    const json = await res.json();
                    const apps = json.success ? json.data.slice(0,5) : [];
                    const ul = document.getElementById('topAppsList');
                    ul.innerHTML = '';
                    if (apps.length === 0) {
                        ul.innerHTML = '<li style="color:#7f8c8d; font-size:0.9em;">Aucun projet en cours.</li>';
                        return;
                    }
                    apps.forEach(app => {
                        const li = document.createElement('li');
                        li.style = 'padding:10px 0; border-bottom:1px solid #eee;';
                        li.innerHTML = `<strong style="color:#2c3e50; display:block; font-size:0.95em;">${escapeHtml(app.nomprojet||app.name||app.nom||'Untitled')}</strong><span style="font-size:0.8em; color:#7f8c8d;">Budget: ${escapeHtml(app.budget||'N/A')} DT</span>`;
                        ul.appendChild(li);
                    });
                } catch (err) { console.error('top apps load failed', err); }
            }

            async function loadPosts({ search='', topic='', sort='latest', page=1 } = {}) {
                const skeleton = document.getElementById('forum-skeleton');
                const feed = document.getElementById('forum-real-feed');
                if (skeleton && feed) { skeleton.style.display = ''; feed.style.display = 'none'; }
                try {
                    const params = new URLSearchParams();
                    if (search) params.set('search', search);
                    if (topic) params.set('topic', topic);
                    if (sort) params.set('sort', sort);
                    params.set('page', page);
                    params.set('format', 'json');
                    const res = await fetch('public/api.php?controller=post&action=index&' + params.toString(), { credentials: 'include' });
                    const json = await res.json();
                    const posts = json.success ? json.data : (json.posts || []);
                    renderPosts(posts);
                } catch (err) { console.error('Failed to load posts', err); renderPosts([]); }
                if (skeleton && feed) { skeleton.style.display = 'none'; feed.style.display = 'block'; }
            }

            function renderPosts(posts) {
                const container = document.getElementById('postsContainer');
                container.innerHTML = '';
                if (!posts || posts.length === 0) {
                    container.innerHTML = `<div class="no-posts empty-state"><h3>Aucun sujet trouvé</h3><p>Essayez une autre recherche ou lancez la première discussion de la communauté.</p><a href="index.php?controller=post&action=create" class="btn-primary">Créer un sujet</a></div>`;
                    return;
                }
                posts.forEach(row => {
                    const postCard = document.createElement('div');
                    postCard.className = 'post-card';
                    postCard.onclick = () => { window.location.href = 'index.php?controller=post&action=show&id=' + encodeURIComponent(row.id_post || row.id); };

                    const left = document.createElement('div'); left.className = 'post-card-left';
                    const upBtn = document.createElement('button'); upBtn.className = 'upvote-box'; upBtn.onclick = (e) => { e.stopPropagation(); toggleReaction(row.id_post||row.id,'LIKE'); };
                    upBtn.innerHTML = `<span class="upvote-icon">▲</span><span class="upvote-count">${escapeHtml(row.likes_count||0)}</span>`;
                    const downBtn = document.createElement('button'); downBtn.className = 'downvote-box'; downBtn.onclick = (e) => { e.stopPropagation(); toggleReaction(row.id_post||row.id,'DISLIKE'); };
                    downBtn.innerHTML = `<span class="downvote-icon">▼</span><span class="downvote-count">${escapeHtml(row.dislikes_count||0)}</span>`;
                    left.appendChild(upBtn); left.appendChild(downBtn);

                    const main = document.createElement('div'); main.className = 'post-card-main';
                    const meta = document.createElement('div'); meta.className = 'post-meta';
                    const topicSpan = document.createElement('span'); topicSpan.className = 'post-topic badge-general'; topicSpan.innerText = row.topic || 'General';
                    meta.appendChild(topicSpan);
                    if (row.projet_id) {
                        const pLink = document.createElement('a'); pLink.href = 'index.php?controller=projet&action=show&id=' + encodeURIComponent(row.projet_id); pLink.className = 'post-topic'; pLink.style = 'background:#e8f4fd;color:#2980b9;border:1px solid #d4e6f1;'; pLink.innerText = 'Projet: ' + (row.projet_nom || row.projet_name || 'Projet');
                        meta.appendChild(pLink);
                    }
                    const authorDiv = document.createElement('div'); authorDiv.className = 'post-author-info'; authorDiv.style = 'cursor:pointer;'; authorDiv.onclick = (e) => { e.stopPropagation(); window.location.href = 'index.php?controller=profile&action=index&id=' + encodeURIComponent(row.auteur_id); };
                    const avatar = document.createElement('div'); avatar.className = 'avatar-sm'; avatar.innerText = (row.auteur_nom||'U').substr(0,1).toUpperCase();
                    const authorName = document.createElement('span'); authorName.className = 'post-author'; authorName.style = 'color:#3498db;font-weight:bold;'; authorName.innerText = row.auteur_nom || 'Utilisateur';
                    authorDiv.appendChild(avatar); authorDiv.appendChild(authorName);
                    meta.appendChild(authorDiv);
                    const dateSpan = document.createElement('span'); dateSpan.className = 'post-date'; dateSpan.innerText = '• ' + (new Date(row.date_creation||row.created_at||Date.now())).toLocaleDateString();
                    meta.appendChild(dateSpan);

                    const title = document.createElement('h3'); title.className = 'post-title'; title.innerHTML = `<a href="index.php?controller=post&action=show&id=${encodeURIComponent(row.id_post||row.id)}">${escapeHtml(row.titre||row.title||'Untitled')}</a>`;
                    const excerpt = document.createElement('p'); excerpt.className = 'post-excerpt'; excerpt.innerText = (row.contenu||row.content||'').substring(0,150) + ((row.contenu||'').length>150 ? '...' : '');

                    const footer = document.createElement('div'); footer.className = 'post-footer'; footer.style = 'display:flex; justify-content:space-between; width:100%;';
                    const leftFooter = document.createElement('div'); leftFooter.style = 'display:flex; gap:14px; align-items:center;';
                    const commentsCount = document.createElement('span'); commentsCount.className = 'comment-count'; commentsCount.innerHTML = `<i data-lucide="message-circle" style="width:14px;height:14px;"></i> ${escapeHtml(row.commentaires_count||row.comments_count||0)} commentaires`;
                    leftFooter.appendChild(commentsCount);
                    footer.appendChild(leftFooter);
                    const saveBtn = document.createElement('button'); saveBtn.style = 'background:none;border:none;cursor:pointer;color:#95a5a6;font-size:16px;'; saveBtn.title='Sauvegarder'; saveBtn.innerHTML = '<i class="fa-regular fa-bookmark"></i>'; saveBtn.onclick = (e)=>{ e.stopPropagation(); saveBtn.classList.toggle('saved'); };
                    footer.appendChild(saveBtn);

                    main.appendChild(meta); main.appendChild(title); main.appendChild(excerpt); main.appendChild(footer);

                    postCard.appendChild(left); postCard.appendChild(main);
                    container.appendChild(postCard);
                });
            }

            async function toggleReaction(postId, reaction) {
                try {
                    const res = await fetch('public/api.php?controller=post&action=reaction', { method: 'POST', credentials: 'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: postId, reaction })});
                    if (!res.ok) throw new Error('Reaction failed');
                    await loadPosts();
                } catch (err) { console.error(err); alert('Action non autorisée ou erreur serveur.'); }
            }

            function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]); }); }

            document.addEventListener('DOMContentLoaded', () => {
                renderTopics();
                loadTopApps();
                // initial load - read query params
                const params = new URLSearchParams(window.location.search);
                loadPosts({ search: params.get('search')||'', topic: params.get('topic')||'', sort: params.get('sort')||'latest', page: Number(params.get('page')||1) });

                document.getElementById('forumSearchForm').addEventListener('submit', (e) => {
                    const q = document.getElementById('forumSearchInput').value;
                    loadPosts({ search: q });
                });
            });
            </script>
            
            <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            </style>
        </main>
    </div>
</div>


