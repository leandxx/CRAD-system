<?php
session_start();
include("../includes/connection.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username = $_SESSION['username'] ?? ''; // ADD THIS
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRAD Faculty Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Custom scrollbar for Webkit browsers (Chrome, Safari, Edge) */
#sidebar nav::-webkit-scrollbar {
    width: 8px; /* width of scrollbar */
}

#sidebar nav::-webkit-scrollbar-track {
    background: #1e40af; /* dark blue track, matches sidebar color */
    border-radius: 4px;
}

#sidebar nav::-webkit-scrollbar-thumb {
    background-color: #3b82f6; /* bright blue thumb */
    border-radius: 4px;
    border: 2px solid #1e40af; /* border same as track for padding effect */
}

#sidebar nav::-webkit-scrollbar-thumb:hover {
    background-color: #60a5fa; /* lighter blue on hover */
}

/* Firefox scrollbar styling */
#sidebar nav {
    scrollbar-width: thin;
    scrollbar-color: #3b82f6 #1e40af; /* thumb and track */
}

    </style>
</head>
<body class="bg-gray-50 font-sans">

<!-- Sidebar -->
<div id="sidebar" class="w-64 bg-blue-800 text-white flex flex-col transition-all duration-300 ease-in-out">
    <!-- Logo + Toggle -->
    <div class="p-4 flex items-center justify-between border-b border-blue-700">
        <div class="flex items-center space-x-3">
            <img src="../assets/img/sms-logo.png" alt="CRAD Logo" class="h-8 w-8">
            <span id="sidebar-title" class="text-xl font-bold">CRAD Faculty Portal</span>
        </div>
        <button onclick="toggleSidebar()" class="text-white focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- User Info -->
    <div class="p-4 flex items-center space-x-3 bg-blue-900" id="sidebar-profile">
        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white">
            <i class="fas fa-user-tie"></i>
        </div>
        <div>
            <p class="font-medium"><?= $_SESSION['name'] ?? 'Faculty Member'; ?></p>
            <p class="text-xs text-blue-200"><?= $_SESSION['role'] ?? 'Research Adviser'; ?></p>
        </div>
    </div>

   <!-- Nav Links -->
        <nav class="flex-1 overflow-y-auto pb-4">
            <div class="px-4 py-3">
                <p class="text-xs uppercase text-blue-300 font-semibold mb-2 sidebar-section">Adviser Tools</p>
                <ul>
                    <li>
                        <a href="/CRAD-system/faculty-pages/faculty-dashboard.php" class="flex items-center space-x-3 px-3 py-2 rounded <?= $current == 'dashboard' ? 'bg-blue-700 text-white' : 'hover:bg-blue-700' ?>">
                            <i class="fas fa-tachometer-alt w-5"></i>
                            <span class="sidebar-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/CRAD-system/faculty-pages/supervision.php" class="flex items-center space-x-3 px-3 py-2 rounded <?= $current == 'supervision' ? 'bg-blue-700 text-white' : 'hover:bg-blue-700' ?>">
                            <i class="fas fa-user-graduate w-5"></i>
                            <span class="sidebar-text">Student Supervision</span>
                        </a>
                    </li>
                    <li>
                        <a href="/CRAD-system/faculty-pages/proposal.php" class="flex items-center space-x-3 px-3 py-2 rounded <?= $current == 'proposal' ? 'bg-blue-700 text-white' : 'hover:bg-blue-700' ?>">
                            <i class="fas fa-file-alt w-5"></i>
                            <span class="sidebar-text">Proposal Review</span>
                        </a>
                    </li>
                  
                    <li>
                        <a href="/CRAD-system/faculty-pages/progress.php" class="flex items-center space-x-3 px-3 py-2 rounded <?= $current == 'progress' ? 'bg-blue-700 text-white' : 'hover:bg-blue-700' ?>">
                            <i class="fas fa-tasks w-5"></i>
                            <span class="sidebar-text">Progress Tracking</span>
                        </a>
                    </li>
                </ul>
            </div>        



        <div class="px-4 py-3 border-t border-blue-700">
            <form action="../auth/logout.php" method="POST">
                <button type="submit" class="w-full text-left flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700 text-red-300">
                    <i class="fas fa-sign-out-alt w-5"></i><span class="sidebar-text">Logout</span>
                </button>
            </form>
        </div>
    </nav>
</div>

<!-- JS Toggle -->
<script>
    let isCollapsed = false;
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const title = document.getElementById('sidebar-title');
        const profile = document.getElementById('sidebar-profile');
        const textItems = document.querySelectorAll('.sidebar-text');
        const sections = document.querySelectorAll('.sidebar-section');

        isCollapsed = !isCollapsed;
        if (isCollapsed) {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-20');
            title.classList.add('hidden');
            profile.classList.add('hidden');
            textItems.forEach(el => el.classList.add('hidden'));
            sections.forEach(el => el.classList.add('hidden'));
        } else {
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');
            title.classList.remove('hidden');
            profile.classList.remove('hidden');
            textItems.forEach(el => el.classList.remove('hidden'));
            sections.forEach(el => el.classList.remove('hidden'));
        }
    }
</script>

</body>
</html>
