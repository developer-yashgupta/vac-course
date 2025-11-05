<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $roll_no = trim($_POST['roll_no']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $year_of_study = trim($_POST['year_of_study']);
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($roll_no) || empty($email) || empty($department) || empty($password)) {
        $error = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = 'Phone number must be 10-15 digits.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $error = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
    } else {
        // Check if email or roll number already exists
        $result = db_query("SELECT id FROM students WHERE email = ? OR roll_no = ?", [$email, $roll_no]);
        
        if (db_num_rows($result) > 0) {
            $error = 'Email or Roll Number already exists.';
        } else {
            // Hash password and insert student
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(32));
            
            $result = db_query("INSERT INTO students (name, roll_no, email, phone, department, year_of_study, gender, date_of_birth, address, password, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                              [$name, $roll_no, $email, $phone, $department, $year_of_study, $gender, $date_of_birth, $address, $hashed_password, $verification_token]);
            
            if ($result) {
                $success = 'Registration successful! You can now login with your credentials.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - VAC Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .registration-container {
            padding: 40px 0;
        }

        .registration-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
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

        .card-header {
            background: var(--primary-gradient) !important;
            border: none;
            padding: 30px;
            text-align: center;
        }

        .card-header h3 {
            color: white;
            font-weight: 600;
            margin: 0;
        }

        .form-section {
            background: rgba(248, 249, 250, 0.8);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            background: rgba(248, 249, 250, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }

        .required {
            color: #e74c3c;
            font-weight: 600;
        }

        .password-strength {
            height: 6px;
            margin-top: 8px;
            border-radius: 3px;
            transition: all 0.3s ease;
            background: #e9ecef;
        }

        .strength-bar {
            height: 100%;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .strength-weak .strength-bar { 
            background: var(--danger-gradient);
            width: 33%;
        }
        .strength-medium .strength-bar { 
            background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
            width: 66%;
        }
        .strength-strong .strength-bar { 
            background: var(--success-gradient);
            width: 100%;
        }

        .btn-register {
            background: var(--primary-gradient);
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            color: white;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .alert {
            border: none;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            border-left: 4px solid #11998e;
            color: #0d7377;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1) 0%, rgba(238, 90, 36, 0.1) 100%);
            border-left: 4px solid #ff6b6b;
            color: #c0392b;
        }

        @media (max-width: 768px) {
            .registration-container {
                padding: 20px 0;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .card-header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-graduation-cap me-2"></i>VAC Course Registration
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <a class="nav-link" href="index.html">
                    <i class="fas fa-home me-1"></i>Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container registration-container" style="margin-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="registration-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus me-2"></i>Student Registration</h3>
                        <small>Fields marked with <span class="required">*</span> are required</small>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-sm btn-success">
                                        <i class="fas fa-sign-in-alt me-1"></i>Login Now
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="registrationForm">
                            <!-- Personal Information -->
                            <div class="form-section">
                                <h5 class="section-title">
                                    <i class="fas fa-user"></i>Personal Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="roll_no" class="form-label">Roll Number <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="roll_no" name="roll_no" required
                                               value="<?php echo isset($_POST['roll_no']) ? htmlspecialchars($_POST['roll_no']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="10-15 digits"
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select" id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                               value="<?php echo isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : ''; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="year_of_study" class="form-label">Year of Study</label>
                                        <select class="form-select" id="year_of_study" name="year_of_study">
                                            <option value="">Select Year</option>
                                            <option value="1st Year" <?php echo (isset($_POST['year_of_study']) && $_POST['year_of_study'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                            <option value="2nd Year" <?php echo (isset($_POST['year_of_study']) && $_POST['year_of_study'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                            <option value="3rd Year" <?php echo (isset($_POST['year_of_study']) && $_POST['year_of_study'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                            <option value="4th Year" <?php echo (isset($_POST['year_of_study']) && $_POST['year_of_study'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                                            <option value="Postgraduate" <?php echo (isset($_POST['year_of_study']) && $_POST['year_of_study'] == 'Postgraduate') ? 'selected' : ''; ?>>Postgraduate</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter your full address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="form-section">
                                <h5 class="section-title">
                                    <i class="fas fa-graduation-cap"></i>Academic Information
                                </h5>
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department <span class="required">*</span></label>
                                    <select class="form-select" id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="Computer Science" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                        <option value="Information Technology" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                                        <option value="Electronics & Communication" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Electronics & Communication') ? 'selected' : ''; ?>>Electronics & Communication</option>
                                        <option value="Electrical Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Electrical Engineering') ? 'selected' : ''; ?>>Electrical Engineering</option>
                                        <option value="Mechanical Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="Civil Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="Chemical Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Chemical Engineering') ? 'selected' : ''; ?>>Chemical Engineering</option>
                                        <option value="Biotechnology" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Biotechnology') ? 'selected' : ''; ?>>Biotechnology</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Security Information -->
                            <div class="form-section">
                                <h5 class="section-title">
                                    <i class="fas fa-lock"></i>Security Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="password-strength" id="passwordStrength">
                                            <div class="strength-bar"></div>
                                        </div>
                                        <small class="text-muted">Must contain at least 8 characters with uppercase, lowercase, and number</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <div id="passwordMatch" class="mt-1"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-register">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="text-decoration-none fw-bold" style="color: #667eea;">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login here
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthContainer = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthContainer.className = 'password-strength';
            if (strength < 3) {
                strengthContainer.classList.add('strength-weak');
            } else if (strength < 4) {
                strengthContainer.classList.add('strength-medium');
            } else {
                strengthContainer.classList.add('strength-strong');
            }
        });

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
            } else if (password === confirmPassword) {
                matchDiv.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>Passwords match</small>';
            } else {
                matchDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>Passwords do not match</small>';
            }
        });

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
        });

        // Add smooth animations to form sections
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.querySelectorAll('.form-section').forEach((section) => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'all 0.6s ease';
            observer.observe(section);
        });
    </script>
</body>
</html>