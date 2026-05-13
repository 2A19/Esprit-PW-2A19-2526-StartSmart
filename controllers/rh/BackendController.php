<?php
require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../config/Auth.php');
require_once(__DIR__ . '/JobOfferController.php');
require_once(__DIR__ . '/ApplicationController.php');
require_once(__DIR__ . '/EmployeeController.php');
require_once(__DIR__ . '/UserController.php');
require_once(__DIR__ . '/../../models/Projet.php');
require_once(__DIR__ . '/../../models/Post.php');
require_once(__DIR__ . '/../../models/Commentaire.php');
require_once(__DIR__ . '/../../models/Categorie.php');
require_once(__DIR__ . '/../../models/Evenement.php');
require_once(__DIR__ . '/../../models/Participant.php');

class BackendController {

    private $jobOfferController;
    private $applicationController;
    private $employeeController;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->jobOfferController = new JobOfferController();
        $this->applicationController = new ApplicationController();
        $this->employeeController = new EmployeeController();
    }

    public function dashboard() {
        $role = strtolower(trim((string) ($_SESSION['user_role'] ?? '')));
        if (empty($_SESSION['user_id']) || !in_array($role, ['admin', 'rh', 'startup'], true)) {
            header('Location: login.php');
            exit;
        }
        if (in_array($role, ['admin', 'rh'], true)) {
            header('Location: rh.php?page=backend/admin');
            exit;
        }

        try {
            // Get all job offers for the startup
            $jobOffers = $this->jobOfferController->listJobOffers($_SESSION['user_id']);
            $jobOffersArray = [];
            while ($job = $jobOffers->fetch(PDO::FETCH_ASSOC)) {
                $jobOffersArray[] = $job;
            }

            // Count applications for each job offer
            $totalApplications = 0;
            $recentApplications = [];
            
            foreach ($jobOffersArray as $job) {
                $apps = $this->applicationController->getByJobOfferId($job['id']);
                $totalApplications += count($apps);
                $recentApplications = array_merge($recentApplications, array_map(function($app) use ($job) {
                    $app['title'] = $job['title'];
                    $app['job_title'] = $job['title'];
                    return $app;
                }, $apps));
            }

            // Sort recent applications by date (newest first)
            usort($recentApplications, function($a, $b) {
                return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
            });
            
            // Get only 5 most recent
            $recentApplications = array_slice($recentApplications, 0, 5);

            // Get employee count
            $totalEmployees = $this->employeeController->countByCompany($_SESSION['user_id']);

            // Get job offer count
            $totalJobOffers = count($jobOffersArray);

            $data = [
                'jobOffers' => $jobOffersArray,
                'totalApplications' => $totalApplications,
                'totalEmployees' => $totalEmployees,
                'totalJobOffers' => $totalJobOffers,
                'recentApplications' => array_slice($recentApplications, 0, 5),
                'currentView' => 'dashboard'
            ];

            $this->renderView('backend/backend', $data);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function admin() {
        $this->requirePlatformAdmin();

        $userController = new UserController();
        $users = $userController->listUsers()->fetchAll(PDO::FETCH_ASSOC);
        $startupUsers = array_values(array_filter($users, function ($user) {
            return strtolower(trim((string)($user['role'] ?? ''))) === 'startup';
        }));
        $standardUsers = array_values(array_filter($users, function ($user) {
            return strtolower(trim((string)($user['role'] ?? ''))) !== 'startup';
        }));

        $projetModel = new Projet($this->db);
        $postModel = new Post($this->db);
        $commentaireModel = new Commentaire($this->db);
        $categorieModel = new Categorie($this->db);
        $evenementModel = new Evenement();
        $participantModel = new Participant();
        $recentProjets = $projetModel->readAll('', null, 'latest', null, 8, 0)->fetchAll(PDO::FETCH_ASSOC);
        $recentPosts = $postModel->readAll('', '', 'latest', null, 8, 0)->fetchAll(PDO::FETCH_ASSOC);
        $recentComments = $commentaireModel->readAll('')->fetchAll(PDO::FETCH_ASSOC);
        $categories = $categorieModel->readAll('')->fetchAll(PDO::FETCH_ASSOC);
        $evenements = $evenementModel->all();
        $eventIds = array_column($evenements, 'id');
        $participantCounts = !empty($eventIds) ? $participantModel->countByEventIds($eventIds) : [];

        $stats = [
            'users' => count($standardUsers),
            'startups' => count($startupUsers),
            'projets' => $projetModel->countAll('', null),
            'posts' => $postModel->countAll(''),
            'comments' => $this->countTableRows('commentaire'),
            'categories' => count($categories),
            'evenements' => count($evenements),
            'job_offers' => $this->countTableRows('job_offers'),
            'applications' => $this->countTableRows('applications'),
            'employees' => $this->countTableRows('employees'),
        ];

        $currentAdmin = [
            'name' => $_SESSION['user_name'] ?? 'Super Admin',
            'initials' => $this->initials($_SESSION['user_name'] ?? 'Super Admin'),
        ];

        include __DIR__ . '/../../views/rh/backend/admin_console.php';
    }

    /**
     * CSV export for admin datasets (stats summary, users, startups, forum, projets).
     * URL: rh.php?page=backend/adminExport&format=csv&dataset=users
     */
    public function adminExport() {
        $this->requirePlatformAdmin();

        $format = strtolower(trim((string)($_GET['format'] ?? 'csv')));
        if (!in_array($format, ['csv', 'pdf'], true)) {
            $format = 'csv';
        }

        $dataset = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($_GET['dataset'] ?? 'stats')));
        $allowed = ['stats', 'users', 'startups', 'project-projects', 'project-categories', 'forum-posts', 'forum-comments', 'evenements'];
        if (!in_array($dataset, $allowed, true)) {
            $dataset = 'stats';
        }

        // ── PDF export for events (server-side, no external library needed) ──
        if ($format === 'pdf' && $dataset === 'evenements') {
            $evenementModel = new Evenement();
            $participantModel = new Participant();
            $evs = $evenementModel->all();
            $evIds = array_column($evs, 'id');
            $pcounts = !empty($evIds) ? $participantModel->countByEventIds($evIds) : [];
            usort($evs, function($a, $b) use ($pcounts) {
                return ($pcounts[(int)($b['id']??0)] ?? 0) <=> ($pcounts[(int)($a['id']??0)] ?? 0);
            });
            $genDate = date('d/m/Y H:i');
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<title>Événements — StartSmart</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:Arial,sans-serif;font-size:11px;color:#1e1e1e;padding:20px;}
h1{font-size:16px;margin-bottom:4px;}
.meta{color:#666;font-size:10px;margin-bottom:16px;}
table{width:100%;border-collapse:collapse;margin-top:8px;}
th{background:#1e3a8a;color:#fff;padding:7px 10px;text-align:left;font-size:10px;}
td{padding:6px 10px;border-bottom:1px solid #e5e7eb;font-size:10px;vertical-align:top;}
tr:nth-child(even) td{background:#f9fafb;}
.bar-wrap{background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden;margin-top:3px;}
.bar{height:100%;border-radius:4px;background:#1e3a8a;}
@media print{
  body{padding:10px;}
  button{display:none!important;}
  @page{size:A4 landscape;margin:15mm;}
}
</style></head><body>';
            echo '<h1>📅 Événements StartSmart — Classement par participants</h1>';
            echo '<div class="meta">Généré le ' . $genDate . ' · ' . count($evs) . ' événement(s) · Total participants : ' . array_sum($pcounts) . '</div>';
            echo '<button onclick="window.print()" style="margin-bottom:14px;padding:7px 18px;background:#1e3a8a;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;">🖨️ Imprimer / Enregistrer en PDF</button>';
            echo '<table><thead><tr><th>#</th><th>Événement</th><th>Date</th><th>Lieu</th><th>Capacité</th><th>Participants</th><th>Remplissage</th><th>Statut</th></tr></thead><tbody>';
            $maxP = !empty($pcounts) ? max($pcounts) : 1;
            foreach ($evs as $i => $ev) {
                $evId = (int)($ev['id'] ?? 0);
                $cnt  = (int)($pcounts[$evId] ?? 0);
                $cap  = ($ev['capacite'] !== null && $ev['capacite'] !== '') ? (int)$ev['capacite'] : 0;
                $fill = $cap > 0 ? min(100, round($cnt / $cap * 100)) : 0;
                $pct  = $maxP > 0 ? round($cnt / $maxP * 100) : 0;
                $date = !empty($ev['date_evenement']) ? date('d/m/Y', strtotime($ev['date_evenement'])) : '—';
                echo '<tr>';
                echo '<td>' . ($i + 1) . '</td>';
                echo '<td><strong>' . htmlspecialchars($ev['titre'] ?? '—') . '</strong></td>';
                echo '<td>' . $date . '</td>';
                echo '<td>' . htmlspecialchars($ev['lieu'] ?? '—') . '</td>';
                echo '<td>' . ($cap > 0 ? $cap . ' places' : 'Illimitée') . '</td>';
                echo '<td><strong>' . $cnt . '</strong> (' . $pct . '% du record)</td>';
                echo '<td>' . ($cap > 0 ? '<div class="bar-wrap"><div class="bar" style="width:' . $fill . '%;background:' . ($fill >= 100 ? '#dc2626' : '#1e3a8a') . ';"></div></div>' . $fill . '%' : '—') . '</td>';
                echo '<td>' . htmlspecialchars($ev['statut'] ?? '—') . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table></body></html>';
            exit;
        }

        $userController = new UserController();
        $users = $userController->listUsers()->fetchAll(PDO::FETCH_ASSOC);
        $startupUsers = array_values(array_filter($users, function ($user) {
            return strtolower(trim((string)($user['role'] ?? ''))) === 'startup';
        }));
        $standardUsers = array_values(array_filter($users, function ($user) {
            return strtolower(trim((string)($user['role'] ?? ''))) !== 'startup';
        }));

        $projetModel = new Projet($this->db);
        $postModel = new Post($this->db);
        $commentaireModel = new Commentaire($this->db);
        $categorieModel = new Categorie($this->db);

        $filename = 'startsmart_admin_' . $dataset . '_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            exit('Export failed');
        }
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        switch ($dataset) {
            case 'stats':
                $statsRows = [
                    ['Indicateur', 'Valeur'],
                    ['Utilisateurs (hors startup)', (string) count($standardUsers)],
                    ['Startups', (string) count($startupUsers)],
                    ['Projets', (string) $projetModel->countAll('', null)],
                    ['Sujets forum', (string) $postModel->countAll('')],
                    ['Commentaires forum', (string) $this->countTableRows('commentaire')],
                    ['Catégories projet', (string) $this->countTableRows('categorie')],
                    ['Offres emploi (RH)', (string) $this->countTableRows('job_offers')],
                    ['Candidatures (RH)', (string) $this->countTableRows('applications')],
                    ['Employés (RH)', (string) $this->countTableRows('employees')],
                    ['Export le', date('c')],
                ];
                foreach ($statsRows as $r) {
                    fputcsv($out, $r, ';');
                }
                break;

            case 'users':
                fputcsv($out, ['id', 'nom', 'prenom', 'email', 'role', 'statut', 'telephone', 'date_inscription'], ';');
                foreach ($standardUsers as $u) {
                    fputcsv($out, [
                        (string)($u['id'] ?? ''),
                        (string)($u['nom'] ?? ''),
                        (string)($u['prenom'] ?? ''),
                        (string)($u['email'] ?? ''),
                        (string)($u['role'] ?? ''),
                        (string)($u['statut'] ?? ''),
                        (string)($u['telephone'] ?? ''),
                        (string)($u['date_inscription'] ?? ''),
                    ], ';');
                }
                break;

            case 'startups':
                fputcsv($out, ['id', 'nom_startup', 'email', 'secteur', 'statut', 'nom_responsable', 'prenom_responsable', 'telephone'], ';');
                foreach ($startupUsers as $u) {
                    fputcsv($out, [
                        (string)($u['id'] ?? ''),
                        (string)($u['nom_startup'] ?? ''),
                        (string)($u['email'] ?? ''),
                        (string)($u['secteur'] ?? ''),
                        (string)($u['statut'] ?? ''),
                        (string)($u['nom_responsable'] ?? ''),
                        (string)($u['prenom_responsable'] ?? ''),
                        (string)($u['telephone'] ?? ''),
                    ], ';');
                }
                break;

            case 'project-projects':
                fputcsv($out, ['id', 'nomprojet', 'auteur_id', 'categorie_id', 'budget', 'statut', 'datedebut', 'datefin'], ';');
                $stmt = $projetModel->readAll('', null, 'latest', null, 5000, 0);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($out, [
                        (string)($row['id'] ?? ''),
                        (string)($row['nomprojet'] ?? ''),
                        (string)($row['auteur_id'] ?? ''),
                        (string)($row['categorie_id'] ?? ''),
                        (string)($row['budget'] ?? ''),
                        (string)($row['statut'] ?? ''),
                        (string)($row['datedebut'] ?? ''),
                        (string)($row['datefin'] ?? ''),
                    ], ';');
                }
                break;

            case 'project-categories':
                fputcsv($out, ['id', 'num', 'titre', 'nom_investisseur'], ';');
                foreach ($categorieModel->readAll('')->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    fputcsv($out, [
                        (string)($row['id'] ?? ''),
                        (string)($row['num'] ?? ''),
                        (string)($row['titre'] ?? ''),
                        (string)($row['nom_investisseur'] ?? ''),
                    ], ';');
                }
                break;

            case 'forum-posts':
                fputcsv($out, ['id_post', 'titre', 'topic', 'auteur_id', 'statut', 'date_creation'], ';');
                $stmt = $postModel->readAll('', '', 'latest', null, 5000, 0);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    fputcsv($out, [
                        (string)($row['id_post'] ?? ''),
                        (string)($row['titre'] ?? ''),
                        (string)($row['topic'] ?? ''),
                        (string)($row['auteur_id'] ?? ''),
                        (string)($row['statut'] ?? ''),
                        (string)($row['date_creation'] ?? ''),
                    ], ';');
                }
                break;

            case 'forum-comments':
                fputcsv($out, ['id_commentaire', 'post_id', 'auteur_id', 'date_creation', 'contenu'], ';');
                $stmt = $this->db->prepare(
                    "SELECT c.id_commentaire, c.post_id, c.auteur_id, c.date_creation, c.contenu
                     FROM commentaire c
                     ORDER BY c.id_commentaire DESC
                     LIMIT 5000"
                );
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $text = (string)($row['contenu'] ?? '');
                    $text = preg_replace('/\s+/', ' ', $text);
                    fputcsv($out, [
                        (string)($row['id_commentaire'] ?? ''),
                        (string)($row['post_id'] ?? ''),
                        (string)($row['auteur_id'] ?? ''),
                        (string)($row['date_creation'] ?? ''),
                        $text,
                    ], ';');
                }
                break;
        }

        fclose($out);
        exit;
    }

    private function countTableRows(string $table): int {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        if ($table === '') {
            return 0;
        }
        try {
            return (int) $this->db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function projets() {
        $this->requirePlatformAdmin();

        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['p'] ?? $_GET['page_num'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $projetModel = new Projet($this->db);
        $totalProjets = $projetModel->countAll($search, null);
        $stmt = $projetModel->readAll($search, null, 'latest', null, $perPage, $offset);
        $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->renderView('backend/backend', [
            'currentView' => 'admin-projets',
            'projets' => $projets,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($totalProjets / $perPage)),
            'totalProjets' => $totalProjets,
        ]);
    }

    public function forum() {
        $this->requirePlatformAdmin();

        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['p'] ?? $_GET['page_num'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $postModel = new Post($this->db);
        $totalPosts = $postModel->countAll($search);
        $stmt = $postModel->readAll($search, '', 'latest', null, $perPage, $offset);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->renderView('backend/backend', [
            'currentView' => 'admin-forum',
            'posts' => $posts,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($totalPosts / $perPage)),
            'totalPosts' => $totalPosts,
        ]);
    }

    private function requirePlatformAdmin(): void {
        $role = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
        if (empty($_SESSION['user_id']) || !in_array($role, ['admin', 'rh'], true)) {
            header('Location: login.php');
            exit;
        }
    }

    private function initials(string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        $first = strtoupper(substr($parts[0] ?? 'S', 0, 1));
        $second = strtoupper(substr($parts[1] ?? 'A', 0, 1));
        return $first . $second;
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../../views/rh/' . $viewPath . '.php';
    }
}
?>
