<?php
session_start();
include('../includes/connection.php'); // Your DB connection
include('../includes/notification-helper.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_admin'])) {
        // Create new admin
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'Admin'; // Default role for created admins
        
        $check_query = "SELECT * FROM user_tbl WHERE email='$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Email already exists!";
            $message_type = "error";
        } else {
            $query = "INSERT INTO user_tbl (email, password, role, created_at) 
                     VALUES ('$email', '$password', '$role', NOW())";
            
            if (mysqli_query($conn, $query)) {
                $message = "Admin account created successfully!";
                $message_type = "success";
            } else {
                $message = "Error creating admin account: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    } elseif (isset($_POST['update_admin'])) {
        // Update admin
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Check if email already exists for another user
        $check_query = "SELECT * FROM user_tbl WHERE email='$email' AND user_id != '$user_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Email already exists!";
            $message_type = "error";
        } else {
            $query = "UPDATE user_tbl SET email='$email'";
            
            // Update password only if provided
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query .= ", password='$password'";
            }
            
            $query .= " WHERE user_id='$user_id'";
            
            if (mysqli_query($conn, $query)) {
                $message = "Admin account updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating admin account: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    } elseif (isset($_POST['delete_admin'])) {
        // Delete admin
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        
        // Prevent deletion of own account
        if ($user_id == $_SESSION['user_id']) {
            $message = "You cannot delete your own account!";
            $message_type = "error";
        } else {
            $query = "DELETE FROM user_tbl WHERE user_id='$user_id'";
            
            if (mysqli_query($conn, $query)) {
                $message = "Admin account deleted successfully!";
                $message_type = "success";
            } else {
                $message = "Error deleting admin account: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    } 
    // Create Staff (was Sub Admin)
    if (isset($_POST['create_staff'])) {
        $email = mysqli_real_escape_string($conn, $_POST['staff_email']);
        $password = password_hash($_POST['staff_password'], PASSWORD_DEFAULT);
        $role = 'staff'; // Make sure this matches your SELECT query

        $check_query = "SELECT * FROM user_tbl WHERE email='$email'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "Staff email already exists!";
            $message_type = "error";
        } else {
            $query = "INSERT INTO user_tbl (email, password, role, created_at) 
                     VALUES ('$email', '$password', '$role', NOW())";

            if (mysqli_query($conn, $query)) {
                $message = "Staff account created successfully!";
                $message_type = "success";
            } else {
                $message = "Error creating staff account: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    }

    // Update Staff
    if (isset($_POST['update_staff'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $check_query = "SELECT * FROM user_tbl WHERE email='$email' AND user_id != '$user_id'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "Staff email already exists!";
            $message_type = "error";
        } else {
            $query = "UPDATE user_tbl SET email='$email'";
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query .= ", password='$password'";
            }
            $query .= " WHERE user_id='$user_id' AND role='staff'";

            if (mysqli_query($conn, $query)) {
                $message = "Staff account updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating staff account: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    }

    // Delete Staff
    if (isset($_POST['delete_staff'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $query = "DELETE FROM user_tbl WHERE user_id='$user_id' AND role='staff'";
        if (mysqli_query($conn, $query)) {
            $message = "Staff account deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting staff account: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}

// Fetch all admin accounts
$query = "SELECT * FROM user_tbl WHERE role='Admin' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$admins = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch all staff accounts
$staff_query = "SELECT * FROM user_tbl WHERE role='staff' ORDER BY created_at DESC";
$staff_result = mysqli_query($conn, $staff_query);
$staffs = mysqli_fetch_all($staff_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
        .gradient-blue {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
        .gradient-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
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
        .modal-overlay {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
            backdrop-filter: blur(4px);
        }
        .modal-content {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
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
    </script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 font-sans h-screen overflow-hidden">

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include('../includes/admin-sidebar.php'); ?>

        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-7xl mx-auto">
                
                <!-- Notification Message -->
                <?php if (!empty($message)): ?>
                    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Create Admin Form -->
                <div class="stats-card p-8 mb-8 animate-slide-up">
                    <div class="flex items-center mb-6">
                        <div class="gradient-blue p-3 rounded-xl mr-4">
                            <i class="fas fa-user-shield text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Create New Admin</h2>
                    </div>
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" id="password" name="password" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="create_admin" 
                                class="gradient-blue text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>Create Admin
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Create Staff Form -->
                <div class="stats-card p-8 mb-8 animate-fade-in">
                    <div class="flex items-center mb-6">
                        <div class="gradient-purple p-3 rounded-xl mr-4">
                            <i class="fas fa-user-tie text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Create New Staff</h2>
                    </div>
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="staff_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="staff_email" name="staff_email" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="staff_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" id="staff_password" name="staff_password" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="create_staff" 
                                class="gradient-purple text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>Create Staff
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Admins List -->
                <div class="stats-card p-8 animate-scale-in">
                    <div class="flex items-center mb-6">
                        <div class="gradient-blue p-3 rounded-xl mr-4">
                            <i class="fas fa-users-cog text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Admin Accounts</h2>
                    </div>
                    
                    <?php if (count($admins) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $admin['user_id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $admin['email']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y g:i A', strtotime($admin['created_at'])); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <!-- Edit Button -->
                                                <button onclick="openEditModal(<?php echo $admin['user_id']; ?>, '<?php echo $admin['email']; ?>')" 
                                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                
                                                <!-- Delete Button -->
                                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $admin['user_id']; ?>">
                                                    <button type="submit" name="delete_admin" 
                                                        class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No admin accounts found.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Staff List -->
                <div class="stats-card p-8 mt-8 animate-scale-in">
                    <div class="flex items-center mb-6">
                        <div class="gradient-purple p-3 rounded-xl mr-4">
                            <i class="fas fa-user-friends text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Staff Accounts</h2>
                    </div>
                    <?php if (count($staffs) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($staffs as $staff): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $staff['user_id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $staff['email']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y g:i A', strtotime($staff['created_at'])); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <!-- Edit Button -->
                                                <button onclick="openStaffEditModal(<?php echo $staff['user_id']; ?>, '<?php echo $staff['email']; ?>')" 
                                                    class="text-purple-600 hover:text-purple-900 mr-3">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <!-- Delete Button -->
                                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this staff?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $staff['user_id']; ?>">
                                                    <button type="submit" name="delete_staff" 
                                                        class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No staff accounts found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay fixed inset-0 flex items-center justify-center hidden">
        <div class="modal-content p-8 w-full max-w-md">
            <div class="flex items-center mb-6">
                <div class="gradient-blue p-3 rounded-xl mr-4">
                    <i class="fas fa-user-edit text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Admin Account</h2>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="update_admin" value="1">
                
                <div class="mb-4">
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="edit_email" name="email" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div class="mb-4">
                    <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-primary text-white rounded-md hover:bg-blue-600 transition-colors">
                        Update Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Staff Edit Modal -->
    <div id="staffEditModal" class="modal-overlay fixed inset-0 flex items-center justify-center hidden">
        <div class="modal-content p-8 w-full max-w-md">
            <div class="flex items-center mb-6">
                <div class="gradient-purple p-3 rounded-xl mr-4">
                    <i class="fas fa-user-edit text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Staff Account</h2>
            </div>
            <form method="POST" id="staffEditForm">
                <input type="hidden" name="user_id" id="staff_edit_user_id">
                <input type="hidden" name="update_staff" value="1">
                
                <div class="mb-4">
                    <label for="staff_edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="staff_edit_email" name="email" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                
                <div class="mb-4">
                    <label for="staff_edit_password" class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                    <input type="password" id="staff_edit_password" name="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStaffEditModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-secondary text-white rounded-md hover:bg-purple-600 transition-colors">
                        Update Staff
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openEditModal(userId, email) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_email').value = email;
            document.getElementById('editModal').classList.remove('hidden');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Staff Modal functions
        function openStaffEditModal(userId, email) {
            document.getElementById('staff_edit_user_id').value = userId;
            document.getElementById('staff_edit_email').value = email;
            document.getElementById('staffEditModal').classList.remove('hidden');
        }
        function closeStaffEditModal() {
            document.getElementById('staffEditModal').classList.add('hidden');
        }
        document.getElementById('staffEditModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeStaffEditModal();
            }
        });
    </script>
</body>
</html>