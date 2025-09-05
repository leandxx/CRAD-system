<?php
session_start();
include('../includes/connection.php'); // Your DB connection
include('../includes/notification-helper.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bulk create clusters
    if (isset($_POST['bulk_create'])) {
        // Auto-generate school year based on current date
        $current_year = date('Y');
        $current_month = date('n');
        // If it's January to June, use previous year as start year
        // If it's July to December, use current year as start year
        $start_year = ($current_month <= 6) ? $current_year - 1 : $current_year;
        $school_year = $start_year . '-' . ($start_year + 1);
        
        $capacity = (int) $_POST['capacity'];
        $programs = ['BS Information Technology', 'BS Hospitality Management', 'BS Office Administration', 'BS Business Administration', 'BS Criminology', 'Bachelor of Elementary Education', 'Bachelor of Secondary Education', 'BS Computer Engineering', 'BS Tourism Management', 'BS Entrepreneurship', 'BS Accounting Information System', 'BS Psychology', 'BL Information Science'];
        
        foreach ($programs as $program) {
            for ($i = 41001; $i <= 41010; $i++) {
                $check = mysqli_query($conn, "SELECT id FROM clusters WHERE program = '$program' AND cluster = '$i'");
                if (mysqli_num_rows($check) == 0) {
                    $sql = "INSERT INTO clusters (program, cluster, school_year, capacity) 
                            VALUES ('$program', '$i', '$school_year', $capacity)";
                    mysqli_query($conn, $sql);
                }
            }
        }
    }
    
    // Create cluster
    if (isset($_POST['create_cluster'])) {
        $program     = mysqli_real_escape_string($conn, $_POST['program']);
        $cluster     = mysqli_real_escape_string($conn, $_POST['cluster']);
        $school_year = mysqli_real_escape_string($conn, $_POST['school_year']);
        $capacity    = (int) $_POST['capacity'];

        $sql = "INSERT INTO clusters (program, cluster, school_year, capacity) 
                VALUES ('$program', '$cluster', '$school_year', $capacity)";
        mysqli_query($conn, $sql);
    }
    
    // Update cluster
    if (isset($_POST['update_cluster'])) {
        $cluster_id  = (int) $_POST['cluster_id'];
        $program     = mysqli_real_escape_string($conn, $_POST['program']);
        $cluster     = mysqli_real_escape_string($conn, $_POST['cluster']);
        $school_year = mysqli_real_escape_string($conn, $_POST['school_year']);
        $capacity    = (int) $_POST['capacity'];

        $sql = "UPDATE clusters 
                SET program = '$program', cluster = '$cluster', 
                    school_year = '$school_year', capacity = $capacity
                WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
    }
    
    // Delete cluster
    if (isset($_POST['delete_cluster'])) {
        $cluster_id = (int) $_POST['cluster_id'];
        
        // Get cluster name and program first
        $cluster_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cluster, program FROM clusters WHERE id = $cluster_id"));
        $cluster_name = $cluster_info['cluster'] ?? null;
        $cluster_program = $cluster_info['program'] ?? null;
        
        if ($cluster_name && $cluster_program) {
            // First, unassign all students from this cluster (program-specific)
            $sql = "UPDATE student_profiles SET cluster = 'Not Assigned', faculty_id = NULL WHERE cluster = '$cluster_name' AND program = '$cluster_program'";
            mysqli_query($conn, $sql);
        }
        
        // Unassign all groups from this cluster
        $sql = "UPDATE groups SET cluster_id = NULL WHERE cluster_id = $cluster_id";
        mysqli_query($conn, $sql);
        
        // Then delete the cluster
        $sql = "DELETE FROM clusters WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
    }

    // Assign adviser
    if (isset($_POST['assign_adviser'])) {
        $cluster_id = (int) $_POST['cluster_id'];
        $faculty_id = (int) $_POST['faculty_id'];

        // Get cluster name and program
        $cluster_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cluster, program FROM clusters WHERE id = $cluster_id"));
        $cluster_name = $cluster_info['cluster'] ?? null;
        $cluster_program = $cluster_info['program'] ?? null;
        
        // Get adviser name
        $adviser_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname FROM faculty WHERE id = $faculty_id"));
        $adviser_name = $adviser_info['fullname'] ?? 'Unknown';

        // Update cluster with adviser
        $sql = "UPDATE clusters 
                SET faculty_id = $faculty_id, assigned_date = NOW(), status = 'assigned' 
                WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
        
        // Update all students in this cluster with the new faculty adviser (program-specific)
        if ($cluster_name && $cluster_program) {
            $sql = "UPDATE student_profiles SET faculty_id = $faculty_id WHERE cluster = '$cluster_name' AND program = '$cluster_program'";
            mysqli_query($conn, $sql);
            
            // Include notification helper and send notifications
            include('../includes/notification-helper.php');
            
            // Get all students in this cluster (program-specific)
            $students_query = "SELECT user_id FROM student_profiles WHERE cluster = '$cluster_name' AND program = '$cluster_program'";
            $students_result = mysqli_query($conn, $students_query);
            
            // Notify all students in the cluster
            while ($student = mysqli_fetch_assoc($students_result)) {
                notifyUser($conn, $student['user_id'], 
                    "Adviser Assigned", 
                    "Prof. $adviser_name has been assigned as your thesis adviser for $cluster_program - Cluster $cluster_name.", 
                    "success"
                );
            }
        }
    }
    
    // Remove adviser assignment
    if (isset($_POST['remove_adviser'])) {
        $cluster_id = (int) $_POST['cluster_id'];

        // Get cluster name and program
        $cluster_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cluster, program FROM clusters WHERE id = $cluster_id"));
        $cluster_name = $cluster_info['cluster'] ?? null;
        $cluster_program = $cluster_info['program'] ?? null;

        // Update cluster to remove adviser
        $sql = "UPDATE clusters 
                SET faculty_id = NULL, assigned_date = NULL, status = 'unassigned' 
                WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
        
        // Remove faculty assignment from all students in this cluster (program-specific)
        if ($cluster_name && $cluster_program) {
            $sql = "UPDATE student_profiles SET faculty_id = NULL WHERE cluster = '$cluster_name' AND program = '$cluster_program'";
            mysqli_query($conn, $sql);
        }
    }

    // Assign group to cluster
if (isset($_POST['assign_group'])) {
    $group_id = (int) $_POST['group_id'];
    $cluster_id = (int) $_POST['cluster_id'];

    // Get cluster's faculty_id, cluster name, and program
    $cluster_info = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT faculty_id, cluster, program FROM clusters WHERE id = $cluster_id"));
    $faculty_id = $cluster_info['faculty_id'] ?? null;
    $cluster_name = $cluster_info['cluster'] ?? null;
    $cluster_program = $cluster_info['program'] ?? null;
    
    // Update group cluster assignment
    $sql = "UPDATE groups SET cluster_id = $cluster_id WHERE id = $group_id";
    mysqli_query($conn, $sql);
    
    // Update all group members using the correct JOIN (program-specific)
    if ($faculty_id) {
        $bulk_update = "UPDATE student_profiles sp 
                       INNER JOIN group_members gm ON sp.user_id = gm.student_id 
                       SET sp.cluster = '$cluster_name', sp.faculty_id = $faculty_id 
                       WHERE gm.group_id = $group_id AND sp.program = '$cluster_program'";
    } else {
        $bulk_update = "UPDATE student_profiles sp 
                       INNER JOIN group_members gm ON sp.user_id = gm.student_id 
                       SET sp.cluster = '$cluster_name', sp.faculty_id = NULL 
                       WHERE gm.group_id = $group_id AND sp.program = '$cluster_program'";
    }
    
    $member_count = 0;
    if (mysqli_query($conn, $bulk_update)) {
        $member_count = mysqli_affected_rows($conn);
    }

    // Update student count in cluster
    $sql = "UPDATE clusters SET student_count = student_count + $member_count WHERE id = $cluster_id";
    mysqli_query($conn, $sql);
    
    $_SESSION['success'] = "Group assigned successfully. $member_count students assigned to cluster.";
}
    
// Remove group from cluster
if (isset($_POST['remove_group'])) {
    $group_id = (int) $_POST['group_id'];
    $cluster_id = (int) $_POST['cluster_id'];

    // Get group info and member count
    $group = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cluster_id FROM groups WHERE id = $group_id"));
    $member_count = mysqli_fetch_row(mysqli_query($conn, 
        "SELECT COUNT(*) FROM group_members WHERE group_id = $group_id"))[0];
    
    if ($group && $group['cluster_id'] == $cluster_id) {
        // Remove group from cluster
        $sql = "UPDATE groups SET cluster_id = NULL WHERE id = $group_id";
        mysqli_query($conn, $sql);
        
        // Update all group members using the correct JOIN (program-specific)
        $bulk_update = "UPDATE student_profiles sp 
                       INNER JOIN group_members gm ON sp.user_id = gm.student_id 
                       SET sp.cluster = 'Not Assigned', sp.faculty_id = NULL 
                       WHERE gm.group_id = $group_id";
        mysqli_query($conn, $bulk_update);

        // Update student count in cluster
        $sql = "UPDATE clusters SET student_count = student_count - $member_count WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
    }
}    
    // Create faculty
    if (isset($_POST['create_faculty'])) {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $department = mysqli_real_escape_string($conn, $_POST['department']);
        $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "INSERT INTO faculty (fullname, department, expertise, email) 
                VALUES ('$fullname', '$department', '$expertise', '$email')";
        mysqli_query($conn, $sql);
    }
    
    // Update faculty
    if (isset($_POST['update_faculty'])) {
        $faculty_id = (int) $_POST['faculty_id'];
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $department = mysqli_real_escape_string($conn, $_POST['department']);
        $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "UPDATE faculty 
                SET fullname = '$fullname', department = '$department', 
                    expertise = '$expertise', email = '$email'
                WHERE id = $faculty_id";
        mysqli_query($conn, $sql);
    }
    
    // Add members to group
    if (isset($_POST['add_members'])) {
        $group_id = (int) $_POST['group_id'];
        $student_ids = $_POST['student_ids']; // this should be an array of 5 student IDs

        if (is_array($student_ids) && count($student_ids) == 5) {
            // First, clear existing members
            $sql = "DELETE FROM group_members WHERE group_id = $group_id";
            mysqli_query($conn, $sql);
            
            // Then add all 5 members
            foreach ($student_ids as $sid) {
                $sid = (int)$sid;
                $sql = "INSERT INTO group_members (group_id, student_id) VALUES ($group_id, $sid)";
                mysqli_query($conn, $sql);
            }
            $_SESSION['success'] = "5 members successfully added to the group.";
        } else {
            $_SESSION['error'] = "You must select exactly 5 students.";
        }
    }
    

    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Fetch data
$clusters = mysqli_query(
    $conn,
    "SELECT c.*, f.fullname AS adviser_name 
     FROM clusters c 
     LEFT JOIN faculty f ON c.faculty_id = f.id 
     ORDER BY c.program, c.cluster"
);

$faculty = mysqli_query(
    $conn,
    "SELECT * FROM faculty ORDER BY department, fullname"
);

$unassigned_groups = mysqli_query(
    $conn,
    "SELECT g.*, COUNT(gm.student_id) as member_count
     FROM groups g
     LEFT JOIN group_members gm ON g.id = gm.group_id
     WHERE g.cluster_id IS NULL
     GROUP BY g.id
     ORDER BY g.program, g.name"
);

// Handle cluster details view
$cluster_details = null;
$cluster_students = [];
if (isset($_GET['view_cluster'])) {
    $cluster_id = (int) $_GET['view_cluster'];
    $cluster_details = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT c.*, f.fullname AS adviser_name, f.department, f.expertise
         FROM clusters c
         LEFT JOIN faculty f ON c.faculty_id = f.id
         WHERE c.id = $cluster_id"));
    
    if ($cluster_details) {
        $cluster_name = $cluster_details['cluster'];
        $cluster_program = $cluster_details['program'];
        $result = mysqli_query($conn,
            "SELECT sp.id, sp.school_id, sp.full_name, sp.program
             FROM student_profiles sp
             WHERE sp.cluster = '$cluster_name' AND sp.program = '$cluster_program'
             ORDER BY sp.full_name ASC");
        $cluster_students = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

// Get statistics
$total_clusters     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters"))[0];
$assigned_clusters  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters WHERE status = 'assigned'"))[0];
$total_students     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles"))[0];
$assigned_students  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles WHERE faculty_id IS NOT NULL AND faculty_id != 0"))[0];
$total_faculty      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM faculty"))[0];
$assigned_faculty   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT faculty_id) FROM clusters WHERE faculty_id IS NOT NULL"))[0];
$total_groups       = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM groups"))[0];
$assigned_groups    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM groups WHERE cluster_id IS NOT NULL"))[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cluster Adviser Assignment | Admin Dashboard</title>
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4A6CF7',
                        secondary: '#6C757D',
                        success: '#28a745',
                        info: '#17a2b8',
                        warning: '#ffc107',
                        danger: '#dc3545',
                        light: '#f8f9fa',
                        dark: '#343a40',
                    }
                }
            }
        }
    </script>
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
        
        .nav-tabs .nav-link.active {
            position: relative;
            color: #4A6CF7;
            background: linear-gradient(135deg, #4A6CF7, #3b82f6);
            color: white;
            border-radius: 12px 12px 0 0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px -4px rgba(74, 108, 247, 0.4);
        }
        
        .nav-tabs .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4A6CF7, #3b82f6);
            border-radius: 2px;
        }
        
        .nav-tabs .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px 12px 0 0;
            margin-right: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .nav-tabs .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.4s;
        }
        
        .nav-tabs .nav-link:hover::before {
            left: 100%;
        }
        
        .nav-tabs .nav-link:not(.active):hover {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.1);
        }
        
        .cluster-card, .group-item, .faculty-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .cluster-card::before, .group-item::before, .faculty-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .cluster-card:hover::before, .group-item:hover::before, .faculty-item:hover::before {
            left: 100%;
        }
        
        .cluster-card:hover, .group-item:hover, .faculty-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.15);
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
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .gradient-red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .gradient-yellow {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .gradient-cyan {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
        }
        
        .action-button {
            transition: all 0.2s ease;
            padding: 0.5rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-button:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .modal {
            transition: opacity 0.15s linear;
        }
        
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: #000;
            opacity: 0.5;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0;
            display: none;
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-dialog {
            position: relative;
            width: auto;
            margin: 0.5rem;
            pointer-events: none;
            max-width: 500px;
            margin: 1.75rem auto;
        }
        
        .modal-lg {
            max-width: 800px;
            margin: 1rem auto;
        }
        
        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            pointer-events: auto;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
            outline: 0;
        }
        
        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 1rem 1rem;
            border-bottom: 1px solid #dee2e6;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        
        .modal-body {
            position: relative;
            flex: 1 1 auto;
            padding: 1rem;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .modal-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            padding: 0.75rem;
            border-top: 1px solid #dee2e6;
            border-bottom-right-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        
        .btn-close {
            padding: 0.5rem 0.5rem;
            margin: -0.5rem -0.5rem -0.5rem auto;
            background-color: transparent;
            border: 0;
            font-size: 1.5rem;
            opacity: 0.5;
            cursor: pointer;
        }
        
        .fade {
            transition: opacity 0.15s linear;
        }
        
        .tab-content > .tab-pane {
            display: none;
        }
        
        .tab-content > .active {
            display: block;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 font-sans min-h-screen">
    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        
        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6">
            <div class="container mx-auto">
                <!-- Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-slide-up">
    
    <!-- Clusters Overview -->
    <div class="stats-card p-4 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-blue-500/10 rounded-full -translate-y-8 translate-x-8"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="gradient-blue p-2 rounded-lg">
                <i class="fas fa-layer-group text-white text-lg"></i>
            </div>
            <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                <?= $total_clusters > 0 ? round(($assigned_clusters / $total_clusters) * 100) : 0 ?>% Assigned
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?= $assigned_clusters ?><span class="text-sm text-gray-500">/<?= $total_clusters ?></span></h3>
        <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Clusters</p>
    </div>

    <!-- Groups Overview -->
    <div class="stats-card p-4 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-cyan-500/10 rounded-full -translate-y-8 translate-x-8"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="gradient-cyan p-2 rounded-lg">
                <i class="fas fa-users text-white text-lg"></i>
            </div>
            <span class="text-xs font-medium text-cyan-600 bg-cyan-100 px-2 py-1 rounded-full">
                <?= $total_groups > 0 ? round(($assigned_groups / $total_groups) * 100) : 0 ?>% Assigned
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?= $assigned_groups ?><span class="text-sm text-gray-500">/<?= $total_groups ?></span></h3>
        <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Groups</p>
    </div>

    <!-- Faculty Overview -->
    <div class="stats-card p-4 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-purple-500/10 rounded-full -translate-y-8 translate-x-8"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="gradient-purple p-2 rounded-lg">
                <i class="fas fa-chalkboard-teacher text-white text-lg"></i>
            </div>
            <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">
                <?= $total_faculty > 0 ? round(($assigned_faculty / $total_faculty) * 100) : 0 ?>% Active
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?= $assigned_faculty ?><span class="text-sm text-gray-500">/<?= $total_faculty ?></span></h3>
        <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Faculty</p>
    </div>

    <!-- Students Overview -->
    <div class="stats-card p-4 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-green-500/10 rounded-full -translate-y-8 translate-x-8"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="gradient-green p-2 rounded-lg">
                <i class="fas fa-user-graduate text-white text-lg"></i>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
                <?= $total_students > 0 ? round(($assigned_students / $total_students) * 100) : 0 ?>% Assigned
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?= $assigned_students ?><span class="text-sm text-gray-500">/<?= $total_students ?></span></h3>
        <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Students</p>
    </div>

</div>
                
                <!-- Tabs Section -->
                <div class="stats-card rounded-2xl overflow-hidden mb-8 animate-scale-in">
                    <ul class="nav-tabs flex border-b border-gray-100 px-6" id="myTab" role="tablist">
                        <li class="mr-2" role="presentation">
                            <button class="nav-link py-4 px-6 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-primary relative flex items-center active" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage" type="button" role="tab">
                                <i class="fas fa-layer-group mr-2"></i>Manage Clusters
                            </button>
                        </li>
                        <li class="mr-2" role="presentation">
                            <button class="nav-link py-4 px-6 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-primary relative flex items-center" id="unassigned-tab" data-bs-toggle="tab" data-bs-target="#unassigned" type="button" role="tab">
                                <i class="fas fa-users mr-2"></i>Unassigned Groups
                            </button>
                        </li>
                        <li class="mr-2" role="presentation">
                            <button class="nav-link py-4 px-6 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-primary relative flex items-center" id="faculty-tab" data-bs-toggle="tab" data-bs-target="#faculty" type="button" role="tab">
                                <i class="fas fa-chalkboard-teacher mr-2"></i>Faculty List
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-8" id="myTabContent">
                        <!-- Manage Clusters Tab -->
                        <div class="tab-pane active" id="manage" role="tabpanel">
                            <div class="flex justify-between items-center mb-8">
                                <div class="flex items-center">
                                    <div class="gradient-blue p-3 rounded-xl mr-4">
                                        <i class="fas fa-layer-group text-white text-xl"></i>
                                    </div>
                                    <h4 class="text-2xl font-bold text-gray-900">Manage Clusters</h4>
                                </div>
                                <div class="flex gap-3">
                                    <select id="programFilter" class="border-2 border-gray-200 text-gray-700 py-3 px-4 rounded-xl text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                        <option value="">All Programs</option>
                                        <option value="BS Information Technology">BS Information Technology (BSIT)</option>
                                        <option value="BS Hospitality Management">BS Hospitality Management (BSHM)</option>
                                        <option value="BS Office Administration">BS Office Administration (BSOA)</option>
                                        <option value="BS Business Administration">BS Business Administration (BSBA)</option>
                                        <option value="BS Criminology">BS Criminology (BSCRIM)</option>
                                        <option value="Bachelor of Elementary Education">Bachelor of Elementary Education (BEED)</option>
                                        <option value="Bachelor of Secondary Education">Bachelor of Secondary Education (BSED)</option>
                                        <option value="BS Computer Engineering">BS Computer Engineering (BSCE)</option>
                                        <option value="BS Tourism Management">BS Tourism Management (BSTM)</option>
                                        <option value="BS Entrepreneurship">BS Entrepreneurship (BSE)</option>
                                        <option value="BS Accounting Information System">BS Accounting Information System (BSAIS)</option>
                                        <option value="BS Psychology">BS Psychology (BSPSYCH)</option>
                                        <option value="BL Information Science">BL Information Science (BLIS)</option>
                                    </select>
                                    <button class="gradient-green text-white font-semibold py-3 px-6 rounded-xl flex items-center transition-all duration-300 hover:shadow-lg hover:scale-105" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                                        <i class="fas fa-magic mr-2"></i>Auto Generate
                                    </button>
                                    <button class="gradient-blue text-white font-semibold py-3 px-6 rounded-xl flex items-center transition-all duration-300 hover:shadow-lg hover:scale-105" data-bs-toggle="modal" data-bs-target="#createClusterModal">
                                        <i class="fas fa-plus-circle mr-2"></i>Create New Cluster
                                    </button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                                <?php while ($cluster = mysqli_fetch_assoc($clusters)): 
                                    $percentage = $cluster['capacity'] > 0 ? ($cluster['student_count'] / $cluster['capacity']) * 100 : 0;
                                    $progress_color = $percentage < 60 ? 'bg-success' : ($percentage < 90 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <div class="cluster-card bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-2xl shadow-lg border border-blue-200 h-full relative overflow-hidden" data-program="<?= htmlspecialchars($cluster['program']) ?>">
                                    <!-- Decorative gradient overlay -->
                                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-400/20 to-transparent rounded-full -translate-y-16 translate-x-16"></div>
                                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-indigo-400/20 to-transparent rounded-full translate-y-12 -translate-x-12"></div>
                                    
                                    <div class="p-6 relative z-10">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex items-center">
                                                <div class="gradient-blue p-3 rounded-xl mr-3 shadow-lg">
                                                    <i class="fas fa-layer-group text-white text-lg"></i>
                                                </div>
                                                <div>
                                                    <h5 class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($cluster['program']) ?></h5>
                                                    <p class="text-gray-600 text-sm font-medium">Cluster <?= htmlspecialchars($cluster['cluster']) ?></p>
                                                </div>
                                            </div>
                                            <span class="px-4 py-2 rounded-full text-xs font-bold shadow-sm <?= $cluster['status'] == 'assigned' ? 'bg-gradient-to-r from-green-400 to-green-600 text-white' : 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white' ?>">
                                                <i class="fas <?= $cluster['status'] == 'assigned' ? 'fa-check-circle' : 'fa-clock' ?> mr-1"></i>
                                                <?= ucfirst($cluster['status']) ?>
                                            </span>
                                        </div>
                                        
                                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-3 mb-4 border border-white/40">
                                            <div class="flex items-center justify-between text-sm mb-2">
                                                <span class="text-gray-700 font-medium">Academic Year</span>
                                                <span class="text-gray-900 font-semibold"><?= htmlspecialchars($cluster['school_year']) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-5">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-gray-700 font-medium text-sm flex items-center">
                                                    <i class="fas fa-users mr-2 text-blue-500"></i>
                                                    Students Enrolled
                                                </span>
                                                <span class="text-gray-900 font-bold"><?= $cluster['student_count'] ?> / <?= $cluster['capacity'] ?></span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                                                <div class="h-3 rounded-full bg-gradient-to-r <?= $percentage < 60 ? 'from-green-400 to-green-600' : ($percentage < 90 ? 'from-yellow-400 to-orange-500' : 'from-red-400 to-red-600') ?> shadow-sm transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <div class="text-right mt-1">
                                                <span class="text-xs font-semibold <?= $percentage < 60 ? 'text-green-600' : ($percentage < 90 ? 'text-orange-600' : 'text-red-600') ?>"><?= round($percentage) ?>% Full</span>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-5 border border-white/40">
                                            <div class="flex items-center">
                                                <div class="gradient-purple p-2 rounded-lg mr-3">
                                                    <i class="fas fa-user-tie text-white text-sm"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wide">Thesis Adviser</p>
                                                    <p class="text-gray-900 font-semibold">
                                                        <?= $cluster['adviser_name'] ? htmlspecialchars($cluster['adviser_name']) : '<span class="text-red-500 flex items-center"><i class="fas fa-exclamation-triangle mr-1"></i>Not assigned</span>' ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="cluster-actions grid grid-cols-2 gap-2">
                                            <button class="bg-white/80 hover:bg-white border border-blue-200 hover:border-blue-300 text-blue-700 hover:text-blue-800 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-md backdrop-blur-sm" 
                                                    onclick="viewCluster(<?= $cluster['id'] ?>)">
                                                <i class="fas fa-eye mr-2"></i>View Details
                                            </button>
                                            
                                            <button class="bg-white/80 hover:bg-white border border-gray-200 hover:border-gray-300 text-gray-700 hover:text-gray-800 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-md backdrop-blur-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#editClusterModal"
                                                    data-cluster-id="<?= $cluster['id'] ?>"
                                                    data-program="<?= htmlspecialchars($cluster['program']) ?>"
                                                    data-cluster="<?= htmlspecialchars($cluster['cluster']) ?>"
                                                    data-school-year="<?= htmlspecialchars($cluster['school_year']) ?>"
                                                    data-capacity="<?= $cluster['capacity'] ?>">
                                                <i class="fas fa-edit mr-2"></i>Edit
                                            </button>
                                            
                                            <?php if ($cluster['faculty_id']): ?>
                                            <form method="POST" class="col-span-1">
                                                <input type="hidden" name="cluster_id" value="<?= $cluster['id'] ?>">
                                                <button type="submit" name="remove_adviser" class="w-full bg-gradient-to-r from-orange-400 to-red-500 hover:from-orange-500 hover:to-red-600 text-white py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" 
                                                        onclick="return confirm('Remove adviser from this cluster?')">
                                                    <i class="fas fa-user-times mr-2"></i>Remove Adviser
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" 
                                                    data-bs-toggle="modal" data-bs-target="#assignAdviserModal"
                                                    data-cluster-id="<?= $cluster['id'] ?>"
                                                    data-cluster-name="<?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?>">
                                                <i class="fas fa-plus mr-2"></i>Assign Adviser
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button class="bg-white/80 hover:bg-red-50 border border-red-200 hover:border-red-300 text-red-600 hover:text-red-700 py-2.5 px-4 rounded-xl text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-md backdrop-blur-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteClusterModal"
                                                    data-cluster-id="<?= $cluster['id'] ?>">
                                                <i class="fas fa-trash mr-2"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <!-- Unassigned Groups Tab -->
                        <div class="tab-pane" id="unassigned" role="tabpanel">
                            <div class="flex justify-between items-center mb-8">
                                <div class="flex items-center">
                                    <div class="gradient-cyan p-3 rounded-xl mr-4">
                                        <i class="fas fa-users text-white text-xl"></i>
                                    </div>
                                    <h4 class="text-2xl font-bold text-gray-900">Unassigned Groups</h4>
                                </div>
                                <div class="flex gap-2">
                                    <button class="border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 px-3 rounded-lg text-sm font-medium flex items-center">
                                        <i class="fas fa-download mr-1"></i>Export
                                    </button>
                                </div>
                            </div>
                            
                            <?php if (mysqli_num_rows($unassigned_groups) > 0): ?>
                            <div class="space-y-3">
                                <?php while ($group = mysqli_fetch_assoc($unassigned_groups)): ?>
                                <div class="group-item bg-white border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                    <div>
                                        <strong class="text-gray-900"><?= htmlspecialchars($group['name']) ?></strong>
                                        <div class="text-gray-500 text-sm mt-1">
                                            <i class="fas fa-graduation-cap mr-1"></i><?= htmlspecialchars($group['program']) ?> 
                                            | <i class="fas fa-users mr-1"></i><?= $group['member_count'] ?> members
                                            | <i class="fas fa-key mr-1"></i>Join Code: <?= htmlspecialchars($group['join_code']) ?>
                                        </div>
                                    </div>
                                    <button class="bg-primary hover:bg-blue-700 text-white py-1.5 px-3 rounded-lg text-sm font-medium flex items-center transition duration-200" data-bs-toggle="modal" data-bs-target="#assignGroupModal" 
                                        data-group-id="<?= $group['id'] ?>" 
                                        data-group-name="<?= htmlspecialchars($group['name']) ?>" 
                                        data-group-program="<?= htmlspecialchars($group['program']) ?>">
                                        <i class="fas fa-plus mr-1"></i>Assign to Cluster
                                    </button>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg flex items-center">
                                <i class="fas fa-info-circle mr-2 text-lg"></i>
                                <div>No unassigned groups available.</div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Faculty List Tab -->
                        <div class="tab-pane" id="faculty" role="tabpanel">
                            <div class="flex justify-between items-center mb-8">
                                <div class="flex items-center">
                                    <div class="gradient-purple p-3 rounded-xl mr-4">
                                        <i class="fas fa-chalkboard-teacher text-white text-xl"></i>
                                    </div>
                                    <h4 class="text-2xl font-bold text-gray-900">Faculty Directory</h4>
                                </div>
                                <div class="flex gap-3">
                                    <select id="departmentFilter" class="border-2 border-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all">
                                        <option value="">All Departments</option>
                                        <option value="Information Technology">Information Technology</option>
                                        <option value="Hospitality Management">Hospitality Management</option>
                                        <option value="Office Administration">Office Administration</option>
                                        <option value="Business Administration">Business Administration</option>
                                        <option value="Criminology">Criminology</option>
                                        <option value="Elementary Education">Elementary Education</option>
                                        <option value="Secondary Education">Secondary Education</option>
                                        <option value="Computer Engineering">Computer Engineering</option>
                                        <option value="Tourism Management">Tourism Management</option>
                                        <option value="Entrepreneurship">Entrepreneurship</option>
                                        <option value="Accounting Information System">Accounting Information System</option>
                                        <option value="Psychology">Psychology</option>
                                        <option value="Information Science">Information Science</option>
                                    </select>
                                    <button class="gradient-purple text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-all duration-300 hover:shadow-lg hover:scale-105" data-bs-toggle="modal" data-bs-target="#createFacultyModal">
                                        <i class="fas fa-plus mr-2"></i>Add Faculty
                                    </button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php 
                                // Reset faculty pointer
                                mysqli_data_seek($faculty, 0);
                                while ($fac = mysqli_fetch_assoc($faculty)): 
                                    // Check if faculty is already assigned to a cluster
                                    $assigned_check = mysqli_query($conn, "SELECT COUNT(*) FROM clusters WHERE faculty_id = " . $fac['id']);
                                    $assigned_count = mysqli_fetch_row($assigned_check)[0];
                                    $is_assigned = $assigned_count > 0;
                                ?>
                                <div class="faculty-item bg-gradient-to-br from-white via-purple-50 to-indigo-100 rounded-2xl shadow-lg border border-purple-200 p-6 relative overflow-hidden" data-department="<?= htmlspecialchars($fac['department']) ?>">
                                    <!-- Decorative elements -->
                                    <div class="absolute top-0 right-0 w-20 h-20 bg-purple-400/10 rounded-full -translate-y-10 translate-x-10"></div>
                                    <div class="absolute bottom-0 left-0 w-16 h-16 bg-indigo-400/10 rounded-full translate-y-8 -translate-x-8"></div>
                                    
                                    <div class="relative z-10">
                                        <!-- Header -->
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-center">
                                                <div class="gradient-purple p-3 rounded-xl mr-3 shadow-lg">
                                                    <i class="fas fa-user-tie text-white text-lg"></i>
                                                </div>
                                                <div>
                                                    <h5 class="font-bold text-gray-900 text-lg leading-tight"><?= htmlspecialchars($fac['fullname']) ?></h5>
                                                    <p class="text-purple-600 text-sm font-medium"><?= htmlspecialchars($fac['department']) ?></p>
                                                </div>
                                            </div>
                                            <?php if ($is_assigned): ?>
                                            <span class="bg-gradient-to-r from-green-400 to-green-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                                <i class="fas fa-check mr-1"></i>Active
                                            </span>
                                            <?php else: ?>
                                            <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                                <i class="fas fa-clock mr-1"></i>Available
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Details -->
                                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 mb-4 border border-white/40">
                                            <div class="flex items-center text-sm">
                                                <i class="fas fa-star text-purple-500 mr-2 w-4"></i>
                                                <span class="text-gray-700"><?= htmlspecialchars($fac['expertise']) ?></span>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex gap-2">
                                            <?php if (!$is_assigned): ?>
                                            <button class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-lg transform hover:scale-105" 
                                                    data-bs-toggle="modal" data-bs-target="#assignFacultyToClusterModal"
                                                    data-faculty-id="<?= $fac['id'] ?>" 
                                                    data-faculty-name="<?= htmlspecialchars($fac['fullname']) ?>">
                                                <i class="fas fa-plus mr-1"></i>Assign
                                            </button>
                                            <?php else: ?>
                                            <div class="flex-1 bg-green-100 text-green-800 py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center">
                                                <i class="fas fa-check mr-1"></i>Assigned
                                            </div>
                                            <?php endif; ?>
                                            <button class="bg-white/80 hover:bg-white border border-gray-200 hover:border-gray-300 text-gray-700 hover:text-gray-800 py-2 px-3 rounded-lg text-xs font-semibold flex items-center justify-center transition-all duration-300 hover:shadow-md backdrop-blur-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#editFacultyModal"
                                                    data-faculty-id="<?= $fac['id'] ?>"
                                                    data-fullname="<?= htmlspecialchars($fac['fullname']) ?>"
                                                    data-department="<?= htmlspecialchars($fac['department']) ?>"
                                                    data-expertise="<?= htmlspecialchars($fac['expertise']) ?>">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modals -->
    <!-- Bulk Create Clusters Modal -->
    <div class="modal fade" id="bulkCreateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-gradient-to-br from-white via-green-50 to-emerald-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
                <form method="POST">
                    <div class="modal-header bg-gradient-to-r from-green-600 to-emerald-700 text-white p-6 border-0">
                        <h5 class="modal-title font-bold text-xl flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-magic text-white"></i>
                            </div>
                            Auto Generate All Clusters
                        </h5>
                        <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6 space-y-6">
                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-5 border border-white/40">
                            <p class="text-gray-700 mb-4 font-medium">This will create clusters 41001-41010 for all programs:</p>
                            <div class="grid grid-cols-1 gap-2 mb-4">
                                <div class="flex items-center p-2 bg-blue-50 rounded-lg">
                                    <i class="fas fa-laptop-code text-blue-600 mr-3"></i>
                                    <span class="text-gray-700">BS Information Technology (10 clusters)</span>
                                </div>
                                <div class="flex items-center p-2 bg-purple-50 rounded-lg">
                                    <i class="fas fa-briefcase text-purple-600 mr-3"></i>
                                    <span class="text-gray-700">BS Business Administration (10 clusters)</span>
                                </div>
                                <div class="flex items-center p-2 bg-yellow-50 rounded-lg">
                                    <i class="fas fa-graduation-cap text-yellow-600 mr-3"></i>
                                    <span class="text-gray-700">Bachelor of Elementary Education (10 clusters)</span>
                                </div>
                                <div class="flex items-center p-2 bg-green-50 rounded-lg">
                                    <i class="fas fa-shield-alt text-green-600 mr-3"></i>
                                    <span class="text-gray-700">BS Criminology (10 clusters)</span>
                                </div>
                                <div class="flex items-center p-2 bg-indigo-50 rounded-lg">
                                    <i class="fas fa-server text-indigo-600 mr-3"></i>
                                    <span class="text-gray-700">BS Computer Engineering (10 clusters)</span>
                                </div>
                            </div>
                            <div class="bg-green-100 border border-green-200 rounded-lg p-3">
                                <p class="text-green-800 font-semibold text-center">Total: 130 clusters will be created (13 programs × 10 clusters each)</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                    <span class="text-blue-800 font-semibold">School Year (Auto-Generated)</span>
                                </div>
                                <p class="text-blue-700 text-sm">
                                    <?php 
                                    $current_year = date('Y');
                                    $current_month = date('n');
                                    $start_year = ($current_month <= 6) ? $current_year - 1 : $current_year;
                                    $auto_school_year = $start_year . '-' . ($start_year + 1);
                                    echo "Clusters will be created for Academic Year: <strong>$auto_school_year</strong>";
                                    ?>
                                </p>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Capacity per Cluster</label>
                                <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all" name="capacity" min="5" step="5" value="25" required>
                                <p class="text-gray-500 text-sm mt-2 flex items-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Must be a multiple of 5 (each group has 5 students)
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                        <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="bulk_create" class="bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg" onclick="return confirm('Create 130 clusters for all programs?')">Generate All Clusters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Cluster Modal -->
    <div class="modal fade" id="createClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-gradient-to-br from-white via-blue-50 to-indigo-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
                <form method="POST">
                    <div class="modal-header bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 border-0">
                        <h5 class="modal-title font-bold text-xl flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-plus-circle text-white"></i>
                            </div>
                            Create New Cluster
                        </h5>
                        <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6 space-y-5">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Program</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="program" required>
                                <option value="">-- Select Program --</option>
                                <option value="BS Information Technology">BS Information Technology (BSIT)</option>
                                <option value="BS Hospitality Management">BS Hospitality Management (BSHM)</option>
                                <option value="BS Office Administration">BS Office Administration (BSOA)</option>
                                <option value="BS Business Administration">BS Business Administration (BSBA)</option>
                                <option value="BS Criminology">BS Criminology (BSCRIM)</option>
                                <option value="Bachelor of Elementary Education">Bachelor of Elementary Education (BEED)</option>
                                <option value="Bachelor of Secondary Education">Bachelor of Secondary Education (BSED)</option>
                                <option value="BS Computer Engineering">BS Computer Engineering (BSCE)</option>
                                <option value="BS Tourism Management">BS Tourism Management (BSTM)</option>
                                <option value="BS Entrepreneurship">BS Entrepreneurship (BSE)</option>
                                <option value="BS Accounting Information System">BS Accounting Information System (BSAIS)</option>
                                <option value="BS Psychology">BS Psychology (BSPSYCH)</option>
                                <option value="BL Information Science">BL Information Science (BLIS)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Cluster Name/Number</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="cluster" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">School Year</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="school_year" value="<?php 
                                $current_year = date('Y');
                                $current_month = date('n');
                                $start_year = ($current_month <= 6) ? $current_year - 1 : $current_year;
                                echo $start_year . '-' . ($start_year + 1);
                            ?>" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Capacity</label>
                            <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="capacity" min="5" step="5" required>
                            <p class="text-gray-500 text-sm mt-2 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Must be a multiple of 5 (each group has 5 students)
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                        <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_cluster" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Create Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Cluster Modal -->
    <div class="modal fade" id="editClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-gradient-to-br from-white via-blue-50 to-indigo-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
                <form method="POST">
                    <input type="hidden" name="cluster_id" id="edit_cluster_id">
                    <div class="modal-header bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 border-0">
                        <h5 class="modal-title font-bold text-xl flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-edit text-white"></i>
                            </div>
                            Edit Cluster
                        </h5>
                        <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6 space-y-5">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Program</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="program" id="edit_program" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Cluster Name/Number</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="cluster" id="edit_cluster" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">School Year</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="school_year" id="edit_school_year" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Capacity</label>
                            <input type="number" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="capacity" id="edit_capacity" min="5" step="5" required>
                            <p class="text-gray-500 text-sm mt-2 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Must be a multiple of 5 (each group has 5 students)
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                        <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_cluster" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Update Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Cluster Modal -->
    <div class="modal fade" id="deleteClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-gradient-to-br from-white via-red-50 to-rose-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
                <form method="POST">
                    <input type="hidden" name="cluster_id" id="delete_cluster_id">
                    <div class="modal-header bg-gradient-to-r from-red-600 to-rose-700 text-white p-6 border-0">
                        <h5 class="modal-title font-bold text-xl flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-5 border border-white/40">
                            <p class="text-gray-700 mb-4 font-medium">Are you sure you want to delete this cluster? This action will:</p>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center p-2 bg-red-50 rounded-lg">
                                    <i class="fas fa-unlink text-red-600 mr-3"></i>
                                    <span class="text-gray-700">Remove all group assignments from this cluster</span>
                                </div>
                                <div class="flex items-center p-2 bg-red-50 rounded-lg">
                                    <i class="fas fa-user-times text-red-600 mr-3"></i>
                                    <span class="text-gray-700">Remove faculty adviser assignment</span>
                                </div>
                                <div class="flex items-center p-2 bg-red-50 rounded-lg">
                                    <i class="fas fa-undo text-red-600 mr-3"></i>
                                    <span class="text-gray-700">Reset cluster assignments for all affected students</span>
                                </div>
                            </div>
                            <div class="bg-red-100 border border-red-200 rounded-lg p-3">
                                <p class="text-red-800 font-semibold text-center flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                        <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_cluster" class="bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Delete Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <!-- Assign Adviser to Specific Cluster Modal -->
<div class="modal fade" id="assignAdviserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-gradient-to-br from-white via-blue-50 to-indigo-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
            <form method="POST">
                <input type="hidden" name="cluster_id" id="assign_cluster_id">
                <div class="modal-header bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 border-0">
                    <h5 class="modal-title font-bold text-xl flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                        Assign Adviser to Cluster
                    </h5>
                    <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body p-6 space-y-5">
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40">
                        <div class="flex items-center">
                            <div class="gradient-blue p-2 rounded-lg mr-3">
                                <i class="fas fa-layer-group text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Selected Cluster</p>
                                <p class="text-gray-900 font-semibold" id="assign_cluster_name"></p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Select Faculty Adviser</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" name="faculty_id" id="adviser_select" required>
                            <option value="">-- Select Faculty --</option>
                            <?php
                            // Reset faculty pointer
                            mysqli_data_seek($faculty, 0);
                            while ($fac = mysqli_fetch_assoc($faculty)):
                                // Check if faculty is already assigned to a cluster
                                $assigned_check = mysqli_query($conn, "SELECT COUNT(*) FROM clusters WHERE faculty_id = " . $fac['id']);
                                $assigned_count = mysqli_fetch_row($assigned_check)[0];
                                $is_assigned = $assigned_count > 0;
                                
                                if (!$is_assigned):
                            ?>
                            <option value="<?= $fac['id'] ?>" data-program="<?= htmlspecialchars($fac['department']) ?>">
                                <?= htmlspecialchars($fac['fullname']) ?> - <?= htmlspecialchars($fac['department']) ?>
                            </option>
                            <?php endif; endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                    <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_adviser" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Assign Adviser</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Faculty to Any Cluster Modal -->
<div class="modal fade" id="assignFacultyToClusterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-gradient-to-br from-white via-purple-50 to-indigo-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
            <form method="POST">
                <input type="hidden" name="faculty_id" id="assign_faculty_id">
                <div class="modal-header bg-gradient-to-r from-purple-600 to-indigo-700 text-white p-6 border-0">
                    <h5 class="modal-title font-bold text-xl flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                        Assign Faculty to Cluster
                    </h5>
                    <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body p-6 space-y-5">
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40">
                        <div class="flex items-center">
                            <div class="gradient-purple p-2 rounded-lg mr-3">
                                <i class="fas fa-user-tie text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Selected Faculty</p>
                                <p class="text-gray-900 font-semibold" id="assign_faculty_name"></p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Select Cluster</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="cluster_id" id="cluster_select_faculty" required>
                            <option value="">-- Select Cluster --</option>
                            <?php
                            // Get clusters without advisers
                            $available_clusters = mysqli_query(
                                $conn,
                                "SELECT * FROM clusters WHERE faculty_id IS NULL OR faculty_id = 0 ORDER BY program, cluster"
                            );
                            
                            while ($cluster = mysqli_fetch_assoc($available_clusters)):
                            ?>
                            <option value="<?= $cluster['id'] ?>" data-program="<?= htmlspecialchars($cluster['program']) ?>">
                                <?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                    <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_adviser" class="bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Assign to Cluster</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Assign Group to Cluster Modal -->
<div class="modal fade" id="assignGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-gradient-to-br from-white via-cyan-50 to-blue-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
            <form method="POST">
                <input type="hidden" name="group_id" id="assign_group_id">
                <div class="modal-header bg-gradient-to-r from-cyan-600 to-blue-700 text-white p-6 border-0">
                    <h5 class="modal-title font-bold text-xl flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-link text-white"></i>
                        </div>
                        Assign Group to Cluster
                    </h5>
                    <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body p-6 space-y-5">
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40">
                        <div class="flex items-center mb-3">
                            <div class="gradient-cyan p-2 rounded-lg mr-3">
                                <i class="fas fa-users text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Selected Group</p>
                                <p class="text-gray-900 font-semibold" id="assign_group_name"></p>
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span>Program: <span id="assign_group_program" class="font-medium"></span></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Select Cluster</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all" name="cluster_id" id="cluster_select" required>
                            <option value="">-- Select Cluster --</option>
                            <?php
                            // Re-fetch clusters to ensure we have fresh data
                            $clusters_for_dropdown = mysqli_query(
                                $conn,
                                "SELECT c.*, f.fullname AS adviser_name 
                                 FROM clusters c 
                                 LEFT JOIN faculty f ON c.faculty_id = f.id 
                                 ORDER BY c.program, c.cluster"
                            );
                            
                            while ($cluster = mysqli_fetch_assoc($clusters_for_dropdown)):
                                // Check if cluster has capacity
                                $available_slots = $cluster['capacity'] - $cluster['student_count'];
                                if ($available_slots >= 5):
                            ?>
                            <option value="<?= $cluster['id'] ?>" data-program="<?= htmlspecialchars($cluster['program']) ?>">
                                <?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?> 
                                (Available: <?= $available_slots ?> slots)
                            </option>
                            <?php endif; endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                    <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_group" class="bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Assign Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Create Faculty Modal -->
    <div class="modal fade" id="createFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-gradient-to-br from-white via-purple-50 to-indigo-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
                <form method="POST">
                    <div class="modal-header bg-gradient-to-r from-purple-600 to-indigo-700 text-white p-6 border-0">
                        <h5 class="modal-title font-bold text-xl flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-plus-circle text-white"></i>
                            </div>
                            Add New Faculty
                        </h5>
                        <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6 space-y-5">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Full Name</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="fullname" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Program</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="department" required>
                                <option value="">-- Select Program --</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Hospitality Management">Hospitality Management</option>
                                <option value="Office Administration">Office Administration</option>
                                <option value="Business Administration">Business Administration</option>
                                <option value="Criminology">Criminology</option>
                                <option value="Elementary Education">Elementary Education</option>
                                <option value="Secondary Education">Secondary Education</option>
                                <option value="Computer Engineering">Computer Engineering</option>
                                <option value="Tourism Management">Tourism Management</option>
                                <option value="Entrepreneurship">Entrepreneurship</option>
                                <option value="Accounting Information System">Accounting Information System</option>
                                <option value="Psychology">Psychology</option>
                                <option value="Information Science">Information Science</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Expertise/Field</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="expertise" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="email">
                        </div>
                    </div>
                    <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                        <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_faculty" class="bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Add Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Faculty Modal -->
    <div class="modal fade" id="editFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-gradient-to-br from-white via-purple-50 to-indigo-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
                <form method="POST">
                    <input type="hidden" name="faculty_id" id="edit_faculty_id">
                    <div class="modal-header bg-gradient-to-r from-purple-600 to-indigo-700 text-white p-6 border-0">
                        <h5 class="modal-title font-bold text-xl flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-edit text-white"></i>
                            </div>
                            Edit Faculty
                        </h5>
                        <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6 space-y-5">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Full Name</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="fullname" id="edit_fullname" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Program</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="department" id="edit_department" required>
                                <option value="">-- Select Program --</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Hospitality Management">Hospitality Management</option>
                                <option value="Office Administration">Office Administration</option>
                                <option value="Business Administration">Business Administration</option>
                                <option value="Criminology">Criminology</option>
                                <option value="Elementary Education">Elementary Education</option>
                                <option value="Secondary Education">Secondary Education</option>
                                <option value="Computer Engineering">Computer Engineering</option>
                                <option value="Tourism Management">Tourism Management</option>
                                <option value="Entrepreneurship">Entrepreneurship</option>
                                <option value="Accounting Information System">Accounting Information System</option>
                                <option value="Psychology">Psychology</option>
                                <option value="Information Science">Information Science</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Expertise/Field</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="expertise" id="edit_expertise" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" name="email" id="edit_email">
                        </div>
                    </div>
                    <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0 space-x-3">
                        <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_faculty" class="bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg">Update Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Cluster Modal -->
    <div class="modal" id="viewClusterModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-gradient-to-br from-white via-blue-50 to-indigo-100 border-0 shadow-2xl rounded-2xl overflow-hidden">
                <div class="modal-header bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 border-0">
                    <h5 class="modal-title font-bold text-xl flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg mr-3">
                            <i class="fas fa-eye text-white"></i>
                        </div>
                        Cluster Details Overview
                    </h5>
                    <button type="button" class="btn-close text-white hover:bg-white/20 rounded-lg p-2 transition-all" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-5 border border-white/40">
                            <div class="flex items-center mb-4">
                                <div class="gradient-blue p-3 rounded-xl mr-3">
                                    <i class="fas fa-layer-group text-white"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 text-lg">Cluster Information</h5>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-600 font-medium">Program:</span>
                                    <span class="font-semibold text-gray-900" id="modal-cluster-program"></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-600 font-medium">Cluster:</span>
                                    <span class="font-semibold text-gray-900" id="modal-cluster-name"></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-600 font-medium">School Year:</span>
                                    <span class="font-semibold text-gray-900" id="modal-school-year"></span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 font-medium">Capacity:</span>
                                    <span class="font-semibold text-gray-900" id="modal-capacity"></span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-5 border border-white/40">
                            <div class="flex items-center mb-4">
                                <div class="gradient-purple p-3 rounded-xl mr-3">
                                    <i class="fas fa-user-tie text-white"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 text-lg">Adviser Information</h5>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-600 font-medium">Name:</span>
                                    <span class="font-semibold text-gray-900" id="modal-adviser-name"></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-600 font-medium">Department:</span>
                                    <span class="font-semibold text-gray-900" id="modal-department"></span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 font-medium">Expertise:</span>
                                    <span class="font-semibold text-gray-900" id="modal-expertise"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-5 border border-white/40">
                        <div class="flex items-center mb-4">
                            <div class="gradient-green p-3 rounded-xl mr-3">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <h5 class="font-bold text-gray-900 text-lg">Students in this Cluster</h5>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Student ID</th>
                                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Name</th>
                                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Program</th>
                                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Group</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-students-table" class="divide-y divide-gray-200">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white/80 backdrop-blur-sm p-6 border-0">
                    <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:shadow-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

   <script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-bs-target');
            
            // Remove active class from all tabs
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to current tab
            this.classList.add('active');
            
            // Hide all tab panes
            tabPanes.forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Show the target tab pane
            document.querySelector(target).classList.add('active');
        });
    });
    
    // Modal functionality
    const modals = document.querySelectorAll('.modal');
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    const modalCloses = document.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
    
    // Show modal function
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Create backdrop if it doesn't exist
            if (!document.querySelector('.modal-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
                
                // Close modal when clicking on backdrop
                backdrop.addEventListener('click', function() {
                    hideModal(modal);
                });
            }
        }
    }

    // Hide modal function
    function hideModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
    
    // Handle modal triggers
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetModal = this.getAttribute('data-bs-target');
            if (targetModal) {
                const modalId = targetModal.substring(1);
                showModal(modalId);
                
                // Handle specific modal data population
                if (this.hasAttribute('data-cluster-id')) {
                    const clusterId = this.getAttribute('data-cluster-id');
                    const program = this.getAttribute('data-program');
                    const cluster = this.getAttribute('data-cluster');
                    const schoolYear = this.getAttribute('data-school-year');
                    const capacity = this.getAttribute('data-capacity');
                    
                    if (modalId === 'editClusterModal') {
                        document.getElementById('edit_cluster_id').value = clusterId;
                        document.getElementById('edit_program').value = program || '';
                        document.getElementById('edit_cluster').value = cluster || '';
                        document.getElementById('edit_school_year').value = schoolYear || '';
                        document.getElementById('edit_capacity').value = capacity || '';
                    } else if (modalId === 'deleteClusterModal') {
                        document.getElementById('delete_cluster_id').value = clusterId;
                    }
                }
                
                if (this.hasAttribute('data-group-id')) {
                    const groupId = this.getAttribute('data-group-id');
                    const groupName = this.getAttribute('data-group-name');
                    const groupProgram = this.getAttribute('data-group-program');
                    
                    if (modalId === 'assignGroupModal') {
                        document.getElementById('assign_group_id').value = groupId;
                        document.getElementById('assign_group_name').textContent = groupName || '';
                        document.getElementById('assign_group_program').textContent = groupProgram || '';
                        
                        // Filter clusters by group program
                        const clusterSelect = document.getElementById('cluster_select');
                        const options = clusterSelect.querySelectorAll('option');
                        options.forEach(option => {
                            if (option.value === '') {
                                option.style.display = 'block';
                            } else {
                                const optionProgram = option.getAttribute('data-program');
                                option.style.display = optionProgram === groupProgram ? 'block' : 'none';
                            }
                        });
                    }
                }
                
                if (this.hasAttribute('data-faculty-id')) {
                    const facultyId = this.getAttribute('data-faculty-id');
                    const facultyName = this.getAttribute('data-faculty-name');
                    
                    if (modalId === 'editFacultyModal') {
                        const fullname = this.getAttribute('data-fullname');
                        const department = this.getAttribute('data-department');
                        const expertise = this.getAttribute('data-expertise');
                        const email = this.getAttribute('data-email');
                        
                        document.getElementById('edit_faculty_id').value = facultyId;
                        document.getElementById('edit_fullname').value = fullname || '';
                        document.getElementById('edit_department').value = department || '';
                        document.getElementById('edit_expertise').value = expertise || '';
                        if (email) document.getElementById('edit_email').value = email;
                    }
                }
            }
        });
    });

    // Handle cluster adviser assignment modal (for specific cluster)
