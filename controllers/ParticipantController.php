<?php

declare(strict_types=1);

namespace Controllers;

use Models\Evenement;
use Models\Participant;

class ParticipantController
{
    private Participant $participantModel;
    private Evenement $evenementModel;

    public function __construct()
    {
        $this->participantModel = new Participant();
        $this->evenementModel = new Evenement();
    }

    public function create(): void
    {
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $evenement = $this->evenementModel->find($eventId);

        if ($evenement === null) {
            header('Location: ' . $this->basePath() . '/index.php?route=evenement/index');
            exit;
        }

        $pageTitle = 'SmartSmart - Participer';
        $activeNav = 'evenements';
        $basePath = $this->basePath();
        $errors = [];
        $formData = [
            'nom' => '',
            'prenom' => '',
            'age' => '',
            'email' => '',
            'telephone' => '',
            'projet' => '',
        ];

        require BASE_PATH . '/views/layouts/header.php';
        require BASE_PATH . '/views/participant/create.php';
        require BASE_PATH . '/views/layouts/footer.php';
    }

    public function store(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . $this->basePath() . '/index.php?route=evenement/index');
            exit;
        }

        $eventId = (int) ($_GET['event_id'] ?? 0);
        $evenement = $this->evenementModel->find($eventId);

        if ($evenement === null) {
            header('Location: ' . $this->basePath() . '/index.php?route=evenement/index');
            exit;
        }

        $formData = [
            'nom' => trim((string) ($_POST['nom'] ?? '')),
            'prenom' => trim((string) ($_POST['prenom'] ?? '')),
            'age' => trim((string) ($_POST['age'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'telephone' => trim((string) ($_POST['telephone'] ?? '')),
            'projet' => trim((string) ($_POST['projet'] ?? '')),
        ];

        $errors = [];
        if ($formData['nom'] === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if ($formData['prenom'] === '') {
            $errors['prenom'] = 'Le prenom est obligatoire.';
        }
        if ($formData['age'] === '' || !ctype_digit($formData['age'])) {
            $errors['age'] = 'Age invalide.';
        }
        if ($formData['email'] === '') {
            $errors['email'] = 'L email est obligatoire.';
        }

        if ($errors !== []) {
            $pageTitle = 'SmartSmart - Participer';
            $activeNav = 'evenements';
            $basePath = $this->basePath();

            require BASE_PATH . '/views/layouts/header.php';
            require BASE_PATH . '/views/participant/create.php';
            require BASE_PATH . '/views/layouts/footer.php';
            return;
        }

        $this->participantModel->create($eventId, $formData);
        $_SESSION['flash']['success'] = 'Participation enregistree avec succes.';
        header('Location: ' . $this->basePath() . '/index.php?route=evenement/show&id=' . $eventId);
        exit;
    }

    private function basePath(): string
    {
        return rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
    }
}
