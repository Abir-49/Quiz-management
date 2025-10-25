-- Quiz Management System Database Schema
-- Demonstrates: Primary Key, Foreign Key, Constraints, Alter

-- Create Database
CREATE DATABASE IF NOT EXISTS quiz_management;
USE quiz_management;

-- 1. Instructor Table
CREATE TABLE instructor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Student Table
CREATE TABLE student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Quiz Table
CREATE TABLE quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    t_id INT NOT NULL,
    q_name VARCHAR(200) NOT NULL,
    expire_date DATETIME NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    total_marks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (t_id) REFERENCES instructor(id) ON DELETE CASCADE
);

-- 4. Questions Table
CREATE TABLE questions (
    q_id INT NOT NULL,
    q_no INT NOT NULL,
    question TEXT NOT NULL,
    A VARCHAR(500) NOT NULL,
    B VARCHAR(500) NOT NULL,
    C VARCHAR(500) NOT NULL,
    D VARCHAR(500) NOT NULL,
    ans ENUM('A', 'B', 'C', 'D') NOT NULL,
    marks INT DEFAULT 1,
    PRIMARY KEY (q_id, q_no),
    FOREIGN KEY (q_id) REFERENCES quiz(id) ON DELETE CASCADE
);

-- 5. Class Table (Junction table for instructor-student relationship)
CREATE TABLE class (
    t_id INT NOT NULL,
    s_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    PRIMARY KEY (t_id, s_id),
    FOREIGN KEY (t_id) REFERENCES instructor(id) ON DELETE CASCADE,
    FOREIGN KEY (s_id) REFERENCES student(id) ON DELETE CASCADE
);

-- 6. Student Answer Table
CREATE TABLE stud_ans (
    s_id INT NOT NULL,
    q_id INT NOT NULL,
    q_no INT NOT NULL,
    ans ENUM('A', 'B', 'C', 'D') NULL,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (s_id, q_id, q_no),
    FOREIGN KEY (s_id) REFERENCES student(id) ON DELETE CASCADE,
    FOREIGN KEY (q_id, q_no) REFERENCES questions(q_id, q_no) ON DELETE CASCADE
);

-- 7. Result Table
CREATE TABLE result (
    q_id INT NOT NULL,
    s_id INT NOT NULL,
    marks INT DEFAULT 0,
    total_marks INT NOT NULL,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (q_id, s_id),
    FOREIGN KEY (q_id) REFERENCES quiz(id) ON DELETE CASCADE,
    FOREIGN KEY (s_id) REFERENCES student(id) ON DELETE CASCADE
);

-- ALTER TABLE demonstrations
ALTER TABLE instructor ADD COLUMN phone VARCHAR(15);
ALTER TABLE student ADD COLUMN department VARCHAR(50);
ALTER TABLE quiz ADD COLUMN description TEXT;

-- CREATE INDEX for performance
CREATE INDEX idx_quiz_expire ON quiz(expire_date);
CREATE INDEX idx_class_status ON class(status);
CREATE INDEX idx_result_marks ON result(marks);

-- VIEW Creation: Active Quizzes
CREATE VIEW active_quizzes AS
SELECT 
    q.id,
    q.q_name,
    q.expire_date,
    q.duration,
    q.total_marks,
    i.name AS instructor_name,
    i.email AS instructor_email,
    COUNT(DISTINCT ques.q_no) AS total_questions
FROM quiz q
INNER JOIN instructor i ON q.t_id = i.id
LEFT JOIN questions ques ON q.id = ques.q_id
WHERE q.expire_date > NOW()
GROUP BY q.id, q.q_name, q.expire_date, q.duration, q.total_marks, i.name, i.email;

-- VIEW: Student Performance Summary
CREATE VIEW student_performance AS
SELECT 
    s.id,
    s.name,
    s.roll,
    s.email,
    COUNT(DISTINCT r.q_id) AS quizzes_taken,
    AVG(r.percentage) AS avg_percentage,
    SUM(r.marks) AS total_marks_earned
FROM student s
LEFT JOIN result r ON s.id = r.s_id
GROUP BY s.id, s.name, s.roll, s.email;

-- VIEW: Instructor Quiz Statistics
CREATE VIEW instructor_quiz_stats AS
SELECT 
    i.id AS instructor_id,
    i.name AS instructor_name,
    COUNT(DISTINCT q.id) AS total_quizzes,
    COUNT(DISTINCT CASE WHEN q.expire_date > NOW() THEN q.id END) AS pending_quizzes,
    COUNT(DISTINCT CASE WHEN q.expire_date <= NOW() THEN q.id END) AS finished_quizzes,
    COUNT(DISTINCT c.s_id) AS total_students
FROM instructor i
LEFT JOIN quiz q ON i.id = q.t_id
LEFT JOIN class c ON i.id = c.t_id AND c.status = 'approved'
GROUP BY i.id, i.name;