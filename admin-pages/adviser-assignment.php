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
        
        // First, unassign all students from this cluster
        $sql = "UPDATE student_profiles SET cluster = NULL, faculty_id = NULL WHERE cluster = $cluster_id";
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

    // Assign student
    if (isset($_POST['assign_student'])) {
        $student_id = (int) $_POST['student_id'];
        $cluster_id = (int) $_POST['cluster_id'];

        // Update student cluster + faculty assignment
        $sql = "UPDATE student_profiles 
                SET cluster = $cluster_id, 
                    faculty_id = (SELECT faculty_id FROM clusters WHERE id = $cluster_id) 
                WHERE id = $student_id";
        mysqli_query($conn, $sql);

        // Update student count in cluster
        $sql = "UPDATE clusters SET student_count = student_count + 1 WHERE id = $cluster_id";
        mysqli_query($conn, $sql);
    }
    
    // Remove student from cluster
    if (isset($_POST['remove_student'])) {
        $student_id = (int) $_POST['student_id'];
        $cluster_id = (int) $_POST['cluster_id'];

        // Get student info first
        $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cluster FROM student_profiles WHERE id = $student_id"));
        
        if ($student && $student['cluster'] == $cluster_id) {
            // Remove student from cluster
            $sql = "UPDATE student_profiles 
                    SET cluster = NULL, faculty_id = NULL 
                    WHERE id = $student_id";
            mysqli_query($conn, $sql);

            // Update student count in cluster
            $sql = "UPDATE clusters SET student_count = student_count - 1 WHERE id = $cluster_id";
            mysqli_query($conn, $sql);
        }
    }
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if we need to view cluster details
$cluster_details = null;
$cluster_students = [];
if (isset($_GET['view_cluster'])) {
    $cluster_id = (int) $_GET['view_cluster'];
    $cluster_details = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT c.*, f.fullname AS adviser_name 
         FROM clusters c 
         LEFT JOIN faculty f ON c.faculty_id = f.id 
         WHERE c.id = $cluster_id"));
    
    if ($cluster_details) {
        $cluster_students = mysqli_query($conn,
            "SELECT * FROM student_profiles 
             WHERE cluster = $cluster_id 
             ORDER BY full_name");
    }
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

$unassigned_students = mysqli_query(
    $conn,
    "SELECT * FROM student_profiles 
     WHERE faculty_id IS NULL OR faculty_id = 0 
     ORDER BY program, full_name"
);

// Get statistics
$total_clusters     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters"))[0];
$assigned_clusters  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM clusters WHERE status = 'assigned'"))[0];
$total_students     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles"))[0];
$assigned_students  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM student_profiles WHERE faculty_id IS NOT NULL AND faculty_id != 0"))[0];
$total_faculty      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM faculty"))[0];
$assigned_faculty   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT faculty_id) FROM clusters WHERE faculty_id IS NOT NULL"))[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cluster Adviser Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            background-color: #f5f7fb;
            color: #343a40;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary);
        }
        
        .stats-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            font-size: 1.8rem;
            opacity: 0.8;
        }
        
        .tab-content {
            padding: 25px;
            border: 1px solid #e1e5eb;
            border-top: none;
            min-height: 500px;
            border-radius: 0 0 12px 12px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-tabs .nav-link {
            border: none;
            padding: 12px 20px;
            font-weight: 600;
            color: #6c757d;
            border-radius: 0;
            position: relative;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: transparent;
            border-bottom: 3px solid var(--primary);
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary);
        }
        
        .cluster-card {
            margin-bottom: 20px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        
        .cluster-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .student-item, .faculty-item {
            padding: 15px;
            border-bottom: 1px solid #edf2f9;
            transition: background-color 0.2s;
            border-radius: 8px;
            margin-bottom: 10px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        
        .student-item:hover, .faculty-item:hover {
            background-color: #f8faff;
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
        }
        
        .modal-header {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .page-header {
            padding-bottom: 15px;
            margin-bottom: 25px;
            border-bottom: 1px solid #e1e5eb;
        }
        
        .badge-success {
            background-color: #4cc9f0;
        }
        
        .badge-warning {
            background-color: #f72585;
        }
        
        .cluster-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .action-btn {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">

    <div class="flex min-h-screen">
        <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>
        
        <div class="flex-1 overflow-y-auto p-6">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card stats-card bg-primary text-white mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Clusters</h6>
                                <h3 class="mb-0"><?= $total_clusters ?></h3>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-success text-white mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Assigned Clusters</h6>
                                <h3 class="mb-0"><?= $assigned_clusters ?></h3>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-info text-white mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Students</h6>
                                <h3 class="mb-0"><?= $total_students ?></h3>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-warning text-white mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Assigned Students</h6>
                                <h3 class="mb-0"><?= $assigned_students ?></h3>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-secondary text-white mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Faculty</h6>
                                <h3 class="mb-0"><?= $total_faculty ?></h3>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-danger text-white mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Assigned Faculty</h6>
                                <h3 class="mb-0"><?= $assigned_faculty ?></h3>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage" type="button" role="tab">
                            <i class="fas fa-layer-group me-2"></i>Manage Clusters
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="unassigned-tab" data-bs-toggle="tab" data-bs-target="#unassigned" type="button" role="tab">
                            <i class="fas fa-users me-2"></i>Unassigned Students
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="faculty-tab" data-bs-toggle="tab" data-bs-target="#faculty" type="button" role="tab">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Faculty List
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="myTabContent">
                    <!-- Manage Clusters Tab -->
                    <div class="tab-pane fade show active" id="manage" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0"><i class="fas fa-layer-group text-primary me-2"></i>Manage Clusters</h3>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClusterModal">
                                <i class="fas fa-plus-circle me-1"></i>Create New Cluster
                            </button>
                        </div>
                        
                        <div class="row mt-4">
                            <?php while ($cluster = mysqli_fetch_assoc($clusters)): 
                                $percentage = $cluster['capacity'] > 0 ? ($cluster['student_count'] / $cluster['capacity']) * 100 : 0;
                                $progress_color = $percentage < 60 ? 'bg-success' : ($percentage < 90 ? 'bg-warning' : 'bg-danger');
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card cluster-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="card-title mb-0"><?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?></h5>
                                            <span class="badge bg-<?= $cluster['status'] == 'assigned' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($cluster['status']) ?>
                                            </span>
                                        </div>
                                        <h6 class="card-subtitle mb-3 text-muted"><?= htmlspecialchars($cluster['school_year']) ?></h6>
                                        
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small>Students: <?= $cluster['student_count'] ?> / <?= $cluster['capacity'] ?></small>
                                                <small><?= round($percentage) ?>%</small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar <?= $progress_color ?>" role="progressbar" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text">
                                            <i class="fas fa-user-tie me-2 text-muted"></i>
                                            <?= $cluster['adviser_name'] ? htmlspecialchars($cluster['adviser_name']) : '<span class="text-danger">Not assigned</span>' ?>
                                        </p>
                                        
                                        <div class="cluster-actions">
                                            <button class="btn btn-sm btn-outline-info action-btn" 
                                                    onclick="window.location.href='?view_cluster=<?= $cluster['id'] ?>#manage'">
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                            
                                            <button class="btn btn-sm btn-outline-secondary action-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#editClusterModal"
                                                    data-cluster-id="<?= $cluster['id'] ?>"
                                                    data-program="<?= htmlspecialchars($cluster['program']) ?>"
                                                    data-cluster="<?= htmlspecialchars($cluster['cluster']) ?>"
                                                    data-school-year="<?= htmlspecialchars($cluster['school_year']) ?>"
                                                    data-capacity="<?= $cluster['capacity'] ?>">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            
                                            <button class="btn btn-sm btn-outline-danger action-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteClusterModal"
                                                    data-cluster-id="<?= $cluster['id'] ?>">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                            
                                            <?php if ($cluster['faculty_id']): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="cluster_id" value="<?= $cluster['id'] ?>">
                                                <button type="submit" name="remove_adviser" class="btn btn-sm btn-outline-warning action-btn" 
                                                        onclick="return confirm('Remove adviser from this cluster?')">
                                                    <i class="fas fa-user-times me-1"></i>Remove
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary action-btn" data-bs-toggle="modal" data-bs-target="#assignAdviserModal" 
                                                    data-cluster-id="<?= $cluster['id'] ?>">
                                                <i class="fas fa-plus me-1"></i>Adviser
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <!-- Unassigned Students Tab -->
                    <div class="tab-pane fade" id="unassigned" role="tabpanel">
                        <h3 class="mb-4"><i class="fas fa-users text-primary me-2"></i>Unassigned Students</h3>
                        
                        <?php if (mysqli_num_rows($unassigned_students) > 0): ?>
                        <div class="list-group">
                            <?php while ($student = mysqli_fetch_assoc($unassigned_students)): ?>
                            <div class="list-group-item student-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($student['full_name']) ?></strong>
                                    <div class="text-muted">
                                        <i class="fas fa-id-card me-1"></i><?= htmlspecialchars($student['school_id']) ?> 
                                        | <i class="fas fa-graduation-cap me-1"></i><?= htmlspecialchars($student['program']) ?>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignStudentModal" 
                                    data-student-id="<?= $student['id'] ?>" 
                                    data-student-name="<?= htmlspecialchars($student['full_name']) ?>" 
                                    data-student-program="<?= htmlspecialchars($student['program']) ?>">
                                    <i class="fas fa-plus me-1"></i>Assign to Cluster
                                </button>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-info-circle me-2 fa-lg"></i>
                            <div>All students have been assigned to clusters.</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Faculty List Tab -->
                    <div class="tab-pane fade" id="faculty" role="tabpanel">
                        <h3 class="mb-4"><i class="fas fa-chalkboard-teacher text-primary me-2"></i>Faculty List</h3>
                        
                        <div class="list-group">
                            <?php while ($fac = mysqli_fetch_assoc($faculty)): 
                                // Check if faculty is already assigned to a cluster
                                $assigned_check = mysqli_query($conn, "SELECT COUNT(*) FROM clusters WHERE faculty_id = " . $fac['id']);
                                $assigned_count = mysqli_fetch_row($assigned_check)[0];
                                $is_assigned = $assigned_count > 0;
                            ?>
                            <div class="list-group-item faculty-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($fac['fullname']) ?></strong>
                                    <div class="text-muted">
                                        <i class="fas fa-building me-1"></i><?= htmlspecialchars($fac['department']) ?> 
                                        | <i class="fas fa-star me-1"></i><?= htmlspecialchars($fac['expertise']) ?>
                                    </div>
                                </div>
                                <div>
                                    <?php if (!$is_assigned): ?>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignAdviserToClusterModal" 
                                        data-faculty-id="<?= $fac['id'] ?>" 
                                        data-faculty-name="<?= htmlspecialchars($fac['fullname']) ?>">
                                        <i class="fas fa-plus me-1"></i>Assign to Cluster
                                    </button>
                                    <?php else: ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Already Assigned</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create Cluster Modal -->
    <div class="modal fade" id="createClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Cluster</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Program</label>
                            <input type="text" class="form-control" name="program" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cluster Name/Number</label>
                            <input type="text" class="form-control" name="cluster" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">School Year</label>
                            <input type="text" class="form-control" name="school_year" placeholder="e.g., 2023-2024" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="50" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_cluster" class="btn btn-primary">Create Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Assign Adviser Modal -->
    <div class="modal fade" id="assignAdviserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-tie me-2"></i>Assign Adviser to Cluster</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="cluster_id" id="cluster_id">
                        <div class="mb-3">
                            <label class="form-label">Select Adviser</label>
                            <select class="form-select" name="faculty_id" required>
                                <option value="">Choose an adviser...</option>
                                <?php 
                                // Reset faculty pointer and loop again
                                mysqli_data_seek($faculty, 0);
                                while ($fac = mysqli_fetch_assoc($faculty)): 
                                ?>
                                <option value="<?= $fac['id'] ?>"><?= htmlspecialchars($fac['fullname']) ?> (<?= htmlspecialchars($fac['department']) ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="assign_adviser" class="btn btn-primary">Assign Adviser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Assign Student to Cluster Modal -->
    <div class="modal fade" id="assignStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-graduate me-2"></i>Assign Student to Cluster</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="assign_student_id">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <input type="text" class="form-control" id="assign_student_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program</label>
                            <input type="text" class="form-control" id="assign_student_program" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Cluster</label>
                            <select class="form-select" name="cluster_id" required>
                                <option value="">Choose a cluster...</option>
                                <?php 
                                // Reset clusters pointer and loop again
                                mysqli_data_seek($clusters, 0);
                                while ($cluster = mysqli_fetch_assoc($clusters)): 
                                ?>
                                <?php if ($cluster['student_count'] < $cluster['capacity']): ?>
                                <option value="<?= $cluster['id'] ?>"><?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?> (<?= $cluster['student_count'] ?>/<?= $cluster['capacity'] ?>)</option>
                                <?php endif; ?>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="assign_student" class="btn btn-primary">Assign to Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Assign Adviser to Cluster Modal (from Faculty tab) -->
    <div class="modal fade" id="assignAdviserToClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-tie me-2"></i>Assign Adviser to Cluster</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="faculty_id" id="faculty_id">
                        <div class="mb-3">
                            <label class="form-label">Adviser</label>
                            <input type="text" class="form-control" id="faculty_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Cluster</label>
                            <select class="form-select" name="cluster_id" required>
                                <option value="">Choose a cluster...</option>
                                <?php 
                                // Reset clusters pointer and loop again
                                mysqli_data_seek($clusters, 0);
                                while ($cluster = mysqli_fetch_assoc($clusters)): 
                                ?>
                                <?php if (!$cluster['faculty_id']): ?>
                                <option value="<?= $cluster['id'] ?>"><?= htmlspecialchars($cluster['program']) ?> - Cluster <?= htmlspecialchars($cluster['cluster']) ?></option>
                                <?php endif; ?>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="assign_adviser" class="btn btn-primary">Assign to Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Cluster Modal -->
    <?php if (isset($_GET['view_cluster'])): ?>
    <div class="modal fade show" id="viewClusterModal" tabindex="-1" style="display: block; padding-right: 17px;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Cluster Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>'"></button>
                </div>
                <div class="modal-body">
                    <?php if ($cluster_details): ?>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4><?= htmlspecialchars($cluster_details['program']) ?> - Cluster <?= htmlspecialchars($cluster_details['cluster']) ?></h4>
                            <p class="text-muted">School Year: <?= htmlspecialchars($cluster_details['school_year']) ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-<?= $cluster_details['status'] == 'assigned' ? 'success' : 'warning' ?>">
                                <?= ucfirst($cluster_details['status']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Capacity</h6>
                                    <h3><?= $cluster_details['student_count'] ?> / <?= $cluster_details['capacity'] ?></h3>
                                    <?php $percentage = $cluster_details['capacity'] > 0 ? ($cluster_details['student_count'] / $cluster_details['capacity']) * 100 : 0; ?>
                                    <div class="progress mt-2">
                                        <div class="progress-bar <?= $percentage < 60 ? 'bg-success' : ($percentage < 90 ? 'bg-warning' : 'bg-danger') ?>" 
                                             role="progressbar" 
                                             style="width: <?= $percentage ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Adviser</h6>
                                    <?php if ($cluster_details['adviser_name']): ?>
                                    <h5><?= htmlspecialchars($cluster_details['adviser_name']) ?></h5>
                                    <p class="text-muted mb-0">Assigned on: <?= date('M j, Y', strtotime($cluster_details['assigned_date'])) ?></p>
                                    <?php else: ?>
                                    <p class="text-danger">No adviser assigned</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Students in this Cluster</h5>
                    <?php if (mysqli_num_rows($cluster_students) > 0): ?>
                    <div class="list-group">
                        <?php while ($student = mysqli_fetch_assoc($cluster_students)): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($student['full_name']) ?></strong>
                                <div class="text-muted"><?= htmlspecialchars($student['school_id']) ?></div>
                            </div>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                <input type="hidden" name="cluster_id" value="<?= $cluster_details['id'] ?>">
                                <button type="submit" name="remove_student" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Remove this student from the cluster?')">
                                    <i class="fas fa-times me-1"></i>Remove
                                </button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No students assigned to this cluster yet.
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Cluster not found!
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>'">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <!-- Edit Cluster Modal -->
    <div class="modal fade" id="editClusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Cluster</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="cluster_id" id="edit_cluster_id">
                        <div class="mb-3">
                            <label class="form-label">Program</label>
                            <input type="text" class="form-control" name="program" id="edit_program" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cluster Name/Number</label>
                            <input type="text" class="form-control" name="cluster" id="edit_cluster" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">School Year</label>
                            <input type="text" class="form-control" name="school_year" id="edit_school_year" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" id="edit_capacity" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_cluster" class="btn btn-primary">Update Cluster</button>
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
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Cluster</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="cluster_id" id="delete_cluster_id">
                        <p>Are you sure you want to delete this cluster?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This will remove all students from this cluster and cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_cluster" class="btn btn-danger">Delete Cluster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle modal data passing
        document.addEventListener('DOMContentLoaded', function() {
            // Assign Adviser Modal
            var assignAdviserModal = document.getElementById('assignAdviserModal');
            assignAdviserModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var clusterId = button.getAttribute('data-cluster-id');
                var modal = this;
                modal.querySelector('#cluster_id').value = clusterId;
            });

            // Assign Student Modal
            var assignStudentModal = document.getElementById('assignStudentModal');
            assignStudentModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var studentId = button.getAttribute('data-student-id');
                var studentName = button.getAttribute('data-student-name');
                var studentProgram = button.getAttribute('data-student-program');
                
                var modal = this;
                modal.querySelector('#assign_student_id').value = studentId;
                modal.querySelector('#assign_student_name').value = studentName;
                modal.querySelector('#assign_student_program').value = studentProgram;
            });

            // Assign Adviser to Cluster Modal (from Faculty tab)
            var assignAdviserToClusterModal = document.getElementById('assignAdviserToClusterModal');
            assignAdviserToClusterModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var facultyId = button.getAttribute('data-faculty-id');
                var facultyName = button.getAttribute('data-faculty-name');
                
                var modal = this;
                modal.querySelector('#faculty_id').value = facultyId;
                modal.querySelector('#faculty_name').value = facultyName;
            });

            // Edit Cluster Modal
            var editClusterModal = document.getElementById('editClusterModal');
            editClusterModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var clusterId = button.getAttribute('data-cluster-id');
                var program = button.getAttribute('data-program');
                var cluster = button.getAttribute('data-cluster');
                var schoolYear = button.getAttribute('data-school-year');
                var capacity = button.getAttribute('data-capacity');
                
                var modal = this;
                modal.querySelector('#edit_cluster_id').value = clusterId;
                modal.querySelector('#edit_program').value = program;
                modal.querySelector('#edit_cluster').value = cluster;
                modal.querySelector('#edit_school_year').value = schoolYear;
                modal.querySelector('#edit_capacity').value = capacity;
            });

            // Delete Cluster Modal
            var deleteClusterModal = document.getElementById('deleteClusterModal');
            deleteClusterModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var clusterId = button.getAttribute('data-cluster-id');
                
                var modal = this;
                modal.querySelector('#delete_cluster_id').value = clusterId;
            });

            // Close view modal if open
            <?php if (isset($_GET['view_cluster'])): ?>
            var viewModal = new bootstrap.Modal(document.getElementById('viewClusterModal'));
            viewModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>                                