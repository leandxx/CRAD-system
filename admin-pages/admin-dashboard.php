<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Submission</title>
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

        function toggleModal() {
            const modal = document.getElementById('proposalModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include('../includes/admin-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <div class="flex items-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                        Admin Dashboard
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
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-circle mr-2"></i>Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i>Settings</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main content area -->
                <main class="flex-1 overflow-y-auto p-6">
    <!-- Dashboard Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Admin Dashboard</h2>
        <p class="text-gray-600">Welcome back, Administrator</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Students</p>
                    <h3 class="text-2xl font-bold">1,254</h3>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
            <p class="text-green-500 text-sm mt-2"><i class="fas fa-arrow-up mr-1"></i> 12% from last month</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Active Proposals</p>
                    <h3 class="text-2xl font-bold">86</h3>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-file-alt text-lg"></i>
                </div>
            </div>
            <p class="text-green-500 text-sm mt-2"><i class="fas fa-arrow-up mr-1"></i> 5 new today</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Reviews</p>
                    <h3 class="text-2xl font-bold">23</h3>
                </div>
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-lg"></i>
                </div>
            </div>
            <p class="text-red-500 text-sm mt-2"><i class="fas fa-arrow-down mr-1"></i> 2 overdue</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Scheduled Defenses</p>
                    <h3 class="text-2xl font-bold">14</h3>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-calendar-check text-lg"></i>
                </div>
            </div>
            <p class="text-blue-500 text-sm mt-2">3 this week</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="#" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-blue-300 transition-colors text-center">
                <div class="text-blue-500 mb-2">
                    <i class="fas fa-user-plus text-2xl"></i>
                </div>
                <p class="font-medium">Add Student</p>
            </a>
            <a href="#" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-purple-300 transition-colors text-center">
                <div class="text-purple-500 mb-2">
                    <i class="fas fa-file-upload text-2xl"></i>
                </div>
                <p class="font-medium">Review Proposals</p>
            </a>
            <a href="#" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-green-300 transition-colors text-center">
                <div class="text-green-500 mb-2">
                    <i class="fas fa-calendar-plus text-2xl"></i>
                </div>
                <p class="font-medium">Schedule Defense</p>
            </a>
            <a href="#" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-red-300 transition-colors text-center">
                <div class="text-red-500 mb-2">
                    <i class="fas fa-cog text-2xl"></i>
                </div>
                <p class="font-medium">System Settings</p>
            </a>
        </div>
    </div>

    <!-- Recent Activity and Upcoming Defenses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Activity -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Recent Activity</h3>
                <a href="#" class="text-sm text-blue-500 hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                        <i class="fas fa-user-plus text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium">New student registered</p>
                        <p class="text-gray-500 text-sm">John Doe - Computer Science</p>
                        <p class="text-gray-400 text-xs">2 hours ago</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="bg-purple-100 p-2 rounded-full mr-3">
                        <i class="fas fa-file-upload text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium">Proposal submitted</p>
                        <p class="text-gray-500 text-sm">"AI in Healthcare" by Jane Smith</p>
                        <p class="text-gray-400 text-xs">5 hours ago</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="bg-green-100 p-2 rounded-full mr-3">
                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium">Proposal approved</p>
                        <p class="text-gray-500 text-sm">"Blockchain Security" by Mark Johnson</p>
                        <p class="text-gray-400 text-xs">1 day ago</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Defenses -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Upcoming Defenses</h3>
                <a href="#" class="text-sm text-blue-500 hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <p class="font-medium">Sarah Williams - PhD Defense</p>
                    <p class="text-gray-500 text-sm">"Machine Learning for Climate Modeling"</p>
                    <div class="flex items-center text-sm text-gray-500 mt-1">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span>Tomorrow, 10:00 AM - Room A12</span>
                    </div>
                </div>
                <div class="border-l-4 border-purple-500 pl-4 py-2">
                    <p class="font-medium">Robert Chen - Master's Defense</p>
                    <p class="text-gray-500 text-sm">"IoT Security Protocols"</p>
                    <div class="flex items-center text-sm text-gray-500 mt-1">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span>June 15, 2:30 PM - Virtual</span>
                    </div>
                </div>
                <div class="border-l-4 border-green-500 pl-4 py-2">
                    <p class="font-medium">Team Project - BSc Defense</p>
                    <p class="text-gray-500 text-sm">"E-Learning Platform Development"</p>
                    <div class="flex items-center text-sm text-gray-500 mt-1">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span>June 18, 9:00 AM - Lab 3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Pending Approvals</h3>
            <a href="#" class="text-sm text-blue-500 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proposal Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Michael Brown</div>
                                    <div class="text-sm text-gray-500">CS-2021-05</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">Neural Networks for Image Recognition</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">3 days ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Under Review</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button class="text-blue-500 hover:text-blue-700 mr-3">Review</button>
                            <button class="text-green-500 hover:text-green-700">Approve</button>
                        </td>
                    </tr>
                    <!-- Additional rows would go here -->
                </tbody>
            </table>
        </div>
    </div>

            </main>
        </div>
    </div>

</body>
</html>