document.addEventListener('click', function(e) {
    const clusterAdviserBtn = e.target.closest('[data-bs-target="#assignAdviserModal"]');
    if (clusterAdviserBtn) {
        e.preventDefault();
        
        const clusterId = clusterAdviserBtn.getAttribute('data-cluster-id');
        const clusterName = clusterAdviserBtn.getAttribute('data-cluster-name');
        const clusterProgram = clusterName.split(' - ')[0]; // Extract program from cluster name
        
        // Set the cluster ID and name in the form
        document.getElementById('assign_cluster_id').value = clusterId;
        document.getElementById('assign_cluster_name').textContent = clusterName;
        
        // Filter advisers by program
        const adviserSelect = document.querySelector('#assignAdviserModal select[name="faculty_id"]');
        const options = adviserSelect.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                const optionProgram = option.getAttribute('data-program');
                // Match program with department
                const programMatch = 
                    (clusterProgram === 'BSCS' && optionProgram === 'Information Technology') ||
                    (clusterProgram === 'BSIT' && optionProgram === 'Information Technology') ||
                    (clusterProgram === 'BSBA' && optionProgram === 'Business Administration') ||
                    (clusterProgram === 'BSED' && optionProgram === 'Education') ||
                    (clusterProgram === 'BSCRIM' && optionProgram === 'Criminology');
                option.style.display = programMatch ? 'block' : 'none';
            }
        });
        
        // Show the modal
        showModal('assignAdviserModal');
    }
});

