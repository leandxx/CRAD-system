<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRAD Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .hide-scrollbar {
            overflow-y: auto;               /* Scroll always available */
            -ms-overflow-style: auto;       /* IE/Edge legacy */
            scrollbar-width: thin;          /* Firefox thin scrollbar */
            scrollbar-gutter: stable;       /* Reserve space so no overlap */
            padding-right: 8px;              /* Fallback for older browsers */
        } 

        /* WebKit browsers (Chrome, Edge, Safari) */
        .hide-scrollbar::-webkit-scrollbar {
            width: 12px;
        }

        .hide-scrollbar::-webkit-scrollbar-track {
            background: transparent; /* Removes white background */
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

        /* Firefox */
        .hide-scrollbar {
            scrollbar-color: rgba(0,0,0,0.22) transparent;
        }

       /* Hide labels and logo when collapsed */
        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .profile-name,
        .sidebar-collapsed .profile-role,
        .sidebar-collapsed .portal-title,
        .sidebar-collapsed .portal-logo { 
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

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 10px;
            min-width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
            background-color: #ef4444;
            color: white;
        }

        .active-nav-item {
            background-color: #1e40af;
            border-left: 3px solid white;
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
                <div class="bg-white p-2 rounded-lg">
                    <i class="fas fa-flask text-2xl text-blue-600 logo-img"></i>
                </div>
            </div>
            <span class="text-lg font-bold portal-title">CRAD Admin</span>
        </div>
        <button class="text-white focus:outline-none" id="toggleSidebar">
            <i class="fas fa-bars text-2xl"></i>
        </button>
    </div>

    <!-- Profile Section -->
    <div class="p-4 flex items-center space-x-3 bg-blue-900">
        <div class="relative">
            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                 alt="Admin profile" class="rounded-full h-10 w-10">
            <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-blue-800"></span>
        </div>
        <div>
            <p class="font-medium profile-name">Admin User</p>
            <p class="text-xs text-blue-200 profile-role">Super Administrator</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 pb-4 hide-scrollbar overflow-y-auto">

        <!-- Administration -->
        <div class="px-4 py-3">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Administration</p>
            <ul>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700 active-nav-item">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">User Management</span>
                        <span class="ml-auto bg-blue-500 text-xs px-2 py-1 rounded-full nav-text">12 new</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span class="nav-text">Research Proposals</span>
                        <span class="ml-auto bg-yellow-500 text-xs px-2 py-1 rounded-full nav-text">42 pending</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-user-tie nav-icon"></i>
                        <span class="nav-text">Advisers & Panels</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Research Management -->
        <div class="px-4 py-3 border-t border-blue-700">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">Research Management</p>
            <ul>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-money-bill-wave nav-icon"></i>
                        <span class="nav-text">Research Grants</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-book nav-icon"></i>
                        <span class="nav-text">Publications</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-calendar-check nav-icon"></i>
                        <span class="nav-text">Defense Scheduling</span>
                        <span class="ml-auto bg-red-500 text-xs px-2 py-1 rounded-full nav-text">15 upcoming</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-microscope nav-icon"></i>
                        <span class="nav-text">Research Facilities</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- System -->
        <div class="px-4 py-3 border-t border-blue-700">
            <p class="text-xs uppercase text-blue-300 font-semibold mb-2 nav-text">System</p>
            <ul>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-chart-line nav-icon"></i>
                        <span class="nav-text">Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-cog nav-icon"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-bell nav-icon"></i>
                        <span class="nav-text">Notifications</span>
                        <span class="ml-auto bg-red-500 text-xs px-2 py-1 rounded-full nav-text">5 new</span>
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
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Enhanced Top Bar -->
      <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b">
        <div class="flex items-center space-x-4">
          <h1 class="text-2xl font-bold text-gray-800">
            <span class="gradient-text">Dashboard</span> Overview
          </h1>
          <span class="text-sm bg-blue-50 text-blue-600 px-3 py-1 rounded-full">Today: <span id="current-date" class="font-medium"></span></span>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <button class="p-2 text-gray-500 hover:text-gray-700 relative">
              <i class="fas fa-bell text-xl"></i>
              <span class="notification-badge">5</span>
            </button>
          </div>
          <div class="relative">
            <button class="p-2 text-gray-500 hover:text-gray-700 relative">
              <i class="fas fa-envelope text-xl"></i>
              <span class="notification-badge">3</span>
            </button>
          </div>
          <div class="flex items-center space-x-3">
            <div class="text-right hidden md:block">
              <p class="font-medium">Dr. Admin User</p>
              <p class="text-xs text-gray-500">Research Director</p>
            </div>
            <div class="relative">
              <img class="h-10 w-10 rounded-full avatar-ring" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Admin">
              <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-white"></span>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content Area -->
      <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <!-- Welcome Message with Stats -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6 mb-6 text-white">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div class="mb-4 md:mb-0">
              <h2 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, Dr. Admin!</h2>
              <p class="opacity-90">Here's your research administration overview for today</p>
            </div>
            <div class="flex space-x-4">
              <div class="text-center">
                <p class="text-sm opacity-80">Pending Tasks</p>
                <p class="text-2xl font-bold">17</p>
              </div>
              <div class="text-center">
                <p class="text-sm opacity-80">Urgent Items</p>
                <p class="text-2xl font-bold">5</p>
              </div>
              <div class="text-center">
                <p class="text-sm opacity-80">Completed</p>
                <p class="text-2xl font-bold">23</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Key Stats with Progress Bars -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-xl shadow p-6 card-hover transition hover:border-l-4 hover:border-blue-500">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-gray-500">Total Users</p>
                <h3 class="text-2xl font-bold text-gray-800">1,248</h3>
                <p class="text-sm text-green-600">+12 this week</p>
              </div>
              <div class="bg-blue-100 p-3 rounded-xl text-blue-600">
                <i class="fas fa-users text-xl"></i>
              </div>
            </div>
            <div class="progress-bar">
              <div class="progress-fill bg-blue-500" style="width: 75%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">75% active this month</p>
          </div>
          
          <div class="bg-white rounded-xl shadow p-6 card-hover transition hover:border-l-4 hover:border-yellow-500">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-gray-500">Pending Proposals</p>
                <h3 class="text-2xl font-bold text-gray-800">42</h3>
                <p class="text-sm text-red-600">+5 new today</p>
              </div>
              <div class="bg-yellow-100 p-3 rounded-xl text-yellow-600">
                <i class="fas fa-file-alt text-xl"></i>
              </div>
            </div>
            <div class="progress-bar">
              <div class="progress-fill bg-yellow-500" style="width: 60%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">60% awaiting initial review</p>
          </div>
          
          <div class="bg-white rounded-xl shadow p-6 card-hover transition hover:border-l-4 hover:border-green-500">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-gray-500">Active Research</p>
                <h3 class="text-2xl font-bold text-gray-800">186</h3>
                <p class="text-sm text-blue-600">8 nearing completion</p>
              </div>
              <div class="bg-green-100 p-3 rounded-xl text-green-600">
                <i class="fas fa-flask text-xl"></i>
              </div>
            </div>
            <div class="progress-bar">
              <div class="progress-fill bg-green-500" style="width: 45%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">45% in data collection phase</p>
          </div>
          
          <div class="bg-white rounded-xl shadow p-6 card-hover transition hover:border-l-4 hover:border-purple-500">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-gray-500">Upcoming Defenses</p>
                <h3 class="text-2xl font-bold text-gray-800">15</h3>
                <p class="text-sm text-purple-600">3 this week</p>
              </div>
              <div class="bg-purple-100 p-3 rounded-xl text-purple-600">
                <i class="fas fa-calendar-check text-xl"></i>
              </div>
            </div>
            <div class="progress-bar">
              <div class="progress-fill bg-purple-500" style="width: 30%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">30% need panel confirmation</p>
          </div>
        </div>
        
        <!-- Charts and Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <!-- Research Status Chart -->
          <div class="bg-white rounded-xl shadow p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Research Project Status</h3>
              <div class="flex space-x-2">
                <button class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-full">This Month</button>
                <button class="text-xs px-3 py-1 bg-gray-50 text-gray-600 rounded-full">All Time</button>
              </div>
            </div>
            <canvas id="researchChart" height="250"></canvas>
          </div>
          
          <!-- Recent Activity with Avatars -->
          <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
              <a href="#" class="text-blue-600 text-sm font-medium">View All</a>
            </div>
            
            <div class="space-y-4">
              <div class="flex items-start group">
                <div class="relative mr-3">
                  <img class="h-9 w-9 rounded-full" src="https://randomuser.me/api/portraits/women/44.jpg" alt="Dr. Johnson">
                  <div class="absolute -bottom-1 -right-1 bg-blue-500 p-1 rounded-full">
                    <i class="fas fa-user-plus text-white text-xs"></i>
                  </div>
                </div>
                <div class="flex-1">
                  <p class="font-medium group-hover:text-blue-600 transition">New faculty member added</p>
                  <p class="text-gray-500 text-sm">Dr. Sarah Johnson was added to the system</p>
                  <p class="text-gray-400 text-xs mt-1 flex items-center">
                    <i class="far fa-clock mr-1"></i> 2 hours ago
                  </p>
                </div>
              </div>
              
              <div class="flex items-start group">
                <div class="relative mr-3">
                  <img class="h-9 w-9 rounded-full" src="https://randomuser.me/api/portraits/women/68.jpg" alt="Jane Doe">
                  <div class="absolute -bottom-1 -right-1 bg-green-500 p-1 rounded-full">
                    <i class="fas fa-check text-white text-xs"></i>
                  </div>
                </div>
                <div class="flex-1">
                  <p class="font-medium group-hover:text-green-600 transition">Proposal approved</p>
                  <p class="text-gray-500 text-sm">"AI in Education" by Jane Doe</p>
                  <p class="text-gray-400 text-xs mt-1 flex items-center">
                    <i class="far fa-clock mr-1"></i> 5 hours ago
                  </p>
                </div>
              </div>
              
              <div class="flex items-start group">
                <div class="relative mr-3">
                  <div class="h-9 w-9 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-exclamation text-yellow-600"></i>
                  </div>
                  <div class="absolute -bottom-1 -right-1 bg-yellow-500 p-1 rounded-full">
                    <i class="fas fa-bell text-white text-xs"></i>
                  </div>
                </div>
                <div class="flex-1">
                  <p class="font-medium group-hover:text-yellow-600 transition">Deadline reminder</p>
                  <p class="text-gray-500 text-sm">Proposal submission in 3 days</p>
                  <p class="text-gray-400 text-xs mt-1 flex items-center">
                    <i class="far fa-clock mr-1"></i> 1 day ago
                  </p>
                </div>
              </div>
              
              <div class="flex items-start group">
                <div class="relative mr-3">
                  <img class="h-9 w-9 rounded-full" src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Smith">
                  <div class="absolute -bottom-1 -right-1 bg-purple-500 p-1 rounded-full">
                    <i class="fas fa-user-tie text-white text-xs"></i>
                  </div>
                </div>
                <div class="flex-1">
                  <p class="font-medium group-hover:text-purple-600 transition">Panel assigned</p>
                  <p class="text-gray-500 text-sm">Dr. John Smith to "Renewable Energy" project</p>
                  <p class="text-gray-400 text-xs mt-1 flex items-center">
                    <i class="far fa-clock mr-1"></i> 1 day ago
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Quick Actions and Upcoming Defenses -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Quick Actions -->
          <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
            
            <div class="grid grid-cols-2 gap-4">
              <a href="#" class="bg-blue-50 p-4 rounded-lg hover:bg-blue-100 transition group">
                <div class="bg-blue-100 p-3 rounded-xl inline-block mb-2 group-hover:bg-blue-200 transition">
                  <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                </div>
                <p class="font-medium">Add User</p>
                <p class="text-xs text-gray-500 mt-1">Register new faculty/student</p>
              </a>
              
              <a href="#" class="bg-green-50 p-4 rounded-lg hover:bg-green-100 transition group">
                <div class="bg-green-100 p-3 rounded-xl inline-block mb-2 group-hover:bg-green-200 transition">
                  <i class="fas fa-file-import text-green-600 text-xl"></i>
                </div>
                <p class="font-medium">Review Proposal</p>
                <p class="text-xs text-gray-500 mt-1">42 pending reviews</p>
              </a>
              
              <a href="#" class="bg-purple-50 p-4 rounded-lg hover:bg-purple-100 transition group">
                <div class="bg-purple-100 p-3 rounded-xl inline-block mb-2 group-hover:bg-purple-200 transition">
                  <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                </div>
                <p class="font-medium">Assign Panel</p>
                <p class="text-xs text-gray-500 mt-1">15 defenses coming up</p>
              </a>
              
              <a href="#" class="bg-yellow-50 p-4 rounded-lg hover:bg-yellow-100 transition group">
                <div class="bg-yellow-100 p-3 rounded-xl inline-block mb-2 group-hover:bg-yellow-200 transition">
                  <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                </div>
                <p class="font-medium">Process Grant</p>
                <p class="text-xs text-gray-500 mt-1">$24,500 pending</p>
              </a>
              
              <a href="#" class="bg-red-50 p-4 rounded-lg hover:bg-red-100 transition group">
                <div class="bg-red-100 p-3 rounded-xl inline-block mb-2 group-hover:bg-red-200 transition">
                  <i class="fas fa-calendar-exclamation text-red-600 text-xl"></i>
                </div>
                <p class="font-medium">Schedule Defense</p>
                <p class="text-xs text-gray-500 mt-1">3 ready to schedule</p>
              </a>
              
              <a href="#" class="bg-indigo-50 p-4 rounded-lg hover:bg-indigo-100 transition group">
                <div class="bg-indigo-100 p-3 rounded-xl inline-block mb-2 group-hover:bg-indigo-200 transition">
                  <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
                </div>
                <p class="font-medium">Generate Report</p>
                <p class="text-xs text-gray-500 mt-1">Monthly statistics</p>
              </a>
            </div>
          </div>
          
          <!-- Upcoming Defenses -->
          <div class="bg-white rounded-xl shadow p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Upcoming Defenses</h3>
              <a href="#" class="text-blue-600 text-sm font-medium">View Calendar</a>
            </div>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Research Title</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">Aug 15, 2023</div>
                      <div class="text-sm text-gray-500">10:00 AM</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">Machine Learning in Healthcare</div>
                      <div class="text-sm text-gray-500">PhD Dissertation</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/12.jpg" alt="">
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">Jane Cooper</div>
                          <div class="text-sm text-gray-500">Computer Science</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <a href="#" class="text-blue-600 hover:text-blue-900">Details</a>
                    </td>
                  </tr>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">Aug 17, 2023</div>
                      <div class="text-sm text-gray-500">2:00 PM</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">Renewable Energy Solutions</div>
                      <div class="text-sm text-gray-500">Master's Thesis</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/42.jpg" alt="">
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">John Smith</div>
                          <div class="text-sm text-gray-500">Engineering</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <a href="#" class="text-blue-600 hover:text-blue-900">Details</a>
                    </td>
                  </tr>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">Aug 20, 2023</div>
                      <div class="text-sm text-gray-500">9:30 AM</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">AI in Education</div>
                      <div class="text-sm text-gray-500">PhD Dissertation</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/68.jpg" alt="">
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">Jane Doe</div>
                          <div class="text-sm text-gray-500">Education</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Scheduled</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <a href="#" class="text-blue-600 hover:text-blue-900">Details</a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    // Set current date
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', options);
    
    // Research Status Chart
    const researchCtx = document.getElementById('researchChart').getContext('2d');
    const researchChart = new Chart(researchCtx, {
      type: 'bar',
      data: {
        labels: ['Proposal', 'Data Collection', 'Analysis', 'Writing', 'Defense', 'Completed'],
        datasets: [{
          label: 'Number of Projects',
          data: [42, 86, 45, 32, 15, 28],
          backgroundColor: [
            'rgba(59, 130, 246, 0.7)',
            'rgba(16, 185, 129, 0.7)',
            'rgba(245, 158, 11, 0.7)',
            'rgba(139, 92, 246, 0.7)',
            'rgba(239, 68, 68, 0.7)',
            'rgba(20, 184, 166, 0.7)'
          ],
          borderColor: [
            'rgba(59, 130, 246, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(245, 158, 11, 1)',
            'rgba(139, 92, 246, 1)',
            'rgba(239, 68, 68, 1)',
            'rgba(20, 184, 166, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              drawBorder: false
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
    
    // Add hover effects to table rows
    document.querySelectorAll('tr').forEach(row => {
      row.addEventListener('mouseenter', () => {
        row.classList.add('bg-gray-50');
      });
      row.addEventListener('mouseleave', () => {
        row.classList.remove('bg-gray-50');
      });
    });
  </script>
</body>
</html>