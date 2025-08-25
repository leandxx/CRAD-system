<?php
session_start();
include('../includes/connection.php'); // Your DB connection

// Insert sample faculty data if table is empty
$checkFaculty = "SELECT COUNT(*) as count FROM faculty";
$result = mysqli_query($conn, $checkFaculty);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $insertFaculty = "INSERT INTO faculty (fullname, department, expertise) VALUES
        ('Dr. Maria Santos', 'Accounting', 'Financial Accounting and Auditing'),
        ('Prof. James Wilson', 'Information Technology', 'Web Development and Database Systems'),
        ('Dr. Lisa Chen', 'Hospitality Management', 'Hotel Operations and Management'),
        ('Prof. Robert Garcia', 'Criminology', 'Forensic Science and Criminal Investigation'),
        ('Dr. Sarah Johnson', 'Tourism', 'Eco-Tourism and Travel Management'),
        ('Dr. Michael Brown', 'Accounting', 'Taxation and Business Law'),
        ('Prof. Emily Williams', 'Information Technology', 'Cybersecurity and Network Administration'),
        ('Dr. David Lee', 'Hospitality Management', 'Food and Beverage Management'),
        ('Prof. Amanda Rodriguez', 'Criminology', 'Criminal Psychology and Behavior'),
        ('Dr. Jennifer Kim', 'Tourism', 'Tourism Planning and Development')";
    
    if (!mysqli_query($conn, $insertFaculty)) {
        die("Error inserting faculty data: " . mysqli_error($conn));
    }
}

// Fetch student data to create clusters - MODIFIED to include cluster "0"
$student_query = "SELECT DISTINCT program, school_year FROM student_profiles WHERE program IS NOT NULL AND school_year IS NOT NULL";
$student_result = mysqli_query($conn, $student_query);

if ($student_result && mysqli_num_rows($student_result) > 0) {
    while ($row = mysqli_fetch_assoc($student_result)) {
        $program = mysqli_real_escape_string($conn, $row['program']);
        $school_year = mysqli_real_escape_string($conn, $row['school_year']);
        
        // Check if cluster already exists for this course and year
        $check_cluster = "SELECT id FROM clusters WHERE program = '$program' AND school_year = '$school_year'";
        $cluster_result = mysqli_query($conn, $check_cluster);
        
        if (mysqli_num_rows($cluster_result) == 0) {
            // Get student count for this course/year
            $count_query = "SELECT COUNT(*) as count FROM student_profiles WHERE program = '$program' AND school_year = '$school_year'";
            $count_result = mysqli_query($conn, $count_query);
            $count_row = mysqli_fetch_assoc($count_result);
            $student_count = $count_row['count'];
            
            // Insert new cluster with cluster = 0 (unassigned)
            $insert_cluster = "INSERT INTO clusters (program, cluster, school_year, student_count, capacity, `status`) 
                              VALUES ('$program', '0', '$school_year', $student_count, 50, 'unassigned')";
            mysqli_query($conn, $insert_cluster);
        }
    }
}

// Fetch faculty data
$faculty_query = "SELECT * FROM faculty ORDER BY department, fullname";
$faculty_result = mysqli_query($conn, $faculty_query);
$faculty = array();

if ($faculty_result && mysqli_num_rows($faculty_result) > 0) {
    while ($row = mysqli_fetch_assoc($faculty_result)) {
        $faculty[] = $row;
    }
}

// ✅ Fetch clusters data - EXCLUDING unassigned clusters (cluster = 0 or empty)
$clusters_query = "SELECT c.*, f.fullname as faculty_name, f.department as faculty_department 
                   FROM clusters c 
                   LEFT JOIN faculty f ON c.faculty_id = f.id 
                   WHERE c.cluster != '0' AND c.cluster != '' 
                   ORDER BY c.program, c.cluster";
$clusters_result = mysqli_query($conn, $clusters_query);
$clusters = array();

if ($clusters_result && mysqli_num_rows($clusters_result) > 0) {
    while ($row = mysqli_fetch_assoc($clusters_result)) {
        $clusters[] = $row;
    }
}

