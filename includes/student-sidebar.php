<?php
session_start();
include("../includes/connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get role from user_tbl
$sql = "SELECT role FROM user_tbl WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$role = $user['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="http://localhost/CRAD-system/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRAD Student Sidebar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .hide-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.25);
            border-radius: 4px;
        }
        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .profile-name,
        .sidebar-collapsed .profile-role,
        .sidebar-collapsed .portal-title,
        .sidebar-collapsed .portal-logo {
            display: none;
        }
        .sidebar-collapsed {
            width: 5rem;
        }
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
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

<!-- Sidebar -->
<div class="sidebar sidebar-transition bg-blue-800 text-white flex flex-col hidden md:flex h-screen w-64" id="sidebar">

    <!-- Header: Logo + Toggle -->
    <div class="p-4 flex items-center justify-between border-b border-blue-700">
        <div class="flex items-center space-x-2">
            <div class="portal-logo">
                <img src="assets/img/sms-logo.png" alt="University Logo" class="h-14 w-16 logo-img">
            </div>
            <span class="text-base font-bold portal-title">CRAD Student Portal</span>
        </div>
        <button class="text-white focus:outline-none" id="toggleSidebar">
            <i class="fas fa-bars text-2xl"></i>
        </button>
    </div>

    <!-- Profile Section -->
    <div class="p-4 flex items-center space-x-3 bg-blue-900">
    <img src="assets/img/me.png" alt="Student profile" class="rounded-full h-14 w-14">
    <div>
        <p class="font-medium profile-name">Hello, <?php echo htmlspecialchars(ucfirst($role)); ?></p>
        <p class="text-xs text-blue-200 profile-role"><?php echo htmlspecialchars($role); ?></p>
    </div>

    <!-- Nav Links (Scrollable Middle) -->
    <div class="flex-1 hide-scrollbar overflow-y-auto">
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Research Process</p>
            <ul>
                <li>
                    <a href="student_pages/student.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-home nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/proposal.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-file-upload nav-icon"></i>
                        <span class="nav-text">Proposal Submission</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/documents.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span class="nav-text">Document Tracker</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/defense.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-calendar-check nav-icon"></i>
                        <span class="nav-text">Defense Scheduling</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Logout Button (Fixed Bottom) -->
    <div class="px-4 py-3 border-t border-blue-700">
        <form action="../CRAD-system/auth/logout.php" method="POST">
            <button type="submit" class="w-full text-left flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700 text-red-300">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="sidebar-text">Logout</span>
            </button>
        </form>
    </div>
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