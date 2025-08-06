<?php
include('../includes/connection.php'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
  <title>CRAD Faculty Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/faculty.css">
  <style>
    /* Additional styles can be added here */
  </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/faculty-sidebar.php'; ?>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-3">
                <h1 class="text-2xl font-bold text-gray-800">Faculty Research Facilities</h1>
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 relative">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                    </button>
                    <button class="flex items-center space-x-2 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="hidden md:inline">John D. Researcher</span>
                    </button>
                </div>
            </header>
            
            <!-- Research Facilities Dashboard -->
            <div class="space-y-6 p-6">
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Active Projects</p>
                                <h3 class="text-2xl font-bold">12</h3>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-flask text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Available Labs</p>
                                <h3 class="text-2xl font-bold">5</h3>
                            </div>
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-microscope text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Pending Requests</p>
                                <h3 class="text-2xl font-bold">3</h3>
                            </div>
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Research Facilities Section -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Available Research Facilities</h2>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition" id="requestFacilityBtn">
                            <i class="fas fa-plus mr-2"></i>Request Facility
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text