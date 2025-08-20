<?php
include("../includes/connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$sql = "SELECT role FROM user_tbl WHERE user_id = ?";
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
    <title>CRAD Student Sidebar</title>
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
        .sidebar-collapsed .section-title,
        .sidebar-collapsed .portal-logo { 
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

        .nav-item {
            transition: all 0.2s ease;
            border-radius: 0.375rem;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body>

<!-- Sidebar Container -->
<div class="sidebar sidebar-transition bg-blue-800 text-white flex flex-col hidden md:flex h-screen w-64" id="sidebar">

    <!-- Logo + Title -->
    <div class="p-4 flex items-center justify-between border-b border-blue-700">
        <div class="flex items-center space-x-2">
            <div class="portal-logo">
               <img src="assets/img/sms-logo.png" alt="University Logo" class="h-14 w-16 logo-img">
            </div>
            <span class="text-bs font-bold portal-title">CRAD Student Portal</span>
        </div>
        <div class="pr-3">
            <button class="text-white focus:outline-none" id="toggleSidebar">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="p-4 flex items-center space-x-3 bg-blue-900">
        <img src="assets/img/me.png" alt="Student profile" class="rounded-full h-12 w-12">
        <div>
            <p class="font-medium profile-name"><?php echo htmlspecialchars($greeting); ?></p>
            <p class="text-xs text-blue-200 profile-role"><?php echo htmlspecialchars(ucfirst($role)); ?></p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 pb-4 hide-scrollbar overflow-y-auto">

        <!-- Dashboard -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 section-title">Research Process</p>
            <ul class="space-y-1">
                <li>
                    <a href="student_pages/student.php" class="nav-item flex items-center space-x-3 px-3 py-2" data-page-title="Dashboard">
                        <i class="fas fa-home nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Research Management -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 section-title">Research Workflow</p>
            <ul class="space-y-1">
                <li>
                    <a href="student_pages/proposal.php" class="nav-item flex items-center space-x-3 px-3 py-2"data-page-title="Proposal Submission">
                        <i class="fas fa-file-upload nav-icon"></i>
                        <span class="nav-text">Proposal Submission</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/defense.php" class="nav-item flex items-center space-x-3 px-3 py-2"data-page-title="Defense Scheduling">
                        <i class="fas fa-calendar-check nav-icon"></i>
                        <span class="nav-text">Defense Scheduling</span>
                    </a>
                </li>
                
            </ul>
        </div>

        <!-- Account -->
        <div class="px-4 py-3 border-t border-blue-700">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 section-title">Account</p>
            <ul class="space-y-1">
                <li>
                    <a href="student_pages/profile.php" class="nav-item flex items-center space-x-3 px-3 py-2">
                        <i class="fas fa-user nav-icon"></i>
                        <span class="nav-text">Profile Settings</span>
                    </a>
                </li>
                <li>
                    <form action="../CRAD-system/auth/student-logout.php" method="POST">
                        <button type="submit" class="nav-item w-full flex items-center space-x-3 px-3 py-2 text-red-300 hover:text-white">
                            <i class="fas fa-sign-out-alt nav-icon"></i>
                            <span class="nav-text">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </nav>
</div>
<!-- Header -->
<div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <div class="flex items-center">
                    <h1 id="page-title" class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                       
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative transition-all hover:scale-105">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 notification-dot pulse"></span>
                    </button>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <i class="fas fa-chevron-down text-xs opacity-70 group-hover:opacity-100 transition"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <a href="student_pages/student-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-circle mr-2"></i>Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i>Settings</a>
                            <a href="auth/student-logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </div>
                </div>
            </header>

<script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');
    });

    // Function to update page title based on clicked link
    function updatePageTitle(linkText) {
        const titleElement = document.getElementById('page-title');
        titleElement.textContent = linkText;
        // Store the title in localStorage
        localStorage.setItem('selectedPageTitle', linkText);
    }

    document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a stored title and restore it
    const storedTitle = localStorage.getItem('selectedPageTitle');
    if (storedTitle) {
        document.getElementById('page-title').textContent = storedTitle;
    }

    const navLinks = document.querySelectorAll('nav a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Use data attribute if available, otherwise use text content
            const pageTitle = this.dataset.pageTitle || this.querySelector('.nav-text').textContent.trim();
            updatePageTitle(pageTitle);
        });
    });

    // Add this for the profile link in the dropdown menu
    const profileLink = document.querySelector('a[href="student_pages/student-profile.php"]');
    if (profileLink) {
        profileLink.addEventListener('click', function(e) {
            updatePageTitle('Student Profile');
        });
    }
});
</script>

</body>
</html>