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

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $registration_id = $_POST['registration_id'];
    $new_status = $_POST['status'];
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
    $result = db_query("UPDATE registrations SET status = ?, admin_notes = ?, approved_by = ?, approved_at = NOW() WHERE id = ?", 
                      [$new_status, $admin_notes, $_SESSION['admin_id'], $registration_id]);
    
    if ($result) {
        $message = 'Registration status updated successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to update status.';
        $message_type = 'danger';
    }
}

// Get statistics
$stats = [];
$stats['total_students'] = db_fetch(db_query("SELECT COUNT(*) as count FROM students"))['count'];
$stats['total_courses'] = db_fetch(db_query("SELECT COUNT(*) as count FROM courses"))['count'];
$stats['total_registrations'] = db_fetch(db_query("SELECT COUNT(*) as count FROM registrations"))['count'];
$stats['pending_registrations'] = db_fetch(db_query("SELECT COUNT(*) as count FROM registrations WHERE status = 'Pending'"))['count'];
$stats['approved_registrations'] = db_fetch(db_query("SELECT COUNT(*) as count FROM registrations WHERE status = 'Approved'"))['count'];
$stats['rejected_registrations'] = db_fetch(db_query("SELECT COUNT(*) as count FROM registrations WHERE status = 'Rejected'"))['count'];

// Get all registrations with complete student and course details
$registrations_query = "SELECT r.id, r.status, r.motivation, r.previous_experience, r.expected_outcome, 
                               r.admin_notes, r.registered_at, r.approved_at,
                               s.id as student_id, s.name as student_name, s.roll_no, s.email, s.phone, 
                               s.department, s.year_of_study, s.gender, s.date_of_birth, s.address, s.created_at as student_joined,
                               c.id as course_id, c.name as course_name, c.instructor, c.duration, c.description, c.seats,
                               a.username as approved_by_admin
                        FROM registrations r
                        JOIN students s ON r.student_id = s.id
                        JOIN courses c ON r.course_id = c.id
                        LEFT JOIN admin a ON r.approved_by = a.id
                        ORDER BY r.registered_at DESC";

$registrations_result = db_query($registrations_query);

// Get recent students
$recent_students_result = db_query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");

