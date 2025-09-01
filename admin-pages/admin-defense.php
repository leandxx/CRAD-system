<?php
session_start();
include('../includes/connection.php');
include('../includes/notification-helper.php');

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
        $chairperson = isset($_POST['chairperson']) ? $_POST['chairperson'] : '';
        
        // Validate required fields
        if (empty($group_id) || empty($defense_date) || empty($start_time) || empty($end_time) || empty($room_id)) {
            $error_message = "All fields are required for scheduling a defense.";
        } elseif (count($panel_members) != 3) {
            $error_message = "Please select exactly 3 panel members (1 chairperson + 2 members).";
        } elseif (strtotime($defense_date) < strtotime(date('Y-m-d'))) {
            $error_message = "Defense date cannot be in the past.";
        } elseif (strtotime($start_time) >= strtotime($end_time)) {
            $error_message = "End time must be after start time.";
        } else {
            // Auto-calculate end time (30 minutes after start time)
            $start_timestamp = strtotime($start_time);
            $end_timestamp = $start_timestamp + (30 * 60); // Add 30 minutes
            $end_time = date('H:i:s', $end_timestamp);
            
            // Check for exact time conflicts (same room, date, and overlapping times)
            $availability_query = "SELECT ds.*, g.name as group_name 
                                  FROM defense_schedules ds
                                  LEFT JOIN groups g ON ds.group_id = g.id
                                  WHERE ds.room_id = '$room_id' 
                                  AND ds.defense_date = '$defense_date' 
                                  AND ds.status = 'scheduled'
                                  AND (
                                      (ds.start_time < '$end_time' AND ds.end_time > '$start_time')
                                  )";
            $availability_result = mysqli_query($conn, $availability_query);
            
            if (mysqli_num_rows($availability_result) > 0) {
                $conflict = mysqli_fetch_assoc($availability_result);
                $conflict_time = date('g:i A', strtotime($conflict['start_time'])) . ' - ' . date('g:i A', strtotime($conflict['end_time']));
                $error_message = "Room conflict detected! The room is already booked by '{$conflict['group_name']}' from {$conflict_time} on this date. Please choose a different time or room.";
            } else {

        // Default status = scheduled
        $status = 'scheduled';

        // Insert defense schedule
        $schedule_query = "INSERT INTO defense_schedules 
                          (group_id, defense_date, start_time, end_time, room_id, status) 
                          VALUES ('$group_id', '$defense_date', '$start_time', '$end_time', '$room_id', '$status')";

        if (mysqli_query($conn, $schedule_query)) {
            $defense_id = mysqli_insert_id($conn);

            // Insert panel members (first one as chairperson, rest as members)
            foreach ($panel_members as $index => $faculty_id) {
                $faculty_id = mysqli_real_escape_string($conn, $faculty_id);
                $role = ($index === 0) ? 'chairperson' : 'member';
                $panel_query = "INSERT INTO defense_panel (defense_id, faculty_id, role) 
                               VALUES ('$defense_id', '$faculty_id', '$role')";
                mysqli_query($conn, $panel_query);
            }

            // Get group name for notification
            $group_query = "SELECT name FROM groups WHERE id = '$group_id'";
            $group_result = mysqli_query($conn, $group_query);
            $group_data = mysqli_fetch_assoc($group_result);
            $group_name = $group_data['name'];

            // Send notification to all users
            $notification_title = "Defense Scheduled";
            $notification_message = "A defense has been scheduled for group: $group_name on $defense_date at $start_time";
            notifyAllUsers($conn, $notification_title, $notification_message, 'info');

            $_SESSION['success_message'] = "Defense scheduled successfully!";
            $_SESSION['refresh_availability'] = true;
            header("Location: admin-defense.php");
            exit();
        } else {
            $error_message = "Error scheduling defense: " . mysqli_error($conn);
        }
            }
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
        
        // Validate required fields
        if (empty($defense_id) || empty($defense_date) || empty($start_time) || empty($end_time) || empty($room_id)) {
            $error_message = "All fields are required for updating a defense.";
        } elseif (strtotime($defense_date) < strtotime(date('Y-m-d'))) {
            $error_message = "Defense date cannot be in the past.";
        } elseif (strtotime($start_time) >= strtotime($end_time)) {
            $error_message = "End time must be after start time.";
        } else {
            // Auto-calculate end time (30 minutes after start time)
            $start_timestamp = strtotime($start_time);
            $end_timestamp = $start_timestamp + (30 * 60); // Add 30 minutes
            $end_time = date('H:i:s', $end_timestamp);
            
            // Check room availability (exclude current defense from check)
            $availability_query = "SELECT ds.*, g.name as group_name 
                                  FROM defense_schedules ds
                                  LEFT JOIN groups g ON ds.group_id = g.id
                                  WHERE ds.room_id = '$room_id' 
                                  AND ds.defense_date = '$defense_date' 
                                  AND ds.status = 'scheduled'
                                  AND ds.id != '$defense_id'
                                  AND (
                                      (ds.start_time < '$end_time' AND ds.end_time > '$start_time')
                                  )";
            $availability_result = mysqli_query($conn, $availability_query);
            
            if (mysqli_num_rows($availability_result) > 0) {
                $conflict = mysqli_fetch_assoc($availability_result);
                $conflict_time = date('g:i A', strtotime($conflict['start_time'])) . ' - ' . date('g:i A', strtotime($conflict['end_time']));
                $error_message = "Room conflict detected! The room is already booked by '{$conflict['group_name']}' from {$conflict_time} on this date. Please choose a different time or room.";
            } else {

        // Get group_id from form if provided, otherwise keep existing
        $group_id = isset($_POST['group_id']) ? mysqli_real_escape_string($conn, $_POST['group_id']) : null;
        
        // Update defense schedule (include group_id if provided)
        if ($group_id) {
            $update_query = "UPDATE defense_schedules 
                             SET group_id = '$group_id', defense_date = '$defense_date', 
                                 start_time = '$start_time', end_time = '$end_time', 
                                 room_id = '$room_id'
                             WHERE id = '$defense_id'";
        } else {
            $update_query = "UPDATE defense_schedules 
                             SET defense_date = '$defense_date', 
                                 start_time = '$start_time', end_time = '$end_time', 
                                 room_id = '$room_id'
                             WHERE id = '$defense_id'";
        }

        if (mysqli_query($conn, $update_query)) {
            // Update panel members
            $delete_panel_query = "DELETE FROM defense_panel WHERE defense_id = '$defense_id'";
            mysqli_query($conn, $delete_panel_query);

            foreach ($panel_members as $index => $faculty_id) {
                $faculty_id = mysqli_real_escape_string($conn, $faculty_id);
                $role = ($index === 0) ? 'chairperson' : 'member';
                $panel_query = "INSERT INTO defense_panel (defense_id, faculty_id, role) 
                               VALUES ('$defense_id', '$faculty_id', '$role')";
                mysqli_query($conn, $panel_query);
            }

            // Get group name for notification
            $group_query = "SELECT g.name FROM groups g JOIN defense_schedules ds ON g.id = ds.group_id WHERE ds.id = '$defense_id'";
            $group_result = mysqli_query($conn, $group_query);
            $group_data = mysqli_fetch_assoc($group_result);
            $group_name = $group_data['name'];

            // Send notification to all users
            $notification_title = "Defense Schedule Updated";
            $notification_message = "Defense schedule has been updated for group: $group_name on $defense_date at $start_time";
            notifyAllUsers($conn, $notification_title, $notification_message, 'info');

            $_SESSION['success_message'] = "Defense schedule updated successfully!";
            header("Location: admin-defense.php");
            exit();
        } else {
            $error_message = "Error updating defense schedule: " . mysqli_error($conn);
        }
            }
        }
    }
}

