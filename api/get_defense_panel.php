<?php
session_start();
include('../includes/connection.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['defense_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Defense ID is required']);
    exit();
}

$defense_id = mysqli_real_escape_string($conn, $_GET['defense_id']);

// Get panel members for the defense
$query = "SELECT dp.faculty_id, dp.role, pm.first_name, pm.last_name 
          FROM defense_panel dp 
          LEFT JOIN panel_members pm ON dp.faculty_id = pm.id
          WHERE dp.defense_id = '$defense_id'";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit();
}

$panel_members = [];
while ($row = mysqli_fetch_assoc($result)) {
    $panel_members[] = $row;
}

header('Content-Type: application/json');
echo json_encode($panel_members);