<?php
// student/events.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('student');

$pageTitle = "Upcoming Events";
$activePage = 'events';

// Fetch upcoming events (current date onwards)
$events = $pdo->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC, event_time ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark">College Events</h2>
            <p class="text-muted">Stay updated with the latest happenings in your college.</p>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($events)): ?>
            <div class="col-12 text-center py-5">
                <div class="card border-0 shadow-sm rounded-lg p-5">
                    <i class="fas fa-calendar-day fa-4x mb-3 text-light"></i>
                    <h4 class="text-muted">No upcoming events scheduled</h4>
                    <p class="text-muted">Check back later for new updates!</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm event-card overflow-hidden">
                        <div class="event-banner bg-primary text-white p-4 position-relative overflow-hidden">
                            <div class="banner-circle"></div>
                            <div class="position-relative z-1">
                                <span class="badge bg-white text-primary mb-2">Upcoming Event</span>
                                <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($event['title']); ?></h4>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4 event-desc">
                                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                            </p>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-light text-primary me-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Date</div>
                                    <div class="fw-bold"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-light text-success me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Time</div>
                                    <div class="fw-bold"><?php echo date('h:i A', strtotime($event['event_time'])); ?></div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-0">
                                <div class="icon-circle bg-light text-danger me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Location</div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($event['location']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-4 pt-0">
                            <button class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                                I'm Interested
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.event-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 20px;
}

.event-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
}

.event-banner {
    min-height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.banner-circle {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.event-desc {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.6;
}

.rounded-lg {
    border-radius: 20px;
}

.z-1 { z-index: 1; }
</style>

<?php
include '../includes/footer.php';
?>
