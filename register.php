<?php
include 'db.php';

$message = "";
$email_error = "";
$sksu_id_error = "";

if (isset($_POST['register'])) {

    $sksu_id  = trim($_POST['sksu_id']);
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // Initialize validation flags
    $is_valid = true;

    // SKSU email validation
    if (!preg_match("/@sksu\.edu\.ph$/", $email)) {
        $message = "❌ Only SKSU email accounts are allowed.";
        $is_valid = false;
    } else {
        // Check for duplicate SKSU ID
        $check_id_sql = "SELECT id FROM users WHERE sksu_id = ?";
        $stmt_id = $conn->prepare($check_id_sql);
        $stmt_id->bind_param("s", $sksu_id);
        $stmt_id->execute();
        $stmt_id->store_result();
        
        if ($stmt_id->num_rows > 0) {
            $sksu_id_error = "SKSU ID already registered";
            $is_valid = false;
        }
        $stmt_id->close();

        // Check for duplicate email
        $check_email_sql = "SELECT id FROM users WHERE email = ?";
        $stmt_email = $conn->prepare($check_email_sql);
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();
        
        if ($stmt_email->num_rows > 0) {
            $email_error = "Email already registered";
            $is_valid = false;
        }
        $stmt_email->close();

        // If all validations pass
        if ($is_valid) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (sksu_id, fullname, email, password, role)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $sksu_id, $fullname, $email, $hashed, $role);

            if ($stmt->execute()) {
                $message = "✅ Registration successful!";
                // Clear form data
                $_POST = array();
            } else {
                $message = "❌ Registration failed. Please try again.";
            }
            $stmt->close();
        } else {
            $message = "❌ Please fix the errors below.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKSU Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            margin: 2rem auto;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .logo-icon svg {
            color: white;
            font-size: 2rem;
        }
        
        .page-title {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .page-subtitle {
            color: #7f8c8d;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .is-invalid:focus {
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon .form-control {
            padding-left: 2.5rem;
        }
        
        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            z-index: 10;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-register:disabled {
            background: linear-gradient(135deg, #cccccc 0%, #999999 100%);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #7f8c8d;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .login-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .message-box {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }
        
        .message-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border: 1px solid rgba(46, 204, 113, 0.2);
        }
        
        .message-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        
        .role-icon {
            margin-right: 0.5rem;
        }
        
        .form-text {
            font-size: 0.875rem;
        }
        
        @media (max-width: 576px) {
            .register-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m1-8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                        <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                    </svg>
                </div>
                <h1 class="page-title">SKSU Registration</h1>
                <p class="page-subtitle">Create your account to access the system</p>
            </div>

            <!-- Message Display -->
            <?php if (!empty($message)): ?>
                <div class="message-box <?= strpos($message, '✅') !== false ? 'message-success' : 'message-error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" id="registrationForm">
                <div class="mb-3">
                    <label class="form-label">SKSU ID</label>
                    <div class="input-icon">
                        <i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                            </svg>
                        </i>
                        <input type="text" 
                               class="form-control <?= !empty($sksu_id_error) ? 'is-invalid' : '' ?>" 
                               name="sksu_id" 
                               placeholder="Enter your SKSU ID" 
                               value="<?= isset($_POST['sksu_id']) ? htmlspecialchars($_POST['sksu_id']) : '' ?>" 
                               required>
                    </div>
                    <?php if (!empty($sksu_id_error)): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($sksu_id_error) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <div class="input-icon">
                        <i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                            </svg>
                        </i>
                        <input type="text" 
                               class="form-control" 
                               name="fullname" 
                               placeholder="Enter your full name" 
                               value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" 
                               required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">SKSU Email</label>
                    <div class="input-icon">
                        <i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                            </svg>
                        </i>
                        <input type="email" 
                               class="form-control <?= !empty($email_error) ? 'is-invalid' : '' ?>" 
                               name="email" 
                               placeholder="username@sksu.edu.ph" 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                               required>
                    </div>
                    <?php if (!empty($email_error)): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($email_error) ?></div>
                    <?php endif; ?>
                    <small class="text-muted">Must end with @sksu.edu.ph</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-icon">
                        <i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                            </svg>
                        </i>
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               placeholder="Enter your password" 
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role" required>
                        <option value="" <?= !isset($_POST['role']) ? 'selected disabled' : '' ?>>-- Select Role --</option>
                        <option value="student" <?= isset($_POST['role']) && $_POST['role'] == 'student' ? 'selected' : '' ?>>
                            Student
                        </option>
                        <option value="osas" <?= isset($_POST['role']) && $_POST['role'] == 'osas' ? 'selected' : '' ?>>
                            OSAS
                        </option>
                        <option value="admin" <?= isset($_POST['role']) && $_POST['role'] == 'admin' ? 'selected' : '' ?>>
                            Admin
                        </option>
                    </select>
                </div>

                <button type="submit" 
                        name="register" 
                        class="btn btn-register" 
                        id="submitBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    </svg>
                    <span id="submitText">Register Account</span>
                    <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            // Real-time duplicate checking (optional enhancement)
            const emailInput = document.querySelector('input[name="email"]');
            const sksuIdInput = document.querySelector('input[name="sksu_id"]');
            
            // Clear error messages when user starts typing
            emailInput.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const errorDiv = this.parentElement.parentElement.querySelector('.invalid-feedback');
                    if (errorDiv) errorDiv.remove();
                }
            });
            
            sksuIdInput.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const errorDiv = this.parentElement.parentElement.querySelector('.invalid-feedback');
                    if (errorDiv) errorDiv.remove();
                }
            });
            
            // Form submission handler
            form.addEventListener('submit', function(e) {
                // Show loading state
                submitBtn.disabled = true;
                submitText.textContent = 'Registering...';
                loadingSpinner.classList.remove('d-none');
                
                // Form will submit normally
            });
            
            // Prevent multiple form submissions
            let isSubmitting = false;
            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                return true;
            });
        });
    </script>
</body>
</html>