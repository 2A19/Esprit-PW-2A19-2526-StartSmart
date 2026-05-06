ALTER TABLE users
  ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER derniere_connexion,
  ADD COLUMN remember_selector VARCHAR(32) DEFAULT NULL AFTER ban_reason,
  ADD COLUMN remember_token_hash VARCHAR(255) DEFAULT NULL AFTER remember_selector,
  ADD COLUMN remember_expires DATETIME DEFAULT NULL AFTER remember_token_hash;

CREATE UNIQUE INDEX idx_users_google_id ON users(google_id);
CREATE INDEX idx_users_remember_selector ON users(remember_selector);
