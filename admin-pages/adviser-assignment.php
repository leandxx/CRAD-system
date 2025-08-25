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
                
                <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-secondary">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-secondary mr-4">
                            <i class="fas fa-chalkboard-teacher text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Available Advisers</h3>
                            <p class="text-2xl font-bold"><?php echo $available_faculty; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
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
        </main>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Assign Adviser to Cluster</h3>
                <button onclick="toggleModal('assignmentModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <div class="px-6 py-4">
                    <input type="hidden" id="cluster_id" name="cluster_id" value="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Course and Year</label>
                            <select id="cluster_select" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" onchange="updateFacultyOptions()">
                                <option value="">-- Select a course and year --</option>
                                <?php 
                                // Get unique course and year combinations for unassigned clusters
                                $course_year_query = "SELECT DISTINCT course, school_year FROM clusters WHERE `status` = 'pending' ORDER BY course, school_year";
                                $course_year_result = mysqli_query($conn, $course_year_query);
                                
                                if ($course_year_result && mysqli_num_rows($course_year_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($course_year_result)):
                                        $course = $row['course'];
                                        $year = $row['school_year'];
                                        
                                        // Count how many unassigned clusters for this course and year
                                        $count_query = "SELECT COUNT(*) as count FROM clusters WHERE course = '$course' AND school_year = '$year' AND `status` = 'pending'";
                                        $count_result = mysqli_query($conn, $count_query);
                                        $count_row = mysqli_fetch_assoc($count_result);
                                        $cluster_count = $count_row['count'];
                                ?>
                                <option value="<?php echo $course . '|' . $year; ?>" data-course="<?php echo $course; ?>" data-year="<?php echo $year; ?>">
                                    <?php echo $course . ' - ' . $year . ' (' . $cluster_count . ' unassigned)'; ?>
                                </option>
                                <?php endwhile; 
                                }?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Adviser</label>
                            <select id="faculty_select" name="faculty_id" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">-- Select an adviser --</option>
                                <?php foreach ($faculty as $member): ?>
                                    <option value="<?php echo $member['id']; ?>" data-department="<?php echo $member['department']; ?>">
                                        <?php echo $member['fullname'] . ' (' . $member['department'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Cluster Number Input Field -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                        <input type="number" name="cluster_number" id="cluster_number" min="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter cluster number" required>
                        <p class="text-xs text-gray-500 mt-1">Please assign a unique cluster number for this group.</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Details</label>
                        <textarea name="notes" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" rows="3" placeholder="Add any notes about this assignment..."></textarea>
                    </div>
                    
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="sendEmail" name="send_email" class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="sendEmail" class="ml-2 block text-sm text-gray-700">Send notification email to adviser</label>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-2">
                    <button type="button" onclick="toggleModal('assignmentModal')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" name="remove_assignment" class="px-4 py-2 border border-red-500 text-red-600 rounded-md text-sm font-medium bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-trash mr-1"></i> Remove
                    </button>
                    <button type="submit" name="adviser_assignment" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Cluster Modal -->
    <div id="viewModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Cluster Details</h3>
                <button onclick="toggleModal('viewModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Course</h4>
                        <p id="view-course" class="text-lg font-semibold"></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Cluster Number</h4>
                        <p id="view-cluster" class="text-lg font-semibold"></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">School Year</h4>
                        <p id="view-year" class="text-lg"></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Status</h4>
                        <p id="view-status" class="text-lg"></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Student Count</h4>
                        <p id="view-students" class="text-lg"></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Capacity</h4>
                        <p id="view-capacity" class="text-lg"></p>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h4 class="text-md font-medium text-gray-700 mb-2">Assigned Adviser</h4>
                    <div id="view-adviser" class="flex items-center">
                        <!-- Adviser details will be populated here -->
                    </div>
                </div>
                
                <div class="border-t pt-4 mt-4">
                    <h4 class="text-md font-medium text-gray-700 mb-2">Assignment Date</h4>
                    <p id="view-date" class="text-sm text-gray-500"></p>
                </div>
            </div>
            <div class="border-t px-6 py-4 bg-gray-50 flex justify-end">
                <button onclick="toggleModal('viewModal')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Cluster Modal -->
    <div id="editModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Edit Cluster Assignment</h3>
                <button onclick="toggleModal('editModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_cluster_id" name="cluster_id" value="">
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="block text-sm font-medium text-gray-700 mb-1">Course</h4>
                            <p id="edit-course" class="text-lg font-semibold"></p>
                        </div>
                        <div>
                            <label for="edit-cluster-number" class="block text-sm font-medium text-gray-700 mb-1">Cluster Number</label>
                            <input type="number" id="edit-cluster-number" name="cluster_number" min="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">School Year</h4>
                            <p id="edit-year" class="text-lg"></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Student Count</h4>
                            <p id="edit-students" class="text-lg"></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="edit-capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                            <input type="number" id="edit-capacity" name="capacity" min="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" required>
                        </div>
                        <div>
                            <label for="edit-faculty" class="block text-sm font-medium text-gray-700 mb-1">Adviser</label>
                            <select id="edit-faculty" name="faculty_id" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">-- Select an adviser --</option>
                                <?php foreach ($faculty as $member): ?>
                                    <option value="<?php echo $member['id']; ?>">
                                        <?php echo $member['fullname'] . ' (' . $member['department'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end space-x-2">
                    <button type="button" onclick="toggleModal('editModal')" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" name="remove_assignment_edit" class="px-4 py-2 border border-red-500 text-red-600 rounded-md text-sm font-medium bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-trash mr-1"></i> Remove
                    </button>
                    <button type="submit" name="edit_cluster" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const courseToDepartment = {
            'BSIT': 'Information Technology',
            'BSHM': 'Hospitality Management',
            'BSA': 'Accounting',
            'BSTM': 'Tourism',
            'BSCRIM': 'Criminology'
        };

        // Modal functionality
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    toggleModal(modal.id);
                }
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const clusterCards = document.querySelectorAll('.cluster-card');
            
            clusterCards.forEach(card => {
                const course = card.querySelector('h3').textContent.toLowerCase();
                const year = card.querySelector('p.text-sm').textContent.toLowerCase();
                const status = card.querySelector('span').textContent.toLowerCase();
                
                if (course.includes(searchTerm) || year.includes(searchTerm) || status.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
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
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Assign adviser function
        function assignAdviser(clusterId, course, isUnassigned) {
            document.getElementById('cluster_id').value = clusterId;
            
            if (isUnassigned) {
                // For unassigned clusters, pre-select the course and year
                const clusterSelect = document.getElementById('cluster_select');
                for (let i = 0; i < clusterSelect.options.length; i++) {
                    if (clusterSelect.options[i].textContent.includes(course)) {
                        clusterSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            toggleModal('assignmentModal');
        }

        // View cluster details
        function viewCluster(clusterId) {
            // In a real application, you would fetch this data from the server via AJAX
            // For this example, we'll simulate with the data we have
            const clusters = <?php echo json_encode($clusters); ?>;
            const cluster = clusters.find(c => c.id == clusterId);
            
            if (cluster) {
                document.getElementById('view-course').textContent = cluster.course;
                document.getElementById('view-cluster').textContent = cluster.cluster === "0" ? "Unassigned" : cluster.cluster;
                document.getElementById('view-year').textContent = cluster.school_year;
                document.getElementById('view-status').textContent = cluster.status.charAt(0).toUpperCase() + cluster.status.slice(1);
                document.getElementById('view-students').textContent = cluster.student_count;
                document.getElementById('view-capacity').textContent = cluster.capacity;
                
                const adviserDiv = document.getElementById('view-adviser');
                if (cluster.faculty_name) {
                    adviserDiv.innerHTML = `
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-primary flex items-center justify-center mr-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <p class="text-md font-medium">${cluster.faculty_name}</p>
                            <p class="text-sm text-gray-500">${cluster.faculty_department} Department</p>
                        </div>
                    `;
                } else {
                    adviserDiv.innerHTML = `
                        <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mr-3">
                            <i class="fas fa-question"></i>
                        </div>
                        <div>
                            <p class="text-md font-medium text-gray-400">No adviser assigned</p>
                            <p class="text-sm text-gray-400">Pending assignment</p>
                        </div>
                    `;
                }
                
                document.getElementById('view-date').textContent = cluster.assigned_date 
                    ? new Date(cluster.assigned_date).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })
                    : 'Not assigned yet';
                
                toggleModal('viewModal');
            }
        }

        // Edit cluster details
        function editCluster(clusterId) {
            const clusters = <?php echo json_encode($clusters); ?>;
            const cluster = clusters.find(c => c.id == clusterId);

            if (cluster) {
                document.getElementById('edit_cluster_id').value = cluster.id;
                document.getElementById('edit-course').textContent = cluster.course;
                document.getElementById('edit-cluster-number').value = cluster.cluster === "0" ? "" : cluster.cluster;
                document.getElementById('edit-year').textContent = cluster.school_year;
                document.getElementById('edit-students').textContent = cluster.student_count;
                document.getElementById('edit-capacity').value = cluster.capacity;

                // Set the current faculty if one is assigned
                const facultySelect = document.getElementById('edit-faculty');
                if (cluster.faculty_id) {
                    facultySelect.value = cluster.faculty_id;
                } else {
                    facultySelect.value = "";
                }

                toggleModal('editModal');
            }
        }

        // Update faculty options based on selected course
        function updateFacultyOptions() {
            const clusterSelect = document.getElementById('cluster_select');
            const facultySelect = document.getElementById('faculty_select');
            const selectedOption = clusterSelect.options[clusterSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const course = selectedOption.getAttribute('data-course');
                const department = courseToDepartment[course] || null;
                for (let i = 0; i < facultySelect.options.length; i++) {
                    const option = facultySelect.options[i];
                    if (!department || option.value === "" || option.getAttribute('data-department') === department) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
                facultySelect.value = "";
            } else {
                // Show all if nothing selected
                for (let i = 0; i < facultySelect.options.length; i++) {
                    facultySelect.options[i].style.display = 'block';
                }
                facultySelect.value = "";
            }
        }
    </script>
</body>
</html>