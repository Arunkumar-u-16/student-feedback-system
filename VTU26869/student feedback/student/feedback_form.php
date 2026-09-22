<?php
// student/feedback_form.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('student');

$student_id = $_SESSION['student_id'];
$assignment_id = $_GET['id'] ?? null;

if (!$assignment_id)
    redirect('student/dashboard.php');

// Check if already submitted
$stmt = $pdo->prepare("SELECT id FROM feedback_responses WHERE student_id = ? AND assignment_id = ?");
$stmt->execute([$student_id, $assignment_id]);
if ($stmt->fetch()) {
    redirect('student/dashboard.php', 'warning', 'You have already submitted feedback for this subject.');
}

// Get subject details
$stmt = $pdo->prepare("
    SELECT sa.*, s.name as subject_name, u.name as faculty_name 
    FROM subject_assignments sa
    JOIN subjects s ON sa.subject_id = s.id
    JOIN faculty f ON sa.faculty_id = f.id
    JOIN users u ON f.user_id = u.id
    WHERE sa.id = ?
");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment)
    redirect('student/dashboard.php');

// Get questions
$questions = $pdo->query("SELECT * FROM feedback_questions WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token']))
        die("CSRF validation failed.");

    try {
        $pdo->beginTransaction();

        // 1. Create main response entry
        $stmt = $pdo->prepare("INSERT INTO feedback_responses (student_id, assignment_id, category) VALUES (?, ?, 'faculty')");
        $stmt->execute([$student_id, $assignment_id]);
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
        redirect('student/dashboard.php', 'success', 'Feedback submitted successfully! Thank you for your input.');
    }
    catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('danger', 'Error submitting feedback: ' . $e->getMessage());
    }
}

$pageTitle = "Feedback Form";
$isDashboard = true;
$activePage = 'dashboard';
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-primary text-white p-4">
                <h5 class="mb-1">Feedback for <?php echo e($assignment['subject_name']); ?></h5>
                <p class="mb-0 small opacity-75">Instructor: <?php echo e($assignment['faculty_name']); ?></p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form action="feedback_form.php?id=<?php echo $assignment_id; ?>" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                    <?php foreach ($questions as $index => $q): ?>
                    <div class="mb-5">
                        <h6 class="fw-bold mb-3"><?php echo($index + 1) . '. ' . e($q['question_text']); ?></h6>
                        
                        <?php if ($q['question_type'] === 'rating'): ?>
                        <div class="d-flex justify-content-between align-items-center rating-container">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="form-check form-check-inline text-center m-0">
                                <input class="form-check-input d-none" type="radio" name="q_<?php echo $q['id']; ?>" id="q_<?php echo $q['id'] . '_' . $i; ?>" value="<?php echo $i; ?>" required>
                                <label class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" for="q_<?php echo $q['id'] . '_' . $i; ?>" style="width: 45px; height: 45px;">
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
                        <button type="submit" class="btn btn-primary px-5">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.form-check-input:checked + label {
    background-color: var(--primary-color) !important;
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
    background-color: rgba(26, 35, 126, 0.05);
    transform: translateY(-2px);
}
</style>

<?php include '../includes/footer.php'; ?>
