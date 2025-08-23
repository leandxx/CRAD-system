<?php
session_start();
include('../includes/connection.php');

// Include PHPMailer
require '../assets/PHPMailer/src/Exception.php';
require '../assets/PHPMailer/src/PHPMailer.php';
require '../assets/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Handle CRUD operations
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        // Add new panel member - with validation
        $first_name = trim($conn->real_escape_string($_POST['first_name'] ?? ''));
        $last_name = trim($conn->real_escape_string($_POST['last_name'] ?? ''));
        $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
        $specialization = trim($conn->real_escape_string($_POST['specialization'] ?? ''));
        $status = $conn->real_escape_string($_POST['status'] ?? 'active');
        
        // Validate inputs
        if (empty($first_name)) {
            $error = "First name is required";
        } elseif (empty($last_name)) {
            $error = "Last name is required";
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Valid email is required";
        } elseif (empty($specialization)) {
            $error = "Specialization is required";
        } else {
            // Check if email already exists
            $check_sql = "SELECT id FROM panel_members WHERE email = '$email'";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error = "A panel member with this email already exists";
            } else {
                $sql = "INSERT INTO panel_members (first_name, last_name, email, specialization, status) 
                        VALUES ('$first_name', '$last_name', '$email', '$specialization', '$status')";
                
                if ($conn->query($sql)) {
                    $success = "Panel member added successfully";
                    // Clear POST data to prevent form repopulation
                    unset($_POST);
                } else {
                    $error = "Error adding panel member: " . $conn->error;
                }
            }
        }
    }
    elseif ($action == 'edit') {
        // Edit panel member
        $id = intval($_POST['id']);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $specialization = $conn->real_escape_string($_POST['specialization']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Validate inputs
        if (empty($first_name) || empty($last_name) || empty($email) || empty($specialization)) {
            $error = "All fields are required";
        } else {
            // Check if email already exists for another panel member
            $check_sql = "SELECT id FROM panel_members WHERE email = '$email' AND id != $id";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error = "A panel member with this email already exists";
            } else {
                $sql = "UPDATE panel_members SET 
                        first_name = '$first_name',
                        last_name = '$last_name',
                        email = '$email',
                        specialization = '$specialization',
                        status = '$status'
                        WHERE id = $id";
                
                if ($conn->query($sql)) {
                    $success = "Panel member updated successfully";
                    // Redirect to clear the edit_id from URL
                    header("Location: panel-management.php?success=Panel+member+updated+successfully");
                    exit();
                } else {
                    $error = "Error updating panel member: " . $conn->error;
                }
            }
        }
    }
    elseif ($action == 'delete') {
        // Delete panel member
        $id = intval($_POST['id']);
        
        $sql = "DELETE FROM panel_members WHERE id = $id";
        
        if ($conn->query($sql)) {
            $success = "Panel member deleted successfully";
        } else {
            $error = "Error deleting panel member: " . $conn->error;
        }
    }
    elseif ($action == 'send_invitation') {
        $panel_ids = $_POST['panel_ids'] ?? [];
        $defense_id = intval($_POST['defense_id']);
        $subject = $conn->real_escape_string($_POST['subject']);
        $message = $conn->real_escape_string($_POST['message']);
        
        if (empty($panel_ids)) {
            $error = "Please select at least one panel member";
        } else {
            $sent_count = 0;
            $error_count = 0;
            $error_details = [];
            
            foreach ($panel_ids as $panel_id) {
                $panel_id = intval($panel_id);
                
                // Generate unique token
                $token = bin2hex(random_bytes(32));
                
                // Check if invitation already exists
                $check_query = "SELECT id FROM panel_invitations WHERE defense_id = $defense_id AND panel_id = $panel_id";
                $check_result = $conn->query($check_query);
                
                if ($check_result->num_rows == 0) {
                    // Insert invitation
                    $invite_query = "INSERT INTO panel_invitations (defense_id, panel_id, token, status, invited_at) 
                                VALUES ($defense_id, $panel_id, '$token', 'pending', NOW())";
                    
                    if ($conn->query($invite_query)) {
                        // Get panel member details
                        $panel_query = "SELECT * FROM panel_members WHERE id = $panel_id";
                        $panel_result = $conn->query($panel_query);
                        $panel = $panel_result->fetch_assoc();
                        
                        // Get defense details
                        $defense_query = "SELECT ds.*, g.name as group_name, p.title as proposal_title 
                                        FROM defense_schedules ds 
                                        JOIN groups g ON ds.group_id = g.id 
                                        JOIN proposals p ON g.id = p.group_id 
                                        WHERE ds.id = $defense_id";
                        $defense_result = $conn->query($defense_query);
                        $defense = $defense_result->fetch_assoc();
                        
                        // Send email using PHPMailer
                        $mail = new PHPMailer(true);
                        
                        try {
                            // Server settings (configure these based on your email provider)
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'girayjohnmarvic09@gmail.com'; // SMTP username
                            $mail->Password   = 'vyxk qizb chng pjec'; // SMTP password (use app password for Gmail)
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;
                            
                            // Recipients
                            $mail->setFrom('noreply@example.com', 'Thesis Defense System');
                            $mail->addAddress($panel['email'], $panel['first_name'] . ' ' . $panel['last_name']);
                            
                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = $subject;
                            
                            // Create HTML email content
                            $emailContent = "
                                <html>
                                <body>
                                    <p>Dear {$panel['first_name']},</p>
                                    <p>{$message}</p>
                                    <p><strong>Defense Details:</strong></p>
                                    <ul>
                                        <li><strong>Title:</strong> {$defense['proposal_title']}</li>
                                        <li><strong>Date:</strong> " . date('M j, Y', strtotime($defense['defense_date'])) . "</li>
                                        <li><strong>Time:</strong> " . date('g:i A', strtotime($defense['start_time'])) . "</li>
                                    </ul>
                                    <p>Please respond to this invitation by clicking one of the links below:</p>
                                    <p>
                                        <a href='http://localhost/CRAD-system/admin-pages/confirm-invitation.php?token=$token&status=accepted' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; border-radius: 5px; margin-right: 10px;'>Accept Invitation</a>
                                        <a href='http://localhost/CRAD-system/admin-pages/confirm-invitation.php?token=$token&status=rejected' style='background-color: #f44336; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; border-radius: 5px;'>Decline Invitation</a>
                                    </p>
                                    <p>Best regards,<br>Thesis Coordinator</p>
                                </body>
                                </html>
                            ";
                            
                            $mail->Body = $emailContent;
                            
                            // Plain text version for non-HTML email clients
                            $mail->AltBody = "Dear {$panel['first_name']},\n\n{$message}\n\nDefense: {$defense['proposal_title']}\nDate: " . date('M j, Y', strtotime($defense['defense_date'])) . "\nTime: " . date('g:i A', strtotime($defense['start_time'])) . "\n\nAccept: http://localhost/CRAD-system/confirm-invitation.php?token=$token&status=accepted\nDecline: http://localhost/CRAD-system/confirm-invitation.php?token=$token&status=rejected\n\nBest regards,\nThesis Coordinator";
                            
                            $mail->send();
                            $sent_count++;
                        } catch (Exception $e) {
                            $error_count++;
                            $error_details[] = "Message could not be sent to {$panel['email']}. Mailer Error: {$mail->ErrorInfo}";
                            error_log("Message could not be sent to {$panel['email']}. Mailer Error: {$mail->ErrorInfo}");
                        }
                    }
                }
            }
            
            if ($error_count > 0) {
                $error = "Sent {$sent_count} invitations, but failed to send {$error_count}. " . implode("; ", $error_details);
            } else {
                $success = "Successfully sent {$sent_count} invitations!";
            }
        }
    }
}

