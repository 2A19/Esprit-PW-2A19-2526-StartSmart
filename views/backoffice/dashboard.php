<?php
declare(strict_types=1);

require BASE_PATH . '/views/backoffice/includes/config.php';

$pageTitle = 'Back Office Dashboard';
$basePath = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
$dashboardPage = (string) ($_GET['page'] ?? 'events');
if (!in_array($dashboardPage, ['dashboard', 'events', 'participants'], true)) {
    $dashboardPage = 'events';
}

$search = trim((string) ($_GET['q'] ?? ''));
$startDate = trim((string) ($_GET['start_date'] ?? ''));
$endDate = trim((string) ($_GET['end_date'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'participants_desc'));
$selectedEventId = (int) ($_GET['event_id'] ?? 0);
$editEventId = (int) ($_GET['edit_id'] ?? 0);

$totalEvents = 0;
$totalParticipants = 0;
$upcomingEvents = 0;
$pastEvents = 0;
$fillRate = 0.0;
$participantsPerEvent = [];
$eventsTable = [];
$eventsPerMonthLabels = [];
$eventsPerMonthData = [];
$participantsPerEventLabels = [];
$participantsPerEventData = [];
$statusLabels = ['Ouvert', 'Ferme'];
$statusData = [0, 0];
$selectedEvent = null;
$editEvent = null;
$selectedEventParticipantsList = [];
$participantFkColumn = 'event_id';
$flashSuccess = $_SESSION['flash']['success'] ?? null;
$flashError = $_SESSION['flash']['error'] ?? null;
unset($_SESSION['flash']['success'], $_SESSION['flash']['error']);

$createErrors = $_SESSION['backoffice']['create_errors'] ?? [];
$createOld = $_SESSION['backoffice']['create_old'] ?? [];
$editErrors = $_SESSION['backoffice']['edit_errors'] ?? [];
$editOld = $_SESSION['backoffice']['edit_old'] ?? [];
$sessionEditId = (int) ($_SESSION['backoffice']['edit_id'] ?? 0);
unset(
    $_SESSION['backoffice']['create_errors'],
    $_SESSION['backoffice']['create_old'],
    $_SESSION['backoffice']['edit_errors'],
    $_SESSION['backoffice']['edit_old'],
    $_SESSION['backoffice']['edit_id']
);

if ($pdo instanceof PDO) {
    try {
        $hasEvenementId = $pdo->query("SHOW COLUMNS FROM participants LIKE 'evenement_id'")->fetch() !== false;
        $participantFkColumn = $hasEvenementId ? 'evenement_id' : 'event_id';

        $totalEvents = (int) $pdo->query('SELECT COUNT(*) FROM evenements')->fetchColumn();
        $totalParticipants = (int) $pdo->query('SELECT COUNT(*) FROM participants')->fetchColumn();
        $upcomingEvents = (int) $pdo->query("SELECT COUNT(*) FROM evenements WHERE date_evenement >= CURDATE()")->fetchColumn();
        $pastEvents = (int) $pdo->query("SELECT COUNT(*) FROM evenements WHERE date_evenement < CURDATE()")->fetchColumn();

        $capacitySum = (int) $pdo->query('SELECT COALESCE(SUM(capacite), 0) FROM evenements')->fetchColumn();
        $fillRate = $capacitySum > 0 ? ($totalParticipants / $capacitySum) * 100 : 0.0;

        $eventsPerMonthStmt = $pdo->query(
            "SELECT DATE_FORMAT(date_evenement, '%b') AS month_label, COUNT(*) AS total
             FROM evenements
             GROUP BY YEAR(date_evenement), MONTH(date_evenement), DATE_FORMAT(date_evenement, '%b')
             ORDER BY YEAR(date_evenement), MONTH(date_evenement)"
        );
        $eventsPerMonthRows = $eventsPerMonthStmt !== false ? $eventsPerMonthStmt->fetchAll() : [];
        foreach ($eventsPerMonthRows as $row) {
            $eventsPerMonthLabels[] = (string) ($row['month_label'] ?? '');
            $eventsPerMonthData[] = (int) ($row['total'] ?? 0);
        }

        $statusStmt = $pdo->query(
            "SELECT
                SUM(CASE WHEN LOWER(statut) = 'ouvert' THEN 1 ELSE 0 END) AS ouvert,
                SUM(CASE WHEN LOWER(statut) <> 'ouvert' THEN 1 ELSE 0 END) AS ferme
             FROM evenements"
        );
        $statusRow = $statusStmt !== false ? $statusStmt->fetch() : false;
        if (is_array($statusRow)) {
            $statusData = [(int) ($statusRow['ouvert'] ?? 0), (int) ($statusRow['ferme'] ?? 0)];
        }

        $baseEventsSql = "SELECT
                e.id,
                e.titre,
                e.date_evenement,
                e.lieu,
                e.statut,
                e.capacite,
                COUNT(p.id) AS participants_count
            FROM evenements e
            LEFT JOIN participants p ON p.$participantFkColumn = e.id";

        $where = [];
        $bindings = [];
        if ($search !== '') {
            $where[] = '(e.titre LIKE :search OR e.lieu LIKE :search)';
            $bindings[':search'] = '%' . $search . '%';
        }
        if ($startDate !== '') {
            $where[] = 'e.date_evenement >= :start_date';
            $bindings[':start_date'] = $startDate;
        }
        if ($endDate !== '') {
            $where[] = 'e.date_evenement <= :end_date';
            $bindings[':end_date'] = $endDate;
        }

        $orderBy = 'participants_count DESC, e.date_evenement ASC';
        if ($sort === 'participants_asc') {
            $orderBy = 'participants_count ASC, e.date_evenement ASC';
        } elseif ($sort === 'date_asc') {
            $orderBy = 'e.date_evenement ASC';
        } elseif ($sort === 'date_desc') {
            $orderBy = 'e.date_evenement DESC';
        }

        $tableSql = $baseEventsSql;
        if ($where !== []) {
            $tableSql .= ' WHERE ' . implode(' AND ', $where);
        }
        $tableSql .= ' GROUP BY e.id, e.titre, e.date_evenement, e.lieu, e.statut, e.capacite';
        $tableSql .= ' ORDER BY ' . $orderBy;

        $tableStmt = $pdo->prepare($tableSql);
        foreach ($bindings as $key => $value) {
            $tableStmt->bindValue($key, $value);
        }
        $tableStmt->execute();
        $eventsTable = $tableStmt->fetchAll();
        $participantsPerEvent = $eventsTable;

        foreach ($eventsTable as $row) {
            $participantsPerEventLabels[] = (string) ($row['titre'] ?? '');
            $participantsPerEventData[] = (int) ($row['participants_count'] ?? 0);
        }

        if ($selectedEventId <= 0 && $eventsTable !== []) {
            $selectedEventId = (int) $eventsTable[0]['id'];
        }
        foreach ($eventsTable as $row) {
            if ((int) $row['id'] === $selectedEventId) {
                $selectedEvent = $row;
                break;
            }
        }

        if ($editEventId > 0) {
            foreach ($eventsTable as $row) {
                if ((int) $row['id'] === $editEventId) {
                    $editEvent = $row;
                    break;
                }
            }
        }

        if ($editEvent === null && $sessionEditId > 0) {
            foreach ($eventsTable as $row) {
                if ((int) $row['id'] === $sessionEditId) {
                    $editEvent = $row;
                    break;
                }
            }
        }

        if ($selectedEvent !== null) {
            $participantsStmt = $pdo->prepare(
                "SELECT id, nom, email FROM participants WHERE $participantFkColumn = :event_id ORDER BY id DESC"
            );
            $participantsStmt->bindValue(':event_id', (int) $selectedEvent['id'], PDO::PARAM_INT);
            $participantsStmt->execute();
            $selectedEventParticipantsList = $participantsStmt->fetchAll();
        }
    } catch (Throwable $exception) {
        $eventsTable = [];
    }
}

require BASE_PATH . '/views/backoffice/includes/header.php';
?>
<div class="crm-layout">
    <aside class="crm-sidebar">
        <div class="brand"><i class="fa-solid fa-shield-halved"></i><span>Back Office</span></div>
        <nav class="sidebar-nav">
            <a class="<?= $dashboardPage === 'dashboard' ? 'active' : ''; ?>" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=backoffice/index&page=dashboard"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a class="<?= $dashboardPage === 'events' ? 'active' : ''; ?>" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=backoffice/index&page=events"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a class="<?= $dashboardPage === 'participants' ? 'active' : ''; ?>" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=backoffice/index&page=participants"><i class="fa-solid fa-users"></i> Participants</a>
        </nav>
    </aside>

    <main class="crm-main">
        <header class="crm-header">
            <h1>Events Admin Dashboard</h1>
            <span>Professional monitoring for events and participants</span>
        </header>

        <?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
            <div class="alert-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (is_string($flashError) && $flashError !== ''): ?>
            <div class="alert-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <section class="stats-grid stats-grid-four">
            <article class="stat-card purple"><p>Total Events</p><h2><?= $totalEvents; ?></h2></article>
            <article class="stat-card cyan"><p>Total Participants</p><h2><?= $totalParticipants; ?></h2></article>
            <article class="stat-card pink"><p>Upcoming Events</p><h2><?= $upcomingEvents; ?></h2></article>
            <article class="stat-card blue"><p>Fill Rate %</p><h2><?= number_format($fillRate, 1); ?>%</h2></article>
        </section>

        <section class="charts-grid">
            <article class="chart-card chart-large">
                <div class="card-head"><h3>Events per month</h3></div>
                <canvas id="eventsLineChart"></canvas>
            </article>
            <article class="chart-card">
                <div class="card-head"><h3>Status (Ouvert / Ferme)</h3></div>
                <canvas id="statusDonutChart"></canvas>
            </article>
            <article class="chart-card chart-wide">
                <div class="card-head"><h3>Participants per event</h3></div>
                <canvas id="participantsBarChart"></canvas>
            </article>
        </section>

        <section class="chart-card chart-wide table-wrap">
            <div class="card-head card-head-actions">
                <h3>Events table</h3>
                <button type="button" class="export-btn" id="exportPdfBtn">
                    <i class="fa-solid fa-file-pdf"></i> Export Report as PDF
                </button>
            </div>

            <form method="post" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/store" class="create-event-form" novalidate>
                <input type="hidden" name="redirect_target" value="backoffice_events">
                <div class="field-wrap">
                    <input type="text" name="titre" placeholder="Event title" value="<?= htmlspecialchars((string) ($createOld['titre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($createErrors['titre'])): ?><small class="field-error"><?= htmlspecialchars((string) $createErrors['titre'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                </div>
                <div class="field-wrap">
                    <input type="date" name="date_evenement" value="<?= htmlspecialchars((string) ($createOld['date_evenement'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($createErrors['date_evenement'])): ?><small class="field-error"><?= htmlspecialchars((string) $createErrors['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                </div>
                <div class="field-wrap">
                    <input type="text" name="lieu" placeholder="Location" value="<?= htmlspecialchars((string) ($createOld['lieu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($createErrors['lieu'])): ?><small class="field-error"><?= htmlspecialchars((string) $createErrors['lieu'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                </div>
                <select name="statut">
                    <option value="Ouvert" <?= (($createOld['statut'] ?? 'Ouvert') === 'Ouvert') ? 'selected' : ''; ?>>Ouvert</option>
                    <option value="Complet" <?= (($createOld['statut'] ?? '') === 'Complet') ? 'selected' : ''; ?>>Complet</option>
                    <option value="Ferme" <?= (($createOld['statut'] ?? '') === 'Ferme') ? 'selected' : ''; ?>>Ferme</option>
                </select>
                <div class="field-wrap">
                    <input type="number" name="capacite" placeholder="Capacity" value="<?= htmlspecialchars((string) ($createOld['capacite'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($createErrors['capacite'])): ?><small class="field-error"><?= htmlspecialchars((string) $createErrors['capacite'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                </div>
                <input type="text" name="image_url" placeholder="Image URL (optional)" value="<?= htmlspecialchars((string) ($createOld['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="field-wrap field-wide">
                    <textarea name="description" rows="2" placeholder="Description"><?= htmlspecialchars((string) ($createOld['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php if (isset($createErrors['description'])): ?><small class="field-error"><?= htmlspecialchars((string) $createErrors['description'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                </div>
                <button type="submit" class="btn-filter">Create Event</button>
            </form>

            <?php if ($editEvent !== null): ?>
                <form method="post" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/update&id=<?= (int) $editEvent['id']; ?>" class="create-event-form edit-form" novalidate>
                    <input type="hidden" name="redirect_target" value="backoffice_events">
                    <div class="field-wrap">
                        <input type="text" name="titre" value="<?= htmlspecialchars((string) ($editOld['titre'] ?? $editEvent['titre']), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (isset($editErrors['titre'])): ?><small class="field-error"><?= htmlspecialchars((string) $editErrors['titre'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                    </div>
                    <div class="field-wrap">
                        <input type="date" name="date_evenement" value="<?= htmlspecialchars((string) ($editOld['date_evenement'] ?? $editEvent['date_evenement']), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (isset($editErrors['date_evenement'])): ?><small class="field-error"><?= htmlspecialchars((string) $editErrors['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                    </div>
                    <div class="field-wrap">
                        <input type="text" name="lieu" value="<?= htmlspecialchars((string) ($editOld['lieu'] ?? $editEvent['lieu']), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (isset($editErrors['lieu'])): ?><small class="field-error"><?= htmlspecialchars((string) $editErrors['lieu'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                    </div>
                    <select name="statut">
                        <?php $editStatus = strtolower((string) ($editOld['statut'] ?? $editEvent['statut'])); ?>
                        <option value="Ouvert" <?= $editStatus === 'ouvert' ? 'selected' : ''; ?>>Ouvert</option>
                        <option value="Complet" <?= $editStatus === 'complet' ? 'selected' : ''; ?>>Complet</option>
                        <option value="Ferme" <?= $editStatus === 'ferme' ? 'selected' : ''; ?>>Ferme</option>
                    </select>
                    <div class="field-wrap">
                        <input type="number" name="capacite" value="<?= htmlspecialchars((string) ($editOld['capacite'] ?? (string) ((int) $editEvent['capacite'])), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (isset($editErrors['capacite'])): ?><small class="field-error"><?= htmlspecialchars((string) $editErrors['capacite'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                    </div>
                    <input type="text" name="image_url" placeholder="Image URL (optional)" value="<?= htmlspecialchars((string) ($editOld['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="field-wrap field-wide">
                        <textarea name="description" rows="2"><?= htmlspecialchars((string) ($editOld['description'] ?? ($editEvent['description'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php if (isset($editErrors['description'])): ?><small class="field-error"><?= htmlspecialchars((string) $editErrors['description'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
                    </div>
                    <button type="submit" class="btn-filter">Save Update</button>
                </form>
            <?php endif; ?>

            <form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php" class="filters">
                <input type="hidden" name="route" value="backoffice/index">
                <input type="hidden" name="page" value="events">
                <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search title or location">
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8'); ?>">
                <select name="sort">
                    <option value="participants_desc" <?= $sort === 'participants_desc' ? 'selected' : ''; ?>>Participants (desc)</option>
                    <option value="participants_asc" <?= $sort === 'participants_asc' ? 'selected' : ''; ?>>Participants (asc)</option>
                    <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : ''; ?>>Date (newest)</option>
                    <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : ''; ?>>Date (oldest)</option>
                </select>
                <button type="submit" class="btn-filter">Apply</button>
            </form>

            <?php if ($selectedEvent !== null): ?>
                <div class="selected-event">
                    <h4>Selected Event: <?= htmlspecialchars((string) $selectedEvent['titre'], ENT_QUOTES, 'UTF-8'); ?></h4>
                    <div class="selected-stats">
                        <div><strong><?= (int) $selectedEvent['participants_count']; ?></strong><span>Participants</span></div>
                        <div><strong><?= (int) $selectedEvent['capacite']; ?></strong><span>Capacity</span></div>
                        <div><strong><?= ((int) $selectedEvent['capacite'] > 0) ? (int) round(((int) $selectedEvent['participants_count'] / (int) $selectedEvent['capacite']) * 100) : 0; ?>%</strong><span>Fill Rate</span></div>
                    </div>

                    <div class="participants-panel">
                        <h5>Participants for selected event</h5>
                        <?php if ($selectedEventParticipantsList === []): ?>
                            <p class="empty-message">No participants for this event yet.</p>
                        <?php else: ?>
                            <div class="participants-list">
                                <?php foreach ($selectedEventParticipantsList as $participant): ?>
                                    <div class="participant-item">
                                        <span><?= htmlspecialchars((string) ($participant['nom'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <small><?= htmlspecialchars((string) ($participant['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="table-scroll">
                <table class="events-table">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Capacity</th>
                        <th>Participants</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($eventsTable === []): ?>
                        <tr><td colspan="7" class="empty-message">No events found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($eventsTable as $event): ?>
                            <tr>
                                <td><a class="event-link-table" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=backoffice/index&page=events&event_id=<?= (int) $event['id']; ?>"><?= htmlspecialchars((string) $event['titre'], ENT_QUOTES, 'UTF-8'); ?></a></td>
                                <td><?= htmlspecialchars((string) $event['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars((string) $event['lieu'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars((string) $event['statut'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= (int) $event['capacite']; ?></td>
                                <td><?= (int) $event['participants_count']; ?></td>
                                <td class="action-cell">
                                    <a class="action-link" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=backoffice/index&page=events&edit_id=<?= (int) $event['id']; ?>">Edit</a>
                                    <a class="action-link" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=backoffice/index&page=events&event_id=<?= (int) $event['id']; ?>">View participants</a>
                                    <form method="post" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/delete&id=<?= (int) $event['id']; ?>" onsubmit="return confirm('Delete this event?');">
                                        <input type="hidden" name="redirect_target" value="backoffice_events">
                                        <button type="submit" class="action-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="meta-summary">
                <span>Past events: <strong><?= $pastEvents; ?></strong></span>
                <span>Filtered events: <strong><?= count($eventsTable); ?></strong></span>
            </div>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
const eventsPerMonthLabels = <?= json_encode($eventsPerMonthLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const eventsPerMonthData = <?= json_encode($eventsPerMonthData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const participantsPerEventLabels = <?= json_encode($participantsPerEventLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const participantsPerEventData = <?= json_encode($participantsPerEventData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const statusLabels = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const statusData = <?= json_encode($statusData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

const gridColor = 'rgba(157, 170, 207, 0.18)';
const tickColor = '#aab3cc';
const safeLineLabels = eventsPerMonthLabels.length ? eventsPerMonthLabels : ['No data'];
const safeLineData = eventsPerMonthData.length ? eventsPerMonthData : [0];
const safeBarLabels = participantsPerEventLabels.length ? participantsPerEventLabels : ['No data'];
const safeBarData = participantsPerEventData.length ? participantsPerEventData : [0];
const safeDonutData = statusData.reduce((sum, value) => sum + Number(value || 0), 0) > 0 ? statusData : [1, 0];

new Chart(document.getElementById('eventsLineChart'), {
    type: 'line',
    data: {
        labels: safeLineLabels,
        datasets: [{ label: 'Events', data: safeLineData, borderColor: '#7b7cff', backgroundColor: 'rgba(123, 124, 255, 0.18)', fill: true, tension: 0.35 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { color: gridColor }, ticks: { color: tickColor } }, y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor } } } }
});

new Chart(document.getElementById('participantsBarChart'), {
    type: 'bar',
    data: {
        labels: safeBarLabels,
        datasets: [{ label: 'Participants', data: safeBarData, backgroundColor: ['#5eead4', '#60a5fa', '#a78bfa', '#f472b6', '#22d3ee', '#818cf8'], borderRadius: 8 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { color: gridColor }, ticks: { color: tickColor } }, y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor } } } }
});

new Chart(document.getElementById('statusDonutChart'), {
    type: 'doughnut',
    data: { labels: statusLabels, datasets: [{ data: safeDonutData, backgroundColor: ['#7b7cff', '#f472b6'], borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: tickColor } } } }
});

const exportBtn = document.getElementById('exportPdfBtn');
if (exportBtn) {
    exportBtn.addEventListener('click', () => {
        const jsPdfGlobal = window.jspdf;
        if (!jsPdfGlobal || !jsPdfGlobal.jsPDF) {
            alert('PDF library failed to load. Please refresh the page.');
            return;
        }

        const { jsPDF } = jsPdfGlobal;
        const doc = new jsPDF({ unit: 'pt', format: 'a4' });

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(18);
        doc.text('Events Admin Report', 40, 45);

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.text('Generated: ' + new Date().toLocaleString(), 40, 62);

        doc.setFontSize(11);
        doc.text('Total Events: <?= (int) $totalEvents; ?>', 40, 84);
        doc.text('Total Participants: <?= (int) $totalParticipants; ?>', 40, 100);
        doc.text('Upcoming Events: <?= (int) $upcomingEvents; ?>', 40, 116);
        doc.text('Past Events: <?= (int) $pastEvents; ?>', 40, 132);
        doc.text('Fill Rate: <?= number_format($fillRate, 1); ?>%', 40, 148);

        let y = 180;
        doc.setFont('helvetica', 'bold');
        doc.text('Events', 40, y);
        y += 18;
        doc.setFont('helvetica', 'normal');

        const rows = Array.from(document.querySelectorAll('.events-table tbody tr'));
        const maxRows = Math.min(rows.length, 20);

        for (let i = 0; i < maxRows; i++) {
            const cells = rows[i].querySelectorAll('td');
            if (cells.length < 6) continue;

            const line = [
                cells[0].innerText.trim(),
                cells[1].innerText.trim(),
                cells[2].innerText.trim(),
                cells[3].innerText.trim(),
                'Cap: ' + cells[4].innerText.trim(),
                'Part: ' + cells[5].innerText.trim()
            ].join(' | ');

            const wrapped = doc.splitTextToSize(line, 520);
            doc.text(wrapped, 40, y);
            y += (wrapped.length * 13) + 4;

            if (y > 780 && i < maxRows - 1) {
                doc.addPage();
                y = 40;
            }
        }

        doc.save('events-admin-report.pdf');
    });
}
</script>
<?php require BASE_PATH . '/views/backoffice/includes/footer.php'; ?>
