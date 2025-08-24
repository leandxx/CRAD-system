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

// Fetch student data to create sections
$student_query = "SELECT DISTINCT course, cluster, school_year FROM student_profiles WHERE course IS NOT NULL AND cluster IS NOT NULL AND school_year IS NOT NULL";
$student_result = mysqli_query($conn, $student_query);

if ($student_result && mysqli_num_rows($student_result) > 0) {
    while ($row = mysqli_fetch_assoc($student_result)) {
        $course = $row['course'];
        $cluster = $row['cluster'];
        $school_year = $row['school_year'];
        
        // Check if section already exists
        $check_section = "SELECT id FROM sections WHERE course = '$course' AND cluster = '$cluster' AND school_year = '$school_year'";
        $section_result = mysqli_query($conn, $check_section);
        
        if (mysqli_num_rows($section_result) == 0) {
            // Get student count for this section
            $count_query = "SELECT COUNT(*) as count FROM student_profiles WHERE course = '$course' AND cluster = '$cluster' AND school_year = '$school_year'";
            $count_result = mysqli_query($conn, $count_query);
            $count_row = mysqli_fetch_assoc($count_result);
            $student_count = $count_row['count'];
            
            // Insert new section
            $insert_section = "INSERT INTO sections (course, cluster, school_year, student_count, capacity) VALUES ('$course', '$cluster', '$school_year', $student_count, 50)";
            mysqli_query($conn, $insert_section);
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

// Fetch sections data
$sections_query = "SELECT s.*, f.fullname as faculty_name, f.department as faculty_department 
                   FROM sections s 
                   LEFT JOIN faculty f ON s.faculty_id = f.id 
                   ORDER BY s.course, s.cluster";
$sections_result = mysqli_query($conn, $sections_query);
$sections = array();

if ($sections_result && mysqli_num_rows($sections_result) > 0) {
    while ($row = mysqli_fetch_assoc($sections_result)) {
        $sections[] = $row;
    }
}

// Count statistics
$total_sections = count($sections);
$assigned_sections = 0;
$pending_sections = 0;

foreach ($sections as $section) {
    if ($section['status'] == 'assigned') {
        $assigned_sections++;
    } else {
        $pending_sections++;
    }
}

$available_faculty = count($faculty);

// Handle form submission for assigning adviser
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_adviser'])) {
    $section_id = $_POST['section_id'];
    $faculty_id = $_POST['faculty_id'];
    $notes = $_POST['notes'];
    $send_email = isset($_POST['send_email']) ? 1 : 0;
    
    // Update section with assigned faculty
    $update_section = "UPDATE sections SET faculty_id = $faculty_id, status = 'assigned', assigned_date = CURDATE() WHERE id = $section_id";
    
    if (mysqli_query($conn, $update_section)) {
        // Insert into assign_adviser table
        $insert_assign = "INSERT INTO assign_adviser (section_id, faculty_id, assigned_date, notes) 
                         VALUES ($section_id, $faculty_id, CURDATE(), '$notes')";
        mysqli_query($conn, $insert_assign);
        
        // Refresh page to show updated data
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "Error assigning adviser: " . mysqli_error($conn);
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
                            <h3 class="text-sm font-medium text-gray-500">Total Sections</h3>
                            <p class="text-2xl font-bold"><?php echo $total_sections; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 card-hover border-l-4 border-success">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-success mr-4">
                            <i class="fas fa-user-check text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Assigned Sections</h3>
                            <p class="text-2xl font-bold"><?php echo $assigned_sections; ?></p>
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
                            <p class="text-2xl font-bold"><?php echo $pending_sections; ?></p>
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
                        <input type="text" id="searchInput" placeholder="Search sections..." class="pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full md:w-64">
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
                    <button onclick="toggleModal()" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-plus-circle mr-2"></i> New Assignment
                    </button>
                </div>
            </div>
            
            <!-- Sections Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($sections as $section): 
                    $percentage = ($section['capacity'] > 0) ? round(($section['student_count'] / $section['capacity']) * 100) : 0;
                ?>
                <div class="section-card bg-white rounded-lg shadow-sm p-6 card-hover" data-status="<?php echo $section['status']; ?>">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900"><?php echo $section['course'] . ' ' . $section['cluster']; ?></h3>
                            <p class="text-sm text-gray-500"><?php echo $section['school_year']; ?></p>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                            <?php echo $section['status'] == 'assigned' ? 'bg-success text-white' : 'bg-warning text-white'; ?>">
                            <?php echo ucfirst($section['status']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-500 mb-1">
                            <span>Students: <?php echo $section['student_count'] . '/' . $section['capacity']; ?></span>
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
                        <?php if ($section['status'] == 'assigned' && $section['faculty_name']): ?>
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-primary flex items-center justify-center mr-3">
                                <i class="fas fa-user-tie text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium"><?php echo $section['faculty_name']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $section['faculty_department']; ?> Department</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Assigned on: <?php echo date('M j, Y', strtotime($section['assigned_date'])); ?></p>
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
                        <p class="text-xs text-gray-500 mt-2"><i class="far fa-calendar-alt mr-1"></i> Created on: <?php echo date('M j, Y', strtotime($section['assigned_date'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4 <?php echo $section['status'] == 'assigned' ? 'flex space-x-2' : ''; ?>">
                        <?php if ($section['status'] == 'assigned'): ?>
                        <button class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded text-sm transition-colors">
                            <i class="fas fa-eye mr-1"></i> View
                        </button>
                        <button class="flex-1 bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <?php else: ?>
                        <button onclick="assignAdviser(<?php echo $section['id']; ?>, '<?php echo $section['course']; ?>')" class="w-full bg-primary hover:bg-blue-700 text-white py-2 rounded text-sm transition-colors">
                            <i class="fas fa-link mr-1"></i> Assign Adviser
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl max-h-screen overflow-y-auto">
            <div class="border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Assign Adviser to Section</h3>
                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <div class="px-6 py-4">
                    <input type="hidden" id="section_id" name="section_id" value="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Section</label>
                            <select id="section_select" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" onchange="updateFacultyOptions()">
                                <option value="">-- Select a section --</option>
                                <?php foreach ($sections as $section): 
                                    if ($section['status'] == 'pending'): ?>
                                    <option value="<?php echo $section['id']; ?>" data-course="<?php echo $section['course']; ?>">
                                        <?php echo $section['course'] . ' ' . $section['cluster'] . ' - ' . $section['school_year'] . ' (' . $section['student_count'] . ' students)'; ?>
                                    </option>
                                <?php endif; endforeach; ?>
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
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Details</label>
                        <textarea name="notes" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary" rows="3" placeholder="Add any notes about this assignment..."></textarea>
                    </div>
                    
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="sendEmail" name="send_email" class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="sendEmail" class="ml-2 block text-sm text-gray-700">Send notification email to adviser</label>
                    </div>
                </div>
                <div class="border-t px-6 py-4 bg-gray-50 flex justify-end">
                    <button type="button" onclick="toggleModal()" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" name="assign_adviser" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle modal visibility
        function toggleModal() {
            const modal = document.getElementById('assignmentModal');
            modal.classList.toggle('hidden');
        }
        
        // Assign adviser to specific section
        function assignAdviser(sectionId, course) {
            document.getElementById('section_id').value = sectionId;
            
            // Find and select the section in the dropdown
            const sectionSelect = document.getElementById('section_select');
            for (let i = 0; i < sectionSelect.options.length; i++) {
                if (sectionSelect.options[i].value == sectionId) {
                    sectionSelect.selectedIndex = i;
                    break;
                }
            }
            
            // Update faculty options based on course
            updateFacultyOptions();
            
            toggleModal();
        }
        
        // Update faculty options based on selected section's course
        function updateFacultyOptions() {
            const sectionSelect = document.getElementById('section_select');
            const facultySelect = document.getElementById('faculty_select');
            const selectedOption = sectionSelect.options[sectionSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const course = selectedOption.getAttribute('data-course');
                document.getElementById('section_id').value = selectedOption.value;
                
                // Enable all options first
                for (let i = 0; i < facultySelect.options.length; i++) {
                    facultySelect.options[i].disabled = false;
                    facultySelect.options[i].style.display = '';
                }
                
                // Disable options that don't match the course department
                for (let i = 1; i < facultySelect.options.length; i++) {
                    const option = facultySelect.options[i];
                    const department = option.getAttribute('data-department');
                    
                    // Simple mapping of course to department
                    let expectedDepartment = '';
                    if (course.includes('Crim')) expectedDepartment = 'Criminology';
                    else if (course.includes('Account')) expectedDepartment = 'Accounting';
                    else if (course.includes('IT') || course.includes('Information')) expectedDepartment = 'Information Technology';
                    else if (course.includes('Hospitality')) expectedDepartment = 'Hospitality Management';
                    else if (course.includes('Tourism')) expectedDepartment = 'Tourism';
                    
                    if (expectedDepartment && department !== expectedDepartment) {
                        option.disabled = true;
                        option.style.display = 'none';
                    }
                }
                
                // Reset selection if current selection is disabled
                if (facultySelect.options[facultySelect.selectedIndex].disabled) {
                    facultySelect.selectedIndex = 0;
                }
            }
        }
        
        // Search functionality
        function handleSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sectionCards = document.querySelectorAll('.section-card');
            
            sectionCards.forEach(card => {
                const sectionText = card.textContent.toLowerCase();
                if (sectionText.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Filter by status
        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const sectionCards = document.querySelectorAll('.section-card');
            
            sectionCards.forEach(card => {
                if (status === 'all' || card.getAttribute('data-status') === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Initialize search functionality
        document.getElementById('searchInput').addEventListener('keyup', handleSearch);
    </script>
</body>
</html>