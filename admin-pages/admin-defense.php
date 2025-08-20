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
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        
        // Insert defense schedule
        $schedule_query = "INSERT INTO defense_schedules (group_id, defense_date, start_time, end_time, room_id, notes) 
                          VALUES ('$group_id', '$defense_date', '$start_time', '$end_time', '$room_id', '$notes')";
        
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
            header("Location: defense_scheduling.php");
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
            header("Location: defense_scheduling.php");
            exit();
        } else {
            $error_message = "Error deleting defense schedule: " . mysqli_error($conn);
        }
    }
}

// Get all defense schedules
$defense_query = "SELECT ds.*, g.name as group_name, r.room_name, r.building 
                 FROM defense_schedules ds 
                 LEFT JOIN groups g ON ds.group_id = g.id 
                 LEFT JOIN rooms r ON ds.room_id = r.id 
                 ORDER BY ds.defense_date, ds.start_time";
$defense_result = mysqli_query($conn, $defense_query);
$defense_schedules = [];

while ($schedule = mysqli_fetch_assoc($defense_result)) {
    // Get panel members for each defense
    $panel_query = "SELECT u.user_id, u.first_name, u.last_name, u.middle_name 
                   FROM defense_panel dp 
                   JOIN user_tbl u ON dp.faculty_id = u.user_id 
                   WHERE dp.defense_id = '{$schedule['id']}'";
    $panel_result = mysqli_query($conn, $panel_query);
    $panel_members = [];
    
    while ($panel = mysqli_fetch_assoc($panel_result)) {
        $panel_members[] = $panel;
    }
    
    $schedule['panel_members'] = $panel_members;
    $defense_schedules[] = $schedule;
}

// Get all groups with approved proposals
$groups_query = "SELECT g.*, p.title as proposal_title 
                FROM groups g 
                JOIN proposals p ON g.id = p.group_id 
                ORDER BY g.name";
$groups_result = mysqli_query($conn, $groups_query);
$groups = [];

while ($group = mysqli_fetch_assoc($groups_result)) {
    $groups[] = $group;
}

// Get all faculty members
$faculty_query = "SELECT * FROM user_tbl WHERE role = 'Faculty' OR role = 'Admin'";
$faculty_result = mysqli_query($conn, $faculty_query);
$faculty_members = [];

while ($faculty = mysqli_fetch_assoc($faculty_result)) {
    $faculty_members[] = $faculty;
}

// Get all rooms
$rooms_query = "SELECT * FROM rooms ORDER BY building, room_name";
$rooms_result = mysqli_query($conn, $rooms_query);
$rooms = [];

while ($room = mysqli_fetch_assoc($rooms_result)) {
    $rooms[] = $room;
}