// Fetch unassigned students (cluster = 0 or NULL)
$unassigned_query = "SELECT * FROM student_profiles WHERE cluster = '0' OR cluster IS NULL OR cluster = '' ORDER BY program, school_year";
$unassigned_result = mysqli_query($conn, $unassigned_query);
$unassigned_students = array();

if ($unassigned_result && mysqli_num_rows($unassigned_result) > 0) {
    while ($row = mysqli_fetch_assoc($unassigned_result)) {
        $unassigned_students[] = $row;
    }
}

// Group unassigned students by course and year
$unassigned_groups = array();
foreach ($unassigned_students as $student) {
    $key = $student['program'] . '|' . $student['school_year'];
    if (!isset($unassigned_groups[$key])) {
        $unassigned_groups[$key] = array(
            'program' => $student['program'],
            'school_year' => $student['school_year'],
            'students' => array(),
            'count' => 0
        );
    }
    $unassigned_groups[$key]['students'][] = $student;
    $unassigned_groups[$key]['count']++;
}

// Count statistics
$total_clusters = count($clusters);
$assigned_clusters = 0;
$pending_clusters = 0;
$unassigned_clusters = 0;

foreach ($clusters as $cluster) {
    if ($cluster['status'] == 'assigned') {
        $assigned_clusters++;
    } else if ($cluster['status'] == 'pending') {
        $pending_clusters++;
    } else if ($cluster['status'] == 'unassigned') {
        $unassigned_clusters++;
    }
}

$available_faculty = count($faculty);

