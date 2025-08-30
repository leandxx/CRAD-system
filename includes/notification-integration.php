<?php
// Example integration file showing how to trigger notifications from other processes
include("connection.php");
include("notification-helper.php");

// Example: When a student submits a proposal
function onProposalSubmitted($conn, $student_id, $proposal_title) {
    // Notify the student
    notifyUser($conn, $student_id, 
        "Proposal Submitted Successfully", 
        "Your research proposal '$proposal_title' has been submitted and is under review.", 
        "success"
    );
    
    // Notify all admins
    notifyAllAdmins($conn, 
        "New Proposal Submitted", 
        "A new research proposal '$proposal_title' has been submitted for review.", 
        "info"
    );
}

// Example: When defense is scheduled
function onDefenseScheduled($conn, $student_id, $defense_date, $defense_time) {
    notifyUser($conn, $student_id, 
        "Defense Scheduled", 
        "Your thesis defense has been scheduled for $defense_date at $defense_time.", 
        "success"
    );
}

// Example: When proposal is approved/rejected
function onProposalReviewed($conn, $student_id, $proposal_title, $status, $feedback = '') {
    $type = $status === 'approved' ? 'success' : 'warning';
    $message = $status === 'approved' 
        ? "Your research proposal '$proposal_title' has been approved!" 
        : "Your research proposal '$proposal_title' needs revision. $feedback";
    
    notifyUser($conn, $student_id, 
        "Proposal " . ucfirst($status), 
        $message, 
        $type
    );
}

// Example: When panel is assigned
function onPanelAssigned($conn, $student_id, $panel_members) {
    $members_list = implode(', ', $panel_members);
    notifyUser($conn, $student_id, 
        "Panel Assigned", 
        "Your thesis panel has been assigned: $members_list", 
        "info"
    );
}

// Example: When adviser is assigned
function onAdviserAssigned($conn, $student_id, $adviser_name) {
    notifyUser($conn, $student_id, 
        "Adviser Assigned", 
        "Prof. $adviser_name has been assigned as your thesis adviser.", 
        "success"
    );
}

// Example usage (uncomment to test):
/*
// Test notification creation
if (isset($_GET['test'])) {
    $test_user_id = 1; // Replace with actual user ID
    
    switch ($_GET['test']) {
        case 'proposal':
            onProposalSubmitted($conn, $test_user_id, "AI-Based Learning System");
            break;
        case 'defense':
            onDefenseScheduled($conn, $test_user_id, "2025-09-15", "2:00 PM");
            break;
        case 'approved':
            onProposalReviewed($conn, $test_user_id, "AI-Based Learning System", "approved");
            break;
        case 'rejected':
            onProposalReviewed($conn, $test_user_id, "AI-Based Learning System", "rejected", "Please revise the methodology section.");
            break;
    }
    
    echo "Test notification created!";
}
*/
?>