<?php
/**
 * Cron Job Setup Script
 * This script helps set up the automatic defense status update cron job
 */

// Check if admin is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    die('Access denied. Admin login required.');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_cron'])) {
    // Test the cron script
    $cron_script = __DIR__ . '/update_defense_status.php';
    
    if (file_exists($cron_script)) {
        $output = [];
        $return_code = 0;
        exec("php $cron_script 2>&1", $output, $return_code);
        
        if ($return_code === 0) {
            $message = "Cron script test successful! Output: " . implode("\n", $output);
        } else {
            $error = "Cron script test failed. Output: " . implode("\n", $output);
        }
    } else {
        $error = "Cron script not found at: $cron_script";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Defense Status Cron Job</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; margin: 15px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Defense Status Cron Job Setup</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <h2>What this does:</h2>
        <p>This cron job automatically updates defense statuses from 'scheduled' to 'passed' (ready for evaluation) when the defense time has ended. This ensures that groups don't get stuck in the scheduled status after their defense time passes.</p>
        
        <h2>Setup Instructions:</h2>
        
        <h3>1. Test the Script</h3>
        <p>First, test if the cron script works properly:</p>
        <form method="POST">
            <button type="submit" name="test_cron" class="btn btn-success">Test Cron Script</button>
        </form>
        
        <h3>2. Add to Crontab</h3>
        <p>Add this line to your server's crontab to run the script every minute:</p>
        <div class="code">
            * * * * * /usr/bin/php <?php echo __DIR__; ?>/update_defense_status.php >> /var/log/defense_status.log 2>&1
        </div>
        
        <h3>3. Manual Crontab Setup</h3>
        <p>To edit the crontab, run this command on your server:</p>
        <div class="code">
            crontab -e
        </div>
        
        <h3>4. Alternative: Web-based Cron</h3>
        <p>If you can't access the server directly, you can set up a web-based cron service to call this URL every minute:</p>
        <div class="code">
            <?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/update_defense_status.php'; ?>
        </div>
        
        <h3>5. Manual Update Button</h3>
        <p>As a backup, you can also use the "Update Overdue" button in the admin-defense page to manually update overdue defenses.</p>
        
        <h2>Logs</h2>
        <p>The script logs all activities. Check the server error logs or the log file specified in the cron command for debugging information.</p>
        
        <div style="margin-top: 30px;">
            <a href="admin-defense.php" class="btn">← Back to Defense Management</a>
        </div>
    </div>
</body>
</html>