CREATE DATABASE mdsv_reporting_system;
USE mdsv_reporting_system;

-- USERS TABLE (admin, teacher, csu, jassu, student)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100),
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','teacher','csu','jassu','student') NOT NULL,
  is_first_login TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- DEPARTMENTS
CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(10) NOT NULL
);

INSERT INTO departments (name, code) VALUES
('School of Technology', 'SOT'),
('School of Education', 'SOE'),
('School of Business', 'SOB');

-- COURSES
CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  department_id INT,
  FOREIGN KEY (department_id) REFERENCES departments(id)
);

INSERT INTO courses (name, department_id) VALUES
('BSIT(Infotech)', 1), ('BIT(Comptech)', 1), ('BIT(Electrote)', 1),
('BSED', 2), ('BEED', 2),
('BSBA', 3), ('BSA', 3);

-- STUDENTS
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(20) UNIQUE NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  course_id INT,
  year_level INT,
  department_id INT,
  user_id INT,
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (department_id) REFERENCES departments(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- VIOLATIONS TABLE
CREATE TABLE violations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(20) NOT NULL,
  reporter_id INT NOT NULL,
  category ENUM('minor','major') NOT NULL,
  violation VARCHAR(100) NOT NULL,
  description TEXT,
  evidence_path VARCHAR(255),
  face_capture_path VARCHAR(255),
  signature_path VARCHAR(255),
  status ENUM('pending','ongoing','completed') DEFAULT 'pending',
  date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reporter_id) REFERENCES users(id)
);

-- DISCIPLINARY ACTIONS
CREATE TABLE disciplinary_actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  violation_id INT NOT NULL,
  student_id VARCHAR(20) NOT NULL,
  sanction TEXT NOT NULL,
  status ENUM('pending','ongoing','completed') DEFAULT 'pending',
  start_date DATE,
  end_date DATE,
  FOREIGN KEY (violation_id) REFERENCES violations(id)
);

-- STUDENT APPEALS
CREATE TABLE appeals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  violation_id INT NOT NULL,
  student_id VARCHAR(20) NOT NULL,
  explanation TEXT NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (violation_id) REFERENCES violations(id)
);

-- NOTIFICATIONS
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  link VARCHAR(255),
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- DEFAULT ADMIN
INSERT INTO users (full_name, username, email, password, role, is_first_login)
VALUES ('Administrator', 'admin', 'admin@mcc.edu', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'admin', 0);
-- default password is: admin123 (bcrypt hashed)