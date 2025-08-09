<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Groups</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .group-card {
            transition: all 0.3s ease;
        }
        .group-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .notification-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .modal {
            transition: opacity 0.3s ease;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
        }
        .avatar {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
            font-weight: bold;
            font-size: 0.75rem;
        }
    </style>
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
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                    Research Groups
                </h1>
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative transition-all hover:scale-105">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 notification-dot animate-pulse"></span>
                    </button>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="hidden md:inline font-medium">John D. Researcher</span>
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

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Search and Filter -->
                <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="relative w-full md:w-64">
                        <input type="text" placeholder="Search groups..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <div class="flex gap-2">
                        <select class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>All Status</option>
                            <option>Approved</option>
                            <option>Pending</option>
                            <option>Rejected</option>
                        </select>
                        <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2">
                            <i class="fas fa-plus"></i> New Group
                        </button>
                    </div>
                </div>

                <!-- Groups Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Group Card 1 -->
                    <div class="group-card bg-white shadow-md rounded-xl overflow-hidden border-l-4 border-blue-600">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-users text-blue-600 mr-2"></i> Group 1
                                </h2>
                                <span class="status-badge bg-green-100 text-green-800">Approved</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Capstone Title: <span class="font-medium">Smart Attendance System</span></p>
                            
                            <div class="mb-4">
                                <p class="text-gray-600 text-sm mb-2">Members:</p>
                                <div class="flex flex-wrap gap-2">
                                    <div class="avatar bg-blue-500">JM</div>
                                    <div class="avatar bg-purple-500">LR</div>
                                    <div class="avatar bg-yellow-500">ES</div>
                                    <div class="avatar bg-red-500">AC</div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Last updated: 2 days ago</span>
                                <button onclick="openModal('group1')" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                    View Details <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Group Card 2 -->
                    <div class="group-card bg-white shadow-md rounded-xl overflow-hidden border-l-4 border-yellow-500">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-users text-yellow-500 mr-2"></i> Group 2
                                </h2>
                                <span class="status-badge bg-yellow-100 text-yellow-800">Pending</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Capstone Title: <span class="font-medium">AI Chatbot for Student Queries</span></p>
                            
                            <div class="mb-4">
                                <p class="text-gray-600 text-sm mb-2">Members:</p>
                                <div class="flex flex-wrap gap-2">
                                    <div class="avatar bg-pink-500">AC</div>
                                    <div class="avatar bg-indigo-500">BD</div>
                                    <div class="avatar bg-green-500">CL</div>
                                    <div class="avatar bg-teal-500">DF</div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Last updated: 1 week ago</span>
                                <button onclick="openModal('group2')" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                    View Details <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Group Card 3 -->
                    <div class="group-card bg-white shadow-md rounded-xl overflow-hidden border-l-4 border-red-500">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-users text-red-500 mr-2"></i> Group 3
                                </h2>
                                <span class="status-badge bg-red-100 text-red-800">Rejected</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Capstone Title: <span class="font-medium">Blockchain-Based Voting System</span></p>
                            
                            <div class="mb-4">
                                <p class="text-gray-600 text-sm mb-2">Members:</p>
                                <div class="flex flex-wrap gap-2">
                                    <div class="avatar bg-orange-500">ML</div>
                                    <div class="avatar bg-blue-600">JS</div>
                                    <div class="avatar bg-purple-600">LJ</div>
                                    <div class="avatar bg-green-600">MB</div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Last updated: 3 days ago</span>
                                <button onclick="openModal('group3')" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                    View Details <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Group Card 4 -->
                    <div class="group-card bg-white shadow-md rounded-xl overflow-hidden border-l-4 border-green-500">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-users text-green-500 mr-2"></i> Group 4
                                </h2>
                                <span class="status-badge bg-green-100 text-green-800">Approved</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Capstone Title: <span class="font-medium">E-Learning Platform</span></p>
                            
                            <div class="mb-4">
                                <p class="text-gray-600 text-sm mb-2">Members:</p>
                                <div class="flex flex-wrap gap-2">
                                    <div class="avatar bg-red-600">ED</div>
                                    <div class="avatar bg-yellow-600">DG</div>
                                    <div class="avatar bg-indigo-600">SW</div>
                                    <div class="avatar bg-teal-600">DM</div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Last updated: 1 day ago</span>
                                <button onclick="openModal('group4')" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                    View Details <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="modal" class="modal fixed inset-0 z-50 hidden items-center justify-center">
        <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-2xl mx-auto rounded shadow-lg z-50 overflow-y-auto">
            <div class="modal-content py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <h2 id="modal-title" class="text-2xl font-bold text-gray-800"></h2>
                    <button onclick="closeModal()" class="modal-close cursor-pointer z-50 p-2">
                        <i class="fas fa-times text-gray-500 hover:text-gray-700"></i>
                    </button>
                </div>
                
                <div id="modal-description" class="mb-4 text-gray-700">
                    <!-- Content will be inserted here -->
                </div>
                
                <div class="flex justify-end pt-2 space-x-4">
                    <button onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Close
                    </button>
                    <button id="modal-action-button" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        View Full Details
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(group) {
            const modal = document.getElementById('modal');
            const title = document.getElementById('modal-title');
            const description = document.getElementById('modal-description');
            const actionButton = document.getElementById('modal-action-button');

            if (group === 'group1') {
                title.innerText = 'Group 1: Smart Attendance System';
                description.innerHTML = `
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Project Description</h3>
                        <p class="text-gray-600">This group is working on a Smart Attendance System that utilizes facial recognition technology to automate attendance tracking in classrooms and corporate environments.</p>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Members</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <div class="avatar bg-blue-500 mr-3">JM</div>
                                <div>
                                    <p class="font-medium">John Marvic Giray</p>
                                    <p class="text-sm text-gray-500">Team Leader</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="avatar bg-purple-500 mr-3">LR</div>
                                <div>
                                    <p class="font-medium">Leandro Reyes</p>
                                    <p class="text-sm text-gray-500">Developer</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="avatar bg-yellow-500 mr-3">ES</div>
                                <div>
                                    <p class="font-medium">Erico Santos</p>
                                    <p class="text-sm text-gray-500">Designer</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="avatar bg-red-500 mr-3">AC</div>
                                <div>
                                    <p class="font-medium">Angelo Cruz</p>
                                    <p class="text-sm text-gray-500">Researcher</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Status</h3>
                        <span class="status-badge bg-green-100 text-green-800">Approved</span>
                        <p class="text-sm text-gray-600 mt-1">Approved on August 15, 2025</p>
                    </div>
                `;
                actionButton.textContent = 'View Project Documents';
            } 
            // Similar blocks for other groups...

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('modal');
            if (event.target.classList.contains('modal-overlay')) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>