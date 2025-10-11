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
    width: 230px;
    height: 230px;
    transition: all 0.3s ease;
  }
  .role-box:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
  }

  /* Smooth fade-in animation */
  @keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade-in {
    animation: fade-in 1s ease forwards;
  }

</style>
</head>
<body class="bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center font-sans" 
style="background-image: linear-gradient(rgba(250, 250, 250, 0.937), rgba(8, 52, 117, 0.942)), url('../assets/img/img.jpg');">
  <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl max-w-5xl w-full mx-4 flex flex-col md:flex-row overflow-hidden border border-gray-200">
    
    <!-- Left Panel (soft gradient to match body) -->
    <div class="md:w-1/2 p-10 flex flex-col justify-center items-center text-center 
                bg-gradient-to-br from-white via-blue-50 to-blue-100 text-gray-800 relative">
      <img src="../assets/img/sms-logo.png" alt="School Logo" class="w-32 h-32 mb-6 rounded-full shadow-lg border-4 border-white" />

      <h1 class="text-4xl md:text-5xl font-extrabold mb-3 tracking-tight text-blue-900">Welcome to</h1>
      <h2 class="text-5xl font-extrabold mb-4 text-blue-700 drop-shadow-md">School Management System</h2>
      <p class="text-gray-700 mb-8 max-w-md leading-relaxed font-medium">
        Empowering education through a unified academic management system that enhances learning, streamlines processes, and connects the academic community.
      </p>
      <a
        href="../auth/landing.php"
        class="bg-blue-700 hover:bg-blue-800 text-white font-extrabold px-6 py-3 rounded-lg shadow-md transition-all duration-300"
      >
        Learn More
      </a>
    </div>

    <!-- Right Panel -->
<div class="md:w-1/2 bg-white p-10 flex flex-col justify-center items-center text-center">
  
  <!-- Welcome Section -->
  <div class="mb-10">
    <h2 class="text-4xl font-extrabold text-blue-900 mb-2 tracking-wide">
      <span class="text-blue-700">CRAD</span>
    </h2>
    <p class="text-gray-600 text-sm italic">
      Intelligent Progressive Research Submission and Tracking System
    </p>
  </div>

  <!-- Role Selection -->
  <h3 class="text-2xl font-bold text-blue-900 mb-8 tracking-wide">
    Choose Your Role
  </h3>

  <div class="flex flex-col md:flex-row gap-8 w-full justify-center">
    <!-- Admin Box -->
    <a href="../auth/admin-login.php"
      class="role-box group bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white flex flex-col items-center justify-center rounded-2xl shadow-lg p-8 text-center transform hover:scale-105 transition-all duration-300">
      <div class="bg-blue-500 p-4 rounded-full mb-4 shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      </div>
      <span class="text-xl font-semibold">Administrator</span>
      <span class="text-sm opacity-90 mt-2">Access admin dashboard</span>
    </a>

    <!-- Student Box -->
    <a href="../auth/student-login.php"
      class="role-box group bg-gradient-to-br from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white flex flex-col items-center justify-center rounded-2xl shadow-lg p-8 text-center transform hover:scale-105 transition-all duration-300">
      <div class="bg-green-500 p-4 rounded-full mb-4 shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M12 14l9-5-9-5-9 5 9 5z" />
          <path
            d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
        </svg>
      </div>
      <span class="text-xl font-semibold">Student</span>
      <span class="text-sm opacity-90 mt-2">Access student portal</span>
    </a>
  </div>

  <!-- Support -->
  <p class="mt-10 text-gray-600 text-sm">
    Need help? 
    <a href="#" class="text-blue-700 font-semibold hover:underline">Contact support</a>
  </p>
</div>

</body>

</html>