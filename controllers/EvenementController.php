<?php

require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Evenement.php';
require_once 'models/Participant.php';

class EvenementController
{
    private $evenementModel;
    private $participantModel;

    public function __construct()
    {
        $this->evenementModel = new Evenement();
        $this->participantModel = new Participant();
    }

    public function index(): void
    {
        $sort = $_GET['sort'] ?? 'date_desc';
        $evenements = $this->evenementModel->all();
        $eventIds = array_column($evenements, 'id');
        $participantCountByEvent = $this->participantModel->countByEventIds($eventIds);

        usort($evenements, function (array $a, array $b) use ($sort, $participantCountByEvent): int {
            if ($sort === 'date_asc') {
                return strcmp((string) ($a['date_evenement'] ?? ''), (string) ($b['date_evenement'] ?? ''));
            }
            if ($sort === 'participants_desc') {
                return ($participantCountByEvent[(int) $b['id']] ?? 0) <=> ($participantCountByEvent[(int) $a['id']] ?? 0);
            }
            return strcmp((string) ($b['date_evenement'] ?? ''), (string) ($a['date_evenement'] ?? ''));
        });

        $flashSuccess = $this->pullFlash('success');
        $flashError   = $this->pullFlash('error');
        $basePath     = $this->basePath();
        $pageTitle    = 'Evenements';
        $currentSort  = $sort;

        ob_start();
        require_once 'views/evenement/index.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    public function statistiques(): void
    {
        $evenements = $this->evenementModel->all();
        $eventIds   = array_column($evenements, 'id');
        $participantCountByEvent = $this->participantModel->countByEventIds($eventIds);

        usort($evenements, function (array $a, array $b) use ($participantCountByEvent): int {
            return ($participantCountByEvent[(int) $b['id']] ?? 0) <=> ($participantCountByEvent[(int) $a['id']] ?? 0);
        });

        $totalParticipants = (int) array_sum($participantCountByEvent);
        $maxCount = !empty($participantCountByEvent) ? (int) max($participantCountByEvent) : 1;
        $basePath  = $this->basePath();
        $pageTitle = 'Statistiques Evenements';

        ob_start();
        require_once 'views/evenement/statistiques.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    public function show(): void
    {
        $id       = (int) ($_GET['id'] ?? 0);
        $evenement = $this->evenementModel->find($id);

        if ($evenement === null) {
            $this->setFlash('error', 'Evenement introuvable.');
            $this->redirect('index.php?url=evenement/index');
        }

        $participants = $this->participantModel->allByEventId($id);
        $flashSuccess  = $this->pullFlash('success');
        $basePath      = $this->basePath();
        $pageTitle     = htmlspecialchars((string) ($evenement['titre'] ?? ''), ENT_QUOTES, 'UTF-8');

        ob_start();
        require_once 'views/evenement/show.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    public function create(): void
    {
        requireAdmin();

        $errors    = [];
        $fromAdmin = ($_GET['from_admin'] ?? '') === '1';
        $formData  = [
            'titre'          => '',
            'date_evenement' => '',
            'lieu'           => '',
            'statut'         => 'Ouvert',
            'capacite'       => '',
            'description'    => '',
            'image_url'      => '',
        ];
        $basePath  = $this->basePath();
        $pageTitle = 'Creer un evenement';

        ob_start();
        require_once 'views/evenement/create.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    public function store(): void
    {
        requireAdmin();

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('index.php?url=evenement/index');
        }

        $fromAdmin = ($_POST['from_admin'] ?? '') === '1';
        $formData  = $this->extractEventFormData();
        $errors    = $this->validateEventData($formData, $fromAdmin);
        $basePath  = $this->basePath();

        if ($errors !== []) {
            if ($fromAdmin) {
                $this->setFlash('error', implode(' | ', $errors));
                $this->redirect('rh.php?page=backend/admin&section=evenements');
                return;
            }
            $pageTitle = 'Creer un evenement';
            ob_start();
            require_once 'views/evenement/create.php';
            $viewContent = ob_get_clean();
            require_once 'views/layout.php';
            return;
        }

        $this->evenementModel->create($formData);
        $this->setFlash('success', 'Evenement cree avec succes.');
        $this->redirect($fromAdmin ? 'rh.php?page=backend/admin&section=evenements' : 'index.php?url=evenement/index');
    }

    public function edit(): void
    {
        requireAdmin();

        $id        = (int) ($_GET['id'] ?? 0);
        $evenement = $this->evenementModel->find($id);

        if ($evenement === null) {
            $this->setFlash('error', 'Evenement introuvable.');
            $this->redirect('index.php?url=evenement/index');
        }

        $errors    = [];
        $fromAdmin = ($_GET['from_admin'] ?? '') === '1';
        $formData  = [
            'titre'          => (string) ($evenement['titre'] ?? ''),
            'date_evenement' => (string) ($evenement['date_evenement'] ?? ''),
            'lieu'           => (string) ($evenement['lieu'] ?? ''),
            'statut'         => (string) ($evenement['statut'] ?? 'Ouvert'),
            'capacite'       => (string) ($evenement['capacite'] ?? ''),
            'description'    => (string) ($evenement['description'] ?? ''),
            'image_url'      => (string) ($evenement['image_url'] ?? ''),
        ];
        $eventId   = $id;
        $basePath  = $this->basePath();
        $pageTitle = 'Modifier l\'evenement';

        ob_start();
        require_once 'views/evenement/edit.php';
        $viewContent = ob_get_clean();
        require_once 'views/layout.php';
    }

    public function update(): void
    {
        requireAdmin();

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('index.php?url=evenement/index');
        }

        $id        = (int) ($_GET['id'] ?? 0);
        $evenement = $this->evenementModel->find($id);
        $basePath  = $this->basePath();
        $fromAdmin = ($_POST['from_admin'] ?? '') === '1';

        if ($evenement === null) {
            $this->setFlash('error', 'Evenement introuvable.');
            $this->redirect($fromAdmin ? 'rh.php?page=backend/admin&section=evenements' : 'index.php?url=evenement/index');
        }

        $formData = $this->extractEventFormData();
        $errors   = $this->validateEventData($formData, true);

        if ($errors !== []) {
            if ($fromAdmin) {
                $this->setFlash('error', implode(' | ', $errors));
                $this->redirect('rh.php?page=backend/admin&section=evenements');
                return;
            }
            $eventId   = $id;
            $pageTitle = 'Modifier l\'evenement';
            ob_start();
            require_once 'views/evenement/edit.php';
            $viewContent = ob_get_clean();
            require_once 'views/layout.php';
            return;
        }

        $this->evenementModel->update($id, $formData);
        $this->setFlash('success', 'Evenement mis a jour avec succes.');
        $this->redirect($fromAdmin ? 'rh.php?page=backend/admin&section=evenements' : 'index.php?url=evenement/show&id=' . $id);
    }

    public function delete(): void
    {
        requireAdmin();

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('index.php?url=evenement/index');
        }

        $id        = (int) ($_GET['id'] ?? 0);
        $fromAdmin = ($_POST['from_admin'] ?? '') === '1';
        $deleted   = $this->evenementModel->delete($id);
        $this->participantModel->deleteByEventId($id);

        if ($deleted) {
            $this->setFlash('success', 'Evenement supprime avec succes.');
        } else {
            $this->setFlash('error', 'Suppression impossible : evenement introuvable.');
        }

        $this->redirect($fromAdmin ? 'rh.php?page=backend/admin&section=evenements' : 'index.php?url=evenement/index');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function extractEventFormData(): array
    {
        return [
            'titre'          => trim((string) ($_POST['titre'] ?? '')),
            'date_evenement' => trim((string) ($_POST['date_evenement'] ?? '')),
            'lieu'           => trim((string) ($_POST['lieu'] ?? '')),
            'statut'         => trim((string) ($_POST['statut'] ?? 'Ouvert')),
            'capacite'       => trim((string) ($_POST['capacite'] ?? '')),
            'description'    => trim((string) ($_POST['description'] ?? '')),
            'image_url'      => trim((string) ($_POST['image_url'] ?? '')),
        ];
    }

    private function validateEventData(array $data, bool $isEdit = false): array
    {
        $errors = [];

        if ($data['titre'] === '') {
            $errors['titre'] = 'Le titre est obligatoire.';
        }

        if ($data['date_evenement'] === '') {
            $errors['date_evenement'] = 'La date est obligatoire.';
        } else {
            $eventDate  = DateTimeImmutable::createFromFormat('Y-m-d', $data['date_evenement']);
            $dateErrors = $eventDate !== false ? DateTimeImmutable::getLastErrors() : null;
            if ($eventDate === false || $dateErrors === null || $dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0) {
                $errors['date_evenement'] = 'La date est invalide (format attendu : AAAA-MM-JJ).';
            } elseif (!$isEdit) {
                $today = new DateTimeImmutable('today');
                if ($eventDate < $today) {
                    $errors['date_evenement'] = 'La date ne peut pas etre dans le passe.';
                } elseif ($eventDate < $today->modify('+7 days')) {
                    $errors['date_evenement'] = 'L\'evenement doit etre programme au moins 7 jours a l\'avance.';
                }
            }
        }

        if ($data['lieu'] === '') {
            $errors['lieu'] = 'Le lieu est obligatoire.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'La description est obligatoire.';
        }

        if ($data['capacite'] !== '' && !ctype_digit($data['capacite'])) {
            $errors['capacite'] = 'La capacite doit etre un entier positif.';
        }

        return $errors;
    }

    private function basePath(): string
    {
        return rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
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
