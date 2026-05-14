<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once __DIR__ . '/resource/RessourceManager.php';
require_once __DIR__ . '/resource/DemandeAccesManager.php';
require_once __DIR__ . '/resource/SponsorManager.php';

class ResourceController {
    private $ressourceController;
    private $demandeController;
    private $sponsorController;

    public function __construct() {
        $this->ressourceController = new RessourceController();
        $this->demandeController = new DemandeAccesController();
        $this->sponsorController = new SponsorController();
    }

    public function index() {
        $this->ressources();
    }

    public function ressources() {
        $ressources = $this->ressourceController->getAvailable();
        $this->render('frontoffice/ressources-list', [
            'pageTitle' => 'Ressources disponibles',
            'ressources' => $ressources,
            'is_admin' => false,
        ]);
    }

    public function demandes() {
        $demandes = $this->demandeController->index();
        $this->render('frontoffice/demandes-list', [
            'pageTitle' => 'Mes demandes d\'accès',
            'demandes' => $demandes,
            'is_admin' => false,
        ]);
    }

    public function demandeCreate() {
        $ressources = $this->ressourceController->getAvailable();
        $this->render('frontoffice/demande-create', [
            'pageTitle' => 'Nouvelle demande d\'accès',
            'ressources' => $ressources,
            'errors' => [],
            'is_admin' => false,
        ]);
    }

