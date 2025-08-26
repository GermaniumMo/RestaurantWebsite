USE savoria_db;

ALTER TABLE users 
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER role;

UPDATE users SET is_active = TRUE WHERE is_active IS NULL;

ALTER TABLE users ADD INDEX idx_active (is_active);
