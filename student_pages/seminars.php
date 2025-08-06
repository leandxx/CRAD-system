<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seminars & Festivals (Viva Pit Senior)</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .seminar-card {
            transition: all 0.3s ease;
        }
        .seminar-card:hover {
            transform: translateY(-5px);
        }
        .notification-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
                    Proposal Submission
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
                        <input type="text" placeholder="Search seminars..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <div class="flex gap-2">
                        <select class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>All Categories</option>
                            <option>Academic</option>
                            <option>Technical</option>
                            <option>Career</option>
                        </select>
                        <select class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>Upcoming</option>
                            <option>Past Events</option>
                            <option>Registered</option>
                        </select>
                    </div>
                </div>

                <!-- Seminar List -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Seminar Card 1 -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden seminar-card border-l-4 border-purple-600">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <h2 class="text-xl font-semibold text-gray-800 mb-2">Capstone Proposal Writing</h2>
                                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">Academic</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Learn how to write a strong and effective capstone proposal that will impress your panelists.</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-day mr-2 text-purple-500"></i>
                                    <span>August 12, 2025</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-clock mr-2 text-purple-500"></i>
                                    <span>10:00 AM - 12:00 PM</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-user-tie mr-2 text-purple-500"></i>
                                    <span>Dr. Maria Santos</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>
                                    <span>Auditorium, 3rd Floor</span>
                                </div>
                            </div>
                            <button class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-user-plus"></i> Register Now
                            </button>
                        </div>
                    </div>

                    <!-- Seminar Card 2 -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden seminar-card border-l-4 border-blue-600">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <h2 class="text-xl font-semibold text-gray-800 mb-2">Thesis Defense Preparation</h2>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Technical</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Master the art of presenting your research with confidence and handling panel questions effectively.</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-day mr-2 text-blue-500"></i>
                                    <span>August 15, 2025</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-clock mr-2 text-blue-500"></i>
                                    <span>1:00 PM - 3:00 PM</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-user-tie mr-2 text-blue-500"></i>
                                    <span>Engr. Ronald Dela Cruz</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                    <span>Room 302, CS Department</span>
                                </div>
                            </div>
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-user-plus"></i> Register Now
                            </button>
                        </div>
                    </div>

                    <!-- Seminar Card 3 -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden seminar-card border-l-4 border-green-600">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <h2 class="text-xl font-semibold text-gray-800 mb-2">Research Methodology Workshop</h2>
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Academic</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Learn proper research techniques, data collection methods, and analysis approaches.</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-day mr-2 text-green-500"></i>
                                    <span>August 20, 2025</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-clock mr-2 text-green-500"></i>
                                    <span>9:00 AM - 11:30 AM</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-user-tie mr-2 text-green-500"></i>
                                    <span>Prof. Angela Reyes</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-2 text-green-500"></i>
                                    <span>Research Center, 2nd Floor</span>
                                </div>
                            </div>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-user-plus"></i> Register Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Festivals Section -->
                <h2 class="text-2xl font-bold text-blue-700 mt-10 mb-6">Upcoming Festivals</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Festival Card 1 -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden seminar-card border-l-4 border-red-600">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <h2 class="text-xl font-semibold text-gray-800 mb-2">Tech Innovation Fair 2025</h2>
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Exhibition</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Showcase of student projects and innovations with industry partners and potential investors.</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-day mr-2 text-red-500"></i>
                                    <span>September 5-7, 2025</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-clock mr-2 text-red-500"></i>
                                    <span>9:00 AM - 5:00 PM Daily</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>
                                    <span>University Gymnasium</span>
                                </div>
                            </div>

    <script>
        // You can add JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Add click event to seminar cards
            const seminarCards = document.querySelectorAll('.seminar-card');
            seminarCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    if (!e.target.classList.contains('seminar-card') && 
                        !e.target.closest('button')) {
                        // Navigate to seminar details or show modal
                        console.log('View seminar details');
                    }
                });
            });
        });
    </script>
</body>
</html>