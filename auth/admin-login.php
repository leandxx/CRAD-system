<?php
session_start();
include("../includes/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']); // changed from 'username'
    $password = $_POST['password'];

    // Use prepared statement to find user by email
    $stmt = $conn->prepare("SELECT * FROM user_tbl WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
           if ($user['role'] === 'Admin') {
                header("Location: ../admin-pages/admin-dashboard.php");
            } elseif ($user['role'] === 'Staff') {
                header("Location: ../staff-pages/staff-dashboard.php");
            } else {
                echo "<script>alert('Unknown user role'); window.history.back();</script>";
            }

            exit();
        } else {
            echo "<script>alert('Incorrect password'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that email'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <title>School Management System - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body 
 class="bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center font-sans" 
  style="background-image: linear-gradient(rgba(250, 250, 250, 0.937), rgba(8, 52, 117, 0.942)), url('../assets/img/img.jpg');"
>
  <div class="bg-white bg-opacity-80 rounded-lg shadow-lg max-w-4xl w-full mx-4 flex flex-col md:flex-row overflow-hidden">
    
    <!-- Left panel (Welcome section) -->
    <div class="md:w-1/2 p-10 flex flex-col justify-center items-start relative bg-blue-50 overflow-hidden">
      
      <!-- Background Logo (behind text) -->
      <div class="absolute inset-0 flex items-center justify-center opacity-10 z-0">
        <img src="../assets/img/sms-logo.png" alt="School Logo" class="w-3/4 h-auto object-contain" />
      </div>

            <!-- Text content (above logo) -->
        <div class="relative z-10 font-bold">
          <h1 class="text-3xl md:text-4xl font-extrabold text-blue-900 mb-3">Welcome to</h1>
          <h2 class="text-4xl md:text-5xl font-extrabold text-blue-700 mb-6 leading-tight">SMS1</h2>
          <p class="text-gray-800 mb-8 max-w-md font-semibold">
            Empowering education through a unified academic management system that enhances learning, streamlines processes, and connects the entire academic community.
          </p>
          <a
            href="../role/role-selection.php"
            class="bg-blue-700 hover:bg-blue-800 text-white font-extrabold px-6 py-3 rounded-md shadow transition duration-300"
          >
            SMS
          </a>
        </div>
  </div>

    <!-- Right panel -->
    <div class="md:w-1/2 bg-white p-10 flex flex-col justify-center">
      <h3 class="text-2xl font-bold text-blue-900 mb-6 text-center">
        Welcome Back, Admin
      </h3>
      
      <form id="loginForm" action="admin-login.php" method="POST" class="space-y-6">
        <!-- Username -->
        <div>
          <label for="email" class="block text-blue-900 font-semibold mb-1">Email</label>
          <input
            type="text"
            id="email"
            name="email"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-blue-900 font-semibold mb-1">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <!-- Submit -->
        <div>
          <button
            type="submit"
            class="w-full bg-blue-700 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-800 transition duration-300">
            Log In
          </button>
        </div>
      </form>

    </div>
  </div>
</body>
</html>
