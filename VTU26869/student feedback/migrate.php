<?php
require_once 'c:/xampp/htdocs/student feedback/config/config.php';
require_once 'c:/xampp/htdocs/student feedback/config/database.php';

try {
    $pdo->exec("ALTER TABLE feedback_responses ADD COLUMN category VARCHAR(50) DEFAULT 'faculty'");
    echo "Added category column.\n";
}
catch (PDOException $e) {
    echo $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE feedback_responses MODIFY COLUMN assignment_id INT NULL");
    echo "Made assignment_id nullable.\n";
}
catch (PDOException $e) {
    echo $e->getMessage() . "\n";
}

try {
    // Drop the old unique key, we will enforce it via PHP
    $pdo->exec("ALTER TABLE feedback_responses DROP INDEX student_id");
    echo "Dropped unique key.\n";
}
catch (PDOException $e) {
    echo $e->getMessage() . "\n";
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        location VARCHAR(255) NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "Created events table.\n";
}
catch (PDOException $e) {
    echo $e->getMessage() . "\n";
}

?>
