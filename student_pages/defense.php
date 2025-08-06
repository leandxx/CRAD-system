<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defense Schedule</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .countdown-timer {
            font-family: 'Courier New', monospace;
        }
        .progress-ring__circle {
            transition: stroke-dashoffset 0.5s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .floating-panel {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .slide-fade-enter-active {
            transition: all 0.3s ease-out;
        }
        .slide-fade-leave-active {
            transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
        }
        .slide-fade-enter-from,
        .slide-fade-leave-to {
            transform: translateX(20px);
            opacity: 0;
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
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <div class="flex items-center">
                  <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                     Defense Schedule
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

            <main class="flex-1 overflow-y-auto p-6">
                <!-- Countdown Banner -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-xl p-6 mb-6 floating-panel">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="mb-4 md:mb-0">
                            <h2 class="text-xl font-bold mb-2">Your Defense Countdown</h2>
                            <p class="text-blue-100">Final defense presentation on September 15, 2025</p>
                        </div>
                        <div class="countdown-timer text-3xl font-bold">
                            <span id="days">00</span>d : 
                            <span id="hours">00</span>h : 
                            <span id="minutes">00</span>m : 
                            <span id="seconds">00</span>s
                        </div>
                    </div>
                </div>

                <!-- Defense Overview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Schedule Card -->
                    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-700">Defense Schedule</h2>
                            <div class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">
                                CONFIRMED
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-600">Date & Time</h3>
                                    <p class="text-gray-900">September 15, 2025 • 10:00 AM – 11:30 AM</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-600">Venue</h3>
                                    <p class="text-gray-900">Innovation Lab Room 304</p>
                                    <button class="text-blue-600 text-sm mt-1 hover:underline flex items-center">
                                        <i class="fas fa-directions mr-1"></i> Get Directions
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-600">Panel Members</h3>
                                    <div class="mt-1 space-y-2">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full bg-blue-200 flex items-center justify-center text-blue-700 text-xs mr-2">
                                                JC
                                            </div>
                                            <span>Prof. Jonathan Cruz (Chair)</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full bg-purple-200 flex items-center justify-center text-purple-700 text-xs mr-2">
                                                AR
                                            </div>
                                            <span>Ms. Angelica Ramos</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full bg-green-200 flex items-center justify-center text-green-700 text-xs mr-2">
                                                MS
                                            </div>
                                            <span>Engr. Michael Santos</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements Card -->
                    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Submission Status</h2>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium">Completion Progress</span>
                                <span>2/3 (67%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 67%"></div>
                            </div>
                        </div>
                        
                        <ul class="space-y-3">
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                                    <span>Proposal Document</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-600 text-sm mr-2">Approved</span>
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                            </li>
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-laptop-code text-blue-500 mr-3"></i>
                                    <span>System Prototype</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-yellow-600 text-sm mr-2">Pending Review</span>
                                    <i class="fas fa-clock text-yellow-500"></i>
                                </div>
                            </li>
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-file-powerpoint text-orange-500 mr-3"></i>
                                    <span>Presentation Slides</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-red-600 text-sm mr-2">Not Submitted</span>
                                    <i class="fas fa-times-circle text-red-500"></i>
                                </div>
                            </li>
                        </ul>
                        
                        <button class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center">
                            <i class="fas fa-upload mr-2"></i> Upload Documents
                        </button>
                    </div>

                    <!-- Preparation Timeline -->
                    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Preparation Timeline</h2>
                        
                        <div class="relative">
                            <!-- Timeline -->
                            <div class="border-l-2 border-blue-200 pl-6 space-y-6">
                                <!-- Item 1 -->
                                <div class="relative">
                                    <div class="absolute -left-9 top-0 w-6 h-6 rounded-full bg-blue-500 border-4 border-white flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                    <div class="pl-2">
                                        <h3 class="font-medium">Submit Final Draft</h3>
                                        <p class="text-sm text-gray-600">Due: Sept 1, 2025</p>
                                        <p class="text-xs text-green-600 mt-1">Completed</p>
                                    </div>
                                </div>
                                
                                <!-- Item 2 -->
                                <div class="relative">
                                    <div class="absolute -left-9 top-0 w-6 h-6 rounded-full bg-blue-500 border-4 border-white flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                    <div class="pl-2">
                                        <h3 class="font-medium">Adviser Approval</h3>
                                        <p class="text-sm text-gray-600">Due: Sept 5, 2025</p>
                                        <p class="text-xs text-green-600 mt-1">Completed</p>
                                    </div>
                                </div>
                                
                                <!-- Item 3 -->
                                <div class="relative">
                                    <div class="absolute -left-9 top-0 w-6 h-6 rounded-full bg-yellow-500 border-4 border-white flex items-center justify-center">
                                        <i class="fas fa-spinner text-white text-xs"></i>
                                    </div>
                                    <div class="pl-2">
                                        <h3 class="font-medium">Presentation Rehearsal</h3>
                                        <p class="text-sm text-gray-600">Due: Sept 10, 2025</p>
                                        <p class="text-xs text-yellow-600 mt-1">In Progress</p>
                                    </div>
                                </div>
                                
                                <!-- Item 4 -->
                                <div class="relative">
                                    <div class="absolute -left-9 top-0 w-6 h-6 rounded-full bg-gray-300 border-4 border-white flex items-center justify-center">
                                        <i class="fas fa-clock text-white text-xs"></i>
                                    </div>
                                    <div class="pl-2">
                                        <h3 class="font-medium">Final Defense</h3>
                                        <p class="text-sm text-gray-600">Sept 15, 2025</p>
                                        <p class="text-xs text-gray-500 mt-1">Upcoming</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips & Resources Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Defense Tips -->
                    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300 lg:col-span-2">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Defense Preparation Tips</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Tip 1 -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <h3 class="font-medium">Presentation Skills</h3>
                                </div>
                                <ul class="text-sm text-gray-700 space-y-1 pl-11">
                                    <li>• Practice speaking clearly and slowly</li>
                                    <li>• Maintain eye contact with panel</li>
                                    <li>• Use gestures naturally</li>
                                </ul>
                            </div>
                            
                            <!-- Tip 2 -->
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <h3 class="font-medium">Content Mastery</h3>
                                </div>
                                <ul class="text-sm text-gray-700 space-y-1 pl-11">
                                    <li>• Know your research inside out</li>
                                    <li>• Prepare for potential questions</li>
                                    <li>• Highlight your contributions</li>
                                </ul>
                            </div>
                            
                            <!-- Tip 3 -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-3">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <h3 class="font-medium">Demo Preparation</h3>
                                </div>
                                <ul class="text-sm text-gray-700 space-y-1 pl-11">
                                    <li>• Test all functionality beforehand</li>
                                    <li>• Prepare backup screenshots</li>
                                    <li>• Have a clear demo script</li>
                                </ul>
                            </div>
                            
                            <!-- Tip 4 -->
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center mr-3">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <h3 class="font-medium">Professionalism</h3>
                                </div>
                                <ul class="text-sm text-gray-700 space-y-1 pl-11">
                                    <li>• Dress professionally</li>
                                    <li>• Arrive 30 minutes early</li>
                                    <li>• Bring printed copies</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Resources -->
                    <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Resources</h2>
                        
                        <div class="space-y-4">
                            <a href="#" class="flex items-center p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Defense Guidelines</h3>
                                    <p class="text-sm text-gray-500">Official university requirements</p>
                                </div>
                            </a>
                            
                            <a href="#" class="flex items-center p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="p-2 bg-purple-100 text-purple-600 rounded-lg mr-4">
                                    <i class="fas fa-file-powerpoint"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Presentation Template</h3>
                                    <p class="text-sm text-gray-500">Standard slide deck format</p>
                                </div>
                            </a>
                            
                            <a href="#" class="flex items-center p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="p-2 bg-green-100 text-green-600 rounded-lg mr-4">
                                    <i class="fas fa-video"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Sample Defense Videos</h3>
                                    <p class="text-sm text-gray-500">Previous successful defenses</p>
                                </div>
                            </a>
                            
                            <a href="#" class="flex items-center p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg mr-4">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Common Questions</h3>
                                    <p class="text-sm text-gray-500">Prepare your answers</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Countdown Timer
        function updateCountdown() {
            const defenseDate = new Date('September 15, 2025 10:00:00').getTime();
            const now = new Date().getTime();
            const distance = defenseDate - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Document upload simulation
        document.querySelector('button[class*="bg-blue-600"]').addEventListener('click', function() {
            alert('Document upload dialog would appear here');
        });
    </script>
</body>
</html>