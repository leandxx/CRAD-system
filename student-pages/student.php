<?php
session_start();
include('../includes/connection.php'); // Your DB connection
include('../includes/notification-helper.php');
date_default_timezone_set('Asia/Manila'); // Set your timezone

// Initialize variables
$now = new DateTime();
$active_timeline = null;
$milestones = [];
$total_milestones = 0;
$completed_milestones = 0;
$progress = 0;
$current_milestone = null;
$isoDeadline = null;

// Get active timeline and milestones
$stmt = $conn->prepare("SELECT t.* FROM submission_timelines t WHERE t.is_active = 1 LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $active_timeline = $result->fetch_assoc();
    
    // Get milestones for active timeline
    $stmt = $conn->prepare("SELECT * FROM timeline_milestones WHERE timeline_id = ? ORDER BY deadline ASC");
    $stmt->bind_param("i", $active_timeline['id']);
    $stmt->execute();
    $milestones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Calculate progress only if we have milestones
    $total_milestones = count($milestones);
    if ($total_milestones > 0) {
        foreach ($milestones as $milestone) {
            $deadline = new DateTime($milestone['deadline']);
            if ($deadline < $now) {
                $completed_milestones++;
            }
            
            // Find current milestone
            if ($deadline > $now && !$current_milestone) {
                $current_milestone = $milestone;
                $isoDeadline = date('c', strtotime($current_milestone['deadline']));
            }
        }
        $progress = ($completed_milestones / $total_milestones) * 100;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRAD student-portal</title>
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/style-student.css">
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
    </script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 font-sans">
    <div class="min-h-screen flex">
          <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>

          <main class="flex-1 overflow-y-auto p-4 lg:p-8 hide-scrollbar">
                <!-- Welcome Section -->
                <div class="mb-8 animate-fade-in">
                    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white shadow-2xl">
                        <div class="flex flex-col lg:flex-row items-center justify-between">
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-bold mb-2">Welcome to CRAD Student Portal</h1>
                                <p class="text-blue-100 text-lg">Track your research progress and manage submissions</p>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold"><?= date('M d, Y') ?></div>
                                        <div class="text-sm text-blue-100"><?= date('l') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Countdown Banner -->
                <div class="countdown-banner text-white p-8 rounded-2xl mb-8 shadow-2xl" id="countdown-banner">
                    <div class="flex flex-col lg:flex-row items-center justify-between">
                        <div class="flex items-center mb-6 lg:mb-0">
                            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 mr-6">
                                <i class="fas fa-stopwatch text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2">Current Phase Countdown</h3>
                                <p class="text-lg opacity-90">
                                    <?php if ($current_milestone): ?>
                                        <span class="font-semibold"><?= htmlspecialchars($current_milestone['title']) ?></span>
                                        <br><span class="text-sm">Deadline: <?= date('F j, Y \a\t g:i A', strtotime($current_milestone['deadline'])) ?></span>
                                    <?php else: ?>
                                        <?= empty($milestones) ? 'No active milestones' : 'All milestones completed' ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
                                <div id="countdown-days" class="text-4xl font-bold mb-1">00</div>
                                <div class="text-sm opacity-90 font-medium">Days</div>
                            </div>
                            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
                                <div id="countdown-hours" class="text-4xl font-bold mb-1">00</div>
                                <div class="text-sm opacity-90 font-medium">Hours</div>
                            </div>
                            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
                                <div id="countdown-minutes" class="text-4xl font-bold mb-1">00</div>
                                <div class="text-sm opacity-90 font-medium">Minutes</div>
                            </div>
                            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
                                <div id="countdown-seconds" class="text-4xl font-bold mb-1">00</div>
                                <div class="text-sm opacity-90 font-medium">Seconds</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Research Submission Overview -->
                <section class="mb-10 crad-slide-up">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 rounded-xl mr-4 shadow-lg">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Research Timeline</h2>
                                <p class="text-gray-600 text-sm">Track your submission progress and milestones</p>
                            </div>
                        </div>
                        <div class="hidden lg:flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600">Active Timeline</span>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-8">
                        <?php if ($active_timeline): ?>
                            <div class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($active_timeline['title']) ?></h3>
                                <p class="text-gray-600 text-lg leading-relaxed"><?= htmlspecialchars($active_timeline['description']) ?></p>
                            </div>
                            
                            <div class="mb-8">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm text-gray-600 mb-4">
                                    <div class="flex items-center mb-2 sm:mb-0">
                                        <i class="fas fa-play-circle mr-2 text-green-500"></i>
                                        <span class="font-medium">
                                            <?php if (!empty($milestones)): ?>
                                                Started: <?= date('M Y', strtotime($milestones[0]['deadline'])) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center mb-2 sm:mb-0">
                                        <i class="fas fa-chart-pie mr-2 text-blue-500"></i>
                                        <span class="font-bold text-lg text-blue-600"><?= round($progress) ?>% Complete</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-flag-checkered mr-2 text-purple-500"></i>
                                        <span class="font-medium">
                                            <?php if (!empty($milestones)): ?>
                                                Target: <?= date('M Y', strtotime(end($milestones)['deadline'])) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?= min(5, count($milestones)) ?> gap-6">
                                <?php foreach ($milestones as $index => $milestone): ?>
                                    <?php
                                    $deadline = new DateTime($milestone['deadline']);
                                    $is_past = $deadline < $now;
                                    $is_current = !$is_past && ($index === 0 || new DateTime($milestones[$index-1]['deadline']) < $now);
                                    ?>
                                    <div class="research-phase <?= $is_past || $is_current ? 'active' : '' ?> p-6 rounded-xl text-center transition-all hover:shadow-lg relative group">
                                        <div class="phase-tooltip"><?= htmlspecialchars($milestone['description']) ?></div>
                                        <div class="mb-4">
                                            <i class="fas 
                                                <?= $is_past ? 'fa-check-circle text-green-500' : ($is_current ? 'fa-clock text-yellow-500' : 'fa-flag text-gray-400') ?> 
                                                text-4xl group-hover:scale-110 transition-transform"
                                            ></i>
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-800 mb-2"><?= htmlspecialchars($milestone['title']) ?></h4>
                                        <div class="text-xs">
                                            <?php if ($is_past): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>Completed
                                                </span>
                                            <?php elseif ($is_current): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php
                                                    $diff = $now->diff($deadline);
                                                    echo $diff->days . ' days left';
                                                    ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    <?= date('M j', strtotime($milestone['deadline'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-500 mb-2">No Active Timeline</h3>
                                <p class="text-gray-400 text-lg">Check back later for submission deadlines and milestones</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Quick Access Cards -->
                <section class="mb-10 crad-slide-up">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center">
                            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-3 rounded-xl mr-4 shadow-lg">
                                <i class="fas fa-bolt text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Quick Actions</h2>
                                <p class="text-gray-600 text-sm">Access your most important tools and features</p>
                            </div>
                        </div>
                        <div class="hidden lg:flex items-center space-x-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600">Ready to Use</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <a href="student-pages/proposal.php" class="quick-access-card crad-hover-lift transition-all duration-300 group rounded-2xl p-8 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-500/10 to-transparent rounded-full -mr-10 -mt-10"></div>
                            <div class="flex items-start space-x-6">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-2xl text-white group-hover:scale-110 transition-transform shadow-lg">
                                    <i class="fas fa-file-upload text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 mb-2">Proposal Submission</h3>
                                    <p class="text-gray-600 leading-relaxed mb-4">Submit and manage your research proposal documents</p>
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Action Required
                                        </span>
                                        <i class="fas fa-arrow-right text-blue-500 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                        
                        <a href="student-pages/defense.php" class="quick-access-card crad-hover-lift transition-all duration-300 group rounded-2xl p-8 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-500/10 to-transparent rounded-full -mr-10 -mt-10"></div>
                            <div class="flex items-start space-x-6">
                                <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 rounded-2xl text-white group-hover:scale-110 transition-transform shadow-lg">
                                    <i class="fas fa-calendar-check text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-600 mb-2">Defense Scheduling</h3>
                                    <p class="text-gray-600 leading-relaxed mb-4">View and manage your thesis defense schedule</p>
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <i class="fas fa-bell mr-1"></i>New Update
                                        </span>
                                        <i class="fas fa-arrow-right text-green-500 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                        
                        <a href="student-pages/student-profile.php" class="quick-access-card crad-hover-lift transition-all duration-300 group rounded-2xl p-8 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-500/10 to-transparent rounded-full -mr-10 -mt-10"></div>
                            <div class="flex items-start space-x-6">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-4 rounded-2xl text-white group-hover:scale-110 transition-transform shadow-lg">
                                    <i class="fas fa-user-circle text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-purple-600 mb-2">Student Profile</h3>
                                    <p class="text-gray-600 leading-relaxed mb-4">View and manage your personal information</p>
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                            <i class="fas fa-user mr-1"></i>Profile Settings
                                        </span>
                                        <i class="fas fa-arrow-right text-purple-500 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Progress Summary Footer -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6 animate-fade-in">
                    <div class="flex flex-col lg:flex-row items-center justify-between">
                        <div class="flex items-center mb-4 lg:mb-0">
                            <div class="bg-blue-500 p-3 rounded-xl mr-4">
                                <i class="fas fa-chart-pie text-white text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Research Progress</h4>
                                <p class="text-gray-600 text-sm">Your current research journey status</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-8">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600"><?= round($progress) ?>%</div>
                                <div class="text-xs text-gray-600">Overall Progress</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600"><?= $completed_milestones ?></div>
                                <div class="text-xs text-gray-600">Completed Phases</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600"><?= $total_milestones - $completed_milestones ?></div>
                                <div class="text-xs text-gray-600">Remaining</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Countdown timer
        function updateCountdown() {
            <?php if ($current_milestone): ?>
                const deadline = new Date("<?= $isoDeadline ?>").getTime();
                const now = new Date().getTime();
                const distance = deadline - now;

                // Time calculations
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update display
                document.getElementById("countdown-days").textContent = String(days).padStart(2, '0');
                document.getElementById("countdown-hours").textContent = String(hours).padStart(2, '0');
                document.getElementById("countdown-minutes").textContent = String(minutes).padStart(2, '0');
                document.getElementById("countdown-seconds").textContent = String(seconds).padStart(2, '0');

                // Change banner color based on urgency
                const banner = document.getElementById('countdown-banner');
                if (distance < (24 * 60 * 60 * 1000)) {
                    // Less than 24 hours - warning
                    banner.classList.remove('from-primary', 'to-secondary', 'from-yellow-500', 'to-yellow-600');
                    banner.classList.add('from-warning', 'to-orange-500');
                } else if (distance < (3 * 24 * 60 * 60 * 1000)) {
                    // Less than 3 days - yellow
                    banner.classList.remove('from-primary', 'to-secondary', 'from-warning', 'to-orange-500');
                    banner.classList.add('from-yellow-500', 'to-yellow-600');
                } else {
                    // Normal state
                    banner.classList.remove('from-warning', 'to-orange-500', 'from-yellow-500', 'to-yellow-600');
                    banner.classList.add('from-primary', 'to-secondary');
                }

                // If countdown finished, reload page
                if (distance < 0) {
        // Add a small delay before reload to ensure the milestone is marked as completed
        setTimeout(() => {
            location.reload();
        }, 1000);
        return;
      }
            <?php else: ?>
                // No current milestone
                document.getElementById("countdown-days").textContent = '00';
                document.getElementById("countdown-hours").textContent = '00';
                document.getElementById("countdown-minutes").textContent = '00';
                document.getElementById("countdown-seconds").textContent = '00';
                document.getElementById('countdown-banner').classList.add('from-gray-500', 'to-gray-600');
            <?php endif; ?>
        }

        // Initialize countdown
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdown();
            setInterval(updateCountdown, 1000);

            // Progress bar animation
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                progressFill.style.width = '0';
                setTimeout(() => {
                    progressFill.style.width = '<?= $progress ?>%';
                }, 300);
            }

            // Hover effects for research phases
            const phases = document.querySelectorAll('.research-phase');
            phases.forEach(phase => {
                phase.addEventListener('mouseenter', () => {
                    phase.style.transform = 'scale(1.05)';
                    phase.style.boxShadow = '0 6px 24px 0 #2563eb22';
                });
                phase.addEventListener('mouseleave', () => {
                    phase.style.transform = 'scale(1)';
                    phase.style.boxShadow = '';
                });
            });
        });

        // Toggle sidebar if needed
        document.getElementById("toggleSidebar")?.addEventListener("click", () => {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("hidden");
        });
    </script>
</body>
</html>