<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Account</title>
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
                        Manage Student Group Accounts
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
    <!-- Header and Action Buttons -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Student Group Accounts</h2>
            <p class="text-gray-600">Manage all student group accounts and permissions</p>
        </div>
        <div class="flex gap-3">
            <button class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2 transition-colors">
                <i class="fas fa-plus"></i> Add Group
            </button>
            <button class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md flex items-center gap-2 transition-colors">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Groups</p>
                    <h3 class="text-2xl font-bold">24</h3>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Active Groups</p>
                    <h3 class="text-2xl font-bold">18</h3>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Approvals</p>
                    <h3 class="text-2xl font-bold">3</h3>
                </div>
                <div class="p-3 rounded-full bg-yellow-100 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <input type="text" placeholder="Search groups..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <select class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <option>All Departments</option>
                <option>Computer Science</option>
                <option>Engineering</option>
                <option>Business</option>
            </select>
            <select class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <option>All Statuses</option>
                <option>Active</option>
                <option>Pending</option>
                <option>Inactive</option>
            </select>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Group 1 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Alpha Researchers</div>
                                    <div class="text-sm text-gray-500">CS-2023-G01</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex -space-x-2">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/women/12.jpg" alt="">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/men/32.jpg" alt="">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/women/45.jpg" alt="">
                                <div class="h-8 w-8 rounded-full border-2 border-white bg-gray-100 flex items-center justify-center text-xs">+2</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Computer Science</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-primary hover:text-blue-700 mr-3"><i class="fas fa-eye"></i></button>
                            <button class="text-warning hover:text-yellow-700 mr-3"><i class="fas fa-edit"></i></button>
                            <button class="text-danger hover:text-red-700"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    
                    <!-- Group 2 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-secondary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Quantum Team</div>
                                    <div class="text-sm text-gray-500">ENG-2023-G05</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex -space-x-2">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/men/22.jpg" alt="">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/men/65.jpg" alt="">
                                <div class="h-8 w-8 rounded-full border-2 border-white bg-gray-100 flex items-center justify-center text-xs">+1</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Engineering</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1 day ago</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-primary hover:text-blue-700 mr-3"><i class="fas fa-eye"></i></button>
                            <button class="text-warning hover:text-yellow-700 mr-3"><i class="fas fa-edit"></i></button>
                            <button class="text-danger hover:text-red-700"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    
                    <!-- Group 3 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-success">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Business Innovators</div>
                                    <div class="text-sm text-gray-500">BUS-2023-G12</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex -space-x-2">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/women/32.jpg" alt="">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/women/55.jpg" alt="">
                                <img class="h-8 w-8 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/men/75.jpg" alt="">
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Business</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3 hours ago</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-primary hover:text-blue-700 mr-3"><i class="fas fa-eye"></i></button>
                            <button class="text-warning hover:text-yellow-700 mr-3"><i class="fas fa-edit"></i></button>
                            <button class="text-danger hover:text-red-700"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Previous </a>
                <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Next </a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700"> Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">24</span> groups </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="#" aria-current="page" class="z-10 bg-primary border-primary text-white relative inline-flex items-center px-4 py-2 border text-sm font-medium"> 1 </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"> 2 </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"> 3 </a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</main>
        </div>
    </div>

</body>
</html>