CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    client_user_id INT,
    client_name VARCHAR(150),
    client_phone VARCHAR(50),
    client_email VARCHAR(100),
    complaint_description TEXT NOT NULL,
    position_id INT,
    contractor_id INT,
    complaint_type VARCHAR(100) NOT NULL,
    complaint_status VARCHAR(50) NOT NULL DEFAULT 'Received',
    complaint_priority VARCHAR(50) NOT NULL DEFAULT 'Medium',
    internal_observations TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_complaint_client_user_id ON complaints (client_user_id);
CREATE INDEX idx_complaint_position_id ON complaints (position_id);