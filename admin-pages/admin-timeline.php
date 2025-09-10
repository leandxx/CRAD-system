<?php
session_start();
date_default_timezone_set('Asia/Manila');
include("../includes/connection.php");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Global: Toggle Final Defense open/closed for ALL proposals
    if (isset($_POST['toggle_final_defense_global'])) {
        $open = (int)($_POST['final_defense_open_global'] ?? 0) === 1 ? 1 : 0;

        // Ensure proposals.final_defense_open exists
        $colCheck = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'proposals' AND COLUMN_NAME = 'final_defense_open'");
        if ($colCheck && ($colRow = $colCheck->fetch_assoc()) && (int)$colRow['cnt'] === 0) {
            @$conn->query("ALTER TABLE proposals ADD COLUMN final_defense_open TINYINT(1) NOT NULL DEFAULT 0 AFTER reviewed_at");
        }

        // Update all proposals
        $stmt = $conn->prepare("UPDATE proposals SET final_defense_open = ?");
        $stmt->bind_param("i", $open);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = $open ? 'Final Defense is now OPEN for ALL students.' : 'Final Defense is now CLOSED for ALL students.';
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
    // Create timeline
    if (isset($_POST['create_timeline'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        $stmt = $conn->prepare("INSERT INTO submission_timelines (title, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);
        $stmt->execute();
        $timeline_id = $stmt->insert_id;
        $stmt->close();

        if (!empty($_POST['milestone_title']) && is_array($_POST['milestone_title'])) {
            foreach ($_POST['milestone_title'] as $key => $msTitle) {
                $msTitle = $_POST['milestone_title'][$key] ?? '';
                $msDesc = $_POST['milestone_description'][$key] ?? '';
                $msDeadline = $_POST['milestone_deadline'][$key] ?? '';

                if ($msTitle && $msDeadline) {
                    $stmt = $conn->prepare("INSERT INTO timeline_milestones (timeline_id, title, description, deadline) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $timeline_id, $msTitle, $msDesc, $msDeadline);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $_SESSION['success_message'] = "Timeline created successfully!";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // AJAX: per-image review update
    if (isset($_POST['ajax_update_image_review'])) {
        header('Content-Type: application/json');
        $proposal_id = (int)($_POST['proposal_id'] ?? 0);
        $payment_type = $_POST['payment_type'] ?? '';
        $image_index = (int)($_POST['image_index'] ?? -1);
        $decision = $_POST['decision'] ?? '';
        $feedback = trim($_POST['feedback'] ?? '');

        if (!$proposal_id || $image_index < 0 || !in_array($payment_type, ['research_forum','pre_oral_defense','final_defense']) || !in_array($decision, ['approved','rejected'])) {
            echo json_encode(['ok' => false, 'error' => 'Invalid parameters']);
            exit();
        }

        // Ensure image_review column exists
        $conn->query("CREATE TABLE IF NOT EXISTS _tmp_check (id INT PRIMARY KEY) ENGINE=InnoDB"); // no-op to ensure permissions
        $colCheck = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'image_review'");
        if ($colCheck && ($colRow = $colCheck->fetch_assoc()) && (int)$colRow['cnt'] === 0) {
            @$conn->query("ALTER TABLE payments ADD COLUMN image_review TEXT NULL AFTER image_receipts");
        }

        // Get group_id -> student_id rows
        $stmt = $conn->prepare("SELECT group_id FROM proposals WHERE id = ?");
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!$row) { echo json_encode(['ok'=>false,'error'=>'Proposal not found']); exit(); }
        $group_id = (int)$row['group_id'];

        // Find the representative payment row (first member)
        $sql = "SELECT p.* FROM payments p JOIN group_members gm ON p.student_id = gm.student_id WHERE gm.group_id = ? AND p.payment_type = ? ORDER BY p.payment_date DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $group_id, $payment_type);
        $stmt->execute();
        $pRes = $stmt->get_result();
        $payment = $pRes->fetch_assoc();
        $stmt->close();
        if (!$payment) { echo json_encode(['ok'=>false,'error'=>'Payment not found']); exit(); }

        $images = [];
        if (!empty($payment['image_receipts'])) {
            $images = json_decode($payment['image_receipts'], true) ?: [];
        }
        if (!isset($images[$image_index])) { echo json_encode(['ok'=>false,'error'=>'Image index out of bounds']); exit(); }

        $review = [];
        if (!empty($payment['image_review'])) {
            $review = json_decode($payment['image_review'], true) ?: [];
        }
        $review[$image_index] = [ 'status' => $decision, 'feedback' => $feedback, 'updated_at' => date('c') ];

        // Compute overall status from per-image review
        $new_status = 'pending';
        if (!empty($images)) {
            $allApproved = true;
            $anyRejected = false;
            foreach ($images as $idx => $_) {
                if (!isset($review[$idx])) { $allApproved = false; }
                else if ($review[$idx]['status'] === 'rejected') { $anyRejected = true; $allApproved = false; }
                else if ($review[$idx]['status'] !== 'approved') { $allApproved = false; }
            }
            if ($anyRejected) $new_status = 'rejected';
            else if ($allApproved) $new_status = 'approved';
            else $new_status = 'pending';
        }

        // Persist review JSON and overall status to ALL group members for consistency
        $new_review_json = json_encode($review);
        $sql = "UPDATE payments p JOIN group_members gm ON p.student_id = gm.student_id SET p.image_review = ?, p.status = ?, p.admin_approved = IF(?='approved',1,0) WHERE gm.group_id = ? AND p.payment_type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $new_review_json, $new_status, $new_status, $group_id, $payment_type);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['ok'=>true,'review'=>$review,'status'=>$new_status]);
        exit();
    }

    // Approve payment receipt
    if (isset($_POST['approve_payment'])) {
        $proposal_id = (int)($_POST['proposal_id'] ?? 0);
        $payment_type = $_POST['payment_type'] ?? 'research_forum';

        // Ensure review_feedback column exists
        $colCheck = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'review_feedback'");
        if ($colCheck && ($colRow = $colCheck->fetch_assoc()) && (int)$colRow['cnt'] === 0) {
            @$conn->query("ALTER TABLE payments ADD COLUMN review_feedback TEXT NULL AFTER admin_approved");
        }

        // Get group_id from proposal
        $stmt = $conn->prepare("SELECT group_id FROM proposals WHERE id = ?");
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row) {
            $group_id = (int)$row['group_id'];
            // Approve all matching payments for the group
            $sql = "UPDATE payments p JOIN group_members gm ON p.student_id = gm.student_id SET p.status = 'approved', p.admin_approved = 1, p.review_feedback = NULL WHERE gm.group_id = ? AND p.payment_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $group_id, $payment_type);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = ucfirst(str_replace('_',' ', $payment_type)) . " receipt approved.";
        } else {
            $_SESSION['error_message'] = "Unable to approve: proposal not found.";
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Reject payment receipt with feedback
    if (isset($_POST['reject_payment'])) {
        $proposal_id = (int)($_POST['proposal_id'] ?? 0);
        $payment_type = $_POST['payment_type'] ?? 'research_forum';
        $feedback = trim($_POST['feedback'] ?? '');

        // Ensure review_feedback column exists
        $colCheck = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'review_feedback'");
        if ($colCheck && ($colRow = $colCheck->fetch_assoc()) && (int)$colRow['cnt'] === 0) {
            @$conn->query("ALTER TABLE payments ADD COLUMN review_feedback TEXT NULL AFTER admin_approved");
        }

        // Get group_id from proposal
        $stmt = $conn->prepare("SELECT group_id FROM proposals WHERE id = ?");
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row) {
            $group_id = (int)$row['group_id'];
            // Reject all matching payments for the group with feedback
            $sql = "UPDATE payments p JOIN group_members gm ON p.student_id = gm.student_id SET p.status = 'rejected', p.admin_approved = 0, p.review_feedback = ? WHERE gm.group_id = ? AND p.payment_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sis", $feedback, $group_id, $payment_type);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = ucfirst(str_replace('_',' ', $payment_type)) . " receipt rejected with feedback.";
        } else {
            $_SESSION['error_message'] = "Unable to reject: proposal not found.";
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Toggle Final Defense open/closed for this proposal's group
    // Removed per-group final defense toggle (global control is used)

    // Update timeline
    if (isset($_POST['update_timeline'])) {
        $timeline_id = (int)($_POST['timeline_id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        $stmt = $conn->prepare("UPDATE submission_timelines SET title = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $description, $timeline_id);
        $stmt->execute();
        $stmt->close();

        // Update/Insert milestones
        $ids   = $_POST['milestone_id'] ?? [];
        $titles = $_POST['milestone_title'] ?? [];
        $descs  = $_POST['milestone_description'] ?? [];
        $deadlines = $_POST['milestone_deadline'] ?? [];

        foreach ($titles as $idx => $msTitle) {
            $msId = $ids[$idx] ?? 'new';
            $msDesc = $descs[$idx] ?? '';
            $msDeadline = $deadlines[$idx] ?? '';

            if (!$msTitle || !$msDeadline) continue;

            if ($msId === 'new') {
                $stmt = $conn->prepare("INSERT INTO timeline_milestones (timeline_id, title, description, deadline) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $timeline_id, $msTitle, $msDesc, $msDeadline);
            } else {
                $msId = (int)$msId;
                $stmt = $conn->prepare("UPDATE timeline_milestones SET title = ?, description = ?, deadline = ? WHERE id = ?");
                $stmt->bind_param("sssi", $msTitle, $msDesc, $msDeadline, $msId);
            }
            $stmt->execute();
            $stmt->close();
        }

        // Handle deleted milestones
        if (!empty($_POST['deleted_milestones'])) {
            $deleted_milestones = json_decode($_POST['deleted_milestones'], true);
            if (is_array($deleted_milestones)) {
                foreach ($deleted_milestones as $mid) {
                    if ($mid !== 'new') {
                        $mid = (int)$mid;
                        $stmt = $conn->prepare("DELETE FROM timeline_milestones WHERE id = ?");
                        $stmt->bind_param("i", $mid);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }

        $_SESSION['success_message'] = "Timeline updated successfully!";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Toggle timeline active status
    if (isset($_POST['toggle_timeline'])) {
        $timeline_id = (int)($_POST['timeline_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE submission_timelines SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $timeline_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Timeline status updated!";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Check proposal requirements
    if (isset($_POST['check_requirements'])) {
        $proposal_id = (int)($_POST['proposal_id'] ?? 0);
        $feedback = $_POST['feedback'] ?? '';
        
        // Get proposal and group information
        $proposal_query = "SELECT p.*, g.id as group_id FROM proposals p JOIN groups g ON p.group_id = g.id WHERE p.id = ?"; 
        $stmt = $conn->prepare($proposal_query);
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $proposal = $result->fetch_assoc();
        $stmt->close();
        
        if ($proposal) {
            // Check if group has paid (research forum payment required for proposal completion)
            $payment_check = checkGroupPaymentStatus($conn, $proposal['group_id']);
            
            if (!$payment_check['has_research_forum_payment']) {
                $_SESSION['error_message'] = "Cannot mark as complete: Group has not submitted Research Forum payment receipt.";
            } else {
                // Mark as complete when admin clicks the button
                $stmt = $conn->prepare("UPDATE proposals SET status = 'Completed', reviewed_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $proposal_id);
                $stmt->execute();
                $stmt->close();
                
                $_SESSION['success_message'] = " Proposal approved successfully! The group can now proceed to the next phase.";
            }
        } else {
            $_SESSION['error_message'] = "Proposal not found.";
        }
        
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
    
    // Revert proposal approval
    if (isset($_POST['revert_approval'])) {
        $proposal_id = (int)($_POST['proposal_id'] ?? 0);
        
        // Get group_id from proposal
        $group_query = "SELECT group_id FROM proposals WHERE id = ?";
        $stmt = $conn->prepare($group_query);
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $proposal_data = $result->fetch_assoc();
        $stmt->close();
        
        if ($proposal_data) {
            // Delete defense schedule for this group
            $delete_panel_query = "DELETE FROM defense_panel WHERE defense_id IN (SELECT id FROM defense_schedules WHERE group_id = ?)";
            $stmt = $conn->prepare($delete_panel_query);
            $stmt->bind_param("i", $proposal_data['group_id']);
            $stmt->execute();
            $stmt->close();
            
            $delete_schedule_query = "DELETE FROM defense_schedules WHERE group_id = ?";
            $stmt = $conn->prepare($delete_schedule_query);
            $stmt->bind_param("i", $proposal_data['group_id']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Revert proposal status
        $stmt = $conn->prepare("UPDATE proposals SET status = 'Pending', reviewed_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success_message'] = "Proposal approval reverted successfully. Status changed back to pending.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Schedule defense
    if (isset($_POST['schedule_defense'])) {
        $proposal_id = (int)($_POST['proposal_id'] ?? 0);
        $defense_date = $_POST['defense_date'] ?? '';
        $defense_time = $_POST['defense_time'] ?? '';
        $defense_venue = $_POST['defense_venue'] ?? '';
        
        if ($proposal_id && $defense_date && $defense_time && $defense_venue) {
            $defense_datetime = $defense_date . ' ' . $defense_time;
            
            // Update proposal with defense details
            $stmt = $conn->prepare("UPDATE proposals SET defense_date = ?, defense_venue = ?, status = 'Scheduled' WHERE id = ?");
            $stmt->bind_param("ssi", $defense_datetime, $defense_venue, $proposal_id);
            $stmt->execute();
            $stmt->close();
            
            // Notify students about the scheduled defense
            include('../includes/notification-helper.php');
            
            $proposal_info_query = "SELECT p.title, g.name as group_name, gm.student_id 
                                   FROM proposals p 
                                   JOIN groups g ON p.group_id = g.id 
                                   JOIN group_members gm ON g.id = gm.group_id 
                                   WHERE p.id = '$proposal_id'";
            $proposal_info_result = mysqli_query($conn, $proposal_info_query);
            
            while ($student = mysqli_fetch_assoc($proposal_info_result)) {
                notifyUser($conn, $student['student_id'], 
                    "Defense Scheduled", 
                    "Your defense for '{$student['title']}' has been scheduled on " . date('F j, Y', strtotime($defense_date)) . " at " . date('g:i A', strtotime($defense_time)) . " in $defense_venue.", 
                    'info'
                );
            }
            
            $_SESSION['success_message'] = "Defense scheduled successfully!";
        } else {
            $_SESSION['error_message'] = "Please fill all defense scheduling details!";
        }
        
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Function to check if all proposal requirements are met
function checkProposalRequirements($conn, $proposal_id) {
    // Get proposal details
    $proposal_query = "SELECT p.*, g.id as group_id, g.name as group_name 
                      FROM proposals p 
                      JOIN groups g ON p.group_id = g.id 
                      WHERE p.id = $proposal_id";
    $proposal_result = mysqli_query($conn, $proposal_query);
    $proposal = mysqli_fetch_assoc($proposal_result);
    
    // Define basic requirements
    $requirements = [
        'has_title' => !empty($proposal['title']),
        'has_description' => !empty($proposal['description']),
        'has_file' => !empty($proposal['file_path']),
        'has_group_members' => hasGroupMembers($conn, $proposal['group_id']),
    ];
    
    // Check if all requirements are met
    $all_requirements_met = true;
    foreach ($requirements as $requirement => $met) {
        if (!$met) {
            $all_requirements_met = false;
            break;
        }
    }
    
    return $all_requirements_met;
}

// Helper function to check if group has members
function hasGroupMembers($conn, $group_id) {
    $query = "SELECT COUNT(*) as count FROM group_members WHERE group_id = $group_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

// Helper function to check if group has minimum required members
function hasMinimumGroupMembers($conn, $group_id, $min_count = 2) {
    $query = "SELECT COUNT(*) as count FROM group_members WHERE group_id = $group_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] >= $min_count;
}

// Function to check group payment status
function checkGroupPaymentStatus($conn, $group_id) {
    // Get all group members
    $members_query = "SELECT student_id FROM group_members WHERE group_id = ?";
    $stmt = $conn->prepare($members_query);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $members_result = $stmt->get_result();
    $stmt->close();
    
    $payment_status = [
        'has_research_forum_payment' => false,
        'has_pre_oral_payment' => false, 
        'has_final_defense_payment' => false,
        'payment_details' => [],
        'payment_images' => [],
        'payment_image_review' => []
    ];
    
    if ($members_result->num_rows > 0) {
        $member = $members_result->fetch_assoc();
        $student_id = $member['student_id'];
        
        // Check for research forum payment (required for proposal completion)
        $research_forum_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'research_forum' AND status = 'approved'";
        $stmt = $conn->prepare($research_forum_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $research_result = $stmt->get_result();
        $payment_status['has_research_forum_payment'] = $research_result->num_rows > 0;
        // Always show latest uploaded images (any status) for admin review
        $rf_latest_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'research_forum' ORDER BY payment_date DESC LIMIT 1";
        $stmt = $conn->prepare($rf_latest_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $rf_latest = $stmt->get_result();
        if ($rf_latest->num_rows > 0) {
            $payment_data = $rf_latest->fetch_assoc();
            if (!empty($payment_data['image_receipts'])) {
                $images = json_decode($payment_data['image_receipts'], true);
                if (is_array($images)) {
                    $web_paths = array_map(function($path) {
                        $filename = basename($path);
                        return '/CRAD-system/assets/uploads/receipts/' . $filename;
                    }, $images);
                    $payment_status['payment_images']['research_forum'] = $web_paths;
                }
            }
            // Include per-image review statuses if available
            if (!empty($payment_data['image_review'])) {
                $review = json_decode($payment_data['image_review'], true);
                if (is_array($review)) {
                    $payment_status['payment_image_review']['research_forum'] = $review;
                }
            }
        }
        $stmt->close();
        
        // Check for pre-oral defense payment
        $pre_oral_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'pre_oral_defense' AND status = 'approved'";
        $stmt = $conn->prepare($pre_oral_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $pre_oral_result = $stmt->get_result();
        $payment_status['has_pre_oral_payment'] = $pre_oral_result->num_rows > 0;
        // Always show latest uploaded images (any status)
        $pre_latest_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'pre_oral_defense' ORDER BY payment_date DESC LIMIT 1";
        $stmt = $conn->prepare($pre_latest_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $pre_latest = $stmt->get_result();
        if ($pre_latest->num_rows > 0) {
            $payment_data = $pre_latest->fetch_assoc();
            if (!empty($payment_data['image_receipts'])) {
                $images = json_decode($payment_data['image_receipts'], true);
                if (is_array($images)) {
                    $web_paths = array_map(function($path) {
                        $filename = basename($path);
                        return '/CRAD-system/assets/uploads/receipts/' . $filename;
                    }, $images);
                    $payment_status['payment_images']['pre_oral_defense'] = $web_paths;
                }
            }
            if (!empty($payment_data['image_review'])) {
                $review = json_decode($payment_data['image_review'], true);
                if (is_array($review)) {
                    $payment_status['payment_image_review']['pre_oral_defense'] = $review;
                }
            }
        }
        $stmt->close();
        
        // Check for final defense payment
        $final_defense_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'final_defense' AND status = 'approved'";
        $stmt = $conn->prepare($final_defense_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $final_result = $stmt->get_result();
        $payment_status['has_final_defense_payment'] = $final_result->num_rows > 0;
        // Always show latest uploaded images (any status)
        $final_latest_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'final_defense' ORDER BY payment_date DESC LIMIT 1";
        $stmt = $conn->prepare($final_latest_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $final_latest = $stmt->get_result();
        if ($final_latest->num_rows > 0) {
            $payment_data = $final_latest->fetch_assoc();
            if (!empty($payment_data['image_receipts'])) {
                $images = json_decode($payment_data['image_receipts'], true);
                if (is_array($images)) {
                    $web_paths = array_map(function($path) {
                        $filename = basename($path);
                        return '/CRAD-system/assets/uploads/receipts/' . $filename;
                    }, $images);
                    $payment_status['payment_images']['final_defense'] = $web_paths;
                }
            }
            if (!empty($payment_data['image_review'])) {
                $review = json_decode($payment_data['image_review'], true);
                if (is_array($review)) {
                    $payment_status['payment_image_review']['final_defense'] = $review;
                }
            }
        }
        $stmt->close();
    }
    
    return $payment_status;
}

// Get active timeline
$active_timeline = null;
$milestones = [];
$stmt = $conn->prepare("SELECT * FROM submission_timelines WHERE is_active = 1 LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $active_timeline = $result->fetch_assoc();

    $stmt = $conn->prepare("SELECT * FROM timeline_milestones WHERE timeline_id = ? ORDER BY deadline ASC");
    $stmt->bind_param("i", $active_timeline['id']);
    $stmt->execute();
    $milestones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

// Determine current milestone (server-side)
$now = new DateTime();
$current_milestone = null;
foreach ($milestones as $m) {
    $dl = new DateTime($m['deadline']);
    if ($dl > $now) {
        $current_milestone = $m;
        break;
    }
}

// Handle search and filter
$search_term = $_GET['search'] ?? '';
$program_filter = $_GET['program'] ?? '';

// Get all submitted proposals for review with program info, cluster info and payment status
$proposals_query = "SELECT 
    p.*, 
    g.name AS group_name, 
    g.id AS group_id, 
    CONCAT(sp.full_name) AS submitted_by,
    sp.program,
    c.cluster
FROM proposals p
JOIN groups g ON p.group_id = g.id
LEFT JOIN clusters c ON g.cluster_id = c.id
JOIN group_members gm ON g.id = gm.group_id
JOIN student_profiles sp ON gm.student_id = sp.user_id
WHERE gm.student_id = (
    SELECT student_id 
    FROM group_members 
    WHERE group_id = g.id 
    LIMIT 1
)";

// Add search conditions
if (!empty($search_term)) {
    $search_term = mysqli_real_escape_string($conn, $search_term);
    $proposals_query .= " AND (p.title LIKE '%$search_term%' OR g.name LIKE '%$search_term%' OR sp.full_name LIKE '%$search_term%')";
}

// Add program filter
if (!empty($program_filter)) {
    $program_filter = mysqli_real_escape_string($conn, $program_filter);
    $proposals_query .= " AND sp.program = '$program_filter'";
}

$proposals_query .= " ORDER BY sp.program ASC, p.submitted_at DESC";

$proposals_result = mysqli_query($conn, $proposals_query);
$proposals = [];
$programs = [];

while ($proposal = mysqli_fetch_assoc($proposals_result)) {
    // Add payment status to each proposal
    $proposal['payment_status'] = checkGroupPaymentStatus($conn, $proposal['group_id']);
    $proposals[] = $proposal;
    if (!in_array($proposal['program'], $programs)) {
        $programs[] = $proposal['program'];
    }
}

// Get all available programs for filter dropdown
$programs_query = "SELECT DISTINCT program FROM student_profiles ORDER BY program";
$programs_result = mysqli_query($conn, $programs_query);
$all_programs = [];
while ($row = mysqli_fetch_assoc($programs_result)) {
    $all_programs[] = $row['program'];
}

// Preformat ISO deadline for JS countdown to avoid timezone parse bugs
$isoDeadline = $current_milestone
    ? date('c', strtotime($current_milestone['deadline'])) // ISO 8601
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Timeline Submission</title>
  <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    .animate-slide-up { animation: slideInUp 0.6s ease-out; }
    .animate-fade-in { animation: fadeIn 0.8s ease-out; }
    .animate-scale-in { animation: scaleIn 0.5s ease-out; }
    .animate-pulse { animation: pulse 2s infinite; }
    
    .modal-container{
      display:flex;
      align-items:center;
      justify-content:center;
      position:fixed;
      inset:0;
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.6));
      backdrop-filter: blur(12px);
      z-index:50;
      transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
      opacity: 0;
      visibility: hidden;
    }
    .modal-container:not(.hidden) {
      opacity: 1;
      visibility: visible;
    }
    .modal-content{
      background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
      border: 2px solid rgba(59, 130, 246, 0.1);
      border-radius: 32px;
      box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.8),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
      width:100%;
      max-width:56rem;
      max-height:90vh;
      overflow:hidden;
      transform: translateY(40px) scale(0.9);
      transition: all 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
    }
    .modal-container:not(.hidden) .modal-content {
      transform: translateY(0) scale(1);
    }
    .modal-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
    }
    .modal-header {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(147, 197, 253, 0.05));
      border-bottom: 1px solid rgba(59, 130, 246, 0.1);
      padding: 2rem;
      border-radius: 32px 32px 0 0;
    }
    .modal-body {
      padding: 2rem;
      max-height: calc(90vh - 200px);
      overflow-y: auto;
    }
    .modal-footer {
      background: linear-gradient(135deg, rgba(248, 250, 252, 0.8), rgba(241, 245, 249, 0.8));
      border-top: 1px solid rgba(59, 130, 246, 0.1);
      padding: 2rem;
      border-radius: 0 0 32px 32px;
    }
    .milestone-dot{
      width:48px;
      height:48px;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.3);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }
    .milestone-dot:hover {
      transform: scale(1.15) translateY(-2px);
      box-shadow: 0 12px 30px -8px rgba(0, 0, 0, 0.4);
    }
    .milestone-dot.current {
      animation: pulse 2s infinite;
    }
    .timeline-connector {
      position: absolute;
      top: 50%;
      left: 100%;
      width: calc(100% - 48px);
      height: 4px;
      background: linear-gradient(90deg, #e5e7eb, #d1d5db);
      border-radius: 2px;
      z-index: -1;
    }
    .timeline-connector.completed {
      background: linear-gradient(90deg, #10b981, #059669);
    }
    .stats-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 24px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 8px 32px -8px rgba(0, 0, 0, 0.1);
    }
    .stats-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.15);
      border-color: rgba(59, 130, 246, 0.3);
    }
    .gradient-blue {
      background: linear-gradient(135deg, #3b82f6, #1d4ed8, #1e40af);
    }
    .gradient-green {
      background: linear-gradient(135deg, #10b981, #059669, #047857);
    }
    .gradient-purple {
      background: linear-gradient(135deg, #8b5cf6, #7c3aed, #6d28d9);
    }
    .gradient-orange {
      background: linear-gradient(135deg, #f59e0b, #d97706, #b45309);
    }
    .proposal-card {
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 20px;
      box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.1);
    }
    .proposal-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
      transition: left 0.6s ease;
    }
    .proposal-card:hover::before {
      left: 100%;
    }
    .proposal-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.2);
      border-color: rgba(59, 130, 246, 0.3);
    }
    .cluster-header {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 197, 253, 0.1));
      border: 2px solid rgba(59, 130, 246, 0.2);
      transition: all 0.3s ease;
    }
    .cluster-header:hover {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(147, 197, 253, 0.15));
      border-color: rgba(59, 130, 246, 0.3);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.2);
    }
    .countdown-digit {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      padding: 0.75rem;
      min-width: 4rem;
      text-align: center;
    }
    .btn-primary {
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      border: none;
      border-radius: 12px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px -4px rgba(59, 130, 246, 0.4);
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.5);
    }
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
    }
    
    .countdown-banner {
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.95) 0%, rgba(124, 58, 237, 0.95) 50%, rgba(79, 70, 229, 0.95) 100%);
      backdrop-filter: blur(25px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
      animation: slideInDown 1s ease-out;
      position: relative;
      overflow: hidden;
    }
    
    .countdown-banner::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
      animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .modal-content::-webkit-scrollbar {
      width: 6px;
    }
    
    .modal-content::-webkit-scrollbar-track {
      background: transparent;
    }
    
    .modal-content::-webkit-scrollbar-thumb {
      background: rgba(59, 130, 246, 0.3);
      border-radius: 3px;
    }
    
    .modal-content::-webkit-scrollbar-thumb:hover {
      background: rgba(59, 130, 246, 0.5);
    }
  </style>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary:'#2563eb', secondary:'#7c3aed',
            success:'#10b981', warning:'#f59e0b', danger:'#ef4444'
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 h-screen overflow-hidden">
  <div class="min-h-screen flex">
    <!-- Sidebar/header -->
    <?php include('../includes/admin-sidebar.php'); ?>
    
    <div class="flex-1 overflow-y-auto p-6">
      <!-- Success message -->
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4 mb-6 shadow-sm animate-slide-up" role="alert">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="bg-green-100 rounded-full p-2">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
              </div>
            </div>
            <div class="ml-3 flex-1">
              <h3 class="text-sm font-semibold text-green-800">Success!</h3>
              <p class="text-sm text-green-700 mt-1"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
            </div>
            <button type="button" class="ml-4 text-green-400 hover:text-green-600 transition-colors" onclick="this.parentElement.parentElement.remove()">
              <i class="fas fa-times text-sm"></i>
            </button>
          </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <!-- Error message -->
      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="crad-alert crad-alert-danger crad-fade-in" role="alert">
          <i class="fas fa-exclamation-circle mr-2"></i>
          <span><?= htmlspecialchars($_SESSION['error_message']) ?></span>
          <button type="button" class="ml-auto text-danger-600 hover:text-danger-800" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
      <?php endif; ?>

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
                <?php if (!empty($milestones)): ?>
                  <?php
                    $now = new DateTime();
                    $current_milestone = null;
                    foreach ($milestones as $milestone) {
                        $deadline = new DateTime($milestone['deadline']);
                        if ($deadline > $now) { $current_milestone = $milestone; break; }
                    }
                  ?>
                  <?php if ($current_milestone): ?>
                    <span class="font-semibold"><?= htmlspecialchars($current_milestone['title']) ?></span>
                    <br><span class="text-sm">Deadline: <?= date('F j, Y \a\t g:i A', strtotime($current_milestone['deadline'])) ?></span>
                  <?php else: ?>
                    All milestones completed
                  <?php endif; ?>
                <?php else: ?>
                  No active milestones
                <?php endif; ?>
              </p>
            </div>
          </div>
          <div class="grid grid-cols-4 gap-4">
            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
              <div id="admin-countdown-days" class="text-4xl font-bold mb-1">00</div>
              <div class="text-sm opacity-90 font-medium">Days</div>
            </div>
            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
              <div id="admin-countdown-hours" class="text-4xl font-bold mb-1">00</div>
              <div class="text-sm opacity-90 font-medium">Hours</div>
            </div>
            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
              <div id="admin-countdown-minutes" class="text-4xl font-bold mb-1">00</div>
              <div class="text-sm opacity-90 font-medium">Minutes</div>
            </div>
            <div class="text-center bg-white/20 backdrop-blur-sm rounded-xl p-4">
              <div id="admin-countdown-seconds" class="text-4xl font-bold mb-1">00</div>
              <div class="text-sm opacity-90 font-medium">Seconds</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Timeline Management -->
      <div class="bg-gradient-to-br from-white via-blue-50 to-indigo-100 border border-blue-200 rounded-2xl p-6 mb-8 shadow-lg">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 rounded-xl mb-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="bg-white/20 p-2 rounded-lg mr-3">
                <i class="fas fa-calendar-alt text-white text-lg"></i>
              </div>
              <div>
                <h2 class="text-xl font-bold">Timeline Management</h2>
                <p class="text-blue-100 text-sm mt-1">Manage submission phases and milestones</p>
              </div>
            </div>
            <button type="button"
              onclick="toggleModal('createTimelineModal')"
              class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300">
              <i class="fas fa-plus mr-2"></i>Create Timeline
            </button>
          </div>
        </div>

        <?php if ($active_timeline): ?>
          <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
              <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($active_timeline['title']) ?></h3>
              <div class="flex gap-2">
                <button type="button"
                  onclick='openEditModal(<?= htmlspecialchars(json_encode($active_timeline), ENT_QUOTES, "UTF-8") ?>, <?= htmlspecialchars(json_encode($milestones), ENT_QUOTES, "UTF-8") ?>)'
                  class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-all">
                  <i class="fas fa-edit mr-1"></i>Edit
                </button>
                <form method="POST" class="inline">
                  <input type="hidden" name="timeline_id" value="<?= (int)$active_timeline['id'] ?>">
                  <button type="submit" name="toggle_timeline" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-all">
                    <i class="fas fa-toggle-off mr-1"></i>Disable
                  </button>
                </form>
              </div>
            </div>
            <p class="text-gray-600 mb-4 text-sm"><?= htmlspecialchars($active_timeline['description']) ?></p>

            <!-- Progress Timeline -->
            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40">
              <?php
                $total_milestones = count($milestones);
                $completed_milestones = 0;
                $now = new DateTime();
                foreach ($milestones as $milestone) {
                    $deadline = new DateTime($milestone['deadline']);
                    if ($deadline < $now) $completed_milestones++;
                }
                $progress = $total_milestones > 0 ? ($completed_milestones / $total_milestones) * 100 : 0;
              ?>
              <div class="h-1.5 bg-gray-200 rounded-full mb-4">
                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full" style="width: <?= $progress ?>%"></div>
              </div>

              <div class="grid grid-cols-<?= count($milestones) ?> gap-2">
                <?php foreach ($milestones as $index => $milestone):
                    $deadline = new DateTime($milestone['deadline']);
                    $is_past = $deadline < $now;
                    $prevDeadline = $index > 0 ? new DateTime($milestones[$index-1]['deadline']) : null;
                    $is_current = !$is_past && (!$prevDeadline || $prevDeadline < $now);
                ?>
                  <div class="text-center">
                    <div class="w-8 h-8 <?= $is_past ? 'bg-green-500' : ($is_current ? 'bg-yellow-500' : 'bg-gray-300') ?> text-white mx-auto mb-2 rounded-full flex items-center justify-center">
                      <i class="fas <?= $is_past ? 'fa-check' : ($is_current ? 'fa-exclamation' : 'fa-flag') ?> text-xs"></i>
                    </div>
                    <div class="text-xs font-medium text-gray-800"><?= htmlspecialchars($milestone['title']) ?></div>
                    <div class="text-xs text-gray-500"><?= date('M j', strtotime($milestone['deadline'])) ?></div>
                    <?php if ($is_current):
                        $diff = $now->diff($deadline);
                        $days_left = $diff->days;
                    ?>
                      <div class="text-xs mt-1 font-medium text-yellow-600"><?= (int)$days_left ?>d left</div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-6 border border-white/40">
              <i class="fas fa-calendar-times text-3xl text-blue-400 mb-3"></i>
              <h3 class="text-base font-semibold text-gray-700 mb-2">No Active Timeline</h3>
              <p class="text-gray-500 text-sm mb-4">Create a new timeline to get started with milestone management.</p>
              <button onclick="toggleModal('createTimelineModal')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all">
                <i class="fas fa-plus mr-2"></i>Create Timeline
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>

    <!-- Proposal Review Section -->
<div class="stats-card rounded-2xl p-8 mb-8 animate-scale-in">
  <div class="flex items-center justify-between mb-8">
    <div class="flex items-center">
      <div class="gradient-purple p-3 rounded-xl mr-4">
        <i class="fas fa-file-alt text-white text-xl"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Proposal Review</h2>
    </div>
    <div class="flex items-center gap-3">
      <form method="POST" class="bg-white/60 backdrop-blur-sm rounded-xl p-2 border border-white/40 flex items-center gap-2">
        <?php
          // Determine global state: if any proposal is closed, show Open All; if all open (>=1), show Close All
          $global_open = 0;
          $has_final_defense_col = 0;
          $colCheck = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'proposals' AND COLUMN_NAME = 'final_defense_open'");
          if ($colCheck && ($colRow = $colCheck->fetch_assoc())) {
            $has_final_defense_col = (int)$colRow['cnt'];
          }
          if ($has_final_defense_col) {
            $res = $conn->query("SELECT COUNT(*) AS total, SUM(final_defense_open = 1) AS open_count FROM proposals");
            if ($res && ($row = $res->fetch_assoc())) {
              $total = (int)$row['total'];
              $open_count = (int)$row['open_count'];
              if ($total > 0 && $open_count === $total) { $global_open = 1; }
            }
          }
        ?>
        <input type="hidden" name="final_defense_open_global" value="<?php echo $global_open ? '0' : '1'; ?>">
        <button type="submit" name="toggle_final_defense_global" id="finalDefenseToggleBtnGlobal" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all <?php echo $global_open ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white'; ?>">
          <?php echo $global_open ? 'Close Final Defense (All Students)' : 'Open Final Defense (All Students)'; ?>
        </button>
        <span class="text-sm <?php echo $global_open ? 'text-green-700 font-medium' : 'text-gray-600'; ?>">
          Current: <?php echo $global_open ? 'OPEN for all' : 'CLOSED (or mixed)'; ?>
        </span>
      </form>
    <span class="gradient-green text-white text-sm font-bold px-4 py-2 rounded-xl shadow-lg">
      <?php echo count($proposals); ?> Submitted
    </span>
    </div>
  </div>

  <!-- Search and Filter Bar -->
  <div class="mb-8 flex flex-col md:flex-row gap-4">
    <div class="flex-1">
      <div class="relative">
        <input type="text" id="searchInput" placeholder="Search by cluster, program, or group name..." 
               value="<?php echo htmlspecialchars($search_term); ?>"
               class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all"
               oninput="searchProposals()">
        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
      </div>
    </div>
    <div class="md:w-48">
      <select id="programFilter" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
        <option value="">All Programs</option>
        <?php foreach ($all_programs as $program): ?>
          <option value="<?php echo htmlspecialchars($program); ?>" <?php echo $program_filter === $program ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($program); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button onclick="applyFilters()" class="px-6 py-3 gradient-blue text-white rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold">
      <i class="fas fa-filter mr-2"></i>Filter
    </button>
    <button onclick="clearFilters()" class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-300 hover:scale-105 font-semibold">
      <i class="fas fa-times mr-2"></i>Clear
    </button>
  </div>

  <?php if (!empty($proposals)): ?>
    <?php 
    // Group by Program → Cluster → Proposals
    $program_groups = [];
    foreach ($proposals as $proposal) {
        $program_name = $proposal['program'] ?: 'Unassigned Program';
        $cluster_name = 'Cluster ' . ($proposal['cluster'] ?? 'Unassigned');
        if (!isset($program_groups[$program_name])) { $program_groups[$program_name] = []; }
        if (!isset($program_groups[$program_name][$cluster_name])) { $program_groups[$program_name][$cluster_name] = []; }
        $program_groups[$program_name][$cluster_name][] = $proposal;
    }
    ?>
    
    <?php foreach ($program_groups as $program_name => $clusters): ?>
      <?php 
        $program_id = md5($program_name);
        $program_total = 0; foreach ($clusters as $cList) { $program_total += count($cList); }
      ?>
      <div class="program-block mb-10">
        <div class="program-header cursor-pointer rounded-2xl p-6 mb-4 transition-all bg-white/70 border border-white/40" onclick="toggleProgram('<?php echo $program_id; ?>')">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="bg-purple-500 p-3 rounded-xl mr-4">
                <i class="fas fa-graduation-cap text-white text-xl"></i>
              </div>
              <div>
                <h3 class="text-2xl font-bold text-gray-800 program-title"><?php echo htmlspecialchars($program_name); ?></h3>
                <p class="text-gray-600">Click to show clusters</p>
              </div>
              <div class="ml-6 bg-purple-500 text-white text-lg font-bold px-4 py-2 rounded-xl shadow-lg">
                <?php echo (int)$program_total; ?>
              </div>
            </div>
            <div class="bg-white/50 p-3 rounded-xl">
              <i class="fas fa-chevron-down text-purple-600 transition-transform text-xl" id="prog-chevron-<?php echo $program_id; ?>"></i>
            </div>
          </div>
        </div>

        <div class="program-content hidden" id="program-<?php echo $program_id; ?>">
          <?php foreach ($clusters as $cluster_name => $cluster_proposals): ?>
          <?php $cluster_id = md5($program_name . '|' . $cluster_name); ?>
          <div class="mb-8 ml-4">
            <div class="cluster-header cursor-pointer rounded-2xl p-6 mb-4 transition-all bg-white/60 border border-white/40" onclick="toggleCluster('<?php echo $cluster_id; ?>')">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="bg-blue-500 p-3 rounded-xl mr-4">
                <i class="fas fa-layer-group text-white text-xl"></i>
              </div>
              <div>
                    <h4 class="text-xl font-bold text-gray-800 cluster-title"><?php echo htmlspecialchars($cluster_name); ?></h4>
                    <p class="text-gray-600">Click to show groups</p>
              </div>
              <div class="ml-6 bg-blue-500 text-white text-lg font-bold px-4 py-2 rounded-xl shadow-lg">
                <?php echo count($cluster_proposals); ?>
              </div>
            </div>
            <div class="bg-white/50 p-3 rounded-xl">
                  <i class="fas fa-chevron-down text-blue-600 transition-transform text-xl" id="chevron-<?php echo $cluster_id; ?>"></i>
            </div>
          </div>
        </div>
        
            <div class="cluster-content hidden" id="cluster-<?php echo $cluster_id; ?>">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 ml-8">
          <?php foreach ($cluster_proposals as $proposal): 
        // Simplified status system
        // Default to pending
        $status_class = 'bg-yellow-100 text-yellow-800';
        $status_text = 'Pending';
        
        // Check status and update accordingly
        if (isset($proposal['status']) && $proposal['status'] !== null && $proposal['status'] !== '') {
          switch ($proposal['status']) {
            case 'Completed':
              $status_class = 'bg-green-100 text-green-800';
              $status_text = 'Completed';
              break;
            case 'Scheduled':
              $status_class = 'bg-purple-100 text-purple-800';
              $status_text = 'Scheduled';
              break;
            default:
              $status_class = 'bg-yellow-100 text-yellow-800';
              $status_text = 'Pending';
          }
        }
        
        // Check payment status
        $payment_status = $proposal['payment_status'];
        $has_paid = $payment_status['has_research_forum_payment'];
      ?>
      
      <div class="proposal-card bg-gradient-to-br from-white via-blue-50 to-indigo-100 border border-blue-200 rounded-xl shadow-md hover:shadow-lg p-4 flex flex-col justify-between relative overflow-hidden min-h-[300px] transition-all duration-300">
        
        <div class="absolute top-3 right-3">
          <?php if ($proposal['status'] === 'Completed'): ?>
            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
          <?php else: ?>
            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
          <?php endif; ?>
        </div>
      
        <div class="relative z-10">
          <!-- Header -->
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-2">
            <div class="flex items-center flex-1 min-w-0">
              <div class="gradient-blue p-2 sm:p-3 rounded-xl mr-2 sm:mr-3 shadow-lg flex-shrink-0">
                <i class="fas fa-file-alt text-white text-sm sm:text-lg"></i>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="text-sm sm:text-lg font-bold text-gray-900 leading-tight truncate"><?php echo htmlspecialchars($proposal['title']); ?></h3>
                <p class="text-xs text-blue-600 font-medium truncate"><?php echo htmlspecialchars($proposal['group_name']); ?></p>
              </div>
            </div>
            <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $status_class; ?>">
              <?php echo $status_text; ?>
            </span>
          </div>

          <!-- Details Section -->
          <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
            <div class="grid grid-cols-1 gap-3">
              <div class="flex items-center text-sm">
                <i class="fas fa-graduation-cap text-blue-500 mr-3 w-4"></i>
                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($proposal['program']); ?></span>
              </div>
              <div class="flex items-center text-sm">
                <i class="fas fa-user text-blue-500 mr-3 w-4"></i>
                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($proposal['submitted_by']); ?></span>
              </div>
              <div class="flex items-center text-sm">
                <i class="fas fa-calendar text-blue-500 mr-3 w-4"></i>
                <span class="text-gray-700 font-medium"><?php echo date('M j, Y', strtotime($proposal['submitted_at'])); ?></span>
              </div>
            </div>
          </div>

          <!-- Payment Section -->
          <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
            <div class="flex items-start">
              <i class="fas fa-credit-card text-purple-500 mr-3 mt-1 w-4"></i>
              <div class="flex-1">
                <p class="text-xs text-gray-600 font-medium uppercase tracking-wide mb-2">Research Forum Payment</p>
                <?php
                  $payment_status = $proposal['payment_status'];
                  $rfPaid = $payment_status['has_research_forum_payment'];
                ?>
                <?php if ($rfPaid): ?>
                  <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                      <i class="fas fa-check-circle mr-1"></i>Paid & Verified
                    </span>
                    <span class="text-xs text-green-600 font-medium">Receipt uploaded</span>
                  </div>
                <?php else: ?>
                  <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                      <i class="fas fa-hourglass-half mr-1"></i>Pending / No Attachment
                    </span>
                    <span class="text-xs text-yellow-700 font-medium">Awaiting approval</span>
                  </div>
                <?php endif; ?>
                
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-2">
            <?php if ($proposal['status'] === 'Completed'): ?>
              <button type="button" onclick='openProposalReviewModal(<?php echo htmlspecialchars(json_encode($proposal), ENT_QUOTES, "UTF-8"); ?>)' class="flex-1 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="View Proposal">
                <i class="fas fa-eye mr-1"></i>View
              </button>
              <button type="button" onclick='openRevertModal(<?php echo htmlspecialchars(json_encode($proposal), ENT_QUOTES, "UTF-8"); ?>)' class="flex-1 bg-gradient-to-r from-orange-400 to-orange-600 hover:from-orange-500 hover:to-orange-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Revert Approval">
                <i class="fas fa-undo mr-1"></i>Revert
              </button>
            <?php else: ?>
              <button type="button" onclick='openProposalReviewModal(<?php echo htmlspecialchars(json_encode($proposal), ENT_QUOTES, "UTF-8"); ?>)' class="flex-1 bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" title="Review Proposal">
                <i class="fas fa-check mr-1"></i>Review
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
          <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="text-center py-16">
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-3xl p-12 max-w-md mx-auto">
        <div class="bg-gray-200 rounded-full p-6 w-24 h-24 mx-auto mb-6 flex items-center justify-center">
          <i class="fas fa-file-alt text-4xl text-gray-400"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-700 mb-4">No Proposals Yet</h3>
        <p class="text-gray-500">Proposals will appear here once students submit them through the system.</p>
      </div>
    </div>
  <?php endif; ?>
</div>

    <!-- Create Timeline Modal -->
    <div id="createTimelineModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: rgba(59, 130, 246, 0.3) transparent;">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 border-0">
                    <h3 class="text-lg font-bold flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-calendar-plus text-white text-sm"></i>
                        </div>
                        Create Timeline
                    </h3>
                    <p class="text-blue-100 mt-1 text-sm">Set up submission phases and milestones</p>
                </div>
                <form method="POST" id="createTimelineForm" class="p-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Timeline Title</label>
                        <input type="text" name="title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Milestones</label>
                        <div id="milestoneContainer">
                            <div class="milestone-item mb-4 p-4 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <h5 class="font-medium">Milestone #1</h5>
                                    <button type="button" class="text-red-500 hover:text-red-700 remove-milestone">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" name="milestone_title[]" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                                        <input type="datetime-local" name="milestone_deadline[]" class="w-full px-3 py-2 border border-gray-300 rounded-md flatpickr" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="milestone_description[]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addMilestone" class="bg-green-100 hover:bg-green-200 text-green-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Milestone
                        </button>
                    </div>
                </form>
                <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                    <button type="button" onclick="toggleModal('createTimelineModal')" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" form="createTimelineForm" name="create_timeline" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                        <i class="fas fa-save mr-2"></i>Create Timeline
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Timeline Modal -->
    <div id="editTimelineModal" class="modal-container hidden">
      <div class="modal-content">
        <div class="modal-header">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="bg-orange-500 p-3 rounded-2xl mr-4">
                <i class="fas fa-edit text-white text-xl"></i>
              </div>
              <div>
                <h3 class="text-3xl font-bold text-gray-800">Edit Timeline</h3>
                <p class="text-gray-600 mt-1">Modify timeline settings and milestones</p>
              </div>
            </div>
            <button type="button" onclick="toggleModal('editTimelineModal')" class="bg-gray-100 hover:bg-gray-200 p-3 rounded-2xl transition-all duration-300 hover:scale-110">
              <i class="fas fa-times text-gray-600 text-lg"></i>
            </button>
          </div>
        </div>
        <div class="modal-body">
          <form method="POST" id="editTimelineForm">
            <input type="hidden" id="edit_timeline_id" name="timeline_id">
            <input type="hidden" id="deleted_milestones" name="deleted_milestones" value="">
            <div class="mb-6">
              <label class="block text-sm font-bold text-gray-700 mb-3">Timeline Title</label>
              <input type="text" id="edit_title" name="title" class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-lg" required>
            </div>
            <div class="mb-8">
              <label class="block text-sm font-bold text-gray-700 mb-3">Description</label>
              <textarea id="edit_description" name="description" rows="4" class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"></textarea>
            </div>

            <div class="flex items-center mb-6">
              <div class="bg-green-500 p-2 rounded-xl mr-3">
                <i class="fas fa-flag text-white"></i>
              </div>
              <h4 class="text-2xl font-bold text-gray-800">Milestones</h4>
            </div>
            <div id="editMilestoneContainer"><!-- populated by JS --></div>

            <div class="flex justify-between mt-8">
              <button type="button" id="addEditMilestone" class="bg-green-100 hover:bg-green-200 text-green-700 px-6 py-3 rounded-2xl font-semibold transition-all duration-300 hover:scale-105">
                <i class="fas fa-plus mr-2"></i> Add Milestone
              </button>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <div class="flex justify-end space-x-4">
            <button type="button" onclick="toggleModal('editTimelineModal')" class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-semibold transition-all duration-300 hover:scale-105">
              Cancel
            </button>
            <button type="submit" form="editTimelineForm" name="update_timeline" class="px-8 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-semibold transition-all duration-300 hover:scale-105 shadow-lg">
              <i class="fas fa-save mr-2"></i>Update Timeline
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Proposal Review Modal -->
    <div id="proposalReviewModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-4xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: rgba(59, 130, 246, 0.3) transparent;">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white p-4 border-0">
                    <h3 class="text-lg font-bold flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-file-search text-white text-sm"></i>
                        </div>
                        Proposal Review
                    </h3>
                    <p class="text-purple-100 mt-1 text-sm">Evaluate and approve student proposal</p>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" id="review_proposal_id" name="proposal_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Group Name</label>
                            <input type="text" id="review_group_name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Submitted By</label>
                            <input type="text" id="review_submitted_by" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Submission Date</label>
                            <input type="text" id="review_submission_date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Current Status</label>
                            <input type="text" id="review_current_status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Proposal Title</label>
                        <input type="text" id="review_proposal_title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Proposal Description</label>
                        <textarea id="review_proposal_description" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none" readonly></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Download Proposal</label>
                        <a id="review_proposal_download" href="#" target="_blank" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-download mr-2"></i>Download PDF File
                        </a>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-credit-card text-blue-600 text-xl mr-3"></i>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800">Payment Status Overview</h4>
                                <p class="text-sm text-gray-600">Review all payment requirements and receipts</p>
                            </div>
                        </div>
                        <div id="paymentStatusSummary" class="space-y-3">
                            <!-- Payment status will be populated by JavaScript -->
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-images text-purple-600 text-xl mr-3"></i>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800">Payment Receipt Images</h4>
                                <p class="text-sm text-gray-600">Click on any image to view in full size</p>
                            </div>
                        </div>
                        <div id="paymentImagesContainer" class="space-y-4">
                            <!-- Payment images will be populated by JavaScript -->
                        </div>
                    </div>

                    

                    <div class="flex justify-center mt-6">
                        <button type="button" onclick="openApprovalModal()" id="approvalButton" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2 mx-auto">
                            <i class="fas fa-check"></i>
                            <span>Approve Proposal</span>
                        </button>
                    </div>
                </form>
                <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                    <button type="button" onclick="toggleModal('proposalReviewModal')" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Confirmation Modal -->
    <div id="approvalModal" class="modal-container hidden">
      <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="bg-green-500 p-3 rounded-2xl mr-4">
                <i class="fas fa-check-circle text-white text-xl"></i>
              </div>
              <div>
                <h3 class="text-3xl font-bold text-green-800">Approve Proposal</h3>
                <p class="text-green-600 mt-1">Confirm proposal approval</p>
              </div>
            </div>
            <button type="button" onclick="toggleModal('approvalModal')" class="bg-gray-100 hover:bg-gray-200 p-3 rounded-2xl transition-all duration-300 hover:scale-110">
              <i class="fas fa-times text-gray-600 text-lg"></i>
            </button>
          </div>
        </div>
        <div class="modal-body">
          <div class="mb-8">
            <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-6 mb-6">
              <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 text-3xl mr-4"></i>
                <div>
                  <h4 class="font-bold text-xl text-green-800 mb-2">Ready for Approval</h4>
                  <p class="text-green-700">All requirements have been verified and the group has completed payment.</p>
                </div>
              </div>
            </div>
            
            <p class="text-gray-700 mb-6 text-lg">Are you sure you want to approve this proposal? This action will:</p>
            <ul class="list-none space-y-3 mb-6">
              <li class="flex items-center text-gray-700">
                <i class="fas fa-check text-green-600 mr-3"></i>
                Mark the proposal as completed
              </li>
              <li class="flex items-center text-gray-700">
                <i class="fas fa-arrow-right text-blue-600 mr-3"></i>
                Allow the group to proceed to the next phase
              </li>
              <li class="flex items-center text-gray-700">
                <i class="fas fa-bell text-yellow-600 mr-3"></i>
                Send approval notifications to all group members
              </li>
            </ul>
          </div>
          
          <form method="POST" id="approvalForm">
            <input type="hidden" id="approval_proposal_id" name="proposal_id">
          </form>
        </div>
        <div class="modal-footer">
          <div class="flex justify-end space-x-4">
            <button type="button" onclick="toggleModal('approvalModal')" class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-semibold transition-all duration-300 hover:scale-105">
              Cancel
            </button>
            <button type="submit" form="approvalForm" name="check_requirements" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-2xl font-semibold transition-all duration-300 hover:scale-105 shadow-lg">
              <i class="fas fa-check mr-2"></i>Approve Proposal
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Revert Approval Modal -->
    <div id="revertModal" class="fixed inset-0 z-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="flex items-center justify-center min-h-screen py-4 px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full modal-content border-0 max-h-[90vh] overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: rgba(59, 130, 246, 0.3) transparent;">
                <div class="bg-gradient-to-r from-orange-600 to-red-700 text-white p-4 border-0">
                    <h3 class="text-lg font-bold flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-undo text-white text-sm"></i>
                        </div>
                        Revert Approval
                    </h3>
                    <p class="text-orange-100 mt-1 text-sm">Undo proposal approval</p>
                </div>
                <div class="p-6">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-xl mr-3"></i>
                            <div>
                                <h4 class="font-medium text-orange-800 mb-1">Warning: Revert Approval</h4>
                                <p class="text-orange-700 text-sm">This will change the proposal status back to pending.</p>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-4">Are you sure you want to revert this approval? This action will:</p>
                    <ul class="list-none space-y-2 mb-6">
                        <li class="flex items-center text-gray-700 text-sm">
                            <i class="fas fa-arrow-left text-orange-600 mr-3 w-4"></i>
                            Change proposal status from "Completed" to "Pending"
                        </li>
                        <li class="flex items-center text-gray-700 text-sm">
                            <i class="fas fa-clock text-red-600 mr-3 w-4"></i>
                            Remove the approval timestamp
                        </li>
                        <li class="flex items-center text-gray-700 text-sm">
                            <i class="fas fa-redo text-blue-600 mr-3 w-4"></i>
                            Allow the proposal to be reviewed again
                        </li>
                    </ul>
                    
                    <form method="POST" id="revertForm">
                        <input type="hidden" id="revert_proposal_id" name="proposal_id">
                    </form>
                </div>
                <div class="backdrop-blur-sm p-4 border-0 space-x-3 flex justify-end">
                    <button type="button" onclick="toggleModal('revertModal')" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-sm">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" form="revertForm" name="revert_approval" class="bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 hover:shadow-lg text-sm">
                        <i class="fas fa-undo mr-2"></i>Revert Approval
                    </button>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl p-8 flex items-center space-x-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <span class="text-lg font-semibold text-gray-700">Processing...</span>
    </div>
  </div>

  </div> <!-- /min-h-screen -->

  <script>
    // --- Modal helpers ---
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        // Handle both modal systems
        if (modal.classList.contains('modal-container')) {
            // New modal system
            modal.classList.toggle('hidden');
        } else {
            // Old modal system (createtimelinemodal and proposalReviewModal)
            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
            } else {
                modal.classList.add('opacity-0', 'pointer-events-none');
            }
        }
    }

    // Close modals on backdrop click or ESC
    document.addEventListener('click', (e)=>{
      const mc = e.target.closest('.modal-container');
      if(mc && e.target === mc){ mc.classList.add('hidden'); mc.classList.remove('flex'); }
      
      // Handle old modal system backdrop clicks
      const oldModal = e.target.closest('.modal-overlay');
      if(oldModal && e.target.classList.contains('modal-overlay')){
        oldModal.classList.add('opacity-0', 'pointer-events-none');
      }
    });
    document.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape'){
        document.querySelectorAll('.modal-container').forEach(m=>{
          m.classList.add('hidden'); m.classList.remove('flex');
        });
        document.querySelectorAll('.modal-overlay').forEach(m=>{
          m.classList.add('opacity-0', 'pointer-events-none');
        });
      }
    });

    // --- Enhanced Countdown System ---
    const ISO_DEADLINE = <?= $isoDeadline ? json_encode($isoDeadline) : 'null' ?>;
    let countdownInterval;
    let lastSecond = -1;

    function setBannerGradient(distance, isExpired = false){
      const banner = document.getElementById('countdown-banner');
      
      if (distance === null) {
        banner.style.background = 'linear-gradient(135deg, rgba(107, 114, 128, 0.95) 0%, rgba(75, 85, 99, 0.95) 100%)';
        return;
      }
      
      if (isExpired) {
        banner.style.background = 'linear-gradient(135deg, rgba(75, 85, 99, 0.95) 0%, rgba(55, 65, 81, 0.95) 100%)';
        return;
      }
      
      if (distance < 24*60*60*1000) {
        banner.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.95) 0%, rgba(220, 38, 38, 0.95) 100%)';
      } else if (distance < 3*24*60*60*1000) {
        banner.style.background = 'linear-gradient(135deg, rgba(245, 158, 11, 0.95) 0%, rgba(217, 119, 6, 0.95) 100%)';
      } else if (distance < 7*24*60*60*1000) {
        banner.style.background = 'linear-gradient(135deg, rgba(251, 191, 36, 0.95) 0%, rgba(245, 158, 11, 0.95) 100%)';
      } else {
        banner.style.background = 'linear-gradient(135deg, rgba(37, 99, 235, 0.95) 0%, rgba(124, 58, 237, 0.95) 50%, rgba(79, 70, 229, 0.95) 100%)';
      }
    }

    function addCountdownAnimation(element, newValue) {
      if (element.textContent !== newValue) {
        element.style.transform = 'scale(1.1)';
        element.style.color = '#fbbf24';
        setTimeout(() => {
          element.textContent = newValue;
          element.style.transform = 'scale(1)';
          element.style.color = '';
        }, 150);
      }
    }

    function updateAdminCountdown(){
      if(!ISO_DEADLINE){
        ['days','hours','minutes','seconds'].forEach(k=>{
          const element = document.getElementById(`admin-countdown-${k}`);
          if (element) element.textContent = '00';
        });
        setBannerGradient(null);
        return;
      }
      
      const deadline = new Date(ISO_DEADLINE).getTime();
      const now = Date.now();
      const distance = deadline - now;

      if (distance < 0) {
        // Expired
        ['days','hours','minutes','seconds'].forEach(k=>{
          const element = document.getElementById(`admin-countdown-${k}`);
          if (element) element.textContent = '00';
        });
        setBannerGradient(distance, true);
        
        // Update banner text to show expired
        const bannerText = document.querySelector('#countdown-banner p');
        if (bannerText && !bannerText.textContent.includes('EXPIRED')) {
          bannerText.innerHTML = '<span class="font-semibold text-red-200">MILESTONE EXPIRED</span><br><span class="text-sm">This phase has ended</span>';
        }
        
        setTimeout(() => location.reload(), 2000);
        return;
      }

      const days = Math.floor(distance/(1000*60*60*24));
      const hours = Math.floor((distance%(1000*60*60*24))/(1000*60*60));
      const minutes = Math.floor((distance%(1000*60*60))/(1000*60));
      const seconds = Math.floor((distance%(1000*60))/1000);

      // Animate changes
      const currentSecond = seconds;
      if (lastSecond !== currentSecond) {
        addCountdownAnimation(document.getElementById("admin-countdown-seconds"), String(seconds).padStart(2,'0'));
        lastSecond = currentSecond;
        
        // Add pulse effect for last 10 seconds
        if (distance < 10000) {
          document.getElementById('countdown-banner').style.animation = 'pulse 0.5s ease-in-out';
          setTimeout(() => {
            document.getElementById('countdown-banner').style.animation = '';
          }, 500);
        }
      }
      
      document.getElementById("admin-countdown-days").textContent = String(days).padStart(2,'0');
      document.getElementById("admin-countdown-hours").textContent = String(hours).padStart(2,'0');
      document.getElementById("admin-countdown-minutes").textContent = String(minutes).padStart(2,'0');

      setBannerGradient(distance);
      
      // Add urgency effects
      if (distance < 60000) { // Last minute
        document.getElementById('countdown-banner').classList.add('animate-pulse');
      } else {
        document.getElementById('countdown-banner').classList.remove('animate-pulse');
      }
    }

    // --- Edit Modal Logic ---
    function openEditModal(timeline, milestones){
      // Reset fields (prevents "another edit popping" feeling)
      document.getElementById('edit_timeline_id').value = timeline.id;
      document.getElementById('edit_title').value = timeline.title || '';
      document.getElementById('edit_description').value = timeline.description || '';
      document.getElementById('deleted_milestones').value = '[]';

      const container = document.getElementById('editMilestoneContainer');
      container.innerHTML = '';

      (milestones || []).forEach((m, i)=>{
        const el = createMilestoneElement({
          id: m.id,
          title: m.title || '',
          description: m.description || '',
          // format to datetime-local: "YYYY-MM-DDTHH:MM"
          deadline: (m.deadline || '').replace(' ', 'T').substring(0,16)
        }, i+1);
        container.appendChild(el);
        flatpickr(el.querySelector('.flatpickr'), { enableTime:true, dateFormat:"Y-m-d H:i", minDate:"today" });
      });

      toggleModal('editTimelineModal');
    }

    function createMilestoneElement(milestone, index){
      const element = document.createElement('div');
      element.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
      element.dataset.milestoneId = milestone.id;

      element.innerHTML = `
        <input type="hidden" name="milestone_id[]" value="${milestone.id}">
        <div class="flex justify-between items-center mb-2">
          <h5 class="font-medium">Milestone #${index}</h5>
          <button type="button" class="text-red-500 hover:text-red-700 remove-milestone"><i class="fas fa-trash"></i></button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="milestone_title[]" value="${milestone.title || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
            <input type="datetime-local" name="milestone_deadline[]" value="${milestone.deadline || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md flatpickr" required>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea name="milestone_description[]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md">${milestone.description || ''}</textarea>
        </div>
      `;
      return element;
    }

    // Proposal Review Modal
    function openProposalReviewModal(proposal) {
      console.log('Opening proposal review modal for:', proposal);
      
      // Check if modal exists
      const modal = document.getElementById('proposalReviewModal');
      if (!modal) {
        console.error('Proposal review modal not found');
        return;
      }
      
      document.getElementById('review_proposal_id').value = proposal.id;
      document.getElementById('review_group_name').value = proposal.group_name;
      document.getElementById('review_submitted_by').value = proposal.submitted_by;
      document.getElementById('review_submission_date').value = new Date(proposal.submitted_at).toLocaleDateString();
      document.getElementById('review_proposal_title').value = proposal.title;
      document.getElementById('review_proposal_description').value = proposal.description;
      document.getElementById('review_proposal_download').href = proposal.file_path;
      // Set proposal id for approve/reject forms inside modal
      const pid = proposal.id;
      const pidInput1 = document.getElementById('modal_payment_proposal_id');
      const pidInput2 = document.getElementById('modal_reject_proposal_id');
      if (pidInput1) pidInput1.value = pid;
      if (pidInput2) pidInput2.value = pid;
      
      // Set current status
      let statusText = 'Pending';
      if (proposal.status) {
        switch (proposal.status) {
          case 'Completed':
            statusText = 'Completed';
            break;
          case 'Scheduled':
            statusText = 'Scheduled for Defense';
            break;
          default:
            statusText = 'Pending';
        }
      }
      
      document.getElementById('review_current_status').value = statusText;
      
      // Set existing feedback
      const feedbackTextarea = document.querySelector('textarea[name="feedback"]');
      if (feedbackTextarea && proposal.feedback) {
        feedbackTextarea.value = proposal.feedback;
      }
      
      // Update payment status summary
      updatePaymentStatusSummary(proposal);
      
      // Per-group Final Defense toggle removed (global control used)

      // Update button based on status and payment
      updateApprovalButton(proposal);
      
      toggleModal('proposalReviewModal');
    }

    // Update approval button based on proposal status
    function updateApprovalButton(proposal) {
      const approvalButton = document.getElementById('approvalButton');
      
      if (proposal.status === 'Completed') {
        approvalButton.textContent = 'Revert Approval';
        approvalButton.className = 'px-8 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 mx-auto';
        approvalButton.onclick = function() { openRevertModal(); };
      } else if (!proposal.payment_status?.has_research_forum_payment) {
        approvalButton.textContent = 'Payment Required';
        approvalButton.className = 'px-8 py-3 bg-red-500 text-white rounded-xl font-semibold shadow-none opacity-80 cursor-not-allowed mx-auto';
        approvalButton.disabled = true;
      } else {
        approvalButton.textContent = 'Approve Proposal';
        approvalButton.className = 'px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 mx-auto';
        approvalButton.disabled = false;
        approvalButton.onclick = function() { openApprovalModal(); };
      }
    }
    
    // Open approval modal
    function openApprovalModal() {
      const proposalId = document.getElementById('review_proposal_id').value;
      document.getElementById('approval_proposal_id').value = proposalId;
      toggleModal('approvalModal');
    }
    
    // Open revert modal
    function openRevertModal(proposal) {
      console.log('Opening revert modal for:', proposal);
      
      // Check if modal exists
      const modal = document.getElementById('revertModal');
      if (!modal) {
        console.error('Revert modal not found');
        return;
      }
      
      if (proposal) {
        document.getElementById('revert_proposal_id').value = proposal.id;
      } else {
        const proposalId = document.getElementById('review_proposal_id').value;
        document.getElementById('revert_proposal_id').value = proposalId;
      }
      
      // Close review modal if open
      const reviewModal = document.getElementById('proposalReviewModal');
      if (reviewModal && !reviewModal.classList.contains('opacity-0')) {
        toggleModal('proposalReviewModal');
      }
      
      toggleModal('revertModal');
    }
    
    // Update payment status summary
    function updatePaymentStatusSummary(proposal) {
      const summaryDiv = document.getElementById('paymentStatusSummary');
      summaryDiv.innerHTML = '';
      
      const paymentTypes = [
        { 
          key: 'has_research_forum_payment', 
          label: 'Research Forum', 
          description: 'Required for proposal submission',
          required: true, 
          imageKey: 'research_forum',
          color: 'blue'
        },
        { 
          key: 'has_pre_oral_payment', 
          label: 'Pre-Oral Defense', 
          description: 'Required for pre-oral defense',
          required: false, 
          imageKey: 'pre_oral_defense',
          color: 'purple'
        },
        { 
          key: 'has_final_defense_payment', 
          label: 'Final Defense', 
          description: 'Required for final defense',
          required: false, 
          imageKey: 'final_defense',
          color: 'green'
        }
      ];
      
      paymentTypes.forEach(payment => {
        const isPaid = proposal.payment_status?.[payment.key] || false;
        const paymentEl = document.createElement('div');
        
        const bgColor = isPaid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
        paymentEl.className = `flex items-center justify-between p-4 rounded-xl border-2 ${bgColor} transition-all hover:shadow-md`;
        
        paymentEl.innerHTML = `
          <div class="flex items-center">
            <div class="${isPaid ? 'bg-green-500' : 'bg-red-500'} p-2 rounded-full mr-4">
              <i class="fas ${isPaid ? 'fa-check' : 'fa-times'} text-white text-sm"></i>
            </div>
            <div>
              <div class="flex items-center">
                <span class="${isPaid ? 'text-green-800' : 'text-red-800'} font-bold text-lg">${payment.label}</span>
                ${payment.required ? '<span class="ml-2 text-xs bg-red-500 text-white px-2 py-1 rounded-full font-bold">REQUIRED</span>' : '<span class="ml-2 text-xs bg-gray-400 text-white px-2 py-1 rounded-full">Optional</span>'}
              </div>
              <p class="text-sm ${isPaid ? 'text-green-600' : 'text-red-600'} mt-1">${payment.description}</p>
            </div>
          </div>
          <div class="text-right">
            <span class="text-lg font-bold ${isPaid ? 'text-green-700' : 'text-red-700'}">
              ${isPaid ? '✓ PAID' : '✗ NOT PAID'}
            </span>
            ${isPaid ? '<p class="text-xs text-green-600 mt-1">Receipt uploaded</p>' : '<p class="text-xs text-red-600 mt-1">Payment required</p>'}
          </div>
        `;
        
        summaryDiv.appendChild(paymentEl);
      });
      
      // Update payment images
      updatePaymentImages(proposal);
    }
    
    // Update payment images display
    function updatePaymentImages(proposal) {
      const imagesContainer = document.getElementById('paymentImagesContainer');
      imagesContainer.innerHTML = '';
      
      const paymentImages = proposal.payment_status?.payment_images || {};
      const paymentImageReview = proposal.payment_status?.payment_image_review || {};
      
      if (Object.keys(paymentImages).length === 0) {
        imagesContainer.innerHTML = '<p class="text-gray-500 text-sm">No payment receipt images uploaded.</p>';
        return;
      }
      
      const paymentTypeLabels = {
        'research_forum': 'Research Forum',
        'pre_oral_defense': 'Pre-Oral Defense', 
        'final_defense': 'Final Defense'
      };
      
      const paymentTypeColors = {
        'research_forum': 'bg-blue-100 text-blue-800 border-blue-200',
        'pre_oral_defense': 'bg-purple-100 text-purple-800 border-purple-200',
        'final_defense': 'bg-green-100 text-green-800 border-green-200'
      };
      
      Object.keys(paymentImages).forEach(paymentType => {
        const images = paymentImages[paymentType];
        if (images && images.length > 0) {
          const sectionDiv = document.createElement('div');
          sectionDiv.className = `border-2 rounded-xl p-4 ${paymentTypeColors[paymentType] || 'border-gray-200'}`;
          
          const headerDiv = document.createElement('div');
          headerDiv.className = 'flex items-center justify-between mb-3';
          headerDiv.innerHTML = `
            <div class="flex items-center">
              <i class="fas fa-receipt text-lg mr-3"></i>
              <div>
                <h4 class="font-bold text-lg">${paymentTypeLabels[paymentType] || paymentType}</h4>
                <p class="text-sm opacity-75">Payment Receipt Images</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="bg-white bg-opacity-50 text-xs font-bold px-3 py-1 rounded-full">
                ${images.length} image${images.length > 1 ? 's' : ''}
              </span>
              <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                <i class="fas fa-check mr-1"></i>PAID
              </span>
            </div>
          `;
          
          const imagesGrid = document.createElement('div');
          imagesGrid.className = 'grid grid-cols-2 md:grid-cols-3 gap-3';
          
          images.forEach((imagePath, index) => {
            const imageDiv = document.createElement('div');
            imageDiv.className = 'relative group cursor-pointer';
            imageDiv.innerHTML = `
              <div class="relative overflow-hidden rounded-lg border-2 border-white shadow-md hover:shadow-lg transition-all cursor-pointer"
                   onclick="openImageModal('${imagePath}', '${paymentTypeLabels[paymentType]}')">
                <img src="${imagePath}" alt="${paymentTypeLabels[paymentType]} Receipt ${index + 1}" 
                     class="w-full h-28 object-cover hover:scale-105 transition-transform duration-300 pointer-events-none">
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded-lg flex items-center justify-center pointer-events-none">
                  <i class="fas fa-search-plus text-white text-xl opacity-0 group-hover:opacity-100 transition-all"></i>
                </div>
                <div class="absolute top-2 left-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded-full pointer-events-none">
                  ${index + 1}
                </div>
              </div>
              <div class="mt-2 flex items-center gap-2">
                <button type="button" class="px-2 py-1 text-xs rounded bg-green-600 text-white hover:bg-green-700" onclick="reviewImage('${paymentType}', ${proposal.id}, ${index}, 'approved')">
                  <i class="fas fa-check mr-1"></i>Approve
                </button>
                <button type="button" class="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700" onclick="showRejectPanel('${paymentType}', ${proposal.id}, ${index})">
                  <i class="fas fa-times mr-1"></i>Reject
                </button>
                <span class="text-xs ml-auto" id="img-status-${paymentType}-${index}"></span>
              </div>
              <div id="reject-panel-${paymentType}-${index}" class="mt-2 hidden">
                <div class="flex items-center gap-2">
                  <input type="text" id="reject-reason-${paymentType}-${index}" class="flex-1 px-2 py-1 border rounded text-xs" placeholder="Reason (e.g., blurry image)" />
                  <button type="button" class="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700" onclick="submitRejectPanel('${paymentType}', ${proposal.id}, ${index})">Confirm</button>
                  <button type="button" class="px-2 py-1 text-xs rounded bg-gray-300 text-gray-800 hover:bg-gray-400" onclick="cancelRejectPanel('${paymentType}', ${index})">Cancel</button>
                </div>
              </div>
            `;
            imagesGrid.appendChild(imageDiv);
          });
          
          sectionDiv.appendChild(headerDiv);
          sectionDiv.appendChild(imagesGrid);
          imagesContainer.appendChild(sectionDiv);
          // Apply existing per-image statuses
          const reviewMap = paymentImageReview[paymentType] || {};
          images.forEach((_, index) => {
            const statusElId = `img-status-${paymentType}-${index}`;
            const statusEl = document.getElementById(statusElId);
            const rv = reviewMap[index];
            if (statusEl && rv) {
              const tag = rv.status === 'approved' ? '<span class="text-green-700 bg-green-100 px-2 py-0.5 rounded">Approved</span>' : '<span class="text-red-700 bg-red-100 px-2 py-0.5 rounded">Rejected</span>';
              const fb = rv.feedback ? `<span class="text-gray-600 ml-2">${rv.feedback}</span>` : '';
              statusEl.innerHTML = tag + fb;
            }
          });
        }
      });
    }

    // AJAX helpers for per-image review
    async function reviewImage(paymentType, proposalId, imageIndex, decision, feedback = '') {
      try {
        const form = new FormData();
        form.append('ajax_update_image_review', '1');
        form.append('proposal_id', String(proposalId));
        form.append('payment_type', paymentType);
        form.append('image_index', String(imageIndex));
        form.append('decision', decision);
        form.append('feedback', feedback);
        const resp = await fetch(window.location.href, { method: 'POST', body: form });
        const data = await resp.json();
        if (!data.ok) { alert(data.error || 'Failed to update'); return; }
        const statusEl = document.getElementById(`img-status-${paymentType}-${imageIndex}`);
        if (statusEl) {
          const tag = decision === 'approved' ? '<span class="text-green-700 bg-green-100 px-2 py-0.5 rounded">Approved</span>' : '<span class="text-red-700 bg-red-100 px-2 py-0.5 rounded">Rejected</span>';
          const fb = feedback ? `<span class=\"text-gray-600 ml-2\">${feedback}</span>` : '';
          statusEl.innerHTML = tag + fb;
        }
        // Update summary badges without full refresh
        try {
          if (paymentType === 'research_forum') {
            const summary = document.getElementById('paymentStatusSummary');
            if (summary && data.status) {
              // Simple refresh of the summary by re-opening the modal data state
              // Re-run the summary and images builder using current proposal object
              // Note: We don't have a live proposal object update; so minimally tweak the badge text
              const badges = summary.querySelectorAll('div');
              badges.forEach(div => {
                if (div.textContent && div.textContent.includes('Research Forum')) {
                  const right = div.querySelector('span.text-lg');
                  if (right) right.textContent = (data.status === 'approved') ? '✓ PAID' : (data.status === 'rejected' ? '✗ NOT PAID' : 'PENDING');
                }
              });
            }
          }
        } catch (e) {}
      } catch (e) {
        alert('Network error');
      }
    }

    function showRejectPanel(paymentType, proposalId, imageIndex) {
      const pane = document.getElementById(`reject-panel-${paymentType}-${imageIndex}`);
      if (pane) pane.classList.remove('hidden');
    }

    function cancelRejectPanel(paymentType, imageIndex) {
      const pane = document.getElementById(`reject-panel-${paymentType}-${imageIndex}`);
      if (pane) pane.classList.add('hidden');
    }

    function submitRejectPanel(paymentType, proposalId, imageIndex) {
      const input = document.getElementById(`reject-reason-${paymentType}-${imageIndex}`);
      const reason = input ? input.value : '';
      reviewImage(paymentType, proposalId, imageIndex, 'rejected', reason || '');
      cancelRejectPanel(paymentType, imageIndex);
    }
    
    // Open image in modal
    function openImageModal(imagePath, paymentType = 'Payment Receipt') {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="relative max-w-5xl max-h-full p-6">
          <div class="bg-white rounded-t-xl p-4 mb-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <i class="fas fa-receipt text-blue-600 text-xl mr-3"></i>
                <div>
                  <h3 class="font-bold text-lg text-gray-800">${paymentType}</h3>
                  <p class="text-sm text-gray-600">Payment Receipt Image</p>
                </div>
              </div>
              <button onclick="this.closest('.fixed').remove()" 
                      class="bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-full transition-all">
                <i class="fas fa-times text-lg"></i>
              </button>
            </div>
          </div>
          <div class="bg-white rounded-b-xl p-4">
            <img src="${imagePath}" alt="${paymentType} Receipt" 
                 class="max-w-full max-h-[70vh] object-contain mx-auto rounded-lg shadow-lg">
          </div>
        </div>
      `;
      
      // Close on backdrop click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          modal.remove();
        }
      });
      
      // Close on ESC key
      const escHandler = function(e) {
        if (e.key === 'Escape') {
          modal.remove();
          document.removeEventListener('keydown', escHandler);
        }
      };
      document.addEventListener('keydown', escHandler);
      
      document.body.appendChild(modal);
    }

    // Global remove handler (works for both modals)
    document.addEventListener('click', function(e){
      const btn = e.target.closest('.remove-milestone');
      if(!btn) return;

      const item = btn.closest('.milestone-item');
      const container = item.parentElement;
      const milestoneId = item.dataset.milestoneId;

      // Track deleted (edit modal only)
      if (milestoneId && milestoneId !== 'new') {
        const deletedInput = document.getElementById('deleted_milestones');
        if (deletedInput) {
          const current = deletedInput.value ? JSON.parse(deletedInput.value) : [];
          current.push(milestoneId);
          deletedInput.value = JSON.stringify(current);
        }
      }

      container.removeChild(item);
      // Renumber
      container.querySelectorAll('.milestone-item h5').forEach((h5, idx)=>{
        h5.textContent = `Milestone #${idx+1}`;
      });
    });

    // Make functions globally accessible
    window.openProposalReviewModal = openProposalReviewModal;
    window.openRevertModal = openRevertModal;
    window.openApprovalModal = openApprovalModal;
    window.updateApprovalButton = updateApprovalButton;
    window.updatePaymentStatusSummary = updatePaymentStatusSummary;
    window.updatePaymentImages = updatePaymentImages;
    window.openImageModal = openImageModal;
    window.toggleModal = toggleModal;

    // Add new milestone in edit modal
    document.getElementById('addEditMilestone').addEventListener('click', function(){
      const container = document.getElementById('editMilestoneContainer');
      const idx = container.querySelectorAll('.milestone-item').length + 1;
      const el = document.createElement('div');
      el.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
      el.dataset.milestoneId = 'new';
      el.innerHTML = `
        <input type="hidden" name="milestone_id[]" value="new">
        <div class="flex justify-between items-center mb-2">
          <h5 class="font-medium">Milestone #${idx}</h5>
          <button type="button" class="text-red-500 hover:text-red-700 remove-milestone"><i class="fas fa-trash"></i></button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="milestone_title[]" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
            <input type="datetime-local" name="milestone_deadline[]" class="w-full px-3 py-2 border border-gray-300 rounded-md flatpickr" required>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea name="milestone_description[]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
        </div>
      `;
      container.appendChild(el);
      flatpickr(el.querySelector('.flatpickr'), { enableTime:true, dateFormat:"Y-m-d H:i", minDate:"today" });
    });

    // Create modal: add new milestone block
    document.getElementById('addMilestone').addEventListener('click', function(){
      const container = document.getElementById('milestoneContainer');
      const count = container.children.length + 1;

      const wrap = document.createElement('div');
      wrap.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
      wrap.innerHTML = `
        <div class="flex justify-between items-center mb-2">
          <h5 class="font-medium">Milestone #${count}</h5>
          <button type="button" class="text-red-500 hover:text-red-700 remove-milestone"><i class="fas fa-trash"></i></button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="milestone_title[]" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
            <input type="datetime-local" name="milestone_deadline[]" class="w-full px-3 py-2 border border-gray-300 rounded-md flatpickr" required>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea name="milestone_description[]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
        </div>
      `;
      container.appendChild(wrap);
      flatpickr(wrap.querySelector('.flatpickr'), { enableTime:true, dateFormat:"Y-m-d H:i", minDate:"today" });
    });

    // Search and filter functions
    function applyFilters() {
      const search = document.getElementById('searchInput').value;
      const program = document.getElementById('programFilter').value;
      
      const params = new URLSearchParams();
      if (search) params.set('search', search);
      if (program) params.set('program', program);
      
      window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    }
    
    function clearFilters() {
      window.location.href = window.location.pathname;
    }
    
    // Allow Enter key to trigger search
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        applyFilters();
      }
    });

    // Toggle program visibility
    function toggleProgram(programId) {
      const content = document.getElementById('program-' + programId);
      const chevron = document.getElementById('prog-chevron-' + programId);
      if (!content || !chevron) return;
      if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
      } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
      }
    }

    // Toggle cluster visibility with smooth animation
    function toggleCluster(clusterId) {
      const content = document.getElementById('cluster-' + clusterId);
      const chevron = document.getElementById('chevron-' + clusterId);
      
      if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
        // Add smooth slide animation
        content.style.opacity = '0';
        content.style.transform = 'translateY(-20px)';
        setTimeout(() => {
          content.style.transition = 'all 0.3s ease';
          content.style.opacity = '1';
          content.style.transform = 'translateY(0)';
        }, 10);
      } else {
        content.style.transition = 'all 0.3s ease';
        content.style.opacity = '0';
        content.style.transform = 'translateY(-20px)';
        chevron.style.transform = 'rotate(0deg)';
        setTimeout(() => {
          content.classList.add('hidden');
          content.style.opacity = '';
          content.style.transform = '';
          content.style.transition = '';
        }, 300);
      }
    }
    
    // Real-time search function (programs, clusters, groups)
    function searchProposals() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const programBlocks = document.querySelectorAll('.program-block');
      const programHeaders = document.querySelectorAll('.program-header');
      const clusterHeaders = document.querySelectorAll('.cluster-header');
      const proposalCards = document.querySelectorAll('.proposal-card');
      
      if (searchTerm === '') {
        // Show all programs, clusters and cards
        programBlocks.forEach(block => block.style.display = 'block');
        document.querySelectorAll('.program-content').forEach(pc => pc.classList.add('hidden'));
        document.querySelectorAll('[id^="prog-chevron-"]').forEach(ch => ch.style.transform = 'rotate(0deg)');
        clusterHeaders.forEach(header => header.parentElement.style.display = 'block');
        proposalCards.forEach(card => card.style.display = 'flex');
        return;
      }
      
      // Hide everything initially
      programBlocks.forEach(block => block.style.display = 'none');
      document.querySelectorAll('.program-content').forEach(pc => pc.classList.add('hidden'));
      document.querySelectorAll('[id^="prog-chevron-"]').forEach(ch => ch.style.transform = 'rotate(0deg)');
      clusterHeaders.forEach(header => header.parentElement.style.display = 'none');
      
      // Check program headers
      programHeaders.forEach(header => {
        const titleEl = header.querySelector('.program-title');
        const name = titleEl ? titleEl.textContent.toLowerCase() : '';
        if (name.includes(searchTerm)) {
          const block = header.closest('.program-block');
          if (block) block.style.display = 'block';
          const programId = header.querySelector('[id^="prog-chevron-"]').id.replace('prog-chevron-', '');
          const programContent = document.getElementById('program-' + programId);
          if (programContent) {
            programContent.classList.remove('hidden');
            document.getElementById('prog-chevron-' + programId).style.transform = 'rotate(180deg)';
            // Show all clusters under this program
            programContent.querySelectorAll('.cluster-header').forEach(ch => ch.parentElement.style.display = 'block');
            programContent.querySelectorAll('.cluster-content').forEach(cc => cc.classList.remove('hidden'));
            programContent.querySelectorAll('.proposal-card').forEach(card => card.style.display = 'flex');
          }
        }
      });

      // Check cluster headers directly
      clusterHeaders.forEach(header => {
        const titleEl = header.querySelector('.cluster-title');
        const clusterName = titleEl ? titleEl.textContent.toLowerCase() : '';
        if (clusterName.includes(searchTerm)) {
          // Show parent program
          const programContent = header.closest('.program-content');
          if (programContent) {
            const programId = programContent.id.replace('program-', '');
            const programBlock = programContent.closest('.program-block');
            if (programBlock) programBlock.style.display = 'block';
            programContent.classList.remove('hidden');
            document.getElementById('prog-chevron-' + programId).style.transform = 'rotate(180deg)';
          }
          header.parentElement.style.display = 'block';
          // Auto-expand cluster
          const clusterId = header.querySelector('[id^="chevron-"]').id.replace('chevron-', '');
          const clusterContent = document.getElementById('cluster-' + clusterId);
          clusterContent.classList.remove('hidden');
          document.getElementById('chevron-' + clusterId).style.transform = 'rotate(180deg)';
          // Show all cards in this cluster
          clusterContent.querySelectorAll('.proposal-card').forEach(card => {
            card.style.display = 'flex';
          });
        }
      });
      
      // Search through proposal cards and cluster headers
      proposalCards.forEach(card => {
        const group = card.querySelector('.text-gray-700').textContent.toLowerCase();
        const program = card.querySelectorAll('.text-gray-700')[1].textContent.toLowerCase();
        
        // Get cluster name from parent cluster header
        const clusterContent = card.closest('.cluster-content');
        let clusterName = '';
        if (clusterContent) {
          const clusterId = clusterContent.id.replace('cluster-', '');
          const clusterHeader = document.getElementById('chevron-' + clusterId).closest('.cluster-header');
          const titleEl = clusterHeader ? clusterHeader.querySelector('.cluster-title') : null;
          clusterName = titleEl ? titleEl.textContent.toLowerCase() : '';
        }
        
        // Get program name from parent program block
        let programName = '';
        const programBlock = card.closest('.program-block');
        if (programBlock) {
          const titleEl = programBlock.querySelector('.program-title');
          programName = titleEl ? titleEl.textContent.toLowerCase() : '';
        }
        
        const matches = group.includes(searchTerm) || 
                       program.includes(searchTerm) || 
                        clusterName.includes(searchTerm) ||
                        programName.includes(searchTerm);
        
        if (matches) {
          card.style.display = 'flex';
          // Show parent cluster
          const clusterContent = card.closest('.cluster-content');
          if (clusterContent) {
            const clusterId = clusterContent.id.replace('cluster-', '');
            const clusterHeaderEl = document.getElementById('chevron-' + clusterId).closest('.cluster-header');
            const clusterWrapper = clusterHeaderEl ? clusterHeaderEl.parentElement : null;
            if (clusterWrapper) clusterWrapper.style.display = 'block';
            // Auto-expand cluster
            clusterContent.classList.remove('hidden');
            document.getElementById('chevron-' + clusterId).style.transform = 'rotate(180deg)';
          }
          // Show parent program
          const programContent = card.closest('.program-content');
          if (programContent) {
            const programId = programContent.id.replace('program-', '');
            const programBlock = programContent.closest('.program-block');
            if (programBlock) programBlock.style.display = 'block';
            programContent.classList.remove('hidden');
            document.getElementById('prog-chevron-' + programId).style.transform = 'rotate(180deg)';
          }
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Show loading overlay
    function showLoading() {
      document.getElementById('loadingOverlay').classList.remove('hidden');
    }
    
    // Hide loading overlay
    function hideLoading() {
      document.getElementById('loadingOverlay').classList.add('hidden');
    }
    
    // Enhanced form submission with loading
    function submitWithLoading(form) {
      showLoading();
      form.submit();
    }

    // Init on load
    document.addEventListener('DOMContentLoaded', function(){
      // Initialize any existing flatpickr fields
      flatpickr(".flatpickr", { 
        enableTime: true, 
        dateFormat: "Y-m-d H:i", 
        minDate: "today",
        theme: "material_blue"
      });

      // Enhanced countdown with smooth updates
      updateAdminCountdown();
      countdownInterval = setInterval(updateAdminCountdown, 1000);
      
      // Ensure modals are attached directly to <body> to avoid clipping by parent containers
       try {
        const modalIds = ['proposalReviewModal', 'approvalModal', 'editTimelineModal', 'createTimelineModal', 'revertModal'];
        modalIds.forEach(id => {
          const el = document.getElementById(id);
          if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
          }
        });
      } catch (e) {}
      
      // Add loading to form submissions
      document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
          showLoading();
        });
      });
      
      // Auto-hide loading after page load
      setTimeout(hideLoading, 500);
      
      // Add smooth transitions to countdown elements
      ['days','hours','minutes','seconds'].forEach(k=>{
        const element = document.getElementById(`admin-countdown-${k}`);
        if (element) {
          element.style.transition = 'all 0.3s ease';
        }
      });
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
      if (countdownInterval) {
        clearInterval(countdownInterval);
      }
    });
  </script>
</body>
</html>