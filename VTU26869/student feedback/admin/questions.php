<?php
// admin/questions.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Manage Questions";
$isDashboard = true;
$activePage = 'questions';

// Auto-migrate to add category if it doesn't exist
try {
    $pdo->exec("ALTER TABLE feedback_questions ADD COLUMN category VARCHAR(50) DEFAULT 'faculty'");
}
catch (PDOException $e) {
// Column likely already exists
}

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $question_text = $_POST['question_text'] ?? '';
        $question_type = $_POST['question_type'] ?? 'rating';
        $category = $_POST['category'] ?? 'faculty';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("INSERT INTO feedback_questions (question_text, question_type, category, is_active) VALUES (?, ?, ?, ?)");
            $stmt->execute([$question_text, $question_type, $category, $is_active]);
            set_flash_message('success', 'Question added successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error adding question: ' . $e->getMessage());
        }
    }
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $question_text = $_POST['question_text'] ?? '';
        $question_type = $_POST['question_type'] ?? 'rating';
        $category = $_POST['category'] ?? 'faculty';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("UPDATE feedback_questions SET question_text = ?, question_type = ?, category = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$question_text, $question_type, $category, $is_active, $id]);
            set_flash_message('success', 'Question updated successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error updating question: ' . $e->getMessage());
        }
    }
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM feedback_questions WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Question deleted successfully!');
        }
        catch (Exception $e) {
            set_flash_message('danger', 'Error deleting question: ' . $e->getMessage());
        }
    }

    // Redirect to avoid form resubmission
    header("Location: questions.php");
    exit;
}

$questions = $pdo->query("SELECT * FROM feedback_questions ORDER BY category ASC, id ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="m-0">Manage Feedback Questions</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
        <i class="fas fa-plus me-2"></i> Add New Question
    </button>
</div>

<div class="card border-0 shadow-sm rounded-lg mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="py-3">Question Text</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Category</th>
                        <th class="py-3">Status</th>
                        <th class="text-end px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                    <tr>
                        <td class="px-4"><?php echo e($q['id']); ?></td>
                        <td><?php echo e($q['question_text']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $q['question_type'] == 'rating' ? 'info' : 'secondary'; ?>">
                                <?php echo ucfirst(e($q['question_type'])); ?>
                            </span>
                        </td>
                        <td><span class="badge bg-primary text-capitalize"><?php echo e($q['category'] ?? 'faculty'); ?></span></td>
                        <td>
                            <?php if ($q['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php
    else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php
    endif; ?>
                        </td>
                        <td class="text-end px-4">
                            <button class="btn btn-sm btn-light text-primary me-2" data-bs-toggle="modal" data-bs-target="#editQuestionModal<?php echo $q['id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="questions.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                <button type="submit" class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Question Modal -->
                    <div class="modal fade" id="editQuestionModal<?php echo $q['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content border-0 rounded-lg">
                                <div class="modal-header bg-primary text-white border-0 p-4">
                                    <h5 class="modal-title">Edit Question</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="questions.php" method="POST" class="needs-validation" novalidate>
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Question Text</label>
                                            <textarea name="question_text" class="form-control" rows="3" required><?php echo e($q['question_text']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Type</label>
                                            <select name="question_type" class="form-select" required>
                                                <option value="rating" <?php echo $q['question_type'] == 'rating' ? 'selected' : ''; ?>>Rating (1-5)</option>
                                                <option value="text" <?php echo $q['question_type'] == 'text' ? 'selected' : ''; ?>>Text Response</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select name="category" class="form-select" required>
                                                <option value="faculty" <?php echo($q['category'] ?? 'faculty') == 'faculty' ? 'selected' : ''; ?>>Faculty Grading</option>
                                                <option value="website" <?php echo($q['category'] ?? '') == 'website' ? 'selected' : ''; ?>>Website</option>
                                                <option value="admin" <?php echo($q['category'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                <option value="events" <?php echo($q['category'] ?? '') == 'events' ? 'selected' : ''; ?>>Events</option>
                                                <option value="technical" <?php echo($q['category'] ?? '') == 'technical' ? 'selected' : ''; ?>>Technical Events</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive<?php echo $q['id']; ?>" <?php echo $q['is_active'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="isActive<?php echo $q['id']; ?>">Active</label>
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
endforeach; ?>
                    <?php if (empty($questions)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No questions available. Start by adding one!</td>
                    </tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header bg-primary text-white border-0 p-4">
                <h5 class="modal-title">Create New Question</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="questions.php" method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea name="question_text" class="form-control" rows="3" placeholder="e.g. Teacher is punctual to the class" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="question_type" class="form-select" required>
                            <option value="rating">Rating (1-5)</option>
                            <option value="text">Text Response</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="faculty">Faculty Grading</option>
                            <option value="website">Website</option>
                            <option value="admin">Admin</option>
                            <option value="events">Events</option>
                            <option value="technical">Technical Events</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
