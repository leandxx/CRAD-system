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
        <?php include('../includes/student-sidebar.php'); ?>
        

  <!-- Main Content Area -->
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

            <div class="flex-1 overflow-y-auto p-6">
    <!-- Header with Countdown -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white p-4 rounded-lg shadow-lg mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="flex items-center mb-4 md:mb-0">
            <i class="fas fa-clock text-2xl mr-3"></i>
            <div>
                <h3 class="font-bold text-lg">Next Proposal Deadline</h3>
                <p class="text-sm opacity-90" id="deadline-text">Final Submission - August 22, 2025 at 11:59 PM</p>
            </div>
        </div>
        <div class="flex items-center">
            <div class="text-center px-4">
                <div id="countdown-days" class="text-3xl font-bold">07</div>
                <div class="text-xs opacity-90">Days</div>
            </div>
            <div class="text-2xl font-bold opacity-70">:</div>
            <div class="text-center px-4">
                <div id="countdown-hours" class="text-3xl font-bold">23</div>
                <div class="text-xs opacity-90">Hours</div>
            </div>
            <div class="text-2xl font-bold opacity-70">:</div>
            <div class="text-center px-4">
                <div id="countdown-minutes" class="text-3xl font-bold">59</div>
                <div class="text-xs opacity-90">Minutes</div>
            </div>
            <div class="text-2xl font-bold opacity-70">:</div>
            <div class="text-center px-4">
                <div id="countdown-seconds" class="text-3xl font-bold">59</div>
                <div class="text-xs opacity-90">Seconds</div>
            </div>
        </div>
    </div>
</div>

<main class="flex-1 overflow-y-auto p-6 space-y-6">

