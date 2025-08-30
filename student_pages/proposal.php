<?php
include('../includes/connection.php');
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
    
    // Check if user has paid
    $payment_query = "SELECT * FROM payments WHERE student_id = '$user_id' AND status = 'completed'";
    $payment_result = mysqli_query($conn, $payment_query);
    $has_paid = mysqli_num_rows($payment_result) > 0;
    
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

// Handle other form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_proposal'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // File upload handling
        $target_dir = "assets/uploads";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = "proposal_" . $group_id . "_" . time() . ".pdf";
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["proposal_file"]["tmp_name"], $target_file)) {
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
            $error_message = "Sorry, there was an error uploading your file.";
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
    

    
    if (isset($_POST['make_payment'])) {
        // Simulate payment processing
        $payment_amount = 100.00;
        $payment_query = "INSERT INTO payments (student_id, amount, status, payment_date) 
                         VALUES ('$user_id', '$payment_amount', 'completed', NOW())";
        if (mysqli_query($conn, $payment_query)) {
            $success_message = "Payment processed successfully!";
            $has_paid = true;
            header("Location: ../student_pages/proposal.php");
            exit();
        } else {
            $error_message = "Error processing payment: " . mysqli_error($conn);
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
    <title>Program Group Proposal Submission</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#7c3aed',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444'
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
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">

    <div class="flex min-h-screen">
        <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>
        
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Status Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <!-- Program Group Status Card -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="crad-icon-circle crad-icon-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Program Group Status</h2>
                            <p class="text-sm text-gray-500">Your program: <span class="font-medium"><?php echo $user_program; ?></span></p>
                        </div>
                    </div>


                </div>

                <?php if ($has_group): ?>
                    <!-- Status Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Group Name -->
                        <div class="p-5 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-blue-800">Program Group</h3>
                                    <p class="text-lg font-medium text-gray-900 mt-1" id="group-name-display"><?php echo $group['name']; ?></p>
                                    <p class="text-sm text-blue-700 mt-1">Program: <?php echo $group['program']; ?></p>
                                </div>
                                <button onclick="toggleGroupNameEdit()" class="text-blue-600 hover:text-blue-800 p-1" title="Edit group name">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                            </div>
                            
                            <!-- Edit form (hidden by default) -->
                            <form method="POST" id="edit-group-name-form" class="hidden mt-3">
                                <input type="hidden" name="update_group_name" value="1">
                                <div class="flex gap-2">
                                    <input type="text" name="new_group_name" value="<?php echo $group['name']; ?>" 
                                           class="flex-1 px-2 py-1 border border-blue-300 rounded text-sm">
                                    <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded text-sm hover:bg-blue-700">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" onclick="toggleGroupNameEdit()" class="bg-gray-400 text-white px-2 py-1 rounded text-sm hover:bg-gray-500">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Cluster Assignment -->
                        <div class="p-5 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                            <h3 class="font-semibold text-purple-800">Cluster Assignment</h3>
                            <?php if ($cluster_info): ?>
                                <p class="text-lg font-medium text-gray-900 mt-1"><?php echo $cluster_info['program']; ?> - Cluster <?php echo $cluster_info['cluster']; ?></p>
                                <p class="text-sm text-purple-700 mt-1">Capacity: <?php echo $cluster_info['student_count']; ?>/<?php echo $cluster_info['capacity']; ?></p>
                            <?php else: ?>
                                <p class="text-lg font-medium text-gray-500 mt-1">Not Assigned</p>
                                <p class="text-sm text-purple-700 mt-1">Waiting for assignment</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Adviser Assignment -->
                        <div class="p-5 bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-xl border border-indigo-200">
                            <h3 class="font-semibold text-indigo-800">Adviser Assignment</h3>
                            <?php if ($cluster_info && $cluster_info['adviser_name']): ?>
                                <p class="text-lg font-medium text-gray-900 mt-1"><?php echo $cluster_info['adviser_name']; ?></p>
                                <p class="text-sm text-indigo-700 mt-1">Faculty Adviser</p>
                            <?php else: ?>
                                <p class="text-lg font-medium text-gray-500 mt-1">Not Assigned</p>
                                <p class="text-sm text-indigo-700 mt-1">Waiting for assignment</p>
                            <?php endif; ?>
                        </div>

                        <!-- Payment Status -->
                        <div class="p-5 bg-gradient-to-r from-<?php echo $has_paid ? 'green' : 'yellow'; ?>-50 to-white rounded-xl border border-<?php echo $has_paid ? 'green' : 'yellow'; ?>-200">
                            <h3 class="font-semibold text-<?php echo $has_paid ? 'green' : 'yellow'; ?>-800">Payment Status</h3>
                            <p class="text-lg font-medium mt-1">
                                <?php echo $has_paid ? '✅ Completed' : '⏳ Pending'; ?>
                            </p>
                            <?php if (!$has_paid): ?>
                                <form method="POST" class="mt-3">
                                    <button type="submit" name="make_payment" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm flex items-center shadow-sm transition-all">
                                        <i class="fas fa-credit-card mr-2"></i> Pay Now
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <!-- Proposal Status -->
                        <div class="p-5 bg-gradient-to-r from-<?php echo $has_proposal ? 'green' : 'gray'; ?>-50 to-white rounded-xl border border-<?php echo $has_proposal ? 'green' : 'gray'; ?>-200">
                            <h3 class="font-semibold text-<?php echo $has_proposal ? 'green' : 'gray'; ?>-800">Proposal Status</h3>
                            <p class="text-lg font-medium mt-1">
                                <?php echo $has_proposal ? ucfirst($proposal['status']) : 'Not Submitted'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Members List -->
                    <div class="mt-8">
                        <h3 class="font-semibold mb-3 text-gray-700">Group Members</h3>
                        <ul class="divide-y divide-gray-200 bg-gray-50 rounded-xl border border-gray-200">
                            <?php
                            $members_query = "SELECT u.*, sp.full_name
                                            FROM group_members gm
                                            JOIN user_tbl u ON gm.student_id = u.user_id
                                            JOIN student_profiles sp ON u.user_id = sp.user_id
                                            WHERE gm.group_id = '$group_id'";
                            $members_result = mysqli_query($conn, $members_query);

                            while ($member = mysqli_fetch_assoc($members_result)) {
                                echo '<li class="py-3 px-4 flex justify-between items-center hover:bg-white transition">';
                                echo '<div>';
                                echo '<p class="font-medium text-gray-800">' . htmlspecialchars($member['full_name']) . '</p>';
                                echo '<p class="text-sm text-gray-500">' . ucfirst($member['role']) . '</p>';
                                echo '</div>';
                                if ($member['user_id'] == $user_id) {
                                    echo '<span class="bg-primary/10 text-primary text-xs font-medium px-2.5 py-1 rounded">You</span>';
                                }
                                echo '</li>';
                            }
                            ?>
                        </ul>
                    </div>

                    <!-- Join Code -->
                    <div class="mt-8">
                        <h3 class="font-semibold mb-3 text-gray-700">Group Join Code</h3>
                        <form id="updateCodeForm" method="POST">
                            <input type="hidden" name="update_join_code" value="1">
                            <input type="hidden" name="new_join_code" id="new_join_code" value="">

                            <div class="flex items-center space-x-3">
                                <div class="relative flex-grow">
                                    <input type="text" id="join_code_value" value="<?php echo $group['join_code']; ?>" readonly 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-lg text-gray-800 shadow-sm">
                                    <button type="button" onclick="copyJoinCode()" 
                                        class="absolute right-2 top-2 p-2 text-gray-500 hover:text-primary transition" title="Copy to clipboard">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                                <button type="button" onclick="generateJoinCode()" 
                                    class="flex items-center bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg shadow-sm transition">
                                    <i class="fas fa-sync-alt mr-2"></i> New Code
                                </button>
                            </div>
                        </form>
                        <p class="text-sm text-gray-500 mt-2">Share this code with other students in your program to let them join your group.</p>

                        <!-- Copy notification -->
                        <div id="copyNotification" class="hidden mt-3 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-lg text-sm shadow-sm">
                            <i class="fas fa-check-circle mr-1"></i> Code copied to clipboard!
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-10">
                        <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700">You are not in a program group yet</h3>
                        <p class="text-gray-500 mb-6">Join an existing program group or create a new one to submit a proposal.</p>
                        <div class="flex justify-center space-x-4">
                            <button onclick="toggleModal('createGroupModal')" 
                                class="flex items-center bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-lg shadow-sm transition">
                                <i class="fas fa-plus mr-2"></i> Create Group
                            </button>
                            <button onclick="toggleModal('joinGroupModal')" 
                                class="flex items-center bg-secondary hover:bg-secondary-dark text-white px-5 py-2.5 rounded-lg shadow-sm transition">
                                <i class="fas fa-sign-in-alt mr-2"></i> Join Group
                            </button>
                        </div>
                    </div>
                    
                    <!-- Available Program Groups -->
                    <div class="mt-8">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Available Program Groups</h3>
                        
                        <!-- Program Filter -->
                        <div class="mb-4 flex items-center">
                            <label class="mr-2 text-sm font-medium text-gray-700">Filter by Program:</label>
                            <select id="program_filter" onchange="filterProgramGroups()" class="px-3 py-1 border border-gray-300 rounded-lg">
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
                                    
                                    echo '<div class="program-group-card p-4 bg-white border border-gray-200 rounded-lg shadow-sm" data-program="' . $available_group['program'] . '">';
                                    echo '<div class="flex justify-between items-start">';
                                    echo '<div>';
                                    echo '<h4 class="font-semibold text-gray-800">' . $available_group['name'] . '</h4>';
                                    echo '<p class="text-sm text-gray-600">Program: ' . $available_group['program'] . '</p>';
                                    echo '<p class="text-sm text-gray-600">Members: ' . $members_count . '/5</p>';
                                    echo '<p class="text-sm text-green-600">Available slots: ' . $available_slots . '</p>';
                                    echo '</div>';
                                    echo '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">' . $available_group['program'] . '</span>';
                                    echo '</div>';
                                    
                                    // Only show join button if user's program matches group's program
                                    if ($user_program === $available_group['program']) {
                                        echo '<form method="POST" class="mt-3">';
                                        echo '<input type="hidden" name="join_code" value="' . $available_group['join_code'] . '">';
                                        echo '<button type="submit" name="join_group" class="w-full bg-primary hover:bg-primary-dark text-white px-3 py-1.5 rounded text-sm">';
                                        echo 'Join Group';
                                        echo '</button>';
                                        echo '</form>';
                                    } else {
                                        echo '<p class="text-sm text-red-500 mt-2">Only ' . $available_group['program'] . ' students can join</p>';
                                    }
                                    
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-gray-500 col-span-2 text-center py-4">No available program groups at the moment.</p>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submission Form (only show if in a group) -->
            <?php if ($has_group): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="crad-icon-circle crad-icon-primary mr-4">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Submit Proposal Letter</h2>
                    </div>
                    
                    <?php if ($has_proposal): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
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
                    
                    <?php if (!$has_paid): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="font-semibold text-yellow-800">Payment Required</h3>
                                    <p class="text-yellow-700">You need to complete the payment before you can submit a proposal.</p>
                                    <form method="POST" class="mt-3">
                                        <button type="submit" name="make_payment" class="bg-primary text-white px-4 py-2 rounded-lg">
                                            <i class="fas fa-credit-card mr-2"></i> Make Payment
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/CRAD-system/student_pages/proposal.php" method="POST" enctype="multipart/form-data" class="space-y-4" <?php echo ($has_proposal || !$has_paid) ? 'onsubmit="return false;"' : ''; ?>>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proposal Title</label>
                            <input type="text" name="title" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary"
                                <?php echo ($has_proposal || !$has_paid) ? 'disabled' : ''; ?>
                                value="<?php echo $has_proposal ? $proposal['title'] : ''; ?>">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary"
                                <?php echo ($has_proposal || !$has_paid) ? 'disabled' : ''; ?>
                            ><?php echo $has_proposal ? $proposal['description'] : ''; ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Signed Proposal (PDF only)</label>
                            <div class="mt-1 flex items-center">
                                <label class="cursor-pointer bg-white border border-gray-300 rounded-lg px-4 py-2 flex items-center hover:bg-gray-50 
                                    <?php echo ($has_proposal || !$has_paid) ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    <span class="text-sm font-medium">Choose File</span>
                                    <input type="file" name="proposal_file" accept=".pdf" required 
                                        <?php echo ($has_proposal || !$has_paid) ? 'disabled' : ''; ?>
                                        class="hidden">
                                </label>
                                <span class="ml-3 text-sm text-gray-500" id="file-name">
                                    <?php echo $has_proposal ? basename($proposal['file_path']) : 'No file chosen'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" name="submit_proposal" 
                                class="bg-gradient-to-r from-primary to-secondary text-white px-6 py-2 rounded-lg hover:shadow-md transition flex items-center
                                <?php echo ($has_proposal || !$has_paid) ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md'; ?>">
                                <i class="fas fa-paper-plane mr-2"></i> 
                                <?php echo $has_proposal ? 'Proposal Already Submitted' : 'Submit Proposal'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Program Group Modal -->
    <div id="createGroupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold text-gray-800">Create New Program Group</h3>
            </div>
            <form method="POST" action="student_pages/proposal.php" class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Name</label>
                    <input type="text" name="group_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select name="program" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary">
                        <option value="<?php echo $user_program; ?>" selected><?php echo $user_program; ?></option>
                        <!-- Add other programs if needed -->
                    </select>
                    <p class="text-sm text-gray-500 mt-1">You can only create groups for your program (<?php echo $user_program; ?>).</p>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="toggleModal('createGroupModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="create_group" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark">Create Program Group</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Join Program Group Modal -->
    <div id="joinGroupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold text-gray-800">Join Program Group</h3>
                <p class="text-sm text-gray-500 mt-1">Your program: <?php echo $user_program; ?></p>
            </div>
            <form method="POST" action="/CRAD-system/student_pages/proposal.php" class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Join Code</label>
                    <input type="text" name="join_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Enter 6-digit code">
                    <p class="text-sm text-gray-500 mt-1">You can only join groups from your program (<?php echo $user_program; ?>).</p>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="toggleModal('joinGroupModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="join_group" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark">Join Program Group</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Display selected file name
        document.querySelector('input[name="proposal_file"]')?.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>