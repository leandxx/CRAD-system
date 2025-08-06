<?php
include("../includes/connection.php");
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
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Hide labels when collapsed */
        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .profile-name,
        .sidebar-collapsed .profile-role,
        .sidebar-collapsed .portal-title {
            display: none;
        }

        .sidebar-collapsed {
            width: 4rem;
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
            <span class="text-lg font-bold portal-title">CRAD Student Portal</span>
        </div>
        <button class="text-white focus:outline-none" id="toggleSidebar">
         <i class="fas fa-bars text-2xl"></i> <!-- 2xl = 1.5rem -->
        </button>
    </div>

    <!-- Profile Section -->
    <div class="p-4 flex items-center space-x-3 bg-blue-900">
        <img src="https://placehold.co/50x50" alt="Student profile" class="rounded-full">
        <div>
            <p class="font-medium profile-name">John D. Researcher</p>
            <p class="text-xs text-blue-200 profile-role">PhD Candidate</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 pb-4 hide-scrollbar overflow-y-auto">

        <!-- Research Process -->
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
                    <a href="student_pages/adviser.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-user-tie nav-icon"></i>
                        <span class="nav-text">Adviser Assignment</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/defense.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-calendar-check nav-icon"></i>
                        <span class="nav-text">Defense Scheduling</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/documents.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span class="nav-text">Document Tracker</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Support Services -->
        <div class="px-4 py-3 border-t border-blue-700">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Support Services</p>
            <ul>
                <li>
                    <a href="student_pages/payments.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-money-bill-wave nav-icon"></i>
                        <span class="nav-text">Payment Verification</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/facilities.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-microscope nav-icon"></i>
                        <span class="nav-text">Research Facilities</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/analytics.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-chart-line nav-icon"></i>
                        <span class="nav-text">Analytics</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Events -->
        <div class="px-4 py-3 border-t border-blue-700">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Events</p>
            <ul>
                <li>
                    <a href="student_pages/seminars.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-calendar-day nav-icon"></i>
                        <span class="nav-text">Seminars/Festivals</span>
                    </a>
                </li>
                <li>
                    <a href="student_pages/groups.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">Research Groups</span>
                    </a>
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
