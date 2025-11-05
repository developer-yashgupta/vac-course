<?php
session_start();
require_once 'db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['course_id'])) {
    header('Location: student_dashboard.php');
    exit();
}

$course_id = $_GET['course_id'];
$student_id = $_SESSION['student_id'];

// Get course details
$course_result = db_query("SELECT * FROM courses WHERE id = ?", [$course_id]);
$course = db_fetch($course_result);

if (!$course) {
    header('Location: student_dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $motivation = trim($_POST['motivation']);
    $previous_experience = trim($_POST['previous_experience']);
    $expected_outcome = trim($_POST['expected_outcome']);
    
    // Check if already registered
    $check_result = db_query("SELECT id FROM registrations WHERE student_id = ? AND course_id = ?", [$student_id, $course_id]);
    
    if (db_num_rows($check_result) > 0) {
        $error = 'You are already registered for this course.';
    } else {
        // Register for course with detailed information
        $result = db_query("INSERT INTO registrations (student_id, course_id, motivation, previous_experience, expected_outcome) VALUES (?, ?, ?, ?, ?)", 
                          [$student_id, $course_id, $motivation, $previous_experience, $expected_outcome]);
        
        if ($result) {
            $success = 'Successfully registered for the course! Your application is pending approval.';
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration - <?php echo htmlspecialchars($course['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">VAC Course Registration</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['student_name']; ?>!</span>
                <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                <a class="nav-link" href="mycourses.php">My Courses</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Course Details</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h6>
                        <p><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor']); ?></p>
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
                        <p><strong>Available Seats:</strong> <?php echo $course['seats']; ?></p>
                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Course Registration Application</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="student_dashboard.php" class="btn btn-primary btn-sm">Back to Dashboard</a>
                                    <a href="mycourses.php" class="btn btn-outline-primary btn-sm">View My Courses</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="motivation" class="form-label">Why do you want to take this course? <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="motivation" name="motivation" rows="4" required 
                                              placeholder="Explain your motivation and interest in this course..."></textarea>
                                    <div class="form-text">Minimum 50 characters required</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="previous_experience" class="form-label">Previous Experience</label>
                                    <textarea class="form-control" id="previous_experience" name="previous_experience" rows="3"
                                              placeholder="Describe any relevant previous experience, courses, or projects..."></textarea>
                                    <div class="form-text">Optional: This helps us understand your background</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="expected_outcome" class="form-label">Expected Learning Outcomes</label>
                                    <textarea class="form-control" id="expected_outcome" name="expected_outcome" rows="3"
                                              placeholder="What do you hope to achieve or learn from this course?"></textarea>
                                    <div class="form-text">Optional: Your learning goals and expectations</div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6>Registration Process:</h6>
                                    <ul class="mb-0">
                                        <li>Submit your application with motivation</li>
                                        <li>Admin will review your application</li>
                                        <li>You'll be notified of approval/rejection</li>
                                        <li>Check "My Courses" for status updates</li>
                                    </ul>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="student_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success">Submit Application</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character count for motivation
        document.getElementById('motivation').addEventListener('input', function() {
            const text = this.value;
            const minLength = 50;
            const currentLength = text.length;
            
            if (currentLength < minLength) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const motivation = document.getElementById('motivation').value;
            if (motivation.length < 50) {
                e.preventDefault();
                alert('Please provide at least 50 characters for your motivation.');
                return false;
            }
        });
    </script>
</body>
</html>