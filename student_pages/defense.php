<?php
session_start();
include('../includes/connection.php');
include('../includes/notification-helper.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header("Location: ../student_pages/student.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's group information
$group_query = "SELECT g.*, gm.student_id 
                FROM groups g 
                JOIN group_members gm ON g.id = gm.group_id 
                WHERE gm.student_id = '$user_id'";
$group_result = mysqli_query($conn, $group_query);
$has_group = mysqli_num_rows($group_result) > 0;

// Get defense schedule if available
$defense_schedule = null;
$panel_members = [];
$requirements_status = [
    'proposal_submitted' => 0,
    'payment_completed' => 0,
    'documents_submitted' => 0,
    'total_required_docs' => 0
];

if ($has_group) {
    $group = mysqli_fetch_assoc($group_result);
    $group_id = $group['id'];
    
    // Check defense schedule - get latest active defense for the group (including redefense variants)
    $defense_query = "SELECT ds.*, r.room_name, r.building
                     FROM defense_schedules ds 
                     LEFT JOIN rooms r ON ds.room_id = r.id 
                     WHERE ds.group_id = '$group_id' AND ds.status IN ('scheduled', 're_defense')
                     ORDER BY ds.defense_date DESC, ds.id DESC
                     LIMIT 1";
    $defense_result = mysqli_query($conn, $defense_query);
    
    if (mysqli_num_rows($defense_result) > 0) {
        // Get the most recent defense (first result since we ordered by date DESC)
        $temp_schedule = mysqli_fetch_assoc($defense_result);
        
        // Get payment status first
        $research_forum_query = "SELECT COUNT(*) as count FROM payments WHERE student_id = '$user_id' AND payment_type = 'research_forum' AND status = 'approved'";
        $research_forum_result = mysqli_query($conn, $research_forum_query);
        $has_research_forum_payment = mysqli_fetch_assoc($research_forum_result)['count'] > 0;
        
        $pre_oral_query = "SELECT COUNT(*) as count FROM payments WHERE student_id = '$user_id' AND payment_type = 'pre_oral_defense' AND status = 'approved'";
        $pre_oral_result = mysqli_query($conn, $pre_oral_query);
        $has_pre_oral_payment = mysqli_fetch_assoc($pre_oral_result)['count'] > 0;
        
        $final_defense_query = "SELECT COUNT(*) as count FROM payments WHERE student_id = '$user_id' AND payment_type = 'final_defense' AND status = 'approved'";
        $final_defense_result = mysqli_query($conn, $final_defense_query);
        $has_final_defense_payment = mysqli_fetch_assoc($final_defense_result)['count'] > 0;
        
        // Only show schedule if student has required payments
        $show_schedule = false;
        // Check defense type from database  
        $defense_type = isset($temp_schedule['defense_type']) ? $temp_schedule['defense_type'] : 'pre_oral';
        $is_pre_oral = ($defense_type === 'pre_oral' || $defense_type === 'pre_oral_redefense');
        $is_redefense = ($defense_type === 'pre_oral_redefense' || $defense_type === 'final_redefense');
        
        if ($is_redefense) {
            // Redefense schedule becomes visible once approved/scheduled by admin
            $show_schedule = true;
        } elseif ($is_pre_oral) {
            // Pre-oral defense - need research forum and pre-oral payments
            $show_schedule = $has_research_forum_payment && $has_pre_oral_payment;
        } else {
            // Final defense - need all payments
            $show_schedule = $has_research_forum_payment && $has_pre_oral_payment && $has_final_defense_payment;
        }
        
        if ($show_schedule) {
            $defense_schedule = $temp_schedule;
            
            // Fetch panel members for this specific defense only
            $panel_query = "SELECT pm.first_name, pm.last_name, pm.role
                           FROM defense_panel dp
                           JOIN panel_members pm ON dp.faculty_id = pm.id
                           WHERE dp.defense_id = '{$defense_schedule['id']}'";
            $panel_result = mysqli_query($conn, $panel_query);
            
            while ($row = mysqli_fetch_assoc($panel_result)) {
                if (!empty($row['first_name']) && !empty($row['last_name'])) {
                    $panel_members[] = [
                        'name' => $row['first_name'] . ' ' . $row['last_name'],
                        'role' => $row['role'],
                        'initials' => substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)
                    ];
                }
            }
            
            // Debug: Check what defense we're showing
            // echo '<pre>Current Defense: '; print_r($defense_schedule); echo '</pre>';
            
            // Debug: Check what roles are actually in the data
            // echo '<pre>Panel Members Debug: '; print_r($panel_members); echo '</pre>';
            // echo '<pre>Defense Schedule Debug: '; print_r($defense_schedule); echo '</pre>';
        }
    }
    
    // Check requirements status
    // Determine if pre-oral defense has been completed (used by notices and timeline)
    $preoral_completed_query = "SELECT 1 FROM defense_schedules WHERE group_id = '$group_id' AND defense_type = 'pre_oral' AND status = 'completed' LIMIT 1";
    $preoral_completed_result = mysqli_query($conn, $preoral_completed_query);
    $has_completed_preoral = $preoral_completed_result && mysqli_num_rows($preoral_completed_result) > 0;

    // 1. Check if proposal is submitted (not checking status since column doesn't exist)
    $proposal_query = "SELECT COUNT(*) as count FROM proposals WHERE group_id = '$group_id'";
    $proposal_result = mysqli_query($conn, $proposal_query);
    if ($proposal_result) {
        $proposal_data = mysqli_fetch_assoc($proposal_result);
        $requirements_status['proposal_submitted'] = $proposal_data['count'];
    }
    
    // 2. Payment status already checked above if defense schedule exists
    if (!isset($has_research_forum_payment)) {
        $research_forum_query = "SELECT COUNT(*) as count FROM payments WHERE student_id = '$user_id' AND payment_type = 'research_forum' AND status = 'approved'";
        $research_forum_result = mysqli_query($conn, $research_forum_query);
        $has_research_forum_payment = mysqli_fetch_assoc($research_forum_result)['count'] > 0;
        
        $pre_oral_query = "SELECT COUNT(*) as count FROM payments WHERE student_id = '$user_id' AND payment_type = 'pre_oral_defense' AND status = 'approved'";
        $pre_oral_result = mysqli_query($conn, $pre_oral_query);
        $has_pre_oral_payment = mysqli_fetch_assoc($pre_oral_result)['count'] > 0;
        
        $final_defense_query = "SELECT COUNT(*) as count FROM payments WHERE student_id = '$user_id' AND payment_type = 'final_defense' AND status = 'approved'";
        $final_defense_result = mysqli_query($conn, $final_defense_query);
        $has_final_defense_payment = mysqli_fetch_assoc($final_defense_result)['count'] > 0;
    }
    
    // For backward compatibility, set payment_completed if any payment is approved
    $requirements_status['payment_completed'] = ($has_research_forum_payment || $has_pre_oral_payment || $has_final_defense_payment) ? 1 : 0;
    
    // 3. Check required documents (if the documents table exists)
    $docs_query = "SELECT COUNT(*) as count FROM required_documents WHERE required_for_defense = 1";
    $docs_result = mysqli_query($conn, $docs_query);
    if ($docs_result) {
        $docs_data = mysqli_fetch_assoc($docs_result);
        $requirements_status['total_required_docs'] = $docs_data['count'];
    }
}

