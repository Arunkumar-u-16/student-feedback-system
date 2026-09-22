<?php
// login.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Login";
$noSidebar = true;

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $selected_role = $_POST['role'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf($csrf_token)) {
        die("CSRF token validation failed.");
    }

    if (empty($email) || empty($password) || empty($selected_role)) {
        set_flash_message('danger', 'Please fill in all fields.');
    }
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $selected_role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'student') {
                $s_stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
                $s_stmt->execute([$user['id']]);
                $_SESSION['student_id'] = $s_stmt->fetchColumn();
            }
            elseif ($user['role'] === 'faculty') {
                $f_stmt = $pdo->prepare("SELECT id FROM faculty WHERE user_id = ?");
                $f_stmt->execute([$user['id']]);
                $_SESSION['faculty_id'] = $f_stmt->fetchColumn();
            }

            redirect('index.php');
        }
        else {
            set_flash_message('danger', 'Invalid credentials or role mismatch.');
        }
    }
}

include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
        </div>
        <h2>Welcome Back</h2>
        <p class="text-center text-muted mb-4">Student Feedback System</p>
        
        <?php display_flash_message(); ?>

        <form action="login.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="role" class="form-label">Sign In As</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                    <select name="role" id="role" class="form-select" required>
                        <option value="" selected disabled>Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control" placeholder="admin@feedback.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Login to Dashboard</button>
            
            <div class="text-center mt-3">
                <p class="text-muted small">Don't have an account? <br> 
                    <a href="register.php" class="text-primary fw-bold text-decoration-none">Create a New Account</a>
                </p>
                
                <div class="mobile-access-trigger pt-3 border-top mt-3">
                    <p class="small text-muted mb-2">Want to use it on your phone?</p>
                    <a href="mobile_access.php" class="btn btn-light btn-sm rounded-pill">
                        <i class="fas fa-qrcode me-1"></i> View Mobile Access Link
                    </a>
                </div>
                
                <hr>
                <small class="text-muted d-block opacity-75">Admin Demo: admin@feedback.com / 123456</small>
            </div>
        </form>
    </div>
</div>

<?php

include 'includes/footer.php';
?>
