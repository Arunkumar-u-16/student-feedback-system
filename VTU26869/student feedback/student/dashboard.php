<?php
// student/dashboard.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('student');

$pageTitle = "Dashboard";
$isDashboard = true;
$activePage = 'dashboard';

$student_id = $_SESSION['student_id'];

// Auto-migrate tables for category-based feedbacks
try {
    $pdo->exec("ALTER TABLE feedback_responses ADD COLUMN category VARCHAR(50) DEFAULT 'faculty'");
}
catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE feedback_questions ADD COLUMN category VARCHAR(50) DEFAULT 'faculty'");
}
catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE feedback_responses MODIFY COLUMN assignment_id INT NULL");
}
catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE feedback_responses DROP INDEX student_id"); // Drop the unique key
}
catch (Exception $e) {
}

// Get subjects assigned to student's course
$stmt = $pdo->prepare("
    SELECT sa.*, s.name as subject_name, u.name as faculty_name, 
    (SELECT COUNT(*) FROM feedback_responses fr WHERE fr.assignment_id = sa.id AND fr.student_id = ? AND (fr.category = 'faculty' OR fr.category IS NULL)) as is_submitted
    FROM subject_assignments sa
    JOIN subjects s ON sa.subject_id = s.id
    JOIN faculty f ON sa.faculty_id = f.id
    JOIN users u ON f.user_id = u.id
    JOIN students st ON st.course_id = sa.course_id
    WHERE st.id = ?
");
$stmt->execute([$student_id, $student_id]);
$assigned_subjects = $stmt->fetchAll();

// Get available global categories (where there is at least one active question)
$stmt = $pdo->query("SELECT DISTINCT category FROM feedback_questions WHERE is_active = 1 AND category != 'faculty'");
$active_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Check submission status for global categories
$global_feedbacks = [];
foreach ($active_categories as $cat) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM feedback_responses WHERE student_id = ? AND category = ?");
    $stmt->execute([$student_id, $cat]);
    $global_feedbacks[$cat] = [
        'name' => ucwords(str_replace('_', ' ', $cat)),
        'is_submitted' => $stmt->fetchColumn() > 0
    ];
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-lg bg-primary text-white p-4 d-flex flex-row justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Hello, <?php echo e($_SESSION['user_name']); ?>!</h4>
                <p class="mb-0 opacity-75">Check out the latest college events or provide your feedback below.</p>
            </div>
            <a href="events.php" class="btn btn-light rounded-pill px-4 fw-bold text-primary">
                <i class="fas fa-calendar-alt me-2"></i>Events
            </a>
        </div>
    </div>
</div>

<div class="row">
    <?php foreach ($assigned_subjects as $sub): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-lg h-100 <?php echo $sub['is_submitted'] ? 'opacity-75' : ''; ?>">
            <div class="card-body p-4 d-flex flex-column">
                <div class="mb-3">
                    <?php if ($sub['is_submitted']): ?>
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Submitted</span>
                    <?php
    else: ?>
                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending</span>
                    <?php
    endif; ?>
                </div>
                <h5 class="fw-bold mb-1"><?php echo e($sub['subject_name']); ?></h5>
                <p class="text-muted small mb-4">Instructor: <?php echo e($sub['faculty_name']); ?></p>
                
                <div class="mt-auto">
                    <?php if ($sub['is_submitted']): ?>
                    <button class="btn btn-light w-100 disabled">Already Submitted</button>
                    <?php
    else: ?>
                    <a href="feedback_form.php?id=<?php echo $sub['id']; ?>" class="btn btn-primary w-100">
                        Give Feedback <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    <?php
    endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
endforeach;
if (empty($assigned_subjects)): ?>
    <div class="col-12 text-center py-5">
        <img src="../assets/images/no-data.svg" alt="No data" style="width: 150px;" class="mb-4 d-none">
        <p class="text-muted">No subjects assigned to your course yet.</p>
    </div>
    <?php
endif; ?>
</div>

<?php if (!empty($global_feedbacks)): ?>
<div class="row mt-4">
    <div class="col-12 mb-3">
        <h5 class="fw-bold">Other Feedback Options</h5>
    </div>
    <?php foreach ($global_feedbacks as $cat_id => $cat_data): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-lg h-100 <?php echo $cat_data['is_submitted'] ? 'opacity-75' : ''; ?>">
            <div class="card-body p-4 d-flex flex-column">
                <div class="mb-3">
                    <?php if ($cat_data['is_submitted']): ?>
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Submitted</span>
                    <?php
        else: ?>
                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending</span>
                    <?php
        endif; ?>
                </div>
                <h5 class="fw-bold mb-1"><?php echo e($cat_data['name']); ?> Feedback</h5>
                <p class="text-muted small mb-4">Share your thoughts on <?php echo e(strtolower($cat_data['name'])); ?> matters.</p>
                
                <div class="mt-auto">
                    <?php if ($cat_data['is_submitted']): ?>
                    <button class="btn btn-light w-100 disabled">Already Submitted</button>
                    <?php
        else: ?>
                    <a href="general_feedback.php?category=<?php echo $cat_id; ?>" class="btn btn-secondary w-100">
                        Give Feedback <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    <?php
        endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    endforeach; ?>
</div>
<?php
endif; ?>

<?php include '../includes/footer.php'; ?>
