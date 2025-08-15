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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
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
            transition: width 1s ease-in-out;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .research-phase {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .research-phase.active {
            background-color: #3b82f6;
            color: white;
        }
        .research-phase.completed {
            background-color: #10b981;
            color: white;
        }
        .research-phase:hover::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
        }
        .notification-dot.pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        .hide-scrollbar {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .glow-card {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }
        .phase-tooltip {
            visibility: hidden;
            width: 120px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .research-phase:hover .phase-tooltip {
            visibility: visible;
            opacity: 1;
        }
        .floating-action {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .event-card:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
                            <span class="hidden md:inline font-medium"><?php echo htmlspecialchars($full_name ?? 'User'); ?></span>
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
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6 mb-8 text-white animate__animated animate__fadeIn">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">Welcome back, <?php echo explode(' ', $full_name ?? 'Researcher')[0]; ?>!</h2>
                            <p class="opacity-90">You're making great progress on your research journey. Keep it up!</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <div class="flex items-center bg-white/20 rounded-full px-4 py-2">
                                <i class="fas fa-trophy mr-2"></i>
                                <span>Current Streak: 5 days in a row</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Research Progress Overview -->
                <section class="mb-8 animate__animated animate__fadeInUp">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-primary"></i> Research Progress
                    </h2>
                    <div class="bg-white rounded-lg shadow-lg p-6 glow-card">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Thesis: "Machine Learning Applications in Healthcare"</h3>
                            <span class="text-sm font-medium bg-blue-100 text-blue-800 px-3 py-1 rounded-full animate-pulse">
                                <i class="fas fa-running mr-1"></i> In Progress
                            </span>
                        </div>
                        
                        <div class="mb-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Started: Jan 2023</span>
                                <span>65% Complete</span>
                                <span>Target: Dec 2023</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 65%"></div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="research-phase active p-4 rounded-lg text-center transition-all hover:shadow-md">
                                <div class="phase-tooltip">Proposal submitted and approved</div>
                                <i class="fas fa-file-alt text-3xl mb-3"></i>
                                <p class="text-sm font-medium">Proposal</p>
                                <p class="text-xs mt-1"><i class="fas fa-check-circle mr-1"></i>Completed</p>
                            </div>
                            <div class="research-phase active p-4 rounded-lg text-center transition-all hover:shadow-md">
                                <div class="phase-tooltip">Defense scheduled for May 30</div>
                                <i class="fas fa-user-tie text-3xl mb-3"></i>
                                <p class="text-sm font-medium">Defense</p>
                                <p class="text-xs mt-1"><i class="fas fa-calendar-day mr-1"></i>May 30</p>
                            </div>
                            <div class="research-phase p-4 rounded-lg text-center border-2 border-dashed border-blue-200 hover:border-blue-300 transition-all hover:shadow-md">
                                <div class="phase-tooltip">Awaiting defense results</div>
                                <i class="fas fa-clipboard-check text-3xl mb-3 text-blue-400"></i>
                                <p class="text-sm font-medium">Revision</p>
                                <p class="text-xs mt-1 text-blue-500"><i class="fas fa-clock mr-1"></i>Pending</p>
                            </div>
                            <div class="research-phase p-4 rounded-lg text-center border border-gray-200 hover:border-gray-300 transition-all hover:shadow-md">
                                <div class="phase-tooltip">Complete all revisions first</div>
                                <i class="fas fa-check-circle text-3xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium">Clearance</p>
                                <p class="text-xs mt-1 text-gray-400">Not Started</p>
                            </div>
                            <div class="research-phase p-4 rounded-lg text-center border border-gray-200 hover:border-gray-300 transition-all hover:shadow-md">
                                <div class="phase-tooltip">Final step after approval</div>
                                <i class="fas fa-graduation-cap text-3xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium">Completion</p>
                                <p class="text-xs mt-1 text-gray-400">Not Started</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Access Cards -->
                <section class="mb-8 animate__animated animate__fadeInUp">
                    <h2 class="text-xl font-semibold mb-6 text-gray-800 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-500"></i> Quick Access
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <a href="student_pages/proposal.php" class="bg-white rounded-xl shadow-md p-6 card-hover transition-all duration-300 border-l-4 border-blue-500 hover:border-blue-600 group">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <i class="fas fa-file-upload text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800 group-hover:text-blue-600">Proposal Submission</h3>
                                    <p class="text-sm text-gray-500 group-hover:text-gray-600">Submit your research proposal</p>
                                </div>
                            </div>
                            <div class="mt-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Action Required
                                </span>
                            </div>
                        </a>
                        
                        <a href="student_pages/defense.php" class="bg-white rounded-xl shadow-md p-6 card-hover transition-all duration-300 border-l-4 border-green-500 hover:border-green-600 group">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                    <i class="fas fa-calendar-check text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800 group-hover:text-green-600">Defense Scheduling</h3>
                                    <p class="text-sm text-gray-500 group-hover:text-gray-600">See your date of defense</p>
                                </div>
                            </div>
                            <div class="mt-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    New Update
                                </span>
                            </div>
                        </a>
                        
                        <a href="student_pages/documents.php" class="bg-white rounded-xl shadow-md p-6 card-hover transition-all duration-300 border-l-4 border-purple-500 hover:border-purple-600 group">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                    <i class="fas fa-tasks text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800 group-hover:text-purple-600">Document Tracker</h3>
                                    <p class="text-sm text-gray-500 group-hover:text-gray-600">Track document status</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-xs text-gray-400">3 pending reviews</span>
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                    <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                                    <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                                </div>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Upcoming Events & Deadlines -->
                <section class="mb-8 animate__animated animate__fadeInUp">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Upcoming Deadlines -->
                        <div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-calendar-times mr-2 text-red-500"></i> Upcoming Deadlines
                                </h2>
                                <a href="#" class="text-sm text-blue-600 hover:underline flex items-center">
                                    View all <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                </a>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-start p-4 border border-red-100 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    <div class="p-3 rounded-full bg-red-200 text-red-600 mr-4">
                                        <i class="fas fa-exclamation"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h3 class="font-medium text-red-800">Proposal Submission</h3>
                                            <span class="text-xs bg-red-200 text-red-800 px-2 py-1 rounded-full">High Priority</span>
                                        </div>
                                        <p class="text-sm text-red-600 mt-1"><i class="far fa-clock mr-1"></i>Due in 3 days - May 15, 2023</p>
                                        <div class="mt-2 flex items-center text-sm text-gray-600">
                                            <i class="fas fa-info-circle mr-2"></i> Final submission for committee review
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-start p-4 border border-blue-100 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    <div class="p-3 rounded-full bg-blue-200 text-blue-600 mr-4">
                                        <i class="fas fa-file-edit"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium text-blue-800">Chapter 1 Revision</h3>
                                        <p class="text-sm text-blue-600 mt-1"><i class="far fa-clock mr-1"></i>Due in 1 week - May 20, 2023</p>
                                        <div class="mt-2 flex items-center text-sm text-gray-600">
                                            <i class="fas fa-user mr-2"></i> Advisor: Dr. Smith
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-start p-4 border border-green-100 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                    <div class="p-3 rounded-full bg-green-200 text-green-600 mr-4">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium text-green-800">Group Meeting</h3>
                                        <p class="text-sm text-green-600 mt-1"><i class="far fa-clock mr-1"></i>Tomorrow - 2:00 PM</p>
                                        <div class="mt-2 flex items-center text-sm text-gray-600">
                                            <i class="fas fa-map-marker-alt mr-2"></i> Research Lab 302
                                        </div>
                                    </div>
                                    <button class="ml-4 text-green-600 hover:text-green-800">
                                        <i class="far fa-calendar-plus text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Events -->
                        <div class="space-y-6">
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-calendar-star mr-2 text-purple-500"></i> Upcoming Events
                                    </h2>
                                    <a href="seminars-festivals.php" class="text-sm text-blue-600 hover:underline flex items-center">
                                        View all <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                                <div class="space-y-4">
                                    <div class="event-card bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4 border border-purple-100 transition-all duration-300">
                                        <div class="flex items-start">
                                            <div class="bg-purple-600 text-white p-3 rounded-lg mr-4 text-center min-w-[60px] shadow-md">
                                                <div class="font-bold text-lg">24</div>
                                                <div class="text-xs uppercase">May</div>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-purple-800">Annual Research Festival</h3>
                                                <p class="text-sm text-purple-600 mt-1"><i class="far fa-clock mr-1"></i>9:00 AM - 5:00 PM</p>
                                                <p class="text-sm text-gray-600 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>Main Auditorium</p>
                                                <button class="mt-3 text-sm bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded-full transition-colors">
                                                    Register Now <i class="fas fa-arrow-right ml-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="event-card bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-100 transition-all duration-300">
                                        <div class="flex items-start">
                                            <div class="bg-blue-600 text-white p-3 rounded-lg mr-4 text-center min-w-[60px] shadow-md">
                                                <div class="font-bold text-lg">05</div>
                                                <div class="text-xs uppercase">Jun</div>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-blue-800">Data Science Workshop</h3>
                                                <p class="text-sm text-blue-600 mt-1"><i class="far fa-clock mr-1"></i>1:00 PM - 4:00 PM</p>
                                                <p class="text-sm text-gray-600 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>Computer Lab 3</p>
                                                <div class="mt-3 flex justify-between items-center">
                                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                        <i class="fas fa-users mr-1"></i> 12/25 spots left
                                                    </span>
                                                    <button class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-full transition-colors">
                                                        Learn More
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Research Tips -->
                            <div class="bg-white rounded-xl shadow-lg p-6 bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100">
                                <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-lightbulb mr-2 text-yellow-500"></i> Research Tip of the Day
                                </h2>
                                <div class="floating-action bg-white p-4 rounded-lg shadow-md border-l-4 border-yellow-400">
                                    <h3 class="font-medium text-gray-800 mb-2">Effective Literature Review</h3>
                                    <p class="text-sm text-gray-600">When reviewing literature, organize your sources by theme rather than chronology. This helps identify patterns and gaps more effectively.</p>
                                    <div class="mt-3 text-right">
                                        <button class="text-xs text-blue-600 hover:underline">
                                            Read more tips <i class="fas fa-chevron-right ml-1"></i>
                                        </button>
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

        // Animated progress update
        document.addEventListener('DOMContentLoaded', () => {
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                // Reset to 0 and animate to 65%
                progressFill.style.width = '0';
                setTimeout(() => {
                    progressFill.style.width = '65%';
                }, 500);
            }

            // Add hover effects to research phases
            const phases = document.querySelectorAll('.research-phase');
            phases.forEach(phase => {
                phase.addEventListener('mouseenter', () => {
                    phase.style.transform = 'scale(1.05)';
                });
                phase.addEventListener('mouseleave', () => {
                    phase.style.transform = 'scale(1)';
                });
            });

            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__fadeInUp');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('section').forEach(section => {
                observer.observe(section);
            });
        });

        // Simulate progress update (for demo purposes)
        setTimeout(() => {
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                progressFill.style.width = '75%';
                document.querySelector('.research-phase:nth-child(3)').classList.add('active');
                document.querySelector('.research-phase:nth-child(3) i').classList.remove('text-blue-400');
                document.querySelector('.research-phase:nth-child(3) i').classList.add('text-white');
                
                // Update tooltip
                const tooltip = document.querySelector('.research-phase:nth-child(3) .phase-tooltip');
                if (tooltip) {
                    tooltip.textContent = "Revisions received - work in progress";
                }
            }
        }, 3000);
    </script>
</body>
</html>