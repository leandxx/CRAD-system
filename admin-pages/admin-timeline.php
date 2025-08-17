<?php
include("../includes/connection.php");


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_timeline'])) {
        // Create new timeline
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("INSERT INTO submission_timelines (title, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);
        $stmt->execute();
        $timeline_id = $stmt->insert_id;
        $stmt->close();
        
        // Add milestones
        foreach ($_POST['milestone_title'] as $key => $title) {
            $description = $_POST['milestone_description'][$key];
            $deadline = $_POST['milestone_deadline'][$key];
            
            $stmt = $conn->prepare("INSERT INTO timeline_milestones (timeline_id, title, description, deadline) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $timeline_id, $title, $description, $deadline);
            $stmt->execute();
            $stmt->close();
        }
        
        $_SESSION['success_message'] = "Timeline created successfully!";
    } elseif (isset($_POST['update_timeline'])) {
        // Update timeline
        $timeline_id = $_POST['timeline_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("UPDATE submission_timelines SET title = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $description, $timeline_id);
        $stmt->execute();
        $stmt->close();
        
        // Update milestones
        foreach ($_POST['milestone_id'] as $key => $milestone_id) {
            $title = $_POST['milestone_title'][$key];
            $description = $_POST['milestone_description'][$key];
            $deadline = $_POST['milestone_deadline'][$key];
            
            if ($milestone_id == 'new') {
                $stmt = $conn->prepare("INSERT INTO timeline_milestones (timeline_id, title, description, deadline) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $timeline_id, $title, $description, $deadline);
            } else {
                $stmt = $conn->prepare("UPDATE timeline_milestones SET title = ?, description = ?, deadline = ? WHERE id = ?");
                $stmt->bind_param("sssi", $title, $description, $deadline, $milestone_id);
            }
            $stmt->execute();
            $stmt->close();
        }
        
        // Handle deleted milestones
        if (isset($_POST['deleted_milestones'])) {
            foreach ($_POST['deleted_milestones'] as $milestone_id) {
                if ($milestone_id != 'new') {
                    $stmt = $conn->prepare("DELETE FROM timeline_milestones WHERE id = ?");
                    $stmt->bind_param("i", $milestone_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        
        $_SESSION['success_message'] = "Timeline updated successfully!";
    } elseif (isset($_POST['toggle_timeline'])) {
        // Toggle timeline active status
        $timeline_id = $_POST['timeline_id'];
        $stmt = $conn->prepare("UPDATE submission_timelines SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $timeline_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success_message'] = "Timeline status updated!";
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
    
    // Get milestones for active timeline
    $stmt = $conn->prepare("SELECT * FROM timeline_milestones WHERE timeline_id = ? ORDER BY deadline ASC");
    $stmt->bind_param("i", $active_timeline['id']);
    $stmt->execute();
    $milestones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Submission</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Ensure modal is properly centered */
    .modal-container {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 50;
    }
    
    .modal-content {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        width: 100%;
        max-width: 42rem;
        max-height: 90vh;
        overflow-y: auto;
    }
</style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            const modal = document.getElementById('submissionModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    </script>
</head>
  <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include('../includes/admin-sidebar.php'); ?>

    <div class="flex-1 overflow-y-auto p-6">
        <!-- Success message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success_message'] ?></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Countdown Header -->
        <div class="bg-gradient-to-r from-primary to-secondary text-white p-4 rounded-lg shadow-lg mb-6">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center mb-4 md:mb-0">
                    <i class="fas fa-clock text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-bold text-lg">Current Phase Countdown</h3>
                        <?php if (!empty($milestones)): ?>
                            <?php 
                            $current_milestone = null;
                            $now = new DateTime();
                            foreach ($milestones as $milestone) {
                                $deadline = new DateTime($milestone['deadline']);
                                if ($deadline > $now) {
                                    $current_milestone = $milestone;
                                    break;
                                }
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
                <button 
                    onclick="toggleModal('createTimelineModal')"
                    class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center transition">
                    <i class="fas fa-plus mr-2"></i> Create New Timeline
                </button>
            </div>

            <?php if ($active_timeline): ?>
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold"><?= htmlspecialchars($active_timeline['title']) ?></h3>
                        <div class="flex space-x-2">
                            <button 
                                onclick="openEditModal(<?= htmlspecialchars(json_encode($active_timeline), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($milestones), ENT_QUOTES, 'UTF-8') ?>)"
                                class="text-primary hover:text-secondary transition"
                            >
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="timeline_id" value="<?= $active_timeline['id'] ?>">
                                <button 
                                    type="submit" 
                                    name="toggle_timeline"
                                    class="text-gray-500 hover:text-gray-700 transition"
                                >
                                    <i class="fas fa-toggle-on mr-1"></i> Disable
                                </button>
                            </form>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6"><?= htmlspecialchars($active_timeline['description']) ?></p>

                    <!-- Progress Timeline -->
                    <div class="relative">
                        <div class="h-2 bg-gray-200 rounded-full mb-8">
                            <?php 
                            $total_milestones = count($milestones);
                            $completed_milestones = 0;
                            $now = new DateTime();
                            foreach ($milestones as $milestone) {
                                $deadline = new DateTime($milestone['deadline']);
                                if ($deadline < $now) {
                                    $completed_milestones++;
                                }
                            }
                            $progress = $total_milestones > 0 ? ($completed_milestones / $total_milestones) * 100 : 0;
                            ?>
                            <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full" style="width: <?= $progress ?>%"></div>
                        </div>
                        
                        <div class="flex justify-between relative">
                            <?php foreach ($milestones as $index => $milestone): ?>
                                <?php
                                $deadline = new DateTime($milestone['deadline']);
                                $is_past = $deadline < $now;
                                $is_current = !$is_past && ($index === 0 || new DateTime($milestones[$index-1]['deadline']) < $now);
                                ?>
                                <div class="text-center w-1/<?= count($milestones) ?> px-2">
                                    <div class="milestone-dot <?= $is_past ? 'bg-success' : ($is_current ? 'bg-warning' : 'bg-gray-200') ?> text-white mx-auto mb-2">
                                        <i class="fas <?= $is_past ? 'fa-check' : ($is_current ? 'fa-exclamation' : 'fa-flag') ?>"></i>
                                    </div>
                                    <div class="text-sm font-medium"><?= htmlspecialchars($milestone['title']) ?></div>
                                    <div class="text-xs text-gray-500"><?= date('M j, Y', strtotime($milestone['deadline'])) ?></div>
                                    <?php if ($is_current): ?>
                                        <?php
                                        $diff = $now->diff($deadline);
                                        $days_left = $diff->days;
                                        ?>
                                        <div class="text-xs mt-1 font-medium text-warning"><?= $days_left ?> days left</div>
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
    </div>

    <!-- Create Timeline Modal -->
   <div id="createTimelineModal" class="modal-container hidden">
    <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Create New Timeline</h3>
                    <button onclick="toggleModal('createTimelineModal')" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST">
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Timeline Title</label>
                        <input type="text" id="title" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
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
                            <button type="submit" name="create_timeline" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-md">
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
                <button onclick="toggleModal('editTimelineModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" id="edit_timeline_id" name="timeline_id">
                <input type="hidden" id="deleted_milestones" name="deleted_milestones" value="">
                <div class="mb-4">
                    <label for="edit_title" class="block text-sm font-medium text-gray-700 mb-1">Timeline Title</label>
                    <input type="text" id="edit_title" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div class="mb-4">
                    <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                </div>
                
                <h4 class="text-lg font-medium mb-3">Milestones</h4>
                <div id="editMilestoneContainer">
                    <!-- Milestones will be added here by JavaScript -->
                </div>
                
                <div class="flex justify-between mt-4">
                    <button type="button" id="addEditMilestone" class="text-primary hover:text-secondary">
                        <i class="fas fa-plus mr-1"></i> Add Milestone
                    </button>
                    <div>
                        <button type="button" onclick="toggleModal('editTimelineModal')" class="mr-2 px-4 py-2 border border-gray-300 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" name="update_timeline" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-md">
                            Update Timeline
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

    <script>
        
// Improved openEditModal function
function openEditModal(timeline, milestones) {
    // Set the timeline information
    document.getElementById('edit_timeline_id').value = timeline.id;
    document.getElementById('edit_title').value = timeline.title;
    document.getElementById('edit_description').value = timeline.description || '';
    document.getElementById('deleted_milestones').value = '';
    
    const container = document.getElementById('editMilestoneContainer');
    container.innerHTML = '';
    
    // Add existing milestones
    milestones.forEach((milestone, index) => {
        const milestoneElement = createMilestoneElement(milestone, index + 1);
        container.appendChild(milestoneElement);
        initializeDatePicker(milestoneElement);
    });
    
    // Show the modal
    toggleModal('editTimelineModal');
}

// Helper function to create milestone element
function createMilestoneElement(milestone, index) {
    const element = document.createElement('div');
    element.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
    element.dataset.milestoneId = milestone.id;
    
    element.innerHTML = `
        <input type="hidden" name="milestone_id[]" value="${milestone.id}">
        <div class="flex justify-between items-center mb-2">
            <h5 class="font-medium">Milestone #${index}</h5>
            <button type="button" class="text-red-500 hover:text-red-700 remove-milestone">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="milestone_title[]" value="${milestone.title}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                <input type="datetime-local" name="milestone_deadline[]" value="${milestone.deadline.replace(' ', 'T')}" class="w-full px-3 py-2 border border-gray-300 rounded-md flatpickr" required>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="milestone_description[]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md">${milestone.description || ''}</textarea>
        </div>
    `;
    
    return element;
}

// Initialize date picker for a milestone element
function initializeDatePicker(element) {
    flatpickr(element.querySelector(".flatpickr"), {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today"
    });
}

// Add event listener for remove milestone buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-milestone')) {
        const milestoneItem = e.target.closest('.milestone-item');
        const container = milestoneItem.parentElement;
        const milestoneId = milestoneItem.dataset.milestoneId;
        
        if (milestoneId && milestoneId !== 'new') {
            const deletedInput = document.getElementById('deleted_milestones');
            const currentDeleted = deletedInput.value ? JSON.parse(deletedInput.value) : [];
            currentDeleted.push(milestoneId);
            deletedInput.value = JSON.stringify(currentDeleted);
        }
        
        container.removeChild(milestoneItem);
        
        // Renumber remaining milestones
        const remainingMilestones = container.querySelectorAll('.milestone-item');
        remainingMilestones.forEach((ms, idx) => {
            ms.querySelector('h5').textContent = `Milestone #${idx + 1}`;
        });
    }
});

// Add new milestone in edit modal
document.getElementById('addEditMilestone').addEventListener('click', function() {
    const container = document.getElementById('editMilestoneContainer');
    const count = container.children.length + 1;
    
    const newMilestone = document.createElement('div');
    newMilestone.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
    newMilestone.dataset.milestoneId = 'new';
    newMilestone.innerHTML = `
        <input type="hidden" name="milestone_id[]" value="new">
        <div class="flex justify-between items-center mb-2">
            <h5 class="font-medium">New Milestone</h5>
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
    `;
    
    container.appendChild(newMilestone);
    initializeDatePicker(newMilestone);
});

        // Admin countdown timer - improved version
    function updateAdminCountdown() {
        <?php if (!empty($milestones)): ?>
            <?php 
            $now = new DateTime();
            $current_milestone = null;
            foreach ($milestones as $milestone) {
                $deadline = new DateTime($milestone['deadline']);
                if ($deadline > $now) {
                    $current_milestone = $milestone;
                    break;
                }
            }
            ?>
            
            <?php if ($current_milestone): ?>
                const deadline = new Date("<?= $current_milestone['deadline'] ?>").getTime();
                const now = new Date().getTime();
                const distance = deadline - now;
                
                // Time calculations
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // Display the result
                document.getElementById("admin-countdown-days").textContent = days.toString().padStart(2, '0');
                document.getElementById("admin-countdown-hours").textContent = hours.toString().padStart(2, '0');
                document.getElementById("admin-countdown-minutes").textContent = minutes.toString().padStart(2, '0');
                
                // Change color if less than 24 hours remaining
                const countdownContainer = document.querySelector('.bg-gradient-to-r');
                if (distance < (24 * 60 * 60 * 1000)) {
                    countdownContainer.classList.remove('from-primary', 'to-secondary');
                    countdownContainer.classList.add('from-red-500', 'to-red-600');
                } else {
                    countdownContainer.classList.remove('from-red-500', 'to-red-600');
                    countdownContainer.classList.add('from-primary', 'to-secondary');
                }
                
                // If the countdown is finished
                if (distance < 0) {
                    countdownContainer.classList.add('from-gray-500', 'to-gray-600');
                    clearInterval(countdownInterval);
                }
            <?php else: ?>
                // All milestones completed
                document.getElementById("admin-countdown-days").textContent = '00';
                document.getElementById("admin-countdown-hours").textContent = '00';
                document.getElementById("admin-countdown-minutes").textContent = '00';
                document.querySelector('.bg-gradient-to-r').classList.add('from-gray-500', 'to-gray-600');
            <?php endif; ?>
        <?php endif; ?>
    }
    // Initialize countdown
    let countdownInterval;
    document.addEventListener('DOMContentLoaded', function() {
        // Start countdown and update every second
        updateAdminCountdown();
        countdownInterval = setInterval(updateAdminCountdown, 1000);
        
        // Your existing DOMContentLoaded code...
        flatpickr(".flatpickr", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today"
        });
        
        // Rest of your initialization code...
    });
        // Initialize date pickers
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".flatpickr", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today"
            });
            
            // Add milestone button
            document.getElementById('addMilestone').addEventListener('click', function() {
                const container = document.getElementById('milestoneContainer');
                const count = container.children.length + 1;
                
                const newMilestone = document.createElement('div');
                newMilestone.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
                newMilestone.innerHTML = `
                    <div class="flex justify-between items-center mb-2">
                        <h5 class="font-medium">Milestone #${count}</h5>
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
                `;
                
                container.appendChild(newMilestone);
                flatpickr(newMilestone.querySelector(".flatpickr"), {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today"
                });
                
                // Add event to remove button
                newMilestone.querySelector('.remove-milestone').addEventListener('click', function() {
                    container.removeChild(newMilestone);
                    // Renumber remaining milestones
                    const milestones = container.querySelectorAll('.milestone-item');
                    milestones.forEach((milestone, index) => {
                        milestone.querySelector('h5').textContent = `Milestone #${index + 1}`;
                    });
                });
            });
            
            // Add edit milestone button
            document.getElementById('addEditMilestone').addEventListener('click', function() {
                const container = document.getElementById('editMilestoneContainer');
                const count = container.children.length + 1;
                
                const newMilestone = document.createElement('div');
                newMilestone.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
                newMilestone.innerHTML = `
                    <input type="hidden" name="milestone_id[]" value="new">
                    <div class="flex justify-between items-center mb-2">
                        <h5 class="font-medium">New Milestone</h5>
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
                `;
                
                container.appendChild(newMilestone);
                flatpickr(newMilestone.querySelector(".flatpickr"), {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today"
                });
                
                // Add event to remove button
                newMilestone.querySelector('.remove-milestone').addEventListener('click', function() {
                    container.removeChild(newMilestone);
                });
            });
            
            // Countdown timer
            updateAdminCountdown();
            setInterval(updateAdminCountdown, 60000);
        });
        
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
        
        function openEditModal(timeline, milestones) {
            document.getElementById('edit_timeline_id').value = timeline.id;
            document.getElementById('edit_title').value = timeline.title;
            document.getElementById('edit_description').value = timeline.description || '';
            
            const container = document.getElementById('editMilestoneContainer');
            container.innerHTML = '';
            
            milestones.forEach((milestone, index) => {
                const milestoneElement = document.createElement('div');
                milestoneElement.className = 'milestone-item mb-4 p-4 border border-gray-200 rounded-lg';
                milestoneElement.innerHTML = `
                    <input type="hidden" name="milestone_id[]" value="${milestone.id}">
                    <div class="flex justify-between items-center mb-2">
                        <h5 class="font-medium">Milestone #${index + 1}</h5>
                        <button type="button" class="text-red-500 hover:text-red-700 remove-milestone">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="milestone_title[]" value="${milestone.title}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                            <input type="datetime-local" name="milestone_deadline[]" value="${milestone.deadline.replace(' ', 'T')}" class="w-full px-3 py-2 border border-gray-300 rounded-md flatpickr" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="milestone_description[]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md">${milestone.description || ''}</textarea>
                    </div>
                `;
                
                container.appendChild(milestoneElement);
                flatpickr(milestoneElement.querySelector(".flatpickr"), {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today"
                });
                
                // Add event to remove button
                milestoneElement.querySelector('.remove-milestone').addEventListener('click', function() {
                    container.removeChild(milestoneElement);
                });
            });
            
            toggleModal('editTimelineModal');
        }
        
        // Admin countdown timer
        function updateAdminCountdown() {
            <?php if (!empty($milestones)): ?>
                <?php 
                $now = new DateTime();
                $current_milestone = null;
                foreach ($milestones as $milestone) {
                    $deadline = new DateTime($milestone['deadline']);
                    if ($deadline > $now) {
                        $current_milestone = $milestone;
                        break;
                    }
                }
                ?>
                
                <?php if ($current_milestone): ?>
                    const deadline = new Date("<?= $current_milestone['deadline'] ?>").getTime();
                    const now = new Date().getTime();
                    const distance = deadline - now;
                    
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    
                    document.getElementById("admin-countdown-days").textContent = days.toString().padStart(2, '0');
                    document.getElementById("admin-countdown-hours").textContent = hours.toString().padStart(2, '0');
                    document.getElementById("admin-countdown-minutes").textContent = minutes.toString().padStart(2, '0');
                    
                    if (distance < (24 * 60 * 60 * 1000)) {
                        document.querySelector('.bg-gradient-to-r').classList.remove('from-primary', 'to-secondary');
                        document.querySelector('.bg-gradient-to-r').classList.add('from-red-500', 'to-red-600');
                    }
                    
                    if (distance < 0) {
                        document.querySelector('.bg-gradient-to-r').classList.add('from-gray-500', 'to-gray-600');
                    }
                <?php else: ?>
                    document.getElementById("admin-countdown-days").textContent = '00';
                    document.getElementById("admin-countdown-hours").textContent = '00';
                    document.getElementById("admin-countdown-minutes").textContent = '00';
                <?php endif; ?>
            <?php endif; ?>
        }
    </script>
    
</body>
</html>