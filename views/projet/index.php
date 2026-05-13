<div class="bo-header">
    <h2>Liste des Projets</h2>
    <div style="display:flex; gap:10px;">
        <button id="btnExportPDF" class="btn-secondary">Exporter PDF</button>
        <a href="index.php?controller=projet&action=create" class="btn-primary">+ Ajouter un Projet</a>
    </div>
</div>

<div class="search-form">
    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher dynamiquement par nom ou numéro...">
</div>

<div class="chart-container">
    <canvas id="projetChart"></canvas>
</div>

    <table class="bo-table" id="projetsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Num</th>
                <th>Nom Projet</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Budget</th>
                <th>Gain</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- rows will be rendered by JS -->
        </tbody>
</table>

<script>
// Graphique Chart.js
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('projetChart').getContext('2d');
    // Fetch projects data from backend via proxy
    async function loadProjectsAndRender() {
        try {
            const res = await fetch('public/api.php?controller=map&action=apiData', { credentials: 'include' });
            const json = await res.json();
            const projets = json.success ? json.data : [];

            // Populate table
            const tbody = document.querySelector('#projetsTable tbody');
            tbody.innerHTML = '';
            if (projets.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Aucun projet trouvé.</td></tr>';
            } else {
                projets.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(row.id)}</td>
                        <td>${escapeHtml(row.num||'')}</td>
                        <td>${escapeHtml(row.nomprojet||row.name||'')}</td>
                        <td>${escapeHtml(row.categorie_nom||'')}</td>
                        <td>${row.auteur_nom ? 'Créé par '+escapeHtml(row.auteur_nom) : '<em>Inconnu</em>'}</td>
                        <td>${escapeHtml(row.datedebut||'')}</td>
                        <td>${escapeHtml(row.datefin||'')}</td>
                        <td>${escapeHtml(row.budget||'')}</td>
                        <td>${escapeHtml(row.gain||'')}</td>
                        <td><a href="index.php?controller=projet&action=show&id=${encodeURIComponent(row.id)}" class="btn-primary">Voir</a></td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Chart stats: use top 6 projects
            const stats = projets.slice(0,6).map(p => ({ nomprojet: p.nomprojet||p.name||'', budget: Number(p.budget||0), gain: Number(p.gain||0) }));
            const labels = stats.map(s => s.nomprojet);
            const budgets = stats.map(s => s.budget);
            const gains = stats.map(s => s.gain);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Budget', data: budgets, backgroundColor: 'rgba(54, 162, 235, 0.6)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 },
                        { label: 'Gain', data: gains, backgroundColor: 'rgba(46, 204, 113, 0.6)', borderColor: 'rgba(46, 204, 113, 1)', borderWidth: 1 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });

        } catch (err) {
            console.error('Failed to load projects', err);
        }
    }

    function escapeHtml(s) { return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]); }); }

    document.addEventListener('DOMContentLoaded', function() {
        loadProjectsAndRender();

        // Export PDF
        document.getElementById('btnExportPDF').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("Liste des Projets", 14, 15);
            doc.autoTable({ html: '#projetsTable', startY: 20 });
            doc.save('projets_export.pdf');
        });

        // Recherche dynamique
        document.getElementById('searchInput').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#projetsTable tbody tr');
            rows.forEach(row => {
                if(row.children.length > 1) {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
                }
            });
        });
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Budget',
                    data: budgets,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Gain',
                    data: gains,
                    backgroundColor: 'rgba(46, 204, 113, 0.6)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Export PDF
    document.getElementById('btnExportPDF').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.text("Liste des Projets", 14, 15);
        doc.autoTable({ html: '#projetsTable', startY: 20 });
        doc.save('projets_export.pdf');
    });

    // Recherche dynamique
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#projetsTable tbody tr');
        
        rows.forEach(row => {
            if(row.children.length > 1) { // Ignorer la ligne "vide" si elle existe
                row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
            }
        });
    });
});
</script>

