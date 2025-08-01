-- Add users table for customer registration and login
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    date_of_birth DATE,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Update reservations table to link with users
ALTER TABLE reservations ADD COLUMN user_id INT NULL;
ALTER TABLE reservations ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Update contact_messages table to link with users
ALTER TABLE contact_messages ADD COLUMN user_id INT NULL;
ALTER TABLE contact_messages ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Update orders table to link with users
ALTER TABLE orders ADD COLUMN user_id INT NULL;
ALTER TABLE orders ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add user sessions table for better session management
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample users (password is 'password123' hashed)
INSERT INTO users (first_name, last_name, email, password, phone, address, city, postal_code, email_verified) VALUES
('John', 'Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(555) 123-4567', '123 Main St', 'New York', '10001', TRUE),
('Jane', 'Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(555) 987-6543', '456 Oak Ave', 'New York', '10002', TRUE),
('Mike', 'Johnson', 'mike.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(555) 456-7890', '789 Pine St', 'New York', '10003', TRUE);
