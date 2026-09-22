<?php
// register.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Create Account";
$noSidebar = true;

// Fetch Courses for students
$courses = $pdo->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf($csrf_token)) {
        die("CSRF token validation failed.");
    }

    try {
        $pdo->beginTransaction();

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Email already registered.");
        }

        // 1. Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        $user_id = $pdo->lastInsertId();

        // 2. Role-specific details
        if ($role === 'student') {
            $course_id = $_POST['course_id'] ?? null;
            $roll_no = $_POST['roll_no'] ?? '';
            $semester = $_POST['semester'] ?? 1;

            if (!$course_id || !$roll_no)
                throw new Exception("Course and Roll Number are required.");

            $stmt = $pdo->prepare("INSERT INTO students (user_id, course_id, roll_no, semester) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $course_id, $roll_no, $semester]);

        }
        elseif ($role === 'faculty') {
            $department = $_POST['department'] ?? '';
            $designation = $_POST['designation'] ?? '';

            if (!$department)
                throw new Exception("Department is required.");

            $stmt = $pdo->prepare("INSERT INTO faculty (user_id, department, designation) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $department, $designation]);
        }

        $pdo->commit();
        redirect('login.php', 'success', 'Registration successful! Please login.');

    }
    catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('danger', $e->getMessage());
    }
}

include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-card" style="max-width: 500px;">
        <div class="text-center mb-4">
            <i class="fas fa-user-plus fa-3x text-primary"></i>
        </div>
        <h2>Create Account</h2>
        <p class="text-center text-muted mb-4">Join the Student Feedback System</p>
        
        <?php display_flash_message(); ?>

        <form action="register.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Register As</label>
                <select name="role" id="register_role" class="form-select" required onchange="toggleFields()">
                    <option value="" selected disabled>Select Role</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                </select>
            </div>

            <!-- Student Specific Fields -->
            <div id="student_fields" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select">
                            <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Roll Number</label>
                        <input type="text" name="roll_no" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select">
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="3">3rd Semester</option>
                        <option value="4">4th Semester</option>
                        <option value="5">5th Semester</option>
                        <option value="6">6th Semester</option>
                        <option value="7">7th Semester</option>
                        <option value="8">8th Semester</option>
                    </select>
                </div>
            </div>

            <!-- Faculty Specific Fields -->
            <div id="faculty_fields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" placeholder="e.g. Computer Science">
                </div>
                <div class="mb-3">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control" placeholder="e.g. Assistant Professor">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
            
            <div class="text-center mt-3">
                <p class="text-muted small">Already have an account? <br> 
                    <a href="login.php" class="text-primary fw-bold text-decoration-none">Back to Login</a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFields() {
    const role = document.getElementById('register_role').value;
    const studentFields = document.getElementById('student_fields');
    const facultyFields = document.getElementById('faculty_fields');
    
    studentFields.style.display = 'none';
    facultyFields.style.display = 'none';
    
    // Remove "required" from hidden inputs
    studentFields.querySelectorAll('input, select').forEach(el => el.removeAttribute('required'));
    facultyFields.querySelectorAll('input, select').forEach(el => el.removeAttribute('required'));

    if (role === 'student') {
        studentFields.style.display = 'block';
        studentFields.querySelectorAll('input, select').forEach(el => el.setAttribute('required', 'true'));
    } else if (role === 'faculty') {
        facultyFields.style.display = 'block';
        facultyFields.querySelectorAll('input, select').forEach(el => el.setAttribute('required', 'true'));
    }
}
</script>

<?php include 'includes/footer.php'; ?>