// Get panel member data for editing
$editData = [];
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $query = "SELECT * FROM panel_members WHERE id = $edit_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    } else {
        header("Location: panel-management.php?error=Panel+member+not+found");
        exit();
    }
}

// Get all panel members
$panel_query = "SELECT * FROM panel_members ORDER BY last_name, first_name";
$panel_result = $conn->query($panel_query);
$panel_members = [];

while ($row = $panel_result->fetch_assoc()) {
    $panel_members[] = $row;
}

// Get all defense schedules
$defense_query = "SELECT ds.*, g.name as group_name, p.title as proposal_title 
                FROM defense_schedules ds 
                JOIN groups g ON ds.group_id = g.id 
                JOIN proposals p ON g.id = p.group_id 
                WHERE ds.defense_date >= CURDATE()
                ORDER BY ds.defense_date DESC";
$defense_result = $conn->query($defense_query);
$defenses = [];

while ($row = $defense_result->fetch_assoc()) {
    $defenses[] = $row;
}

// Get invitation statistics
$stats_query = "SELECT 
    COUNT(*) as total_invitations,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM panel_invitations";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Create panel_invitations table if it doesn't exist
$create_table_query = "
CREATE TABLE IF NOT EXISTS panel_invitations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    defense_id INT NOT NULL,
    panel_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    invited_at DATETIME NOT NULL,
    responded_at DATETIME NULL,
    FOREIGN KEY (defense_id) REFERENCES defense_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (panel_id) REFERENCES panel_members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_invitation (defense_id, panel_id)
)";
$conn->query($create_table_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Management System</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notification-dot {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        [multiple] {
            height: auto;
            min-height: 42px;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            transition: opacity 200ms ease-in-out;
        }
        .modal-content {
            transform: translateY(-20px);
            transition: transform 200ms ease-in-out, opacity 200ms ease-in-out;
        }
        .modal-active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content-active {
            transform: translateY(0);
        }
        .email-preview {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            background-color: #f8fafc;
            border-radius: 0.375rem;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-accepted { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #b91c1c; }
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .email-template {
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 1rem;
            background-color: #f8fafc;
            margin-top: 0.5rem;
        }
        .email-template p {
            margin-bottom: 1rem;
        }
        .email-template ul {
            list-style-type: disc;
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        .email-template a.button {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            margin-right: 0.5rem;
        }
        .accept-button {
            background-color: #4CAF50;
            color: white;
        }
        .decline-button {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">
    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        
        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6">
            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $success; ?></span>
                    <button onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                    <button onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Total Panel Members</p>
                        <h3 class="text-2xl font-bold"><?php echo count($panel_members); ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-primary text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Pending Invitations</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['pending'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-warning text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Accepted</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['accepted'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-success text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Rejected</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['rejected'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-times-circle text-danger text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Add Panel Member Card -->
                <div class="bg-white rounded-lg shadow p-6 card-hover">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-plus text-blue-500 mr-2"></i>
                        Quick Add Panel Member
                    </h2>
                    
                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required">First Name</label>
                                <input type="text" name="first_name" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required">Last Name</label>
                                <input type="text" name="last_name" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Email</label>
                            <input type="email" name="email" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Specialization</label>
                            <input type="text" name="specialization" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                            <i class="fas fa-plus mr-1"></i> Add Panel Member
                        </button>
                    </form>
                </div>

                <!-- Send Invitation Card -->
                <div class="bg-white rounded-lg shadow p-6 card-hover">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-paper-plane text-green-500 mr-2"></i>
                        Send Panel Invitation
                    </h2>
                    
                    <form action="" method="POST" class="space-y-4" id="invitationForm">
                        <input type="hidden" name="action" value="send_invitation">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Select Defense</label>
                            <select name="defense_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary" id="defenseSelect">
                                <option value="">-- Select a defense --</option>
                                <?php foreach ($defenses as $defense): ?>
                                <option value="<?php echo $defense['id']; ?>" data-title="<?php echo htmlspecialchars($defense['proposal_title']); ?>" data-date="<?php echo $defense['defense_date']; ?>" data-time="<?php echo $defense['start_time']; ?>">
                                    <?php echo $defense['proposal_title'] . ' - ' . date('M j, Y', strtotime($defense['defense_date'])); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Select Panel Members</label>
                            <select name="panel_ids[]" multiple required 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary h-32" id="panelSelect">
                                <?php foreach ($panel_members as $panel): ?>
                                <option value="<?php echo $panel['id']; ?>" data-email="<?php echo $panel['email']; ?>" data-name="<?php echo htmlspecialchars($panel['first_name'] . ' ' . $panel['last_name']); ?>">
                                    <?php echo $panel['first_name'] . ' ' . $panel['last_name'] . ' (' . $panel['specialization'] . ')'; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple panel members</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Email Subject</label>
                            <input type="text" name="subject" value="Panel Invitation for Thesis Defense" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary" id="emailSubject">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Message</label>
                            <textarea name="message" rows="5" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary" id="emailMessage">Dear Panel Member,

You have been invited to serve on a thesis defense panel. Please click the link below to confirm your availability.

Thank you for your participation.

Best regards,
Thesis Coordinator</textarea>
                        </div>
                        
                        <!-- Email Preview Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Preview</label>
                            <div class="email-template mt-2" id="emailPreview">
                                <p><strong>Subject:</strong> <span id="previewSubject">Panel Invitation for Thesis Defense</span></p>
                                <div id="previewContent">
                                    <p>Dear <span id="previewName">Panel Member</span>,</p>
                                    <p>You have been invited to serve on a thesis defense panel. Please click the link below to confirm your availability.</p>
                                    <p>Thank you for your participation.</p>
                                    <p><strong>Defense Details:</strong></p>
                                    <ul>
                                        <li><strong>Title:</strong> <span id="previewTitle">Selected defense title will appear here</span></li>
                                        <li><strong>Date:</strong> <span id="previewDate">Selected date will appear here</span></li>
                                        <li><strong>Time:</strong> <span id="previewTime">Selected time will appear here</span></li>
                                    </ul>
                                    <p>Please respond to this invitation by clicking one of the links below:</p>
                                    <p>
                                        <a href="../admin-pages/confirm-result.php" class="button accept-button">Accept Invitation</a>
                                        <a href="#" class="button decline-button">Decline Invitation</a>
                                    </p>
                                    <p>Best regards,<br>Thesis Coordinator</p>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                            <i class="fas fa-paper-plane mr-1"></i> Send Invitations
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel List -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-users mr-2 text-blue-500"></i>
                        Panel Members (<?php echo count($panel_members); ?>)
                    </h2>
                    <div class="flex space-x-2">
                        <button onclick="window.location.reload()" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-300 transition">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($panel_members) > 0): ?>
                                <?php foreach ($panel_members as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <i class="fas fa-user text-indigo-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $row['specialization']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $row['email']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="?edit_id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this panel member?')" class="text-red-600 hover:text-red-900" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        <i class="fas fa-users text-gray-300 text-3xl mb-2"></i><br>
                                        No panel members found. Add your first panel member above.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Invitations -->
            <div class="bg-white shadow rounded-lg p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-history mr-2 text-blue-500"></i>
                    Recent Invitations
                </h2>
                
                <?php
                $recent_query = "SELECT pi.*, pm.first_name, pm.last_name, pm.email, 
                               ds.defense_date, p.title as proposal_title,
                               DATEDIFF(NOW(), pi.invited_at) as days_ago
                               FROM panel_invitations pi
                               JOIN panel_members pm ON pi.panel_id = pm.id
                               JOIN defense_schedules ds ON pi.defense_id = ds.id
                               JOIN proposals p ON ds.group_id = p.group_id
                               ORDER BY pi.invited_at DESC LIMIT 5";
                $recent_result = $conn->query($recent_query);
                $recent_invitations = [];
                
                while ($row = $recent_result->fetch_assoc()) {
                    $recent_invitations[] = $row;
                }
                ?>
                
                <?php if (count($recent_invitations) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Panel Member</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Defense</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invited</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_invitations as $invite): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $invite['first_name'] . ' ' . $invite['last_name']; ?></div>
                                        <div class="text-sm text-gray-500"><?php echo $invite['email']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $invite['proposal_title']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo date('M j, Y', strtotime($invite['defense_date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">  
                                          <span class="status-badge status-<?php echo $invite['status']; ?>">
                                            <?php echo ucfirst($invite['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php 
                                            $daysAgo = $invite['days_ago'];
                                            if ($daysAgo == 0) {
                                                echo 'Today';
                                            } elseif ($daysAgo == 1) {
                                                echo 'Yesterday';
                                            } else {
                                                echo $daysAgo . ' days ago';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-envelope text-gray-300 text-4xl mb-3"></i>
                        <p>No invitations sent yet. Send your first invitation using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <?php if (!empty($editData)): ?>
    <div class="fixed inset-0 overflow-y-auto z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200" id="editModal">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-edit text-blue-500 mr-2"></i>
                        Edit Panel Member
                    </h3>
                    
                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required">First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($editData['first_name']); ?>" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required">Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($editData['last_name']); ?>" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($editData['email']); ?>" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Specialization</label>
                            <input type="text" name="specialization" value="<?php echo htmlspecialchars($editData['specialization']); ?>" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Status</label>
                            <select name="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                                <option value="active" <?php echo $editData['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $editData['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update Panel Member
                            </button>
                            <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Show modal if editing
        <?php if (!empty($editData)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editModal');
            modal.classList.add('modal-active');
            modal.classList.remove('opacity-0');
            modal.classList.remove('pointer-events-none');
            
            const modalContent = modal.querySelector('.modal-content');
            modalContent.classList.add('modal-content-active');
        });
        <?php endif; ?>

        function closeModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('modal-active');
            modal.classList.add('opacity-0');
            modal.classList.add('pointer-events-none');
            
            const modalContent = modal.querySelector('.modal-content');
            modalContent.classList.remove('modal-content-active');
            
            // Redirect to clear the edit_id parameter
            setTimeout(() => {
                window.location.href = 'panel-management.php';
            }, 200);
        }

        // Email preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const defenseSelect = document.getElementById('defenseSelect');
            const panelSelect = document.getElementById('panelSelect');
            const emailSubject = document.getElementById('emailSubject');
            const emailMessage = document.getElementById('emailMessage');
            
            const previewSubject = document.getElementById('previewSubject');
            const previewName = document.getElementById('previewName');
            const previewTitle = document.getElementById('previewTitle');
            const previewDate = document.getElementById('previewDate');
            const previewTime = document.getElementById('previewTime');
            
            // Update preview when defense selection changes
            defenseSelect.addEventListener('change', updatePreview);
            
            // Update preview when panel selection changes
            panelSelect.addEventListener('change', updatePreview);
            
            // Update preview when subject changes
            emailSubject.addEventListener('input', function() {
                previewSubject.textContent = this.value;
            });
            
            // Update preview when message changes
            emailMessage.addEventListener('input', updatePreview);
            
            function updatePreview() {
                // Update defense details
                const selectedDefense = defenseSelect.options[defenseSelect.selectedIndex];
                if (selectedDefense && selectedDefense.value) {
                    previewTitle.textContent = selectedDefense.getAttribute('data-title');
                    previewDate.textContent = new Date(selectedDefense.getAttribute('data-date')).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                    
                    const timeValue = selectedDefense.getAttribute('data-time');
                    if (timeValue) {
                        const timeParts = timeValue.split(':');
                        let hours = parseInt(timeParts[0]);
                        const minutes = timeParts[1];
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12;
                        hours = hours ? hours : 12; // the hour '0' should be '12'
                        previewTime.textContent = hours + ':' + minutes + ' ' + ampm;
                    }
                }
                
                // Update panel member name
                const selectedPanel = panelSelect.options[panelSelect.selectedIndex];
                if (selectedPanel && selectedPanel.value) {
                    previewName.textContent = selectedPanel.getAttribute('data-name');
                } else {
                    previewName.textContent = 'Panel Member';
                }
            }
            
            // Initialize the preview
            updatePreview();
            
            // Add confirmation for delete actions
            const deleteButtons = document.querySelectorAll('form button[type="submit"]');
            deleteButtons.forEach(button => {
                const form = button.closest('form');
                if (form && form.querySelector('input[name="action"]').value === 'delete') {
                    button.addEventListener('click', function(e) {
                        if (!confirm('Are you sure you want to delete this panel member?')) {
                            e.preventDefault();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>