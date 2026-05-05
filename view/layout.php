<!DOCTYPE html>
<html lang="fr" class="<?php echo (isset($is_admin) && $is_admin === true) ? 'admin-page' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'StartSmart - Gestion des Ressources'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?php echo (isset($is_admin) && $is_admin === true) ? 'admin-page' : ''; ?>">
    <header>
        <div class="container">
            <div class="logo">StartSmart</div>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="index.php?page=ressources">Ressources</a>
                <a href="index.php?page=demandes">Mes Demandes</a>
                <a href="index.php?page=backoffice">Admin</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php
        // Afficher les messages d'alerte
        if (isset($_SESSION['success']) && !empty($_SESSION['success'])):
            foreach ($_SESSION['success'] as $msg):
        ?>
                <div class="alert alert-success">
                    <strong>Succès!</strong> <?php echo htmlspecialchars($msg); ?>
                </div>
        <?php
            endforeach;
            unset($_SESSION['success']);
        endif;
        ?>

        <?php
        if (isset($_SESSION['error']) && !empty($_SESSION['error'])):
            foreach ($_SESSION['error'] as $msg):
        ?>
                <div class="alert alert-error">
                    <strong>Erreur!</strong> <?php echo htmlspecialchars($msg); ?>
                </div>
        <?php
            endforeach;
            unset($_SESSION['error']);
        endif;
        ?>

        <?php
        if (isset($_SESSION['warning']) && !empty($_SESSION['warning'])):
            foreach ($_SESSION['warning'] as $msg):
        ?>
                <div class="alert alert-warning">
                    <strong>Attention!</strong> <?php echo htmlspecialchars($msg); ?>
                </div>
        <?php
            endforeach;
            unset($_SESSION['warning']);
        endif;
        ?>

        <?php
        // Afficher le menu admin si on est en mode admin
        if (isset($is_admin) && $is_admin === true):
            include __DIR__ . '/backoffice/admin-menu.php';
        endif;
        ?>

        <!-- Le contenu spécifique à chaque page sera affiché ici -->
</body>
</html>
