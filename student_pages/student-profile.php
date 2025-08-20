<?php
include('../includes/connection.php');

// Check if student is logged in (you'll need to implement your authentication)
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;

// Handle form submission for both create and update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $school_year = trim($_POST['school_year']);
    $department = trim($_POST['department']);
    $student_id_input = trim($_POST['student_id']);
    
    // If we're in edit mode (student_id from session exists)
    if ($student_id) {
        // Update existing student record
        $updateQuery = "UPDATE students SET fullname='$fullname', school_year='$school_year', 
                        department='$department' WHERE student_id='$student_id'";
        
        if ($conn->query($updateQuery)) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    } else {
        // Check if student ID already exists (for new registration)
        $checkQuery = "SELECT * FROM students WHERE student_id = '$student_id_input'";
        $checkResult = $conn->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $error = "Student ID already exists!";
        } else {
            // Insert new student record
            $insertQuery = "INSERT INTO students (fullname, school_year, department, student_id) 
                            VALUES ('$fullname', '$school_year', '$department', '$student_id_input')";
            
            if ($conn->query($insertQuery)) {
                // Set session variable if you want to implement login system
                // $_SESSION['student_id'] = $student_id_input;
                $success = "Profile created successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}

// Fetch existing student data if student is logged in
$existingData = null;
if ($student_id) {
    $fetchQuery = "SELECT * FROM students WHERE student_id = '$student_id'";
    $result = $conn->query($fetchQuery);
    if ($result->num_rows > 0) {
        $existingData = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#7c3aed',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444'
                    }
                }
            }
        }
        
        // Function to toggle edit mode
        function toggleEditMode() {
            const form = document.getElementById('profileForm');
            const inputs = form.querySelectorAll('input, select');
            const isViewMode = inputs[0].hasAttribute('readonly');
            
            if (isViewMode) {
                // Switch to edit mode
                inputs.forEach(input => {
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                    input.classList.remove('bg-gray-100');
                    input.classList.add('bg-white');
                });
                
                document.getElementById('editBtn').innerHTML = '<i class="fas fa-times mr-2"></i> Cancel';
                document.getElementById('saveBtn').classList.remove('hidden');
                document.getElementById('sectionBtn').classList.remove('hidden');
            } else {
                // Switch to view mode
                inputs.forEach(input => {
                    if (input.id !== 'student_id') {
                        input.setAttribute('readonly', 'readonly');
                    } else {
                        input.setAttribute('disabled', 'disabled');
                    }
                    input.classList.remove('bg-white');
                    input.classList.add('bg-gray-100');
                });
                
                document.getElementById('editBtn').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Profile';
                document.getElementById('saveBtn').classList.add('hidden');
                document.getElementById('sectionBtn').classList.add('hidden');
                
                // Reset form to original values if cancelled
                <?php if ($existingData): ?>
                document.getElementById('fullname').value = '<?php echo $existingData['fullname']; ?>';
                document.getElementById('school_year').value = '<?php echo $existingData['school_year']; ?>';
                document.getElementById('department').value = '<?php echo $existingData['department']; ?>';
                <?php endif; ?>
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php include('../includes/student-sidebar.php'); ?>
        <?php include('../includes/student-header.php'); ?>

        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold text-primary mb-6">
                    <?php echo $existingData ? 'Student Profile' : 'Student Profile Setup'; ?>
                </h1>
                
                <!-- Status Messages -->
                <?php if(isset($success)): ?>
                    <div class="bg-success text-white p-3 rounded-lg mb-6">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="bg-danger text-white p-3 rounded-lg mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Profile Form -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form method="POST" action="" id="profileForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div class="md:col-span-2">
                                <label for="fullname" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="fullname" name="fullname" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                    placeholder="Enter your full name"
                                    value="<?php echo $existingData ? $existingData['fullname'] : ''; ?>"
                                    <?php echo $existingData ? 'readonly' : ''; ?>
                                    style="<?php echo $existingData ? 'background-color: #f3f4f6;' : ''; ?>">
                            </div>
                            
                            <!-- School Year -->
                            <div>
                                <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                                <select id="school_year" name="school_year" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                    <?php echo $existingData ? 'disabled' : ''; ?>
                                    style="<?php echo $existingData ? 'background-color: #f3f4f6;' : ''; ?>">
                                    <option value="" disabled <?php echo !$existingData ? 'selected' : ''; ?>>Select School Year</option>
                                    <option value="2023-2024" <?php echo ($existingData && $existingData['school_year'] == '2023-2024') ? 'selected' : ''; ?>>2023-2024</option>
                                    <option value="2024-2025" <?php echo ($existingData && $existingData['school_year'] == '2024-2025') ? 'selected' : ''; ?>>2024-2025</option>
                                    <option value="2025-2026" <?php echo ($existingData && $existingData['school_year'] == '2025-2026') ? 'selected' : ''; ?>>2025-2026</option>
                                    <option value="2026-2027" <?php echo ($existingData && $existingData['school_year'] == '2026-2027') ? 'selected' : ''; ?>>2026-2027</option>
                                </select>
                            </div>
                            
                            <!-- Department -->
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select id="department" name="department" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                    <?php echo $existingData ? 'disabled' : ''; ?>
                                    style="<?php echo $existingData ? 'background-color: #f3f4f6;' : ''; ?>">
                                    <option value="" disabled <?php echo !$existingData ? 'selected' : ''; ?>>Select Department</option>
                                    <option value="Computer Science" <?php echo ($existingData && $existingData['department'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Engineering" <?php echo ($existingData && $existingData['department'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                                    <option value="Business Administration" <?php echo ($existingData && $existingData['department'] == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                                    <option value="Education" <?php echo ($existingData && $existingData['department'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                                    <option value="Arts and Sciences" <?php echo ($existingData && $existingData['department'] == 'Arts and Sciences') ? 'selected' : ''; ?>>Arts and Sciences</option>
                                    <option value="Nursing" <?php echo ($existingData && $existingData['department'] == 'Nursing') ? 'selected' : ''; ?>>Nursing</option>
                                </select>
                            </div>
                            
                            <!-- Student ID -->
                            <div class="md:col-span-2">
                                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
                                <input type="text" id="student_id" name="student_id" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                    placeholder="Enter your student ID"
                                    value="<?php echo $existingData ? $existingData['student_id'] : ''; ?>"
                                    <?php echo $existingData ? 'disabled' : ''; ?>
                                    style="<?php echo $existingData ? 'background-color: #f3f4f6;' : ''; ?>">
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <?php if($existingData): ?>
                                <button type="button" id="editBtn" onclick="toggleEditMode()" 
                                    class="px-6 py-2 bg-warning text-white rounded-lg hover:bg-yellow-600 transition duration-300 flex items-center">
                                    <i class="fas fa-edit mr-2"></i> Edit Profile
                                </button>
                                
                                <button type="submit" id="saveBtn"
                                    class="hidden px-6 py-2 bg-success text-white rounded-lg hover:bg-green-700 transition duration-300 flex items-center">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                                
                                <button type="button" id="sectionBtn"
                                    class="hidden px-6 py-2 bg-secondary text-white rounded-lg hover:bg-purple-700 transition duration-300 flex items-center">
                                    <i class="fas fa-list mr-2"></i> Manage Sections
                                </button>
                            <?php else: ?>
                                <button type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition duration-300 flex items-center">
                                    <i class="fas fa-save mr-2"></i> Save Profile
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Information Note -->
                <div class="mt-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-primary text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-primary">Important Information</h3>
                            <div class="mt-1 text-sm text-gray-600">
                                <p>Please ensure all information provided is accurate. Your student ID will be verified with university records.</p>
                                <?php if($existingData): ?>
                                    <p class="mt-2">Click "Edit Profile" to modify your information or "Manage Sections" to update your course sections.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>