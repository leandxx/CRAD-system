<?php
include('../includes/connection.php');
session_start(); // <- Kailangan ito sa bawat page bago gamitin $_SESSION

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header("Location: ../student_pages/student.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
    
    // Check if group has already submitted a proposal
    $proposal_query = "SELECT * FROM proposals WHERE group_id = '$group_id'";
    $proposal_result = mysqli_query($conn, $proposal_query);
    $has_proposal = mysqli_num_rows($proposal_result) > 0;
    
    if ($has_proposal) {
        $proposal = mysqli_fetch_assoc($proposal_result);
    }
    
    // Check if user has paid (payment is per student, not per group)
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
}

// Handle other form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_proposal'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // File upload handling
        $target_dir = "../uploads/proposals/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = "proposal_" . $group_id . "_" . time() . ".pdf";
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["proposal_file"]["tmp_name"], $target_file)) {
            $insert_query = "INSERT INTO proposals (group_id, title, description, file_path) 
                            VALUES ('$group_id', '$title', '$description', '$target_file')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success_message = "Proposal submitted successfully!";
                $has_proposal = true;
                // Refresh page to show updated status
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
        
        // Generate a unique join code (6 characters)
        $join_code = substr(strtoupper(md5(uniqid(rand(), true))), 0, 6);
        
        // Create new group
        $create_group_query = "INSERT INTO groups (name, join_code) VALUES ('$group_name', '$join_code')";
        if (mysqli_query($conn, $create_group_query)) {
            $new_group_id = mysqli_insert_id($conn);
            
            // Add user as group member (no leader concept in your schema)
            $add_member_query = "INSERT INTO group_members (group_id, student_id) VALUES ('$new_group_id', '$user_id')";
            if (mysqli_query($conn, $add_member_query)) {
                $success_message = "Group created successfully!";
                // Refresh page to show updated status
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
            
            // Check if user is already in a group
            if ($has_group) {
                $error_message = "You are already in a group. Leave your current group first.";
            } else {
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
                        $success_message = "Joined group successfully!";
                        // Refresh page to show updated status
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
    
    if (isset($_POST['leave_group'])) {
        // Remove user from group
        $leave_query = "DELETE FROM group_members WHERE group_id = '$group_id' AND student_id = '$user_id'";
        if (mysqli_query($conn, $leave_query)) {
            $success_message = "You have left the group.";
            header("Location: ../student_pages/proposal.php");
            exit();
        } else {
            $error_message = "Error leaving group: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['make_payment'])) {
        // Simulate payment processing
        $payment_amount = 100.00; // Example amount
        $payment_query = "INSERT INTO payments (student_id, amount, status, payment_date) 
                         VALUES ('$user_id', '$payment_amount', 'completed', NOW())";
        if (mysqli_query($conn, $payment_query)) {
            $success_message = "Payment processed successfully!";
            $has_paid = true;
            header("Location: proposal.php");
            exit();
        } else {
            $error_message = "Error processing payment: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['remove_member'])) {
        $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
        $remove_query = "DELETE FROM group_members WHERE group_id = '$group_id' AND student_id = '$member_id'";
        if (mysqli_query($conn, $remove_query)) {
            $success_message = "Member removed successfully!";
            header("Location: proposal.php");
            exit();
        } else {
            $error_message = "Error removing member: " . mysqli_error($conn);
        }
    }
}

// Get user information for display
$user_query = "SELECT * FROM user_tbl WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Submission</title>
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
            // Show loading animation
            document.getElementById('joinCodeDisplay').classList.add('hidden');
            document.getElementById('joinCodeLoading').classList.remove('hidden');
            
            // Generate a random 6-character code
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 6; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            
            // Update the hidden input value
            document.getElementById('new_join_code').value = result;
            
            // Submit the form after a short delay to show the animation
            setTimeout(function() {
                document.getElementById('updateCodeForm').submit();
            }, 800);
        }
        
        function copyJoinCode() {
            const joinCode = document.getElementById('join_code_value').textContent;
            navigator.clipboard.writeText(joinCode).then(function() {
                // Show copied notification
                const notification = document.getElementById('copyNotification');
                notification.classList.remove('hidden');
                setTimeout(function() {
                    notification.classList.add('hidden');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
        
        function confirmLeaveGroup() {
            return confirm("Are you sure you want to leave this group?");
        }
        
        function confirmRemoveMember(memberName) {
            return confirm("Are you sure you want to remove " + memberName + " from the group?");
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

            <!-- Group Status Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="bg-primary/10 p-3 rounded-full mr-4">
                            <i class="fas fa-users text-primary text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Group Status</h2>
                    </div>
                    
                    <?php if ($has_group): ?>
                        <form method="POST" onsubmit="return confirmLeaveGroup();">
                            <button type="submit" name="leave_group" class="bg-danger text-white px-4 py-2 rounded-lg hover:bg-danger-dark flex items-center">
                                <i class="fas fa-sign-out-alt mr-2"></i> Leave Group
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <?php if ($has_group): ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800">Group Name</h3>
                            <p class="text-lg"><?php echo $group['name']; ?></p>
                        </div>
                        
                        <div class="bg-<?php echo $has_paid ? 'green' : 'yellow'; ?>-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-<?php echo $has_paid ? 'green' : 'yellow'; ?>-800">Payment Status</h3>
                            <p class="text-lg"><?php echo $has_paid ? 'Completed' : 'Pending'; ?></p>
                            <?php if (!$has_paid): ?>
                                <form method="POST" class="mt-2">
                                    <button type="submit" name="make_payment" class="bg-success text-white px-3 py-1 rounded text-sm">
                                        <i class="fas fa-credit-card mr-1"></i> Pay Now
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bg-<?php echo $has_proposal ? 'green' : 'gray'; ?>-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-<?php echo $has_proposal ? 'green' : 'gray'; ?>-800">Proposal Status</h3>
                            <p class="text-lg"><?php echo $has_proposal ? ucfirst($proposal['status']) : 'Not Submitted'; ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="font-semibold mb-2">Group Members</h3>
                        <ul class="divide-y divide-gray-200">
                            <?php
                            $members_query = "SELECT u.* 
                                            FROM group_members gm 
                                            JOIN user_tbl u ON gm.student_id = u.user_id 
                                            WHERE gm.group_id = '$group_id'";
                            $members_result = mysqli_query($conn, $members_query);
                            
                            while ($member = mysqli_fetch_assoc($members_result)) {
                                echo '<li class="py-2 flex justify-between items-center">';
                                echo '<div>';
                                echo '<p class="font-medium">' . $member['email'] . '</p>';
                                echo '<p class="text-sm text-gray-500">' . ucfirst($member['role']) . '</p>';
                                echo '</div>';
                                echo '<div class="flex items-center">';
                                if ($member['user_id'] == $user_id) {
                                    echo '<span class="bg-primary/10 text-primary text-xs font-medium px-2.5 py-0.5 rounded mr-2">You</span>';
                                }
                                echo '</div>';
                                echo '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="font-semibold mb-2">Group Join Code</h3>
                        <form id="updateCodeForm" method="POST">
                            <input type="hidden" name="update_join_code" value="1">
                            <input type="hidden" name="new_join_code" id="new_join_code" value="">
                            
                            <div class="flex items-center mb-2">
                                <div class="relative flex-grow">
                                    <div id="joinCodeDisplay" class="flex items-center">
                                        <input type="text" id="join_code_value" value="<?php echo $group['join_code']; ?>" readonly 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-lg">
                                        <button type="button" onclick="copyJoinCode()" class="ml-2 p-2 text-gray-500 hover:text-primary" title="Copy to clipboard">
                                            <i class="far fa-copy"></i>
                                        </button>
                                    </div>
                                    <div id="joinCodeLoading" class="hidden flex items-center justify-center py-2">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                        <span class="ml-2">Generating new code...</span>
                                    </div>
                                </div>
                                <button type="button" onclick="generateJoinCode()" class="ml-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark flex items-center">
                                    <i class="fas fa-sync-alt mr-2"></i> Generate New Code
                                </button>
                            </div>
                        </form>
                        <p class="text-sm text-gray-500">Share this code with other students to let them join your group.</p>
                        
                        <!-- Copy notification -->
                        <div id="copyNotification" class="hidden mt-2 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded text-sm">
                            <i class="fas fa-check-circle mr-1"></i> Code copied to clipboard!
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600">You are not in a group yet</h3>
                        <p class="text-gray-500 mb-6">Join an existing group or create a new one to submit a proposal.</p>
                        <div class="flex justify-center space-x-4">
                            <button onclick="toggleModal('createGroupModal')" class="bg-primary text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-plus mr-2"></i> Create Group
                            </button>
                            <button onclick="toggleModal('joinGroupModal')" class="bg-secondary text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-sign-in-alt mr-2"></i> Join Group
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submission Form (only show if in a group) -->
            <?php if ($has_group): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-primary/10 p-3 rounded-full mr-4">
                            <i class="fas fa-file-upload text-primary text-xl"></i>
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
                    
                    <form action="proposal.php" method="POST" enctype="multipart/form-data" class="space-y-4" <?php echo ($has_proposal || !$has_paid) ? 'onsubmit="return false;"' : ''; ?>>
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

    <!-- Create Group Modal -->
    <div id="createGroupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold text-gray-800">Create New Group</h3>
            </div>
            <form method="POST" action="proposal.php" class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Name</label>
                    <input type="text" name="group_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="toggleModal('createGroupModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="create_group" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark">Create Group</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Join Group Modal -->
    <div id="joinGroupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold text-gray-800">Join Existing Group</h3>
            </div>
            <form method="POST" action="/CRAD-system/student_pages/proposal.php" class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Join Code</label>
                    <input type="text" name="join_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Enter 6-digit code">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="toggleModal('joinGroupModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="join_group" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark">Join Group</button>
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