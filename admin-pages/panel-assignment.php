<?php
session_start();
include('../includes/connection.php');
include('../includes/notification-helper.php');

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
        $role = $conn->real_escape_string($_POST['role'] ?? 'member');
        
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
                $sql = "INSERT INTO panel_members (first_name, last_name, email, specialization, program, status, role) 
                        VALUES ('$first_name', '$last_name', '$email', '$specialization', '$program', '$status', '$role')";
                
                if ($conn->query($sql)) {
                    // Send notification to all users
                    notifyAllUsers($conn, "New Panel Member Added", "A new panel member has been added: $first_name $last_name ($specialization)", 'info');
                    
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
        $role = $conn->real_escape_string($_POST['role']);
        
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
                'status' => $status,
                'role' => $role
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
                    'status' => $status,
                    'role' => $role
                ];
            } else {
                $sql = "UPDATE panel_members SET 
                        first_name = '$first_name',
                        last_name = '$last_name',
                        email = '$email',
                        specialization = '$specialization',
                        program = '$program',
                        status = '$status',
                        role = '$role'
                        WHERE id = $id";
                
                if ($conn->query($sql)) {
                    // Send notification to all users
                    notifyAllUsers($conn, "Panel Member Updated", "Panel member information has been updated: $first_name $last_name", 'info');
                    
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
                        'status' => $status,
                        'role' => $role
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
        $message_raw = $_POST['message'];
        $message = $conn->real_escape_string($message_raw);
        
        if (empty($panel_ids)) {
            $error = "Please select at least one panel member";
        } else {
            $sent_count = 0;
            $error_count = 0;
            $error_details = [];
            
            foreach ($panel_ids as $panel_id) {
                $panel_id = intval($panel_id);

                // Check if a pending invitation already exists
                $check_invite = "SELECT id FROM panel_invitations WHERE panel_id = $panel_id AND status = 'pending'";
                $check_result = $conn->query($check_invite);

                if ($check_result->num_rows > 0) {
                    $error_count++;
                    $error_details[] = "Panel member ID $panel_id already has a pending invitation.";
                    continue;
                }

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
                        // Decode any literal "\r\n" to actual newlines, then convert to <br>
$clean_message = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $message_raw);
$emailContent = "
    <html>
    <body>
        <p>Dear {$panel['first_name']},</p>
        <p>" . nl2br($clean_message) . "</p>
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
                        
                        // Send notification to all users about invitation sent
                        notifyAllUsers($conn, 
                            "Panel Invitation Sent", 
                            "Panel invitation has been sent to {$panel['first_name']} {$panel['last_name']} for thesis defense panel.", 
                            "info"
                        );
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
    'bsit' => 'BSIT - BS Information Technology',
    'bshm' => 'BSHM - BS Hospitality Management',
    'bsoa' => 'BSOA - BS Office Administration',
    'bsba' => 'BSBA - BS Business Administration',
    'bscrim' => 'BSCrim - BS Criminology',
    'beed' => 'BEEd - Bachelor of Elementary Education',
    'bsed' => 'BSEd - Bachelor of Secondary Education',
    'bsce' => 'BSCpE - BS Computer Engineering',
    'bstm' => 'BSTM - BS Tourism Management',
    'bsentrep' => 'BSEntrep - BS Entrepreneurship',
    'bsais' => 'BSAIS - BS Accounting Information System',
    'bspsych' => 'BSPsych - BS Psychology',
    'blis' => 'BLIS - BL Information Science'
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
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        @keyframes slideInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-slide-up { animation: slideInUp 0.6s ease-out; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out; }
        .animate-scale-in { animation: scaleIn 0.5s ease-out; }
        [multiple] {
            height: auto;
            min-height: 42px;
        }
        .required:after {
            content: " *";
            color: #ef4444;
            font-weight: bold;
        }
        .modal-overlay {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
            backdrop-filter: blur(4px);
            transition: all 300ms ease-in-out;
        }
        .modal-content {
            transform: translateY(-30px) scale(0.95);
            transition: all 300ms cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .modal-active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content-active {
            transform: translateY(0) scale(1);
        }
        .email-preview {
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid #e2e8f0;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 12px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }
        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .status-pending { 
            background: linear-gradient(135deg, #fef3c7, #fde68a); 
            color: #92400e; 
            border: 1px solid #f59e0b;
        }
        .status-accepted { 
            background: linear-gradient(135deg, #d1fae5, #a7f3d0); 
            color: #065f46; 
            border: 1px solid #10b981;
        }
        .status-rejected { 
            background: linear-gradient(135deg, #fee2e2, #fecaca); 
            color: #b91c1c; 
            border: 1px solid #ef4444;
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .card-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .card-hover:hover::before {
            left: 100%;
        }
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.15);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-blue {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
        .gradient-green {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .gradient-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        .gradient-red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .program-tab {
            cursor: pointer;
            padding: 12px 24px;
            border-radius: 8px 8px 0 0;
            margin-right: 2px;
            transition: all 0.2s ease;
            position: relative;
            font-weight: 500;
            font-size: 14px;
            background: #f1f3f4;
            color: #5f6368;
            border: 1px solid #dadce0;
            border-bottom: none;
        }
        .program-tab:hover:not(.active) {
            background: #e8eaed;
        }
        .program-tab.active {
            background: white;
            color: #1a73e8;
            border-color: #dadce0;
            border-bottom: 2px solid #1a73e8;
            z-index: 1;
        }
        .program-content {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }
        .program-content.active {
            display: block;
        }
        .program-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .bsit-badge { 
            background: linear-gradient(135deg, #e0f2fe, #bae6fd); 
            color: #0369a1; 
            border: 1px solid #0ea5e9;
        }
        .bscs-badge { 
            background: linear-gradient(135deg, #fce7f3, #fbcfe8); 
            color: #be185d; 
            border: 1px solid #ec4899;
        }
        .bsis-badge { 
            background: linear-gradient(135deg, #dcfce7, #bbf7d0); 
            color: #166534; 
            border: 1px solid #22c55e;
        }
        .bsemc-badge { 
            background: linear-gradient(135deg, #fef3c7, #fde68a); 
            color: #92400e; 
            border: 1px solid #f59e0b;
        }
        .general-badge { 
            background: linear-gradient(135deg, #e5e7eb, #d1d5db); 
            color: #374151; 
            border: 1px solid #6b7280;
        }
        .enhanced-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .enhanced-table th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.75rem;
        }
        .enhanced-table tr:hover {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        .action-button {
            transition: all 0.2s ease;
            padding: 0.5rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .action-button:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .floating-action {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 40;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 1rem;
            border-radius: 50%;
            box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.5);
            transition: all 0.3s ease;
        }
        .floating-action:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 12px 35px -8px rgba(59, 130, 246, 0.7);
        }
        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px -8px rgba(0, 0, 0, 0.1);
        }
        /* Custom scrollbar styling */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        .custom-scrollbar-green::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-green::-webkit-scrollbar-track {
            background: rgba(34, 197, 94, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-green::-webkit-scrollbar-thumb {
            background: rgba(34, 197, 94, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-green::-webkit-scrollbar-thumb:hover {
            background: rgba(34, 197, 94, 0.6);
        }
        .custom-scrollbar-blue::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-blue::-webkit-scrollbar-track {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-blue::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-blue::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.6);
        }
        .custom-scrollbar-indigo::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-indigo::-webkit-scrollbar-track {
            background: rgba(99, 102, 241, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-indigo::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-indigo::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.6);
        }
        .custom-scrollbar-red::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-red::-webkit-scrollbar-track {
            background: rgba(239, 68, 68, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-red::-webkit-scrollbar-thumb {
            background: rgba(239, 68, 68, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-red::-webkit-scrollbar-thumb:hover {
            background: rgba(239, 68, 68, 0.6);
        }
        /* Modern program tabs */
        .program-tab-modern {
            display: inline-flex;
            items-center: center;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.3);
            color: #6b7280;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .program-tab-modern:hover:not(.active) {
            background: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }
        .program-tab-modern.active {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            border-color: #8b5cf6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 h-screen overflow-hidden">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-slide-up">
                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-blue-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="gradient-blue p-2 rounded-lg">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                            Total
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo count($all_panel_members); ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Panel Members</p>
                </div>

                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-orange-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 p-2 rounded-lg">
                            <i class="fas fa-clock text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded-full">
                            Pending
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending'] ?? 0; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Invitations</p>
                </div>

                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-green-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="gradient-green p-2 rounded-lg">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
                            Accepted
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['accepted'] ?? 0; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Responses</p>
                </div>

                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-red-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="gradient-red p-2 rounded-lg">
                            <i class="fas fa-times-circle text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-red-600 bg-red-100 px-2 py-1 rounded-full">
                            Rejected
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['rejected'] ?? 0; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Responses</p>
                </div>
            </div>

            <!-- Quick Actions Buttons -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 animate-fade-in">
    <!-- Add Panel Member Box -->
    <div class="stats-card p-6 card-hover group">
        <div class="flex items-center mb-4">
            <div class="gradient-blue p-3 rounded-xl mr-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-plus text-white text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Add Panel Member</h3>
        </div>
        <p class="text-gray-600 mb-6 leading-relaxed">Add a new panel member to the system with their contact information and specialization details.</p>
        <button onclick="openModal('addPanelModal')" class="w-full gradient-blue text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 flex items-center justify-center font-semibold group-hover:scale-105">
            <i class="fas fa-user-plus mr-2"></i> Add New Member
        </button>
    </div>

    <!-- Send Invitation Box -->
    <div class="stats-card p-6 card-hover group">
        <div class="flex items-center mb-4">
            <div class="gradient-green p-3 rounded-xl mr-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-paper-plane text-white text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Send Invitation</h3>
        </div>
        <p class="text-gray-600 mb-6 leading-relaxed">Send email invitations to selected panel members for upcoming thesis defense sessions.</p>
        <button onclick="openModal('sendInvitationModal')" class="w-full gradient-green text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 flex items-center justify-center font-semibold group-hover:scale-105">
            <i class="fas fa-paper-plane mr-2"></i> Send Invitations
        </button>
    </div>
</div>

           <!-- Program Overview -->
<div class="bg-white rounded-2xl p-6 mb-8 shadow-lg border border-gray-100">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-chart-pie text-indigo-600 mr-3"></i>
            Program Distribution
        </h2>
    </div>
    
    <div id="programGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php 
        $i = 0;
        foreach ($programs as $program_key => $program_name): 
            $member_count = isset($panel_members_by_program[$program_key]) ? count($panel_members_by_program[$program_key]) : 0;
            $program_stats_data = $program_stats[$program_key] ?? ['pending' => 0, 'accepted' => 0, 'rejected' => 0];
            $hiddenClass = ($i >= 5) ? "hidden" : ""; // hide after 5
        ?>
        <div class="program-card text-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors <?php echo $hiddenClass; ?>">
            <div class="text-2xl font-bold text-gray-800 mb-1"><?php echo $member_count; ?></div>
            <div class="text-sm font-medium text-gray-600 mb-3"><?php echo $program_name; ?></div>
            <div class="space-y-1">
                <div class="flex items-center justify-center text-xs">
                    <span class="w-2 h-2 bg-orange-400 rounded-full mr-1"></span>
                    <span class="text-orange-600"><?php echo $program_stats_data['pending']; ?> Pending</span>
                </div>
                <div class="flex items-center justify-center text-xs">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                    <span class="text-green-600"><?php echo $program_stats_data['accepted']; ?> Accepted</span>
                </div>
                <div class="flex items-center justify-center text-xs">
                    <span class="w-2 h-2 bg-red-400 rounded-full mr-1"></span>
                    <span class="text-red-600"><?php echo $program_stats_data['rejected']; ?> Rejected</span>
                </div>
            </div>
        </div>
        <?php 
            $i++;
        endforeach; 
        ?>
    </div>

    <?php if (count($programs) > 5): ?>
    <div class="text-center mt-4">
        <button id="togglePrograms" type="button" class="text-indigo-600 font-medium hover:underline text-sm">
            View All
        </button>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("togglePrograms");
    const cards = document.querySelectorAll("#programGrid .program-card");

    if (!toggleBtn) return;

    let expanded = false;

    toggleBtn.addEventListener("click", () => {
        expanded = !expanded;
        cards.forEach((card, index) => {
            if (expanded) {
                card.classList.remove("hidden");
            } else if (index >= 5) {
                card.classList.add("hidden");
            }
        });
        toggleBtn.textContent = expanded ? "View Less" : "View All";
    });
});
</script>

            <!-- Panel Members by Program -->
            <div class="bg-gradient-to-br from-white via-purple-50 to-indigo-100 rounded-2xl p-6 mb-8 animate-scale-in shadow-xl border-0">
                <div class="flex items-center mb-6">
                    <div class="gradient-purple p-3 rounded-xl mr-4">
                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Panel Members by Program
                    </h2>
                </div>
                
                <div class="flex flex-wrap gap-2 mb-6">
                    <?php 
                    $first = true;
                    foreach ($programs as $program_key => $program_name): 
                        $member_count = isset($panel_members_by_program[$program_key]) ? count($panel_members_by_program[$program_key]) : 0;
                    ?>
                    <button class="program-tab-modern <?php echo $first ? 'active' : ''; ?>" 
                         data-program="<?php echo $program_key; ?>">
                        <span class="font-medium"><?php echo $program_name; ?></span>
                        <span class="ml-2 px-2 py-1 bg-white/30 rounded-full text-xs font-bold">
                            <?php echo $member_count; ?>
                        </span>
                    </button>
                    <?php 
                    $first = false;
                    endforeach; 
                    ?>
                </div>
                
                <!-- Panel Cards by Program -->
                <?php 
                $first = true;
                foreach ($programs as $program_key => $program_name): 
                    $members = $panel_members_by_program[$program_key] ?? [];
                ?>
                <div class="program-content <?php echo $first ? 'active' : ''; ?>" id="program-<?php echo $program_key; ?>">
                    <?php if (count($members) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($members as $row): ?>
                            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-white/40 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center shadow-md mr-3">
                                            <i class="fas fa-user text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 text-sm"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h3>
                                            <span class="text-xs <?php echo $row['program'] . '-badge'; ?> program-badge">
                                                <?php echo $programs[$row['program']]; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-1">
                                        <a href="admin-pages/panel-assignment.php?edit_id=<?php echo $row['id']; ?>" class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <button type="button" onclick="showDeleteModal(<?php echo $row['id']; ?>)" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-briefcase mr-2 w-3"></i>
                                        <span class="truncate"><?php echo $row['specialization']; ?></span>
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-envelope mr-2 w-3"></i>
                                        <span class="truncate"><?php echo $row['email']; ?></span>
                                    </div>
                                    <div class="flex items-center justify-between pt-2">
                                        <span class="status-badge <?php echo $row['role'] == 'chairperson' ? 'status-accepted' : 'bg-blue-100 text-blue-800'; ?> text-xs">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                        <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-accepted' : 'bg-gray-100 text-gray-800'; ?> text-xs">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 text-gray-500">
                            <div class="gradient-bg p-6 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                                <i class="fas fa-users text-white text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Panel Members Found</h3>
                            <p class="text-gray-500 mb-6">No panel members found for <?php echo $program_name; ?>.</p>
                            <button onclick="openModal('addPanelModal')" class="gradient-blue text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 font-semibold">
                                <i class="fas fa-plus mr-2"></i> Add Panel Member
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
    <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-blue">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 border-0">
                <h3 class="text-lg font-bold flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <i class="fas fa-user-plus text-white text-sm"></i>
                    </div>
                    Add Panel Member
                </h3>
                <p class="text-blue-100 mt-1 text-sm">Fill in the details below to add a new panel member.</p>
            </div>
            <div class="p-4">
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1 text-sm required">First Name</label>
                            <input type="text" name="first_name" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1 text-sm required">Last Name</label>
                            <input type="text" name="last_name" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Email</label>
                        <input type="email" name="email" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Specialization</label>
                        <input type="text" name="specialization" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Program</label>
                        <select name="program" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                            <?php foreach ($programs as $program_key => $program_name): ?>
                            <option value="<?php echo $program_key; ?>"><?php echo $program_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Role</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                            <option value="member">Member</option>
                            <option value="chairperson">Chairperson</option>
                        </select>
                    </div>
                    <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                        <button type="button" onclick="closeModal('addPanelModal')" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">Cancel</button>
                        <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                            <i class="fas fa-plus mr-1"></i> Add Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Invitation Modal -->
<div id="sendInvitationModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block bg-gradient-to-br from-white via-green-50 to-emerald-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-green">
            <div class="bg-gradient-to-r from-green-600 to-emerald-700 text-white p-4 border-0">
                <h3 class="text-lg font-bold flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <i class="fas fa-paper-plane text-white text-sm"></i>
                    </div>
                    Send Panel Invitation
                </h3>
                <p class="text-green-100 mt-1 text-sm">Select panel members and send them an invitation.</p>
            </div>
            <div class="p-4">
                <form action="" method="POST" class="space-y-4" id="invitationForm">
                    <input type="hidden" name="action" value="send_invitation">

                    <!-- Panel Members Selection -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2 text-sm required">Select Panel Members</label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- LEFT SIDE: Program Dropdown -->
                            <div>
                                <label for="programSelect" class="block text-sm font-medium text-gray-600 mb-2">Choose Program</label>
                                <select id="programSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                                    <option value="">-- Select Program --</option>
                                    <?php foreach ($programs as $programKey => $programName): ?>
                                        <option value="<?php echo $programKey; ?>"><?php echo $programName; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- RIGHT SIDE: Members -->
                            <div>
                                <div id="membersContainer" class="bg-white border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto custom-scrollbar-green">
                                    <p class="text-xs text-gray-500">Select a program to view its members</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mt-2">Select multiple panel members to send invitations</p>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Subject</label>
                        <input type="text" name="subject" required value="Invitation to Serve as Panel Member"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all text-sm">
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Message</label>
                        <textarea name="message" rows="6" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all text-sm custom-scrollbar-green">Dear Panel Member,

You are invited to serve as a panel member for our upcoming thesis defenses.

Please respond to this invitation by clicking the appropriate link below.

Best regards,
Thesis Coordinator</textarea>
                    </div>

                    <!-- Email Preview -->
                    <div class="bg-gray-50 p-3 rounded-lg border">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Email Preview:</h4>
                        <div class="bg-white p-3 rounded-md shadow-inner text-sm space-y-2">
                            <p><strong>Subject:</strong> <span id="preview-subject">Invitation to Serve as Panel Member</span></p>
                            <hr class="my-2">
                            <div id="preview-message" class="whitespace-pre-line text-xs">
Dear Panel Member,

You are invited to serve as a panel member for our upcoming thesis defenses.

Please respond to this invitation by clicking the appropriate link below.

Best regards,
Thesis Coordinator
                            </div>
                            <hr class="my-2">
                            <p class="text-xs text-gray-500">*Accept/Decline buttons will be automatically added to the email</p>
                        </div>
                    </div>

                    <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                        <button type="button" onclick="closeModal('sendInvitationModal')" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">Cancel</button>
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
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
    <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block bg-gradient-to-br from-white via-indigo-50 to-purple-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-indigo">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white p-4 border-0">
                <h3 class="text-lg font-bold flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <i class="fas fa-edit text-white text-sm"></i>
                    </div>
                    Edit Panel Member
                </h3>
                <p class="text-indigo-100 mt-1 text-sm">Update panel member information below.</p>
            </div>
            <div class="p-4">
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1 text-sm required">First Name</label>
                            <input type="text" name="first_name" required 
                                value="<?php echo htmlspecialchars($editData['first_name']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1 text-sm required">Last Name</label>
                            <input type="text" name="last_name" required 
                                value="<?php echo htmlspecialchars($editData['last_name']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Email</label>
                        <input type="email" name="email" required 
                            value="<?php echo htmlspecialchars($editData['email']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Specialization</label>
                        <input type="text" name="specialization" required 
                            value="<?php echo htmlspecialchars($editData['specialization']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Program</label>
                        <select name="program" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                            <?php foreach ($programs as $program_key => $program_name): ?>
                            <option value="<?php echo $program_key; ?>" <?php echo ($editData['program'] == $program_key) ? 'selected' : ''; ?>>
                                <?php echo $program_name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Role</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                            <option value="member" <?php echo ($editData['role'] == 'member') ? 'selected' : ''; ?>>Member</option>
                            <option value="chairperson" <?php echo ($editData['role'] == 'chairperson') ? 'selected' : ''; ?>>Chairperson</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1 text-sm required">Status</label>
                        <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                            <option value="active" <?php echo ($editData['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editData['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                        <a href="admin-pages/panel-assignment.php" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">Cancel</a>
                        <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                            <i class="fas fa-save mr-1"></i> Update Member
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

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block bg-gradient-to-br from-white via-red-50 to-rose-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-md w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-red">
            <div class="bg-gradient-to-r from-red-600 to-rose-700 text-white p-4 border-0">
                <h3 class="text-lg font-bold flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    Confirm Deletion
                </h3>
                <p class="text-red-100 mt-1 text-sm">This action cannot be undone.</p>
            </div>
            <div class="p-4">
                <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40 mb-4">
                    <p class="text-gray-700 mb-3 font-medium">Are you sure you want to delete this panel member?</p>
                    <div class="space-y-2">
                        <div class="flex items-center p-2 bg-red-50 rounded-lg">
                            <i class="fas fa-user-times text-red-600 mr-3"></i>
                            <span class="text-gray-700 text-sm">Panel member will be permanently removed</span>
                        </div>
                        <div class="flex items-center p-2 bg-red-50 rounded-lg">
                            <i class="fas fa-envelope-open-text text-red-600 mr-3"></i>
                            <span class="text-gray-700 text-sm">All related invitations will be cancelled</span>
                        </div>
                    </div>
                </div>
                <form id="deleteForm" action="" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId" value="">
                    <div class="bg-white/80 backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                        <button type="button" onclick="closeModal('deleteConfirmModal')" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">Cancel</button>
                        <button type="submit" class="bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                            <i class="fas fa-trash mr-1"></i> Delete Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== MODAL FUNCTIONS =====
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('modal-active');

            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                setTimeout(() => {
                    modalContent.classList.add('modal-content-active');
                }, 10);
            }
        } else {
            console.error('Modal not found:', modalId);
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.classList.remove('modal-content-active');
        }

        setTimeout(() => {
            modal.classList.remove('modal-active');
            modal.classList.add('opacity-0', 'pointer-events-none');
        }, 200);
    }

    // ===== DELETE MODAL =====
    function showDeleteModal(memberId) {
        console.log('showDeleteModal called with ID:', memberId);
        const deleteIdInput = document.getElementById('deleteId');
        if (deleteIdInput) {
            deleteIdInput.value = memberId;
            openModal('deleteConfirmModal');
        } else {
            console.error('deleteId input not found');
        }
    }

    // Make function globally accessible
    window.showDeleteModal = showDeleteModal;

    // ===== CLICK OUTSIDE TO CLOSE =====
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            closeModal(event.target.id);
        }
    });

    // ===== ESC KEY TO CLOSE =====
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                if (!modal.classList.contains('opacity-0')) {
                    closeModal(modal.id);
                }
            });
        }
    });

    // ===== PROGRAM TABS =====
    document.querySelectorAll('.program-tab-modern').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.program-tab-modern').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const program = this.dataset.program;
            document.querySelectorAll('.program-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById('program-' + program).classList.add('active');
        });
    });



    // ===== EMAIL PREVIEW (only if fields exist) =====
    const subjectInput = document.querySelector('input[name="subject"]');
    const messageInput = document.querySelector('textarea[name="message"]');

    if (subjectInput) {
        subjectInput.addEventListener('input', function() {
            document.getElementById('preview-subject').textContent = this.value;
        });
    }

    if (messageInput) {
        messageInput.addEventListener('input', function() {
            document.getElementById('preview-message').innerHTML = this.value.replace(/\n/g, '<br>');
        });
    }

      // Panel members data from PHP
    const panelMembersByProgram = <?php echo json_encode($panel_members_by_program); ?>;
    
    const programSelect = document.getElementById("programSelect");
    const membersContainer = document.getElementById("membersContainer");

    programSelect.addEventListener("change", function() {
        const selectedProgram = this.value;
        membersContainer.innerHTML = "";

        if (selectedProgram && panelMembersByProgram[selectedProgram]) {
            const members = panelMembersByProgram[selectedProgram];
            members.forEach(member => {
                const label = document.createElement("label");
                label.className = "flex items-center p-2 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors";

                label.innerHTML = `
                    <input type="checkbox" name="panel_ids[]" value="${member.id}" 
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500 mr-3">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">${member.first_name} ${member.last_name}</div>
                        <div class="text-xs text-gray-500">${member.email} • ${member.specialization}</div>
                    </div>
                `;

                membersContainer.appendChild(label);
            });
        } else {
            membersContainer.innerHTML = `<p class="text-xs text-gray-500">No members available for this program.</p>`;
        }
    });
</script>
</body>
</html>