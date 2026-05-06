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
            <?php foreach ($projets as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['num']; ?></td>
                <td><?php echo $row['nomprojet']; ?></td>
                <td><?php echo htmlspecialchars($row['categorie_nom']); ?></td>
                <td><?php echo $row['auteur_nom'] ? "Créé par " . htmlspecialchars($row['auteur_nom']) : '<em>Inconnu</em>'; ?></td>
                <td><?php echo $row['datedebut']; ?></td>
                <td><?php echo $row['datefin']; ?></td>
                <td><?php echo $row['budget']; ?></td>
                <td><?php echo $row['gain']; ?></td>
                <td>
                    <?php if (function_exists('isAdmin') && function_exists('currentUserId') && (isAdmin() || currentUserId() === (int)$row['auteur_id'])): ?>
                        <a href="index.php?controller=projet&action=edit&id=<?php echo $row['id']; ?>" class="btn-edit">Modifier</a>
                        <a href="index.php?controller=projet&action=delete&id=<?php echo $row['id']; ?>" class="btn-danger">Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php if (empty($projets)): ?>
        <tr>
            <td colspan="10" style="text-align: center;">Aucun projet trouvé.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
// Graphique Chart.js
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('projetChart').getContext('2d');
    const stats = <?php echo json_encode($stats); ?>;
    
    const labels = stats.map(s => s.nomprojet);
    const budgets = stats.map(s => s.budget);
    const gains = stats.map(s => s.gain);

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
