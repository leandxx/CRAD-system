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

// Date-based Defense Status Reports
$current_month = date('Y-m');
$current_year = date('Y');

$defense_by_course = mysqli_query($conn, "
    SELECT 
        g.program,
        COUNT(CASE WHEN ds.defense_result = 'redefense' THEN 1 END) as redefense_month,
        COUNT(CASE WHEN ds.defense_result = 'passed' OR ds.status = 'passed' THEN 1 END) as completed_month,
        COUNT(CASE WHEN ds.defense_type = 'pre_oral' AND ds.status = 'scheduled' THEN 1 END) as pre_oral_month,
        COUNT(CASE WHEN ds.defense_type = 'final' AND ds.status = 'scheduled' THEN 1 END) as final_month,
        COUNT(CASE WHEN ds.defense_result = 'passed' OR ds.status = 'passed' THEN 1 END) as completed_year
    FROM groups g
    LEFT JOIN defense_schedules ds ON g.id = ds.group_id
    WHERE g.program IS NOT NULL
    GROUP BY g.program
");

$in_redefense = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM defense_schedules WHERE defense_result = 'redefense'"))[0];
$completed_defense = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM defense_schedules WHERE defense_result = 'passed' OR status = 'passed'"))[0];
$in_pre_oral = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM defense_schedules WHERE defense_type = 'pre_oral' AND status = 'scheduled'"))[0];
$in_final = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM defense_schedules WHERE defense_type = 'final' AND status = 'scheduled'"))[0];

$yearly_completed = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM defense_schedules WHERE (defense_result = 'passed' OR status = 'passed') AND YEAR(defense_date) = '$current_year'"))[0];

// Generate automated report text
$total_defenses = $in_redefense + $completed_defense + $in_pre_oral + $in_final;
$completion_rate = $total_defenses > 0 ? round(($completed_defense / $total_defenses) * 100) : 0;
$month_name = date('F Y');
$report_text = "Monthly Report for {$month_name}: {$total_defenses} defense activities this month with {$completion_rate}% completion rate. ";
$report_text .= "Year-to-date: {$yearly_completed} defenses completed in {$current_year}. ";
if ($in_redefense > 0) {
    $report_text .= "{$in_redefense} groups require redefense this month.";
} else {
    $report_text .= "No redefenses required this month.";
}

// Chart data queries
// Pie Chart: Defense stages by groups
$defense_stages = mysqli_query($conn, "
    SELECT 
        CASE 
            WHEN ds.defense_type IS NULL THEN 'Proposal'
            WHEN ds.defense_type = 'pre_oral' AND ds.status = 'scheduled' THEN 'Pre-Oral'
            WHEN ds.defense_result = 'redefense' THEN 'Redefense'
            WHEN ds.defense_type = 'final' AND ds.status = 'scheduled' THEN 'Final Defense'
            WHEN ds.defense_result = 'passed' OR ds.status = 'passed' THEN 'Completed'
            ELSE 'Proposal'
        END as stage,
        COUNT(*) as count
    FROM groups g
    LEFT JOIN defense_schedules ds ON g.id = ds.group_id
    GROUP BY stage
");

// Bar Chart: Research completion by program
$research_by_program = mysqli_query($conn, "
    SELECT 
        g.program,
        COUNT(CASE WHEN p.status = 'Completed' THEN 1 END) as completed,
        COUNT(CASE WHEN p.status = 'Pending' THEN 1 END) as ongoing
    FROM groups g
    LEFT JOIN proposals p ON g.id = p.group_id
    WHERE g.program IS NOT NULL
    GROUP BY g.program
");

// Progress Tracker: Flow from Proposal to Final Defense by program
$progress_flow = mysqli_query($conn, "
    SELECT 
        g.program,
        COUNT(DISTINCT g.id) as total_groups,
        COUNT(DISTINCT CASE WHEN ds.defense_type = 'pre_oral' THEN g.id END) as pre_oral,
        COUNT(DISTINCT CASE WHEN ds.defense_type = 'final' THEN g.id END) as final_defense
    FROM groups g
    LEFT JOIN defense_schedules ds ON g.id = ds.group_id
    WHERE g.program IS NOT NULL
    GROUP BY g.program
");

// Recent activities
$recent_students = mysqli_query($conn, "SELECT full_name, program, id FROM student_profiles ORDER BY id DESC LIMIT 3");
$recent_proposals = mysqli_query($conn, "SELECT title, status, id FROM proposals ORDER BY id DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Admin!</title>
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/style-admin-dashboard.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Defense Status Pie Chart
            const defenseStatusCtx = document.getElementById('defenseStatusChart').getContext('2d');
            new Chart(defenseStatusCtx, {
                type: 'pie',
                data: {
                    labels: ['Redefense', 'Completed', 'Pre-Oral', 'Final Defense'],
                    datasets: [{
                        data: [<?= $in_redefense ?>, <?= $completed_defense ?>, <?= $in_pre_oral ?>, <?= $in_final ?>],
                        backgroundColor: ['#ef4444', '#10b981', '#3b82f6', '#8b5cf6'],
                        borderWidth: 3,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Pie Chart: Defense Stages
            const defenseCtx = document.getElementById('defenseStagesChart').getContext('2d');
            new Chart(defenseCtx, {
                type: 'pie',
                data: {
                    labels: [<?php 
                        $stages = [];
                        mysqli_data_seek($defense_stages, 0);
                        while($row = mysqli_fetch_assoc($defense_stages)) {
                            $stages[] = "'" . $row['stage'] . "'";
                        }
                        echo implode(',', $stages);
                    ?>],
                    datasets: [{
                        data: [<?php 
                            $counts = [];
                            mysqli_data_seek($defense_stages, 0);
                            while($row = mysqli_fetch_assoc($defense_stages)) {
                                $counts[] = $row['count'];
                            }
                            echo implode(',', $counts);
                        ?>],
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Bar Chart: Research by Program
            const researchCtx = document.getElementById('researchByProgramChart').getContext('2d');
            new Chart(researchCtx, {
                type: 'bar',
                data: {
                    labels: [<?php 
                        $programs = [];
                        mysqli_data_seek($research_by_program, 0);
                        while($row = mysqli_fetch_assoc($research_by_program)) {
                            $programs[] = "'" . $row['program'] . "'";
                        }
                        echo implode(',', $programs);
                    ?>],
                    datasets: [{
                        label: 'Completed',
                        data: [<?php 
                            $completed = [];
                            mysqli_data_seek($research_by_program, 0);
                            while($row = mysqli_fetch_assoc($research_by_program)) {
                                $completed[] = $row['completed'] ?? 0;
                            }
                            echo implode(',', $completed);
                        ?>],
                        backgroundColor: '#10b981'
                    }, {
                        label: 'Ongoing',
                        data: [<?php 
                            $ongoing = [];
                            mysqli_data_seek($research_by_program, 0);
                            while($row = mysqli_fetch_assoc($research_by_program)) {
                                $ongoing[] = $row['ongoing'] ?? 0;
                            }
                            echo implode(',', $ongoing);
                        ?>],
                        backgroundColor: '#f59e0b'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Progress Flow Chart
            const progressCtx = document.getElementById('progressFlowChart').getContext('2d');
            new Chart(progressCtx, {
                type: 'line',
                data: {
                    labels: [<?php 
                        $flow_programs = [];
                        mysqli_data_seek($progress_flow, 0);
                        while($row = mysqli_fetch_assoc($progress_flow)) {
                            $flow_programs[] = "'" . $row['program'] . "'";
                        }
                        echo implode(',', $flow_programs);
                    ?>],
                    datasets: [{
                        label: 'Total Groups',
                        data: [<?php 
                            $total = [];
                            mysqli_data_seek($progress_flow, 0);
                            while($row = mysqli_fetch_assoc($progress_flow)) {
                                $total[] = $row['total_groups'] ?? 0;
                            }
                            echo implode(',', $total);
                        ?>],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Pre-Oral',
                        data: [<?php 
                            $pre_oral = [];
                            mysqli_data_seek($progress_flow, 0);
                            while($row = mysqli_fetch_assoc($progress_flow)) {
                                $pre_oral[] = $row['pre_oral'] ?? 0;
                            }
                            echo implode(',', $pre_oral);
                        ?>],
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Final Defense',
                        data: [<?php 
                            $final = [];
                            mysqli_data_seek($progress_flow, 0);
                            while($row = mysqli_fetch_assoc($progress_flow)) {
                                $final[] = $row['final_defense'] ?? 0;
                            }
                            echo implode(',', $final);
                        ?>],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
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
                <p class="<?= $overdue_proposals > 0 ? 'text-red-600' : 'text-green-600' ?> text-sm font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i> <?= $overdue_proposals ?> overdue</p>
                <span class="text-xs text-gray-500">proposals</span>
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
                <p class="text-green-600 text-sm font-semibold"><i class="fas fa-check mr-1"></i> <?= $active_groups ?> active</p>
                <span class="text-xs text-gray-500">groups</span>
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

    <!-- Defense Status Report -->
    <div class="mb-10 animate-fade-in">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <div class="gradient-blue p-3 rounded-xl mr-4 shadow-lg">
                    <i class="fas fa-chart-pie text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Defense Status Report</h3>
                    <p class="text-gray-600 text-sm">Real-time defense progress overview</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-8">
            <!-- Automated Report Text -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 mb-8 border border-blue-200">
                <div class="flex items-start">
                    <i class="fas fa-robot text-blue-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Automated Analysis</h4>
                        <p class="text-gray-700 leading-relaxed"><?= isset($report_text) ? $report_text : 'Report data loading...' ?></p>
                        <p class="text-xs text-gray-500 mt-2">Generated on <?= date('M d, Y \a\t g:i A') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Overall Status</h4>
                    <div style="height: 300px; width: 300px; margin: 0 auto;">
                        <canvas id="defenseStatusChart"></canvas>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-800 mb-4">By Course - <?= date('F Y') ?></h4>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <?php while($course = mysqli_fetch_assoc($defense_by_course)): ?>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h5 class="font-semibold text-gray-800 mb-3"><?= $course['program'] ?></h5>
                            <div class="mb-3">
                                <p class="text-xs text-gray-600 mb-2">This Month:</p>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-red-600">Redefense:</span>
                                        <span class="font-bold"><?= $course['redefense_month'] ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-green-600">Completed:</span>
                                        <span class="font-bold"><?= $course['completed_month'] ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-600">Pre-Oral:</span>
                                        <span class="font-bold"><?= $course['pre_oral_month'] ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-purple-600">Final:</span>
                                        <span class="font-bold"><?= $course['final_month'] ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t pt-2">
                                <p class="text-xs text-gray-600 mb-1">Year <?= $current_year ?>:</p>
                                <div class="flex justify-between text-sm">
                                    <span class="text-green-600">Total Completed:</span>
                                    <span class="font-bold"><?= $course['completed_year'] ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <!-- Pie Chart: Defense Stages -->
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Groups by Defense Stage</h3>
            <canvas id="defenseStagesChart" width="300" height="300"></canvas>
        </div>

        <!-- Bar Chart: Research by Program -->
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Research Status by Program</h3>
            <canvas id="researchByProgramChart" width="400" height="300"></canvas>
        </div>

        <!-- Progress Tracker -->
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Research Progress Flow</h3>
            <canvas id="progressFlowChart" width="400" height="300"></canvas>
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