<?php
require_once __DIR__ . '/../models/Notification.php';

class NotificationService {
    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new Notification($db);
    }

    public function createNotification($userId, $actorId, $type, $referenceId = null, $message = null) {
        if ($userId === $actorId) return false;
        $this->model->user_id = $userId;
        $this->model->actor_id = $actorId;
        $this->model->type = $type;
        $this->model->reference_id = $referenceId;
        $this->model->message = $message;
        return $this->model->create();
    }

    public function getLatestForUser($userId, $limit = 10) {
        return $this->model->getUnreadByUser($userId, $limit);
    }

    public function getSinceForUser($userId, $sinceId = 0, $limit = 50) {
        return $this->model->getSinceByUser($userId, $sinceId, $limit);
    }

    public function countUnread($userId) {
        return $this->model->countUnreadByUser($userId);
    }

    public function markRead($id, $userId) {
        return $this->model->markAsRead($id, $userId);
    }

    public function markAllRead($userId) {
        return $this->model->markAllRead($userId);
    }
}

?>
