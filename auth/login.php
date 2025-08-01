<?php
session_start();
include("../includes/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statement
    $stmt = $conn->prepare("SELECT * FROM login_tbl WHERE username = ?");
    $stmt->bind_param("s", $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id']; // match DB column
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // fetched from DB directly

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] === 'faculty') {
                header("Location: ../faculty/faculty.html");
            } elseif ($user['role'] === 'student') {
                header("Location: ../student/student.html");
            } else {
                echo "<script>alert('Unknown user role'); window.history.back();</script>";
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that username/email'); window.history.back();</script>";
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
    
    <!-- Left panel -->
    <div class="md:w-1/2 p-10 flex flex-col justify-center items-start relative bg-blue-50">
      <h1 class="text-3xl md:text-4xl font-extrabold text-blue-900 mb-3">Welcome to</h1>
      <h2 class="text-4xl md:text-5xl font-extrabold text-blue-700 mb-6 leading-tight">CRAD SYSTEM</h2>
      <p class="text-gray-700 mb-8 max-w-md">
        Efficiently manage research proposals, monitor statuses, assign advisers and panels, and explore AI-powered categorization — all in one place.
      </p>
      <a
        href="landing.php"
        class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-3 rounded-md shadow transition duration-300"
      >
        SMS
      </a>
      <div class="absolute right-8 bottom-6 w-32 h-32">
        <img src="../assets/img/logo.png" alt="School Logo" class="w-full h-full object-contain" />
      </div>
    </div>

    <!-- Right panel -->
    <div class="md:w-1/2 bg-white p-10 flex flex-col justify-center">
      <h3 class="text-2xl font-bold text-blue-900 mb-6 text-center">
        Log in to your account
      </h3>
      
      <form id="loginForm" action="login.php" method="POST" class="space-y-6">
        <!-- Username -->
        <div>
          <label for="username" class="block text-blue-900 font-semibold mb-1">Username or Email</label>
          <input
            type="text"
            id="username"
            name="username"
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
            class="w-full bg-blue-700 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-800 transition duration-300"
          >
            Log In
          </button>
        </div>
      </form>

      <p class="mt-6 text-center text-gray-600 text-sm">
        Don’t have an account?
        <a href="register.php" class="text-blue-700 hover:underline">Sign up</a>
      </p>
    </div>
  </div>
</body>
</html>
