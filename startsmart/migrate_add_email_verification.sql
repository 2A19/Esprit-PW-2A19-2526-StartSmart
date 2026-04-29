-- ============================================================
--  StartSmart – Migration: Add email verification
--  Run this ONCE in phpMyAdmin > SQL tab if you already have
--  an existing startsmart_db (don't re-import the full SQL)
-- ============================================================

USE startsmart_db;

-- 1. Add 'pending' to the statut enum (if not already there)
ALTER TABLE users
  MODIFY COLUMN statut ENUM('actif','inactif','banni','verifie','pending') DEFAULT 'pending';

-- 2. Add email verification columns (if not already there)
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS email_token VARCHAR(64) DEFAULT NULL AFTER derniere_connexion,
  ADD COLUMN IF NOT EXISTS email_token_expires DATETIME DEFAULT NULL AFTER email_token;

-- 3. Add index for fast token lookups
CREATE INDEX IF NOT EXISTS idx_users_email_token ON users(email_token);

-- 4. IMPORTANT: Mark all existing users as already verified
--    so they can still log in without needing to re-verify
UPDATE users SET statut = 'actif' WHERE statut IN ('actif', 'verifie') OR statut IS NULL;

-- Done! Existing accounts keep working. New registrations will require email verification.
