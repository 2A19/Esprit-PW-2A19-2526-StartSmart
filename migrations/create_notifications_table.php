<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/Database.php';

$db = Database::getInstance()->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL DEFAULT 'sms',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    related_id INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$db->exec($sql);
echo "✓ Table 'notifications' created successfully.\n";
