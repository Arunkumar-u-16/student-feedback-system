<?php
// faculty/reports.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('faculty');

$pageTitle = "My Performance Reports";
$isDashboard = true;
$activePage = 'reports';
$faculty_id = $_SESSION['faculty_id'];

// Get specific assignment if requested
$assignment_id = $_GET['id'] ?? null;

// Fetch all subjects assigned to this faculty for the dropdown/selector
$stmt = $pdo->prepare("
    SELECT sa.id, s.name as subject_name, c.name as course_name 
    FROM subject_assignments sa
    JOIN subjects s ON sa.subject_id = s.id
    JOIN courses c ON sa.course_id = c.id
    WHERE sa.faculty_id = ?
");
$stmt->execute([$faculty_id]);
$my_subjects = $stmt->fetchAll();

$report_data = null;
$comments = [];

if ($assignment_id) {
    // 1. Get Question-wise Analytics
    $stmt = $pdo->prepare("
        SELECT fq.id, fq.question_text, fq.question_type,
               AVG(fa.rating) as avg_rating,
               COUNT(fa.id) as total_responses
        FROM feedback_questions fq
        LEFT JOIN feedback_answers fa ON fq.id = fa.question_id
        LEFT JOIN feedback_responses fr ON fa.response_id = fr.id
        WHERE fr.assignment_id = ? AND fq.question_type = 'rating'
        GROUP BY fq.id
    ");
    $stmt->execute([$assignment_id]);
    $report_data = $stmt->fetchAll();

    // 2. Get Text Comments (Anonymously)
    $stmt = $pdo->prepare("
        SELECT fa.answer_text
        FROM feedback_answers fa
        JOIN feedback_responses fr ON fa.response_id = fr.id
        JOIN feedback_questions fq ON fa.question_id = fq.id
        WHERE fr.assignment_id = ? AND fq.question_type = 'text' AND fa.answer_text IS NOT NULL AND fa.answer_text != ''
    ");
    $stmt->execute([$assignment_id]);
    $comments = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-lg p-4">
            <h5 class="mb-4">Select Subject Report</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-9">
                    <select name="id" class="form-select form-select-lg" required>
                        <option value="" selected disabled>Choose a subject...</option>
                        <?php foreach ($my_subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>" <?php echo($assignment_id == $sub['id']) ? 'selected' : ''; ?>>
                            <?php echo e($sub['subject_name']); ?> (<?php echo e($sub['course_name']); ?>)
                        </option>
                        <?php
endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100">View Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($assignment_id && $report_data): ?>
<div class="row">
    <!-- Question-wise Breakdown -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-lg p-4 h-100">
            <h5 class="mb-4">Question-wise Analysis</h5>
            <div class="list-group list-group-flush">
                <?php foreach ($report_data as $row):
        $pct = ($row['avg_rating'] / 5) * 100;
?>
                <div class="list-group-item px-0 py-3 border-bottom-0">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold"><?php echo e($row['question_text']); ?></span>
                        <span class="text-primary fw-bold"><?php echo number_format($row['avg_rating'], 1); ?> / 5</span>
                    </div>
                    <div class="progress" style="height: 12px; border-radius: 6px; background-color: #eee;">
                        <div class="progress-bar <?php echo $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-info' : 'bg-warning'); ?>" 
                             role="progressbar" style="width: <?php echo $pct; ?>%"></div>
                    </div>
                </div>
                <?php
    endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-lg p-4 h-100 bg-light">
            <h5 class="mb-4">Student Comments</h5>
            <div style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($comments)): ?>
                    <p class="text-muted italic">No comments submitted for this subject yet.</p>
                <?php
    else: ?>
                    <?php foreach ($comments as $c): ?>
                    <div class="card border-0 shadow-sm mb-3 rounded-lg">
                        <div class="card-body p-3">
                            <i class="fas fa-quote-left text-primary opacity-25 mb-2"></i>
                            <p class="mb-0 small text-dark"><?php echo e($c['answer_text']); ?></p>
                        </div>
                    </div>
                    <?php
        endforeach; ?>
                <?php
    endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
elseif ($assignment_id): ?>
<div class="alert alert-info border-0 shadow-sm rounded-lg">
    <i class="fas fa-info-circle me-2"></i> No feedback data available for this subject yet.
</div>
<?php
endif; ?>

<?php include '../includes/footer.php'; ?>
