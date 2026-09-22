<?php
// admin/courses.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Manage Courses";
$isDashboard = true;
$activePage = 'courses';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';
        $description = $_POST['description'] ?? '';

        try {
            $stmt = $pdo->prepare("INSERT INTO courses (name, code, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $code, $description]);
            set_flash_message('success', 'Course added successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error adding course: ' . $e->getMessage());
        }
    }
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';
        $description = $_POST['description'] ?? '';

        try {
            $stmt = $pdo->prepare("UPDATE courses SET name = ?, code = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $code, $description, $id]);
            set_flash_message('success', 'Course updated successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error updating course: ' . $e->getMessage());
        }
    }
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Course deleted successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error deleting course: ' . $e->getMessage());
        }
    }
    header("Location: courses.php");
    exit;
}

$courses = $pdo->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="m-0">Course Catalog</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
        <i class="fas fa-plus me-2"></i> Add New Course
    </button>
</div>

<div class="row">
    <?php foreach ($courses as $course): ?>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-lg h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-primary px-3 py-2"><?php echo e($course['code']); ?></span>
                    <div class="dropdown">
                        <button class="btn btn-link link-muted p-0" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                        <ul class="dropdown-menu">
                            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editCourseModal<?php echo $course['id']; ?>">Edit</button></li>
                            <li>
                                <form action="courses.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <button type="submit" class="dropdown-item text-danger">Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <h5 class="fw-bold mb-2"><?php echo e($course['name']); ?></h5>
                <p class="text-muted small mb-3"><?php echo e($course['description']); ?></p>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal<?php echo $course['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-lg">
                <div class="modal-header bg-primary text-white border-0 p-4">
                    <h5 class="modal-title">Edit Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="courses.php" method="POST" class="needs-validation" novalidate>
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="code" class="form-control" value="<?php echo e($course['code']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($course['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo e($course['description']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
endforeach;
if (empty($courses)): ?>
    <div class="col-12 text-center py-5">
        <p class="text-muted">No courses available. Start by adding one!</p>
    </div>
    <?php
endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header bg-primary text-white border-0 p-4">
                <h5 class="modal-title">Create New Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="courses.php" method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Course Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. CS101" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Computer Science & Engineering" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
