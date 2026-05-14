-- Fixed matching system migration
CREATE TABLE IF NOT EXISTS matching_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    projet_id INT NOT NULL,
    match_score DECIMAL(5, 2),
    skill_match DECIMAL(5, 2),
    interest_match DECIMAL(5, 2),
    activity_match DECIMAL(5, 2),
    match_summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_cache_entry (user_id, projet_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (projet_id) REFERENCES projet(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS matching_interaction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    projet_id INT NOT NULL,
    action ENUM('view', 'like', 'skip', 'save', 'apply') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (projet_id) REFERENCES projet(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_projet_id (projet_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
