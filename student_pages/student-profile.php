<?php
include('../includes/connection.php');
session_start();

// Debug: Check what's in the session
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], 'student') !== 0) {
    header("Location: ../auth/student-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Check if profile already exists
$profile_check = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$profile_check->bind_param("i", $user_id);
$profile_check->execute();
$profile_result = $profile_check->get_result();
$existing_profile = $profile_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_id = $_POST['school_id'];
    $full_name = $_POST['full_name'];
    $course = $_POST['course']; // Changed from department to course
    $section = $_POST['section'];
    $school_year = $_POST['school_year'];
    
    if ($existing_profile) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE student_profiles SET school_id=?, full_name=?, course=?, section=?, school_year=? WHERE user_id=?");
        $stmt->bind_param("sssssi", $school_id, $full_name, $course, $section, $school_year, $user_id);
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO student_profiles (user_id, school_id, full_name, course, section, school_year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $school_id, $full_name, $course, $section, $school_year);
    }
    
    if ($stmt->execute()) {
        $message = "Profile saved successfully!";
        // Refresh the existing profile data
        $profile_check->close();
        $profile_check = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
        $profile_check->bind_param("i", $user_id);
        $profile_check->execute();
        $profile_result = $profile_check->get_result();
        $existing_profile = $profile_result->fetch_assoc();
    } else {
        $message = "Error saving profile: " . $conn->error;
    }
    
    $stmt->close();
}
$profile_check->close();
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
    </script>
    <style>
        .profile-card {
            transition: all 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans h-screen overflow-hidden">

    <div class="min-h-screen flex">
        <!-- Sidebar/header -->
        <?php 
        // Check if sidebar exists, if not provide a fallback
        $sidebar_path = '../includes/student-sidebar.php';
        if (file_exists($sidebar_path)) {
            include($sidebar_path);
        } else {
            echo '<div class="w-64 bg-primary text-white p-4">Student Sidebar (Missing)</div>';
        }
        ?>

        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto p-6">
           

                <?php if (!empty($message)): ?>
                    <div class="mb-6 p-4 rounded-md <?php echo strpos($message, 'Error') !== false ? 'bg-danger/20 text-danger' : 'bg-success/20 text-success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-md overflow-hidden profile-card">
                    <div class="bg-gradient-to-r from-primary to-secondary p-6 text-white">
                        <h2 class="text-2xl font-semibold">Personal Information</h2>
                        <p class="opacity-90">Please provide accurate information as it appears in school records</p>
                    </div>
                    
                    <form method="POST" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="school_id" class="block text-sm font-medium text-gray-700 mb-1">School ID</label>
                                <input 
                                    type="text" 
                                    id="school_id" 
                                    name="school_id" 
                                    value="<?php echo isset($existing_profile['school_id']) ? htmlspecialchars($existing_profile['school_id']) : ''; ?>" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition input-field"
                                    required
                                >
                            </div>
                            
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input 
                                    type="text" 
                                    id="full_name" 
                                    name="full_name" 
                                    value="<?php echo isset($existing_profile['full_name']) ? htmlspecialchars($existing_profile['full_name']) : ''; ?>" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition input-field"
                                    required
                                >
                            </div>
                            
                            <div>
                                <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                                <select 
                                    id="course" 
                                    name="course" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition input-field"
                                    required
                                >
                                   <option value="">Select Course</option>
                                    <option value="BSCS" <?php echo (isset($existing_profile['course']) && $existing_profile['course'] == 'BSCS') ? 'selected' : ''; ?>>BSCS - Computer Science</option>
                                    <option value="BSBA" <?php echo (isset($existing_profile['course']) && $existing_profile['course'] == 'BSBA') ? 'selected' : ''; ?>>BSBA - Business Administration</option>
                                    <option value="BSED" <?php echo (isset($existing_profile['course']) && $existing_profile['course'] == 'BSED') ? 'selected' : ''; ?>>BSED - Education</option>
                                    <option value="BSIT" <?php echo (isset($existing_profile['course']) && $existing_profile['course'] == 'BSIT') ? 'selected' : ''; ?>>BSIT - Information Technology</option>
                                    <option value="BSCRIM" <?php echo (isset($existing_profile['course']) && $existing_profile['course'] == 'BSCRIM') ? 'selected' : ''; ?>>BSCRIM - Criminology</option>

                                </select>
                            </div>
                            
                            <div>
                                <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                                <input 
                                    type="text" 
                                    id="section" 
                                    name="section" 
                                    value="<?php echo isset($existing_profile['section']) ? htmlspecialchars($existing_profile['section']) : ''; ?>" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition input-field"
                                    required
                                >
                            </div>
                            
                            <div>
                                <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                                <select 
                                    id="school_year" 
                                    name="school_year" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition input-field"
                                    required
                                >
                                    <option value="">Select School Year</option>
                                    <?php
                                    $current_year = date('Y');
                                    for ($i = 0; $i < 5; $i++) {
                                        $year_option = ($current_year - $i) . '-' . ($current_year - $i + 1);
                                        $selected = (isset($existing_profile['school_year']) && $existing_profile['school_year'] == $year_option) ? 'selected' : '';
                                        echo "<option value=\"$year_option\" $selected>$year_option</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit" 
                                class="px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 flex items-center"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php if (isset($existing_profile) && $existing_profile): ?>
                <div class="mt-8 bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-success p-6 text-white">
                        <h2 class="text-2xl font-semibold">Current Profile Information</h2>
                        <p class="opacity-90">Your profile is complete and visible to authorized personnel</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="bg-primary/10 p-3 rounded-full mr-4">
                                    <i class="fas fa-id-card text-primary text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">School ID</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($existing_profile['school_id']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="bg-primary/10 p-3 rounded-full mr-4">
                                    <i class="fas fa-user text-primary text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Full Name</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($existing_profile['full_name']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="bg-primary/10 p-3 rounded-full mr-4">
                                    <i class="fas fa-book text-primary text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Course</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($existing_profile['course']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="bg-primary/10 p-3 rounded-full mr-4">
                                    <i class="fas fa-users text-primary text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Section</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($existing_profile['section']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="bg-primary/10 p-3 rounded-full mr-4">
                                    <i class="fas fa-calendar-alt text-primary text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">School Year</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($existing_profile['school_year']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let valid = true;
            const inputs = this.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('border-danger');
                } else {
                    input.classList.remove('border-danger');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>