-- Create database
CREATE DATABASE IF NOT EXISTS vac_course;
USE vac_course;

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    roll_no VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    department VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Admin table
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    instructor VARCHAR(100) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    description TEXT,
    seats INT DEFAULT 50
);

-- Registrations table
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    course_id INT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Insert default admin
INSERT INTO admin (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample courses
INSERT INTO courses (name, instructor, duration, description, seats) VALUES
('Web Development Fundamentals', 'Dr. Smith', '8 weeks', 'Learn HTML, CSS, JavaScript basics', 30),
('Data Science with Python', 'Prof. Johnson', '12 weeks', 'Introduction to data analysis and machine learning', 25),
('Digital Marketing', 'Ms. Brown', '6 weeks', 'Social media marketing and SEO strategies', 40),
('Mobile App Development', 'Dr. Wilson', '10 weeks', 'Build Android and iOS applications', 20),
('Cybersecurity Basics', 'Prof. Davis', '8 weeks', 'Network security and ethical hacking', 35);