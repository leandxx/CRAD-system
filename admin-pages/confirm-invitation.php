<?php
session_start();
include('../includes/connection.php');

if (isset($_GET['token']) && isset($_GET['status'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    
    // Check if token exists and is valid
    $query = "SELECT pi.*, pm.first_name, pm.last_name, pm.email
             FROM panel_invitations pi
             JOIN panel_members pm ON pi.panel_id = pm.id
             WHERE pi.token = '$token' 
             AND pi.status = 'pending'";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $invitation = mysqli_fetch_assoc($result);
        
        // Update invitation status
        $update_query = "UPDATE panel_invitations 
                        SET status = '$status', responded_at = NOW() 
                        WHERE token = '$token'";
        
        if (mysqli_query($conn, $update_query)) {
            if ($status == 'accepted') {
                /**
                 * NOTE:
                 * Since defense_id is not stored in panel_invitations,
                 * you cannot directly insert into defense_panel here
                 * unless you later modify invitations to include defense_id.
                 */
                $_SESSION['confirmation_message'] = "Thank you for accepting the panel invitation!";
            } else {
                $_SESSION['confirmation_message'] = "You have declined the panel invitation.";
            }
        }
    } else {
        $_SESSION['confirmation_message'] = "Invalid or expired invitation link.";
    }
} else {
    $_SESSION['confirmation_message'] = "Invalid confirmation request.";
}

header("Location: ../admin-pages/confirmation-result.php");
exit();
