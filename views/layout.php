<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'StartSmart - Plateforme Startup'; ?></title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jsPDF and AutoTable for PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body>

    <header class="navbar">
        <div class="logo-container">
            <a href="index.php"><img src="logo.png" alt="Logo StartSmart" class="logo-img"></a>
        </div>
        
        <nav class="nav-links">
            <div class="dropdown">
                <a href="#" class="nav-btn dropbtn">Projet ▾</a>
                <div class="dropdown-content">
                    <a href="index.php?controller=projet&action=index">Projets</a>
                    <a href="index.php?controller=categorie&action=index">Catégories</a>
                </div>
            </div>
            <a href="achref.html" class="nav-btn">achref</a>
            <a href="youssef.html" class="nav-btn">youssef</a>
            <a href="gahgouh.html" class="nav-btn">gahgouh</a>
            <a href="bakali.html" class="nav-btn">bakali</a>
            <a href="roua.html" class="nav-btn">roua</a>
            <a href="#" class="btn-get-started">GET STARTED</a>
        </nav>
    </header>

    <div class="main-content <?php echo ($controller != 'home') ? 'container-box' : ''; ?>">
        <?php 
        if (isset($viewContent)) {
            echo $viewContent;
        }
        ?>
    </div>

</body>
</html>
