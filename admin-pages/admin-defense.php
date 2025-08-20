<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defense Scheduling</title>
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
            const modal = document.getElementById('proposalModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
        
        // Function to handle status filtering
        function filterStatus(status) {
            const rows = document.querySelectorAll('#defenseTable tbody tr');
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
            
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.getAttribute('data-filter') === status) {
                    btn.classList.add('bg-primary', 'text-white');
                    btn.classList.remove('bg-gray-200', 'text-gray-700');
                } else {
                    btn.classList.remove('bg-primary', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                }
            });
        }
        
        // Function to handle search
        function handleSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#defenseTable tbody tr');
            
            rows.forEach(row => {
                const textContent = row.textContent.toLowerCase();
                if (textContent.includes(searchTerm)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Set default filter to 'all'
            filterStatus('all');
        });
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
        .schedule-card {
            transition: all 0.3s ease;
        }
        .schedule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        <?php include('../includes/admin-header.php'); ?>


            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto p-6" style="max-height: calc(100vh - 80px);">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                        <div>
                            <p class="text-gray-600">Total Proposals</p>
                            <h3 class="text-2xl font-bold">42</h3>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-file-alt text-primary text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                        <div>
                            <p class="text-gray-600">Scheduled</p>
                            <h3 class="text-2xl font-bold">28</h3>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-calendar-check text-success text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                        <div>
                            <p class="text-gray-600">Pending</p>
                            <h3 class="text-2xl font-bold">10</h3>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-clock text-warning text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                        <div>
                            <p class="text-gray-600">Completed</p>
                            <h3 class="text-2xl font-bold">4</h3>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-check-circle text-secondary text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Filters and Actions -->
                <div class="bg-white rounded-lg shadow mb-6 p-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex flex-wrap gap-2">
                            <button onclick="filterStatus('all')" data-filter="all" class="filter-btn px-3 py-1 rounded-full bg-primary text-white text-sm">All</button>
                            <button onclick="filterStatus('scheduled')" data-filter="scheduled" class="filter-btn px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm">Scheduled</button>
                            <button onclick="filterStatus('pending')" data-filter="pending" class="filter-btn px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm">Pending</button>
                            <button onclick="filterStatus('completed')" data-filter="completed" class="filter-btn px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm">Completed</button>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative">
                                <input type="text" id="searchInput" placeholder="Search proposals..." onkeyup="handleSearch()" class="pl-10 pr-4 py-2 border rounded-lg w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-primary">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <button onclick="toggleModal()" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Schedule Defense
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Defense Schedule Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table id="defenseTable" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group/Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Panel</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Example Row 1 -->
                                <tr data-status="scheduled">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">Automated Student Attendance System</div>
                                        <div class="text-sm text-gray-500">Group Alpha</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">John Smith, Maria Garcia, David Lee</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">May 15, 2023</div>
                                        <div class="text-sm text-gray-500">10:00 AM - 11:30 AM</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Room 302, CS Building
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Dr. Johnson, Prof. Williams, Dr. Kim
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Scheduled</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                                        <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <!-- Example Row 2 -->
                                <tr data-status="pending">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">E-Commerce Platform with AI Recommendations</div>
                                        <div class="text-sm text-gray-500">Group Beta</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Sarah Johnson, Michael Chen, Emily Wilson</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Not scheduled</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        -
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Not assigned
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                                        <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <!-- Example Row 3 -->
                                <tr data-status="completed">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">IoT-Based Smart Home System</div>
                                        <div class="text-sm text-gray-500">Group Gamma</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Robert Brown, Jennifer Davis, Thomas Moore</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">April 28, 2023</div>
                                        <div class="text-sm text-gray-500">2:00 PM - 3:30 PM</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Room 105, Engineering Building
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Dr. Anderson, Prof. Martinez, Dr. White
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-eye"></i></button>
                                        <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <!-- Add more rows as needed -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Upcoming Defenses Section -->
                <h2 class="text-xl font-bold mt-8 mb-4 text-gray-700">Upcoming Defenses</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <!-- Defense Card 1 -->
                    <div class="schedule-card bg-white rounded-lg shadow p-4 border-l-4 border-primary">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg">Automated Student Attendance System</h3>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Scheduled</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Group Alpha</p>
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <span>May 15, 2023 | 10:00 AM - 11:30 AM</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <i class="far fa-building mr-2"></i>
                            <span>Room 302, CS Building</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-200 border-2 border-white flex items-center justify-center">
                                    <span class="text-xs font-semibold">JJ</span>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-green-200 border-2 border-white flex items-center justify-center">
                                    <span class="text-xs font-semibold">MW</span>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-purple-200 border-2 border-white flex items-center justify-center">
                                    <span class="text-xs font-semibold">SK</span>
                                </div>
                            </div>
                            <button class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">View Details</button>
                        </div>
                    </div>
                    
                    <!-- Defense Card 2 -->
                    <div class="schedule-card bg-white rounded-lg shadow p-4 border-l-4 border-warning">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg">Blockchain Voting System</h3>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded">Pending</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Group Delta</p>
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <span>Not scheduled yet</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <i class="far fa-building mr-2"></i>
                            <span>Venue not assigned</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">Panel not assigned</div>
                            <button class="text-xs bg-primary hover:bg-blue-700 text-white px-2 py-1 rounded">Schedule Now</button>
                        </div>
                    </div>
                    
                    <!-- Defense Card 3 -->
                    <div class="schedule-card bg-white rounded-lg shadow p-4 border-l-4 border-success">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg">Healthcare Management System</h3>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Completed</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-3">Group Epsilon</p>
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <span>April 20, 2023 | 9:00 AM - 10:30 AM</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <i class="far fa-building mr-2"></i>
                            <span>Room 201, Medical Building</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">Score: 92/100</div>
                            <button class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">View Results</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Schedule Defense Modal -->
    <div id="proposalModal" class="fixed inset-0 w-full h-full flex items-center justify-center z-50 hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Schedule Defense</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="proposalSelect">
                                Select Proposal
                            </label>
                            <select id="proposalSelect" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">-- Select a proposal --</option>
                                <option value="1">Automated Student Attendance System - Group Alpha</option>
                                <option value="2">E-Commerce Platform with AI Recommendations - Group Beta</option>
                                <option value="3">IoT-Based Smart Home System - Group Gamma</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="defenseDate">
                                Defense Date
                            </label>
                            <input type="date" id="defenseDate" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="startTime">
                                Start Time
                            </label>
                            <input type="time" id="startTime" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="endTime">
                                End Time
                            </label>
                            <input type="time" id="endTime" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="venue">
                                Venue
                            </label>
                            <input type="text" id="venue" placeholder="Enter venue" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="panel">
                                Panel Members
                            </label>
                            <select id="panel" multiple class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary h-32">
                                <option value="1">Dr. Johnson</option>
                                <option value="2">Prof. Williams</option>
                                <option value="3">Dr. Kim</option>
                                <option value="4">Dr. Anderson</option>
                                <option value="5">Prof. Martinez</option>
                                <option value="6">Dr. White</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple panel members</p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
                            Additional Notes
                        </label>
                        <textarea id="notes" rows="3" placeholder="Any additional information..." class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="toggleModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700">
                            Schedule Defense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>