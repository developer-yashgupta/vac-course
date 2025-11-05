<?php
session_start();
require_once 'db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

// Get all courses
$courses_result = db_query("SELECT * FROM courses ORDER BY name");

// Get student's registration count
$student_id = $_SESSION['student_id'];
$reg_count_result = db_query("SELECT COUNT(*) as count FROM registrations WHERE student_id = ?", [$student_id]);
$reg_count = db_fetch($reg_count_result)['count'];

// Get student's recent registrations
$recent_regs_result = db_query("SELECT c.name, r.status, r.registered_at FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.student_id = ? ORDER BY r.registered_at DESC LIMIT 3", [$student_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - VAC Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar-brand {
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-text {
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 20px;
            padding: 8px 16px !important;
            margin: 0 5px;
        }

        .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .dashboard-container {
            padding: 100px 0 50px;
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInDown 0.8s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
        }

        .stats-card:nth-child(1) .stats-icon {
            background: var(--primary-gradient);
        }

        .stats-card:nth-child(2) .stats-icon {
            background: var(--success-gradient);
        }

        .stats-card:nth-child(3) .stats-icon {
            background: var(--info-gradient);
        }

        .courses-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-title i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .course-card {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: 100%;
            position: relative;
        }

        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .course-card:hover::before {
            transform: scaleX(1);
        }

        .course-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: var(--primary-gradient) !important;
            border: none;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.1) 10px,
                rgba(255, 255, 255, 0.1) 20px
            );
            animation: shimmer 3s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%); }
            100% { transform: translateX(100%) translateY(100%); }
        }

        .card-header h5 {
            color: white;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .card-body {
            padding: 25px;
        }

        .course-info {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        .course-info i {
            color: #667eea;
            width: 20px;
        }

        .btn-apply {
            background: var(--success-gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .btn-apply::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-apply:hover::before {
            left: 100%;
        }

        .alert {
            border: none;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            border-left: 4px solid #11998e;
            color: #0d7377;
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%);
            border-left: 4px solid #f093fb;
            color: #d63384;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1) 0%, rgba(238, 90, 36, 0.1) 100%);
            border-left: 4px solid #ff6b6b;
            color: #c0392b;
        }

        .recent-activity {
            background: rgba(248, 249, 250, 0.8);
            border-radius: 15px;
            padding: 20px;
            margin-top: 30px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: white;
        }

        .status-pending .activity-icon {
            background: var(--warning-gradient);
        }

        .status-approved .activity-icon {
            background: var(--success-gradient);
        }

        .status-rejected .activity-icon {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        @media (max-width: 768px) {
            .welcome-title {
                font-size: 2rem;
            }
            
            .dashboard-container {
                padding: 80px 0 30px;
            }
            
            .welcome-section, .courses-section {
                padding: 25px;
                margin-bottom: 25px;
            }
        }

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 40px;
            height: 40px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>VAC Course Registration
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>Welcome, <?php echo $_SESSION['student_name']; ?>!
                </span>
                <a class="nav-link" href="mycourses.php">
                    <i class="fas fa-book me-1"></i>My Courses
                </a>
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user-edit me-1"></i>Profile
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="container">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="welcome-title">
                            <i class="fas fa-star me-3"></i>Discover Amazing Courses
                        </h1>
                        <p class="lead text-muted mb-0">
                            Enhance your skills with our carefully curated Value Added Courses. 
                            Apply now and take your career to the next level!
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <h4><?php echo db_num_rows($courses_result); ?></h4>
                                    <small class="text-muted">Available Courses</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <h4><?php echo $reg_count; ?></h4>
                                    <small class="text-muted">My Applications</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <h4>A+</h4>
                                    <small class="text-muted">Grade Goal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (db_num_rows($recent_regs_result) > 0): ?>
                    <div class="recent-activity">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-clock me-2"></i>Recent Activity
                        </h6>
                        <?php while ($activity = db_fetch($recent_regs_result)): ?>
                            <div class="activity-item status-<?php echo strtolower($activity['status']); ?>">
                                <div class="activity-icon">
                                    <?php
                                    switch($activity['status']) {
                                        case 'Pending': echo '<i class="fas fa-clock"></i>'; break;
                                        case 'Approved': echo '<i class="fas fa-check"></i>'; break;
                                        case 'Rejected': echo '<i class="fas fa-times"></i>'; break;
                                    }
                                    ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($activity['name']); ?></div>
                                    <small class="text-muted">
                                        Applied on <?php echo date('M d, Y', strtotime($activity['registered_at'])); ?> • 
                                        Status: <?php echo $activity['status']; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Courses Section -->
            <div class="courses-section">
                <h2 class="section-title">
                    <i class="fas fa-graduation-cap"></i>Available VAC Courses
                </h2>
                
                <div class="row g-4">
                    <?php 
                    // Reset the result pointer
                    $courses_result = db_query("SELECT * FROM courses ORDER BY name");
                    while ($course = db_fetch($courses_result)): 
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="course-card">
                                <div class="card-header">
                                    <h5><?php echo htmlspecialchars($course['name']); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="course-info">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?php echo htmlspecialchars($course['instructor']); ?></span>
                                    </div>
                                    <div class="course-info">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($course['duration']); ?></span>
                                    </div>
                                    <div class="course-info">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $course['seats']; ?> seats available</span>
                                    </div>
                                    <p class="text-muted mt-3 mb-4">
                                        <?php echo htmlspecialchars($course['description']); ?>
                                    </p>
                                    <a href="course_register.php?course_id=<?php echo $course['id']; ?>" 
                                       class="btn btn-apply">
                                        <i class="fas fa-paper-plane me-2"></i>Apply Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.querySelectorAll('.course-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });

        // Add hover effects to stats cards
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.05)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add click animation to apply buttons
        document.querySelectorAll('.btn-apply').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 40px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 2px 30px rgba(0, 0, 0, 0.1)';
            }
        });
    </script>

    <style>
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</body>
</html>