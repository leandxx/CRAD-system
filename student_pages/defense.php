<?php
session_start();
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
          <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>
        


        

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