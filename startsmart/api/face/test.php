<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$results = [];

// 1. Session check
$results['session_user_id'] = $_SESSION['user_id'] ?? 'MISSING - not logged in';

// 2. shell_exec enabled?
$disabled = ini_get('disable_functions');
$results['shell_exec_disabled'] = str_contains($disabled, 'shell_exec') ? 'YES - THIS IS THE PROBLEM' : 'no';

// 3. Find python
$python3 = trim(shell_exec('where python3 2>NUL') ?? '');
$python  = trim(shell_exec('where python 2>NUL') ?? '');
$results['python3_path'] = $python3 ?: 'not found';
$results['python_path']  = $python  ?: 'not found';

// 4. Python version
$ver = trim(shell_exec('python --version 2>&1') ?? '');
$results['python_version'] = $ver ?: 'could not run';

// 5. Service file exists?
$service = __DIR__ . '/../../python/face_recognition_service.py';
$results['service_file_exists'] = file_exists($service) ? 'yes' : 'NO - MISSING';
$results['service_path'] = $service;

// 6. Try running service directly
if (file_exists($service)) {
    $out = shell_exec('python ' . escapeshellarg($service) . ' help 2>&1');
    $results['service_test'] = $out ?? 'null - shell_exec returned nothing';
}

// 7. temp dir writable?
$temp = __DIR__ . '/../../public/img/temp/';
if (!is_dir($temp)) @mkdir($temp, 0755, true);
$results['temp_dir_writable'] = is_writable($temp) ? 'yes' : 'NO - NOT WRITABLE';

echo json_encode($results, JSON_PRETTY_PRINT);
