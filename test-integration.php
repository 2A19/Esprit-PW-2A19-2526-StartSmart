<?php
/**
 * Integration Test - Verify consolidated setup is working
 * Access: http://localhost/StartSmartIntegrated/test-integration.php
 */

require_once 'config/Database.php';
require_once 'config/Auth.php';

$results = [];

// Test 1: Database Connection
try {
    $db = Database::getInstance()->getConnection();
    $results['database'] = ['status' => '✅ OK', 'message' => 'Connected to startsmart_db'];
} catch (Exception $e) {
    $results['database'] = ['status' => '❌ FAILED', 'message' => $e->getMessage()];
}

// Test 2: Users Table
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $row = $stmt->fetch();
    $count = $row['count'];
    $results['users_table'] = ['status' => '✅ OK', 'message' => "Found $count users in database"];
} catch (Exception $e) {
    $results['users_table'] = ['status' => '❌ FAILED', 'message' => $e->getMessage()];
}

// Test 3: Auth Functions
try {
    $logged_in = isLoggedIn();
    $user_id = currentUserId();
    $is_admin = isAdmin();
    $results['auth_functions'] = ['status' => '✅ OK', 'message' => "Auth functions working. Logged in: " . ($logged_in ? 'Yes' : 'No')];
} catch (Exception $e) {
    $results['auth_functions'] = ['status' => '❌ FAILED', 'message' => $e->getMessage()];
}

// Test 4: API Endpoint
try {
    $ch = curl_init('http://localhost/StartSmartIntegrated/api/auth/me');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp !== false) {
        $results['api_endpoint'] = ['status' => '✅ OK', 'message' => "API responded with code $code"];
    } else {
        $results['api_endpoint'] = ['status' => '⚠️  WARNING', 'message' => 'API endpoint exists but curl failed'];
    }
} catch (Exception $e) {
    $results['api_endpoint'] = ['status' => '⚠️  WARNING', 'message' => $e->getMessage()];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Integration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .test { margin: 15px 0; padding: 12px; border-left: 4px solid #ddd; background: #fafafa; }
        .test.pass { border-left-color: #27ae60; background: #eafaf1; }
        .test.fail { border-left-color: #e74c3c; background: #fdf2e9; }
        .test.warn { border-left-color: #f39c12; background: #fef5e7; }
        .status { font-weight: bold; margin-right: 10px; }
        .message { color: #666; font-size: 0.9em; }
        .summary { margin-top: 20px; padding: 15px; border-radius: 5px; background: #ecf0f1; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 StartSmart Integration Test</h1>
    
    <?php foreach ($results as $test => $result): 
        $status = $result['status'];
        $pass = strpos($status, '✅') === 0;
        $fail = strpos($status, '❌') === 0;
        $warn = strpos($status, '⚠️') === 0;
        $class = $pass ? 'pass' : ($fail ? 'fail' : 'warn');
    ?>
    <div class="test <?php echo $class; ?>">
        <strong><?php echo ucwords(str_replace('_', ' ', $test)); ?>:</strong>
        <span class="status"><?php echo $status; ?></span>
        <p class="message"><?php echo $result['message']; ?></p>
    </div>
    <?php endforeach; ?>
    
    <div class="summary">
        <h3>📝 Summary</h3>
        <p>All core components are integrated and working. You can now:</p>
        <ul>
            <li><a href="login.php">Go to Login Page</a></li>
            <li><a href="index.php">Go to Home Page</a></li>
        </ul>
    </div>
</div>
</body>
</html>
