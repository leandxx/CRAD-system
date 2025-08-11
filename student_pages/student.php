<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <title>CRAD Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-collapse {
            transition: all 0.3s ease;
        }
        .active-nav-item {
            background-color: #3b82f6;
            color: white;
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e5e7eb;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background-color: #3b82f6;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .research-phase {
            transition: all 0.3s ease;
        }
        .research-phase.active {
            background-color: #3b82f6;
            color: white;
        }
        .research-phase.completed {
            background-color: #10b981;
            color: white;
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
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/student-sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <div class="flex items-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                    Dashboard
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

            <main class="flex-1 overflow-y-auto p-6 hide-scrollbar">
                <!-- Research Progress Overview -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Research Progress</h2>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Thesis: "Machine Learning Applications in Healthcare"</h3>
                            <span class="text-sm font-medium text-blue-600">65% Complete</span>
                        </div>
                        
                        <div class="progress-bar mb-6">
                            <div class="progress-fill" style="width: 65%"></div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="research-phase active p-3 rounded-lg text-center">
                                <i class="fas fa-file-alt text-2xl mb-2"></i>
                                <p class="text-sm font-medium">Proposal</p>
                                <p class="text-xs">Submitted</p>
                            </div>
                            <div class="research-phase active p-3 rounded-lg text-center">
                                <i class="fas fa-user-tie text-2xl mb-2"></i>
                                <p class="text-sm font-medium">Adviser</p>
                                <p class="text-xs">Assigned</p>
                            </div>
                            <div class="research-phase p-3 rounded-lg text-center border border-gray-200">
                                <i class="fas fa-microphone text-2xl mb-2 text-gray-400"></i>
                                <p class="text-sm font-medium">Defense</p>
                                <p class="text-xs">Pending</p>
                            </div>
                            <div class="research-phase p-3 rounded-lg text-center border border-gray-200">
                                <i class="fas fa-clipboard-check text-2xl mb-2 text-gray-400"></i>
                                <p class="text-sm font-medium">Revision</p>
                                <p class="text-xs">Pending</p>
                            </div>
                            <div class="research-phase p-3 rounded-lg text-center border border-gray-200">
                                <i class="fas fa-graduation-cap text-2xl mb-2 text-gray-400"></i>
                                <p class="text-sm font-medium">Completion</p>
                                <p class="text-xs">Pending</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Access Cards -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Quick Access</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Research Process Cards -->
                        <a href="proposal-submission.php" class="bg-white rounded-lg shadow p-6 card-hover transition-all duration-300">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <i class="fas fa-file-upload text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Proposal Submission</h3>
                                    <p class="text-sm text-gray-500">Submit your research proposal</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="adviser-assignment.php" class="bg-white rounded-lg shadow p-6 card-hover transition-all duration-300">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                    <i class="fas fa-user-tie text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Adviser Assignment</h3>
                                    <p class="text-sm text-gray-500">View assigned adviser</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="defense-scheduling.php" class="bg-white rounded-lg shadow p-6 card-hover transition-all duration-300">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <i class="fas fa-calendar-check text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Defense Scheduling</h3>
                                    <p class="text-sm text-gray-500">Schedule your defense</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="document-tracker.php" class="bg-white rounded-lg shadow p-6 card-hover transition-all duration-300">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                    <i class="fas fa-tasks text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Document Tracker</h3>
                                    <p class="text-sm text-gray-500">Track document status</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Upcoming Events & Deadlines -->
                <section class="mb-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Upcoming Deadlines -->
                        <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">Upcoming Deadlines</h2>
                                <a href="#" class="text-sm text-blue-600 hover:underline">View all</a>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-start p-3 border-b border-gray-100 hover:bg-gray-50 rounded">
                                    <div class="p-2 rounded-full bg-red-100 text-red-600 mr-4">
                                        <i class="fas fa-exclamation"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium">Proposal Submission</h3>
                                        <p class="text-sm text-gray-500">Due in 3 days - May 15, 2023</p>
                                    </div>
                                    <button class="text-blue-600 text-sm font-medium">Submit Now</button>
                                </div>
                                
                                <div class="flex items-start p-3 border-b border-gray-100 hover:bg-gray-50 rounded">
                                    <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-4">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium">Chapter 1 Revision</h3>
                                        <p class="text-sm text-gray-500">Due in 1 week - May 20, 2023</p>
                                    </div>
                                    <button class="text-blue-600 text-sm font-medium">View Details</button>
                                </div>
                                
                                <div class="flex items-start p-3 hover:bg-gray-50 rounded">
                                    <div class="p-2 rounded-full bg-green-100 text-green-600 mr-4">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium">Group Meeting</h3>
                                        <p class="text-sm text-gray-500">Tomorrow - 2:00 PM</p>
                                    </div>
                                    <button class="text-blue-600 text-sm font-medium">Add to Calendar</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Support Services -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-semibold mb-4 text-gray-800">Support Services</h2>
                            <div class="space-y-4">
                                <a href="payment-verification.php" class="flex items-center p-3 hover:bg-gray-50 rounded transition">
                                    <div class="p-2 rounded-full bg-indigo-100 text-indigo-600 mr-4">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium">Payment Verification</h3>
                                        <p class="text-sm text-gray-500">Check payment status</p>
                                    </div>
                                </a>
                                
                                <a href="research-facilities.php" class="flex items-center p-3 hover:bg-gray-50 rounded transition">
                                    <div class="p-2 rounded-full bg-teal-100 text-teal-600 mr-4">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium">Research Facilities</h3>
                                        <p class="text-sm text-gray-500">Book equipment/labs</p>
                                    </div>
                                </a>
                                
                                <a href="analytics.php" class="flex items-center p-3 hover:bg-gray-50 rounded transition">
                                    <div class="p-2 rounded-full bg-orange-100 text-orange-600 mr-4">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium">Analytics</h3>
                                        <p class="text-sm text-gray-500">View research metrics</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Events & Research Groups -->
                <section>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Upcoming Events -->
                        <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">Upcoming Events</h2>
                                <a href="seminars-festivals.php" class="text-sm text-blue-600 hover:underline">View all</a>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start">
                                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg mr-4 text-center">
                                            <div class="font-bold text-lg">24</div>
                                            <div class="text-xs">MAY</div>
                                        </div>
                                        <div>
                                            <h3 class="font-medium">Annual Research Festival</h3>
                                            <p class="text-sm text-gray-500">9:00 AM - 5:00 PM</p>
                                            <p class="text-sm mt-1">Main Auditorium</p>
                                            <button class="mt-2 text-sm text-blue-600 hover:underline">Register Now</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-start">
                                        <div class="bg-green-100 text-green-800 p-2 rounded-lg mr-4 text-center">
                                            <div class="font-bold text-lg">05</div>
                                            <div class="text-xs">JUN</div>
                                        </div>
                                        <div>
                                            <h3 class="font-medium">Data Science Workshop</h3>
                                            <p class="text-sm text-gray-500">1:00 PM - 4:00 PM</p>
                                            <p class="text-sm mt-1">Computer Lab 3</p>
                                            <button class="mt-2 text-sm text-blue-600 hover:underline">Learn More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Research Groups -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">Research Groups</h2>
                                <a href="research-groups.php" class="text-sm text-blue-600 hover:underline">View all</a>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center p-3 hover:bg-gray-50 rounded transition">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium">AI Research Group</h3>
                                        <p class="text-sm text-gray-500">12 members</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-3 hover:bg-gray-50 rounded transition">
                                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-4">
                                        <i class="fas fa-leaf"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium">Environmental Science</h3>
                                        <p class="text-sm text-gray-500">8 members</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-3 hover:bg-gray-50 rounded transition">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mr-4">
                                        <i class="fas fa-heartbeat"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium">Health Informatics</h3>
                                        <p class="text-sm text-gray-500">15 members</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        // Toggle sidebar visibility
        document.getElementById("toggleSidebar")?.addEventListener("click", () => {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("hidden");
        });

        // Simulate progress update (for demo purposes)
        setTimeout(() => {
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                progressFill.style.width = '75%';
                document.querySelector('.research-phase:nth-child(3)').classList.add('active');
                document.querySelector('.research-phase:nth-child(3) i').classList.remove('text-gray-400');
                document.querySelector('.research-phase:nth-child(3) i').classList.add('text-white');
            }
        }, 2000);
    </script>
</body>
</html>