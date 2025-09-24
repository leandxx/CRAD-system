<?php
include("includes/connection.php");

// Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql) === TRUE) {
    echo "Notifications table created successfully!<br>";
    
    // Insert sample notifications for testing
    $sample_notifications = [
        [1, "Welcome to CRAD System", "Welcome to the Center for Research and Development system!", "success"],
        [1, "System Update", "The system has been updated with new features.", "info"],
        [7, "New Student Registration", "A new student has registered in the system.", "info"],
        [8, "Proposal Deadline Reminder", "Don't forget to submit your research proposal by the deadline.", "warning"]
    ];
    
    $insert_sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    
    foreach ($sample_notifications as $notification) {
        $stmt->bind_param("isss", $notification[0], $notification[1], $notification[2], $notification[3]);
        $stmt->execute();
    }
    
    echo "Sample notifications inserted successfully!<br>";
    echo "<br><strong>Setup Complete!</strong><br>";
    echo "The notification system is now ready to use.<br>";
    echo "<a href='admin-pages/admin-dashboard.php'>Go to Admin Dashboard</a> | ";
    echo "<a href='student_pages/student.php'>Go to Student Dashboard</a>";
    
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>