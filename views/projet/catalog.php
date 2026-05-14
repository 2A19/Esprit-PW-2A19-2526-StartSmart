<div class="projet-container">
    <div class="projet-header">
        <div>
            <h2>Découvrez les Startups</h2>
            <p>Explorez les projets innovants et rejoignez la communauté entrepreneuriale.</p>
        </div>
        <div class="forum-actions">
            <?php if (function_exists('currentUserId') && currentUserId()): ?>
                <a href="index.php?controller=projet&action=create" class="btn-primary">+ Nouveau Projet</a>
            <?php else: ?>
                <a href="login.php" class="btn-primary">Connectez-vous</a>
            <?php endif; ?>
        </div>
    </div>

    <form id="projetSearchForm" class="projet-searchbar" action="javascript:void(0);">
        <input type="search" id="projetSearchInput" name="search" placeholder="Rechercher un projet...">
        <button type="submit" class="btn-primary">Rechercher</button>
    </form>

    <div class="projet-layout">
        <aside class="projet-sidebar">
            <!-- Categories Filter -->
            <div class="sidebar-box filter-box">
                <h3>Catégories</h3>
                <ul id="categoryList" class="category-list">
                    <!-- categories populated by JS -->
                </ul>
            </div>
            
            <!-- Sort Options -->
            <div class="sidebar-box sort-box">
                <h3>Trier par</h3>
                <form action="index.php" method="GET" class="sort-form">
                    <input type="hidden" name="controller" value="projet">
                    <input type="hidden" name="action" value="index">
                    <?php if(isset($_GET['categorie_id'])) echo '<input type="hidden" name="categorie_id" value="'.htmlspecialchars($_GET['categorie_id']).'">'; ?>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="latest" <?php echo (isset($_GET['sort']) && $_GET['sort']=='latest') ? 'selected' : ''; ?>>Plus récents</option>
                        <option value="trending" <?php echo (isset($_GET['sort']) && $_GET['sort']=='trending') ? 'selected' : ''; ?>>Tendance</option>
                        <option value="most_discussed" <?php echo (isset($_GET['sort']) && $_GET['sort']=='most_discussed') ? 'selected' : ''; ?>>Plus discutés</option>
                        <option value="budget_desc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='budget_desc') ? 'selected' : ''; ?>>Budget élevé</option>
                    </select>
                </form>
            </div>

            <!-- Stats -->
            <div class="sidebar-box stats-box">
                <h3>Statistiques</h3>
                <div style="text-align: center; margin-top: 15px;">
                    <div id="statProjectsCount" style="font-size: 28px; font-weight: 700; color: #0B1C48; margin-bottom: 5px;">0</div>
                    <div style="font-size: 13px; color: #6c757d;">Projets Actifs</div>
                    <div id="statBudgetTotal" style="font-size: 28px; font-weight: 700; color: #0B1C48; margin: 15px 0 5px 0;">$0M</div>
                    <div style="font-size: 13px; color: #6c757d;">Budget Total</div>
                </div>
            </div>
        </aside>

        <!-- Projects Grid -->
        <main>
            <div id="projetGrid" class="projet-grid"><!-- projects rendered here --></div>

            <!-- Pagination -->
            <div id="projetPagination" class="pagination" style="display:none;"></div>
            
            <script>
            async function loadProjets({ search='', categorie_id='', sort='latest', page=1 } = {}) {
                try {
                    const params = new URLSearchParams();
                    if (search) params.set('search', search);
                    if (categorie_id) params.set('categorie_id', categorie_id);
                    if (sort) params.set('sort', sort);
                    params.set('page', page);
                    params.set('format', 'json');
                    const res = await fetch('public/api.php?controller=projet&action=index&' + params.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'include' });
                    const json = await res.json();
                    const projets = json.success ? json.data : (json.projets || []);
                    const categories = json.categories || [];
                    const totalPages = json.totalPages || 1;
                    renderCategories(categories);
                    renderStats(projets);
                    renderGrid(projets);
                    renderPagination(totalPages, page);
                } catch (err) {
                    console.error('Failed to load projects', err);
                }
            }

            function renderCategories(categories) {
                const ul = document.getElementById('categoryList'); ul.innerHTML = '';
                const liAll = document.createElement('li'); liAll.innerHTML = `<a href="javascript:void(0);" data-cat="">Toutes les catégories</a>`; ul.appendChild(liAll);
                categories.forEach(cat => {
                    const li = document.createElement('li');
                    li.innerHTML = `<a href="javascript:void(0);" data-cat="${cat.id}">${escapeHtml(cat.typeprojet||cat.name)}</a>`;
                    ul.appendChild(li);
                });
                ul.querySelectorAll('a').forEach(a => a.addEventListener('click', (e)=>{ const cat = e.target.dataset.cat||''; loadProjets({ categorie_id: cat }); }));
            }

            function renderStats(projets) {
                document.getElementById('statProjectsCount').innerText = projets.length;
                const totalBudget = projets.reduce((s,p)=>s + (Number(p.budget)||0), 0) / 1000000;
                document.getElementById('statBudgetTotal').innerText = '$' + (totalBudget.toFixed(1)) + 'M';
            }

            function renderGrid(projets) {
                const grid = document.getElementById('projetGrid'); grid.innerHTML = '';
                if (!projets || projets.length===0) {
                    grid.innerHTML = `<div class="empty-state"><h3>Aucun projet trouvé</h3><p>Essayez une autre recherche ou lancez votre premier projet.</p></div>`;
                    return;
                }
                projets.forEach(row => {
                    const card = document.createElement('div'); card.className = 'projet-card';
                    card.innerHTML = `
                        <span class="projet-category-badge">${escapeHtml(row.categorie_nom||'Sans catégorie')}</span>
                        <a href="index.php?controller=projet&action=show&id=${encodeURIComponent(row.id)}" class="projet-title">${escapeHtml(row.nomprojet||row.name||'Untitled')}</a>
                        <p class="projet-description">${escapeHtml((row.description||'').substring(0,100))}${(row.description&&row.description.length>100)?'...':''}</p>
                    `;
                    // competences
                    if (row.competences && row.competences.length) {
                        const comps = document.createElement('div'); comps.style = 'margin-top: auto; margin-bottom:15px; display:flex; flex-wrap:wrap; gap:6px;';
                        row.competences.slice(0,3).forEach(c => { const s=document.createElement('span'); s.style='background:#e8f4fd;color:#2980b9;font-size:11px;padding:4px 10px;border-radius:12px;font-weight:bold;border:1px solid #d4e6f1;'; s.innerText = c.nom||c; comps.appendChild(s); });
                        if (row.competences.length>3) { const more=document.createElement('span'); more.style='color:#2980b9;font-size:11px;padding:4px;font-weight:bold;'; more.innerText = '+'+(row.competences.length-3); comps.appendChild(more); }
                        card.appendChild(comps);
                    }
                    // meta and footer
                    const meta = document.createElement('div'); meta.className='projet-meta'; meta.innerHTML = `<div class="projet-budget"><span>💰</span> <strong>${escapeHtml(row.budget||0)} DT</strong></div>`;
                    const author = document.createElement('div'); author.className='projet-author'; author.style='cursor:pointer;'; author.innerHTML = `<span class="avatar-xs">${escapeHtml(((row.auteur_nom||'U').substring(0,1)).toUpperCase())}</span> <span style="color:#3498db;font-weight:bold;">${escapeHtml(row.auteur_nom||'Utilisateur')}</span>`;
                    author.onclick = ()=>{ window.location.href = 'index.php?controller=profile&action=index&id=' + encodeURIComponent(row.auteur_id); };
                    meta.appendChild(author);
                    card.appendChild(meta);

                    const footer = document.createElement('div'); footer.className='projet-footer'; footer.innerHTML = `<div class="projet-reactions"><span style="font-size:12px;color:#6c757d;padding:6px 10px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="message-circle" style="width:14px;height:14px;"></i> ${escapeHtml(row.commentaires_count||0)} commentaires</span></div><div class="projet-actions"><a href="index.php?controller=projet&action=show&id=${encodeURIComponent(row.id)}" class="btn-view">Voir</a></div>`;
                    card.appendChild(footer);
                    document.getElementById('projetGrid').appendChild(card);
                });
            }

            function renderPagination(totalPages, currentPage) {
                const p = document.getElementById('projetPagination');
                p.style.display = totalPages>1 ? 'block' : 'none';
                p.innerHTML = '';
                for (let i=1;i<=totalPages;i++) {
                    const a = document.createElement('a'); a.href = 'javascript:void(0);'; a.innerText = i; if (i===currentPage) a.className='current';
                    a.addEventListener('click', ()=> loadProjets({ page: i }));
                    p.appendChild(a);
                }
            }

            function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]); }); }

            document.getElementById('projetSearchForm').addEventListener('submit', (e)=>{ const q = document.getElementById('projetSearchInput').value; loadProjets({ search: q }); });

            document.addEventListener('DOMContentLoaded', ()=>{ loadProjets(); });
            </script>
        </main>
    </div>
</div>

<script src="projet.js"></script>
<link rel="stylesheet" href="projet.css">


