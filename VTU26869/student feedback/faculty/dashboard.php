<?php
// faculty/dashboard.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('faculty');

$pageTitle = "Faculty Dashboard";
$isDashboard = true;
$activePage = 'dashboard';
$faculty_id = $_SESSION['faculty_id'];

// Get assigned subjects and their stats
$stmt = $pdo->prepare("
    SELECT sa.id, s.name as subject_name, c.name as course_name,
    (SELECT COUNT(*) FROM feedback_responses fr WHERE fr.assignment_id = sa.id) as response_count,
    (SELECT AVG(fa.rating) FROM feedback_answers fa 
     JOIN feedback_responses fr ON fa.response_id = fr.id 
     JOIN feedback_questions fq ON fa.question_id = fq.id
     WHERE fr.assignment_id = sa.id AND fq.question_type = 'rating') as avg_rating
    FROM subject_assignments sa
    JOIN subjects s ON sa.subject_id = s.id
    JOIN courses c ON sa.course_id = c.id
    WHERE sa.faculty_id = ?
");
$stmt->execute([$faculty_id]);
$stats = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row g-4 mb-5">
    <?php foreach ($stats as $s): ?>
    <div class="col-md-6 col-lg-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="icon-box bg-primary text-white">
                    <i class="fas fa-book"></i>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark border">Avg. Rating</span>
                    <div class="h3 fw-bold text-primary m-0">
                        <?php echo $s['avg_rating'] ? number_format($s['avg_rating'], 1) : 'N/A'; ?>
                    </div>
                </div>
            </div>
            <h5 class="fw-bold mb-1"><?php echo e($s['subject_name']); ?></h5>
            <p class="text-muted small mb-3"><?php echo e($s['course_name']); ?></p>
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <span class="text-muted small"><i class="fas fa-users me-1"></i> Responses: <?php echo $s['response_count']; ?></span>
                <a href="reports.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-link p-0 text-decoration-none">View Details <i class="fas fa-chevron-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <?php
endforeach;
if (empty($stats)): ?>
    <div class="col-12 text-center py-5">
        <p class="text-muted">No subjects assigned yet.</p>
    </div>
    <?php
endif; ?>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-lg p-4">
            <h5 class="mb-4">Subject Performance Comparison</h5>
            <canvas id="facultyChart" height="150"></canvas>
        </div>
    </div>
</div>

<?php

$subject_names = json_encode(array_column($stats, 'subject_name'));
$ratings = json_encode(array_column($stats, 'avg_rating'));

$extraJS = "
<script>
    const ctx = document.getElementById('facultyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: $subject_names,
            datasets: [{
                label: 'Average Rating (out of 5)',
                data: $ratings,
                backgroundColor: '#3949ab',
                borderRadius: 8
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, max: 5 }
            }
        }
    });
</script>
";
include '../includes/footer.php'; ?>
