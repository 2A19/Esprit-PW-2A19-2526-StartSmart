-- ============================================================
--  StartSmart – Add Face Recognition Support
--  Adds columns to users table for face recognition
-- ============================================================

-- Check if columns exist before adding (for safety)
ALTER TABLE users
ADD COLUMN IF NOT EXISTS face_encoding LONGBLOB DEFAULT NULL,
ADD COLUMN IF NOT EXISTS face_recognition_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS face_setup_date DATETIME DEFAULT NULL;

-- Add index for quick lookups
CREATE INDEX IF NOT EXISTS idx_face_enabled ON users(face_recognition_enabled);

-- Note: face_encoding stores the JSON-serialized face encoding vector (numpy array as JSON)