// Check if all requirements are met
$all_requirements_met = false;
if ($has_group) {
    $all_requirements_met = (
        $requirements_status['proposal_submitted'] > 0 &&
        $requirements_status['payment_completed'] > 0 &&
        $requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defense Schedule</title>
    <link rel="icon" type="image/png" href="assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            min-height: 100vh;
        }
        
        .countdown-timer {
            font-family: 'Courier New', monospace;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .floating-panel {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.9) 0%, rgba(124, 58, 237, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            animation: slideInDown 0.8s ease-out;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease-out;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .requirement-status {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .requirement-status:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .timeline-item:not(:last-child):after {
            content: '';
            position: absolute;
            left: -1.6rem;
            bottom: -1.5rem;
            height: 2rem;
            width: 2px;
            background: linear-gradient(to bottom, #e5e7eb, transparent);
        }
        
        .resource-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .resource-card:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .glow {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.2);
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .stats-card:hover::before {
            left: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
        .animate-delay-4 { animation-delay: 0.4s; }
        
        .enhanced-timeline {
            position: relative;
        }
        
        .enhanced-timeline::before {
            content: '';
            position: absolute;
            left: -2px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #3b82f6, #8b5cf6, #06b6d4);
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#7c3aed',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        dark: {
                            100: '#1f2937',
                            200: '#111827'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>
        
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <!-- Status Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-r-lg mb-6 flex items-center shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span class="block sm:inline"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-lg mb-6 flex items-center shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span class="block sm:inline"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Countdown Banner (only show if defense is scheduled and not completed) -->
            <?php if ($defense_schedule && $defense_schedule['status'] == 'scheduled'): ?>
            <div class="floating-panel text-white rounded-2xl p-6 mb-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-black opacity-10"></div>
                <div class="absolute -right-6 -bottom-6 w-40 h-40 rounded-full bg-white opacity-10"></div>
                <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-white opacity-5"></div>
                
                <div class="flex flex-col md:flex-row items-center justify-between relative z-10">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-2xl font-bold mb-2">Your Defense Countdown</h2>
                        <p class="text-blue-100 opacity-90"><?php echo $is_pre_oral ? 'Pre-oral' : 'Final'; ?> defense presentation on <?php echo date('F j, Y', strtotime($defense_schedule['defense_date'])); ?></p>
                    </div>
                    <div class="countdown-timer text-4xl font-bold bg-white bg-opacity-10 px-6 py-4 rounded-xl">
                        <span id="days" class="text-white">00</span>d 
                        <span class="opacity-70">:</span>
                        <span id="hours" class="text-white">00</span>h 
                        <span class="opacity-70">:</span>
                        <span id="minutes" class="text-white">00</span>m 
                        <span class="opacity-70">:</span>
                        <span id="seconds" class="text-white">00</span>s
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Requirements Status Card -->
            <div class="glass-card p-6 rounded-2xl mb-8 glow animate-delay-1">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 pb-3 border-b border-gray-100 flex items-center">
                    <i class="fas fa-clipboard-list text-primary mr-3"></i>
                    Defense Requirements Status
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- Proposal Submission -->
                    <div class="requirement-status stats-card p-5 rounded-xl <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'bg-gradient-to-br from-green-50 to-green-100 border-green-200 glow' : 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-200'; ?> border">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center mr-3 shadow-sm">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="font-medium text-gray-700">Proposal Submitted</h3>
                        </div>
                        <p class="text-sm font-medium <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'text-green-700' : 'text-gray-600'; ?>">
                            <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'Completed' : 'Pending'; ?>
                        </p>
                    </div>
                    
                    <!-- Payment -->
                    <div class="requirement-status stats-card p-5 rounded-xl <?php echo ($requirements_status['payment_completed'] > 0) ? 'bg-gradient-to-br from-green-50 to-green-100 border-green-200 glow' : 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-200'; ?> border">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full <?php echo ($requirements_status['payment_completed'] > 0) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center mr-3 shadow-sm">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h3 class="font-medium text-gray-700">Payment Completed</h3>
                        </div>
                        <p class="text-sm font-medium <?php echo ($requirements_status['payment_completed'] > 0) ? 'text-green-700' : 'text-gray-600'; ?>">
                            <?php echo ($requirements_status['payment_completed'] > 0) ? 'Completed' : 'Pending'; ?>
                        </p>
                    </div>
                    
                    <!-- Documents -->
                    <div class="requirement-status stats-card p-5 rounded-xl <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'bg-gradient-to-br from-green-50 to-green-100 border-green-200 glow' : 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-200'; ?> border">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center mr-3 shadow-sm">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <h3 class="font-medium text-gray-700">Required Documents</h3>
                        </div>
                        <p class="text-sm font-medium <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'text-green-700' : 'text-gray-600'; ?>">
                            <?php echo $requirements_status['documents_submitted'] . '/' . $requirements_status['total_required_docs']; ?> Submitted
                        </p>
                    </div>
                    
                    <!-- Overall Status -->
                    <div class="requirement-status stats-card p-5 rounded-xl <?php echo $all_requirements_met ? 'bg-gradient-to-br from-green-50 to-green-100 border-green-200 glow' : 'bg-gradient-to-br from-yellow-50 to-yellow-100 border-yellow-200'; ?> border">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full <?php echo $all_requirements_met ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white'; ?> flex items-center justify-center mr-3 shadow-sm">
                                <i class="fas fa-flag"></i>
                            </div>
                            <h3 class="font-medium text-gray-700">Defense Eligibility</h3>
                        </div>
                        <p class="text-sm font-medium <?php echo $all_requirements_met ? 'text-green-700' : 'text-yellow-700'; ?>">
                            <?php echo $all_requirements_met ? 'Eligible for Defense' : 'Requirements Pending'; ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!$has_research_forum_payment || !$has_pre_oral_payment || !$has_final_defense_payment): ?>
                <div class="mt-6 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl backdrop-filter backdrop-blur-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-yellow-100 p-2 rounded-lg mr-4">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-yellow-800">Payment Required for Schedule Visibility</h3>
                            <p class="text-yellow-700 mt-1">Defense schedules will only be visible after completing the required payments:</p>
                            <ul class="text-yellow-700 text-sm mt-2 space-y-1">
                                <?php if (!$has_research_forum_payment): ?>
                                    <li>• Research Forum payment required</li>
                                <?php endif; ?>
                                <?php if (!$has_pre_oral_payment): ?>
                                    <li>• Pre-Oral Defense payment required for pre-oral schedules</li>
                                <?php endif; ?>
                                <?php if ($has_completed_preoral && !$has_final_defense_payment): ?>
                                    <li>• Final Defense payment required for final defense schedules</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Defense Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Schedule Card -->
                <?php if ($defense_schedule): ?>
                <div class="glass-card p-6 rounded-2xl glow animate-delay-2">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-calendar-day text-primary mr-3"></i>
                            Defense Schedule
                        </h2>
                        <?php 
                        $status_class = 'bg-gray-100 text-gray-700';
                        $status_text = 'PENDING';
                        
                        if ($defense_schedule['status'] == 'completed') {
                            $status_class = 'bg-green-100 text-green-700';
                            $status_text = 'COMPLETED';
                        } elseif ($defense_schedule['status'] == 're_defense') {
                            $status_class = 'bg-red-100 text-red-700';
                            $status_text = 'RE-DEFENSE';
                        } elseif ($defense_schedule['status'] == 'scheduled') {
                            $status_class = 'bg-blue-100 text-blue-700';
                            $status_text = 'SCHEDULED';
                        }
                        ?>
                        <div class="<?php echo $status_class; ?> text-xs font-semibold px-3 py-1 rounded-full">
                            <?php echo $status_text; ?>
                        </div>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-600 text-sm">Defense Type & Date</h3>
                                <p class="text-gray-900 font-medium"><?php 
                                    $t = $defense_schedule['defense_type'];
                                    if ($t === 'pre_oral_redefense') echo 'Pre-Oral Redefense';
                                    elseif ($t === 'final_redefense') echo 'Final Redefense';
                                    else echo ucfirst(str_replace('_', ' ', $t)) . ' Defense';
                                ?></p>
                                <p class="text-gray-700"><?php echo date('F j, Y', strtotime($defense_schedule['defense_date'])); ?></p>
                                <p class="text-gray-600 text-sm"><?php echo date('g:i A', strtotime($defense_schedule['start_time'])); ?> – <?php echo date('g:i A', strtotime($defense_schedule['end_time'])); ?></p>
                            </div>
                        </div>
                        
                        <?php if (!empty($defense_schedule['building']) && !empty($defense_schedule['room_name'])): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-600 text-sm">Venue</h3>
                                <p class="text-gray-900 font-medium"><?php echo $defense_schedule['building'] . ' ' . $defense_schedule['room_name']; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($panel_members)): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-600 text-sm mb-2">Panel Members</h3>
                                <div class="space-y-3">
                                    <?php 
                                    // Display all panel members with same design
                                    foreach ($panel_members as $panel): 
                                        $colors = ['blue', 'purple', 'green', 'indigo'];
                                        $color = $colors[array_rand($colors)];
                                        $role_display = strtolower(trim($panel['role'])) === 'chairperson' ? 'Chairperson' : 'Member';
                                    ?>
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-<?php echo $color; ?>-100 flex items-center justify-center text-<?php echo $color; ?>-700 text-xs font-semibold mr-3 shadow-sm">
                                            <?php echo strtoupper($panel['initials']); ?>
                                        </div>
                                        <div>
                                            <p class="text-gray-900 font-medium text-sm"><?php echo $panel['name']; ?></p>
                                            <p class="text-gray-600 text-xs"><?php echo $role_display; ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($defense_schedule['status'] == 'completed'): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-2 bg-green-100 text-green-600 rounded-lg mr-4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-600 text-sm">Status</h3>
                                <p class="text-green-700 font-medium">Defense Completed Successfully</p>
                                <?php if ($defense_schedule['defense_type'] == 'pre_oral'): ?>
                                <p class="text-gray-600 text-sm mt-1">Awaiting final defense schedule</p>
                                <?php else: ?>
                                <p class="text-green-600 text-sm mt-1 font-medium">🎉 All defense requirements completed! Congratulations!</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php elseif ($defense_schedule['status'] == 're_defense'): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 p-2 bg-red-100 text-red-600 rounded-lg mr-4">
                                <i class="fas fa-redo"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-600 text-sm">Status</h3>
                                <p class="text-red-700 font-medium">Re-defense Required</p>
                                <p class="text-gray-600 text-sm mt-1">Please prepare for your re-defense presentation</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="glass-card p-6 rounded-2xl glow flex flex-col animate-delay-2">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-calendar-day text-primary mr-3"></i>
                            Defense Schedule
                        </h2>
                        <div class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1 rounded-full">
                            PENDING
                        </div>
                    </div>
                    
                    <div class="text-center py-8 flex-1 flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-600">No Defense Scheduled Yet</h3>
                        <p class="text-gray-500 mt-2 max-w-xs">Your defense schedule will be assigned once all requirements are completed.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Preparation Timeline -->
                <div class="glass-card p-6 rounded-2xl glow animate-delay-3">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 pb-3 border-b border-gray-100 flex items-center">
                        <i class="fas fa-road text-primary mr-3"></i>
                        Preparation Timeline
                    </h2>
                    
                    <div class="relative">
                        <!-- Timeline -->
                        <div class="enhanced-timeline border-l-2 border-gray-200 pl-8 space-y-8">
                            <!-- Item 1 - Proposal Submission -->
                            <div class="relative timeline-item">
                                <div class="absolute -left-10 top-0 w-8 h-8 rounded-full <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'bg-green-500 shadow-md' : 'bg-gray-300'; ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'fa-check' : 'fa-clock'; ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium text-gray-800">Proposal Submission</h3>
                                    <p class="text-sm text-gray-600 mt-1">Required for defense scheduling</p>
                                    <p class="text-xs font-medium <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'text-green-600' : 'text-gray-500'; ?> mt-2">
                                        <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'Completed' : 'Pending'; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 2 - Payment (dynamic by current phase) -->
                            <?php 
                                $payments_ok_preoral = ($has_research_forum_payment && $has_pre_oral_payment);
                                $payments_ok_final = ($has_research_forum_payment && $has_pre_oral_payment && $has_final_defense_payment);
                                $is_final_phase = $has_completed_preoral; // after pre-oral completion, next phase is final
                                $payments_ok = $is_final_phase ? $payments_ok_final : $payments_ok_preoral;
                                $payment_requirements_text = $is_final_phase 
                                    ? 'Required: Research Forum + Pre-Oral + Final payments' 
                                    : 'Required: Research Forum + Pre-Oral payments';
                            ?>
                            <div class="relative timeline-item">
                                <div class="absolute -left-10 top-0 w-8 h-8 rounded-full <?php echo $payments_ok ? 'bg-green-500 shadow-md' : 'bg-gray-300'; ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo $payments_ok ? 'fa-check' : 'fa-clock'; ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium text-gray-800">Payment Completion</h3>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo $payment_requirements_text; ?></p>
                                    <p class="text-xs font-medium <?php echo $payments_ok ? 'text-green-600' : 'text-gray-500'; ?> mt-2">
                                        <?php echo $payments_ok ? 'Completed' : 'Pending'; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 3 - Documents -->
                            <div class="relative timeline-item">
                                <div class="absolute -left-10 top-0 w-8 h-8 rounded-full <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'bg-green-500 shadow-md' : 'bg-gray-300'; ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'fa-check' : 'fa-clock'; ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium text-gray-800">Required Documents Submission</h3>
                                    <p class="text-sm text-gray-600 mt-1">Required for defense scheduling</p>
                                    <p class="text-xs font-medium <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'text-green-600' : 'text-gray-500'; ?> mt-2">
                                        <?php echo $requirements_status['documents_submitted'] . '/' . $requirements_status['total_required_docs']; ?> Submitted
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 4 - Defense Scheduling -->
                            <div class="relative timeline-item">
                                <div class="absolute -left-10 top-0 w-8 h-8 rounded-full <?php echo ($defense_schedule) ? 'bg-green-500 shadow-md' : ($all_requirements_met ? 'bg-yellow-500 shadow-md' : 'bg-gray-300'); ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($defense_schedule) ? 'fa-check' : ($all_requirements_met ? 'fa-spinner fa-pulse' : 'fa-clock'); ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium text-gray-800">Defense Schedule Assignment</h3>
                                    <p class="text-sm text-gray-600 mt-1">Assigned by coordinator</p>
                                    <p class="text-xs font-medium <?php echo ($defense_schedule) ? 'text-green-600' : ($all_requirements_met ? 'text-yellow-600' : 'text-gray-500'); ?> mt-2">
                                        <?php echo ($defense_schedule) ? 'Scheduled' : ($all_requirements_met ? 'Pending Assignment' : 'Requirements Pending'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 5 - Current Defense (Pre-oral or Final) -->
                            <div class="relative">
                                <?php 
                                $defense_completed = ($defense_schedule && $defense_schedule['status'] == 'completed');
                                $defense_scheduled = ($defense_schedule && $defense_schedule['status'] == 'scheduled');
                                $defense_redefense = ($defense_schedule && $defense_schedule['status'] == 're_defense');
                                $is_current_final = ($defense_schedule && $defense_schedule['defense_type'] == 'final');
                                ?>
                                <div class="absolute -left-10 top-0 w-8 h-8 rounded-full <?php echo $defense_completed ? 'bg-green-500 shadow-md' : ($defense_redefense ? 'bg-red-500 shadow-md' : ($defense_scheduled ? 'bg-blue-500 shadow-md' : 'bg-gray-300')); ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo $defense_completed ? 'fa-check' : ($defense_redefense ? 'fa-redo' : ($defense_scheduled ? 'fa-calendar' : 'fa-clock')); ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium text-gray-800"><?php echo ($defense_schedule) ? ucfirst(str_replace('_', ' ', $defense_schedule['defense_type'])) . ' Defense' : 'Defense Presentation'; ?></h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?php 
                                        if ($defense_completed) {
                                            if ($is_current_final) {
                                                echo '🎉 Completed successfully - All requirements fulfilled!';
                                            } else {
                                                echo 'Completed successfully';
                                            }
                                        } elseif ($defense_redefense) {
                                            echo 'Re-defense required';
                                        } elseif ($defense_scheduled) {
                                            echo 'Scheduled for presentation';
                                        } else {
                                            echo 'Presentation and evaluation';
                                        }
                                        ?>
                                    </p>
                                    <p class="text-xs font-medium <?php echo $defense_completed ? 'text-green-600' : ($defense_redefense ? 'text-red-600' : ($defense_scheduled ? 'text-blue-600' : 'text-gray-500')); ?> mt-2">
                                        <?php 
                                        if ($defense_schedule) {
                                            echo date('F j, Y', strtotime($defense_schedule['defense_date']));
                                            if ($defense_completed) {
                                                if ($is_current_final) {
                                                    echo ' - 🏆 CONGRATULATIONS!';
                                                } else {
                                                    echo ' - Completed';
                                                }
                                            } elseif ($defense_redefense) {
                                                echo ' - Re-defense';
                                            }
                                        } else {
                                            echo 'Not Scheduled';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 6 - Final Defense (show if pre-oral exists and is completed) -->
                            <?php 
                            // $has_completed_preoral computed above
                            
                            if ($has_completed_preoral): 
                                // Check for final defense
                                $final_defense_query = "SELECT * FROM defense_schedules WHERE group_id = '$group_id' AND defense_type = 'final' ORDER BY defense_date DESC LIMIT 1";
                                $final_defense_result = mysqli_query($conn, $final_defense_query);
                                $final_defense = mysqli_fetch_assoc($final_defense_result);
                                
                                $final_completed = ($final_defense && $final_defense['status'] == 'completed');
                                $final_scheduled = ($final_defense && $final_defense['status'] == 'scheduled');
                                $final_redefense = ($final_defense && $final_defense['status'] == 're_defense');
                            ?>
                            <div class="relative">
                                <div class="absolute -left-10 top-0 w-8 h-8 rounded-full <?php echo $final_completed ? 'bg-green-500 shadow-md' : ($final_redefense ? 'bg-red-500 shadow-md' : ($final_scheduled ? 'bg-blue-500 shadow-md' : 'bg-yellow-500 shadow-md')); ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo $final_completed ? 'fa-check' : ($final_redefense ? 'fa-redo' : ($final_scheduled ? 'fa-calendar' : 'fa-hourglass-half')); ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium text-gray-800">Final Defense</h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?php 
                                        if ($final_completed) {
                                            echo '🎉 Completed successfully - All requirements fulfilled!';
                                        } elseif ($final_redefense) {
                                            echo 'Re-defense required';
                                        } elseif ($final_scheduled) {
                                            echo 'Scheduled for presentation';
                                        } else {
                                            echo 'Awaiting schedule assignment';
                                        }
                                        ?>
                                    </p>
                                    <p class="text-xs font-medium <?php echo $final_completed ? 'text-green-600' : ($final_redefense ? 'text-red-600' : ($final_scheduled ? 'text-blue-600' : 'text-yellow-600')); ?> mt-2">
                                        <?php 
                                        if ($final_defense) {
                                            echo date('F j, Y', strtotime($final_defense['defense_date']));
                                            if ($final_completed) echo ' - 🏆 CONGRATULATIONS!';
                                            elseif ($final_redefense) echo ' - Re-defense';
                                        } else {
                                            echo 'Pending Assignment';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Item 7 - Graduation Ready (if any final defense completed) -->
                            <?php 
                            // Check if any final defense is completed
                            $any_final_completed_query = "SELECT * FROM defense_schedules WHERE group_id = '$group_id' AND defense_type = 'final' AND status = 'completed' LIMIT 1";
                            $any_final_completed_result = mysqli_query($conn, $any_final_completed_query);
                            $any_final_completed = mysqli_num_rows($any_final_completed_result) > 0;
                            
                            if ($any_final_completed): ?>
                            <div class="relative">
                                <div class="absolute -left-10 top-0 w-8 h-8 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-500 shadow-lg border-4 border-white flex items-center justify-center">
                                    <i class="fas fa-graduation-cap text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium text-gray-800">Ready for Graduation</h3>
                                    <p class="text-sm text-gray-600 mt-1">All defense requirements completed successfully</p>
                                    <p class="text-xs font-medium text-yellow-600 mt-2">🎓 Eligible for graduation ceremony</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Additional Resources Card -->
                <div class="glass-card p-6 rounded-2xl glow animate-delay-4">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 pb-3 border-b border-gray-100 flex items-center">
                        <i class="fas fa-toolbox text-primary mr-3"></i>
                        Defense Resources
                    </h2>
                    
                    <div class="space-y-4">
                        <a href="#" class="resource-card flex items-center p-4 border border-gray-100 rounded-xl hover:border-primary-100 hover:bg-primary-50 transition group">
                            <div class="flex-shrink-0 p-3 bg-blue-100 text-blue-600 rounded-lg mr-4 group-hover:bg-blue-200 transition">
                                <i class="fas fa-file-powerpoint"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-primary transition">Presentation Template</h3>
                                <p class="text-sm text-gray-600 mt-1">Download defense slides template</p>
                            </div>
                            <div class="ml-auto text-gray-300 group-hover:text-primary transition">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="resource-card flex items-center p-4 border border-gray-100 rounded-xl hover:border-primary-100 hover:bg-primary-50 transition group">
                            <div class="flex-shrink-0 p-3 bg-blue-100 text-blue-600 rounded-lg mr-4 group-hover:bg-blue-200 transition">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-primary transition">Evaluation Rubric</h3>
                                <p class="text-sm text-gray-600 mt-1">View defense evaluation criteria</p>
                            </div>
                            <div class="ml-auto text-gray-300 group-hover:text-primary transition">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="resource-card flex items-center p-4 border border-gray-100 rounded-xl hover:border-primary-100 hover:bg-primary-50 transition group">
                            <div class="flex-shrink-0 p-3 bg-blue-100 text-blue-600 rounded-lg mr-4 group-hover:bg-blue-200 transition">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-primary transition">FAQ & Guidelines</h3>
                                <p class="text-sm text-gray-600 mt-1">Common questions and defense tips</p>
                            </div>
                            <div class="ml-auto text-gray-300 group-hover:text-primary transition">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="resource-card flex items-center p-4 border border-gray-100 rounded-xl hover:border-primary-100 hover:bg-primary-50 transition group">
                            <div class="flex-shrink-0 p-3 bg-blue-100 text-blue-600 rounded-lg mr-4 group-hover:bg-blue-200 transition">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-primary transition">Schedule Practice Session</h3>
                                <p class="text-sm text-gray-600 mt-1">Book a practice room with your adviser</p>
                            </div>
                            <div class="ml-auto text-gray-300 group-hover:text-primary transition">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Countdown Timer (only if defense is scheduled and not completed)
        <?php if ($defense_schedule && $defense_schedule['status'] == 'scheduled'): ?>
        function updateCountdown() {
            // Try different date format approaches
            const dateStr1 = '<?php echo date('Y-m-d', strtotime($defense_schedule['defense_date'])); ?>T<?php echo $defense_schedule['start_time']; ?>';
            const dateStr2 = '<?php echo date('Y/m/d H:i:s', strtotime($defense_schedule['defense_date'] . ' ' . $defense_schedule['start_time'])); ?>';
            
            console.log('Date string 1:', dateStr1);
            console.log('Date string 2:', dateStr2);
            
            const defenseDate = new Date(dateStr2).getTime();
            const now = new Date().getTime();
            const distance = defenseDate - now;
            
            console.log('Defense Date Timestamp:', defenseDate);
            console.log('Current Time:', now);
            console.log('Distance:', distance);
            console.log('Is NaN?', isNaN(defenseDate));
            
            // If defense date has passed or is invalid
            if (distance < 0 || isNaN(defenseDate)) {
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                
                // Show defense completed message
                const countdownContainer = document.querySelector('.countdown-timer');
                if (countdownContainer && distance < 0) {
                    countdownContainer.innerHTML = '<div class="text-center"><i class="fas fa-check-circle text-green-400 text-3xl mb-2"></i><br><span class="text-lg font-semibold">Defense Completed</span></div>';
                }
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        <?php endif; ?>

        // Document upload simulation
        document.querySelectorAll('a[href="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                alert('This resource would be available here in a complete implementation');
            });
        });
    </script>
</body>
</html>