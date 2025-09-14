<?php
/**
 * Test Script for Defense Status Updates
 * This script helps test the defense status update functionality
 */

session_start();
include('../includes/connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    die('Access denied. Admin login required.');
}

$message = '';
$error = '';

// Get current status of all defenses
$query = "SELECT ds.id, ds.group_id, ds.defense_date, ds.start_time, ds.end_time, ds.status, 
                 g.name as group_name,
                 CONCAT(ds.defense_date, ' ', ds.end_time) as defense_end_datetime
          FROM defense_schedules ds 
          LEFT JOIN groups g ON ds.group_id = g.id 
          ORDER BY ds.defense_date, ds.start_time";
$result = mysqli_query($conn, $query);

$defenses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $defenses[] = $row;
}

// Check for overdue defenses
$current_datetime = date('Y-m-d H:i:s');
$overdue_defenses = [];

foreach ($defenses as $defense) {
    if ($defense['status'] == 'scheduled' && strtotime($defense['defense_end_datetime']) <= strtotime($current_datetime)) {
        $overdue_defenses[] = $defense;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_update'])) {
    // Test the update functionality
    $update_query = "UPDATE defense_schedules 
                    SET status = 'passed', 
                        updated_at = NOW() 
                    WHERE status = 'scheduled' 
                    AND CONCAT(defense_date, ' ', end_time) <= '$current_datetime'";
    
    if (mysqli_query($conn, $update_query)) {
        $affected_rows = mysqli_affected_rows($conn);
        $message = "Test successful! Updated $affected_rows overdue defense(s).";
        // Refresh the data
        header("Location: test_defense_status.php");
        exit();
    } else {
        $error = "Test failed: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Defense Status Updates</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .status-scheduled { color: #ffc107; font-weight: bold; }
        .status-passed { color: #28a745; font-weight: bold; }
        .status-completed { color: #6c757d; font-weight: bold; }
        .status-failed { color: #dc3545; font-weight: bold; }
        .overdue { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Defense Status Test</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="warning">
            <strong>Current Time:</strong> <?php echo $current_datetime; ?><br>
            <strong>Overdue Defenses:</strong> <?php echo count($overdue_defenses); ?>
        </div>
        
        <?php if (count($overdue_defenses) > 0): ?>
            <form method="POST">
                <button type="submit" name="test_update" class="btn btn-warning">
                    Test Update <?php echo count($overdue_defenses); ?> Overdue Defense(s)
                </button>
            </form>
        <?php endif; ?>
        
        <h2>All Defense Schedules</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Group</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Overdue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($defenses as $defense): ?>
                    <?php 
                    $is_overdue = $defense['status'] == 'scheduled' && strtotime($defense['defense_end_datetime']) <= strtotime($current_datetime);
                    $row_class = $is_overdue ? 'overdue' : '';
                    ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td><?php echo $defense['id']; ?></td>
                        <td><?php echo htmlspecialchars($defense['group_name']); ?></td>
                        <td><?php echo $defense['defense_date']; ?></td>
                        <td><?php echo $defense['start_time']; ?></td>
                        <td><?php echo $defense['end_time']; ?></td>
                        <td class="status-<?php echo $defense['status']; ?>"><?php echo strtoupper($defense['status']); ?></td>
                        <td><?php echo $is_overdue ? 'YES' : 'No'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px;">
            <a href="admin-defense.php" class="btn">← Back to Defense Management</a>
            <a href="setup_cron.php" class="btn">Setup Cron Job</a>
        </div>
    </div>
</body>
</html>