<?php
session_start();
include('../includes/connection.php'); // Your DB connection

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        // First, unassign all groups from this cluster
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

        $sql = "UPDATE clusters 
                SET faculty_id = $faculty_id, assigned_date = NOW(), status = 'assigned' 
                WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
    }
    
    // Remove adviser assignment
    if (isset($_POST['remove_adviser'])) {
        $cluster_id = (int) $_POST['cluster_id'];

        $sql = "UPDATE clusters 
                SET faculty_id = NULL, assigned_date = NULL, status = 'unassigned' 
                WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
    }

    // Assign group to cluster
if (isset($_POST['assign_group'])) {
    $group_id = (int) $_POST['group_id'];
    $cluster_id = (int) $_POST['cluster_id'];

    // Check if group has exactly 5 members
    $member_count = mysqli_fetch_row(mysqli_query($conn, 
        "SELECT COUNT(*) FROM group_members WHERE group_id = $group_id"))[0];
    
    if ($member_count == 5) {
        // Update group cluster assignment
        $sql = "UPDATE groups SET cluster_id = $cluster_id WHERE id = $group_id";
        mysqli_query($conn, $sql);
        
        // Update student cluster + faculty assignment for all group members
        // FIXED: Changed sp.cluster to sp.cluster_id
$sql = "UPDATE student_profiles sp
        JOIN group_members gm ON sp.id = gm.student_id
        SET sp.cluster = $cluster_id, 
            sp.faculty_id = (SELECT faculty_id FROM clusters WHERE id = $cluster_id)
        WHERE gm.group_id = $group_id";
mysqli_query($conn, $sql);


        // Update student count in cluster
        $sql = "UPDATE clusters SET student_count = student_count + 5 WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
    } else {
        $_SESSION['error'] = "Group must have exactly 5 members to be assigned to a cluster";
    }
}
    
