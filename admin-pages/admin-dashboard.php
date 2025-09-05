<?php
session_start();
include('../includes/connection.php'); // Your DB connection
date_default_timezone_set('Asia/Manila'); // Set Manila timezone

// Fetch dashboard statistics
$total_students = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles"))[0];
$total_groups = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM groups"))[0];
$total_proposals = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM proposals"))[0];
$pending_proposals = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM proposals WHERE status = 'pending'"))[0];
$total_faculty = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM faculty"))[0];
$total_clusters = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters"))[0];

// Calculate real statistics
$students_last_month = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)"))[0] ?? 0;
$students_prev_month = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH) AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)"))[0] ?? 1;
$student_growth = $students_prev_month > 0 ? round((($students_last_month - $students_prev_month) / $students_prev_month) * 100) : ($students_last_month > 0 ? 100 : 0);

$proposals_today = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM proposals WHERE DATE(submitted_at) = CURDATE()"))[0] ?? 0;
$completed_proposals = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM proposals WHERE status = 'Completed'"))[0];
$proposal_progress = $total_proposals > 0 ? round(($completed_proposals / $total_proposals) * 100) : 0;

$overdue_proposals = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM proposals p WHERE p.status = 'Pending' AND p.submitted_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"))[0] ?? 0;
$pending_progress = $total_proposals > 0 ? round(($pending_proposals / $total_proposals) * 100) : 0;

$active_groups = $total_groups; // All groups are considered active
$group_progress = $total_groups > 0 ? round(($active_groups / max($total_groups, 1)) * 100) : 0;

