<?php
session_start();
include('../includes/connection.php'); // Your DB connection
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
    <title>Student Dashboard - CRAD System</title>
    <?php 
        $base_url = '../';
        include('../includes/crad-head.php'); 
    ?>
    <style>
        .progress-bar {
            width: 100%;
            height: 16px;
            background: #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 4px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2563eb 60%, #7c3aed 100%);
            width: 0;
            border-radius: 8px;
            transition: width 1.5s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 0 2px 8px 0 #2563eb22;
        }
        .research-phase {
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), box-shadow 0.3s;
        }
        .research-phase.active {
            background: linear-gradient(90deg, #dbeafe 60%, #ede9fe 100%);
            box-shadow: 0 4px 16px 0 #2563eb11;
            border: 2px solid #2563eb;
        }
        .phase-tooltip {
            opacity: 0;
            pointer-events: none;
            position: absolute;
            left: 50%;
            top: -2.5rem;
            transform: translateX(-50%);
            background: #fff;
            color: #2563eb;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            box-shadow: 0 2px 8px 0 #2563eb22;
            transition: opacity 0.3s;
            z-index: 10;
        }
        .research-phase:hover .phase-tooltip {
            opacity: 1;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="crad-page">
    <div class="min-h-screen flex">
          <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>



            <main class="flex-1 overflow-y-auto p-6 hide-scrollbar">
                <!-- Countdown Header -->
                <div class="bg-gradient-to-r from-primary to-secondary text-white p-4 rounded-lg shadow-lg mb-6" id="countdown-banner">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="flex items-center mb-4 md:mb-0">
                            <i class="fas fa-clock text-2xl mr-3"></i>
                            <div>
                                <h3 class="font-bold text-lg">Current Phase Countdown</h3>
                                <p class="text-sm opacity-90">
                                    <?php if ($current_milestone): ?>
                                        <?= htmlspecialchars($current_milestone['title']) ?> - 
                                        Ends <?= date('F j, Y \a\t g:i A', strtotime($current_milestone['deadline'])) ?>
                                    <?php else: ?>
                                        <?= empty($milestones) ? 'No active milestones' : 'All milestones completed' ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="text-center px-4">
                                <div id="countdown-days" class="text-3xl font-bold">00</div>
                                <div class="text-xs opacity-90">Days</div>
                            </div>
                            <div class="text-2xl font-bold opacity-70">:</div>
                            <div class="text-center px-4">
                                <div id="countdown-hours" class="text-3xl font-bold">00</div>
                                <div class="text-xs opacity-90">Hours</div>
                            </div>
                            <div class="text-2xl font-bold opacity-70">:</div>
                            <div class="text-center px-4">
                                <div id="countdown-minutes" class="text-3xl font-bold">00</div>
                                <div class="text-xs opacity-90">Minutes</div>
                            </div>
                            <div class="text-2xl font-bold opacity-70">:</div>
                            <div class="text-center px-4">
                                <div id="countdown-seconds" class="text-3xl font-bold">00</div>
                                <div class="text-xs opacity-90">Seconds</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Research Submission Overview -->
                <section class="mb-8 crad-slide-up">
                    <h2 class="crad-heading-2 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-primary"></i> Submission Timeline
                    </h2>
                    <div class="crad-card">
                        <?php if ($active_timeline): ?>
                            <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($active_timeline['title']) ?></h3>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($active_timeline['description']) ?></p>
                            
                            <div class="mb-6">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>
                                        <?php if (!empty($milestones)): ?>
                                            Started: <?= date('M Y', strtotime($milestones[0]['deadline'])) ?>
                                        <?php endif; ?>
                                    </span>
                                    <span><?= round($progress) ?>% Complete</span>
                                    <span>
                                        <?php if (!empty($milestones)): ?>
                                            Target: <?= date('M Y', strtotime(end($milestones)['deadline'])) ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-<?= min(5, count($milestones)) ?> gap-4">
                                <?php foreach ($milestones as $index => $milestone): ?>
                                    <?php
                                    $deadline = new DateTime($milestone['deadline']);
                                    $is_past = $deadline < $now;
                                    $is_current = !$is_past && ($index === 0 || new DateTime($milestones[$index-1]['deadline']) < $now);
                                    ?>
                                    <div class="research-phase <?= $is_past || $is_current ? 'active' : '' ?> p-4 rounded-lg text-center transition-all hover:shadow-md">
                                        <div class="phase-tooltip"><?= htmlspecialchars($milestone['description']) ?></div>
                                        <i class="fas 
                                            <?= $is_past ? 'fa-check-circle text-success' : ($is_current ? 'fa-exclamation-circle text-warning' : 'fa-flag text-gray-300') ?> 
                                            text-3xl mb-3"
                                        ></i>
                                        <p class="text-sm font-medium"><?= htmlspecialchars($milestone['title']) ?></p>
                                        <p class="text-xs mt-1">
                                            <?php if ($is_past): ?>
                                                <i class="fas fa-check-circle mr-1"></i>Completed
                                            <?php elseif ($is_current): ?>
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php
                                                $diff = $now->diff($deadline);
                                                echo $diff->days . ' days left';
                                                ?>
                                            <?php else: ?>
                                                <i class="fas fa-calendar-day mr-1"></i>
                                                <?= date('M j', strtotime($milestone['deadline'])) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-500">No active timeline</h3>
                                <p class="text-gray-400">Check back later for submission deadlines</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Quick Access Cards -->
                <section class="mb-8 crad-slide-up">
                    <h2 class="crad-heading-2 mb-6 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-500"></i> Quick Access
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <a href="student_pages/proposal.php" class="crad-card crad-hover-lift transition-all duration-300 border-l-4 border-blue-500 hover:border-blue-600 group">
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
                        
                        <a href="student_pages/defense.php" class="crad-card crad-hover-lift transition-all duration-300 border-l-4 border-green-500 hover:border-green-600 group">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-full bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                    <i class="fas fa-calendar-check text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800 group-hover:text-green-600">Defense Scheduling</h3>
                                    <p class="text-sm text-gray-500 group-hover:text-gray-600">See your defense date</p>
                                </div>
                            </div>
                            <div class="mt-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    New Update
                                </span>
                            </div>
                        </a>
                        
                        <a href="student_pages/documents.php" class="crad-card crad-hover-lift transition-all duration-300 border-l-4 border-purple-500 hover:border-purple-600 group">
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