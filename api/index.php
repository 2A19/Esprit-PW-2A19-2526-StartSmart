<?php
/**
 * API Router - Central endpoint for all API calls
 * Routes requests to appropriate handlers
 */

header('Content-Type: application/json');

// Get the full path
$path = $_GET['path'] ?? '';

// Parse first segment (endpoint type)
$parts = explode('/', trim($path, '/'));
$endpoint = $parts[0] ?? '';

// Route to appropriate handler
switch ($endpoint) {
    case 'auth':
        require __DIR__ . '/auth.php';
        break;
        
    case 'users.php':
        require __DIR__ . '/users.php';
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint not found: ' . $endpoint]);
        exit;
}

