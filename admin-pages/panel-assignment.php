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
        $program = $conn->real_escape_string($_POST['program'] ?? 'general');
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
                $sql = "INSERT INTO panel_members (first_name, last_name, email, specialization, program, status) 
                        VALUES ('$first_name', '$last_name', '$email', '$specialization', '$program', '$status')";
                
                if ($conn->query($sql)) {
                    $success = "Panel member added successfully";
                    // Redirect to avoid resubmission
                    header("Location: panel-assignment.php?success=" . urlencode($success));
                    exit();
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
        $program = $conn->real_escape_string($_POST['program']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Validate inputs
        if (empty($first_name) || empty($last_name) || empty($email) || empty($specialization)) {
            $error = "All fields are required";
            // Keep $editData populated so the modal stays open with the entered data
            $editData = [
                'id' => $id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'specialization' => $specialization,
                'program' => $program,
                'status' => $status
            ];
        } else {
            // Check if email already exists for another panel member
            $check_sql = "SELECT id FROM panel_members WHERE email = '$email' AND id != $id";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error = "A panel member with this email already exists";
                $editData = [
                    'id' => $id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'specialization' => $specialization,
                    'program' => $program,
                    'status' => $status
                ];
            } else {
                $sql = "UPDATE panel_members SET 
                        first_name = '$first_name',
                        last_name = '$last_name',
                        email = '$email',
                        specialization = '$specialization',
                        program = '$program',
                        status = '$status'
                        WHERE id = $id";
                
                if ($conn->query($sql)) {
                    $success = "Panel member updated successfully";
                    // Redirect to remove edit_id from URL
                    header("Location: panel-assignment.php?success=" . urlencode($success));
                    exit();
                } else {
                    $error = "Error updating panel member: " . $conn->error;
                    $editData = [
                        'id' => $id,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'specialization' => $specialization,
                        'program' => $program,
                        'status' => $status
                    ];
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
            header("Location: panel-assignment.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Error deleting panel member: " . $conn->error;
        }
    }
    elseif ($action == 'send_invitation') {
        $panel_ids = $_POST['panel_ids'] ?? [];
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
                
                
                $invite_query = "INSERT INTO panel_invitations (panel_id, token, status, invited_at) 
                            VALUES ($panel_id, '$token', 'pending', NOW())";
                
                if ($conn->query($invite_query)) {
                    // Get panel member details
                    $panel_query = "SELECT * FROM panel_members WHERE id = $panel_id";
                    $panel_result = $conn->query($panel_query);
                    $panel = $panel_result->fetch_assoc();
                    
                    // Send email using PHPMailer
                    $mail = new PHPMailer(true);
                    
                    try {
                        // Server settings (configure these based on your email provider)
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'cl6.crad@gmail.com'; // SMTP username
                        $mail->Password   = 'fafn bcnq rcqe qgke'; // SMTP password (use app password for Gmail)
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
                        $mail->AltBody = "Dear {$panel['first_name']},\n\n{$message}\n\nAccept: http://localhost/CRAD-system/confirm-invitation.php?token=$token&status=accepted\nDecline: http://localhost/CRAD-system/confirm-invitation.php?token=$token&status=rejected\n\nBest regards,\nThesis Coordinator";
                        
                        $mail->send();
                        $sent_count++;
                    } catch (Exception $e) {
                        $error_count++;
                        $error_details[] = "Message could not be sent to {$panel['email']}. Mailer Error: {$mail->ErrorInfo}";
                        error_log("Message could not be sent to {$panel['email']}. Mailer Error: {$mail->ErrorInfo}");
                    }
                }
            }
            
            if ($error_count > 0) {
                $error = "Sent {$sent_count} invitations, but failed to send {$error_count}. " . implode("; ", $error_details);
                header("Location: panel-assignment.php?error=" . urlencode($error));
                exit();
            } else {
                $success = "Successfully sent {$sent_count} invitations!";
                header("Location: panel-assignment.php?success=" . urlencode($success));
                exit();
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
        // Redirect to the same page with an error message
        header("Location: panel-assignment.php?error=Panel+member+not+found");
        exit();
    }
}

// Define bachelor programs
$programs = [
    'bsit' => 'BS Information Technology',
    'bscs' => 'BS Computer Science',
    'bsis' => 'BS Information Systems',
    'bsemc' => 'BS Entertainment and Multimedia Computing',
    'general' => 'General (All Programs)'
];

// Get all panel members grouped by program
$panel_query = "SELECT * FROM panel_members ORDER BY program, last_name, first_name";
$panel_result = $conn->query($panel_query);
$panel_members_by_program = [];

while ($row = $panel_result->fetch_assoc()) {
    $program = $row['program'];
    if (!isset($panel_members_by_program[$program])) {
        $panel_members_by_program[$program] = [];
    }
    $panel_members_by_program[$program][] = $row;
}

// Get all panel members for dropdowns
$all_panel_members = [];
foreach ($panel_members_by_program as $program => $members) {
    $all_panel_members = array_merge($all_panel_members, $members);
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

// Get program-specific stats
$program_stats_query = "SELECT 
    pm.program,
    COUNT(pm.id) as total_members,
    COUNT(pi.id) as total_invitations,
    SUM(CASE WHEN pi.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN pi.status = 'accepted' THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN pi.status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM panel_members pm
    LEFT JOIN panel_invitations pi ON pm.id = pi.panel_id
    GROUP BY pm.program";
$program_stats_result = $conn->query($program_stats_query);
$program_stats = [];

while ($row = $program_stats_result->fetch_assoc()) {
    $program_stats[$row['program']] = $row;
}

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
        #sendInvitationModal .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }
        .program-tab {
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            margin-right: 0.5rem;
            transition: all 0.2s;
        }
        .program-tab.active {
            background-color: #3b82f6;
            color: white;
        }
        .program-content {
            display: none;
        }
        .program-content.active {
            display: block;
        }
        .program-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .bsit-badge { background-color: #e0f2fe; color: #0369a1; }
        .bscs-badge { background-color: #fce7f3; color: #be185d; }
        .bsis-badge { background-color: #dcfce7; color: #166534; }
        .bsemc-badge { background-color: #fef3c7; color: #92400e; }
        .general-badge { background-color: #e5e7eb; color: #374151; }
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

            <div class="mb-10">
     <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">📊 Panel Assignment Status</h2>
        <button id="toggleButton" 
            class="flex items-center gap-2 px-4 py-1.5 border border-indigo-500 text-indigo-600 rounded-full text-sm font-medium hover:bg-indigo-50 transition">
            See All 
            <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Total Panel Members</p>
                        <h3 class="text-2xl font-bold"><?php echo count($all_panel_members); ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-500 text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Pending Invitations</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['pending'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-500 text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Accepted</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['accepted'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between card-hover">
                    <div>
                        <p class="text-gray-600">Rejected</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['rejected'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-times-circle text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Program-specific Stats -->

    <div id="program-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($programs as $index => $program_name): 
            $stats = $program_stats[$index] ?? ['total_members' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];
        ?>
        <div class="program-card 
            <?php echo $index >= 4 ? 'hidden' : 'featured'; ?> 
            bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transition transform hover:-translate-y-1 hover:shadow-2xl">
            
            <!-- Header -->
            <div class="p-4 flex justify-between items-center 
                <?php echo $index < 4 ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white' : 'bg-gray-100'; ?>">
                
                <span class="text-sm font-semibold"><?php echo $program_name; ?></span>
                <span class="px-3 py-1 rounded-full text-xs font-medium 
                    <?php echo $index < 4 ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-600'; ?>">
                    <?php echo $stats['total_members']; ?> members
                </span>
            </div>

            <!-- Body Stats -->
            <div class="p-5 grid grid-cols-3 gap-4 text-center">
                <div class="flex flex-col items-center">
                    <div class="text-yellow-500 text-lg font-bold"><?php echo $stats['pending']; ?></div>
                    <span class="text-gray-500 text-xs">Pending</span>
                </div>
                <div class="flex flex-col items-center">
                    <div class="text-green-600 text-lg font-bold"><?php echo $stats['accepted']; ?></div>
                    <span class="text-gray-500 text-xs">Accepted</span>
                </div>
                <div class="flex flex-col items-center">
                    <div class="text-red-600 text-lg font-bold"><?php echo $stats['rejected']; ?></div>
                    <span class="text-gray-500 text-xs">Rejected</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

            <!-- Quick Actions Buttons -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <!-- Add Panel Member Box -->
    <div class="border border-gray-300 rounded-lg p-4 shadow-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            <i class="fas fa-user-plus text-blue-500 mr-2"></i>Add Panel Member
        </h3>
        <p class="text-sm text-gray-600 mb-3">Add a new panel member to the system with their contact information and specialization details.</p>
        <button onclick="openModal('addPanelModal')" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center justify-center">
            <i class="fas fa-user-plus mr-2"></i> Add New Member
        </button>
    </div>

    <!-- Send Invitation Box -->
    <div class="border border-gray-300 rounded-lg p-4 shadow-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            <i class="fas fa-paper-plane text-green-500 mr-2"></i>Send Invitation
        </h3>
        <p class="text-sm text-gray-600 mb-3">Send email invitations to selected panel members for upcoming thesis defense sessions.</p>
        <button onclick="openModal('sendInvitationModal')" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition flex items-center justify-center">
            <i class="fas fa-paper-plane mr-2"></i> Send Invitations
        </button>
    </div>
</div>

            <!-- Program Tabs -->
            <div class="bg-white shadow rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-graduation-cap mr-2 text-blue-500"></i>
                    Panel Members by Program
                </h2>
                
                <div class="flex flex-wrap mb-4">
                    <?php 
                    $first = true;
                    foreach ($programs as $program_key => $program_name): 
                    ?>
                    <div class="program-tab <?php echo $first ? 'active bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>" 
                         data-program="<?php echo $program_key; ?>">
                        <?php echo $program_name; ?>
                        <span class="bg-white <?php echo $first ? 'text-blue-600' : 'text-gray-700'; ?> rounded-full px-2 py-1 text-xs ml-1">
                            <?php echo isset($panel_members_by_program[$program_key]) ? count($panel_members_by_program[$program_key]) : 0; ?>
                        </span>
                    </div>
                    <?php 
                    $first = false;
                    endforeach; 
                    ?>
                </div>
                
                <!-- Panel List by Program -->
                <?php 
                $first = true;
                foreach ($programs as $program_key => $program_name): 
                    $members = $panel_members_by_program[$program_key] ?? [];
                ?>
                <div class="program-content <?php echo $first ? 'active' : ''; ?>" id="program-<?php echo $program_key; ?>">
                    <?php if (count($members) > 0): ?>
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
                                    <?php foreach ($members as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-indigo-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></div>
                                                    <span class="text-xs <?php echo $row['program'] . '-badge'; ?> program-badge">
                                                        <?php echo $programs[$row['program']]; ?>
                                                    </span>
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
    <a href="admin-pages/panel-assignment.php?edit_id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
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
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-users text-gray-300 text-4xl mb-3"></i>
                            <p>No panel members found for <?php echo $program_name; ?>.</p>
                            <button onclick="openModal('addPanelModal')" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                                Add Panel Member
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php 
                $first = false;
                endforeach; 
                ?>
            </div>

            <!-- Add Panel Member Modal -->
<div id="addPanelModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border border-gray-300 rounded-lg shadow-md">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-user-plus text-blue-500 mr-2"></i>
                    Add Panel Member
                </h3>
                <p class="text-sm text-gray-600 mb-4">Fill in the details below to add a new panel member to the system.</p>
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">First Name</label>
                            <input type="text" name="first_name" required 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Last Name</label>
                            <input type="text" name="last_name" required 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Email</label>
                        <input type="email" name="email" required 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Specialization</label>
                        <input type="text" name="specialization" required 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Program</label>
                        <select name="program" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <?php foreach ($programs as $program_key => $program_name): ?>
                            <option value="<?php echo $program_key; ?>"><?php echo $program_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal('addPanelModal')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">Cancel</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                            <i class="fas fa-plus mr-1"></i> Add Panel Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Invitation Modal -->
<div id="sendInvitationModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border border-gray-300 rounded-lg shadow-md">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-paper-plane text-green-500 mr-2"></i>
                    Send Panel Invitation
                </h3>
                <p class="text-sm text-gray-600 mb-4">Select panel members and send them an invitation to serve on the panel.</p>
                <form action="" method="POST" class="space-y-4" id="invitationForm">
                    <input type="hidden" name="action" value="send_invitation">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Select Panel Members</label>
                        <select name="panel_ids[]" multiple required 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 h-40">
                            <?php foreach ($panel_members_by_program as $program => $members): ?>
                            <optgroup label="<?php echo $programs[$program]; ?>">
                                <?php foreach ($members as $member): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo $member['first_name'] . ' ' . $member['last_name']; ?> (<?php echo $member['email']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple panel members</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Subject</label>
                        <input type="text" name="subject" required value="Invitation to Serve as Panel Member"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Message</label>
                        <textarea name="message" rows="6" required 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">Dear Panel Member,

You are invited to serve as a panel member for our upcoming thesis defenses.

Please respond to this invitation by clicking the appropriate link below.

Best regards,
Thesis Coordinator</textarea>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Email Preview:</h4>
                        <div class="email-preview bg-white p-3 text-sm">
                            <p><strong>Subject:</strong> <span id="preview-subject">Invitation to Serve as Panel Member</span></p>
                            <hr class="my-2">
                            <div id="preview-message">
                                Dear Panel Member,<br><br>
                                You are invited to serve as a panel member for our upcoming thesis defenses.<br><br>
                                Please respond to this invitation by clicking the appropriate link below.<br><br>
                                Best regards,<br>
                                Thesis Coordinator
                            </div>
                            <hr class="my-2">
                            <p class="text-xs text-gray-500">*Accept/Decline buttons will be automatically added to the email</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal('sendInvitationModal')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">Cancel</button>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                            <i class="fas fa-paper-plane mr-1"></i> Send Invitations
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

            <!-- Edit Panel Member Modal (shown when edit_id is in URL) -->
<?php if (!empty($editData)): ?>
<div id="editPanelModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border border-gray-300 rounded-lg shadow-md">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-edit text-indigo-500 mr-2"></i>
                    Edit Panel Member
                </h3>
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">First Name</label>
                            <input type="text" name="first_name" required 
                                value="<?php echo htmlspecialchars($editData['first_name']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required">Last Name</label>
                            <input type="text" name="last_name" required 
                                value="<?php echo htmlspecialchars($editData['last_name']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Email</label>
                        <input type="email" name="email" required 
                            value="<?php echo htmlspecialchars($editData['email']); ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Specialization</label>
                        <input type="text" name="specialization" required 
                            value="<?php echo htmlspecialchars($editData['specialization']); ?>"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Program</label>
                        <select name="program" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <?php foreach ($programs as $program_key => $program_name): ?>
                            <option value="<?php echo $program_key; ?>" <?php echo ($editData['program'] == $program_key) ? 'selected' : ''; ?>>
                                <?php echo $program_name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required">Status</label>
                        <select name="status" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="active" <?php echo ($editData['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editData['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="admin-pages/panel-assignment.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">Cancel</a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                            <i class="fas fa-save mr-1"></i> Update Panel Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-open edit modal when page loads if there's edit data
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editPanelModal');
    if (editModal) {
        // Show the modal
        editModal.classList.remove('opacity-0', 'pointer-events-none');
        editModal.classList.add('modal-active');
        
        // Animate it in
        const modalContent = editModal.querySelector('.modal-content');
        setTimeout(() => {
            modalContent.classList.add('modal-content-active');
        }, 10);
        
        // Close modal when clicking outside
        editModal.addEventListener('click', function(event) {
            if (event.target === editModal) {
                window.location.href = 'panel-assignment.php';
            }
        });
    }
});
</script>
<?php endif; ?>

    <script>
        // Modal functionality
        const button = document.getElementById("toggleButton");
    const hiddenCards = document.querySelectorAll(".program-card.hidden");
    let expanded = false;

    button.addEventListener("click", () => {
        hiddenCards.forEach(card => {
            card.classList.toggle("hidden");
        });
        expanded = !expanded;
        button.textContent = expanded ? "See Less" : "See All";
    });

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('modal-active');
            
            const modalContent = modal.querySelector('.modal-content');
            setTimeout(() => {
                modalContent.classList.add('modal-content-active');
            }, 10);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const modalContent = modal.querySelector('.modal-content');
            
            modalContent.classList.remove('modal-content-active');
            setTimeout(() => {
                modal.classList.remove('modal-active');
                modal.classList.add('opacity-0', 'pointer-events-none');
            }, 200);
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                const modal = event.target.closest('.modal-overlay');
                const modalContent = modal.querySelector('.modal-content');
                
                modalContent.classList.remove('modal-content-active');
                setTimeout(() => {
                    modal.classList.remove('modal-active');
                    modal.classList.add('opacity-0', 'pointer-events-none');
                }, 200);
            }
        });

        // Program tabs functionality
        document.querySelectorAll('.program-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.program-tab').forEach(t => {
                    t.classList.remove('active', 'bg-blue-600', 'text-white');
                    t.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                });
                this.classList.add('active', 'bg-blue-600', 'text-white');
                this.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                
                // Show corresponding content
                const program = this.dataset.program;
                document.querySelectorAll('.program-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById('program-' + program).classList.add('active');
            });
        });

        // Email preview functionality
        document.querySelector('input[name="subject"]').addEventListener('input', function() {
            document.getElementById('preview-subject').textContent = this.value;
        });

        document.querySelector('textarea[name="message"]').addEventListener('input', function() {
            document.getElementById('preview-message').innerHTML = this.value.replace(/\n/g, '<br>');
        });

        // Auto-open edit modal if there's edit data
        <?php if (!empty($editData)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editPanelModal');
            if (editModal) {
                editModal.classList.add('modal-active');
                const modalContent = editModal.querySelector('.modal-content');
                modalContent.classList.add('modal-content-active');
            }
        });
        <?php endif; ?>

        // Close edit modal with escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                <?php if (!empty($editData)): ?>
                window.location.href = 'panel-assignment.php';
                <?php else: ?>
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    if (!modal.classList.contains('opacity-0')) {
                        closeModal(modal.id);
                    }
                });
                <?php endif; ?>
            }
        });
    </script>
</body>
</html>