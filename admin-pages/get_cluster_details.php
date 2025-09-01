<?php
session_start();
include('../includes/connection.php');

header('Content-Type: application/json');

if (!isset($_GET['cluster_id'])) {
    echo json_encode(['success' => false, 'error' => 'Cluster ID not provided']);
    exit;
}

$cluster_id = (int) $_GET['cluster_id'];

// Get cluster details with adviser information
$cluster_query = "SELECT c.*, f.fullname AS adviser_name, f.department, f.expertise
                  FROM clusters c
                  LEFT JOIN faculty f ON c.faculty_id = f.id
                  WHERE c.id = $cluster_id";
$cluster_result = mysqli_query($conn, $cluster_query);

if (!$cluster_result || mysqli_num_rows($cluster_result) == 0) {
    echo json_encode(['success' => false, 'error' => 'Cluster not found']);
    exit;
}

$cluster = mysqli_fetch_assoc($cluster_result);

// Get students in this cluster
$cluster_name = $cluster['cluster'];
$students_query = "SELECT sp.id, sp.school_id, sp.full_name, sp.program, g.name as group_name
                   FROM student_profiles sp
                   LEFT JOIN group_members gm ON sp.user_id = gm.student_id
                   LEFT JOIN groups g ON gm.group_id = g.id
                   WHERE sp.cluster = '$cluster_name'
                   ORDER BY sp.full_name ASC";
$students_result = mysqli_query($conn, $students_query);

$students = [];
while ($student = mysqli_fetch_assoc($students_result)) {
    $students[] = $student;
}

echo json_encode([
    'success' => true,
    'cluster' => $cluster,
    'students' => $students
]);
?>