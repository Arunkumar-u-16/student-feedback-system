<?php
// admin/reports.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Detailed Feedback Reports";
$isDashboard = true;
$activePage = 'reports';

// Handle Filter inputs
$course_filter = $_GET['course_id'] ?? '';
$subject_filter = $_GET['subject_id'] ?? '';

// Build Query for Submissions
$query = "
    SELECT fr.id, fr.submitted_at, u.name as student_name, c.name as course_name, s.name as subject_name, sa.id as assignment_id
    FROM feedback_responses fr
    JOIN students st ON fr.student_id = st.id
    JOIN users u ON st.user_id = u.id
    JOIN subject_assignments sa ON fr.assignment_id = sa.id
    JOIN subjects s ON sa.subject_id = s.id
    JOIN courses c ON sa.course_id = c.id
    WHERE 1=1
";

$params = [];
if ($course_filter) {
    $query .= " AND c.id = ?";
    $params[] = $course_filter;
}
if ($subject_filter) {
    $query .= " AND s.id = ?";
    $params[] = $subject_filter;
}

$query .= " ORDER BY fr.submitted_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// Fetch courses and subjects for filters
$courses = $pdo->query("SELECT * FROM courses")->fetchAll();
$subjects = $pdo->query("SELECT * FROM subjects")->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-lg p-4">
            <h5 class="mb-4">Filter Reports</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo($course_filter == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo e($c['name']); ?>
                        </option>
                        <?php
endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo($subject_filter == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo e($s['name']); ?>
                        </option>
                        <?php
endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="custom-table table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Subject</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $sub): ?>
            <tr>
                <td><?php echo date('d M Y', strtotime($sub['submitted_at'])); ?></td>
                <td class="fw-bold"><?php echo e($sub['student_name']); ?></td>
                <td><?php echo e($sub['course_name']); ?></td>
                <td><?php echo e($sub['subject_name']); ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal_<?php echo $sub['id']; ?>">
                        View Breakdown
                    </button>
                </td>
            </tr>

            <!-- Detail Modal -->
            <div class="modal fade" id="modal_<?php echo $sub['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content border-0 rounded-lg shadow-lg">
                        <div class="modal-header bg-primary text-white border-0 p-4">
                            <h5 class="modal-title">Feedback Detail: <?php echo e($sub['student_name']); ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Subject</small>
                                    <span class="fw-bold"><?php echo e($sub['subject_name']); ?></span>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <small class="text-muted d-block">Date Submitted</small>
                                    <span class="fw-bold"><?php echo format_date($sub['submitted_at']); ?></span>
                                </div>
                            </div>

                            <div class="list-group list-group-flush border-top">
                                <?php
    $ans_stmt = $pdo->prepare("
                                    SELECT fa.*, fq.question_text, fq.question_type 
                                    FROM feedback_answers fa
                                    JOIN feedback_questions fq ON fa.question_id = fq.id
                                    WHERE fa.response_id = ?
                                ");
    $ans_stmt->execute([$sub['id']]);
    $answers = $ans_stmt->fetchAll();

    foreach ($answers as $idx => $ans):
        $percentage = ($ans['rating'] / 5) * 100;
?>
                                <div class="list-group-item px-0 py-4 border-bottom">
                                    <p class="mb-3 fw-bold">Q<?php echo($idx + 1); ?>. <?php echo e($ans['question_text']); ?></p>
                                    
                                    <?php if ($ans['question_type'] === 'rating'): ?>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="progress flex-grow-1" style="height: 10px; border-radius: 5px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?php echo $percentage; ?>%" 
                                                 aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-3"><?php echo $ans['rating']; ?> / 5 (<?php echo $percentage; ?>%)</span>
                                    </div>
                                    <?php
        else: ?>
                                    <div class="bg-light p-3 rounded-lg text-muted italic">
                                        "<?php echo e($ans['answer_text'] ?: 'No comments provided.'); ?>"
                                    </div>
                                    <?php
        endif; ?>
                                </div>
                                <?php
    endforeach; ?>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close Report</button>
                            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Report</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
endforeach;
if (empty($submissions)): ?>
            <tr><td colspan="5" class="text-center py-5">No feedback records found for the selected criteria.</td></tr>
            <?php
endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
