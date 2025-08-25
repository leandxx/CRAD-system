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

    // Handle proposal status update
    if (isset($_POST['update_proposal_status'])) {
        $proposal_id = (int)($_POST['proposal_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $feedback = $_POST['feedback'] ?? '';

        // Convert to proper case to match database enum
        $status_map = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'under_review' => 'Under Review',
            'revision_requested' => 'Revision Requested'
        ];
        
        $db_status = $status_map[$status] ?? 'Pending';

        $stmt = $conn->prepare("UPDATE proposals SET status = ?, feedback = ?, reviewed_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $db_status, $feedback, $proposal_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Proposal status updated successfully!";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
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

// Get all submitted proposals for review
$proposals_query = "SELECT 
    p.*, 
    g.name AS group_name, 
    g.id AS group_id, 
    CONCAT(sp.full_name) AS submitted_by
FROM proposals p
JOIN groups g ON p.group_id = g.id
JOIN group_members gm ON g.id = gm.group_id
JOIN student_profiles sp ON gm.student_id = sp.user_id
WHERE gm.student_id = (
    SELECT student_id 
    FROM group_members 
    WHERE group_id = g.id 
    LIMIT 1
)
ORDER BY p.submitted_at DESC;
";
$proposals_result = mysqli_query($conn, $proposals_query);
$proposals = [];

while ($proposal = mysqli_fetch_assoc($proposals_result)) {
    $proposals[] = $proposal;
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
    .modal-container{display:flex;align-items:center;justify-content:center;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:50}
    .modal-content{background:#fff;border-radius:.5rem;box-shadow:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04);width:100%;max-width:42rem;max-height:90vh;overflow-y:auto}
    .milestone-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center}
    .proposal-status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-pending { background-color: #fef3c7; color: #92400e; }
    .status-under-review { background-color: #dbeafe; color: #1e40af; }
    .status-approved { background-color: #d1fae5; color: #065f46; }
    .status-rejected { background-color: #fee2e2; color: #b91c1c; }
    .status-revision-requested { background-color: #fef3c7; color: #92400e; }
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
<body class="bg-gray-50 text-gray-800 font-sans h-screen overflow-hidden">

  <div class="min-h-screen flex">
    <!-- Sidebar/header -->
    <?php include('../includes/staff-sidebar.php'); ?>
    
    <div class="flex-1 overflow-y-auto p-6">
      <!-- Success message -->
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
          <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success_message']) ?></span>
          <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <!-- Countdown Header -->
      <div id="countdown-banner" class="bg-gradient-to-r from-primary to-secondary text-white p-4 rounded-lg shadow-lg mb-6">
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
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center">
            <div class="bg-primary/10 p-3 rounded-full mr-4">
              <i class="fas fa-calendar-alt text-primary text-xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Submission Timeline Management</h2>
          </div>
          <button type="button"
            onclick="toggleModal('createTimelineModal')"
            class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg flex items-center transition">
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
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center">
      <div class="bg-primary/10 p-3 rounded-full mr-4">
        <i class="fas fa-file-alt text-primary text-xl"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Proposal Review</h2>
    </div>
    <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
      <?php echo count($proposals); ?> Submitted
    </span>
  </div>

  <?php if (!empty($proposals)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($proposals as $proposal): 
        // Default status
        $status_class = 'bg-yellow-100 text-yellow-800';
        $status_text = 'Pending';
        
        if (!empty($proposal['status'])) {
          switch ($proposal['status']) {
            case 'Approved':
              $status_class = 'bg-green-100 text-green-800';
              $status_text = 'Approved';
              break;
            case 'Rejected':
              $status_class = 'bg-red-100 text-red-800';
              $status_text = 'Rejected';
              break;
            case 'Under Review':
              $status_class = 'bg-blue-100 text-blue-800';
              $status_text = 'Under Review';
              break;
            case 'Revision Requested':
              $status_class = 'bg-purple-100 text-purple-800';
              $status_text = 'Revision Requested';
              break;
            default:
              $status_class = 'bg-yellow-100 text-yellow-800';
              $status_text = 'Pending';
          }
        }
      ?>
      
      <div class="bg-white border border-gray-200 shadow-md rounded-xl p-6 flex flex-col justify-between">
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
            <span class="font-semibold">Submitted By:</span> <?php echo htmlspecialchars($proposal['submitted_by']); ?>
          </p>
          <p class="text-sm text-gray-500 mb-4">
            <span class="font-semibold">Date:</span> <?php echo date('M j, Y', strtotime($proposal['submitted_at'])); ?>
          </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
          <button onclick='openProposalReviewModal(<?php echo htmlspecialchars(json_encode($proposal), ENT_QUOTES, "UTF-8"); ?>)'
            class="inline-flex items-center px-3 py-2 bg-primary text-white text-sm font-medium rounded-lg shadow hover:bg-secondary transition">
            <i class="fas fa-eye mr-2"></i> Review
          </button>
          
          <a href="<?php echo htmlspecialchars($proposal['file_path']); ?>" 
             target="_blank" 
             class="inline-flex items-center text-gray-600 hover:text-gray-900 text-sm font-medium">
            <i class="fas fa-download mr-2"></i> Download
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
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
            <input type="hidden" name="update_proposal_status" value="1">
            
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
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="pending">Pending</option>
                <option value="under_review">Under Review</option>
                <option value="approved">Approved</option>
                <option value="revision_requested">Revision Requested</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Feedback</label>
              <textarea name="feedback" rows="4" placeholder="Provide feedback for the group..." class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" onclick="toggleModal('proposalReviewModal')" class="px-4 py-2 border border-gray-300 rounded-md">
                Cancel
              </button>
              <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-md">
                Update Status
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
      
      // Set current status - handle both cases
      let statusText = 'Pending';
      let statusValue = 'pending';
      
      if (proposal.status) {
        switch (proposal.status.toLowerCase()) {
          case 'approved':
            statusText = 'Approved';
            statusValue = 'approved';
            break;
          case 'rejected':
            statusText = 'Rejected';
            statusValue = 'rejected';
            break;
          case 'under review':
            statusText = 'Under Review';
            statusValue = 'under_review';
            break;
          case 'revision requested':
            statusText = 'Revision Requested';
            statusValue = 'revision_requested';
            break;
          default:
            statusText = 'Pending';
            statusValue = 'pending';
        }
      }
      
      document.getElementById('review_current_status').textContent = statusText;
      
      // Set the status dropdown to current value
      const statusSelect = document.querySelector('select[name="status"]');
      if (statusSelect) {
        statusSelect.value = statusValue;
      }
      
      // Set existing feedback
      const feedbackTextarea = document.querySelector('textarea[name="feedback"]');
      if (feedbackTextarea && proposal.feedback) {
        feedbackTextarea.value = proposal.feedback;
      }
      
      toggleModal('proposalReviewModal');
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