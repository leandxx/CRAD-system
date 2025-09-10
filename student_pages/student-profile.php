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

// Check group membership to determine if program should be locked
$group_assignment = null;
$in_group = false;
$group_program = null;
$group_cluster_id = null;
try {
    $grp_sql = "SELECT g.program, g.cluster_id FROM groups g JOIN group_members gm ON g.id = gm.group_id WHERE gm.student_id = ? LIMIT 1";
    $grp_stmt = $conn->prepare($grp_sql);
    $grp_stmt->bind_param("i", $user_id);
    $grp_stmt->execute();
    $grp_res = $grp_stmt->get_result();
    $group_assignment = $grp_res ? $grp_res->fetch_assoc() : null;
    $grp_stmt->close();
    if ($group_assignment) {
        $in_group = true;
        $group_program = $group_assignment['program'];
        $group_cluster_id = $group_assignment['cluster_id'];
    }
} catch (Exception $e) {}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_id = $_POST['school_id'];
    $full_name = $_POST['full_name'];
    $school_year = $_POST['school_year'];
    
    // Program/Cluster logic
    // - If student is in a group, force program to group's program; cluster follows group's cluster if assigned
    // - If not in a group, allow selecting program; cluster stays as-is or 'Not Assigned' on first creation
    if ($in_group) {
        $program = $group_program;
        if (!empty($group_cluster_id)) {
            $csql = "SELECT cluster, faculty_id FROM clusters WHERE id = ?";
            $cstmt = $conn->prepare($csql);
            $cstmt->bind_param("i", $group_cluster_id);
            $cstmt->execute();
            $cres = $cstmt->get_result();
            $cinfo = $cres ? $cres->fetch_assoc() : null;
            $cstmt->close();
            if ($cinfo) {
                $cluster = $cinfo['cluster'];
            } else {
                $cluster = isset($existing_profile['cluster']) && $existing_profile['cluster'] !== '' ? $existing_profile['cluster'] : 'Not Assigned';
            }
        } else {
            $cluster = isset($existing_profile['cluster']) && $existing_profile['cluster'] !== '' ? $existing_profile['cluster'] : 'Not Assigned';
        }
    } else {
        $program = isset($_POST['program']) ? $_POST['program'] : ($existing_profile['program'] ?? '');
        $cluster = $existing_profile ? ($existing_profile['cluster'] ?? 'Not Assigned') : 'Not Assigned';
    }
    
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

// After processing, ensure displayed cluster reflects group assignment if applicable
// If the student is a member of a group that has a cluster_id, mirror that cluster into student_profiles for consistency
try {
    $group_sql = "SELECT g.cluster_id FROM groups g JOIN group_members gm ON g.id = gm.group_id WHERE gm.student_id = ? LIMIT 1";
    $group_stmt = $conn->prepare($group_sql);
    $group_stmt->bind_param("i", $user_id);
    $group_stmt->execute();
    $group_result = $group_stmt->get_result();
    $group_data = $group_result ? $group_result->fetch_assoc() : null;
    $group_stmt->close();

    if ($group_data && !empty($group_data['cluster_id'])) {
        $cid = (int)$group_data['cluster_id'];
        $csql = "SELECT cluster, faculty_id, program FROM clusters WHERE id = ?";
        $cstmt = $conn->prepare($csql);
        $cstmt->bind_param("i", $cid);
        $cstmt->execute();
        $cres = $cstmt->get_result();
        $cinfo = $cres ? $cres->fetch_assoc() : null;
        $cstmt->close();

        if ($cinfo) {
            // If profile exists but has different cluster/program, update it to match admin/group assignment
            if ($existing_profile) {
                $needs_update = ($existing_profile['cluster'] !== $cinfo['cluster']) || ($existing_profile['program'] !== $cinfo['program']) || ((int)$existing_profile['faculty_id'] !== (int)$cinfo['faculty_id']);
                if ($needs_update) {
                    $usql = "UPDATE student_profiles SET cluster = ?, program = ?, faculty_id = ? WHERE user_id = ?";
                    $ustmt = $conn->prepare($usql);
                    $fid = $cinfo['faculty_id'] ? (int)$cinfo['faculty_id'] : null;
                    $ustmt->bind_param("ssii", $cinfo['cluster'], $cinfo['program'], $fid, $user_id);
                    $ustmt->execute();
                    $ustmt->close();
                    // Refresh existing_profile reference for rendering
                    $profile_check = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
                    $profile_check->bind_param("i", $user_id);
                    $profile_check->execute();
                    $profile_result = $profile_check->get_result();
                    $existing_profile = $profile_result->fetch_assoc();
                    $profile_check->close();
                }
            }
        }
    }
} catch (Exception $e) {
    // Silent fail for display sync; avoid breaking profile page
}
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
                                    <?php echo ($in_group ? 'disabled' : ''); ?>
                                    required
                                >
                                   <option value="" <?php echo (empty($existing_profile['program']) ? 'selected' : ''); ?>>Select Program</option>
                                    <option value="BSIT" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSIT') ? 'selected' : ''; ?>>BS Information Technology (BSIT)</option>
                                    <option value="BSHM" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSHM') ? 'selected' : ''; ?>>BS Hospitality Management (BSHM)</option>
                                    <option value="BSOA" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSOA') ? 'selected' : ''; ?>>BS Office Administration (BSOA)</option>
                                    <option value="BSBA" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSBA') ? 'selected' : ''; ?>>BS Business Administration (BSBA)</option>
                                    <option value="BSCRIM" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSCRIM') ? 'selected' : ''; ?>>BS Criminology (BSCRIM)</option>
                                    <option value="BEED" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BEED') ? 'selected' : ''; ?>>Bachelor of Elementary Education (BEED)</option>
                                    <option value="BSED" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSED') ? 'selected' : ''; ?>>Bachelor of Secondary Education (BSED)</option>
                                    <option value="BSCE" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSCE') ? 'selected' : ''; ?>>BS Computer Engineering (BSCE)</option>
                                    <option value="BSTM" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSTM') ? 'selected' : ''; ?>>BS Tourism Management (BSTM)</option>
                                    <option value="BSE" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSE') ? 'selected' : ''; ?>>BS Entrepreneurship (BSE)</option>
                                    <option value="BSAIS" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSAIS') ? 'selected' : ''; ?>>BS Accounting Information System (BSAIS)</option>
                                    <option value="BSPSYCH" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BSPSYCH') ? 'selected' : ''; ?>>BS Psychology (BSPSYCH)</option>
                                    <option value="BLIS" <?php echo (isset($existing_profile['program']) && $existing_profile['program'] == 'BLIS') ? 'selected' : ''; ?>>BL Information Science (BLIS)</option>
                                </select>
                                <?php if ($in_group): ?>
                                <input type="hidden" name="program" value="<?php echo htmlspecialchars($group_program); ?>">
                                <p class="text-xs text-gray-500 mt-1">Program is set by your group and cannot be changed here.</p>
                                <?php endif; ?>
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