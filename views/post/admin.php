<div class="bo-header" style="border-bottom: 2px solid #f0f2f5; padding-bottom: 15px; margin-bottom: 20px;">
    <h2>Modération du Forum</h2>
    <div style="display:flex; gap:10px;">
        <a href="index.php?controller=post&action=index" class="btn-secondary">Voir le Forum Public</a>
        <a href="index.php?controller=post&action=create" class="btn-primary" style="background-color: #3498db;">+ Nouveau Sujet</a>
    </div>
</div>

<div class="search-form" style="background: #f9fbfd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e1e8ed;">
    <div style="display:flex; gap:10px; width: 100%;">
        <input type="text" id="searchPost" class="search-input" placeholder="Rechercher un sujet par titre ou auteur..." style="flex:1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="button" onclick="loadPosts()" class="btn-primary">Filtrer</button>
    </div>
</div>

<div style="overflow-x: auto;">
    <table class="bo-table" id="adminPostsTable" style="width: 100%; border-collapse: collapse; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Sujet</th>
                <th style="padding: 12px; text-align: left;">Auteur</th>
                <th style="padding: 12px; text-align: center;">Thème</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
                <th style="padding: 12px; text-align: right;">Interactions</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="adminPostsBody">
            <tr><td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">Chargement...</td></tr>
        </tbody>
    </table>
</div>

<nav class="pagination-bar" id="paginationBar" aria-label="Pagination des sujets" style="margin-top: 20px; text-align: center;"></nav>

<script>
let currentPage = 1;

async function loadPosts(page = 1) {
    const search = document.getElementById('searchPost').value.trim();
    currentPage = page;
    try {
        let url = 'public/api.php?controller=post&action=index&page=' + page + '&format=json';
        if (search) url += '&search=' + encodeURIComponent(search);
        
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'include' });
        const json = await res.json();
        const posts = json.success ? json.data : [];
        const totalPages = json.totalPages || 1;
        
        const tbody = document.getElementById('adminPostsBody');
        tbody.innerHTML = '';
        
        if (!posts || posts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d; font-style: italic;">Aucun sujet trouvé dans le forum.</td></tr>';
            document.getElementById('paginationBar').innerHTML = '';
            return;
        }
        
        posts.forEach(row => {
            const statusBg = (row.statut === 'actif') ? '#eafaf1' : '#fdf2e9';
            const statusColor = (row.statut === 'actif') ? '#27ae60' : '#d35400';
            const statusText = (row.statut === 'actif') ? 'Actif' : (row.statut || 'Inactif');
            
            const tr = document.createElement('tr');
            tr.style = 'border-bottom: 1px solid #eee; transition: background-color 0.2s;';
            tr.innerHTML = `
                <td style="padding: 12px; color: #7f8c8d;">#${row.id_post}</td>
                <td style="padding: 12px;">
                    <div style="font-weight: bold; color: #2c3e50;">
                        <a href="index.php?controller=post&action=show&id=${row.id_post}" style="color: inherit; text-decoration: none;">
                            ${escapeHtml(row.titre)}
                        </a>
                    </div>
                    <div style="font-size: 0.85em; color: #95a5a6; margin-top: 4px;">
                        Publié le ${new Date(row.date_creation).toLocaleString('fr-FR')}
                    </div>
                </td>
                <td style="padding: 12px;">
                    <span style="background: #e8f4f8; color: #2980b9; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        ${escapeHtml(row.auteur_nom || 'Utilisateur')}
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span style="font-size: 0.8em; padding: 3px 6px; border-radius: 4px;">
                        ${escapeHtml(row.topic || 'General')}
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span style="background: ${statusBg}; color: ${statusColor}; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        ${statusText}
                    </span>
                </td>
                <td style="padding: 12px; text-align: right;">
                    <div style="font-size: 0.9em;">
                        💬 ${row.commentaires_count || 0} | 👍 ${row.likes_count || 0} | 👎 ${row.dislikes_count || 0}
                    </div>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <a href="index.php?controller=post&action=edit&id=${row.id_post}" style="margin-right: 5px; padding: 5px 10px; font-size: 0.85em; color: #3498db; text-decoration: none;">Gérer</a>
                    <a href="index.php?controller=post&action=delete&id=${row.id_post}" style="padding: 5px 10px; font-size: 0.85em; color: #e74c3c; text-decoration: none;" onclick="return confirm('Attention : La suppression de ce sujet entraînera la perte de tous ses commentaires.');">Supprimer</a>
                </td>
            `;
            tbody.appendChild(tr);
        });
        
        const paginationBar = document.getElementById('paginationBar');
        paginationBar.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const a = document.createElement('a');
                a.href = '#';
                a.innerText = i;
                a.style = `display: inline-block; padding: 6px 12px; margin: 0 4px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #2c3e50;${i === page ? 'background-color: #3498db; color: white;' : ''}`;
                a.onclick = (e) => { e.preventDefault(); loadPosts(i); };
                paginationBar.appendChild(a);
            }
        }
    } catch (err) { console.error('loadPosts error', err); }
}

function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

document.addEventListener('DOMContentLoaded', () => loadPosts(1));
</script>

