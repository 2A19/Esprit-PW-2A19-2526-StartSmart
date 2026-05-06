<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

header('Content-Type: application/json');

use Config\Database;

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    require_once dirname(__DIR__) . '/config.php';
    require_once dirname(__DIR__) . '/config/Database.php';

    if (!defined('GROQ_API_KEY') || GROQ_API_KEY === '') {
        throw new Exception('Missing GROQ_API_KEY in config.php');
    }

    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
        echo json_encode(['error' => 'Invalid request format']);
        exit;
    }

    $sanitizedMessages = [];
    foreach ($input['messages'] as $msg) {
        if (!is_array($msg)) {
            continue;
        }

        $role = (string) ($msg['role'] ?? 'user');
        $content = trim((string) ($msg['content'] ?? ''));
        if ($content === '') {
            continue;
        }
        if (!in_array($role, ['user', 'assistant'], true)) {
            $role = 'user';
        }

        $sanitizedMessages[] = [
            'role' => $role,
            'content' => function_exists('mb_strlen') && mb_strlen($content) > 1200
                ? mb_substr($content, 0, 1200)
                : $content,
        ];
    }

    if ($sanitizedMessages === []) {
        echo json_encode(['error' => 'No valid messages provided']);
        exit;
    }

    $sanitizedMessages = array_slice($sanitizedMessages, -12);

    $pdo = Database::getConnection();

    $events = [];
    try {
        $result = $pdo->query("SELECT id, titre, date_evenement, lieu, description, statut FROM evenements ORDER BY date_evenement DESC LIMIT 50");
        $events = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) {
        $events = [];
    }

    $participants = [];
    try {
        $result = $pdo->query("SELECT id, nom, email FROM participants ORDER BY id DESC LIMIT 50");
        $participants = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) {
        $participants = [];
    }

    $projects = [];
    try {
        $result = $pdo->query("SELECT * FROM projects LIMIT 50");
        $projects = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) {
        $projects = [];
    }

    $ressourcesHumaines = [];
    try {
        $result = $pdo->query("SELECT * FROM ressources_humaines LIMIT 50");
        $ressourcesHumaines = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) {
        $ressourcesHumaines = [];
    }

    $formations = [];
    try {
        $result = $pdo->query("SELECT * FROM formations LIMIT 50");
        $formations = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) {
        $formations = [];
    }

    $system_prompt = "You are an AI assistant for SmartSmart, a platform for innovative entrepreneurs in Tunisia. "
        . "You ONLY answer questions about SmartSmart's data. Do not answer unrelated questions.\n\n"
        . "Here is the current data from the platform:\n\n"
        . "EVENTS: " . json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n"
        . "PARTICIPANTS: " . json_encode($participants, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n"
        . "PROJECTS: " . json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n"
        . "RESSOURCES_HUMAINES: " . json_encode($ressourcesHumaines, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n"
        . "FORMATIONS: " . json_encode($formations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n"
        . "If the user asks about something not in this data, say: 'Je n'ai pas cette information sur SmartSmart.' "
        . "Always respond in the same language the user writes in (French or Arabic or English).";

    $requestBody = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => array_merge(
            [['role' => 'system', 'content' => $system_prompt]],
            $sanitizedMessages
        ),
        'max_tokens' => 1000,
        'temperature' => 0.2,
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . GROQ_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($requestBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $response = curl_exec($ch);
    if (!$response) {
        throw new Exception('No response from Groq: ' . curl_error($ch));
    }

    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode >= 400) {
        throw new Exception('Groq HTTP error: ' . $statusCode . ' | ' . $response);
    }

    $data = json_decode((string) $response, true);
    if (!isset($data['choices'][0]['message']['content'])) {
        throw new Exception('Bad Groq response: ' . (string) $response);
    }

    $reply = trim((string) $data['choices'][0]['message']['content']);
    if ($reply === '') {
        throw new Exception('Empty assistant reply');
    }

    echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Exception $e) {
    echo json_encode(['debug_error' => $e->getMessage()]);
    exit;
} catch (Error $e) {
    echo json_encode(['debug_error' => $e->getMessage()]);
    exit;
}
