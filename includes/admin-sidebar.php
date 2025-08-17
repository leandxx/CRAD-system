<?php
session_start();
include("../includes/connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get username and role from user_tbl
$sql = "SELECT role, role FROM user_tbl WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$role = $user['role'];
// Create greeting based on role
$greeting = "Hello, " . match($role) {
    'admin' => 'Admin',
    'student' => 'Student',
    default => 'User'
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="http://localhost/CRAD-system/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRAD Admin Sidebar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .hide-scrollbar {
            overflow-y: auto;
            -ms-overflow-style: auto;
            scrollbar-width: thin;
            scrollbar-gutter: stable;
            padding-right: 8px;
        }

        .hide-scrollbar::-webkit-scrollbar {
            width: 12px;
        }

        .hide-scrollbar::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 12px;
        }

        .hide-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.22);
            border-radius: 8px;
            border: 3px solid transparent;
            background-clip: padding-box;
        }

        .hide-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0,0,0,0.34);
        }

        .hide-scrollbar {
            scrollbar-color: rgba(0,0,0,0.22) transparent;
        }

        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .profile-name,
        .sidebar-collapsed .profile-role,
        .sidebar-collapsed .portal-title,
        .sidebar-collapsed .portal-logo,
        .sidebar-collapsed .sidebar-text { 
            display: none;
        }

        .sidebar-collapsed {
            width: 5rem;
        }

        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }

        .sidebar img.logo-img {
            transition: all 0.3s ease;
        }

        .sidebar-collapsed img.logo-img {
            width: 2.5rem;
            height: 2.5rem;
        }

        .sidebar .nav-icon {
            width: 1.25rem;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Sidebar Container -->
<div class="sidebar sidebar-transition bg-blue-800 text-white flex flex-col hidden md:flex h-screen w-64" id="sidebar">

    <!-- Logo + Title -->
    <div class="p-4 flex items-center justify-between border-b border-blue-700">
        <div class="flex items-center space-x-2">
            <div class="portal-logo">
               <img src="assets/img/sms-logo.png" alt="University Logo" class="h-14 w-16 logo-img">
            </div>
            <span class="text-bs font-bold portal-title">CRAD Admin Portal</span>
        </div>
        <div class="pr-3">
            <button class="text-white focus:outline-none" id="toggleSidebar">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="p-4 flex items-center space-x-3 bg-blue-900">
        <img src="assets/img/me.png" alt="Admin profile" class="rounded-full h-12 w-12">
        <div>
            <p class="font-medium profile-name"><?php echo htmlspecialchars($greeting); ?></p>
            <p class="text-xs text-blue-200 profile-role"><?php echo htmlspecialchars($role); ?></p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 pb-4 hide-scrollbar overflow-y-auto">

        <!-- Dashboard -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Dashboard</p>
            <ul>
                <li>
                    <a href="admin-pages/admin-dashboard.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Admin Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Account Management -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Account Management</p>
            <ul>
                <li>
                    <a href="admin-pages/manage-student.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-user-graduate nav-icon"></i>
                        <span class="nav-text">Manage Students</span>
                    </a>
                </li>
            
                <li>
                    <a href="admin_pages/manage-admins.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-user-shield nav-icon"></i>
                        <span class="nav-text">Manage Admins</span>
                    </a>
                </li>
            </ul>
        </div>


        <!-- Research Management -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Research Management</p>
            <ul>
                <li>
                    <a href="admin-pages/admin-proposals.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span class="nav-text">Proposals</span>
                    </a>
                </li>
                <li>
                    <a href="admin_pages/defense-scheduling.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Defense Scheduling</span>
                    </a>
                </li>
                <li>
                    <a href="admin_pages/research-categories.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-tags nav-icon"></i>
                        <span class="nav-text">Research Categories</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- System Settings -->
        <div class="px-4 py-3 border-t border-blue-700">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">System Settings</p>
            <ul>
                <li>
                    <a href="admin_pages/system-settings.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-cog nav-icon"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <li>
                    <form action="../CRAD-system/auth/logout.php" method="POST">
                        <button type="submit" class="w-full text-left flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700 text-red-300">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span class="sidebar-text">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </nav>
</div>

<!-- JS TOGGLE -->
<script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');
    });
</script>

</body>
</html>