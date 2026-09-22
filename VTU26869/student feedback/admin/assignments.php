<?php
// admin/assignments.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Subject Assignments";
$isDashboard = true;
$activePage = 'assignments';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign') {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    $subject_id = $_POST['subject_id'];
    $faculty_id = $_POST['faculty_id'];
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'];

    try {
        $stmt = $pdo->prepare("INSERT INTO subject_assignments (subject_id, faculty_id, course_id, academic_year) VALUES (?, ?, ?, ?)");
        $stmt->execute([$subject_id, $faculty_id, $course_id, $academic_year]);
        set_flash_message('success', 'Subject assigned successfully!');
    }
    catch (Exception $e) {
        set_flash_message('danger', 'Error assigning subject: ' . $e->getMessage());
    }
}

// Fetch Assignments
$assignments = $pdo->query("
    SELECT sa.*, s.name as subject_name, u.name as faculty_name, c.name as course_name 
    FROM subject_assignments sa
    JOIN subjects s ON sa.subject_id = s.id
    JOIN faculty f ON sa.faculty_id = f.id
    JOIN users u ON f.user_id = u.id
    JOIN courses c ON sa.course_id = c.id
")->fetchAll();

$subjects = $pdo->query("SELECT * FROM subjects")->fetchAll();
$facultyList = $pdo->query("SELECT f.id, u.name FROM faculty f JOIN users u ON f.user_id = u.id")->fetchAll();
$courses = $pdo->query("SELECT * FROM courses")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="m-0">Faculty Load Mapping</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
        <i class="fas fa-plus me-2"></i> New Assignment
    </button>
</div>

<div class="custom-table table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Faculty</th>
                <th>Course</th>
                <th>Academic Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $as): ?>
            <tr>
                <td class="fw-bold"><?php echo e($as['subject_name']); ?></td>
                <td><?php echo e($as['faculty_name']); ?></td>
                <td><?php echo e($as['course_name']); ?></td>
                <td><?php echo e($as['academic_year']); ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php
endforeach;
if (empty($assignments)): ?>
            <tr><td colspan="5" class="text-center py-4">No assignments found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header bg-primary text-white border-0 p-4">
                <h5 class="modal-title">Assign Subject to Faculty</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="assignments.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="assign">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo e($s['name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Faculty</label>
                        <select name="faculty_id" class="form-select" required>
                            <?php foreach ($facultyList as $f): ?>
                            <option value="<?php echo $f['id']; ?>"><?php echo e($f['name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Course</label>
                        <select name="course_id" class="form-select" required>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2023-24" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100">Confirm Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
