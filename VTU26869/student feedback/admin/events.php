<?php
// admin/events.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

check_auth('admin');

$pageTitle = "Manage Events";
$activePage = 'events';

$success = '';
$error = '';

// Handle Add/Edit Event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $location = $_POST['location'];

        if ($_POST['action'] === 'add') {
            try {
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $event_date, $event_time, $location]);
                $success = "Event added successfully!";
            } catch (PDOException $e) {
                $error = "Error adding event: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'];
            try {
                $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ? WHERE id = ?");
                $stmt->execute([$title, $description, $event_date, $event_time, $location, $id]);
                $success = "Event updated successfully!";
            } catch (PDOException $e) {
                $error = "Error updating event: " . $e->getMessage();
            }
        }
    }
}

// Handle Delete Event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Event deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting event: " . $e->getMessage();
    }
}

// Fetch all events
$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC, event_time DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Manage College Events</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
            <i class="fas fa-plus me-2"></i>Add New Event
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Event Details</th>
                            <th class="py-3">Date & Time</th>
                            <th class="py-3">Location</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3 d-block opacity-25"></i>
                                    No events found. Add your first event!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($event['title']); ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 300px;">
                                            <?php echo htmlspecialchars($event['description']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="far fa-calendar-alt me-2 text-primary"></i><?php echo date('M d, Y', strtotime($event['event_date'])); ?></div>
                                        <div class="small text-muted"><i class="far fa-clock me-2"></i><?php echo date('h:i A', strtotime($event['event_time'])); ?></div>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i><?php echo htmlspecialchars($event['location']); ?>
                                    </td>
                                    <td class="text-end px-4">
                                        <button class="btn btn-sm btn-outline-primary me-2 edit-event" 
                                                data-id="<?php echo $event['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($event['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($event['description']); ?>"
                                                data-date="<?php echo $event['event_date']; ?>"
                                                data-time="<?php echo $event['event_time']; ?>"
                                                data-location="<?php echo htmlspecialchars($event['location']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this event?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Event Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="event_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Event Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="event_date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="event_time" id="edit_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="edit_location" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJS = "
<script>
    document.querySelectorAll('.edit-event').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_title').value = this.dataset.title;
            document.getElementById('edit_description').value = this.dataset.description;
            document.getElementById('edit_date').value = this.dataset.date;
            document.getElementById('edit_time').value = this.dataset.time;
            document.getElementById('edit_location').value = this.dataset.location;
            
            var editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
            editModal.show();
        });
    });
</script>
";
include '../includes/footer.php';
?>
