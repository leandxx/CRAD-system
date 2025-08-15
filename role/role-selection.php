<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <title>School Management System - Select Role</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .role-box {
      width: 220px;
      height: 220px;
      transition: all 0.3s ease;
    }
    .role-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body 
  class="bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center font-sans" 
  style="background-image: linear-gradient(rgba(250, 250, 250, 0.937), rgba(8, 52, 117, 0.942)), url('../assets/img/img.jpg');"
>
  <div class="bg-white bg-opacity-80 rounded-lg shadow-lg max-w-4xl w-full mx-4 flex flex-col md:flex-row overflow-hidden">
    
    <!-- Left panel (same as login page) -->
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

    <!-- Right panel - Role Selection -->
    <div class="md:w-1/2 bg-white p-10 flex flex-col justify-center items-center">
      <h3 class="text-2xl font-bold text-blue-900 mb-8 text-center">
        Select Your Role
      </h3>
      
      <div class="flex flex-col md:flex-row gap-6 w-full justify-center">
        <!-- Admin Box -->
        <a href="../auth/login.php" class="role-box bg-blue-600 text-white flex flex-col items-center justify-center rounded-lg shadow-md p-6 text-center">
          <div class="bg-blue-500 p-3 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <span class="text-xl font-bold">Administrator</span>
          <span class="text-sm opacity-90 mt-2">Access admin dashboard</span>
        </a>
        
        <!-- Student Box -->
        <a href="../auth/login.php" class="role-box bg-green-600 text-white flex flex-col items-center justify-center rounded-lg shadow-md p-6 text-center">
          <div class="bg-green-500 p-3 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M12 14l9-5-9-5-9 5 9 5z" />
              <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
          </div>
          <span class="text-xl font-bold">Student</span>
          <span class="text-sm opacity-90 mt-2">Access student portal</span>
        </a>
      </div>

      <p class="mt-8 text-center text-gray-600 text-sm">
        Need help? <a href="#" class="text-blue-700 hover:underline">Contact support</a>
      </p>
    </div>
  </div>
</body>
</html>