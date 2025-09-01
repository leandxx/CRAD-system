<?php
session_start();
date_default_timezone_set('Asia/Manila');
include("../includes/connection.php");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        'payment_details' => []
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
        $stmt->close();
        
        // Check for pre-oral defense payment
        $pre_oral_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'pre_oral_defense' AND status = 'approved'";
        $stmt = $conn->prepare($pre_oral_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $pre_oral_result = $stmt->get_result();
        $payment_status['has_pre_oral_payment'] = $pre_oral_result->num_rows > 0;
        $stmt->close();
        
        // Check for final defense payment
        $final_defense_query = "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'final_defense' AND status = 'approved'";
        $stmt = $conn->prepare($final_defense_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $final_result = $stmt->get_result();
        $payment_status['has_final_defense_payment'] = $final_result->num_rows > 0;
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
  <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
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
    .animate-slide-up { animation: slideInUp 0.6s ease-out; }
    .animate-fade-in { animation: fadeIn 0.8s ease-out; }
    .animate-scale-in { animation: scaleIn 0.5s ease-out; }
    
    .modal-container{
      display:flex;
      align-items:center;
      justify-content:center;
      position:fixed;
      inset:0;
      background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
      backdrop-filter: blur(4px);
      z-index:50;
      transition: all 300ms ease-in-out;
    }
    .modal-content{
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 16px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      width:100%;
      max-width:42rem;
      max-height:90vh;
      overflow-y:auto;
      transform: translateY(-30px) scale(0.95);
      transition: all 300ms cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .modal-container:not(.hidden) .modal-content {
      transform: translateY(0) scale(1);
    }
    .milestone-dot{
      width:40px;
      height:40px;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.3);
      transition: all 0.3s ease;
    }
    .milestone-dot:hover {
      transform: scale(1.1);
      box-shadow: 0 8px 20px -8px rgba(0, 0, 0, 0.4);
    }
    .proposal-status-badge {
      display: inline-block;
      padding: 0.375rem 0.875rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .status-pending { 
      background: linear-gradient(135deg, #fef3c7, #fde68a); 
      color: #92400e; 
      border: 1px solid #f59e0b;
    }
    .status-under-review { 
      background: linear-gradient(135deg, #dbeafe, #bfdbfe); 
      color: #1e40af; 
      border: 1px solid #3b82f6;
    }
    .status-approved { 
      background: linear-gradient(135deg, #d1fae5, #a7f3d0); 
      color: #065f46; 
      border: 1px solid #10b981;
    }
    .status-rejected { 
      background: linear-gradient(135deg, #fee2e2, #fecaca); 
      color: #b91c1c; 
      border: 1px solid #ef4444;
    }
    .status-revision-requested { 
      background: linear-gradient(135deg, #fef3c7, #fde68a); 
      color: #92400e; 
      border: 1px solid #f59e0b;
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
    .gradient-purple {
      background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }
    .proposal-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    .proposal-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    .proposal-card:hover::before {
      left: 100%;
    }
    .proposal-card:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.15);
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

      <!-- Countdown Header -->
      <div id="countdown-banner" class="bg-gradient-to-r from-primary to-secondary text-white p-6 rounded-2xl shadow-xl mb-8 animate-slide-up">
        <div class="flex flex-col md:flex-row items-center justify-between">
          <div class="flex items-center mb-4 md:mb-0">
            <i class="fas fa-clock text-2xl mr-3"></i>
            <div>
              <h3 class="font-bold text-lg">Current Phase Countdown</h3>
              <?php if (!empty($milestones)): ?>
                <?php
                  $now = new DateTime();
                  $current_milestone = null;
                  foreach ($milestones as $milestone) {
                      $deadline = new DateTime($milestone['deadline']);
                      if ($deadline > $now) { $current_milestone = $milestone; break; }
                  }
                ?>
                <p class="text-sm opacity-90">
                  <?= $current_milestone ? htmlspecialchars($current_milestone['title']) : 'All milestones completed' ?> -
                  Ends <?= $current_milestone ? date('F j, Y \a\t g:i A', strtotime($current_milestone['deadline'])) : '' ?>
                </p>
              <?php else: ?>
                <p class="text-sm opacity-90">No active milestones</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="flex items-center">
            <div class="text-center px-4">
              <div id="admin-countdown-days" class="text-3xl font-bold">00</div>
              <div class="text-xs opacity-90">Days</div>
            </div>
            <div class="text-2xl font-bold opacity-70">:</div>
            <div class="text-center px-4">
              <div id="admin-countdown-hours" class="text-3xl font-bold">00</div>
              <div class="text-xs opacity-90">Hours</div>
            </div>
            <div class="text-2xl font-bold opacity-70">:</div>
            <div class="text-center px-4">
              <div id="admin-countdown-minutes" class="text-3xl font-bold">00</div>
              <div class="text-xs opacity-90">Minutes</div>
            </div>
            <div class="text-2xl font-bold opacity-70">:</div>
            <div class="text-center px-4">
              <div id="admin-countdown-seconds" class="text-3xl font-bold">00</div>
              <div class="text-xs opacity-90">Seconds</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Timeline Management -->
      <div class="stats-card p-8 mb-8 animate-fade-in">
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center">
            <div class="gradient-blue p-4 rounded-2xl shadow-lg mr-4">
              <i class="fas fa-calendar-alt text-white text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Submission Timeline Management</h2>
          </div>
          <button type="button"
            onclick="toggleModal('createTimelineModal')"
            class="gradient-blue text-white px-6 py-3 rounded-xl flex items-center font-semibold transition-all duration-300 hover:shadow-lg hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Create New Timeline
          </button>
        </div>

        <?php if ($active_timeline): ?>
          <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-semibold"><?= htmlspecialchars($active_timeline['title']) ?></h3>
              <div class="flex space-x-2">
                <button type="button"
                  onclick='openEditModal(<?= htmlspecialchars(json_encode($active_timeline), ENT_QUOTES, "UTF-8") ?>, <?= htmlspecialchars(json_encode($milestones), ENT_QUOTES, "UTF-8") ?>)'
                  class="text-primary hover:text-secondary transition">
                  <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <form method="POST" class="inline">
                  <input type="hidden" name="timeline_id" value="<?= (int)$active_timeline['id'] ?>">
                  <button type="submit" name="toggle_timeline" class="text-gray-500 hover:text-gray-700 transition">
                    <i class="fas fa-toggle-on mr-1"></i> Disable
                  </button>
                </form>
              </div>
            </div>
            <p class="text-gray-600 mb-6"><?= htmlspecialchars($active_timeline['description']) ?></p>

            <!-- Progress Timeline -->
            <div class="relative">
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
              <div class="h-2 bg-gray-200 rounded-full mb-8">
                <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full" style="width: <?= $progress ?>%"></div>
              </div>

              <div class="flex justify-between relative gap-2">
                <?php foreach ($milestones as $index => $milestone):
                    $deadline = new DateTime($milestone['deadline']);
                    $is_past = $deadline < $now;
                    $prevDeadline = $index > 0 ? new DateTime($milestones[$index-1]['deadline']) : null;
                    $is_current = !$is_past && (!$prevDeadline || $prevDeadline < $now);
                ?>
                  <div class="flex-1 text-center px-2">
                    <div class="milestone-dot <?= $is_past ? 'bg-success' : ($is_current ? 'bg-warning' : 'bg-gray-300') ?> text-white mx-auto mb-2">
                      <i class="fas <?= $is_past ? 'fa-check' : ($is_current ? 'fa-exclamation' : 'fa-flag') ?>"></i>
                    </div>
                    <div class="text-sm font-medium"><?= htmlspecialchars($milestone['title']) ?></div>
                    <div class="text-xs text-gray-500"><?= date('M j, Y', strtotime($milestone['deadline'])) ?></div>
                    <?php if ($is_current):
                        $diff = $now->diff($deadline);
                        $days_left = $diff->days;
                    ?>
                      <div class="text-xs mt-1 font-medium text-warning"><?= (int)$days_left ?> days left</div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-500">No active timeline</h3>
            <p class="text-gray-400">Create a new timeline to get started</p>
          </div>
        <?php endif; ?>
      </div>

    <!-- Proposal Review Section -->
<div class="stats-card p-8 mb-8 animate-scale-in">
  <div class="flex items-center justify-between mb-8">
    <div class="flex items-center">
      <div class="gradient-purple p-4 rounded-2xl shadow-lg mr-4">
        <i class="fas fa-file-alt text-white text-2xl"></i>
      </div>
      <h2 class="text-3xl font-bold text-gray-800">Proposal Review</h2>
    </div>
    <span class="gradient-green text-white text-sm font-bold px-4 py-2 rounded-xl shadow-lg">
      <?php echo count($proposals); ?> Submitted
    </span>
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
    // Group proposals by cluster and program
    $grouped_proposals = [];
    foreach ($proposals as $proposal) {
        $cluster_key = $proposal['program'] . ' - Cluster ' . ($proposal['cluster'] ?? 'Unassigned');
        $grouped_proposals[$cluster_key][] = $proposal;
    }
    ?>
    
    <?php foreach ($grouped_proposals as $cluster_name => $cluster_proposals): ?>
      <div class="mb-8">
        <div class="cluster-header cursor-pointer bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 mb-4 hover:shadow-md transition-all" onclick="toggleCluster('<?php echo md5($cluster_name); ?>')">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="fas fa-layer-group text-blue-600 text-xl mr-3"></i>
              <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($cluster_name); ?></h3>
              <span class="ml-4 gradient-blue text-white text-sm font-bold px-3 py-2 rounded-xl shadow-lg">
                <?php echo count($cluster_proposals); ?> proposal<?php echo count($cluster_proposals) !== 1 ? 's' : ''; ?>
              </span>
            </div>
            <i class="fas fa-chevron-down text-blue-600 transition-transform" id="chevron-<?php echo md5($cluster_name); ?>"></i>
          </div>
        </div>
        
        <div class="cluster-content hidden" id="cluster-<?php echo md5($cluster_name); ?>">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 ml-8">
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
      
      <div class="proposal-card bg-white border border-gray-200 shadow-md rounded-2xl p-6 flex flex-col justify-between">
        <!-- Header -->
        <div>
          <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-semibold text-gray-900">
              <?php echo htmlspecialchars($proposal['title']); ?>
            </h3>
            <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $status_class; ?>">
              <?php echo $status_text; ?>
            </span>
          </div>

          <p class="text-sm text-gray-700 mb-2">
            <span class="font-semibold">Group:</span> <?php echo htmlspecialchars($proposal['group_name']); ?>
          </p>
          <p class="text-sm text-gray-700 mb-2">
            <span class="font-semibold">Program:</span> <?php echo htmlspecialchars($proposal['program']); ?>
          </p>
          <p class="text-sm text-gray-700 mb-2">
            <span class="font-semibold">Submitted By:</span> <?php echo htmlspecialchars($proposal['submitted_by']); ?>
          </p>
          <p class="text-sm text-gray-500 mb-2">
            <span class="font-semibold">Date:</span> <?php echo date('M j, Y', strtotime($proposal['submitted_at'])); ?>
          </p>
          
          <!-- Payment Status Indicator -->
          <div class="mb-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-700">Payment Status:</span>
              <?php if ($has_paid): ?>
                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                  <i class="fas fa-check-circle mr-1"></i> Paid
                </span>
              <?php else: ?>
                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                  <i class="fas fa-times-circle mr-1"></i> Not Paid
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-center items-center mt-4 pt-4 border-t border-gray-100">
          <?php if ($proposal['status'] === 'Completed'): ?>
            <button onclick='openRevertModal(<?php echo htmlspecialchars(json_encode($proposal), ENT_QUOTES, "UTF-8"); ?>)'
              class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold rounded-xl shadow-lg transition-all duration-300 hover:scale-105">
              <i class="fas fa-undo mr-2"></i> Revert Approval
            </button>
          <?php elseif (!$has_paid): ?>
            <button disabled class="inline-flex items-center px-6 py-3 bg-red-500 text-white text-sm font-bold rounded-xl shadow-lg cursor-not-allowed">
              <i class="fas fa-times mr-2"></i> Payment Required
            </button>
          <?php else: ?>
            <button onclick='openProposalReviewModal(<?php echo htmlspecialchars(json_encode($proposal), ENT_QUOTES, "UTF-8"); ?>)'
              class="inline-flex items-center px-6 py-3 gradient-green text-white text-sm font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
              <i class="fas fa-check mr-2"></i> Review Proposal
            </button>
          <?php endif; ?>
        </div>
      </div>
          <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="text-center py-8">
      <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
      <h3 class="text-lg font-medium text-gray-500">No proposals submitted yet</h3>
      <p class="text-gray-400">Proposals will appear here once students submit them</p>
    </div>
  <?php endif; ?>
</div>

    <!-- Create Timeline Modal -->
    <div id="createTimelineModal" class="modal-container hidden">
      <div class="modal-content">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Create New Timeline</h3>
            <button type="button" onclick="toggleModal('createTimelineModal')" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form method="POST">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Timeline Title</label>
              <input type="text" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>

            <h4 class="text-lg font-medium mb-3">Milestones</h4>
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

            <div class="flex justify-between mt-4">
              <button type="button" id="addMilestone" class="text-primary hover:text-secondary">
                <i class="fas fa-plus mr-1"></i> Add Milestone
              </button>
              <div>
                <button type="button" onclick="toggleModal('createTimelineModal')" class="mr-2 px-4 py-2 border border-gray-300 rounded-md">
                  Cancel
                </button>
                <button type="submit" name="create_timeline" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-md">
                  Save Timeline
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Timeline Modal -->
    <div id="editTimelineModal" class="modal-container hidden">
      <div class="modal-content">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Edit Timeline</h3>
            <button type="button" onclick="toggleModal('editTimelineModal')" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form method="POST">
            <input type="hidden" id="edit_timeline_id" name="timeline_id">
            <input type="hidden" id="deleted_milestones" name="deleted_milestones" value="">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Timeline Title</label>
              <input type="text" id="edit_title" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>

            <h4 class="text-lg font-medium mb-3">Milestones</h4>
            <div id="editMilestoneContainer"><!-- populated by JS --></div>

            <div class="flex justify-between mt-4">
              <button type="button" id="addEditMilestone" class="text-primary hover:text-secondary">
                <i class="fas fa-plus mr-1"></i> Add Milestone
              </button>
              <div>
                <button type="button" onclick="toggleModal('editTimelineModal')" class="mr-2 px-4 py-2 border border-gray-300 rounded-md">
                  Cancel
                </button>
                <button type="submit" name="update_timeline" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-md">
                  Update Timeline
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Proposal Review Modal -->
    <div id="proposalReviewModal" class="modal-container hidden">
      <div class="modal-content" style="max-width: 800px;">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Review Proposal</h3>
            <button type="button" onclick="toggleModal('proposalReviewModal')" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form method="POST">
            <input type="hidden" id="review_proposal_id" name="proposal_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Group Name</label>
                <p id="review_group_name" class="text-sm text-gray-900 p-2 bg-gray-100 rounded-md"></p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Submitted By</label>
                <p id="review_submitted_by" class="text-sm text-gray-900 p-2 bg-gray-100 rounded-md"></p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Submission Date</label>
                <p id="review_submission_date" class="text-sm text-gray-900 p-2 bg-gray-100 rounded-md"></p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Status</label>
                <p id="review_current_status" class="text-sm text-gray-900 p-2 bg-gray-100 rounded-md"></p>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Proposal Title</label>
              <p id="review_proposal_title" class="text-lg font-medium text-gray-900 p-2 bg-gray-100 rounded-md"></p>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Proposal Description</label>
              <p id="review_proposal_description" class="text-sm text-gray-900 p-2 bg-gray-100 rounded-md h-32 overflow-y-auto"></p>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Download Proposal</label>
              <a id="review_proposal_download" href="#" target="_blank" class="inline-flex items-center text-primary hover:text-secondary">
                <i class="fas fa-download mr-2"></i> Download PDF File
              </a>
            </div>
            
            <div class="mb-6">
              <button type="button" onclick="openApprovalModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full" id="approvalButton">
                Approve Proposal
              </button>
            </div>
            
            <!-- Payment Status Summary -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
              <h4 class="text-lg font-medium mb-3">💰 Payment Status Summary</h4>
              <div id="paymentStatusSummary" class="space-y-2">
                <!-- Payment status will be populated by JavaScript -->
              </div>
            </div>
            

            <div class="flex justify-end">
              <button type="button" onclick="toggleModal('proposalReviewModal')" class="px-4 py-2 border border-gray-300 rounded-md">
                Close
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Approval Confirmation Modal -->
    <div id="approvalModal" class="modal-container hidden">
      <div class="modal-content" style="max-width: 500px;">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-green-800">✅ Approve Proposal</h3>
            <button type="button" onclick="toggleModal('approvalModal')" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="mb-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
              <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                <div>
                  <h4 class="font-semibold text-green-800">Ready for Approval</h4>
                  <p class="text-green-700 text-sm">All requirements have been verified and the group has completed payment.</p>
                </div>
              </div>
            </div>
            
            <p class="text-gray-700 mb-4">Are you sure you want to approve this proposal? This action will:</p>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 mb-4">
              <li>Mark the proposal as completed</li>
              <li>Allow the group to proceed to the next phase</li>
              <li>Send approval notifications to all group members</li>
            </ul>
          </div>
          
          <form method="POST">
            <input type="hidden" id="approval_proposal_id" name="proposal_id">
            <div class="flex justify-end space-x-3">
              <button type="button" onclick="toggleModal('approvalModal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" name="check_requirements" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-semibold">
                <i class="fas fa-check mr-2"></i>Approve Proposal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Revert Approval Modal -->
    <div id="revertModal" class="modal-container hidden">
      <div class="modal-content" style="max-width: 500px;">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-orange-800">🔄 Revert Approval</h3>
            <button type="button" onclick="toggleModal('revertModal')" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="mb-6">
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
              <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-orange-600 text-2xl mr-3"></i>
                <div>
                  <h4 class="font-semibold text-orange-800">Warning: Revert Approval</h4>
                  <p class="text-orange-700 text-sm">This will change the proposal status back to pending.</p>
                </div>
              </div>
            </div>
            
            <p class="text-gray-700 mb-4">Are you sure you want to revert this approval? This action will:</p>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 mb-4">
              <li>Change proposal status from "Completed" to "Pending"</li>
              <li>Remove the approval timestamp</li>
              <li>Allow the proposal to be reviewed again</li>
            </ul>
          </div>
          
          <form method="POST">
            <input type="hidden" id="revert_proposal_id" name="proposal_id">
            <div class="flex justify-end space-x-3">
              <button type="button" onclick="toggleModal('revertModal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" name="revert_approval" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-md font-semibold">
                <i class="fas fa-undo mr-2"></i>Revert Approval
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div> <!-- /min-h-screen -->

  <script>
    // --- Modal helpers ---
    function toggleModal(modalId){
      const modal = document.getElementById(modalId);
      modal.classList.toggle('hidden');
      modal.classList.toggle('flex');
    }

    // Close modals on backdrop click or ESC
    document.addEventListener('click', (e)=>{
      const mc = e.target.closest('.modal-container');
      if(mc && e.target === mc){ mc.classList.add('hidden'); mc.classList.remove('flex'); }
    });
    document.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape'){
        document.querySelectorAll('.modal-container').forEach(m=>{
          m.classList.add('hidden'); m.classList.remove('flex');
        });
      }
    });

    // --- Countdown (uses ISO 8601 to avoid timezone parse issues) ---
    const ISO_DEADLINE = <?= $isoDeadline ? json_encode($isoDeadline) : 'null' ?>;

    function setBannerGradient(distance){
      const banner = document.getElementById('countdown-banner');
      banner.classList.remove('from-primary','to-secondary','from-yellow-500','to-yellow-600','from-warning','to-orange-500','from-gray-500','to-gray-600');

      if (distance === null) {
        banner.classList.add('from-gray-500','to-gray-600');
        return;
      }
      if (distance < 0) {
        // Past — let reload handle it
        return;
      }
      if (distance < 24*60*60*1000) {
        banner.classList.add('from-warning','to-orange-500');
      } else if (distance < 3*24*60*60*1000) {
        banner.classList.add('from-yellow-500','to-yellow-600');
      } else {
        banner.classList.add('from-primary','to-secondary');
      }
    }

    function updateAdminCountdown(){
      if(!ISO_DEADLINE){
        ['days','hours','minutes','seconds'].forEach(k=>{
          document.getElementById(`admin-countdown-${k}`).textContent = '00';
        });
        setBannerGradient(null);
        return;
      }
      const deadline = new Date(ISO_DEADLINE).getTime();
      const now = Date.now();
      const distance = deadline - now;

      const days = Math.floor(distance/(1000*60*60*24));
      const hours = Math.floor((distance%(1000*60*60*24))/(1000*60*60));
      const minutes = Math.floor((distance%(1000*60*60))/(1000*60));
      const seconds = Math.floor((distance%(1000*60))/1000);

      document.getElementById("admin-countdown-days").textContent = String(Math.max(0,days)).padStart(2,'0');
      document.getElementById("admin-countdown-hours").textContent = String(Math.max(0,hours)).padStart(2,'0');
      document.getElementById("admin-countdown-minutes").textContent = String(Math.max(0,minutes)).padStart(2,'0');
      document.getElementById("admin-countdown-seconds").textContent = String(Math.max(0,seconds)).padStart(2,'0');

      setBannerGradient(distance);

      if (distance < 0) {
        // Add a small delay before reload to ensure the milestone is marked as completed
        setTimeout(() => {
            location.reload();
        }, 1000);
        return;
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
      document.getElementById('review_proposal_id').value = proposal.id;
      document.getElementById('review_group_name').textContent = proposal.group_name;
      document.getElementById('review_submitted_by').textContent = proposal.submitted_by;
      document.getElementById('review_submission_date').textContent = new Date(proposal.submitted_at).toLocaleDateString();
      document.getElementById('review_proposal_title').textContent = proposal.title;
      document.getElementById('review_proposal_description').textContent = proposal.description;
      document.getElementById('review_proposal_download').href = proposal.file_path;
      
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
      
      document.getElementById('review_current_status').textContent = statusText;
      
      // Set existing feedback
      const feedbackTextarea = document.querySelector('textarea[name="feedback"]');
      if (feedbackTextarea && proposal.feedback) {
        feedbackTextarea.value = proposal.feedback;
      }
      
      // Update payment status summary
      updatePaymentStatusSummary(proposal);
      
      // Update button based on status and payment
      updateApprovalButton(proposal);
      

      
      toggleModal('proposalReviewModal');
    }

    // Update approval button based on proposal status
    function updateApprovalButton(proposal) {
      const approvalButton = document.getElementById('approvalButton');
      
      if (proposal.status === 'Completed') {
        approvalButton.textContent = '🔄 Revert Approval';
        approvalButton.className = 'bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md w-full font-semibold';
        approvalButton.onclick = function() { openRevertModal(); };
      } else if (!proposal.payment_status?.has_research_forum_payment) {
        approvalButton.textContent = '❌ Payment Required';
        approvalButton.className = 'bg-red-500 text-white px-4 py-2 rounded-md w-full font-semibold cursor-not-allowed';
        approvalButton.disabled = true;
      } else {
        approvalButton.textContent = '✅ Approve Proposal';
        approvalButton.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full font-semibold';
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
      if (proposal) {
        document.getElementById('revert_proposal_id').value = proposal.id;
      } else {
        const proposalId = document.getElementById('review_proposal_id').value;
        document.getElementById('revert_proposal_id').value = proposalId;
      }
      toggleModal('proposalReviewModal');
      toggleModal('revertModal');
    }
    
    // Update payment status summary
    function updatePaymentStatusSummary(proposal) {
      const summaryDiv = document.getElementById('paymentStatusSummary');
      summaryDiv.innerHTML = '';
      
      const paymentTypes = [
        { key: 'has_research_forum_payment', label: 'Research Forum', required: true },
        { key: 'has_pre_oral_payment', label: 'Pre-Oral Defense', required: false },
        { key: 'has_final_defense_payment', label: 'Final Defense', required: false }
      ];
      
      paymentTypes.forEach(payment => {
        const isPaid = proposal.payment_status?.[payment.key] || false;
        const paymentEl = document.createElement('div');
        paymentEl.className = 'flex items-center justify-between py-2 px-3 rounded-md';
        paymentEl.style.backgroundColor = isPaid ? '#f0fdf4' : '#fef2f2';
        paymentEl.className += ' border border-gray-200';
        
        paymentEl.innerHTML = `
          <div class="flex items-center">
            <i class="fas ${isPaid ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600'} mr-2"></i>
            <span class="${isPaid ? 'text-green-800 font-medium' : 'text-red-800'}">${payment.label}</span>
            ${payment.required ? '<span class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Required</span>' : ''}
          </div>
          <span class="text-xs font-medium ${isPaid ? 'text-green-700' : 'text-red-700'}">
            ${isPaid ? 'PAID' : 'NOT PAID'}
          </span>
        `;
        
        summaryDiv.appendChild(paymentEl);
      });
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

    // Toggle cluster visibility
    function toggleCluster(clusterId) {
      const content = document.getElementById('cluster-' + clusterId);
      const chevron = document.getElementById('chevron-' + clusterId);
      
      if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
      } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
      }
    }
    
    // Real-time search function
    function searchProposals() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const clusterHeaders = document.querySelectorAll('.cluster-header');
      const proposalCards = document.querySelectorAll('.proposal-card');
      
      if (searchTerm === '') {
        // Show all clusters and cards
        clusterHeaders.forEach(header => header.parentElement.style.display = 'block');
        proposalCards.forEach(card => card.style.display = 'flex');
        return;
      }
      
      // Hide all clusters initially
      clusterHeaders.forEach(header => header.parentElement.style.display = 'none');
      
      // Also check cluster headers directly
      clusterHeaders.forEach(header => {
        const clusterName = header.querySelector('h3').textContent.toLowerCase();
        if (clusterName.includes(searchTerm)) {
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
          clusterName = clusterHeader.querySelector('h3').textContent.toLowerCase();
        }
        
        const matches = group.includes(searchTerm) || 
                       program.includes(searchTerm) || 
                       clusterName.includes(searchTerm);
        
        if (matches) {
          card.style.display = 'flex';
          // Show parent cluster
          const clusterContent = card.closest('.cluster-content');
          if (clusterContent) {
            const clusterId = clusterContent.id.replace('cluster-', '');
            const clusterHeader = document.getElementById('chevron-' + clusterId).closest('.cluster-header').parentElement;
            clusterHeader.style.display = 'block';
            // Auto-expand cluster
            clusterContent.classList.remove('hidden');
            document.getElementById('chevron-' + clusterId).style.transform = 'rotate(180deg)';
          }
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Init on load
    document.addEventListener('DOMContentLoaded', function(){
      // Initialize any existing flatpickr fields
      flatpickr(".flatpickr", { enableTime:true, dateFormat:"Y-m-d H:i", minDate:"today" });

      // Countdown every second
      updateAdminCountdown();
      setInterval(updateAdminCountdown, 1000);
    });
  </script>
</body>
</html>