// Remove group from cluster
if (isset($_POST['remove_group'])) {
    $group_id = (int) $_POST['group_id'];
    $cluster_id = (int) $_POST['cluster_id'];

    // Get group info first
    $group = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cluster_id FROM groups WHERE id = $group_id"));
    
    if ($group && $group['cluster_id'] == $cluster_id) {
        // Remove group from cluster
        $sql = "UPDATE groups SET cluster_id = NULL WHERE id = $group_id";
        mysqli_query($conn, $sql);
        
        // Remove cluster assignment from all group members
        // FIXED: Changed sp.cluster to sp.cluster_id
       $sql = "UPDATE student_profiles sp
        JOIN group_members gm ON sp.id = gm.student_id
        SET sp.cluster = 'Not Assigned', sp.faculty_id = NULL
        WHERE gm.group_id = $group_id";
mysqli_query($conn, $sql);

        // Update student count in cluster
        $sql = "UPDATE clusters SET student_count = student_count - 5 WHERE id = $cluster_id";
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
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($cluster_details) {
    // Fetch groups (you already have this)
    $cluster_groups = mysqli_query($conn,
        "SELECT g.*, COUNT(gm.student_id) as member_count
         FROM groups g
         LEFT JOIN group_members gm ON g.id = gm.group_id
         WHERE g.cluster_id = $cluster_id
         GROUP BY g.id
         ORDER BY g.name");

    // 👇 Fetch students in this cluster
    $result = mysqli_query($conn,
        "SELECT sp.id, sp.school_id, sp.full_name, sp.program
         FROM student_profiles sp
         WHERE sp.cluster = '$cluster_id'
         ORDER BY sp.full_name");
    
    $cluster_students = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
     HAVING member_count = 5
     ORDER BY g.program, g.name"
);

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
        .nav-tabs .nav-link.active {
            position: relative;
            color: #4A6CF7;
        }
        
        .nav-tabs .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #4A6CF7;
            border-radius: 3px 3px 0 0;
        }
        
        .cluster-card, .group-item, .faculty-item {
            transition: all 0.3s ease;
        }
        
        .cluster-card:hover, .group-item:hover, .faculty-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">
    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        
        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6">
            <div class="container mx-auto">
                <!-- Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    
    <!-- Total Clusters -->
    <div class="bg-white text-gray-800 rounded-lg shadow-sm overflow-hidden border-l-4 border-blue-600">
        <div class="p-5">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-gray-500 text-sm font-medium uppercase">Total Clusters</h6>
                    <h3 class="text-2xl font-bold"><?= $total_clusters ?></h3>
                </div>
                <div class="text-2xl text-blue-600">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Clusters -->
    <div class="bg-white text-gray-800 rounded-lg shadow-sm overflow-hidden border-l-4 border-green-600">
        <div class="p-5">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-gray-500 text-sm font-medium uppercase">Assigned Clusters</h6>
                    <h3 class="text-2xl font-bold"><?= $assigned_clusters ?></h3>
                </div>
                <div class="text-2xl text-green-600">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Groups -->
    <div class="bg-white text-gray-800 rounded-lg shadow-sm overflow-hidden border-l-4 border-cyan-600">
        <div class="p-5">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-gray-500 text-sm font-medium uppercase">Total Groups</h6>
                    <h3 class="text-2xl font-bold"><?= $total_groups ?></h3>
                </div>
                <div class="text-2xl text-cyan-600">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Groups -->
    <div class="bg-white text-gray-800 rounded-lg shadow-sm overflow-hidden border-l-4 border-yellow-600">
        <div class="p-5">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-gray-500 text-sm font-medium uppercase">Assigned Groups</h6>
                    <h3 class="text-2xl font-bold"><?= $assigned_groups ?></h3>
                </div>
                <div class="text-2xl text-yellow-600">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Faculty -->
    <div class="bg-white text-gray-800 rounded-lg shadow-sm overflow-hidden border-l-4 border-gray-600">
        <div class="p-5">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-gray-500 text-sm font-medium uppercase">Total Faculty</h6>
                    <h3 class="text-2xl font-bold"><?= $total_faculty ?></h3>
                </div>
                <div class="text-2xl text-gray-600">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Faculty -->
    <div class="bg-white text-gray-800 rounded-lg shadow-sm overflow-hidden border-l-4 border-red-600">
        <div class="p-5">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-gray-500 text-sm font-medium uppercase">Assigned Faculty</h6>
                    <h3 class="text-2xl font-bold"><?= $assigned_faculty ?></h3>
                </div>
                <div class="text-2xl text-red-600">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
    </div>

</div>
                
                <!-- Tabs Section -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                    <ul class="nav-tabs flex border-b border-gray-200 px-6" id="myTab" role="tablist">
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
                    
                    <div class="tab-content p-6" id="myTabContent">
                        <!-- Manage Clusters Tab -->
                        <div class="tab-pane active" id="manage" role="tabpanel">
                            <div class="flex justify-between items-center mb-6">
                                <h4 class="text-lg font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-layer-group text-primary mr-2"></i>Manage Clusters
                                </h4>
                                <div class="flex gap-2">
                                    <button class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition duration-200" data-bs-toggle="modal" data-bs-target="#createClusterModal">
                                        <i class="fas fa-plus-circle mr-2"></i>Create New Cluster
                                    </button>
                                    <button class="border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 px-3 rounded-lg text-sm font-medium flex items-center">
                                        <i class="fas fa-filter mr-1"></i>Filter
                                    </button>
                                    <button class="border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 px-3 rounded-lg text-sm font-medium flex items-center">
                                        <i class="fas fa-sort mr-1"></i>Sort
                                    </button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                                <?php while ($cluster = mysqli_fetch_assoc($clusters)): 
                                    $percentage = $cluster['capacity'] > 0 ? ($cluster['student_count'] / $cluster['capacity']) * 100 : 0;
                                    $progress_color = $percentage < 60 ? 'bg-success' : ($percentage < 90 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <div class="cluster-card bg-white rounded-lg shadow-sm border border-gray-100 h-full">
                                    <div class="p-5">
                                        <div class="flex justify-between items-center mb-3">
                                            <h5 class="font-bold text-gray-900"><?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?></h5>
                                            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $cluster['status'] == 'assigned' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                <?= ucfirst($cluster['status']) ?>
                                            </span>
                                        </div>
                                        <h6 class="text-gray-500 text-sm mb-4"><?= htmlspecialchars($cluster['school_year']) ?></h6>
                                        
                                        <div class="mb-4">
                                            <div class="flex justify-between mb-1 text-sm">
                                                <span class="text-gray-600">Students: <?= $cluster['student_count'] ?> / <?= $cluster['capacity'] ?></span>
                                                <span class="text-gray-600"><?= round($percentage) ?>%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full <?= $progress_color ?>" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <p class="text-gray-700 mb-4 flex items-center">
                                            <i class="fas fa-user-tie mr-2 text-gray-500"></i>
                                            <?= $cluster['adviser_name'] ? htmlspecialchars($cluster['adviser_name']) : '<span class="text-red-500">Not assigned</span>' ?>
                                        </p>
                                        
                                        <div class="cluster-actions flex flex-wrap gap-2 mt-4">
                                            <button class="bg-blue-50 hover:bg-blue-100 text-blue-700 py-1.5 px-3 rounded-lg text-xs font-medium flex items-center transition duration-200" 
                                                    onclick="window.location.href='admin-pages/adviser-assignment.php?view_cluster=<?= $cluster['id'] ?>#manage'">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </button>
                                            
                                            <button class="bg-gray-50 hover:bg-gray-100 text-gray-700 py-1.5 px-3 rounded-lg text-xs font-medium flex items-center transition duration-200" 
                                                    data-bs-toggle="modal" data-bs-target="#editClusterModal"
                                                    data-cluster-id="<?= $cluster['id'] ?>"
                                                    data-program="<?= htmlspecialchars($cluster['program']) ?>"
                                                    data-cluster="<?= htmlspecialchars($cluster['cluster']) ?>"
                                                    data-school-year="<?= htmlspecialchars($cluster['school_year']) ?>"
                                                    data-capacity="<?= $cluster['capacity'] ?>">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </button>
                                            
                                            <button class="bg-red-50 hover:bg-red-100 text-red-700 py-1.5 px-3 rounded-lg text-xs font-medium flex items-center transition duration-200" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteClusterModal"
                                                    data-cluster-id="<?= $cluster['id'] ?>">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                            
                                            <?php if ($cluster['faculty_id']): ?>
<form method="POST" class="inline">
    <input type="hidden" name="cluster_id" value="<?= $cluster['id'] ?>">
    <button type="submit" name="remove_adviser" class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 py-1.5 px-3 rounded-lg text-xs font-medium flex items-center transition duration-200" 
            onclick="return confirm('Remove adviser from this cluster?')">
        <i class="fas fa-user-times mr-1"></i>Remove
    </button>
</form>
<?php else: ?>
<button class="bg-primary hover:bg-blue-700 text-white py-1.5 px-3 rounded-lg text-xs font-medium flex items-center transition duration-200" 
        data-bs-toggle="modal" data-bs-target="#assignAdviserModal"
        data-cluster-id="<?= $cluster['id'] ?>"
        data-cluster-name="<?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?>">
    <i class="fas fa-plus mr-1"></i>Adviser
</button>
<?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <!-- Unassigned Groups Tab -->
                        <div class="tab-pane" id="unassigned" role="tabpanel">
                            <div class="flex justify-between items-center mb-6">
                                <h4 class="text-lg font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-users text-primary mr-2"></i>Unassigned Groups (Complete Groups of 5)
                                </h4>
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
                                <div>No complete groups of 5 available for assignment.</div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Faculty List Tab -->
                        <div class="tab-pane" id="faculty" role="tabpanel">
                            <div class="flex justify-between items-center mb-6">
                                <h4 class="text-lg font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Faculty List
                                </h4>
                                <div>
                                    <button class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition duration-200" data-bs-toggle="modal" data-bs-target="#createFacultyModal">
                                        <i class="fas fa-plus-circle mr-2"></i>Add New Faculty
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <?php 
                                // Reset faculty pointer
                                mysqli_data_seek($faculty, 0);
                                while ($fac = mysqli_fetch_assoc($faculty)): 
                                    // Check if faculty is already assigned to a cluster
                                    $assigned_check = mysqli_query($conn, "SELECT COUNT(*) FROM clusters WHERE faculty_id = " . $fac['id']);
                                    $assigned_count = mysqli_fetch_row($assigned_check)[0];
                                    $is_assigned = $assigned_count > 0;
                                ?>
                                <div class="faculty-item bg-white border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                    <div>
                                        <strong class="text-gray-900"><?= htmlspecialchars($fac['fullname']) ?></strong>
                                        <div class="text-gray-500 text-sm mt-1">
                                            <i class="fas fa-building mr-1"></i><?= htmlspecialchars($fac['department']) ?> 
                                            | <i class="fas fa-star mr-1"></i><?= htmlspecialchars($fac['expertise']) ?>
                                            <?php if (!empty($fac['email'])): ?>
                                            | <i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($fac['email']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <?php if (!$is_assigned): ?>
                                        <button class="bg-primary hover:bg-blue-700 text-white py-1.5 px-3 rounded-lg text-sm font-medium flex items-center transition duration-200" 
        data-bs-toggle="modal" data-bs-target="#assignFacultyToClusterModal"
        data-faculty-id="<?= $fac['id'] ?>" 
        data-faculty-name="<?= htmlspecialchars($fac['fullname']) ?>">
    <i class="fas fa-plus mr-1"></i>Assign to Cluster
</button>
                                        <?php else: ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-check mr-1"></i>Already Assigned
                                        </span>
                                        <?php endif; ?>
                                        <button class="bg-gray-50 hover:bg-gray-100 text-gray-700 py-1.5 px-3 rounded-lg text-sm font-medium flex items-center transition duration-200" 
                                                data-bs-toggle="modal" data-bs-target="#editFacultyModal"
                                                data-faculty-id="<?= $fac['id'] ?>"
                                                data-fullname="<?= htmlspecialchars($fac['fullname']) ?>"
                                                data-department="<?= htmlspecialchars($fac['department']) ?>"
                                                data-expertise="<?= htmlspecialchars($fac['expertise']) ?>"                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
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
    <!-- Create Cluster Modal -->
    <div class="modal fade" id="createClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="modal-title font-bold flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i>Create New Cluster
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Program</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="program" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Cluster Name/Number</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="cluster" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">School Year</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="school_year" placeholder="e.g., 2023-2024" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Capacity</label>
                            <input type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="capacity" min="5" step="5" required>
                            <p class="text-gray-500 text-xs mt-1">Must be a multiple of 5 (each group has 5 students)</p>
                        </div>
                    </div>
                    <div class="modal-footer p-4 border-t border-gray-200">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="create_cluster" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Create Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Cluster Modal -->
    <div class="modal fade" id="editClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="cluster_id" id="edit_cluster_id">
                    <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="modal-title font-bold flex items-center">
                            <i class="fas fa-edit mr-2"></i>Edit Cluster
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Program</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="program" id="edit_program" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Cluster Name/Number</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="cluster" id="edit_cluster" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">School Year</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="school_year" id="edit_school_year" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Capacity</label>
                            <input type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="capacity" id="edit_capacity" min="5" step="5" required>
                            <p class="text-gray-500 text-xs mt-1">Must be a multiple of 5 (each group has 5 students)</p>
                        </div>
                    </div>
                    <div class="modal-footer p-4 border-t border-gray-200">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_cluster" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Update Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Cluster Modal -->
    <div class="modal fade" id="deleteClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="cluster_id" id="delete_cluster_id">
                    <div class="modal-header bg-red-600 text-white p-4 rounded-t-lg">
                        <h5 class="modal-title font-bold flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6">
                        <p class="text-gray-700 mb-4">Are you sure you want to delete this cluster? This action will:</p>
                        <ul class="list-disc list-inside text-gray-600 mb-4">
                            <li>Remove all group assignments from this cluster</li>
                            <li>Remove faculty adviser assignment</li>
                            <li>Reset cluster assignments for all affected students</li>
                        </ul>
                        <p class="text-red-600 font-medium">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer p-4 border-t border-gray-200">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_cluster" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Delete Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <!-- Assign Adviser to Specific Cluster Modal -->
<div class="modal fade" id="assignAdviserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="cluster_id" id="assign_cluster_id">
                <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                    <h5 class="modal-title font-bold flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>Assign Adviser to Cluster
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body p-6">
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">Cluster: <span id="assign_cluster_name" class="font-semibold"></span></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Select Faculty Adviser</label>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="faculty_id" required>
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
                            <option value="<?= $fac['id'] ?>">
                                <?= htmlspecialchars($fac['fullname']) ?> - <?= htmlspecialchars($fac['department']) ?>
                            </option>
                            <?php endif; endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200">
                    <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_adviser" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Assign Adviser</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Faculty to Any Cluster Modal -->
<div class="modal fade" id="assignFacultyToClusterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="faculty_id" id="assign_faculty_id">
                <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                    <h5 class="modal-title font-bold flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>Assign Faculty to Cluster
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body p-6">
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">Faculty: <span id="assign_faculty_name" class="font-semibold"></span></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Select Cluster</label>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="cluster_id" required>
                            <option value="">-- Select Cluster --</option>
                            <?php
                            // Get clusters without advisers
                            $available_clusters = mysqli_query(
                                $conn,
                                "SELECT * FROM clusters WHERE faculty_id IS NULL OR faculty_id = 0 ORDER BY program, cluster"
                            );
                            
                            while ($cluster = mysqli_fetch_assoc($available_clusters)):
                            ?>
                            <option value="<?= $cluster['id'] ?>">
                                <?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200">
                    <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_adviser" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Assign to Cluster</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Assign Group to Cluster Modal -->
<div class="modal fade" id="assignGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="group_id" id="assign_group_id">
                <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                    <h5 class="modal-title font-bold flex items-center">
                        <i class="fas fa-link mr-2"></i>Assign Group to Cluster
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body p-6">
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">Group: <span id="assign_group_name" class="font-semibold"></span></p>
                        <p class="text-gray-600 text-sm mb-4">Program: <span id="assign_group_program"></span></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Select Cluster</label>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="cluster_id" required>
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
                                // Check if cluster has capacity and matches the group's program
                                $available_slots = $cluster['capacity'] - $cluster['student_count'];
                                if ($available_slots >= 5):
                            ?>
                            <option value="<?= $cluster['id'] ?>">
                                <?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?> 
                                (Available: <?= $available_slots ?> slots)
                            </option>
                            <?php endif; endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200">
                    <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_group" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Assign Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Create Faculty Modal -->
    <div class="modal fade" id="createFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="modal-title font-bold flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i>Add New Faculty
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Full Name</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="fullname" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Department</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="department" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Expertise/Field</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="expertise" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="email">
                        </div>
                    </div>
                    <div class="modal-footer p-4 border-t border-gray-200">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_faculty" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Add Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Faculty Modal -->
    <div class="modal fade" id="editFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="faculty_id" id="edit_faculty_id">
                    <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="modal-title font-bold flex items-center">
                            <i class="fas fa-edit mr-2"></i>Edit Faculty
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Full Name</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="fullname" id="edit_fullname" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Department</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="department" id="edit_department" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Expertise/Field</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="expertise" id="edit_expertise" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" name="email" id="edit_email">
                        </div>
                    </div>
                    <div class="modal-footer p-4 border-t border-gray-200">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_faculty" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Update Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     <!-- View Cluster Modal (shown when view_cluster parameter is set) -->
    <?php if (isset($_GET['view_cluster'])): ?>
    <div class="modal show" id="viewClusterModal" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white p-4 rounded-t-lg">
                    <h5 class="modal-title font-bold flex items-center">
                        <i class="fas fa-eye mr-2"></i>View Cluster Details
                    </h5>
                    <button type="button" class="btn-close text-white" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF'] ?>'">×</button>
                </div>
                <div class="modal-body p-6">
                    <?php if ($cluster_details): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h5 class="font-bold text-gray-900 mb-3">Cluster Information</h5>
                            <div class="space-y-2">
                                <p><strong>Program:</strong> <?= htmlspecialchars($cluster_details['program']) ?></p>
                                <p><strong>Cluster:</strong> <?= htmlspecialchars($cluster_details['cluster']) ?></p>
                                <p><strong>School Year:</strong> <?= htmlspecialchars($cluster_details['school_year']) ?></p>
                                <p><strong>Capacity:</strong> <?= $cluster_details['student_count'] ?> / <?= $cluster_details['capacity'] ?></p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <?php $percentage = $cluster_details['capacity'] > 0 ? ($cluster_details['student_count'] / $cluster_details['capacity']) * 100 : 0; ?>
                                    <div class="h-2 rounded-full <?= $percentage < 60 ? 'bg-success' : ($percentage < 90 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-900 mb-3">Adviser Information</h5>
                            <?php if ($cluster_details['faculty_id']): ?>
                            <div class="space-y-2">
                                <p><strong>Name:</strong> <?= htmlspecialchars($cluster_details['adviser_name']) ?></p>
                                <p><strong>Department:</strong> <?= htmlspecialchars($cluster_details['department']) ?></p>
                                <p><strong>Expertise:</strong> <?= htmlspecialchars($cluster_details['expertise']) ?></p>
                            </div>
                            <?php else: ?>
                            <p class="text-red-500">No adviser assigned to this cluster.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <h5 class="font-bold text-gray-900 mb-3">Students in this Cluster</h5>
                        <?php if (!empty($cluster_students)): ?>
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3">Student ID</th>
                                        <th class="px-4 py-3">Name</th>
                                        <th class="px-4 py-3">Program</th>
                                        <th class="px-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tbody>
    <?php foreach ($cluster_students as $student): ?>
    <tr class="border-b border-gray-200 hover:bg-gray-50">
        <td class="px-4 py-3"><?= htmlspecialchars($student['school_id']) ?></td>
        <td class="px-4 py-3"><?= htmlspecialchars($student['full_name']) ?></td>
        <td class="px-4 py-3"><?= htmlspecialchars($student['program']) ?></td>
        <td class="px-4 py-3">
            <form method="POST" class="inline">
                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                <input type="hidden" name="cluster_id" value="<?= $cluster_details['id'] ?>">
                <button type="submit" name="remove_student" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Remove this student from the cluster?')">
                    <i class="fas fa-times-circle mr-1"></i>Remove
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg">
                            No Groups assigned to this cluster yet.
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-red-500">Cluster not found.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer p-4 border-t border-gray-200">
                    <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF'] ?>'">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
        
        // Set the cluster ID and name in the form
        document.getElementById('assign_cluster_id').value = clusterId;
        document.getElementById('assign_cluster_name').textContent = clusterName;
        
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
        
        // Set the faculty ID and name in the form
        document.getElementById('assign_faculty_id').value = facultyId;
        document.getElementById('assign_faculty_name').textContent = facultyName;
        
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
    
    // Make the openAssignAdviserModal function available globally
    window.openAssignAdviserModal = openAssignAdviserModal;
});
</script>
   
</body>
</html>