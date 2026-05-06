-- ============================================================
-- Migration: password reset OTP + timed ban support
-- Run in phpMyAdmin or mysql CLI
-- ============================================================

USE startsmart_db;

-- Password reset OTP columns
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS reset_otp         VARCHAR(6)   DEFAULT NULL AFTER email_token_expires,
  ADD COLUMN IF NOT EXISTS reset_otp_expires DATETIME     DEFAULT NULL AFTER reset_otp;

-- Timed ban support (NULL = permanent ban)
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS ban_expires       DATETIME     DEFAULT NULL AFTER reset_otp_expires,
  ADD COLUMN IF NOT EXISTS ban_reason        VARCHAR(255) DEFAULT NULL AFTER ban_expires;

-- Index for OTP lookup
CREATE INDEX IF NOT EXISTS idx_users_reset_otp ON users(reset_otp);
