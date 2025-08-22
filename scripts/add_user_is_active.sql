-- Add is_active column to users table
USE savoria_db;

-- Add is_active column to users table
ALTER TABLE users 
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER role;

-- Update existing users to be active
UPDATE users SET is_active = TRUE WHERE is_active IS NULL;

-- Add index for performance
ALTER TABLE users ADD INDEX idx_active (is_active);
