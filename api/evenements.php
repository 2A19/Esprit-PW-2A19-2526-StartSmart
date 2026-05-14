<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Participant.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'participants') {
    $eventId = (int) ($_GET['event_id'] ?? 0);
    if ($eventId <= 0) {
        echo json_encode([]);
        exit;
    }
    $model = new Participant();
    $rows  = $model->allByEventId($eventId);
    echo json_encode(array_map(function ($p) {
        return [
            'nom'       => $p['nom']       ?? '',
            'prenom'    => $p['prenom']    ?? '',
            'age'       => $p['age']       ?? '',
            'email'     => $p['email']     ?? '',
            'telephone' => $p['telephone'] ?? '',
            'projet'    => $p['projet']    ?? '',
        ];
    }, $rows), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['error' => 'Action inconnue']);
