<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }
        
        .sidebar {
            transition: all 0.3s;
        }
        
        .card-hover {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .progress-bar {
            height: 6px;
            border-radius: 3px;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .glow-on-hover:hover {
            filter: drop-shadow(0 0 8px rgba(79, 70, 229, 0.3));
        }
        
        .floating-btn {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
        
        .activity-item {
            transition: all 0.2s;
        }
        
        .activity-item:hover {
            background-color: rgba(249, 250, 251, 0.8);
            transform: translateX(5px);
        }
        
        .sidebar-item {
            transition: all 0.2s;
        }
        
        .sidebar-item:hover {
            background-color: rgba(79, 70, 229, 0.1);
        }
        
        .sidebar-item.active {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="sidebar bg-gradient-to-b from-indigo-50 to-white w-64 flex flex-col border-r border-gray-200">
            <!-- Scrollable Content -->
            <div class="px-4 py-6 flex-1 flex flex-col overflow-y-auto">
                <!-- Logo -->
                <div class="flex items-center justify-center mb-8 px-2">
                    <div>
                        <img src="../assets/img/sms-logo.png" alt="University Logo" class="h-16">
                    </div>
                    <h4 class="font-bold text-indigo-800 text-lg">SMS Admin</h4>
                </div>
                
                <!-- Menu -->
                <nav class="flex-1">
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 mb-1">Main</p>
                        <a href="#" class="sidebar-item active flex items-center px-2 py-3 rounded-lg font-medium">
                            <i class="fas fa-tachometer-alt mr-3 text-indigo-600"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 mb-1">Management</p>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-user-graduate mr-3 w-5 text-center text-gray-500"></i>
                            <span>Enrollment</span>
                            <span class="ml-auto bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full">24 New</span>
                        </a>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-clipboard-list mr-3 w-5 text-center text-gray-500"></i>
                            <span>Registrar (SIS)</span>
                        </a>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-book mr-3 w-5 text-center text-gray-500"></i>
                            <span>Curriculum</span>
                        </a>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-certificate mr-3 w-5 text-center text-gray-500"></i>
                            <span>Accreditation</span>
                            <span class="ml-auto bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">3 Updates</span>
                        </a>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 mb-1">Operations</p>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-money-bill-wave mr-3 w-5 text-center text-gray-500"></i>
                            <span>Payments</span>
                        </a>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-chalkboard-teacher mr-3 w-5 text-center text-gray-500"></i>
                            <span>Faculty</span>
                        </a>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-calendar-alt mr-3 w-5 text-center text-gray-500"></i>
                            <span>Class Scheduling</span>
                        </a>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-icons mr-3 w-5 text-center text-gray-500"></i>
                            <span>Co-curricular</span>
                        </a>
                    </div>
                    
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 mb-1">Learning</p>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-laptop-code mr-3 w-5 text-center text-gray-500"></i>
                            <span>Online Learning</span>
                        </a>
                        <a href="#" class="sidebar-item flex items-center px-2 py-2 text-gray-700 rounded-lg mb-1">
                            <i class="fas fa-flask mr-3 w-5 text-center text-gray-500"></i>
                            <span>CRAD (Research)</span>
                            <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">5 New</span>
                        </a>
                    </div>
                </nav>
            </div>
            
            <!-- Fixed User Profile -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="relative">
                        <img src="https://placehold.co/40x40" alt="Admin profile" class="rounded-full mr-3 border-2 border-indigo-200">
                        <span class="absolute bottom-0 right-3 h-3 w-3 bg-green-500 rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium">Admin User</p>
                        <p class="text-xs text-gray-500">Super Admin</p>
                    </div>
                    <button class="text-gray-400 hover:text-indigo-600 transition-colors">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation -->
            <div class="bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800 flex items-center">
                        <span>Dashboard Overview</span>
                        <span class="ml-2 text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">Real-time</span>
                    </h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" id="searchInput">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button class="relative text-gray-500 hover:text-indigo-600 transition-colors" id="notificationBtn">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="notification-badge bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </button>
                        <button class="text-gray-500 hover:text-indigo-600 transition-colors" id="messageBtn">
                            <i class="fas fa-envelope text-lg"></i>
                            <span class="notification-badge bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">5</span>
                        </button>
                        <button class="text-gray-500 hover:text-indigo-600 transition-colors" id="settingsBtn">
                            <i class="fas fa-cog text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-700 rounded-xl p-6 mb-6 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <h2 class="text-2xl font-bold mb-2">Welcome back, Admin!</h2>
                        <p class="max-w-lg mb-4">You have <span class="font-bold">12 pending tasks</span> and <span class="font-bold">3 new messages</span> waiting for your attention.</p>
                        <button class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-all glow-on-hover">
                            View Tasks
                        </button>
                    </div>
                    <div class="absolute -right-10 -bottom-10 opacity-20">
                        <i class="fas fa-university text-9xl"></i>
                    </div>
                    <div class="absolute top-0 right-0 h-full w-1/3 bg-gradient-to-l from-indigo-600 to-transparent"></div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Enrollment -->
                    <div class="card-hover bg-white rounded-xl p-6 border-l-4 border-indigo-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 h-full w-16 bg-indigo-50 opacity-30"></div>
                        <div class="relative">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Enrollment</p>
                                    <h3 class="text-2xl font-bold text-gray-800 mt-1">2,458</h3>
                                </div>
                                <div class="bg-indigo-100 text-indigo-800 p-3 rounded-lg">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    12.5%
                                </p>
                                <p class="text-sm text-gray-500">vs last semester</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payments -->
                    <div class="card-hover bg-white rounded-xl p-6 border-l-4 border-green-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 h-full w-16 bg-green-50 opacity-30"></div>
                        <div class="relative">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Payments</p>
                                    <h3 class="text-2xl font-bold text-gray-800 mt-1">₱4.2M</h3>
                                </div>
                                <div class="bg-green-100 text-green-800 p-3 rounded-lg">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    8.2%
                                </p>
                                <p class="text-sm text-gray-500">vs last month</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Courses -->
                    <div class="card-hover bg-white rounded-xl p-6 border-l-4 border-blue-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 h-full w-16 bg-blue-50 opacity-30"></div>
                        <div class="relative">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Courses</p>
                                    <h3 class="text-2xl font-bold text-gray-800 mt-1">98</h3>
                                </div>
                                <div class="bg-blue-100 text-blue-800 p-3 rounded-lg">
                                    <i class="fas fa-book"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    3.5%
                                </p>
                                <p class="text-sm text-gray-500">vs last year</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Faculty -->
                    <div class="card-hover bg-white rounded-xl p-6 border-l-4 border-purple-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 h-full w-16 bg-purple-50 opacity-30"></div>
                        <div class="relative">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Faculty</p>
                                    <h3 class="text-2xl font-bold text-gray-800 mt-1">184</h3>
                                </div>
                                <div class="bg-purple-100 text-purple-800 p-3 rounded-lg">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    5.1%
                                </p>
                                <p class="text-sm text-gray-500">vs last year</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Dashboard Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Enrollment Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2 card-hover">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Enrollment Trends</h2>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-xs bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors" id="semesterBtn">Semester</button>
                                <button class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors" id="yearBtn">Year</button>
                                <button class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors" id="programBtn">Program</button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="enrollmentChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Recent Activities</h2>
                            <button class="text-indigo-600 text-sm font-medium hover:text-indigo-800 transition-colors">View All</button>
                        </div>
                        <div class="space-y-4">
                            <div class="activity-item flex items-start p-2 rounded-lg cursor-pointer">
                                <div class="bg-indigo-100 text-indigo-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">12 new enrollments</p>
                                    <p class="text-xs text-gray-500">Today, 9:42 AM</p>
                                </div>
                            </div>
                            <div class="activity-item flex items-start p-2 rounded-lg cursor-pointer">
                                <div class="bg-green-100 text-green-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">32 tuition payments processed</p>
                                    <p class="text-xs text-gray-500">Today, 11:30 AM</p>
                                </div>
                            </div>
                            <div class="activity-item flex items-start p-2 rounded-lg cursor-pointer">
                                <div class="bg-yellow-100 text-yellow-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Faculty meeting scheduled</p>
                                    <p class="text-xs text-gray-500">Yesterday, 3:15 PM</p>
                                </div>
                            </div>
                            <div class="activity-item flex items-start p-2 rounded-lg cursor-pointer">
                                <div class="bg-purple-100 text-purple-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Class schedules updated</p>
                                    <p class="text-xs text-gray-500">Yesterday, 5:45 PM</p>
                                </div>
                            </div>
                            <div class="activity-item flex items-start p-2 rounded-lg cursor-pointer">
                                <div class="bg-blue-100 text-blue-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">3 new research proposals</p>
                                    <p class="text-xs text-gray-500">2 days ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Accreditation Status -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                        <h2 class="text-lg font-semibold text-gray-800 mb-6">Accreditation Status</h2>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">Computer Science</span>
                                    <span class="text-sm font-medium text-green-500">Level IV</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full progress-animate" style="width: 100%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">Business Administration</span>
                                    <span class="text-sm font-medium text-green-500">Level III</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-400 h-2 rounded-full progress-animate" style="width: 90%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">Engineering</span>
                                    <span class="text-sm font-medium text-yellow-500">Level II</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full progress-animate" style="width: 70%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">Education</span>
                                    <span class="text-sm font-medium text-blue-500">In Progress</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full progress-animate" style="width: 45%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Scheduling -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Class Scheduling</h2>
                            <button class="text-indigo-600 text-sm font-medium hover:text-indigo-800 transition-colors">View All</button>
                        </div>
                        <div class="space-y-4">
                            <div class="border rounded-lg p-3 hover:border-indigo-300 transition-colors cursor-pointer hover:shadow-sm">
                                <div class="flex justify-between">
                                    <p class="font-medium">CS 101 - Introduction</p>
                                    <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">MW 8:00-9:30</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Room 302, Dr. Smith</p>
                            </div>
                            <div class="border rounded-lg p-3 hover:border-indigo-300 transition-colors cursor-pointer hover:shadow-sm">
                                <div class="flex justify-between">
                                    <p class="font-medium">MATH 202 - Calculus II</p>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">TTh 10:00-11:30</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Room 205, Dr. Johnson</p>
                            </div>
                            <div class="border rounded-lg p-3 hover:border-indigo-300 transition-colors cursor-pointer hover:shadow-sm">
                                <div class="flex justify-between">
                                    <p class="font-medium">ENGL 101 - Composition</p>
                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">MWF 1:00-2:00</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Room 101, Prof. Williams</p>
                            </div>
                            <div class="border rounded-lg p-3 hover:border-indigo-300 transition-colors cursor-pointer hover:shadow-sm">
                                <div class="flex justify-between">
                                    <p class="font-medium">PHYS 210 - Physics Lab</p>
                                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">F 2:00-5:00</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Science Bldg., Dr. Brown</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Online Learning -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                        <h2 class="text-lg font-semibold text-gray-800 mb-6">Online Learning Status</h2>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">Active Courses</span>
                                    <span class="text-sm font-medium">78%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-500 h-2 rounded-full progress-animate" style="width: 78%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">Completed Assignments</span>
                                    <span class="text-sm font-medium">62%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full progress-animate" style="width: 62%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium">Student Engagement</span>
                                    <span class="text-sm font-medium">89%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full progress-animate" style="width: 89%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-indigo-50 rounded-lg">
                                <div class="text-2xl font-bold mb-1 text-indigo-700">4,325</div>
                                <div class="text-xs text-gray-500">Active Students</div>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold mb-1 text-blue-700">168</div>
                                <div class="text-xs text-gray-500">Online Instructors</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Third Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Co-curricular Activities -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Co-curricular Activities</h2>
                            <button class="text-indigo-600 text-sm font-medium hover:text-indigo-800 transition-colors">View All</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                                <div class="flex items-center mb-3">
                                    <div class="bg-red-100 text-red-800 p-2 rounded-lg mr-3">
                                        <i class="fas fa-headphones"></i>
                                    </div>
                                    <h3 class="font-medium">Music Club</h3>
                                </div>
                                <p class="text-xs text-gray-500 mb-2">Meets every Wednesday at 4 PM</p>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">42 Members</span>
                                    <span class="text-indigo-600 font-medium">Active</span>
                                </div>
                            </div>
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                                <div class="flex items-center mb-3">
                                    <div class="bg-green-100 text-green-800 p-2 rounded-lg mr-3">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <h3 class="font-medium">Coding Club</h3>
                                </div>
                                <p class="text-xs text-gray-500 mb-2">Meets every Friday at 3 PM</p>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">36 Members</span>
                                    <span class="text-indigo-600 font-medium">Active</span>
                                </div>
                            </div>
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                                <div class="flex items-center mb-3">
                                    <div class="bg-blue-100 text-blue-800 p-2 rounded-lg mr-3">
                                        <i class="fas fa-futbol"></i>
                                    </div>
                                    <h3 class="font-medium">Football Team</h3>
                                </div>
                                <p class="text-xs text-gray-500 mb-2">Practices Mon-Thu at 5 PM</p>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">24 Members</span>
                                    <span class="text-indigo-600 font-medium">Active</span>
                                </div>
                            </div>
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                                <div class="flex items-center mb-3">
                                    <div class="bg-yellow-100 text-yellow-800 p-2 rounded-lg mr-3">
                                        <i class="fas fa-microscope"></i>
                                    </div>
                                    <h3 class="font-medium">Science Club</h3>
                                </div>
                                <p class="text-xs text-gray-500 mb-2">Meets every Tuesday at 4:30 PM</p>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">28 Members</span>
                                    <span class="text-indigo-600 font-medium">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Research Projects -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">CRAD Research Projects</h2>
                            <button class="text-indigo-600 text-sm font-medium hover:text-indigo-800 transition-colors">View All</button>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start border-b pb-4 hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer">
                                <div class="bg-indigo-100 text-indigo-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h3 class="font-medium">AI in Education</h3>
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Active</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Dr. Rodriguez, Budget: ₱250,000</p>
                                </div>
                            </div>
                            <div class="flex items-start border-b pb-4 hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer">
                                <div class="bg-blue-100 text-blue-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-atom"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h3 class="font-medium">Renewable Energy</h3>
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Active</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Dr. Chen, Budget: ₱350,000</p>
                                </div>
                            </div>
                            <div class="flex items-start border-b pb-4 hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer">
                                <div class="bg-purple-100 text-purple-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-seedling"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h3 class="font-medium">Sustainable Agriculture</h3>
                                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Pending</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Dr. Garcia, Budget: ₱180,000</p>
                                </div>
                            </div>
                            <div class="flex items-start hover:bg-gray-50 p-2 rounded-lg transition-colors cursor-pointer">
                                <div class="bg-orange-100 text-orange-800 p-2 rounded-lg mr-3 mt-1">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h3 class="font-medium">Health Informatics</h3>
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Completed</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Dr. Wong, Budget: ₱210,000</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <button class="fixed bottom-6 right-6 bg-indigo-600 text-white p-4 rounded-full shadow-lg hover:bg-indigo-700 transition-colors glow-on-hover floating-btn">
                <i class="fas fa-plus text-xl"></i>
    </button>

    <!-- Notification Dropdown (Hidden by default) -->
    <div class="hidden absolute right-4 top-16 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-20" id="notificationDropdown">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-medium text-gray-800">Notifications (3)</h3>
        </div>
        <div class="overflow-y-auto max-h-96">
            <div class="p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer">
                <div class="flex items-start">
                    <div class="bg-indigo-100 text-indigo-800 p-2 rounded-lg mr-3">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">New enrollment request</p>
                        <p class="text-xs text-gray-500 mt-1">5 students waiting for approval</p>
                        <p class="text-xs text-gray-400 mt-1">10 minutes ago</p>
                    </div>
                </div>
            </div>
            <div class="p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer">
                <div class="flex items-start">
                    <div class="bg-green-100 text-green-800 p-2 rounded-lg mr-3">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">Payment processed</p>
                        <p class="text-xs text-gray-500 mt-1">Juan Dela Cruz paid tuition</p>
                        <p class="text-xs text-gray-400 mt-1">1 hour ago</p>
                    </div>
                </div>
            </div>
            <div class="p-3 hover:bg-gray-50 cursor-pointer">
                <div class="flex items-start">
                    <div class="bg-yellow-100 text-yellow-800 p-2 rounded-lg mr-3">
                        <i class="fas fa-calendar-exclamation"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">Schedule conflict</p>
                        <p class="text-xs text-gray-500 mt-1">Room 302 double booked</p>
                        <p class="text-xs text-gray-400 mt-1">3 hours ago</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-3 bg-gray-50 text-center">
            <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View all notifications</a>
        </div>
    </div>

    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Enrollment Chart
            const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
            const enrollmentChart = new Chart(enrollmentCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: '2023',
                            data: [120, 190, 170, 220, 180, 150, 210, 240, 200, 250, 230, 260],
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: '2024',
                            data: [150, 210, 190, 240, 210, 180, 230, 270, 240, 290, 260, 300],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
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

            // Chart filter buttons
            document.getElementById('semesterBtn').addEventListener('click', function() {
                // Update chart for semester view
                enrollmentChart.data.labels = ['1st Sem', '2nd Sem', 'Summer'];
                enrollmentChart.data.datasets[0].data = [1250, 1400, 300];
                enrollmentChart.data.datasets[1].data = [1450, 1600, 350];
                enrollmentChart.update();
                
                // Update button states
                this.classList.remove('bg-gray-100', 'text-gray-700');
                this.classList.add('bg-indigo-600', 'text-white');
                document.getElementById('yearBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('yearBtn').classList.add('bg-gray-100', 'text-gray-700');
                document.getElementById('programBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('programBtn').classList.add('bg-gray-100', 'text-gray-700');
            });

            document.getElementById('yearBtn').addEventListener('click', function() {
                // Update chart for year view
                enrollmentChart.data.labels = ['2019', '2020', '2021', '2022', '2023', '2024'];
                enrollmentChart.data.datasets[0].data = [1800, 2000, 2100, 2300, 2500, 0];
                enrollmentChart.data.datasets[1].data = [0, 0, 0, 0, 2500, 2800];
                enrollmentChart.update();
                
                // Update button states
                this.classList.remove('bg-gray-100', 'text-gray-700');
                this.classList.add('bg-indigo-600', 'text-white');
                document.getElementById('semesterBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('semesterBtn').classList.add('bg-gray-100', 'text-gray-700');
                document.getElementById('programBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('programBtn').classList.add('bg-gray-100', 'text-gray-700');
            });

            document.getElementById('programBtn').addEventListener('click', function() {
                // Update chart for program view
                enrollmentChart.data.labels = ['CS', 'IT', 'Eng', 'Bus', 'Nurs', 'Educ'];
                enrollmentChart.data.datasets[0].data = [650, 420, 380, 520, 320, 210];
                enrollmentChart.data.datasets[1].data = [700, 450, 400, 550, 350, 250];
                enrollmentChart.update();
                
                // Update button states
                this.classList.remove('bg-gray-100', 'text-gray-700');
                this.classList.add('bg-indigo-600', 'text-white');
                document.getElementById('semesterBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('semesterBtn').classList.add('bg-gray-100', 'text-gray-700');
                document.getElementById('yearBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('yearBtn').classList.add('bg-gray-100', 'text-gray-700');
            });

            // Notification dropdown toggle
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
                notificationDropdown.classList.toggle('absolute');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                if (!notificationDropdown.classList.contains('hidden')) {
                    notificationDropdown.classList.add('hidden');
                }
            });

            // Animate progress bars on scroll
            const progressBars = document.querySelectorAll('.progress-animate');
            
            const animateProgressBars = () => {
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            };

            // Simple intersection observer to trigger animation when elements come into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateProgressBars();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            // Observe the dashboard container
            const dashboardContainer = document.querySelector('.p-6');
            if (dashboardContainer) {
                observer.observe(dashboardContainer);
            }
        });
    </script>
</body>
</html>