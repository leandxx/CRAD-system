<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-blue-700">Analytics & Reports</h1>
                <p class="text-gray-600">Visual breakdown of your capstone progress, submissions, and performance</p>
            </div>

            <!-- Analytics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Proposal Status -->
                <div class="bg-white shadow rounded-lg p-5">
                    <h2 class="text-sm text-gray-500">Proposal Status</h2>
                    <p class="text-2xl font-semibold text-blue-600 mt-2">Submitted</p>
                </div>

                <!-- Adviser Feedbacks -->
                <div class="bg-white shadow rounded-lg p-5">
                    <h2 class="text-sm text-gray-500">Adviser Feedbacks</h2>
                    <p class="text-2xl font-semibold text-green-500 mt-2">4 Reviews</p>
                </div>

                <!-- Defense Progress -->
                <div class="bg-white shadow rounded-lg p-5">
                    <h2 class="text-sm text-gray-500">Defense Progress</h2>
                    <p class="text-2xl font-semibold text-yellow-500 mt-2">Pending</p>
                </div>

                <!-- Payment Completion -->
                <div class="bg-white shadow rounded-lg p-5">
                    <h2 class="text-sm text-gray-500">Payment Completion</h2>
                    <p class="text-2xl font-semibold text-red-500 mt-2">₱1,000 Pending</p>
                </div>
            </div>

            <!-- Placeholder for Charts -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Capstone Progress Chart</h3>
                <div class="w-full h-64 flex items-center justify-center text-gray-400 border border-dashed border-gray-300 rounded">
                    Chart visualization coming soon...
                </div>
            </div>
        </main>
    </div>
</body>
</html>