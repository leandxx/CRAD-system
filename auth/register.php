<?php
session_start();
include("../includes/connection.php");

$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['userType'];

    // Validate full name
    if (empty($fullName) || !preg_match("/^[a-zA-Z\s]+$/", $fullName)) {
        $alertMessage = 'Please enter a valid full name (letters and spaces only).';
    }
    // Email validation
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alertMessage = 'Please enter a valid email address!';
    }
    // Password validation
    elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W]/', $password)
    ) {
        $alertMessage = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
    }
    // Match passwords
    elseif ($password !== $confirmPassword) {
        $alertMessage = 'Passwords do not match!';
    }
    else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM login_tbl WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $alertMessage = 'Email is already registered!';
            $stmt->close();
        } else {
            $stmt->close();
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO login_tbl (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullName, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
            } else {
                $alertMessage = 'Registration failed: ' . $stmt->error;
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <title>School Management System - Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <script>
    function togglePasswordVisibility(id) {
      const passwordField = document.getElementById(id);
      const type = passwordField.type === 'password' ? 'text' : 'password';
      passwordField.type = type;
    }
  </script>
</head>
<body 
  class="bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center font-sans" 
  style="background-image: linear-gradient(rgba(250, 250, 250, 0.937), rgba(8, 52, 117, 0.942)), url('../assets/img/img.jpg');"
>
  <div class="bg-white bg-opacity-80 rounded-lg shadow-lg max-w-4xl w-full mx-4 flex flex-col md:flex-row overflow-hidden">
    <!-- Left panel with welcome text and logo -->
    <div class="md:w-1/2 p-10 flex flex-col justify-center items-start relative bg-blue-50">
      <h1 class="text-3xl md:text-4xl font-extrabold text-blue-900 mb-3">
        Welcome to
      </h1>
      <h2 class="text-4xl md:text-5xl font-extrabold text-blue-700 mb-6 leading-tight">
        CRAD SYSTEM
      </h2>
      <p class="text-gray-700 mb-8 max-w-md">
        Efficiently manage research proposals, monitor statuses, assign advisers and panels, and explore AI-powered categorization — all in one place.
      </p>
      <button><a href="landing.php"
        class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-3 rounded-md shadow transition duration-300"
      >
        SMS
        </a>
      </button>
      <div class="absolute right-8 bottom-6 w-32 h-32">
        <img
          src="../assets/img/logo.png"
          alt="School Logo"
          class="w-full h-full object-contain"
        />
      </div>
    </div>

    <!-- Right panel with full rectangular registration form -->
    <div class="md:w-1/2 bg-white p-10 flex flex-col justify-center">
      <h3 class="text-2xl font-bold text-blue-900 mb-6 text-center">
        Create your account
      </h3>
      
      <form id="registrationForm" class="space-y-6" method="POST" action="">
        <!-- Alert Message -->
        <?php if ($alertMessage): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?php echo $alertMessage; ?></span>
          </div>
        <?php endif; ?>

        <!-- Full Name -->
<div>
  <label for="full_name" class="block text-blue-900 font-semibold mb-1">Full Name</label>
  <input
    type="text"
    id="full_name"
    name="full_name"
    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
    required
  />
</div>

<!-- Email -->
<div>
  <label for="email" class="block text-blue-900 font-semibold mb-1">Email</label>
  <input
    type="email"
    id="email"
    name="email"
    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
    required
  />
</div>

        <!-- Password -->
        <div class="relative">
          <label for="password" class="block text-blue-900 font-semibold mb-1">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
          <button type="button" onclick="togglePasswordVisibility('password')" class="absolute right-3 top-9">
            <i class="fas fa-eye"></i>
          </button>
        </div>

        <!-- Confirm Password -->
        <div class="relative">
          <label for="confirmPassword" class="block text-blue-900 font-semibold mb-1">Confirm Password</label>
          <input
            type="password"
            id="confirmPassword"
            name="confirmPassword"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
          <button type="button" onclick="togglePasswordVisibility('confirmPassword')" class="absolute right-3 top-9">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <p id="passwordMismatch" class="text-red-500 text-sm hidden">Passwords do not match!</p>

        <!-- User Type -->
        <div>
          <label for="userType" class="block text-blue-900 font-semibold mb-1">Select User Type</label>
          <select
            id="userType"
            name="userType"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="" disabled selected>Choose your role</option>
            <option value="Admin">Admin</option>
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
          </select>
        </div>

        <!-- Submit -->
        <div>
          <button
            type="submit"
            class="w-full bg-blue-700 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-800 transition duration-300"
          >
            Register
          </button>
        </div>
      </form>

      <!-- Login link -->
      <p class="mt-6 text-center text-gray-600 text-sm">
        Already have an account?
        <a href="login.php" class="text-blue-700 hover:underline">Log in</a>
      </p>
    </div>
  </div>

  <script>
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirmPassword');
    const passwordMismatchMessage = document.getElementById('passwordMismatch');

    confirmPasswordField.addEventListener('input', function() {
      if (passwordField.value !== confirmPasswordField.value) {
        passwordMismatchMessage.classList.remove('hidden');
      } else {
        passwordMismatchMessage.classList.add('hidden');
      }
    });
  </script>
</body>
</html>
