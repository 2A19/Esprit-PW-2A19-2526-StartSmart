<div class="bo-header" style="border-bottom: 2px solid #f0f2f5; padding-bottom: 15px; margin-bottom: 20px;">
    <h2>Modération des Catégories</h2>
    <div style="display:flex; gap:10px;">
        <button id="btnExportPDF" class="btn-secondary">Exporter PDF</button>
        <?php if (isAdmin()): ?>
            <a href="index.php?controller=categorie&action=create" class="btn-primary" style="background-color: #3498db;">+ Nouvelle Catégorie</a>
        <?php endif; ?>
    </div>
</div>

<div style="display: flex; gap: 20px; margin-bottom: 20px; align-items: flex-start;">
    <!-- Formulaire de recherche -->
    <div class="search-form" style="background: #f9fbfd; padding: 15px; border-radius: 8px; border: 1px solid #e1e8ed; flex: 2;">
        <input type="text" id="searchInputCat" class="search-input" placeholder="Rechercher dynamiquement par type ou investisseur..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    
    <!-- Mini graphique -->
    <div class="chart-container" style="background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #e1e8ed; box-shadow: 0 1px 3px rgba(0,0,0,0.05); flex: 1; max-width: 200px; height: 120px;">
        <canvas id="categorieChart"></canvas>
    </div>
</div>

<div style="overflow-x: auto;">
    <table class="bo-table" id="categoriesTable" style="width: 100%; border-collapse: collapse; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 12px; text-align: left;">ID / Num</th>
                <th style="padding: 12px; text-align: left;">Type Projet</th>
                <th style="padding: 12px; text-align: left;">Investisseur</th>
                <th style="padding: 12px; text-align: center;">Projets Associés</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $row): ?>
            <tr style="border-bottom: 1px solid #eee; transition: background-color 0.2s;">
                <td style="padding: 12px; color: #7f8c8d;">
                    <strong>#<?php echo $row['id']; ?></strong><br>
                    <span style="font-size: 0.85em;"><?php echo htmlspecialchars($row['num'] ?? ''); ?></span>
                </td>
                <td style="padding: 12px; font-weight: bold; color: #2c3e50;">
                    <?php echo htmlspecialchars($row['typeprojet']); ?>
                </td>
                <td style="padding: 12px;">
                    <span style="background: #e8f4f8; color: #2980b9; padding: 4px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        <?php echo htmlspecialchars($row['nom_investisseur']); ?>
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <span style="background: #f4f6f7; color: #34495e; padding: 4px 10px; border-radius: 12px; border: 1px solid #bdc3c7; font-weight: bold;">
                        <?php echo isset($row['projets_count']) ? (int) $row['projets_count'] : 0; ?>
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <?php if (isAdmin()): ?>
                        <a href="index.php?controller=categorie&action=edit&id=<?php echo $row['id']; ?>" class="btn-edit" style="margin-right: 5px; padding: 5px 10px; font-size: 0.85em;">Modifier</a>
                        <a href="index.php?controller=categorie&action=delete&id=<?php echo $row['id']; ?>" class="btn-danger" style="padding: 5px 10px; font-size: 0.85em;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">Supprimer</a>
                    <?php else: ?>
                        <em style="color: #7f8c8d; font-size: 0.85em;">Lecture seule</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #7f8c8d; font-style: italic;">Aucune catégorie trouvée.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

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
            plugins: {
                legend: {
                    display: false // Hide legend to save space
                }
            }
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
