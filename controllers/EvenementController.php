<?php

declare(strict_types=1);

namespace Controllers;

use Models\Evenement;
use Models\Participant;

class EvenementController
{
    private Evenement $evenementModel;
    private Participant $participantModel;

    public function __construct()
    {
        $this->evenementModel = new Evenement();
        $this->participantModel = new Participant();
    }

    public function index(): void
    {
        $pageTitle = 'SmartSmart - Evenements';
        $activeNav = 'evenements';
        $evenements = $this->evenementModel->all();
        $participantCountByEvent = $this->participantModel->countByEventIds(array_column($evenements, 'id'));
        $flashSuccess = $this->pullFlash('success');
        $basePath = $this->basePath();

        require BASE_PATH . '/views/layouts/header.php';
        require BASE_PATH . '/views/evenement/index.php';
        require BASE_PATH . '/views/layouts/footer.php';
    }

    public function create(): void
    {
        $this->redirectToBackofficeEvents();
    }

    public function store(): void
    {
        $redirectTarget = (string) ($_POST['redirect_target'] ?? '');

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirectToBackofficeEvents();
        }

        $formData = $this->extractEventFormData();
        $errors = $this->validateEventData($formData);

        if ($errors !== []) {
            if ($redirectTarget === 'backoffice_events') {
                $_SESSION['backoffice']['create_errors'] = $errors;
                $_SESSION['backoffice']['create_old'] = $formData;
                $this->setFlash('error', 'Creation impossible. Corrigez les champs en rouge.');
                $this->redirectToUrl($this->basePath() . '/index.php?route=backoffice/index&page=events');
            }

            $pageTitle = 'SmartSmart - Creer evenement';
            $activeNav = 'evenements';
            $basePath = $this->basePath();

            require BASE_PATH . '/views/layouts/header.php';
            require BASE_PATH . '/views/evenement/create.php';
            require BASE_PATH . '/views/layouts/footer.php';
            return;
        }

        $this->evenementModel->create($formData);
        $this->setFlash('success', 'Evenement cree avec succes.');

        if ($redirectTarget === 'backoffice_events') {
            $this->redirectToUrl($this->basePath() . '/index.php?route=backoffice/index&page=events');
        }

        $this->redirectToBackofficeEvents();
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $evenement = $this->evenementModel->find($id);

        if ($evenement === null) {
            $this->setFlash('success', 'Evenement introuvable.');
            $this->redirect('evenement/index');
        }

        $participants = $this->participantModel->allByEventId($id);
        $flashSuccess = $this->pullFlash('success');
        $pageTitle = 'SmartSmart - Detail evenement';
        $activeNav = 'evenements';
        $basePath = $this->basePath();

        require BASE_PATH . '/views/layouts/header.php';
        require BASE_PATH . '/views/evenement/show.php';
        require BASE_PATH . '/views/layouts/footer.php';
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $evenement = $this->evenementModel->find($id);

        if ($evenement === null) {
            $this->setFlash('success', 'Evenement introuvable.');
            $this->redirect('evenement/index');
        }

        $this->redirectToBackofficeEvents(['edit_id' => (string) $id]);
    }

    public function update(): void
    {
        $redirectTarget = (string) ($_POST['redirect_target'] ?? '');

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirectToBackofficeEvents();
        }

        $id = (int) ($_GET['id'] ?? 0);
        $evenement = $this->evenementModel->find($id);
        if ($evenement === null) {
            $this->setFlash('success', 'Evenement introuvable.');
            if ($redirectTarget === 'backoffice_events') {
                $this->redirectToUrl($this->basePath() . '/index.php?route=backoffice/index&page=events');
            }
            $this->redirectToBackofficeEvents();
        }

        $formData = $this->extractEventFormData();
        $errors = $this->validateEventData($formData);

        if ($errors !== []) {
            if ($redirectTarget === 'backoffice_events') {
                $_SESSION['backoffice']['edit_errors'] = $errors;
                $_SESSION['backoffice']['edit_old'] = $formData;
                $_SESSION['backoffice']['edit_id'] = $id;
                $this->setFlash('error', 'Mise a jour impossible. Corrigez les champs en rouge.');
                $this->redirectToUrl($this->basePath() . '/index.php?route=backoffice/index&page=events&edit_id=' . $id);
            }

            $_SESSION['backoffice']['edit_errors'] = $errors;
            $_SESSION['backoffice']['edit_old'] = $formData;
            $_SESSION['backoffice']['edit_id'] = $id;
            $this->setFlash('error', 'Mise a jour impossible. Corrigez les champs en rouge.');
            $this->redirectToBackofficeEvents(['edit_id' => (string) $id]);
        }

        $this->evenementModel->update($id, $formData);
        $this->setFlash('success', 'Evenement mis a jour avec succes.');

        if ($redirectTarget === 'backoffice_events') {
            $this->redirectToUrl($this->basePath() . '/index.php?route=backoffice/index&page=events&event_id=' . $id);
        }

        $this->redirectToBackofficeEvents(['event_id' => (string) $id]);
    }

    public function delete(): void
    {
        $redirectTarget = (string) ($_POST['redirect_target'] ?? '');

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirectToBackofficeEvents();
        }

        $id = (int) ($_GET['id'] ?? 0);
        $this->evenementModel->delete($id);
        $this->participantModel->deleteByEventId($id);
        $this->setFlash('success', 'Evenement supprime.');

        if ($redirectTarget === 'backoffice_events') {
            $this->redirectToUrl($this->basePath() . '/index.php?route=backoffice/index&page=events');
        }

        $this->redirectToBackofficeEvents();
    }

    /**
     * @return array<string, string>
     */
    private function extractEventFormData(): array
    {
        return [
            'titre' => trim((string) ($_POST['titre'] ?? '')),
            'date_evenement' => trim((string) ($_POST['date_evenement'] ?? '')),
            'lieu' => trim((string) ($_POST['lieu'] ?? '')),
            'statut' => trim((string) ($_POST['statut'] ?? 'Ouvert')),
            'capacite' => trim((string) ($_POST['capacite'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'image_url' => trim((string) ($_POST['image_url'] ?? '')),
        ];
    }

    /**
     * @param array<string, string> $data
     * @return array<string, string>
     */
    private function validateEventData(array $data): array
    {
        $errors = [];

        if ($data['titre'] === '') {
            $errors['titre'] = 'Le titre est obligatoire.';
        }

        if ($data['date_evenement'] === '') {
            $errors['date_evenement'] = 'La date est obligatoire.';
        }

        if ($data['lieu'] === '') {
            $errors['lieu'] = 'Le lieu est obligatoire.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'La description est obligatoire.';
        }

        if ($data['capacite'] !== '' && !ctype_digit($data['capacite'])) {
            $errors['capacite'] = 'La capacite doit etre un nombre entier positif.';
        }

        return $errors;
    }

    private function basePath(): string
    {
        return rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
    }

    private function redirect(string $route): void
    {
        header('Location: ' . $this->basePath() . '/index.php?route=' . $route);
        exit;
    }

    private function redirectToUrl(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * @param array<string, string> $params
     */
    private function redirectToBackofficeEvents(array $params = []): void
    {
        $query = array_merge(['route' => 'backoffice/index', 'page' => 'events'], $params);
        $this->redirectToUrl($this->basePath() . '/index.php?' . http_build_query($query));
    }

    private function setFlash(string $key, string $value): void
    {
        $_SESSION['flash'][$key] = $value;
    }

    private function pullFlash(string $key): ?string
    {
        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $value = (string) $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }
}
