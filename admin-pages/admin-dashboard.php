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
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        


            <!-- Main content area -->
                <main class="flex-1 overflow-y-auto p-6">


    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Students</p>
                    <h3 class="text-2xl font-bold"><?= $total_students ?></h3>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
            <p class="text-green-500 text-sm mt-2"><i class="fas fa-arrow-up mr-1"></i> 12% from last month</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Proposals</p>
                    <h3 class="text-2xl font-bold"><?= $total_proposals ?></h3>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-file-alt text-lg"></i>
                </div>
            </div>
            <p class="text-green-500 text-sm mt-2"><i class="fas fa-arrow-up mr-1"></i> 5 new today</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Reviews</p>
                    <h3 class="text-2xl font-bold"><?= $pending_proposals ?></h3>
                </div>
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-lg"></i>
                </div>
            </div>
            <p class="text-red-500 text-sm mt-2"><i class="fas fa-arrow-down mr-1"></i> 2 overdue</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Groups</p>
                    <h3 class="text-2xl font-bold"><?= $total_groups ?></h3>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-calendar-check text-lg"></i>
                </div>
            </div>
            <p class="text-blue-500 text-sm mt-2"><?= $total_clusters ?> clusters</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="admin-pages/admin-timeline.php" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-blue-300 transition-colors text-center">
                <div class="text-blue-500 mb-2">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
                <p class="font-medium">Research Timeline</p>
            </a>
            <a href="admin-pages/admin-defense.php" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-purple-300 transition-colors text-center">
                <div class="text-purple-500 mb-2">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                </div>
                <p class="font-medium">Defense Scheduling</p>
            </a>
            <a href="admin-pages/panel-assignment.php" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-green-300 transition-colors text-center">
                <div class="text-green-500 mb-2">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <p class="font-medium">Panel Assignment</p>
            </a>
            <a href="admin-pages/adviser-assignment.php" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:border-red-300 transition-colors text-center">
                <div class="text-red-500 mb-2">
                    <i class="fas fa-user-tie text-2xl"></i>
                </div>
                <p class="font-medium">Adviser Assignment</p>
            </a>
        </div>
    </div>

    <!-- Recent Activity and Upcoming Defenses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Activity -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Recent Activity</h3>
                <a href="#" class="text-sm text-blue-500 hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($recent_students) > 0): ?>
                    <?php while ($student = mysqli_fetch_assoc($recent_students)): ?>
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-full mr-3">
                            <i class="fas fa-user-plus text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium">New student registered</p>
                            <p class="text-gray-500 text-sm"><?= htmlspecialchars($student['full_name']) ?> - <?= htmlspecialchars($student['program']) ?></p>
                            <p class="text-gray-400 text-xs">Recently added</p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-sm">No recent student registrations</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Proposals -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Recent Proposals</h3>
                <a href="#" class="text-sm text-blue-500 hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($recent_proposals) > 0): ?>
                    <?php while ($proposal = mysqli_fetch_assoc($recent_proposals)): ?>
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <p class="font-medium"><?= htmlspecialchars($proposal['title']) ?></p>
                        <p class="text-gray-500 text-sm">Status: <?= ucfirst($proposal['status']) ?></p>
                        <div class="flex items-center text-sm text-gray-500 mt-1">
                            <i class="fas fa-file mr-1"></i>
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