// Handle faculty to cluster assignment modal (for specific faculty)
document.addEventListener('click', function(e) {
    const facultyAssignBtn = e.target.closest('[data-bs-target="#assignFacultyToClusterModal"]');
    if (facultyAssignBtn) {
        e.preventDefault();
        
        const facultyId = facultyAssignBtn.getAttribute('data-faculty-id');
        const facultyName = facultyAssignBtn.getAttribute('data-faculty-name');
        const facultyDept = facultyName.split(' - ')[1] || ''; // Extract department from name
        
        // Set the faculty ID and name in the form
        document.getElementById('assign_faculty_id').value = facultyId;
        document.getElementById('assign_faculty_name').textContent = facultyName;
        
        // Filter clusters by program matching faculty department
        const clusterSelect = document.querySelector('#assignFacultyToClusterModal select[name="cluster_id"]');
        const options = clusterSelect.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                const optionProgram = option.getAttribute('data-program');
                // Show clusters from matching programs
                const programMatch = 
                    (facultyDept === 'Information Technology' && (optionProgram === 'BSCS' || optionProgram === 'BSIT')) ||
                    (facultyDept === 'Business Administration' && optionProgram === 'BSBA') ||
                    (facultyDept === 'Education' && optionProgram === 'BSED') ||
                    (facultyDept === 'Criminology' && optionProgram === 'BSCRIM');
                option.style.display = programMatch ? 'block' : 'none';
            }
        });
        
        // Show the modal
        showModal('assignFacultyToClusterModal');
    }
});
    
    // Handle modal close buttons
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            const modal = this.closest('.modal');
            hideModal(modal);
        });
    });
    
    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal(this);
            }
        });
    });

    // Handle faculty assignment modal
