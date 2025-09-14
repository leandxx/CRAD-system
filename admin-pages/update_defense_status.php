<?php
/**
 * Defense Status Update Script
 * This script automatically updates defense status from 'scheduled' to 'passed' 
 * when the defense time has ended. It should be run as a cron job every minute.
 * 
 * Usage: php update_defense_status.php
 * Cron: * * * * * /usr/bin/php /path/to/update_defense_status.php
 */

// Set timezone
date_default_timezone_set('Asia/Manila'); // Adjust timezone as needed

// Include database connection
include('../includes/connection.php');
include('../includes/notification-helper.php');

// Log function for debugging
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] Defense Status Update: $message");
}

logMessage("Starting defense status update check...");

// Get current datetime
$current_datetime = date('Y-m-d H:i:s');
$current_timestamp = strtotime($current_datetime);

// Query to find all scheduled defenses that should be moved to passed status
$query = "SELECT ds.id, ds.group_id, ds.defense_date, ds.end_time, ds.status, g.name as group_name
          FROM defense_schedules ds 
          LEFT JOIN groups g ON ds.group_id = g.id 
          WHERE ds.status = 'scheduled' 
          AND CONCAT(ds.defense_date, ' ', ds.end_time) <= '$current_datetime'";

$result = mysqli_query($conn, $query);

if (!$result) {
    logMessage("Database error: " . mysqli_error($conn));
    exit(1);
}

$updated_count = 0;
$error_count = 0;

while ($defense = mysqli_fetch_assoc($result)) {
    $defense_id = $defense['id'];
    $group_name = $defense['group_name'];
    $defense_datetime = $defense['defense_date'] . ' ' . $defense['end_time'];
    
    logMessage("Processing defense ID $defense_id for group '$group_name' (ended at: $defense_datetime)");
    
    // Update status to passed (ready for evaluation)
    $update_query = "UPDATE defense_schedules 
                    SET status = 'passed', 
                        updated_at = NOW() 
                    WHERE id = '$defense_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $updated_count++;
        
        // Send notification
        $notification_title = "Defense Ready for Evaluation";
        $notification_message = "The defense for group '$group_name' has concluded and is ready for evaluation.";
        
        try {
            notifyAllUsers($conn, $notification_title, $notification_message, 'info');
            logMessage("Notification sent for defense ID $defense_id");
        } catch (Exception $e) {
            logMessage("Failed to send notification for defense ID $defense_id: " . $e->getMessage());
        }
        
        logMessage("Successfully updated defense ID $defense_id to 'passed' status");
    } else {
        $error_count++;
        logMessage("Error updating defense ID $defense_id: " . mysqli_error($conn));
    }
}

// Log summary
logMessage("Defense status update completed. Updated: $updated_count, Errors: $error_count");

// Close database connection
mysqli_close($conn);

// Exit with success code
exit(0);
?>