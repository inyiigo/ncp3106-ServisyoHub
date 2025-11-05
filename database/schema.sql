-- ServisyoHub Database Schema
-- Run this file to create the necessary database tables

-- Jobs/Gawain table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(200) NOT NULL,
    budget DECIMAL(10,2) NULL,
    date_needed DATE NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    posted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_jobs_status (status),
    INDEX idx_jobs_posted_at (posted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add other tables below as needed
