<?php
include('../includes/connection.php');
include('../includes/notification-helper.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's program information
$user_query = "SELECT u.*, sp.program FROM user_tbl u 
               JOIN student_profiles sp ON u.user_id = sp.user_id 
               WHERE u.user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
$user_program = $user_data['program'] ?? 'N/A';

// Get user's group information
$group_query = "SELECT g.*, gm.student_id 
                FROM groups g 
                JOIN group_members gm ON g.id = gm.group_id 
                WHERE gm.student_id = '$user_id'";
$group_result = mysqli_query($conn, $group_query);
$has_group = mysqli_num_rows($group_result) > 0;

if ($has_group) {
    $group = mysqli_fetch_assoc($group_result);
    $group_id = $group['id'];
    
    // Get cluster and adviser information
    $cluster_query = "SELECT c.*, f.fullname as adviser_name 
                      FROM clusters c 
                      LEFT JOIN faculty f ON c.faculty_id = f.id 
                      WHERE c.id = (SELECT cluster_id FROM groups WHERE id = '$group_id')";
    $cluster_result = mysqli_query($conn, $cluster_query);
    $cluster_info = mysqli_fetch_assoc($cluster_result);
    
    // Check if group has already submitted a proposal
    $proposal_query = "SELECT * FROM proposals WHERE group_id = '$group_id'";
    $proposal_result = mysqli_query($conn, $proposal_query);
    $has_proposal = mysqli_num_rows($proposal_result) > 0;
    
    if ($has_proposal) {
        $proposal = mysqli_fetch_assoc($proposal_result);
    }
    
    // Check payment status for each type (check any group member's payment)
    $research_forum_query = "SELECT p.* FROM payments p 
                            JOIN group_members gm ON p.student_id = gm.student_id 
                            WHERE gm.group_id = '$group_id' AND p.payment_type = 'research_forum' AND p.status = 'approved' LIMIT 1";
    $research_forum_result = mysqli_query($conn, $research_forum_query);
    $has_research_forum_payment = mysqli_num_rows($research_forum_result) > 0;
    $research_forum_data = mysqli_fetch_assoc($research_forum_result);
    
    $pre_oral_query = "SELECT p.* FROM payments p 
                      JOIN group_members gm ON p.student_id = gm.student_id 
                      WHERE gm.group_id = '$group_id' AND p.payment_type = 'pre_oral_defense' AND p.status = 'approved' LIMIT 1";
    $pre_oral_result = mysqli_query($conn, $pre_oral_query);
    $has_pre_oral_payment = mysqli_num_rows($pre_oral_result) > 0;
    $pre_oral_data = mysqli_fetch_assoc($pre_oral_result);
    
    $final_defense_query = "SELECT p.* FROM payments p 
                           JOIN group_members gm ON p.student_id = gm.student_id 
                           WHERE gm.group_id = '$group_id' AND p.payment_type = 'final_defense' AND p.status = 'approved' LIMIT 1";
    $final_defense_result = mysqli_query($conn, $final_defense_query);
    $has_final_defense_payment = mysqli_num_rows($final_defense_result) > 0;
    $final_defense_data = mysqli_fetch_assoc($final_defense_result);
    
    // For proposal submission, only research forum payment is required
    $has_paid = $has_research_forum_payment;
    
    // Handle join code generation
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_join_code'])) {
        $new_join_code = mysqli_real_escape_string($conn, $_POST['new_join_code']);
        $update_code_query = "UPDATE groups SET join_code = '$new_join_code' WHERE id = '$group_id'";
        if (mysqli_query($conn, $update_code_query)) {
            $_SESSION['success_message'] = "Join code updated successfully!";
            header("Location: ../student_pages/proposal.php");
            exit();
        } else {
            $error_message = "Error updating join code: " . mysqli_error($conn);
        }
    }
    
    // Handle group name update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_group_name'])) {
        $new_group_name = mysqli_real_escape_string($conn, $_POST['new_group_name']);
        
        // Check if the group name is already taken
        $check_name_query = "SELECT id FROM groups WHERE name = '$new_group_name' AND id != '$group_id'";
        $check_result = mysqli_query($conn, $check_name_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Group name '$new_group_name' is already taken. Please choose another name.";
        } else {
            $update_name_query = "UPDATE groups SET name = '$new_group_name' WHERE id = '$group_id'";
            if (mysqli_query($conn, $update_name_query)) {
                $_SESSION['success_message'] = "Group name updated successfully!";
                header("Location: ../student_pages/proposal.php");
                exit();
            } else {
                $error_message = "Error updating group name: " . mysqli_error($conn);
            }
        }
    }
}

