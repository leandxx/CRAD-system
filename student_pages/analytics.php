<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Analytics</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .progress-ring__circle {
            transition: stroke-dashoffset 0.5s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
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
                    Analytics
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
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Research Progress -->
                    <div class="bg-white shadow rounded-lg p-5 card-hover transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-sm text-gray-500 font-medium">Research Progress</h2>
                                <p class="text-2xl font-semibold text-blue-600 mt-2">65%</p>
                            </div>
                            <div class="relative w-16 h-16">
                                <svg class="w-full h-full" viewBox="0 0 36 36">
                                    <path
                                        d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none"
                                        stroke="#e5e7eb"
                                        stroke-width="3"
                                    />
                                    <path
                                        d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none"
                                        stroke="#3b82f6"
                                        stroke-width="3"
                                        stroke-dasharray="65, 100"
                                        class="progress-ring__circle"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-blue-600">65%</div>
                            </div>
                        </div>
                        <div class="mt-3 text-sm text-gray-500">
                            <span class="text-green-500"><i class="fas fa-caret-up mr-1"></i> 12%</span> from last month
                        </div>
                    </div>

                    <!-- Adviser Feedbacks -->
                    <div class="bg-white shadow rounded-lg p-5 card-hover transition-all">
                        <h2 class="text-sm text-gray-500 font-medium">Adviser Feedbacks</h2>
                        <p class="text-2xl font-semibold text-green-500 mt-2">4 Reviews</p>
                        <div class="mt-3 flex items-center">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center text-blue-600">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 text-sm text-gray-500">
                                <span class="font-medium">3</span> pending responses
                            </div>
                        </div>
                    </div>

                    <!-- Milestones Completed -->
                    <div class="bg-white shadow rounded-lg p-5 card-hover transition-all">
                        <h2 class="text-sm text-gray-500 font-medium">Milestones Completed</h2>
                        <p class="text-2xl font-semibold text-purple-500 mt-2">3/5</p>
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: 60%"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Next: Final Defense</div>
                        </div>
                    </div>

                    <!-- Research Score -->
                    <div class="bg-white shadow rounded-lg p-5 card-hover transition-all">
                        <h2 class="text-sm text-gray-500 font-medium">Research Score</h2>
                        <div class="flex items-end mt-2">
                            <p class="text-2xl font-semibold text-yellow-500">8.7</p>
                            <span class="text-gray-500 text-sm ml-1">/10</span>
                        </div>
                        <div class="mt-2 flex">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fas fa-star text-yellow-400 text-sm <?php echo $i<=4 ? 'mr-1' : ''; ?>"></i>
                            <?php endfor; ?>
                            <span class="text-xs text-gray-500 ml-2">(4.35/5)</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Progress Chart -->
                    <div class="bg-white rounded-lg shadow p-6 lg:col-span-2 fade-in">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-700">Research Timeline Progress</h3>
                            <select class="text-sm border border-gray-300 rounded px-3 py-1 focus:outline-none">
                                <option>Last 6 Months</option>
                                <option selected>This Year</option>
                                <option>All Time</option>
                            </select>
                        </div>
                        <div class="h-80">
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>

                    <!-- Feedback Distribution -->
                    <div class="bg-white rounded-lg shadow p-6 fade-in">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">Feedback Types</h3>
                        <div class="h-80">
                            <canvas id="feedbackChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity & Resource Usage -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow p-6 lg:col-span-2 fade-in">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <div class="flex items-start border-b border-gray-100 pb-4">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-full mr-4">
                                    <i class="fas fa-comment-alt"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h4 class="font-medium">New feedback on Chapter 3</h4>
                                        <span class="text-xs text-gray-500">2 hours ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Dr. Smith provided comments on your methodology section.</p>
                                </div>
                            </div>
                            <div class="flex items-start border-b border-gray-100 pb-4">
                                <div class="p-2 bg-green-100 text-green-600 rounded-full mr-4">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h4 class="font-medium">Proposal approved</h4>
                                        <span class="text-xs text-gray-500">1 day ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Your research proposal has been approved by the committee.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-full mr-4">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h4 class="font-medium">Defense scheduled</h4>
                                        <span class="text-xs text-gray-500">3 days ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Your final defense is scheduled for June 15, 2023.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Usage -->
                    <div class="bg-white rounded-lg shadow p-6 fade-in">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">Resource Usage</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Lab Equipment Hours</span>
                                    <span>12/20 hrs</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: 60%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Supervisor Meetings</span>
                                    <span>5/8 sessions</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 62.5%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Library Resources</span>
                                    <span>8/10 items</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-500 h-2 rounded-full" style="width: 80%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Software Licenses</span>
                                    <span>2/3 active</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 66%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Progress Chart
        const progressCtx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(progressCtx, {
            type: 'line',
            data: {
                labels: ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'],
                datasets: [{
                    label: 'Completion %',
                    data: [5, 15, 25, 30, 45, 55, 60, 65, 65],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Expected %',
                    data: [10, 20, 35, 45, 55, 65, 75, 80, 85],
                    borderColor: '#e5e7eb',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });

        // Feedback Chart
        const feedbackCtx = document.getElementById('feedbackChart').getContext('2d');
        const feedbackChart = new Chart(feedbackCtx, {
            type: 'doughnut',
            data: {
                labels: ['Methodology', 'Writing', 'Content', 'Formatting', 'References'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#8b5cf6',
                        '#ef4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // Animation for cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = 1;
                }, index * 100);
            });
        });
    </script>
</body>
</html>