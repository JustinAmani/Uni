-- Université des Mascareignes - University Application System
-- Database Schema

CREATE DATABASE IF NOT EXISTS udm_applications CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE udm_applications;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('applicant', 'agent', 'admin') DEFAULT 'applicant',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    reset_token VARCHAR(100) NULL,
    reset_expires DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_number VARCHAR(20) UNIQUE,
    user_id INT NOT NULL,
    status ENUM('draft','submitted','under_review','accepted','rejected') DEFAULT 'draft',
    current_step INT DEFAULT 1,

    -- Personal Details
    title VARCHAR(10),
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    maiden_name VARCHAR(100),
    gender VARCHAR(10),
    date_of_birth DATE,
    marital_status VARCHAR(20),
    national_id VARCHAR(100),
    place_of_birth VARCHAR(100),
    nationality VARCHAR(100),
    email VARCHAR(255),
    mobile_number VARCHAR(30),
    home_number VARCHAR(30),

    -- Permanent Address
    perm_address_line1 VARCHAR(255),
    perm_address_line2 VARCHAR(255),
    perm_address_line3 VARCHAR(255),
    perm_town VARCHAR(100),
    perm_postcode VARCHAR(20),
    perm_country VARCHAR(100),

    -- Correspondence Address (in Mauritius)
    corr_address_line1 VARCHAR(255),
    corr_address_line2 VARCHAR(255),
    corr_town VARCHAR(100),

    -- Course Level
    course_level VARCHAR(50),
    entry_level VARCHAR(50),

    -- Language Certificates
    has_english_cert TINYINT(1) DEFAULT 0,
    has_french_cert TINYINT(1) DEFAULT 0,

    -- Scholarship
    has_scholarship TINYINT(1) DEFAULT 0,
    scholarship_type VARCHAR(50),
    scholarship_other VARCHAR(255),

    -- Under 18 Guardian
    guardian_name VARCHAR(200),
    guardian_address TEXT,
    guardian_occupation VARCHAR(100),
    guardian_phone VARCHAR(30),
    guardian_mobile VARCHAR(30),

    -- Payment
    payment_status ENUM('pending','paid') DEFAULT 'pending',
    payment_receipt VARCHAR(50),
    payment_amount DECIMAL(8,2) DEFAULT 25.00,

    -- Declaration
    declaration_agreed TINYINT(1) DEFAULT 0,
    declaration_date DATE,

    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    preference_order TINYINT NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    faculty_code VARCHAR(10),
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS secondary_schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    institution_name VARCHAR(255) NOT NULL,
    entered_month TINYINT,
    entered_year SMALLINT,
    left_month TINYINT,
    left_year SMALLINT,
    display_order TINYINT DEFAULT 0,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS o_level_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    subject_name VARCHAR(255) NOT NULL,
    attempt1_month VARCHAR(7),
    attempt1_grade VARCHAR(10),
    attempt2_month VARCHAR(7),
    attempt2_grade VARCHAR(10),
    attempt3_month VARCHAR(7),
    attempt3_grade VARCHAR(10),
    display_order TINYINT DEFAULT 0,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS a_level_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    level_type ENUM('principal','subsidiary') NOT NULL,
    subject_name VARCHAR(255) NOT NULL,
    attempt1_month VARCHAR(7),
    attempt1_grade VARCHAR(10),
    attempt2_month VARCHAR(7),
    attempt2_grade VARCHAR(10),
    attempt3_month VARCHAR(7),
    attempt3_grade VARCHAR(10),
    display_order TINYINT DEFAULT 0,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS employment_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    employer_name VARCHAR(255),
    job_title VARCHAR(255),
    start_date DATE,
    end_date DATE,
    is_current TINYINT(1) DEFAULT 0,
    responsibilities TEXT,
    display_order TINYINT DEFAULT 0,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    document_type ENUM('passport_photo','national_id','sc_certificate','hsc_certificate','degree_diploma','other') NOT NULL,
    original_name VARCHAR(255),
    stored_name VARCHAR(255),
    file_path VARCHAR(500),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- Default agent account (password: Agent@2026)
INSERT IGNORE INTO users (email, password, role, first_name, last_name)
VALUES (
    'agent@udm.ac.mu',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'agent',
    'UdM',
    'Agent'
);

-- Default admin (password: Admin@2026)
INSERT IGNORE INTO users (email, password, role, first_name, last_name)
VALUES (
    'admin@udm.ac.mu',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'UdM',
    'Admin'
);
