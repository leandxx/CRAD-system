<?php
include("../includes/connection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get username and role from user_tbl
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
    default => 'Admin'
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

        .notification-dropdown {
            transform: translateY(-10px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .notification-dropdown.show {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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
                    <a href="admin-pages/admin-dashboard.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700" data-page-title="Admin Dashboard">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Admin Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Research Management -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Research Management</p>
            <ul>
                <li>
                    <a href="admin-pages/admin-timeline.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700" data-page-title="Admin Submission Timeline">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span class="nav-text">Research Timeline & Submission</span>
                    </a>
                </li>
                <li>
                    <a href="admin-pages/admin-defense.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700" data-page-title="Admin Defense Scheduling">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Defense Scheduling</span>
                    </a>
                </li>

                 <!-- Panel Assignment Module -->
                <li>
                    <a href="admin-pages/panel-assignment.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700" data-page-title="Admin Panel Assignment">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">Panel Management & Assignment</span>
                    </a>
                </li>

                <li>
                    <a href="admin-pages/adviser-assignment.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700" data-page-title="Adviser Assignment">
                        <i class="fas fa-user-tie nav-icon"></i>
                        <span class="nav-text">Adviser Assignment</span>
                    </a>
                </li>

            
            </ul>
        </div>

        <!-- Account Management -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Account Management</p>
            <ul>
                <li>
                    <a href="admin-pages/manage-admins.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700" data-page-title="Admin Management">
                        <i class="fas fa-user-shield nav-icon"></i>
                        <span class="nav-text">Manage Admins</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- System Settings -->
        <div class="px-4 py-3 border-t border-blue-700">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">System Settings</p>
            <ul>
                <li>
                    <a href="admin-pages/system-settings.php" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-cog nav-icon"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <li>
                    <form action="../CRAD-system/auth/admin-logout.php" method="POST">
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

<div class="flex-1 flex flex-col overflow-hidden h-screen">
           <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
    <div class="flex items-center">
        <h1 id="page-title" class="text-2xl md:text-3xl font-bold text-primary flex items-center">
        </h1>
    </div>
    <div class="flex items-center space-x-4">
        <div class="relative">
            <button id="notificationBtn" class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative transition-all hover:scale-105">
                <i class="fas fa-bell"></i>
                <span id="notificationCount" class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center notification-dot pulse" style="display: none;">0</span>
            </button>
            <div id="notificationDropdown" class="notification-dropdown absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                </div>
                <div id="notificationList" class="max-h-64 overflow-y-auto">
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
                <div class="p-3 border-t text-center">
                    <button id="markAllRead" class="text-sm text-blue-600 hover:text-blue-800">Mark all as read</button>
                </div>
            </div>
        </div>
        <div class="relative group">
            <button class="flex items-center space-x-2 focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white">
                    <i class="fas fa-user text-sm"></i>
                </div>
                <i class="fas fa-chevron-down text-xs opacity-70 group-hover:opacity-100 transition"></i>
            </button>
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                <a href="admin-pages/admin-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-circle mr-2"></i>Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i>Settings</a>
                <a href="auth/admin-logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
            </div>
        </div>
    </div>
</header>


<!-- JS TOGGLE -->

<script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');
    });

    // Notification system
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationCount = document.getElementById('notificationCount');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllRead');

    // Load notifications on page load
    loadNotifications();
    updateNotificationCount();

    // Refresh notifications every 30 seconds
    setInterval(() => {
        updateNotificationCount();
    }, 30000);

    notificationBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notificationDropdown.classList.toggle('show');
        if (notificationDropdown.classList.contains('show')) {
            loadNotifications();
        }
    });

    markAllReadBtn.addEventListener('click', () => {
        markAllAsRead();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });

    function loadNotifications() {
        fetch('includes/notification-api.php?action=get')
            .then(response => response.json())
            .then(notifications => {
                displayNotifications(notifications);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationList.innerHTML = '<div class="p-4 text-center text-red-500">Error loading notifications</div>';
            });
    }

    function updateNotificationCount() {
        fetch('includes/notification-api.php?action=count')
            .then(response => response.json())
            .then(data => {
                const count = data.count;
                if (count > 0) {
                    notificationCount.textContent = count;
                    notificationCount.style.display = 'flex';
                } else {
                    notificationCount.style.display = 'none';
                }
            })
            .catch(error => console.error('Error updating count:', error));
    }

    function displayNotifications(notifications) {
        if (notifications.length === 0) {
            notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
            return;
        }

        const html = notifications.map(notification => {
            const typeColors = {
                'info': 'bg-blue-500',
                'success': 'bg-green-500',
                'warning': 'bg-yellow-500',
                'error': 'bg-red-500'
            };
            
            return `
                <div class="p-3 border-b hover:bg-gray-50 cursor-pointer notification-item ${notification.is_read ? 'opacity-60' : ''}" data-id="${notification.id}">
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 ${typeColors[notification.type]} rounded-full mt-2 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">${notification.title}</p>
                            <p class="text-xs text-gray-500 mt-1">${notification.message}</p>
                            <p class="text-xs text-gray-400 mt-1">${notification.time_ago}</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        notificationList.innerHTML = html;

        // Add click handlers to mark as read
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.id;
                markAsRead(id);
                item.classList.add('opacity-60');
            });
        });
    }

    function markAsRead(notificationId) {
        const formData = new FormData();
        formData.append('notification_id', notificationId);

        fetch('includes/notification-api.php?action=mark_read', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error marking as read:', error));
    }

    function markAllAsRead() {
        fetch('includes/notification-api.php?action=mark_all_read', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error marking all as read:', error));
    }

    // Function to update page title based on clicked link
    function updatePageTitle(linkText) {
        const titleElement = document.getElementById('page-title');
        titleElement.textContent = linkText;
        // Store the title in localStorage
        localStorage.setItem('selectedPageTitle', linkText);
    }

    // Add click event listeners to all navigation links
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

    });
</script>

</body>
</html>