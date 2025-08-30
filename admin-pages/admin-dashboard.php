<?php
session_start();
include('../includes/connection.php'); // Your DB connection

// Fetch dashboard statistics
$total_students = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles"))[0];
$total_groups = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM groups"))[0];
$total_proposals = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM proposals"))[0];
$pending_proposals = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM proposals WHERE status = 'pending'"))[0];
$total_faculty = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM faculty"))[0];
$total_clusters = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters"))[0];

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
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        @keyframes slideInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-slide-up { animation: slideInUp 0.6s ease-out; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out; }
        .animate-scale-in { animation: scaleIn 0.5s ease-out; }
        
        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px -8px rgba(0, 0, 0, 0.1);
        }
        .gradient-blue {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
        .gradient-green {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .gradient-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        .gradient-yellow {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .gradient-red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        .action-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .action-card:hover::before {
            left: 100%;
        }
        .action-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.15);
        }
        .activity-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .activity-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .activity-card:hover::before {
            left: 100%;
        }
        .activity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -8px rgba(0, 0, 0, 0.1);
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
                <main class="flex-1 overflow-y-auto p-6">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 animate-slide-up">
        <div class="stats-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Total Students</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $total_students ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                        <div class="gradient-blue h-1.5 rounded-full" style="width: 85%"></div>
                    </div>
                </div>
                <div class="gradient-blue p-4 rounded-2xl shadow-lg">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
            </div>
            <p class="text-green-600 text-sm mt-3 font-semibold"><i class="fas fa-arrow-up mr-1"></i> 12% from last month</p>
        </div>

        <div class="stats-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Total Proposals</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $total_proposals ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                        <div class="gradient-purple h-1.5 rounded-full" style="width: 70%"></div>
                    </div>
                </div>
                <div class="gradient-purple p-4 rounded-2xl shadow-lg">
                    <i class="fas fa-file-alt text-white text-2xl"></i>
                </div>
            </div>
            <p class="text-green-600 text-sm mt-3 font-semibold"><i class="fas fa-arrow-up mr-1"></i> 5 new today</p>
        </div>

        <div class="stats-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Pending Reviews</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $pending_proposals ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                        <div class="gradient-yellow h-1.5 rounded-full" style="width: 45%"></div>
                    </div>
                </div>
                <div class="gradient-yellow p-4 rounded-2xl shadow-lg">
                    <i class="fas fa-clock text-white text-2xl"></i>
                </div>
            </div>
            <p class="text-red-600 text-sm mt-3 font-semibold"><i class="fas fa-arrow-down mr-1"></i> 2 overdue</p>
        </div>

        <div class="stats-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Total Groups</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $total_groups ?></h3>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                        <div class="gradient-green h-1.5 rounded-full" style="width: 90%"></div>
                    </div>
                </div>
                <div class="gradient-green p-4 rounded-2xl shadow-lg">
                    <i class="fas fa-calendar-check text-white text-2xl"></i>
                </div>
            </div>
            <p class="text-blue-600 text-sm mt-3 font-semibold"><?= $total_clusters ?> clusters</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-10 animate-fade-in">
        <div class="flex items-center mb-6">
            <div class="gradient-blue p-3 rounded-xl mr-4">
                <i class="fas fa-bolt text-white text-xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800">Quick Actions</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <a href="admin-pages/admin-timeline.php" class="action-card stats-card p-6 text-center group">
                <div class="gradient-blue p-4 rounded-2xl mb-4 mx-auto w-fit group-hover:scale-110 transition-transform">
                    <i class="fas fa-file-alt text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-800">Research Timeline</p>
                <p class="text-sm text-gray-600 mt-1">Manage submission timelines</p>
            </a>
            <a href="admin-pages/admin-defense.php" class="action-card stats-card p-6 text-center group">
                <div class="gradient-purple p-4 rounded-2xl mb-4 mx-auto w-fit group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-alt text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-800">Defense Scheduling</p>
                <p class="text-sm text-gray-600 mt-1">Schedule defense sessions</p>
            </a>
            <a href="admin-pages/panel-assignment.php" class="action-card stats-card p-6 text-center group">
                <div class="gradient-green p-4 rounded-2xl mb-4 mx-auto w-fit group-hover:scale-110 transition-transform">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-800">Panel Assignment</p>
                <p class="text-sm text-gray-600 mt-1">Manage panel members</p>
            </a>
            <a href="admin-pages/adviser-assignment.php" class="action-card stats-card p-6 text-center group">
                <div class="gradient-red p-4 rounded-2xl mb-4 mx-auto w-fit group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-tie text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-800">Adviser Assignment</p>
                <p class="text-sm text-gray-600 mt-1">Assign thesis advisers</p>
            </a>
        </div>
    </div>

    <!-- Recent Activity and Upcoming Defenses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10 animate-scale-in">
        <!-- Recent Activity -->
        <div class="activity-card stats-card p-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <div class="gradient-blue p-3 rounded-xl mr-4">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Recent Activity</h3>
                </div>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($recent_students) > 0): ?>
                    <?php while ($student = mysqli_fetch_assoc($recent_students)): ?>
                    <div class="flex items-start p-3 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100">
                        <div class="gradient-blue p-3 rounded-xl mr-4 shadow-lg">
                            <i class="fas fa-user-plus text-white text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">New student registered</p>
                            <p class="text-gray-600 text-sm font-medium"><?= htmlspecialchars($student['full_name']) ?> - <?= htmlspecialchars($student['program']) ?></p>
                            <p class="text-gray-500 text-xs mt-1">Recently added</p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-sm">No recent student registrations</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Proposals -->
        <div class="activity-card stats-card p-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <div class="gradient-purple p-3 rounded-xl mr-4">
                        <i class="fas fa-file-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Recent Proposals</h3>
                </div>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-semibold">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($recent_proposals) > 0): ?>
                    <?php while ($proposal = mysqli_fetch_assoc($recent_proposals)): ?>
                    <div class="p-4 rounded-xl bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500">
                        <p class="font-bold text-gray-800"><?= htmlspecialchars($proposal['title']) ?></p>
                        <p class="text-gray-600 text-sm font-medium mt-1">Status: <?= ucfirst($proposal['status']) ?></p>
                        <div class="flex items-center text-sm text-gray-500 mt-2">
                            <i class="fas fa-file mr-2"></i>
                            Proposal ID: <?= $proposal['id'] ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-sm">No recent proposals</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    

            </main>
        </div>
    </div>

</body>
</html>