<?php
include("includes/connection.php");
include("includes/notification-helper.php");

echo "<h2>Testing Notification System</h2>";

// Test creating a notification for user ID 1 (admin)
$test_result = notifyUser($conn, 1, 
    "Test Notification", 
    "This is a test notification to verify the system is working.", 
    "info"
);

if ($test_result) {
    echo "<p style='color: green;'>✅ Test notification created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to create test notification.</p>";
    echo "<p>Error: " . mysqli_error($conn) . "</p>";
}

// Test notifying all admins
$admin_result = notifyAllAdmins($conn, 
    "System Test", 
    "Testing admin notification broadcast.", 
    "success"
);

if ($admin_result) {
    echo "<p style='color: green;'>✅ Admin broadcast notification sent!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send admin broadcast.</p>";
}

echo "<br><a href='admin-pages/admin-dashboard.php'>Go to Admin Dashboard</a>";
echo " | <a href='student_pages/student.php'>Go to Student Dashboard</a>";
?>