// Handle other form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_proposal'])) {
        // Check if proposal already exists to prevent duplicates
        if ($has_proposal) {
            $error_message = "Proposal already submitted for this group.";
        } else {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // File upload handling
        $target_dir = "../assets/uploads/proposals/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Validate file upload
        if (!isset($_FILES['proposal_file']) || $_FILES['proposal_file']['error'] !== UPLOAD_ERR_OK) {
            $error_message = "Please select a valid PDF file to upload.";
        } else {
            $file_info = $_FILES['proposal_file'];
            $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
            
            // Validate file type
            if ($file_extension !== 'pdf') {
                $error_message = "Only PDF files are allowed.";
            } elseif ($file_info['size'] > 10 * 1024 * 1024) { // 10MB limit
                $error_message = "File size must be less than 10MB.";
            } else {
                $original_name = $file_info['name'];
                $target_file = $target_dir . $original_name;
                
                if (move_uploaded_file($file_info['tmp_name'], $target_file)) {
            $insert_query = "INSERT INTO proposals (group_id, title, description, file_path) 
                            VALUES ('$group_id', '$title', '$description', '$target_file')";
            
                if (mysqli_query($conn, $insert_query)) {
                // Include notification helper
                include('../includes/notification-helper.php');
                
                // Get student name for notification
                $student_query = "SELECT sp.full_name FROM student_profiles sp WHERE sp.user_id = '$user_id'";
                $student_result = mysqli_query($conn, $student_query);
                $student_data = mysqli_fetch_assoc($student_result);
                $student_name = $student_data['full_name'] ?? 'Student';
                
                // Notify the student
                notifyUser($conn, $user_id, 
                    "Proposal Submitted Successfully", 
                    "Your research proposal '$title' has been submitted and is under review.", 
                    "success"
                );
                
                // Notify all admins
                notifyAllAdmins($conn, 
                    "New Proposal Submitted", 
                    "$student_name submitted a new research proposal '$title' for review.", 
                    "info"
                );
                
                    $success_message = "Proposal submitted successfully!";
                    $has_proposal = true;
                    header("Location: ../student_pages/proposal.php");
                    exit();
                } else {
                    $error_message = "Error submitting proposal: " . mysqli_error($conn);
                }
                } else {
                    $error_message = "Error uploading file. Please try again.";
                }
            }
        }
        }
    }
    
    if (isset($_POST['update_proposal'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // Check if new file is uploaded
        if (isset($_FILES['proposal_file']) && $_FILES['proposal_file']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../assets/uploads/proposals/";
            $file_info = $_FILES['proposal_file'];
            $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
            
            if ($file_extension !== 'pdf') {
                $error_message = "Only PDF files are allowed.";
            } elseif ($file_info['size'] > 10 * 1024 * 1024) {
                $error_message = "File size must be less than 10MB.";
            } else {
                $original_name = $file_info['name'];
                $target_file = $target_dir . $original_name;
                
                if (move_uploaded_file($file_info['tmp_name'], $target_file)) {
                    $update_query = "UPDATE proposals SET title = '$title', description = '$description', file_path = '$target_file' WHERE group_id = '$group_id'";
                } else {
                    $error_message = "Error uploading file. Please try again.";
                }
            }
        } else {
            // Update without changing file
            $update_query = "UPDATE proposals SET title = '$title', description = '$description' WHERE group_id = '$group_id'";
        }
        
        if (isset($update_query) && mysqli_query($conn, $update_query)) {
            $success_message = "Proposal updated successfully!";
            header("Location: ../student_pages/proposal.php");
            exit();
        } elseif (!isset($error_message)) {
            $error_message = "Error updating proposal: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['create_group'])) {
        $group_name = mysqli_real_escape_string($conn, $_POST['group_name']);
        $program = mysqli_real_escape_string($conn, $_POST['program']);
        
        // Generate a unique join code (6 characters)
        $join_code = substr(strtoupper(md5(uniqid(rand(), true))), 0, 6);
        
        // Create new program-based group
        $create_group_query = "INSERT INTO groups (name, program, join_code) VALUES ('$group_name', '$program', '$join_code')";
        if (mysqli_query($conn, $create_group_query)) {
            $new_group_id = mysqli_insert_id($conn);
            
            // Add user as group member
            $add_member_query = "INSERT INTO group_members (group_id, student_id) VALUES ('$new_group_id', '$user_id')";
            if (mysqli_query($conn, $add_member_query)) {
                $success_message = "Program group created successfully!";
                header("Location: ../student_pages/proposal.php");
                exit();
            } else {
                $error_message = "Error adding you to the group: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Error creating group: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['join_group'])) {
        $join_code = mysqli_real_escape_string($conn, $_POST['join_code']);
        
        // Check if join code exists
        $check_code_query = "SELECT * FROM groups WHERE join_code = '$join_code'";
        $code_result = mysqli_query($conn, $check_code_query);
        
        if (mysqli_num_rows($code_result) > 0) {
            $group_data = mysqli_fetch_assoc($code_result);
            $group_id_to_join = $group_data['id'];
            $group_program = $group_data['program'];
            
            // Check if user is already in a group
            if ($has_group) {
                $error_message = "You are already in a group. Leave your current group first.";
            } 
            // Check if user's program matches the group's program
            elseif ($user_program !== $group_program) {
                $error_message = "You can only join groups from your program ($user_program). This group is for $group_program.";
            }
            else {
                // Check if group is full (max 5 members)
                $member_count_query = "SELECT COUNT(*) as count FROM group_members WHERE group_id = '$group_id_to_join'";
                $member_count_result = mysqli_query($conn, $member_count_query);
                $member_count = mysqli_fetch_assoc($member_count_result)['count'];
                
                if ($member_count >= 5) {
                    $error_message = "This group is already full (maximum 5 members).";
                } else {
                    // Add user to group
                    $join_query = "INSERT INTO group_members (group_id, student_id) VALUES ('$group_id_to_join', '$user_id')";
                    if (mysqli_query($conn, $join_query)) {
                        // Get group's cluster assignment
                        $group_cluster_query = "SELECT cluster_id FROM groups WHERE id = '$group_id_to_join'";
                        $group_cluster_result = mysqli_query($conn, $group_cluster_query);
                        $group_cluster_data = mysqli_fetch_assoc($group_cluster_result);
                        
                        if ($group_cluster_data && $group_cluster_data['cluster_id']) {
                            // Assign new member to same cluster and adviser as group
                            $assign_query = "UPDATE student_profiles 
                                            SET cluster = (SELECT cluster FROM clusters WHERE id = '{$group_cluster_data['cluster_id']}'),
                                                faculty_id = (SELECT faculty_id FROM clusters WHERE id = '{$group_cluster_data['cluster_id']}'),
                                                updated_at = NOW() 
                                            WHERE user_id = '$user_id'";
                            mysqli_query($conn, $assign_query);
                            
                            // Update cluster student count
                            $update_count_query = "UPDATE clusters SET student_count = student_count + 1 WHERE id = '{$group_cluster_data['cluster_id']}'";
                            mysqli_query($conn, $update_count_query);
                        }
                        
                        $success_message = "Joined program group successfully!";
                        header("Location: ../student_pages/proposal.php");
                        exit();
                    } else {
                        $error_message = "Error joining group: " . mysqli_error($conn);
                    }
                }
            }
        } else {
            $error_message = "Invalid join code.";
        }
    }
    
    if (isset($_POST['upload_payment'])) {
        $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
        $payment_amount = 100.00;
        
        $target_dir = "../assets/uploads/receipts/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Check if group already has payment for this type
        $existing_payment_query = "SELECT p.* FROM payments p 
                                  JOIN group_members gm ON p.student_id = gm.student_id 
                                  WHERE gm.group_id = '$group_id' AND p.payment_type = '$payment_type' LIMIT 1";
        $existing_result = mysqli_query($conn, $existing_payment_query);
        $existing_payment = mysqli_fetch_assoc($existing_result);
        
        // Validate file uploads (multiple images)
        if (!isset($_FILES['payment_images']) || empty($_FILES['payment_images']['name'][0])) {
            $error_message = "Please select at least one image receipt to upload.";
        } else {
            $uploaded_files = [];
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_file_size = 5 * 1024 * 1024; // 5MB limit
            
            // Process each uploaded file
            for ($i = 0; $i < count($_FILES['payment_images']['name']); $i++) {
                if ($_FILES['payment_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['payment_images']['name'][$i];
                    $file_tmp = $_FILES['payment_images']['tmp_name'][$i];
                    $file_size = $_FILES['payment_images']['size'][$i];
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Validate file type
                    if (!in_array($file_extension, $allowed_types)) {
                        $error_message = "Only JPG, JPEG, PNG, and GIF images are allowed.";
                        break;
                    }
                    
                    // Validate file size
                    if ($file_size > $max_file_size) {
                        $error_message = "Each image must be less than 5MB.";
                        break;
                    }
                    
                    // Generate unique filename
                    $unique_name = time() . '_' . $i . '_' . $file_name;
                    $target_file = $target_dir . $unique_name;
                    
                    if (move_uploaded_file($file_tmp, $target_file)) {
                        $uploaded_files[] = $target_file;
                    } else {
                        $error_message = "Error uploading file: " . $file_name;
                        break;
                    }
                }
            }
            
            // If all files uploaded successfully
            if (empty($error_message) && !empty($uploaded_files)) {
                $image_receipts_json = json_encode($uploaded_files);
                
                if ($existing_payment) {
                    // Delete old image files
                    if ($existing_payment['image_receipts']) {
                        $old_images = json_decode($existing_payment['image_receipts'], true);
                        if ($old_images) {
                            foreach ($old_images as $old_image) {
                                if (file_exists($old_image)) {
                                    unlink($old_image);
                                }
                            }
                        }
                    }
                    
                    // Update existing payment for all group members
                    $update_query = "UPDATE payments p 
                                    JOIN group_members gm ON p.student_id = gm.student_id 
                                    SET p.image_receipts = '$image_receipts_json', p.payment_date = NOW() 
                                    WHERE gm.group_id = '$group_id' AND p.payment_type = '$payment_type'";
                    mysqli_query($conn, $update_query);
                    $success_message = "Group payment receipt images updated successfully!";
                } else {
                    // Create new payment for all group members
                    $members_query = "SELECT student_id FROM group_members WHERE group_id = '$group_id'";
                    $members_result = mysqli_query($conn, $members_query);
                    
                    while ($member = mysqli_fetch_assoc($members_result)) {
                        $member_id = $member['student_id'];
                        $payment_query = "INSERT INTO payments (student_id, payment_type, amount, image_receipts, status, payment_date) 
                                         VALUES ('$member_id', '$payment_type', '$payment_amount', '$image_receipts_json', 'approved', NOW())";
                        mysqli_query($conn, $payment_query);
                    }
                    $success_message = "Group payment receipt images uploaded successfully!";
                }
                
                header("Location: ../student_pages/proposal.php");
                exit();
            }
        }
    }
    
    if (isset($_POST['remove_member'])) {
        $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
        $remove_query = "DELETE FROM group_members WHERE group_id = '$group_id' AND student_id = '$member_id'";
        if (mysqli_query($conn, $remove_query)) {
            // Reset removed member's cluster
            $reset_cluster_query = "UPDATE student_profiles 
                                    SET cluster = NULL, updated_at = NOW() 
                                    WHERE user_id = '$member_id'";
            mysqli_query($conn, $reset_cluster_query);

            $success_message = "Member removed successfully!";
            header("Location: ../student_pages/proposal.php");
            exit();
        } else {
            $error_message = "Error removing member: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['leave_group'])) {
        $leave_query = "DELETE FROM group_members WHERE group_id = '$group_id' AND student_id = '$user_id'";
        if (mysqli_query($conn, $leave_query)) {
            // Get current cluster_id before resetting
            $current_cluster_query = "SELECT cluster_id FROM groups WHERE id = '$group_id'";
            $current_cluster_result = mysqli_query($conn, $current_cluster_query);
            $current_cluster_data = mysqli_fetch_assoc($current_cluster_result);
            
            // Reset user's cluster
            $reset_cluster_query = "UPDATE student_profiles 
                                    SET cluster = NULL, faculty_id = NULL, updated_at = NOW() 
                                    WHERE user_id = '$user_id'";
            mysqli_query($conn, $reset_cluster_query);
            
            // Update cluster student count
            if ($current_cluster_data && $current_cluster_data['cluster_id']) {
                $update_count_query = "UPDATE clusters SET student_count = student_count - 1 WHERE id = '{$current_cluster_data['cluster_id']}'";
                mysqli_query($conn, $update_count_query);
            }
            
            $success_message = "Left group successfully!";
            header("Location: ../student_pages/proposal.php");
            exit();
        } else {
            $error_message = "Error leaving group: " . mysqli_error($conn);
        }
    }
}

// Get available programs for dropdown
$programs_query = "SELECT DISTINCT program FROM groups ORDER BY program";
$programs_result = mysqli_query($conn, $programs_query);
$available_programs = [];
while ($row = mysqli_fetch_assoc($programs_result)) {
    $available_programs[] = $row['program'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Group Proposal Submission | CRAD System</title>
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        secondary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            500: '#a855f7',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                        success: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                        },
                        warning: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                        },
                        gray: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                        'card': '0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02)',
                    }
                }
            }
        }

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
        
        function generateJoinCode() {
            document.getElementById('joinCodeDisplay').classList.add('hidden');
            document.getElementById('joinCodeLoading').classList.remove('hidden');
            
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 6; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            
            document.getElementById('new_join_code').value = result;
            
            setTimeout(function() {
                document.getElementById('updateCodeForm').submit();
            }, 800);
        }
        
        function copyJoinCode() {
            const joinCode = document.getElementById('join_code_value').textContent;
            navigator.clipboard.writeText(joinCode).then(function() {
                const notification = document.getElementById('copyNotification');
                notification.classList.remove('hidden');
                setTimeout(function() {
                    notification.classList.add('hidden');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
        
        function confirmRemoveMember(memberName) {
            return confirm("Are you sure you want to remove " + memberName + " from the group?");
        }
        
        function filterProgramGroups() {
            const programSelect = document.getElementById('program_filter');
            const program = programSelect.value;
            const groupCards = document.querySelectorAll('.program-group-card');
            
            groupCards.forEach(card => {
                if (program === 'all' || card.dataset.program === program) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }
        
        function toggleGroupNameEdit() {
            const display = document.getElementById('group-name-display');
            const form = document.getElementById('edit-group-name-form');
            
            if (form.classList.contains('hidden')) {
                display.classList.add('hidden');
                form.classList.remove('hidden');
                form.querySelector('input[name="new_group_name"]').focus();
            } else {
                display.classList.remove('hidden');
                form.classList.add('hidden');
            }
        }
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #ffffff;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease-out;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .stats-card:hover::before {
            left: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card-hover {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        
        .enhanced-modal {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .enhanced-button {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .enhanced-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .enhanced-button:hover::before {
            left: 100%;
        }
        
        .enhanced-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="font-sans bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>
        
        <div class="flex-1 overflow-y-auto">
            <main class="p-6">
                <!-- Status Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success_message)): ?>
                    <div class="bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Program Group Status Card -->
                <div class="glass-card rounded-2xl p-6 mb-8 animate-delay-1">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="bg-primary-100 p-3 rounded-xl">
                                <i class="fas fa-users text-primary-600 text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Program Group Status</h2>
                                <p class="text-sm text-gray-500">Your program: <span class="font-medium text-primary-600"><?php echo $user_program; ?></span></p>
                            </div>
                        </div>
                        <?php if ($has_group): ?>
                            <div class="bg-primary-50 text-primary-700 px-3 py-1 rounded-full text-sm font-medium flex items-center">
                                <span class="h-2 w-2 bg-primary-600 rounded-full mr-2"></span>
                                Active Group
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($has_group): ?>
                        <!-- Status Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                            <!-- Group Name Card -->
                            <div class="stats-card bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold text-blue-800 text-sm uppercase tracking-wide">Program Group</h3>
                                        <p class="text-lg font-bold text-gray-900 mt-1" id="group-name-display"><?php echo $group['name']; ?></p>
                                        <p class="text-xs text-blue-700 mt-1">Program: <?php echo $group['program']; ?></p>
                                    </div>
                                    <button onclick="toggleGroupNameEdit()" class="text-blue-600 hover:text-blue-800 p-1 transition">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                </div>
                                
                                <!-- Edit form (hidden by default) -->
                                <form method="POST" id="edit-group-name-form" class="hidden mt-3">
                                    <input type="hidden" name="update_group_name" value="1">
                                    <div class="flex gap-2">
                                        <select name="new_group_name" class="flex-1 px-2 py-1 border border-blue-300 rounded text-sm">
                                            <?php 
                                            // Get taken group names
                                            $taken_names_query = "SELECT name FROM groups WHERE id != '$group_id'";
                                            $taken_names_result = mysqli_query($conn, $taken_names_query);
                                            $taken_names = [];
                                            while ($row = mysqli_fetch_assoc($taken_names_result)) {
                                                $taken_names[] = $row['name'];
                                            }
                                            
                                            for($i = 1; $i <= 100; $i++): 
                                                $group_name = "GRP $i";
                                                $is_taken = in_array($group_name, $taken_names);
                                                $is_current = ($group['name'] === $group_name);
                                            ?>
                                                <option value="<?php echo $group_name; ?>" 
                                                    <?php echo $is_current ? 'selected' : ''; ?>
                                                    <?php echo ($is_taken && !$is_current) ? 'disabled' : ''; ?>>
                                                    <?php echo $group_name; ?><?php echo ($is_taken && !$is_current) ? ' (Taken)' : ''; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded text-sm hover:bg-blue-700">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" onclick="toggleGroupNameEdit()" class="bg-gray-400 text-white px-2 py-1 rounded text-sm hover:bg-gray-500">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Cluster Assignment Card -->
                            <div class="stats-card bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200">
                                <h3 class="font-semibold text-purple-800 text-sm uppercase tracking-wide">Cluster Assignment</h3>
                                <?php if ($cluster_info): ?>
                                    <p class="text-lg font-bold text-gray-900 mt-1"><?php echo $cluster_info['program']; ?> - Cluster <?php echo $cluster_info['cluster']; ?></p>
                                    <p class="text-xs text-purple-700 mt-1">Capacity: <?php echo $cluster_info['student_count']; ?>/<?php echo $cluster_info['capacity']; ?></p>
                                <?php else: ?>
                                    <p class="text-lg font-bold text-gray-500 mt-1">Not Assigned</p>
                                    <p class="text-xs text-purple-700 mt-1">Waiting for assignment</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Adviser Assignment Card -->
                            <div class="stats-card bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-5 border border-indigo-200">
                                <h3 class="font-semibold text-indigo-800 text-sm uppercase tracking-wide">Adviser Assignment</h3>
                                <?php if ($cluster_info && $cluster_info['adviser_name']): ?>
                                    <p class="text-lg font-bold text-gray-900 mt-1"><?php echo $cluster_info['adviser_name']; ?></p>
                                    <p class="text-xs text-indigo-700 mt-1">Faculty Adviser</p>
                                <?php else: ?>
                                    <p class="text-lg font-medium text-gray-500 mt-1">Not Assigned</p>
                                    <p class="text-xs text-indigo-700 mt-1">Waiting for assignment</p>
                                <?php endif; ?>
                            </div>

                            <!-- Payment Status Card -->
                            <div class="stats-card bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                                <h3 class="font-semibold text-blue-800 text-sm uppercase tracking-wide">Payment Status</h3>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Research Forum:</span>
                                        <?php if ($has_research_forum_payment): ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Approved</span>
                                        <?php else: ?>
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Pre-Oral Defense:</span>
                                        <?php if ($has_pre_oral_payment): ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Approved</span>
                                        <?php else: ?>
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Final Defense:</span>
                                        <?php if ($has_final_defense_payment): ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Approved</span>
                                        <?php else: ?>
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button onclick="toggleModal('paymentModal')" class="mt-3 w-full bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm flex items-center justify-center">
                                    <i class="fas fa-upload mr-2"></i> Upload Receipt
                                </button>
                            </div>
                        </div>

                        <!-- Members List -->
                        <div class="mb-8">
                            <h3 class="font-semibold mb-4 text-gray-700 flex items-center">
                                <i class="fas fa-user-friends mr-2 text-primary-500"></i>
                                Group Members
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <?php
                                $members_query = "SELECT u.*, sp.full_name
                                                FROM group_members gm
                                                JOIN user_tbl u ON gm.student_id = u.user_id
                                                JOIN student_profiles sp ON u.user_id = sp.user_id
                                                WHERE gm.group_id = '$group_id'";
                                $members_result = mysqli_query($conn, $members_query);

                                while ($member = mysqli_fetch_assoc($members_result)) {
                                    $initials = '';
                                    $name_parts = explode(' ', $member['full_name']);
                                    foreach ($name_parts as $part) {
                                        $initials .= strtoupper(substr($part, 0, 1));
                                    }
                                    $initials = substr($initials, 0, 2);
                                    
                                    $colors = ['primary', 'purple', 'blue', 'green', 'yellow'];
                                    $color = $colors[array_rand($colors)];
                                    
                                    echo '<div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-200">';
                                    echo '<div class="bg-'.$color.'-100 text-'.$color.'-600 h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-2">';
                                    echo '<span class="font-semibold">'.$initials.'</span>';
                                    echo '</div>';
                                    echo '<p class="font-medium text-gray-800">' . htmlspecialchars($member['full_name']) . '</p>';
                                    echo '<p class="text-xs text-gray-500">' . ucfirst($member['role']) . '</p>';
                                    if ($member['user_id'] == $user_id) {
                                        echo '<span class="inline-block mt-2 bg-primary-100 text-primary-700 text-xs px-2 py-1 rounded-full">You</span>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Join Code -->
                        <div class="mb-8">
                            <h3 class="font-semibold mb-3 text-gray-700 flex items-center">
                                <i class="fas fa-share-alt mr-2 text-primary-500"></i>
                                Group Join Code
                            </h3>
                            <form id="updateCodeForm" method="POST">
                                <input type="hidden" name="update_join_code" value="1">
                                <input type="hidden" name="new_join_code" id="new_join_code" value="">

                                <div class="flex items-center space-x-3">
                                    <div class="relative flex-grow">
                                        <input type="text" id="join_code_value" value="<?php echo $group['join_code']; ?>" readonly 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 font-mono text-lg text-gray-800 shadow-sm text-center tracking-widest">
                                        <button type="button" onclick="copyJoinCode()" 
                                            class="absolute right-2 top-2 p-2 text-gray-500 hover:text-primary-600 transition" title="Copy to clipboard">
                                            <i class="far fa-copy"></i>
                                        </button>
                                    </div>
                                    <button type="button" onclick="generateJoinCode()" 
                                        class="flex items-center bg-primary-600 hover:bg-primary-700 text-white px-4 py-3 rounded-lg shadow-sm transition">
                                        <i class="fas fa-sync-alt mr-2"></i> New Code
                                    </button>
                                </div>
                            </form>
                            <p class="text-sm text-gray-500 mt-2">Share this code with other students in your program to let them join your group.</p>

                            <!-- Copy notification -->
                            <div id="copyNotification" class="hidden mt-3 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-lg text-sm shadow-sm flex items-center">
                                <i class="fas fa-check-circle mr-2"></i> Code copied to clipboard!
                            </div>
                        </div>

                        <!-- Leave Group -->
                        <div class="mb-8">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to leave this group? This action cannot be undone.')">
                                <button type="submit" name="leave_group" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Leave Group
                                </button>
                            </form>
                        </div>

                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="text-center py-10">
                            <div class="bg-gray-100 inline-block p-6 rounded-full mb-4">
                                <i class="fas fa-users text-gray-400 text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-700">You are not in a program group yet</h3>
                            <p class="text-gray-500 mb-6">Join an existing program group or create a new one to submit a proposal.</p>
                            <div class="flex justify-center space-x-4">
                                <button onclick="toggleModal('createGroupModal')" 
                                    class="enhanced-button flex items-center bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-lg shadow-sm transition">
                                    <i class="fas fa-plus mr-2"></i> Create Group
                                </button>
                                <button onclick="toggleModal('joinGroupModal')" 
                                    class="enhanced-button flex items-center bg-secondary-600 hover:bg-secondary-700 text-white px-5 py-2.5 rounded-lg shadow-sm transition">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Join Group
                                </button>
                            </div>
                        </div>
                        
                        <!-- Available Program Groups -->
                        <div class="mt-8">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                                <i class="fas fa-list-alt mr-2 text-primary-500"></i>
                                Available Program Groups
                            </h3>
                            
                            <!-- Program Filter -->
                            <div class="mb-4 flex items-center glass-card p-3 rounded-lg">
                                <label class="mr-2 text-sm font-medium text-gray-700">Filter by Program:</label>
                                <select id="program_filter" onchange="filterProgramGroups()" class="px-3 py-1 border border-gray-300 rounded-lg bg-gray-50">
                                    <option value="all">All Programs</option>
                                    <?php foreach ($available_programs as $program): ?>
                                        <option value="<?php echo $program; ?>" <?php echo ($program == $user_program) ? 'selected' : ''; ?>>
                                            <?php echo $program; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php
                                // Get available program groups
                                $available_groups_query = "SELECT g.*, COUNT(gm.student_id) as member_count 
                                                         FROM groups g 
                                                         LEFT JOIN group_members gm ON g.id = gm.group_id 
                                                         GROUP BY g.id 
                                                         HAVING member_count < 5 
                                                         ORDER BY g.program, g.name";
                                $available_groups_result = mysqli_query($conn, $available_groups_query);
                                
                                if (mysqli_num_rows($available_groups_result) > 0) {
                                    while ($available_group = mysqli_fetch_assoc($available_groups_result)) {
                                        $members_count = $available_group['member_count'];
                                        $available_slots = 5 - $members_count;
                                        
                                        echo '<div class="program-group-card p-4 card-hover rounded-lg" data-program="' . $available_group['program'] . '">';
                                        echo '<div class="flex justify-between items-start mb-3">';
                                        echo '<div>';
                                        echo '<h4 class="font-semibold text-gray-800">' . $available_group['name'] . '</h4>';
                                        echo '<p class="text-sm text-gray-600">Program: ' . $available_group['program'] . '</p>';
                                        echo '</div>';
                                        echo '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">' . $available_group['program'] . '</span>';
                                        echo '</div>';
                                        echo '<div class="flex justify-between items-center mb-3">';
                                        echo '<div class="text-sm text-gray-600">';
                                        echo '<span class="font-medium">' . $members_count . '/5</span> members';
                                        echo '</div>';
                                        echo '<div class="text-sm text-green-600 font-medium">';
                                        echo $available_slots . ' slot' . ($available_slots > 1 ? 's' : '') . ' available';
                                        echo '</div>';
                                        echo '</div>';
                                        
                                        // Only show join button if user's program matches group's program
                                        if ($user_program === $available_group['program']) {
                                            echo '<form method="POST" class="mt-3">';
                                            echo '<input type="hidden" name="join_code" value="' . $available_group['join_code'] . '">';
                                            echo '<button type="submit" name="join_group" class="w-full bg-primary-600 hover:bg-primary-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition">';
                                            echo 'Join Group';
                                            echo '</button>';
                                            echo '</form>';
                                        } else {
                                            echo '<p class="text-sm text-red-500 mt-2 text-center">Only ' . $available_group['program'] . ' students can join</p>';
                                        }
                                        
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p class="text-gray-500 col-span-2 text-center py-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">No available program groups at the moment.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submission Form (only show if in a group) -->
                <?php if ($has_group): ?>
                    <div class="glass-card rounded-xl p-6 animate-delay-2">
                        <div class="flex items-center mb-6">
                            <div class="bg-primary-100 p-3 rounded-xl mr-4">
                                <i class="fas fa-file-upload text-primary-600 text-2xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800">Submit Proposal Letter</h2>
                        </div>
                        
                        <?php if ($has_proposal): ?>
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 mb-6 backdrop-filter backdrop-blur-sm">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                    <div>
                                        <h3 class="font-semibold text-blue-800">Proposal Already Submitted</h3>
                                        <p class="text-blue-700">You submitted your proposal on <?php echo date('F j, Y, g:i a', strtotime($proposal['submitted_at'])); ?>.</p>
                                        <p class="text-blue-700 mt-1">Status: <span class="font-medium"><?php echo ucfirst($proposal['status']); ?></span></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$has_research_forum_payment): ?>
                            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4 mb-6 backdrop-filter backdrop-blur-sm">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                                    <div>
                                        <h3 class="font-semibold text-yellow-800">Research Forum Payment Required</h3>
                                        <p class="text-yellow-700">Team leader needs to upload the group's Research Forum payment receipt before you can submit a proposal.</p>
                                        <button onclick="toggleModal('paymentModal')" class="mt-3 bg-primary-600 text-white px-4 py-2 rounded-lg">
                                            <i class="fas fa-upload mr-2"></i> Upload Receipt
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form action="/CRAD-system/student_pages/proposal.php" method="POST" enctype="multipart/form-data" class="space-y-4" <?php echo !$has_research_forum_payment ? 'onsubmit="return false;"' : ''; ?>>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Proposal Title</label>
                                <input type="text" name="title" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                                    <?php echo !$has_research_forum_payment ? 'disabled' : ''; ?>
                                    value="<?php echo $has_proposal ? $proposal['title'] : ''; ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="3" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                                    <?php echo !$has_research_forum_payment ? 'disabled' : ''; ?>
                                ><?php echo $has_proposal ? $proposal['description'] : ''; ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <?php echo $has_proposal ? 'Update Proposal File (PDF only) - Optional' : 'Upload Signed Proposal (PDF only)'; ?>
                                </label>
                                <div class="mt-1 flex items-center">
                                    <label class="cursor-pointer bg-white border border-gray-300 rounded-lg px-4 py-2 flex items-center hover:bg-gray-50 transition 
                                        <?php echo !$has_research_forum_payment ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                        <span class="text-sm font-medium" id="file-label">Choose PDF File</span>
                                        <input type="file" name="proposal_file" accept=".pdf" <?php echo !$has_proposal ? 'required' : ''; ?> 
                                            <?php echo !$has_research_forum_payment ? 'disabled' : ''; ?>
                                            class="hidden" onchange="updateFileName(this)">
                                    </label>
                                    <span class="ml-3 text-sm text-gray-500" id="file-name">
                                        <?php echo $has_proposal ? basename($proposal['file_path']) : 'No file chosen'; ?>
                                    </span>
                                </div>
                                <?php if ($has_proposal): ?>
                                    <p class="text-sm text-gray-500 mt-1">Leave empty to keep current file</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" name="<?php echo $has_proposal ? 'update_proposal' : 'submit_proposal'; ?>" 
                                    class="enhanced-button bg-gradient-to-r from-primary-600 to-secondary-600 text-white px-6 py-3 rounded-lg hover:shadow-md transition flex items-center justify-center font-medium
                                    <?php echo !$has_research_forum_payment ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md'; ?>">
                                    <i class="fas fa-<?php echo $has_proposal ? 'edit' : 'paper-plane'; ?> mr-2"></i> 
                                    <?php echo $has_proposal ? 'Update Proposal' : 'Submit Proposal'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Create Program Group Modal -->
    <div id="createGroupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="enhanced-modal rounded-lg shadow-xl w-full max-w-md">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Create New Program Group</h3>
                <button onclick="toggleModal('createGroupModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Name</label>
                    <select name="group_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Select Group Name</option>
                        <?php for($i = 1; $i <= 100; $i++): ?>
                            <option value="GRP <?php echo $i; ?>">GRP <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select name="program" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="<?php echo $user_program; ?>" selected><?php echo $user_program; ?></option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">You can only create groups for your program (<?php echo $user_program; ?>).</p>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="toggleModal('createGroupModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" name="create_group" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">Create Program Group</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Join Program Group Modal -->
    <div id="joinGroupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="enhanced-modal rounded-lg shadow-xl w-full max-w-md">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Join Program Group</h3>
                <button onclick="toggleModal('joinGroupModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Join Code</label>
                    <input type="text" name="join_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Enter 6-digit code">
                    <p class="text-sm text-gray-500 mt-1">You can only join groups from your program (<?php echo $user_program; ?>).</p>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="toggleModal('joinGroupModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" name="join_group" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">Join Program Group</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Upload Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="enhanced-modal rounded-lg shadow-xl w-full max-w-lg">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Manage Group Payment Receipt</h3>
                <button onclick="toggleModal('paymentModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type</label>
                    <select name="payment_type" id="paymentTypeSelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" onchange="showExistingImages()">
                        <option value="">Select payment type</option>
                        <option value="research_forum">Research Forum <?php echo $has_research_forum_payment ? '(Uploaded)' : ''; ?></option>
                        <option value="pre_oral_defense">Pre-Oral Defense <?php echo $has_pre_oral_payment ? '(Uploaded)' : ''; ?></option>
                        <option value="final_defense">Final Defense <?php echo $has_final_defense_payment ? '(Uploaded)' : ''; ?></option>
                    </select>
                </div>
                
                <!-- Existing Images Display -->
                <div id="existingImages" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                    <div id="currentImagesGrid" class="grid grid-cols-2 gap-2 mb-3"></div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Update Payment Receipt Images</label>
                    
                    <!-- Important Notice -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-2"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium mb-1">Important Requirements:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Images must be <strong>clear and readable</strong></li>
                                    <li>Upload a <strong>collage receipt</strong> showing all group members' payments</li>
                                    <li>Ensure all text and amounts are visible</li>
                                    <li>Avoid blurry or dark images</li>
                                    <li><strong>No edited or manipulated images</strong> - receipts will be verified by CRAD</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-1">
                        <label class="cursor-pointer bg-white border-2 border-dashed border-gray-300 rounded-lg px-4 py-6 flex flex-col items-center hover:bg-gray-50 transition w-full">
                            <i class="fas fa-images text-blue-500 text-2xl mb-2"></i>
                            <span class="text-sm font-medium text-gray-700">Choose New Image Files</span>
                            <span class="text-xs text-gray-500 mt-1">JPG, PNG, GIF (Max 5MB each)</span>
                            <input type="file" name="payment_images[]" accept="image/*" multiple required class="hidden" onchange="updateImagePreview(this)">
                        </label>
                    </div>
                    <div id="imagePreview" class="mt-3 grid grid-cols-2 gap-2 hidden"></div>
                    <p class="text-sm text-gray-500 mt-2">Upload new images to replace existing ones. All group members will see the updated images.</p>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="toggleModal('paymentModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" name="upload_payment" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-upload mr-2"></i><span id="uploadButtonText">Upload Images</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Image View Modal -->
    <div id="imageViewModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center p-4 z-50" onclick="closeImageModal()">
        <div class="max-w-4xl max-h-full bg-white rounded-lg overflow-hidden" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Receipt Image</h3>
                <button onclick="closeImageModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" src="" alt="Receipt" class="max-w-full max-h-96 mx-auto rounded-lg">
            </div>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : 'No file chosen';
            const fileLabel = document.getElementById('file-label');
            const fileNameSpan = document.getElementById('file-name');
            
            if (input.files[0]) {
                fileLabel.textContent = 'PDF Selected';
                fileNameSpan.textContent = fileName;
                fileNameSpan.classList.remove('text-gray-500');
                fileNameSpan.classList.add('text-green-600', 'font-medium');
            } else {
                fileLabel.textContent = 'Choose PDF File';
                fileNameSpan.textContent = 'No file chosen';
                fileNameSpan.classList.remove('text-green-600', 'font-medium');
                fileNameSpan.classList.add('text-gray-500');
            }
        }
        
        function updateImagePreview(input) {
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                previewContainer.classList.remove('hidden');
                
                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'relative';
                            imageDiv.innerHTML = `
                                <img src="${e.target.result}" class="w-full h-20 object-cover rounded-lg border">
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b-lg truncate">
                                    ${file.name}
                                </div>
                            `;
                            previewContainer.appendChild(imageDiv);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewContainer.classList.add('hidden');
            }
        }
        
        function viewImage(imageSrc, title) {
            const modal = document.getElementById('imageViewModal');
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            
            modalImage.src = imageSrc;
            modalTitle.textContent = title;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeImageModal() {
            const modal = document.getElementById('imageViewModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        function showExistingImages() {
            const paymentType = document.getElementById('paymentTypeSelect').value;
            const existingImagesDiv = document.getElementById('existingImages');
            const currentImagesGrid = document.getElementById('currentImagesGrid');
            const uploadButtonText = document.getElementById('uploadButtonText');
            
            // Payment data from PHP
            const paymentData = {
                'research_forum': <?php echo json_encode($research_forum_data); ?>,
                'pre_oral_defense': <?php echo json_encode($pre_oral_data); ?>,
                'final_defense': <?php echo json_encode($final_defense_data); ?>
            };
            
            currentImagesGrid.innerHTML = '';
            
            if (paymentType && paymentData[paymentType] && paymentData[paymentType].image_receipts) {
                const images = JSON.parse(paymentData[paymentType].image_receipts);
                existingImagesDiv.classList.remove('hidden');
                uploadButtonText.textContent = 'Update Images';
                
                images.forEach((imagePath, index) => {
                    // Convert path to web-accessible format
                    const webPath = imagePath.replace('../assets/', '/CRAD-system/assets/');
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'relative cursor-pointer hover:opacity-80 transition';
                    imageDiv.onclick = () => viewImage(webPath, `Receipt ${index + 1}`);
                    imageDiv.innerHTML = `
                        <img src="${webPath}" class="w-full h-20 object-cover rounded-lg border" alt="Receipt ${index + 1}">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b-lg">
                            Receipt ${index + 1}
                        </div>
                        <div class="absolute top-1 right-1 bg-black bg-opacity-50 text-white rounded-full p-1">
                            <i class="fas fa-eye text-xs"></i>
                        </div>
                    `;
                    currentImagesGrid.appendChild(imageDiv);
                });
            } else {
                existingImagesDiv.classList.add('hidden');
                uploadButtonText.textContent = 'Upload Images';
            }
        }
    </script>
</body>
</html>