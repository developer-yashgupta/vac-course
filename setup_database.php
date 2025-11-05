<?php
$host = "localhost";
$username = "root";
$password = "kali";

try {
    // Create connection without database first
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL successfully!<br>";
    
    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS vac_course");
    echo "Database 'vac_course' created successfully<br>";
    
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=vac_course", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Execute SQL commands directly
    $sql_commands = [
        "CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            roll_no VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(15),
            department VARCHAR(100) NOT NULL,
            year_of_study VARCHAR(20),
            gender ENUM('Male', 'Female', 'Other'),
            date_of_birth DATE,
            address TEXT,
            password VARCHAR(255) NOT NULL,
            profile_image VARCHAR(255),
            email_verified BOOLEAN DEFAULT FALSE,
            verification_token VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            instructor VARCHAR(100) NOT NULL,
            duration VARCHAR(50) NOT NULL,
            description TEXT,
            seats INT DEFAULT 50
        )",
        
        "CREATE TABLE IF NOT EXISTS registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT,
            course_id INT,
            status ENUM('Pending', 'Approved', 'Rejected', 'Waitlisted') DEFAULT 'Pending',
            priority_level INT DEFAULT 1,
            motivation TEXT,
            previous_experience TEXT,
            expected_outcome TEXT,
            admin_notes TEXT,
            approved_by INT,
            approved_at DATETIME,
            registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id),
            FOREIGN KEY (course_id) REFERENCES courses(id),
            FOREIGN KEY (approved_by) REFERENCES admin(id)
        )"
    ];
    
    foreach ($sql_commands as $sql) {
        try {
            $conn->exec($sql);
            echo "Table created successfully<br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br>";
        }
    }
    
    // Insert default admin (password: admin123) - only if not exists
    $check_admin = $conn->prepare("SELECT COUNT(*) as count FROM admin WHERE username = 'admin'");
    $check_admin->execute();
    $admin_exists = $check_admin->fetch()['count'];
    
    if ($admin_exists == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $admin_password]);
        echo "Default admin created<br>";
    } else {
        echo "Admin user already exists<br>";
    }
    
    // Insert sample courses
    $courses = [
        ['Web Development Fundamentals', 'Dr. Smith', '8 weeks', 'Learn HTML, CSS, JavaScript basics', 30],
        ['Data Science with Python', 'Prof. Johnson', '12 weeks', 'Introduction to data analysis and machine learning', 25],
        ['Digital Marketing', 'Ms. Brown', '6 weeks', 'Social media marketing and SEO strategies', 40],
        ['Mobile App Development', 'Dr. Wilson', '10 weeks', 'Build Android and iOS applications', 20],
        ['Cybersecurity Basics', 'Prof. Davis', '8 weeks', 'Network security and ethical hacking', 35]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO courses (name, instructor, duration, description, seats) VALUES (?, ?, ?, ?, ?)");
    foreach ($courses as $course) {
        $stmt->execute($course);
    }
    echo "Sample courses added<br>";
    
    echo "<br><strong>Database setup completed!</strong><br>";
    echo "<a href='index.html'>Go to Application</a>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>