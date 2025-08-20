<?php
session_start();
include('../includes/connection.php');

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
    
    // Check defense schedule
    $defense_query = "SELECT ds.*, r.room_name, r.building 
                     FROM defense_schedules ds 
                     LEFT JOIN rooms r ON ds.room_id = r.id 
                     WHERE ds.group_id = '$group_id'";
    $defense_result = mysqli_query($conn, $defense_query);
    
    if (mysqli_num_rows($defense_result) > 0) {
        $defense_schedule = mysqli_fetch_assoc($defense_result);
        
        // Get panel members
        $panel_query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.middle_name, dp.role 
                       FROM defense_panel dp 
                       JOIN user_tbl u ON dp.faculty_id = u.user_id 
                       WHERE dp.defense_id = '{$defense_schedule['id']}' 
                       ORDER BY dp.role";
        $panel_result = mysqli_query($conn, $panel_query);
        
        while ($panel = mysqli_fetch_assoc($panel_result)) {
            $panel_members[] = $panel;
        }
    }
    
    // Check requirements status
    // 1. Check if proposal is submitted (not checking status since column doesn't exist)
    $proposal_query = "SELECT COUNT(*) as count FROM proposals WHERE group_id = '$group_id'";
    $proposal_result = mysqli_query($conn, $proposal_query);
    if ($proposal_result) {
        $proposal_data = mysqli_fetch_assoc($proposal_result);
        $requirements_status['proposal_submitted'] = $proposal_data['count'];
    }
    
    // 2. Check if payment is completed
    $payment_query = "SELECT COUNT(*) as count FROM payments WHERE student_id = '$user_id' AND status = 'completed'";
    $payment_result = mysqli_query($conn, $payment_query);
    if ($payment_result) {
        $payment_data = mysqli_fetch_assoc($payment_result);
        $requirements_status['payment_completed'] = $payment_data['count'];
    }
    
    // 3. Check required documents (if the documents table exists)
    $docs_query = "SELECT COUNT(*) as count FROM required_documents WHERE required_for_defense = 1";
    $docs_result = mysqli_query($conn, $docs_query);
    if ($docs_result) {
        $docs_data = mysqli_fetch_assoc($docs_result);
        $requirements_status['total_required_docs'] = $docs_data['count'];
        
        // Check submitted documents if table exists
        $submitted_query = "SELECT COUNT(*) as count FROM document_submissions WHERE group_id = '$group_id'";
        $submitted_result = mysqli_query($conn, $submitted_query);
        if ($submitted_result) {
            $submitted_data = mysqli_fetch_assoc($submitted_result);
            $requirements_status['documents_submitted'] = $submitted_data['count'];
        }
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
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .countdown-timer {
            font-family: 'Courier New', monospace;
        }
        .floating-panel {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .requirement-status {
            transition: all 0.3s ease;
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
<body class="bg-gray-50 text-gray-800 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>
        
        <main class="flex-1 overflow-y-auto p-6">
            <!-- Status Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Countdown Banner (only show if defense is scheduled) -->
            <?php if ($defense_schedule): ?>
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-xl p-6 mb-6 floating-panel">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-xl font-bold mb-2">Your Defense Countdown</h2>
                        <p class="text-blue-100">Final defense presentation on <?php echo date('F j, Y', strtotime($defense_schedule['defense_date'])); ?></p>
                    </div>
                    <div class="countdown-timer text-3xl font-bold">
                        <span id="days">00</span>d : 
                        <span id="hours">00</span>h : 
                        <span id="minutes">00</span>m : 
                        <span id="seconds">00</span>s
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Requirements Status Card -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300 mb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Defense Requirements Status</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Proposal Submission -->
                    <div class="requirement-status p-4 rounded-lg border <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-gray-50'; ?>">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center mr-2">
                                <i class="fas fa-file-alt text-xs"></i>
                            </div>
                            <h3 class="font-medium">Proposal Submitted</h3>
                        </div>
                        <p class="text-sm <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'text-green-700' : 'text-gray-600'; ?>">
                            <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'Completed' : 'Pending'; ?>
                        </p>
                    </div>
                    
                    <!-- Payment -->
                    <div class="requirement-status p-4 rounded-lg border <?php echo ($requirements_status['payment_completed'] > 0) ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-gray-50'; ?>">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full <?php echo ($requirements_status['payment_completed'] > 0) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center mr-2">
                                <i class="fas fa-credit-card text-xs"></i>
                            </div>
                            <h3 class="font-medium">Payment Completed</h3>
                        </div>
                        <p class="text-sm <?php echo ($requirements_status['payment_completed'] > 0) ? 'text-green-700' : 'text-gray-600'; ?>">
                            <?php echo ($requirements_status['payment_completed'] > 0) ? 'Completed' : 'Pending'; ?>
                        </p>
                    </div>
                    
                    <!-- Documents -->
                    <div class="requirement-status p-4 rounded-lg border <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-gray-50'; ?>">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'; ?> flex items-center justify-center mr-2">
                                <i class="fas fa-file-upload text-xs"></i>
                            </div>
                            <h3 class="font-medium">Required Documents</h3>
                        </div>
                        <p class="text-sm <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'text-green-700' : 'text-gray-600'; ?>">
                            <?php echo $requirements_status['documents_submitted'] . '/' . $requirements_status['total_required_docs']; ?> Submitted
                        </p>
                    </div>
                    
                    <!-- Overall Status -->
                    <div class="requirement-status p-4 rounded-lg border <?php echo $all_requirements_met ? 'border-green-500 bg-green-50' : 'border-yellow-500 bg-yellow-50'; ?>">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full <?php echo $all_requirements_met ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white'; ?> flex items-center justify-center mr-2">
                                <i class="fas fa-flag text-xs"></i>
                            </div>
                            <h3 class="font-medium">Defense Eligibility</h3>
                        </div>
                        <p class="text-sm <?php echo $all_requirements_met ? 'text-green-700' : 'text-yellow-700'; ?>">
                            <?php echo $all_requirements_met ? 'Eligible for Defense' : 'Requirements Pending'; ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!$all_requirements_met): ?>
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-blue-800">Defense Scheduling</h3>
                            <p class="text-blue-700">Your defense schedule will be assigned once all requirements are completed. Please complete the pending requirements above.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Defense Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Schedule Card -->
                <?php if ($defense_schedule): ?>
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Defense Schedule</h2>
                        <div class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">
                            CONFIRMED
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-600">Date & Time</h3>
                                <p class="text-gray-900"><?php echo date('F j, Y', strtotime($defense_schedule['defense_date'])); ?> • <?php echo date('g:i A', strtotime($defense_schedule['start_time'])); ?> – <?php echo date('g:i A', strtotime($defense_schedule['end_time'])); ?></p>
                            </div>
                        </div>
                        
                        <?php if (!empty($defense_schedule['building']) && !empty($defense_schedule['room_name'])): ?>
                        <div class="flex items-start">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-600">Venue</h3>
                                <p class="text-gray-900"><?php echo $defense_schedule['building'] . ' ' . $defense_schedule['room_name']; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($panel_members)): ?>
                        <div class="flex items-start">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-600">Panel Members</h3>
                                <div class="mt-1 space-y-2">
                                    <?php foreach ($panel_members as $panel): 
                                        $initials = '';
                                        if (!empty($panel['first_name'])) $initials .= substr($panel['first_name'], 0, 1);
                                        if (!empty($panel['last_name'])) $initials .= substr($panel['last_name'], 0, 1);
                                        if (empty($initials)) $initials = substr($panel['email'], 0, 2);
                                        
                                        $colors = ['blue', 'purple', 'green', 'yellow', 'red'];
                                        $color = $colors[array_rand($colors)];
                                    ?>
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded-full bg-<?php echo $color; ?>-200 flex items-center justify-center text-<?php echo $color; ?>-700 text-xs mr-2">
                                            <?php echo strtoupper($initials); ?>
                                        </div>
                                        <span>
                                            <?php 
                                            $name = '';
                                            if (!empty($panel['first_name'])) $name .= $panel['first_name'] . ' ';
                                            if (!empty($panel['middle_name'])) $name .= substr($panel['middle_name'], 0, 1) . '. ';
                                            if (!empty($panel['last_name'])) $name .= $panel['last_name'];
                                            if (empty($name)) $name = $panel['email'];
                                            echo $name . ' (' . ucfirst($panel['role']) . ')';
                                            ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Defense Schedule</h2>
                        <div class="bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1 rounded-full">
                            PENDING
                        </div>
                    </div>
                    
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-600">No Defense Scheduled Yet</h3>
                        <p class="text-gray-500 mt-2">Your defense schedule will be assigned once all requirements are completed.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Preparation Timeline -->
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Preparation Timeline</h2>
                    
                    <div class="relative">
                        <!-- Timeline -->
                        <div class="border-l-2 border-blue-200 pl-6 space-y-6">
                            <!-- Item 1 - Proposal Submission -->
                            <div class="relative">
                                <div class="absolute -left-9 top-0 w-6 h-6 rounded-full <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'bg-green-500' : 'bg-gray-300'; ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'fa-check' : 'fa-clock'; ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium">Proposal Submission</h3>
                                    <p class="text-sm text-gray-600">Required for defense scheduling</p>
                                    <p class="text-xs <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'text-green-600' : 'text-gray-500'; ?> mt-1">
                                        <?php echo ($requirements_status['proposal_submitted'] > 0) ? 'Completed' : 'Pending'; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 2 - Payment -->
                            <div class="relative">
                                <div class="absolute -left-9 top-0 w-6 h-6 rounded-full <?php echo ($requirements_status['payment_completed'] > 0) ? 'bg-green-500' : 'bg-gray-300'; ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($requirements_status['payment_completed'] > 0) ? 'fa-check' : 'fa-clock'; ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium">Payment Completion</h3>
                                    <p class="text-sm text-gray-600">Required for defense scheduling</p>
                                    <p class="text-xs <?php echo ($requirements_status['payment_completed'] > 0) ? 'text-green-600' : 'text-gray-500'; ?> mt-1">
                                        <?php echo ($requirements_status['payment_completed'] > 0) ? 'Completed' : 'Pending'; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 3 - Documents -->
                            <div class="relative">
                                <div class="absolute -left-9 top-0 w-6 h-6 rounded-full <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'bg-green-500' : 'bg-gray-300'; ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'fa-check' : 'fa-clock'; ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium">Required Documents Submission</h3>
                                    <p class="text-sm text-gray-600">Required for defense scheduling</p>
                                    <p class="text-xs <?php echo ($requirements_status['documents_submitted'] >= $requirements_status['total_required_docs']) ? 'text-green-600' : 'text-gray-500'; ?> mt-1">
                                        <?php echo $requirements_status['documents_submitted'] . '/' . $requirements_status['total_required_docs']; ?> Submitted
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 4 - Defense Scheduling -->
                            <div class="relative">
                                <div class="absolute -left-9 top-0 w-6 h-6 rounded-full <?php echo ($defense_schedule) ? 'bg-green-500' : ($all_requirements_met ? 'bg-yellow-500' : 'bg-gray-300'); ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($defense_schedule) ? 'fa-check' : ($all_requirements_met ? 'fa-spinner' : 'fa-clock'); ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium">Defense Schedule Assignment</h3>
                                    <p class="text-sm text-gray-600">Assigned by coordinator</p>
                                    <p class="text-xs <?php echo ($defense_schedule) ? 'text-green-600' : ($all_requirements_met ? 'text-yellow-600' : 'text-gray-500'); ?> mt-1">
                                        <?php echo ($defense_schedule) ? 'Scheduled' : ($all_requirements_met ? 'Pending Assignment' : 'Requirements Pending'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Item 5 - Final Defense -->
                            <div class="relative">
                                <div class="absolute -left-9 top-0 w-6 h-6 rounded-full <?php echo ($defense_schedule && strtotime($defense_schedule['defense_date']) < time()) ? 'bg-green-500' : 'bg-gray-300'; ?> border-4 border-white flex items-center justify-center">
                                    <i class="fas <?php echo ($defense_schedule && strtotime($defense_schedule['defense_date']) < time()) ? 'fa-check' : 'fa-clock'; ?> text-white text-xs"></i>
                                </div>
                                <div class="pl-2">
                                    <h3 class="font-medium">Final Defense</h3>
                                    <p class="text-sm text-gray-600">Presentation and evaluation</p>
                                    <p class="text-xs <?php echo ($defense_schedule && strtotime($defense_schedule['defense_date']) < time()) ? 'text-green-600' : 'text-gray-500'; ?> mt-1">
                                        <?php echo ($defense_schedule) ? date('F j, Y', strtotime($defense_schedule['defense_date'])) : 'Not Scheduled'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Resources Card -->
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Defense Resources</h2>
                    
                    <div class="space-y-4">
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-file-powerpoint"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Presentation Template</h3>
                                <p class="text-sm text-gray-600">Download defense slides template</p>
                            </div>
                        </a>
                        
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Evaluation Rubric</h3>
                                <p class="text-sm text-gray-600">View defense evaluation criteria</p>
                            </div>
                        </a>
                        
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">FAQ & Guidelines</h3>
                                <p class="text-sm text-gray-600">Common questions and defense tips</p>
                            </div>
                        </a>
                        
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Schedule Practice Session</h3>
                                <p class="text-sm text-gray-600">Book a practice room with your adviser</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Countdown Timer (only if defense is scheduled)
        <?php if ($defense_schedule): ?>
        function updateCountdown() {
            const defenseDate = new Date('<?php echo $defense_schedule['defense_date'] . ' ' . $defense_schedule['start_time']; ?>').getTime();
            const now = new Date().getTime();
            const distance = defenseDate - now;
            
            // If defense date has passed
            if (distance < 0) {
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
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