document.addEventListener('click', function(e) {
    const facultyAssignBtn = e.target.closest('[data-bs-target="#assignAdviserModal"]');
    if (facultyAssignBtn) {
        e.preventDefault();
        
        const facultyId = facultyAssignBtn.getAttribute('data-faculty-id');
        const facultyName = facultyAssignBtn.getAttribute('data-faculty-name');
        
        // Set the faculty ID in the form
        document.getElementById('assign_faculty_id').value = facultyId;
        document.getElementById('assign_faculty_name').textContent = facultyName;
        
        // Show the modal
        showModal('assignAdviserModal');
    }
});
    
    // Handle form submissions with confirmation
    const deleteForms = document.querySelectorAll('form[action*="delete"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Handle remove actions
    const removeButtons = document.querySelectorAll('button[name="remove_adviser"], button[name="remove_group"]');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to remove this assignment?')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    });
    
    // View cluster modal handling
    const viewClusterModal = document.getElementById('viewClusterModal');
    if (viewClusterModal && viewClusterModal.style.display === 'block') {
        document.body.style.overflow = 'hidden';
        
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
        
        // Close view cluster modal when clicking outside
        viewClusterModal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal(this);
                window.location.href = '<?php echo $_SERVER["PHP_SELF"] ?>';
            }
        });
        
        // Close view cluster modal when clicking on backdrop
        backdrop.addEventListener('click', function() {
            hideModal(viewClusterModal);
            window.location.href = '<?php echo $_SERVER["PHP_SELF"] ?>';
        });
    }
    
    // Program filter functionality
    const programFilter = document.getElementById('programFilter');
    if (programFilter) {
        programFilter.addEventListener('change', function() {
            const selectedProgram = this.value;
            const clusterCards = document.querySelectorAll('.cluster-card');
            
            clusterCards.forEach(card => {
                const cardProgram = card.getAttribute('data-program');
                if (selectedProgram === '' || cardProgram === selectedProgram) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Department filter functionality
    const departmentFilter = document.getElementById('departmentFilter');
    if (departmentFilter) {
        departmentFilter.addEventListener('change', function() {
            const selectedDepartment = this.value;
            const facultyCards = document.querySelectorAll('.faculty-item');
            
            facultyCards.forEach(card => {
                const cardDepartment = card.getAttribute('data-department');
                if (selectedDepartment === '' || cardDepartment === selectedDepartment) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // View cluster function
    window.viewCluster = function(clusterId) {
        fetch(`admin-pages/get_cluster_details.php?cluster_id=${clusterId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateViewModal(data.cluster, data.students);
                    showModal('viewClusterModal');
                } else {
                    alert('Error loading cluster details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading cluster details');
            });
    };
    
    // Populate view modal with data
    function populateViewModal(cluster, students) {
        document.getElementById('modal-cluster-program').textContent = cluster.program;
        document.getElementById('modal-cluster-name').textContent = cluster.cluster;
        document.getElementById('modal-school-year').textContent = cluster.school_year;
        document.getElementById('modal-capacity').textContent = `${cluster.student_count} / ${cluster.capacity}`;
        document.getElementById('modal-adviser-name').textContent = cluster.adviser_name || 'Not assigned';
        document.getElementById('modal-department').textContent = cluster.department || 'N/A';
        document.getElementById('modal-expertise').textContent = cluster.expertise || 'N/A';
        
        const studentsTable = document.getElementById('modal-students-table');
        studentsTable.innerHTML = '';
        
        if (students.length > 0) {
            students.forEach((student, index) => {
                const row = `
                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <td class="px-6 py-4 font-medium text-gray-900">${student.school_id}</td>
                        <td class="px-6 py-4 text-gray-700">${student.full_name}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${student.program}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700">${student.group_name || 'No Group'}</td>
                    </tr>
                `;
                studentsTable.innerHTML += row;
            });
        } else {
            studentsTable.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500"><div class="flex flex-col items-center"><i class="fas fa-users text-gray-300 text-3xl mb-2"></i><span>No students assigned to this cluster</span></div></td></tr>';
        }
    }
    
    // Make the openAssignAdviserModal function available globally
    window.openAssignAdviserModal = openAssignAdviserModal;
});
</script>
   
</body>
</html>