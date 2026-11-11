CREATE TABLE shortlink (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_code VARCHAR(8) UNIQUE NOT NULL,
    original_url TEXT NOT NULL,
    clicks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_short_code (short_code)
);