// Get all defense schedules
$defense_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, r.room_name, r.building, p.title as proposal_title,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                 FROM defense_schedules ds 
                 LEFT JOIN groups g ON ds.group_id = g.id 
                 LEFT JOIN clusters c ON g.cluster_id = c.id
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
        $panel_query = "SELECT u.user_id, u.email, pm.id as panel_member_id
                   FROM defense_panel dp 
                   JOIN user_tbl u ON dp.faculty_id = u.user_id 
                   LEFT JOIN panel_members pm ON u.user_id = pm.id
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

// Get all groups with completed/approved proposals and check payment status
$groups_query = "SELECT g.*, p.title as proposal_title, c.cluster,
                (SELECT COUNT(*) FROM payments pay 
                 JOIN group_members gm ON pay.student_id = gm.student_id 
                 WHERE gm.group_id = g.id AND pay.payment_type = 'research_forum' AND pay.status = 'approved') as research_forum_payments,
                (SELECT COUNT(*) FROM payments pay 
                 JOIN group_members gm ON pay.student_id = gm.student_id 
                 WHERE gm.group_id = g.id AND pay.payment_type = 'pre_oral_defense' AND pay.status = 'approved') as pre_oral_payments,
                (SELECT COUNT(*) FROM payments pay 
                 JOIN group_members gm ON pay.student_id = gm.student_id 
                 WHERE gm.group_id = g.id AND pay.payment_type = 'final_defense' AND pay.status = 'approved') as final_defense_payments,
                (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as member_count
                FROM groups g 
                JOIN proposals p ON g.id = p.group_id 
                LEFT JOIN clusters c ON g.cluster_id = c.id
                WHERE p.status IN ('Completed', 'Approved')
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
// First, try to get all active panel members
$accepted_panel_query = "SELECT DISTINCT pm.* 
                        FROM panel_members pm 
                        WHERE pm.status = 'active'
                        ORDER BY pm.last_name, pm.first_name";
$accepted_panel_result = mysqli_query($conn, $accepted_panel_query);
$accepted_panel_members = [];

while ($panel_member = mysqli_fetch_assoc($accepted_panel_result)) {
    $accepted_panel_members[] = $panel_member;
}

// If no active panel members found, get all panel members
if (empty($accepted_panel_members)) {
    $all_panel_query = "SELECT * FROM panel_members ORDER BY last_name, first_name";
    $all_panel_result = mysqli_query($conn, $all_panel_query);
    
    while ($panel_member = mysqli_fetch_assoc($all_panel_result)) {
        $accepted_panel_members[] = $panel_member;
    }
}

// Get all rooms
$rooms_query = "SELECT * FROM rooms ORDER BY building, room_name";
$rooms_result = mysqli_query($conn, $rooms_query);
$rooms = [];

while ($room = mysqli_fetch_assoc($rooms_result)) {
    $rooms[] = $room;
}

// Get stats for dashboard
$total_proposals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM proposals WHERE status IN ('Completed', 'Approved')"));
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
        
        .scroll-container {
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }
        .main-content {
            max-height: 100vh;
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .schedule-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .schedule-card:hover::before {
            left: 100%;
        }
        .schedule-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.15);
        }
        .modal {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform: scale(0.95);
            opacity: 0;
        }
        .modal.active {
            transform: scale(1);
            opacity: 1;
        }
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
        .gradient-yellow {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .gradient-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        .defense-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .defense-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .defense-card:hover::before {
            left: 100%;
        }
        .defense-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.15);
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
        .main-tab {
            transition: all 0.3s ease;
        }
        .main-tab:hover {
            color: #3b82f6;
        }
        .main-tab-content {
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

        function toggleModal(groupId = null, groupName = null, program = null, cluster = null) {
            const modal = document.getElementById('proposalModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
            
            if (groupId) {
                document.getElementById('selected_group_id').value = groupId;
                const displayText = `${groupName} - ${program} ${cluster ? '(Cluster ' + cluster + ')' : ''}`;
                document.getElementById('selected_group_name').textContent = displayText;
            }
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
        
        // Function to switch between main tabs
        function switchMainTab(tabName) {
            // Update active tab
            document.querySelectorAll('.main-tab').forEach(tab => {
                if (tab.id === tabName + 'Tab') {
                    tab.classList.add('text-primary', 'border-primary');
                    tab.classList.remove('text-gray-500', 'border-transparent');
                } else {
                    tab.classList.remove('text-primary', 'border-primary');
                    tab.classList.add('text-gray-500', 'border-transparent');
                }
            });
            
            // Show active content
            if (tabName === 'schedules') {
                document.getElementById('schedulesContent').classList.remove('hidden');
                document.getElementById('availabilityContent').classList.add('hidden');
                document.getElementById('scheduleCards').classList.remove('hidden');
                document.getElementById('availabilityCards').classList.add('hidden');
            } else if (tabName === 'availability') {
                document.getElementById('schedulesContent').classList.add('hidden');
                document.getElementById('availabilityContent').classList.remove('hidden');
                document.getElementById('scheduleCards').classList.add('hidden');
                document.getElementById('availabilityCards').classList.remove('hidden');
                // Load room availability immediately when tab is clicked
                checkRoomAvailability();
            }
        }
        
        // Function to automatically set end time to 30 minutes after start time
        function setEndTime() {
            const startTime = document.getElementById('start_time').value;
            if (startTime) {
                const startDate = new Date('1970-01-01T' + startTime + ':00');
                const endDate = new Date(startDate.getTime() + 30 * 60000); // Add 30 minutes
                const endTime = endDate.toTimeString().slice(0, 5);
                document.getElementById('end_time').value = endTime;
            }
        }
        
        // Function to automatically set end time for edit modal
        function setEditEndTime() {
            const startTime = document.getElementById('edit_start_time').value;
            if (startTime) {
                const startDate = new Date('1970-01-01T' + startTime + ':00');
                const endDate = new Date(startDate.getTime() + 30 * 60000); // Add 30 minutes
                const endTime = endDate.toTimeString().slice(0, 5);
                document.getElementById('edit_end_time').value = endTime;
            }
        }
        
        // Function to check room availability
        function checkRoomAvailability() {
            const selectedDate = document.getElementById('availabilityDate').value;
            if (!selectedDate) return;
            
            // Update selected date display
            const dateObj = new Date(selectedDate);
            const formattedDate = dateObj.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            document.getElementById('selectedDate').textContent = formattedDate;
            
            // Show loading state
            document.getElementById('roomAvailabilityGrid').innerHTML = 
                '<div class="col-span-3 text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i><p class="text-gray-500">Loading room availability...</p></div>';
            
            // Fetch room availability via AJAX
            fetch('admin-pages/get_room_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'date=' + encodeURIComponent(selectedDate)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                displayRoomAvailability(data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('roomAvailabilityGrid').innerHTML = 
                    '<div class="col-span-3 text-center py-8"><p class="text-red-500">Error: ' + error.message + '</p></div>';
            });
        }
        
        // Function to display room availability
        function displayRoomAvailability(rooms) {
            const grid = document.getElementById('roomAvailabilityGrid');
            
            if (!rooms || rooms.length === 0) {
                grid.innerHTML = '<div class="col-span-3 text-center py-8"><p class="text-gray-500">No rooms found</p></div>';
                return;
            }
            
            let html = '';
            rooms.forEach(room => {
                const hasSchedules = room.schedules && room.schedules.length > 0;
                const statusClass = hasSchedules ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
                
                let statusText = 'Available all day';
                if (hasSchedules) {
                    const firstSchedule = room.schedules[0];
                    const lastSchedule = room.schedules[room.schedules.length - 1];
                    statusText = `Available before ${firstSchedule.start_time} and after ${lastSchedule.end_time}`;
                }
                
                const statusIcon = hasSchedules ? 'fa-times-circle' : 'fa-check-circle';
                
                html += `
                    <div class="bg-white border border-gray-200 rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-door-open text-gray-400 mr-2"></i>
                                    <h3 class="text-lg font-semibold text-gray-900">${room.room_name}</h3>
                                </div>
                                <div class="flex items-center text-sm text-gray-500 mb-1">
                                    <i class="fas fa-building text-gray-400 mr-2"></i>
                                    <span>${room.building}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-users text-gray-400 mr-2"></i>
                                    <span>Capacity: ${room.capacity || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="px-3 py-1 text-xs font-medium rounded-full ${statusClass} flex items-center">
                                    <i class="fas ${statusIcon} mr-1"></i>
                                    ${statusText}
                                </span>
                            </div>
                        </div>
                        
                        <div class="border-t pt-3">
                            ${hasSchedules ? `
                                <div class="space-y-1">
                                    ${room.schedules.map(schedule => `
                                        <div class="text-xs bg-red-50 rounded p-2">
                                            <span class="text-red-600">Occupied ${schedule.start_time} - ${schedule.end_time} (${schedule.group_name})</span>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : `
                                <div class="flex items-center text-sm text-green-600">
                                    <i class="fas fa-calendar-check text-green-500 mr-2"></i>
                                    <span>Available all day</span>
                                </div>
                            `}
                        </div>
                    </div>
                `;
            });
            
            grid.innerHTML = html;
        }
        

    </script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 font-sans">

    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        
        <!-- Main content area -->
        <main class="flex-1 p-6 main-content">
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-slide-up">
                <div class="stats-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Total Proposals</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_proposals; ?></h3>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                            <div class="gradient-blue h-1.5 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="gradient-blue p-4 rounded-2xl shadow-lg">
                        <i class="fas fa-file-alt text-white text-2xl"></i>
                    </div>
                </div>
                <div class="stats-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Scheduled</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $scheduled_defenses; ?></h3>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                            <div class="gradient-green h-1.5 rounded-full" style="width: <?php echo $total_proposals > 0 ? ($scheduled_defenses / $total_proposals * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="gradient-green p-4 rounded-2xl shadow-lg">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                </div>
                <div class="stats-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $pending_defenses; ?></h3>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                            <div class="gradient-yellow h-1.5 rounded-full" style="width: <?php echo $total_proposals > 0 ? ($pending_defenses / $total_proposals * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="gradient-yellow p-4 rounded-2xl shadow-lg">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                </div>
                <div class="stats-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 font-medium text-sm uppercase tracking-wide">Completed</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $completed_defenses; ?></h3>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                            <div class="gradient-purple h-1.5 rounded-full" style="width: <?php echo $total_proposals > 0 ? ($completed_defenses / $total_proposals * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="gradient-purple p-4 rounded-2xl shadow-lg">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Main Tabs -->
            <div class="stats-card rounded-2xl mb-8 p-6 animate-fade-in">
                <div class="flex flex-col gap-6">
                    <!-- Tab Navigation -->
                    <div class="flex border-b border-gray-200">
                        <button onclick="switchMainTab('schedules')" id="schedulesTab" class="main-tab px-6 py-3 font-semibold text-primary border-b-2 border-primary">Defense Schedules</button>
                        <button onclick="switchMainTab('availability')" id="availabilityTab" class="main-tab px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:text-primary">Room Availability</button>
                    </div>
                    
                    <!-- Schedules Tab Content -->
                    <div id="schedulesContent" class="main-tab-content">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="flex flex-wrap gap-3">
                                <button onclick="filterStatus('all')" data-filter="all" class="filter-btn px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold transition-all hover:scale-105">All</button>
                                <button onclick="filterStatus('scheduled')" data-filter="scheduled" class="filter-btn px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold transition-all hover:scale-105 hover:bg-gray-200">Scheduled</button>
                                <button onclick="filterStatus('pending')" data-filter="pending" class="filter-btn px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold transition-all hover:scale-105 hover:bg-gray-200">Pending</button>
                                <button onclick="filterStatus('completed')" data-filter="completed" class="filter-btn px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold transition-all hover:scale-105 hover:bg-gray-200">Completed</button>
                            </div>
                            <div class="flex gap-3">
                                <div class="relative">
                                    <input type="text" id="searchInput" placeholder="Search proposals..." onkeyup="handleSearch()" class="pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <button onclick="toggleModal()" class="gradient-blue text-white px-6 py-3 rounded-xl flex items-center font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i> Schedule Defense
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Room Availability Tab Content -->
                    <div id="availabilityContent" class="main-tab-content hidden">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="flex items-center gap-4">
                                <div class="gradient-green p-3 rounded-xl">
                                    <i class="fas fa-door-open text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">Room Availability</h3>
                            </div>
                            <div class="flex gap-3">
                                <input type="date" id="availabilityDate" value="<?php echo date('Y-m-d'); ?>" onchange="checkRoomAvailability()" class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                <button onclick="checkRoomAvailability()" class="gradient-green text-white px-6 py-3 rounded-xl flex items-center font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105">
                                    <i class="fas fa-search mr-2"></i> Check Availability
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Defense Schedule Cards -->
            <div id="scheduleCards" class="stats-card rounded-2xl p-8 animate-scale-in">
                <div class="flex items-center mb-8">
                    <div class="gradient-blue p-3 rounded-xl mr-4">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Defense Schedule</h2>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($defense_schedules as $schedule): ?>
                    <!-- Scheduled Defense Card -->
                    <div class="defense-card bg-white border border-gray-200 rounded-xl shadow-md p-5 flex flex-col justify-between" 
                         data-status="<?php echo $schedule['status']; ?>" 
                         data-defense-id="<?php echo $schedule['id']; ?>">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo $schedule['proposal_title'] ?? 'No Title'; ?></h3>
                            <p class="text-sm text-gray-500 mb-3"><?php echo $schedule['program'] . ($schedule['cluster'] ? ' - Cluster ' . $schedule['cluster'] : '') . ' - ' . $schedule['group_name']; ?></p>

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
                            
                            <div class="flex space-x-2">
                                <button class="text-indigo-600 hover:text-indigo-900 p-1" onclick="populateEditForm(<?php echo htmlspecialchars(json_encode($schedule)); ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmDelete(<?php echo $schedule['id']; ?>, '<?php echo $schedule['group_name']; ?>')" class="text-red-600 hover:text-red-900 p-1" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Pending / Unscheduled Groups by Cluster -->
                    <?php 
                    $unscheduled_query = "SELECT g.*, p.title as proposal_title, c.cluster
                                        FROM groups g 
                                        JOIN proposals p ON g.id = p.group_id 
                                        LEFT JOIN clusters c ON g.cluster_id = c.id
                                        WHERE g.id NOT IN (SELECT group_id FROM defense_schedules) 
                                        AND p.status IN ('Completed', 'Approved')
                                        ORDER BY g.program, c.cluster, g.name";
                    $unscheduled_result = mysqli_query($conn, $unscheduled_query);
                    $unscheduled_groups = [];
                    
                    while ($group = mysqli_fetch_assoc($unscheduled_result)) {
                        $cluster_key = $group['program'] . ' - Cluster ' . ($group['cluster'] ?? 'Unassigned');
                        $unscheduled_groups[$cluster_key][] = $group;
                    }
                    
                    foreach ($unscheduled_groups as $cluster_name => $cluster_groups): ?>
                    <div class="col-span-full mb-6">
                        <div class="cluster-header cursor-pointer bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-4 mb-4 hover:shadow-md transition-all" onclick="toggleDefenseCluster('<?php echo md5($cluster_name); ?>')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-layer-group text-yellow-600 text-xl mr-3"></i>
                                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($cluster_name); ?></h3>
                                    <span class="ml-4 bg-yellow-500 text-white text-sm font-bold px-3 py-2 rounded-xl shadow-lg">
                                        <?php echo count($cluster_groups); ?> pending
                                    </span>
                                </div>
                                <i class="fas fa-chevron-down text-yellow-600 transition-transform" id="defense-chevron-<?php echo md5($cluster_name); ?>"></i>
                            </div>
                        </div>
                        
                        <div class="cluster-content hidden ml-8" id="defense-cluster-<?php echo md5($cluster_name); ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($cluster_groups as $group): ?>
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
                                        <button onclick="toggleModal(<?php echo $group['id']; ?>, '<?php echo addslashes($group['name']); ?>', '<?php echo $group['program']; ?>', '<?php echo $group['cluster'] ?? ''; ?>')" class="text-primary hover:text-blue-900 mr-3">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Room Availability Cards -->
            <div id="availabilityCards" class="stats-card rounded-2xl p-8 animate-scale-in hidden">
                <div class="flex items-center mb-8">
                    <div class="gradient-green p-3 rounded-xl mr-4">
                        <i class="fas fa-door-open text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Room Availability</h2>
                    <span id="selectedDate" class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full"><?php echo date('M j, Y'); ?></span>
                </div>
                
                <div id="roomAvailabilityGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Room availability cards will be populated here -->
                </div>
            </div>

            <!-- Upcoming Defenses Section -->
            <div class="flex items-center mt-10 mb-6">
                <div class="gradient-green p-3 rounded-xl mr-4">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Upcoming Defenses</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in">
                <?php 
                $upcoming_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, p.title as proposal_title, r.room_name, r.building 
                                FROM defense_schedules ds 
                                JOIN groups g ON ds.group_id = g.id 
                                LEFT JOIN clusters c ON g.cluster_id = c.id
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
                    <p class="text-gray-600 text-sm mb-3"><?php echo $upcoming['program'] . ($upcoming['cluster'] ? ' - Cluster ' . $upcoming['cluster'] : '') . ' - ' . $upcoming['group_name']; ?></p>
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
                <input type="hidden" name="group_id" id="selected_group_id" required>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Selected Group</label>
                    <div class="px-3 py-2 bg-gray-100 rounded-lg">
                        <p id="selected_group_name" class="font-medium text-gray-800"></p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-medium mb-1">Defense Type</label>
                    <select id="defense_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                        <option value="pre_oral">Pre-Oral Defense</option>
                        <option value="final">Final Defense</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="defense_date">Defense Date</label>
                        <input type="date" name="defense_date" id="defense_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
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
                        <input type="time" name="start_time" id="start_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="setEndTime()">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="end_time">End Time (Auto-set to 30 mins)</label>
                        <input type="time" name="end_time" id="end_time" required readonly class="w-full px-3 py-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Panel Members</label>
                    
                    <!-- Panel Tabs -->
                    <div class="panel-tabs mb-3">
                        <div class="panel-tab active" data-tab="chairperson" onclick="switchPanelTab('chairperson')">Chairperson</div>
                        <div class="panel-tab" data-tab="member" onclick="switchPanelTab('member')">Members</div>
                    </div>
                    
                    <!-- Chairperson Panel Content -->
                    <div class="panel-content active" data-tab="chairperson">
                        <?php 
                        $chairpersons = array_filter($accepted_panel_members, function($member) {
                            return $member['role'] === 'chairperson';
                        });
                        if (!empty($chairpersons)): ?>
                        <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg bg-gray-50">
                            <?php foreach ($chairpersons as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>" class="mr-3 rounded text-primary focus:ring-primary panel-checkbox" data-role="chairperson" onchange="validatePanelSelection()">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <?php if (!empty($panel_member['specialization'])): ?>
                                    <div class="text-xs text-blue-600 mt-1"><?php echo $panel_member['specialization']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select exactly 1 chairperson for this defense schedule.</p>
                        <?php else: ?>
                        <div class="text-center p-6 border rounded-lg bg-gray-50">
                            <i class="fas fa-user-tie text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No chairpersons found.</p>
                            <p class="text-xs text-gray-400">Please add chairpersons in the Panel Assignment section first.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Members Panel Content -->
                    <div class="panel-content" data-tab="member">
                        <?php 
                        $members = array_filter($accepted_panel_members, function($member) {
                            return $member['role'] === 'member';
                        });
                        if (!empty($members)): ?>
                        <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg bg-gray-50">
                            <?php foreach ($members as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>" class="mr-3 rounded text-primary focus:ring-primary panel-checkbox" data-role="member" onchange="validatePanelSelection()">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <?php if (!empty($panel_member['specialization'])): ?>
                                    <div class="text-xs text-blue-600 mt-1"><?php echo $panel_member['specialization']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select exactly 2 panel members for this defense schedule.</p>
                        <?php else: ?>
                        <div class="text-center p-6 border rounded-lg bg-gray-50">
                            <i class="fas fa-users text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No panel members found.</p>
                            <p class="text-xs text-gray-400">Please add panel members in the Panel Assignment section first.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="toggleModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" name="schedule_defense" id="schedule_btn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 flex items-center opacity-50 cursor-not-allowed" disabled>
                        <i class="fas fa-calendar-plus mr-2"></i>Schedule Defense
                    </button>
                </div>
                <div id="payment_warning" class="mt-3 p-3 bg-yellow-100 border border-yellow-300 rounded-lg text-yellow-800 text-sm hidden">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span id="warning_text"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Defense Modal -->
    <div id="editDefenseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
         <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl h-[85vh] flex flex-col mx-auto my-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center flex-shrink-0">
                <h3 class="text-lg font-semibold">Edit Defense Schedule</h3>
                <button onclick="toggleEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto">
            <form method="POST" action="" class="p-6" id="editForm">
                <input type="hidden" name="defense_id" id="edit_defense_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_group_id">Group</label>
                    <select name="group_id" id="edit_group_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select a group</option>
                        <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['program'] . ($group['cluster'] ? ' - Cluster ' . $group['cluster'] : '') . ' - ' . $group['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_defense_date">Defense Date</label>
                        <input type="date" name="defense_date" id="edit_defense_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
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
                        <input type="time" name="start_time" id="edit_start_time" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="setEditEndTime()">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_end_time">End Time (Auto-set to 30 mins)</label>
                        <input type="time" name="end_time" id="edit_end_time" required readonly class="w-full px-3 py-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Panel Members</label>
                    
                    <!-- Panel Tabs -->
                    <div class="panel-tabs mb-3">
                        <div class="panel-tab active" data-tab="edit_chairperson" onclick="switchEditPanelTab('edit_chairperson')">Chairperson</div>
                        <div class="panel-tab" data-tab="edit_member" onclick="switchEditPanelTab('edit_member')">Members</div>
                    </div>
                    
                    <!-- Edit Chairperson Panel Content -->
                    <div class="panel-content active" data-tab="edit_chairperson">
                        <?php 
                        $chairpersons = array_filter($accepted_panel_members, function($member) {
                            return $member['role'] === 'chairperson';
                        });
                        if (!empty($chairpersons)): ?>
                        <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-scroll p-2 border rounded-lg bg-gray-50">
                            <?php foreach ($chairpersons as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>" class="edit-panel-member mr-3 rounded text-primary focus:ring-primary" data-id="<?php echo $panel_member['id']; ?>">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <?php if (!empty($panel_member['specialization'])): ?>
                                    <div class="text-xs text-blue-600 mt-1"><?php echo $panel_member['specialization']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select chairperson for this defense schedule.</p>
                        <?php else: ?>
                        <div class="text-center p-6 border rounded-lg bg-gray-50">
                            <i class="fas fa-user-tie text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No chairpersons found.</p>
                            <p class="text-xs text-gray-400">Please add chairpersons in the Panel Assignment section first.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Edit Members Panel Content -->
                    <div class="panel-content" data-tab="edit_member">
                        <?php 
                        $members = array_filter($accepted_panel_members, function($member) {
                            return $member['role'] === 'member';
                        });
                        if (!empty($members)): ?>
                        <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-scroll p-2 border rounded-lg bg-gray-50">
                            <?php foreach ($members as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>" class="edit-panel-member mr-3 rounded text-primary focus:ring-primary" data-id="<?php echo $panel_member['id']; ?>">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <?php if (!empty($panel_member['specialization'])): ?>
                                    <div class="text-xs text-blue-600 mt-1"><?php echo $panel_member['specialization']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select panel members for this defense schedule.</p>
                        <?php else: ?>
                        <div class="text-center p-6 border rounded-lg bg-gray-50">
                            <i class="fas fa-users text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No panel members found.</p>
                            <p class="text-xs text-gray-400">Please add panel members in the Panel Assignment section first.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t mt-6 px-6 pb-6">
                    <button type="button" onclick="toggleEditModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" name="edit_defense" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-save mr-2"></i>Update Schedule
                    </button>
                </div>
            </form>
            </div>
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
    try {
        document.getElementById('edit_defense_id').value = schedule.id;
        document.getElementById('edit_group_id').value = schedule.group_id || '';
        document.getElementById('edit_defense_date').value = schedule.defense_date;
        document.getElementById('edit_room_id').value = schedule.room_id || '';
        document.getElementById('edit_start_time').value = schedule.start_time;
        document.getElementById('edit_end_time').value = schedule.end_time;
        
        // Clear all checkboxes first
        document.querySelectorAll('.edit-panel-member').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Check the panel members for this defense
        // The panel members might be stored differently in the schedule object
        if (schedule.panel_members && schedule.panel_members.length > 0) {
            schedule.panel_members.forEach(panel => {
                // Try different possible ID fields
                const panelId = panel.user_id || panel.id || panel.faculty_id;
                if (panelId) {
                    const checkbox = document.querySelector(`.edit-panel-member[value="${panelId}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                }
            });
        }
        
        toggleEditModal();
    } catch (error) {
        console.error('Error populating edit form:', error);
        alert('Error loading defense data for editing.');
    }
}
        
        // Function to switch between panel member tabs in edit modal
        function switchEditPanelTab(tabName) {
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
        
        // Validate panel selection (1 chairperson + 2 members = 3 total)
        function validatePanelSelection() {
            const chairpersonBoxes = document.querySelectorAll('.panel-checkbox[data-role="chairperson"]:checked');
            const memberBoxes = document.querySelectorAll('.panel-checkbox[data-role="member"]:checked');
            const scheduleBtn = document.getElementById('schedule_btn');
            
            const chairpersonCount = chairpersonBoxes.length;
            const memberCount = memberBoxes.length;
            const totalCount = chairpersonCount + memberCount;
            
            // Disable other chairperson checkboxes if one is selected
            const allChairpersonBoxes = document.querySelectorAll('.panel-checkbox[data-role="chairperson"]');
            allChairpersonBoxes.forEach(box => {
                if (!box.checked && chairpersonCount >= 1) {
                    box.disabled = true;
                    box.parentElement.style.opacity = '0.5';
                } else if (chairpersonCount < 1) {
                    box.disabled = false;
                    box.parentElement.style.opacity = '1';
                }
            });
            
            // Disable other member checkboxes if two are selected
            const allMemberBoxes = document.querySelectorAll('.panel-checkbox[data-role="member"]');
            allMemberBoxes.forEach(box => {
                if (!box.checked && memberCount >= 2) {
                    box.disabled = true;
                    box.parentElement.style.opacity = '0.5';
                } else if (memberCount < 2) {
                    box.disabled = false;
                    box.parentElement.style.opacity = '1';
                }
            });
            
            // Enable/disable schedule button based on selection
            if (totalCount === 3 && chairpersonCount === 1 && memberCount === 2) {
                scheduleBtn.disabled = false;
                scheduleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                scheduleBtn.disabled = true;
                scheduleBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
        
        // Enable schedule button when modal opens with group selected
        function enableScheduleButton() {
            validatePanelSelection();
        }
        
        // Toggle defense cluster visibility
        function toggleDefenseCluster(clusterId) {
            const content = document.getElementById('defense-cluster-' + clusterId);
            const chevron = document.getElementById('defense-chevron-' + clusterId);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        }
        
        // Initialize page on load
        document.addEventListener('DOMContentLoaded', function() {
            // Set up auto end time calculation for new schedule form
            document.getElementById('start_time').addEventListener('change', setEndTime);
            
            // Initialize edit modal tabs on page load
            // Set default filter to 'all'
            filterStatus('all');
            
            // Set default panel tab to 'chairperson'
            switchPanelTab('chairperson');
            
            // Edit modal default to chairperson tab
            switchEditPanelTab('edit_chairperson');
            
            // Validate panel selection on load
            validatePanelSelection();
            
            // Check if we need to refresh room availability
            <?php if (isset($_SESSION['refresh_availability'])): ?>
            // Switch to availability tab and refresh
            switchMainTab('availability');
            <?php unset($_SESSION['refresh_availability']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>