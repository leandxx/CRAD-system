<?php
session_start();
include('../includes/connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../admin_pages/admin.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['schedule_defense'])) {
        $group_id = mysqli_real_escape_string($conn, $_POST['group_id']);
        $defense_date = mysqli_real_escape_string($conn, $_POST['defense_date']);
        $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
        $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
        $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
        $panel_members = isset($_POST['panel_members']) ? $_POST['panel_members'] : [];

        // Default status = scheduled
        $status = 'scheduled';

        // Insert defense schedule
        $schedule_query = "INSERT INTO defense_schedules 
                          (group_id, defense_date, start_time, end_time, room_id, status) 
                          VALUES ('$group_id', '$defense_date', '$start_time', '$end_time', '$room_id', '$status')";

        if (mysqli_query($conn, $schedule_query)) {
            $defense_id = mysqli_insert_id($conn);

            // Insert panel members
            foreach ($panel_members as $faculty_id) {
                $faculty_id = mysqli_real_escape_string($conn, $faculty_id);
                $panel_query = "INSERT INTO defense_panel (defense_id, faculty_id, role) 
                               VALUES ('$defense_id', '$faculty_id', 'member')";
                mysqli_query($conn, $panel_query);
            }

            $_SESSION['success_message'] = "Defense scheduled successfully!";
            header("Location: admin-defense.php");
            exit();
        } else {
            $error_message = "Error scheduling defense: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['delete_schedule'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);

        // Delete panel members first
        $delete_panel_query = "DELETE FROM defense_panel WHERE defense_id = '$defense_id'";
        mysqli_query($conn, $delete_panel_query);

        // Delete defense schedule
        $delete_schedule_query = "DELETE FROM defense_schedules WHERE id = '$defense_id'";

        if (mysqli_query($conn, $delete_schedule_query)) {
            $_SESSION['success_message'] = "Defense schedule deleted successfully!";
            header("Location: admin-defense.php");
            exit();
        } else {
            $error_message = "Error deleting defense schedule: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['edit_defense'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $defense_date = mysqli_real_escape_string($conn, $_POST['defense_date']);
        $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
        $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
        $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
        $panel_members = isset($_POST['panel_members']) ? $_POST['panel_members'] : [];

        // Update defense schedule (don't update group_id as it shouldn't change)
        $update_query = "UPDATE defense_schedules 
                         SET defense_date = '$defense_date', 
                             start_time = '$start_time', end_time = '$end_time', 
                             room_id = '$room_id'
                         WHERE id = '$defense_id'";

        if (mysqli_query($conn, $update_query)) {
            // Update panel members
            $delete_panel_query = "DELETE FROM defense_panel WHERE defense_id = '$defense_id'";
            mysqli_query($conn, $delete_panel_query);

            foreach ($panel_members as $faculty_id) {
                $faculty_id = mysqli_real_escape_string($conn, $faculty_id);
                $panel_query = "INSERT INTO defense_panel (defense_id, faculty_id, role) 
                               VALUES ('$defense_id', '$faculty_id', 'member')";
                mysqli_query($conn, $panel_query);
            }

            $_SESSION['success_message'] = "Defense schedule updated successfully!";
            header("Location: admin-defense.php");
            exit();
        } else {
            $error_message = "Error updating defense schedule: " . mysqli_error($conn);
        }
    }
}

// Get all defense schedules
$defense_query = "SELECT ds.*, g.name as group_name, r.room_name, r.building, p.title as proposal_title,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                 FROM defense_schedules ds 
                 LEFT JOIN groups g ON ds.group_id = g.id 
                 LEFT JOIN rooms r ON ds.room_id = r.id 
                 LEFT JOIN proposals p ON g.id = p.group_id
                 LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                 LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                 GROUP BY ds.id
                 ORDER BY ds.defense_date, ds.start_time";
$defense_result = mysqli_query($conn, $defense_query);
$defense_schedules = [];

while ($schedule = mysqli_fetch_assoc($defense_result)) {
    // Get panel members for each defense
    $panel_query = "SELECT u.user_id, u.email
                   FROM defense_panel dp 
                   JOIN user_tbl u ON dp.faculty_id = u.user_id 
                   WHERE dp.defense_id = '{$schedule['id']}'";
    $panel_result = mysqli_query($conn, $panel_query);
    $panel_members = [];

    while ($panel = mysqli_fetch_assoc($panel_result)) {
        $panel_members[] = $panel;
    }

    // If defense_date < today → mark as completed
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    if ($schedule['defense_date'] < $current_date || 
        ($schedule['defense_date'] == $current_date && $schedule['end_time'] < $current_time)) {
        // Update status in database if not already completed
        if ($schedule['status'] != 'completed') {
            $update_query = "UPDATE defense_schedules SET status = 'completed' WHERE id = '{$schedule['id']}'";
            mysqli_query($conn, $update_query);
            $schedule['status'] = 'completed';
        }
    }

    $schedule['panel_members'] = $panel_members;
    $defense_schedules[] = $schedule;
}

// Get all groups with approved proposals
$groups_query = "SELECT g.*, p.title as proposal_title 
                FROM groups g 
                JOIN proposals p ON g.id = p.group_id 
                WHERE p.status = 'Approved'
                ORDER BY g.name";
$groups_result = mysqli_query($conn, $groups_query);
$groups = [];

while ($group = mysqli_fetch_assoc($groups_result)) {
    $groups[] = $group;
}

// Get all faculty members (Admin and Faculty roles)
$faculty_query = "SELECT * FROM user_tbl WHERE role = 'Faculty' OR role = 'Admin'";
$faculty_result = mysqli_query($conn, $faculty_query);
$faculty_members = [];

while ($faculty = mysqli_fetch_assoc($faculty_result)) {
    $faculty_members[] = $faculty;
}

// Get accepted panel members from panel management system
$accepted_panel_query = "SELECT pm.*, pi.status as invitation_status 
                        FROM panel_members pm 
                        LEFT JOIN panel_invitations pi ON pm.id = pi.panel_id 
                        WHERE pi.status = 'accepted' OR pm.status = 'active'
                        GROUP BY pm.id
                        ORDER BY pm.last_name, pm.first_name";
$accepted_panel_result = mysqli_query($conn, $accepted_panel_query);
$accepted_panel_members = [];

while ($panel_member = mysqli_fetch_assoc($accepted_panel_result)) {
    $accepted_panel_members[] = $panel_member;
}

// Get all rooms
$rooms_query = "SELECT * FROM rooms ORDER BY building, room_name";
$rooms_result = mysqli_query($conn, $rooms_query);
$rooms = [];

while ($room = mysqli_fetch_assoc($rooms_result)) {
    $rooms[] = $room;
}

// Get stats for dashboard
$total_proposals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM proposals WHERE status = 'Approved'"));
$scheduled_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM defense_schedules WHERE status = 'scheduled'"));
$pending_defenses = $total_proposals - $scheduled_defenses;
$completed_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM defense_schedules WHERE status = 'completed'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defense Scheduling</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .scroll-container {
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }
        .notification-dot.pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .schedule-card {
            transition: all 0.3s ease;
        }
        .schedule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: scale(0.95);
            opacity: 0;
        }
        .modal.active {
            transform: scale(1);
            opacity: 1;
        }
        .details-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.75rem 1rem;
            align-items: start;
        }
        .detail-icon {
            margin-top: 0.25rem;
            color: #6b7280;
        }
        .panel-tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1rem;
        }
        .panel-tab {
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .panel-tab.active {
            border-bottom-color: #3b82f6;
            color: #3b82f6;
            font-weight: 500;
        }
        .panel-content {
            display: none;
        }
        .panel-content.active {
            display: block;
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

        function toggleEditModal() {
            const modal = document.getElementById('editDefenseModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
        
        function toggleDetailsModal() {
            const modal = document.getElementById('detailsModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
            setTimeout(() => {
                modal.classList.toggle('active');
            }, 10);
        }
        
        // Function to handle status filtering
        function filterStatus(status) {
            const cards = document.querySelectorAll('.defense-card');
            cards.forEach(card => {
                if (status === 'all') {
                    card.classList.remove('hidden');
                } else {
                    const cardStatus = card.getAttribute('data-status');
                    if (cardStatus === status) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                }
            });
            
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.getAttribute('data-filter') === status) {
                    btn.classList.add('bg-primary', 'text-white');
                    btn.classList.remove('bg-gray-200', 'text-gray-700');
                } else {
                    btn.classList.remove('bg-primary', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                }
            });
        }
        
        // Function to handle search
        function handleSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.defense-card');
            
            cards.forEach(card => {
                const textContent = card.textContent.toLowerCase();
                if (textContent.includes(searchTerm)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }
        
        // Function to confirm deletion
        function confirmDelete(defenseId, groupName) {
            if (confirm(`Are you sure you want to delete the defense schedule for ${groupName}?`)) {
                document.getElementById('defense_id').value = defenseId;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Function to view defense details
        function viewDefenseDetails(defenseId) {
            // Find the defense schedule by ID
            const defenseCard = document.querySelector(`.defense-card[data-defense-id="${defenseId}"]`);
            if (defenseCard) {
                // Extract details from the card
                const title = defenseCard.querySelector('h3').textContent;
                const group = defenseCard.querySelector('.text-gray-500').textContent;
                const date = defenseCard.querySelector('.fa-calendar').parentNode.textContent.trim();
                const time = defenseCard.querySelector('.fa-clock').parentNode.textContent.trim();
                const location = defenseCard.querySelector('.fa-map-marker-alt').parentNode.textContent.trim();
                const panel = defenseCard.querySelector('.fa-users').parentNode.textContent.trim();
                const status = defenseCard.querySelector('.px-3').textContent.trim();
                
                // Populate the details modal
                document.getElementById('detailTitle').textContent = title;
                document.getElementById('detailGroup').textContent = group;
                document.getElementById('detailDate').textContent = date;
                document.getElementById('detailTime').textContent = time;
                document.getElementById('detailLocation').textContent = location;
                document.getElementById('detailPanel').textContent = panel;
                document.getElementById('detailStatus').textContent = status;
                
                // Set status color
                const statusElement = document.getElementById('detailStatus');
                statusElement.className = 'px-3 py-1 text-xs font-medium rounded-full';
                if (status === 'Completed') {
                    statusElement.classList.add('bg-green-100', 'text-green-800');
                } else if (status === 'Cancelled') {
                    statusElement.classList.add('bg-red-100', 'text-red-800');
                } else {
                    statusElement.classList.add('bg-blue-100', 'text-blue-800');
                }
                
                toggleDetailsModal();
            }
        }
        
        // Function to switch between panel member tabs
        function switchPanelTab(tabName) {
            // Update active tab
            document.querySelectorAll('.panel-tab').forEach(tab => {
                if (tab.getAttribute('data-tab') === tabName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
            
            // Show active content
            document.querySelectorAll('.panel-content').forEach(content => {
                if (content.getAttribute('data-tab') === tabName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Set default filter to 'all'
            filterStatus('all');
            
            // Set default panel tab to 'accepted'
            switchPanelTab('accepted');
        });
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        
        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6 scroll-container">
            <!-- Status Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Total Proposals</p>
                        <h3 class="text-2xl font-bold"><?php echo $total_proposals; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-file-alt text-primary text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Scheduled</p>
                        <h3 class="text-2xl font-bold"><?php echo $scheduled_defenses; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-success text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Pending</p>
                        <h3 class="text-2xl font-bold"><?php echo $pending_defenses; ?></h3>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-warning text-xl"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Completed</p>
                        <h3 class="text-2xl font-bold"><?php echo $completed_defenses; ?></h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-secondary text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="bg-white rounded-lg shadow mb-6 p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="filterStatus('all')" data-filter="all" class="filter-btn px-3 py-1 rounded-full bg-primary text-white text-sm">All</button>
                        <button onclick="filterStatus('scheduled')" data-filter="scheduled" class="filter-btn px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm">Scheduled</button>
                        <button onclick="filterStatus('pending')" data-filter="pending" class="filter-btn px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm">Pending</button>
                        <button onclick="filterStatus('completed')" data-filter="completed" class="filter-btn px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-sm">Completed</button>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search proposals..." onkeyup="handleSearch()" class="pl-10 pr-4 py-2 border rounded-lg w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-primary">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button onclick="toggleModal()" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Schedule Defense
                        </button>
                    </div>
                </div>
            </div>

            <!-- Defense Schedule Cards -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Defense Schedule</h2>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($defense_schedules as $schedule): ?>
                    <!-- Scheduled Defense Card -->
                    <div class="defense-card bg-white border border-gray-200 rounded-xl shadow-md p-5 flex flex-col justify-between" 
                         data-status="<?php echo $schedule['status']; ?>" 
                         data-defense-id="<?php echo $schedule['id']; ?>">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo $schedule['proposal_title'] ?? 'No Title'; ?></h3>
                            <p class="text-sm text-gray-500 mb-3"><?php echo $schedule['group_name']; ?></p>

                            <p class="text-sm text-gray-700 mb-1">
                                <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                <?php echo date('M j, Y', strtotime($schedule['defense_date'])); ?>
                            </p>
                            <p class="text-sm text-gray-700 mb-1">
                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
                            </p>
                            <p class="text-sm text-gray-700 mb-1">
                                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                <?php echo $schedule['building'] . ' ' . $schedule['room_name']; ?>
                            </p>
                            <p class="text-sm text-gray-700 mb-3">
    <i class="fas fa-users mr-2 text-gray-400"></i>
    <?php echo !empty($schedule['panel_names']) ? $schedule['panel_names'] : 'No panel assigned'; ?>
</p>
                        </div>

                        <!-- Status & Actions -->
                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                            <?php if ($schedule['status'] == 'completed'): ?>
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Completed</span>
                            <?php elseif ($schedule['status'] == 'cancelled'): ?>
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Cancelled</span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Scheduled</span>
                            <?php endif; ?>
                            
                            <div>
                                <button class="text-indigo-600 hover:text-indigo-900 mr-3" onclick="populateEditForm(<?php echo htmlspecialchars(json_encode($schedule)); ?>)"><i class="fas fa-edit"></i></button>
                                <button onclick="confirmDelete(<?php echo $schedule['id']; ?>, '<?php echo $schedule['group_name']; ?>')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Pending / Unscheduled Groups -->
                    <?php 
                    $unscheduled_query = "SELECT g.*, p.title as proposal_title 
                                        FROM groups g 
                                        JOIN proposals p ON g.id = p.group_id 
                                        WHERE g.id NOT IN (SELECT group_id FROM defense_schedules) 
                                        AND p.status = 'Approved'
                                        ORDER BY g.name";
                    $unscheduled_result = mysqli_query($conn, $unscheduled_query);

                    while ($group = mysqli_fetch_assoc($unscheduled_result)): ?>
                    <div class="defense-card bg-gray-50 border border-gray-200 rounded-xl shadow-md p-5 flex flex-col justify-between" data-status="pending">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo $group['proposal_title']; ?></h3>
                            <p class="text-sm text-gray-500 mb-3"><?php echo $group['name']; ?></p>
                            <p class="text-sm text-gray-700 mb-2"><i class="fas fa-calendar-times mr-2 text-gray-400"></i> Not scheduled</p>
                            <p class="text-sm text-gray-700 mb-2"><i class="fas fa-map-marker-alt mr-2 text-gray-400"></i> - </p>
                            <p class="text-sm text-gray-700 mb-3"><i class="fas fa-users mr-2 text-gray-400"></i> Not assigned</p>
                        </div>

                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            <button onclick="toggleModal()" class="text-primary hover:text-blue-900 mr-3">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Upcoming Defenses Section -->
            <h2 class="text-xl font-bold mt-8 mb-4 text-gray-700">Upcoming Defenses</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <?php 
                $upcoming_query = "SELECT ds.*, g.name as group_name, p.title as proposal_title, r.room_name, r.building 
                                FROM defense_schedules ds 
                                JOIN groups g ON ds.group_id = g.id 
                                JOIN proposals p ON g.id = p.group_id 
                                LEFT JOIN rooms r ON ds.room_id = r.id 
                                WHERE ds.defense_date >= CURDATE() AND ds.status = 'scheduled'
                                ORDER BY ds.defense_date, ds.start_time 
                                LIMIT 3";
                $upcoming_result = mysqli_query($conn, $upcoming_query);
                
                while ($upcoming = mysqli_fetch_assoc($upcoming_result)): 
                ?>
                <div class="schedule-card bg-white rounded-lg shadow p-4 border-l-4 border-primary">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg"><?php echo $upcoming['proposal_title']; ?></h3>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Scheduled</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-3"><?php echo $upcoming['group_name']; ?></p>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span><?php echo date('M j, Y', strtotime($upcoming['defense_date'])); ?>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i class="far fa-clock mr-2"></i>
                        <span><?php echo date('g:i A', strtotime($upcoming['start_time'])); ?> - <?php echo date('g:i A', strtotime($upcoming['end_time'])); ?></span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <span><?php echo $upcoming['building'] . ' ' . $upcoming['room_name']; ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($upcoming_result) === 0): ?>
                <div class="schedule-card bg-white rounded-lg shadow p-4 border-l-4 border-gray-300 col-span-3 text-center py-8">
                    <i class="far fa-calendar-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">No upcoming defenses scheduled</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Schedule Defense Modal -->
    <div id="proposalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-auto my-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Schedule Defense</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" class="p-6">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="group_id">Select Group</label>
                    <select name="group_id" id="group_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select a group</option>
                        <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name'] . ' - ' . $group['proposal_title']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="defense_date">Defense Date</label>
                        <input type="date" name="defense_date" id="defense_date" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="room_id">Room</label>
                        <select name="room_id" id="room_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select a room</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo $room['building'] . ' - ' . $room['room_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Panel Members</label>
                    
                    <!-- Panel Tabs -->
                    <div class="panel-tabs mb-3">
                        <div class="panel-tab active" data-tab="accepted" onclick="switchPanelTab('accepted')">Accepted Panel</div>
                    </div>
                    
                    <!-- Accepted Panel Content -->
                    <div class="panel-content active" data-tab="accepted">
                        <?php if (!empty($accepted_panel_members)): ?>
                        <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg">
                            <?php foreach ($accepted_panel_members as $panel_member): ?>
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>" class="mr-2 rounded text-primary focus:ring-primary">
                                <span><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name'] . ' (' . $panel_member['email'] . ')'; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 text-sm p-2 border rounded-lg">No accepted panel members found.</p>
                        <?php endif; ?>
                    </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="toggleModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="schedule_defense" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700">Schedule Defense</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Defense Modal -->
    <div id="editDefenseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
         <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-auto my-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Edit Defense Schedule</h3>
                <button onclick="toggleEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" class="p-6">
                <input type="hidden" name="defense_id" id="edit_defense_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Group</label>
                    <p id="edit_group_name" class="px-3 py-2 bg-gray-100 rounded-lg"></p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_defense_date">Defense Date</label>
                        <input type="date" name="defense_date" id="edit_defense_date" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_room_id">Room</label>
                        <select name="room_id" id="edit_room_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select a room</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo $room['building'] . ' - ' . $room['room_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_start_time">Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_end_time">End Time</label>
                        <input type="time" name="end_time" id="edit_end_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Panel Members</label>
                    
                    <!-- Panel Tabs -->
                    <div class="panel-tabs mb-3">
                        <div class="panel-tab active" data-tab="edit_accepted" onclick="switchEditPanelTab('edit_accepted')">Accepted Panel</div>
                        <div class="panel-tab" data-tab="edit_faculty" onclick="switchEditPanelTab('edit_faculty')">All Faculty</div>
                    </div>
                    
                    <!-- Accepted Panel Content -->
                    <div class="panel-content active" data-tab="edit_accepted">
                        <?php if (!empty($accepted_panel_members)): ?>
                        <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg" id="edit_accepted_panel">
                            <?php foreach ($accepted_panel_members as $panel_member): ?>
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>" class="edit-panel-member mr-2 rounded text-primary focus:ring-primary" data-id="<?php echo $panel_member['id']; ?>">
                                <span><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name'] . ' (' . $panel_member['email'] . ')'; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 text-sm p-2 border rounded-lg">No accepted panel members found.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- All Faculty Content -->
                    <div class="panel-content" data-tab="edit_faculty">
                        <?php if (!empty($faculty_members)): ?>
                        <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg" id="edit_faculty_panel">
                            <?php foreach ($faculty_members as $faculty): ?>
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $faculty['user_id']; ?>" class="edit-panel-member mr-2 rounded text-primary focus:ring-primary" data-id="<?php echo $faculty['user_id']; ?>">
                                <span><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name'] . ' (' . $panel_member['email'] . ')'; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 text-sm p-2 border rounded-lg">No faculty members found.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="toggleEditModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="edit_defense" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Defense Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Defense Details</h3>
                <button onclick="toggleDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <h3 id="detailTitle" class="text-xl font-semibold text-gray-900 mb-2"></h3>
                <p id="detailGroup" class="text-gray-600 mb-6"></p>
                
                <div class="details-grid mb-6">
                    <i class="fas fa-calendar detail-icon"></i>
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p id="detailDate" class="font-medium"></p>
                    </div>
                    
                    <i class="fas fa-clock detail-icon"></i>
                    <div>
                        <p class="text-sm text-gray-500">Time</p>
                        <p id="detailTime" class="font-medium"></p>
                    </div>
                    
                    <i class="fas fa-map-marker-alt detail-icon"></i>
                    <div>
                        <p class="text-sm text-gray-500">Location</p>
                        <p id="detailLocation" class="font-medium"></p>
                    </div>
                    
                    <i class="fas fa-users detail-icon"></i>
                    <div>
                        <p class="text-sm text-gray-500">Panel Members</p>
                        <p id="detailPanel" class="font-medium"></p>
                    </div>
                    
                    <i class="fas fa-info-circle detail-icon"></i>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p id="detailStatus" class="font-medium"></p>
                    </div>
                </div>
                
                <div class="flex justify-end pt-4 border-t">
                    <button onclick="toggleDetailsModal()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" action="" class="hidden">
        <input type="hidden" name="defense_id" id="defense_id">
        <input type="hidden" name="delete_schedule" value="1">
    </form>

    <script>
        // Function to populate edit form with defense data
        function populateEditForm(schedule) {
            document.getElementById('edit_defense_id').value = schedule.id;
            document.getElementById('edit_group_name').textContent = schedule.group_name + ' - ' + (schedule.proposal_title || 'No Title');
            document.getElementById('edit_defense_date').value = schedule.defense_date;
            document.getElementById('edit_room_id').value = schedule.room_id;
            document.getElementById('edit_start_time').value = schedule.start_time;
            document.getElementById('edit_end_time').value = schedule.end_time;
            
            // Clear all checkboxes first
            document.querySelectorAll('.edit-panel-member').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Check the panel members for this defense
            if (schedule.panel_members && schedule.panel_members.length > 0) {
                schedule.panel_members.forEach(panel => {
                    const checkbox = document.querySelector(`.edit-panel-member[data-id="${panel.user_id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            toggleEditModal();
        }
        
        // Function to switch between panel member tabs in edit modal
        function switchEditPanelTab(tabName) {
            // Update active tab
            document.querySelectorAll('.panel-tab[data-tab^="edit_"]').forEach(tab => {
                if (tab.getAttribute('data-tab') === tabName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
            
            // Show active content
            document.querySelectorAll('.panel-content[data-tab^="edit_"]').forEach(content => {
                if (content.getAttribute('data-tab') === tabName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>