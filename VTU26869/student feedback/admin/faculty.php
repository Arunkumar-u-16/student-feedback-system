<?php
// admin/faculty.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Manage Faculty";
$isDashboard = true;
$activePage = 'faculty';

// Handle Add Faculty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? 'faculty123', PASSWORD_DEFAULT);
        $department = $_POST['department'] ?? '';
        $designation = $_POST['designation'] ?? '';

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'faculty')");
            $stmt->execute([$name, $email, $password]);
            $user_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO faculty (user_id, department, designation) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $department, $designation]);

            $pdo->commit();
            set_flash_message('success', 'Faculty added successfully!');
        }
        catch (Exception $e) {
            $pdo->rollBack();
            set_flash_message('danger', 'Error adding faculty: ' . $e->getMessage());
        }
    }
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $user_id = $_POST['user_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $department = $_POST['department'] ?? '';
        $designation = $_POST['designation'] ?? '';

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'faculty'");
            $stmt->execute([$name, $email, $user_id]);

            $stmt = $pdo->prepare("UPDATE faculty SET department = ?, designation = ? WHERE id = ?");
            $stmt->execute([$department, $designation, $id]);

            $pdo->commit();
            set_flash_message('success', 'Faculty updated successfully!');
        }
        catch (Exception $e) {
            $pdo->rollBack();
            set_flash_message('danger', 'Error updating faculty: ' . $e->getMessage());
        }
    }
    elseif ($action === 'delete') {
        $user_id = $_POST['user_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'faculty'");
            $stmt->execute([$user_id]);
            set_flash_message('success', 'Faculty deleted successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error deleting faculty: ' . $e->getMessage());
        }
    }
    header("Location: faculty.php");
    exit;
}

$facultyList = $pdo->query("
    SELECT f.*, u.name, u.email 
    FROM faculty f 
    JOIN users u ON f.user_id = u.id 
    ORDER BY u.created_at DESC
")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="m-0">Faculty Directory</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
        <i class="fas fa-plus me-2"></i> Add Faculty Member
    </button>
</div>

<div class="custom-table table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($facultyList as $faculty): ?>
            <tr>
                <td class="fw-bold"><?php echo e($faculty['name']); ?></td>
                <td><?php echo e($faculty['email']); ?></td>
                <td><span class="badge bg-light text-dark border"><?php echo e($faculty['department']); ?></span></td>
                <td><?php echo e($faculty['designation']); ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFacultyModal<?php echo $faculty['id']; ?>"><i class="fas fa-edit"></i></button>
                    <form action="faculty.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this faculty member?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?php echo $faculty['user_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>

            <!-- Edit Faculty Modal -->
            <div class="modal fade" id="editFacultyModal<?php echo $faculty['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content border-0 rounded-lg shadow">
                        <div class="modal-header border-0 bg-primary text-white p-4">
                            <h5 class="modal-title">Edit Faculty Member</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="faculty.php" method="POST" class="needs-validation" novalidate>
                            <div class="modal-body p-4">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $faculty['id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $faculty['user_id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($faculty['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo e($faculty['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department" class="form-control" value="<?php echo e($faculty['department']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="designation" class="form-control" value="<?php echo e($faculty['designation']); ?>" required>
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
if (empty($facultyList)): ?>
            <tr><td colspan="5" class="text-center py-4">No faculty members found.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Faculty Modal -->
<div class="modal fade" id="addFacultyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg shadow">
            <div class="modal-header border-0 bg-primary text-white p-4">
                <h5 class="modal-title">Add Faculty Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="faculty.php" method="POST" class="needs-validation" novalidate>
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
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" placeholder="e.g. Computer Science" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Assistant Professor" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Faculty</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