// Get course enrollment stats
$course_stats_result = db_query("SELECT c.name, c.seats, COUNT(r.id) as enrolled, 
                                        SUM(CASE WHEN r.status = 'Approved' THEN 1 ELSE 0 END) as approved_count
                                 FROM courses c 
                                 LEFT JOIN registrations r ON c.id = r.course_id 
                                 GROUP BY c.id, c.name, c.seats 
                                 ORDER BY enrolled DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VAC Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.danger { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        
        .table-container {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .student-details {
            font-size: 0.9em;
            color: #666;
        }
        
        .course-details {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> VAC Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-shield"></i> Welcome, <?php echo $_SESSION['admin_username']; ?>!
                </span>
                <a class="nav-link" href="manage_courses.php">
                    <i class="fas fa-book"></i> Manage Courses
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?php echo $stats['total_students']; ?></h3>
                            <p class="mb-0">Total Students</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card info">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?php echo $stats['total_courses']; ?></h3>
                            <p class="mb-0">Total Courses</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?php echo $stats['pending_registrations']; ?></h3>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?php echo $stats['approved_registrations']; ?></h3>
                            <p class="mb-0">Approved</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card danger">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?php echo $stats['rejected_registrations']; ?></h3>
                            <p class="mb-0">Rejected</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3><?php echo $stats['total_registrations']; ?></h3>
                            <p class="mb-0">Total Applications</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Registrations Table -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> All Course Registrations
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Student Details</th>
                                        <th>Course</th>
                                        <th>Application Info</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = db_fetch($registrations_result)): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                                                <div class="student-details">
                                                    <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($row['roll_no']); ?><br>
                                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?><br>
                                                    <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($row['department']); ?>
                                                    <?php if ($row['year_of_study']): ?>
                                                        - <?php echo htmlspecialchars($row['year_of_study']); ?>
                                                    <?php endif; ?><br>
                                                    <?php if ($row['phone']): ?>
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?><br>
                                                    <?php endif; ?>
                                                    <i class="fas fa-calendar"></i> Joined: <?php echo date('M d, Y', strtotime($row['student_joined'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['course_name']); ?></strong><br>
                                                <div class="course-details">
                                                    <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($row['instructor']); ?><br>
                                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($row['duration']); ?><br>
                                                    <i class="fas fa-users"></i> <?php echo $row['seats']; ?> seats
                                                </div>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>Applied:</strong> <?php echo date('M d, Y H:i', strtotime($row['registered_at'])); ?><br>
                                                    <?php if ($row['motivation']): ?>
                                                        <strong>Motivation:</strong> <?php echo htmlspecialchars(substr($row['motivation'], 0, 100)) . '...'; ?><br>
                                                    <?php endif; ?>
                                                    <?php if ($row['approved_at']): ?>
                                                        <strong>Processed:</strong> <?php echo date('M d, Y', strtotime($row['approved_at'])); ?><br>
                                                        <strong>By:</strong> <?php echo htmlspecialchars($row['approved_by_admin'] ?: 'System'); ?>
                                                    <?php endif; ?>
                                                </small>
                                                <?php if ($row['motivation'] || $row['previous_experience'] || $row['expected_outcome']): ?>
                                                    <br><button class="btn btn-sm btn-outline-info" onclick="showApplicationDetails(<?php echo $row['id']; ?>)">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $row['status'];
                                                $badge_class = '';
                                                switch ($status) {
                                                    case 'Pending': $badge_class = 'bg-warning text-dark'; break;
                                                    case 'Approved': $badge_class = 'bg-success'; break;
                                                    case 'Rejected': $badge_class = 'bg-danger'; break;
                                                    case 'Waitlisted': $badge_class = 'bg-info'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                                <?php if ($row['admin_notes']): ?>
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars(substr($row['admin_notes'], 0, 50)) . '...'; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] == 'Pending'): ?>
                                                    <button class="btn btn-sm btn-success mb-1" onclick="updateStatus(<?php echo $row['id']; ?>, 'Approved')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger mb-1" onclick="updateStatus(<?php echo $row['id']; ?>, 'Rejected')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                    <button class="btn btn-sm btn-info mb-1" onclick="updateStatus(<?php echo $row['id']; ?>, 'Waitlisted')">
                                                        <i class="fas fa-list"></i> Waitlist
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="updateStatus(<?php echo $row['id']; ?>, 'Pending')">
                                                        <i class="fas fa-undo"></i> Reset
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar with additional info -->
            <div class="col-md-4">
                <!-- Recent Students -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-user-plus"></i> Recent Students</h6>
                    </div>
                    <div class="card-body">
                        <?php while ($student = db_fetch($recent_students_result)): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <strong><?php echo htmlspecialchars($student['name']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($student['department']); ?><br>
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </small>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('M d', strtotime($student['created_at'])); ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Course Enrollment Stats -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Course Enrollment</h6>
                    </div>
                    <div class="card-body">
                        <?php while ($course_stat = db_fetch($course_stats_result)): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($course_stat['name']); ?></strong>
                                    <span class="badge bg-primary"><?php echo $course_stat['enrolled']; ?></span>
                                </div>
                                <div class="progress mt-1" style="height: 8px;">
                                    <?php 
                                    $percentage = $course_stat['seats'] > 0 ? ($course_stat['approved_count'] / $course_stat['seats']) * 100 : 0;
                                    $percentage = min($percentage, 100);
                                    ?>
                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <small class="text-muted">
                                    <?php echo $course_stat['approved_count']; ?> approved / <?php echo $course_stat['seats']; ?> seats
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Registration Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="registration_id" id="modal_registration_id">
                        <input type="hidden" name="status" id="modal_status">
                        
                        <div class="mb-3">
                            <label class="form-label">New Status:</label>
                            <span id="modal_status_display" class="badge fs-6"></span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes (Optional)</label>
                            <textarea class="form-control" name="admin_notes" id="admin_notes" rows="3" 
                                      placeholder="Add any notes about this decision..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Application Details Modal -->
    <div class="modal fade" id="applicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="applicationModalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store registration data for modals
        const registrations = <?php 
            // Reset the result pointer and create JSON data
            $registrations_result = db_query($registrations_query);
            $data = [];
            while ($row = db_fetch($registrations_result)) {
                $data[] = $row;
            }
            echo json_encode($data);
        ?>;

        function updateStatus(registrationId, status) {
            document.getElementById('modal_registration_id').value = registrationId;
            document.getElementById('modal_status').value = status;
            
            const statusDisplay = document.getElementById('modal_status_display');
            statusDisplay.textContent = status;
            statusDisplay.className = 'badge fs-6 ';
            
            switch(status) {
                case 'Approved': statusDisplay.classList.add('bg-success'); break;
                case 'Rejected': statusDisplay.classList.add('bg-danger'); break;
                case 'Waitlisted': statusDisplay.classList.add('bg-info'); break;
                case 'Pending': statusDisplay.classList.add('bg-warning', 'text-dark'); break;
            }
            
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function showApplicationDetails(registrationId) {
            const registration = registrations.find(r => r.id == registrationId);
            if (!registration) return;

            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Student Information</h6>
                        <p><strong>Name:</strong> ${registration.student_name}</p>
                        <p><strong>Roll No:</strong> ${registration.roll_no}</p>
                        <p><strong>Email:</strong> ${registration.email}</p>
                        <p><strong>Phone:</strong> ${registration.phone || 'N/A'}</p>
                        <p><strong>Department:</strong> ${registration.department}</p>
                        <p><strong>Year:</strong> ${registration.year_of_study || 'N/A'}</p>
                        <p><strong>Gender:</strong> ${registration.gender || 'N/A'}</p>
                        <p><strong>Address:</strong> ${registration.address || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Course Information</h6>
                        <p><strong>Course:</strong> ${registration.course_name}</p>
                        <p><strong>Instructor:</strong> ${registration.instructor}</p>
                        <p><strong>Duration:</strong> ${registration.duration}</p>
                        <p><strong>Applied:</strong> ${new Date(registration.registered_at).toLocaleDateString()}</p>
                        <p><strong>Status:</strong> <span class="badge bg-primary">${registration.status}</span></p>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Motivation</h6>
                    <p class="text-muted">${registration.motivation || 'No motivation provided'}</p>
                </div>
                <div class="mb-3">
                    <h6>Previous Experience</h6>
                    <p class="text-muted">${registration.previous_experience || 'No previous experience mentioned'}</p>
                </div>
                <div class="mb-3">
                    <h6>Expected Learning Outcomes</h6>
                    <p class="text-muted">${registration.expected_outcome || 'No expected outcomes mentioned'}</p>
                </div>
                ${registration.admin_notes ? `
                <div class="mb-3">
                    <h6>Admin Notes</h6>
                    <p class="text-muted">${registration.admin_notes}</p>
                </div>
                ` : ''}
            `;

            document.getElementById('applicationModalContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('applicationModal')).show();
        }
    </script>
</body>
</html>