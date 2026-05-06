<?php
/**
 * api/notifications.php - AJAX endpoint for notifications
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';

switch ($action) {

    case 'list':
        $stmt = $db->prepare("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50");
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unread = $db->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();

        echo json_encode([
            'success'       => true,
            'notifications' => $notifications,
            'unread_count'  => (int)$unread
        ]);
        break;

    case 'mark_read':
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            // Mark all as read
            $db->exec("UPDATE notifications SET is_read = 1");
        }
        echo json_encode(['success' => true]);
        break;

    case 'count':
        $unread = $db->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
        echo json_encode(['unread_count' => (int)$unread]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
