<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Facilities</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .facility-card {
            transition: all 0.3s ease;
        }
        .facility-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .availability-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .available {
            background-color: #10b981;
        }
        .unavailable {
            background-color: #ef4444;
        }
        .soon-available {
            background-color: #f59e0b;
        }
        .map-container {
            height: 300px;
            background-color: #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

         <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                    Facilities
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

            <main class="flex-1 overflow-y-auto p-6">
                <!-- Notification Banner -->
                <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-r-lg flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium">AI-Powered Facility Assignment</p>
                        <p class="text-sm">Our system automatically assigns the best facility for your defense based on your research requirements and availability.</p>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button class="tab-btn px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600" data-tab="assigned">
                        Your Assignment
                    </button>
                    <button class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-blue-600" data-tab="facilities">
                        Available Facilities
                    </button>
                    <button class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-blue-600" data-tab="booking">
                        Booking History
                    </button>
                </div>

                <!-- Assigned Facility Tab -->
                <div id="assigned-tab" class="tab-content active">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Capstone Submission Status -->
                        <div class="bg-white p-6 rounded-xl shadow facility-card">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-blue-700">Your Capstone Submission</h2>
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">Submitted</span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                        <i class="fas fa-file-upload"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700">Final document submitted</p>
                                        <p class="text-sm text-gray-500">August 2, 2025</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700">Adviser approval received</p>
                                        <p class="text-sm text-gray-500">August 3, 2025</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700">AI scheduling completed</p>
                                        <p class="text-sm text-gray-500">August 4, 2025</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Facility -->
                        <div class="bg-white p-6 rounded-xl shadow facility-card">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-blue-700">Your Defense Facility</h2>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">Confirmed</span>
                            </div>
                            <div class="flex items-start mb-4">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium">Room 402 - Tech Lab Building</h3>
                                    <p class="text-gray-600">Capacity: 25 people | Projector, Whiteboard, Video Recording</p>
                                    <button class="text-blue-600 hover:underline text-sm mt-1 flex items-center">
                                        <i class="fas fa-directions mr-1"></i> Get Directions
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Defense Schedule</h3>
                                    <p class="text-gray-700">August 10, 2025</p>
                                    <p class="text-gray-700">10:00 AM - 11:30 AM</p>
                                    <button class="text-blue-600 hover:underline text-sm mt-1 flex items-center">
                                        <i class="fas fa-calendar-plus mr-1"></i> Add to Calendar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Recommendation Explanation -->
                    <div class="bg-white p-6 rounded-xl shadow mb-6">
                        <h3 class="text-xl font-semibold text-blue-700 mb-4">Why This Facility Was Assigned</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="flex items-start p-3 bg-blue-50 rounded-lg">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-full mr-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Group Size</h4>
                                    <p class="text-sm text-gray-600">Your group of 4 members fits perfectly in this space</p>
                                </div>
                            </div>
                            <div class="flex items-start p-3 bg-blue-50 rounded-lg">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-full mr-3">
                                    <i class="fas fa-microchip"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Technical Needs</h4>
                                    <p class="text-sm text-gray-600">Required: Projector, Video Recording</p>
                                </div>
                            </div>
                            <div class="flex items-start p-3 bg-blue-50 rounded-lg">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-full mr-3">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Panel Availability</h4>
                                    <p class="text-sm text-gray-600">All panel members available at this time</p>
                                </div>
                            </div>
                            <div class="flex items-start p-3 bg-blue-50 rounded-lg">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-full mr-3">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">AI Score</h4>
                                    <p class="text-sm text-gray-600">94% match with your requirements</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 italic">
                            <i class="fas fa-lightbulb mr-1 text-yellow-500"></i> This recommendation was generated through our AI scheduling system analyzing 15 different factors.
                        </div>
                    </div>

                    <!-- Facility Map -->
                    <div class="bg-white p-6 rounded-xl shadow">
                        <h3 class="text-xl font-semibold text-blue-700 mb-4">Facility Location</h3>
                        <div class="map-container flex items-center justify-center text-gray-500 mb-4">
                            <!-- In a real implementation, this would be an embedded map -->
                            <div class="text-center">
                                <i class="fas fa-map-marked-alt text-4xl mb-2 text-blue-500"></i>
                                <p>Interactive map of Tech Lab Building</p>
                                <p class="text-sm">Room 402 highlighted</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="border p-3 rounded-lg">
                                <h4 class="font-medium mb-1">Building Access</h4>
                                <p class="text-sm text-gray-600">Main entrance, 3rd floor</p>
                            </div>
                            <div class="border p-3 rounded-lg">
                                <h4 class="font-medium mb-1">Parking Information</h4>
                                <p class="text-sm text-gray-600">Visitor lot B, 50m from building</p>
                            </div>
                            <div class="border p-3 rounded-lg">
                                <h4 class="font-medium mb-1">Accessibility</h4>
                                <p class="text-sm text-gray-600">Wheelchair accessible, elevator available</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Facilities Tab -->
                <div id="facilities-tab" class="tab-content">
                    <div class="bg-white p-6 rounded-xl shadow mb-6">
                        <h2 class="text-xl font-semibold text-blue-700 mb-4">Available Research Facilities</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Facility 1 -->
                            <div class="border rounded-lg overflow-hidden facility-card">
                                <div class="h-40 bg-blue-100 flex items-center justify-center text-blue-500">
                                    <i class="fas fa-laptop-house text-4xl"></i>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-semibold">Tech Lab 402</h3>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                            <span class="availability-dot available"></span> Available
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3">Capacity: 25 | Projector, Recording</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">3 slots today</span>
                                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Facility 2 -->
                            <div class="border rounded-lg overflow-hidden facility-card">
                                <div class="h-40 bg-purple-100 flex items-center justify-center text-purple-500">
                                    <i class="fas fa-chalkboard-teacher text-4xl"></i>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-semibold">Seminar Room 205</h3>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                            <span class="availability-dot soon-available"></span> Limited
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3">Capacity: 15 | Whiteboard, TV</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">1 slot today</span>
                                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Facility 3 -->
                            <div class="border rounded-lg overflow-hidden facility-card">
                                <div class="h-40 bg-green-100 flex items-center justify-center text-green-500">
                                    <i class="fas fa-flask text-4xl"></i>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-semibold">Science Lab 310</h3>
                                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                                            <span class="availability-dot unavailable"></span> Booked
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3">Capacity: 20 | Lab Equipment</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Next available: Aug 12</span>
                                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow">
                        <h3 class="text-xl font-semibold text-blue-700 mb-4">Request Facility Change</h3>
                        <p class="text-gray-700 mb-4">If you have special requirements not met by your current assignment, you may request a facility change.</p>
                        
                        <form class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Facility</label>
                                <select class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select facility</option>
                                    <option value="lab402">Tech Lab 402</option>
                                    <option value="room205">Seminar Room 205</option>
                                    <option value="lab310">Science Lab 310</option>
                                    <option value="auditorium">Main Auditorium</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Change</label>
                                <textarea rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Explain why you need a different facility..."></textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Booking History Tab -->
                <div id="booking-tab" class="tab-content">
                    <div class="bg-white rounded-xl shadow overflow-hidden">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold">Your Facility Booking History</h2>
                            <div class="relative">
                                <input type="text" placeholder="Search bookings..." class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Current Booking -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Aug 10, 2025</div>
                                            <div class="text-sm text-gray-500">10:00-11:30 AM</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Tech Lab 402</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Final Defense</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Confirmed</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Details</a>
                                            <a href="#" class="text-red-600 hover:text-red-900">Cancel</a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Past Booking -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Jul 15, 2025</div>
                                            <div class="text-sm text-gray-500">2:00-4:00 PM</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Seminar Room 205</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Progress Meeting</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Completed</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="#" class="text-blue-600 hover:text-blue-900">Details</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">2</span> of <span class="font-medium">2</span> bookings
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs and buttons
                document.querySelectorAll('.tab-btn').forEach(t => {
                    t.classList.remove('text-blue-600', 'border-blue-600');
                    t.classList.add('text-gray-500', 'hover:text-blue-600');
                });
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.remove('text-gray-500', 'hover:text-blue-600');
                this.classList.add('text-blue-600', 'border-blue-600');
                
                // Show corresponding tab content
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Simulate facility details modal
        document.querySelectorAll('button:contains("View Details")').forEach(btn => {
            btn.addEventListener('click', function() {
                alert('Facility details modal would appear here with calendar availability');
            });
        });
    </script>
</body>
</html>