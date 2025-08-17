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
        <?php include('../includes/admin-sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden h-screen">
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4 shadow-sm">
                <div class="flex items-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary flex items-center">
                        Admin Dashboard
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
                    <h3 class="font-bold text-lg">Current Phase Countdown</h3>
                    <p class="text-sm opacity-90">Revisions Period - Ends October 10, 2025 at 11:59 PM</p>
                </div>
            </div>
            <div class="flex items-center">
                <div class="text-center px-4">
                    <div id="admin-countdown-days" class="text-3xl font-bold">05</div>
                    <div class="text-xs opacity-90">Days</div>
                </div>
                <div class="text-2xl font-bold opacity-70">:</div>
                <div class="text-center px-4">
                    <div id="admin-countdown-hours" class="text-3xl font-bold">14</div>
                    <div class="text-xs opacity-90">Hours</div>
                </div>
                <div class="text-2xl font-bold opacity-70">:</div>
                <div class="text-center px-4">
                    <div id="admin-countdown-minutes" class="text-3xl font-bold">23</div>
                    <div class="text-xs opacity-90">Minutes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Management -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="bg-primary/10 p-3 rounded-full mr-4">
                    <i class="fas fa-calendar-alt text-primary text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Active Submission Timeline</h2>
            </div>
            <div class="flex space-x-2">
                <button class="text-primary hover:text-secondary transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button class="text-gray-500 hover:text-gray-700 transition">
                    <i class="fas fa-toggle-on mr-1"></i> Disable
                </button>
            </div>
        </div>

        <!-- Progress Timeline -->
        <div class="relative">
            <div class="h-2 bg-gray-200 rounded-full mb-8">
                <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full" style="width: 60%"></div>
            </div>
            
            <div class="flex justify-between relative">
                <!-- Milestone 1 -->
                <div class="text-center w-1/4">
                    <div class="milestone-dot bg-primary text-white mx-auto mb-2">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="text-sm font-medium">Draft Submission</div>
                    <div class="text-xs text-gray-500">Sep 15, 2023</div>
                </div>
                
                <!-- Milestone 2 -->
                <div class="text-center w-1/4">
                    <div class="milestone-dot bg-warning text-white mx-auto mb-2">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="text-sm font-medium">Revisions</div>
                    <div class="text-xs text-gray-500">Oct 10, 2023</div>
                    <div class="text-xs mt-1 font-medium text-warning">5 days left</div>
                </div>
                
                <!-- Milestone 3 -->
                <div class="text-center w-1/4">
                    <div class="milestone-dot bg-gray-200 text-gray-600 mx-auto mb-2">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="text-sm font-medium">Final Submission</div>
                    <div class="text-xs text-gray-500">Dec 1, 2023</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Proposals Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="bg-primary/10 p-3 rounded-full mr-4">
                    <i class="fas fa-users text-primary text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Group Proposals</h2>
            </div>
            <div class="flex space-x-3">
                <div class="relative">
                    <select class="appearance-none bg-gray-100 border border-gray-200 rounded-lg px-4 py-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option>All Groups</option>
                        <option>Pending Review</option>
                        <option>Approved</option>
                        <option>Needs Revision</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-xs text-gray-500 pointer-events-none"></i>
                </div>
                <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition flex items-center">
                    <i class="fas fa-download mr-2"></i> Export
                </button>
            </div>
        </div>

        <!-- Group Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Group 1 -->
            <div class="border rounded-lg overflow-hidden hover:shadow-md transition-all">
                <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-800">Group Alpha</h3>
                    <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium">
                        Revisions Needed
                    </span>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-medium">E-Commerce Platform</p>
                            <p class="text-sm text-gray-500">Submitted: Oct 5, 2023</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">Adviser</p>
                            <p class="text-sm text-gray-500">Dr. Maria Santos</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-sm font-medium text-gray-700 mb-1">Members</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">Juan Dela Cruz</span>
                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">Maria Garcia</span>
                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">John Smith</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center pt-3 border-t">
                        <button class="text-primary hover:text-secondary text-sm flex items-center">
                            <i class="fas fa-eye mr-1"></i> View
                        </button>
                        <div class="flex space-x-2">
                            <button class="text-success hover:text-success-dark text-sm flex items-center">
                                <i class="fas fa-check mr-1"></i> Approve
                            </button>
                            <button class="text-danger hover:text-danger-dark text-sm flex items-center">
                                <i class="fas fa-times mr-1"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Group 2 -->
            <div class="border rounded-lg overflow-hidden hover:shadow-md transition-all">
                <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-800">Group Beta</h3>
                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                        Under Review
                    </span>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-medium">AI Learning System</p>
                            <p class="text-sm text-gray-500">Submitted: Oct 8, 2023</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">Adviser</p>
                            <p class="text-sm text-gray-500">Prof. James Wilson</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-sm font-medium text-gray-700 mb-1">Members</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">Sarah Johnson</span>
                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">Michael Brown</span>
                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">Emily Davis</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center pt-3 border-t">
                        <button class="text-primary hover:text-secondary text-sm flex items-center">
                            <i class="fas fa-eye mr-1"></i> View
                        </button>
                        <div class="flex space-x-2">
                            <button class="text-success hover:text-success-dark text-sm flex items-center">
                                <i class="fas fa-check mr-1"></i> Approve
                            </button>
                            <button class="text-danger hover:text-danger-dark text-sm flex items-center">
                                <i class="fas fa-times mr-1"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-500">
                Showing <span class="font-medium">1</span> to <span class="font-medium">2</span> of <span class="font-medium">8</span> groups
            </div>
            <div class="flex space-x-2">
                <button class="px-3 py-1 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-100">
                    Previous
                </button>
                <button class="px-3 py-1 rounded-lg bg-primary text-white">
                    1
                </button>
                <button class="px-3 py-1 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                    2
                </button>
                <button class="px-3 py-1 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-100">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Admin countdown timer
    function updateAdminCountdown() {
        const deadline = new Date('2025-09-15T23:59:59').getTime();
        const now = new Date().getTime();
        const distance = deadline - now;
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById("admin-countdown-days").textContent = days.toString().padStart(2, '0');
        document.getElementById("admin-countdown-hours").textContent = hours.toString().padStart(2, '0');
        document.getElementById("admin-countdown-minutes").textContent = minutes.toString().padStart(2, '0');
        
        if (distance < (24 * 60 * 60 * 1000)) {
            document.querySelector('.bg-gradient-to-r').classList.remove('from-primary', 'to-secondary');
            document.querySelector('.bg-gradient-to-r').classList.add('from-red-500', 'to-red-600');
        }
        
        if (distance < 0) {
            document.querySelector('.bg-gradient-to-r').classList.add('from-gray-500', 'to-gray-600');
        }
    }

    // Initialize and update every minute
    updateAdminCountdown();
    setInterval(updateAdminCountdown, 60000);
</script>

</body>
</html>