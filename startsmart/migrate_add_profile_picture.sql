-- ============================================================
-- StartSmart – Migration: Add Profile Picture Support
-- Add profile_picture column to users table
-- ============================================================

USE startsmart_db;

-- Add profile_picture column if it doesn't exist
ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER derniere_connexion;

-- Optional: Create an index for faster queries
-- ALTER TABLE users ADD INDEX idx_profile (profile_picture);
