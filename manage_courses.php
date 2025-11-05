<?php
session_start();
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle course operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_course'])) {
        $name = trim($_POST['name']);
        $instructor = trim($_POST['instructor']);
        $duration = trim($_POST['duration']);
        $description = trim($_POST['description']);
        $seats = intval($_POST['seats']);
        
        if (!empty($name) && !empty($instructor) && !empty($duration) && $seats > 0) {
            $result = db_query("INSERT INTO courses (name, instructor, duration, description, seats) VALUES (?, ?, ?, ?, ?)", 
                              [$name, $instructor, $duration, $description, $seats]);
            
            if ($result) {
                $message = 'Course added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to add course.';
                $message_type = 'danger';
            }
        } else {
            $message = 'Please fill in all required fields.';
            $message_type = 'danger';
        }
    } elseif (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];
        
        // Check if course has registrations
        $check_result = db_query("SELECT COUNT(*) as count FROM registrations WHERE course_id = ?", [$course_id]);
        $count = db_fetch($check_result)['count'];
        
        if ($count > 0) {
            $message = 'Cannot delete course with existing registrations.';
            $message_type = 'warning';
        } else {
            $result = db_query("DELETE FROM courses WHERE id = ?", [$course_id]);
            
            if ($result) {
                $message = 'Course deleted successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to delete course.';
                $message_type = 'danger';
            }
        }
    }
}

// Get all courses
$courses_result = db_query("SELECT * FROM courses ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - VAC Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">VAC Admin Panel</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['admin_username']; ?>!</span>
                <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Courses</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add Course Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Add New Course</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Course Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="instructor" class="form-label">Instructor</label>
                            <input type="text" class="form-control" id="instructor" name="instructor" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duration</label>
                            <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 8 weeks" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="seats" class="form-label">Available Seats</label>
                            <input type="number" class="form-control" id="seats" name="seats" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                </form>
            </div>
        </div>

        <!-- Existing Courses -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">Existing Courses</h4>
            </div>
            <div class="card-body">
                <?php if (db_num_rows($courses_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Course Name</th>
                                    <th>Instructor</th>
                                    <th>Duration</th>
                                    <th>Seats</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($course = db_fetch($courses_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                        <td><?php echo htmlspecialchars($course['duration']); ?></td>
                                        <td><?php echo $course['seats']; ?></td>
                                        <td><?php echo htmlspecialchars(substr($course['description'], 0, 50)) . '...'; ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" name="delete_course" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No courses available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>