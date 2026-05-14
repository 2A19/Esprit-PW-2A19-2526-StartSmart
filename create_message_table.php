<?php
require 'config/Database.php';

try {
    $db = (new Database())->getConnection();
    
    // Create message table
    $query = "
    CREATE TABLE IF NOT EXISTS message (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        projet_id INT NOT NULL,
        content TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
        FOREIGN KEY (projet_id) REFERENCES projet(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($query);
    echo "Message table created successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