    public function demandeStore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('ressources');
        }

        if ($this->demandeController->store($_POST)) {
            $this->flash('success', 'Demande créée avec succès.');
            $this->redirect('demandes');
        }

        $this->render('frontoffice/demande-create', [
            'pageTitle' => 'Nouvelle demande d\'accès',
            'ressources' => $this->ressourceController->getAvailable(),
            'errors' => $this->demandeController->getErrors(),
            'is_admin' => false,
        ]);
    }

    public function admin() {
        $this->resourceList();
    }

    public function resourceList() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date';
        $ressources = (!empty($search) || $sortBy !== 'date')
            ? $this->ressourceController->searchAndSort($search, $sortBy)
            : $this->ressourceController->index();

        $this->render('backoffice/ressource-list', [
            'pageTitle' => 'Gestion des ressources',
            'ressources' => $ressources,
            'is_admin' => true,
        ]);
    }

    public function resourceCreate() {
        $this->render('backoffice/ressource-create', [
            'pageTitle' => 'Créer une ressource',
            'errors' => [],
            'is_admin' => true,
        ]);
    }

    public function resourceShow() {
        $id = (int)($_GET['id'] ?? 0);
        $ressource = $id ? $this->ressourceController->show($id) : null;
        if (!$ressource) {
            $this->redirect('ressources');
        }

        $html = '<div class="page-header"><h1>' . htmlspecialchars($ressource['nom_ressource']) . '</h1><p>Ressource proposée par ' . htmlspecialchars($ressource['nom_sponsor']) . '.</p></div>'
            . '<div class="card"><div class="resource-detail-grid">'
            . '<p><strong>Type</strong><span>' . htmlspecialchars($ressource['type_ressource']) . '</span></p>'
            . '<p><strong>Disponibilité</strong><span>' . (int)($ressource['quantite_disponible'] - $ressource['quantite_utilisee']) . ' / ' . (int)$ressource['quantite_disponible'] . '</span></p>'
            . '<p><strong>Statut</strong><span>' . htmlspecialchars($ressource['statut']) . '</span></p>'
            . '</div><p style="margin-top:18px;">' . htmlspecialchars($ressource['description'] ?? '') . '</p>'
            . '<div class="btn-group" style="margin-top:20px;"><a class="btn btn-primary" href="index.php?controller=resource&action=demandeCreate&ressource=' . (int)$ressource['id_ressource'] . '">Demander l\'accès</a>'
            . '<a class="btn btn-secondary" href="index.php?controller=resource&action=ressources">Retour</a></div></div>';

        $this->renderInline($ressource['nom_ressource'], $html, false);
    }

    public function resourceStore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('resourceList');
        }

        if ($this->ressourceController->store($_POST)) {
            $this->flash('success', 'Ressource créée avec succès.');
            $this->redirect('resourceList');
        }

        $this->render('backoffice/ressource-create', [
            'pageTitle' => 'Créer une ressource',
            'errors' => $this->ressourceController->getErrors(),
            'is_admin' => true,
        ]);
    }

    public function resourceEdit() {
        $id = (int)($_GET['id'] ?? 0);
        $ressource = $id ? $this->ressourceController->show($id) : null;
        if (!$ressource) {
            $this->redirect('resourceList');
        }

        $this->render('backoffice/ressource-edit', [
            'pageTitle' => 'Modifier une ressource',
            'ressource' => $ressource,
            'errors' => [],
            'is_admin' => true,
        ]);
    }

    public function resourceUpdate() {
        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id && $this->ressourceController->update($id, $_POST)) {
            $this->flash('success', 'Ressource mise à jour avec succès.');
            $this->redirect('resourceList');
        }

        $this->flash('error', $this->ressourceController->getErrors() ?: ['Erreur lors de la mise à jour.']);
        $this->redirect('resourceEdit&id=' . $id);
    }

    public function resourceDelete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->flash($id && $this->ressourceController->delete($id) ? 'success' : 'error', $id ? 'Ressource supprimée.' : 'Erreur lors de la suppression.');
        $this->redirect('resourceList');
    }

    public function demandeList() {
        $this->render('backoffice/demande-list', [
            'pageTitle' => 'Gestion des demandes d\'accès',
            'demandes' => $this->demandeController->index(),
            'is_admin' => true,
        ]);
    }

    public function demandeDetail() {
        $id = (int)($_GET['id'] ?? 0);
        $demande = $id ? $this->demandeController->show($id) : null;
        if (!$demande) {
            $this->redirect('demandes');
        }

        $this->renderInline('Détails de la demande', $this->demandeDetailHtml($demande), true);
    }

    public function demandeAccepter() {
        $id = (int)($_GET['id'] ?? 0);
        $ok = $id && $this->demandeController->accepter($id);
        $this->flash($ok ? 'success' : 'error', $ok ? 'Demande acceptée avec succès.' : ($this->demandeController->getErrors() ?: ['Erreur lors de l\'acceptation.']));
        $this->redirect('demandeList');
    }

    public function demandeRefuser() {
        $id = (int)($_GET['id'] ?? 0);
        $demande = $id ? $this->demandeController->show($id) : null;
        if (!$demande) {
            $this->redirect('demandeList');
        }

        $this->render('backoffice/demande-refuser', [
            'pageTitle' => 'Refuser une demande',
            'demande' => $demande,
            'errors' => [],
            'is_admin' => true,
        ]);
    }

    public function demandeRefuserStore() {
        $id = (int)($_GET['id'] ?? 0);
        $raison = $_POST['raison_refus'] ?? '';
        $ok = $_SERVER['REQUEST_METHOD'] === 'POST' && $id && $this->demandeController->refuser($id, $raison);
        $this->flash($ok ? 'success' : 'error', $ok ? 'Demande refusée.' : 'Erreur lors du refus.');
        $this->redirect('demandeList');
    }

    public function sponsorList() {
        $this->render('backoffice/sponsor-list', [
            'pageTitle' => 'Gestion des sponsors',
            'sponsors' => $this->sponsorController->index(),
            'is_admin' => true,
        ]);
    }

    public function sponsorCreate() {
        $this->render('backoffice/sponsor-create', [
            'pageTitle' => 'Créer un sponsor',
            'errors' => [],
            'is_admin' => true,
        ]);
    }

    public function sponsorStore() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->sponsorController->store($_POST)) {
            $this->flash('success', 'Sponsor créé avec succès.');
            $this->redirect('sponsorList');
        }

        $this->render('backoffice/sponsor-create', [
            'pageTitle' => 'Créer un sponsor',
            'errors' => $this->sponsorController->getErrors(),
            'is_admin' => true,
        ]);
    }

    public function sponsorEdit() {
        $id = (int)($_GET['id'] ?? 0);
        $sponsor = $id ? $this->sponsorController->show($id) : null;
        if (!$sponsor) {
            $this->redirect('sponsorList');
        }

        $this->render('backoffice/sponsor-edit', [
            'pageTitle' => 'Modifier un sponsor',
            'sponsor' => $sponsor,
            'errors' => [],
            'is_admin' => true,
        ]);
    }

    public function sponsorUpdate() {
        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id && $this->sponsorController->update($id, $_POST)) {
            $this->flash('success', 'Sponsor mis à jour avec succès.');
            $this->redirect('sponsorList');
        }

        $this->flash('error', $this->sponsorController->getErrors() ?: ['Erreur lors de la mise à jour du sponsor.']);
        $this->redirect('sponsorEdit&id=' . $id);
    }

    public function sponsorDelete() {
        $id = (int)($_GET['id'] ?? 0);
        $ok = $id && $this->sponsorController->delete($id);
        $this->flash($ok ? 'success' : 'error', $ok ? 'Sponsor supprimé.' : 'Erreur lors de la suppression.');
        $this->redirect('sponsorList');
    }

    private function render(string $view, array $vars = []): void {
        extract($vars);
        ob_start();
        echo '<section class="resource-module' . (!empty($is_admin) ? ' resource-admin' : '') . '">';
        $this->renderFlash();
        if (!empty($is_admin)) {
            require 'views/resource/backoffice/admin-menu.php';
        }
        require 'views/resource/' . $view . '.php';
        echo '</section>';
        $viewContent = ob_get_clean();
        require 'views/layout.php';
    }

    private function renderInline(string $title, string $html, bool $isAdmin = false): void {
        $pageTitle = $title;
        $is_admin = $isAdmin;
        ob_start();
        echo '<section class="resource-module' . ($isAdmin ? ' resource-admin' : '') . '">';
        $this->renderFlash();
        if ($isAdmin) {
            require 'views/resource/backoffice/admin-menu.php';
        }
        echo $html;
        echo '</section>';
        $viewContent = ob_get_clean();
        require 'views/layout.php';
    }

    private function renderFlash(): void {
        foreach (['success', 'error', 'warning'] as $type) {
            if (empty($_SESSION['resource_' . $type])) {
                continue;
            }
            foreach ((array) $_SESSION['resource_' . $type] as $message) {
                echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars((string) $message) . '</div>';
            }
            unset($_SESSION['resource_' . $type]);
        }
    }

    private function flash(string $type, $message): void {
        foreach ((array) $message as $item) {
            $_SESSION['resource_' . $type][] = $item;
        }
    }

    private function redirect(string $action): void {
        header('Location: index.php?controller=resource&action=' . $action);
        exit;
    }

    private function demandeDetailHtml(array $demande): string {
        return '<div class="page-header"><h1>Détails de la demande</h1><p>Suivi de la demande d\'accès ressource.</p></div>'
            . '<div class="card"><div class="resource-detail-grid">'
            . '<p><strong>Utilisateur</strong><span>' . htmlspecialchars($demande['nom_utilisateur']) . '</span></p>'
            . '<p><strong>Ressource</strong><span>' . htmlspecialchars($demande['nom_ressource']) . '</span></p>'
            . '<p><strong>Sponsor</strong><span>' . htmlspecialchars($demande['nom_sponsor']) . '</span></p>'
            . '<p><strong>Statut</strong><span>' . htmlspecialchars($demande['statut_demande']) . '</span></p>'
            . '</div></div>';
    }
}
?>
