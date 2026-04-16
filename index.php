<?php
/**
 * index.php - Point d'entrée principal de l'application
 * Routeur pour diriger les requêtes vers les contrôleurs appropriés
 */

session_start();

// Charger les contrôleurs
require_once __DIR__ . '/controller/RessourceController.php';
require_once __DIR__ . '/controller/DemandeAccesController.php';

// Initialiser les contrôleurs
$ressourceController = new RessourceController();
$demandeController = new DemandeAccesController();

// Récupérer la page demandée
$page = $_GET['page'] ?? 'accueil';

// Initialiser les variables pour les vues
$title = 'StartSmart';
$ressources = [];
$demandes = [];
$ressource = [];
$demande = [];
$errors = [];
$success = [];

// Gestion des routes
switch ($page) {
    // ============================================
    // FRONTOFFICE - RESSOURCES
    // ============================================
    case 'ressources':
        $title = 'Ressources Disponibles';
        $ressources = $ressourceController->getAvailable();
        ob_start();
        include __DIR__ . '/view/layout.php';
        include __DIR__ . '/view/frontoffice/ressources-list.php';
        ob_end_flush();
        exit;

    // ============================================
    // FRONTOFFICE - DEMANDES D'ACCÈS
    // ============================================
    case 'demandes':
        $title = 'Mes Demandes d\'Accès';
        $demandes = $demandeController->index();
        ob_start();
        include __DIR__ . '/view/layout.php';
        include __DIR__ . '/view/frontoffice/demandes-list.php';
        ob_end_flush();
        exit;

    case 'demande-create':
        $title = 'Nouvelle Demande d\'Accès';
        $ressources = $ressourceController->getAvailable();
        $errors = [];
        ob_start();
        include __DIR__ . '/view/layout.php';
        include __DIR__ . '/view/frontoffice/demande-create.php';
        ob_end_flush();
        exit;

    case 'demande-store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($demandeController->store($_POST)) {
                $_SESSION['success'][] = 'Demande créée avec succès!';
                header('Location: index.php?page=demandes');
                exit;
            } else {
                $_SESSION['error'] = $demandeController->getErrors();
                $errors = $_SESSION['error'];
                $ressources = $ressourceController->getAvailable();
                $title = 'Nouvelle Demande d\'Accès';
                ob_start();
                include __DIR__ . '/view/layout.php';
                include __DIR__ . '/view/frontoffice/demande-create.php';
                ob_end_flush();
                exit;
            }
        }
        header('Location: index.php?page=ressources');
        exit;

    // ============================================
    // BACKOFFICE - RESSOURCES
    // ============================================
    case 'backoffice':
    case 'ressource-list':
        $title = 'Gestion des Ressources';
        $ressources = $ressourceController->index();
        ob_start();
        include __DIR__ . '/view/layout.php';
        include __DIR__ . '/view/backoffice/ressource-list.php';
        ob_end_flush();
        exit;

    case 'ressource-create':
        $title = 'Créer une Ressource';
        $errors = [];
        ob_start();
        include __DIR__ . '/view/layout.php';
        include __DIR__ . '/view/backoffice/ressource-create.php';
        ob_end_flush();
        exit;

    case 'ressource-store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($ressourceController->store($_POST)) {
                $_SESSION['success'][] = 'Ressource créée avec succès!';
                header('Location: index.php?page=ressource-list');
                exit;
            } else {
                $_SESSION['error'] = $ressourceController->getErrors();
                $errors = $_SESSION['error'];
                $title = 'Créer une Ressource';
                ob_start();
                include __DIR__ . '/view/layout.php';
                include __DIR__ . '/view/backoffice/ressource-create.php';
                ob_end_flush();
                exit;
            }
        }
        header('Location: index.php?page=ressource-list');
        exit;

    case 'ressource-edit':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $ressource = $ressourceController->show($id);
            if ($ressource) {
                $title = 'Modifier une Ressource';
                $errors = [];
                ob_start();
                include __DIR__ . '/view/layout.php';
                include __DIR__ . '/view/backoffice/ressource-edit.php';
                ob_end_flush();
                exit;
            }
        }
        header('Location: index.php?page=ressource-list');
        exit;

    case 'ressource-update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_GET['id'] ?? null;
            if ($id && $ressourceController->update($id, $_POST)) {
                $_SESSION['success'][] = 'Ressource mise à jour avec succès!';
                header('Location: index.php?page=ressource-list');
                exit;
            } else {
                $_SESSION['error'] = $ressourceController->getErrors();
                header('Location: index.php?page=ressource-edit&id=' . $id);
                exit;
            }
        }
        header('Location: index.php?page=ressource-list');
        exit;

    case 'ressource-delete':
        $id = $_GET['id'] ?? null;
        if ($id && $ressourceController->delete($id)) {
            $_SESSION['success'][] = 'Ressource supprimée avec succès!';
        } else {
            $_SESSION['error'][] = 'Erreur lors de la suppression';
        }
        header('Location: index.php?page=ressource-list');
        exit;

    // ============================================
    // BACKOFFICE - DEMANDES D'ACCÈS
    // ============================================
    case 'demande-list':
        $title = 'Gestion des Demandes d\'Accès';
        $demandes = $demandeController->index();
        ob_start();
        include __DIR__ . '/view/layout.php';
        include __DIR__ . '/view/backoffice/demande-list.php';
        ob_end_flush();
        exit;

    case 'demande-detail':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $demande = $demandeController->show($id);
            if ($demande) {
                $title = 'Détails de la Demande';
                ob_start();
                include __DIR__ . '/view/layout.php';
                // Vous pouvez créer une vue détail
                echo '<div class="container"><div class="card">';
                echo '<h2>Détails de la Demande</h2>';
                echo '<p><strong>Utilisateur:</strong> ' . htmlspecialchars($demande['nom_utilisateur']) . '</p>';
                echo '<p><strong>Ressource:</strong> ' . htmlspecialchars($demande['nom_ressource']) . '</p>';
                echo '<p><strong>Statut:</strong> ' . htmlspecialchars($demande['statut_demande']) . '</p>';
                echo '</div></div>';
                ob_end_flush();
                exit;
            }
        }
        header('Location: index.php?page=demandes');
        exit;

    case 'demande-accepter':
        $id = $_GET['id'] ?? null;
        if ($id && $demandeController->accepter($id)) {
            $_SESSION['success'][] = 'Demande acceptée avec succès!';
        } else {
            $_SESSION['error'][] = 'Erreur lors de l\'acceptation';
        }
        header('Location: index.php?page=demande-list');
        exit;

    case 'demande-refuser':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $demande = $demandeController->show($id);
            if ($demande) {
                $title = 'Refuser une Demande';
                $errors = [];
                ob_start();
                include __DIR__ . '/view/layout.php';
                include __DIR__ . '/view/backoffice/demande-refuser.php';
                ob_end_flush();
                exit;
            }
        }
        header('Location: index.php?page=demande-list');
        exit;

    case 'demande-refuser-store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_GET['id'] ?? null;
            $raison = $_POST['raison_refus'] ?? '';
            if ($id && $demandeController->refuser($id, $raison)) {
                $_SESSION['success'][] = 'Demande refusée';
                header('Location: index.php?page=demande-list');
                exit;
            } else {
                $_SESSION['error'][] = 'Erreur lors du refus';
                header('Location: index.php?page=demande-refuser&id=' . $id);
                exit;
            }
        }
        header('Location: index.php?page=demande-list');
        exit;

    // ============================================
    // PAGE D'ACCUEIL
    // ============================================
    default:
        $title = 'StartSmart - Gestion des Ressources et Sponsors';
        ob_start();
        include __DIR__ . '/view/layout.php';
        ?>
        <div class="page-header">
            <h1>Bienvenue sur StartSmart</h1>
            <p>Plateforme de gestion des ressources et sponsors pour les startups</p>
        </div>

        <div class="row">
            <div class="stat-card">
                <div class="label">Ressources Disponibles</div>
                <div class="number"><?php echo count($ressourceController->getAvailable()); ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Demandes en Attente</div>
                <div class="number"><?php echo count(array_filter($demandeController->index(), function($d) { return $d['statut_demande'] === 'en_attente'; })); ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Demandes Acceptées</div>
                <div class="number"><?php echo count(array_filter($demandeController->index(), function($d) { return $d['statut_demande'] === 'acceptee'; })); ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Navigation Rapide</h2>
            </div>
            <div class="row">
                <div class="col">
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 4px; text-align: center;">
                        <h3 style="color: var(--primary-dark); margin-top: 0;">👥 Pour les Start-Upers</h3>
                        <p>Consultez les ressources disponibles et déposez vos demandes d'accès</p>
                        <a href="index.php?page=ressources" class="btn btn-primary">Voir les Ressources</a>
                    </div>
                </div>
                <div class="col">
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 4px; text-align: center;">
                        <h3 style="color: var(--primary-dark); margin-top: 0;">⚙️ Pour les Sponsors</h3>
                        <p>Gérez vos ressources et traitez les demandes d'accès</p>
                        <a href="index.php?page=demande-list" class="btn btn-secondary">Gérer les Demandes</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        ob_end_flush();
        exit;
}
?>
