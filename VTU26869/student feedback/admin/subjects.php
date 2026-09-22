<?php
// admin/subjects.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Manage Subjects";
$isDashboard = true;
$activePage = 'subjects';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';
        $description = $_POST['description'] ?? '';

        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (name, code, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $code, $description]);
            set_flash_message('success', 'Subject added successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error adding subject: ' . $e->getMessage());
        }
    }
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';
        $description = $_POST['description'] ?? '';

        try {
            $stmt = $pdo->prepare("UPDATE subjects SET name = ?, code = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $code, $description, $id]);
            set_flash_message('success', 'Subject updated successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error updating subject: ' . $e->getMessage());
        }
    }
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Subject deleted successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error deleting subject: ' . $e->getMessage());
        }
    }
    header("Location: subjects.php");
    exit;
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="m-0">Subject Repository</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
        <i class="fas fa-plus me-2"></i> Add New Subject
    </button>
</div>

<div class="custom-table table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Code</th>
                <th>Subject Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subjects as $subject): ?>
            <tr>
                <td class="fw-bold"><?php echo e($subject['code']); ?></td>
                <td><?php echo e($subject['name']); ?></td>
                <td><small class="text-muted"><?php echo e($subject['description']); ?></small></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSubjectModal<?php echo $subject['id']; ?>"><i class="fas fa-edit"></i></button>
                    <form action="subjects.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>

            <!-- Edit Subject Modal -->
            <div class="modal fade" id="editSubjectModal<?php echo $subject['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content border-0 rounded-lg">
                        <div class="modal-header bg-primary text-white border-0 p-4">
                            <h5 class="modal-title">Edit Subject</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="subjects.php" method="POST" class="needs-validation" novalidate>
                            <div class="modal-body p-4">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Subject Code</label>
                                    <input type="text" name="code" class="form-control" value="<?php echo e($subject['code']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subject Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($subject['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2"><?php echo e($subject['description']); ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-4 pt-0">
                                <button type="submit" class="btn btn-primary w-100 py-2">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
endforeach;
if (empty($subjects)): ?>
            <tr><td colspan="4" class="text-center py-4">No subjects found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header bg-primary text-white border-0 p-4">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="subjects.php" method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Subject Code</label>
                        <input type="text" name="code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 py-2">Save Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
