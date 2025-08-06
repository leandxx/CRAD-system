<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Submission</title>
    <link rel="icon" href="../assets/img/sms-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content with vertical scroll -->
        <main class="flex-1 p-6 overflow-y-auto h-screen">

            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-blue-700">Project Proposal</h1>
                <p class="text-gray-600">Submit or view your capstone proposal details</p>
            </div>

            <!-- Proposal Info Card -->
            <div class="bg-white rounded-xl shadow p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Proposal Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project Title</label>
                        <input type="text" placeholder="Enter your title..." class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project Type</label>
                        <select class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select type</option>
                            <option value="capstone">Capstone</option>
                            <option value="thesis">Thesis</option>
                            <option value="research">Research</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Project Description</label>
                        <textarea rows="5" placeholder="Brief project overview..." class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 transition">Submit Proposal</button>
                </div>
            </div>

            <!-- Submitted Proposals List -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Your Submitted Proposals</h2>
                <ul class="divide-y divide-gray-200">
                    <li class="py-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-800">SCHOOL MANAGEMENT SYSTEM 1</p>
                                <p class="text-sm text-gray-500">Submitted on: August 5, 2025</p>
                            </div>
                            <a href="#" class="text-blue-600 hover:underline text-sm">View Details</a>
                        </div>
                    </li>
                    <!-- Add more <li> if needed -->
                </ul>
            </div>

        </main>
    </div>

</body>
</html>
