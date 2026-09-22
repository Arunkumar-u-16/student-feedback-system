<?php
$role = $_SESSION['role'] ?? '';
?>
<nav id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-graduation-cap me-2"></i>FEEDBACK</h3>
    </div>

    <ul class="list-unstyled components">
        <?php if ($role === 'admin'): ?>
            <li class="<?php echo($activePage == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo($activePage == 'courses') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/courses.php"><i class="fas fa-book"></i> Courses</a>
            </li>
            <li class="<?php echo($activePage == 'subjects') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/subjects.php"><i class="fas fa-book-open"></i> Subjects</a>
            </li>
            <li class="<?php echo($activePage == 'faculty') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/faculty.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a>
            </li>
            <li class="<?php echo($activePage == 'students') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/students.php"><i class="fas fa-user-graduate"></i> Students</a>
            </li>
            <li class="<?php echo($activePage == 'assignments') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/assignments.php"><i class="fas fa-tasks"></i> Assignments</a>
            </li>
            <li class="<?php echo($activePage == 'questions') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/questions.php"><i class="fas fa-question-circle"></i> Questions</a>
            </li>
            <li class="<?php echo($activePage == 'reports') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
            </li>
            <li class="<?php echo($activePage == 'events') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/events.php"><i class="fas fa-calendar-alt"></i> Events</a>
            </li>
        <?php
elseif ($role === 'student'): ?>
            <li class="<?php echo($activePage == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>student/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo($activePage == 'events') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>student/events.php"><i class="fas fa-calendar-alt"></i> Events</a>
            </li>
        <?php
elseif ($role === 'faculty'): ?>
            <li class="<?php echo($activePage == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>faculty/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo($activePage == 'reports') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>faculty/reports.php"><i class="fas fa-chart-line"></i> My Feedback</a>
            </li>
        <?php
endif; ?>
        
        <li>
            <a href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</nav>
