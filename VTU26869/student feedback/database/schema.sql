CREATE DATABASE IF NOT EXISTS student_feedback_db;
USE student_feedback_db;

-- 1. Users table (Centralized authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student', 'faculty') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (role)
) ENGINE=InnoDB;

-- 2. Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

-- 3. Subjects table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

-- 4. Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    roll_no VARCHAR(20) NOT NULL UNIQUE,
    semester INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Faculty table
CREATE TABLE faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department VARCHAR(100) NOT NULL,
    designation VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Subject Assignments (Mapping Subjects to Faculty for specific Courses)
CREATE TABLE subject_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY (subject_id, faculty_id, course_id, academic_year)
) ENGINE=InnoDB;

-- 7. Feedback Questions
CREATE TABLE feedback_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    question_type ENUM('rating', 'text') DEFAULT 'rating',
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

-- 8. Feedback Responses (Main record for a submission)
CREATE TABLE feedback_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assignment_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES subject_assignments(id) ON DELETE CASCADE,
    UNIQUE KEY (student_id, assignment_id) -- One feedback per subject assignment
) ENGINE=InnoDB;

-- 9. Feedback Answers (Individual answers for each question)
CREATE TABLE feedback_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    question_id INT NOT NULL,
    rating INT DEFAULT 0, -- 1 to 5 for rating type
    answer_text TEXT,      -- for text type
    FOREIGN KEY (response_id) REFERENCES feedback_responses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES feedback_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample Data
-- Admin (Password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@feedback.com', '$2y$10$4fxsYzl/b/9GF6KZY1ON0.kymQualmrWrUFu0ArSpz/W6SSQR7vR1y', 'admin');

-- Sample Course
INSERT INTO courses (name, code, description) VALUES 
('Computer Science & Engineering', 'CSE', 'Bachelor of Technology in CSE');

-- Sample Subject
INSERT INTO subjects (name, code, description) VALUES 
('Software Engineering', 'CS302', 'Core concepts of SDLC and Design Patterns');

-- Feedback Questions
INSERT INTO feedback_questions (question_text, question_type) VALUES 
('Teacher is punctual to the class', 'rating'),
('Teacher covers the syllabus on time', 'rating'),
('Teacher makes the subject interesting', 'rating'),
('Teacher is available for guidance', 'rating'),
('Any other comments?', 'text');

-- 10. Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

