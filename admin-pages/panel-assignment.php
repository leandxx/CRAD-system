<?php
include('../includes/connection.php');

// Handle CRUD operations
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        // Add new panel member - with validation
        $first_name = trim($conn->real_escape_string($_POST['first_name'] ?? ''));
        $last_name = trim($conn->real_escape_string($_POST['last_name'] ?? ''));
        $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
        $specialization = trim($conn->real_escape_string($_POST['specialization'] ?? ''));
        $status = $conn->real_escape_string($_POST['status'] ?? 'active');
        
        // Validate inputs
        if (empty($first_name)) {
            $error = "First name is required";
        } elseif (empty($last_name)) {
            $error = "Last name is required";
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Valid email is required";
        } elseif (empty($specialization)) {
            $error = "Specialization is required";
        } else {
            // Check if email already exists
            $check_sql = "SELECT id FROM panel_members WHERE email = '$email'";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error = "A panel member with this email already exists";
            } else {
                $sql = "INSERT INTO panel_members (first_name, last_name, email, specialization, status) 
                        VALUES ('$first_name', '$last_name', '$email', '$specialization', '$status')";
                
                if ($conn->query($sql)) {
                    $success = "Panel member added successfully";
                    // Clear POST data to prevent form repopulation
                    unset($_POST);
                } else {
                    $error = "Error adding panel member: " . $conn->error;
                }
            }
        }
    }
    elseif ($action == 'edit') {
        // Edit panel member
        $id = intval($_POST['id']);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $specialization = $conn->real_escape_string($_POST['specialization']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Validate inputs
        if (empty($first_name) || empty($last_name) || empty($email) || empty($specialization)) {
            $error = "All fields are required";
        } else {
            // Check if email already exists for another panel member
            $check_sql = "SELECT id FROM panel_members WHERE email = '$email' AND id != $id";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error = "A panel member with this email already exists";
            } else {
                $sql = "UPDATE panel_members SET 
                        first_name = '$first_name',
                        last_name = '$last_name',
                        email = '$email',
                        specialization = '$specialization',
                        status = '$status'
                        WHERE id = $id";
                
                if ($conn->query($sql)) {
                    $success = "Panel member updated successfully";
                    // Redirect to clear the edit_id from URL
                    header("Location: panel-management.php?success=Panel+member+updated+successfully");
                    exit();
                } else {
                    $error = "Error updating panel member: " . $conn->error;
                }
            }
        }
    }
    elseif ($action == 'delete') {
        // Delete panel member
        $id = intval($_POST['id']);
        
        $sql = "DELETE FROM panel_members WHERE id = $id";
        
        if ($conn->query($sql)) {
            $success = "Panel member deleted successfully";
        } else {
            $error = "Error deleting panel member: " . $conn->error;
        }
    }
    elseif ($action == 'send_availability') {
        $panel_ids = $_POST['panel_ids'] ?? [];
        $subject = $conn->real_escape_string($_POST['subject']);
        $message = $conn->real_escape_string($_POST['message']);
        $questions = $_POST['questions'] ?? [];
        
        if (empty($panel_ids)) {
            $error = "Please select at least one panel member";
        } else {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Check if admin has authenticated with Gmail
            if (!isset($_SESSION['gmail_authenticated'])) {
                $_SESSION['email_data'] = [
                    'panel_ids' => $panel_ids,
                    'subject' => $subject,
                    'message' => $message,
                    'questions' => $questions
                ];
                header("Location: gmail-auth.php");
                exit();
            }
            
            // Proceed with email sending (original PHPMailer code)
            require '../vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_SESSION['gmail_email'];
                $mail->Password = $_SESSION['gmail_token']; // Use app password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Rest of your email sending code...
                // Make sure to properly close all the braces here
            } catch (Exception $e) {
                $error = "Email error: " . $e->getMessage();
            }
        }
    }
}

