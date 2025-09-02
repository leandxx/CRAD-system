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
        $defense_type = mysqli_real_escape_string($conn, $_POST['defense_type'] ?? 'initial');
        $parent_defense_id = !empty($_POST['parent_defense_id']) ? mysqli_real_escape_string($conn, $_POST['parent_defense_id']) : 'NULL';
        $redefense_reason = !empty($_POST['redefense_reason']) ? mysqli_real_escape_string($conn, $_POST['redefense_reason']) : 'NULL';
        
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
                          (group_id, defense_date, start_time, end_time, room_id, status, defense_type, parent_defense_id, redefense_reason) 
                          VALUES ('$group_id', '$defense_date', '$start_time', '$end_time', '$room_id', '$status', '$defense_type', $parent_defense_id, '$redefense_reason')";

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
            } else {
                $error_message = "Error scheduling defense: " . mysqli_error($conn);
            }
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

        }
        
        if (!isset($error_message)) {
            $_SESSION['success_message'] = "Defense scheduled successfully!";
            $_SESSION['refresh_availability'] = true;
            header("Location: admin-defense.php");
            exit();
        }
            }
        }

    if (isset($_POST['mark_failed'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $update_query = "UPDATE defense_schedules SET status = 'failed' WHERE id = '$defense_id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success_message'] = "Defense marked as failed. You can now schedule a redefense.";
        } else {
            $error_message = "Error updating defense status: " . mysqli_error($conn);
        }
        header("Location: admin-defense.php");
        exit();
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

    if (isset($_POST['complete_defense'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $result = mysqli_real_escape_string($conn, $_POST['result']); // 'pass' or 'fail'
        
        // Get defense info
        $defense_info_query = "SELECT ds.*, g.name as group_name FROM defense_schedules ds 
                              JOIN groups g ON ds.group_id = g.id WHERE ds.id = '$defense_id'";
        $defense_info = mysqli_fetch_assoc(mysqli_query($conn, $defense_info_query));
        
        if ($result === 'pass') {
            if ($defense_info['defense_type'] === 'pre_oral') {
                // Update status to ready for final defense
                $update_query = "UPDATE defense_schedules SET status = 'completed' WHERE id = '$defense_id'";
                mysqli_query($conn, $update_query);
                $_SESSION['success_message'] = "Pre-oral defense completed. Group is now ready for final defense.";
            } else {
                // Final defense passed - mark as completed
                $update_query = "UPDATE defense_schedules SET status = 'completed' WHERE id = '$defense_id'";
                mysqli_query($conn, $update_query);
                $_SESSION['success_message'] = "Final defense completed successfully!";
            }
        } else {
            // Failed - mark for re-defense
            $update_query = "UPDATE defense_schedules SET status = 're_defense' WHERE id = '$defense_id'";
            mysqli_query($conn, $update_query);
            $_SESSION['success_message'] = "Defense marked for re-defense.";
        }
        
        header("Location: admin-defense.php");
        exit();
    }

    if (isset($_POST['edit_defense'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $defense_date = mysqli_real_escape_string($conn, $_POST['defense_date']);
        $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
        $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
        $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
        $panel_members = isset($_POST['panel_members']) ? $_POST['panel_members'] : [];
        
        // Validate required fields
        if (strtotime($defense_date) < strtotime(date('Y-m-d'))) {
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

// Auto-update finished defenses based on defense type
mysqli_query($conn, "UPDATE defense_schedules SET status = 'proceeding_final' WHERE status = 'scheduled' AND defense_type = 'pre_oral' AND (defense_date < CURDATE() OR (defense_date = CURDATE() AND end_time < CURTIME()))");
mysqli_query($conn, "UPDATE defense_schedules SET status = 'awaiting_result' WHERE status = 'scheduled' AND defense_type = 'final' AND (defense_date < CURDATE() OR (defense_date = CURDATE() AND end_time < CURTIME()))");

// Get upcoming defense schedules organized by program and cluster
$defense_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, r.room_name, r.building, p.title as proposal_title,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                 FROM defense_schedules ds 
                 LEFT JOIN groups g ON ds.group_id = g.id 
                 LEFT JOIN clusters c ON g.cluster_id = c.id
                 LEFT JOIN rooms r ON ds.room_id = r.id 
                 LEFT JOIN proposals p ON g.id = p.group_id
                 LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                 LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                 WHERE ds.status IN ('scheduled', 're_defense', 'proceeding_final', 'awaiting_result')
                 GROUP BY ds.id
                 ORDER BY g.program, c.cluster, g.name, ds.defense_date, ds.start_time";
$defense_result = mysqli_query($conn, $defense_query);
$defense_schedules = [];
$organized_schedules = [];

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

    // Auto-update finished defenses based on type
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    if ($schedule['status'] == 'scheduled' && 
        ($schedule['defense_date'] < $current_date || 
        ($schedule['defense_date'] == $current_date && $schedule['end_time'] < $current_time))) {
        if ($schedule['defense_type'] == 'pre_oral') {
            $update_query = "UPDATE defense_schedules SET status = 'proceeding_final' WHERE id = '{$schedule['id']}'";
            $schedule['status'] = 'proceeding_final';
        } else {
            $update_query = "UPDATE defense_schedules SET status = 'awaiting_result' WHERE id = '{$schedule['id']}'";
            $schedule['status'] = 'awaiting_result';
        }
        mysqli_query($conn, $update_query);
    }

   $schedule['panel_members'] = $panel_members;
    $defense_schedules[] = $schedule;
    
    // Organize by program -> cluster -> group
    $program = $schedule['program'];
    $cluster = $schedule['cluster'] ?? 'Unassigned';
    $organized_schedules[$program][$cluster][] = $schedule;
}

// Get all groups with completed/approved proposals and check payment status (exclude already scheduled groups)
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
                LEFT JOIN defense_schedules ds ON g.id = ds.group_id AND ds.status IN ('scheduled', 'completed')
                WHERE p.status IN ('Completed', 'Approved', 'approved', 'completed')
                AND ds.id IS NULL
                ORDER BY g.name";

// Get all groups for edit modal (including scheduled ones)
$all_groups_query = "SELECT g.*, p.title as proposal_title, c.cluster
                    FROM groups g 
                    JOIN proposals p ON g.id = p.group_id 
                    LEFT JOIN clusters c ON g.cluster_id = c.id
                    WHERE p.status IN ('Completed', 'Approved', 'approved', 'completed')
                    ORDER BY g.name";
$all_groups_result = mysqli_query($conn, $all_groups_query);
$all_groups = [];

while ($group = mysqli_fetch_assoc($all_groups_result)) {
    $all_groups[] = $group;
}
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
$total_proposals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM proposals WHERE status IN ('Completed', 'Approved', 'approved', 'completed')"));
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
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
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
        .modal-overlay {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
            backdrop-filter: blur(4px);
            transition: all 300ms ease-in-out;
        }
        .modal-content {
            transform: translateY(-30px) scale(0.95);
            transition: all 300ms cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .modal-active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content-active {
            transform: translateY(0) scale(1);
        }
        .custom-scrollbar-blue::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-blue::-webkit-scrollbar-track {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-blue::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-blue::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.6);
        }
        .custom-scrollbar-indigo::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-indigo::-webkit-scrollbar-track {
            background: rgba(99, 102, 241, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-indigo::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-indigo::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.6);
        }
        .custom-scrollbar-green::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-green::-webkit-scrollbar-track {
            background: rgba(34, 197, 94, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-green::-webkit-scrollbar-thumb {
            background: rgba(34, 197, 94, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-green::-webkit-scrollbar-thumb:hover {
            background: rgba(34, 197, 94, 0.6);
        }
        .custom-scrollbar-red::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar-red::-webkit-scrollbar-track {
            background: rgba(239, 68, 68, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar-red::-webkit-scrollbar-thumb {
            background: rgba(239, 68, 68, 0.4);
            border-radius: 10px;
        }
        .custom-scrollbar-red::-webkit-scrollbar-thumb:hover {
            background: rgba(239, 68, 68, 0.6);
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

        // ===== MODAL FUNCTIONS =====
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('modal-active');

                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    setTimeout(() => {
                        modalContent.classList.add('modal-content-active');
                    }, 10);
                }
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.classList.remove('modal-content-active');
            }

            setTimeout(() => {
                modal.classList.remove('modal-active');
                modal.classList.add('opacity-0', 'pointer-events-none');
            }, 200);
        }

        function toggleModal() {
            const modal = document.getElementById('proposalModal');
            if (modal.classList.contains('opacity-0')) {
                openModal('proposalModal');
            } else {
                closeModal('proposalModal');
            }
        }

        function toggleEditModal() {
            const modal = document.getElementById('editDefenseModal');
            if (modal.classList.contains('opacity-0')) {
                openModal('editDefenseModal');
            } else {
                closeModal('editDefenseModal');
            }
        }
        
        function toggleDetailsModal() {
            const modal = document.getElementById('detailsModal');
            if (modal.classList.contains('opacity-0')) {
                openModal('detailsModal');
            } else {
                closeModal('detailsModal');
            }
        }

        // ===== CLICK OUTSIDE TO CLOSE =====
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                closeModal(event.target.id);
            }
        });

        // ===== ESC KEY TO CLOSE =====
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    if (!modal.classList.contains('opacity-0')) {
                        closeModal(modal.id);
                    }
                });
            }
        });

        // ===== DELETE MODAL =====
        function showDeleteModal(defenseId, groupName) {
            document.getElementById('defense_id').value = defenseId;
            openModal('deleteConfirmModal');
        }

        // Make function globally accessible
        window.showDeleteModal = showDeleteModal;
        window.confirmDelete = confirmDelete;
        window.openModal = openModal;
        window.closeModal = closeModal;
        
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
            showDeleteModal(defenseId, groupName);
        }
        
        // Function to mark defense as failed
        function markFailed(defenseId) {
            if (confirm('Mark this defense as failed? This will allow scheduling a redefense.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="defense_id" value="${defenseId}"><input type="hidden" name="mark_failed" value="1">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Function to schedule redefense
        function scheduleRedefense(groupId, parentDefenseId) {
            document.getElementById('defense_type').value = 'redefense';
            document.getElementById('parent_defense_id').value = parentDefenseId;
            document.getElementById('group_id').value = groupId;
            document.getElementById('group_id').disabled = true;
            document.getElementById('redefense_reason_div').classList.remove('hidden');
            document.querySelector('#proposalModal h3').textContent = 'Schedule Redefense';
            toggleModal();
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
                // Load room availability immediately when tab is clicked
                checkRoomAvailability();
            }
        }
        
        // Function to validate defense duration (minimum 30 minutes)
        function validateDefenseDuration() {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime && endTime) {
                const start = new Date('1970-01-01T' + startTime);
                const end = new Date('1970-01-01T' + endTime);
                const duration = (end - start) / (1000 * 60); // minutes
                
                if (duration < 30) {
                    alert('Defense duration must be at least 30 minutes.');
                    return false;
                }
                
                // Suggest optimal end time if duration is not in 30-minute increments
                if (duration % 30 !== 0) {
                    const optimalDuration = Math.ceil(duration / 30) * 30;
                    const optimalEnd = new Date(start.getTime() + optimalDuration * 60000);
                    const optimalEndTime = optimalEnd.toTimeString().slice(0, 5);
                    
                    if (confirm(`For optimal room usage, consider extending to ${optimalEndTime} (${optimalDuration} minutes total). Update end time?`)) {
                        document.getElementById('end_time').value = optimalEndTime;
                    }
                }
            }
            return true;
        }
        
        // Function to populate available time slots
        function populateTimeSlots() {
            const roomId = document.getElementById('room_id').value;
            const date = document.getElementById('defense_date').value;
            const timeSlotSelect = document.getElementById('time_slot');
            
            if (!roomId || !date) {
                timeSlotSelect.innerHTML = '<option value="">Select date and room first</option>';
                return;
            }
            
            timeSlotSelect.innerHTML = '<option value="">Loading available slots...</option>';
            
            fetch('get_room_availability.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `date=${encodeURIComponent(date)}&room_id=${encodeURIComponent(roomId)}`
            })
            .then(response => response.json())
            .then(data => {
                const room = data.find(r => r.id == roomId);
                const slots = generateTimeSlots(room ? room.schedules : []);
                
                timeSlotSelect.innerHTML = '<option value="">Select a time slot</option>';
                slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = `${slot.start}|${slot.end}`;
                    option.textContent = `${slot.start} - ${slot.end} (${slot.duration} min)`;
                    timeSlotSelect.appendChild(option);
                });
                
                if (slots.length === 0) {
                    timeSlotSelect.innerHTML = '<option value="">No available slots</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                timeSlotSelect.innerHTML = '<option value="">Error loading slots</option>';
            });
        }
        
        // Function to generate available time slots
        function generateTimeSlots(schedules) {
            const slots = [];
            const workStart = 9 * 60; // 9:00 AM
            const workEnd = 17 * 60;  // 5:00 PM
            
            // Sort schedules by start time
            schedules.sort((a, b) => {
                const timeA = timeToMinutes(a.start_time);
                const timeB = timeToMinutes(b.start_time);
                return timeA - timeB;
            });
            
            let currentTime = workStart;
            
            schedules.forEach(schedule => {
                const startMinutes = timeToMinutes(schedule.start_time);
                const endMinutes = timeToMinutes(schedule.end_time);
                
                // Add slots before this schedule
                while (currentTime + 30 <= startMinutes) {
                    const slotEnd = Math.min(currentTime + 30, startMinutes);
                    if (slotEnd - currentTime >= 30) {
                        slots.push({
                            start: minutesToTime(currentTime),
                            end: minutesToTime(slotEnd),
                            duration: slotEnd - currentTime
                        });
                    }
                    currentTime += 30;
                }
                currentTime = Math.max(currentTime, endMinutes);
            });
            
            // Add remaining slots after last schedule
            while (currentTime + 30 <= workEnd) {
                slots.push({
                    start: minutesToTime(currentTime),
                    end: minutesToTime(currentTime + 30),
                    duration: 30
                });
                currentTime += 30;
            }
            
            return slots;
        }
        
        // Helper functions
        function timeToMinutes(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }
        
        function minutesToTime(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
        }
        
        // Function to populate edit modal time slots
        function populateEditTimeSlots() {
            const roomId = document.getElementById('edit_room_id').value;
            const date = document.getElementById('edit_defense_date').value;
            const timeSlotSelect = document.getElementById('edit_time_slot');
            const currentDefenseId = document.getElementById('edit_defense_id').value;
            
            if (!roomId || !date) {
                timeSlotSelect.innerHTML = '<option value="">Select date and room first</option>';
                return;
            }
            
            timeSlotSelect.innerHTML = '<option value="">Loading available slots...</option>';
            
            fetch('get_room_availability.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `date=${encodeURIComponent(date)}&room_id=${encodeURIComponent(roomId)}&exclude_defense_id=${encodeURIComponent(currentDefenseId)}`
            })
            .then(response => response.json())
            .then(data => {
                const room = data.find(r => r.id == roomId);
                const slots = generateTimeSlots(room ? room.schedules : []);
                
                timeSlotSelect.innerHTML = '<option value="">Select a time slot</option>';
                slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = `${slot.start}|${slot.end}`;
                    option.textContent = `${slot.start} - ${slot.end} (${slot.duration} min)`;
                    timeSlotSelect.appendChild(option);
                });
                
                if (slots.length === 0) {
                    timeSlotSelect.innerHTML = '<option value="">No available slots</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                timeSlotSelect.innerHTML = '<option value="">Error loading slots</option>';
            });
        }
        
        // Function to update edit modal hidden time inputs
        function updateEditTimeInputs() {
            const timeSlot = document.getElementById('edit_time_slot').value;
            const durationDisplay = document.getElementById('edit_duration_display');
            
            if (timeSlot) {
                const [startTime, endTime] = timeSlot.split('|');
                document.getElementById('edit_start_time').value = startTime;
                document.getElementById('edit_end_time').value = endTime;
                
                const duration = timeToMinutes(endTime) - timeToMinutes(startTime);
                durationDisplay.textContent = `${startTime} - ${endTime} (${duration} minutes)`;
            } else {
                document.getElementById('edit_start_time').value = '';
                document.getElementById('edit_end_time').value = '';
                durationDisplay.textContent = 'No slot selected';
            }
        }

        // Function to update hidden time inputs when slot is selected
        function updateTimeInputs() {
            const timeSlot = document.getElementById('time_slot').value;
            const durationDisplay = document.getElementById('duration_display');
            
            if (timeSlot) {
                const [startTime, endTime] = timeSlot.split('|');
                document.getElementById('start_time').value = startTime;
                document.getElementById('end_time').value = endTime;
                
                const duration = timeToMinutes(endTime) - timeToMinutes(startTime);
                durationDisplay.textContent = `${startTime} - ${endTime} (${duration} minutes)`;
            } else {
                document.getElementById('start_time').value = '';
                document.getElementById('end_time').value = '';
                durationDisplay.textContent = 'No slot selected';
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
                
                const gradientClass = hasSchedules ? 'from-white via-red-50 to-rose-100 border-red-200' : 'from-white via-green-50 to-emerald-100 border-green-200';
                const iconGradient = hasSchedules ? 'bg-gradient-to-r from-red-500 to-rose-600' : 'bg-gradient-to-r from-green-500 to-emerald-600';
                
                html += `
                    <div class="defense-card bg-gradient-to-br ${gradientClass} border rounded-2xl shadow-lg p-6 relative overflow-hidden">
                        <!-- Decorative elements -->
                        <div class="absolute top-0 right-0 w-20 h-20 ${hasSchedules ? 'bg-red-400/10' : 'bg-green-400/10'} rounded-full -translate-y-10 translate-x-10"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 ${hasSchedules ? 'bg-rose-400/10' : 'bg-emerald-400/10'} rounded-full translate-y-8 -translate-x-8"></div>
                        
                        <div class="relative z-10">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center">
                                    <div class="${iconGradient} p-3 rounded-xl mr-3 shadow-lg">
                                        <i class="fas fa-door-open text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 leading-tight">${room.room_name}</h3>
                                        <p class="text-xs ${hasSchedules ? 'text-red-600' : 'text-green-600'} font-medium">${room.building}</p>
                                    </div>
                                </div>
                                <span class="${statusClass} px-1.5 py-0.5 rounded-full text-xs font-normal shadow-sm flex items-center">
                                    <i class="fas ${statusIcon} mr-1 text-xs"></i>${hasSchedules ? 'Occupied' : 'Available'}
                                </span>
                            </div>

                            <!-- Details Section -->
                            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                <div class="flex items-center text-sm mb-2">
                                    <i class="fas fa-users ${hasSchedules ? 'text-red-500' : 'text-green-500'} mr-3 w-4"></i>
                                    <span class="text-gray-700 font-medium">Capacity: ${room.capacity || 'N/A'}</span>
                                </div>
                                ${(() => {
                                    const availableSlots = generateTimeSlots(room.schedules || []);
                                    if (availableSlots.length > 0) {
                                        return `
                                            <div class="space-y-1">
                                                <p class="text-xs text-green-600 font-medium mb-2">Available Slots:</p>
                                                ${availableSlots.slice(0, 4).map(slot => `
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-clock text-green-500 mr-3 w-4"></i>
                                                        <span class="text-gray-700 font-medium">${slot.start} - ${slot.end} (${slot.duration} min)</span>
                                                    </div>
                                                `).join('')}
                                                ${availableSlots.length > 4 ? `<p class="text-xs text-gray-500">+${availableSlots.length - 4} more slots</p>` : ''}
                                            </div>
                                        `;
                                    } else {
                                        return `
                                            <div class="flex items-center text-sm">
                                                <i class="fas fa-times-circle text-red-500 mr-3 w-4"></i>
                                                <span class="text-gray-700 font-medium">No available slots</span>
                                            </div>
                                        `;
                                    }
                                })()}
                                ${hasSchedules ? `
                                    <div class="mt-3 pt-2 border-t border-gray-200">
                                        <p class="text-xs text-red-600 font-medium mb-1">In Use:</p>
                                        ${room.schedules.map(schedule => `
                                            <div class="flex items-center text-xs text-red-600 mb-1">
                                                <span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                                                ${schedule.start_time} - ${schedule.end_time} (${schedule.group_name})
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : ''}
                            </div>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-slide-up">
                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-blue-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="gradient-blue p-2 rounded-lg">
                            <i class="fas fa-file-alt text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                            Total
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_proposals; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Proposals</p>
                </div>

                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-green-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="gradient-green p-2 rounded-lg">
                            <i class="fas fa-calendar-check text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
                            Scheduled
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $scheduled_defenses; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Defenses</p>
                </div>

                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-orange-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 p-2 rounded-lg">
                            <i class="fas fa-clock text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded-full">
                            Pending
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $pending_defenses; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Defenses</p>
                </div>

                <div class="stats-card p-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-purple-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="gradient-purple p-2 rounded-lg">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">
                            Completed
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $completed_defenses; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Defenses</p>
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
                        
                        <!-- Room Availability Results -->
                        <div class="mt-8">
                            <div class="flex items-center mb-6">
                                <span id="selectedDate" class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full"><?php echo date('F j, Y'); ?></span>
                            </div>
                            
                            <div id="roomAvailabilityGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="col-span-3 text-center py-8">
                                    <i class="fas fa-calendar-alt text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-500">Select a date to check room availability</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Groups Ready for Defense -->
            <div id="scheduleCards" class="stats-card rounded-2xl p-8 animate-scale-in mb-8">
                <div class="flex items-center mb-8">
                    <div class="gradient-yellow p-3 rounded-xl mr-4">
                        <i class="fas fa-calendar-plus text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Scheduled Groups</h2>
                </div>

                <?php if (empty($organized_schedules)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">No scheduled defenses found</p>
                </div>
                <?php else: ?>
                
                <?php foreach ($organized_schedules as $program => $clusters): ?>
                <div class="program-section mb-8 bg-gradient-to-br from-white to-blue-50 rounded-2xl border border-blue-100 shadow-lg overflow-hidden">
                    <div class="program-header cursor-pointer bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-5 hover:from-blue-700 hover:to-indigo-800 transition-all" onclick="toggleProgram('<?php echo md5($program); ?>')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-white/20 p-3 rounded-xl mr-4">
                                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold"><?php echo htmlspecialchars($program); ?></h3>
                                    <p class="text-blue-100 text-sm mt-1"><?php echo count($clusters); ?> cluster<?php echo count($clusters) > 1 ? 's' : ''; ?> • <?php echo array_sum(array_map('count', $clusters)); ?> scheduled defenses</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <span class="bg-white/20 text-white text-sm font-bold px-4 py-2 rounded-xl mr-3">
                                    <?php echo array_sum(array_map('count', $clusters)); ?> Total
                                </span>
                                <i class="fas fa-chevron-down text-white transition-transform text-xl" id="program-chevron-<?php echo md5($program); ?>"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="program-content hidden p-6" id="program-<?php echo md5($program); ?>">
                        <div class="grid gap-6">
                            <?php foreach ($clusters as $cluster_name => $cluster_schedules): ?>
                            <div class="cluster-section bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                                <div class="cluster-header cursor-pointer bg-gradient-to-r from-gray-100 to-slate-100 border-b border-gray-200 p-4 hover:from-gray-200 hover:to-slate-200 transition-all" onclick="toggleCluster('<?php echo md5($program . $cluster_name); ?>')">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-r from-gray-500 to-slate-600 p-2 rounded-lg mr-3">
                                                <i class="fas fa-layer-group text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-xl font-bold text-gray-800">Cluster <?php echo htmlspecialchars($cluster_name); ?></h4>
                                                <p class="text-gray-600 text-sm"><?php echo count($cluster_schedules); ?> group<?php echo count($cluster_schedules) > 1 ? 's' : ''; ?> scheduled</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-gradient-to-r from-gray-500 to-slate-600 text-white text-sm font-bold px-3 py-1 rounded-lg mr-3">
                                                <?php echo count($cluster_schedules); ?>
                                            </span>
                                            <i class="fas fa-chevron-down text-gray-600 transition-transform" id="cluster-chevron-<?php echo md5($program . $cluster_name); ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="cluster-content hidden p-4" id="cluster-<?php echo md5($program . $cluster_name); ?>">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                        <?php foreach ($cluster_schedules as $schedule): ?>
                                        <div class="defense-card bg-gradient-to-br from-white via-blue-50 to-indigo-100 border border-blue-200 rounded-xl shadow-md hover:shadow-lg p-4 flex flex-col justify-between relative overflow-hidden min-h-[300px] transition-all duration-300" 
                                             data-status="<?php echo $schedule['status']; ?>" 
                                             data-defense-id="<?php echo $schedule['id']; ?>">
                                            
                                            <div class="absolute top-3 right-3">
                                                <?php if ($schedule['status'] == 'completed'): ?>
                                                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                                <?php elseif ($schedule['status'] == 'failed'): ?>
                                                    <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                                                <?php else: ?>
                                                    <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                                                <?php endif; ?>
                                            </div>
                                        
                                        <div class="relative z-10">
                                            <!-- Header -->
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-2">
                                                <div class="flex items-center flex-1 min-w-0">
                                                    <div class="gradient-blue p-2 sm:p-3 rounded-xl mr-2 sm:mr-3 shadow-lg flex-shrink-0">
                                                        <i class="fas fa-calendar-alt text-white text-sm sm:text-lg"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="text-sm sm:text-lg font-bold text-gray-900 leading-tight truncate"><?php echo $schedule['group_name']; ?></h3>
                                                        <p class="text-xs text-blue-600 font-medium truncate"><?php echo $schedule['proposal_title'] ?? 'No Title'; ?></p>
                                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $schedule['defense_type'] === 'final' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $schedule['defense_type'])); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <?php if ($schedule['status'] == 'completed'): ?>
                                                    <span class="bg-gradient-to-r from-green-400 to-green-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                                        <i class="fas fa-check-circle mr-1"></i>Completed
                                                    </span>
                                                <?php elseif ($schedule['status'] == 'cancelled'): ?>
                                                    <span class="bg-gradient-to-r from-red-400 to-red-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                                        <i class="fas fa-times-circle mr-1"></i>Cancelled
                                                    </span>
                                                <?php elseif ($schedule['status'] == 'failed'): ?>
                                                    <span class="bg-gradient-to-r from-orange-400 to-orange-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>Failed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="bg-gradient-to-r from-blue-400 to-blue-600 text-white px-1.5 py-0.5 rounded-full text-xs font-normal shadow-sm flex items-center">
                                                        <i class="fas fa-clock mr-1 text-xs"></i>Scheduled
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Details Section -->
                                            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                                <div class="grid grid-cols-1 gap-3">
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-calendar text-blue-500 mr-3 w-4"></i>
                                                        <span class="text-gray-700 font-medium"><?php echo date('M j, Y', strtotime($schedule['defense_date'])); ?></span>
                                                    </div>
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-clock text-blue-500 mr-3 w-4"></i>
                                                        <span class="text-gray-700 font-medium">
                                                            <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - 
                                                            <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-map-marker-alt text-blue-500 mr-3 w-4"></i>
                                                        <span class="text-gray-700 font-medium"><?php echo $schedule['building'] . ' ' . $schedule['room_name']; ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Panel Section -->
                                            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                                <div class="flex items-start">
                                                    <i class="fas fa-users text-purple-500 mr-3 mt-1 w-4"></i>
                                                    <div class="flex-1">
                                                        <p class="text-xs text-gray-600 font-medium uppercase tracking-wide mb-1">Panel Members</p>
                                                        <p class="text-gray-700 font-medium text-sm">
                                                            <?php echo !empty($schedule['panel_names']) ? $schedule['panel_names'] : '<span class="text-orange-500">No panel assigned</span>'; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <?php if ($schedule['status'] == 'completed'): ?>
                                                    <button onclick="markFailed(<?php echo $schedule['id']; ?>)" class="flex-1 bg-gradient-to-r from-orange-400 to-orange-600 hover:from-orange-500 hover:to-orange-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Mark as Failed">
                                                        <i class="fas fa-times-circle mr-1"></i>Mark Failed
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($schedule['status'] == 'failed'): ?>
                                                    <button onclick="scheduleRedefense(<?php echo $schedule['group_id']; ?>, <?php echo $schedule['id']; ?>)" class="flex-1 bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Schedule Redefense">
                                                        <i class="fas fa-redo mr-1"></i>Redefense
                                                    </button>
                                                <?php endif; ?>
                                                <button class="bg-white/80 hover:bg-white border border-blue-200 hover:border-blue-300 text-blue-700 hover:text-blue-800 py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-md backdrop-blur-sm" onclick="populateEditForm(<?php echo htmlspecialchars(json_encode($schedule)); ?>)" title="Edit">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </button>
                                                <button onclick="deleteDefense(<?php echo $schedule['id']; ?>)" class="bg-white/80 hover:bg-red-50 border border-red-200 hover:border-red-300 text-red-600 hover:text-red-700 py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-md backdrop-blur-sm" title="Delete">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>



            <!-- Upcoming Defenses Section -->
            <div id="upcomingCards" class="stats-card rounded-2xl p-8 animate-scale-in mb-8">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <div class="gradient-green p-3 rounded-xl mr-4">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Upcoming Defenses</h2>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="expandAllUpcoming()" class="bg-green-100 hover:bg-green-200 text-green-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-expand-arrows-alt mr-2"></i>Expand All
                        </button>
                        <button onclick="collapseAllUpcoming()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-compress-arrows-alt mr-2"></i>Collapse All
                        </button>
                    </div>
                </div>
                
                <?php 
                $upcoming_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, p.title as proposal_title, r.room_name, r.building,
                                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                                FROM defense_schedules ds 
                                JOIN groups g ON ds.group_id = g.id 
                                LEFT JOIN clusters c ON g.cluster_id = c.id
                                JOIN proposals p ON g.id = p.group_id 
                                LEFT JOIN rooms r ON ds.room_id = r.id 
                                LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                                LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                                WHERE ds.defense_date >= CURDATE() AND ds.status = 'scheduled'
                                GROUP BY ds.id
                                ORDER BY g.program, c.cluster, g.name, ds.defense_date, ds.start_time";
                $upcoming_result = mysqli_query($conn, $upcoming_query);
                $organized_upcoming = [];
                
                while ($upcoming = mysqli_fetch_assoc($upcoming_result)) {
                    $program = $upcoming['program'];
                    $cluster = $upcoming['cluster'] ?? 'Unassigned';
                    $organized_upcoming[$program][$cluster][] = $upcoming;
                }
                ?>
                
                <?php if (empty($organized_upcoming)): ?>
                <div class="text-center py-12">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-8 max-w-md mx-auto">
                        <i class="far fa-calendar-alt text-5xl text-green-400 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Upcoming Defenses</h3>
                        <p class="text-gray-500 text-sm mb-4">All scheduled defenses are either completed or in the past.</p>
                        <button onclick="toggleModal()" class="gradient-green text-white px-6 py-2 rounded-lg text-sm font-semibold hover:shadow-lg transition-all">
                            <i class="fas fa-plus mr-2"></i>Schedule New Defense
                        </button>
                    </div>
                </div>
                <?php else: ?>
                
                <?php foreach ($organized_upcoming as $program => $clusters): ?>
                <div class="mb-6">
                    <div class="program-header cursor-pointer bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4 mb-4 hover:shadow-md transition-all" onclick="toggleUpcomingProgram('<?php echo md5($program . '_upcoming'); ?>')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-green-600 text-xl mr-3"></i>
                                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($program); ?> - Upcoming</h3>
                                <span class="ml-4 bg-green-500 text-white text-sm font-bold px-3 py-2 rounded-xl shadow-lg">
                                    <?php echo array_sum(array_map('count', $clusters)); ?> upcoming
                                </span>
                            </div>
                            <i class="fas fa-chevron-down text-green-600 transition-transform" id="upcoming-program-chevron-<?php echo md5($program . '_upcoming'); ?>"></i>
                        </div>
                    </div>
                    
                    <div class="program-content hidden ml-4" id="upcoming-program-<?php echo md5($program . '_upcoming'); ?>">
                        <?php foreach ($clusters as $cluster_name => $cluster_upcoming): ?>
                        <div class="cluster-section mb-4">
                            <div class="cluster-header cursor-pointer bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200 rounded-xl p-3 mb-3 hover:shadow-md transition-all" onclick="toggleUpcomingCluster('<?php echo md5($program . $cluster_name . '_upcoming'); ?>')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-layer-group text-gray-600 text-lg mr-3"></i>
                                        <h4 class="text-lg font-semibold text-gray-800">Cluster <?php echo htmlspecialchars($cluster_name); ?></h4>
                                        <span class="ml-3 bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                                            <?php echo count($cluster_upcoming); ?> groups
                                        </span>
                                    </div>
                                    <i class="fas fa-chevron-down text-gray-600 transition-transform" id="upcoming-cluster-chevron-<?php echo md5($program . $cluster_name . '_upcoming'); ?>"></i>
                                </div>
                            </div>
                            
                            <div class="cluster-content hidden ml-4" id="upcoming-cluster-<?php echo md5($program . $cluster_name . '_upcoming'); ?>">
                                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
                                    <?php foreach ($cluster_upcoming as $upcoming): ?>
                                    <div class="defense-card bg-gradient-to-br from-white via-green-50 to-emerald-100 border border-green-200 rounded-2xl shadow-lg p-4 sm:p-6 flex flex-col justify-between relative overflow-hidden min-h-[280px] sm:min-h-[300px] hover:shadow-xl transition-all">
                                        
                                        <!-- Decorative elements -->
                                        <div class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-emerald-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                                        
                                        <div class="relative z-10">
                                            <!-- Header -->
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-2">
                                                <div class="flex items-center flex-1 min-w-0">
                                                    <div class="gradient-green p-2 sm:p-3 rounded-xl mr-2 sm:mr-3 shadow-lg flex-shrink-0">
                                                        <i class="fas fa-calendar-check text-white text-sm sm:text-lg"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h4 class="text-sm sm:text-lg font-bold text-gray-900 leading-tight truncate"><?php echo $upcoming['group_name']; ?></h4>
                                                        <p class="text-xs text-green-600 font-medium truncate"><?php echo $upcoming['proposal_title']; ?></p>
                                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $upcoming['defense_type'] === 'final' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $upcoming['defense_type'])); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="bg-gradient-to-r from-green-400 to-emerald-600 text-white px-2 py-1 rounded-full text-xs font-medium shadow-sm flex items-center">
                                                    <i class="fas fa-clock mr-1 text-xs"></i>Upcoming
                                                </span>
                                            </div>

                                            <!-- Details Section -->
                                            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                                <div class="space-y-2 text-sm">
                                                    <div class="flex items-center text-gray-700">
                                                        <i class="fas fa-calendar text-green-500 mr-3 w-4"></i>
                                                        <span class="font-medium"><?php echo date('M j, Y', strtotime($upcoming['defense_date'])); ?></span>
                                                    </div>
                                                    <div class="flex items-center text-gray-700">
                                                        <i class="fas fa-clock text-green-500 mr-3 w-4"></i>
                                                        <span class="font-medium"><?php echo date('g:i A', strtotime($upcoming['start_time'])); ?> - <?php echo date('g:i A', strtotime($upcoming['end_time'])); ?></span>
                                                    </div>
                                                    <div class="flex items-center text-gray-700">
                                                        <i class="fas fa-map-marker-alt text-green-500 mr-3 w-4"></i>
                                                        <span class="font-medium"><?php echo $upcoming['building'] . ' ' . $upcoming['room_name']; ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action -->
                                            <button onclick="viewUpcomingDefense(<?php echo htmlspecialchars(json_encode($upcoming)); ?>)" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-300 hover:shadow-lg">
                                                <i class="fas fa-eye mr-2"></i>View Details
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
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Completed Defenses Section -->
            <div id="completedCards" class="stats-card rounded-2xl p-8 animate-scale-in mb-8">
                <div class="flex items-center mb-8">
                    <div class="gradient-purple p-3 rounded-xl mr-4">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Completed Defenses</h2>
                </div>
                
                <?php 
                // Get completed defenses organized by program -> cluster -> group
                $completed_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, r.room_name, r.building, p.title as proposal_title
                                   FROM defense_schedules ds 
                                   LEFT JOIN groups g ON ds.group_id = g.id 
                                   LEFT JOIN clusters c ON g.cluster_id = c.id
                                   LEFT JOIN rooms r ON ds.room_id = r.id 
                                   LEFT JOIN proposals p ON g.id = p.group_id
                                   WHERE ds.status = 'completed'
                                   ORDER BY g.program, c.cluster, g.name, ds.defense_date DESC";
                $completed_result = mysqli_query($conn, $completed_query);
                $organized_completed = [];
                
                while ($completed = mysqli_fetch_assoc($completed_result)) {
                    $program = $completed['program'];
                    $cluster = $completed['cluster'] ?? 'Unassigned';
                    $organized_completed[$program][$cluster][] = $completed;
                }
                
                if (empty($organized_completed)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">No completed defenses yet</p>
                </div>
                <?php else: ?>
                
                <?php foreach ($organized_completed as $program => $clusters): ?>
                <div class="mb-6">
                    <div class="program-header cursor-pointer bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4 mb-4 hover:shadow-md transition-all" onclick="toggleCompletedProgram('<?php echo md5($program . '_completed'); ?>')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($program); ?> - Completed</h3>
                                <span class="ml-4 bg-green-500 text-white text-sm font-bold px-3 py-2 rounded-xl shadow-lg">
                                    <?php echo array_sum(array_map('count', $clusters)); ?> completed
                                </span>
                            </div>
                            <i class="fas fa-chevron-down text-green-600 transition-transform" id="completed-program-chevron-<?php echo md5($program . '_completed'); ?>"></i>
                        </div>
                    </div>
                    
                    <div class="program-content hidden ml-4" id="completed-program-<?php echo md5($program . '_completed'); ?>">
                        <?php foreach ($clusters as $cluster_name => $cluster_completed): ?>
                        <div class="cluster-section mb-4">
                            <div class="cluster-header cursor-pointer bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200 rounded-xl p-3 mb-3 hover:shadow-md transition-all" onclick="toggleCompletedCluster('<?php echo md5($program . $cluster_name . '_completed'); ?>')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-layer-group text-gray-600 text-lg mr-3"></i>
                                        <h4 class="text-lg font-semibold text-gray-800">Cluster <?php echo htmlspecialchars($cluster_name); ?></h4>
                                        <span class="ml-3 bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                                            <?php echo count($cluster_completed); ?> groups
                                        </span>
                                    </div>
                                    <i class="fas fa-chevron-down text-gray-600 transition-transform" id="completed-cluster-chevron-<?php echo md5($program . $cluster_name . '_completed'); ?>"></i>
                                </div>
                            </div>
                            
                            <div class="cluster-content hidden ml-4" id="completed-cluster-<?php echo md5($program . $cluster_name . '_completed'); ?>">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($cluster_completed as $completed): ?>
                                    <div class="defense-card bg-white border border-gray-200 rounded-xl shadow-md p-4">
                                        <div>
                                            <div class="flex justify-between items-start mb-2">
                                                <h5 class="text-md font-semibold text-gray-900"><?php echo $completed['proposal_title'] ?? 'No Title'; ?></h5>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $completed['defense_type'] === 'final' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $completed['defense_type'])); ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 mb-2"><?php echo $completed['group_name']; ?></p>
                                            <p class="text-xs text-gray-600 mb-1">
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo date('M j, Y', strtotime($completed['defense_date'])); ?>
                                            </p>
                                            <p class="text-xs text-gray-600 mb-2">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php echo date('g:i A', strtotime($completed['start_time'])); ?> - 
                                                <?php echo date('g:i A', strtotime($completed['end_time'])); ?>
                                            </p>
                                        </div>
                                        
                                        <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Completed</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

<script>
// Toggle program sections
function toggleProgram(programId) {
    const content = document.getElementById('program-' + programId);
    const chevron = document.getElementById('program-chevron-' + programId);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle cluster sections
function toggleCluster(clusterId) {
    const content = document.getElementById('cluster-' + clusterId);
    const chevron = document.getElementById('cluster-chevron-' + clusterId);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle upcoming program sections
function toggleUpcomingProgram(programId) {
    const content = document.getElementById('upcoming-program-' + programId);
    const chevron = document.getElementById('upcoming-program-chevron-' + programId);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle upcoming cluster sections
function toggleUpcomingCluster(clusterId) {
    const content = document.getElementById('upcoming-cluster-' + clusterId);
    const chevron = document.getElementById('upcoming-cluster-chevron-' + clusterId);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle completed program sections
function toggleCompletedProgram(programId) {
    const content = document.getElementById('completed-program-' + programId);
    const chevron = document.getElementById('completed-program-chevron-' + programId);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle completed cluster sections
function toggleCompletedCluster(clusterId) {
    const content = document.getElementById('completed-cluster-' + clusterId);
    const chevron = document.getElementById('completed-cluster-chevron-' + clusterId);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}
</script>
</body>
</html>

                    <?php 
                    // Get unscheduled groups (groups with approved proposals but no scheduled defense)
                    $unscheduled_query = "SELECT g.*, p.title as proposal_title, c.cluster
                                         FROM groups g 
                                         JOIN proposals p ON g.id = p.group_id 
                                         LEFT JOIN clusters c ON g.cluster_id = c.id
                                         LEFT JOIN defense_schedules ds ON g.id = ds.group_id AND ds.status IN ('scheduled', 'completed')
                                         WHERE p.status IN ('Completed', 'Approved', 'approved', 'completed')
                                         AND ds.id IS NULL
                                         ORDER BY g.name";
                    $unscheduled_result = mysqli_query($conn, $unscheduled_query);
                    
                    while ($group = mysqli_fetch_assoc($unscheduled_result)): ?>
                    <div class="defense-card bg-gradient-to-br from-white via-yellow-50 to-orange-100 border border-yellow-200 rounded-2xl shadow-lg p-6 flex flex-col justify-between relative overflow-hidden" data-status="pending">
                        
                        <!-- Decorative elements -->
                        <div class="absolute top-0 right-0 w-20 h-20 bg-yellow-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-orange-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                        
                        <div class="relative z-10">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-r from-yellow-400 to-orange-500 p-3 rounded-xl mr-3 shadow-lg">
                                        <i class="fas fa-clock text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 leading-tight"><?php echo $group['name']; ?></h3>
                                        <p class="text-xs text-orange-600 font-medium"><?php echo $group['proposal_title']; ?></p>
                                    </div>
                                </div>
                                <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                    <i class="fas fa-hourglass-half mr-1"></i>Pending
                                </span>
                            </div>

                            <!-- Details Section -->
                            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                <div class="grid grid-cols-1 gap-3">
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-calendar-times text-orange-500 mr-3 w-4"></i>
                                        <span class="text-gray-700 font-medium">Not scheduled</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-map-marker-alt text-orange-500 mr-3 w-4"></i>
                                        <span class="text-gray-700 font-medium">No room assigned</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-users text-orange-500 mr-3 w-4"></i>
                                        <span class="text-gray-700 font-medium">No panel assigned</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action -->
                            <button onclick="toggleModal()" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white py-3 px-4 rounded-xl text-sm font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-calendar-plus mr-2"></i>Schedule Defense
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            



        </main>
    </div>

    <!-- Schedule Defense Modal -->
    <div id="proposalModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-blue">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 border-0">
                    <h3 class="text-lg font-bold flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-calendar-plus text-white text-sm"></i>
                        </div>
                        Schedule Defense
                    </h3>
                    <p class="text-blue-100 mt-1 text-sm">Schedule a new defense session for the selected group.</p>
                </div>
            <form method="POST" action="" class="p-6" onsubmit="return validateDefenseDuration()">
                <input type="hidden" name="defense_type" id="defense_type" value="initial">
                <input type="hidden" name="parent_defense_id" id="parent_defense_id">
                <input type="hidden" name="group_id" id="group_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Group</label>
                    <select name="group_id" id="schedule_group_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select a group</option>
                        <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['program'] . ($group['cluster'] ? ' - Cluster ' . $group['cluster'] : '') . ' - ' . $group['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-medium mb-1">Defense Type</label>
                    <select name="defense_type" id="defense_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                        <option value="pre_oral">Pre-Oral Defense</option>
                        <option value="final">Final Defense</option>
                    </select>
                </div>
                
                <div id="redefense_reason_div" class="mb-4 hidden">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="redefense_reason">Redefense Reason</label>
                    <textarea name="redefense_reason" id="redefense_reason" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Reason for redefense..."></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="defense_date">Defense Date</label>
                        <input type="date" name="defense_date" id="defense_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="populateTimeSlots()">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="room_id">Room</label>
                        <select name="room_id" id="room_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="populateTimeSlots()">
                            <option value="">Select a room</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo $room['building'] . ' - ' . $room['room_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="time_slot">Available Time Slots</label>
                        <select name="time_slot" id="time_slot" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="updateTimeInputs()">
                            <option value="">Select date and room first</option>
                        </select>
                        <input type="hidden" name="start_time" id="start_time">
                        <input type="hidden" name="end_time" id="end_time">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Selected Duration</label>
                        <div class="px-3 py-2 bg-gray-100 rounded-lg text-sm" id="duration_display">No slot selected</div>
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
                
                <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                    <button type="button" onclick="toggleModal()" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" name="schedule_defense" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
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
    </div>

    <!-- Edit Defense Modal -->
    <div id="editDefenseModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block bg-gradient-to-br from-white via-indigo-50 to-purple-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-4xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-indigo">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white p-4 border-0">
                    <h3 class="text-lg font-bold flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-edit text-white text-sm"></i>
                        </div>
                        Edit Defense Schedule
                    </h3>
                    <p class="text-indigo-100 mt-1 text-sm">Update defense schedule information below.</p>
                </div>
            <div class="flex-1 overflow-y-auto">
            <form method="POST" action="" class="p-6" id="editForm">
                <input type="hidden" name="defense_id" id="edit_defense_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_group_id">Group</label>
                    <select name="group_id" id="edit_group_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select a group</option>
                        <?php foreach ($all_groups as $group): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['program'] . ($group['cluster'] ? ' - Cluster ' . $group['cluster'] : '') . ' - ' . $group['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-medium mb-1">Defense Type</label>
                    <select name="defense_type" id="edit_defense_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                        <option value="pre_oral">Pre-Oral Defense</option>
                        <option value="final">Final Defense</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_defense_date">Defense Date</label>
                        <input type="date" name="defense_date" id="edit_defense_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="populateEditTimeSlots()">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_room_id">Room</label>
                        <select name="room_id" id="edit_room_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="populateEditTimeSlots()">
                            <option value="">Select a room</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo $room['building'] . ' - ' . $room['room_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_time_slot">Available Time Slots</label>
                        <select name="time_slot" id="edit_time_slot" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="updateEditTimeInputs()">
                            <option value="">Select date and room first</option>
                        </select>
                        <input type="hidden" name="start_time" id="edit_start_time">
                        <input type="hidden" name="end_time" id="edit_end_time">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Selected Duration</label>
                        <div class="px-3 py-2 bg-gray-100 rounded-lg text-sm" id="edit_duration_display">No slot selected</div>
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
                
                <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                    <button type="button" onclick="toggleEditModal()" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" name="edit_defense" class="bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                        <i class="fas fa-save mr-2"></i>Update Schedule
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Defense Details Modal -->
    <div id="detailsModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block bg-gradient-to-br from-white via-green-50 to-emerald-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-green">
                <div class="bg-gradient-to-r from-green-600 to-emerald-700 text-white p-4 border-0">
                    <h3 class="text-lg font-bold flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-info-circle text-white text-sm"></i>
                        </div>
                        Defense Details
                    </h3>
                    <p class="text-green-100 mt-1 text-sm">View detailed information about this defense schedule.</p>
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
                
                <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                    <button onclick="toggleDetailsModal()" class="bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

   <!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal Content -->
        <div class="inline-block bg-gradient-to-br from-white via-red-50 to-rose-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-md w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-red">
            <div class="bg-gradient-to-r from-red-600 to-rose-700 text-white p-4 border-0">
                <h3 class="text-lg font-bold flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    Confirm Deletion
                </h3>
                <p class="text-red-100 mt-1 text-sm">This action cannot be undone.</p>
            </div>

            <div class="p-4">
                <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40 mb-4">
                    <p class="text-gray-700 mb-3 font-medium">Are you sure you want to delete this defense schedule?</p>
                    <div class="space-y-2">
                        <div class="flex items-center p-2 bg-red-50 rounded-lg">
                            <i class="fas fa-calendar-times text-red-600 mr-3"></i>
                            <span class="text-gray-700 text-sm">Defense schedule will be permanently removed</span>
                        </div>
                        <div class="flex items-center p-2 bg-red-50 rounded-lg">
                            <i class="fas fa-users-slash text-red-600 mr-3"></i>
                            <span class="text-gray-700 text-sm">Panel assignments will be cancelled</span>
                        </div>
                    </div>
                </div>

                <!-- Delete Form -->
                <form id="deleteForm" method="POST" action="">
                    <input type="hidden" name="defense_id" id="defense_id">
                    <input type="hidden" name="delete_schedule" value="1">

                    <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                        <button type="button" onclick="closeModal('deleteConfirmModal')" 
                            class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" 
                            class="bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                            <i class="fas fa-trash mr-2"></i>Delete Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
/* ========= MODAL FUNCTIONS ========= */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('modal-active');

    const modalContent = modal.querySelector('.modal-content');
    if (modalContent) {
        setTimeout(() => modalContent.classList.add('modal-content-active'), 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const modalContent = modal.querySelector('.modal-content');
    if (modalContent) modalContent.classList.remove('modal-content-active');

    setTimeout(() => {
        modal.classList.remove('modal-active');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }, 200);
}

/* ========= DELETE FUNCTIONS ========= */
function deleteDefense(defenseId) {
    // Create animated confirmation dialog
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 z-[60] flex items-center justify-center p-4';
    overlay.style.background = 'linear-gradient(135deg, rgba(0,0,0,0.7), rgba(239,68,68,0.2))';
    overlay.style.backdropFilter = 'blur(8px)';
    overlay.style.opacity = '0';
    overlay.style.transition = 'all 0.3s ease';
    
    overlay.innerHTML = `
        <div class="bg-gradient-to-br from-white via-red-50 to-rose-100 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 transition-all duration-300 border border-red-200" id="deleteDialog">
            <div class="bg-gradient-to-r from-red-500 to-rose-600 p-4 rounded-t-2xl">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3 animate-pulse">
                        <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Delete Defense Schedule</h3>
                        <p class="text-red-100 text-sm">This action cannot be undone</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-red-200">
                    <p class="text-gray-800 font-medium mb-3">Are you sure you want to delete this defense schedule?</p>
                    <div class="space-y-2">
                        <div class="flex items-center p-2 bg-red-50 rounded-lg">
                            <i class="fas fa-calendar-times text-red-500 mr-3"></i>
                            <span class="text-gray-700 text-sm">Schedule will be permanently removed</span>
                        </div>
                        <div class="flex items-center p-2 bg-red-50 rounded-lg">
                            <i class="fas fa-users-slash text-red-500 mr-3"></i>
                            <span class="text-gray-700 text-sm">Panel assignments will be cancelled</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 justify-end">
                    <button onclick="this.closest('.fixed').remove()" class="bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button onclick="confirmDeleteAction(${defenseId}, this)" class="bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg transform hover:scale-105">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Animate in
    setTimeout(() => {
        overlay.style.opacity = '1';
        overlay.querySelector('#deleteDialog').style.transform = 'scale(1)';
    }, 10);
    
    // Close on backdrop click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.remove();
    });
}

function confirmDeleteAction(defenseId, button) {
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    button.disabled = true;
    
    // Create and submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `<input type="hidden" name="defense_id" value="${defenseId}"><input type="hidden" name="delete_schedule" value="1">`;
    document.body.appendChild(form);
    form.submit();
}

function showDeleteModal(defenseId) {
    console.log('showDeleteModal called with ID:', defenseId);
    document.getElementById('defense_id').value = defenseId;
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('modal-active');
}

function confirmDelete(defenseId, groupName) {
    console.log('confirmDelete called:', defenseId, groupName);
    showDeleteModal(defenseId);
}

function toggleModal() {
    const modal = document.getElementById('proposalModal');
    if (modal.classList.contains('opacity-0')) {
        openModal('proposalModal');
    } else {
        closeModal('proposalModal');
    }
}

function toggleEditModal() {
    const modal = document.getElementById('editDefenseModal');
    if (modal.classList.contains('opacity-0')) {
        openModal('editDefenseModal');
    } else {
        closeModal('editDefenseModal');
    }
}

function toggleDetailsModal() {
    const modal = document.getElementById('detailsModal');
    if (modal.classList.contains('opacity-0')) {
        openModal('detailsModal');
    } else {
        closeModal('detailsModal');
    }
}

/* ========= EDIT FORM POPULATION ========= */
function populateEditForm(schedule) {
    try {
        document.getElementById('edit_defense_id').value = schedule.id || '';
        document.getElementById('edit_group_id').value = schedule.group_id || '';
        document.getElementById('edit_defense_type').value = schedule.defense_type || 'pre_oral';
        document.getElementById('edit_defense_date').value = schedule.defense_date || '';
        document.getElementById('edit_room_id').value = schedule.room_id || '';
        document.getElementById('edit_start_time').value = schedule.start_time || '';
        document.getElementById('edit_end_time').value = schedule.end_time || '';
        
        // Set current time slot and populate available slots
        if (schedule.start_time && schedule.end_time) {
            const currentSlot = `${schedule.start_time}|${schedule.end_time}`;
            setTimeout(() => {
                populateEditTimeSlots();
                setTimeout(() => {
                    const timeSlotSelect = document.getElementById('edit_time_slot');
                    const currentOption = document.createElement('option');
                    currentOption.value = currentSlot;
                    currentOption.textContent = `${schedule.start_time} - ${schedule.end_time} (Current)`;
                    currentOption.selected = true;
                    timeSlotSelect.insertBefore(currentOption, timeSlotSelect.firstChild);
                    updateEditTimeInputs();
                }, 500);
            }, 100);
        }

        // Reset all panel checkboxes
        document.querySelectorAll('.edit-panel-member').forEach(cb => cb.checked = false);

        // Apply assigned panel members
        if (schedule.panel_members && Array.isArray(schedule.panel_members)) {
            schedule.panel_members.forEach(panel => {
                const panelId = panel.user_id || panel.id || panel.faculty_id;
                if (panelId) {
                    const checkbox = document.querySelector(`.edit-panel-member[value="${panelId}"]`);
                    if (checkbox) checkbox.checked = true;
                }
            });
        }

        toggleEditModal();
    } catch (error) {
        console.error('Error populating edit form:', error);
        alert('Error loading defense data for editing.');
    }
}

/* ========= PANEL TABS ========= */
function switchEditPanelTab(tabName) {
    document.querySelectorAll('.panel-tab').forEach(tab => {
        tab.classList.toggle('active', tab.dataset.tab === tabName);
    });

    document.querySelectorAll('.panel-content').forEach(content => {
        content.classList.toggle('active', content.dataset.tab === tabName);
    });
}

/* ========= VIEW UPCOMING DEFENSE ========= */
function viewUpcomingDefense(defense) {
    document.getElementById('detailTitle').textContent = defense.group_name;
    document.getElementById('detailGroup').textContent = defense.proposal_title;
    document.getElementById('detailDate').textContent = new Date(defense.defense_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    document.getElementById('detailTime').textContent = new Date('1970-01-01T' + defense.start_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) + ' - ' + new Date('1970-01-01T' + defense.end_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    document.getElementById('detailLocation').textContent = defense.building + ' ' + defense.room_name;
    document.getElementById('detailPanel').innerHTML = defense.panel_names ? defense.panel_names : '<span class="text-orange-500">No panel assigned</span>';
    document.getElementById('detailStatus').textContent = 'Scheduled';
    document.getElementById('detailStatus').className = 'px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800';
    toggleDetailsModal();
}

/* ========= GLOBAL ACCESS ========= */
window.deleteDefense = deleteDefense;
window.confirmDeleteAction = confirmDeleteAction;
window.openModal = openModal;
window.closeModal = closeModal;
window.showDeleteModal = showDeleteModal;
window.confirmDelete = confirmDelete;
window.toggleModal = toggleModal;
window.toggleEditModal = toggleEditModal;
window.toggleDetailsModal = toggleDetailsModal;
window.populateEditForm = populateEditForm;
window.switchEditPanelTab = switchEditPanelTab;
window.viewUpcomingDefense = viewUpcomingDefense;

/* ========= INIT ========= */
document.addEventListener('DOMContentLoaded', () => {
    // Default filters/tabs
    if (typeof filterStatus === 'function') filterStatus('all');
    if (typeof switchPanelTab === 'function') switchPanelTab('chairperson');
    switchEditPanelTab('edit_chairperson');

    // Refresh availability tab if needed
    <?php if (isset($_SESSION['refresh_availability'])): ?>
        if (typeof switchMainTab === 'function') switchMainTab('availability');
        <?php unset($_SESSION['refresh_availability']); ?>
    <?php endif; ?>
});
</script>
</body>
</html>