<?php
include('../includes/connection.php'); // Your DB connection
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details from database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM login_tbl WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Close statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-8">My Profile</h1>
                
                <!-- Profile Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-700 p-6 text-white">
                        <div class="flex items-center">
                            <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div class="ml-6">
                                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                                <p class="text-blue-100"><?php echo htmlspecialchars($user['role']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Personal Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500">Full Name</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Role</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($user['role']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Academic Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Academic Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500">Department</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($user['department']); ?></p>
                                    </div>
                                    <?php if ($user['specialization']): ?>
                                    <div>
                                        <p class="text-sm text-gray-500">Specialization</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($user['specialization']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm text-gray-500">Section</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($user['section']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Cluster</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($user['cluster']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="edit-profile.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                <i class="fas fa-edit mr-2"></i>Edit Profile
                            </a>
                            <a href="change-password.php" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                <i class="fas fa-lock mr-2"></i>Change Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>