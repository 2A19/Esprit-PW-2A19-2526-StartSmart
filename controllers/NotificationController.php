<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'services/NotificationService.php';

class NotificationController {
    private $db;
    private $service;

    public function __construct() {
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized", "success" => false]);
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->service = new NotificationService($this->db);
    }

    public function count() {
        header('Content-Type: application/json');
        $count = $this->service->countUnread(currentUserId());
        echo json_encode(["success" => true, "count" => $count]);
        exit;
    }

    public function list() {
        header('Content-Type: application/json');
        $items = $this->service->getLatestForUser(currentUserId(), 10);
        echo json_encode(["success" => true, "items" => $items]);
        exit;
    }

    public function stream() {
        // Server-Sent Events stream for notifications
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 0);
        set_time_limit(0);
        ignore_user_abort(true);

        $since = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
        $lastId = $since;

        // Unlock session so other requests from this user aren't blocked
        session_write_close();

        while (!connection_aborted()) {
            $items = $this->service->getSinceForUser(currentUserId(), $lastId, 50);
            if (!empty($items)) {
                foreach ($items as $n) {
                    $lastId = max($lastId, (int)$n['id_notification']);
                    $payload = json_encode($n);
                    echo "event: notification\n";
                    echo "data: {$payload}\n\n";
                }
                @ob_flush(); @flush();
            }

            // keep-alive ping
            echo ": ping\n\n";
            @ob_flush(); @flush();
            sleep(5);
        }
        exit;
    }

    public function markRead() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'));
        if (empty($data->id)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing id"]);
            exit;
        }
        $ok = $this->service->markRead((int)$data->id, currentUserId());
        echo json_encode(["success" => $ok]);
        exit;
    }

    public function markAllRead() {
        $ok = $this->service->markAllRead(currentUserId());
        echo json_encode(["success" => $ok]);
        exit;
    }
}

?>