// Get panel member data for editing
$editData = [];
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $query = "SELECT * FROM panel_members WHERE id = $edit_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    } else {
        header("Location: panel-management.php?error=Panel+member+not+found");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Management System</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notification-dot {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        [multiple] {
            height: auto;
            min-height: 42px;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            transition: opacity 200ms ease-in-out;
        }
        .modal-content {
            transform: translateY(-20px);
            transition: transform 200ms ease-in-out, opacity 200ms ease-in-out;
        }
        .modal-active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content-active {
            transform: translateY(0);
        }
        .email-preview {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            background-color: #f8fafc;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
    <div class="min-h-screen flex">
         <!-- Sidebar/header -->
        <?php include('../includes/admin-sidebar.php'); ?>
        <?php include('../includes/admin-header.php'); ?>

       
            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $success; ?></span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Panel List -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-users mr-2 text-blue-500"></i>
                            Panel Members
                        </h2>
                        <div class="flex space-x-2">
                            <!-- Availability Request Button -->
                            <button id="availabilityRequestBtn" 
                                    class="bg-green-500 text-white px-3 py-2 rounded-md hover:bg-green-600 transition flex items-center"
                                    title="Request Availability from Panel Members">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <span class="hidden md:inline">Request Availability</span>
                            </button>
                            
                            <button 
                                id="addPanelBtn" 
                                class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 transition flex items-center"
                            >
                                <i class="fas fa-plus mr-1"></i>
                                <span class="hidden md:inline">Add Panel Member</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $query = "SELECT * FROM panel_members ORDER BY last_name, first_name";
                                $result = $conn->query($query);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo '<tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <i class="fas fa-user text-indigo-600"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">'.$row['first_name'].' '.$row['last_name'].'</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">'.$row['specialization'].'</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">'.$row['email'].'</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full '.($row['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800').'">'.ucfirst($row['status']).'</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="?edit_id='.$row['id'].'" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit"><i class="fas fa-edit"></i></a>
                                                <form action="" method="POST" class="inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="'.$row['id'].'">
                                                    <button type="submit" onclick="return confirm(\'Are you sure you want to delete this panel member?\')" class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No panel members found</td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Panel Modal -->
    <div id="addPanelModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="modal-overlay absolute inset-0 opacity-0 transition-opacity"></div>
        <div class="modal-content relative bg-white rounded-lg shadow-xl p-6 w-11/12 md:w-2/3 lg:w-1/2 max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">
                    <i class="fas fa-user-plus text-blue-500 mr-2"></i>
                    Add New Panel Member
                </h3>
                <button id="closeAddPanelModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="panelFirstName" class="block text-sm font-medium text-gray-700 required">First Name</label>
                        <input type="text" id="panelFirstName" name="first_name" 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" 
                               required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="panelLastName" class="block text-sm font-medium text-gray-700 required">Last Name</label>
                        <input type="text" id="panelLastName" name="last_name" 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" 
                               required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div>
                    <label for="panelEmail" class="block text-sm font-medium text-gray-700 required">Email</label>
                    <input type="email" id="panelEmail" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="panelSpecialization" class="block text-sm font-medium text-gray-700 required">Specialization</label>
                    <input type="text" id="panelSpecialization" name="specialization" 
                           value="<?php echo htmlspecialchars($_POST['specialization'] ?? ''); ?>" 
                           required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="panelStatus" class="block text-sm font-medium text-gray-700 required">Status</label>
                    <select id="panelStatus" name="status" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="active" <?php echo ($_POST['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_POST['status'] ?? 'active') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelAddPanel" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 transition flex items-center">
                        <i class="fas fa-save mr-1"></i> Save Panel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Panel Modal -->
    <div id="editPanelModal" class="fixed inset-0 z-50 flex items-center justify-center <?php echo !isset($_GET['edit_id']) ? 'hidden' : ''; ?>">
        <div class="modal-overlay absolute inset-0 <?php echo !isset($_GET['edit_id']) ? 'opacity-0' : 'opacity-50'; ?>"></div>
        <div class="modal-content relative bg-white rounded-lg shadow-xl p-6 w-11/12 md:w-2/3 lg:w-1/2 max-w-2xl <?php echo !isset($_GET['edit_id']) ? 'opacity-0 transform -translate-y-20' : 'opacity-100 transform translate-y-0'; ?>">
            <?php if (!empty($editData)): ?>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">
                    <i class="fas fa-user-edit text-blue-500 mr-2"></i>
                    Edit Panel Member
                </h3>
                <a href="?" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </a>
            </div>
            
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="editFirstName" class="block text-sm font-medium text-gray-700 required">First Name</label>
                        <input type="text" id="editFirstName" name="first_name" value="<?php echo htmlspecialchars($editData['first_name']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="editLastName" class="block text-sm font-medium text-gray-700 required">Last Name</label>
                        <input type="text" id="editLastName" name="last_name" value="<?php echo htmlspecialchars($editData['last_name']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div>
                    <label for="editEmail" class="block text-sm font-medium text-gray-700 required">Email</label>
                    <input type="email" id="editEmail" name="email" value="<?php echo htmlspecialchars($editData['email']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="editSpecialization" class="block text-sm font-medium text-gray-700 required">Specialization</label>
                    <input type="text" id="editSpecialization" name="specialization" value="<?php echo htmlspecialchars($editData['specialization']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="editStatus" class="block text-sm font-medium text-gray-700 required">Status</label>
                    <select id="editStatus" name="status" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="active" <?php echo $editData['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $editData['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="?" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-primary hover:bg-blue-700">
                        <i class="fas fa-save mr-1"></i> Update Panel
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Availability Request Modal -->
    <div id="availabilityModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="modal-overlay absolute inset-0 opacity-0 transition-opacity"></div>
        <div class="modal-content relative bg-white rounded-lg shadow-xl p-6 w-11/12 md:w-2/3 lg:w-1/2 max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                    Request Panel Availability
                </h3>
                <button id="closeAvailabilityModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="send_availability">
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="panelSelect" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-users mr-2 text-blue-500"></i>
                            Select Panel Member(s) *
                        </label>
                        <select id="panelSelect" name="panel_ids[]" multiple class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary h-auto">
                            <?php
                            $query = "SELECT * FROM panel_members WHERE status = 'active' ORDER BY last_name, first_name";
                            $result = $conn->query($query);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<option value="'.$row['id'].'">'.$row['first_name'].' '.$row['last_name'].' ('.$row['specialization'].')</option>';
                                }
                            }
                            ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple panel members</p>
                    </div>
                    <div>
                        <label for="formSubject" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-envelope mr-2 text-blue-500"></i>
                            Email Subject *
                        </label>
                        <input type="text" id="formSubject" name="subject" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" 
                               value="Request for Defense Availability" required>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label for="formMessage" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                        <i class="fas fa-comment-alt mr-2 text-blue-500"></i>
                        Message to Panel *
                    </label>
                    <textarea id="formMessage" name="message" rows="5" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" required>Dear Panel Member,

Please provide your availability for the upcoming thesis defenses by completing the following information. This will help us schedule defenses at convenient times for all parties involved.

Thank you for your cooperation.

Best regards,
Thesis Coordinator</textarea>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-question-circle mr-2 text-blue-500"></i>
                        Availability Information Needed *
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <input type="checkbox" id="qDays" name="questions[]" value="Preferred days" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded mt-1" checked>
                            <label for="qDays" class="ml-2 block text-sm text-gray-700">
                                <span class="font-medium">Preferred days:</span> Which days of the week are you typically available for defenses?
                            </label>
                        </div>
                        <div class="flex items-start">
                            <input type="checkbox" id="qTimes" name="questions[]" value="Time slots" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded mt-1" checked>
                            <label for="qTimes" class="ml-2 block text-sm text-gray-700">
                                <span class="font-medium">Time slots:</span> What time slots work best for you (morning, afternoon, specific hours)?
                            </label>
                        </div>
                        <div class="flex items-start">
                            <input type="checkbox" id="qConflict" name="questions[]" value="Blackout dates" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded mt-1" checked>
                            <label for="qConflict" class="ml-2 block text-sm text-gray-700">
                                <span class="font-medium">Blackout dates:</span> Are there any specific dates when you will NOT be available?
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="previewEmail" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-eye mr-1"></i> Preview Email
                    </button>
                    <button type="button" id="cancelAvailability" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-1"></i> Send Request
                    </button>
                    <button type="button" id="connectGmail" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fab fa-google mr-1"></i> Connect Gmail
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Preview Modal -->
    <div id="previewModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="modal-overlay absolute inset-0 opacity-0 transition-opacity"></div>
        <div class="modal-content relative bg-white rounded-lg shadow-xl p-6 w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">
                    <i class="fas fa-envelope-open-text text-blue-500 mr-2"></i>
                    Email Preview
                </h3>
                <button id="closePreviewModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="email-preview" id="emailPreviewContent">
                <!-- Preview content will be inserted here -->
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="button" id="closePreview" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-times mr-1"></i> Close Preview
                </button>
            </div>
        </div>
    </div>

    <script>
                    document.getElementById('connectGmail').addEventListener('click', function() {
                window.open('admin-pages/gmail-auth.php', 'gmailAuth', 'width=500,height=600');
            });

            // Listen for auth completion
            window.addEventListener('message', function(e) {
                if (e.data.gmailAuth) {
                    alert('Gmail connected successfully!');
                }
            });
        // Modal handling functions
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            const overlay = modal.querySelector('.modal-overlay');
            const content = modal.querySelector('.modal-content');
            
            if (show) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    overlay.classList.add('opacity-50');
                    content.classList.add('opacity-100', 'transform', 'translate-y-0');
                }, 20);
            } else {
                overlay.classList.remove('opacity-50');
                content.classList.remove('opacity-100', 'transform', 'translate-y-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 200);
            }
        }

        // Event listeners for modals
        document.getElementById('addPanelBtn').addEventListener('click', () => toggleModal('addPanelModal', true));
        document.getElementById('closeAddPanelModal').addEventListener('click', () => toggleModal('addPanelModal', false));
        document.getElementById('cancelAddPanel').addEventListener('click', () => toggleModal('addPanelModal', false));

        document.getElementById('availabilityRequestBtn').addEventListener('click', () => toggleModal('availabilityModal', true));
        document.getElementById('closeAvailabilityModal').addEventListener('click', () => toggleModal('availabilityModal', false));
        document.getElementById('cancelAvailability').addEventListener('click', () => toggleModal('availabilityModal', false));

        // Close modals when clicking outside
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function() {
                const modal = this.closest('.fixed');
                toggleModal(modal.id, false);
            });
        });

        // Auto-open edit modal if edit_id is in URL
        <?php if (isset($_GET['edit_id'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                toggleModal('editPanelModal', true);
            });
        <?php endif; ?>

        // Form validation for availability request
        document.querySelector('#availabilityModal form').addEventListener('submit', function(e) {
            const selectedPanels = Array.from(document.getElementById('panelSelect').selectedOptions);
            const selectedQuestions = Array.from(document.querySelectorAll('#availabilityModal input[type="checkbox"]:checked'));
            
            if (selectedPanels.length === 0) {
                alert('Please select at least one panel member');
                e.preventDefault();
                return;
            }
            
            if (selectedQuestions.length === 0) {
                alert('Please select at least one question to include in the request');
                e.preventDefault();
                return;
            }
            
            // Show confirmation dialog
            if (!confirm(`Are you sure you want to send this availability request to ${selectedPanels.length} panel member(s)?`)) {
                e.preventDefault();
            }
        });

        // Email preview functionality
        document.getElementById('previewEmail').addEventListener('click', function() {
            const subject = document.getElementById('formSubject').value;
            const message = document.getElementById('formMessage').value;
            const questions = Array.from(document.querySelectorAll('#availabilityModal input[type="checkbox"]:checked'))
                                 .map(cb => cb.nextElementSibling.textContent.trim());
            
            // Build preview content
            let previewContent = `
                <div class="container">
                    <div class="header bg-gray-100 p-4 rounded-t-lg">
                        <h2 class="text-xl font-bold">${escapeHtml(subject)}</h2>
                    </div>
                    <div class="content p-4">
                        <p class="mb-4">Dear Panel Member,</p>
                        ${message.split('\n').map(para => `<p class="mb-4">${escapeHtml(para)}</p>`).join('')}
                        <p class="font-bold mb-2">Please provide the following information:</p>
                        <ul class="list-disc pl-5 mb-4">`;
            
            questions.forEach(q => {
                previewContent += `<li class="mb-1">${escapeHtml(q)}</li>`;
            });
            
            previewContent += `
                        </ul>
                        <p class="mb-4">Please reply to this email with your availability at your earliest convenience.</p>
                        <p class="mb-4">Best regards,<br>Thesis Coordinator</p>
                    </div>
                    <div class="footer p-4 border-t border-gray-200 text-sm text-gray-600">
                        <p>This is a preview of how the email will appear to recipients.</p>
                    </div>
                </div>
            `;
            
            // Insert into preview modal
            document.getElementById('emailPreviewContent').innerHTML = previewContent;
            
            // Show preview modal
            toggleModal('previewModal', true);
        });
        
        // Helper function to escape HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Close preview modal
        document.getElementById('closePreviewModal').addEventListener('click', () => toggleModal('previewModal', false));
        document.getElementById('closePreview').addEventListener('click', () => toggleModal('previewModal', false));

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Escape key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[id$="Modal"].fixed:not(.hidden)').forEach(modal => {
                    toggleModal(modal.id, false);
                });
            }
        });
    </script>
</html>
<?php   
$conn->close();
?>