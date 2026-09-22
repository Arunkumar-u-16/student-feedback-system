<?php
// admin/dashboard.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Admin Dashboard";
$isDashboard = true;
$activePage = 'dashboard';

// Fetch Statistics
$stats = [
    'students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'faculty' => $pdo->query("SELECT COUNT(*) FROM faculty")->fetchColumn(),
    'courses' => $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
    'feedback' => $pdo->query("SELECT COUNT(*) FROM feedback_responses")->fetchColumn(),
];

include '../includes/header.php';
?>

<!-- Management Portal Section -->
<div class="mb-5 mt-2">
    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
        <i class="fas fa-th-large text-primary opacity-50"></i>
        Management Portal
    </h5>
    <div class="row g-3">
        <div class="col-4 col-md-3">
            <a href="courses.php" class="portal-item">
                <div class="portal-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-book"></i>
                </div>
                <span>Courses</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="subjects.php" class="portal-item">
                <div class="portal-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-book-open"></i>
                </div>
                <span>Subjects</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="faculty.php" class="portal-item">
                <div class="portal-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <span>Faculty</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="students.php" class="portal-item">
                <div class="portal-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <span>Students</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="assignments.php" class="portal-item">
                <div class="portal-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-tasks"></i>
                </div>
                <span>Assignments</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="questions.php" class="portal-item">
                <div class="portal-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-question-circle"></i>
                </div>
                <span>Questions</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="reports.php" class="portal-item">
                <div class="portal-icon bg-dark bg-opacity-10 text-dark">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <span>Reports</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="events.php" class="portal-item">
                <div class="portal-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span>Events</span>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="../logout.php" class="portal-item">
                <div class="portal-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<style>
.portal-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.25rem 0.5rem;
    background: white;
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    height: 100%;
}

.portal-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.08);
}

.portal-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
}

.portal-item span {
    font-size: 0.75rem;
    font-weight: 600;
    color: #4b5563;
    text-align: center;
}
</style>

<div class="row g-4">
    <!-- Stat Cards -->
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box bg-primary text-white">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>Total Students</h4>
            <div class="value"><?php echo $stats['students']; ?></div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box bg-success text-white">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h4>Faculty Members</h4>
            <div class="value"><?php echo $stats['faculty']; ?></div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box bg-info text-white">
                <i class="fas fa-book"></i>
            </div>
            <h4>Total Courses</h4>
            <div class="value"><?php echo $stats['courses']; ?></div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box bg-warning text-white">
                <i class="fas fa-comments"></i>
            </div>
            <h4>Feedback Received</h4>
            <div class="value"><?php echo $stats['feedback']; ?></div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-lg p-4">
            <h5 class="mb-4">Recent Feedback Trend</h5>
            <canvas id="feedbackChart" height="250"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-lg p-4">
            <h5 class="mb-4">Quick Actions</h5>
            <div class="d-grid gap-3">
                <a href="students.php" class="btn btn-outline-primary text-start">
                    <i class="fas fa-plus me-2"></i> Add New Student
                </a>
                <a href="faculty.php" class="btn btn-outline-primary text-start">
                    <i class="fas fa-plus me-2"></i> Add New Faculty
                </a>
                <a href="assignments.php" class="btn btn-outline-primary text-start">
                    <i class="fas fa-link me-2"></i> Assign Subject
                </a>
                <a href="reports.php" class="btn btn-primary text-start">
                    <i class="fas fa-file-alt me-2"></i> Generate Detailed Report
                </a>
            </div>
        </div>

        <!-- Mobile Quick Access -->
        <div class="card border-0 shadow-sm rounded-20 p-4 mt-4 overflow-hidden position-relative mobile-access-card">
            <div class="card-glow"></div>
            <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                <i class="fas fa-mobile-alt text-primary"></i>
                Mobile Access Center
            </h5>
            <?php 
                // Try to detect local IP, fallback to hardcoded if needed
                $detectedIp = getHostByName(getHostName());
                // If it's 127.0.0.1, it's not useful for mobile, so use the previously hardcoded one or a placeholder
                $localIp = ($detectedIp !== '127.0.0.1') ? $detectedIp : "192.168.29.151"; 
                
                // Construct the base URL for the project
                $currentPath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(dirname(__FILE__, 2)));
                $currentPath = str_replace('\\', '/', $currentPath);
                $mobileUrl = "http://" . $localIp . $currentPath . "/";
                
                $qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($mobileUrl);
            ?>
            <div class="text-center position-relative">
                <div class="qr-wrapper mb-4">
                    <img src="<?php echo $qrApi; ?>" alt="QR Code" class="img-fluid rounded-3 shadow-sm p-2 bg-white" style="max-width: 140px;">
                </div>
                
                <p class="small text-secondary mb-3">Scan this code with any mobile device on the <b>same Wi-Fi network</b> to access the feedback system.</p>
                
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm bg-light border-0" id="mobileLink" value="<?php echo $mobileUrl; ?>" readonly>
                    <button class="btn btn-primary btn-sm" onclick="copyLink()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>

                <div class="d-grid gap-2">
                    <a href="https://wa.me/?text=<?php echo urlencode("Open the Student Feedback System here: " . $mobileUrl); ?>" target="_blank" class="btn btn-sm btn-success bg-opacity-75 border-0">
                        <i class="fab fa-whatsapp me-2"></i> Share via WhatsApp
                    </a>
                </div>
            </div>
        </div>

        <style>
        .rounded-20 { border-radius: 20px; }
        .mobile-access-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid rgba(0,0,0,0.05) !important;
        }
        .card-glow {
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        .qr-wrapper {
            transition: transform 0.3s ease;
            display: inline-block;
        }
        .qr-wrapper:hover {
            transform: scale(1.05);
        }
        #mobileLink {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.7rem;
            color: #6c757d;
        }
        </style>

        <script>
        function copyLink() {
            var copyText = document.getElementById("mobileLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            // Visual feedback
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            icon.classList.replace('fa-copy', 'fa-check');
            btn.classList.replace('btn-primary', 'btn-success');
            
            setTimeout(() => {
                icon.classList.replace('fa-check', 'fa-copy');
                btn.classList.replace('btn-success', 'btn-primary');
            }, 2000);
        }
        </script>
    </div>
</div>

<?php

$extraJS = "
<script>
    const ctx = document.getElementById('feedbackChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
            datasets: [{
                label: 'Feedback Submissions',
                data: [12, 19, 3, 5, 2],
                borderColor: '#1a237e',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(26, 35, 126, 0.1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
";
include '../includes/footer.php';
?>
