<div class="bo-header" style="border-bottom: 2px solid #f0f2f5; padding-bottom: 15px; margin-bottom: 20px;">
    <h2>Modération des Projets</h2>
    <div style="display:flex; gap:10px;">
        <a href="index.php?controller=projet&action=index" class="btn-secondary">Voir le Catalogue Public</a>
        <a href="index.php?controller=projet&action=create" class="btn-primary" style="background-color: #3498db;">+ Nouveau Projet</a>
    </div>
</div>

<div class="search-form" style="background: #f9fbfd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e1e8ed;">
    <div style="display:flex; gap:10px; width: 100%;">
        <input type="text" id="searchProjet" class="search-input" placeholder="Rechercher un projet par nom ou description..." style="flex:1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="button" onclick="loadProjets()" class="btn-primary">Filtrer</button>
    </div>
</div>

<div style="overflow-x: auto;">
    <table class="bo-table" id="adminProjetsTable" style="width: 100%; border-collapse: collapse; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Startup / Projet</th>
                <th style="padding: 12px; text-align: left;">Auteur</th>
                <th style="padding: 12px; text-align: center;">Catégorie</th>
                <th style="padding: 12px; text-align: center;">Budget</th>
                <th style="padding: 12px; text-align: right;">Interactions</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="adminProjetsBody">
            <tr><td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">Chargement...</td></tr>
        </tbody>
    </table>
</div>

<nav class="pagination-bar" id="paginationBar" aria-label="Pagination des projets" style="margin-top: 20px; text-align: center;"></nav>

<script>
let currentPage = 1;

async function loadProjets(page = 1) {
    const search = document.getElementById('searchProjet').value.trim();
    currentPage = page;
    try {
        let url = 'public/api.php?controller=projet&action=index&page=' + page + '&format=json';
        if (search) url += '&search=' + encodeURIComponent(search);
        
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'include' });
        const json = await res.json();
        const projets = json.success ? json.data : [];
        const totalPages = json.totalPages || 1;
        
        const tbody = document.getElementById('adminProjetsBody');
        tbody.innerHTML = '';
        
        if (!projets || projets.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d; font-style: italic;">Aucun projet trouvé.</td></tr>';
            document.getElementById('paginationBar').innerHTML = '';
            return;
        }
        
        projets.forEach(row => {
            const tr = document.createElement('tr');
            tr.style = 'border-bottom: 1px solid #eee; transition: background-color 0.2s;';
            tr.innerHTML = `
                <td style="padding: 12px; color: #7f8c8d;">#${row.id}</td>
                <td style="padding: 12px;">
                    <a href="index.php?controller=projet&action=show&id=${row.id}" style="color: #2c3e50; text-decoration: none; font-weight: bold;">
                        ${row.nomprojet}
                    </a>
                </td>
                <td style="padding: 12px;">
                    <span style="background: #e8f4f8; color: #2980b9; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        ${row.auteur_nom || 'Utilisateur'}
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span style="background: #f4f6f7; color: #34495e; padding: 3px 8px; border-radius: 4px; border: 1px solid #bdc3c7; font-size: 0.85em;">
                        ${row.categorie_nom || 'Général'}
                    </span>
                </td>
                <td style="padding: 12px; text-align: center; font-weight: bold; color: #27ae60;">
                    ${Number(row.budget).toLocaleString('fr-FR')} €
                </td>
                <td style="padding: 12px; text-align: right;">
                    <div style="font-size: 0.9em;">
                        💬 ${row.commentaires_count || 0} | 👍 ${row.likes_count || 0} | 👎 ${row.dislikes_count || 0}
                    </div>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <a href="index.php?controller=projet&action=edit&id=${row.id}" style="margin-right: 5px; padding: 5px 10px; font-size: 0.85em; color: #3498db; text-decoration: none;">Modifier</a>
                    <a href="index.php?controller=projet&action=delete&id=${row.id}" style="padding: 5px 10px; font-size: 0.85em; color: #e74c3c; text-decoration: none;" onclick="return confirm('Attention : La suppression de ce projet est irréversible.');">Supprimer</a>
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
                a.onclick = (e) => { e.preventDefault(); loadProjets(i); };
                paginationBar.appendChild(a);
            }
        }
    } catch (err) { console.error('loadProjets error', err); }
}

document.addEventListener('DOMContentLoaded', () => loadProjets(1));
</script>

