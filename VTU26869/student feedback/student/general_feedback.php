<?php
// student/general_feedback.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('student');

$student_id = $_SESSION['student_id'];
$category = $_GET['category'] ?? null;

if (!$category) {
    redirect('student/dashboard.php');
}

// Check if already submitted
$stmt = $pdo->prepare("SELECT id FROM feedback_responses WHERE student_id = ? AND category = ?");
$stmt->execute([$student_id, $category]);
if ($stmt->fetch()) {
    redirect('student/dashboard.php', 'warning', 'You have already submitted feedback for this category.');
}

// Get active questions for this category
$stmt = $pdo->prepare("SELECT * FROM feedback_questions WHERE is_active = 1 AND category = ? ORDER BY id ASC");
$stmt->execute([$category]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    redirect('student/dashboard.php', 'info', 'No questions available for this category right now.');
}

$categoryName = ucwords(str_replace('_', ' ', $category));

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    try {
        $pdo->beginTransaction();

        // 1. Create main response entry (assignment_id is NULL)
        $stmt = $pdo->prepare("INSERT INTO feedback_responses (student_id, assignment_id, category) VALUES (?, NULL, ?)");
        $stmt->execute([$student_id, $category]);
        $response_id = $pdo->lastInsertId();

        // 2. Save answers
        foreach ($questions as $q) {
            $ans_stmt = $pdo->prepare("INSERT INTO feedback_answers (response_id, question_id, rating, answer_text) VALUES (?, ?, ?, ?)");
            if ($q['question_type'] === 'rating') {
                $rating = $_POST['q_' . $q['id']] ?? 0;
                $ans_stmt->execute([$response_id, $q['id'], $rating, null]);
            }
            else {
                $comment = $_POST['q_' . $q['id']] ?? '';
                $ans_stmt->execute([$response_id, $q['id'], 0, $comment]);
            }
        }

        $pdo->commit();
        redirect('student/dashboard.php', 'success', $categoryName . ' feedback submitted successfully! Thank you for your input.');
    }
    catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('danger', 'Error submitting feedback: ' . $e->getMessage());
    }
}

$pageTitle = $categoryName . " Feedback";
$isDashboard = true;
$activePage = 'dashboard';
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-secondary text-white p-4">
                <h5 class="mb-1"><?php echo $categoryName; ?> Feedback</h5>
                <p class="mb-0 small opacity-75">Your candid feedback helps us improve.</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form action="general_feedback.php?category=<?php echo urlencode($category); ?>" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                    <?php foreach ($questions as $index => $q): ?>
                    <div class="mb-5">
                        <h6 class="fw-bold mb-3"><?php echo($index + 1) . '. ' . e($q['question_text']); ?></h6>
                        
                        <?php if ($q['question_type'] === 'rating'): ?>
                        <div class="d-flex justify-content-between align-items-center rating-container">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="form-check form-check-inline text-center m-0">
                                <input class="form-check-input d-none" type="radio" name="q_<?php echo $q['id']; ?>" id="q_<?php echo $q['id'] . '_' . $i; ?>" value="<?php echo $i; ?>" required>
                                <label class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" for="q_<?php echo $q['id'] . '_' . $i; ?>" style="width: 45px; height: 45px;">
                                    <?php echo $i; ?>
                                </label>
                                <small class="text-muted d-block mt-1">
                                    <?php echo($i == 1) ? 'Poor' : (($i == 5) ? 'Excellent' : ''); ?>
                                </small>
                            </div>
                            <?php
        endfor; ?>
                        </div>
                        <?php
    else: ?>
                        <textarea name="q_<?php echo $q['id']; ?>" class="form-control" rows="4" placeholder="Share your thoughts here..."></textarea>
                        <?php
    endif; ?>
                    </div>
                    <?php
endforeach; ?>

                    <div class="border-top pt-4 text-end">
                        <a href="dashboard.php" class="btn btn-light px-4 me-2">Cancel</a>
                        <button type="submit" class="btn btn-secondary px-5">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.form-check-input:checked + label {
    background-color: var(--bs-secondary) !important;
    color: white !important;
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.rating-container label {
    cursor: pointer;
    transition: all 0.2s ease;
    border-width: 2px;
}
.rating-container label:hover {
    background-color: rgba(108, 117, 125, 0.05); /* Secondary color slight hover */
    transform: translateY(-2px);
}
</style>

<?php include '../includes/footer.php'; ?>
