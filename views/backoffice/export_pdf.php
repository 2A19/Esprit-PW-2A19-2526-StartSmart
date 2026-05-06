<?php
declare(strict_types=1);

require BASE_PATH . '/views/backoffice/includes/config.php';

$search = trim((string) ($_GET['q'] ?? ''));
$startDate = trim((string) ($_GET['start_date'] ?? ''));
$endDate = trim((string) ($_GET['end_date'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'participants_desc'));

$participantFkColumn = 'event_id';
$events = [];
$totalEvents = 0;
$totalParticipants = 0;
$upcomingEvents = 0;
$pastEvents = 0;

if ($pdo instanceof PDO) {
    try {
        $hasEvenementId = $pdo->query("SHOW COLUMNS FROM participants LIKE 'evenement_id'")->fetch() !== false;
        $participantFkColumn = $hasEvenementId ? 'evenement_id' : 'event_id';

        $totalEvents = (int) $pdo->query('SELECT COUNT(*) FROM evenements')->fetchColumn();
        $totalParticipants = (int) $pdo->query('SELECT COUNT(*) FROM participants')->fetchColumn();
        $upcomingEvents = (int) $pdo->query("SELECT COUNT(*) FROM evenements WHERE date_evenement >= CURDATE()")->fetchColumn();
        $pastEvents = (int) $pdo->query("SELECT COUNT(*) FROM evenements WHERE date_evenement < CURDATE()")->fetchColumn();

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

        $sql = "SELECT e.id, e.titre, e.date_evenement, e.lieu, e.statut, e.capacite, COUNT(p.id) AS participants_count
                FROM evenements e
                LEFT JOIN participants p ON p.$participantFkColumn = e.id";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' GROUP BY e.id, e.titre, e.date_evenement, e.lieu, e.statut, e.capacite';
        $sql .= ' ORDER BY ' . $orderBy;

        $stmt = $pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $events = $stmt->fetchAll();
    } catch (Throwable $exception) {
        $events = [];
    }
}

$html = '<h1>Back Office Events Report</h1>';
$html .= '<p>Generated: ' . htmlspecialchars(date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8') . '</p>';
$html .= '<ul>';
$html .= '<li>Total Events: ' . $totalEvents . '</li>';
$html .= '<li>Total Participants: ' . $totalParticipants . '</li>';
$html .= '<li>Upcoming Events: ' . $upcomingEvents . '</li>';
$html .= '<li>Past Events: ' . $pastEvents . '</li>';
$html .= '</ul>';
$html .= '<table width="100%" border="1" cellspacing="0" cellpadding="6">';
$html .= '<thead><tr><th>Title</th><th>Date</th><th>Location</th><th>Status</th><th>Capacity</th><th>Participants</th></tr></thead><tbody>';
foreach ($events as $event) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars((string) $event['titre'], ENT_QUOTES, 'UTF-8') . '</td>';
    $html .= '<td>' . htmlspecialchars((string) $event['date_evenement'], ENT_QUOTES, 'UTF-8') . '</td>';
    $html .= '<td>' . htmlspecialchars((string) $event['lieu'], ENT_QUOTES, 'UTF-8') . '</td>';
    $html .= '<td>' . htmlspecialchars((string) $event['statut'], ENT_QUOTES, 'UTF-8') . '</td>';
    $html .= '<td>' . (int) $event['capacite'] . '</td>';
    $html .= '<td>' . (int) $event['participants_count'] . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

$autoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

if (class_exists(\Dompdf\Dompdf::class)) {
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml('<html><body style="font-family: DejaVu Sans, sans-serif;">' . $html . '</body></html>');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('events-report.pdf', ['Attachment' => true]);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><html><head><meta charset="UTF-8"><title>Report</title></head><body>';
echo '<p><strong>Dompdf not installed.</strong> Install it with <code>composer require dompdf/dompdf</code>.</p>';
echo $html;
echo '</body></html>';
