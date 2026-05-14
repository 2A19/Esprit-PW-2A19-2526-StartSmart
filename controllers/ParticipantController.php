<?php

require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Evenement.php';
require_once 'models/Participant.php';

class ParticipantController
{
    private $participantModel;
    private $evenementModel;

    public function __construct()
    {
        $this->participantModel = new Participant();
        $this->evenementModel   = new Evenement();
    }

    public function create(): void
    {
        requireLogin();

        $eventId   = (int) ($_GET['event_id'] ?? 0);
        $evenement = $this->evenementModel->find($eventId);
        $fromAdmin = ($_GET['from_admin'] ?? '') === '1';

        if ($evenement === null) {
            header('Location: index.php?url=evenement/index');
            exit;
        }

        $pageTitle = 'Participer a l\'evenement';
        $basePath  = $this->basePath();
        $errors    = [];
        $formData  = [
            'nom'       => '',
            'prenom'    => '',
            'age'       => '',
            'email'     => '',
            'telephone' => '',
            'projet'    => '',
        ];

        ob_start();
        require_once 'views/participant/create.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    public function store(): void
    {
        requireLogin();

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?url=evenement/index');
            exit;
        }

        $eventId   = (int) ($_GET['event_id'] ?? 0);
        $fromAdmin = ($_POST['from_admin'] ?? '') === '1';

        if ($eventId <= 0 || !$this->participantModel->eventExists($eventId)) {
            $_SESSION['flash']['error'] = 'Evenement invalide.';
            header('Location: index.php?url=evenement/index');
            exit;
        }

        $evenement = $this->evenementModel->find($eventId);
        if ($evenement === null) {
            header('Location: index.php?url=evenement/index');
            exit;
        }

        $formData = [
            'nom'       => trim((string) ($_POST['nom'] ?? '')),
            'prenom'    => trim((string) ($_POST['prenom'] ?? '')),
            'age'       => trim((string) ($_POST['age'] ?? '')),
            'email'     => trim((string) ($_POST['email'] ?? '')),
            'telephone' => trim((string) ($_POST['telephone'] ?? '')),
            'projet'    => trim((string) ($_POST['projet'] ?? '')),
        ];

        $errors = [];
        if ($formData['nom'] === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if ($formData['prenom'] === '') {
            $errors['prenom'] = 'Le prenom est obligatoire.';
        }
        if ($formData['age'] === '' || !ctype_digit($formData['age']) || (int) $formData['age'] <= 0) {
            $errors['age'] = 'Age invalide (nombre entier positif attendu).';
        }
        if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }

        $capacity = (int) ($evenement['capacite'] ?? 0);
        if ($capacity > 0 && $this->participantModel->countByEventId($eventId) >= $capacity) {
            $errors['general'] = 'L\'evenement est complet (capacite atteinte).';
        }

        $basePath  = $this->basePath();
        $pageTitle = 'Participer a l\'evenement';

        if ($errors !== []) {
            if ($fromAdmin) {
                $_SESSION['flash']['error'] = implode(' | ', $errors);
                header('Location: rh.php?page=backend/admin&section=evenements');
                exit;
            }
            ob_start();
            require_once 'views/participant/create.php';
            $viewContent = ob_get_clean();
            require_once 'views/layout.php';
            return;
        }

        try {
            $this->participantModel->create($eventId, $formData, $capacity > 0 ? $capacity : null);
            $_SESSION['flash']['success'] = 'Participation enregistree avec succes. Bienvenue !';
            $redirect = $fromAdmin
                ? 'rh.php?page=backend/admin&section=evenements'
                : 'index.php?url=evenement/show&id=' . $eventId;
            header('Location: ' . $redirect);
            exit;
        } catch (RuntimeException $exception) {
            $errors['general'] = $exception->getMessage();
            ob_start();
            require_once 'views/participant/create.php';
            $viewContent = ob_get_clean();
            require_once 'views/layout.php';
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function basePath(): string
    {
        return rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
    }
}
