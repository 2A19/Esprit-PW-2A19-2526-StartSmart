#!/usr/bin/env php
<?php
/**
 * CLI: verify MySQL config used by StartSmartIntegrated (same rules as config/Database.php).
 *
 * Usage (from project root):
 *   php scripts/db_health.php
 */
$root = dirname(__DIR__);
require_once $root . '/config/Database.php';

$c = Database::getResolvedConfig();
echo "Resolved DB config:\n";
echo "  host: {$c['host']}\n";
echo "  port: {$c['port']}\n";
echo "  database: {$c['database']}\n";
echo "  username: {$c['username']}\n";
echo "  password: " . ($c['password'] !== '' ? '(set)' : '(empty)') . "\n\n";

try {
    $pdo = (new Database())->getConnection();
    echo "PDO connect: OK\n";
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo "  Current schema: " . ($dbName ?: '(none)') . "\n";

    $tables = ['users', 'utilisateur', 'job_offers', 'post', 'projet'];
    foreach ($tables as $t) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $stmt->execute([$t]);
        $exists = (int) $stmt->fetchColumn() > 0;
        echo "  table `{$t}`: " . ($exists ? 'yes' : 'no') . "\n";
    }

    if ($pdo->query("SHOW TABLES LIKE 'users'")->rowCount()) {
        $n = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        echo "\nusers row count: {$n}\n";
    } else {
        echo "\nWARNING: no `users` table — RH login uses this table. Import or merge RH schema into this database.\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "FAILED: " . $e->getMessage() . "\n");
    exit(1);
}

echo "\nDone.\n";
