<?php
// Script to add notification integration to all PHP files
include('includes/connection.php');
include('includes/notification-helper.php');

// List of files to add notifications to
$files_to_update = [
    // Admin pages
    'admin-pages/admin-dashboard.php',
    'admin-pages/admin-defense.php', 
    'admin-pages/admin-timeline.php',
    'admin-pages/adviser-assignment.php',
    'admin-pages/panel-assignment.php',
    'admin-pages/manage-admins.php',
    
    // Student pages
    'student_pages/proposal.php',
    'student_pages/defense.php',
    'student_pages/student-profile.php',
    
    // Staff pages
    'staff-pages/staff-dashboard.php',
    'staff-pages/staff-defense.php',
    'staff-pages/staff-timeline.php',
    'staff-pages/staff-adviser-assignment.php',
    'staff-pages/staff-panel-assignment.php',
    
    // Auth pages
    'auth/register.php',
    'auth/student-login.php',
    'auth/admin-login.php'
];

echo "<h2>Adding notification integration to files...</h2>";

foreach ($files_to_update as $file) {
    $full_path = __DIR__ . '/' . $file;
    
    if (file_exists($full_path)) {
        $content = file_get_contents($full_path);
        
        // Check if notification includes are already present
        if (strpos($content, 'notification-helper.php') === false) {
            // Add notification includes after connection.php
            $content = str_replace(
                "include('../includes/connection.php');",
                "include('../includes/connection.php');\ninclude('../includes/notification-helper.php');",
                $content
            );
            
            // Also handle cases with different include patterns
            $content = str_replace(
                "include(\"../includes/connection.php\");",
                "include(\"../includes/connection.php\");\ninclude(\"../includes/notification-helper.php\");",
                $content
            );
        }
        
        // Write back to file
        file_put_contents($full_path, $content);
        echo "✓ Updated: $file<br>";
    } else {
        echo "✗ File not found: $file<br>";
    }
}

echo "<h3>Notification integration completed!</h3>";
echo "<p>All files have been updated with notification functionality.</p>";
?>