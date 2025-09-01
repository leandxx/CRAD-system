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

// Get students in this cluster (program-specific)
$cluster_name = $cluster['cluster'];
$cluster_program = $cluster['program'];
$students_query = "SELECT sp.id, sp.school_id, sp.full_name, sp.program
                   FROM student_profiles sp
                   WHERE sp.cluster = '$cluster_name' AND sp.program = '$cluster_program'
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