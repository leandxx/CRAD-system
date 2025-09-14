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
        $defense_type = mysqli_real_escape_string($conn, $_POST['defense_type'] ?? 'pre_oral');
        $parent_defense_id = !empty($_POST['parent_defense_id']) ? mysqli_real_escape_string($conn, $_POST['parent_defense_id']) : 'NULL';
        $redefense_reason = !empty($_POST['redefense_reason']) ? mysqli_real_escape_string($conn, $_POST['redefense_reason']) : 'NULL';
        
        // Validate required fields
        if (empty($group_id) || empty($defense_date) || empty($start_time) || empty($end_time) || empty($room_id)) {
            $_SESSION['error_message'] = "All fields are required for scheduling a defense.";
            header("Location: admin-defense.php");
            exit();
        } elseif (strtotime($defense_date) < strtotime(date('Y-m-d'))) {
            $_SESSION['error_message'] = "Defense date cannot be in the past.";
            header("Location: admin-defense.php");
            exit();
        } elseif (strtotime($start_time) >= strtotime($end_time)) {
            $_SESSION['error_message'] = "End time must be after start time.";
            header("Location: admin-defense.php");
            exit();
        } else {
            // Check if all group members have paid required fees
            $unpaid_check = "SELECT COUNT(*) as unpaid_count 
                            FROM group_members gm 
                            WHERE gm.group_id = '$group_id' 
                            AND gm.student_id NOT IN (
                                SELECT DISTINCT student_id 
                                FROM payments 
                                WHERE status = 'approved' 
                                AND payment_type IN ('research_forum', 'pre_oral_defense')
                            )";
            $unpaid_result = mysqli_query($conn, $unpaid_check);
            $unpaid_data = mysqli_fetch_assoc($unpaid_result);
            
            if ($unpaid_data['unpaid_count'] > 0) {
                $_SESSION['error_message'] = "Cannot schedule defense. Some group members have unpaid fees. Please verify payment status first.";
                header("Location: admin-defense.php");
                exit();
            } else {
                // Check room availability
                $exclude_defense = '';
                if (!empty($_POST['parent_defense_id']) && $_POST['parent_defense_id'] != 'NULL' && $defense_type == 'redefense') {
                    // For redefenses, exclude the current defense being updated
                    $exclude_defense = " AND id != '" . mysqli_real_escape_string($conn, $_POST['parent_defense_id']) . "'";
                }
                
                $availability_query = "SELECT COUNT(*) as conflict_count 
                                  FROM defense_schedules 
                                  WHERE room_id = '$room_id' 
                                  AND defense_date = '$defense_date' 
                                  AND status IN ('scheduled', 'passed')
                                  $exclude_defense
                                  AND (
                                      (start_time <= '$start_time' AND end_time > '$start_time') OR
                                      (start_time < '$end_time' AND end_time >= '$end_time') OR
                                      (start_time >= '$start_time' AND end_time <= '$end_time')
                                  )";
                $availability_result = mysqli_query($conn, $availability_query);
                $availability_data = mysqli_fetch_assoc($availability_result);
                
                if ($availability_data['conflict_count'] > 0) {
                    $_SESSION['error_message'] = "Room is not available during the selected time slot. Please choose a different time or room.";
                    header("Location: admin-defense.php");
                    exit();
                } else {

        // Default status = scheduled
        $status = 'scheduled';

        // Check if this is a redefense
        if (!empty($_POST['parent_defense_id']) && $_POST['parent_defense_id'] != 'NULL' && $defense_type == 'redefense') {
            // Update existing defense to become a redefense
            $parent_id = mysqli_real_escape_string($conn, $_POST['parent_defense_id']);
            $schedule_query = "UPDATE defense_schedules 
                              SET defense_date = '$defense_date', 
                                  start_time = '$start_time', 
                                  end_time = '$end_time', 
                                  room_id = '$room_id', 
                                  status = '$status', 
                                  defense_type = '$defense_type', 
                                  redefense_reason = '$redefense_reason', 
                                  is_redefense = 1,
                                  updated_at = NOW()
                              WHERE id = '$parent_id'";
            
            if (mysqli_query($conn, $schedule_query)) {
                $defense_id = $parent_id; // Use the existing defense ID
            } else {
                $_SESSION['error_message'] = "Failed to update defense schedule: " . mysqli_error($conn);
                header("Location: admin-defense.php");
                exit();
            }
        } else {
            // Insert new defense schedule
            $schedule_query = "INSERT INTO defense_schedules 
                              (group_id, defense_date, start_time, end_time, room_id, status, defense_type, parent_defense_id, redefense_reason, is_redefense) 
                              VALUES ('$group_id', '$defense_date', '$start_time', '$end_time', '$room_id', '$status', '$defense_type', NULL, NULL, 0)";
            
            if (mysqli_query($conn, $schedule_query)) {
                $defense_id = mysqli_insert_id($conn);
            } else {
                $_SESSION['error_message'] = "Failed to create defense schedule: " . mysqli_error($conn);
                header("Location: admin-defense.php");
                exit();
            }
        }

        if (empty($_SESSION['error_message'])) {

            // Delete existing panel members first (to prevent duplicates)
            mysqli_query($conn, "DELETE FROM defense_panel WHERE defense_id = '$defense_id'");
            
            // Insert panel members with their roles
            foreach ($panel_members as $member_data) {
                $parts = explode('|', $member_data);
                $faculty_id = mysqli_real_escape_string($conn, $parts[0]);
                $role = mysqli_real_escape_string($conn, $parts[1] ?? 'member');
                
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
            if (!empty($_POST['parent_defense_id']) && $_POST['parent_defense_id'] != 'NULL' && $defense_type == 'redefense') {
                $notification_title = "Redefense Scheduled";
                $notification_message = "A redefense has been scheduled for group: $group_name on $defense_date at $start_time";
            } else {
                $notification_title = "Defense Scheduled";
                $notification_message = "A defense has been scheduled for group: $group_name on $defense_date at $start_time";
            }
            notifyAllUsers($conn, $notification_title, $notification_message, 'info');

            if (!empty($_POST['parent_defense_id']) && $_POST['parent_defense_id'] != 'NULL' && $defense_type == 'redefense') {
                $_SESSION['success_message'] = "Redefense scheduled successfully!";
            } else {
                $_SESSION['success_message'] = "Defense scheduled successfully!";
            }
            $_SESSION['refresh_availability'] = true;
            header("Location: admin-defense.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error scheduling defense: " . mysqli_error($conn);
            header("Location: admin-defense.php");
            exit();
        }
                }
            }
        }
    }

    if (isset($_POST['mark_failed'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $update_query = "UPDATE defense_schedules SET status = 'failed' WHERE id = '$defense_id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success_message'] = "Defense marked as failed. You can now schedule a redefense.";
        } else {
            $_SESSION['error_message'] = "Error updating defense status: " . mysqli_error($conn);
        }
        header("Location: admin-defense.php");
        exit();
    }

    if (isset($_POST['mark_passed'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $update_query = "UPDATE defense_schedules SET status = 'completed' WHERE id = '$defense_id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success_message'] = "Defense marked as passed and completed.";
        } else {
            $_SESSION['error_message'] = "Error updating defense status: " . mysqli_error($conn);
        }
        header("Location: admin-defense.php");
        exit();
    }

    if (isset($_POST['confirm_defense'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $update_query = "UPDATE defense_schedules SET status = 'passed' WHERE id = '$defense_id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success_message'] = "Defense confirmed and ready for evaluation.";
        } else {
            $_SESSION['error_message'] = "Error confirming defense: " . mysqli_error($conn);
        }
        header("Location: admin-defense.php");
        exit();
    }

    if (isset($_POST['mark_completed'])) {
        $defense_id = mysqli_real_escape_string($conn, $_POST['defense_id']);
        $update_query = "UPDATE defense_schedules SET status = 'completed' WHERE id = '$defense_id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success_message'] = "Defense marked as completed.";
        } else {
            $_SESSION['error_message'] = "Error updating defense status: " . mysqli_error($conn);
        }
        header("Location: admin-defense.php");
        exit();
    }

    // Manual trigger to update all overdue defenses
    if (isset($_POST['update_overdue_defenses'])) {
        $current_datetime = date('Y-m-d H:i:s');
        $update_query = "UPDATE defense_schedules 
                        SET status = 'passed', 
                            updated_at = NOW() 
                        WHERE status = 'scheduled' 
                        AND CONCAT(defense_date, ' ', end_time) <= '$current_datetime'";
        
        if (mysqli_query($conn, $update_query)) {
            $affected_rows = mysqli_affected_rows($conn);
            $_SESSION['success_message'] = "Updated $affected_rows overdue defense(s) to evaluation status.";
        } else {
            $_SESSION['error_message'] = "Error updating overdue defenses: " . mysqli_error($conn);
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
            $_SESSION['error_message'] = "Error deleting defense schedule: " . mysqli_error($conn);
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
        if (strtotime($defense_date) < strtotime(date('Y-m-d'))) {
            $_SESSION['error_message'] = "Defense date cannot be in the past.";
            header("Location: admin-defense.php");
            exit();
        } elseif (strtotime($start_time) >= strtotime($end_time)) {
            $_SESSION['error_message'] = "End time must be after start time.";
            header("Location: admin-defense.php");
            exit();
        } else {
            // Check room availability (exclude current defense from check)
            $availability_query = "SELECT COUNT(*) as conflict_count 
                                  FROM defense_schedules 
                                  WHERE room_id = '$room_id' 
                                  AND defense_date = '$defense_date' 
                                  AND status IN ('scheduled', 'passed')
                                  AND id != '$defense_id'
                                  AND (
                                      (start_time <= '$start_time' AND end_time > '$start_time') OR
                                      (start_time < '$end_time' AND end_time >= '$end_time') OR
                                      (start_time >= '$start_time' AND end_time <= '$end_time')
                                  )";
            $availability_result = mysqli_query($conn, $availability_query);
            $availability_data = mysqli_fetch_assoc($availability_result);
            
            if ($availability_data['conflict_count'] > 0) {
                $_SESSION['error_message'] = "Room is not available during the selected time slot. Please choose a different time or room.";
                header("Location: admin-defense.php");
                exit();
            } else {

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

            // Insert panel members with their roles
            foreach ($panel_members as $member_data) {
                $parts = explode('|', $member_data);
                $faculty_id = mysqli_real_escape_string($conn, $parts[0]);
                $role = mysqli_real_escape_string($conn, $parts[1] ?? 'member');
                
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
            $_SESSION['error_message'] = "Error updating defense schedule: " . mysqli_error($conn);
            header("Location: admin-defense.php");
            exit();
        }
            }
        }
    }
}

// Get all defense schedules organized by program, adviser, and cluster
$defense_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, p.title as proposal_title, r.room_name, r.building,
                 f.fullname as adviser_name, f.id as adviser_id,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                 FROM defense_schedules ds 
                 LEFT JOIN groups g ON ds.group_id = g.id 
                 LEFT JOIN clusters c ON g.cluster_id = c.id
                 LEFT JOIN faculty f ON c.faculty_id = f.id
                 LEFT JOIN rooms r ON ds.room_id = r.id 
                 LEFT JOIN proposals p ON g.id = p.group_id
                 LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                 LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                 WHERE ds.status = 'scheduled' AND CONCAT(ds.defense_date, ' ', ds.end_time) > NOW()
                 GROUP BY ds.id
                 ORDER BY g.program, f.fullname, c.cluster, ds.defense_date, ds.start_time";
$defense_result = mysqli_query($conn, $defense_query);
$defense_schedules = [];
$defense_by_program = [];

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

    $schedule['panel_members'] = $panel_members;
    $defense_schedules[] = $schedule;
    
    // Organize by program, adviser, and cluster (consistent 4-level structure)
    $adviser_name = $schedule['adviser_name'] ?: 'Unassigned Adviser';
    $adviser_id = $schedule['adviser_id'] ?: 'unassigned';
    $program = $schedule['program'] ?: 'Unknown';
    $cluster = $schedule['cluster'] ?: 'No Cluster';
    
    $defense_by_program[$program]['advisers'][$adviser_id]['adviser_name'] = $adviser_name;
    $defense_by_program[$program]['advisers'][$adviser_id]['clusters'][$cluster]['defenses'][] = $schedule;
}

// Handle automatic status updates for overdue defenses
$current_datetime = date('Y-m-d H:i:s');
$overdue_query = "SELECT ds.id, ds.group_id, g.name as group_name, ds.defense_date, ds.end_time
                  FROM defense_schedules ds 
                  LEFT JOIN groups g ON ds.group_id = g.id 
                  WHERE ds.status = 'scheduled' 
                  AND CONCAT(ds.defense_date, ' ', ds.end_time) <= '$current_datetime'";
$overdue_result = mysqli_query($conn, $overdue_query);

while ($overdue_defense = mysqli_fetch_assoc($overdue_result)) {
    // Update status to passed (ready for evaluation)
    $update_query = "UPDATE defense_schedules 
                    SET status = 'passed', 
                        updated_at = NOW() 
                    WHERE id = '{$overdue_defense['id']}'";
    if (mysqli_query($conn, $update_query)) {
        // Send notification
        $notification_title = "Defense Ready for Evaluation";
        $notification_message = "The defense for group {$overdue_defense['group_name']} has concluded and is ready for evaluation.";
        notifyAllUsers($conn, $notification_title, $notification_message, 'info');
        
        // Log the status change
        $defense_datetime = $overdue_defense['defense_date'] . ' ' . $overdue_defense['end_time'];
        error_log("Defense ID {$overdue_defense['id']} automatically moved to evaluation. Defense time: $defense_datetime, Current time: $current_datetime");
        
        // Set a flag to refresh the page to show updated status
        $_SESSION['defense_status_updated'] = true;
    } else {
        error_log("Error updating defense status: " . mysqli_error($conn));
    }
}

// Get all groups with completed/approved proposals
$groups_query = "SELECT g.*, p.title as proposal_title 
                FROM groups g 
                JOIN proposals p ON g.id = p.group_id 
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

// Get all groups with their programs for filtering
$groups_programs_query = "SELECT id, name, program FROM groups ORDER BY name";
$groups_programs_result = mysqli_query($conn, $groups_programs_query);
$groups_programs = [];
while ($group_program = mysqli_fetch_assoc($groups_programs_result)) {
    $groups_programs[$group_program['id']] = strtolower($group_program['program']);
}

// Get all rooms
$rooms_query = "SELECT * FROM rooms ORDER BY building, room_name";
$rooms_result = mysqli_query($conn, $rooms_query);
$rooms = [];

while ($room = mysqli_fetch_assoc($rooms_result)) {
    $rooms[] = $room;
}

// Get upcoming defenses organized by program, adviser, and cluster
$upcoming_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, p.title as proposal_title, r.room_name, r.building,
                 f.fullname as adviser_name, f.id as adviser_id,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                FROM defense_schedules ds 
                JOIN groups g ON ds.group_id = g.id 
                LEFT JOIN clusters c ON g.cluster_id = c.id
                LEFT JOIN faculty f ON c.faculty_id = f.id
                JOIN proposals p ON g.id = p.group_id 
                LEFT JOIN rooms r ON ds.room_id = r.id 
                LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                WHERE CONCAT(ds.defense_date, ' ', ds.end_time) > NOW() AND ds.status = 'scheduled'
                GROUP BY ds.id
                ORDER BY g.program, f.fullname, c.cluster, ds.defense_date, ds.start_time";
$upcoming_result = mysqli_query($conn, $upcoming_query);
$upcoming_by_program = [];

while ($upcoming = mysqli_fetch_assoc($upcoming_result)) {
    $adviser_name = $upcoming['adviser_name'] ?: 'Unassigned Adviser';
    $adviser_id = $upcoming['adviser_id'] ?: 'unassigned';
    $program = $upcoming['program'] ?: 'Unknown';
    $cluster = $upcoming['cluster'] ?: 'No Cluster';
    
    $upcoming_by_program[$program]['advisers'][$adviser_id]['adviser_name'] = $adviser_name;
    $upcoming_by_program[$program]['advisers'][$adviser_id]['clusters'][$cluster]['defenses'][] = $upcoming;
}

// Get pending/unscheduled groups organized by program
$pending_query = "SELECT g.*, g.program, c.cluster, p.title as proposal_title, 
                 f.fullname as adviser_name, f.id as adviser_id
                FROM groups g 
                LEFT JOIN clusters c ON g.cluster_id = c.id
                LEFT JOIN faculty f ON c.faculty_id = f.id
                JOIN proposals p ON g.id = p.group_id 
                WHERE g.id NOT IN (SELECT group_id FROM defense_schedules) 
                AND p.status IN ('Completed', 'Approved')
                ORDER BY g.program, f.fullname, c.cluster, g.name";
$pending_result = mysqli_query($conn, $pending_query);
$pending_by_program = [];

while ($group = mysqli_fetch_assoc($pending_result)) {
    $adviser_name = $group['adviser_name'] ?: 'Unassigned Adviser';
    $adviser_id = $group['adviser_id'] ?: 'unassigned';
    $program = $group['program'] ?: 'Unknown';
    $cluster = $group['cluster'] ?: 'No Cluster';
    
    $pending_by_program[$program]['advisers'][$adviser_id]['adviser_name'] = $adviser_name;
    $pending_by_program[$program]['advisers'][$adviser_id]['clusters'][$cluster]['groups'][] = $group;
}

// Get passed defenses (ready for evaluation) organized by program
$confirmed_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, p.title as proposal_title, r.room_name, r.building,
                 f.fullname as adviser_name, f.id as adviser_id,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                FROM defense_schedules ds 
                JOIN groups g ON ds.group_id = g.id 
                LEFT JOIN clusters c ON g.cluster_id = c.id
                LEFT JOIN faculty f ON c.faculty_id = f.id
                JOIN proposals p ON g.id = p.group_id 
                LEFT JOIN rooms r ON ds.room_id = r.id 
                LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                WHERE ds.status = 'passed'
                GROUP BY ds.id
                ORDER BY g.program, f.fullname, ds.defense_date DESC";
$confirmed_result = mysqli_query($conn, $confirmed_query);
$confirmed_by_program = [];

while ($confirmed = mysqli_fetch_assoc($confirmed_result)) {
    $adviser_name = $confirmed['adviser_name'] ?: 'Unassigned Adviser';
    $adviser_id = $confirmed['adviser_id'] ?: 'unassigned';
    $program = $confirmed['program'] ?: 'Unknown';
    $cluster = $confirmed['cluster'] ?: 'No Cluster';
    
    $confirmed_by_program[$program]['advisers'][$adviser_id]['adviser_name'] = $adviser_name;
    $confirmed_by_program[$program]['advisers'][$adviser_id]['clusters'][$cluster]['defenses'][] = $confirmed;
}

// Get failed defenses (need redefense) organized by program
$failed_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, p.title as proposal_title, r.room_name, r.building,
                 f.fullname as adviser_name, f.id as adviser_id, ds.redefense_reason,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                FROM defense_schedules ds 
                JOIN groups g ON ds.group_id = g.id 
                LEFT JOIN clusters c ON g.cluster_id = c.id
                LEFT JOIN faculty f ON c.faculty_id = f.id
                JOIN proposals p ON g.id = p.group_id 
                LEFT JOIN rooms r ON ds.room_id = r.id 
                LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                WHERE ds.status = 'failed'
                GROUP BY ds.id
                ORDER BY g.program, f.fullname, ds.defense_date DESC";
$failed_result = mysqli_query($conn, $failed_query);
$failed_by_program = [];

while ($failed = mysqli_fetch_assoc($failed_result)) {
    $adviser_name = $failed['adviser_name'] ?: 'Unassigned Adviser';
    $adviser_id = $failed['adviser_id'] ?: 'unassigned';
    $program = $failed['program'] ?: 'Unknown';
    $cluster = $failed['cluster'] ?: 'No Cluster';
    
    $failed_by_program[$program]['advisers'][$adviser_id]['adviser_name'] = $adviser_name;
    $failed_by_program[$program]['advisers'][$adviser_id]['clusters'][$cluster]['defenses'][] = $failed;
}

// Get completed defenses organized by program, adviser, and cluster
$completed_query = "SELECT ds.*, g.name as group_name, g.program, c.cluster, p.title as proposal_title, r.room_name, r.building,
                 f.fullname as adviser_name, f.id as adviser_id,
                 GROUP_CONCAT(CONCAT(pm.first_name, ' ', pm.last_name) SEPARATOR ', ') as panel_names
                FROM defense_schedules ds 
                JOIN groups g ON ds.group_id = g.id 
                LEFT JOIN clusters c ON g.cluster_id = c.id
                LEFT JOIN faculty f ON c.faculty_id = f.id
                JOIN proposals p ON g.id = p.group_id 
                LEFT JOIN rooms r ON ds.room_id = r.id 
                LEFT JOIN defense_panel dp ON ds.id = dp.defense_id
                LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
                WHERE ds.status IN ('completed', 'passed')
                GROUP BY ds.id
                ORDER BY g.program, f.fullname, c.cluster, ds.defense_date DESC";
$completed_result = mysqli_query($conn, $completed_query);
$completed_by_program = [];

while ($completed = mysqli_fetch_assoc($completed_result)) {
    $adviser_name = $completed['adviser_name'] ?: 'Unassigned Adviser';
    $adviser_id = $completed['adviser_id'] ?: 'unassigned';
    $program = $completed['program'] ?: 'Unknown';
    $cluster = $completed['cluster'] ?: 'No Cluster';
    
    $completed_by_program[$program]['advisers'][$adviser_id]['adviser_name'] = $adviser_name;
    $completed_by_program[$program]['advisers'][$adviser_id]['clusters'][$cluster]['defenses'][] = $completed;
}

// Get stats for dashboard
$total_proposals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM proposals WHERE status IN ('Completed', 'Approved')"));
$scheduled_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM defense_schedules WHERE status = 'scheduled' AND CONCAT(defense_date, ' ', end_time) > NOW()"));
$confirmed_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM defense_schedules WHERE status = 'passed'"));
$pending_defenses = $total_proposals - $scheduled_defenses;
$completed_defenses = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM defense_schedules WHERE status IN ('completed', 'passed')"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS | Defense Scheduling</title>
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
        .gradient-red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
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
            
            <?php if (isset($_SESSION['defense_status_updated'])): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Some defenses have been automatically moved to evaluation status. They will no longer appear in the Defense Schedule tab.</span>
                    <button onclick="location.reload()" class="ml-4 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">Refresh Now</button>
                </div>
                <?php unset($_SESSION['defense_status_updated']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="flex w-full mb-8 animate-slide-up gap-4">
                <div class="stats-card p-4 relative overflow-hidden flex-1">
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

                <div class="stats-card p-4 relative overflow-hidden flex-1">
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

                <div class="stats-card p-4 relative overflow-hidden flex-1">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-purple-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="gradient-purple p-2 rounded-lg">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">
                            Passed
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $confirmed_defenses; ?></h3>
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Defenses</p>
                </div>

                <div class="stats-card p-4 relative overflow-hidden flex-1">
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

                <div class="stats-card p-4 relative overflow-hidden flex-1">
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
                        <button onclick="switchMainTab('confirmation')" id="confirmationTab" class="main-tab px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:text-primary">Defense Evaluation</button>
                        <button onclick="switchMainTab('completed')" id="completedTab" class="main-tab px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:text-primary">Completed Defenses</button>
                    </div>
                    
                    <!-- Schedules Tab Content -->
                    <div id="schedulesContent" class="main-tab-content">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="flex flex-wrap gap-3">
                                <button onclick="filterStatus('all')" data-filter="all" class="filter-btn px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold transition-all hover:scale-105">All</button>
                                <button onclick="filterStatus('scheduled')" data-filter="scheduled" class="filter-btn px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold transition-all hover:scale-105 hover:bg-gray-200">Scheduled</button>
                                <button onclick="filterStatus('pending')" data-filter="pending" class="filter-btn px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold transition-all hover:scale-105 hover:bg-gray-200">Pending</button>
                            </div>
                            <div class="flex gap-3">
                                <div class="relative">
                                    <input type="text" id="searchInput" placeholder="Search proposals..." onkeyup="handleSearch()" class="pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <button onclick="updateOverdueDefenses()" id="updateOverdueBtn" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 rounded-xl flex items-center font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105" title="Update overdue defenses to evaluation status">
                                    <i class="fas fa-clock mr-2"></i> <span id="overdueText">Update Overdue</span> <span id="overdueCount" class="ml-1 bg-red-500 text-white text-xs rounded-full px-2 py-1 hidden">0</span>
                                </button>
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
                    <div id="confirmationContent" class="main-tab-content hidden">
                        <div class="flex items-center gap-4">
                            <div class="gradient-purple p-3 rounded-xl">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Defense Evaluation</h3>
                        </div>
                    </div>
                    
                    <div id="completedContent" class="main-tab-content hidden">
                        <div class="flex items-center gap-4">
                            <div class="gradient-green p-3 rounded-xl">
                                <i class="fas fa-trophy text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Completed Defenses</h3>
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
                <div class="space-y-6">
                    <?php foreach ($defense_by_program as $program => $program_data): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-b border-gray-200 cursor-pointer" onclick="toggleProgram('<?php echo $program; ?>')">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <i class="fas fa-graduation-cap mr-2"></i><?php echo $program; ?>
                                    <span class="text-sm text-gray-500 ml-2">
                                        <?php 
                                        $total_scheduled = 0;
                                        foreach ($program_data['advisers'] as $adviser_id => $adviser_data) {
                                            foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                $total_scheduled += count($cluster_data['defenses']);
                                            }
                                        }
                                        echo "($total_scheduled scheduled defense" . ($total_scheduled > 1 ? 's' : '') . ")";
                                        ?>
                                    </span>
                                </h3>
                                <i class="fas fa-chevron-down transition-transform" id="icon-<?php echo $program; ?>"></i>
                            </div>
                        </div>
                        <div class="program-content" id="content-<?php echo $program; ?>" style="display: none;">
                            <?php foreach ($program_data['advisers'] as $adviser_id => $adviser_data): ?>
                            <div class="border-b border-gray-100 last:border-b-0">
                                <div class="p-3 bg-gray-50 cursor-pointer" onclick="toggleAdviser('<?php echo $program . '-' . $adviser_id; ?>')">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-gray-700">
                                            <i class="fas fa-user-tie mr-2"></i><?php echo $adviser_data['adviser_name']; ?>
                                            <span class="text-sm text-gray-500 ml-2">
                                                <?php 
                                                $adviser_scheduled = 0;
                                                foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                    $adviser_scheduled += count($cluster_data['defenses']);
                                                }
                                                echo "($adviser_scheduled scheduled defense" . ($adviser_scheduled > 1 ? 's' : '') . ")";
                                                ?>
                                            </span>
                                        </h4>
                                        <i class="fas fa-chevron-down transition-transform text-sm" id="adviser-icon-<?php echo $program . '-' . $adviser_id; ?>"></i>
                                    </div>
                                </div>
                                <div class="adviser-content" id="adviser-content-<?php echo $program . '-' . $adviser_id; ?>" style="display: none;">
                                    <?php foreach ($adviser_data['clusters'] as $cluster => $cluster_data): ?>
                                    <div class="border-b border-gray-100 last:border-b-0">
                                        <div class="p-3 bg-gray-50 cursor-pointer" onclick="toggleCluster('<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>')">
                                            <div class="flex items-center justify-between">
                                                <h5 class="font-medium text-gray-600">
                                                    <i class="fas fa-layer-group mr-2"></i>Cluster <?php echo $cluster; ?>
                                                    <span class="text-sm text-gray-500 ml-2">
                                                        (<?php echo count($cluster_data['defenses']); ?> scheduled defense<?php echo count($cluster_data['defenses']) > 1 ? 's' : ''; ?>)
                                                    </span>
                                                </h5>
                                                <i class="fas fa-chevron-down transition-transform text-sm" id="cluster-icon-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="cluster-content" id="cluster-content-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>" style="display: none;">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                                                <?php foreach ($cluster_data['defenses'] as $schedule): ?>
                    <!-- Scheduled Defense Card -->
                    <div class="defense-card bg-gradient-to-br from-white via-blue-50 to-indigo-100 border border-blue-200 rounded-2xl shadow-lg p-6 flex flex-col justify-between relative overflow-hidden" 
                         data-status="<?php echo $schedule['status']; ?>" 
                         data-defense-id="<?php echo $schedule['id']; ?>"
                         data-defense-date="<?php echo $schedule['defense_date']; ?>"
                         data-end-time="<?php echo $schedule['end_time']; ?>">
                        
                        <!-- Decorative elements -->
                        <div class="absolute top-0 right-0 w-20 h-20 bg-blue-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-indigo-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                        
                        <div class="relative z-10">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center">
                                    <div class="gradient-blue p-3 rounded-xl mr-3 shadow-lg">
                                        <i class="fas fa-calendar-alt text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 leading-tight"><?php echo $schedule['group_name']; ?></h3>
                                        <p class="text-xs text-blue-600 font-medium"><?php echo $schedule['proposal_title'] ?? 'No Title'; ?></p>
                                        <p class="text-xs text-gray-500 font-medium">
                                            <?php 
                                            if ($schedule['defense_type'] == 'redefense') {
                                                echo 'Redefense Defense';
                                                if (!empty($schedule['parent_defense_id'])) {
                                                    echo ' (Retake)';
                                                }
                                            } else {
                                                echo ucfirst(str_replace('_', ' ', $schedule['defense_type'])) . ' Defense';
                                            }
                                            ?>
                                        </p>
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
                            <div class="flex gap-2">
                                <?php if ($schedule['status'] == 'scheduled'): ?>
                                    <?php 
                                    $current_timestamp = strtotime(date('Y-m-d H:i:s'));
                                    $defense_end_timestamp = strtotime($schedule['defense_date'] . ' ' . $schedule['end_time']);
                                    $is_past_end_time = $defense_end_timestamp <= $current_timestamp;
                                    ?>
                                    <?php if ($is_past_end_time): ?>
                                        <button onclick="confirmDefense(<?php echo $schedule['id']; ?>)" class="flex-1 bg-gradient-to-r from-purple-400 to-purple-600 hover:from-purple-500 hover:to-purple-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Confirm Defense">
                                            <i class="fas fa-check-circle mr-1"></i>Confirm
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($schedule['status'] == 'completed'): ?>
                                    <span class="flex-1 bg-gradient-to-r from-green-400 to-green-600 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center">
                                        <i class="fas fa-trophy mr-1"></i>Final Completed
                                    </span>
                                <?php endif; ?>
                                <?php if ($schedule['status'] == 'failed'): ?>
                                    <button onclick="scheduleRedefense(<?php echo $schedule['group_id']; ?>, <?php echo $schedule['id']; ?>, '<?php echo addslashes($schedule['group_name']); ?>', '<?php echo addslashes($schedule['proposal_title']); ?>')" class="flex-1 bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Schedule Redefense">
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
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Pending / Unscheduled Groups -->
                    <?php foreach ($pending_by_program as $program => $program_data): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-orange-200">
                        <div class="p-4 border-b border-orange-200 cursor-pointer" onclick="togglePendingProgramDefenses('<?php echo $program; ?>')">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-orange-600">
                                    <i class="fas fa-graduation-cap mr-2"></i><?php echo $program; ?>
                                    <span class="text-sm text-gray-500 ml-2">
                                        <?php 
                                        $total_pending = 0;
                                        foreach ($program_data['advisers'] as $adviser_id => $adviser_data) {
                                            foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                $total_pending += count($cluster_data['groups']);
                                            }
                                        }
                                        echo "($total_pending pending group" . ($total_pending > 1 ? 's' : '') . ")";
                                        ?>
                                    </span>
                                </h3>
                                <i class="fas fa-chevron-down transition-transform" id="pending-program-icon-<?php echo $program; ?>"></i>
                            </div>
                        </div>
                        <div class="pending-program-content" id="pending-program-content-<?php echo $program; ?>" style="display: none;">
                            <?php foreach ($program_data['advisers'] as $adviser_id => $adviser_data): ?>
                            <div class="border-b border-gray-100 last:border-b-0">
                                <div class="p-3 bg-orange-50 cursor-pointer" onclick="togglePendingAdviserDefenses('<?php echo $program . '-' . $adviser_id; ?>')">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-orange-700">
                                            <i class="fas fa-user-tie mr-2"></i><?php echo $adviser_data['adviser_name']; ?>
                                            <span class="text-sm text-gray-500 ml-2">
                                                <?php 
                                                $adviser_pending = 0;
                                                foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                    $adviser_pending += count($cluster_data['groups']);
                                                }
                                                echo "($adviser_pending pending group" . ($adviser_pending > 1 ? 's' : '') . ")";
                                                ?>
                                            </span>
                                        </h4>
                                        <i class="fas fa-chevron-down transition-transform text-sm" id="pending-adviser-icon-<?php echo $program . '-' . $adviser_id; ?>"></i>
                                    </div>
                                </div>
                                <div class="pending-adviser-content" id="pending-adviser-content-<?php echo $program . '-' . $adviser_id; ?>" style="display: none;">
                                    <?php foreach ($adviser_data['clusters'] as $cluster => $cluster_data): ?>
                                    <div class="border-b border-gray-100 last:border-b-0">
                                        <div class="p-3 bg-orange-50 cursor-pointer" onclick="togglePendingClusterDefenses('<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>')">
                                            <div class="flex items-center justify-between">
                                                <h5 class="font-medium text-orange-600">
                                                    <i class="fas fa-layer-group mr-2"></i>Cluster <?php echo $cluster; ?>
                                                    <span class="text-sm text-gray-500 ml-2">
                                                        (<?php echo count($cluster_data['groups']); ?> group<?php echo count($cluster_data['groups']) > 1 ? 's' : ''; ?>)
                                                    </span>
                                                </h5>
                                                <i class="fas fa-chevron-down transition-transform text-sm" id="pending-cluster-icon-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="pending-cluster-content" id="pending-cluster-content-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>" style="display: none;">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                                                <?php foreach ($cluster_data['groups'] as $group): ?>
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
                                                        <button onclick="scheduleDefenseForGroup(<?php echo $group['id']; ?>, '<?php echo addslashes($group['name']); ?>', '<?php echo addslashes($group['proposal_title']); ?>')" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white py-3 px-4 rounded-xl text-sm font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105">
                                                            <i class="fas fa-calendar-plus mr-2"></i>Schedule Defense
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
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($pending_by_program)): ?>
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <i class="fas fa-graduation-cap text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No pending groups found. All groups have been scheduled for defense.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($defense_by_program)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i class="far fa-calendar-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">No scheduled defenses found. All defenses have either been completed or moved to evaluation.</p>
                </div>
                <?php endif; ?>
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

              <!-- Defense Confirmation Cards -->
            <div id="confirmationCards" class="stats-card rounded-2xl p-8 animate-scale-in hidden">
                        <div class="flex items-center mb-8">
                            <div class="gradient-purple p-3 rounded-xl mr-4">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800">Defense Evaluation</h2>
                        </div>
                <div class="space-y-6 mb-8 animate-fade-in">
                    <?php foreach ($confirmed_by_program as $program => $program_data): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-b border-gray-200 cursor-pointer" onclick="toggleProgramDefenses('<?php echo $program; ?>')">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-purple-700">
                                    <i class="fas fa-graduation-cap mr-2"></i><?php echo $program; ?>
                                    <span class="text-sm text-gray-500 ml-2">
                                        <?php 
                                        $total_defenses = 0;
                                        foreach ($program_data['advisers'] as $adviser_id => $adviser_data) {
                                            foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                $total_defenses += count($cluster_data['defenses']);
                                            }
                                        }
                                        echo "($total_defenses defense" . ($total_defenses > 1 ? 's' : '') . ")";
                                        ?>
                                    </span>
                                </h3>
                                <i class="fas fa-chevron-down transition-transform" id="program-icon-<?php echo $program; ?>"></i>
                            </div>
                        </div>
                        <div class="program-content" id="program-content-<?php echo $program; ?>" style="display: none;">
                            <?php foreach ($program_data['advisers'] as $adviser_id => $adviser_data): ?>
                            <div class="border-b border-gray-100 last:border-b-0">
                                <div class="p-3 bg-purple-50 cursor-pointer" onclick="toggleAdviserDefenses('<?php echo $program . '-' . $adviser_id; ?>')">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-purple-700">
                                            <i class="fas fa-user-tie mr-2"></i><?php echo $adviser_data['adviser_name']; ?>
                                            <span class="text-sm text-gray-500 ml-2">
                                                <?php 
                                                $adviser_defenses = 0;
                                                foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                    $adviser_defenses += count($cluster_data['defenses']);
                                                }
                                                echo "($adviser_defenses defense" . ($adviser_defenses > 1 ? 's' : '') . ")";
                                                ?>
                                            </span>
                                        </h4>
                                        <i class="fas fa-chevron-down transition-transform text-sm" id="adviser-icon-<?php echo $program . '-' . $adviser_id; ?>"></i>
                                    </div>
                                </div>
                                <div class="adviser-content" id="adviser-content-<?php echo $program . '-' . $adviser_id; ?>" style="display: none;">
                                    <?php foreach ($adviser_data['clusters'] as $cluster => $cluster_data): ?>
                                    <div class="border-b border-gray-100 last:border-b-0">
                                        <div class="p-3 bg-purple-50 cursor-pointer" onclick="toggleClusterDefenses('<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>')">
                                            <div class="flex items-center justify-between">
                                                <h5 class="font-medium text-purple-600">
                                                    <i class="fas fa-layer-group mr-2"></i>Cluster <?php echo $cluster; ?>
                                                    <span class="text-sm text-gray-500 ml-2">
                                                        (<?php echo count($cluster_data['defenses']); ?> defense<?php echo count($cluster_data['defenses']) > 1 ? 's' : ''; ?>)
                                                    </span>
                                                </h5>
                                                <i class="fas fa-chevron-down transition-transform text-sm" id="cluster-icon-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="cluster-content" id="cluster-content-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>" style="display: none;">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                                                <?php foreach ($cluster_data['defenses'] as $confirmed): ?>
                        <div class="defense-card bg-gradient-to-br from-white via-purple-50 to-violet-100 border border-purple-200 rounded-2xl shadow-lg p-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-purple-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                            <div class="absolute bottom-0 left-0 w-16 h-16 bg-violet-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center">
                                        <div class="gradient-purple p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-check-circle text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 leading-tight"><?php echo $confirmed['group_name']; ?></h3>
                                            <p class="text-xs text-purple-600 font-medium"><?php echo $confirmed['proposal_title']; ?></p>
                                            <p class="text-xs text-gray-500">
                                                <?php 
                                                if ($confirmed['defense_type'] == 'redefense') {
                                                    echo 'Redefense Defense';
                                                    if (!empty($confirmed['parent_defense_id'])) {
                                                        echo ' (Retake)';
                                                    }
                                                } else {
                                                    echo ucfirst($confirmed['defense_type']) . ' Defense';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="bg-gradient-to-r from-purple-400 to-violet-600 text-white px-1.5 py-0.5 rounded-full text-xs font-normal shadow-sm flex items-center">
                                        <i class="fas fa-check mr-1 text-xs"></i>Passed
                                    </span>
                                </div>

                                <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                    <div class="grid grid-cols-1 gap-3">
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-calendar text-purple-500 mr-3 w-4"></i>
                                            <span class="text-gray-700 font-medium"><?php echo date('M j, Y', strtotime($confirmed['defense_date'])); ?></span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-clock text-purple-500 mr-3 w-4"></i>
                                            <span class="text-gray-700 font-medium">
                                                <?php echo date('g:i A', strtotime($confirmed['start_time'])); ?> - 
                                                <?php echo date('g:i A', strtotime($confirmed['end_time'])); ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-map-marker-alt text-purple-500 mr-3 w-4"></i>
                                            <span class="text-gray-700 font-medium"><?php echo $confirmed['building'] . ' ' . $confirmed['room_name']; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button onclick="markDefensePassed(<?php echo $confirmed['id']; ?>)" class="flex-1 bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Mark as Passed">
                                        <i class="fas fa-check mr-1"></i>Pass
                                    </button>
                                    <button onclick="markDefenseFailed(<?php echo $confirmed['id']; ?>)" class="flex-1 bg-gradient-to-r from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Mark as Failed">
                                        <i class="fas fa-times mr-1"></i>Fail
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
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($confirmed_by_program)): ?>
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <i class="fas fa-check-circle text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No passed defenses awaiting evaluation</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Failed Defenses Cards (Redefense Required) -->
            <div id="failedCards" class="stats-card rounded-2xl p-8 animate-scale-in hidden">
                <div class="flex items-center mb-8">
                    <div class="gradient-red p-3 rounded-xl mr-4">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Failed Defenses (Redefense Required)</h2>
                </div>
                <div class="space-y-6 mb-8 animate-fade-in">
                    <?php foreach ($failed_by_program as $program => $program_data): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-b border-gray-200 cursor-pointer" onclick="toggleFailedProgramDefenses('<?php echo $program; ?>')">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-red-700">
                                    <i class="fas fa-graduation-cap mr-2"></i><?php echo $program; ?>
                                    <span class="text-sm text-gray-500 ml-2">
                                        <?php 
                                        $total_failed = 0;
                                        foreach ($program_data['advisers'] as $adviser_id => $adviser_data) {
                                            foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                $total_failed += count($cluster_data['defenses']);
                                            }
                                        }
                                        echo "($total_failed failed defense" . ($total_failed > 1 ? 's' : '') . ")";
                                        ?>
                                    </span>
                                </h3>
                                <i class="fas fa-chevron-down transition-transform" id="failed-program-icon-<?php echo $program; ?>"></i>
                            </div>
                        </div>
                        <div class="program-content" id="failed-program-content-<?php echo $program; ?>" style="display: none;">
                            <?php foreach ($program_data['advisers'] as $adviser_id => $adviser_data): ?>
                            <div class="border-b border-gray-100 last:border-b-0">
                                <div class="p-3 bg-red-50 cursor-pointer" onclick="toggleFailedAdviserDefenses('<?php echo $program . '-' . $adviser_id; ?>')">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-red-700">
                                            <i class="fas fa-user-tie mr-2"></i><?php echo $adviser_data['adviser_name']; ?>
                                            <span class="text-sm text-gray-500 ml-2">
                                                <?php 
                                                $adviser_failed = 0;
                                                foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                    $adviser_failed += count($cluster_data['defenses']);
                                                }
                                                echo "($adviser_failed failed defense" . ($adviser_failed > 1 ? 's' : '') . ")";
                                                ?>
                                            </span>
                                        </h4>
                                        <i class="fas fa-chevron-down transition-transform text-sm" id="failed-adviser-icon-<?php echo $program . '-' . $adviser_id; ?>"></i>
                                    </div>
                                </div>
                                <div class="adviser-content" id="failed-adviser-content-<?php echo $program . '-' . $adviser_id; ?>" style="display: none;">
                                    <?php foreach ($adviser_data['clusters'] as $cluster => $cluster_data): ?>
                                    <div class="border-b border-gray-100 last:border-b-0">
                                        <div class="p-3 bg-red-50 cursor-pointer" onclick="toggleFailedClusterDefenses('<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>')">
                                            <div class="flex items-center justify-between">
                                                <h5 class="font-medium text-red-600">
                                                    <i class="fas fa-layer-group mr-2"></i>Cluster <?php echo $cluster; ?>
                                                    <span class="text-sm text-gray-500 ml-2">
                                                        (<?php echo count($cluster_data['defenses']); ?> failed defense<?php echo count($cluster_data['defenses']) > 1 ? 's' : ''; ?>)
                                                    </span>
                                                </h5>
                                                <i class="fas fa-chevron-down transition-transform text-sm" id="failed-cluster-icon-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="cluster-content" id="failed-cluster-content-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>" style="display: none;">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                                                <?php foreach ($cluster_data['defenses'] as $failed): ?>
                        <div class="defense-card bg-gradient-to-br from-white via-red-50 to-pink-100 border border-red-200 rounded-2xl shadow-lg p-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-red-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                            <div class="absolute bottom-0 left-0 w-16 h-16 bg-pink-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center">
                                        <div class="gradient-red p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-times-circle text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 leading-tight"><?php echo $failed['group_name']; ?></h3>
                                            <p class="text-xs text-red-600 font-medium"><?php echo $failed['proposal_title']; ?></p>
                                            <p class="text-xs text-gray-500">
                                                <?php 
                                                if ($failed['defense_type'] == 'redefense') {
                                                    echo 'Redefense Defense';
                                                    if (!empty($failed['parent_defense_id'])) {
                                                        echo ' (Retake)';
                                                    }
                                                } else {
                                                    echo ucfirst($failed['defense_type']) . ' Defense';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="bg-gradient-to-r from-red-400 to-pink-600 text-white px-1.5 py-0.5 rounded-full text-xs font-normal shadow-sm flex items-center">
                                        <i class="fas fa-times mr-1 text-xs"></i>Failed
                                    </span>
                                </div>

                                <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                    <div class="grid grid-cols-1 gap-3">
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-calendar text-red-500 mr-3 w-4"></i>
                                            <span class="text-gray-700 font-medium"><?php echo date('M j, Y', strtotime($failed['defense_date'])); ?></span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-clock text-red-500 mr-3 w-4"></i>
                                            <span class="text-gray-700 font-medium">
                                                <?php echo date('g:i A', strtotime($failed['start_time'])); ?> - 
                                                <?php echo date('g:i A', strtotime($failed['end_time'])); ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-3 w-4"></i>
                                            <span class="text-gray-700 font-medium"><?php echo $failed['building'] . ' ' . $failed['room_name']; ?></span>
                                        </div>
                                        <?php if (!empty($failed['redefense_reason'])): ?>
                                        <div class="flex items-start text-sm">
                                            <i class="fas fa-comment text-red-500 mr-3 w-4 mt-1"></i>
                                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($failed['redefense_reason']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button onclick="scheduleRedefense(<?php echo $failed['group_id']; ?>, <?php echo $failed['id']; ?>, '<?php echo addslashes($failed['group_name']); ?>', '<?php echo addslashes($failed['proposal_title']); ?>')" class="flex-1 bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Schedule Redefense">
                                        <i class="fas fa-redo mr-1"></i>Schedule Redefense
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
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($failed_by_program)): ?>
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <i class="fas fa-check-circle text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No failed defenses requiring redefense</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Completed Defenses Cards -->
            <div id="completedCards" class="stats-card rounded-2xl p-8 animate-scale-in hidden">
                <div class="flex items-center mb-8">
                    <div class="gradient-green p-3 rounded-xl mr-4">
                        <i class="fas fa-trophy text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Completed Defenses</h2>
                </div>
                
                <div class="space-y-6 mb-8 animate-fade-in">
                    <?php foreach ($completed_by_program as $program => $program_data): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-b border-gray-200 cursor-pointer" onclick="toggleCompletedProgram('<?php echo $program; ?>')">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-green-700">
                                    <i class="fas fa-graduation-cap mr-2"></i><?php echo $program; ?>
                                    <span class="text-sm text-gray-500 ml-2">
                                        <?php 
                                        $total_completed = 0;
                                        foreach ($program_data['advisers'] as $adviser_id => $adviser_data) {
                                            foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                $total_completed += count($cluster_data['defenses']);
                                            }
                                        }
                                        echo "($total_completed completed defense" . ($total_completed > 1 ? 's' : '') . ")";
                                        ?>
                                    </span>
                                </h3>
                                <i class="fas fa-chevron-down transition-transform" id="completed-icon-<?php echo $program; ?>"></i>
                            </div>
                        </div>
                        <div class="program-content" id="completed-content-<?php echo $program; ?>" style="display: none;">
                            <?php foreach ($program_data['advisers'] as $adviser_id => $adviser_data): ?>
                            <div class="border-b border-gray-100 last:border-b-0">
                                <div class="p-3 bg-green-50 cursor-pointer" onclick="toggleCompletedAdviser('<?php echo $program . '-' . $adviser_id; ?>')">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-green-700">
                                            <i class="fas fa-user-tie mr-2"></i><?php echo $adviser_data['adviser_name']; ?>
                                            <span class="text-sm text-gray-500 ml-2">
                                                <?php 
                                                $adviser_completed = 0;
                                                foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                    $adviser_completed += count($cluster_data['defenses']);
                                                }
                                                echo "($adviser_completed completed defense" . ($adviser_completed > 1 ? 's' : '') . ")";
                                                ?>
                                            </span>
                                        </h4>
                                        <i class="fas fa-chevron-down transition-transform text-sm" id="completed-adviser-icon-<?php echo $program . '-' . $adviser_id; ?>"></i>
                                    </div>
                                </div>
                                <div class="adviser-content" id="completed-adviser-content-<?php echo $program . '-' . $adviser_id; ?>" style="display: none;">
                                    <?php foreach ($adviser_data['clusters'] as $cluster => $cluster_data): ?>
                                    <div class="border-b border-gray-100 last:border-b-0">
                                        <div class="p-3 bg-green-50 cursor-pointer" onclick="toggleCompletedCluster('<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>')">
                                            <div class="flex items-center justify-between">
                                                <h5 class="font-medium text-green-600">
                                                    <i class="fas fa-layer-group mr-2"></i>Cluster <?php echo $cluster; ?>
                                                    <span class="text-sm text-gray-500 ml-2">
                                                        (<?php echo count($cluster_data['defenses']); ?> completed defense<?php echo count($cluster_data['defenses']) > 1 ? 's' : ''; ?>)
                                                    </span>
                                                </h5>
                                                <i class="fas fa-chevron-down transition-transform text-sm" id="completed-cluster-icon-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="cluster-content" id="completed-cluster-content-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>" style="display: none;">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                                                <?php foreach ($cluster_data['defenses'] as $completed): ?>
                                        <div class="defense-card bg-gradient-to-br from-white via-green-50 to-emerald-100 border border-green-200 rounded-2xl shadow-lg p-6 relative overflow-hidden">
                                            <div class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                                            <div class="absolute bottom-0 left-0 w-16 h-16 bg-emerald-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                                            
                                            <div class="relative z-10">
                                                <div class="flex justify-between items-start mb-4">
                                                    <div class="flex items-center">
                                                        <div class="gradient-green p-3 rounded-xl mr-3 shadow-lg">
                                                            <i class="fas fa-trophy text-white text-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h3 class="text-lg font-bold text-gray-900 leading-tight"><?php echo $completed['group_name']; ?></h3>
                                                            <p class="text-xs text-green-600 font-medium"><?php echo $completed['proposal_title']; ?></p>
                                                            <p class="text-xs text-gray-500"><?php echo ucfirst($completed['defense_type']); ?> Defense</p>
                                                        </div>
                                                    </div>
                                                    <span class="bg-gradient-to-r from-green-400 to-emerald-600 text-white px-1.5 py-0.5 rounded-full text-xs font-normal shadow-sm flex items-center">
                                                        <i class="fas fa-check mr-1 text-xs"></i>Completed
                                                    </span>
                                                </div>

                                            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                                <div class="grid grid-cols-1 gap-3">
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-calendar text-green-500 mr-3 w-4"></i>
                                                        <span class="text-gray-700 font-medium"><?php echo date('M j, Y', strtotime($completed['defense_date'])); ?></span>
                                                    </div>
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-clock text-green-500 mr-3 w-4"></i>
                                                        <span class="text-gray-700 font-medium">
                                                            <?php echo date('g:i A', strtotime($completed['start_time'])); ?> - 
                                                            <?php echo date('g:i A', strtotime($completed['end_time'])); ?>
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-map-marker-alt text-green-500 mr-3 w-4"></i>
                                                        <span class="text-gray-700 font-medium"><?php echo $completed['building'] . ' ' . $completed['room_name']; ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="flex gap-2">
                                                    <?php if ($completed['status'] == 'passed'): ?>
                                                        <?php if ($completed['defense_type'] == 'pre_oral'): ?>
                                                            <button onclick="scheduleFinalDefense(<?php echo $completed['group_id']; ?>, <?php echo $completed['id']; ?>, '<?php echo addslashes($completed['group_name']); ?>', '<?php echo addslashes($completed['proposal_title']); ?>')" class="flex-1 bg-gradient-to-r from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Schedule Final Defense">
                                                                <i class="fas fa-arrow-right mr-1"></i>Final Defense
                                                            </button>
                                                        <?php else: ?>
                                                            <button onclick="markDefenseCompleted(<?php echo $completed['id']; ?>)" class="flex-1 bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Mark as Completed">
                                                                <i class="fas fa-check mr-1"></i>Mark Completed
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="flex-1 bg-gradient-to-r from-green-400 to-green-600 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center">
                                                            <i class="fas fa-trophy mr-1"></i>Final Completed
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
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
                    
                    <?php if (empty($completed_by_program)): ?>
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <i class="fas fa-trophy text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No completed defenses yet</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Defenses Section -->
              <div class="flex items-center mt-10 mb-6">
                <div class="gradient-green p-3 rounded-xl mr-4">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Upcoming Defenses</h2>
            </div>
            <div class="space-y-6 mb-8 animate-fade-in">
                <?php foreach ($upcoming_by_program as $program => $program_data): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200 cursor-pointer" onclick="toggleUpcomingProgram('<?php echo $program; ?>')">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-green-700"><?php echo $program; ?> - Upcoming</h3>
                            <i class="fas fa-chevron-down transition-transform" id="upcoming-icon-<?php echo $program; ?>"></i>
                        </div>
                    </div>
                    <div class="program-content" id="upcoming-content-<?php echo $program; ?>" style="display: none;">
                        <?php foreach ($program_data['advisers'] as $adviser_id => $adviser_data): ?>
                        <div class="border-b border-gray-100 last:border-b-0">
                            <div class="p-3 bg-green-50 cursor-pointer" onclick="toggleUpcomingAdviser('<?php echo $program . '-' . $adviser_id; ?>')">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-green-700">
                                        <i class="fas fa-user-tie mr-2"></i><?php echo $adviser_data['adviser_name']; ?>
                                        <span class="text-sm text-gray-500 ml-2">
                                            <?php 
                                            $adviser_upcoming = 0;
                                            foreach ($adviser_data['clusters'] as $cluster => $cluster_data) {
                                                $adviser_upcoming += count($cluster_data['defenses']);
                                            }
                                            echo "($adviser_upcoming upcoming defense" . ($adviser_upcoming > 1 ? 's' : '') . ")";
                                            ?>
                                        </span>
                                    </h4>
                                    <i class="fas fa-chevron-down transition-transform text-sm" id="upcoming-adviser-icon-<?php echo $program . '-' . $adviser_id; ?>"></i>
                                </div>
                            </div>
                            <div class="upcoming-adviser-content" id="upcoming-adviser-content-<?php echo $program . '-' . $adviser_id; ?>" style="display: none;">
                                <?php foreach ($adviser_data['clusters'] as $cluster => $cluster_data): ?>
                                <div class="border-b border-gray-100 last:border-b-0">
                                    <div class="p-3 bg-green-50 cursor-pointer" onclick="toggleUpcomingCluster('<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>')">
                                        <div class="flex items-center justify-between">
                                            <h5 class="font-medium text-green-600">
                                                <i class="fas fa-layer-group mr-2"></i>Cluster <?php echo $cluster; ?>
                                                <span class="text-sm text-gray-500 ml-2">
                                                    (<?php echo count($cluster_data['defenses']); ?> upcoming defense<?php echo count($cluster_data['defenses']) > 1 ? 's' : ''; ?>)
                                                </span>
                                            </h5>
                                            <i class="fas fa-chevron-down transition-transform text-sm" id="upcoming-cluster-icon-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>"></i>
                                        </div>
                                    </div>
                                    <div class="cluster-content" id="upcoming-cluster-content-<?php echo $program . '-' . $adviser_id . '-' . $cluster; ?>" style="display: none;">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                                            <?php foreach ($cluster_data['defenses'] as $upcoming): ?>
                                            <div class="defense-card bg-gradient-to-br from-white via-green-50 to-emerald-100 border border-green-200 rounded-2xl shadow-lg p-6 relative overflow-hidden">
                                                <!-- Decorative elements -->
                                                <div class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                                                <div class="absolute bottom-0 left-0 w-16 h-16 bg-emerald-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                                                
                                                <div class="relative z-10">
                                                    <!-- Header -->
                                                    <div class="flex justify-between items-start mb-4">
                                                        <div class="flex items-center">
                                                            <div class="gradient-green p-3 rounded-xl mr-3 shadow-lg">
                                                                <i class="fas fa-calendar-check text-white text-lg"></i>
                                                            </div>
                                                            <div>
                                                                <h3 class="text-lg font-bold text-gray-900 leading-tight"><?php echo $upcoming['group_name']; ?></h3>
                                                                <p class="text-xs text-green-600 font-medium"><?php echo $upcoming['proposal_title']; ?></p>
                                                                <p class="text-xs text-gray-500 font-medium">
                                                                    <?php 
                                                                    if ($upcoming['defense_type'] == 'redefense') {
                                                                        echo 'Redefense Defense';
                                                                        if (!empty($upcoming['parent_defense_id'])) {
                                                                            echo ' (Retake)';
                                                                        }
                                                                    } else {
                                                                        echo ucfirst(str_replace('_', ' ', $upcoming['defense_type'])) . ' Defense';
                                                                    }
                                                                    ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <span class="bg-gradient-to-r from-green-400 to-emerald-600 text-white px-1.5 py-0.5 rounded-full text-xs font-normal shadow-sm flex items-center">
                                                            <i class="fas fa-clock mr-1 text-xs"></i>Upcoming
                                                        </span>
                                                    </div>

                                                    <!-- Details Section -->
                                                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                                        <div class="grid grid-cols-1 gap-3">
                                                            <div class="flex items-center text-sm">
                                                                <i class="fas fa-calendar text-green-500 mr-3 w-4"></i>
                                                                <span class="text-gray-700 font-medium"><?php echo date('M j, Y', strtotime($upcoming['defense_date'])); ?></span>
                                                            </div>
                                                            <div class="flex items-center text-sm">
                                                                <i class="fas fa-clock text-green-500 mr-3 w-4"></i>
                                                                <span class="text-gray-700 font-medium">
                                                                    <?php echo date('g:i A', strtotime($upcoming['start_time'])); ?> - 
                                                                    <?php echo date('g:i A', strtotime($upcoming['end_time'])); ?>
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center text-sm">
                                                                <i class="fas fa-map-marker-alt text-green-500 mr-3 w-4"></i>
                                                                <span class="text-gray-700 font-medium"><?php echo $upcoming['building'] . ' ' . $upcoming['room_name']; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Action -->
                                                    <button onclick="viewUpcomingDefense(<?php echo htmlspecialchars(json_encode($upcoming)); ?>)" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white py-3 px-4 rounded-xl text-sm font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105">
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
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($upcoming_by_program)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i class="far fa-calendar-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">No upcoming defenses scheduled</p>
                </div>
                <?php endif; ?>
            </div>
        </main>  <!-- Close main tag here -->
    </div>    

    <!-- Schedule Defense Modal -->
    <div id="proposalModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto custom-scrollbar-blue">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 border-0">
                    <h3 class="text-lg font-bold flex items-center" id="modal-title">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-calendar-plus text-white text-sm"></i>
                        </div>
                        Schedule Defense
                    </h3>
                    <p class="text-blue-100 mt-1 text-sm">Schedule a new defense session for the selected group.</p>
                </div>
            <form method="POST" action="" class="p-6" onsubmit="return validateDefenseDuration()">
                <input type="hidden" name="defense_type" id="defense_type" value="pre_oral">
                <input type="hidden" name="parent_defense_id" id="parent_defense_id">
                
                <input type="hidden" name="group_id" id="group_id">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Selected Group</label>
                    <div id="selected_group_display" class="px-3 py-2 bg-gray-100 rounded-lg text-sm">No group selected</div>
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
                        <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg bg-gray-50" id="chairperson-list">
                            <?php 
                            $chairpersons = array_filter($accepted_panel_members, function($member) {
                                return $member['role'] === 'chairperson';
                            });
                            foreach ($chairpersons as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all panel-member-item" data-program="<?php echo strtolower($panel_member['program']); ?>">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>|chair" class="mr-3 rounded text-primary focus:ring-primary">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <div class="text-xs text-purple-600 mt-1"><?php echo ucfirst($panel_member['program']); ?> Program</div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select chairperson for this defense schedule.</p>
                        <div id="no-chairpersons-message" class="text-center p-6 border rounded-lg bg-gray-50 hidden">
                            <i class="fas fa-user-tie text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No chairpersons found for this program.</p>
                        </div>
                    </div>
                    
                    <!-- Members Panel Content -->
                    <div class="panel-content" data-tab="member">
                        <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg bg-gray-50" id="members-list">
                            <?php 
                            $members = array_filter($accepted_panel_members, function($member) {
                                return $member['role'] === 'member';
                            });
                            foreach ($members as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all panel-member-item" data-program="<?php echo strtolower($panel_member['program']); ?>">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>|member" class="mr-3 rounded text-primary focus:ring-primary">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <div class="text-xs text-blue-600 mt-1"><?php echo $panel_member['specialization']; ?></div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select panel members for this defense schedule.</p>
                        <div id="no-members-message" class="text-center p-6 border rounded-lg bg-gray-50 hidden">
                            <i class="fas fa-users text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No panel members found for this program.</p>
                        </div>
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
                    <label class="block text-gray-700 text-sm font-medium mb-2">Group</label>
                    <p id="edit_group_name" class="px-3 py-2 bg-gray-100 rounded-lg"></p>
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
                        <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-scroll p-2 border rounded-lg bg-gray-50" id="edit-chairperson-list">
                            <?php 
                            $chairpersons = array_filter($accepted_panel_members, function($member) {
                                return $member['role'] === 'chairperson';
                            });
                            foreach ($chairpersons as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all edit-panel-member-item" data-program="<?php echo strtolower($panel_member['program']); ?>">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>|chair" class="edit-panel-member mr-3 rounded text-primary focus:ring-primary" data-id="<?php echo $panel_member['id']; ?>">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <div class="text-xs text-purple-600 mt-1"><?php echo ucfirst($panel_member['program']); ?> Program</div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select chairperson for this defense schedule.</p>
                        <div id="edit-no-chairpersons-message" class="text-center p-6 border rounded-lg bg-gray-50 hidden">
                            <i class="fas fa-user-tie text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No chairpersons found for this program.</p>
                        </div>
                    </div>
                    
                    <!-- Edit Members Panel Content -->
                    <div class="panel-content" data-tab="edit_member">
                        <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-scroll p-2 border rounded-lg bg-gray-50" id="edit-members-list">
                            <?php 
                            $members = array_filter($accepted_panel_members, function($member) {
                                return $member['role'] === 'member';
                            });
                            foreach ($members as $panel_member): ?>
                            <label class="flex items-center p-3 hover:bg-white rounded-lg cursor-pointer border border-transparent hover:border-blue-200 transition-all edit-panel-member-item" data-program="<?php echo strtolower($panel_member['program']); ?>">
                                <input type="checkbox" name="panel_members[]" value="<?php echo $panel_member['id']; ?>|member" class="edit-panel-member mr-3 rounded text-primary focus:ring-primary" data-id="<?php echo $panel_member['id']; ?>">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo $panel_member['first_name'] . ' ' . $panel_member['last_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $panel_member['email']; ?></div>
                                    <div class="text-xs text-blue-600 mt-1"><?php echo $panel_member['specialization']; ?></div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select panel members for this defense schedule.</p>
                        <div id="edit-no-members-message" class="text-center p-6 border rounded-lg bg-gray-50 hidden">
                            <i class="fas fa-users text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm mb-2">No panel members found for this program.</p>
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
            const programs = document.querySelectorAll('.bg-white.rounded-xl.shadow-sm.border');
            
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
            
            // Show/hide program sections based on whether they have visible cards
            programs.forEach(program => {
                const hasVisibleCards = Array.from(program.querySelectorAll('.defense-card')).some(card => !card.classList.contains('hidden'));
                if (hasVisibleCards) {
                    program.classList.remove('hidden');
                } else {
                    program.classList.add('hidden');
                }
            });
            
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.getAttribute('data-filter') === status) {
                    btn.classList.add('bg-primary', 'text-white');
                    btn.classList.remove('bg-gray-100', 'text-gray-700');
                } else {
                    btn.classList.remove('bg-primary', 'text-white');
                    btn.classList.add('bg-gray-100', 'text-gray-700');
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
        
        // Function to update overdue defenses
        function updateOverdueDefenses() {
            // Count overdue defenses first
            const scheduledDefenses = document.querySelectorAll('[data-status="scheduled"]');
            const currentTime = new Date();
            let overdueCount = 0;
            
            scheduledDefenses.forEach(defense => {
                const defenseDate = defense.getAttribute('data-defense-date');
                const endTime = defense.getAttribute('data-end-time');
                if (defenseDate && endTime) {
                    const defenseEndTime = new Date(defenseDate + ' ' + endTime);
                    if (defenseEndTime <= currentTime) {
                        overdueCount++;
                    }
                }
            });
            
            if (overdueCount === 0) {
                alert('No overdue defenses found. All scheduled defenses are still within their time limits.');
                return;
            }
            
            if (confirm(`Found ${overdueCount} overdue defense(s). Update them to evaluation status? This will move them from the Defense Schedule tab to the Defense Evaluation tab.`)) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
                button.disabled = true;
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="update_overdue_defenses" value="1">`;
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
        
        // Function to switch between edit panel tabs
        function switchEditPanelTab(tabName) {
            document.querySelectorAll('.panel-tab').forEach(tab => {
                if (tab.getAttribute('data-tab') === tabName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

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
                document.getElementById('confirmationContent').classList.add('hidden');
                document.getElementById('completedContent').classList.add('hidden');
                document.getElementById('scheduleCards').classList.remove('hidden');
                document.getElementById('availabilityCards').classList.add('hidden');
                document.getElementById('confirmationCards').classList.add('hidden');
                document.getElementById('failedCards').classList.add('hidden');
                document.getElementById('completedCards').classList.add('hidden');
            } else if (tabName === 'availability') {
                document.getElementById('schedulesContent').classList.add('hidden');
                document.getElementById('availabilityContent').classList.remove('hidden');
                document.getElementById('confirmationContent').classList.add('hidden');
                document.getElementById('completedContent').classList.add('hidden');
                document.getElementById('scheduleCards').classList.add('hidden');
                document.getElementById('availabilityCards').classList.remove('hidden');
                document.getElementById('confirmationCards').classList.add('hidden');
                document.getElementById('failedCards').classList.add('hidden');
                document.getElementById('completedCards').classList.add('hidden');
                // Load room availability immediately when tab is clicked
                checkRoomAvailability();
            } else if (tabName === 'confirmation') {
                document.getElementById('schedulesContent').classList.add('hidden');
                document.getElementById('availabilityContent').classList.add('hidden');
                document.getElementById('confirmationContent').classList.remove('hidden');
                document.getElementById('completedContent').classList.add('hidden');
                document.getElementById('scheduleCards').classList.add('hidden');
                document.getElementById('availabilityCards').classList.add('hidden');
                document.getElementById('confirmationCards').classList.remove('hidden');
                document.getElementById('failedCards').classList.remove('hidden');
                document.getElementById('completedCards').classList.add('hidden');
            } else if (tabName === 'completed') {
                document.getElementById('schedulesContent').classList.add('hidden');
                document.getElementById('availabilityContent').classList.add('hidden');
                document.getElementById('confirmationContent').classList.add('hidden');
                document.getElementById('completedContent').classList.remove('hidden');
                document.getElementById('scheduleCards').classList.add('hidden');
                document.getElementById('availabilityCards').classList.add('hidden');
                document.getElementById('confirmationCards').classList.add('hidden');
                document.getElementById('failedCards').classList.add('hidden');
                document.getElementById('completedCards').classList.remove('hidden');
            }
        }
        
        // Function to validate defense duration (minimum 30 minutes)
        function validateDefenseDuration() {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const groupId = document.getElementById('group_id').value;
            const program = groupsPrograms[groupId] || '';
            const minDuration = program.toLowerCase() === 'bsit' ? 60 : 40;
            
            if (startTime && endTime) {
                const start = new Date('1970-01-01T' + startTime);
                const end = new Date('1970-01-01T' + endTime);
                const duration = (end - start) / (1000 * 60); // minutes
                
                if (duration < minDuration) {
                    alert(`Defense duration must be at least ${minDuration} minutes for ${program.toUpperCase()} program.`);
                    return false;
                }
                
                // Suggest optimal end time if duration is not in proper increments
                if (duration % minDuration !== 0) {
                    const optimalDuration = Math.ceil(duration / minDuration) * minDuration;
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
            
            fetch('admin-pages/get_room_availability.php', {
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
            
            // Get selected group's program
            const groupId = document.getElementById('group_id').value;
            const program = groupsPrograms[groupId] || '';
            const slotDuration = program.toLowerCase() === 'bsit' ? 60 : 40;
            
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
                while (currentTime + slotDuration <= startMinutes) {
                    const slotEnd = Math.min(currentTime + slotDuration, startMinutes);
                    if (slotEnd - currentTime >= slotDuration) {
                        slots.push({
                            start: minutesToTime(currentTime),
                            end: minutesToTime(slotEnd),
                            duration: slotEnd - currentTime
                        });
                    }
                    currentTime += slotDuration;
                }
                currentTime = Math.max(currentTime, endMinutes);
            });
            
            // Add remaining slots after last schedule
            while (currentTime + slotDuration <= workEnd) {
                slots.push({
                    start: minutesToTime(currentTime),
                    end: minutesToTime(currentTime + slotDuration),
                    duration: slotDuration
                });
                currentTime += slotDuration;
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
            
            fetch('admin-pages/get_room_availability.php', {
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
                                        <p class="text-xs text-red-600 font-semibold mb-2">In Use:</p>
                                        ${room.schedules.map(schedule => `
                                            <div class="flex items-center text-xs mb-3">
                                                <div class="w-full bg-red-50 rounded-xl p-3 border border-red-200 shadow-sm hover:shadow-md transition">
                                                    <!-- Time -->
                                                    <div class="flex items-center mb-2">
                                                        <span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                                                        <span class="text-red-700 font-semibold text-sm">${schedule.start_time} - ${schedule.end_time}</span>
                                                    </div>

                                                    <!-- Details in one line -->
                                                    <div class="grid grid-cols-3 gap-4 text-gray-700 text-[13px]">
                                                        <div><span class="font-medium text-gray-900">Program:</span> ${schedule.program || 'Not specified'}</div>
                                                        <div><span class="font-medium text-gray-900">Cluster:</span> ${schedule.cluster || 'Not specified'}</div>
                                                        <div><span class="font-medium text-gray-900">Group:</span> ${schedule.group_name || 'Not specified'}</div>
                                                    </div>
                                                </div>
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
        // Reset form when opening for new defense (only if no group is already selected)
        if (!document.getElementById('group_id').value) {
            resetDefenseForm();
        }
        openModal('proposalModal');
    } else {
        closeModal('proposalModal');
    }
}

function resetDefenseForm() {
    document.getElementById('defense_type').value = 'pre_oral';
    document.getElementById('parent_defense_id').value = '';
    document.getElementById('group_id').value = '';
    document.getElementById('selected_group_display').textContent = 'No group selected';
    document.getElementById('redefense_reason_div').classList.add('hidden');
    document.getElementById('modal-title').innerHTML = '<div class="bg-white/20 p-2 rounded-lg mr-3"><i class="fas fa-calendar-plus text-white text-sm"></i></div>Schedule Defense';
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
        document.getElementById('edit_group_name').textContent =
            (schedule.proposal_title || 'No Title') + ' - ' + (schedule.group_name || 'No Group');

        document.getElementById('edit_defense_date').value = schedule.defense_date || '';
        document.getElementById('edit_room_id').value = schedule.room_id || '';
        document.getElementById('edit_start_time').value = schedule.start_time || '';
        document.getElementById('edit_end_time').value = schedule.end_time || '';
        
        // Filter panel members by group's program
        if (schedule.group_id) {
            filterEditPanelMembersByGroup(schedule.group_id);
        }
        
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
window.scheduleDefenseForGroup = scheduleDefenseForGroup;
window.scheduleFinalDefense = scheduleFinalDefense;
window.scheduleRedefense = scheduleRedefense;

/* ========= PROGRAM/CLUSTER TOGGLE FUNCTIONS ========= */
function toggleFailedProgram(program) {
    const content = document.getElementById('failed-content-' + program);
    const icon = document.getElementById('failed-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleFailedCluster(clusterKey) {
    const content = document.getElementById('failed-content-' + clusterKey);
    const icon = document.getElementById('failed-icon-' + clusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleProgram(program) {
    const content = document.getElementById('content-' + program);
    const icon = document.getElementById('icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleAdviser(programAdviserKey) {
    const content = document.getElementById('adviser-content-' + programAdviserKey);
    const icon = document.getElementById('adviser-icon-' + programAdviserKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleCluster(programAdviserClusterKey) {
    const content = document.getElementById('cluster-content-' + programAdviserClusterKey);
    const icon = document.getElementById('cluster-icon-' + programAdviserClusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}


function toggleUpcomingProgram(program) {
    const content = document.getElementById('upcoming-content-' + program);
    const icon = document.getElementById('upcoming-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleUpcomingCluster(clusterKey) {
    const content = document.getElementById('upcoming-content-' + clusterKey);
    const icon = document.getElementById('upcoming-icon-' + clusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function togglePendingProgram(program) {
    const content = document.getElementById('pending-content-' + program);
    const icon = document.getElementById('pending-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function togglePendingCluster(clusterKey) {
    const content = document.getElementById('pending-content-' + clusterKey);
    const icon = document.getElementById('pending-icon-' + clusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleCompletedProgram(program) {
    const content = document.getElementById('completed-content-' + program);
    const icon = document.getElementById('completed-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleCompletedCluster(clusterKey) {
    const content = document.getElementById('completed-content-' + clusterKey);
    const icon = document.getElementById('completed-icon-' + clusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function scheduleFinalDefense(groupId, parentDefenseId, groupName, proposalTitle) {
    document.getElementById('defense_type').value = 'final';
    document.getElementById('parent_defense_id').value = parentDefenseId;
    document.getElementById('group_id').value = groupId;
    document.getElementById('selected_group_display').textContent = groupName + ' - ' + proposalTitle;
    document.getElementById('redefense_reason_div').classList.add('hidden');
    document.getElementById('modal-title').innerHTML = '<div class="bg-white/20 p-2 rounded-lg mr-3"><i class="fas fa-graduation-cap text-white text-sm"></i></div>Schedule Final Defense';
    
    // Filter panel members by group's program
    filterPanelMembersByGroup(groupId);
    
    toggleModal();
}

function scheduleRedefense(groupId, parentDefenseId, groupName, proposalTitle) {
    document.getElementById('defense_type').value = 'redefense';
    document.getElementById('parent_defense_id').value = parentDefenseId;
    document.getElementById('group_id').value = groupId;
    document.getElementById('selected_group_display').textContent = groupName + ' - ' + proposalTitle;
    document.getElementById('redefense_reason_div').classList.remove('hidden');
    document.getElementById('modal-title').innerHTML = '<div class="bg-white/20 p-2 rounded-lg mr-3"><i class="fas fa-redo text-white text-sm"></i></div>Schedule Redefense';
    
    // Get existing panel members for the parent defense
    fetch('get_defense_panel.php?defense_id=' + parentDefenseId)
        .then(response => response.json())
        .then(data => {
            // Pre-select existing panel members
            data.forEach(member => {
                const checkbox = document.querySelector(`input[value="${member.faculty_id}|${member.role}"]`);
                if (checkbox) checkbox.checked = true;
            });
        })
        .catch(error => console.error('Error loading panel members:', error));
    
    // Filter panel members by group's program
    filterPanelMembersByGroup(groupId);
    
    toggleModal();
}

function scheduleDefenseForGroup(groupId, groupName, proposalTitle) {
    document.getElementById('defense_type').value = 'pre_oral';
    document.getElementById('parent_defense_id').value = '';
    document.getElementById('group_id').value = groupId;
    document.getElementById('selected_group_display').textContent = groupName + ' - ' + proposalTitle;
    document.getElementById('redefense_reason_div').classList.add('hidden');
    document.getElementById('modal-title').innerHTML = '<div class="bg-white/20 p-2 rounded-lg mr-3"><i class="fas fa-calendar-plus text-white text-sm"></i></div>Schedule Defense';
    
    // Filter panel members by group's program
    filterPanelMembersByGroup(groupId);
    
    toggleModal();
}

/* ========= PANEL MEMBER FILTERING ========= */
// Groups and their programs data from PHP
const groupsPrograms = <?php echo json_encode($groups_programs); ?>;

function filterPanelMembersByGroup(groupId) {
    const groupProgram = groupsPrograms[groupId];
    if (!groupProgram) return;
    
    // Filter chairpersons - only show those with matching program or general
    const chairpersonItems = document.querySelectorAll('#chairperson-list .panel-member-item');
    let visibleChairpersons = 0;
    
    chairpersonItems.forEach(item => {
        const itemProgram = item.getAttribute('data-program');
        if (itemProgram === groupProgram || itemProgram === 'general') {
            item.style.display = 'block';
            visibleChairpersons++;
        } else {
            item.style.display = 'none';
            // Uncheck hidden items
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        }
    });
    
    // Show/hide no chairpersons message
    const noChairpersonsMsg = document.getElementById('no-chairpersons-message');
    if (visibleChairpersons === 0) {
        noChairpersonsMsg.classList.remove('hidden');
    } else {
        noChairpersonsMsg.classList.add('hidden');
    }
    
    // Filter members - only show those with matching program or general
    const memberItems = document.querySelectorAll('#members-list .panel-member-item');
    let visibleMembers = 0;
    
    memberItems.forEach(item => {
        const itemProgram = item.getAttribute('data-program');
        if (itemProgram === groupProgram || itemProgram === 'general') {
            item.style.display = 'block';
            visibleMembers++;
        } else {
            item.style.display = 'none';
            // Uncheck hidden items
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        }
    });
    
    // Show/hide no members message
    const noMembersMsg = document.getElementById('no-members-message');
    if (visibleMembers === 0) {
        noMembersMsg.classList.remove('hidden');
    } else {
        noMembersMsg.classList.add('hidden');
    }
}

function filterEditPanelMembersByGroup(groupId) {
    const groupProgram = groupsPrograms[groupId];
    if (!groupProgram) return;
    
    // Filter edit chairpersons - only show those with matching program or general
    const editChairpersonItems = document.querySelectorAll('#edit-chairperson-list .edit-panel-member-item');
    let visibleEditChairpersons = 0;
    
    editChairpersonItems.forEach(item => {
        const itemProgram = item.getAttribute('data-program');
        if (itemProgram === groupProgram || itemProgram === 'general') {
            item.style.display = 'block';
            visibleEditChairpersons++;
        } else {
            item.style.display = 'none';
            // Uncheck hidden items
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        }
    });
    
    // Show/hide no chairpersons message
    const editNoChairpersonsMsg = document.getElementById('edit-no-chairpersons-message');
    if (visibleEditChairpersons === 0) {
        editNoChairpersonsMsg.classList.remove('hidden');
    } else {
        editNoChairpersonsMsg.classList.add('hidden');
    }
    
    // Filter edit members - only show those with matching program or general
    const editMemberItems = document.querySelectorAll('#edit-members-list .edit-panel-member-item');
    let visibleEditMembers = 0;
    
    editMemberItems.forEach(item => {
        const itemProgram = item.getAttribute('data-program');
        if (itemProgram === groupProgram || itemProgram === 'general') {
            item.style.display = 'block';
            visibleEditMembers++;
        } else {
            item.style.display = 'none';
            // Uncheck hidden items
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        }
    });
    
    // Show/hide no members message
    const editNoMembersMsg = document.getElementById('edit-no-members-message');
    if (visibleEditMembers === 0) {
        editNoMembersMsg.classList.remove('hidden');
    } else {
        editNoMembersMsg.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize groupsPrograms from PHP data
    window.groupsPrograms = <?php echo json_encode($groups_programs); ?>;
    
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

// ========= NEW DEFENSE CONFIRMATION FUNCTIONS =========
function markDefensePassed(defenseId) {
    if (confirm('Mark this defense as passed? This will move it to completed defenses.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="defense_id" value="${defenseId}"><input type="hidden" name="mark_passed" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function markDefenseCompleted(defenseId) {
    if (confirm('Mark this defense as completed? This will finalize the defense process.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="defense_id" value="${defenseId}"><input type="hidden" name="mark_completed" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function markDefenseFailed(defenseId) {
    if (confirm('Mark this defense as failed? This will allow scheduling a redefense.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="defense_id" value="${defenseId}"><input type="hidden" name="mark_failed" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmDefense(defenseId) {
    if (confirm('Mark this defense as passed? This will move it to the evaluation tab.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="defense_id" value="${defenseId}"><input type="hidden" name="confirm_defense" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleConfirmedProgram(program) {
    const content = document.getElementById('confirmed-content-' + program);
    const icon = document.getElementById('confirmed-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleConfirmedCluster(clusterKey) {
    const content = document.getElementById('confirmed-content-' + clusterKey);
    const icon = document.getElementById('confirmed-icon-' + clusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleAdviserDefenses(programAdviserKey) {
    const content = document.getElementById('adviser-content-' + programAdviserKey);
    const icon = document.getElementById('adviser-icon-' + programAdviserKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleProgramDefenses(program) {
    const content = document.getElementById('program-content-' + program);
    const icon = document.getElementById('program-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleClusterDefenses(programAdviserClusterKey) {
    const content = document.getElementById('cluster-content-' + programAdviserClusterKey);
    const icon = document.getElementById('cluster-icon-' + programAdviserClusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleFailedAdviserDefenses(programAdviserKey) {
    const content = document.getElementById('failed-adviser-content-' + programAdviserKey);
    const icon = document.getElementById('failed-adviser-icon-' + programAdviserKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleFailedProgramDefenses(program) {
    const content = document.getElementById('failed-program-content-' + program);
    const icon = document.getElementById('failed-program-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleFailedClusterDefenses(programAdviserClusterKey) {
    const content = document.getElementById('failed-cluster-content-' + programAdviserClusterKey);
    const icon = document.getElementById('failed-cluster-icon-' + programAdviserClusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function togglePendingAdviserDefenses(programAdviserKey) {
    const content = document.getElementById('pending-adviser-content-' + programAdviserKey);
    const icon = document.getElementById('pending-adviser-icon-' + programAdviserKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function togglePendingProgramDefenses(program) {
    const content = document.getElementById('pending-program-content-' + program);
    const icon = document.getElementById('pending-program-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function togglePendingClusterDefenses(programAdviserClusterKey) {
    const content = document.getElementById('pending-cluster-content-' + programAdviserClusterKey);
    const icon = document.getElementById('pending-cluster-icon-' + programAdviserClusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleCompletedProgram(program) {
    const content = document.getElementById('completed-content-' + program);
    const icon = document.getElementById('completed-icon-' + program);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleCompletedCluster(clusterKey) {
    const content = document.getElementById('completed-content-' + clusterKey);
    const icon = document.getElementById('completed-icon-' + clusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleCompletedAdviser(programAdviserKey) {
    const content = document.getElementById('completed-adviser-content-' + programAdviserKey);
    const icon = document.getElementById('completed-adviser-icon-' + programAdviserKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleCompletedCluster(programAdviserClusterKey) {
    const content = document.getElementById('completed-cluster-content-' + programAdviserClusterKey);
    const icon = document.getElementById('completed-cluster-icon-' + programAdviserClusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleUpcomingAdviser(programAdviserKey) {
    const content = document.getElementById('upcoming-adviser-content-' + programAdviserKey);
    const icon = document.getElementById('upcoming-adviser-icon-' + programAdviserKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleUpcomingCluster(programAdviserClusterKey) {
    const content = document.getElementById('upcoming-cluster-content-' + programAdviserClusterKey);
    const icon = document.getElementById('upcoming-cluster-icon-' + programAdviserClusterKey);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

// Make functions globally accessible
window.markDefensePassed = markDefensePassed;
window.markDefenseFailed = markDefenseFailed;
window.confirmDefense = confirmDefense;
window.toggleConfirmedProgram = toggleConfirmedProgram;
window.toggleConfirmedCluster = toggleConfirmedCluster;
window.toggleCompletedProgram = toggleCompletedProgram;
window.toggleCompletedCluster = toggleCompletedCluster;

// Auto-refresh mechanism to check for overdue defenses
let lastCheckTime = Date.now();
const CHECK_INTERVAL = 30000; // Check every 30 seconds

function checkForOverdueDefenses() {
    const now = Date.now();
    if (now - lastCheckTime >= CHECK_INTERVAL) {
        lastCheckTime = now;
        
        // Check if there are any scheduled defenses that should be overdue
        const scheduledDefenses = document.querySelectorAll('[data-status="scheduled"]');
        const currentTime = new Date();
        
        let overdueCount = 0;
        scheduledDefenses.forEach(defense => {
            const defenseDate = defense.getAttribute('data-defense-date');
            const endTime = defense.getAttribute('data-end-time');
            if (defenseDate && endTime) {
                const defenseEndTime = new Date(defenseDate + ' ' + endTime);
                if (defenseEndTime <= currentTime) {
                    overdueCount++;
                }
            }
        });
        
        // Update the button to show count
        const overdueCountElement = document.getElementById('overdueCount');
        const updateOverdueBtn = document.getElementById('updateOverdueBtn');
        
        if (overdueCount > 0) {
            overdueCountElement.textContent = overdueCount;
            overdueCountElement.classList.remove('hidden');
            updateOverdueBtn.classList.add('animate-pulse');
            
            // Show a subtle notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-orange-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            notification.innerHTML = `<i class="fas fa-clock mr-2"></i>${overdueCount} defense(s) are overdue. Click "Update Overdue" to move them to evaluation.`;
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        } else {
            overdueCountElement.classList.add('hidden');
            updateOverdueBtn.classList.remove('animate-pulse');
        }
    }
}

// Start the auto-check
setInterval(checkForOverdueDefenses, 10000); // Check every 10 seconds

// Run check immediately when page loads
document.addEventListener('DOMContentLoaded', function() {
    checkForOverdueDefenses();
});

// Also check when the page becomes visible (user switches back to tab)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        checkForOverdueDefenses();
    }
});
</script>

</body>
</html>
