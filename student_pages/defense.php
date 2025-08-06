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
    <?php include('../includes/student-sidebar.php'); ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-y-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-blue-700">Capstone Defense Schedule</h1>
            <p class="text-gray-600">View your scheduled defense and preparation requirements.</p>
        </div>

        <!-- Defense Info Card -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Your Defense Schedule</h2>
                <p class="text-sm text-gray-600 mb-4">Here are the details of your upcoming defense.</p>

                <div class="space-y-2">
                    <div>
                        <span class="font-medium text-gray-600">Date:</span>
                        <span class="text-gray-900">September 15, 2025</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Time:</span>
                        <span class="text-gray-900">10:00 AM – 11:30 AM</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Venue:</span>
                        <span class="text-gray-900">Innovation Lab Room 304</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Panelists:</span>
                        <ul class="list-disc ml-6 text-gray-800">
                            <li>Prof. Jonathan Cruz</li>
                            <li>Ms. Angelica Ramos</li>
                            <li>Engr. Michael Santos</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Defense Requirements</h2>
                <p class="text-sm text-gray-600 mb-4">Check the status of your submission and approval.</p>

                <ul class="space-y-3">
                    <li class="flex items-center justify-between">
                        <span>Proposal Document</span>
                        <span class="bg-green-100 text-green-700 text-sm font-medium px-3 py-1 rounded-full">Approved</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>Capstone System Prototype</span>
                        <span class="bg-yellow-100 text-yellow-700 text-sm font-medium px-3 py-1 rounded-full">Pending</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>PowerPoint Presentation</span>
                        <span class="bg-red-100 text-red-700 text-sm font-medium px-3 py-1 rounded-full">Not Submitted</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tips Section -->
        <div class="mt-10">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tips for a Successful Defense</h2>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Rehearse your presentation multiple times.</li>
                <li>Prepare answers to common panel questions.</li>
                <li>Ensure your system demo runs smoothly.</li>
                <li>Dress professionally and be confident.</li>
            </ul>
        </div>
    </main>
</div>

</body>
</html>
