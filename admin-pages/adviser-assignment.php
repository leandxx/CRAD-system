<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adviser Assignment</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
  /* Add this to your existing styles */
  .scroll-container {
    max-height: calc(100vh - 80px);
    overflow-y: auto;
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

        function toggleModal() {
            const modal = document.getElementById('assignmentModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
        
        // Function to handle search
        function handleSearch() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.section-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchValue) ? '' : 'none';
            });
        }
        
        // Function to filter by status
        function filterByStatus() {
            const statusValue = document.getElementById('statusFilter').value;
            const cards = document.querySelectorAll('.section-card');
            
            cards.forEach(card => {
                const status = card.getAttribute('data-status');
                if (statusValue === 'all' || status === statusValue) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
    <style>
        .notification-dot.pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #e5e7eb;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans h-screen overflow-hidden">

    <body class="bg-gray-50 text-gray-800 font-sans h-screen">
    <div class="flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        <?php include('../includes/admin-header.php'); ?>


            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto p-6 scroll-container">              
                  <!-- Dashboard Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-primary">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-primary mr-4">
                                <i class="fas fa-layer-group text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Total Sections</h3>
                                <p class="text-2xl font-bold">12</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-success">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-success mr-4">
                                <i class="fas fa-user-check text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Assigned Sections</h3>
                                <p class="text-2xl font-bold">8</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-warning">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-warning mr-4">
                                <i class="fas fa-user-clock text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Pending Assignments</h3>
                                <p class="text-2xl font-bold">4</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-secondary">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-secondary mr-4">
                                <i class="fas fa-chalkboard-teacher text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Available Advisers</h3>
                                <p class="text-2xl font-bold">15</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Bar -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-4">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search sections..." class="pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full md:w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        
                        <div>
                            <select id="statusFilter" onchange="filterByStatus()" class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full md:w-48">
                                <option value="all">All Status</option>
                                <option value="assigned">Assigned</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <button onclick="toggleModal()" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i> New Assignment
                        </button>
                    </div>
                </div>
                
                <!-- Sections Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Section Card 1 -->
                    <div class="section-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="assigned">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Section A</h3>
                                <p class="text-sm text-gray-500">4th Year - Computer Science</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-success text-white">
                                Assigned
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Students: 25/25</span>
                                <span>100%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center mr-3">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Dr. Maria Santos</p>
                                    <p class="text-xs text-gray-500">Computer Science Department</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Assigned on: Jan 15, 2023</p>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded text-sm transition-colors">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button class="flex-1 bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section Card 2 -->
                    <div class="section-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="pending">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Section B</h3>
                                <p class="text-sm text-gray-500">4th Year - Engineering</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-warning text-white">
                                Pending
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Students: 0/28</span>
                                <span>0%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-warning" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mr-3">
                                    <i class="fas fa-question text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-400">No adviser assigned</p>
                                    <p class="text-xs text-gray-400">Pending assignment</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Created on: Feb 1, 2023</p>
                        </div>
                        
                        <div class="mt-4">
                            <button onclick="toggleModal()" class="w-full bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-link mr-1"></i> Assign Adviser
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section Card 3 -->
                    <div class="section-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="assigned">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Section C</h3>
                                <p class="text-sm text-gray-500">4th Year - Business Administration</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-success text-white">
                                Assigned
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Students: 22/22</span>
                                <span>100%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center mr-3">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Prof. James Wilson</p>
                                    <p class="text-xs text-gray-500">Business Department</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Assigned on: Dec 10, 2022</p>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded text-sm transition-colors">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button class="flex-1 bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section Card 4 -->
                    <div class="section-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="assigned">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Section D</h3>
                                <p class="text-sm text-gray-500">4th Year - Mathematics</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-success text-white">
                                Assigned
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Students: 30/30</span>
                                <span>100%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center mr-3">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Dr. Lisa Chen</p>
                                    <p class="text-xs text-gray-500">Mathematics Department</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Assigned on: Nov 5, 2022</p>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded text-sm transition-colors">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button class="flex-1 bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section Card 5 -->
                    <div class="section-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="pending">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Section E</h3>
                                <p class="text-sm text-gray-500">4th Year - Psychology</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-warning text-white">
                                Pending
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Students: 0/26</span>
                                <span>0%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-warning" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mr-3">
                                    <i class="fas fa-question text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-400">No adviser assigned</p>
                                    <p class="text-xs text-gray-400">Pending assignment</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Created on: Jan 28, 2023</p>
                        </div>
                        
                        <div class="mt-4">
                            <button onclick="toggleModal()" class="w-full bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-link mr-1"></i> Assign Adviser
                            </button>
                        </div>
                    </div>
                    
                    <!-- Section Card 6 -->
                    <div class="section-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="assigned">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Section F</h3>
                                <p class="text-sm text-gray-500">4th Year - Architecture</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-success text-white">
                                Assigned
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Students: 18/18</span>
                                <span>100%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center mr-3">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Prof. Robert Garcia</p>
                                    <p class="text-xs text-gray-500">Architecture Department</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Assigned on: Oct 15, 2022</p>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded text-sm transition-colors">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button class="flex-1 bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Assign Adviser to Section</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4">
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Section</label>
                            <select class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">-- Select a section --</option>
                                <option value="B">Section B - 4th Year Engineering (28 students)</option>
                                <option value="E">Section E - 4th Year Psychology (26 students)</option>
                                <option value="G">Section G - 4th Year Literature (24 students)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Adviser</label>
                            <select class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">-- Select an adviser --</option>
                                <option value="1">Dr. Michael Brown (Engineering)</option>
                                <option value="2">Dr. Sarah Johnson (Psychology)</option>
                                <option value="3">Prof. Emily Williams (Literature)</option>
                                <option value="4">Dr. David Lee (Engineering)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Details</label>
                        <textarea class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" rows="3" placeholder="Add any notes about this assignment..."></textarea>
                    </div>
                    
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="sendEmail" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="sendEmail" class="ml-2 block text-sm text-gray-700">Send notification email to adviser</label>
                    </div>
                </form>
            </div>
            <div class="border-t px-6 py-4 bg-gray-50 flex justify-end">
                <button onclick="toggleModal()" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Cancel
                </button>
                <button class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Confirm Assignment
                </button>
            </div>
        </div>
    </div>

    <script>
        // Initialize search functionality
        document.getElementById('searchInput').addEventListener('keyup', handleSearch);
    </script>
</body>
</html>