<?php
// admin/students.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Manage Students";
$isDashboard = true;
$activePage = 'students';

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? 'student123', PASSWORD_DEFAULT);
        $course_id = $_POST['course_id'] ?? '';
        $roll_no = $_POST['roll_no'] ?? '';
        $semester = $_POST['semester'] ?? 1;

        try {
            $pdo->beginTransaction();

            // 1. Create User
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
            $stmt->execute([$name, $email, $password]);
            $user_id = $pdo->lastInsertId();

            // 2. Create Student
            $stmt = $pdo->prepare("INSERT INTO students (user_id, course_id, roll_no, semester) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $course_id, $roll_no, $semester]);

            $pdo->commit();
            set_flash_message('success', 'Student added successfully!');
        }
        catch (Exception $e) {
            $pdo->rollBack();
            set_flash_message('danger', 'Error adding student: ' . $e->getMessage());
        }
    }
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $user_id = $_POST['user_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $course_id = $_POST['course_id'] ?? '';
        $roll_no = $_POST['roll_no'] ?? '';
        $semester = $_POST['semester'] ?? 1;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'student'");
            $stmt->execute([$name, $email, $user_id]);

            $stmt = $pdo->prepare("UPDATE students SET course_id = ?, roll_no = ?, semester = ? WHERE id = ?");
            $stmt->execute([$course_id, $roll_no, $semester, $id]);

            $pdo->commit();
            set_flash_message('success', 'Student updated successfully!');
        }
        catch (Exception $e) {
            $pdo->rollBack();
            set_flash_message('danger', 'Error updating student: ' . $e->getMessage());
        }
    }
    elseif ($action === 'delete') {
        $user_id = $_POST['user_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->execute([$user_id]);
            set_flash_message('success', 'Student deleted successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error deleting student: ' . $e->getMessage());
        }
    }
    header("Location: students.php");
    exit;
}

// Fetch Students
$students = $pdo->query("
    SELECT s.*, u.name, u.email, c.name as course_name 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    JOIN courses c ON s.course_id = c.id
    ORDER BY u.created_at DESC
")->fetchAll();

// Fetch Courses for Dropdown
$courses = $pdo->query("SELECT * FROM courses")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="m-0">Student Registry</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fas fa-plus me-2"></i> Add New Student
    </button>
</div>

<div class="custom-table table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Roll No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td class="fw-bold"><?php echo e($student['roll_no']); ?></td>
                <td><?php echo e($student['name']); ?></td>
                <td><?php echo e($student['email']); ?></td>
                <td><span class="badge bg-light text-dark border"><?php echo e($student['course_name']); ?></span></td>
                <td>Sem <?php echo e($student['semester']); ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStudentModal<?php echo $student['id']; ?>"><i class="fas fa-edit"></i></button>
                    <form action="students.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this student?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?php echo $student['user_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>

            <!-- Edit Student Modal -->
            <div class="modal fade" id="editStudentModal<?php echo $student['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content border-0 rounded-lg shadow">
                        <div class="modal-header border-0 bg-primary text-white p-4 rounded-top">
                            <h5 class="modal-title">Edit Student</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="students.php" method="POST" class="needs-validation" novalidate>
                            <div class="modal-body p-4">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $student['user_id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($student['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo e($student['email']); ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Roll Number</label>
                                        <input type="text" name="roll_no" class="form-control" value="<?php echo e($student['roll_no']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Course</label>
                                        <select name="course_id" class="form-select" required>
                                            <?php foreach ($courses as $course): ?>
                                            <option value="<?php echo $course['id']; ?>" <?php echo $course['id'] == $student['course_id'] ? 'selected' : ''; ?>><?php echo e($course['name']); ?></option>
                                            <?php
    endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Semester</label>
                                    <select name="semester" class="form-select" required>
                                        <option value="1" <?php echo $student['semester'] == '1' ? 'selected' : ''; ?>>Semester 1</option>
                                        <option value="2" <?php echo $student['semester'] == '2' ? 'selected' : ''; ?>>Semester 2</option>
                                        <option value="3" <?php echo $student['semester'] == '3' ? 'selected' : ''; ?>>Semester 3</option>
                                        <option value="4" <?php echo $student['semester'] == '4' ? 'selected' : ''; ?>>Semester 4</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-4 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
endforeach;
if (empty($students)): ?>
            <tr><td colspan="6" class="text-center py-4">No students found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg shadow">
            <div class="modal-header border-0 bg-primary text-white p-4 rounded-top">
                <h5 class="modal-title">Add New Student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="students.php" method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Roll Number</label>
                            <input type="text" name="roll_no" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select" required>
                                <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo e($course['name']); ?></option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            <option value="3">Semester 3</option>
                            <option value="4">Semester 4</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss.modal="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
