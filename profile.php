<?php
session_start();
require_once 'db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $year_of_study = trim($_POST['year_of_study']);
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($name) || empty($department)) {
        $message = 'Name and Department are required fields.';
        $message_type = 'danger';
    } elseif (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $message = 'Phone number must be 10-15 digits.';
        $message_type = 'danger';
    } else {
        $result = db_query("UPDATE students SET name = ?, phone = ?, department = ?, year_of_study = ?, gender = ?, date_of_birth = ?, address = ? WHERE id = ?", 
                          [$name, $phone, $department, $year_of_study, $gender, $date_of_birth, $address, $student_id]);
        
        if ($result) {
            $_SESSION['student_name'] = $name; // Update session
            $message = 'Profile updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to update profile. Please try again.';
            $message_type = 'danger';
        }
    }
}

// Get student details
$student_result = db_query("SELECT * FROM students WHERE id = ?", [$student_id]);
$student = db_fetch($student_result);

// Get registration statistics
$stats_result = db_query("SELECT 
    COUNT(*) as total_registrations,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM registrations WHERE student_id = ?", [$student_id]);
$stats = db_fetch($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - VAC Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">VAC Course Registration</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['student_name']; ?>!</span>
                <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                <a class="nav-link" href="mycourses.php">My Courses</a>
                <a class="nav-link active" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Profile Statistics -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Registration Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border-end">
                                    <h4 class="text-primary"><?php echo $stats['total_registrations']; ?></h4>
                                    <small class="text-muted">Total Applications</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <h4 class="text-success"><?php echo $stats['approved']; ?></h4>
                                <small class="text-muted">Approved</small>
                            </div>
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-warning"><?php echo $stats['pending']; ?></h4>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-danger"><?php echo $stats['rejected']; ?></h4>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Account Information</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Roll Number:</strong> <?php echo htmlspecialchars($student['roll_no']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                        <p><strong>Member Since:</strong> <?php echo date('M Y', strtotime($student['created_at'])); ?></p>
                        <p class="mb-0"><strong>Email Status:</strong> 
                            <?php if ($student['email_verified']): ?>
                                <span class="badge bg-success">Verified</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Not Verified</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-user-edit"></i> Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($student['name']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($student['phone']); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-control" id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="Computer Science" <?php echo ($student['department'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                        <option value="Information Technology" <?php echo ($student['department'] == 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                                        <option value="Electronics & Communication" <?php echo ($student['department'] == 'Electronics & Communication') ? 'selected' : ''; ?>>Electronics & Communication</option>
                                        <option value="Electrical Engineering" <?php echo ($student['department'] == 'Electrical Engineering') ? 'selected' : ''; ?>>Electrical Engineering</option>
                                        <option value="Mechanical Engineering" <?php echo ($student['department'] == 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="Civil Engineering" <?php echo ($student['department'] == 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="Chemical Engineering" <?php echo ($student['department'] == 'Chemical Engineering') ? 'selected' : ''; ?>>Chemical Engineering</option>
                                        <option value="Biotechnology" <?php echo ($student['department'] == 'Biotechnology') ? 'selected' : ''; ?>>Biotechnology</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="year_of_study" class="form-label">Year of Study</label>
                                    <select class="form-control" id="year_of_study" name="year_of_study">
                                        <option value="">Select Year</option>
                                        <option value="1st Year" <?php echo ($student['year_of_study'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                        <option value="2nd Year" <?php echo ($student['year_of_study'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                        <option value="3rd Year" <?php echo ($student['year_of_study'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                        <option value="4th Year" <?php echo ($student['year_of_study'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                                        <option value="Postgraduate" <?php echo ($student['year_of_study'] == 'Postgraduate') ? 'selected' : ''; ?>>Postgraduate</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo ($student['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($student['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($student['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                           value="<?php echo $student['date_of_birth']; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>