// Get stats for dashboard
$total_proposals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM proposals"));
$scheduled_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM defense_schedules"));
$pending_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM proposals WHERE id NOT IN (SELECT group_id FROM defense_schedules)"));
$completed_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM defense_schedules WHERE defense_date < CURDATE()"));
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
        
        // Function to handle status filtering
        function filterStatus(status) {
            const rows = document.querySelectorAll('#defenseTable tbody tr');
            rows.forEach(row => {
                if (status === 'all') {
                    row.classList.remove('hidden');
                } else {
                    const rowStatus = row.getAttribute('data-status');
                    if (rowStatus === status) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
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
            const rows = document.querySelectorAll('#defenseTable tbody tr');
            
            rows.forEach(row => {
                const textContent = row.textContent.toLowerCase();
                if (textContent.includes(searchTerm)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
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
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Set default filter to 'all'
            filterStatus('all');
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

            <!-- Defense Schedule Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="defenseTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group/Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Panel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($defense_schedules as $schedule): 
                                $status = 'scheduled';
                                $current_date = date('Y-m-d');
                                if ($schedule['defense_date'] < $current_date) {
                                    $status = 'completed';
                                }
                            ?>
                            <tr data-status="<?php echo $status; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo $schedule['proposal_title'] ?? 'No Title'; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $schedule['group_name']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M j, Y', strtotime($schedule['defense_date'])); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('g:i A', strtotime($schedule['end_time'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $schedule['building'] . ' ' . $schedule['room_name']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    $panel_names = [];
                                    foreach ($schedule['panel_members'] as $panel) {
                                        $name = '';
                                        if (!empty($panel['first_name'])) $name .= $panel['first_name'] . ' ';
                                        if (!empty($panel['middle_name'])) $name .= substr($panel['middle_name'], 0, 1) . '. ';
                                        if (!empty($panel['last_name'])) $name .= $panel['last_name'];
                                        $panel_names[] = $name;
                                    }
                                    echo implode(', ', $panel_names);
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($status == 'completed'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Scheduled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                                    <button onclick="confirmDelete(<?php echo $schedule['id']; ?>, '<?php echo $schedule['group_name']; ?>')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Show groups without defense schedules -->
                            <?php 
                            $unscheduled_query = "SELECT g.*, p.title as proposal_title 
                                                FROM groups g 
                                                JOIN proposals p ON g.id = p.group_id 
                                                WHERE g.id NOT IN (SELECT group_id FROM defense_schedules) 
                                                ORDER BY g.name";
                            $unscheduled_result = mysqli_query($conn, $unscheduled_query);
                            
                            while ($group = mysqli_fetch_assoc($unscheduled_result)): 
                            ?>
                            <tr data-status="pending">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo $group['proposal_title']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $group['name']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Not scheduled</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Not assigned</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="toggleModal()" class="text-primary hover:text-blue-900 mr-3"><i class="fas fa-calendar-plus"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
                                 WHERE ds.defense_date >= CURDATE() 
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
                        <span><?php echo date('M j, Y', strtotime($upcoming['defense_date'])); ?> | <?php echo date('g:i A', strtotime($upcoming['start_time'])); ?> - <?php echo date('g:i A', strtotime($upcoming['end_time'])); ?></span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 mb-3">
                        <i class="far fa-building mr-2"></i>
                        <span><?php echo $upcoming['building'] . ' ' . $upcoming['room_name']; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">Panel assigned</div>
                        <button class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">View Details</button>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <!-- Show if no upcoming defenses -->
                <?php if (mysqli_num_rows($upcoming_result) == 0): ?>
                <div class="schedule-card bg-white rounded-lg shadow p-4 border-l-4 border-warning">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg">No Upcoming Defenses</h3>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded">Info</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">There are no defense schedules in the upcoming days.</p>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span>No dates scheduled</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">Schedule a defense now</div>
                        <button onclick="toggleModal()" class="text-xs bg-primary hover:bg-blue-700 text-white px-2 py-1 rounded">Schedule Now</button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Schedule Defense Modal -->
    <div id="proposalModal" class="fixed inset-0 w-full h-full flex items-center justify-center z-50 hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Schedule Defense</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form method="POST" action="defense_scheduling.php">
                    <input type="hidden" name="schedule_defense" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="group_id">
                                Select Group
                            </label>
                            <select id="group_id" name="group_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">-- Select a group --</option>
                                <?php foreach ($groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>"><?php echo $group['name'] . ' - ' . $group['proposal_title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="defense_date">
                                Defense Date
                            </label>
                            <input type="date" id="defense_date" name="defense_date" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="start_time">
                                Start Time
                            </label>
                            <input type="time" id="start_time" name="start_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="end_time">
                                End Time
                            </label>
                            <input type="time" id="end_time" name="end_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="room_id">
                                Venue
                            </label>
                            <select id="room_id" name="room_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">-- Select a room --</option>
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>"><?php echo $room['building'] . ' - ' . $room['room_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="panel_members">
                                Panel Members
                            </label>
                            <select id="panel_members" name="panel_members[]" multiple class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary h-32">
                                <?php foreach ($faculty_members as $faculty): 
                                    $name = '';
                                    if (!empty($faculty['first_name'])) $name .= $faculty['first_name'] . ' ';
                                    if (!empty($faculty['middle_name'])) $name .= substr($faculty['middle_name'], 0, 1) . '. ';
                                    if (!empty($faculty['last_name'])) $name .= $faculty['last_name'];
                                ?>
                                <option value="<?php echo $faculty['user_id']; ?>"><?php echo $name . ' (' . $faculty['role'] . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple panel members</p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
                            Additional Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Any additional information..." class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="toggleModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700">
                            Schedule Defense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden form for deletion -->
    <form id="deleteForm" method="POST" action="defense_scheduling.php" class="hidden">
        <input type="hidden" name="delete_schedule" value="1">
        <input type="hidden" id="defense_id" name="defense_id" value="">
    </form>

</body>
</html>