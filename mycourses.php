<?php
session_start();
require_once 'db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student's registered courses
$query = "SELECT c.name, c.instructor, r.status, r.registered_at 
          FROM registrations r 
          JOIN courses c ON r.course_id = c.id 
          WHERE r.student_id = ? 
          ORDER BY r.registered_at DESC";

$result = db_query($query, [$student_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - VAC Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">VAC Course Registration</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['student_name']; ?>!</span>
                <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>My Course Registrations</h2>
        
        <?php if (db_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Course Name</th>
                            <th>Instructor</th>
                            <th>Status</th>
                            <th>Registered Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = db_fetch($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['instructor']); ?></td>
                                <td>
                                    <?php
                                    $status = $row['status'];
                                    $badge_class = '';
                                    switch ($status) {
                                        case 'Pending':
                                            $badge_class = 'bg-warning';
                                            break;
                                        case 'Approved':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'Rejected':
                                            $badge_class = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['registered_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h4>No Course Registrations</h4>
                <p>You haven't registered for any courses yet. <a href="student_dashboard.php">Browse available courses</a> to get started.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>