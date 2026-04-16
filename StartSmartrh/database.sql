-- StartSmart HR Database Schema
-- Create database and tables for HR management system

-- Create database
CREATE DATABASE IF NOT EXISTS startsmart_hr;
USE startsmart_hr;

-- Users table (for storing users: job seekers and startups)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('job_seeker', 'startup', 'admin') NOT NULL DEFAULT 'job_seeker',
    company_name VARCHAR(150),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job Offers table
CREATE TABLE IF NOT EXISTS job_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description LONGTEXT NOT NULL,
    requirements LONGTEXT,
    salary_min DECIMAL(10, 2) NOT NULL,
    salary_max DECIMAL(10, 2) NOT NULL,
    location VARCHAR(100) NOT NULL,
    type ENUM('Full-time', 'Part-time', 'Contract', 'Freelance') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_offer_id INT NOT NULL,
    user_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    experience VARCHAR(100),
    cover_letter LONGTEXT NOT NULL,
    resume VARCHAR(255),
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_job_offer_id (job_offer_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employees table
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_offer_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    salary DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_department (department),
    INDEX idx_status (status),
    UNIQUE KEY unique_email_per_company (user_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_offers_salary ON job_offers(salary_min, salary_max);
CREATE INDEX idx_offers_location ON job_offers(location);
CREATE INDEX idx_apps_email ON applications(email);
CREATE INDEX idx_emp_department ON employees(department, status);

-- Insert sample data (optional)
INSERT INTO users (full_name, email, password, role, company_name, phone) VALUES
('Demo Job Seeker', 'seeker@example.com', '$2y$10$L9VcvowvKvKQhR3Gj8R0Se5bNcANlVr3vu8dzppLHKpuK3XZLLILu', 'job_seeker', NULL, '555-0001'),
('Demo Startup', 'startup@example.com', '$2y$10$L9VcvowvKvKQhR3Gj8R0Se5bNcANlVr3vu8dzppLHKpuK3XZLLILu', 'startup', 'Tech Innovations Inc', '555-0002');
-- Password is: Password123 (hashed with bcrypt)
