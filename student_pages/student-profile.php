<?php
include('../includes/connection.php');
include('../includes/notification-helper.php');
session_start();

// Debug: Check what's in the session
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], 'student') !== 0) {
    header("Location: ../auth/student-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$assigned_adviser = null;

// Check if profile already exists
$profile_check = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$profile_check->bind_param("i", $user_id);
$profile_check->execute();
$profile_result = $profile_check->get_result();
$existing_profile = $profile_result->fetch_assoc();

// If profile exists, fetch assigned adviser
if ($existing_profile && $existing_profile['faculty_id']) {
    $faculty_id = $existing_profile['faculty_id'];
    
    // Query to get assigned adviser directly from faculty_id
    $adviser_query = "SELECT * FROM faculty WHERE id = ?";
    
    $adviser_stmt = $conn->prepare($adviser_query);
    $adviser_stmt->bind_param("i", $faculty_id);
    $adviser_stmt->execute();
    $adviser_result = $adviser_stmt->get_result();
    
    if ($adviser_result && $adviser_result->num_rows > 0) {
        $assigned_adviser = $adviser_result->fetch_assoc();
    }
    $adviser_stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_id = $_POST['school_id'];
    $full_name = $_POST['full_name'];
    $program = $_POST['program'];
    $school_year = $_POST['school_year'];
    
    // Get cluster assignment from admin based on course and school year
    $cluster_query = "
        SELECT cluster 
        FROM clusters 
        WHERE program = ? AND school_year = ? AND status = 'active'
        LIMIT 1
    ";
    
    $cluster_stmt = $conn->prepare($cluster_query);
    $cluster_stmt->bind_param("ss", $program, $school_year);
    $cluster_stmt->execute();
    $cluster_result = $cluster_stmt->get_result();
    
    if ($cluster_result && $cluster_result->num_rows > 0) {
        $cluster_data = $cluster_result->fetch_assoc();
        $cluster = $cluster_data['cluster'];
    } else {
        // If no cluster is assigned by admin, set a default value
        $cluster = 'Not Assigned';
    }
    $cluster_stmt->close();
    
    if ($existing_profile) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE student_profiles SET school_id=?, full_name=?, program=?, cluster=?, school_year=? WHERE user_id=?");
        $stmt->bind_param("sssssi", $school_id, $full_name, $program, $cluster, $school_year, $user_id);
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO student_profiles (user_id, school_id, full_name, program, cluster, school_year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $school_id, $full_name, $program, $cluster, $school_year);
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
        
        // Refresh adviser data if profile was updated
        if ($existing_profile && $existing_profile['faculty_id']) {
            $faculty_id = $existing_profile['faculty_id'];
            
            $adviser_query = "SELECT * FROM faculty WHERE id = ?";
            
            $adviser_stmt = $conn->prepare($adviser_query);
            $adviser_stmt->bind_param("i", $faculty_id);
            $adviser_stmt->execute();
            $adviser_result = $adviser_stmt->get_result();
            
            if ($adviser_result && $adviser_result->num_rows > 0) {
                $assigned_adviser = $adviser_result->fetch_assoc();
            }
            $adviser_stmt->close();
        }
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
    <link rel="icon" href="assets/img/sms-logo.png" type="image/png">
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #ffffff;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease-out;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease-out;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .enhanced-header {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.9) 0%, rgba(124, 58, 237, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .info-card {
            background: rgba(249, 250, 251, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .info-card:hover::before {
            left: 100%;
        }
        
        .info-card:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .input-field {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(209, 213, 219, 0.5);
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            border-color: rgba(37, 99, 235, 0.5);
        }
        
        .cluster-display {
            background: rgba(243, 244, 246, 0.8);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(209, 213, 219, 0.5);
            min-height: 3rem;
            display: flex;
            align-items: center;
        }
        
        .enhanced-button {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .enhanced-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .enhanced-button:hover::before {
            left: 100%;
        }
        
        .enhanced-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .adviser-card {
            background: rgba(249, 250, 251, 0.8);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .adviser-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 text-gray-800 font-sans h-screen overflow-hidden">

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
            <div class="max-w-4xl mx-auto">
                

                <?php if (!empty($message)): ?>
                    <div class="mb-6 p-4 rounded-md <?php echo strpos($message, 'Error') !== false ? 'bg-danger/20 text-danger' : 'bg-success/20 text-success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="profile-card rounded-xl overflow-hidden animate-delay-1">
                    <div class="enhanced-header p-6 text-white">
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
                                <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                                <select 
                                    id="program" 
                                    name="program" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition input-field"
                                    required
                                >
                                   <option value="">Select Program</option>
                                    <option value="BS Information Technology" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Information Technology') ? 'selected' : ''; ?>>BS Information Technology (BSIT)</option>
                                    <option value="BS Hospitality Management" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Hospitality Management') ? 'selected' : ''; ?>>BS Hospitality Management (BSHM)</option>
                                    <option value="BS Office Administration" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Office Administration') ? 'selected' : ''; ?>>BS Office Administration (BSOA)</option>
                                    <option value="BS Business Administration" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Business Administration') ? 'selected' : ''; ?>>BS Business Administration (BSBA)</option>
                                    <option value="BS Criminology" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Criminology') ? 'selected' : ''; ?>>BS Criminology (BSCRIM)</option>
                                    <option value="Bachelor of Elementary Education" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'Bachelor of Elementary Education') ? 'selected' : ''; ?>>Bachelor of Elementary Education (BEED)</option>
                                    <option value="Bachelor of Secondary Education" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'Bachelor of Secondary Education') ? 'selected' : ''; ?>>Bachelor of Secondary Education (BSED)</option>
                                    <option value="BS Computer Engineering" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Computer Engineering') ? 'selected' : ''; ?>>BS Computer Engineering (BSCE)</option>
                                    <option value="BS Tourism Management" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Tourism Management') ? 'selected' : ''; ?>>BS Tourism Management (BSTM)</option>
                                    <option value="BS Entrepreneurship" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Entrepreneurship') ? 'selected' : ''; ?>>BS Entrepreneurship (BSE)</option>
                                    <option value="BS Accounting Information System" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Accounting Information System') ? 'selected' : ''; ?>>BS Accounting Information System (BSAIS)</option>
                                    <option value="BS Psychology" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BS Psychology') ? 'selected' : ''; ?>>BS Psychology (BSPSYCH)</option>
                                    <option value="BL Information Science" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BL Information Science') ? 'selected' : ''; ?>>BL Information Science (BLIS)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="cluster" class="block text-sm font-medium text-gray-700 mb-1">Cluster</label>
                                <div class="cluster-display">
                                    <?php 
                                    if (isset($existing_profile['cluster'])) {
                                        echo htmlspecialchars($existing_profile['cluster']);
                                    } else {
                                        echo 'Cluster will be assigned automatically based on your course and school year';
                                    }
                                    ?>
                                </div>
                                <input 
                                    type="hidden" 
                                    id="cluster" 
                                    name="cluster" 
                                    value="<?php echo isset($existing_profile['cluster']) ? htmlspecialchars($existing_profile['cluster']) : ''; ?>"
                                >
                            </div>
                            
                            <div>
                                <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                                <input 
                                    type="text" 
                                    id="school_year" 
                                    name="school_year" 
                                    value="<?php echo isset($existing_profile['school_year']) ? htmlspecialchars($existing_profile['school_year']) : date('Y') . '-' . (date('Y') + 1); ?>" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition input-field"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit" 
                                class="enhanced-button px-6 py-3 text-white font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 flex items-center"
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
                                    <p class="text-sm text-gray-500">Program</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($existing_profile['program']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="bg-primary/10 p-3 rounded-full mr-4">
                                    <i class="fas fa-users text-primary text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Cluster</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($existing_profile['cluster']); ?></p>
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
                
                <!-- Assigned Adviser Section -->
                <?php if (isset($existing_profile) && $existing_profile): ?>
                <div class="mt-8 bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-secondary p-6 text-white">
                        <h2 class="text-2xl font-semibold">Assigned Adviser</h2>
                        <p class="opacity-90">Your academic adviser for <?php echo htmlspecialchars($existing_profile['school_year']); ?></p>
                    </div>
                    
                    <div class="p-6">
                        <?php if ($assigned_adviser): ?>
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 p-6 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-24 h-24 rounded-full bg-secondary/20 flex items-center justify-center">
                                    <i class="fas fa-user-tie text-secondary text-3xl"></i>
                                </div>
                            </div>
                            
                            <div class="flex-grow text-center md:text-left">
                                <h3 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($assigned_adviser['fullname']); ?></h3>
                                <p class="text-gray-600"><?php echo htmlspecialchars($assigned_adviser['department']); ?> Department</p>
                                
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Expertise</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($assigned_adviser['expertise']); ?></p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Contact</p>
                                        <p class="font-medium"><?php echo strtolower(str_replace(' ', '.', $assigned_adviser['fullname'])); ?>@school.edu</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0">
                                <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center">
                                    <i class="fas fa-envelope mr-2"></i>
                                    Contact
                                </button>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center p-8 bg-warning/10 rounded-lg">
                            <i class="fas fa-user-clock text-warning text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Adviser Assigned Yet</h3>
                            <p class="text-gray-600">Your section has not been assigned an adviser yet. Please check back later.</p>
                        </div>
                        <?php endif; ?>
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