// Handle form submission for assigning adviser - MODIFIED to update student_profiles
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adviser_assignment'])) {
    $cluster_id = mysqli_real_escape_string($conn, $_POST['cluster_id']);
    $faculty_id = mysqli_real_escape_string($conn, $_POST['faculty_id']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $cluster_number = mysqli_real_escape_string($conn, $_POST['cluster_number']);

    // Check for empty faculty_id
    if (empty($faculty_id)) {
        $error_message = "Please select an adviser.";
    } else {
        // First get cluster details
        $cluster_query = "SELECT * FROM clusters WHERE id = $cluster_id";
        $cluster_result = mysqli_query($conn, $cluster_query);

        if (!$cluster_result) {
            $error_message = "Error retrieving cluster data: " . mysqli_error($conn);
        } else {
            $cluster_data = mysqli_fetch_assoc($cluster_result);

            // Check if the entered cluster number already exists for this course and school year
            $check_existing_cluster = "SELECT id FROM clusters WHERE program = '{$cluster_data['program']}' 
                                      AND cluster = '$cluster_number' 
                                      AND school_year = '{$cluster_data['school_year']}'
                                      AND id != $cluster_id";
            $existing_result = mysqli_query($conn, $check_existing_cluster);

            if (!$existing_result) {
                $error_message = "Error checking existing clusters: " . mysqli_error($conn);
            } else if (mysqli_num_rows($existing_result) > 0) {
                $error_message = "Cluster number $cluster_number already exists for {$cluster_data['program']} in {$cluster_data['school_year']}. Please choose a different cluster number.";
            } else {
                // Update the cluster record with the new cluster number and faculty
                $update_cluster = "UPDATE clusters SET cluster = '$cluster_number', faculty_id = $faculty_id, `status` = 'assigned', assigned_date = CURDATE() WHERE id = $cluster_id";

                if (mysqli_query($conn, $update_cluster)) {
                    // Update all student_profiles with this course, school_year to the new cluster number
                    $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                                       WHERE program = '{$cluster_data['program']}' 
                                       AND school_year = '{$cluster_data['school_year']}'";

                    if (mysqli_query($conn, $update_students)) {
                        // Insert into assign_adviser table
                        $insert_assign = "INSERT INTO adviser_assignment (cluster_id, faculty_id, assigned_date, notes, cluster_number) 
                                         VALUES ($cluster_id, $faculty_id, CURDATE(), '$notes', '$cluster_number')";
                        mysqli_query($conn, $insert_assign);

                        // Refresh page to show updated data
                        header("Location: ".$_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $error_message = "Error updating student profiles: " . mysqli_error($conn);
                    }
                } else {
                    $error_message = "Error assigning adviser: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Handle form submission for editing cluster
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_cluster'])) {
    $cluster_id = mysqli_real_escape_string($conn, $_POST['cluster_id']);
    $faculty_id = mysqli_real_escape_string($conn, $_POST['faculty_id']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    $cluster_number = mysqli_real_escape_string($conn, $_POST['cluster_number']);

    // Get current cluster details
    $cluster_query = "SELECT * FROM clusters WHERE id = $cluster_id";
    $cluster_result = mysqli_query($conn, $cluster_query);
    $cluster_data = mysqli_fetch_assoc($cluster_result);

    // Check for duplicate cluster number in same course/year
    $check_existing_cluster = "SELECT id FROM clusters WHERE program = '{$cluster_data['program']}' 
                              AND cluster = '$cluster_number' 
                              AND school_year = '{$cluster_data['school_year']}'
                              AND id != $cluster_id";
    $existing_result = mysqli_query($conn, $check_existing_cluster);

    if (!$existing_result) {
        $error_message = "Error checking existing clusters: " . mysqli_error($conn);
    } else if (mysqli_num_rows($existing_result) > 0) {
        $error_message = "Cluster number $cluster_number already exists for {$cluster_data['program']} in {$cluster_data['school_year']}. Please choose a different cluster number.";
    } else {
        // Update cluster record
        $update_cluster = "UPDATE clusters SET faculty_id = $faculty_id, capacity = $capacity, cluster = '$cluster_number' WHERE id = $cluster_id";
        if (mysqli_query($conn, $update_cluster)) {
            // Update student_profiles to new cluster number
            $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                                WHERE program = '{$cluster_data['program']}' 
                                AND school_year = '{$cluster_data['school_year']}' 
                                AND cluster = '{$cluster_data['cluster']}'";
            mysqli_query($conn, $update_students);

            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Error updating cluster: " . mysqli_error($conn);
        }
    }
}

// Handle remove assignment (unassign cluster)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_assignment'])) {
    $cluster_id = mysqli_real_escape_string($conn, $_POST['cluster_id']);

    // Get cluster details
    $cluster_query = "SELECT * FROM clusters WHERE id = $cluster_id";
    $cluster_result = mysqli_query($conn, $cluster_query);
    $cluster_data = mysqli_fetch_assoc($cluster_result);

    // Unassign all students in this cluster
    $unassign_students = "UPDATE student_profiles SET cluster = '0' 
                         WHERE program = '{$cluster_data['program']}' 
                         AND school_year = '{$cluster_data['school_year']}' 
                         AND cluster = '{$cluster_data['cluster']}'";
    mysqli_query($conn, $unassign_students);

    // Reset cluster status and adviser
    $reset_cluster = "UPDATE clusters SET faculty_id = NULL, `status` = 'unassigned', assigned_date = NULL, cluster = '0' WHERE id = $cluster_id";
    mysqli_query($conn, $reset_cluster);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle creating new cluster from unassigned students
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_cluster'])) {
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $school_year = mysqli_real_escape_string($conn, $_POST['school_year']);
    $cluster_number = mysqli_real_escape_string($conn, $_POST['cluster_number']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    
    // Check if cluster number already exists for this course and year
    $check_cluster = "SELECT id FROM clusters WHERE program = '$program' AND cluster = '$cluster_number' AND school_year = '$school_year'";
    $result = mysqli_query($conn, $check_cluster);
    
    if (mysqli_num_rows($result) > 0) {
        $error_message = "Cluster number $cluster_number already exists for $program in $school_year. Please choose a different cluster number.";
    } else {
        // Get student count for this course/year
        $count_query = "SELECT COUNT(*) as count FROM student_profiles WHERE program = '$program' AND school_year = '$school_year'";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $student_count = $count_row['count'];
        
        // Create new cluster
        $insert_cluster = "INSERT INTO clusters (program, cluster, school_year, student_count, capacity, `status`) 
                          VALUES ('$program', '$cluster_number', '$school_year', $student_count, $capacity, 'pending')";
        
        if (mysqli_query($conn, $insert_cluster)) {
            // Update students with the new cluster number
            $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                               WHERE program = '$program' AND school_year = '$school_year'";
            mysqli_query($conn, $update_students);
            
            $success_message = "New cluster created successfully and students assigned.";
            header("Location: ".$_SERVER['PHP_SELF']."?success=".urlencode($success_message));
            exit();
        } else {
            $error_message = "Error creating cluster: " . mysqli_error($conn);
        }
    }
}

// Handle assigning students to cluster
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_students'])) {
    $student_ids = $_POST['student_ids'];
    $cluster_id = mysqli_real_escape_string($conn, $_POST['cluster_id']);
    
    // Get cluster details
    $cluster_query = "SELECT * FROM clusters WHERE id = $cluster_id";
    $cluster_result = mysqli_query($conn, $cluster_query);
    $cluster_data = mysqli_fetch_assoc($cluster_result);
    
    $program = $cluster_data['program'];
    $school_year = $cluster_data['school_year'];
    $cluster_number = $cluster_data['cluster'];
    
    // Update selected students
    if (!empty($student_ids)) {
        $ids_string = implode(",", array_map('intval', $student_ids));
        $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                           WHERE id IN ($ids_string) 
                           AND program = '$program' 
                           AND school_year = '$school_year'";
        
        if (mysqli_query($conn, $update_students)) {
            // Update student count in cluster
            $update_count = "UPDATE clusters SET student_count = student_count + " . count($student_ids) . " WHERE id = $cluster_id";
            mysqli_query($conn, $update_count);
            
            $success_message = count($student_ids) . " students assigned to cluster successfully.";
            header("Location: ".$_SERVER['PHP_SELF']."?success=".urlencode($success_message));
            exit();
        } else {
            $error_message = "Error assigning students: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Please select at least one student to assign.";
    }
}

// Handle converting unassigned cluster to assigned cluster
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['convert_cluster'])) {
    $cluster_id = mysqli_real_escape_string($conn, $_POST['cluster_id']);
    $cluster_number = mysqli_real_escape_string($conn, $_POST['cluster_number']);
    
    // Check if cluster number already exists
    $check_cluster = "SELECT id FROM clusters WHERE cluster = '$cluster_number' AND id != $cluster_id";
    $result = mysqli_query($conn, $check_cluster);
    
    if (mysqli_num_rows($result) > 0) {
        $error_message = "Cluster number $cluster_number already exists. Please choose a different cluster number.";
    } else {
        // Update cluster status and number
        $update_cluster = "UPDATE clusters SET cluster = '$cluster_number', status = 'pending' WHERE id = $cluster_id";
        
        if (mysqli_query($conn, $update_cluster)) {
            // Update students with the new cluster number
            $cluster_query = "SELECT * FROM clusters WHERE id = $cluster_id";
            $cluster_result = mysqli_query($conn, $cluster_query);
            $cluster_data = mysqli_fetch_assoc($cluster_result);
            
            $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                               WHERE program = '{$cluster_data['program']}' 
                               AND school_year = '{$cluster_data['school_year']}'";
            mysqli_query($conn, $update_students);
            
            $success_message = "Cluster converted successfully. You can now assign an adviser.";
            header("Location: ".$_SERVER['PHP_SELF']."?success=".urlencode($success_message));
            exit();
        } else {
            $error_message = "Error converting cluster: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adviser Assignment</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .scroll-container {
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }
        .notification-dot.pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #e5e7eb;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease-in-out;
        }
        .primary { color: #3b82f6; }
        .bg-primary { background-color: #3b82f6; }
        .success { color: #10b981; }
        .bg-success { background-color: #10b981; }
        .warning { color: #f59e0b; }
        .bg-warning { background-color: #f59e0b; }
        .secondary { color: #8b5cf6; }
        .bg-secondary { background-color: #8b5cf6; }
        .danger { color: #ef4444; }
        .bg-danger { background-color: #ef4444; }
        .modal {
            transition: opacity 0.25s ease;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .student-checkbox:checked + .student-item {
            background-color: #eff6ff;
            border-color: #3b82f6;
        }
        .unassigned-cluster {
            border-left: 4px solid #f59e0b;
        }
        .assigned-cluster {
            border-left: 4px solid #10b981;
        }
        .pending-cluster {
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans h-screen overflow-hidden">
    <div class="flex">
        <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        
        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6 scroll-container">              
            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-primary">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-primary mr-4">
                            <i class="fas fa-layer-group text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Clusters</h3>
                            <p class="text-2xl font-bold"><?php echo $total_clusters; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-success">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-success mr-4">
                            <i class="fas fa-user-check text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Assigned Clusters</h3>
                            <p class="text-2xl font-bold"><?php echo $assigned_clusters; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-warning">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-warning mr-4">
                            <i class="fas fa-user-clock text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Pending Assignments</h3>
                            <p class="text-2xl font-bold"><?php echo $pending_clusters; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-danger">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-danger mr-4">
                            <i class="fas fa-users-slash text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Unassigned Groups</h3>
                            <p class="text-2xl font-bold"><?php echo $unassigned_clusters; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm p-1 mb-6 flex border-b">
                <button id="clusters-tab" class="tab-button px-4 py-2 font-medium text-sm rounded-md mr-2 active" data-tab="clusters">
                    <i class="fas fa-layer-group mr-2"></i> All Clusters
                </button>
                <button id="unassigned-tab" class="tab-button px-4 py-2 font-medium text-sm rounded-md" data-tab="unassigned">
                    <i class="fas fa-users mr-2"></i> Unassigned Students
                    <?php if (count($unassigned_students) > 0): ?>
                    <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo count($unassigned_students); ?></span>
                    <?php endif; ?>
                </button>
            </div>
            
            <!-- Error message display -->
            <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.150a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Success message display -->
            <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $_GET['success']; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.150a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Clusters Tab Content -->
            <div id="clusters-content" class="tab-content active">
                <!-- Action Bar -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-4">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search clusters..." class="pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full md:w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        
                        <div>
                            <select id="statusFilter" onchange="filterByStatus()" class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary w-full md:w-48">
                                <option value="all">All Status</option>
                                <option value="assigned">Assigned</option>
                                <option value="pending">Pending</option>
                                <option value="unassigned">Unassigned</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <button onclick="toggleModal('assignmentModal')" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i> New Assignment
                        </button>
                    </div>
                </div>
                
                <!-- Clusters Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($clusters as $cluster): 
                        $percentage = ($cluster['capacity'] > 0) ? round(($cluster['student_count'] / $cluster['capacity']) * 100) : 0;
                        $status_class = '';
                        if ($cluster['status'] == 'assigned') $status_class = 'assigned-cluster';
                        else if ($cluster['status'] == 'pending') $status_class = 'pending-cluster';
                        else if ($cluster['status'] == 'unassigned') $status_class = 'unassigned-cluster';
                    ?>
                    <div class="cluster-card bg-white rounded-lg shadow-sm p-6 card-hover <?php echo $status_class; ?>" data-status="<?php echo $cluster['status']; ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    <?php echo $cluster['program']; ?>
                                    <?php if ($cluster['cluster'] === "0"): ?>
                                        <span class="text-orange-600">(Unassigned Group)</span>
                                    <?php else: ?>
                                        <?php echo ' ' . $cluster['cluster']; ?>
                                    <?php endif; ?>
                                </h3>
                                <p class="text-sm text-gray-500"><?php echo $cluster['school_year']; ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                if ($cluster['status'] == 'assigned') echo 'bg-success text-white';
                                elseif ($cluster['status'] == 'pending') echo 'bg-warning text-white';
                                else echo 'bg-danger text-white';
                                ?>">
                                <?php echo ucfirst($cluster['status']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Students: <?php echo $cluster['student_count'] . '/' . $cluster['capacity']; ?></span>
                                <span><?php echo $percentage; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill 
                                    <?php 
                                    if ($percentage == 100) echo 'bg-success';
                                    elseif ($percentage >= 50) echo 'bg-primary';
                                    else echo 'bg-warning';
                                    ?>" 
                                    style="width: <?php echo $percentage; ?>%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <?php if ($cluster['status'] == 'assigned' && $cluster['faculty_name']): ?>
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center mr-3">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium"><?php echo $cluster['faculty_name']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $cluster['faculty_department']; ?> Department</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Assigned on: <?php echo date('M j, Y', strtotime($cluster['assigned_date'])); ?></p>
                            <?php else: ?>
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mr-3">
                                    <i class="fas fa-question text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-400">No adviser assigned</p>
                                    <p class="text-xs text-gray-400">
                                        <?php echo $cluster['status'] == 'unassigned' ? 'Unassigned group' : 'Pending assignment'; ?>
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Created on: <?php echo date('M j, Y', strtotime($cluster['created_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <?php if ($cluster['status'] == 'assigned'): ?>
                            <button onclick="viewCluster(<?php echo $cluster['id']; ?>)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded text-sm transition-colors">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button onclick="editCluster(<?php echo $cluster['id']; ?>)" class="flex-1 bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <?php elseif ($cluster['status'] == 'pending'): ?>
                            <button onclick="assignAdviser(<?php echo $cluster['id']; ?>, '<?php echo $cluster['program']; ?>', false)" class="w-full bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-link mr-1"></i> Assign Adviser
                            </button>
                            <?php else: ?>
                            <button onclick="convertCluster(<?php echo $cluster['id']; ?>, '<?php echo $cluster['program']; ?>', '<?php echo $cluster['school_year']; ?>')" class="w-full bg-success hover:bg-green-700 text-white py-2 rounded text-sm transition-colors">
                                                                <i class="fas fa-plus-circle mr-1"></i> Create Cluster
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($clusters)): ?>
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <i class="fas fa-layer-group text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-500 mb-2">No clusters found</h3>
                    <p class="text-gray-400 mb-4">Start by creating your first cluster assignment</p>
                    <button onclick="toggleModal('assignmentModal')" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                        <i class="fas fa-plus-circle mr-2"></i> Create Cluster
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Unassigned Students Tab Content -->
            <div id="unassigned-content" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Unassigned Students</h2>
                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            <?php echo count($unassigned_students); ?> students
                        </span>
                    </div>
                    
                    <?php if (!empty($unassigned_groups)): ?>
                    <div class="space-y-6">
                        <?php foreach ($unassigned_groups as $group): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="font-semibold text-lg"><?php echo $group['program']; ?></h3>
                                    <p class="text-sm text-gray-500">School Year: <?php echo $group['school_year']; ?></p>
                                </div>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    <?php echo $group['count']; ?> students
                                </span>
                            </div>
                            
                            <div class="mb-4">
                                <button onclick="viewUnassignedStudents('<?php echo $group['program']; ?>', '<?php echo $group['school_year']; ?>')" 
                                        class="text-primary hover:text-blue-700 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i> View Students
                                </button>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button onclick="createClusterFromUnassigned('<?php echo $group['program']; ?>', '<?php echo $group['school_year']; ?>')" 
                                        class="bg-success hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                    <i class="fas fa-plus-circle mr-1"></i> Create Cluster
                                </button>
                                <button onclick="assignToExistingCluster('<?php echo $group['program']; ?>', '<?php echo $group['school_year']; ?>')" 
                                        class="bg-primary hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                    <i class="fas fa-link mr-1"></i> Assign to Existing Cluster
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-500">No unassigned students</h3>
                        <p class="text-gray-400">All students have been assigned to clusters</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Cluster Modal -->
    <div id="createClusterModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/3 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold">Create New Cluster</h3>
            </div>
            <form method="POST" action="">
                <div class="p-6 space-y-4">
                    <input type="hidden" id="create_program" name="program">
                    <input type="hidden" id="create_school_year" name="school_year">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                        <input type="text" name="cluster_number" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <input type="number" name="capacity" min="1" value="50" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('createClusterModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" name="create_cluster" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Create Cluster</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Adviser Modal -->
    <div id="assignmentModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/2 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold">Assign Adviser</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="cluster_id" name="cluster_id">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course & Year</label>
                        <input type="text" id="program_info" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                        <input type="text" name="cluster_number" id="cluster_number" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Adviser</label>
                        <select name="faculty_id" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                            <option value="">-- Select Adviser --</option>
                            <?php foreach ($faculty as $adviser): ?>
                            <option value="<?php echo $adviser['id']; ?>"><?php echo $adviser['fullname']; ?> - <?php echo $adviser['department']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('assignmentModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" name="adviser_assignment" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Assign Adviser</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Cluster Modal -->
    <div id="editClusterModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/2 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold">Edit Cluster</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_cluster_id" name="cluster_id">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course & Year</label>
                        <input type="text" id="edit_program_info" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                        <input type="text" name="cluster_number" id="edit_cluster_number" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <input type="number" name="capacity" id="edit_capacity" min="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Adviser</label>
                        <select name="faculty_id" id="edit_faculty_id" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">-- No Adviser --</option>
                            <?php foreach ($faculty as $adviser): ?>
                            <option value="<?php echo $adviser['id']; ?>"><?php echo $adviser['fullname']; ?> - <?php echo $adviser['department']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('editClusterModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" name="edit_cluster" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Students Modal -->
    <div id="viewStudentsModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-semibold" id="viewStudentsTitle">Students</h3>
                <button onclick="toggleModal('viewStudentsModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <input type="text" id="studentSearch" placeholder="Search students..." class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div id="studentsList" class="space-y-2 max-h-96 overflow-y-auto">
                    <!-- Students will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Assign to Existing Cluster Modal -->
    <div id="assignToClusterModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/2 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold">Assign Students to Cluster</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="assign_program" name="program">
                <input type="hidden" id="assign_school_year" name="school_year">
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Cluster</label>
                        <select name="cluster_id" id="clusterSelect" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                            <option value="">-- Select Cluster --</option>
                            <?php foreach ($clusters as $cluster): ?>
                                <?php if ($cluster['status'] != 'unassigned'): ?>
                                <option value="<?php echo $cluster['id']; ?>" data-program="<?php echo $cluster['program']; ?>" data-year="<?php echo $cluster['school_year']; ?>">
                                    <?php echo $cluster['program'] . ' ' . $cluster['cluster'] . ' (' . $cluster['school_year'] . ')'; ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="studentSelection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Students to Assign</label>
                        <div class="border rounded-lg p-4 max-h-64 overflow-y-auto">
                            <div id="studentCheckboxes" class="space-y-2">
                                <!-- Student checkboxes will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('assignToClusterModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" name="assign_students" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Assign Students</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Convert Cluster Modal -->
    <div id="convertClusterModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/3 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4">
                <h3 class="text-xl font-semibold">Convert to Cluster</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="convert_cluster_id" name="cluster_id">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course & Year</label>
                        <input type="text" id="convert_program_info" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                        <input type="text" name="cluster_number" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('convertClusterModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" name="convert_cluster" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Convert</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked tab
                button.classList.add('active');
                
                // Hide all tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show the selected tab content
                const tabId = button.getAttribute('data-tab');
                document.getElementById(`${tabId}-content`).classList.add('active');
            });
        });

        // Modal functionality
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const clusterCards = document.querySelectorAll('.cluster-card');
            
            clusterCards.forEach(card => {
                const program = card.querySelector('h3').textContent.toLowerCase();
                const year = card.querySelector('p').textContent.toLowerCase();
                
                if (program.includes(searchTerm) || year.includes(searchTerm)) {
                    card.parentElement.style.display = 'block';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });

        // Filter by status
        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const clusterCards = document.querySelectorAll('.cluster-card');
            
            clusterCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                
                if (status === 'all' || cardStatus === status) {
                    card.parentElement.style.display = 'block';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        }

        // Assign adviser function
        function assignAdviser(clusterId, program, isNew = false) {
            document.getElementById('cluster_id').value = clusterId;
            document.getElementById('program_info').value = program;
            
            if (isNew) {
                document.getElementById('cluster_number').value = '';
            } else {
                // Get the current cluster number if editing
                const clusterCard = document.querySelector(`.cluster-card [data-id="${clusterId}"]`);
                if (clusterCard) {
                    const clusterNumber = clusterCard.getAttribute('data-cluster');
                    document.getElementById('cluster_number').value = clusterNumber;
                }
            }
            
            toggleModal('assignmentModal');
        }

        // Edit cluster function
        function editCluster(clusterId) {
            // You would typically fetch cluster details via AJAX here
            // For now, we'll just show the modal
            toggleModal('editClusterModal');
        }

        // View cluster function
        function viewCluster(clusterId) {
            // You would typically fetch cluster details via AJAX here
            alert('View cluster details for ID: ' + clusterId);
        }

        // Create cluster from unassigned students
        function createClusterFromUnassigned(program, schoolYear) {
            document.getElementById('create_program').value = program;
            document.getElementById('create_school_year').value = schoolYear;
            toggleModal('createClusterModal');
        }

        // View unassigned students
        function viewUnassignedStudents(program, schoolYear) {
            // You would typically fetch students via AJAX here
            // For demonstration, we'll just show a message
            document.getElementById('viewStudentsTitle').textContent = `Students - ${program} (${schoolYear})`;
            document.getElementById('studentsList').innerHTML = `<p class="text-gray-500">Loading students for ${program} ${schoolYear}...</p>`;
            toggleModal('viewStudentsModal');
            
            // Simulate loading students
            setTimeout(() => {
                // This would be replaced with actual data from your server
                document.getElementById('studentsList').innerHTML = `
                    <div class="p-3 border rounded flex justify-between items-center">
                        <div>
                            <p class="font-medium">John Doe</p>
                            <p class="text-sm text-gray-500">ID: 20230001</p>
                        </div>
                        <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">Unassigned</span>
                    </div>
                    <div class="p-3 border rounded flex justify-between items-center">
                        <div>
                            <p class="font-medium">Jane Smith</p>
                            <p class="text-sm text-gray-500">ID: 20230002</p>
                        </div>
                        <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">Unassigned</span>
                    </div>
                `;
            }, 500);
        }

        // Assign to existing cluster
        function assignToExistingCluster(program, schoolYear) {
            document.getElementById('assign_program').value = program;
            document.getElementById('assign_school_year').value = schoolYear;
            toggleModal('assignToClusterModal');
        }

        // Convert cluster function
        function convertCluster(clusterId, program, schoolYear) {
            document.getElementById('convert_cluster_id').value = clusterId;
            document.getElementById('convert_program_info').value = `${program} (${schoolYear})`;
            toggleModal('convertClusterModal');
        }

        // Cluster selection change handler
        document.getElementById('clusterSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const program = selectedOption.getAttribute('data-program');
            const year = selectedOption.getAttribute('data-year');
            
            if (program && year) {
                // Show student selection area
                document.getElementById('studentSelection').classList.remove('hidden');
                
                // Load students for this course and year (this would be an AJAX call in a real implementation)
                document.getElementById('studentCheckboxes').innerHTML = `
                    <div class="p-3 border rounded">
                        <label class="flex items-center">
                            <input type="checkbox" name="student_ids[]" value="1" class="student-checkbox mr-3">
                            <div>
                                <p class="font-medium">John Doe</p>
                                <p class="text-sm text-gray-500">ID: 20230001</p>
                            </div>
                        </label>
                    </div>
                `;
            } else {
                document.getElementById('studentSelection').classList.add('hidden');
            }
        });

        // Student search functionality
        document.getElementById('studentSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const studentItems = document.querySelectorAll('#studentsList > div');
            
            studentItems.forEach(item => {
                const studentName = item.querySelector('p.font-medium').textContent.toLowerCase();
                const studentId = item.querySelector('p.text-sm').textContent.toLowerCase();
                
                if (studentName.includes(searchTerm) || studentId.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>