<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Facilities</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('../includes/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-blue-700">Facilities Assignment</h1>
                <p class="text-gray-600">Automatically assign defense rooms based on your final capstone submission</p>
            </div>

            <!-- Notification -->
            <div class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded mb-6">
                <strong>Note:</strong> Once your full capstone is submitted, the system will automatically assign a facility for your defense using AI-powered scheduling.
            </div>

            <!-- Capstone Submission Card -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Capstone Submission Status -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-blue-700 mb-2">Your Capstone Submission</h2>
                    <p class="text-gray-700">Your group has submitted the final capstone document on <strong>August 2, 2025</strong>.</p>
                    <p class="text-green-600 font-medium mt-2">Status: Submitted</p>
                </div>

                <!-- Assigned Facility -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-blue-700 mb-2">Assigned Facility</h2>
                    <p class="text-gray-700">Based on AI scheduling, your group is assigned to:</p>
                    <p class="text-lg font-semibold text-gray-800 mt-2">Room 402 - Tech Lab Building</p>
                    <p class="text-gray-600 mt-1">Scheduled on: <strong>August 10, 2025 | 10:00 AM</strong></p>
                </div>
            </div>

            <!-- AI Recommendation Explanation -->
            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold text-blue-700 mb-4">How this was decided</h3>
                <ul class="list-disc pl-6 text-gray-700 space-y-1">
                    <li>Availability of faculty evaluators</li>
                    <li>Group size and capstone domain</li>
                    <li>Technical equipment requirements</li>
                    <li>Room availability and capacity</li>
                </ul>

                <p class="mt-4 text-sm text-gray-500 italic">* This recommendation was generated through OpenAI's scheduling model based on your capstone data.</p>
            </div>
        </main>
    </div>
</body>

</html>