// Recent activities
$recent_students = mysqli_query($conn, "SELECT full_name, program, id FROM student_profiles ORDER BY id DESC LIMIT 3");
$recent_proposals = mysqli_query($conn, "SELECT title, status, id FROM proposals ORDER BY id DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Submission</title>
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #ffffff;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: slideInUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }
        
        .glass-card:hover::before {
            left: 100%;
        }
        
        .glass-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.18);
        }
        
        .quick-access-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .quick-access-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.6s;
        }
        
        .quick-access-card:hover::after {
            left: 100%;
        }
        
        .quick-access-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .animate-delay-1 { animation-delay: 0.2s; }
        .animate-delay-2 { animation-delay: 0.4s; }
        .animate-delay-3 { animation-delay: 0.6s; }
        
        /* Enhanced gradient effects */
        .gradient-blue {
            background: linear-gradient(135deg, #3b82f6, #1e40af, #1d4ed8);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .gradient-green {
            background: linear-gradient(135deg, #10b981, #047857, #059669);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        .gradient-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed, #6d28d9);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }
        .gradient-yellow {
            background: linear-gradient(135deg, #f59e0b, #d97706, #b45309);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        .gradient-red {
            background: linear-gradient(135deg, #ef4444, #dc2626, #b91c1c);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
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
    </script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 font-sans">

    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        
            <!-- Main content area -->
                <main class="flex-1 overflow-y-auto p-4 lg:p-8 hide-scrollbar">
    <!-- Welcome Section -->
    <div class="mb-8 animate-fade-in">
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white shadow-2xl">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div>
                    <h1 class="text-3xl lg:text-4xl font-bold mb-2">Welcome to CRAD Admin Portal</h1>
                    <p class="text-blue-100 text-lg">Manage research activities and monitor system performance</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold"><?= date('F d, Y') ?></div>
                            <div class="text-sm text-blue-100"><?= date('l') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-fade-in">
        <div class="glass-card rounded-2xl p-6 group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <div class="gradient-blue p-2 rounded-lg mr-3">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-sm uppercase tracking-wide">Students</p>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-3"><?= $total_students ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="gradient-blue h-2 rounded-full transition-all duration-1000" style="width: <?= min(100, max(10, ($total_students / 50) * 100)) ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4">
                <p class="<?= $student_growth >= 0 ? 'text-green-600' : 'text-red-600' ?> text-sm font-semibold">
                    <i class="fas fa-arrow-<?= $student_growth >= 0 ? 'up' : 'down' ?> mr-1"></i> <?= abs($student_growth) ?>% <?= $student_growth >= 0 ? 'increase' : 'decrease' ?>
                </p>
                <span class="text-xs text-gray-500">vs last month</span>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 group animate-delay-1">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <div class="gradient-purple p-2 rounded-lg mr-3">
                            <i class="fas fa-file-alt text-white text-lg"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-sm uppercase tracking-wide">Proposals</p>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-3"><?= $total_proposals ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="gradient-purple h-2 rounded-full transition-all duration-1000" style="width: <?= $proposal_progress ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4">
                <p class="text-green-600 text-sm font-semibold"><i class="fas fa-plus mr-1"></i> <?= $proposals_today ?> new today</p>
                <span class="text-xs text-gray-500">submissions</span>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 group animate-delay-2">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <div class="gradient-yellow p-2 rounded-lg mr-3">
                            <i class="fas fa-clock text-white text-lg"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-sm uppercase tracking-wide">Pending</p>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-3"><?= $pending_proposals ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="gradient-yellow h-2 rounded-full transition-all duration-1000" style="width: <?= $pending_progress ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4">
                <p class="text-red-600 text-sm font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i> <?= $overdue_proposals ?> overdue</p>
                <span class="text-xs text-gray-500">need attention</span>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 group animate-delay-3">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <div class="gradient-green p-2 rounded-lg mr-3">
                            <i class="fas fa-users-cog text-white text-lg"></i>
                        </div>
                        <p class="text-gray-600 font-semibold text-sm uppercase tracking-wide">Groups</p>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-3"><?= $total_groups ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="gradient-green h-2 rounded-full transition-all duration-1000" style="width: <?= $group_progress ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4">
                <p class="text-blue-600 text-sm font-semibold"><i class="fas fa-layer-group mr-1"></i> <?= $total_clusters ?> clusters</p>
                <span class="text-xs text-gray-500">active</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-10 animate-fade-in">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <div class="gradient-blue p-3 rounded-xl mr-4 shadow-lg">
                    <i class="fas fa-bolt text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Quick Actions</h3>
                    <p class="text-gray-600 text-sm">Frequently used administrative tools</p>
                </div>
            </div>
            <div class="hidden lg:flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-600">System Online</span>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="admin-pages/admin-timeline.php" class="quick-access-card rounded-2xl p-8 text-center group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-500/10 to-transparent rounded-full -mr-10 -mt-10"></div>
                <div class="gradient-blue p-5 rounded-2xl mb-6 mx-auto w-fit group-hover:scale-110 transition-all duration-300 shadow-lg">
                    <i class="fas fa-calendar-week text-white text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-lg mb-2">Research Timeline</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Manage submission deadlines and research milestones</p>
                <div class="mt-4 flex items-center justify-center text-blue-600 group-hover:text-blue-700">
                    <span class="text-xs font-semibold">Manage Timeline</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
            <a href="admin-pages/admin-defense.php" class="quick-access-card rounded-2xl p-8 text-center group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-500/10 to-transparent rounded-full -mr-10 -mt-10"></div>
                <div class="gradient-purple p-5 rounded-2xl mb-6 mx-auto w-fit group-hover:scale-110 transition-all duration-300 shadow-lg">
                    <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-lg mb-2">Defense Scheduling</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Schedule and coordinate thesis defense sessions</p>
                <div class="mt-4 flex items-center justify-center text-purple-600 group-hover:text-purple-700">
                    <span class="text-xs font-semibold">Schedule Defense</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
            <a href="admin-pages/panel-assignment.php" class="quick-access-card rounded-2xl p-8 text-center group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-500/10 to-transparent rounded-full -mr-10 -mt-10"></div>
                <div class="gradient-green p-5 rounded-2xl mb-6 mx-auto w-fit group-hover:scale-110 transition-all duration-300 shadow-lg">
                    <i class="fas fa-user-friends text-white text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-lg mb-2">Panel Assignment</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Assign and manage evaluation panel members</p>
                <div class="mt-4 flex items-center justify-center text-green-600 group-hover:text-green-700">
                    <span class="text-xs font-semibold">Manage Panels</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
            <a href="admin-pages/adviser-assignment.php" class="quick-access-card rounded-2xl p-8 text-center group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-500/10 to-transparent rounded-full -mr-10 -mt-10"></div>
                <div class="gradient-red p-5 rounded-2xl mb-6 mx-auto w-fit group-hover:scale-110 transition-all duration-300 shadow-lg">
                    <i class="fas fa-user-graduate text-white text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-lg mb-2">Adviser Assignment</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Assign thesis advisers to student groups</p>
                <div class="mt-4 flex items-center justify-center text-red-600 group-hover:text-red-700">
                    <span class="text-xs font-semibold">Assign Advisers</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Activity Dashboard -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-10 animate-fade-in">
        <!-- Recent Activity -->
        <div class="xl:col-span-2 glass-card rounded-2xl p-8">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center">
                    <div class="gradient-blue p-3 rounded-xl mr-4 shadow-lg">
                        <i class="fas fa-activity text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Recent Activity</h3>
                        <p class="text-gray-600 text-sm">Latest system activities and registrations</p>
                    </div>
                </div>
                <button onclick="document.getElementById('activityModal').style.display='flex'" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">View All</button>
            </div>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($recent_students) > 0): ?>
                    <?php while ($student = mysqli_fetch_assoc($recent_students)): ?>
                    <div class="flex items-start p-4 rounded-xl bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border border-blue-100 hover:shadow-md transition-all">
                        <div class="gradient-blue p-3 rounded-xl mr-4 shadow-lg">
                            <i class="fas fa-user-plus text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-gray-800">New Student Registration</p>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">New</span>
                            </div>
                            <p class="text-gray-700 font-medium mt-1"><?= htmlspecialchars($student['full_name']) ?></p>
                            <div class="flex items-center mt-2 text-sm text-gray-600">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                <span><?= htmlspecialchars($student['program']) ?></span>
                                <span class="mx-2">•</span>
                                <span>ID: <?= $student['id'] ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                        <p class="text-gray-500">No recent student registrations</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Proposals -->
        <div class="glass-card rounded-2xl p-8">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center">
                    <div class="gradient-purple p-3 rounded-xl mr-4 shadow-lg">
                        <i class="fas fa-file-text text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Latest Proposals</h3>
                        <p class="text-gray-600 text-sm">Recent submissions</p>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($recent_proposals) > 0): ?>
                    <?php while ($proposal = mysqli_fetch_assoc($recent_proposals)): ?>
                    <div class="p-4 rounded-xl bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-bold text-gray-800 text-sm leading-tight"><?= htmlspecialchars(substr($proposal['title'], 0, 40)) ?><?= strlen($proposal['title']) > 40 ? '...' : '' ?></h4>
                            <span class="text-xs px-2 py-1 rounded-full <?= $proposal['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                <?= ucfirst($proposal['status']) ?>
                            </span>
                        </div>
                        <div class="flex items-center text-xs text-gray-500 mt-3">
                            <i class="fas fa-hashtag mr-1"></i>
                            <span><?= $proposal['id'] ?></span>
                            <span class="mx-2">•</span>
                            <i class="fas fa-clock mr-1"></i>
                            <span>Recently submitted</span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-file-alt text-gray-300 text-3xl mb-4"></i>
                        <p class="text-gray-500 text-sm">No recent proposals</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-200">
                <button onclick="document.getElementById('proposalsModal').style.display='flex'" class="w-full bg-purple-50 text-purple-600 hover:bg-purple-100 py-2 px-4 rounded-lg text-sm font-semibold transition-colors text-center block">
                    View All Proposals
                </button>
            </div>
        </div>
    </div>

    <!-- System Status Footer -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6 animate-fade-in">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <div class="flex items-center mb-4 lg:mb-0">
                <div class="bg-green-500 p-3 rounded-xl mr-4">
                    <i class="fas fa-server text-white text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800">System Status</h4>
                    <p class="text-gray-600 text-sm">All systems operational</p>
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-800"><?= $total_faculty ?></div>
                    <div class="text-xs text-gray-600">Faculty Members</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-800"><?= $total_clusters ?></div>
                    <div class="text-xs text-gray-600">Active Clusters</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-green-600">99.9%</div>
                    <div class="text-xs text-gray-600">Uptime</div>
                </div>
            </div>
        </div>
    </div>

    

            </main>
        </div>
    </div>

    <!-- Activity Modal -->
    <div id="activityModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50" style="display:none;">
        <div class="bg-white rounded-2xl p-8 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">All Recent Activities</h3>
                <button onclick="document.getElementById('activityModal').style.display='none'" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-4">
                <?php 
                // Get all recent students for modal
                $all_students = mysqli_query($conn, "SELECT full_name, program, id FROM student_profiles ORDER BY id DESC LIMIT 20");
                if (mysqli_num_rows($all_students) > 0): 
                    while ($student = mysqli_fetch_assoc($all_students)): 
                ?>
                <div class="flex items-start p-4 rounded-xl bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border border-blue-100">
                    <div class="bg-blue-500 p-3 rounded-xl mr-4">
                        <i class="fas fa-user-plus text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-gray-800">New Student Registration</p>
                        <p class="text-gray-700 font-medium mt-1"><?= htmlspecialchars($student['full_name']) ?></p>
                        <div class="flex items-center mt-2 text-sm text-gray-600">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span><?= htmlspecialchars($student['program']) ?></span>
                            <span class="mx-2">•</span>
                            <span>ID: <?= $student['id'] ?></span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        Recently
                    </div>
                </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">No recent activities</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Proposals Modal -->
    <div id="proposalsModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50" style="display:none;">
        <div class="bg-white rounded-2xl p-8 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">All Recent Proposals</h3>
                <button onclick="document.getElementById('proposalsModal').style.display='none'" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-4">
                <?php 
                // Get all recent proposals for modal
                $all_proposals = mysqli_query($conn, "SELECT title, status, id FROM proposals ORDER BY id DESC LIMIT 20");
                if (mysqli_num_rows($all_proposals) > 0): 
                    while ($proposal = mysqli_fetch_assoc($all_proposals)): 
                ?>
                <div class="p-4 rounded-xl bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500">
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($proposal['title']) ?></h4>
                        <span class="text-xs px-2 py-1 rounded-full <?= $proposal['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                            <?= ucfirst($proposal['status']) ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 mt-3">
                        <div class="flex items-center">
                            <i class="fas fa-hashtag mr-1"></i>
                            <span><?= $proposal['id'] ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-1"></i>
                            <span>Recently submitted</span>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                <div class="text-center py-8">
                    <i class="fas fa-file-alt text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">No recent proposals</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Close modal when clicking outside
        document.getElementById('activityModal').onclick = function(event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        };
        
        document.getElementById('proposalsModal').onclick = function(event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        };
    </script>

</body>
</html>