-- ==========================================
-- 1. CREATE DATABASE
-- ==========================================
DROP DATABASE IF EXISTS tasks;
CREATE DATABASE tasks;
USE tasks;

-- ==========================================
-- 2. CREATE TABLES
-- ==========================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL DEFAULT '$2y$10$ZElHxKxfvZLeJJG0VrfLdewUim.LN4rKJIU6qJkHd.gPSPs7YKwoO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL DEFAULT '$2y$10$ZElHxKxfvZLeJJG0VrfLdewUim.LN4rKJIU6qJkHd.gPSPs7YKwoO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Task Table (UPDATED: Added status ENUM with default 'pending')
CREATE TABLE task (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    details TEXT,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'started', 'done') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- 3. TRIGGERS
-- ==========================================
DELIMITER $$
CREATE TRIGGER before_user_insert BEFORE INSERT ON users FOR EACH ROW
BEGIN IF EXISTS (SELECT 1 FROM admin WHERE mobile = NEW.mobile) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mobile exists in admin'; END IF; END$$
CREATE TRIGGER before_user_update BEFORE UPDATE ON users FOR EACH ROW
BEGIN IF NEW.mobile != OLD.mobile AND EXISTS (SELECT 1 FROM admin WHERE mobile = NEW.mobile) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mobile exists in admin'; END IF; END$$
CREATE TRIGGER before_admin_insert BEFORE INSERT ON admin FOR EACH ROW
BEGIN IF EXISTS (SELECT 1 FROM users WHERE mobile = NEW.mobile) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mobile exists in users'; END IF; END$$
CREATE TRIGGER before_admin_update BEFORE UPDATE ON admin FOR EACH ROW
BEGIN IF NEW.mobile != OLD.mobile AND EXISTS (SELECT 1 FROM users WHERE mobile = NEW.mobile) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mobile exists in users'; END IF; END$$
DELIMITER ;

-- ==========================================
-- 4. SAMPLE DATA
-- ==========================================
INSERT INTO admin (mobile) VALUES ('1000000001'), ('1000000002');
INSERT INTO users (name, role, mobile) VALUES ('Alice Smith', 'Developer', '2000000001'), ('Bob Johnson', 'Designer', '2000000002'), ('Charlie Davis', 'Manager', '2000000003');

INSERT INTO task (user_id, name, details, start_time, end_time) VALUES
(1, 'Fix Login Bug', 'Resolve issue where users cannot login via mobile OTP.', '09:00:00', '12:00:00'),
(1, 'Update Dashboard UI', 'Change the dashboard color scheme to match new branding.', '13:00:00', '17:00:00'),
(2, 'Design Mobile App Logo', 'Create a new modern logo for the upcoming mobile app release.', '10:00:00', '15:00:00'),
(3, 'Q4 Planning Meeting', 'Prepare and lead the Q4 task distribution meeting.', '14:00:00', '16:00:00');