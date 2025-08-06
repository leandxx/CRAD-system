<?php
include('../includes/connection.php'); // Your DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defense Schedule</title>
    <link rel="icon" type="image/png" href="../assets/img/sms-logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <?php include('../student/student-sidebar.php'); ?>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-3xl font-bold mb-6">Defense Schedule</h1>

            <!-- Defense Schedule Table -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Your Defense Timeline</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Defense Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Panel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Example row -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">Final Defense</td>
                                <td class="px-6 py-4 whitespace-nowrap">September 25, 2025</td>
                                <td class="px-6 py-4 whitespace-nowrap">10:00 AM</td>
                                <td class="px-6 py-4 whitespace-nowrap">Dr. Reyes, Prof. Santos</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Scheduled
                                    </span>
                                </td>
                            </tr>
                            <!-- Add more rows dynamically with PHP/MySQL -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes or Instructions -->
            <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-sm text-yellow-700">
                    Please arrive 30 minutes before your scheduled time. Make sure all required documents are submitted at least 2 days prior to the defense.
                </p>
            </div>

        </main>
    </div>

</body>
</html>
