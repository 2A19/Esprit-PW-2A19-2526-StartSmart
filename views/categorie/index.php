<div class="bo-header">
    <h2>Liste des Catégories</h2>
    <div style="display:flex; gap:10px;">
        <button id="btnExportPDF" class="btn-secondary">Exporter PDF</button>
        <a href="index.php?controller=categorie&action=create" class="btn-primary">+ Ajouter une Catégorie</a>
    </div>
</div>

<div class="search-form">
    <input type="text" id="searchInputCat" class="search-input" placeholder="Rechercher dynamiquement par type, investisseur ou projet...">
</div>

<div class="chart-container">
    <canvas id="categorieChart"></canvas>
</div>

<table class="bo-table" id="categoriesTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Num</th>
            <th>Type Projet</th>
            <th>Nom Investisseur</th>
            <th>Projet Associé</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $row): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['num']; ?></td>
            <td><?php echo $row['typeprojet']; ?></td>
            <td><?php echo $row['nom_investisseur']; ?></td>
            <td><?php echo $row['nomprojet'] ? $row['nomprojet'] : '<em>Aucun</em>'; ?></td>
            <td>
                <a href="index.php?controller=categorie&action=edit&id=<?php echo $row['id']; ?>" class="btn-edit">Modifier</a>
                <a href="index.php?controller=categorie&action=delete&id=<?php echo $row['id']; ?>" class="btn-danger">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <tr>
            <td colspan="6" style="text-align: center;">Aucune catégorie trouvée.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
// Graphique Chart.js
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('categorieChart').getContext('2d');
    const stats = <?php echo json_encode($stats); ?>;
    
    const labels = stats.map(s => s.typeprojet);
    const dataCounts = stats.map(s => s.total);

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Nombre de catégories',
                    data: dataCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });

    // Export PDF
    document.getElementById('btnExportPDF').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.text("Liste des Catégories", 14, 15);
        doc.autoTable({ html: '#categoriesTable', startY: 20 });
        doc.save('categories_export.pdf');
    });

    // Recherche dynamique
    document.getElementById('searchInputCat').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#categoriesTable tbody tr');
        
        rows.forEach(row => {
            if(row.children.length > 1) { // Ignorer la ligne "vide"
                row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
            }
        });
    });
});
</script>