<!-- Enhanced Timeline Section -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex items-center mb-6">
        <div class="bg-primary/10 p-3 rounded-full mr-4">
            <i class="fas fa-calendar-check text-primary text-xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Your Submission Timeline</h2>
    </div>
    
    <!-- Progress Timeline -->
    <div class="relative">
        <!-- Progress bar -->
        <div class="h-2 bg-gray-200 rounded-full mb-8">
            <div id="progress-bar" class="h-full bg-gradient-to-r from-primary to-secondary rounded-full" style="width: 75%"></div>
        </div>
        
        <!-- Milestones -->
        <div class="flex justify-between relative">
            <!-- Milestone 1 -->
            <div class="text-center w-1/4">
                <div class="milestone-dot bg-primary text-white mx-auto mb-2">
                    <i class="fas fa-check"></i>
                </div>
                <div class="text-sm font-medium">Proposal Draft</div>
                <div class="text-xs text-gray-500">Due: Aug 1, 2025</div>
                <div class="text-xs mt-1 font-medium text-success">Completed</div>
            </div>
            
            <!-- Milestone 2 -->
            <div class="text-center w-1/4">
                <div class="milestone-dot bg-primary text-white mx-auto mb-2">
                    <i class="fas fa-check"></i>
                </div>
                <div class="text-sm font-medium">Initial Review</div>
                <div class="text-xs text-gray-500">Due: Aug 8, 2025</div>
                <div class="text-xs mt-1 font-medium text-success">Completed</div>
            </div>
            
            <!-- Milestone 3 -->
            <div class="text-center w-1/4">
                <div class="milestone-dot bg-warning text-white mx-auto mb-2">
                    <i class="fas fa-exclamation"></i>
                </div>
                <div class="text-sm font-medium">Revisions</div>
                <div class="text-xs text-gray-500" id="current-due-date">Due: Aug 15, 2025</div>
                <div class="text-xs mt-1 font-medium text-warning" id="current-countdown">
                    5 days 12 hours left
                </div>
            </div>
            
            <!-- Milestone 4 -->
            <div class="text-center w-1/4">
                <div class="milestone-dot bg-gray-200 text-gray-600 mx-auto mb-2">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="text-sm font-medium">Final Submission</div>
                <div class="text-xs text-gray-500">Due: Aug 22, 2025</div>
                <div class="text-xs mt-1 font-medium text-gray-600" id="next-countdown">
                    12 days left
                </div>
            </div>
        </div>
    </div>
    
    <!-- Current Task Card -->
    <div class="mt-8 bg-blue-50 border border-blue-100 rounded-lg p-4">
        <div class="flex items-start">
            <div class="bg-blue-100 p-2 rounded-full mr-3">
                <i class="fas fa-tasks text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-medium text-blue-800 mb-1">Current Task: Revisions</h3>
                <p class="text-sm text-blue-700">
                    You need to submit your revised proposal by <span class="font-semibold">August 15, 2025</span>.
                    <span id="dynamic-message">You have 5 days and 12 hours remaining</span>.
                </p>
                <div class="mt-3 flex items-center">
                    <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition flex items-center text-sm">
                        <i class="fas fa-upload mr-2"></i> Submit Revision
                    </button>
                    <button class="ml-3 text-primary hover:text-secondary transition flex items-center text-sm">
                        <i class="fas fa-comment-alt mr-2"></i> View Feedback
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Submission Section -->
<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <div class="bg-primary/10 p-3 rounded-full mr-4">
                <i class="fas fa-file-upload text-primary text-xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Your Submissions</h2>
        </div>
        <div class="flex space-x-3">
            <button onclick="toggleModal()" class="bg-gradient-to-r from-primary to-secondary text-white px-4 py-2 rounded-lg hover:shadow-md transition-all duration-300 flex items-center">
                <i class="fas fa-plus mr-2"></i> New Submission
            </button>
            <div class="relative">
                <select class="appearance-none bg-gray-100 border border-gray-200 rounded-lg px-4 py-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                    <option>All Status</option>
                    <option>Draft</option>
                    <option>Submitted</option>
                    <option>Under Review</option>
                    <option>Approved</option>
                    <option>Rejected</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-3 text-xs text-gray-500 pointer-events-none"></i>
            </div>
        </div>
    </div>

    <!-- Submission Cards -->
    <div class="space-y-4">
        <!-- Active Submission -->
        <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md">
            <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg text-gray-800 flex items-center">
                        E-Commerce Platform
                        <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium flex items-center">
                            <i class="fas fa-edit mr-1"></i> Revisions Required
                        </span>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="far fa-clock mr-1"></i> Submitted: August 5, 2025
                        <span class="mx-2">•</span>
                        <i class="fas fa-user-tie mr-1"></i> Adviser: Dr. Smith
                    </p>
                </div>
                <div class="flex space-x-2">
                    <button class="text-primary hover:text-secondary transition">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="text-gray-500 hover:text-gray-700 transition">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="text-red-500 hover:text-red-700 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <div class="px-5 py-3 bg-blue-50 border-t">
                <div class="flex items-center text-sm text-blue-700">
                    <i class="fas fa-comment-alt mr-2"></i>
                    <span><strong>Adviser Feedback:</strong> Please revise chapters 2-3 and add more case studies</span>
                </div>
                <div class="mt-2 flex justify-end">
                    <button class="text-sm text-primary hover:text-secondary flex items-center">
                        <i class="fas fa-paper-plane mr-1"></i> Submit Revision
                    </button>
                </div>
            </div>
        </div>

        <!-- Past Submission -->
        <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md">
            <div class="bg-gray-50 px-5 py-4 border-b flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg text-gray-800 flex items-center">
                        School Management System
                        <span class="ml-3 text-xs px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 font-medium flex items-center">
                            <i class="fas fa-check-circle mr-1"></i> Approved
                        </span>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="far fa-clock mr-1"></i> Submitted: July 28, 2025
                        <span class="mx-2">•</span>
                        <i class="fas fa-user-tie mr-1"></i> Adviser: Prof. Johnson
                    </p>
                </div>
                <div class="flex space-x-2">
                    <button class="text-primary hover:text-secondary transition">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-gray-500 hover:text-gray-700 transition">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<script>
    // Real-time countdown for header
    const updateCountdown = () => {
        const deadline = new Date('2025-08-22T23:59:59').getTime();
        const now = new Date().getTime();
        const distance = deadline - now;
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById("countdown-days").textContent = days.toString().padStart(2, '0');
        document.getElementById("countdown-hours").textContent = hours.toString().padStart(2, '0');
        document.getElementById("countdown-minutes").textContent = minutes.toString().padStart(2, '0');
        document.getElementById("countdown-seconds").textContent = seconds.toString().padStart(2, '0');
        
        if (distance < (24 * 60 * 60 * 1000)) {
            document.querySelector('.bg-gradient-to-r').classList.remove('from-primary', 'to-secondary');
            document.querySelector('.bg-gradient-to-r').classList.add('from-red-500', 'to-red-600');
        }
        
        if (distance < 0) {
            document.getElementById("deadline-text").textContent = "Final Submission - Deadline Passed";
            document.querySelector('.bg-gradient-to-r').classList.add('from-gray-500', 'to-gray-600');
        }
    };

    // Update timeline progress
    const updateTimeline = () => {
        const currentDue = new Date('2025-08-15T23:59:59');
        const finalDue = new Date('2025-08-22T23:59:59');
        const now = new Date();
        
        // Current milestone countdown
        if (now < currentDue) {
            const diff = currentDue - now;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            
            document.getElementById("current-countdown").textContent = 
                `${days} day${days !== 1 ? 's' : ''} ${hours} hour${hours !== 1 ? 's' : ''} left`;
            document.getElementById("dynamic-message").textContent = 
                `You have ${days} day${days !== 1 ? 's' : ''} and ${hours} hour${hours !== 1 ? 's' : ''} remaining`;
        }
        
        // Next milestone countdown
        if (now < finalDue) {
            const diff = finalDue - now;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            document.getElementById("next-countdown").textContent = `${days} day${days !== 1 ? 's' : ''} left`;
        }
    };

    // Initialize and update every second
    updateCountdown();
    updateTimeline();
    setInterval(updateCountdown, 1000);
    setInterval(updateTimeline, 1000);
</script>

</body>
</html>