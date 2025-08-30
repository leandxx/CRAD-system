<?php
function createNotification($conn, $user_id, $title, $message, $type = 'info') {
    $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    return $stmt->execute();
}

function notifyUser($conn, $user_id, $title, $message, $type = 'info') {
    return createNotification($conn, $user_id, $title, $message, $type);
}

function notifyAllAdmins($conn, $title, $message, $type = 'info') {
    $sql = "SELECT user_id FROM user_tbl WHERE role = 'admin'";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        createNotification($conn, $row['user_id'], $title, $message, $type);
    }
}

function notifyAllStudents($conn, $title, $message, $type = 'info') {
    $sql = "SELECT user_id FROM user_tbl WHERE role = 'student'";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        createNotification($conn, $row['user_id'], $title, $message, $type);
    }
}

function notifyAllStaff($conn, $title, $message, $type = 'info') {
    $sql = "SELECT user_id FROM user_tbl WHERE role = 'staff'";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        createNotification($conn, $row['user_id'], $title, $message, $type);
    }
}

function notifyAllUsers($conn, $title, $message, $type = 'info') {
    $sql = "SELECT user_id FROM user_tbl WHERE role IN ('student', 'admin', 'staff')";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        createNotification($conn, $row['user_id'], $title, $message, $type);
    }
}
?>