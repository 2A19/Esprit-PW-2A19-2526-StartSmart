<?php
/**
 * test-connexion.php - Script de test de connexion à la base de données
 * Accédez à http://localhost/startsmart/test-connexion.php
 */

require_once __DIR__ . '/config/Database.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Connexion - StartSmart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #1d2f5a;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <h1>Test Connexion - StartSmart</h1>

    <?php
    try {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        echo '<div class="success">';
        echo '<strong>✓ Connexion réussie!</strong><br>';
        echo 'Connecté à la base de données: startsmart';
        echo '</div>';
        
        // Tester une requête
        $stmt = $connection->prepare("SELECT COUNT(*) as total FROM sponsors");
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo '<div class="success">';
        echo '<strong>✓ Requête test réussie!</strong><br>';
        echo 'Nombre de sponsors: ' . $result['total'];
        echo '</div>';
        
        // Afficher les statistiques
        echo '<h2>Statistiques de la Base de Données</h2>';
        
        $tables = ['sponsors', 'ressources', 'utilisateurs', 'demandes_acces'];
        
        echo '<table>';
        echo '<tr><th>Table</th><th>Nombre d\'enregistrements</th></tr>';
        
        foreach ($tables as $table) {
            $stmt = $connection->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $row = $stmt->fetch();
            echo '<tr><td>' . $table . '</td><td>' . $row['count'] . '</td></tr>';
        }
        
        echo '</table>';
        
        // Afficher quelques données
        echo '<h2>Sponsors</h2>';
        $stmt = $connection->prepare("SELECT * FROM sponsors LIMIT 3");
        $stmt->execute();
        $sponsors = $stmt->fetchAll();
        
        if (!empty($sponsors)) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Nom</th><th>Email</th><th>Statut</th></tr>';
            foreach ($sponsors as $sponsor) {
                echo '<tr>';
                echo '<td>' . $sponsor['id_sponsor'] . '</td>';
                echo '<td>' . htmlspecialchars($sponsor['nom_sponsor']) . '</td>';
                echo '<td>' . htmlspecialchars($sponsor['email_sponsor']) . '</td>';
                echo '<td>' . $sponsor['statut'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        
        echo '<br><a href="index.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #7dd442; color: white; text-decoration: none; border-radius: 4px;">Aller à l\'accueil</a>';
        
    } catch (Exception $e) {
        echo '<div class="error">';
        echo '<strong>✗ Erreur de connexion</strong><br>';
        echo $e->getMessage();
        echo '</div>';
    }
    ?>
</body>
</html>
