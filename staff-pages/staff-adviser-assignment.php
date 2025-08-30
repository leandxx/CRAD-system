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
$student_query = "SELECT DISTINCT course, cluster, school_year FROM student_profiles WHERE course IS NOT NULL AND school_year IS NOT NULL";
$student_result = mysqli_query($conn, $student_query);

if ($student_result && mysqli_num_rows($student_result) > 0) {
    while ($row = mysqli_fetch_assoc($student_result)) {
        $course = mysqli_real_escape_string($conn, $row['course']);
        $cluster = mysqli_real_escape_string($conn, $row['cluster']);
        $school_year = mysqli_real_escape_string($conn, $row['school_year']);
        
        // Check if cluster already exists - MODIFIED to handle cluster "0"
        $check_cluster = "SELECT id FROM clusters WHERE course = '$course' AND cluster = '$cluster' AND school_year = '$school_year'";
        $cluster_result = mysqli_query($conn, $check_cluster);
        
        if (mysqli_num_rows($cluster_result) == 0) {
            // Get student count for this cluster
            $count_query = "SELECT COUNT(*) as count FROM student_profiles WHERE course = '$course' AND cluster = '$cluster' AND school_year = '$school_year'";
            $count_result = mysqli_query($conn, $count_query);
            $count_row = mysqli_fetch_assoc($count_result);
            $student_count = $count_row['count'];
            
            // Insert new cluster - MODIFIED to handle cluster "0"
            $cluster_value = ($cluster === "0") ? "0" : $cluster;
            $insert_cluster = "INSERT INTO clusters (course, cluster, school_year, student_count, capacity, `status`) VALUES ('$course', '$cluster_value', '$school_year', $student_count, 50, 'pending')";
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

// Fetch clusters data
$clusters_query = "SELECT c.*, f.fullname as faculty_name, f.department as faculty_department 
                   FROM clusters c 
                   LEFT JOIN faculty f ON c.faculty_id = f.id 
                   ORDER BY c.course, c.cluster";
$clusters_result = mysqli_query($conn, $clusters_query);
$clusters = array();

if ($clusters_result && mysqli_num_rows($clusters_result) > 0) {
    while ($row = mysqli_fetch_assoc($clusters_result)) {
        $clusters[] = $row;
    }
}

// Fetch unassigned students (cluster = 0)
$unassigned_query = "SELECT * FROM student_profiles WHERE cluster = '0' OR cluster IS NULL OR cluster = '' ORDER BY course, school_year";
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
    $key = $student['course'] . '|' . $student['school_year'];
    if (!isset($unassigned_groups[$key])) {
        $unassigned_groups[$key] = array(
            'course' => $student['course'],
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

foreach ($clusters as $cluster) {
    if ($cluster['status'] == 'assigned') {
        $assigned_clusters++;
    } else {
        $pending_clusters++;
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
            $check_existing_cluster = "SELECT id FROM clusters WHERE course = '{$cluster_data['course']}' 
                                      AND cluster = '$cluster_number' 
                                      AND school_year = '{$cluster_data['school_year']}'
                                      AND id != $cluster_id";
            $existing_result = mysqli_query($conn, $check_existing_cluster);

            if (!$existing_result) {
                $error_message = "Error checking existing clusters: " . mysqli_error($conn);
            } else if (mysqli_num_rows($existing_result) > 0) {
                $error_message = "Cluster number $cluster_number already exists for {$cluster_data['course']} in {$cluster_data['school_year']}. Please choose a different cluster number.";
            } else {
                // Update the cluster record with the new cluster number and faculty
                $update_cluster = "UPDATE clusters SET cluster = '$cluster_number', faculty_id = $faculty_id, `status` = 'assigned', assigned_date = CURDATE() WHERE id = $cluster_id";

                if (mysqli_query($conn, $update_cluster)) {
                    // Update all student_profiles with this course, school_year to the new cluster number
                    $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                                       WHERE course = '{$cluster_data['course']}' 
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
    $check_existing_cluster = "SELECT id FROM clusters WHERE course = '{$cluster_data['course']}' 
                              AND cluster = '$cluster_number' 
                              AND school_year = '{$cluster_data['school_year']}'
                              AND id != $cluster_id";
    $existing_result = mysqli_query($conn, $check_existing_cluster);

    if (!$existing_result) {
        $error_message = "Error checking existing clusters: " . mysqli_error($conn);
    } else if (mysqli_num_rows($existing_result) > 0) {
        $error_message = "Cluster number $cluster_number already exists for {$cluster_data['course']} in {$cluster_data['school_year']}. Please choose a different cluster number.";
    } else {
        // Update cluster record
        $update_cluster = "UPDATE clusters SET faculty_id = $faculty_id, capacity = $capacity, cluster = '$cluster_number' WHERE id = $cluster_id";
        if (mysqli_query($conn, $update_cluster)) {
            // Count students in this cluster
            $student_count_query = "SELECT COUNT(*) as count FROM student_profiles WHERE course = '{$cluster_data['course']}' AND school_year = '{$cluster_data['school_year']}' AND cluster = '{$cluster_data['cluster']}'";
            $student_count_result = mysqli_query($conn, $student_count_query);
            $student_count_row = mysqli_fetch_assoc($student_count_result);
            $student_count = $student_count_row['count'];

            if ($student_count == 1) {
                // Unassign the last student
                $unassign_student = "UPDATE student_profiles SET cluster = '', adviser = '' 
                                    WHERE course = '{$cluster_data['course']}' 
                                    AND school_year = '{$cluster_data['school_year']}' 
                                    AND cluster = '{$cluster_data['cluster']}'";
                mysqli_query($conn, $unassign_student);
            } else {
                // Update student_profiles to new cluster number
                $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                                    WHERE course = '{$cluster_data['course']}' 
                                    AND school_year = '{$cluster_data['school_year']}' 
                                    AND cluster = '{$cluster_data['cluster']}'";
                mysqli_query($conn, $update_students);
            }

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
    $unassign_students = "UPDATE student_profiles SET cluster = '', adviser = '' 
                         WHERE course = '{$cluster_data['course']}' 
                         AND school_year = '{$cluster_data['school_year']}' 
                         AND cluster = '{$cluster_data['cluster']}'";
    mysqli_query($conn, $unassign_students);

    // Optionally, reset cluster status and adviser
    $reset_cluster = "UPDATE clusters SET faculty_id = NULL, `status` = 'pending', assigned_date = NULL WHERE id = $cluster_id";
    mysqli_query($conn, $reset_cluster);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Remove assignment (unassign cluster) - MODIFIED to update student_profiles
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_assignment_edit'])) {
    $cluster_id = mysqli_real_escape_string($conn, $_POST['cluster_id']);

    // Get cluster details
    $cluster_query = "SELECT * FROM clusters WHERE id = $cluster_id";
    $cluster_result = mysqli_query($conn, $cluster_query);
    $cluster_data = mysqli_fetch_assoc($cluster_result);

    // Unassign all students in this cluster
    $unassign_students = "UPDATE student_profiles SET cluster = '0', faculty_id = NULL 
                         WHERE course = '{$cluster_data['course']}' 
                         AND school_year = '{$cluster_data['school_year']}' 
                         AND cluster = '{$cluster_data['cluster']}'";
    mysqli_query($conn, $unassign_students);

    // Reset cluster status, adviser, and cluster number in clusters table
    $reset_cluster = "UPDATE clusters SET faculty_id = NULL, `status` = 'pending', assigned_date = NULL, cluster = '0' WHERE id = $cluster_id";
    mysqli_query($conn, $reset_cluster);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle creating new cluster from unassigned students
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_cluster'])) {
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $school_year = mysqli_real_escape_string($conn, $_POST['school_year']);
    $cluster_number = mysqli_real_escape_string($conn, $_POST['cluster_number']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    
    // Check if cluster number already exists for this course and year
    $check_cluster = "SELECT id FROM clusters WHERE course = '$course' AND cluster = '$cluster_number' AND school_year = '$school_year'";
    $result = mysqli_query($conn, $check_cluster);
    
    if (mysqli_num_rows($result) > 0) {
        $error_message = "Cluster number $cluster_number already exists for $course in $school_year. Please choose a different cluster number.";
    } else {
        // Create new cluster
        $insert_cluster = "INSERT INTO clusters (course, cluster, school_year, student_count, capacity, `status`) 
                          VALUES ('$course', '$cluster_number', '$school_year', 0, $capacity, 'pending')";
        
        if (mysqli_query($conn, $insert_cluster)) {
            $success_message = "New cluster created successfully. You can now assign students to it.";
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
    
    $course = $cluster_data['course'];
    $school_year = $cluster_data['school_year'];
    $cluster_number = $cluster_data['cluster'];
    
    // Update selected students
    if (!empty($student_ids)) {
        $ids_string = implode(",", array_map('intval', $student_ids));
        $update_students = "UPDATE student_profiles SET cluster = '$cluster_number' 
                           WHERE id IN ($ids_string) 
                           AND course = '$course' 
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
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans h-screen overflow-hidden">
    <div class="flex">
        <!-- Sidebar/header -->
        <?php include('../includes/staff-sidebar.php'); ?>
        
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
                
                <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-secondary">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-secondary mr-4">
                            <i class="fas fa-user-graduate text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Unassigned Students</h3>
                            <p class="text-2xl font-bold"><?php echo count($unassigned_students); ?></p>
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
                    ?>
                    <div class="cluster-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="<?php echo $cluster['status']; ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    <?php echo $cluster['course']; ?>
                                    <?php if ($cluster['cluster'] === "0"): ?>
                                        <span class="text-orange-600">(Unassigned Cluster)</span>
                                    <?php else: ?>
                                        <?php echo ' ' . $cluster['cluster']; ?>
                                    <?php endif; ?>
                                </h3>
                                <p class="text-sm text-gray-500"><?php echo $cluster['school_year']; ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                if ($cluster['status'] == 'assigned') echo 'bg-success text-white';
                                elseif ($cluster['cluster'] === "0") echo 'bg-orange-500 text-white';
                                else echo 'bg-warning text-white';
                                ?>">
                                <?php 
                                if ($cluster['cluster'] === "0") echo 'Unassigned';
                                else echo ucfirst($cluster['status']); 
                                ?>
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
                                    <p class="text-xs text-gray-400">Pending assignment</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Created on: <?php echo date('M j, Y', strtotime($cluster['created_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4 <?php echo $cluster['status'] == 'assigned' ? 'flex space-x-2' : ''; ?>">
                            <?php if ($cluster['status'] == 'assigned'): ?>
                            <button onclick="viewCluster(<?php echo $cluster['id']; ?>)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded text-sm transition-colors">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button onclick="editCluster(<?php echo $cluster['id']; ?>)" class="flex-1 bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <?php else: ?>
                            <button onclick="assignAdviser(<?php echo $cluster['id']; ?>, '<?php echo $cluster['course']; ?>', <?php echo $cluster['cluster'] === "0" ? 'true' : 'false'; ?>)" class="w-full bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                                <i class="fas fa-link mr-1"></i> 
                                <?php echo $cluster['cluster'] === "0" ? 'Assign Cluster' : 'Assign Adviser'; ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Unassigned Students Tab Content -->
            <div id="unassigned-content" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 md:mb-0">Unassigned Students</h2>
                        <button onclick="toggleModal('createClusterModal')" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i> Create New Cluster
                        </button>
                    </div>
                    
                    <?php if (count($unassigned_groups) > 0): ?>
                        <?php foreach ($unassigned_groups as $key => $group): ?>
                        <div class="mb-8 border-b pb-6 last:border-b-0 last:pb-0">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?php echo $group['course']; ?> - <?php echo $group['school_year']; ?>
                                    <span class="text-sm font-normal text-gray-500 ml-2">(<?php echo $group['count']; ?> students)</span>
                                </h3>
                                                <div class="flex space-x-2">
                                    <button onclick="assignToExistingCluster('<?php echo $group['course']; ?>', '<?php echo $group['school_year']; ?>')" class="bg-primary hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                        <i class="fas fa-link mr-1"></i> Assign to Existing Cluster
                                    </button>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($group['students'] as $student): ?>
                                    <div class="bg-white rounded-lg p-3 border flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-sm"><?php echo isset($student['full_name']) ? $student['full_name'] : '[No Name]'; ?></p>
                                            <p class="text-xs text-gray-500">ID: <?php echo isset($student['student_id']) ? $student['student_id'] : '[No ID]'; ?></p>
                                        </div>
                                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Unassigned</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <div class="bg-green-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">All students are assigned!</h3>
                        <p class="text-gray-500">There are currently no unassigned students in the system.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Assign Adviser Modal -->
    <div id="assignmentModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden modal">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-1/2 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Assign Adviser to Cluster</h3>
                <button onclick="toggleModal('assignmentModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" id="assignmentForm">
                <div class="p-6 space-y-4">
                    <input type="hidden" name="cluster_id" id="modal_cluster_id">
                    <input type="hidden" name="adviser_assignment" value="1">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course & Year</label>
                        <p id="modal_course_info" class="font-semibold text-gray-900"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                        <input type="text" name="cluster_number" id="modal_cluster_number" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                        <p class="text-xs text-gray-500 mt-1">Enter a unique cluster number for this group</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Adviser</label>
                        <select name="faculty_id" id="faculty_select" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                            <option value="">Select an adviser</option>
                            <?php foreach ($faculty as $f): ?>
                            <option value="<?php echo $f['id']; ?>" data-department="<?php echo $f['department']; ?>"><?php echo $f['fullname']; ?> - <?php echo $f['department']; ?> (<?php echo $f['expertise']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Add any notes about this assignment..."></textarea>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('assignmentModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Assign Adviser</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Cluster Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden modal">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-1/2 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Edit Cluster Assignment</h3>
                <button onclick="toggleModal('editModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="cluster_id" id="edit_cluster_id">
                <input type="hidden" name="edit_cluster" value="1">
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course & Year</label>
                        <p id="edit_course_info" class="font-semibold text-gray-900"></p>
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
                        <select name="faculty_id" id="edit_faculty_id" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                            <option value="">Select an adviser</option>
                            <?php foreach ($faculty as $f): ?>
                            <option value="<?php echo $f['id']; ?>" data-department="<?php echo $f['department']; ?>"><?php echo $f['fullname']; ?> - <?php echo $f['department']; ?> (<?php echo $f['expertise']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-between">
                    <form method="POST" action="" class="inline">
                        <input type="hidden" name="cluster_id" id="remove_cluster_id">
                        <input type="hidden" name="remove_assignment_edit" value="1">
                        <button type="submit" onclick="return confirm('Are you sure you want to remove this assignment? All students will be moved back to unassigned.')" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
                            <i class="fas fa-unlink mr-1"></i> Remove Assignment
                        </button>
                    </form>
                    <div class="space-x-3">
                        <button type="button" onclick="toggleModal('editModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Cluster Modal -->
    <div id="createClusterModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden modal">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-1/2 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Create New Cluster</h3>
                <button onclick="toggleModal('createClusterModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="create_cluster" value="1">
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Course & Year</label>
                        <select name="course_year" id="course_year" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required onchange="updateClusterNumber()">
                            <option value="">Select course and year</option>
                            <?php foreach ($unassigned_groups as $key => $group): ?>
                            <option value="<?php echo $group['course'] . '|' . $group['school_year']; ?>">
                                <?php echo $group['course']; ?> - <?php echo $group['school_year']; ?> (<?php echo $group['count']; ?> students)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                            <input type="text" name="course" id="modal_course" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                            <input type="text" name="school_year" id="modal_school_year" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                        <input type="text" name="cluster_number" id="modal_new_cluster_number" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                        <p class="text-xs text-gray-500 mt-1">Enter a unique cluster number</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <input type="number" name="capacity" min="1" value="50" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('createClusterModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Create Cluster</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Students Modal -->
    <div id="assignStudentsModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden modal">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Assign Students to Cluster</h3>
                <button onclick="toggleModal('assignStudentsModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="assign_students" value="1">
                <input type="hidden" name="cluster_id" id="assign_cluster_id">
                
                <div class="p-6">
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900" id="assign_cluster_info"></h4>
                        <p class="text-sm text-gray-500">Select students to assign to this cluster</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <div class="grid grid-cols-1 gap-2" id="studentList">
                            <!-- Students will be populated here by JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="toggleModal('assignStudentsModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">Assign Selected Students</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.getAttribute('data-tab');
                
                // Update active tab button
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                
                // Show active tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tab + '-content').classList.add('active');
            });
        });

        // Modal functionality
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });

        // Assign adviser function
        function assignAdviser(clusterId, course, isUnassigned) {
            document.getElementById('modal_cluster_id').value = clusterId;
            document.getElementById('modal_course_info').textContent = course;
            
            if (isUnassigned) {
                document.getElementById('modal_cluster_number').value = '';
                document.getElementById('modal_cluster_number').placeholder = 'Enter cluster number';
            } else {
                document.getElementById('modal_cluster_number').value = 'Cluster ' + clusterId;
            }
            
            // Filter faculty by program compatibility
            const facultySelect = document.getElementById('faculty_select');
            const options = facultySelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else {
                    const department = option.getAttribute('data-department');
                    const programMatch = 
                        (course.includes('Computer Science') && department === 'Information Technology') ||
                        (course.includes('Information Technology') && department === 'Information Technology') ||
                        (course.includes('Business') && department === 'Accounting') ||
                        (course.includes('Accounting') && department === 'Accounting') ||
                        (course.includes('Education') && department === 'Education') ||
                        (course.includes('Criminology') && department === 'Criminology') ||
                        (course.includes('Tourism') && department === 'Tourism') ||
                        (course.includes('Hospitality') && department === 'Hospitality Management');
                    
                    option.style.display = programMatch ? 'block' : 'none';
                }
            });
            
            toggleModal('assignmentModal');
        }

        // Edit cluster function
        function editCluster(clusterId) {
            // In a real implementation, you would fetch cluster data via AJAX
            // For now, we'll just set the cluster ID
            document.getElementById('edit_cluster_id').value = clusterId;
            document.getElementById('remove_cluster_id').value = clusterId;
            toggleModal('editModal');
        }

        // View cluster function
        function viewCluster(clusterId) {
            alert('View details for cluster ' + clusterId + '. This would show student list and details in a real implementation.');
        }

        // Filter clusters by status
        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const clusters = document.querySelectorAll('.cluster-card');
            
            clusters.forEach(cluster => {
                if (status === 'all' || cluster.getAttribute('data-status') === status) {
                    cluster.style.display = 'block';
                } else {
                    cluster.style.display = 'none';
                }
            });
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const clusters = document.querySelectorAll('.cluster-card');
            
            clusters.forEach(cluster => {
                const course = cluster.querySelector('h3').textContent.toLowerCase();
                const year = cluster.querySelector('p').textContent.toLowerCase();
                
                if (course.includes(searchTerm) || year.includes(searchTerm)) {
                    cluster.style.display = 'block';
                } else {
                    cluster.style.display = 'none';
                }
            });
        });

        // Update course and year when selecting from dropdown
        function updateClusterNumber() {
            const courseYear = document.getElementById('course_year').value;
            if (courseYear) {
                const [course, schoolYear] = courseYear.split('|');
                document.getElementById('modal_course').value = course;
                document.getElementById('modal_school_year').value = schoolYear;
                
                // Suggest a cluster number based on course
                const courseCode = course.split(' ').map(word => word[0]).join('').toUpperCase();
                document.getElementById('modal_new_cluster_number').value = courseCode + '-01';
            }
        }

        // Assign to existing cluster
        function assignToExistingCluster(course, schoolYear) {
            alert(`This would show a modal to assign ${course} ${schoolYear} students to an existing cluster. Implementation would fetch available clusters and show a selection interface.`);
        }

        // Initialize any necessary functionality on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Any initialization code can go here
        });
    </script>
</body>
</html>